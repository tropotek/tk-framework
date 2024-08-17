<?php

namespace Tk\Logger;

use Tk\FileUtil;

class RequestLog extends StreamLog
{

    public function __construct(string $filepath, string $level = self::DEBUG)
    {
        if (!is_dir(dirname($filepath))) {
            FileUtil::mkdir(dirname($filepath));
        }
        parent::__construct($filepath, $level);

        // clear log after each request
        file_put_contents($filepath, '');
    }

}