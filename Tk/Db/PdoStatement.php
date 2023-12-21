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
     * Total row count from last query without a limit applied
     */
    protected int $total = 0;

    protected int $limit = 0;

    protected int $offset = 0;


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
    public function execute(?array $params = null, bool $withCount = true): bool
    {
        try {
            $result = parent::execute($this->setBindParams($params));
            if ($withCount) {
                [$this->limit, $this->offset, $this->total] =
                    $this->getDb()->countFoundRows($this->db->getLastQuery(), $this->getBindParams() ?? []);
            }
            //vd($this->db->getLastQuery(), $this->limit, $this->offset, $this->total);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), null, $this->getDb()->getLastQuery(), $params);
        }
        return $result;
    }

    /**
     * find all placeholders in the SQL string
     * the matches $m is a somewhat confusing array -- refer to the PHP docs
     *
     * @source: @Greg Jorgensen (OUM)
     * @param array|null $params
     * @return array|null
     */
    private function setBindParams(?array $params = null): ?array
    {
        // return if null or not an associative array
        if (!is_array($params)) return $params;
        if (array_keys($params) === range(0, count($params) - 1)) return $params;    // is sequential

        $sql = $this->queryString;

        // find all placeholders in the SQL string
        // the matches $m is a somewhat confusing array -- refer to the PHP docs
        // Source: @Greg Jorgensen (OUM)
        $fParams = [];
        $n = preg_match_all('/:([a-zA-Z0-9_]+)/', $sql, $m, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        if ($n) {
            for ($i = $n - 1; $i >= 0; $i--) {
                $match = $m[$i][0][0];  // the entire placeholder pattern, with optional wildcards
                $pos = $m[$i][0][1];    // the position in the string the placeholder begins
                $key = $m[$i][1][0];    // the placeholder name without : or wildcards

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
        }

        $this->bindParams = $fParams;
        $this->getDb()->setLastQuery($sql);
        return $fParams;
    }

    /**
     * Total row count without a limit applied
     */
    final public function getQueryTotal(): int
    {
        return $this->total;
    }

    final public function getQueryLimit(): int
    {
        return $this->limit;
    }

    final public function getQueryOffset(): int
    {
        return $this->offset;
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