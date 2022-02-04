<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class MessagingConfigTest extends TestCase
{
    public function testReturnedChannelsArrayShouldContainChannelNamesAsKeys(){
        putenv('EVENT_CHANNELS=
            channel1:\Application\Messaging\Impl\ExampleFilter1:\Application\Messaging\Impl\ExampleTranslator1
            channel2:\Application\Messaging\Impl\ExampleFilter2:\Application\Messaging\Impl\ExampleTranslator2
        ');

        $config=include('config/messaging.php');

        $this->assertArrayHasKey('channel1', $config['channels']);
        $this->assertArrayHasKey('channel2', $config['channels']);
    }

    public function testReturnedChannelsArrayShouldContainSecondPartAsFilterIfExists(){
        putenv('EVENT_CHANNELS=
            channel1:\Application\Messaging\Impl\ExampleFilter1:\Application\Messaging\Impl\ExampleTranslator1
            channel2
            channel3::\Application\Messaging\Impl\ExampleTranslator2
        ');

        $config=include('config/messaging.php');

        $this->assertEquals('\Application\Messaging\Impl\ExampleFilter1', $config['channels']['channel1']['filter']['class']);
        $this->assertEmpty($config['channels']['channel2']['filter']);
        $this->assertEmpty($config['channels']['channel3']['filter']);
    }

    public function testReturnedChannelsArrayShouldContainThirdPartAsTranslatorIfExists(){
        putenv('EVENT_CHANNELS=
            channel1:\Application\Messaging\Impl\ExampleFilter1:\Application\Messaging\Impl\ExampleTranslator1
            channel2
            channel3::\Application\Messaging\Impl\ExampleTranslator2
        ');

        $config=include('config/messaging.php');

        $this->assertEquals('\Application\Messaging\Impl\ExampleTranslator1', $config['channels']['channel1']['translator']['class']);
        $this->assertEmpty($config['channels']['channel2']['translator']);
        $this->assertEquals('\Application\Messaging\Impl\ExampleTranslator2', $config['channels']['channel3']['translator']['class']);
    }
}
