<?php
namespace Imperium\JSON;

class BaseObject extends \ArrayObject
{
    
    /**
     * Returns value at specified index, or Undefined
     * 
     * If the index does not exist an \Imperium\JSON\Undefined is returned
     * 
     * @param mixed $index String or integer index of an array
     * @return mixed
     */
    public function offsetGet($index)
    {
        $ar = $this->getArrayCopy();
        if (isset($ar[$index])) {
            return $ar[$index];
        } elseif (in_array($index, array_keys($ar))){
            return null;
        }
        return new Undefined;
    }
}