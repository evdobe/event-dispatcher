<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class DispatcherConfigTest extends TestCase
{
    public function testShouldReturnEventChannelOnChannelKey(){
        putenv('EVENT_CHANNEL=channel1');

        $config=include('config/dispatcher.php');

        $this->assertEquals('channel1', $config['channel']);
    }

    public function testReturnedArrayShouldContainFilterConfigIfExists(){
        putenv('EVENT_FILTER=aFilterClassName|arg1|arg2');

        $config=include('config/dispatcher.php');

        $this->assertEquals('aFilterClassName', $config['filter']['class']);
        $this->assertEquals(['arg1', 'arg2'], $config['filter']['args']);
    }

    public function testReturnedArrayShouldContainMapperConfig(){
        putenv('MESSAGE_MAPPER=aMapperClassName|arg1|arg2');

        $config=include('config/dispatcher.php');

        $this->assertEquals('aMapperClassName', $config['mapper']['class']);
        $this->assertEquals(['arg1', 'arg2'], $config['mapper']['args']);
    }

    public function testReturnedFilterConfigShouldBeNullIfEventFilterIsNotSet(){
        putenv('EVENT_FILTER=');

        $config=include('config/dispatcher.php');

        $this->assertNull($config['filter']);
    }

    public function testReturnedFilterClassShouldDefaultToNameFilterWhenOnlyArgsAreSet(){
        putenv('EVENT_FILTER=|arg1|arg2');

        $config=include('config/dispatcher.php');

        $this->assertEquals('Application\Event\Filter', $config['filter']['class']);
        $this->assertEquals(['arg1', 'arg2'], $config['filter']['args']);
    }

    public function testReturnedMapperClassShouldDefaultToMessagingMessageMapperWhenMapperNotSet(){
        putenv('MESSAGE_MAPPER=');

        $config=include('config/dispatcher.php');

        $this->assertEquals('Application\Messaging\MessageMapper', $config['mapper']['class']);
        $this->assertEquals([], $config['mapper']['args']);
    }

    public function testReturnedMapperClassShouldDefaultToMessagingMessageMapperWhenOnlyMapperArgsAreSet(){
        putenv('MESSAGE_MAPPER=|arg1|arg2');

        $config=include('config/dispatcher.php');

        $this->assertEquals('Application\Messaging\MessageMapper', $config['mapper']['class']);
        $this->assertEquals(['arg1', 'arg2'], $config['mapper']['args']);
    }
}
