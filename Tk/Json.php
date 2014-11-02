<?php
/**
 * Created by PhpStorm.
 * User: mifsudm
 * Date: 2/6/14
 * Time: 12:50 PM
 */

namespace Tk;

if (!defined('JSON_PRETTY_PRINT')) {
    define('JSON_PRETTY_PRINT', 128);
}

class Json
{

    /**
     * (PHP 5 &gt;= 5.2.0, PECL json &gt;= 1.2.0)<br/>
     * Returns the JSON representation of a value
     * @link http://php.net/manual/en/function.json-encode.php
     * @param mixed $value <p>
     * The <i>value</i> being encoded. Can be any type except
     * a resource.
     * </p>
     * <p>
     * This function only works with UTF-8 encoded data.
     * </p>
     * @param int $options [optional] <p>
     * Bitmask consisting of <b>JSON_HEX_QUOT</b>,
     * <b>JSON_HEX_TAG</b>,
     * <b>JSON_HEX_AMP</b>,
     * <b>JSON_HEX_APOS</b>,
     * <b>JSON_NUMERIC_CHECK</b>,
     * <b>JSON_PRETTY_PRINT</b>,
     * <b>JSON_UNESCAPED_SLASHES</b>,
     * <b>JSON_FORCE_OBJECT</b>,
     * <b>JSON_UNESCAPED_UNICODE</b>. The behaviour of these
     * constants is described on
     * the JSON constants page.
     * </p>
     *
     * @return string a JSON encoded string on success or <b>FALSE</b> on failure.
     */
    static function encode($value, $options = 0 )
    {
        $str = json_encode($value, $options);
        if (version_compare(PHP_VERSION, '5.4.0', '<') && ($options & JSON_PRETTY_PRINT) == JSON_PRETTY_PRINT) {
            $str = self::prettyPrint($str);
        }
        return $str;
    }

    /**
     * (PHP 5 &gt;= 5.2.0, PECL json &gt;= 1.2.0)<br/>
     * Decodes a JSON string
     * @link http://php.net/manual/en/function.json-decode.php
     * @param string $json <p>
     * The <i>json</i> string being decoded.
     * </p>
     * <p>
     * This function only works with UTF-8 encoded data.
     * </p>
     * @param bool $assoc [optional] <p>
     * When <b>TRUE</b>, returned objects will be converted into
     * associative arrays.
     * </p>
     * @param int $depth [optional] <p>
     * User specified recursion depth.
     * </p>
     * @param int $options [optional] <p>
     * Bitmask of JSON decode options. Currently only
     * <b>JSON_BIGINT_AS_STRING</b>
     * is supported (default is to cast large integers as floats)
     * </p>
     * @return mixed the value encoded in <i>json</i> in appropriate
     * PHP type. Values true, false and
     * null (case-insensitive) are returned as <b>TRUE</b>, <b>FALSE</b>
     * and <b>NULL</b> respectively. <b>NULL</b> is returned if the
     * <i>json</i> cannot be decoded or if the encoded
     * data is deeper than the recursion limit.
     */
    static function decode($json, $assoc = false, $depth = 512, $options = null)
    {
        $obj = json_decode($json, $assoc, $depth, $options);
        return $obj;
    }




    /**
     * Format a json string into a readable source
     *
     * @param $json
     * @param string $tab Default `  `
     * @return string
     */
    static function prettyPrint($json, $tab = '   ')
    {
        $tabcount = 0;
        $result = '';
        $inquote = false;
        $ignorenext = false;
        $newline = "\n";


        for ($i = 0; $i < strlen($json); $i++) {
            $char = $json[$i];

            if ($ignorenext) {
                $result .= $char;
                $ignorenext = false;
            } else {
                switch ($char) {
                    case ':':
                        $result .= $char . (!$inquote ? " " : "");
                        break;
                    case '{':
                        if (!$inquote) {
                            $tabcount++;
                            $result .= ' ' . $char . $newline . str_repeat($tab, $tabcount);
                        } else {
                            $result .= $char;
                        }
                        break;
                    case '}':
                        if (!$inquote) {
                            $tabcount--;
                            $result = trim($result) . $newline . str_repeat($tab, $tabcount) . $char;
                        } else {
                            $result .= $char;
                        }
                        break;
                    case '[':
                        if (!$inquote) {
                            $tabcount++;
                            $result .= ' ' . $char . $newline . str_repeat($tab, $tabcount);
                        } else {
                            $result .= $char;
                        }
                        break;
                    case ']':
                        if (!$inquote) {
                            $tabcount--;
                            $result = trim($result) . $newline . str_repeat($tab, $tabcount) . $char;
                        } else {
                            $result .= $char;
                        }
                        break;
                    case ',':
                        if (!$inquote) {
                            $result .= $char . $newline . str_repeat($tab, $tabcount);
                        } else {
                            $result .= $char;
                        }
                        break;
                    case '"':
                        $inquote = !$inquote;
                        $result .= $char;
                        break;
//                case '\\':
//                    if ($inquote) $ignorenext = true;
//                    $result .= $char;
//                    break;
                    default:
                        $result .= $char;
                }
            }
        }
        $result = str_replace('"_empty_": ', '"": ', $result);
        $result = str_replace('\/', '/', $result);

        return $result;
    }


}