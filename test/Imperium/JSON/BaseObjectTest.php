<?php

namespace Imperium\JSON;


class BaseObjectTest extends \PHPUnit_Framework_TestCase
{
    public function testOffsetGet()
    {
        $bo = new BaseObject(
            array(
                'bob'  => 'bobert',
                'kath' => 'kate',
            )
        );
        $this->assertEquals($bo['bob'], 'bobert');
        $this->assertTrue($bo['frank'] instanceof Undefined);
    }
}

