<?php
namespace Tk\Db;

use Tk\Str;

/**
 * Class PdoStatement
 *
 * NOTE: When using the statement in a foreach loop, any overriden
 * method calls to fetch, fetchObject, etc will not be called
 * in this object, it has something to do with the way the PDOStatement
 * object uses it Traversable methods internally
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
        if ($params !== null && !is_array($params) && count(func_get_args())) {
            $params = func_get_args();
        }

        $sql = $this->queryString;
        if (is_array($params)) {
            // find all placeholders in the SQL string
            // the matches $m is a somewhat confusing array -- refer to the PHP docs
            // Source: @Greg Jorgensen (OUM)
            $fParams = [];
            $n = preg_match_all('/:([a-zA-Z0-9_]+)/', $sql, $m, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

            if ($n) {
                for ($i = $n-1; $i >=0; $i--) {
                    $match = $m[$i][0][0];  // the entire placeholder pattern, with optional wildcards
                    $pos = $m[$i][0][1];    // the position in the string the placeholder begins
                    $key = $m[$i][1][0];    // the placeholder name without : or wildcardss

                    // get the value and convert it to a SQL type, with escaping and quoting strings
                    if (is_array($params[$key])) {   // assume this is for the IN query
                        $newKey = '';
                        foreach ($params[$key] as $k => $v) {
                            $nk = sprintf('%s_%s', $key, $k);
                            $newKey .= sprintf(':%s,', $nk);
                            $fParams[$nk] = $v;
                        }
                        $newKey = rtrim($newKey, ',');
                        // replace the placeholder with the value
                        $sql = substr_replace($sql, $newKey, $pos, strlen($match));
                    } else {
                        if (array_key_exists($key, $params)) $fParams[$key] = $params[$key];
                    }
                }
                $params = $fParams;
                //$this->queryString = $sql;    // This is readonly now (use Db::getLastQuery())
            } else {
                $params = [];
            }
        }

        $this->bindParams = $params;
        $this->db->setLastQuery($sql);
        try {
            $result = parent::execute($params);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), null, $sql, $params);
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