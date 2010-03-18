<?php
namespace Imperium\JSON;

use Imperium as I;

class SchemaTest extends \PHPUnit_Framework_TestCase
{
    public function testValidateBasic()
    {
        $schema = '{"description":"An object","type":"object"}';
        $json0 = '{}';
        $json1 = '[]';
        $exp0  = array('valid'=>true,'errors'=>array());
        $exp1  = array('valid'=>false, 'errors'=>array(array('property'=>'','message'=>"Must be an object, found: array")));
        $exp2  = array('valid'=>false, 'errors'=>array(array('property'=>'','message'=>"No provided schema")));
        
        $j0d = I\JSON::decode($json0);
        $j1d = I\JSON::decode($json1);
        $sd = I\JSON::decode($schema);
        
        $return = Schema::validate($j0d, $sd);
        $this->assertEquals($exp0, $return);
        
        $return = Schema::validate($j1d, $sd);
        $this->assertEquals($exp1, $return);
        
        $return = Schema::validate($j0d, '');
        $this->assertEquals($exp2, $return);
    }
    
    public function testValidateNumber()
    {
        $schema = '{"description":"An Object with numbers",'.
                   '"type":"object",'.
                   '"properties":{'.
                       '"somenumber":{"type":"number"},'.
                       '"anothernumber":{"type":"number"},'.
                       '"thirdnumber":{"type":"number"}'.
                       '}'.
                   '}';
        $json = '{"somenumber":50.33,"anothernumber":999999999999784992992882771739,"thirdnumber":"a number"}';
        $expected = array('valid'=>false,'errors'=>
            array(array('property'=>'.thirdnumber','message'=>'Must be a number, found: string')));
        
        $jd = I\JSON::decode($json);
        $sd = I\JSON::decode($schema);
        
        $return = Schema::validate($jd, $sd);
        $this->assertEquals($expected, $return);
    }
    
    public function testValidateInteger()
    {
        $schema = '{"description":"Important integers",'.
                   '"type":"object",'.
                   '"properties":{'.
                       '"id":{"type":"integer"},'.
                       '"index":{"type":"integer"},'.
                       '"frank":{"type":"integer"}'.
                       '}'.
                   '}';
        $json = '{"id":50,"index":9223372036854775807,"frank":"frank"}';
        $expected = array('valid'=>false,'errors'=>
            array(array('property'=>'.frank','message'=>'Must be an integer, found: string')));
        
        $jd = I\JSON::decode($json);
        $sd = I\JSON::decode($schema);
        
        $return = Schema::validate($jd, $sd);
        $this->assertEquals($expected, $return);
    }
    
    public function testValidateString()
    {
        $schema = '{"description":"Important string",'.
                   '"type":"object",'.
                   '"properties":{'.
                       '"index":{"type":"string"}'.
                       '}'.
                   '}';
        $jr0 = '{"index":"this is a string"}';
        $ex0 = array('valid'=>true,'errors'=>array());
        
        $jr1 = '{"index":[]}';
        $ex1 = array('valid'=>false,'errors'=>array(array('property'=>'.index','message'=>'Must be a string, found: array')));
        
        $jd0 = I\JSON::decode($jr0);
        $jd1 = I\JSON::decode($jr1);
        $sd = I\JSON::decode($schema);
        
        $return = Schema::validate($jd0, $sd);
        $this->assertEquals($ex0, $return);
        
        $return = Schema::validate($jd1, $sd);
        $this->assertEquals($ex1, $return);
    }
    
    public function testValidateBoolean()
    {
        $schema = '{"description":"A bool",'.
                   '"type":"object",'.
                   '"properties":{'.
                       '"work":{"type":"boolean"},'.
                       '"sick":{"type":"boolean"},'.
                       '"judy":{"type":"boolean"}'.
                       '}'.
                   '}';
        $json = '{"work":false,"sick":true,"judy":"judy"}';
        $expected = array('valid'=>false,'errors'=>
            array(array('property'=>'.judy','message'=>'Must be a boolean, found: string')));
        
        $jd = I\JSON::decode($json);
        $sd = I\JSON::decode($schema);
        
        $return = Schema::validate($jd, $sd);
        $this->assertEquals($expected, $return);
    }
    
    public function testValidateNull()
    {
        $schema = '{"description":"A Null",'.
                   '"type":"object",'.
                   '"properties":{'.
                       '"work":{"type":"null"},'.
                       '"sick":{"type":"null"}'.
                       '}'.
                   '}';
        $json = '{"work":null,"sick":"null"}';
        $expected = array('valid'=>false,'errors'=>
            array(array('property'=>'.sick','message'=>'Must be a null, found: string')));
        
        $jd = I\JSON::decode($json);
        $sd = I\JSON::decode($schema);
        
        $this->assertEquals(
            new Object(array('work'=>null,'sick'=>'null')),
            $jd
        );
        
        $return = Schema::validate($jd, $sd);
        $this->assertEquals($expected, $return);
    }

    public function testValidateArray()
    {
        $schema = '{"description":"Array",'.
                   '"type":"object",'.
                   '"properties":{'.
                       '"work":{"type":"array"},'.
                       '"sick":{"type":"array"}'.
                       '}'.
                   '}';
        $json = '{"work":["0","1"],"sick":"array"}';
        $expected = array('valid'=>false,'errors'=>
            array(array('property'=>'.sick','message'=>'Must be an array, found: string')));
        
        $jd = I\JSON::decode($json);
        $sd = I\JSON::decode($schema);
        
        $return = Schema::validate($jd, $sd);
        $this->assertEquals($expected, $return);
    }

    
//TODO: type=string; pattern, maxLen,minLen
//TODO: type=number; min(mineq),max(maxeq),maxdec
//TODO: type=integer; min(mineq),max(maxeq)
//TODO: type=array min&max
//TODO: type=any
//TODO: type=ArrayObject
//TODO: type=Object
//TODO: type=????
//TODO: disallow
//TODO: optional
    
    
    /*
    public function testValidateBasic()
    {
        $schema = '{"description":"A geographical coordinate",'.
                   '"type":"object",'.
                   '"properties":{'.
                       '"latitude":{"type":"number"},'.
                       '"longitude":{"type":"number"},'.
                       '"index":{"type":"integer"}'.
                       '}'.
                   '}';
        $json = '{"latitude":50.33,"longitude":11}';
        $expected = array('valid'=>true,'errors'=>array());
        
        $jd = I\JSON::decode($json);
        $sd = I\JSON::decode($schema);
        
        $return = Schema::validate($jd, $sd);
        $this->assertEquals($expected, $return);
    }
    */
}