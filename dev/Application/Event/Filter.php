<?php declare(strict_types=1);

namespace Application\Event;

interface Filter
{
    public function __construct(array $args);

    public function matches(array $eventData):bool;

    public function getSqlMatcher():?string;
}
