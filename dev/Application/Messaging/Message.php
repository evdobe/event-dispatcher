<?php declare(strict_types=1);

namespace Application\Messaging;

interface Message
{

    public function getBody():string;

    public function getHeaders():array;

    public function getHeader(string $name, mixed $default = null):mixed;

    public function getProperties():array;

    public function getPropery(string $name, mixed $default = null):mixed;

    public function getKey():?string;

    public function withBody(string $body):Message;

    public function withHeader(string $name, mixed $value):Message;

    public function withProperty(string $name, mixed $value):Message;

    public function withoutHeader(string $name):Message;

    public function withoutProperty(string $name):Message;
}