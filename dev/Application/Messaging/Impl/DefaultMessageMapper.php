<?php declare(strict_types=1);

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
        $res =  $message->withBody(json_encode($data['data']))
            ->withProperty('timestamp', $data['timestamp'])
            ->withProperty('id', (string)$data['id'])
            ->withHeader('name', (string)$data['name'])
            ->withHeader('aggregate_id', (string)$data['aggregate_id'])
            ->withHeader('aggregate_version', (string)$data['aggregate_version'])
            ->withKey((string)$data[$this->keyAttr]);

        if (!empty($data['correlation_id'])){
            $res = $res->withProperty('correlation_id', (string)$data['correlation_id']);
        }
        if (!empty($data['user_id'])){
            $res = $res->withProperty('user_id', (string)$data['user_id']);
        }

        return $res;
    }
}
