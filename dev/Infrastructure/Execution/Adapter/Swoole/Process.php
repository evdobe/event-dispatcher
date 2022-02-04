<?php

namespace Infrastructure\Execution\Adapter\Swoole;

use Application\Execution\Process as ApplicationProcess;

use Swoole\Process as SwooleProcess;

class Process implements ApplicationProcess
{
    protected SwooleProcess $delegate;

    public function __construct(callable $callback)
    {
        $this->delegate = new SwooleProcess($callback);
    }

    public function getDelegate():SwooleProcess{
        return $this->delegate;
    }

}
