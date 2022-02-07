<?php

namespace Application\Messaging;

interface MessageBuilder
{
    public function __construct(array $arg);

    public function build(array $data):Message;
}
