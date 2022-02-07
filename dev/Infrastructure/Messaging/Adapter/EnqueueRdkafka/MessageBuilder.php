<?php

namespace Infrastructure\Messaging\Adapter\EnqueueRdkafka;

use Application\Messaging\MessageBuilder as ApplicationMessageBuilder;

use Enqueue\RdKafka\RdKafkaMessage;

class MessageBuilder implements ApplicationMessageBuilder
{
    protected string $keyAttr = 'aggregateId';
    
    public function __construct(array $arg = [])
    {
        if (!empty($arg)){
            $this->keyAttr = $arg[0];
        }
    }

    public function build(array $data): Message
    {
        $message = new Message(new RdKafkaMessage());
        $message->withBody($data['data'])
            ->withProperty('timestamp', $data['timestamp'])
            ->withProperty('id', $data['id'])
            ->withHeader('name', $data['name'])
            ->withHeader('aggregateId', $data['aggregateId'])
            ->withHeader('aggregateVersion', $data['aggregateVersion']);
        $message->withKey($data[$this->keyAttr]);
        return $message;   
    }
}
