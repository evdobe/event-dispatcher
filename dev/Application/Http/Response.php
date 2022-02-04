<?php

namespace Application\Http;

interface Response
{
    public function end(string $content = null): void;

    public function header(string $key, string $value): void;


}
