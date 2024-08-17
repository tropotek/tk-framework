<?php
namespace Tt\Table\Action;

use Tk\CallbackCollection;
use Tk\Uri;
use Tt\Table\Action;
use Tt\Table\Cell;
use Tt\Table\Cell\RowSelect;

/**
 *
 * NOTE: This Action does not call the onExecute() or onShow() callback queues
 */
class Csv extends Action
{
    const EXCLUDED_CELLS = [
        RowSelect::class,
    ];

    protected string    $confirmStr = 'Export selected records to CSV?';
    protected string    $filename = '';
    protected array     $excluded = [];
    protected RowSelect $rowSelect;
    protected CallbackCollection $onCsv;


    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->onCsv = CallbackCollection::create();
    }

    public static function create(RowSelect $rowSelect, string $name = 'export'): static
    {
        $obj = new static($name);
        $obj->rowSelect = $rowSelect;
        return $obj;
    }

    public function execute(): void
    {
        $val = $this->getTable()->makeRequestKey($this->getName());
        $this->setActive(($_POST[$this->getName()] ?? '') == $val);
        if (!$this->isActive()) return;

        $selected = $_POST[$this->rowSelect->getName()] ?? [];
        $rows = $this->getOnCsv()->execute($this, $selected);
        if (!count($rows)) {
            Uri::create()->redirect();
        }

        $filename = $this->getTable()->getId() . '_' . date('Ymd') . '.csv';
        if ($this->getFilename()) {
            $filename = $this->getFilename() . '_' . date('Ymd') . '.csv';
        }

        // Output the CSV data
        $out = fopen('php://output', 'w');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');

        $arr = [];
        // Write cell labels to first line of csv...
            /* @var $cell Cell */
        foreach ($this->getTable()->getCells() as $cell) {
            if ($this->isExcluded($cell)) continue;
            $arr[] = $cell->getHeader();
        }
        fputcsv($out, $arr);

        foreach ($rows as $i => $row) {
            $csvData = [];
            /* @var $cell Cell */
            foreach ($this->getTable()->getCells() as $cell) {
                if ($this->isExcluded($cell)) continue;
                $csvData[$cell->getName()] = $cell->getValue($row);
            }
            fputcsv($out, $csvData);
        }

        fclose($out);
        exit;



    }

    public function getHtml(): string
    {
        $val = $this->getTable()->makeRequestKey($this->getName());

        return <<<HTML
<button type="submit" name="{$this->getName()}" value="{$val}" class="tk-action-csv btn btn-sm btn-light"
    title="Export Records"
    data-confirm="{$this->getConfirmStr()}"
    data-row-select="{$this->rowSelect->getName()}">
    <i class="fa fa-fw fa-list-alt"></i> {$this->getLabel()}
</button>
HTML;
    }

    protected function getConfirmStr(): string
    {
        return $this->confirmStr;
    }

    public function setConfirmStr(string $confirmStr): static
    {
        $this->confirmStr = $confirmStr;
        return $this;
    }

    /**
     * @callable function (\Tk\Table\Action\Delete $action, $obj): ?bool { }
     */
    public function addOnCsv(callable $callable, int $priority = CallbackCollection::DEFAULT_PRIORITY): static
    {
        $this->getOnCsv()->append($callable, $priority);
        return $this;
    }

    public function getOnCsv(): CallbackCollection
    {
        return $this->onCsv;
    }

    public function getExcluded(): array
    {
        return $this->excluded;
    }

    /**
     * An array of cell names to exclude from the CSV data
     */
    public function setExcluded(array $excluded): Csv
    {
        $this->excluded = $excluded;
        return $this;
    }

    private function isExcluded(Cell $cell): bool
    {
        if (in_array(get_class($cell), self::EXCLUDED_CELLS)) return true;
        return in_array($cell->getName(), $this->excluded);
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): Csv
    {
        $this->filename = $filename;
        return $this;
    }

}