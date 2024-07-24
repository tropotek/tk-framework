<?php
namespace Tt;

class DbException extends \Tk\Exception
{

    public function __construct($message = "", int|string $code = 0, \Throwable $previous = null, $dump = '', $args = null)
    {
        //format dump query
        if ($dump) {
            $dump = explode("\n", str_replace([',', ' WHERE', ' FROM', ' LIMIT', ' ORDER', ' LEFT JOIN'],
                [', ', "\n  WHERE", "\n  FROM", "\n  LIMIT", "\n  ORDER", "\n  LEFT JOIN"], $dump));
            foreach ($dump as $i => $s) {
                $dump[$i] = '  ' . wordwrap($s, 120, "\n  ");
            }
            $dump = "\n\nQuery: \n" . implode("\n", $dump);
        }
        if (is_array($args)) {
            $dump .= "\n\nBind: \n" . print_r($args, true);
        }

        parent::__construct($message, (int)$code, $previous, $dump);
    }

}
