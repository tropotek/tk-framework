<?php
namespace Tk\Db;

use Tk\Str;
use Tk\Table;

class Filter extends \Tk\Collection
{

    protected string $orderBy   = '';
    protected ?int   $limit     = null;
    protected ?int   $offset    = null;
    protected array  $from      = [];
    protected array  $where     = [];


    public static function create(array|Filter $params = [], string $orderBy = '', ?int $limit = null, ?int $offset = null): self
    {
        if ($params instanceof Filter) return $params;
        $obj = new self();
        $obj->orderBy = preg_replace('/[^a-z0-9, _-]/i', '', $orderBy);
        $obj->limit = $limit;
        $obj->offset = $offset;
        $obj->replace($params);
        return $obj;
    }

    public static function createFromTable(null|array|Filter $params, Table $table): self
    {
        return self::create($params, $table->getOrderBy(), $table->getLimit(), $table->getOffset());
    }


    public function getFromStr(bool $trimFirst = true): string
    {
        if ($trimFirst && isset($this->from[0])) {
            $this->from[0] = trim($this->from[0], ',');
            $this->from[0] = str_replace('LEFT JOIN', '', $this->from[0]);
            $this->from[0] = str_replace('RIGHT JOIN', '', $this->from[0]);
            $this->from[0] = str_replace('INNER JOIN', '', $this->from[0]);
            $this->from[0] = str_replace('JOIN', '', $this->from[0]);
            $this->from[0] = trim($this->from[0]);
        }
        return implode("\n", $this->from);
    }

    public function getFrom(): array
    {
        return $this->from;
    }

    public function setFrom(array $from): self
    {
        $this->from = $from;
        return $this;
    }

    public function prependFrom(string $from): self
    {
        $str = trim($from);
        array_unshift($this->from, $str);
        return $this;
    }

    public function appendFrom(string $from): self
    {
        $this->from[] = $from;
        return $this;
    }


    public function getWhereStr(): string
    {
        if (isset($this->where[0])) {
            $this->where[0] = trim($this->where[0], 'AND');
            $this->where[0] = trim($this->where[0], 'OR');
            $this->where[0] = trim($this->where[0]);
        }
        return implode("\n", $this->where);
    }

    public function getWhere(): array
    {
        return $this->where;
    }

    public function setWhere(array $where): self
    {
        $this->where = $where;
        return $this;
    }

    public function prependWhere(string $where, mixed ...$args): self
    {
        $str = vsprintf($where, $args);
        array_unshift($this->where, $str);
        return $this;
    }

    public function appendWhere(string $where, mixed ...$args): self
    {
        $this->where[] = vsprintf($where, $args);
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

    public function resetLimits(): self
    {
        $this->limit = 0;
        $this->offset = 0;
        return $this;
    }

    public function getSql(bool $trimFirst = true): string
    {
        $from = '';
        if (count($this->getFrom($trimFirst))) {
            $from = $this->getFromStr();
        }

        $where = '';
        if (count($this->getWhere())) {
            $where = 'WHERE ' . $this->getWhereStr();
        }

        $orderBy = '';
        if ($this->getOrderBy()) {
            $orderBy = 'ORDER BY ' . $this->getSqlOrderBy();
        }

        $limitStr = '';
        if (($this->getLimit() ?? 0) > 0) {
            $limitStr = 'LIMIT ' . $this->getLimit();
            if (($this->getOffset() ?? 0) > 0) {
                $limitStr .= ' OFFSET ' . $this->getOffset();
            }
        }

        $sql = <<<SQL
            $from
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
            $order = trim(Str::toSnake($order));
            if ($order[0] == '-') {     // descending
                $col = substr($order, 1);
                $order = "$col DESC";
            }
            $sql[] = trim($order);
        }
        return implode(', ', $sql);
    }
}