<?php
namespace Imperium\JSON;

class Undefined
{
    public $value = null;
    public function __toString()
    {
        return '';
    }
}