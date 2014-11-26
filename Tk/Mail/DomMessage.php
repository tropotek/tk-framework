<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Mail;

/**
 * Tk\Mail\DomMessage
 *
 * @package Tk\Mail
 */
class DomMessage extends Message implements \Dom\RendererInterface
{
    /**
     * @var \Dom\Template
     */
    private $templateOrg = null;

    /**
     * @var \Dom\Template
     */
    private $template = null;

    /**
     * @var string
     */
    protected $content = '';





    /**
     * Send this message to its recipients.
     *
     * @return bool
     */
    public function send()
    {
        if (!$this->getBody()) {
            $this->show();
            parent::setBody($this->getTemplate()->toString());
        }
        if (parent::send()) {
            //$this->reset();
        }
        return $this;
    }

    /**
     * reset the arrays:
     *  o to
     *  o cc
     *  o bcc
     *  o fileAttachments
     *  o stringAttachments
     *
     * @return \Tk\Mail\Message
     */
    public function reset()
    {
        $this->template = clone $this->templateOrg;
        parent::setBody('');
        return parent::reset();
    }

    /**
     * setBody
     *
     * @param string $body
     * @throws \Tk\Mail\Exception
     */
    public function setBody($body)
    {
        throw new Exception('You cannot set the body directly with a Dom Message. Use setContent() and setTemplate()');
    }

    /**
     * setContent
     *
     * @param type $str
     */
    public function setContent($str)
    {
        $this->content = $str;
    }


    /**
     * Execute the renderer.
     *
     */
    public function show()
    {
        $template = $this->getTemplate();
        $template->replaceHtml('content', $this->content);
        $template->insertText('subject', $this->getSubject());

        $request = \Tk\Request::getInstance();
        $template->insertText('requestUri', $request->getRequestUri()->toString());
        $template->setAttr('requestUri', 'href', $request->getRequestUri()->toString());
        $template->insertText('remoteIp', $request->getRemoteAddr());
        $template->insertText('userAgent', $request->getUserAgent());
    }


    /**
     * Make the template
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xmlStr = <<<HTML
<html>
<head>
  <title>Email</title>
  <style type="text/css">
body {
  font-family: arial,sans-serif;
  font-size: 80%;
  padding: 10px;
  margin: 0;
  background-color: #FFF;
}
p {
  line-height: 1.2em;
}
  </style>
</head>
<body>

  <h3 var="subject"></h3>
  <hr/>
  <p>&#160;</p>
  <div class="content" var="content"></div>
  <p>&#160;</p>
  <div class="footer">
    <hr />
    <p>
      <i>Page:</i> <a href="#" var="requestUri"></a><br/>
      <i>IP Address:</i> <span var="remoteIp"></span><br/>
      <i>User Agent:</i> <span var="userAgent"></span>
    </p>
  </div>

</body>
</html>
HTML;

        return \Dom\Template::load($xmlStr);
    }


    /**
     * Set a new template for this renderer.
     *
     * @param \Dom\Template $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        $this->templateOrg = clone $template;
    }

    /**
     * Get the template
     * This method will try to call the magic method __makeTemplate
     * to get a template if non exsits.
     * Use this for object that use internal templates.
     *
     * @return \Dom/Template
     */
    public function getTemplate()
    {
        $magic = '__makeTemplate';
        if (!$this->template && method_exists($this, $magic)) {
            $this->template = $this->$magic();
            $this->templateOrg = clone $this->template;
        }
        return $this->template;
    }


}