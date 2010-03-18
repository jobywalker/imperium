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
                'joby' => null,
            )
        );
        $this->assertEquals('bobert', $bo['bob']);
        $this->assertEquals(null, $bo['joby']);
        $this->assertTrue($bo['frank'] instanceof Undefined);
    }
}

