<?php

use Phalcon\Loader;

$loader = new Loader();
$loader->registerNamespaces(
    array(
// ARCH DO NOT REMOVE THIS LINE
@@register@@)
);
$loader->register();