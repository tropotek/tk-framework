<?php

namespace Tt\Table;

use Tk\Exception;

class PhpRenderer extends TableRenderer
{

    public function getHtml(): string
    {
        // TODO: Implement getHtml() method.
        ob_start();
        include ($this->path);
        return ob_get_clean();
    }
}