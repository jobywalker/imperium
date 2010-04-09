<?php
namespace Imperium\XML;

use Imperium\Exception as E;

/**
 * Encode PHP natives as XML string
 * @author Joby Walker <joby@imperium.org>
 */
class Encoder
{
    /**
     * Create declaration for node
     * @param array $options
     * @return string
     */
    public static function declaration($options)
    {
        if (isset($options['declare']) && $options['declare']) {
            $encoding = isset($options['encoding']) ? ' encoding="'.$options['encoding'].'"' : '';
            return "<?xml version=\"1.0\"$encoding?>\n";
        }
        return '';
    }

    /**
     * Determine the type of the value
     * @param mixed $value
     * @return string
     */
    public static function getType($value)
    {
        $type = \strtolower(\gettype($value));
        if ($type == 'object') {
            if ($value instanceof \Imperium\JSON\ArrayObject) {
                return 'array';
            } elseif ($value instanceof \Imperium\JSON\Undefined) {
                return 'undefined';
            }
        } elseif ($type == 'array') {
            if (!empty($value) && (array_keys($value) !== range(0, count($value) - 1))) {
                return 'object';
            }
        } elseif ($type == 'integer' || $type == 'double') {
            return 'number';
        }
        return $type;
    }

    /**
     * Encode content into nodes
     * @param mixed $data Content
     * @param string $node Node name
     * @param array $options
     * @return string
     */
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

    /**
     * Create a node with child nodes
     * @param mixed $data Content
     * @param string $node Node name
     * @param array $options
     * @return string
     */
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

    /**
     * Create an array of nodes
     * @param array $data Content of the nodes
     * @param string $node Node name
     * @param array $options
     * @return string
     */
    public static function encodeArray($data, $node = 'root',$options = array())
    {
        $s = '';
        foreach ($data as $value) {
            $s .= self::encode($value, $node, $options);
        }
        return $s;
    }

    /**
     * Create boolean value node
     * @param boolean $data
     * @param string $node Node name
     * @param array $options
     * @return string
     */
    public static function encodeBoolean($data, $node = 'root',$options = array())
    {
        if ($data) {
            return self::makeNode('true', $node, $options);
        }
        return self::makeNode('false', $node, $options);
    }

    /**
     * Create a numeric value node
     * @param mixed $data Content for the node
     * @param string $node Node name
     * @param array $options
     * @return string
     */
    public static function encodeNumber($data, $node = 'root',$options = array())
    {
        return self::makeNode((string)$data, $node, $options);
    }

    /**
     * Create a string value node
     * @param string $data Content for the node
     * @param string $node Node name
     * @param array $options
     * @return string
     */
    public static function encodeString($data, $node = 'root',$options = array())
    {
        $data = \htmlspecialchars($data);
        return self::makeNode($data, $node, $options);
    }

    /**
     * Create a null value node
     * @param null $data Should be null
     * @param string $node Node name
     * @param array $options
     * @return string
     */
    public static function encodeNull($data, $node = 'root',$options = array())
    {
        return self::declaration($options) . self::offset($options) . '<' . self::normalizeNode($node) . '/>';
    }

    /**
     * Create the XML string node
     * @param string $data Content of the node
     * @param string $node Node name
     * @param array $options
     * @return string
     */
    public static function makeNode($data, $node, $options = array())
    {
        $decl = self::declaration($options);
        $pre = self::offset($options);
        $wrap = '';
        if (isset($options['wrap']) && $options['wrap'] && isset($options['offset']) && $options['offset']) {
            if ($pre) {
                $wrap = $pre;
            } else {
                $wrap = "\n";
            }
        }
        $inner = '';
        if ($node === null) {
            $inner = $data;
        } else {
            $node = self::normalizeNode($node);
            $inner = '<' . $node . '>' . $data . $wrap . '</' . $node . '>';
        }
        return $decl . $pre . $inner;
    }

    /**
     * Create a valid XML node name
     * @param string $node
     * @return string
     * @throws Imperium\Exception\InvalidInputValue If the $node parameter cannot be made into a valid node
     */
    public static function normalizeNode($node)
    {
        $node = preg_replace('/^[^a-zA-Z_]+/', '', $node);
        $node = preg_replace('/[^a-zA-Z0-9_]/', '', $node);
        if (!$node) {
            throw new E\InvalidInputValue('XML node names must not be blank');
        }
        return $node;
    }

    /**
     * Determines the appropriate offset for pretty xml output
     * @param array $options
     * @return string
     */
    public static function offset($options=array())
    {
        $pre = '';
        if (isset($options['offset']) && $options['offset'] && isset($options['depth']) && $options['depth'] > 0) {
            $pre .= "\n";
            for ($x=0; $x<$options['depth']; $x++) {
                $pre .= $options['offset'];
            }
        }
        return $pre;
    }
}