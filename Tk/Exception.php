<?php
namespace Tk;

class Exception extends \Exception
{

    protected string $dump = '';

    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null, string $dump = '')
    {
        parent::__construct($message, $code, $previous);
        $this->dump = $dump;
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

class WarningException              extends Exception {}
class ParseException                extends Exception {}
class NoticeException               extends Exception {}
class CoreErrorException            extends Exception {}
class CoreWarningException          extends Exception {}
class CompileErrorException         extends Exception {}
class CompileWarningException       extends Exception {}
class UserErrorException            extends Exception {}
class UserWarningException          extends Exception {}
class UserNoticeException           extends Exception {}
class StrictException               extends Exception {}
class RecoverableErrorException     extends Exception {}
class DeprecatedException           extends Exception {}
class UserDeprecatedException       extends Exception {}

