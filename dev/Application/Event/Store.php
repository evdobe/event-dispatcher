<?php declare(strict_types=1);

namespace Application\Event;

interface Store
{
    public function listen(Dispatcher $dispatcher):void;
}
