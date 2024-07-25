<?php
namespace Tt;

class DbStatement extends \PDOStatement
{
    protected Db $db;
    protected ?array $meta = null;

    protected function __construct(Db $db)
    {
        $this->db = $db;
    }

    public function execute(array|null $params = null): bool
    {
        try {
            $result = parent::execute($params);
        } catch (\Exception $e) {
            throw new DbException($e->getMessage(), $e->getCode(), null, $this->getDb()->getLastQuery(), $params);
        }
        return $result;
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

    public function getDb(): Db
    {
        return $this->db;
    }

}