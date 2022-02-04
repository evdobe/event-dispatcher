<?php

namespace Infrastructure\Messaging\Adapter\EnqueueRdkafka;

use Application\Messaging\Message as ApplicationMessage;

use Enqueue\RdKafka\RdKafkaMessage;

class Message implements ApplicationMessage
{
    public function __construct(protected RdKafkaMessage $delegate)
    {
        
    }

    public function getBody():string {
        return $this->delegate->getBody();
    }

    public function getHeaders():array {
        return $this->delegate->getHeaders();
    }

    public function getHeader(string $name, mixed $default = null):mixed{
        return $this->delegate->getHeader(name: $name, default: $default);
    }

    public function getProperties():array{
        return $this->delegate->getProperties();
    }

    public function getPropery(string $name, mixed $default = null):mixed{
        return $this->delegate->getProperty(name: $name, default: $default);
    }

    public function getKey():?string{
        return $this->delegate->getKey();
    }

    public function withBody(string $body): ApplicationMessage
    {
        $delegate = clone($this->delegate);
        $delegate->setBody(body: $body);
        return new Message($delegate);
    }

    public function withHeader(string $name, mixed $value): ApplicationMessage
    {
        $delegate = clone($this->delegate);
        $delegate->setHeader(name: $name, value: $value);
        return new Message($delegate);
    }

    public function withProperty(string $name, mixed $value): ApplicationMessage
    {
        $delegate = clone($this->delegate);
        $delegate->setProperty(name: $name, value: $value);
        return new Message($delegate);
    }

    public function withoutHeader(string $name): ApplicationMessage
    {
        $delegate = clone($this->delegate);
        $headers = $delegate->getHeaders();
        unset($headers[$name]);
        $delegate->setHeaders($headers);
        return new Message($delegate);
    }

    public function withoutProperty(string $name): ApplicationMessage
    {
        $delegate = clone($this->delegate);
        $properties = $delegate->getProperties();
        unset($properties[$name]);
        $delegate->setProperties($properties);
        return new Message($delegate);
    }

}
