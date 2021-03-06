<?php
namespace Imperium\XML;

use Imperium\Exception as E;

/**
 * Decodes an XML string into PHP native content
 *
 * @author Joby Walker <joby@imperium.org>
 */
class Decoder
{
    /**
     * Decode xml string to php native content
     * @param string $string XML String
     * @param array $options
     * @return mixed PHP native content
     */
    public static function decode($string, $options = array())
    {
        # strip xml declaration
        $string = self::stripDeclaration($string);
        # handle cdata (find cdatablocks replace with htmlspecialchars())
        $string = self::encodeCdata($string);
        # handle attributes (find "..." blocks and htmlspecialchars());
        $string = self::encodeAttributes($string);
        $elements = preg_split('/(\<[^\>]*>)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);

        return self::unfurl($elements, $options);
    }

    /**
     * Strip XML declaration from content
     * @param string $string XML content
     * @return string
     */
    private static function stripDeclaration($string)
    {
        return preg_replace('/^\s*\<\?xml[^\?\>]*\?\>\s*/', '', $string);
    }

    /**
     * Replace CDATA sections with htmlescaped content
     * @param string $string XML Content
     * @return string
     */
    private static function encodeCdata($string)
    {
        $e = preg_split('/(\<\!\[CDATA\[.*\]\]\>)/U', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
        $output = '';
        foreach ($e as $s) {
            $matches = array();
            if (preg_match('/^\<\!\[CDATA\[(.*)\]\]\>$/', $s, $matches)) {
                $output .= \htmlspecialchars($matches[1]);
            } else {
                $output .= $s;
            }
        }
        return $output;
    }

    /**
     * Replace attribute values with htmlescaped content
     * @param $string $string XML content
     * @return $string
     */
    private static function encodeAttributes($string)
    {
        $e = preg_split('/(\"[^\"]*\")/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
        $output = '';
        foreach ($e as $s) {
            $matches = array();
            if (preg_match('/^\"(.*)\"$/', $s, $matches)) {
                $output .= '"'.\htmlspecialchars($matches[1]).'"';
            } else {
                $output .= $s;
            }
        }
        return $output;
    }

    /**
     * Recursive unfurl of elements into noded structure
     * @param array $elements
     * @param array $options
     * @return mixed PHP native content
     */
    private static function unfurl($elements, $options)
    {
        if (isset($options['StripContainer']) && $options['StripContainer']) {
            $strip = true;
            $options['StripContainer'] = false;
        } else {
            $strip = false;
        }
        $count = count($elements);
        if ($count == 1) {
            # if one element must be a value
            return self::value($elements[0]);
        }
        # must have child elements
        $stage = array();
        while (count($elements)) {
            if (preg_match('/^[\s]*$/', $elements[0])) {
                array_shift($elements);
            }
            if (preg_match('/^[\s]*$/', $elements[count($elements)-1])) {
                array_pop($elements);
            }

            $node = array_shift($elements);
            $name = self::nodeName($node);
            if (preg_match('/^\<.*\/\s*\>$/', $node)) {
                # null node "<node/>"
                $stage[$name][] = null;
            } else {
                # we have an node with a value or children
                # find end node index
                $end = self::endNode($elements, $name);
                $stage[$name][] = self::unfurl(array_splice($elements, 0, $end), $options);
                array_shift($elements);
            }
        }
        $final = array();
        foreach ($stage as $key => $value) {
            if (count($value)==1) {
                if ($strip) {
                    return $value[0];
                }
                $final[$key] = $value[0];
            } else {
                if ($strip) {
                    return $value;
                }
                $final[$key] = $value;
            }
        }
        if (isset($options['ArrayOutput']) && $options['ArrayOutput']) {
            return $final;
        } else {
            return (object) $final;
        }
    }

    /**
     * Find the corresponding end node from
     * @param array $elements
     * @param string $node
     * @return integer Key for the end node
     */
    private static function endNode($elements, $node)
    {
        $depth = 0;
        for ($x = 0; $x<count($elements); $x++) {
            if (!preg_match('/^\<.*\>$/', $elements[$x])) {
                # irrelevant
            } elseif (preg_match('/^\<.*\/\>$/', $elements[$x])){
                # empty node -- irrelevant
            } elseif (preg_match('/^\<\/.*\>$/', $elements[$x])) {
                # end node
                if ($depth > 0) {
                    $depth--;
                } elseif ($node == self::nodeName($elements[$x])) {
                    return $x;
                } else {
                    throw new E\InvalidInputValue('Unexpected end node');
                }
            } elseif (preg_match('/^\<.*\>$/', $elements[$x])) {
                # must be start node
                $depth++;
            }
        }
        throw new E\InvalidInputValue("No end node found for $node");
    }

    /**
     * Determine the node name from segment
     * @param string $string
     * @return string
     */
    private static function nodeName($string)
    {
        $string = trim($string, "</>");
        $ex = explode(' ', $string);
        return $ex[0];
    }

    /**
     * Retrive a node value
     * @param string $input
     * @return mixed
     */
    private static function value($input)
    {
        $input = trim($input);
        if ($input === 'true') {
            return true;
        } elseif ($input === 'false') {
            return false;
        } elseif (is_numeric($input)) {
            if ((string)intval($input) === $input) {
                return intval($input);
            }
            return (float) $input;
        }
        return \htmlspecialchars_decode($input);
    }


}