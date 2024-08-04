<?php

namespace Tt;

use Tk\Ui\Attributes;
use Tk\Ui\Traits\AttributesTrait;
use Tt\Table\Cell;

class Table
{
    use AttributesTrait;

    protected string     $id      = '';
    protected array      $cells   = [];
    protected string     $orderBy = '';
    protected Attributes $rowAttrs;


    public function __construct(string $tableId = 'table')
    {
        $this->rowAttrs = new Attributes();
        $this->setId($tableId);
    }

    /**
     * ensure the id is unique
     */
    protected function setId($id): static
    {
        static $instances = [];
        if ($this->getId()) return $this;
        if (isset($instances[$id])) {
            $instances[$id]++;
        } else {
            $instances[$id] = 0;
        }
        if ($instances[$id] > 0) $id = $instances[$id].'_'.$id;
        $this->id = $id;
        $this->setAttr('id', $this->getId());
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOrderBy(): string
    {
        return $this->orderBy;
    }

    public function setOrderBy(string $orderBy): Table
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    public function getRowAttrs(): Attributes
    {
        return $this->rowAttrs;
    }

    public function getCells(): array
    {
        return $this->cells;
    }

    public function appendCell(string|Cell $cell, string $refCell = ''): Cell
    {
        if (is_string($cell)) {
            $cell = new Cell($cell);
        }
        if ($this->getCell($cell->getName())) {
            throw new \Exception("Cell with name '{$cell->getName()}' already exists.");
        }
        $cell->setTable($this);

        $ref = $this->getCell($refCell);
        if ($ref instanceof Cell) {
            $a = [];
            foreach ($this->cells as $k => $v) {
                $a[$k] = $v;
                if ($k === $refCell) $a[$cell->getName()] = $cell;
            }
            $this->cells = $a;
        } else {
            $this->cells[$cell->getName()] = $cell;
        }

        return $cell;
    }

    public function prependCell(string|Cell $cell, string $refCell = ''): Cell
    {
        if (is_string($cell)) {
            $cell = new Cell($cell);
        }
        if ($this->getCell($cell->getName())) {
            throw new \Exception("Cell with name '{$cell->getName()}' already exists.");
        }
        $cell->setTable($this);

        $ref = $this->getCell($refCell);
        if ($ref instanceof Cell) {
            $a = [];
            foreach ($this->cells as $k => $v) {
                if ($k === $refCell) $a[$cell->getName()] = $cell;
                $a[$k] = $v;
            }
            $this->cells = $a;
        } else {
            $this->cells = [$cell->getName() => $cell] + $this->cells;
        }

        return $cell;
    }

    public function removeCell($cellName): static
    {
        if ($this->cells[$cellName] ?? false) {
            unset($this->cells[$cellName]);
        }
        return $this;
    }

    public function getCell(string $name): ?Cell
    {
        return $this->cells[$name] ?? null;
    }

}