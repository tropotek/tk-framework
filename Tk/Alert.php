<?php
namespace Tk;

/**
 * A class to add and render Bootstrap alert boxes
 *
 * A container of Msg objects
 *
 * @see http://getbootstrap.com/components/#alerts
 */
class Alert extends \Dom\Renderer\Renderer implements \Dom\Renderer\DisplayInterface
{
    /**
     *
     */
    const TYPE_WARNING = 'warning';
    const TYPE_SUCCESS = 'success';
    const TYPE_INFO = 'info';
    const TYPE_ERROR = 'danger';

    /**
     * Change this if you have a different alert class prefix
     * @var string
     */
    public static $CSS_PREFIX = 'alert-';

    /**
     * @var string
     */
    protected $message = '';

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $type = '';

    /**
     * @var string
     */
    protected $icon = '';


    /**
     *
     * @param string $message
     * @param string $type Use the constants \Mod\Alert::TYPE_INFO, etc
     * @param string $title
     * @param string $icon
     */
    public function __construct($message, $type = 'info', $title = '', $icon = '')
    {
        $this->message = $message;
        $this->type = $type;
        $this->title = $title;
        $this->icon = $icon;
    }

    /**
     * Get an instance of this object
     *
     * @throws \Tk\Exception
     */
    public static function getInstance()
    {
        throw new \Tk\Exception('Check the \App\Page\Iface and change to \Tk\Alert::someMethod() to \Tk\AlertCollection::someMethod()');
    }

    /**
     * add a message to display on next page load
     *
     * @param string $message
     * @param string $type Use the constants \Mod\Alert::TYPE_INFO, etc
     * @param string $title
     * @param string $icon
     * @return Alert
     */
    public static function create($message, $type = '', $title = '', $icon = '')
    {
        return new self($message, $type, $title, $icon);
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
        $msg = self::create($message, $type, $title, $icon);
        AlertCollection::getInstance()->messages[$type][] = $msg;
        AlertCollection::getInstance()->session[AlertCollection::SID] = AlertCollection::getInstance()->messages;
    }

    public static function addSuccess($message, $title = 'Success')
    {
        self::add($message, $title, self::TYPE_SUCCESS, 'icon-ok-sign');
    }

    public static function addWarning($message, $title = 'Warning')
    {
        self::add($message, $title, self::TYPE_WARNING, 'icon-warning-sign');
    }

    public static function addError($message, $title = 'Error')
    {
        self::add($message, $title, self::TYPE_ERROR, 'icon-remove-sign');
    }

    public static function addInfo($message, $title = 'Information')
    {
        self::add($message, $title, self::TYPE_INFO, 'icon-exclamation-sign');
    }

    /**
     * Get the class for hte alert containing div
     * Returns the self::$CSS_PREFIX . $this->type
     *
     * Change the $CSS_PREFIX in your sites bootstrap/config if needed
     *
     * @return string
     */
    public function getCss()
    {
        return self::$CSS_PREFIX . $this->type;
    }

    /**
     * show
     *
     * @param \Dom\Template $template
     * @return \Dom\Template
     * @throws \Dom\Exception
     */
    public function show($template = null)
    {
        if (!$template)
            $template = $this->__makeTemplate();

        if ($this->title) {
            $template->insertText('title', htmlentities($this->title));
            $template->setChoice('title');
        }
        $template->insertHtml('message', $this->message);
        $template->addCss('alert', $this->getCss());
        $template->setAttr('alert', 'data-type', $this->type);
        if ($this->icon) {
            $template->addCss('icon', $this->icon);
            $template->setChoice('icon');
        }

        return $template;
    }

    public function getHtmlTemplate()
    {
        $html = <<<HTML
  <div class="alert" var="alert">
    <button class="close noblock" data-dismiss="alert">&times;</button>
    <h4 choice="title"><i choice="icon" var="icon"></i> <strong var="title"></strong></h4>
    <span var="message"></span>
  </div>
HTML;
        return $html;
    }

    /**
     * makeTemplate
     *
     * @return string
     */
    public function __makeTemplate()
    {
        return \Dom\Loader::load($this->getHtmlTemplate());
    }

}
