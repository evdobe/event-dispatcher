<?php

namespace Application\Messaging;

interface MessageMapper
{
    public function __construct(array $args);

    public function map(array $data, Message $message):Message;
}
