<?php

namespace Application\Messaging;

interface MessageBuilder
{
    public function __construct(array $args);

    public function build(array $data):Message;
}
