<?php
namespace Imperium\JSON;
use \Imperium\Exception as E;

class Value
{
    protected $value = null;
    
    public function __construct($value) 
    {
        $this->set($value);
    }
    
    public function get()
    {
        return $this->value;
    }
    
    public function set($value)
    {
        if (in_array(gettype($value), array('array', 'object'))) {
            throw new E\InvalidInputType(__METHOD__.': value must not be an array or object');
        }
        $this->value = $value;
    }
    
}