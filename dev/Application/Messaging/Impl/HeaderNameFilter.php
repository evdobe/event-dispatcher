<?php declare(strict_types=1);

namespace Application\Messaging\Impl;

use Application\Messaging\Filter;
use Application\Messaging\Message;

class HeaderNameFilter implements Filter
{
    protected array $names;

    public function __construct(array $arg)
    {
        $this->names = $arg;
    }

    public function matches(Message $message): bool
    {
        return in_array($message->getHeader('name'), $this->names);
    }
}
