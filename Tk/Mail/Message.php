<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Mail;

/**
 * Message
 *
 *
 *
 * @package Tk\Mail
 */
class Message extends \Tk\Object
{
    /**
     * @var array
     */
    protected $to = array();

    /**
     * @var array
     */
    protected $cc = array();

    /**
     * @var array
     */
    protected $bcc = array();

    /**
     * @var array
     */
    protected $from = array();

    /**
     * @var string
     */
    protected $subject = '{No Subject}';

    /**
     * @var string
     */
    protected $body = '';

    /**
     * @var bool
     */
    protected $html = true;

    /**
     * @var array
     */
    protected $headerList = array();

    /**
     * @var array
     */
    protected $attachmentList = array();





    /**
     *
     * @param string $email
     * @param string $name
     * @return string
     */
    static function join($email, $name = '')
    {
        if (!$name) {
            return $email;
        }
        return sprintf('%s <%s>', $name, $email);
    }

    /**
     * split an email address from its parts to an array
     * EG:
     *   o 'email1@example.com'
     *   o
     *
     * @param string $address
     * @return array Containing (email, name)
     */
    static function split($address)
    {
        $address = trim($address);
        if (preg_match('/(.+) <(\S+)>/', $address, $regs)) {
            return array(trim(strip_tags(strtolower($regs[1]))), $regs[0]);
        } else if (preg_match('/((\S+)@(\S+))/', $address, $regs)) {
            return array(trim(strip_tags(strtolower($address))), '');
        }
        return array(trim(strip_tags(strtolower($address))), '');
    }

    /**
     * take an email list fom above and return a string
     *
     * @param array $list
     * @return string
     */
    static function listToStr($list)
    {
        $str = '';
        foreach ($list as $e => $n) {
            $str .= self::join($e, $n) . ',';
        }
        if ($str) $str = substr($str, 0, -1);
        return $str;
    }

    /**
     * Take a string and break it into a list
     * EG:
     *  'email1@test.org,email2@eample.com,...'
     *  'name #1 <email1@test.org>, name #2 <wmail2@test.org>, ...'
     *
     * returns a compatible email array for to,cc,bcc, from
     *
     * @param string $str
     * @return array
     */
    static function strToList($str)
    {
        $arr = explode(',', $str);
        $list = array();
        foreach ($arr as $s) {
            list($e, $n) = self::split($s);
            $list[$e] = $n;
        }
        return $list;
    }



