<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Mail;

/**
 * An email gateway object.
 *
 * @package Tk\Mail
 */
class Gateway extends \Tk\Object
{

    /**
     * @var \Tk\Mail\Gateway
     */
    static $instance = null;

    /**
     * @var array
     */
    protected $validReferers = array();

    /**
     * @var \Tk\Mail\Driver\Mailer
     */
    protected $mail = null;

    /**
     * The status of the last sent message
     * @var bool
     */
    protected $lastSent = null;

    /**
     * The status of the last sent message
     * @var \Tk\Mail\Message
     */
    protected $lastMessage = null;


    /**
     * @var array
     */
    protected $callbackList = array();


    protected $testMode = false;
    protected $testEmail = 'null@example.com.au';





    /**
     * Get an instance of the email gateway
     *
     * @return \Tk\Mail\Gateway
     */
    static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * init
     *
     */
    private function init()
    {
        $config = $this->getConfig();
        $this->mail = new Driver\Mailer();
        switch($config->get('mail.method')) {
            case 'smtp':
                $this->mail->IsSMTP();
                $this->mail->SMTPAuth = $config->get('mail.smtp.enableAuth');
                $this->mail->SMTPKeepAlive = $config->get('mail.smtp.enableKeepAlive');
                $this->mail->SMTPSecure = $config->get('mail.smtp.secure');
                $this->mail->Host = $config->get('mail.smtp.host');
                $this->mail->Port = $config->get('mail.smtp.port');
                $this->mail->Username = $config->get('mail.smtp.username');
                $this->mail->Password = $config->get('mail.smtp.password');
                break;
            case 'pop3':
                $pop = new Driver\Pop3();
                $pop->Authorise($config->get('mail.smtp.host'), 110, 30, $config->get('mail.smtp.username'), $config->get('mail.smtp.password'), 1);
                $this->mail->IsSMTP();
                $this->mail->Host = $config->get('mail.smtp.host');
                break;
            case 'sendmail':
                $this->mail->IsSendmail();
                break;
            case 'qmail':
                $this->mail->IsQmail();
                break;
            default:    // 'mail', 'phpmail', etc....
                $this->mail->IsMail();
        }
        $this->testMode = false;
        if (!$config->isLive()) {
            $this->testMode = true;
            $this->testEmail = 'noreply@example.com';
            if ($config->get('system.debugEmail')) {
                $this->testEmail = $config->get('system.debugEmail');
            }
        }

        if ($config->exists('mail.validReferers')) {
            $refs = $config->get('mail.validReferers');
            if (!is_array($refs)) {
                $refs = explode(',',  $refs);
            }
            $this->validReferers = array_merge($this->validReferers, $refs);
        }
        $this->validReferers[] = $_SERVER['HTTP_HOST'];
    }


    /**
     * Send an email message
     *
     * @param \Tk\Mail\Message $message
     * @return bool
     */
    static function send(Message $message)
    {
        return self::getInstance()->sendMessage($message);
    }

