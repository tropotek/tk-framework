<?php
namespace Tk\Mvc;

use Dom\Template;
use Tk\DataMap\Db\TextEncrypt;
use Tk\Db\Session;
use Tk\Traits\SingletonTrait;
use Tk\Traits\SystemTrait;
use Tt\Db;

class Bootstrap
{
    use SingletonTrait;
    use SystemTrait;

    public function init(): void
    {
        // Apply all php config settings to php
        foreach ($this->getConfig()->getGroup('php', true) as $k => $v) {
            @ini_set($k, $v);
        }

        Db::connect(
            $this->getConfig()->get('db.mysql', ''),
            $this->getConfig()->get('db.mysql.options', []),
        );
        if ($this->getConfig()->get('php.date.timezone')) {
            DB::setTimezone($this->getConfig()->get('php.date.timezone'));
        }

        $this->getFactory()->initLogger();

        // Init tk error handler
        \Tk\ErrorHandler::instance();

        \Tk\Debug\VarDump::instance();

        TextEncrypt::$encryptKey = $this->getConfig()->get('system.encrypt', '');

        if ($this->getConfig()->isDev()) {
            // Allow self-signed certs in file_get_contents in dev environment
            stream_context_set_default(["ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ]]);
        }

        if ($this->getSystem()->isCli()) {
            $this->cliInit();
        } else {
            $this->httpInit();
        }
    }

    protected function httpInit(): void
    {
        /**
         * This makes our life easier when dealing with paths. Everything is relative
         * to the application root now.
         */
        chdir($this->getConfig()->getBasePath());

        \Tk\Uri::$SITE_HOSTNAME = $this->getConfig()->getHostname();
        \Tk\Uri::$BASE_URL = $this->getConfig()->getBaseUrl();
        if ($this->getConfig()->isDebug()) {
            Template::$ENABLE_TRACER = true;
        }

        // init session
        $this->getFactory()->getSession();

        // init the Request
        $this->getFactory()->getRequest();

        // Setup EventDispatcher and subscribe events, loads routes
        $this->getFactory()->initEventDispatcher();

    }

    protected function cliInit(): void
    {
        // Setup EventDispatcher and subscribe events, loads routes
        $this->getFactory()->initEventDispatcher();
    }

}