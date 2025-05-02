<?php
namespace Tk;

use Tk\Db\Model;

class DbStatement extends \PDOStatement
{
    protected ?array $meta = null;
    protected ?array $lastParams = null;

    public function execute(array|null $params = null): bool
    {
        try {
            // remove any params not in query
            if (is_array($params)) {
                $p = [];
                foreach ($params as $k => $v) {
                    if (str_contains($this->queryString, ":$k")) $p[$k] = $v;
                }
                $params = $p;
            }
            $this->lastParams = $params;
            $result = parent::execute($params);
        } catch (\Exception $e) {
            throw new Db\Exception($e->getMessage(), $e->getCode(), $this->queryString, $params);
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
     * Note the DB log for capturing the last executed query is disabled for this method
     *
     * @template T of object
     * @param class-string<T> $classname
     * @return T|null|false
     */
    public function fetchMappedObject(string $classname): object|null|false
    {
        if (!class_exists($classname)) {
            throw new Db\Exception("class name '{$classname}' does not exist");
        }

        // disable DB query log for Db::countTotalRows() to work correctly
        $olog = Db::$LOG;
        Db::$LOG = false;
        $obj = new $classname;

        // use PDO mapping if class is not a DbModel object
        if (!($obj instanceof Model)) {
            $obj = $this->fetchObject($classname);
            Db::$LOG = $olog;
            return $obj;
        }

        $row = $this->fetch(\PDO::FETCH_ASSOC);
        if ($row === false) {
            Db::$LOG = $olog;
            return false;
        }

        $obj->__map($row);
        Db::$LOG = $olog;

        return $obj;
    }

    public function getLastParams(): ?array
    {
        return $this->lastParams;
    }

}