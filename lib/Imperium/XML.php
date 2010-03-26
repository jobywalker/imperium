<?php
namespace Imperium;

use Imperium\Exception as E;

class XML
{
    public static function encode($data, $root='root', $options = array())
    {
        return XML\Encoder::encode($data, $root, $options);
    }
    
    public static function decode($string)
    {
        return XML\Decoder::decode($string);
    }
}