<?php
namespace Tk;

use Tk\DataMap\ModelMapper;
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
                $p = array_filter($params, function ($k) {
                    return str_contains($this->queryString, ":$k");
                }, ARRAY_FILTER_USE_KEY);
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

        // check if the constructor requires params, if so, use reflection to create object
        if (is_subclass_of($classname, Model::class)) {
            $map = $classname::getDataMap();
        } else {
            $map = ModelMapper::instance()->getDataMap($classname);
        }

        $useReflection = false;
        if ($map) {
            $useReflection = $map->getConstructorRequiresParams();
        }
        $obj = ObjectUtil::createObjectInstance($classname, $useReflection);


        // use PDO mapping if class is not a DbModel object
        if (!($obj instanceof Model)) {
            $obj = $this->fetchObject($classname);
            return $obj;
        }

        $row = $this->fetch(\PDO::FETCH_ASSOC);
        if ($row === false) {
            return false;
        }

        $obj->__map($row);

        return $obj;
    }

    public function getLastParams(): ?array
    {
        return $this->lastParams;
    }

}