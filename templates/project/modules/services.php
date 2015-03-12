<?php

/**
 * Services are globally registered in this file
 */

use Phalcon\Mvc\Router;
use Phalcon\Mvc\Router\Annotations;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\DI\FactoryDefault;
use Phalcon\Session\Adapter\Files as SessionAdapter;

/**
 * The FactoryDefault Dependency Injector automatically registers the right services to provide a full stack framework
 */
$di = new FactoryDefault();

/**
 * Registering a router
 */
$di['router'] = function () {

    $router = new Annotations(false);

    $router->setDefaultModule('@@module_path@@');

    // TODO Add specific route setting here

    // ARCH DO NOT REMOVE THIS LINE
    @@module_route@@

    return $router;
};

/**
 * The URL component is used to generate all kinds of URLs in the application
 */
$di['url'] = function () {
    $url = new UrlResolver();
    $url->setBaseUri('/@@name@@/');

    return $url;
};

/**
 * Starts the session the first time some component requests the session service
 */
$di['session'] = function () {
    $session = new SessionAdapter();
    $session->start();

    return $session;
};
