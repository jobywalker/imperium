<?php
namespace Imperium;

use Imperium\Exception as E;

class XML
{
    /**
     * Encode PHP native data to XML
     * @param mixed $data
     * @param string $root Root node of the resulting XML
     * @param array $options Encoding options
     * @return string
     */
    public static function encode($data, $root='root', $options = array())
    {
        return XML\Encoder::encode($data, $root, $options);
    }

    /**
     * Decode XML string to php native
     * @param string $string XML content as string
     * @param array $options Decoding options
     * @return mixed PHP native content
     */
    public static function decode($string, $options=array())
    {
        return XML\Decoder::decode($string, $options);
    }
}