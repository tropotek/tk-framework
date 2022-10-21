<?php
/**
 * Remember to refresh the cache after editing
 *
 * Reload the page with <Ctrl>+<Shift>+R
 */
use Symfony\Component\Routing;

$routes = \Tk\Factory::instance()->getRouteCollection();


// Enable the tk mirror controller page if key set in config
if (\Tk\Config::instance()->get('db.mirror.secret', ''))
    $routes->add('system-mirror',  new Routing\Route('/util/mirror',     ['_controller' => '\Tk\Db\Util\Mirror::doDefault']));