    /**
     * Send this message to its recipients.
     * NOTE: Remember to catch any exceptions, if you do not want you application to exit.
     */
    public function send()
    {
        $this->notify('preSend');
        $b = Gateway::send($this);
        return $b;
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
     * @param bool $full
     * @return \Tk\Mail\Message
     */
    public function reset($full = false)
    {
        $this->to = array();
        $this->cc = array();
        $this->bcc = array();
        if ($full) {
            $this->from = array();
            $this->attachmentList = array();
            $this->headerList = array();
        }
        return $this;
    }

    /**
     * Adds a custom header.
     *
     * @param string $header
     * @return \Tk\Mail\Message
     */
    public function addHeader($header)
    {
        $this->headerList[] = explode(':', $header, 2);
        return $this;
    }

    /**
     * Get a list of
     *
     * @return array
     */
    public function getHeadersList()
    {
        return $this->headerList;
    }


    /**
     * set From
     *
     * @param string $email
     * @param string $name
     * @return \Tk\Mail\Message
     */
    public function setFrom($email, $name = '')
    {
        $this->from = array(trim($email), trim($name));
        $this->notify('setFrom');
        return $this;
    }

    /**
     * Return the from address
     * array('email', 'name')
     *
     * @return array
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Add a recipient address to the message
     *
     * @param string $email
     * @param string $name
     * @return \Tk\Mail\Message
     */
    public function addTo($email, $name = '')
    {
        return $this->addAddress($email, $name, $this->to);
    }

    /**
     * Get the to recipient list
     *
     * array(
     *   'email1' => 'name1',
     *   'email2' => 'name2'
     * );
     *
     * @return array
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Add A Carbon Copy recipient
     *
     * @param string $email
     * @param string $name
     * @return \Tk\Mail\Message
     */
    public function addCc($email, $name = '')
    {
        return $this->addAddress($email, $name, $this->cc);
    }

    /**
     * Get the Cc recipient list
     *
     * array(
     *   'email1' => 'name1',
     *   'email2' => 'name2'
     * );
     *
     * @return array
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * Add a Blind Carbon Copy recipiant
     *
     * @param string $email
     * @param string $name
     * @return \Tk\Mail\Message
     */
    public function addBcc($email, $name = '')
    {
        return $this->addAddress($email, $name, $this->bcc);
    }

    /**
     * Get the bcc recipient list
     *
     * array(
     *   'email1' => 'name1',
     *   'email2' => 'name2'
     * );
     *
     * @return array
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * Add a recipient address to the message
     * Only for internal usage
     *
     * @param string $address
     * @param string $name
     * @param $arr
     * @return \Tk\Mail\Message
     */
    private function addAddress($address, $name, &$arr)
    {
        if (!$address) return $this;
        if (!$name) {
            list($address, $name) = self::split($address);
        }
        if (!is_array($address)) {
            if (strpos($address, ',') !== false) {
                $address = self::strToList($address);
            }
            $address = array($address => $name);
        }
        foreach ($address as $email => $n) {
            $email = trim(strip_tags(strtolower($email)));
            if (!preg_match(\Tk\Validator::REG_EMAIL, $email)) continue;
            $arr[$email] = trim(strip_tags($n));
        }
        return $this;
    }


    /**
     * Set the subject
     *
     * @param string $subject
     * @return \Tk\Mail\Message
     */
    public function setSubject($subject)
    {
        $this->validateString($subject);
        $this->subject = $subject;
        $this->notify('setSubject');
        return $this;
    }

    /**
     * Returns the message subject.
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * The message text body
     *
     * @param string $body
     * @return \Tk\Mail\Message
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Returns the message body.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }


    /**
     * Is this message a html message
     * If no parameter given nothing is set but its value returned.
     *
     * @param bool $b (Optional)If not set acts as a getter(accessor) method
     * @return bool
     */
    public function isHtml($b = null)
    {
        if ($b !== null) {
            $this->html = $b;
        }
        return $this->html;
    }

    /**
     * Adds an attachment from a path on the filesystem.
     * Returns false if the file could not be found
     * or accessed.
     *
     * @param string $path Path to the file.
     * @param string $name if null file basename used
     * @param string $type File extension (MIME) type. If null tries to auto detect type.
     * @return \Tk\Mail\Message
     * @throws \Tk\Mail\Exception
     */
    public function addAttachment($path, $name = '', $type = 'application/octet-stream')
    {
        $encoding = 'base64';

        if (!is_readable($path)) {
            throw new Exception('Cannot read file: ' . $path);
        }
        if (!$type) {
            $type = mime_content_type($path);
            if (!$type) {
                $type = 'application/octet-stream';
            }
        }
        if (!$name) {
            $name = basename($path);
        }

        $string = file_get_contents($path);
        return $this->addStringAttachment($string, $name, $encoding, $type);
    }

    /**
     * Get the file attachments
     *
     * @return array
     */
    public function getAttachmentList()
    {
        return $this->attachmentList;
    }

    /**
     * Adds a string or binary attachment (non-filesystem) to the list.
     * This method can be used to attach ascii or binary data,
     * such as a BLOB record from a database.
     *
     * @param string $string String attachment data.
     * @param string $name Name of the attachment.
     * @param string $encoding File encoding
     * @param string $type File extension (MIME) type.
     * @return \Tk\Mail\Message
     */
    public function addStringAttachment($string, $name, $encoding = 'base64', $type = 'application/octet-stream')
    {
        $obj = new \stdClass();
        //$this->validateString($string);
        $obj->string = $string;
        $this->validateString($name);
        $obj->name = $name;
        $obj->encoding = $encoding;
        $obj->type = $type;
        $this->attachmentList[] = $obj;
        $this->notify('addAttachment');
        return $this;
    }



    /**
     * Check that a string looks roughly like an email address should
     * Static so it can be used without instantiation
     * Tries to use PHP built-in validator in the filter extension (from PHP 5.2), falls back to a reasonably competent regex validator
     * Conforms approximately to RFC2822
     *
     * @link http://www.hexillion.com/samples/#Regex Original pattern found here
     * @param string $email The email address to check
     * @throws \Tk\Mail\Exception
     */
    private function validateEmail($email)
    {
        //if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        if (!preg_match('/^[0-9a-zA-Z\-\._]*@[0-9a-zA-Z\-]([-.]?[0-9a-zA-Z])*$/', $email)) {
            throw new Exception('Invalid email address');
        }
    }

