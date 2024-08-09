<?php
namespace Tt\Table;

use Tt\Table;

abstract class TableRenderer extends Renderer
{

    // max page links to how in the pager
    const MAX_PAGES = 10;

    const CSS_SELECTED = 'active';
    const CSS_DISABLED = 'disabled';

    const LIMIT_LIST   = [
        '-- All --' => 0,
        '10'  => 10,
        '25'  => 25,
        '50'  => 50,
        '100' => 100,
        '250' => 250,
    ];

    protected string  $path          = '';
    protected array   $footer        = [];
    protected bool    $footerEnabled = true;
    protected ?array  $rows          = null;

    public function __construct(Table $table, array $rows, string $templatePath)
    {
        if (!is_file($templatePath)) {
            throw new \Exception("File not found: $templatePath");
        }
        $this->setTable($table);
        $this->rows = $rows;
        $this->path = $templatePath;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function isFooterEnabled(): bool
    {
        return $this->footerEnabled;
    }

    public function setFooterEnabled(bool $footerEnabled): TableRenderer
    {
        $this->footerEnabled = $footerEnabled;
        return $this;
    }

    public function addFooter(string $name, Renderer $renderer): TableRenderer
    {
        $this->footer[$name] = $renderer;
        return $this;
    }

    public function getFooter(): array
    {
        return $this->footer;
    }

}