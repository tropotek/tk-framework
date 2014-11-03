<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */


/**
 * Seperate capital letters and add a white space:
 * EG:
 *   `ThisIsASentance` => `This Is A Sentance`
 *
 * @param string $str
 * @return string
 */
function addSpace($str)
{
    return preg_replace('/[A-Z]/', ' $0', $str);
}


/**
 *
 * @param string $url
 * @param bool $removeWww remove the www.|ftp.|mail. part of the domain
 * @return type
 */
function stripDomain($url, $removeWww = true)
{
    $host = $url;
    $info = parse_url($url);
    if (isset($info['host'])) {
        $host = $info['host'];
    }
    if ($removeWww) {
        $host = str_replace(array('www.', 'ftp.', 'smtp.', 'mail.', 'pop.', 'webmail.'), '', $host);
    }
    return $host;
}



/**
 * return the class name after removeing the namespace:
 * EG: \Tk\Url = Url
 *      \Tk\Url = Url
 *
 * @param string|object $className
 * @return string
 */
function removeNamespace($className)
{
    if (is_object($className)) $className = get_class ($className);
    $arr = explode('_', $className);
    if (strpos($className, '\\') !== false) {
        $arr = explode('\\', $className);
    }
    return array_pop($arr);
}


/**
 * Remove all accented characters from a string
 * and replace them with their unaccnted equivelents
 *
 * @param string $str
 * @return string
 * @package Tk
 */
function clearAccents($str)
{
  setlocale(LC_ALL, "en_US.utf8");
  return iconv("utf-8", "us-ascii//TRANSLIT//IGNORE", $str);
}

/**
 * Surround a string by quotation marks. Single quote by default
 *
 * @param string $str
 * @param string $quote
 * @return string
 * @package Tk
 * @deprecated 2014-04-16 - Remove in next major version
 */
function enquote($str, $quote = "'")
{
    return $quote . $str . $quote;
}

/**
 * Return the string with the first character lowercased
 *
 * @param string $str
 * @return string
 * @package Tk
 */
if (!function_exists('lcfirst')) {
    function lcfirst($str)
    {
        return (string)(strtolower($str[0]).substr($str,1));
    }
}

/**
 * Convert camele case words so "testFunc" would convert to "Test Func"
 * Adds a capital at the first char and ass a space before all other upper case chars
 *
 * @param $str
 * @return string
 * @package Tk
 */
function ucSplit($str)
{
    return ucfirst(preg_replace('/[A-Z]/', ' $0', $str));
}

/**
 * @param $haystack
 * @param $needle
 * @return bool
 */
function startsWith($haystack, $needle)
{
    return $needle === "" || strpos($haystack, $needle) === 0;
}

/**
 * @param $haystack
 * @param $needle
 * @return bool
 */
