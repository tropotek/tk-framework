<?php
namespace Tt\Table\Cell;

use Tt\Table\Cell;

class RowSelect extends Cell
{
    protected string $property = '';

    public function __construct(string $name, string $property = '')
    {
        parent::__construct($name);
        $this->property = $property ?: $name;

        $this->addCss('text-center');
        $this->setHeader(sprintf('<input type="checkbox" name="%s_all" title="Select All" class="tk-tcb-head" />', $name));
    }

    public static function create(string $name, string $property = ''): static
    {
        return new static($name, $property);
    }

    public function getValue(array|object $row): string
    {
        if (is_array($row)) $row = (object)$row;
        $id = $row->{$this->getProperty()} ?? '';
        return sprintf('<input type="checkbox" name="%s[]" value="%s" class="tk-tcb"/>', $this->getName(), e($id));
    }

    public function getProperty(): string
    {
        return $this->property;
    }

}