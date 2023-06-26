<?php
/**
 * Setup system configuration parameters
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
use Tk\Config;

return function (Config $config)
{

    $config->set('path.data',                 '/data');
    $config->set('path.cache',                '/data/cache');
    $config->set('path.temp',                 '/data/tmp');
    $config->set('path.src',                  '/src');
    $config->set('path.config',               '/src/config');
    $config->set('path.vendor',               '/vendor');
    $config->set('path.vendor.org',           '/vendor/ttek');
    $config->set('path.template',             '/html');

    // Session Defaults
    $config->set('session.db_enable',         false);
    $config->set('session.db_table',          '_session');
    $config->set('session.db_id_col',         'session_id');
    $config->set('session.db_data_col',       'data');
    $config->set('session.db_lifetime_col',   'lifetime');
    $config->set('session.db_time_col',       'time');

    $config->set('session.cookie_secure',       true);
    $config->set('session.cookie_httponly',     true);
    $config->set('session.cookie_samesite',     'Strict');
    $config->set('session.cookie_path',         $config->getBaseUrl());

    $config->set('debug',     false);
    $config->set('debug.script', $config->get('path.config') . '/dev.php');

    $config->set('log.system.request', $config->get('path.cache') . '/requestLog.txt');
    $config->set('log.logLevel', \Psr\Log\LogLevel::ERROR);

    $config->set('log.ignore.noLog', false);

    // Set the timezone in the config.ini
    $config->set('php.date.timezone', 'Australia/Melbourne');

    \Tk\FileUtil::mkdir($config->getSystem()->makePath($config->get('path.temp')), true);
    \Tk\FileUtil::mkdir($config->getSystem()->makePath($config->get('path.cache')), true);


    // Setup default migration paths
    $vendorPath = $config->getBasePath() . $config->get('path.vendor.org');
    $libPaths = scandir($vendorPath);
    array_shift($libPaths);
    array_shift($libPaths);
    $migratePaths = [$config->getBasePath() . '/src/config/sql'] +
        array_map(fn($path) => $vendorPath . '/' . $path . '/config/sql' , $libPaths);
    $config->set('db.migrate.paths', $migratePaths);

    // These files are always executed in the /src/config/sql path
    $config->set('db.migrate.static', ['views.sql', 'procedures.sql', 'events.sql', 'triggers.sql']);


};