<?php
namespace Tk\Db\Mapper;

use Tk\Collection;
use Tk\DataMap\DataMap;
use Tk\DataMap\DataTypeInterface;
use Tk\Db\Pdo;
use Tk\Db\Exception;
use Tk\Db\Tool;
use Tk\Factory;
use Tk\Traits\SystemTrait;

/**
 * Some reserved column names and assumed meanings:
 *  - `id`       => An integer that is assumed to be the records primary key
 *                  foreign keys are assumed to be named `<foreign_table>_id`
 *  - `modified` => A timestamp that gets incremented on updates
 *  - `created`  => A timestamp not really reserved but assumed
 *  - `del`      => If it exists the records are marked `del` = 1 rather than deleted
 *
 * If your columns conflict, then you should modify the mapper or DB accordingly
 *
 */
abstract class Mapper
{
    use SystemTrait;

    const DATA_MAP_DB    = 'dbMap';
    const DATA_MAP_FORM  = 'dbForm';
    const DATA_MAP_TABLE = 'dbTable';


    /**
     * @var Mapper[]|array
     */
    protected static array $_INSTANCE = [];

    /**
     * Allow records that have been deleted to be retrieved
     */
    public static bool $HIDE_DELETED = true;

    protected Pdo $db;

    protected string $table = '';

    protected string $modelClass = '';

    protected string $alias = 'a';

    protected ?array $tableInfo = null;

    protected Collection $dataMappers;

    protected ?DataMap $dbMap = null;

    protected ?DataMap $formMap = null;

    /**
     * This will hold the primary key DataMap type
     * If there are more than one primary key then
     *   the first one found is used
     */
    protected ?DataTypeInterface $primaryType = null;

    /**
     * This will hold the deleteType DataMap field
     * if one exists
     */
    protected ?DataTypeInterface $deleteType = null;


    public function __construct(?Pdo $db = null)
    {
        $this->dataMappers = new Collection();
        $this->setDb($db);
        $this->makeDataMaps();
    }

    /**
     * Get/Create an instance of a data mapper.
     */
    static function create(?Pdo $db = null): static
    {
        $mapperClass = static::class;
        if (!preg_match('/(.+)(Map)$/', $mapperClass, $regs)) {
            throw new Exception('Invalid mapper class name');
        }
        $modelClass = $regs[1];
        if (!class_exists($modelClass)) {
            throw new Exception('Model class for this mapper not found!');
        }

        $arr = explode('\\', $modelClass);
        $table = self::toDbProperty(array_pop($arr));

        $db = $db ?? Factory::instance()->getDb();

        if (!isset(self::$_INSTANCE[$mapperClass])) {
            $obj = new $mapperClass($db);
            $obj->setModelClass($modelClass);
            $obj->setTable($table);
            self::$_INSTANCE[$mapperClass] = $obj;
        }
        return self::$_INSTANCE[$mapperClass];
    }

    public function getDataMappers(): Collection
    {
        return $this->dataMappers;
    }

    public function getDataMap(string $name): ?DataMap
    {
        return $this->getDataMappers()->get($name);
    }

    public function addDataMap(string $name, DataMap $map): static
    {
        if (!$this->getDataMappers()->has($name)) {
            $this->getDataMappers()->set($name, $map);
        }
        return $this;
    }

    abstract protected function makeDataMaps(): void;

    // TODO: These are helper functions, should we remove them or are they worth having.

    /**
     * Returns a valid DB DataMap
     */
    public function getDbMap(): DataMap
    {
        return $this->getDataMappers()->get(self::DATA_MAP_DB, new DataMap());
    }

    /**
     * Returns a valid form DataMap
     */
    public function getFormMap(): DataMap
    {
        return $this->getDataMappers()->get(self::DATA_MAP_FORM, new DataMap());
    }

    /**
     * Returns a valid table DataMap
     */
    public function getTableMap(): DataMap
    {
        return $this->getDataMappers()->get(self::DATA_MAP_TABLE, new DataMap());
    }

    public function insert(Model $obj): int
    {
        if (!$this->getPrimaryType()) {
            throw new Exception('Invalid operation, no primary key found');
        }

        $bind = [];
        $this->getDbMap()->loadArray($bind, $obj);

        $keys = array_keys($bind);
        $cols = implode(', ', $this->getDb()->quoteParameterArray($keys));
        $values = implode(', :', array_keys($bind));
        foreach ($bind as $col => $value) {
            unset($bind[$col]);
            $bind[':' . $col] = $value;
            $inf = $this->getTableInfo($col);
            if ($inf['Extra']?? '' == 'current_timestamp()') continue;
        }
        $sql = 'INSERT INTO ' . $this->quoteParameter($this->table) . ' (' . $cols . ')  VALUES (:' . $values . ')';
        $this->getDb()->prepare($sql)->execute($bind);

        $seq = '';
        if ($this->getDb()->getDriver() == 'pgsql') {   // Generate the seq key for Postgres only
            $seq = $this->getTable().'_'.$this->getPrimaryType()->getProperty().'_seq';
        }
        $id = (int)$this->getDb()->lastInsertId($seq);
        return $id;
    }

