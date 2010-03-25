<?php
namespace Imperium\XML;

use Imperium\Exception as E;

class Encoder
{
    public static function declaration($options)
    {
        if (isset($options['declare']) && $options['declare']) {
            $encoding = isset($options['encoding']) ? ' encoding="'.$options['encoding'].'"' : '';
            return "<?xml version=\"1.0\"$encoding?>\n";
        }
        return '';
    }
    
    public static function getType($value)
    {
        $type = \strtolower(\gettype($value));
        if ($type == 'object') {
            if ($value instanceof \Imperium\JSON\ArrayObject) {
                return 'array';
            } elseif ($value instanceof \Imperium\JSON\Undefined) {
                return 'undefined';
            }
        } elseif ($type == 'integer' || $type == 'double') {
            return 'number';
        }
        return $type;
    }
    
    public static function encode($data, $node = 'root', $options = array())
    {
        $type = self::getType($data);
        if ($type == 'object') {
            return self::encodeObject($data, $node, $options);
        } elseif ($type == 'array') {
            return self::encodeArray($data, $node, $options);
        } elseif ($type == 'boolean') {
            return self::encodeBoolean($data, $node, $options);
        } elseif ($type == 'number') {
            return self::encodeNumber($data, $node, $options);
        } elseif ($type == 'string') {
            return self::encodeString($data, $node, $options);
        } elseif ($type == 'null') {
            return self::encodeNull($data, $node, $options);
        }
    }

    public static function encodeObject($data, $node = 'root',$options = array())
    {
        $s = '';
        $childOptions = $options;
        $childOptions['declare'] = false;
        $options['wrap'] = true;
        if (isset($childOptions['depth'])) {
            $childOptions['depth']++;
        } else {
            $childOptions['depth'] = 1;
        }
        foreach ((array)$data as $key => $value) {
            $s .= self::encode($value, $key, $childOptions);
        }
        return self::makeNode($s, $node, $options);
    }
    
    public static function encodeArray($data, $node = 'root',$options = array())
    {
        $s = '';
        foreach ($data as $value) {
            $s .= self::encode($value, $node, $options);
        }
        return $s;
    }
    
    public static function encodeBoolean($data, $node = 'root',$options = array())
    {
        if ($data) {
            return self::makeNode('true', $node, $options);
        }
        return self::makeNode('false', $node, $options);
    }
    
    public static function encodeNumber($data, $node = 'root',$options = array())
    {
        return self::makeNode((string)$data, $node, $options);
    }
    
    public static function encodeString($data, $node = 'root',$options = array())
    {
        $data = htmlspecialchars($data);
        return self::makeNode($data, $node, $options);
    }
    
    public static function encodeNull($data, $node = 'root',$options = array())
    {
        return self::declaration($options) . self::offset($options) . '<' . self::normalizeNode($node) . '/>';
    }
    
    public static function makeNode($data, $node, $options = array())
    {
        $decl = self::declaration($options);
        $node = self::normalizeNode($node);
        $pre = self::offset($options);
        $wrap = '';
        if (isset($options['wrap']) && $options['wrap'] && isset($options['offset']) && $options['offset']) {
            if ($pre) {
                $wrap = $pre;
            } else {
                $wrap = "\n";
            }
        }
        return $decl . $pre . '<' . $node . '>' . $data . $wrap . '</' . $node . '>';
    }
    
    public static function normalizeNode($node)
    {
        $node = preg_replace('/^[^a-zA-Z_]+/', '', $node);
        $node = preg_replace('/[^a-zA-Z0-9_]/', '', $node);
        if (!$node) {
            throw new E\InvalidInputValue('XML node names must not be blank');
        }
        return $node;
    }
    
    public static function offset($options=array())
    {
        $pre = '';
        if (isset($options['offset']) && $options['offset'] && isset($options['depth']) && $options['depth']) {
            $pre .= "\n";
            for ($x=0; $x<$options['depth']; $x++) {
                $pre .= $options['offset'];
            }
        }
        return $pre;
    }
}