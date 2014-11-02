<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Mail;

/**
 * This message accepts text templates and replaces {param} with the
 * corosponding value. Use Tk_Mail_TplMessage::get() and Tk_Mail_TplMessage::set()
 * to set the replaceable template variables.
 *
 * The default template param list:
 *  o {subject}
 *  o {siteUrl}
 *  o {requestUri}
 *  o {refererUri}
 *  o {remoteIp}
 *  o {userAgent}
 *  o {ccEmailList}
 *  o {bccEmailList}
 *  o {toEmailList}
 *  o {toEmail}
 *  o {fromEmail}
 *
 *
 * @package Tk\Mail
 */
class TplMessage extends Message
{

    protected $replaceList = array();

    protected $template = '';



    /**
     * __construct
     *
     * @param string $template
     */
    public function __construct($template = '{content}')
    {
        $this->template = $template;

        $this->set('requestUri', $this->getRequest()->getRequestUri()->toString());
        if ($this->getRequest()->getReferer()) {
            $this->set('refererUri', $this->getRequest()->getReferer()->toString());
        }
        $this->set('remoteIp', $this->getRequest()->getRemoteAddr());
        $this->set('userAgent', $this->getRequest()->getUserAgent());
        $this->set('siteUrl', $this->getConfig()->getSiteUrl());
        $this->set('siteTitle', $this->getConfig()->getSiteTitle());
        $this->set('date', \Tk\Date::create()->toString(\Tk\Date::LONG_DATETIME));
    }

    /**
     * create
     *
     * @param string $template
     * @return \Tk\Mail\TplMessage
     */
    static public function create($template = '{content}')
    {
        $obj = new self($template);
        return $obj;
    }

    /**
     * Ideal extended classes will extend this class
     * and add their extra parameters that are available.
     * This will help in documenting templates and their available params.
     * Ex:
     * <code>
     *   public function getAvaliableParams()
     *   {
     *       $arr = parent::getAvaliableParams();
     *       $array['newParam'] = array(
     *          'the reffering url the email was sent from.',
     *          'http://example.com/~user/Projects/index.php'
     *            );
     *       return $arr;
     *   }
     * </code>
     *
     * @return array
     * @deprecated No longer available (V2.0 Remove)
     */
    public function getAvailableParams()
    {
        $arr = array();
//        $arr['subject'] = array(
//            'A one sentance string of text, the subject line of this message.',      // Description
//            'This is an example of a subject line'                                  // Example of data
//        );
//        $arr['requestUri'] = array(
//            'The url the email was sent from.',
//            'http://example.com/site/contact.php'
//        );
//        $arr['refererUri'] = array(
//            'the reffering url the email was sent from.',
//            'http://example.com/site/index.php'
//        );
//        $arr['remoteIp'] = array(
//            'The IP address of the client machine that sent the email.',
//            '192.168.0.255'
//        );
//        $arr['userAgent'] = array(
//            'The user agent string of the client sending the email.',
//            'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:19.0) Gecko/20100101 Firefox/19.0'
//        );
//        $arr['siteUrl'] = array(
//            'This is the site base url. Useful for linking media.',
//            '/site/home/'
//        );
//        $arr['ccEmailList'] = array(
//            'The CC email list if available.',
//            'email1@example.com, email2@example.com, email3@example.com'
//        );
//        $arr['bccEmailList'] = array(
//            'The BCC email list if available.',
//            'email1@example.com, email2@example.com, email3@example.com'
//        );
//        $arr['toEmailList'] = array(
//            'The recipient email list.',
//            'email1@example.com, email2@example.com, email3@example.com'
//        );
//        $arr['toEmail'] = array(
//            'The recipient email list. An alias for `toEmailList`.',
//            'email1@example.com, email2@example.com, email3@example.com'
//        );
//        $arr['fromEmail'] = array(
//            'The sender email address.',
//            'email1@example.com'
//        );
//        $arr['sig'] = array(
//            'The sender signiture',
//            '<p>Thanks,</p><p>Mick...</p>'
//        );
        return $arr;
    }



    /**
     * add a Replace Item
     *
     * @param string $key
     * @param string $value
     * @return \Tk\Mail\TplMessage
     */
    public function set($key, $value)
    {
        $this->replaceList[$key] = $value;
        return $this;
    }

    /**
     * delete an item
     *
     * @param string $key
     * @return \Tk\Mail\TplMessage
     */
    public function delete($key)
    {
        if (isset($this->replaceList[$key]))
            unset($this->replaceList[$key]);
        return $this;
    }

    /**
     * get a replace param
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        if (isset($this->replaceList[$key]))
            return $this->replaceList[$key];
    }

    /**
     * Does an entry exist
     *
     * @param string $key
     * @return bool
     */
    public function exists($key)
    {
        return isset($this->replaceList[$key]);
    }

    /**
     * set Replace List
     *
     * @param array $arr
     * @return \Tk\Mail\TplMessage
     */
    public function setList($arr)
    {
        $this->replaceList = $arr;
        return $this;
    }

    /**
     * Execute the renderer.
     * NOTICE: `content` is treated as a template as well. Vars will be replaced
     * in its text as well as the template text.
     *
     * @return bool
     */
    public function send()
    {
        $this->set('subject', $this->getSubject());
        $this->set('ccEmailList', implode(', ', $this->getCc()));
        $this->set('bccEmailList', implode(', ', $this->getBcc()));
        $this->set('toEmailList', implode(', ', $this->getTo()));
        $this->set('toEmail', implode(', ', $this->getTo()));
        list($fe, $fn) = $this->getFrom();
        $email = $fe;
        if ($fn) {
            $email = $fn . ' <' . $email . '>';
        }
        $this->set('fromEmail', $email);

        $template = $this->template;

        $template = str_replace('{content}', $this->replaceList['content'], $template);
        $template = \Tk\Template::parseTemplate($template, $this->replaceList);

//        $template = str_replace('{content}', $this->replaceList['content'], $template);
//        foreach ($this->replaceList as $k => $v) {
//            if ($k == 'content') continue;
//            $template = str_replace('{'.$k.'}', $v, $template);
//        }



        $this->body = $template;
        return parent::send();
    }


}