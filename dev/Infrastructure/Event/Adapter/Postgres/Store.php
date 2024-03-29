<?php declare(strict_types=1);

namespace Infrastructure\Event\Adapter\Postgres;

use Application\Event\Dispatcher;
use Application\Event\Filter;
use Application\Event\Mapper;
use Application\Event\Store as EventStore;
use Application\Messaging\Message;
use PDO;
use PDOException;

class Store implements EventStore
{

    protected PDO $con;

    protected bool $listenerSetUp = false;

    protected const EVENT_NOTIFY_PROCEDURE_SQL = "
        CREATE OR REPLACE FUNCTION public.event_notify()
        RETURNS trigger
        AS \$function\$
        BEGIN
            IF NEW.dispatched = false %%filter_matcher%% THEN
                PERFORM pg_notify('event', NEW.id::text);
            END IF;
            RETURN NULL;
        END;
        \$function\$
        LANGUAGE plpgsql;
    ";

    protected const EVENT_NOTIFY_TRIGGER_SQL = "
        CREATE TRIGGER trigger_on_event_insert AFTER INSERT ON \"event\"
        FOR EACH ROW EXECUTE PROCEDURE event_notify();
    ";

    protected const UPDATE_EVENT_SQL = "
        UPDATE event SET dispatched=true, dispatched_at=NOW() WHERE id=:id and dispatched=false;
    ";

    protected const SELECT_UNDISPATCHED_EVENTS_SQL = "
        SELECT * FROM event AS NEW where NEW.dispatched = false %%filter_matcher%% ORDER BY id LIMIT %%polling_select_limit%%;
    ";

    protected const LISTEN_TIMEOUT = 1000;

    public function __construct(protected ?Filter $filter = null, bool $setupListener = false)
    {
        $this->con = new PDO("pgsql:host=".getenv('STORE_DB_HOST').";port=" . (getenv('DB_PORT') ?: '5432').";dbname=".getenv('STORE_DB_NAME'), getenv('STORE_DB_USER'), getenv('STORE_DB_PASSWORD'));
        if ($setupListener){
            $this->setUpListener();
        }
    }

    public function listen(Dispatcher $dispatcher):void{
        if (!$this->listenerSetUp){
            throw new \Exception('Listener is not set up!');
        }
        $dispatcher->pollProducer();
        $notification = $this->con->pgsqlGetNotify(PDO::FETCH_ASSOC, self::LISTEN_TIMEOUT);
        if (!$notification) {
            return;
        }
        $eventId = $notification['payload'];
        $stmt = $this->con->prepare("SELECT * FROM event WHERE id=:id"); 
        $stmt->execute(['id' => $eventId]); 
        $eventData = $stmt->fetch();
        $eventData['data'] = json_decode($eventData['data'], true);
        echo "Received notification for event with id = ".$eventData['id']."\n";
        $this->dispatch(eventData:$eventData, dispatcher:$dispatcher);
    }

    public function dispatchAllUndispatched(Dispatcher $dispatcher):void
    {
        $data = $this->con
            ->query(str_replace("%%polling_select_limit%%", (getenv('POLLING_DB_SELECT_LIMIT') ?: '10000'), str_replace("%%filter_matcher%%", $this->getFilterMatcher(), SELF::SELECT_UNDISPATCHED_EVENTS_SQL)), \PDO::FETCH_ASSOC)
            ->fetchAll();
        foreach ($data as $eventData){
            $eventData['data'] = json_decode($eventData['data'], true);
            echo "Dispatching undispatched event with id = ".$eventData['id']."\n";
            $this->dispatch(eventData:$eventData, dispatcher:$dispatcher);
        }
    }

    protected function getFilterMatcher():string{
        $filterMatcher = '';
        if ($this->filter && $matcherStr = $this->filter->getSqlMatcher()){
            $filterMatcher = "AND (".$matcherStr.")";
        }
        return $filterMatcher;
    }

    protected function setUpListener(){
        $filterMatcher = '';
        if ($this->filter && $matcherStr = $this->filter->getSqlMatcher()){
            $filterMatcher = "AND (".$matcherStr.")";
        }
        $this->con->exec(str_replace("%%filter_matcher%%", $this->getFilterMatcher(), SELF::EVENT_NOTIFY_PROCEDURE_SQL));
        try {
            $this->con->exec(SELF::EVENT_NOTIFY_TRIGGER_SQL);
        }
        catch (PDOException $e){
            if ($e->getCode() == '42710'){
                //SQLSTATE[42710]: Duplicate object: 7 ERROR:  trigger "trigger_on_event_insert" for relation "event"
                echo "Trigger already defined: ".$e->getMessage()."\n";
            }
            else  {
                throw $e;
            }    
        }
        $this->con->exec("LISTEN event;");
        $this->listenerSetUp = true;
    }

    protected function dispatch(array $eventData, Dispatcher $dispatcher):void{
        if (!$dispatcher->dispatch(eventData: $eventData)){
            echo "Event with id ".$eventData['id']." skipped by dispatcher.\n";
        }
    }

    public function dispatchSuccessCallback(string $eventId):void{
        $statement = $this->con->prepare(self::UPDATE_EVENT_SQL);
        $statement->execute(['id' => $eventId]);
    }
}
