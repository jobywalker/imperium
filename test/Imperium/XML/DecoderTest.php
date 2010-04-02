<?php

namespace Imperium\XML;

use Imperium\Exception as E;

class DecoderTest extends \PHPUnit_Framework_TestCase
{
    public function testDecode()
    {
        $xml = "<?xml version=\"1.0\" ?>\n".
        '<a><b bob="&<>"><![CDATA[c<"&>]]></b><d/><e>44.44</e><f></f><zs><z>1</z><z>true</z><z>false</z></zs></a>';
        $exp = (object) array(
            'a'=>(object)array(
                'b'=>'c<"&>',
                'd'=>null,
                'e'=>44.44,
                'f'=>'',
                'zs'=> (object) array('z'=>array(
                    1,true,false
                )),
            ),
        );
        $this->assertEquals(
            $exp,
            Decoder::decode($xml)
        );
    }

    public function testArrayOutput()
    {
        $xml = "<?xml version=\"1.0\" ?>\n".
        '<a><b bob="&<>"><![CDATA[c<"&>]]></b><d/><e>44.44</e><f></f><zs><z>1</z><z>true</z><z>false</z></zs></a>';
        $exp = array(
            'a'=> array(
                'b'=>'c<"&>',
                'd'=>null,
                'e'=>44.44,
                'f'=>'',
                'zs'=> array('z'=>array(
                    1,true,false
                )),
            ),
        );
        $this->assertEquals(
            $exp,
            Decoder::decode($xml, array('ArrayOutput' => true))
        );
    }

    public function testStripContainer()
    {
        $xml = "<?xml version=\"1.0\" ?>\n".
        '<a><b bob="&<>"><![CDATA[c<"&>]]></b><d/><e>44.44</e><f></f><zs><z>1</z><z>true</z><z>false</z></zs></a>';
        $exp = (object)array(
                'b'=>'c<"&>',
                'd'=>null,
                'e'=>44.44,
                'f'=>'',
                'zs'=> (object) array('z'=>array(
                    1,true,false
                )),
        );
        $this->assertEquals(
            $exp,
            Decoder::decode($xml, array('StripContainer'=>true))
        );
        $this->assertEquals(
            array(1,2),
            Decoder::decode('<b>1</b><b>2</b>', array('StripContainer'=>true))
        );
    }

    public function testFail()
    {
        $xml = '<a><b></a>';
        try {
            $this->assertFalse(Decoder::decode($xml));
        } catch (E\InvalidInputValue $e) {
            $this->assertEquals("No end node found for a", $e->getMessage());
        }
        $xml = '<a><b></b></c></a>';
        try {
            $this->assertFalse(Decoder::decode($xml));
        } catch (E\InvalidInputValue $e) {
            $this->assertEquals('Unexpected end node', $e->getMessage());
        }
    }
}