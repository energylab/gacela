<?php

set_include_path(get_include_path().PATH_SEPARATOR.dirname(__DIR__).'/');


require_once 'library/Gacela.php';
require_once 'TestCase.php';
require_once 'DbTestCase.php';

$gacela = Gacela::instance();