<?php

/**
 * returns $s escaped for HTML output
 */
function e(mixed $s): string
{
    if (is_numeric($s)) return strval($s);
    if (empty($s)) return '';
    return htmlentities($s, ENT_QUOTES | ENT_HTML401, "UTF-8", false);
}

/**
 * returns $s cleaned for display in a <textarea>...</textarea>
 */
function tae(?string $s): string
{
    return strip_tags($s ?? '');
}

/**
 * returns $s cleaned for use as an HTML attribute value
 */
function eattr(mixed $s): string
{
    if (is_numeric($s)) return strval($s);
    if (empty($s)) return '';
    return htmlspecialchars($s, ENT_COMPAT | ENT_HTML401, "UTF-8", false);
}

/**
 * convert an array map to a string of HTML data attributes
 */
function data_attr(array $array): string
{
    $data = [];
    foreach($array as $k => $v) {
        $data[] = sprintf('data-%s="%s" ', $k, eattr($v));
    }
    return implode(' ', $data);
}

/**
 * returns true if parameter is truthy or any of y, yes, 1, ok, true (case-insensitive)
 * returns false otherwise
 */
function truefalse(mixed $val): bool
{
	$val = strval($val);
	if (empty($val)) return false;
	return boolval(preg_match('/^(y|yes|1|ok|true)$/i', $val));
}
