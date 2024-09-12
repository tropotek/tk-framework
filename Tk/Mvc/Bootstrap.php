<?php
namespace Tk\Mvc;

use Bs\Factory;
use Dom\Template;
use Tk\Config;
use Tk\DataMap\Db\TextEncrypt;
use Tk\System;
use Tk\Traits\SingletonTrait;
use Tk\Db;

class Bootstrap
{
    use SingletonTrait;

    public function init(): void
    {
        $config = Config::instance();

        // Apply all php config settings to php
        foreach ($config->getGroup('php', true) as $k => $v) {
            @ini_set($k, $v);
        }

        // make app directories if not exists
        \Tk\FileUtil::mkdir(System::makePath($config->get('path.temp')), true);
        \Tk\FileUtil::mkdir(System::makePath($config->get('path.cache')), true);


        // Setup default migration paths
        // todo: cache this as it will not change often/ever
        $vendorPath = $config->getBasePath() . $config->get('path.vendor.org');
        $libPaths = scandir($vendorPath);
        array_shift($libPaths);
        array_shift($libPaths);
        $migratePaths = [$config->getBasePath() . '/src/config/sql'] +
            array_map(fn($path) => $vendorPath . '/' . $path . '/config/sql' , $libPaths);
        $config->set('db.migrate.paths', $migratePaths);

        Db::connect(
            $config->get('db.mysql', ''),
            $config->get('db.mysql.options', []),
        );
        if ($config->get('php.date.timezone')) {
            DB::setTimezone($config->get('php.date.timezone'));
        }

        Factory::instance()->initLogger();

        // Init tk error handler
        \Tk\ErrorHandler::instance();

        \Tk\Debug\VarDump::instance();

        TextEncrypt::$encryptKey = $config->get('system.encrypt', '');

        if ($config->isDev()) {
            // Allow self-signed certs in file_get_contents in dev environment
            stream_context_set_default(["ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ]]);
        }

        if (System::isCli()) {
            $this->cliInit();
        } else {
            $this->httpInit();
        }
    }

    protected function httpInit(): void
    {
        $config = Config::instance();

        /**
         * This makes our life easier when dealing with paths. Everything is relative
         * to the application root now.
         */
        chdir($config->getBasePath());

        \Tk\Uri::$SITE_HOSTNAME = $config->getHostname();
        \Tk\Uri::$BASE_URL = $config->getBaseUrl();
        if ($config->isDebug()) {
            Template::$ENABLE_TRACER = true;
        }

        // init session
        Factory::instance()->initSession();

        // init the Request
        Factory::instance()->getRequest();

        // Setup EventDispatcher and subscribe events, loads routes
        Factory::instance()->initEventDispatcher();

    }

    protected function cliInit(): void
    {
        // Setup EventDispatcher and subscribe events, loads routes
        Factory::instance()->initEventDispatcher();
    }

}