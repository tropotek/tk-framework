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

/**
 * replace accented characters with ASCII equivalents in a filename string
 */
function sanitize_filename(string|null $filename): string
{
	if (empty($filename)) return '';

	$chars = [
		'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
		'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
		'Ï'=>'I', 'Ñ'=>'N', 'Ń'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U',
		'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
		'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
		'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ń'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u',
		'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f',
		'ă'=>'a', 'ș'=>'s', 'ț'=>'t', 'Ă'=>'A', 'Ș'=>'S', 'Ț'=>'T',
	];

	// translated accented and unicode characters
	$filename = strtr($filename, $chars);
	setlocale(LC_ALL, "en_US.utf8");
	$filename = iconv('UTF-8', 'ASCII//TRANSLIT', $filename);
	if (!is_string($filename)) return "";

	$filename = preg_replace("/[^ '-~]+/", '_', $filename); // only ASCII printable chars
	$filename = preg_replace(
		"~
		[^a-zA-Z0-9 ;\-_.()']+ |           						# whitelist characters
		(^(CON|PRN|AUX|NUL|COM\d|LPT\d)(\.[a-zA-Z0-9]*)?$) 		# blacklist windows reserved words
		~xi",
		'_', $filename);
	$filename = preg_replace('/[ _]{2,}/', ' ', $filename); // no duplicate spaces or underscores, no space/underscore chains
	$filename = preg_replace('/[ ._]{2,}/', '.', $filename); // no spaces around dots, no dot chains (a..a)|(a._.a)
	$filename = trim($filename, ' ._-'); // avoids ".", ".." or ".hiddenFiles"

	$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION)); // lowercase extension
	// maximize filename length to 255 bytes http://serverfault.com/a/9548/44086
	$encoding = mb_detect_encoding($filename);
	if (is_string($encoding)) {
		$filename = mb_strcut(pathinfo($filename, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), $encoding) . ($ext ? '.' . $ext : '');
	} else {
		$filename = "";
	}

	return $filename ?: uniqid("upload_");
}

if (!function_exists('getallheaders')) {
    function getallheaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}


