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
use Application\Execution\Timer;
use Application\Messaging\MessageBuilder;
use Application\Messaging\MessageMapper;
use DI\Container;

use function PHPUnit\Framework\isEmpty;

$builder = new DI\ContainerBuilder();
$builder->addDefinitions('config/di.php');
$container = $builder->build();

$httpServer = $container->get(HttpServer::class);
$httpHandler = $container->get(HttpHandler::class);

$timer = $container->get(Timer::class);

$dispatcherConfig = include('config/dispatcher.php');

$process = $container->make(Process::class, ["callback" => function($process) use ($dispatcherConfig, $container){
    echo "Starting process...\n";
    $eventDispatcher = buildDispatcher(config:$dispatcherConfig, container: $container, setupListener:true);
    $eventDispatcher->start();
    sleep(1);
}]);
$httpServer->addProcess($process);

$httpServer->on(
    "start",
    function (HttpServer $httpServer) use ($timer, $dispatcherConfig, $container) {
        $eventDispatcher = buildDispatcher(config:$dispatcherConfig, container: $container, setupListener:false);
        echo "Checking for undispatched events...\n";
        $eventDispatcher->dispatchUndispatched();
        $timer->tick(2*60*1000, function() use ($dispatcherConfig, $container, $eventDispatcher){
            echo "Priodically checking for undispatched events...\n";
            $eventDispatcher->dispatchUndispatched();
            sleep(1);
        });
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

function buildDispatcher(array $config, Container $container, bool $setupListener):EventDispatcher{
    $filter = $config['filter']?$container->make($config['filter']['class'], ['args' => $config['filter']['args']]):null;
    return  $container->make(EventDispatcher::class, [
        'store' => $container->make(Store::class, [
            'filter' => $filter, 
            'setupListener' => $setupListener
        ]),
        'producer' => $container->make(MessagingProducer::class, ['config' => $config['connectionConfig'], 'channel' => $config['channel']]), 
        'filter' => $filter, 
        'builder' => $container->make(MessageBuilder::class, [
            'mapper' =>  $container->make(
                $config['mapper']['class'], 
                ['args' => $config['mapper']['args']]
            )
        ])
    ]);
}
