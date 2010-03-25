<?php
namespace Imperium;

class XMLTest extends \PHPUnit_Framework_TestCase
{
    public function testEncode()
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
        $this->assertEquals($expected, XML::encode($test, 'User', array('declare'=>true,'offset'=>'    ')));
    }
}