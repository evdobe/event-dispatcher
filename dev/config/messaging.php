<?php declare(strict_types=1);

$connectionConfig = [
    'global' => [
        'metadata.broker.list' => getenv('MESSAGE_BROKER_HOST').':'.getenv('MESSAGE_BROKER_PORT'),
    ]
];

$channels = getenv('EVENT_CHANNELS');

$channelsConfig = array_filter(array_map(function(string $row){
    return trim($row);
},explode("\n", $channels)));

$channelsConfig = array_reduce($channelsConfig, function(array $carry, string $item){
    $parts = array_map(function(string $row){
        return trim($row);
    },explode(":", $item));

    $classConfig = function(string $configStr){
        $parts = explode("|", $configStr);
        $className = trim(array_shift($parts));
        $argumentArray = array_map(function(string $arg){
            return trim($arg);
        },$parts);
        return $className?[
            'class' => $className,
            'arg' => $argumentArray
        ]:null;
    };
    
    $carry[$parts[0]] = [
        'filter' => count($parts) >1?$classConfig($parts[1]):null,
        'builder' => count($parts) >2?$classConfig($parts[2]):null,
    ];
    return $carry;
},[]);

return [
    'connection' => $connectionConfig,
    'channels' => $channelsConfig
];