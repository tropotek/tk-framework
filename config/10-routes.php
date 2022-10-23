<?php
/**
 * Remember to refresh the cache after editing
 *
 * Reload the page with <Ctrl>+<Shift>+R
 */
use Symfony\Component\Routing\Loader\Configurator\CollectionConfigurator;

return function (CollectionConfigurator $routes) {

    if (\Tk\Config::instance()->get('db.mirror.secret', '')) {
        $routes->add('system-mirror', '/util/mirror')
            ->controller([\Tk\Db\Util\Mirror::class, 'doDefault']);
    }

};