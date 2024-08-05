<?php
namespace Tk\Mvc;

use Dom\Template;
use Tk\DataMap\Db\TextEncrypt;
use Tk\Traits\SingletonTrait;
use Tk\Traits\SystemTrait;
use Tt\Db;

class Bootstrap
{
    use SingletonTrait;
    use SystemTrait;

    public function init(): void
    {
        Db::connect(
            $this->getConfig()->get('db.mysql', ''),
            $this->getConfig()->get('db.mysql.options', []),
        );
        if ($this->getConfig()->get('php.date.timezone')) {
            DB::setTimezone($this->getConfig()->get('php.date.timezone'));
        }

        // Apply all php config settings to php
        foreach ($this->getConfig()->getGroup('php', true) as $k => $v) {
            @ini_set($k, $v);
        }

        // Init tk error handler
        \Tk\ErrorHandler::instance($this->getFactory()->getLogger());

        \Tk\Debug\VarDump::instance($this->getFactory()->getLogger());

        TextEncrypt::$encryptKey = $this->getConfig()->get('system.encrypt', '');

        if ($this->getConfig()->isDebug()) {
            // Allow self-signed certs in file_get_contents in debug mode
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

        $session = $this->getFactory()->getSession();
        $session->start();  // NOTE: stdout before $session->start() will throw error

        // ready the Request
        $request = $this->getFactory()->getRequest();
        $request->setSession($session);

        // Setup EventDispatcher and subscribe events, loads routes
        $this->getFactory()->initEventDispatcher();

    }

    protected function cliInit(): void
    {
        // Setup EventDispatcher and subscribe events, loads routes
        $this->getFactory()->initEventDispatcher();
    }

}