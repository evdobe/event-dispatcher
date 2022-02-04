<?php declare(strict_types=1);

namespace Application\Http;

use Application\Execution\Process;

interface Server {

    public function start():void;

    public function on(string $eventName, callable $callback):void;

    public function addProcess(Process $process): bool;
    
}