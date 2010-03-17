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
        $this->assertEquals(JSON::encode(JSON::decode($string)), $string);
    }
}