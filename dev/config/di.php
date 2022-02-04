<?php declare(strict_types=1);

return [
    \Application\Http\Server::class => DI\get(\Infrastructure\Http\Adapter\Swoole\Server::class),
    \Infrastructure\Http\Adapter\Swoole\Server::class =>  DI\autowire()
        ->constructorParameter('port', intval(getenv('HTTP_PORT'))),
    \Application\Http\Handler::class => DI\get(\Application\Http\Impl\PingHandler::class),
    \Application\Messaging\Consumer::class => DI\autowire(\Infrastructure\Messaging\Adapter\EnqueueRdkafka\Consumer::class),
    \Application\Messaging\Handler::class => DI\autowire(\Application\Messaging\Impl\EventHandler::class),
    \Application\Execution\Process::class => DI\autowire(\Infrastructure\Execution\Adapter\Swoole\Process::class),
    \Application\Event\Store::class => DI\autowire(\Infrastructure\Event\Adapter\Postgres\Store::class)
        ->constructorParameter('mapper', DI\get(Infrastructure\Event\Adapter\Pdo\Mapper::class)),
    
];