<?php

namespace Infrastructure\Event\Adapter\Pdo;

use Application\Event\Mapper as EventMapper;
use Application\Messaging\Message;

class Mapper implements EventMapper
{
    public function map(Message $message, string $channel): array
    {
        return [
            ':name' => $message->getHeader('name'),
            ':channel' => $channel,
            ':correlation_id' => $message->getPropery('id'),
            ':aggregate_id' => $message->getHeader('aggregateId'),
            ':aggregate_version' => $message->getHeader('aggregateVersion'),
            ':data' => $message->getBody(),
            ':timestamp' => $message->getPropery('timestamp')
        ];
    }

}
