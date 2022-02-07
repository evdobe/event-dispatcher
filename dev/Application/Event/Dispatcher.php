<?php declare(strict_types=1);

namespace Application\Event;

use Application\Messaging\MessageBuilder;
use Application\Messaging\Producer;

interface Dispatcher
{
    public function __construct(Store $store, Producer $producer, Filter $filter, MessageBuilder $builder);

    public function dispatch(array $eventData):bool;
    
    public function start():void;
}
