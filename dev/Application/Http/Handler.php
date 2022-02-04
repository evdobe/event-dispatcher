<?php declare(strict_types=1);

namespace Application\Http;

use Application\Http\Request;
use Application\Http\Response;

interface Handler
{
    public function handle(Request $request, Response $response);
}
