<?php
namespace Imperium\JSON;

use Imperium as I;

class SchemaTest extends \PHPUnit_Framework_TestCase
{
    public function testValidateBasic()
    {
        $schema = '{"description":"An object","type":"object"}';
        $ischema = '{"description":"An object","type":"bob"}';
        $ischema2 = '[]';
        $json0 = '{}';
        $json1 = '[]';
        $exp0  = array('valid'=>true,'errors'=>array());
        $exp1  = array('valid'=>false, 'errors'=>array(array('property'=>'','message'=>"Must be an object, found: array")));
        $exp2  = array('valid'=>false, 'errors'=>array(array('property'=>'','message'=>"No provided schema")));
        $exp3  = array('valid'=>false, 'errors'=>array(array('property'=>'','message'=>"Unknown type in schema: bob")));
        $exp4  = array('valid'=>false, 'errors'=>array(array('property'=>'','message'=>"Invalid schema/property definition.")));
        
        $j0d = I\JSON::decode($json0);
        $j1d = I\JSON::decode($json1);
        $sd = I\JSON::decode($schema);
        $id = I\JSON::decode($ischema);
        $id2 = I\JSON::decode($ischema2);
        
        $return = Schema::validate($j0d, $sd);
        $this->assertEquals($exp0, $return);
        
        $return = Schema::validate($j1d, $sd);
        $this->assertEquals($exp1, $return);
        
        $return = Schema::validate($j0d, '');
        $this->assertEquals($exp2, $return);
        
        $return = Schema::validate($j0d, $id);
        $this->assertEquals($exp3, $return);
        
        $return = Schema::validate($j0d, $id2);
        $this->assertEquals($exp4, $return);
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

    public function testValidateAny()
    {
        $schema = '{"description":"Things",'.
                   '"type":"object",'.
                   '"properties":{'.
                       '"first":{"type":"any"},'.
                       '"second":{"type":"any"},'.
                       '"third":{"type":"any"}'.
                       '}'.
                   '}';
        $json = '{"first":3.33,"second":[2,"2"],"third":null}';
        $expected = array('valid'=>true,'errors'=>array());
        
        $jd = I\JSON::decode($json);
        $sd = I\JSON::decode($schema);
        
        $return = Schema::validate($jd, $sd);
        $this->assertEquals($expected, $return);
    }
    
    public function testValidatePattern()
    {
        $schema = '{"description":"Important strings",'.
                   '"type":"object",'.
                   '"properties":{'.
                       '"index":{"type":"string","pattern":"^[a-z0-9]+$"},'.
                       '"tag":{"type":"string","pattern":"^[a-z0-9_-]+$"}'.
                       '}'.
                   '}';
        $json = '{"index":"A55","tag":"a-55"}';
        $expected = array('valid'=>false,'errors'=>
            array(array('property'=>'.index','message'=>'Must match pattern: /^[a-z0-9]+$/')));
        
        $jd = I\JSON::decode($json);
        $sd = I\JSON::decode($schema, 2);
        
        $return = Schema::validate($jd, $sd);
        $this->assertEquals($expected, $return);
    }
    
    public function testValidateMinMaxLength()
    {
        $schema = '{"description":"Important strings",'.
                   '"type":"object",'.
                   '"properties":{'.
                       '"index":{"type":"string","minLength":64},'.
                       '"short":{"type":"string","maxLength":10},'.
                       '"tag":{"type":"string","minLength":3,"maxLength":10}'.
                       '}'.
                   '}';
        $json = '{"index":"A55","short":"This is a very long string","tag":"a-55"}';
        $expected = array('valid'=>false,'errors'=>array(
            array('property'=>'.index','message'=>'Must not have length under 64'),
            array('property'=>'.short','message'=>'Must not exceed length of 10'),
        ));
        
        $jd = I\JSON::decode($json);
        $sd = I\JSON::decode($schema, 2);
        
        $return = Schema::validate($jd, $sd);
        $this->assertEquals($expected, $return);
    }
    
    public function testValidateMinMaxInteger()
    {
        $schema = '{"description":"Important integers",'.
                   '"type":"object",'.
                   '"properties":{'.
                       '"min0":{"type":"integer","minimum":5},'.
                       '"min1":{"type":"integer","minimum":5},'.
                       '"min2":{"type":"integer","minimum":5,"minimumCanEqual":true},'.
                       '"min3":{"type":"integer","minimum":5,"minimumCanEqual":false},'.
                       '"max0":{"type":"integer","maximum":5},'.
                       '"max1":{"type":"integer","maximum":5},'.
                       '"max2":{"type":"integer","maximum":5,"maximumCanEqual":true},'.
                       '"max3":{"type":"integer","maximum":5,"maximumCanEqual":false}'.
                       '}'.
                   '}';
        $json = '{"min0":5,"min1":4,"min2":5,"min3":5,"max0":5,"max1":6,"max2":5,"max3":5}';
        $expected = array('valid'=>false,'errors'=>array(
            array('property'=>'.min1','message'=>'Must be greater than or equal to: 5'),
            array('property'=>'.min3','message'=>'Must be greater than: 5'),
            array('property'=>'.max1','message'=>'Must be less than or equal to: 5'),
            array('property'=>'.max3','message'=>'Must be less than: 5'),
        ));
        
        $jd = I\JSON::decode($json);
        $sd = I\JSON::decode($schema);
        
        $return = Schema::validate($jd, $sd);
        $this->assertEquals($expected, $return);
    }    
    public function testValidateMinMaxItems()
    {
        $schema = '{"description":"Array",'.
                   '"type":"object",'.
                   '"properties":{'.
                       '"work":{"type":"array","minItems":1,"maxItems":3},'.
                       '"sick":{"type":"array","minItems":1,"maxItems":3},'.
                       '"vaca":{"type":"array","minItems":1,"maxItems":3},'.
                       '"holi":{"type":"array","minItems":1,"maxItems":3}'.
                       '}'.
                   '}';
        $json = '{"work":["0"],"sick":["0","1","3"],"vaca":["0","1","3","4"],"holi":[]}';
        $expected = array('valid'=>false,'errors'=>array(
            array('property'=>'.vaca','message'=>'Must have no more than 3 elements'),
            array('property'=>'.holi','message'=>'Must have at least 1 elements'),
         ));
        
        $jd = I\JSON::decode($json);
        $sd = I\JSON::decode($schema, 2);
        
        $return = Schema::validate($jd, $sd);
        $this->assertEquals($expected, $return);
    }

    public function testValidateDisallow()
    {
        $schema = '{"description":"Important integers",'.
                   '"type":"object",'.
                   '"properties":{'.
                       '"id":{"type":"any","disallow":"string"},'.
#                       '"index":{"type":"any"},'.
                       '"frank":{"type":"any","disallow":"string"}'.
                       '}'.
                   '}';
        $json = '{"id":50,"frank":"frank"}';
        $expected = array('valid'=>false,'errors'=>array(
            array('property'=>'.frank','message'=>'Type must not match disallow')
        ));
        
        $jd = I\JSON::decode($json);
        $sd = I\JSON::decode($schema);
        
        $return = Schema::validate($jd, $sd);
        $this->assertEquals($expected, $return);
    }
//TODO: type=number; min(mineq),max(maxeq),maxdec
    public function testValidateOptional()
    {
        $schema = '{"description":"Important strings",'.
                   '"type":"object",'.
                   '"properties":{'.
                       '"index":{"type":"string"},'.
                       '"short":{"type":"string","optional":false},'.
                       '"tag":{"type":"string","optional":true}'.
                       '}'.
                   '}';
        $json = '{}';
        $expected = array('valid'=>false,'errors'=>array(
            array('property'=>'.index','message'=>'Property is required'),
            array('property'=>'.short','message'=>'Property is required'),
        ));
        
        $jd = I\JSON::decode($json);
        $sd = I\JSON::decode($schema, 2);
        
        $return = Schema::validate($jd, $sd);
        $this->assertEquals($expected, $return);
    }

    public function testValidateTypes()
    {
        $schema = '{'.
                   '"type":"object",'.
                   '"properties":{'.
                       '"tomas":{"type":["string","integer"]},'.
                       '"index":{"type":["string","integer"]},'.
                       '"frank":{"type":["string","integer"]}'.
                       '}'.
                   '}';
        $json = '{"tomas":50,"index":"index","frank":44.44}';
        $expected = array('valid'=>false,'errors'=>
            array(array('property'=>'.frank','message'=>'Must be one of optional choices.'),
        ));
        
        $jd = I\JSON::decode($json);
        $sd = I\JSON::decode($schema);
        
        $return = Schema::validate($jd, $sd);
        $this->assertEquals($expected, $return);
    }

    public function testValidateMinMaxNumber()
    {
        $schema = '{"description":"Important numbers",'.
                   '"type":"object",'.
                   '"properties":{'.
                       '"min0":{"type":"number","minimum":5.5},'.
                       '"min1":{"type":"number","minimum":5.5},'.
                       '"min2":{"type":"number","minimum":5.5,"minimumCanEqual":true},'.
                       '"min3":{"type":"number","minimum":5.5,"minimumCanEqual":false},'.
                       '"max0":{"type":"number","maximum":5.5},'.
                       '"max1":{"type":"number","maximum":5.5},'.
                       '"max2":{"type":"number","maximum":5.5,"maximumCanEqual":true},'.
                       '"max3":{"type":"number","maximum":5.5,"maximumCanEqual":false}'.
                       '}'.
                   '}';
        $json = '{"min0":5.5,"min1":4.1,"min2":5.5,"min3":5.5,"max0":5.5,"max1":6.1,"max2":5.5,"max3":5.5}';
        $expected = array('valid'=>false,'errors'=>array(
            array('property'=>'.min1','message'=>'Must be greater than or equal to: 5.5'),
            array('property'=>'.min3','message'=>'Must be greater than: 5.5'),
            array('property'=>'.max1','message'=>'Must be less than or equal to: 5.5'),
            array('property'=>'.max3','message'=>'Must be less than: 5.5'),
        ));
        
        $jd = I\JSON::decode($json);
        $sd = I\JSON::decode($schema);
        
        $return = Schema::validate($jd, $sd);
        $this->assertEquals($expected, $return);
    }  

    public function testValidateMaxDecNumber()
    {
        $schema = '{"description":"An Object with numbers",'.
                   '"type":"object",'.
                   '"properties":{'.
                       '"number0":{"type":"number","maxDecimal":4},'.
                       '"number1":{"type":"number","maxDecimal":4}'.
                       '}'.
                   '}';
        $json = '{"number0":50.3333,"number1":50.33333}';
        $expected = array('valid'=>false,'errors'=>
            array(array('property'=>'.number1','message'=>'Must not exceed 4 decimal places')));
        
        $jd = I\JSON::decode($json);
        $sd = I\JSON::decode($schema);
        
        $return = Schema::validate($jd, $sd);
        $this->assertEquals($expected, $return);
    }  
    

//TODO: type=Object

}