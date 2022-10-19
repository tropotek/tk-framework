<?php

namespace Tk\Mvc;

use Tk\Exception;
use Tk\Traits\SingletonTrait;
use Tk\Traits\SystemTrait;

/**
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Bootstrap
{
    use SingletonTrait;
    use SystemTrait;

    public function init()
    {
        /**
         * This makes our life easier when dealing with paths. Everything is relative
         * to the application root now.
         */
        chdir($this->getConfig()->getBasePath());

        // Apply all php config settings to php
        foreach ($this->getConfig()->getGroup('php', true) as $k => $v) {
            @ini_set($k, $v);
        }

        \Tk\Uri::$SITE_HOSTNAME = $this->getFactory()->getRequest()->getHost();
        \Tk\Uri::$BASE_URL = $this->getConfig()->getBaseUrl();

        // Init tk error handler
        \Tk\ErrorHandler::instance($this->getFactory()->getLogger());

        // Setup Vardump, ensure it does not log on production installs
        $vdLog = null;
        if ($this->getConfig()->isDebug()) $vdLog = $this->getFactory()->getLogger();
        \Tk\Debug\VarDump::instance($vdLog);

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
        }

        // Setup EventDispatcher and subscribe events, loads routes
        $this->getFactory()->initEventDispatcher();

    }

}