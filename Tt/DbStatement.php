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
            throw new Exception($e->getMessage(), $e->getCode(), null, $this->getDb()->getLastQuery(), $params);
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
            throw new Exception("class name '{$classname}' does not exist");
        }

        $obj = new $classname;
        // use PDO mapping if class is not a DbModel object
        if (!($obj instanceof DbModel)) return $this->fetchObject($classname);

        $row = $this->fetch(\PDO::FETCH_ASSOC);
        $meta = $this->getQueryMeta();
        if ($row === false) return false;

        $obj->__map($row, $meta);
        return $obj;
    }

    public function getQueryMeta(): array
    {
        if (!is_null($this->meta)) return $this->meta;

        $types = [
            'VAR_STRING' => 'string',
            'STRING'     => 'string',
            'ENUM'       => 'string',
            'SET'        => 'string',
            'INT24'      => 'int',
            'TINY'       => 'int',     // tinyint(1) returned as 'bool'
            'SHORT'      => 'int',
            'LONG'       => 'int',
            'LONGLONG'   => 'int',
            'FLOAT'      => 'float',
            'DOUBLE'     => 'float',
            'TIMESTAMP'  => 'timestamp',
            'DATETIME'   => 'datetime',
            'DATE'       => 'date',
            'NEWDATE'    => 'date',
            'TIME'       => 'time',
            'YEAR'       => 'year',
            'TINYBLOB'   => 'blob',
            'MEDIUMBLOB' => 'blob',
            'LONGBLOB'   => 'blob',
            'BLOB'       => 'blob',
            'NULL'       => 'null',
            'INTERVAL'   => 'interval',
            'GEOMETRY'   => 'geometry',
            'JSON'       => 'json',   // JSON types are return as BLOB, prepend json cols with 'json_'
        ];

        $this->meta = [];
        for($i = 0; $i < $this->columnCount(); $i++) {
            $col = (object)$this->getColumnMeta($i);
            $col->type_name = $types[$col->native_type] ?? 'unknown';
			if ($col->native_type == 'TINY' && $col->len == 1) $col->type_name = 'bool';
            $col->name_camel     = lcfirst(str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $col->name))));
			$col->is_numeric     = in_array($col->type_name, ['int', 'decimal', 'float']);
			$col->is_string      = in_array($col->type_name, ['string', 'timestamp', 'datetime', 'date', 'time', 'year']);
			$col->is_datetime    = in_array($col->type_name, ['timestamp', 'datetime', 'date', 'time', 'year']);
			$col->is_enum        = ($col->native_type == 'ENUM');
			$col->is_set         = ($col->native_type == 'SET');
			$col->is_primary_key = in_array('primary_key', $col->flags);
			$col->is_unique      = (bool)array_intersect(['primary_key', 'unique_key'], $col->flags);
            $col->is_json        = str_starts_with($col->name, 'json_');
            $col->timezone       = $this->getDb()->queryVal("SELECT @@session.time_zone");
            $this->meta[$col->name] = $col;
        }

        return $this->meta;
    }

    public function getDb(): Db
    {
        return $this->db;
    }

}