<?php
namespace Imperium\JSON;

class ValueTest extends \PHPUnit_Framework_TestCase
{
    public function testMakeValue()
    {
        $vs = array(null, true, 5, 685.88, "success");
        foreach ($vs as $v) {
            $n = new Value($v);
            $this->assertEquals('Imperium\JSON\Value', get_class($n));
            $this->assertEquals($v, $n->get());
        }
    }
    
    public function testMakeValueFail()
    {
        $vs = array(array(), new \StdClass());
        foreach ($vs as $v)
        {
            try {
                $n = new Value($v);
            } catch (\Imperium\Exception\InvalidInputType $e) {
                $this->assertTrue(true);
            }
        }
    }
}