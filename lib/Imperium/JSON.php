<?php

namespace Imperium;
use Imperium\JSON as J;

/**
 * Helper JSON methods
 * 
 * #requires json PECL module or Zend_Json
 * 
 * @author jobywalker
 * 
 */
class JSON
{
    const CODER_JSON = 1;
    const CODER_ZEND = 2;
    
    /**
     * Decode JSON encoded string into PHP type
     * 
     * This method uses json_decode if it exists, otherwise Zend_Json::decode.
     * 
     * @param string $json JSON encoded string
     * @param integer $decoder Which decoder to use, default is autodetect.
     * @return mixed Decoded value as appropriate PHP type
     */
    public static function decode($json, $decoder=null)
    {
        if (!in_array($decoder, array(self::CODER_JSON, self::CODER_ZEND))) {
            $decoder = function_exists('json_decode') ? self::CODER_JSON : self::CODER_ZEND;
        }
        if ($decoder == self::CODER_JSON) {
            $value = json_decode($json, false);
        } elseif ($decoder == self::CODER_ZEND) {
            $value = \Zend_Json::decode($json, \Zend_Json::TYPE_OBJECT);
        }
        return self::rebuild($value);
    }
    
    
    /**
     * Encode PHP type into JSON encoded string
     * 
     * @param mixed $value PHP type
     * @param integer $encoder Which encoder to use, default is autodetect
     * @return string JSON encoded string
     */
    public static function encode($value, $encoder=null)
    {
        if (!in_array($encoder, array(self::CODER_JSON, self::CODER_ZEND))) {
            $encoder = function_exists('json_encode') ? self::CODER_JSON : self::CODER_ZEND;
        }
        $value = self::debuild($value);
        if ($encoder == self::CODER_JSON) {
            return json_encode($value);
        }
        return \Zend_Json::encode($value);
    }
    
    
    /**
     * Rebuids PHP content to use appropriate Imperium\JSON\Base
     * 
     * @param mixed $value
     * @return mixed
     */
    private static function rebuild($value)
    {
        if ($value instanceof \StdClass) {
            $temp = (array) $value;
            $object = true;
        } elseif (is_array($value)) {
            $temp = $value;
            $object = false;
        } else {
            return $value;
        }
        $inner = array();
        foreach ($temp as $key => $prop) {
            $inner[$key] = self::rebuild($prop);
        }
        if ($object) {
            return new J\Object($inner);
        } else {
            return new J\ArrayObject($inner);
        }
    }
    
    
    /**
     * Replaces Imperium\JSON\Base objects with arrays
     *  
     * @param mixed $value
     * @return $value
     */
    private static function debuild($value)
    {
        if ($value instanceof J\BaseObject) {
            $output = array();
            foreach ($value as $key => $property) {
                $output[$key] = self::debuild($property);
            } 
            return $output;
        }
        return $value;
    }
    

}