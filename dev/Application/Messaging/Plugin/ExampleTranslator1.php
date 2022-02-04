<?php declare(strict_types=1);

namespace Application\Messaging\Plugin;

use Application\Messaging\Message;
use Application\Messaging\Translator;

class ExampleTranslator1 implements Translator
{
    public function __construct(array $arg){

    }
    
    public function translate(Message $message): Message
    {
        $body = $message->getBody();
        $body.= "! (translated by ExampleTranslator1)";
        return $message->withBody($body);
    }
}
