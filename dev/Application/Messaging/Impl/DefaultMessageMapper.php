<?php

namespace Application\Messaging\Impl;

use Application\Messaging\Message;
use Application\Messaging\MessageMapper;

class DefaultMessageMapper implements MessageMapper
{
    protected string $keyAttr = 'aggregate_id';
    
    public function __construct(array $args = [])
    {
        if (!empty($args)){
            $this->keyAttr = $args[0];
        }
    }

    public function map(array $data, Message $message): Message
    {
        return $message->withBody(json_encode($data['data']))
            ->withProperty('timestamp', $data['timestamp'])
            ->withProperty('id', $data['id'])
            ->withHeader('name', $data['name'])
            ->withHeader('aggregate_id', $data['aggregate_id'])
            ->withHeader('aggregate_version', $data['aggregate_version'])
            ->withKey($data[$this->keyAttr]);
    }
}
