<?php declare(strict_types=1);

namespace Application\Messaging;

interface Translator
{
    public function __construct(array $arg);
    public function translate(Message $message):Message;
}
