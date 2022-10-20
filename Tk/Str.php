<?php
namespace Tk;

/**
 * An object filled with string utility methods.
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Str
{

    /**
     * Strip tag attributes and their values from html
     * By default the $attrs contains tag events
     */
    public static function stripAttrs(string $str, ?array $attrs = null): string
    {
        if ($attrs === null)
            $attrs = ['onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy',
                'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload',
                'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu',
                'oncontrolselect', 'oncopy', 'oncut', 'ondataavaible', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick',
                'ondeactivate', 'ondrag', 'ondragdrop', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart',
                'ondrop', 'onerror', 'onerrorupdate', 'onfilterupdate', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout',
                'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown',
                'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseup', 'onmousedown', 'onmoveout', 'onmouseover', 'onmouseup', 'onmousewheel',
                'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset',
                'onresize', 'onresizeend', 'onresizestart', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll',
                'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload'];

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

    public static function stripStyles(string $html, array $tags = ['table', 'th', 'tr', 'td', 'tbody', 'thead'], array $styles = ['height', 'width']): string
    {
        foreach ($styles as $style) {
            $reg = sprintf('/(<%s)(.*)(%s: [0-9a-z]+;)/i', implode('|', $tags), $style);
            $html = preg_replace($reg, '$1$2', $html);
        }
        return $html;
    }

    /**
     * prepend each line with an index number
     */
    public static function lineNumbers(string $str): string
    {
        $lines = explode("\n", $str);
        foreach ($lines as $i => $line) {
            $lines[$i] = ($i+1) . '  ' . $line;
        }
        return implode("\n", $lines);
    }

    /**
     * Return the string with the first character lowercase
     */
    public static function lcFirst(string $str): string
    {
        return strtolower($str[0]) . substr($str, 1);
    }

    /**
     * Convert to CamelCase so "test_func_name" would convert to "testFuncName"
     * Adds a capital at the first char and ass a space before all other upper case chars
     */
    public static function toCamel(string $str): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $str))));
    }

    /**
     * Convert to snake Case so "testFuncName" would convert to "test_func_name"
     */
    public static function toSnake(string $str, $ch = '_'): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]+|(?<!^|\d)[\d]+/', $ch.'$0', $str));
    }

    /**
     * Convert camel case to words "testFunc" => "Test Func"
     *
     * @param string $str
     * @return string
     */
    public static function camel2words($str)
    {
        return ucfirst(preg_replace('/[A-Z]/', ' $0', $str));
    }

    /**
     * Replace any double control/linefeed characters with a single character
     */
    public static function singleNewLines(string $str, string $replace = "\n"): string
    {
        return preg_replace('~(*BSR_ANYCRLF)\R{2}~', $replace, $str);
    }

    /**
     * Explode using multiple delimiters
     *
     * @return false|string[]|array
     */
    public static function explode(array $delimiters, string $string)
    {
        return explode( chr( 1 ), str_replace( $delimiters, chr( 1 ), $string ) );
    }

    /**
     * trim all strings in an array
     */
    public static function trimArray(array $arr): array
    {
        return array_map('trim', $arr);
    }

    /**
     * Substring without cutting a word boundry
     * tiny words (length 3 by default) are included on the result.
     * "..." is added if result do not reach original string length
     *
     * @return string
     * @todo Lookup a more refined way of doing this
     */
    public static function wordcat(string $str, int $length, string $endStr = '', int $minword = 3): string
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
     */
    public static function strcat(string $str, int $length, string $suffix = '...'): string
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
     */
    public static function getByteSize(string $str): int
    {
        // STRINGS ARE EXPECTED TO BE IN ASCII OR UTF-8 FORMAT
        // Number of characters in string
        $strlen_var = strlen($str);
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
    public static function stripEntities(string $str, string $replacement = '')
    {
        return preg_replace('/&#?[a-z0-9]+;/i',$replacement, $str);
    }

    /**
     * Convert html special characters to numeric entities (eg: &nbsp; to &#160;)
     */
    public static function numericEntities(string $xml): string
    {
        $list = get_html_translation_table(\HTML_ENTITIES, ENT_NOQUOTES);
        $mapping = [];
        foreach ($list as $char => $entity) {
            $mapping[strtolower($entity)] = '&#' . self::ord($char) . ';';
        }
        $xml = str_replace(array_keys($mapping), $mapping, $xml);
        return $xml;
    }

    /**
     * Since PHP's ord() function is not compatible with UTF-8
     * Here is a workaround.... GGRRR!!!!
     */
    public static function ord(string $ch): int
    {
        $k = mb_convert_encoding($ch, 'UCS-2LE', 'UTF-8');
        $k1 = ord(substr($k, 0, 1));
        $k2 = ord(substr($k, 1, 1));
        return $k2 * 256 + $k1;
    }

    /**
     * Test if a string is UTF-8 encoded
     * @todo: Test this is working correctly
     */
    public static function isUtf8(string $string): bool
    {
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
     * @param mixed $var
     */
    public static function varToString($var): string
    {
        if (is_object($var)) {
            return sprintf('Object(%s)', get_class($var));
        }
        if (is_array($var)) {
            $a = [];
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
     * Is the string an HTML/XML string
     */
    public static function isHtml(string $str): bool
    {
        return (strlen($str) != strlen(strip_tags($str)));
    }

}
