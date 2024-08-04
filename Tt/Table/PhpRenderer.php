<?php

namespace Tt\Table;

use Tt\Table;

class PhpRenderer extends Renderer
{
    protected string $path = '';

    public function __construct(Table $table, string $templatePath)
    {
        $this->table = $table;
        if (!is_file($templatePath)) {
            throw new \Exception("File not found: $templatePath");
        }
        $this->path = $templatePath;
    }

    public function getHtml(array $rows): string
    {
        // TODO: Implement getHtml() method.
        ob_start();
        include ($this->path);
        return ob_get_clean();
    }
}