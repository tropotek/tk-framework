<?php
namespace Tt\Table;

use Tt\Table;

abstract class TableRenderer extends Renderer
{
    // TODO: this needs to be configurable (maybe along with template paths?)
    const TABLE_JS = '/vendor/ttek/tk-framework/Tt/Table/templates/tkTable.js';

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
    protected array   $rows          = [];
    protected bool    $footerEnabled = true;

    public function __construct(Table $table, string $templatePath)
    {
        if (!is_file($templatePath)) {
            throw new \Exception("File not found: $templatePath");
        }
        $this->setTable($table);
        $this->path = $templatePath;
    }

    public function getRows(): ?array
    {
        return $this->rows;
    }

    public function setRows(array $rows, ?int $totalRows = null): static
    {
        if (!is_null($totalRows)) {
            $this->getTable()->setTotalRows($totalRows);
        }
        $this->rows = $rows;
        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function isFooterEnabled(): bool
    {
        return $this->footerEnabled;
    }

    public function setFooterEnabled(bool $footerEnabled): static
    {
        $this->footerEnabled = $footerEnabled;
        return $this;
    }

    public function addFooter(string $name, Renderer $renderer): static
    {
        $this->footer[$name] = $renderer;
        return $this;
    }

    public function getFooter(): array
    {
        return $this->footer;
    }

}