<?php
namespace Tk\Db;

use Tk\ObjectUtil;
use Tk\Traits\DataTrait;
use Tk\DataMap\DataMap;
use Tk\Db;

abstract class Model
{
    use DataTrait;

    /**
     * Class datamaps, by default each class has a single DataMap
     * generated by the getDataMap function
     * @var DataMap[]|array
     */
    protected static array $_MAPS = [];


    /**
     * Magic method to map an array to an object
     */
    public function __map(array $row, ?DataMap $map = null): void
    {
        if (is_null($map)) {
            $map = static::getDataMap();
        }
        $map->loadObject($this, $row);
    }

	/**
	 * load properties of this object from database
	 */
    public function reload(): void
    {
        $map = $this->getDataMap();
        $priKey = $map->getPrimaryKey()?->getProperty();
        if (is_null($priKey)) return;
        $id = $this->$priKey;

        if ($id && method_exists($this, 'find')) {
            $obj = static::find($id);
        } else {
            $obj = new static();
        }
        if (is_null($obj)) return;
		foreach (get_object_vars($obj) as $prop => $val) {
			$this->$prop = $val;
		}
    }

    /**
     * Auto generate a DataMap for this object.
     *
     * Default table name is the snake case of the class name, 'MenuItem' => 'menu_item'
     * Default view name is the same with 'v_' prepended, ''v_menu_item'
     * If the view table does not exist it is ignored
     *
     * Override this method if you need to create a custom DataMap with different table names
     */
    public static function getDataMap(): DataMap
    {
        $name = static::class;
        if (self::hasMap($name)) return self::getMap($name);

        // autogen table/view name from class
        $table = strtolower(preg_replace('/(?<!^)[A-Z]+|(?<!^|\d)[\d]+/', '_$0', ObjectUtil::basename(static::class)));
        $view = "v_{$table}";

        Db::$LOG = false;   // disable cache of last statement
        if (!Db::tableExists($view)) $view = $table;

        $v_meta = [];
        $t_meta = Db::getTableInfo($table);
        if (!empty($view)) {
            $v_meta = Db::getTableInfo($view);
        }
        $roCols = array_diff_key($v_meta, $t_meta);
        Db::$LOG = true;

        $map = new DataMap();

        // autogenerate a data map from DB and object metadata
        foreach ($t_meta+$roCols as $meta) {
            if (!property_exists(static::class, $meta->name_camel)) continue;

            $type = DataMap::makeDbType($meta);
            if ($meta->is_primary_key) {
                $type->setFlag(DataMap::PRI);
            }
            if ($roCols[$meta->name] ?? false) {
                $type->setAccess(DataMap::READ);
            }
            $map->addType($type);
        }

        self::setMap($name, $map);
        return $map;
    }

    public static function getFormMap(): DataMap
    {
        $name = 'form_'. static::class;
        if (self::hasMap($name)) return self::getMap($name);

        $map = new DataMap();
        $primaryId = self::getDataMap()->getPrimaryKey()->getProperty();

        $reflect = new \ReflectionClass(static::class);
        $props = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);
        foreach ($props as $prop) {
            if (str_starts_with($prop->getName(), '_') || $prop->isReadOnly() || $prop->isStatic()) continue;
            $type = DataMap::makeFormType($prop->getType()->getName(), $prop->getName());
            if ($primaryId == $prop->getName()) {
                $type->setFlag(DataMap::PRI);
            }
            if ($prop->isReadOnly()) {
                $type->setAccess(DataMap::READ);
            }
            $map->addType($type);
        }
        self::setMap($name, $map);
        return $map;
    }



    public static function getMap(string $name): ?DataMap
    {
        return self::$_MAPS[$name] ?? null;
    }

    public static function setMap(string $name, DataMap $map): void
    {
        self::$_MAPS[$name] = $map;
    }

    public static function hasMap(string $name): bool
    {
        return array_key_exists($name, self::$_MAPS);
    }

}