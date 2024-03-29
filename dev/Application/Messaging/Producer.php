<?php declare(strict_types=1);

namespace Application\Messaging;

interface Producer
{
    public function __construct(array $config, string $channel);

    public function send(Message $message):void;

    public function setDeliverySuccessCallback(callable $callback):void;

    public function poll(int $timeoutMs) : void;

}
