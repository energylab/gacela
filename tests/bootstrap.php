<?php

set_include_path(get_include_path().PATH_SEPARATOR.dirname(__DIR__).'/');

require_once 'library/Gacela.php';

$gacela = Gacela::instance();

require_once 'Test/GUnit/TestCase.php';
require_once 'Test/GUnit/Extensions/Database/TestCase.php';