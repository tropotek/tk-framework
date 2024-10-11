<?php
namespace Tk\Mail;

use Tk\FileUtil;
use Tk\Exception;

class Message
{

    /**
     * If set to true then emails can contain the users full name
     *  o 	User Name <username@domain.edu.au>
     *
     * If set to false all long email addresses will be cleaned to only contain the email address
     *  o username@domain.edu.au
     */
    public static bool $ENABLE_EXTENDED_ADDRESS = true;

    protected array  $to             = [];
    protected array  $cc             = [];
    protected array  $bcc            = [];
    protected string $from           = '';
    protected string $replyTo        = '';
    protected string $subject        = '{No Subject}';
    protected string $body           = '';
    protected bool   $html           = true;
    protected array  $headerList     = [];
    protected array  $attachmentList = [];


    public function __construct(string $body = '', string $subject = '', string $to = '', string $from = '')
    {
        $this->setBody($body);
        $this->setSubject($subject);
        $this->addTo($to);
        $this->setFrom($from);
    }

    public static function create(string $body = '', string $subject = '', string $to = '', string $from = ''): self
    {
        return new self($body, $subject, $to, $from);
    }

    public function setBody(string $body): static
    {
        $this->body = $body;
        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Returns the parsed message body ready for sending.
     */
    public function getParsed(): string
    {
        return $this->getBody();
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function addHeader(string $header, string $value = ''): self
    {
        if (str_contains($header, ':')) {
            $this->headerList[] = explode(':', $header, 2);
        } else {
            $this->headerList[$header] = $value;
        }
        return $this;
    }

    public function getHeadersList(): array
    {
        return $this->headerList;
    }

    public function setHeaderList(array $array = []): self
    {
        $this->headerList = $array;
        return $this;
    }

    public function setFrom(string $email): self
    {
        $this->from = trim($email);
        return $this;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function setReplyTo(string $email): self
    {
        $this->replyTo = trim($email);
        return $this;
    }

    public function getReplyTo(): string
    {
        return $this->replyTo;
    }

    public function addTo(string $email): self
    {
        return $this->addAddress($email, $this->to);
    }

    public function getTo(): array
    {
        return $this->to;
    }

    public function hasRecipient(): bool
    {
        return (count($this->getTo()) > 0);
    }

    public function addCc(string $email): self
    {
        return $this->addAddress($email, $this->cc);
    }

    public function getCc(): array
    {
        return $this->cc;
    }

    public function addBcc(string $email): self
    {
        return $this->addAddress($email, $this->bcc);
    }

    public function getBcc(): array
    {
        return $this->bcc;
    }

    public function getAllRecipients(): array
    {
        return $this->getTo() + $this->getCc() + $this->getBcc();
    }

    /**
     * Add a recipient address to the message
     * Only for internal usage
     */
    private function addAddress(string $email, array &$arr): self
    {
        if ($email) {
            $list = self::strToList($email);
            foreach ($list as $e) {
                if (self::isValidEmail($e)) {
                    $arr[] = trim($e);
                }
            }
        }
        return $this;
    }

    /**
     * reset the arrays:
     *  o to
     *  o cc
     *  o bcc
     * If full true include:
     *  o from
     *  o fileAttachments
     *  o stringAttachments
     *
     */
    public function reset(): self
    {
        $this->to = [];
        $this->cc = [];
        $this->bcc = [];
        return $this;
    }

    /**
     * Is this message a html message
     */
    public function setHtml(bool $b = true): self
    {
        $this->html = $b;
        return $this;
    }

    public function isHtml(): bool
    {
        return $this->html;
    }

    /**
     * take an email list and return a string
     */
    public static function listToStr(array|string $list, string $separator = ','): string
    {
        if (is_string($list)) $list = self::strToList($list);
        $str = '';
        foreach ($list as $email) {
            if (!self::isValidEmail($email)) continue;
            $str .= $email . $separator;
        }
        return rtrim($str, $separator);
    }

    /**
     * Take a string and break it into a list
     * EG:
     *  'email1@test.org,email2@eample.com,...'
     *  'email1@test.org;email2@eample.com,...'
     *  'email1@test.org:email2@eample.com,...'
     *  'name #1 <email1@test.org>,name #2 <wmail2@test.org>,...'
     *
     * returns a compatible email array for to,cc,bcc, from
     * @note There may be a bug here now that we know that email usernames can contain any ascii character
     */
    public static function strToList(string $str, string $separator = ','): array
    {
        $str = str_replace(';', ',', $str);
        $str = str_replace(':', ',', $str);
        if ($separator)
            $str = str_replace($separator, ',', $str);
        return explode(',', $str);
    }

    /**
     * split an email address from its parts to an array
     * EG:
     *   o "username@domain.com" = array('username@domain.com', 'username')
     *   o "User Name <username@domain.com>" = array('username@domain.com', 'User Name')
     *   O All unknowns return array('{$email}', '')
     */
    public static function splitEmail(string $email): array
    {
        $email = trim($email);
        if (preg_match('/(.+) <(\S+)>/', $email, $regs)) {
            return array(strtolower($regs[2]), $regs[1]);
        } else if (preg_match('/((\S+)@(\S+))/', $email, $regs)) {
            return array(strtolower($email), $regs[2]);
        }
        return array($email, '');
    }

    public static function joinEmail(string $email, string $name = ''): string
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return '';
        if (!$email || !$name || !self::$ENABLE_EXTENDED_ADDRESS) {
            return $email;
        }
        return sprintf('%s <%s>', $name, $email);
    }

    public static function isValidEmail(string $email): bool
    {
        list($e, $n) = self::splitEmail($email);
        return filter_var($e, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Adds an attachment from a path on the filesystem.
     * Returns false if the file could not be found
     * or accessed.
     */
    public function addAttachment(string $path, string $name = '', string $type = 'application/octet-stream'): self
    {
        $encoding = 'base64';
        if (!is_readable($path)) {
            throw new Exception('Cannot read file: ' . $path);
        }
        if (!$type) {
            $type = FileUtil::getMimeType($path);
        }
        if (!$name) {
            $name = basename($path);
        }
        $data = file_get_contents($path);
        return $this->addStringAttachment($data, $name, $encoding, $type);
    }

    /**
     * Get the file attachments
     */
    public function getAttachmentList(): array
    {
        return $this->attachmentList;
    }

    public function setAttachmentList(array $array = []): self
    {
        $this->attachmentList = $array;
        return $this;
    }

    /**
     * Adds a string or binary attachment (non-filesystem) to the list.
     * This method can be used to attach ascii or binary data,
     * such as a BLOB record from a database.
     *
     * @param string $data Binary attachment data.
     * @param string $name Name of the attachment.
     * @param string $encoding File encoding
     * @param string $type File extension (MIME) type.
     */
    public function addStringAttachment(string $data, string $name, string $encoding = 'base64', string $type = 'application/octet-stream'): self
    {
        $obj = new \stdClass();
        $obj->name = $name;
        $obj->encoding = $encoding;
        if ($type == 'application/octet-stream') {      // Try to locate the correct mime if not found
            $mime = FileUtil::getMimeType($name);
            if ($mime) $type = $mime;
        }
        $obj->type = $type;
        $obj->string = $data;         // This is not encoded, should be raw attachment binary data

        $this->attachmentList[] = $obj;
        return $this;
    }

    /**
     * Return a string representation of this message
     */
    public function toString(): string
    {
        $str = "\nisHtml: " . ($this->isHtml() ? 'Yes' : 'No') . " \n";
        $str .= 'Attachments: ' . count($this->attachmentList) . "\n";

        /* email/name arrays */
        $str .= 'from: ' . $this->getFrom() . "\n";
        if ($this->getReplyTo()) {
            $str .= 'replyTo: ' . $this->getReplyTo() . "\n";
        }
        $str .= 'to: ' . self::listToStr($this->getTo()) . "\n";
        if (count($this->cc))
            $str .= 'cc: ' . self::listToStr($this->getCc()) . "\n";
        if (count($this->bcc))
            $str .= 'bcc: ' . self::listToStr($this->getBcc()) . "\n";

        $str .= "subject: " . $this->getSubject() . "\n";
        $str .= "body:  \n  " . str_replace($this->getBody(), "\n", "\n  ") . "\n\n";
        return $str;
    }

}