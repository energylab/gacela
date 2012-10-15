<?php

set_include_path(get_include_path().PATH_SEPARATOR.dirname(__DIR__).'/');

require_once 'library/Gacela.php';

$gacela = Gacela::instance();

$gacela->registerNamespace('Test', __DIR__);