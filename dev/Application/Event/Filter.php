<?php

namespace Application\Event;

interface Filter
{
    public function __construct(array $args);

    public function matches(array $eventData):bool;
}
