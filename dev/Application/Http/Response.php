<?php declare(strict_types=1);

namespace Application\Http;

interface Response
{
    public function end(string $content = null): void;

    public function header(string $key, string $value): void;


}
