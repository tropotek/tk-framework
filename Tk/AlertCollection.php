<?php
namespace Tk;

/**
 * A class to add and render Bootstrap alert boxes
 *
 * A container of Msg objects
 *
 * @see http://getbootstrap.com/components/#alerts
 */
class AlertCollection extends \Dom\Renderer\Renderer implements \Dom\Renderer\DisplayInterface
{

    const SID = 'App_Alert';

    /**
     * @var Alert
     */
    public static $instance = null;

    /**
     * @var array
     */
    public $messages = array();

    /**
     * @var \Tk\Session
     */
    public $session = null;


    /**
     * Singleton, Use getInstance()
     * Use:
     *   Alert::getInstance()
     */
    private function __construct() { }

    /**
     * Get an instance of this object
     *
     * @param \Tk\Session|array $session
     * @return AlertCollection
     */
    public static function getInstance($session = array())
    {
        if (!$session)
            $session = \Tk\Config::getInstance()->getSession();

        if (!self::$instance && $session) {
            if (isset($session[self::SID])) {
                self::$instance = new self();
                self::$instance->messages = $session[self::SID];
            } else {
                self::$instance = new self();
                $session[self::SID] = array();
            }
            self::$instance->session = $session;
        }
        return self::$instance;
    }


    /**
     * add a message to display on next page load
     *
     * @param string $message
     * @param string $title
     * @param string $type Use the constants \Mod\Alert::TYPE_INFO, etc
     * @param string $icon
     */
    public static function add($message, $title = 'Warning', $type = '', $icon = '')
    {
        $msg = Alert::create($message, $type, $title, $icon);
        self::getInstance()->messages[$type][] = $msg;
        self::getInstance()->session[self::SID] = self::getInstance()->messages;
    }

    public static function addSuccess($message, $title = 'Success')
    {
        self::add($message, $title, Alert::TYPE_SUCCESS, 'icon-ok-sign');
    }

    public static function addWarning($message, $title = 'Warning')
    {
        self::add($message, $title, Alert::TYPE_WARNING, 'icon-warning-sign');
    }

    public static function addError($message, $title = 'Error')
    {
        self::add($message, $title, Alert::TYPE_ERROR, 'icon-remove-sign');
    }

    public static function addInfo($message, $title = 'Information')
    {
        self::add($message, $title, Alert::TYPE_INFO, 'icon-exclamation-sign');
    }

    /**
     * Get message list
     *
     * @param string $type
     * @return Alert[]
     */
    public function getMessageList($type = '')
    {
        if (isset($this->messages[$type])) {
            return $this->messages[$type];
        }
        return $this->messages;
    }

    /**
     * show
     *
     * @return \Dom\Template
     */
    public function show()
    {
        $template = null;
        if (self::hasMessages()) {
            $this->template = null; // Render with new template each time
            $template = $this->getTemplate();
            foreach ($this->messages as $msgList) {
                /* @var Alert $msg */
                foreach ($msgList as $type => $msg) {
                    $template->appendTemplate('alertContainer', $msg->show());
                }
            }
            $this->clear();
        }
        return $template;
    }

    /**
     * Check if there are any messages
     *
     * @return bool
     */
    public static function hasMessages()
    {
        return count(self::getInstance()->messages);
    }

    /**
     * Clear the message list
     *
     * @return AlertCollection
     */
    public function clear()
    {
        $this->messages = array();
        self::getInstance()->session[self::SID] = self::getInstance()->messages;
        return $this;
    }

    /**
     * makeTemplate
     *
     * @return string
     */
    public function __makeTemplate()
    {
        $xmlStr = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<div class="tk-alert-container" var="alertContainer"></div>
XML;
        return \Dom\Loader::load($xmlStr);
    }

}
