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
    
    /**
     * Decode JSON encoded string into PHP type
     * 
     * This method uses json_decode if it exists, otherwise Zend_Json::decode.
     * 
     * @param string $json JSON encoded string
     * @return mixed Decoded value as appropriate PHP type
     */
    public static function decode($json)
    {
        if (function_exists('json_decode')) {
            $value = json_decode($json, true);
        } else {
            $value = \Zend_Json::decode($json);
        }
        return self::rebuild($value);
    }
    
    
    /**
     * Encode PHP type into JSON encoded string
     * 
     * @param mixed $value PHP type
     * @return string JSON encoded string
     */
    public static function encode($value)
    {
        $value = self::debuild($value);
        if (function_exists('json_encode')) {
            return json_encode($value);
        } else {
            return \Zend_Json::encode($value);
        }
    }
    
    
    /**
     * Rebuids PHP content to use appropriate Imperium\JSON\Base
     * 
     * @param mixed $value
     * @return mixed
     */
    private static function rebuild($value)
    {
        if (!is_array($value)) {
            return $value;
        }
        $temp = array();
        foreach ($value as $key => $prop) {
            $temp[$key] = self::rebuild($prop);
        }
        if (self::isJSONObject($value)) {
            return new J\Object($temp);
        } else {
            return new J\ArrayObject($temp);
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
    
    
    /**
     * Checks arrays to determine if it is an object (associative) or array (indexed)
     * 
     * @param array $value
     * @return boolean True if parameter is an associative array
     */
    private static function isJSONObject($value)
    {
        if (!is_array($value)) {
            return false;
        }
        if (!empty($value) && (array_keys($value) !== range(0, count($value)-1))) {
            return true;
        }
        return false;
    }

}