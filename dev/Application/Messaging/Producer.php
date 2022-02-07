<?php declare(strict_types=1);

namespace Application\Messaging;

interface Producer
{
    public function __construct(array $config);

    public function send(Message $message):void;

}
