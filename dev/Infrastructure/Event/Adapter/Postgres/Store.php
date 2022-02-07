<?php declare(strict_types=1);

namespace Infrastructure\Event\Adapter\Postgres;

use Application\Event\Dispatcher;
use Application\Event\Mapper;
use Application\Event\Store as EventStore;
use Application\Messaging\Message;
use PDO;
use PDOException;

class Store implements EventStore
{

    protected PDO $con;

    protected const EVENT_NOTIFY_PROCEDURE_SQL = "
        CREATE OR REPLACE FUNCTION public.event_notify()
        RETURNS trigger
        AS \$function\$
        BEGIN
            IF NEW.dispatched = false THEN
                PERFORM pg_notify('event', row_to_json(NEW)::text);
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

    protected const LISTEN_TIMEOUT = 60*10000;

    public function __construct()
    {
        $this->con = new PDO("pgsql:host=".getenv('STORE_DB_HOST').";dbname=".getenv('STORE_DB_NAME'), getenv('STORE_DB_USER'), getenv('STORE_DB_PASSWORD'));
        $this->setUpListener();
    }

    public function listen(Dispatcher $dispatcher):void{
        echo "Listening for pgsql notifications...\n";
        $notification = $this->con->pgsqlGetNotify(PDO::FETCH_ASSOC, self::LISTEN_TIMEOUT);
        if (!$notification) {
            echo "Timeout with no messages\n";
            return;
        }
        $eventData = json_decode($notification['payload'], true);
        $this->dispatch(eventData:$eventData, dispatcher:$dispatcher);
    }

    protected function setUpListener(){
        $this->con->exec(SELF::EVENT_NOTIFY_PROCEDURE_SQL);
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
    }

    protected function dispatch(array $eventData, Dispatcher $dispatcher){
        $this->con->beginTransaction();
        try {
            $statement = $this->con->prepare(self::UPDATE_EVENT_SQL);
            $statement->execute(['id' => $eventData['id']]);
            if ($statement->rowCount() != 1) {
                $this->con->rollBack();
                echo "!!!POSSIBLE ERROR??? Failed to update event with id ".$eventData['id']."! Maybe already dispatched? Skipping event.\n";
                return;
            }
            if ($dispatcher->dispatch(eventData: $eventData)){
                $this->con->commit();
                return;
            }
            $this->con->rollBack();
        }
        catch(\Exception $e) {
            $this->con->rollBack();
            throw $e;
        } 
    }
}
