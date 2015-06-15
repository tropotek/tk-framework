<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 * @package Tk
 */
include_once (dirname(__FILE__) . '/string.php');



$_1234_HandlerEnabled = true;
/**
 * Disable the error handler that throws exceptions
 * This can be used to silence non errors that can sometime occur
 * in PHP
 * <code>
 * <?php
 *   disableErrorHandler();
 *   $exif = exif_read_data($this->filename);
 *   enableErrorHandler();
 * ?>
 * </code>
 *
 */
function enableErrorHandler()
{
    global $_1234_HandlerEnabled;
    $_1234_HandlerEnabled = true;
}

function disableErrorHandler()
{
    global $_1234_HandlerEnabled;
    $_1234_HandlerEnabled = false;
}

/**
 * A custom Exception thrower to turn PHP errors into execeptions.
 *
 * @param int $context
 * @param string $msg
 * @param string $file
 * @param int $line
 * @throws Exception
 * @throws Tk\Exception
 * @see http://au.php.net/manual/en/class.errorException.php
 * @package Tk
 */
function ErrorHandler($context, $msg, $file, $line)
{
    global $_1234_HandlerEnabled;
    if (!$_1234_HandlerEnabled) return;

    $e = null;
    if (class_exists('\Tk\Exception'))  {
        $e = new \Tk\Exception($msg, $context);
    } else {
        $e = new \Exception($msg, $context);
    }
    switch ($context) {         // Ignore all warnings and notices in live mode
        case \E_WARNING :        // 2
        case \E_NOTICE :         // 8
        case \E_CORE_WARNING :   // 32
        case \E_USER_WARNING :   // 512
        case \E_USER_NOTICE :    // 1024
        case \E_STRICT :         // 2048
        case \E_DEPRECATED:      // 8192       // PHP 5.3
        case \E_USER_DEPRECATED: // 16384      // PHP 5.3
            if (class_exists('\Tk\Log\Log')) {
                \Tk\Log\Log::write($e->toString(), \Tk\Log\Log::DEBUG);
            }
            return;
    }
    throw $e;
}
set_error_handler('ErrorHandler');


/**
 * Output a visual dump of an object.
 *
 * EG:<br/>
 * <code>
 *   // var dump usage
 *   vd($arg1, $arg2, $arg3, ...);
 * </code>
 *
 * @optional param mixed $args Multiple vars retrieved using func_get_args()
 * @return mixed
 * @package Debug
 */
function vd()
{
    $args = func_get_args();
    if (class_exists('\Tk\Debug\Vd')) {
        if (class_exists('\Tk\Config') && !\Tk\Config::getInstance()->isDebug()) {
            return;
        }
        $method = new \ReflectionMethod('\Tk\Debug\Vd', 'dbgWrite');
        return $method->invokeArgs(NULL, $args);
    }

    foreach ($args as $v) {
        error_log(print_r($v, true));
    }
}


/**
 * Alias to write to the log through the \Tk\Log\Log object
 *
 * @param string $message
 * @param int $code
 * @return bool|\Tk\Log\Log
 */
function tklog($message, $code = \Tk\Log\Log::NOTICE)
{
    if (class_exists('\Tk\Config') && class_exists('\Tk\Log\Log')) {
        return \Tk\Config::getInstance()->getLog()->write($message, $code);
    }
    return error_log(print_r($message, true));
}


/**
 * This is a low level mail command and should not be
 * used for major application emails. No security checks are performed
 * with this function it is mearly a wrapper around the PHP mail() function
 *
 * Useful for sending messages to internal users.
 *
 * @param type $to
 * @param type $message
 * @param string $subject
 * @param string $from
 * @param string $headers
 * @return bool
 */
function tkMail($to, $message, $subject = '{No Subject}', $from = '', $headers = '') {

    $mailSent = false;
    $headers .= 'From: ' . $from . "\r\n" .
        'Reply-To: ' . $from . "\r\n" .
        'X-Mailer: TkLib PHP' . phpversion() . "\r\n";
    $msgSent = mail(
        $to,
        $subject,
        $message,
        $headers
    );

    return $msgSent;
}




/**
 * Create an array to work with the form list fields
 * Turn a single dimensional array into a two dimensional array.
 *
 * @param array $arr
 * @return array
 */
function createSelectArray($arr) {
    $new = array();
    foreach ($arr as $k => $v) {
        $new[] = array($k, $v);
    }
    return $new;
}

