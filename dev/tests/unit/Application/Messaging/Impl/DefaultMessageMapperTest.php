<?php declare(strict_types=1);

namespace Application\Messaging\Impl;

use Infrastructure\Messaging\Adapter\EnqueueRdkafka\Message;
use Enqueue\RdKafka\RdKafkaMessage;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class DefaultMessageMapperTest extends TestCase
{

    protected function eventData(){
        return [
            'id' => 27,
            'name' => 'eventName',
            'aggregate_id' => 12,
            'correlation_id' => 123,
            'aggregate_version' => 13,
            'data' => ['key1' => 'value1', 'key2' => 'value2'],
            'timestamp' => '2022-01-27 12:03:23'
        ];
    }

    protected function expectedMessage(){
        return (new Message(delegate: new RdKafkaMessage()))
            ->withHeader(name: 'name', value: 'eventName')
            ->withHeader(name: 'aggregate_id', value: '12')
            ->withHeader(name: 'aggregate_version', value: '13')
            ->withProperty(name: 'timestamp', value: '2022-01-27 12:03:23')
            ->withProperty(name: 'id', value: '27')
            ->withProperty(name: 'correlation_id', value: '123')
            ->withBody(body: '{"key1":"value1","key2":"value2"}')
            ->withKey('12')
        ;
    }

    

    public function testShouldMapTheMessageToDataForDbInsert(){
        $mapper = new DefaultMessageMapper();
        $this->assertEquals($this->expectedMessage(), $mapper->map(data: $this->eventData(), message:new Message(delegate: new RdKafkaMessage()))
        );
    }
}
