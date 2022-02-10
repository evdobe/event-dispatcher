<?php declare(strict_types=1);

namespace Application\Event;

interface Store
{
    public function __construct(?Filter $filter = null, bool $setupListener = false);

    public function listen(Dispatcher $dispatcher):void;

    public function dispatchAllUndispatched(Dispatcher $dispatcher):void;
}
