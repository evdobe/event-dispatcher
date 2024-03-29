<?php declare(strict_types=1);

namespace Application\Execution;

interface Timer
{
    public function tick(int $intervalMs, callable $callback, array $params = []):int|bool;
}
