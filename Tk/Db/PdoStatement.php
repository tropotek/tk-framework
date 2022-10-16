<?php
namespace Tk\Db;

/**
 * Class PdoStatement
 *
 * NOTE: When using the statement in a foreach loop, any overriden
 * method calls to fetch, fetchObject, etc will not be called
 * in this object, it has something to do with the way the PDOStatement
 * object uses it Traversable methods internally
 *
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class PdoStatement extends \PDOStatement
{
    protected Pdo $db;

    protected ?array $bindParams = null;


    /**
     * Represents a prepared statement and, after the statement is executed, an associated result set
     *
     * @see http://www.php.net/manual/en/class.pdostatement.php
     */
    protected function __construct(Pdo $pdo)
    {
        $this->db = $pdo;
        $this->setFetchMode(\PDO::FETCH_OBJ);
    }

    /**
     * Executes a prepared statement
     *
     * @see http://us3.php.net/manual/en/pdostatement.execute.php
     */
    public function execute($params = null): bool
    {
        if (!is_array($params) && count(func_get_args())) {
            $params = func_get_args();
        }
        $this->bindParams = $params;
        $this->db->setLastQuery($this->queryString);
        try {
            $result = parent::execute($params);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), null, $this->queryString, $params);
        }
        return $result;
    }

    /**
     * Get the params bound upon the last call to execute()
     */
    public function getBindParams(): ?array
    {
        return $this->bindParams;
    }

    public function getDb(): Pdo
    {
        return $this->db;
    }

}