<?php declare(strict_types=1);

namespace Infrastructure\Http\Adapter\Swoole;

use Application\Http\Response as HttpResponse;

use Swoole\Http\Response as SwooleHttpResponse; 

class Response implements HttpResponse
{
    public function __construct(protected SwooleHttpResponse $delegate)
    {
        
    }

    public function end(?string $content = null): void
    {
        $this->delegate->end(content: $content);
    }

    public function header(string $key, string $value): void
    {
        $this->delegate->header(key: $key, value: $value);
    }
}
