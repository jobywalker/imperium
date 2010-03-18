<?php
namespace Imperium;
use \Imperium\JSON as J;

class JSONTest extends \PHPUnit_Framework_TestCase
{
    public function testDecode()
    {
        $this->assertEquals(
            JSON::decode('{"User":{"Name":"Bob","Tags":["lazy","scruffy"]}}'),
            new J\Object(
                array(
                    "User"=> new J\Object(
                        array(
                            "Name" => "Bob",
                            "Tags" => new J\ArrayObject(
                                array("lazy","scruffy")
                            )
                        )
                    )
                )
            )
        );
    }
    
    public function testEncode()
    {
        $this->assertEquals(
            JSON::encode(
                new J\Object(
                    array(
                        "User"=> new J\Object(
                            array(
                                "Name" => "Bob",
                                "Tags" => new J\ArrayObject(
                                    array("lazy","scruffy")
                                )
                            )
                        )
                    )
                )
            ),
            '{"User":{"Name":"Bob","Tags":["lazy","scruffy"]}}'
        );
    }
    
    public function testFull()
    {
        $string = '{"User":{"Name":"Bob","Tags":["lazy","scruffy"]}}';
        
        //test that decoding and re-encoding returns the original content
        $this->assertEquals(JSON::encode(JSON::decode($string)), $string);
        
        //test that the zend and pecl/json decoders return the same result
        $this->assertEquals(
            JSON::decode($string, JSON::CODER_JSON),
            JSON::decode($string, JSON::CODER_ZEND)
        );
        
        //test that the zend and pecl/json encoders return the same result
        $string = JSON::decode($string);
        $this->assertEquals(
            JSON::encode($string, JSON::CODER_JSON),
            JSON::encode($string, JSON::CODER_ZEND)
        );
        
    }
}