<?php
namespace Tk\Db;

/**
 * Class Exception
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Exception extends \Tk\Exception
{

    /**
     * Exception constructor.
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     * @param string $dump
     * @param null $args
     */
    public function __construct($message = "", $code = 0, \Throwable $previous = null, $dump = '', $args = null)
    {
        //format dump query
        if ($dump) {
            $dump = explode("\n", str_replace(array(',', ' WHERE', ' FROM', ' LIMIT', ' ORDER', ' LEFT JOIN'),
                array(', ', "\n  WHERE", "\n  FROM", "\n  LIMIT", "\n  ORDER", "\n  LEFT JOIN"), $dump));
            foreach ($dump as $i => $s) {
                $dump[$i] = '  ' . wordwrap($s, 120, "\n  ");
            }
            $dump = "\n\nQuery: \n" . implode("\n", $dump);
        }
        if (is_array($args)) {
            $dump .= "\n\nBind: \n" . print_r($args, true);
        }

        parent::__construct($message, $code, $previous, $dump);
    }

}
