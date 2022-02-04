<?php

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
class InvalidMessageContext implements Context
{
    protected static RdKafkaContext $kafkaContext;

    protected string $channelWithInvalidFilter;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
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
     * @Given The invalid channel is set
     */
    public function theInvalidChannelIsSet()
    {
        Assert::that(getenv('INVALID_CHANNEL'))->notEmpty();
    }

    /**
     * @When listener encounters an invalid message
     */
    public function listenerEncountersAnInvalidMessage()
    {
        Assert::that(getenv('EVENT_CHANNELS'))->contains('InvalidFilter');

        $this->channelWithInvalidFilter = explode(':',trim(array_values(array_filter(explode("\n", getenv('EVENT_CHANNELS')), function($row){
            return str_contains($row, 'InvalidFilter');
        }))[0]))[0];
        
        Assert::that($this->channelWithInvalidFilter)->notEmpty();

        var_dump($this->channelWithInvalidFilter);

        $topic = self::$kafkaContext->createTopic($this->channelWithInvalidFilter);
        $producer = self::$kafkaContext->createProducer();
        $producer->send($topic, self::$kafkaContext->createMessage("Invalid message",[],["name" => "invalid"]));
    }

    /**
     * @Then it should republish it on invalid channel
     */
    public function itShouldRepublishItOnInvalidChannel()
    {
        $topic = self::$kafkaContext->createTopic(getenv('INVALID_CHANNEL'));
        $consumer = self::$kafkaContext->createConsumer($topic);
        $message = $consumer->receive(60000);
        if ($message){
            while($res = $consumer->receive(1000)){
                $message = $res;
            }
        }
        $consumer->acknowledge($message);

        Assert::that($message)->notNull();
        Assert::that($message->getProperty('source'))->eq($this->channelWithInvalidFilter);
        Assert::that($message->getProperty('invalidBy'))->eq(getenv('MESSAGE_CONSUMER_GROUP'));
        Assert::that($message->getProperty('invalidAt'))->notEmpty();
        Assert::that($message->getProperty('exception'))->notEmpty();
    }
}
