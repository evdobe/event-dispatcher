<?php declare(strict_types=1);

namespace Application\Execution;

interface Process
{
    public function __construct(callable $callback);

}
