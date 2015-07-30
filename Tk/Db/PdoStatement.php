<?php
namespace Tk\Db;

/**
 * Class PdoStatement
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