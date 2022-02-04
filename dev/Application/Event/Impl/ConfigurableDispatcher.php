<?php declare(strict_types=1);

namespace Application\Event\Impl;

use Application\Event\Dispatcher;
use Application\Event\Store;
use Application\Messaging\Producer;

class ConfigurableDispatcher implements Dispatcher
{

    public function __construct(protected Store $store, protected Producer $producer, protected array $config)
    {
        
    }
    
    public function start(): void
    {
        
    }

}
