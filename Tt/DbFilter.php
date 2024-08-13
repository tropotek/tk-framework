<?php
namespace Tt;

use Tk\Str;

class DbFilter extends \Tk\Collection
{

    protected string $orderBy   = '';
    protected ?int   $limit     = null;
    protected ?int   $offset    = null;
    protected string $where     = '';


    public static function create(null|array|DbFilter $params, string $orderBy = '', ?int $limit = null, ?int $offset = null): static
    {
        if ($params instanceof DbFilter) return $params;
        $obj = new self();
        $obj->orderBy = preg_replace('/[^a-z0-9, _-]/', '', $orderBy);
        $obj->limit = $limit;
        $obj->offset = $offset;
        $obj->replace($params);
        return $obj;
    }

    public static function createFromTable(null|array|DbFilter $params, Table $table): static
    {
        return static::create($params, $table->getOrderBy(), $table->getLimit(), $table->getOffset());
    }

    public function getWhere(): string
    {
        $where = trim($this->where);
        $where = rtrim($where, 'AND');
        return rtrim($where, 'OR');
    }

    public function setWhere(string $where): static
    {
        $this->where = $where;
        return $this;
    }

    public function prependWhere(string $where, ...$args): static
    {
        $this->where = vsprintf($where, $args) . $this->where;
        return $this;
    }

    public function appendWhere(string $where, ...$args): static
    {
        $this->where .= vsprintf($where, $args);
        return $this;
    }

    public function getOrderBy(): string
    {
        return $this->orderBy;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function resetLimits(): static
    {
        $this->limit = 0;
        $this->offset = 0;
        return $this;
    }

    public function getSql(): string
    {
        // ORDER BY
        $orderBy = '';
        if ($this->getOrderBy()) {
            $orderBy = 'ORDER BY ' . $this->getSqlOrderBy();
        }

        // LIMIT
        $limitStr = '';
        if (($this->getLimit() ?? 0) > 0) {
            $limitStr = 'LIMIT ' . $this->getLimit();
            if (($this->getOffset() ?? 0) > 0) {
                $limitStr .= ' OFFSET ' . $this->getOffset();
            }
        }

        $where = '';
        if ($this->getWhere()) {
            $where = 'WHERE ' . $this->getWhere();
        }

        $sql = <<<SQL
            $where
            $orderBy
            $limitStr
        SQL;
        return trim($sql);
    }

    private function getSqlOrderBy(): string
    {
        $orders = explode(',', $this->getOrderBy());
        $orders = array_map('trim', $orders);

        $sql = [];
        foreach ($orders as $order) {
            $order = Str::toSnake($order);
            if ($order[0] == '-') {     // descending
                $col = substr($order, 1);
                $sql[] = "$col DESC";
            } else {
                $sql[] = "$order";
            }
        }
        return implode(', ', $sql);
    }
}