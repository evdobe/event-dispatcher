<?php declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Application\Event\Store;
use Application\Http\Server as HttpServer;
use Application\Http\Request as HttpRequest;
use Application\Http\Response as HttpResponse;
use Application\Http\Handler as HttpHandler;

use Application\Event\Dispatcher as EventDispatcher;
use Application\Messaging\Producer as MessagingProducer;

use Application\Execution\Process;
use Application\Messaging\MessageBuilder;
use Application\Messaging\MessageMapper;

$builder = new DI\ContainerBuilder();
$builder->addDefinitions('config/di.php');
$container = $builder->build();

$httpServer = $container->get(HttpServer::class);
$httpHandler = $container->get(HttpHandler::class);

$dispatcherConfig = include('config/dispatcher.php');


$process = $container->make(Process::class, ["callback" => function($process) use ($dispatcherConfig, $container){
    echo "Starting process...\n";
    $filter = $dispatcherConfig['filter']?$container->make($dispatcherConfig['filter']['class'], ['args' => $dispatcherConfig['filter']['args']]):null;
    $eventDispatcher = $container->make(EventDispatcher::class, [
        'store' => $container->make(Store::class, [
            'filter' => $filter, 
        ]),
        'producer' => $container->make(MessagingProducer::class, ['config' => $dispatcherConfig['connectionConfig'], 'channel' => $dispatcherConfig['channel']]), 
        'filter' => $filter, 
        'builder' => $container->make(MessageBuilder::class, [
            'mapper' =>  $container->make(
                $dispatcherConfig['mapper']['class'], 
                ['args' => $dispatcherConfig['mapper']['args']]
            )
        ])
    ]);
    $eventDispatcher->start();
}]);
$httpServer->addProcess($process);


$httpServer->on(
    "start",
    function (HttpServer $httpServer) {
        echo "HTTP httpServer is started.\n";
    }
);

$httpServer->on(
    "request",
    function (HttpRequest $request, HttpResponse $response) use ($httpHandler){
        $httpHandler->handle($request, $response);
    }
);

$httpServer->start();
