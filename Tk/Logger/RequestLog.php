<?php
namespace Tk\Logger;

class RequestLog extends StreamLog
{

    public function __construct(string $filepath, string $level = self::DEBUG)
    {
        parent::__construct($filepath, $level);
        // clear log after each request
        if (is_file($filepath)) {
            file_put_contents($filepath, '');
        }
    }

}