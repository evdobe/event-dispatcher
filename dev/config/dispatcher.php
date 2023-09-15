<?php declare(strict_types=1);

use Application\Event\Filter;
use Application\Messaging\MessageMapper;

$connectionConfig = [
    'global' => [
        'metadata.broker.list' => getenv('MESSAGE_BROKER_HOST').':'.getenv('MESSAGE_BROKER_PORT'),
    ]
];

if (getenv('MESSAGE_BROKER_SECURITY_PROTOCOL') === 'SASL_SSL'){
    $connectionConfig['global']['security.protocol'] = 'SASL_SSL';
    $connectionConfig['global']['sasl.mechanisms'] = getenv('MESSAGE_BROKER_SASL_MECHANISMS')?:'PLAIN';
    $connectionConfig['global']['sasl.username'] = getenv('MESSAGE_BROKER_SASL_USERNAME')?:'$ConnectionString';
    $connectionConfig['global']['sasl.password'] = getenv('MESSAGE_BROKER_SASL_PASSWORD');
}

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