function endsWith($haystack, $needle)
{
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

/**
 * Substring without losing word meaning and
 * tiny words (length 3 by default) are included on the result.
 * "..." is added if result do not reach original string length
 *
 * UPDATE: Css has a feature to do a similar thing, use .class { overflow: hidden; width: 160px; text-overflow: ellipsis; }
 *   this will add `...` if the text is to overflow the container. This may be a bettor option
 *   for text so that another design can show the entire text, if it has space..
 *
 * @param string $str
 * @param int $length
 * @param string $endStr
 * @param int $minword
 * @return string
 * @package Tk
 */
function wordcat($str, $length, $endStr = '', $minword = 3)
{
    if (!$str) {
        return $str;
    }
    $sub = '';
    $len = 0;

    foreach (explode(' ', $str) as $word)
    {
        $part = (($sub != '') ? ' ' : '') . $word;
        $sub .= $part;
        $len += strlen($part);
        if (strlen($word) > $minword && strlen($sub) >= $length)
        {
            break;
        }
    }
    if (count(explode(' ', $str)) == 1) {
        $sub = substr($str, 0, $length);
    }
    return $sub . (($length < strlen($str)) ? $endStr : '');
}


/**
 * Strip tag attributes and their values from html
 * By default the $attrs contains tag events
 *
 * @param string $str
 * @param array $attrs Eg: array('onclick', 'onmouseup', 'onmousedown', ...);
 * @return mixed|string
 * @package Tk
 */
function stripAttrs($str, $attrs = null)
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
 * Convert html special characters to nemeric entities (eg: &nbsp; to &#160;)
 * Usefull for XML encoding strings
 *
 * @param string $xml
 * @return string
 * @package Tk
 */
function numericEntities($xml)
{
    $list = get_html_translation_table(HTML_ENTITIES, ENT_NOQUOTES, 'UTF-8');
        $mapping = array();
        foreach ($list as $char => $entity) {
            $mapping[strtolower($entity)] = '&#' . tkOrd($char) . ';';
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
function tkOrd($ch)
{
    $k = mb_convert_encoding($ch, 'UCS-2LE', 'UTF-8');
    $k1 = ord(substr($k, 0, 1));
    $k2 = ord(substr($k, 1, 1));
    return $k2 * 256 + $k1;
}



/**
 * Converts all accent characters to ASCII characters.
 *
 * If there are no accent characters, then the string given is just returned.
 *
 * @param string $string Text that might have accent characters
 * @return string Filtered string with replaced "nice" characters.
 * @package Tk
 */
function remove_accents($string) {
    if ( !preg_match('/[\x80-\xff]/', $string) )
        return $string;

    if (isUtf8($string)) {
        $chars = array(
        // Decompositions for Latin-1 Supplement
        chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
        chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
        chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
        chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
        chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
        chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
        chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
        chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
        chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
        chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
        chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
        chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
        chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
        chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
        chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
        chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
        chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
        chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
        chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
        chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
        chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
        chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
        chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
        chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
        chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
        chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
        chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
        chr(195).chr(191) => 'y',
        // Decompositions for Latin Extended-A
        chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
        chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
        chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
        chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
        chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
        chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
        chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
        chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
        chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
        chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
        chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
        chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
        chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
        chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
        chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
        chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
        chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
        chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
        chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
        chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
        chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
        chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
        chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
        chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
        chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
        chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
        chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
        chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
        chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
        chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
        chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
        chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
        chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
        chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
        chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
        chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
        chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
        chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
        chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
        chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
        chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
        chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
        chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
        chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
        chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
        chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
        chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
        chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
        chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
        chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
        chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
        chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
        chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
        chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
        chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
        chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
        chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
        chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
        chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
        chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
        chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
        chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
        chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
        chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
        // Euro Sign
        chr(226).chr(130).chr(172) => 'E',
        // GBP (Pound) Sign
        chr(194).chr(163) => '');

        $string = strtr($string, $chars);
    } else {
        // Assume ISO-8859-1 if not UTF-8
        $chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
            .chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
            .chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
            .chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
            .chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
            .chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
            .chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
            .chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
            .chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
            .chr(252).chr(253).chr(255);

        $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

        $string = strtr($string, $chars['in'], $chars['out']);
        $double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
        $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
        $string = str_replace($double_chars['in'], $double_chars['out'], $string);
    }

    return $string;
}

/**
 * Test if a string is UTF-8 encoded
 *
 * @param string $string
 * @return bool|int
 * @package Tk
 */
function isUtf8($string) { // v1.01
    $_is_utf8_split = 5000;
    if (strlen($string) > $_is_utf8_split) {
        // Based on: http://mobile-website.mobi/php-utf8-vs-iso-8859-1-59
        for ($i=0,$s=$_is_utf8_split,$j=ceil(strlen($string)/$_is_utf8_split);$i < $j;$i++,$s+=$_is_utf8_split) {
            if (isUtf8(substr($string,$s,$_is_utf8_split)))
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
 * Count the number of bytes of a given string.
 * Input string is expected to be ASCII or UTF-8 encoded.
 * Warning: the function doesn't return the number of chars
 * in the string, but the number of bytes.
 * See http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
 * for information on UTF-8.
 *
 * @param string $str The string to compute number of bytes
 * @return int The length in bytes of the given string.
 * @package Tk
 */
function str2bytes($str)
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
        };
    };
    return $d;
}


