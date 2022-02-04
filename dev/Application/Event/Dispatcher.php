<?php declare(strict_types=1);

namespace Application\Event;

use Application\Messaging\Producer;

interface Dispatcher
{
    public function __construct(Store $store, Producer $producer);

    public function start():void;
}
