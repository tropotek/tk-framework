<?php
namespace Tt;

use Symfony\Component\HttpFoundation\Request;
use Tk\Collection;
use Tk\Str;
use Tk\Ui\Attributes;
use Tk\Ui\Traits\AttributesTrait;
use Tt\Table\Action;
use Tt\Table\Cell;

class Table
{
    use AttributesTrait;

    const PARAM_LIMIT    = 'limit';
    const PARAM_OFFSET   = 'offset';
    const PARAM_PAGE     = 'page';
    const PARAM_TOTAL    = 'total';
    const PARAM_ORDERBY  = 'orderBy';

    protected string     $id        = '';
    protected int        $limit     = 0;
    protected int        $page      = 1;
    protected string     $orderBy   = '';
    protected int        $totalRows = 0;

    protected Collection $cells;
    protected Collection $actions;
    protected Attributes $rowAttrs;
    protected Attributes $headerAttrs;


    public function __construct(string $tableId = 'tbl')
    {
        $this->rowAttrs    = new Attributes();
        $this->headerAttrs = new Attributes();
        $this->cells       = new Collection();
        $this->actions     = new Collection();
        $this->setId($tableId);
    }

    /**
     * Execute table actions
     */
    public function execute(Request $request): static
    {
        /* @var Action $action */
        foreach ($this->getActions() as $action) {
            $action->execute($request);
        }

        // get the order by value from the request (if any)
        $orderByKey = $this->makeRequestKey(self::PARAM_ORDERBY);
        if (!empty($request->query->get($orderByKey))) {
            $this->setOrderBy($request->query->get($orderByKey));
        }

        return $this;
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
        if ($instances[$id] > 0) $id = $instances[$id].$id;
        $this->id = $id;
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

    public function setRowAttrs(Attributes $rowAttrs): Table
    {
        $this->rowAttrs = $rowAttrs;
        return $this;
    }

    public function getHeaderAttrs(): Attributes
    {
        return $this->headerAttrs;
    }

    public function getTotalRows(): int
    {
        return $this->totalRows;
    }

    public function setTotalRows(int $totalRows): Table
    {
        $this->totalRows = ($totalRows < 0) ? 0 : $totalRows;
        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): Table
    {
        $this->limit = ($limit < 0) ? 0 : $limit;
        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): Table
    {
        $this->page = ($page < 1) ? 1 : $page;
        return $this;
    }

    public function getOffset(): int
    {
        return $this->getLimit() * ($this->getPage()-1);
    }

    public function getCells(): Collection
    {
        return $this->cells;
    }

    public function getCell(string $name): ?Cell
    {
        return $this->getCells()->get($name);
    }

    public function removeCell($cellName): static
    {
        $this->getCells()->remove($cellName);
        return $this;
    }

    public function appendCell(string|Cell $cell, ?string $refName = null): Cell
    {
        if (is_string($cell)) {
            $cell = new Cell($cell);
        }
        if ($this->getCells()->has($cell->getName())) {
            throw new \Exception("Cell with name '{$cell->getName()}' already exists.");
        }
        $cell->setTable($this);
        return $this->getCells()->append($cell->getName(), $cell, $refName);
    }

    public function prependCell(string|Cell $cell, ?string $refName = null): Cell
    {
        if (is_string($cell)) {
            $cell = new Cell($cell);
        }
        if ($this->getCells()->has($cell->getName())) {
            throw new \Exception("Cell with name '{$cell->getName()}' already exists.");
        }
        $cell->setTable($this);
        return $this->getCells()->prepend($cell->getName(), $cell, $refName);
    }

    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function getAction(string $name): ?Action
    {
        return $this->getActions()->get($name);
    }

    public function removeAction($actionName): static
    {
        $this->getActions()->remove($actionName);
        return $this;
    }

    public function appendAction(string|Action $action, ?string $refName = null): Action
    {
        if (is_string($action)) {
            $action = new Action($action);
        }
        if ($this->getActions()->has($action->getName())) {
            throw new \Tk\Table\Exception("Action with name '{$action->getName()}' already exists.");
        }
        $action->setTable($this);
        return $this->getActions()->append($action->getName(), $action, $refName);
    }

    public function prependAction(string|Action $action, ?string $refName = null): Action
    {
        if (is_string($action)) {
            $action = new Action($action);
        }
        if ($this->getActions()->has($action->getName())) {
            throw new \Tk\Table\Exception("Action with name '{$action->getName()}' already exists.");
        }
        $action->setTable($this);
        return $this->getActions()->prepend($action->getName(), $action, $refName);
    }

    /**
     * Create request key with prepended string
     * returns: `{id}_{$key}`
     */
    public function makeRequestKey($key): string
    {
        return $this->getId() . '_' . $key;
    }

}