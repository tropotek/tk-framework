<?php
namespace Tk;

/**
 * An object filled with string utility methods.
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Str
{

    /**
     * Strip tag attributes and their values from html
     * By default the $attrs contains tag events
     *
     * @param string $str
     * @param array $attrs Eg: array('onclick', 'onmouseup', 'onmousedown', ...);
     * @return string
     */
    public static function stripAttrs($str, $attrs = null)
    {
        if ($attrs === null)
            $attrs = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy',
                'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload',
                'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu',
                'oncontrolselect', 'oncopy', 'oncut', 'ondataavaible', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick',
                'ondeactivate', 'ondrag', 'ondragdrop', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart',
                'ondrop', 'onerror', 'onerrorupdate', 'onfilterupdate', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout',
                'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown',
                'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseup', 'onmousedown', 'onmoveout', 'onmouseover', 'onmouseup', 'onmousewheel',
                'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset',
                'onresize', 'onresizeend', 'onresizestart', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll',
                'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');

        if (!is_array($attrs))
            $attrs = explode(",", $attrs);

        foreach ($attrs as $at) {
            $reg = "/(<.*)( $at=\"([^\".]*)\")(.*>)/i";
            while (preg_match($reg, $str)) {
                $str = preg_replace($reg, '$1$4', $str);
            }
        }
        return $str;
    }

    /**
     * @param string $html
     * @param array $tags
     * @param array $styles
     * @return string
     */
    public static function stripStyles($html, $tags = array('table', 'th', 'tr', 'td', 'tbody', 'thead'), $styles = array('height', 'width'))
    {
        foreach ($styles as $style) {
            $reg = sprintf('/(<%s)(.*)(%s: [0-9a-z]+;)/i', implode('|', $tags), $style);
            $html = preg_replace($reg, '$1$2', $html);
        }
        return $html;
    }

    /**
     * prepend each line with an index number
     *
     * @param $str
     * @return string
     */
    public static function lineNumbers($str)
    {
        $lines = explode("\n", $str);
        foreach ($lines as $i => $line) {
            $lines[$i] = ($i+1) . '  ' . $line;
        }
        return implode("\n", $lines);
    }

    /**
     * Return the string with the first character lowercase
     *
     * @param $str
     * @return string
     */
    public static function lcFirst($str)
    {
        return strtolower($str[0]) . substr($str, 1);
    }

    /**
     * Convert to CamelCase so "Test FuncName" would convert to "testFuncName"
     * Adds a capital at the first char and ass a space before all other upper case chars
     *
     * @param string $str
     * @return string
     */
    public static function toCamelCase($str)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $str))));
    }

    /**
     * Convert camel case words so "testFunc" would convert to "Test Func"
     * Adds a capital at the first char and ass a space before all other upper case chars
     *
     * @param string $str
     * @return string
     */
    public static function ucSplit($str)
    {
        return ucfirst(preg_replace('/[A-Z]/', ' $0', $str));
    }

    /**
     * Replace any double control/linefeed characters with a single
     * character
     *
     * @param string $str
     * @param string $replace (optional) The newline replacement string
     * @return string
     */
    public static function singleNewLines($str, $replace = "\n")
    {
        //return preg_replace('~(*BSR_ANYCRLF)\R~', $replace, $str);
        return preg_replace('~(*BSR_ANYCRLF)\R{2}~', $replace, $str);
    }


    /**
     * Explode using multiple delimiters
     *
     * @param array $delimiters
     * @param string $string
     * @return false|string[]
     */
    public static function explode(array $delimiters, string $string)
    {
        return explode( chr( 1 ), str_replace( $delimiters, chr( 1 ), $string ) );
    }

    /**
     * @param string[] $arr
     * @return string[]
     */
    public static function trimArray(array $arr)
    {
        $a = array();
        foreach ($arr as $k => $v) {
            if ($v == null || trim($v) == '') continue;
            $a[$k] = trim($v);
        }
        return $a;
    }


    /**
     * Substring without losing word meaning and
     * tiny words (length 3 by default) are included on the result.
     * "..." is added if result do not reach original string length
     *
     * @param string $str
     * @param integer $length
     * @param string $endStr
     * @param integer $minword
     * @return string
     */
    public static function wordcat($str, $length, $endStr = '', $minword = 3)
    {
        if ($length < 1) return $str;
        if (!$str) {
            return $str;
        }
        $sub = '';
        $len = 0;

        if (count(explode(' ', $str)) == 1)
            return self::strcat($str, $length, $endStr);

        foreach (explode(' ', $str) as $word) {
            $part = (($sub != '') ? ' ' : '') . $word;
            $sub .= $part;
            $len += strlen($part);
            if (strlen($word) > $minword && strlen($sub) >= $length) {
                break;
            }
        }

        return $sub . (($len < strlen($str)) ? $endStr : '');
    }

    /**
     * concatenate a sting and add a suffix to the end if it is concatenated.
     *
     * @param string $str
     * @param int $length
     * @param string $suffix
     * @return string
     */
    public static function strcat($str, $length, $suffix = '...')
    {
        if (strlen($str) > $length) {
            $str = substr($str, 0, $length) . $suffix;
        }
        return $str;
    }

    /**
     * Count the number of bytes of a given string.
     * Input string is expected to be ASCII or UTF-8 encoded.
     * Warning: the function doesn't return the number of chars
     * in the string, but the number of bytes.
     * See http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
     * for information on UTF-8.
     *
     * @param string $str The string to compute number of bytes
     * @return integer The length in bytes of the given string.
     */
    public static function strByteSize($str)
    {
        // STRINGS ARE EXPECTED TO BE IN ASCII OR UTF-8 FORMAT
        // Number of characters in string
        $strlen_var = strlen($str);

        // string bytes counter
        $d = 0;

        /*
         * Iterate over every character in the string,
         * escaping with a slash or encoding to UTF-8 where necessary
         */
        for($c = 0; $c < $strlen_var; ++$c) {
            $ord_var_c = ord($str[$c]);
            switch (true) {
                case (($ord_var_c >= 0x20) && ($ord_var_c <= 0x7F)) :
                    // characters U-00000000 - U-0000007F (same as ASCII)
                    $d++;
                    break;
                case (($ord_var_c & 0xE0) == 0xC0) :
                    // characters U-00000080 - U-000007FF, mask 110XXXXX
                    $d += 2;
                    break;
                case (($ord_var_c & 0xF0) == 0xE0) :
                    // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                    $d += 3;
                    break;
                case (($ord_var_c & 0xF8) == 0xF0) :
                    // characters U-00010000 - U-001FFFFF, mask 11110XXX
                    $d += 4;
                    break;
                case (($ord_var_c & 0xFC) == 0xF8) :
                    // characters U-00200000 - U-03FFFFFF, mask 111110XX
                    $d += 5;
                    break;
                case (($ord_var_c & 0xFE) == 0xFC) :
                    // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                    $d += 6;
                    break;
                default :
                    $d++;
            }
        }
        return $d;
    }

    /**
     * @param string $str
     * @param string $replacement
     * @return string|string[]|null
     */
    public static function stripEntities($str, $replacement = '')
    {
        return preg_replace('/&#?[a-z0-9]+;/i',$replacement, $str);
    }

    /**
     * Convert html special characters to nemeric entities (eg: &nbsp; to &#160;)
     * Usefull for XML encoding strings
     *
     * @param string $xml
     * @return string
         */
    public static function numericEntities($xml)
    {
        $list = get_html_translation_table(\HTML_ENTITIES, ENT_NOQUOTES);
        $mapping = array();
        foreach ($list as $char => $entity) {
            $mapping[strtolower($entity)] = '&#' . self::ord($char) . ';';
        }
        $xml = str_replace(array_keys($mapping), $mapping, $xml);
        return $xml;
    }

    /**
     * Since PHP's ord() function is not compatible with UTF-8
     * Here is a workaround.... GGRRR!!!!
     *
     * @param string $ch
     * @return integer
     */
    public static function ord($ch)
    {
        $k = mb_convert_encoding($ch, 'UCS-2LE', 'UTF-8');
        $k1 = ord(substr($k, 0, 1));
        $k2 = ord(substr($k, 1, 1));
        return $k2 * 256 + $k1;
    }

    /**
     * Test if a string is UTF-8 encoded
     *
     * @param string $string
     * @todo: Test this is working correctly
     * @return bool
     */
    public static function isUtf8($string) { // v1.01
        $_is_utf8_split = 5000;
        if (strlen($string) > $_is_utf8_split) {
            // Based on: http://mobile-website.mobi/php-utf8-vs-iso-8859-1-59
            for ($i=0,$s=$_is_utf8_split,$j=ceil(strlen($string)/$_is_utf8_split);$i < $j;$i++,$s+=$_is_utf8_split) {
                if (self::isUtf8(substr($string,$s,$_is_utf8_split)))
                    return true;
            }
            return false;
        }
        // From http://w3.org/International/questions/qa-forms-utf-8.html
        return preg_match('%^(?:
            [\x09\x0A\x0D\x20-\x7E]            # ASCII
          | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
          |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
          | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
          |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
          |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
          | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
          |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
        )*$%xs', $string);

    }

    /**
     * varToString
     *
     * @param $var
     * @return string
     */
    public static function varToString($var)
    {
        if (is_object($var)) {
            return sprintf('Object(%s)', get_class($var));
        }
        if (is_array($var)) {
            $a = array();
            foreach ($var as $k => $v) {
                $a[] = sprintf('%s => %s', $k, self::varToString($v));
            }
            return sprintf('Array(%s)', implode(', ', $a));
        }
        if (is_resource($var)) {
            return sprintf('Resource(%s)', get_resource_type($var));
        }
        if (null === $var) {
            return 'null';
        }
        if (false === $var) {
            return 'false';
        }
        if (true === $var) {
            return 'true';
        }
        return (string) $var;
    }

    /**
     * Is the string a HTML string
     *
     * @param $str
     * @return bool
     */
    public static function isHtml($str)
    {
        return (strlen($str) != strlen(strip_tags($str)));
    }

}
