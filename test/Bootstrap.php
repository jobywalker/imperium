<?php
set_include_path(__DIR__.'/../lib:'.__DIR__.'/../vendor:/data/phplib/pear:/data/phplib/zend:'.get_include_path());
require_once 'SplClassLoader.php';
$classloader = new SplClassLoader();
$classloader->register();