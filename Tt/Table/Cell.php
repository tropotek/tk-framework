<?php

namespace Tt\Table;

use Tk\Ui\Attributes;
use Tk\Ui\Traits\AttributesTrait;
use Tt\Table;

class Cell
{
    use AttributesTrait;

    protected string     $name        = '';
    protected ?string    $value       = null;
    protected string     $header      = '';
    protected bool       $visible     = true;
    protected bool       $sortable    = false;
    protected ?Table     $table       = null;
    protected Attributes $headerAttrs;

    /**
     * an array of callable types, called with call_user_func_array()
     */
    protected array $onValue  = [];


    public function __construct(string $name, string $header = '')
    {
        $this->name = $name;
        $this->headerAttrs = new Attributes();
        $this->addCss('m'.ucfirst($name));
        $this->headerAttrs->addCss('mh'.ucfirst($name));

        if (!$header) {  // Set the default header label if none supplied
            $header = preg_replace('/(Id|_id)$/', '', $name);
            $header = str_replace(['_', '-'], ' ', $header);
            $header = ucwords(preg_replace('/[A-Z]/', ' $0', $header));
        }
        $this->setHeader($header);
    }

    public static function create(string $name, string $header = ''): static
    {
        return new static($name, $header);
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Called before the value is used, set this to change the table raw data value
     * Use this to set the cell properties before rendering
     *
     * Callback: function (array|object $row, Cell $cell) {  }
     */
    public function addOnValue(callable $callable): static
    {
        $this->onValue[] = $callable;
        return $this;
    }

    public function setOnValue(array $onValue): static
    {
        $this->onValue = $onValue;
        return $this;
    }

    public function getValue(array|object $row): mixed
    {
        if (!is_null($this->value)) return $this->value;
        if (is_array($row)) $row = (object)$row;
        $return = null;
        foreach ($this->onValue as $callable) {
            $r = call_user_func_array($callable, [$row, $this]);
            if (!is_null($r)) $return = $r;
        }
        if (!is_null($return)) return $return;

        return $row->{$this->getName()};
    }

    public function setValue(mixed $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function setHeader(string $header): static
    {
        $this->header = $header;
        return $this;
    }

    public function getHeader(): string
    {
        return $this->header;
    }

    public function getHeaderAttrs(): Attributes
    {
        return $this->headerAttrs;
    }

    public function setHeaderAttrs(Attributes $headerAttrs): static
    {
        $this->headerAttrs = $headerAttrs;
        return $this;
    }

    public function setVisible(bool $visible): static
    {
        $this->visible = $visible;
        return $this;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function setSortable(bool $sortable): static
    {
        $this->sortable = $sortable;
        return $this;
    }

    public function getTable(): ?Table
    {
        return $this->table;
    }

    public function setTable(?Table $table): Cell
    {
        $this->table = $table;
        return $this;
    }

}