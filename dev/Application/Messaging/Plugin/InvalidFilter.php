<?php declare(strict_types=1);

namespace Application\Messaging\Plugin;

use Application\Messaging\Filter;
use Application\Messaging\Message;

class InvalidFilter implements Filter
{
    public function __construct(array $arg){

    }
    
    public function matches(Message $message): bool
    {
        if ($message->getHeader('name') == 'invalid'){
            throw new \Exception('I cannot handle this message!!!');
        }
        return true;
    }
}