/**
 * Create a password hash using a random salt.
 *
 * @param string $str
 * @return string Format of '<hash>:<salt>'
 * @deprecated
 */
function tkPasswordHash($str)
{
    $salt = '';
    if (strpos($str, ':')) {
        $arr = explode(':', $str);
        $str = $arr[0];
        $salt = $arr[1];
    }
    $key = '!@}"#<($%]*[;$/?>.,)_+=-{^&';
    $length = 15;
    if (!$salt) {
        $salt = substr(md5(uniqid(\Tk\Math::rand(), true) . $key . microtime()), 0, $length);
    } else {
        $salt = substr($salt, 0, $length);
    }
    $hash = crypt($salt . $key . $str, '$5$rounds=3259$'.$salt.'$') . ':' . $salt;
    $arr = explode('$', $hash);
    return array_pop($arr);
}


/**
 *
 * Here's the solution I ended up using, which I've tested on the agents listed at
 * http://whatsmyuseragent.com/CommonUserAgents.asp It has the advantage of being
 * compact and reasonably easy to extend (just add entries to the $known array
 * defined at the top).  It should be fairly performant as well, since it doesn't
 * do any iteratoin or recursion.
 *
 * @param type $agent
 * @return type
 * @package Tk
 */
function browserInfo($agent = null)
{
    static $data = null;

    if (!$data) {
        // Declare known browsers to look for
        $known = array('msie', 'firefox', 'safari', 'webkit', 'opera', 'netscape',
            'konqueror', 'gecko');

        // Clean up agent and build regex that matches phrases for known browsers
        // (e.g. "Firefox/2.0" or "MSIE 6.0" (This only matches the major and minor
        // version numbers.  E.g. "2.0.0.6" is parsed as simply "2.0"
        $agent = strtolower($agent ? $agent : $_SERVER['HTTP_USER_AGENT']);
        $pattern = '#(?<browser>' . join('|', $known) .
            ')[/ ]+(?<version>[0-9]+(?:\.[0-9]+)?)#';

        // Find all phrases (or return empty array if none found)
        if (!preg_match_all($pattern, $agent, $matches)) return array();

        // Since some UAs have more than one phrase (e.g Firefox has a Gecko phrase,
        // Opera 7,8 have a MSIE phrase), use the last one found (the right-most one
        // in the UA).  That's usually the most correct.
        $i = count($matches['browser']) - 1;
        $data = array('name' => $matches['browser'][$i], 'version' => $matches['version'][$i]);
    }
    return $data;
}

/**
 * Get the mime type of a file based on its extension
 *
 * @param string $filename
 * @return string
 * @package Tk
 */
function getFileMimeType($filename)
{
    $mime_types = array('txt' => 'text/plain', 'htm' => 'text/html', 'html' => 'text/html', 'php' => 'text/html', 'css' => 'text/css', 'js' => 'application/javascript', 'json' => 'application/json', 'xml' => 'application/xml', 'swf' => 'application/x-shockwave-flash', 'flv' => 'video/x-flv',
        // images
        'png' => 'image/png', 'jpe' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'jpg' => 'image/jpeg', 'gif' => 'image/gif', 'bmp' => 'image/bmp', 'ico' => 'image/vnd.microsoft.icon', 'tiff' => 'image/tiff', 'tif' => 'image/tiff', 'svg' => 'image/svg+xml', 'svgz' => 'image/svg+xml',
        // archives
        'zip' => 'application/zip', 'rar' => 'application/x-rar-compressed', 'exe' => 'application/x-msdownload', 'msi' => 'application/x-msdownload', 'cab' => 'application/vnd.ms-cab-compressed',
        // audio/video
        'mp3' => 'audio/mpeg', 'qt' => 'video/quicktime', 'mov' => 'video/quicktime',
        // adobe
        'pdf' => 'application/pdf', 'psd' => 'image/vnd.adobe.photoshop', 'ai' => 'application/postscript', 'eps' => 'application/postscript', 'ps' => 'application/postscript',
        // ms office
        'doc' => 'application/msword', 'rtf' => 'application/rtf', 'xls' => 'application/vnd.ms-excel', 'ppt' => 'application/vnd.ms-powerpoint',
        // open office
        'odt' => 'application/vnd.oasis.opendocument.text', 'ods' => 'application/vnd.oasis.opendocument.spreadsheet');

    $extArr = explode('.', $filename);
    $ext = strtolower(array_pop($extArr));
    if (array_key_exists($ext, $mime_types)) {
        return $mime_types[$ext];
    } elseif (function_exists('finfo_open')) {
        $finfo = finfo_open(\FILEINFO_MIME);
        $mimetype = finfo_file($finfo, $filename);
        finfo_close($finfo);
        return $mimetype;
    } else {
        return 'application/octet-stream';
    }
}

