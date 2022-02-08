<?php

namespace Application\Messaging\Impl;

use Application\Messaging\Message;
use Application\Messaging\MessageMapper;

class DefaultMessageMapper implements MessageMapper
{
    protected string $keyAttr = 'aggregateId';
    
    public function __construct(array $args = [])
    {
        if (!empty($args)){
            $this->keyAttr = $args[0];
        }
    }

    public function map(array $data, Message $message): Message
    {
        return $message->withBody($data['data'])
            ->withProperty('timestamp', $data['timestamp'])
            ->withProperty('id', $data['id'])
            ->withHeader('name', $data['name'])
            ->withHeader('aggregateId', $data['aggregateId'])
            ->withHeader('aggregateVersion', $data['aggregateVersion'])
            ->withKey($data[$this->keyAttr]);
    }
}