    /**
     * Send a mime email message
     *
     * @param \Tk\Mail\Message $message
     * @return bool
     * @throws \Tk\Mail\Exception
     */
    private function sendMessage(Message $message)
    {
        // Need to do this each send in-case settings change in the config.
        $this->init();
        if (!count($message->getTo())) {
            throw new Exception('No valid recipients found!');
        }
        if (!count($message->getFrom())) {
            throw new Exception('No valid sender email found!');
        }
        $this->lastMessage = $message;

        if ($message->isHtml()) {
            $this->mail->MsgHTML($message->getBody());
            $this->mail->AltBody = strip_tags($message->getBody());
        } else {
            $this->mail->Body = $message->getBody();
        }

        foreach ($message->getAttachmentList() as $obj) {
            $this->mail->AddStringAttachment($obj->string, $obj->name, $obj->encoding, $obj->type);
        }
        $this->mail->AddCustomHeader('X-PHPMAILER: ' . $this->getConfig()->get('lib.name') . ' - Ver: ' . $this->getConfig()->get('lib.version') );
        $this->mail->AddCustomHeader('X-Sender-IP: ' . $this->getRequest()->getRemoteAddr());
        $this->mail->AddCustomHeader('X-SiteReferer: ' . $this->getSession()->get('_site_referer'));
        $this->mail->AddCustomHeader('X-Referer: ' . $this->getSession()->get('_site_referer'));

        $this->checkReferer($this->validReferers);

        if ($this->testMode) {  // Send dev emails and headers of live emails if testing or debug
            $this->mail->Subject = 'Debug: ' . $message->getSubject();

            //to
            $this->mail->AddAddress($this->testEmail, 'Debug To');
            //$str = implode(', ', array_keys($message->getTo()));
            $this->mail->AddCustomHeader('X-TkDebug-To: ' . Message::listToStr($message->getTo()));

            //From
            $this->mail->SetFrom($this->testEmail, 'Debug From');
            $this->mail->AddCustomHeader('X-TkDebug-From: ' . current($message->getFrom()));

            // CC
            if (count($message->getCc())) {
                //$str = implode(', ', array_keys($message->getCc()));
                $this->mail->AddCustomHeader('X-TkDebug-Cc: ' . Message::listToStr($message->getCc()));
            }
            // BCC
            if (count($message->getBcc())) {
                //$str = implode(', ', array_keys($message->getBcc()));
                $this->mail->AddCustomHeader('X-TkDebug-Bcc: ' . Message::listToStr($message->getBcc()));
            }
        } else {        // Send live emails
            $this->mail->Subject = $message->getSubject();


            $f = $message->getFrom();
            if ($f) {
                $this->mail->SetFrom($f[0], $f[1]);
            } else {
                $e = 'root@' . $_SERVER['HTTP_HOST'];
                $this->mail->SetFrom($e, 'System');
            }

            foreach ($message->getTo() as $e => $n) {
                $this->mail->AddAddress($e, $n);
            }
            foreach ($message->getCc() as $e => $n) {
                $this->mail->AddCC($e, $n);
            }
            foreach ($message->getBcc() as $e => $n) {
                $this->mail->AddBCC($e, $n);
            }
            if ($this->getConfig()->get('system.site.email.bcc')) {
                $arr = explode(',', $this->getConfig()->get('system.site.email.bcc'));
                foreach ($arr as $em)
                    $this->mail->AddBCC($em);
            }
        }

        $this->notify('preSendMessage');


        $this->lastSent = $this->mail->Send();
        $this->doCallback($message, $this->lastSent);
        $this->notify('postSendMessage');

        $this->mail->ClearAllRecipients();
        $this->mail->ClearAttachments();
        $this->mail->ClearCustomHeaders();
        $this->mail->ClearReplyTos();
        return $this->lastSent;
    }


    /**
     * Mail Callback: the function that handles the result of the send email action.
     *
     *
     * @param  \Tk\Mail\Message $message  The message object
     * @param  boolean $isSent  The result of the send action
     */
    public function doCallback($message, $isSent)
    {
        // Use to log email message after sent
        if (count($this->callbackList)) {
            foreach ($this->callbackList as $callbackArr) {
                if ((is_string($callbackArr) && function_exists($callbackArr)) ||
                    (is_array($callbackArr) && method_exists($callbackArr[0], $callbackArr[1])) )
                {
                    $params = array($message, $isSent);
                    call_user_func_array($callbackArr, $params);
                }
            }
        }
    }

    /**
     * Add a callback function to the gateway.
     * Is called after the email is sent.
     *
     * @param callback $function
     */
    public function addCallback($function)
    {
        $this->callbackList[] = $function;
    }

    /**
     * Gte the last sent message status
     *
     * @return bool
     */
    public function getLastSent()
    {
        return $this->lastSent;
    }

    /**
     * Get the Tk mail object.
     *
     *
     * @return \Tk\Mail\Driver\Mailer
     */
    public function getMail()
    {
        return $this->mail;
    }


    /**
     * check_referer() breaks up the environmental variable
     * HTTP_REFERER by "/" and then checks to see if the second
     * member of the array (from the explode) matches any of the
     * domains listed in the $referers array (declared at top)
     *
     * @param array $referers
     * @throws \Tk\Mail\Exception
     */
    private function checkReferer($referers)
    {
        // do not check referrer for CLI apps
        if (substr(php_sapi_name(), 0, 3) == 'cli') {
            return;
        }
        $this->notify('preCheckReferer');
        if (count($referers) > 0) {
            if ($_SERVER['HTTP_REFERER']) {
                $temp = explode('/', $_SERVER['HTTP_REFERER']);
                $found = false;
                while (list(, $stored_referer) = each($referers)) {
                    if (preg_match('/^' . $stored_referer . '$/i', $temp[2]))
                        $found = true;
                }
                if (!$found) {
                    throw new Exception("You are coming from an unauthorized domain. Illegal Referer.");
                }
            } else {
                throw new Exception("Sorry, but I cannot figure out who sent you here. Your browser is not sending an HTTP_REFERER. This could be caused by a firewall or browser that removes the HTTP_REFERER from each HTTP request you submit.");
            }
        } else {
            throw new Exception("There are no referers defined. All submissions will be denied.");
        }
    }

}

