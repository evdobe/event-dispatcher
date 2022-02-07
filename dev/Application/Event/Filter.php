<?php

namespace Application\Event;

interface Filter
{
    public function __construct(array $arg);

    public function matches(array $eventData):bool;
}
