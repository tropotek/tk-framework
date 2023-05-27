<?php
namespace Tk\Mvc;

use Dom\Template;
use Tk\Traits\SingletonTrait;
use Tk\Traits\SystemTrait;

class Bootstrap
{
    use SingletonTrait;
    use SystemTrait;

    public function init()
    {
        // Apply all php config settings to php
        foreach ($this->getConfig()->getGroup('php', true) as $k => $v) {
            @ini_set($k, $v);
        }

        // Init tk error handler
        \Tk\ErrorHandler::instance($this->getFactory()->getLogger());

        // Setup Vardump, ensure it does not log on production installs
        $vdLog = null;
        if ($this->getConfig()->isDebug()) $vdLog = $this->getFactory()->getLogger();
        \Tk\Debug\VarDump::instance($vdLog);

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

    protected function httpInit()
    {
        /**
         * This makes our life easier when dealing with paths. Everything is relative
         * to the application root now.
         */
        chdir($this->getConfig()->getBasePath());

        \Tk\Uri::$SITE_HOSTNAME = $this->getFactory()->getRequest()->getHost();
        \Tk\Uri::$BASE_URL = $this->getConfig()->getBaseUrl();

        $session = $this->getFactory()->getSession();
        $session->start();  // NOTE: stdout before $session->start() will throw error

        // ready the Request
        $request = $this->getFactory()->getRequest();
        $request->setSession($session);

        if ($this->getConfig()->isDebug()) {
            // Allow self-signed certs in file_get_contents in debug mode
            stream_context_set_default(["ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ]]);
            Template::$ENABLE_TRACER = true;
        }

        // Setup EventDispatcher and subscribe events, loads routes
        $this->getFactory()->initEventDispatcher();

    }

    protected function cliInit()
    {
        // Setup EventDispatcher and subscribe events, loads routes
        $this->getFactory()->initEventDispatcher();
    }

}