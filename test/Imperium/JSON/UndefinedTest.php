<?php

namespace Imperium\JSON;


class UndefinedTest extends \PHPUnit_Framework_TestCase
{
    public function testToString()
    {
        $u = new Undefined();
        $this->assertEquals((string)$u, '');
    }
}
