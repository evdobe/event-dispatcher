<?php

namespace Application\Event\Impl;

use Application\Event\Filter;

class NameFilter implements Filter
{

    protected array $names;
    public function __construct(array $args)
    {
        $this->names = $args;
    }

    public function matches(array $eventData): bool
    {
        return in_array($eventData, $this->names);
    }

}