    /**
     * See if a string contains any supicious coding.
     *
     * @param string $str
     * @throws \Tk\Mail\Exception
     */
    private function validateString($str)
    {
        if (!$str) { return; }
        $badStrings = array("content-type:", "mime-version:", "multipart\/mixed", "content-transfer-encoding:", "bcc:", "cc:", "to:");
        foreach ($badStrings as $badString) {
            if (preg_match('/'.$badString.'/i', strtolower($str))) {
                throw new Exception("'$badString' found. Suspected injection attempt - mail not being sent.");
            }
        }
        if (preg_match("/(%0A|%0D|\\n+|\\r+)/i", $str) != 0) {
            throw new Exception("newline found in '$str'. Suspected injection attempt - mail not being sent.");
        }
    }


    /**
     * Helper Method
     * Make a default HTML template to create HTML emails
     * usage:
     *  $message->setBody($message->createHtmlTemplate($bodyStr));
     *
     * @param string $body
     * @return string
     */
    static function createHtmlTemplate($body)
    {
        $request = \Tk\Request::getInstance();
        $referer = '';
        if ($request->getReferer()) {
            $referer = $request->getReferer()->toString();
        }

        $defaultHtml = sprintf('
<html>
<head>
  <title>Email</title>

<style type="text/css">
body {
  font-family: arial,sans-serif;
  font-size: 80%%;
  padding: 5px;
  background-color: #FFF;
}
table {
  font-size: 0.9em;
}
th, td {
  vertical-align: top;
}
table {

}
th {
  text-align: left;
}
td {
  padding: 4px 5px;
}
.content {
  padding: 0px 0px 0px 20px;
}
p {
  margin: 0px 0px 10px 0px;
  padding: 0px;
}
</style>
</head>
<body>
  <div class="content">%s</div>
  <p>&#160;</p>
  <hr />
  <div class="footer">
    <p>
      <i>Page:</i> <a href="%s">%s</a><br/>
      <i>Referer:</i> <span>%s</span><br/>
      <i>IP Address:</i> <span>%s</span><br/>
      <i>User Agent:</i> <span>%s</span>
    </p>
  </div>
</body>
</html>', $body, $request->getRequestUri()->toString(), $request->getRequestUri()->toString(), $referer, $request->getRemoteAddr(), $request->getUserAgent());

        return $defaultHtml;
    }


    /**
     * Return a string representation of this message
     *
     * @return string
     */
    public function toString()
    {
        $str = '';
        $str .= "\nisHtml: " . ($this->isHtml() ? 'Yes' : 'No') . " \n";
        $str .= 'Attatchments: ' . count($this->attachmentList) . "\n";

        /* email/name arrays */
        $str .= 'from: ' . current($this->getFrom()) . "\n";
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