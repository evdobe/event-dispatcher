<?php declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;
use Assert\Assertion;
use Assert\Assert;

use Enqueue\RdKafka\RdKafkaContext;
use Enqueue\RdKafka\RdKafkaTopic;
use Enqueue\RdKafka\RdKafkaConsumer;
use Enqueue\RdKafka\RdKafkaProducer;

use Behat\Testwork\Hook\Scope\BeforeSuiteScope;

/**
 * Defines application features from the specific context.
 */
class DispatchEventContext implements Context
{

    const NEW_EVENT_INSERT_SQL = 'INSERT INTO event 
        (name, "aggregate_id", "aggregate_version", data, "timestamp", "correlation_id", "user_id") 
        VALUES (:name, :aggregate_id, :aggregate_version, :data, :timestamp, :correlation_id, :user_id)';

    const ALREADY_DISPATCHED_EVENT_INSERT_SQL = 'INSERT INTO event 
        (name, "aggregate_id", "aggregate_version", data, "timestamp", "dispatched", "dispatched_at") 
        VALUES (:name, :aggregate_id, :aggregate_version, :data, :timestamp, true, :dispatched_at)';


    protected static RdKafkaContext $kafkaContext;

    protected string $eventChannelName;

    protected PDO $con;

    protected int|string $lastEventId;


    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->con = new PDO("pgsql:host=".getenv('STORE_DB_HOST').";dbname=".getenv('STORE_DB_NAME'), getenv('STORE_DB_USER'), getenv('STORE_DB_PASSWORD'));
        $stmt = $this->con->prepare('TRUNCATE TABLE "event"');
        $stmt->execute(); 
    }

    protected function getFilterMatchingEventName(){
        $filterConfig = getenv('EVENT_FILTER');
        $parts = explode('|', $filterConfig);
        if (count($parts)>1){
            return $parts[1];
        }
        return 'arandomname';
    }

    /**
     * @BeforeSuite
     */
    public static function createKafkaContext(BeforeSuiteScope $scope)
    {
        self::$kafkaContext = (new \Enqueue\RdKafka\RdKafkaConnectionFactory(
            [
                'global' => [
                    'metadata.broker.list' => getenv('MESSAGE_BROKER_HOST').':'.getenv('MESSAGE_BROKER_PORT'),
                    'group.id' => 'tester',
                ],
                'topic' => [
                    'auto.offset.reset' => 'earliest',
                    'enable.auto.commit' => 'true',
                    'auto.commit.interval.ms' => '10'
                ],
            ]
        ))->createContext();
    }

    /**
     * @Given The event channel is set
     */
    public function theEventChannelIsSet()
    {
        $this->eventChannelName = getenv('EVENT_CHANNEL');
        Assert::that($this->eventChannelName)->notEmpty();
    }

    /**
     * @When an event matching dispatcher filter is inserted in db
     */
    public function anEventMatchingDispatcherFilterIsInsertedInDb()
    {
        $statement = $this->con->prepare(self::NEW_EVENT_INSERT_SQL);
        $statement->execute(
            [
                ':name' => $this->getFilterMatchingEventName(),
                ':aggregate_id' => 2,
                ':aggregate_version' => 1,
                ':data' => '{"akey":"avalue"}',
                ':timestamp' => '2022-01-28 12:23:56.123456',
                ':correlation_id' => 123,
                ':user_id' => 23232
            ]
        );
        $this->lastEventId = $this->con->lastInsertId();

    }

    /**
     * @Then dispatcher should produce a message with event data on event channel
     */
    public function dispatcherShouldProduceAMessageWithEventDataOnEventChannel()
    {
        $topic = self::$kafkaContext->createTopic($this->eventChannelName);
        $topic->setPartition(0);
        $consumer = self::$kafkaContext->createConsumer($topic);
        $message = $consumer->receive(10000);
        $consumer->acknowledge($message);

        $expectedMessage = self::$kafkaContext->createMessage('{"akey":"avalue"}', [
            'id' => (string)$this->lastEventId,
            'timestamp' => '2022-01-28 12:23:56.123456',
            'correlation_id' => "123",
            'user_id' => "23232"
        ], [
            'name' => $this->getFilterMatchingEventName(),
            'aggregate_id' => "2",
            'aggregate_version' => "1"
        ]);
        $expectedMessage->setKey('2');

        Assert::that($message->getBody())->eq($expectedMessage->getBody());
        Assert::that($message->getHeaders())->eq($expectedMessage->getHeaders());
        Assert::that($message->getProperties())->eq($expectedMessage->getProperties());
        Assert::that($message->getKey())->eq($expectedMessage->getKey());
    }

    /**
     * @When an event not matching dispatcher filter is inserted in db
     */
    public function anEventNotMatchingDispatcherFilterIsInsertedInDb()
    {
        $statement = $this->con->prepare(self::NEW_EVENT_INSERT_SQL);
        $statement->execute(
            [
                ':name' => 'notmatchingname',
                ':aggregate_id' => 2,
                ':aggregate_version' => 1,
                ':data' => '{"akey":"avalue"}',
                ':timestamp' => '2022-01-28 12:23:56.123456',
                ':correlation_id' => null,
                ':user_id' => null,
            ]
        );
        $this->lastEventId = $this->con->lastInsertId();
    }

    /**
     * @Then dispatcher should not produce a message with event data on event channel
     */
    public function dispatcherShouldNotProduceAMessageWithEventDataOnEventChannel()
    {
        $topic = self::$kafkaContext->createTopic($this->eventChannelName);
        $topic->setPartition(0);
        $consumer = self::$kafkaContext->createConsumer($topic);
        $message = $consumer->receive(10000);
        Assert::that($message)->null();
    }

    /**
     * @Then the event should be marked as dipatched in db
     */
    public function theEventShouldBeMarkedAsDipatchedInDb()
    {
        sleep(2);
        $stmt = $this->con->prepare('SELECT "dispatched", "dispatched_at" FROM event where id = :id');
        $stmt->execute(['id' => $this->lastEventId]); 
        $data = $stmt->fetch();

        Assert::that($data['dispatched'])->true();
        Assert::that($data['dispatched_at'])->notNull();
    }

    /**
     * @Then the event should not be marked as dipatched in db
     */
    public function theEventShouldNotBeMarkedAsDipatchedInDb()
    {
        sleep(2);
        $stmt = $this->con->prepare('SELECT "dispatched", "dispatched_at" FROM event where id = :id');
        $stmt->execute(['id' => $this->lastEventId]); 
        $data = $stmt->fetch();

        Assert::that($data['dispatched'])->false();
        Assert::that($data['dispatched_at'])->null();
    }

    /**
     * @When an already dispatcehd event is inserted in db
     */
    public function anAlreadyDispatcehdEventIsInsertedInDb()
    {
        $statement = $this->con->prepare(self::ALREADY_DISPATCHED_EVENT_INSERT_SQL);
        $statement->execute(
            [
                ':name' => $this->getFilterMatchingEventName(),
                ':aggregate_id' => 2,
                ':aggregate_version' => 1,
                ':data' => '{"akey":"avalue"}',
                ':timestamp' => '2022-01-28 12:23:56.123456',
                ':dispatched_at' => '2022-01-28 12:26:47.123456',
            ]
        );
        $this->lastEventId = $this->con->lastInsertId();
    }

    /**
     * @Then the event dispatced datetime should not be altered
     */
    public function theEventDispatcedDatetimeShouldNotBeAltered()
    {
        $stmt = $this->con->prepare('SELECT "dispatched_at" FROM event where id = :id');
        $stmt->execute(['id' => $this->lastEventId]); 
        $data = $stmt->fetch();

        Assert::that($data['dispatched_at'])->eq('2022-01-28 12:26:47.123456');
    }

    /**
     * @Given we stop kafka
     */
    public function weStopKafka()
    {
        echo "Stop the kafka container with `bin/env dev stop kafka` and then press any key to continue";
    }

    /**
     * @Given kafka is down
     */
    public function kafkaIsDown()
    {
        // Disable canonical mode, so characters are available immediately
        system('stty -icanon');
        // Read a single character from the user
        $input = fgetc(STDIN);
        // Re-enable canonical mode
        system('stty icanon');
        echo "Assuming kafka is now stopped...\n";
    }

    /**
     * @Then we start kafka
     */
    public function weStartKafka()
    {
        echo "Start the kafka container with `bin/env dev up -d kafka` and then press any key to continue";
    }

    /**
     * @Then kafka is up
     */
    public function kafkaIsUp()
    {
        // Disable canonical mode, so characters are available immediately
        system('stty -icanon');
        // Read a single character from the user
        $input = fgetc(STDIN);
        // Re-enable canonical mode
        system('stty icanon');
        echo "Assuming kafka is now started...\n";
    }

}
