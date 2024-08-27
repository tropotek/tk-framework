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
class Csv extends Select
{
    const EXCLUDED_CELLS = [
        RowSelect::class,
    ];

    protected string    $filename = '';
    protected array     $excluded = [];


    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->setAttr('title', 'Export Records');
        $this->removeAttr('disabled');
    }

    public static function create(RowSelect $rowSelect, string $name = 'export', $icon = 'fa fa-fw fa-list-alt'): static
    {
        $obj = parent::create($rowSelect, $name, $icon);
        $obj->setConfirmStr('Export selected records to CSV?');
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

    /**
     * @callable function (\Tk\Table\Action\Delete $action, $obj): ?bool { }
     */
    public function addOnCsv(callable $callable, int $priority = CallbackCollection::DEFAULT_PRIORITY): static
    {
        $this->getOnSelect()->append($callable, $priority);
        return $this;
    }

    public function getOnCsv(): CallbackCollection
    {
        return $this->getOnSelect();
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