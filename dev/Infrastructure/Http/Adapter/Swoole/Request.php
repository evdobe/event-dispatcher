<?php declare(strict_types=1);

namespace Infrastructure\Http\Adapter\Swoole;

use Application\Http\Request as HttpRequest;

use Swoole\Http\Request as SwooleHttpRequest; 

class Request implements HttpRequest
{
    public function __construct(protected SwooleHttpRequest $delegate)
    {
        
    }
}
