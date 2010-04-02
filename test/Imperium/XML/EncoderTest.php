<?php
namespace Imperium\XML;

class EncoderTest extends \PHPUnit_Framework_TestCase
{
    
    public function testDeclaration()
    {
        $this->assertEquals(
            '<?xml version="1.0"?>'."\n",
            Encoder::declaration(array('declare'=>true))
        );
        $this->assertEquals(
            '<?xml version="1.0" encoding="fake-enc"?>'."\n",
            Encoder::declaration(array('declare'=>true,'encoding'=>'fake-enc'))
        );
        $this->assertEquals(
            '',
            Encoder::declaration(array('declare'=>false))
        );
    }
    
    public function testGetType()
    {
        $types = array(
            'string'    => "this is a \n string",
            'string'    => '55.55',
            'number'    => 5,
            'number'    => 55.55,
            'boolean'   => true,
            'boolean'   => false,
            'null'      => null,
            'object'    => new Encoder(),
            'object'    => (object) array(1,2,3),
            'object'    => array('s' => 4, 'g' => 'x'),
            'array'     => array(1,2,3),
            'array'     => new \Imperium\JSON\ArrayObject(array()),
            'undefined' => new \Imperium\JSON\Undefined(),
        );
        foreach ($types as $expected => $test) {
            $this->assertEquals(
                $expected,
                Encoder::getType($test)
            );
        }
    }
    
    public function testNormalizeNode()
    {
        $nodes = array(
            'Node' => 'Node',
            'x5_5' => 'x5_5',
            'Bob'  => '#Bob',
            'Judy' => '12Judy',
            'Zip'  => '283992(*&(@*@&-Z&^i--##p',
        );
        foreach ($nodes as $expected => $test) {
            $this->assertEquals(
                $expected,
                Encoder::normalizeNode($test)
            );
        }
        try {
            $x = Encoder::normalizeNode('###');
            $this->assertEquals('Should have an exception', $x);
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Imperium\Exception\InvalidInputValue);
            $this->assertEquals(
                'XML node names must not be blank',
                $e->getMessage()
            );
        }
    }
    
    public function testOffset()
    {
        $offsets = array(
            '' => null,
            '' => array('offset'=>' ', 'depth'=>0),
            "\n " => array('offset'=>' ', 'depth'=>1),
            "\n x x" => array('offset'=>' x', 'depth'=>2)
        );
        foreach ($offsets as $expected => $test) {
            $this->assertEquals(
                $expected,
                Encoder::offset($test)
            );
        }
    }
    
    public function testEncodeNull()
    {
        $this->assertEquals(
            '<Null/>',
            Encoder::encodeNull(null, 'Null')
        );
    }
    
    public function testEncodeBoolean()
    {
        $this->assertEquals(
            "<root>true</root>",
            Encoder::encodeBoolean(true)
        );
        $this->assertEquals(
            "\n    <root>true</root>",
            Encoder::encodeBoolean(true, 'root', array('offset'=>' ', 'depth'=>4))
        );
        $this->assertEquals(
            "<?xml version=\"1.0\"?>\n<Bob>false</Bob>",
            Encoder::encodeBoolean(false, '3Bob', array('declare'=>'true'))
        );
    }
    
    public function testEncodeNumber()
    {
        $this->assertEquals(
            '<SweetNumber>3.14</SweetNumber>',
            Encoder::encodeNumber(3.14, 'Sweet-Number')
        );
    }
    
    public function testEncodeString()
    {
        $this->assertEquals(
            '<Stringy>This &quot;string&quot; is &lt;bold&gt; &amp; rock\'n.</Stringy>',
            Encoder::encodeString('This "string" is <bold> & rock\'n.', 'Stringy')
        );
    }
    
    public function testEncodeArray()
    {
        $this->assertEquals(
            '<Int>1</Int><Int>2</Int><Int>3</Int>',
            Encoder::encodeArray(array(1,2,array(3)), 'Int')
        );
    }
    
    public function testEncodeObject()
    {
        $this->assertEquals(
            '<Obj><s>a</s><i>1</i></Obj>',
            Encoder::encodeObject((object)array('s'=>'a','i'=>1), 'Obj')
        );
    }
    
    public function testFull()
    {
        $expected = "<?xml version=\"1.0\"?>\n".
                    "<User>\n".
                    "    <Name>Joby Walker</Name>\n".
                    "    <HasSkills>false</HasSkills>\n".
                    "    <Locations>\n".
                    "        <Location>\n".
                    "            <Type>work</Type>\n".
                    "            <City>Seattle</City>\n".
                    "        </Location>\n".
                    "        <Location>\n".
                    "            <Type>home</Type>\n".
                    "            <City>Seattle</City>\n".
                    "            <Cats>2</Cats>\n".
                    "            <Dogs/>\n".
                    "        </Location>\n".
                    "    </Locations>\n".
                    "</User>";
        $test = (object) array(
            'Name' => 'Joby Walker',
            'HasSkills' => false,
            'Talent' => new \Imperium\JSON\Undefined(),
            'Locations' => (object) array(
                'Location' => array(
                    (object) array('Type'=>'work','City'=>'Seattle'),
                    (object) array('Type'=>'home','City'=>'Seattle','Cats'=>2,'Dogs'=>null),
                ),
            ),
        );
        $this->assertEquals($expected, Encoder::encode($test, 'User', array('declare'=>true,'offset'=>'    ')));
    }
    
    
}