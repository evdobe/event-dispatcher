<?php declare(strict_types=1);

namespace Infrastructure\Event\Adapter\Pdo;

use Infrastructure\Messaging\Adapter\EnqueueRdkafka\Message;
use Enqueue\RdKafka\RdKafkaMessage;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class MapperTest extends TestCase
{
    protected function enqueueRdkafkaMessage(){
        return (new Message(delegate: new RdKafkaMessage()))
            ->withHeader(name: 'name', value: 'eventName')
            ->withHeader(name: 'aggregateId', value: 12)
            ->withHeader(name: 'aggregateVersion', value: 13)
            ->withProperty(name: 'timestamp', value: '2022-01-27 12:03:23 Z')
            ->withProperty(name: 'id', value: 27)
            ->withBody(body: 'a test body')
        ;
    }

    protected function expectedEventData(){
        return [
            ':name' => 'eventName',
            ':channel' => 'eventChannel',
            ':correlation_id' => 27,
            ':aggregate_id' => 12,
            ':aggregate_version' => 13,
            ':data' => 'a test body',
            ':timestamp' => '2022-01-27 12:03:23 Z'
        ];
    }

    public function testShouldMapTheMessageToDataForDbInsert(){
        $mapper = new Mapper();
        $this->assertEquals($this->expectedEventData(), $mapper->map(
            message: $this->enqueueRdkafkaMessage(), 
            channel: 'eventChannel')
        );
    }
}
