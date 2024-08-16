<?php
namespace Tt;

class DbException extends \Exception
{

    protected string $dump = '';

    public function __construct(string $message = "", int|string $code = 0, string $dump = '', array|object|null $args = null)
    {
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
        $this->dump = $dump;

        parent::__construct($message, (int)$code);
    }

    public function __toString(): string
    {
        $str = parent::__toString();
        if ($this->dump != null) {
            $str .= $this->dump . "\n\n";
        }
        return $str;
    }

}
