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

    protected array $channels;

    public function __construct(protected array $config)
    {
        $this->context = (new RdKafkaConnectionFactory($config))
            ->createContext();
        $this->delegate = $this->context->createProducer();
    }

    public function send(string $channel, Message $message):void
    {
        if (!array_key_exists($channel, $this->channels)){
            $this->channels[$channel] = $this->context->createTopic($channel);
        }
        $topic = $this->channels[$channel];
        $kafkaMessage = new RdKafkaMessage(
            body: $message->getBody(),
            properties: $message->getProperties(),
            headers: $message->getHeaders()
        );
        $this->delegate->send(destination:$topic, message:$kafkaMessage);
        
    }
}
