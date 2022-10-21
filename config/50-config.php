<?php
return function (\Tk\Config $config)
{
    $config->set('script.start.time', microtime(true));

    // Site paths
    $config->set('base.path', $config->getSystem()->discoverBasePath());
    $config->set('base.url', $config->getSystem()->discoverBaseUrl());

    $config->set('path.data',         '/data');
    $config->set('path.cache',        '/data/cache');
    $config->set('path.temp',         '/data/temp');
    $config->set('path.src',          '/src');
    $config->set('path.config',       '/src/config');
    $config->set('path.vendor',       '/vendor');
    $config->set('path.vendor.org',   '/vendor/ttek');
    $config->set('path.template',     '/html');

    $config->set('path.routes',       '/src/config/routes.php');
    $config->set('path.routes.cache', '/data/cache/routes.cache.php');

    // Session Defaults
    $config->set('session.db_enable',         false);
    $config->set('session.db_table',          '_session');
    $config->set('session.db_id_col',         'session_id');
    $config->set('session.db_data_col',       'data');
    $config->set('session.db_lifetime_col',   'lifetime');
    $config->set('session.db_time_col',       'time');

    $config->set('debug', false);
    $config->set('log.system.request', $config->get('path.temp') . '/requestLog.txt');
    $config->set('log.logLevel', \Psr\Log\LogLevel::ERROR);

    // Set the timezone in the config.ini
    $config->set('php.date.timezone', 'Australia/Melbourne');

};