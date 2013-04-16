<?php

set_include_path(get_include_path().PATH_SEPARATOR.dirname(__DIR__).'/');

require_once 'library/Gacela.php';


ini_set('soap.wsdl_cache_enabled', '0'); 
ini_set('soap.wsdl_cache_ttl', '0'); 

$gacela = Gacela::instance();

require_once 'Test/GUnit/TestCase.php';
require_once 'Test/GUnit/Extensions/Database/TestCase.php';
