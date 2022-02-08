<?php

namespace Infrastructure\Messaging\Adapter\EnqueueRdkafka;

use Application\Messaging\Message;
use Application\Messaging\Producer as ApplicationProducer;

use Enqueue\RdKafka\RdKafkaContext;
use Enqueue\RdKafka\RdKafkaConnectionFactory;
use Enqueue\RdKafka\RdKafkaTopic;
use Enqueue\RdKafka\RdKafkaProducer;
use Enqueue\RdKafka\RdKafkaMessage;


class Producer implements ApplicationProducer
{

    protected RdKafkaContext $context;

    protected RdKafkaProducer $delegate;

    protected RdKafkaTopic $topic;

    public function __construct(protected array $config, protected string $channel)
    {
        $this->context = (new RdKafkaConnectionFactory($config))
            ->createContext();
        $this->topic = $this->context->createTopic($channel);
        $this->delegate = $this->context->createProducer();
    }

    public function send(Message $message):void
    {
        $kafkaMessage = $this->context->createMessage(
            body: $message->getBody(),
            properties: $message->getProperties(),
            headers: $message->getHeaders()
        );
        $kafkaMessage->setKey($message->getKey());
        $this->delegate->send(destination:$this->topic, message:$kafkaMessage);
        
    }
}
