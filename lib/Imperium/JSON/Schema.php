<?php
namespace Imperium\JSON;

/**
 * type -- mostly done
 * properties -- done
 * items
 * optional -- done
 * additionalProperties
 * requires
 * minimum -- done
 * maximum -- done
 * minimumCanEqual -- done
 * maximumCanEqual -- done
 * minItems -- done
 * maxItems -- done
 * pattern -- done
 * maxLength -- done
 * minLength -- done
 * enum
 * description
 * format
 * contentEncoding
 * default
 * maxDecimal -- done
 * disallow
 * extends
 */
class Schema
{
    
    public static function validate( $instance, $schema ){
        if ($schema) {
            $s = new Schema();
            $errors = $s->checkProperty($instance, $schema, '', '');
        } else {
            $errors = array(self::error('', 'No provided schema'));
        }
        return array(
            'valid'  => empty($errors) ? true : false,
            'errors' => $errors,
        );
    }

    
    /**
     * Generate error array
     * 
     * @param string $path
     * @param string $message
     * @param boolean $wrap True: will wrap the error array in another array.
     * @return array
     */
    private static function error($path, $message, $wrap=false)
    {
        $error = array(
            'property' => $path,
            'message'  => $message,
        );
        if ($wrap) {
            $error = array($error);
        }
        return $error;
    }
    
    
    /**
     * Get the javascript type of the value
     * 
     * @param mixed $value
     * @return string
     */
    private static function getType($value)
    {
        $type = strtolower(gettype($value));
        if ($type == 'object') {
            if ($value instanceof ArrayObject) {
                $type = 'array';
            }
        } elseif ($type == 'double') {
            $type = preg_match('/\./', $value) ? $type = 'number' : $type = 'integer';
        }
        return $type;
    }
    
    
    /**
     * 
     * @param $value
     * @param $schema
     * @param $path
     * @return unknown_type
     */
    private static function checkString($value, $schema, $path)
    {
        $type = self::getType($value);
        if ($type != 'string') {
            return self::error($path, "Must be a string, found: $type", true);
        }
        $errors = array();
        $pattern = $schema['pattern'];
        if (is_string($pattern) && !preg_match("/$pattern/", $value)) {
            $errors[] = self::error($path, "Must match pattern: /$pattern/");
        }
        $maxLen = $schema['maxLength'];
        if (is_int($maxLen) && strlen($value) > $maxLen) {
            $errors[] = self::error($path, "Must not exceed length of $maxLen");
        }
        $minLen = $schema['minLength'];
        if (is_int($minLen) && strlen($value) < $minLen) {
            $errors[] = self::error($path, "Must not have length under $minLen");
        }
        
        return $errors;
    }
    
    
    private static function checkNumber($value, $schema, $path)
    {
        $type = self::getType($value);
        if (!in_array($type, array('number', 'integer'))) {
            return self::error($path, "Must be a number, found: $type", true);
        }
        $errors = array();
        //check minimum value
        $minimum = $schema['minimum'];
        $minEq   = $schema['minimumCanEqual'] === false ? false : true;
        if (is_numeric($minimum)) {
            if ($minEq && $value < $minimum) {
                $errors[] = self::error($path, "Must be greater than or equal to: $minimum");
            } elseif (!$minEq && $value <= $minimum) {
                $errors[] = self::error($path, "Must be greater than: $minimum");
            }
        }
            
        //check maximum value
        $maximum = $schema['maximum'];
        $maxEq   = $schema['maximumCanEqual'];
        if (is_numeric($maximum)) {
            if ($maxEq && $value > $maximum) {
                $errors[] = self::error($path, "Must be less than or equal to: $maximum");
            } elseif (!$maxEq && $value >= $maximum) {
                $errors[] = self::error($path, "Must be less than: $maximum");
            }
        }
        
        //check maxDecimal
        $maxDec = $schema['maxDecimal'];
        if (is_int($maxDec) && $maxDec >=0) {
            $decs = preg_replace('/^\d*\./', '', (string)$value);
            if (strlen($decs) > $maxDec) {
                $errors[] = self::error($path, "Must not exceed $maxDec decimal places");
            }
        }
        
        return $errors;
    }
    
    private static function checkInteger($value, $schema, $path)
    {
        $type = self::getType($value);
        if ($type != 'integer') {
            return self::error($path, "Must be an integer, found: $type", true);
        }
        $errors = array();
        //check minimum value
        $minimum = $schema['minimum'];
        $minEq   = $schema['minimumCanEqual'] === false ? false : true;
        if (is_numeric($minimum)) {
            if ($minEq && $value < $minimum) {
                $errors[] = self::error($path, "Must be greater than or equal to: $minimum");
            } elseif (!$minEq && $value <= $minimum) {
                $errors[] = self::error($path, "Must be greater than: $minimum");
            }
        }
            
        //check maximum value
        $maximum = $schema['maximum'];
        $maxEq   = $schema['maximumCanEqual'] === false ? false : true;
        if (is_numeric($maximum)) {
            if ($maxEq && $value > $maximum) {
                $errors[] = self::error($path, "Must be less than or equal to: $maximum");
            } elseif (!$maxEq && $value >= $maximum) {
                $errors[] = self::error($path, "Must be less than: $maximum");
            }
        }        
        
        return $errors;
    }


