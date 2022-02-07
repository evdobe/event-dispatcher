<?php declare(strict_types=1);

use Application\Event\Filter;
use Application\Messaging\MessageBuilder;

$connectionConfig = [
    'global' => [
        'metadata.broker.list' => getenv('MESSAGE_BROKER_HOST').':'.getenv('MESSAGE_BROKER_PORT'),
    ]
];

$channel = getenv('EVENT_CHANNEL');

$eventFilterStr = getenv('EVENT_FILTER');
$eventFilterConfigParts = array_map(function(string $row){
    return trim($row);
},explode('|', $eventFilterStr));

$eventFilterClassName = array_shift($eventFilterConfigParts);
$eventFilterConfig = [
    'filter' => $eventFilterClassName?$eventFilterClassName:Filter::class,
    'args' => array_values($eventFilterConfigParts)
];

$messageBuilderStr = getenv('MeSSAGE_BUILDER');
$messageBuilderConfigParts = array_map(function(string $row){
    return trim($row);
},explode('|', $messageBuilderStr));

$messageBuilderClassName = array_shift($messageBuilderConfigParts);
$messageBuilderConfig = [
    'builder' => $messageBuilderClassName?$messageBuilderClassName:MessageBuilder::class,
    'args' => array_values($messageBuilderConfigParts)
];

return [
    'eventFilter' => $eventFilterConfig,
    'messageBuilder' => $messageBuilderConfig
];