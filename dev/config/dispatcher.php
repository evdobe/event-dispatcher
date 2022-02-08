<?php declare(strict_types=1);

use Application\Event\Filter;
use Application\Messaging\MessageMapper;

$connectionConfig = [
    'global' => [
        'metadata.broker.list' => getenv('MESSAGE_BROKER_HOST').':'.getenv('MESSAGE_BROKER_PORT'),
    ]
];

$channel = getenv('EVENT_CHANNEL');

$eventFilterStr = getenv('EVENT_FILTER');
if (empty($eventFilterStr)){
    $eventFilterConfig = null;
}
else {
    $eventFilterConfigParts = array_map(function(string $row){
        return trim($row);
    },explode('|', $eventFilterStr));
    $eventFilterClassName = array_shift($eventFilterConfigParts);
    $eventFilterConfig = [
        'class' => $eventFilterClassName?$eventFilterClassName:Filter::class,
        'args' => array_values($eventFilterConfigParts)
    ];
}

$messageMapperStr = getenv('MESSAGE_MAPPER');
$messageMapperConfigParts = array_map(function(string $row){
    return trim($row);
},explode('|', $messageMapperStr));

$messageMapperClassName = array_shift($messageMapperConfigParts);
$messageMapperConfig = [
    'class' => $messageMapperClassName?$messageMapperClassName:MessageMapper::class,
    'args' => array_values($messageMapperConfigParts)
];

return [
    'channel' => $channel,
    'connectionConfig' => $connectionConfig,
    'filter' => $eventFilterConfig,
    'mapper' => $messageMapperConfig
];