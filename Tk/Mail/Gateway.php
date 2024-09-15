<?php
namespace Tk\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use Tk\Log;
use Tk\Uri;
use Tk\Exception;

class Gateway
{

    protected array  $params        = [];
    protected array  $validReferers = [];
    protected array  $error         = [];
    protected bool   $lastSent      = true;
    protected string $host          = 'localhost';

    protected Message   $lastMessage;
    protected PHPMailer $mailer;


    public function __construct(array $params)
    {
        $this->params = $params;
        $this->mailer = new PHPMailer();

        if (isset($this->params['mail.driver'])) {
            // Set the mail driver Default: mail();
            switch ($this->params['mail.driver']) {
                case 'smtp':
                    $this->mailer->isSMTP();
                    $this->mailer->SMTPAuth      = $this->params['mail.smtp.enableAuth'];
                    $this->mailer->SMTPKeepAlive = $this->params['mail.smtp.enableKeepAlive'];
                    $this->mailer->SMTPSecure    = $this->params['mail.smtp.secure'];
                    $this->mailer->Host          = $this->params['mail.smtp.host'];
                    $this->mailer->Port          = $this->params['mail.smtp.port'];
                    $this->mailer->Username      = $this->params['mail.smtp.username'];
                    $this->mailer->Password      = $this->params['mail.smtp.password'];
                    break;
                case 'sendmail':
                    $this->mailer->isSendmail();
                    break;
                case 'qmail':
                    $this->mailer->isQmail();
                    break;
                default:
                    $this->mailer->isMail();
                    break;
            }
        }

        if (isset($this->params['mail.dkim.domain'])) {
            if (empty($this->params['mail.dkim.domain'])) {
                throw new Exception('Invalid DKIM domain value.');
            }
            if (empty($this->params['mail.dkim.private']) && empty($this->params['mail.dkim.private_string'])) {
                throw new Exception('Invalid DKIM private key value.');
            }

            $this->mailer->DKIM_domain         = $this->params['mail.dkim.domain'] ?? '';
            $this->mailer->DKIM_private        = $this->params['mail.dkim.private'] ?? '';
            $this->mailer->DKIM_private_string = $this->params['mail.dkim.private_string'] ?? '';
            $this->mailer->DKIM_passphrase     = $this->params['mail.dkim.passphrase'] ?? '';
            $this->mailer->DKIM_selector       = $this->params['mail.dkim.selector'] ?? 'default';
        }

        if (isset($_SERVER['HTTP_HOST'])) {
            $this->host = $_SERVER['HTTP_HOST'];
            $this->validReferers[] = $this->host;
        }

        if (!empty($this->params['mail.validReferers'])) {
            if (!is_array($this->params['mail.validReferers'])) {
                $this->params['mail.validReferers'] = explode(',', $this->params['mail.validReferers']);
            }
            $this->validReferers += $this->params['mail.validReferers'];
        }
    }

