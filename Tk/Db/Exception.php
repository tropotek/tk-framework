<?php
namespace Tk\Db;

use Tk\Db;

class Exception extends \Tk\Exception
{
    protected string $dump = '';

    public function __construct(string $message = "", int|string $code = 0, string $dump = '', array|object|null $args = null)
    {
        parent::__construct($message, (int)$code);

        if ($dump) {
            $dump = explode("\n", $dump);
            foreach ($dump as $i => $s) {
                $dump[$i] = '  ' . wordwrap(trim($s), 120, "\n  ");
            }
            $dump = "\n\nQuery: \n" . implode("\n", $dump);
        }
        if (is_array($args)) {
            $dump .= "\n\nBind: \n" . print_r($args, true);
        } else {
            $stm = Db::getLastStatement();
            if ($stm) {
                $dump .= "\n\nBind: \n" . print_r($stm->getLastParams(), true);
            }
        }
        $this->dump = $dump;

    }

}
