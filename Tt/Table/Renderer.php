<?php

namespace Tt\Table;

use Tt\Table;

abstract class Renderer
{
    protected Table $table;

    /**
     * render a HTML string of the rendered table
     */
    abstract public function getHtml(array $rows): string;

    public function getTable(): Table
    {
        return $this->table;
    }

    public function setTable(Table $table): Renderer
    {
        $this->table = $table;
        return $this;
    }



}