/**
 * PHP Override to get the MIME type of a file
 *   if the function mime_content_type does not exsit
 * @package Tk
 */
if (!function_exists('mime_content_type')) {

    function mime_content_type($filename)
    {
        return getFileMimeType($filename);
    }

}


/**
 * Add a method get_called_class for PHP < 5.3
 *
 * NOTICE: When calling this function be sure that you only have one
 * function call per line to ensure you get teh correct class.
 *
 * This is just a poor replacement of the new 5.3 function not meant as a
 * substitute
 * @package Tk
 *
 */
if (!function_exists('get_called_class')) {

    /**
     * @param bool $bt
     * @param int $l
     * @return string
     * @throws Exception
     * @deprecated 2014-04-16 - Will be removed in next major version
     */
    function get_called_class($bt = false, $l = 1)
    {
        if (!$bt) {
            $bt = debug_backtrace();
        }
        if (!isset($bt[$l])) {
            throw new Exception("Cannot find called class -> stack level too deep.");
        }
        if (!isset($bt[$l]['type'])) {
            throw new Exception('type not set');
        } else {
            switch ($bt[$l]['type']) {
                case '::' :
                    $lines = file($bt[$l]['file']);
                    $i = 0;
                    $callerLine = '';
                    do {
                        $i++;
                        $callerLine = $lines[$bt[$l]['line'] - $i] . $callerLine;
                    } while (stripos($callerLine, $bt[$l]['function']) === false);
                    preg_match('/([a-zA-Z0-9\_]+)::' . $bt[$l]['function'] . '/', $callerLine, $matches);
                    if (!isset($matches[1])) {
                        // must be an edge case.
                        throw new Exception("Could not find caller class: originating method call is obscured.");
                    }
                    switch ($matches[1]) {
                        case 'self' :
                        case 'parent' :
                            return get_called_class($bt, $l + 1);
                        default :
                            return $matches[1];
                    }
                // won't get here.
                case '->' :
                    switch ($bt[$l]['function']) {
                        case '__get' :
                            // edge case -> get class of calling object
                            if (!is_object($bt[$l]['object']))
                                    throw new Exception("Edge case fail. __get called on non object.");
                            return get_class($bt[$l]['object']);
                        default :
                            return $bt[$l]['class'];
                    }
                default :
                    throw new Exception("Unknown backtrace method type");
            }
        }
    }

}






/************************** Setup PHP Environment **************************/

/*if (empty($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST']  = 'example.com';
}*/

/**
 *  This is a little hack for IIS with no $_SERVER['REQUEST_URI'] value
 */
if (empty($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);
    if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']) { $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING']; }
}
if (empty($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = '/';
}

/**
 * disable magic_quotes_gpc if enabled
 */
if (get_magic_quotes_gpc()) {
    /**
     * Disable magic quotes if enabled on the server
     */
    function magicQuotesGpc()
    {
        function traverse(&$arr)
        {
            if (!is_array($arr)) {
                return;
            }
            foreach ($arr as $key => $val) {
                is_array($arr[$key]) ? traverse($arr[$key]) : ($arr[$key] = stripslashes($arr[$key]));
            }
        }
        $gpc = array(&$_COOKIE, &$_REQUEST, &$_GET, &$_POST);
        traverse($gpc);
    }
    magicQuotesGpc();
}


/**
 * Fix IE submit key name
 * When a form contains an image submit. IE uses 'submit_x' and 'submit_y'
 * as the $_REQUEST key names. Here we add the value 'submit' to the request to fix this
 * issue.
 */
//if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0')) {
if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
    foreach ($_REQUEST as $key => $value) {
        if (substr($key, -2) == '_x' && !array_key_exists(substr($key, 0, -2), $_REQUEST)) {
            $newKey = substr($key, 0, -2);
            $_REQUEST[$newKey] = $value;
        }
    }
}











