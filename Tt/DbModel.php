<?php
namespace Tt;

use Tk\Factory;
use Tk\ObjectUtil;
use Tk\Traits\DataTrait;
use Tt\DataMap\DataMap;

abstract class DbModel
{
    use DataTrait;

    /**
     * Class datamaps, by default each class has a single DataMap
     * generated by the getDataMap function
     * @var DataMap[]|array
     */
    protected static array $_MAPS = [];

    protected string $_primaryKey = 'id';


    public function __construct()
    {
        $this->_primaryKey = self::getDataMap()->getPrimaryKey()->getProperty();
    }

    /**
     * Magic method called by DbStatement to map a row to an object
     * In this case we use a DataMap to load the object with PHP values
     */
    public function __map(array $row): void
    {
        $map = static::getDataMap();
        $this->_primaryKey = strval($map->getPrimaryKey()?->getProperty());
        $map->loadObject($this, $row);
    }

    /**
     * Auto generate a DataMap for this object.
     *
     * if no table and view names are supplied this objects class name will be
     * converted to snake case and used as the table name, the view will be the table
     * name with 'v_' prepended.
     *
     * Alternatively call self::getDataMap('my_table', 'v_my_view') from the constructor to
     * init with your own table names.
     *
     * Override this method if you want to create a custom DataMap
     */
    public static function getDataMap(string $table = '', string $view = ''): DataMap
    {
        $map = self::$_MAPS[static::class] ?? null;
        if (!is_null($map)) return $map;

        // autogen table/view from class name if empty
        if (empty($table) && empty($view)) {
            $table = strtolower(preg_replace('/(?<!^)[A-Z]+|(?<!^|\d)[\d]+/', '_$0', ObjectUtil::basename(static::class)));
            $view = "v_{$table}";
            if (!Db::tableExists($view)) $view = $table;
        }

        $v_meta = [];
        $t_meta = Db::getTableInfo($table);
        if (!empty($view)) {
            $v_meta = Db::getTableInfo($view);
        }
        $roCols = array_diff_key($v_meta, $t_meta);

        $map = new DataMap();

        // autogenerate a data map from DB and object metadata
        foreach ($t_meta+$roCols as $meta) {
            if (!property_exists(static::class, $meta->name_camel)) continue;

            $type = DataMap::makeType($meta);
            if ($meta->is_primary_key) {
                $type->setFlag(DataMap::PRI);
            }
            if ($roCols[$meta->name] ?? false) {
                $type->setAccess(DataMap::READ);
            }
            $map->addType($type);
        }

        self::$_MAPS[static::class] = $map;
        return $map;
    }

	/**
	 * load properties of this object from database
	 * necessary to set properties if using views
	 */
    public function reload(): void
    {
        if (!method_exists($this, 'get')) return;

        if ($this->getId()) {
            $obj = static::get($this->getId());
        } else {
            $obj = new static();
        }
		foreach (get_object_vars($obj) as $prop => $val) {
			$this->$prop = $val;
		}
    }

    public function getId(): int
    {
        if (!$this->_primaryKey) return 0;
        return intval($this->{$this->_primaryKey});
    }

}