    private static function checkBoolean($value, $schema, $path)
    {
        $type = self::getType($value);
        if ($type != 'boolean') {
            return self::error($path, "Must be a boolean, found: $type", true);
        }
        return array();
    }
    
    
    private static function checkNull($value, $schema, $path)
    {
        $type = self::getType($value);
        if ($type != 'null') {
            return self::error($path, "Must be a null, found: $type", true);
        }
        return array();
    }
    
    
    private static function checkArray($value, $schema, $path)
    {
        $type = self::getType($value);
        if ($type != 'array') {
            return self::error($path, "Must be an array, found: $type", true);
        }
        $errors = array();
        
        $min = $schema['minItems'];
        if (is_int($min) && $value->count() < $min) {
            $errors[] = self::error($path, "Must have at least $min elements");
        }
        $max = $schema['maxItems'];
        if (is_int($max) && $value->count() > $max) {
            $errors[] = self::error($path, "Must have no more than $max elements");
        }
        return $errors;
    }
    
    
    private static function checkObject($value, $schema, $path)
    {
        $type = self::getType($value);
        if ($type != 'object') {
            return self::error($path, "Must be an object, found: $type", true);
        }

        $errors = array();
        
        $properties = $schema['properties'];
        if ($properties instanceof Undefined) {
            return $errors;
        } elseif ($properties instanceof Object) {
            foreach ($properties as $key => $definition) {
                $errors = array_merge($errors, self::checkProperty($value[$key], $definition, "$path.$key"));
            }
        }
        

        
        return $errors;
    }

    
    private static function checkType($value, $type, $schema, $path)
    {
        if (is_string($type)) {
            if ($type == 'any') {
                return array();
            } elseif ($type == 'string') {
                return self::checkString($value, $schema, $path);
            } elseif ($type == 'number') {
                return self::checkNumber($value, $schema, $path);
            } elseif ($type == 'integer') {
                return self::checkInteger($value, $schema, $path);
            } elseif ($type == 'object') {
                return self::checkObject($value, $schema, $path);
            } elseif ($type == 'array') {
                return self::checkArray($value, $schema, $path);
            } elseif ($type == 'boolean') {
                return self::checkBoolean($value, $schema, $path);
            } elseif ($type == 'null') {
                return self::checkNull($value, $schema, $path);
            } else {
                return self::error($path, "Unknown type in schema: $type", true);
            }
        } elseif ($type instanceof ArrayObject) {
            foreach ($type as $option) {
                $errors = self::checkType($value, $option, $schema, $path);
                if (empty($errors)) {
                    return array();
                }
            }
            return self::error($path, 'Must be one of optional choices.', true); //TODO: list found and options
        }
        //TODO: handle included schemas
    }
    
    
    private static function checkProperty($instance, $schema, $path)
    {
        if (!($schema instanceof Object)) {
            return self::error($path, 'Invalid schema/property definition.', true);
        }
        
        //TODO: handle .readonly
                
        // if undefined check if property is optional
        if ($instance instanceof Undefined) {
            if ($schema['optional'] !== true) {
                return self::error($path, 'Property is required', true);
            }
            return array();
        }
        
        $errors = array();
        
        //TODO: handle .extends
        
        // Check type of is property
        if ($prop = $schema['type']) {
            $errors = array_merge($errors, self::checkType($instance, $prop, $schema, $path));
        }
        
        // check type against disallow
        if ($prop = $schema['disallow']) {
            if (!($prop instanceof Undefined)) {
                $disCheck = self::checkType($instance, $prop, $schema, $path);
                if (empty($disCheck)) {
                    $errors = array_merge($errors, self::error($path, 'Type must not match disallow', true));
                }
            }
        }
        
        
        
        // End of the tests for Undefined or null values
        if ($instance === null) {
            return $errors;
        } else {
            
        }
        
        if ($instance instanceof Object) {
        } elseif ($instance instanceof ArrayObject) {
        } else {
        
        

            
            
        }
        return $errors;
    }
    

/*
    
    private function checkObjold($instance, $schema, $path, $additionalProp)
    {
        $errors = array();
        if ($schema instanceof BaseObject) {
            if (!($instance instanceof Object)) {
                $errors[] = array(
                    'property' => $path,
                    'message'  => 'an object is required',
                );
            }
            foreach ($schema as $key => $propDef) {
                if (!preg_match("/^__/", $key)) {
                    $errors = array_merge($errors, $this->checkProp($instance[$key], $propDef, $path, $key));
                }
            }
        }
        foreach ($instance as $key => $value) {
            if (!preg_match("/^__/", $key) && $schema && (!isset($schema[$key]) || !$schema[$key]) && $additionalProp === false) {
                $errors[] = array(
                    'property' => $path,
                    'message'  => 'Not defined in schma and the schema does not allow additional properties',
                );
            }
            $requires = self::requires($schema, $key);
            if ($requires && !isset($instance[$requires])) {
                $errors[] = array(
                    'property' => $path,
                    'message'  => "The presence of the property $key requires that $requires is also present",
                );
            }
            if ($schema && is_array($schema) && self::isObject($schema) && !isset($schema[$key])) {
                $errors = array_merge($errors, $this->checkProp($value, $additionalProp, $path, $key));
            }
            //TODO: check value.$schema?
        }
        return $errors;
    }

*/ 

}