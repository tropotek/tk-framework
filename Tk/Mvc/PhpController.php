<?php

namespace Tk\Mvc;

use Symfony\Component\HttpFoundation\Request;
use Tk\Exception;
use Tk\Traits\SystemTrait;

/**
 * This controller os used to execute a php route
 */
class PhpController
{
    use SystemTrait;

    public function doDefault(Request $request): string
    {
        $path = $this->makePath($request->attributes->get('path'));
        if (!is_file($path)) {
            throw new Exception("File not found {$path}");
        }

        //extract($request->attributes->all(), EXTR_SKIP);
        ob_start();
        include $path;
        return ob_get_clean();
    }
}