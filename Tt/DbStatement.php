<?php
namespace Tt;

class DbStatement extends \PDOStatement
{
    protected ?array $meta = null;
    protected ?array $lastParams = null;

    public function execute(array|null $params = null): bool
    {
        try {
            // remove any params not in query
            if (!is_null($params)) {
                $p = [];
                foreach ($params as $k => $v) {
                    if (str_contains($this->queryString, ":$k")) $p[$k] = $v;
                }
                $params = $p;
            }
            $this->lastParams = $params;
            $result = parent::execute($params);
        } catch (\Exception $e) {
            throw new DbException($e->getMessage(), $e->getCode(), $this->queryString, $params);
        }
        return $result;
    }

    /**
     * get total rows from query without any LIMITs
     */
    public function getTotalRows(): int
    {
        [$_, $_, $total] = Db::countTotalRows($this->queryString, $this->lastParams);
        return $total;
    }

    /**
     * @template T of object
     * @param class-string<T> $classname
     * @return T|null|false
     */
    public function fetchMappedObject(string $classname): object|null|false
    {
        if (!class_exists($classname)) {
            throw new DbException("class name '{$classname}' does not exist");
        }

        $obj = new $classname;

        // use PDO mapping if class is not a DbModel object
        if (!($obj instanceof DbModel)) return $this->fetchObject($classname);

        $row = $this->fetch(\PDO::FETCH_ASSOC);
        if ($row === false) return false;

        $obj->__map($row);
        return $obj;
    }

    public function getLastParams(): ?array
    {
        return $this->lastParams;
    }

}