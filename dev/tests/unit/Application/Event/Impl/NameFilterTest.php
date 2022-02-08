<?php

namespace Application\Event\Impl;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class NameFilterTest extends TestCase
{
    public function testShouldMatchEventsWithMatchingName(){
        $sut = new NameFilter(['matching1', 'matching2']);

        $this->assertTrue($sut->matches([
            'name' => 'matching1'
        ]));
    }

    public function testShouldNotMatchEventsWithNotMatchingName(){
        $sut = new NameFilter(['matching1', 'matching2']);

        $this->assertFalse($sut->matches([
            'name' => 'notmatching'
        ]));
    }

    public function testShouldProvideAnSqlMatcher(){
        $sut = new NameFilter(['matching1', 'matching2']);

        $this->assertEquals('NEW.name IN (\'matching1\',\'matching2\')', $sut->getSqlMatcher());
    }
}
