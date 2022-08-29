<?php declare(strict_types=1);

namespace Application\Messaging;

interface MessageBuilder
{
    public function __construct(MessageMapper $mapper);

    public function build(array $data):Message;
}
