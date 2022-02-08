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

$builder = new DI\ContainerBuilder();
$builder->addDefinitions('config/di.php');
$container = $builder->build();

$httpServer = $container->get(HttpServer::class);
$httpHandler = $container->get(HttpHandler::class);

$messagingConfig = include('config/messaging.php');


$process = $container->make(Process::class, ["callback" => function($process) use ($messagingConfig, $container){
    echo "Starting process...\n";
    $eventDispatcher = $container->make(EventDispatcher::class, [
        'store' => $container->get(Store::class),
        'producer' => $container->make(MessagingProducer::class, ['config' => $messagingConfig['connectionConfig'], 'channel' => $messagingConfig['channel']]), 
        'filter' => $messagingConfig['filter']?$container->make($messagingConfig['filter']['class'], ['args' => $messagingConfig['filter']['args']]):null, 
        'builder' => $messagingConfig['builder']?$container->make($messagingConfig['builder']['class'], ['args' => $messagingConfig['builder']['args']]):null, 
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
