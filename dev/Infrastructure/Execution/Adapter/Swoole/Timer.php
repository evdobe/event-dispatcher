<?php declare(strict_types=1);

namespace Infrastructure\Execution\Adapter\Swoole;

use Application\Execution\Timer as ApplicationTimer;

class Timer implements ApplicationTimer
{
    public function tick(int $intervalMs, callable $callback, array $params = []): int|bool
    {
        return \Swoole\Timer::tick($intervalMs, $callback, $params);
    }
}