    public function update(Model $obj): int
    {
        if (!$this->getPrimaryType()) {
            throw new Exception('Invalid operation, no primary key found');
        }

        $bind = [];
        $this->getDbMap()->loadArray($bind, $obj);

        $set = [];
        foreach ($bind as $col => $value) {
            unset($bind[$col]);
            $inf = $this->getTableInfo($col);
            if (str_contains($inf['Extra'] ?? '', 'on update')) continue;
            $bind[':' . $col] = $value;
            $set[] = $this->quoteParameter($col) . ' = :' . $col;
        }

        $where = $this->quoteParameter($this->getPrimaryType()->getKey()) . ' = ' . $bind[':' . $this->getPrimaryType()->getKey()];
        $sql = sprintf('UPDATE %s SET %s WHERE %s', $this->quoteParameter($this->table), implode(', ', $set), $where);
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($bind);

        return $stmt->rowCount();
    }

    public function delete(Model $obj): int
    {
        if (!$this->getPrimaryType()) {
            throw new Exception('Invalid operation, no primary key found');
        }

        $where = $this->quoteParameter($this->getPrimaryType()->getKey()) . ' = ' . $this->getPrimaryType()->getPropertyValue($obj);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        $sql = sprintf('DELETE FROM %s %s LIMIT 1', $this->quoteParameter($this->table), $where);
        if ($this->getDeleteType()) {
            $sql = sprintf('UPDATE %s SET %s = 1 %s LIMIT 1',
                $this->quoteParameter($this->table),
                $this->quoteParameter($this->getDeleteType()->getKey()),
                $where
            );
        }
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * A Utility method that checks the id and does and insert
     * or an update  based on the objects current state
     */
    public function save(Model $obj): void
    {
        if (!$this->getPrimaryType()) {
            throw new Exception('Invalid operation, no primary key found');
        }

        if ($this->getPrimaryType()->getPropertyValue($obj)) {
            $obj->update();
        } else {
            $obj->insert();
        }
    }

    /**
     * A select query using a prepared statement. Less control
     *
     * @see http://www.sitepoint.com/integrating-the-data-mappers/
     * @deprecated TODO: See if we need this ?
     */
    public function selectPrepared(array $bind =[], ?Tool $tool = null, string $boolOperator = 'AND'): Result
    {
        if (!$tool instanceof Tool) $tool = new Tool();

        $alias = $this->getAlias();
        if ($alias) $alias = $alias . '.';

        if (self::$HIDE_DELETED && $this->getDeleteType()) {
            $bind[$this->getDeleteType()->getKey()] = '0';
        }

        $from = $this->getTable() . ' ' . $this->getAlias();
        $where = [];
        if ($bind) {
            foreach ($bind as $col => $value) {
                unset($bind[$col]);
                $bind[':' . $col] = $value;
                $where[] = $alias. $this->quoteParameter($col) . ' = :' . $col;
            }
        }
        $where = implode(' ' . $boolOperator . ' ', $where);

        // Build Query
        $foundRowsKey = '';
        if ($this->getDb()->getDriver() == 'mysql') {
            $foundRowsKey = 'SQL_CALC_FOUND_ROWS';
        }
        $sql = sprintf('SELECT %s %s * FROM %s %s ',
            $foundRowsKey,
            $tool->isDistinct() ? 'DISTINCT' : '',
            $from,
            ($bind) ? ' WHERE ' . $where : ' '
        );

        $sql .= $tool->toSql($this->getAlias(), $this->getDb());

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($bind);

        $arr = Result::createFromMapper($this, $stmt, $tool);
        return $arr;
    }

    /**
     * Select a number of elements from a database
     */
    public function selectFrom(string $from = '', string $where = '', ?Tool $tool = null, string $select = ''): Result
    {
        if (!$tool instanceof Tool) $tool = new Tool();

        $alias = $this->getAlias();
        if ($alias) $alias = $alias . '.';

        if (!$from) {
            $from = sprintf('%s %s', $this->quoteParameter($this->getTable()), $this->getAlias());
        }

        if (
            self::$HIDE_DELETED &&
            $this->getDeleteType() &&
            !str_contains($where, $this->quoteParameter($this->getDeleteType()->getKey()))
        ) {
            if ($where) {
                $where = sprintf('%s%s = 0 AND %s ', $alias, $this->quoteParameter($this->getDeleteType()->getKey()), $where);
            } else {
                $where = sprintf('%s%s = 0 ', $alias, $this->quoteParameter($this->getDeleteType()->getKey()));
            }
        }
        if ($where) $where = 'WHERE ' . $where;

        $distinct = '';
        if ($tool->isDistinct()) $distinct = 'DISTINCT';

        // OrderBy, GroupBy, Limit, etc
        $toolStr = $tool->toSql($alias, $this->getDb());

        $foundRowsKey = '';
        if ($this->getDb()->getDriver() == 'mysql') {
            $foundRowsKey = 'SQL_CALC_FOUND_ROWS';
        }

        if (!$select) {
            $select = $alias.'*';
        }

        $sql = sprintf('SELECT %s %s %s FROM %s %s %s ',
            $foundRowsKey, $distinct, $select, $from, $where, $toolStr);
        $stmt = $this->getDb()->prepare($sql);

        $stmt->execute();

        return Result::createFromMapper($this, $stmt, $tool);
    }

    public function selectFromFilter(Filter $filter, $tool = null): Result
    {
        return $this->selectFrom($filter->getFrom(), $filter->getWhere(), $tool, $filter->getSelect());
    }

    /**
     * Select a number of elements from a database
     *
     * EG: "`column1`=4 AND `column2`='string'"
     */
    public function select(string $where = '', ?Tool $tool = null): Result
    {
        return $this->selectFrom('', $where, $tool);
    }

    public function find(mixed $id): ?Model
    {
        if (!$this->getPrimaryType()) {
            throw new Exception('Invalid operation, no primary key found');
        }
        $where = sprintf('%s = %s', $this->quoteParameter($this->getPrimaryType()->getKey()), $id);
        $list = $this->select($where);
        return $list->current();
    }

    /**
     * Find all objects in DB
     */
    public function findAll(?Tool $tool = null): Result
    {
        return $this->select('', $tool);
    }

    /**
     * Create a sql query string from an array.
     * Handy for testing multiple values
     * EG:
     *   "a.type = 'Self Assessment' AND a.type != 'Testing' AND 'Thinking'"
     */
    public function makeMultiQuery($value, string $columnName, string $logic = 'OR', string $compare = '='): string
    {
        if (!is_array($value)) $value = array($value);
        $w = '';
        foreach ($value as $r) {
            if ($r === null || $r === '') continue;
            $w .= sprintf('%s %s %s %s ', $columnName, $compare, $this->getDb()->quote($r), $logic);
        }
        if ($w)
            $w = rtrim($w, ' '.$logic.' ');
        return $w;
    }

    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    protected function setModelClass(string $modelClass): Mapper
    {
        $this->modelClass = $modelClass;
        return $this;
    }

    /**
     * Get the table alias used for multiple table queries.
     * The default alias is 'a'
     *
     *   EG: a.`id`
     */
    public function getAlias(): string
    {
        return rtrim($this->alias, '.');
    }

    /**
     * Set the table alias
     */
    public function setAlias(string $alias): Mapper
    {
        $alias = trim($alias, '.');
        if (!$alias || preg_match('/[a-z0-9_]+/i', $alias))
            $this->alias = $alias;
        return $this;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getPrimaryType(): ?DataTypeInterface
    {
        return $this->primaryType;
    }

    protected function setPrimaryType(DataTypeInterface $primaryType): Mapper
    {
        $this->primaryType = $primaryType;
        return $this;
    }

    /**
     * If set then records will be marked deleted instead of physically deleted
     */
    public function setDeleteType(DataTypeInterface $type): Mapper
    {
        $this->deleteType = $type;
        return $this;
    }

    /**
     * Returns the name of the column to mark deleted. (update col to 1)
     * returns null if we are to physically delete the record
     */
    public function getDeleteType(): ?DataTypeInterface
    {
        return $this->deleteType;
    }

    /**
     * Set the table or view this model gets its data from
     */
    public function setTable(string $table): Mapper
    {
        if (!$this->getDb()->hasTable($table)) {
            throw new Exception('Table not found: ' . $table);
        }
        $this->table = $table;
        $this->tableInfo = $this->getDb()->getTableInfo($this->getTable());

        // Set primary field
        foreach ($this->tableInfo as $key => $atts) {
            if (strtoupper($atts['Key']) == 'PRI') {
                $this->primaryType = $this->getDbMap()->getKeyType($key);
                break;
            }
        }
        return $this;
    }

    /**
     * If a colum name is supplied then that column info is returned
     */
    public function getTableInfo(?string $column = null): ?array
    {
        return (is_string($column) ? $this->tableInfo[$column] : $this->tableInfo);
    }

    public function hasColumn(string $column): bool
    {
        return array_key_exists($column, $this->tableInfo);
    }

    public function getDb(): Pdo
    {
        return $this->db;
    }

    private function setDb(Pdo $db): Mapper
    {
        $this->db = $db;
        return $this;
    }


    /**
     * Use this function to escape a table name and add a prefix if it is set
     */
    public function quote(string $str): string
    {
        return  $this->getDb()->quote($str);
    }

    /**
     * Quote a parameter or table name based on the quote system
     * if the param exists in the reserved words list
     */
    public function quoteParameter(string $str): string
    {
        return  $this->getDb()->quoteParameter($str);
    }

    /**
     * Encode string to avoid sql injections.
     */
    public function escapeString(string $str): string
    {
        return  $this->getDb()->escapeString($str);
    }

    /**
     * Convert camelCase property names to underscore db property name
     *
     * EG: 'someProperty' is converted to 'some_property'
     */
    public static function toDbProperty(string $property): string
    {
        return ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $property)), '_');
    }

}