    public function send(Message $message): bool
    {
        $this->error = [];
        try {
            if (!count($message->getTo())) {
                throw new Exception('No valid recipients found!');
            }
            if (!$message->getFrom()) {
                throw new Exception('No valid sender email found!');
            }
            $this->checkReferer($this->validReferers);

            //$event = new MailEvent($this, $message);
            //$this->dispatcher?->dispatch($event, MailEvents::PRE_SEND);

            if ($message->isHtml()) {
                $this->mailer->msgHTML($message->getParsed());
                $this->mailer->AltBody = strip_tags($message->getParsed());
            } else {
                $this->mailer->Body = $message->getParsed();
            }

            $this->mailer->CharSet = 'UTF-8';
            if (isset($this->params['mail.encoding']) && $this->params['mail.encoding']) {
                $this->mailer->CharSet = $this->params['mail.encoding'];
            }

            foreach ($message->getAttachmentList() as $obj) {
                $this->mailer->addStringAttachment($obj->string, $obj->name, $obj->encoding, $obj->type);
            }

            $message->addHeader('X-Application', 'tk-mail');
            $message->addHeader('X-Application-Name', 'tk-mail');
            $message->addHeader('X-Application-Version', '8.0.0');

            if (!empty($this->params['site.title'])) {
                $message->addHeader('X-Application-Name', $this->params['site.title']);
            }
            if (isset($this->params['system.info.version'])) {
                $message->addHeader('X-Application-Version', $this->params['system.info.version']);
            }

            $message->addHeader('X-Sender-IP', $this->params['clientIp'] ?? '');
            $message->addHeader('X-Host', $this->params['hostname'] ?? '');
            $message->addHeader('X-Referer', Uri::create($this->params['referer'] ?? '')->getRelativePath());

            $this->mailer->Subject = $message->getSubject();

            // Dev env test email redirect
            if (($this->params['env.type'] ?? '') == 'dev') {

                //$this->mailer->SMTPDebug = 2;
                $message->addHeader('X-Debug-To', Message::listToStr($message->getTo()));
                $message->addHeader('X-Debug-From', $message->getFrom());
                if ($message->getReplyTo()) {
                    $message->addHeader('X-Debug-Reply-To', $message->getReplyTo());
                }

                // Set debug recipient and sender
                $testEmail = $this->params['system.debug.email'] ?? 'debug@'.$this->host;
                if ($testEmail == 'debug@'.$this->host) {
                    Log::notice("No debug email found. Add \$config['system.debug.email'] = 'email@example.com' to your config.php");
                }

                if (is_array($testEmail)) {
                    foreach ($testEmail as $i => $em) {
                        if ($i == 0) {
                            $testEmail = $em;
                            $this->mailer->addAddress($em, 'Debug To');
                        } else {
                            $this->mailer->addCC($em, 'Debug To');
                        }
                    }
                } else {
                    $this->mailer->addAddress($testEmail, 'Debug To');
                }

                $this->mailer->setFrom($testEmail, 'Debug From');

                if (count($message->getCc())) {
                    $message->addHeader('X-Debug-Cc', Message::listToStr($message->getCc()));
                }
                if (count($message->getBcc())) {
                    $message->addHeader('X-Debug-Bcc', Message::listToStr($message->getBcc()));
                }
            } else {        // Send live emails
                $email = $message->getFrom();
                if (!$email) $email = 'noreply@' . $this->host;
                list($e, $n) = Message::splitEmail($email);
                $this->mailer->setFrom($e, $n);

                if ($message->getReplyTo()) {
                    list($e, $n) = Message::splitEmail($message->getReplyTo());
                    $this->mailer->addReplyTo($e, $n);
                }

                foreach ($message->getTo() as $email) {
                    list($e, $n) = Message::splitEmail($email);
                    $this->mailer->addAddress($e, $n);
                }
                foreach ($message->getCc() as $email) {
                    list($e, $n) = Message::splitEmail($email);
                    $this->mailer->addCC($e, $n);
                }
                foreach ($message->getBcc() as $email) {
                    list($e, $n) = Message::splitEmail($email);
                    $this->mailer->addBCC($e, $n);
                }
            }

            foreach ($message->getHeadersList() as $h => $v) {
                $this->mailer->addCustomHeader($h, $v);
            }

            // Set dkim identity
            if (isset($this->params['mail.dkim'])) {
                $this->mailer->DKIM_identity = $this->mailer->From;
            }

            // Send Email
            $this->lastMessage = $message;

            $this->lastSent = $this->mailer->send();
            if (!$this->lastSent) {
                throw new Exception($this->mailer->ErrorInfo);
            }

            // Dispatch Post Send Event
            //$this->dispatcher?->dispatch($event, MailEvents::POST_SEND);

        } catch (\Exception $e) {
            $this->error[] = $e->getMessage();
            throw $e;
        }

        $this->mailer->clearAllRecipients();
        $this->mailer->clearAttachments();
        $this->mailer->clearCustomHeaders();
        $this->mailer->clearReplyTos();
        return $this->lastSent;
    }

    public function getErrors(): array
    {
        return $this->error;
    }

    /**
     * Get the last sent message status
     */
    public function getLastSent(): bool
    {
        return $this->lastSent;
    }

    public function getLastMessage(): Message
    {
        return $this->lastMessage;
    }

//    public function getDispatcher(): EventDispatcher
//    {
//        return $this->dispatcher;
//    }

//    public function setDispatcher(EventDispatcher $dispatcher): static
//    {
//        $this->dispatcher = $dispatcher;
//        return $this;
//    }

    public function getMailer(): PHPMailer
    {
        return $this->mailer;
    }

    /**
     * check_referer() breaks up the environmental variable
     * HTTP_REFERER by "/" and then checks to see if the second
     * member of the array (from the explode) matches any of the
     * domains listed in the $referers array (declared at top)
     */
    private function checkReferer(array $referers): void
    {
        // do not check referrer for CLI apps
        if (str_starts_with(php_sapi_name(), 'cli')) {
            return;
        }
        if (!isset($this->params['mail.checkReferer']) || !$this->params['mail.checkReferer']) {
            return;
        }

        if (count($referers) > 0) {
            if (isset($_SERVER['HTTP_REFERER'])) {
                $temp = explode('/', $_SERVER['HTTP_REFERER']);
                $found = false;
                foreach ($referers as $k => $stored_referer) {
                    if (preg_match('/^' . $stored_referer . '$/i', $temp[2])) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    throw new Exception("You are coming from an unauthorized domain. Illegal Referer.");
                }
            } else {
                throw new Exception("Sorry, but I cannot figure out who sent you here. Your browser is not sending an HTTP_REFERER. This could be caused by a firewall or browser that removes the HTTP_REFERER from each HTTP request you submit.");
            }
        } else {
            throw new Exception("There is no referer defined. All submissions will be denied.");
        }
    }

}