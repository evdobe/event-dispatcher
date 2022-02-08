<?php declare(strict_types=1);

namespace Application\Event\Impl;

use Application\Event\Dispatcher;
use Application\Event\Filter;
use Application\Event\Store;
use Application\Messaging\MessageBuilder;
use Application\Messaging\Producer;

class DefaultDispatcher implements Dispatcher
{

    public function __construct(
        protected Store $store, 
        protected Producer $producer, 
        protected Filter $filter,
        protected MessageBuilder $builder) 
    {
        
    }

    public function dispatch(array $eventData): bool
    {
        if (!$this->filter->matches(eventData:$eventData)){
            return false;
        }
        $message = $this->builder->build(data: $eventData);
        $this->producer->send(message: $message);
        return true;
    }
    
    public function start(): void
    {
        while (true) {
            $this->store->listen(dispatcher:$this);
        }
    }

    public function dispatchUndispatched(): void
    {
        $this->store->dispatchAllUndispatched(dispatcher:$this);
    }

}
