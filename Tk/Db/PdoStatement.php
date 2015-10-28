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
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @author Patrick S Scott<lazeras@kaoses.com>
 * @link http://www.kaoses.com
 * @license Copyright 2007 Michael Mifsud
 */
class PdoStatement extends \PDOStatement
{
    /**
     * @var Pdo
     */
    protected $pdo;

    /**
     * @var array
     */
    protected $params = array();




    /**
     * Represents a prepared statement and, after the statement is executed, an associated result set
     *
     * @see http://www.php.net/manual/en/class.pdostatement.php
     * @param Pdo $pdo
     */
    protected function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
        return $this;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getParam($name)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
        return null;
    }





    /**
     * Executes a prepared statement
     *
     *  @see http://us3.php.net/manual/en/pdostatement.execute.php
     * @param array $args null
     *
     * @return boolean $boolean
     */
    public function execute($args = null)
    {
        $start  = microtime(true);
        if (!is_array($args)) {
            $args = func_get_args();
        }
        $result = parent::execute($args);
        $this->pdo->addLog(
            array(
                'query'  => $this->queryString,
                'time'   => microtime(true) - $start,
                'values' => $args,
            )
        );
        return $result;
    }

}