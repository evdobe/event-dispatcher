<?php declare(strict_types=1);

namespace Application\Http\Impl;

use Application\Http\Handler;
use Application\Http\Request;
use Application\Http\Response;

class PingHandler implements Handler
{
    public function handle(Request $request, Response $response)
    {
        $response->header(key: 'Content-Type', value: 'application/json');
        $response->header(key: 'Cache-Control', value: 'no-cache');
        $response->end(content: json_encode(['ack' => time()]));
    }
   
}
