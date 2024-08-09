<?php
namespace Tk\Db\Mapper;

use Tk\Traits\CollectionTrait;
use Tk\Db\Event\DbEvent;
use Tk\ObjectUtil;
use Tk\Traits\SystemTrait;
use Tt\Db;

/**
 * @deprecated to be replaced by \Tt\DbModel
 */
abstract class Model implements ModelInterface
{
    use SystemTrait;
    use CollectionTrait;

    /**
     * Object models should have a related mapper class
     * EG:
     *    \App\Db\User => \App\Db\UserMap
     *
     * In this case the \App\Db\User class would extend this class
     */
    public static string $MAPPER_APPEND = 'Map';


    /**
     * Get this object's DB mapper
     *
     * The Mapper class will be taken from this class's name if not supplied
     * By default the Database is attempted to be set from the Tk\Config object if it exists
     * Also the Default table name is generated from this object: EG: /App/Db/WebUser = 'webUser'
     * This would in-turn look for the mapper class /App/Db/WebUserMap
     * Change the self::$APPEND parameter to change the class append name
     * The method setDb() must be called after calling getMapper() if you do not wish to use the DB from the config
     *
     */
    static function getMapperInstance(string $mapperClass = '', ?Db $db = null)
    {
        if (!$mapperClass)
            $mapperClass = get_called_class() . self::$MAPPER_APPEND;
        if (!preg_match('/'.self::$MAPPER_APPEND.'$/', $mapperClass))
            $mapperClass = $mapperClass . self::$MAPPER_APPEND;

        if (!class_exists($mapperClass)) {
            throw new \Tk\Db\Exception('Data mapper class not found: ' . $mapperClass);
        }

        // Default table class
        $arr = explode('\\', static::class);
        $table = array_pop($arr);

        return $mapperClass::create($db, static::class, $table);
    }

    public function getMapper(): Mapper
    {
        return self::getMapperInstance(static::class);
    }

    /**
     * This base class automatically clones attributes of type object or
     *   array values of type object recursively.
     *   Just inherit your own classes from this base class.
     */
    public function __clone()
    {
        $object_vars = get_object_vars($this);
        foreach ($object_vars as $attr_name => $attr_value) {
            if (is_object($this->$attr_name)) {
                $this->$attr_name = clone $this->$attr_name;
            } else if (is_array($this->$attr_name)) {
                // Note: This copies only one dimension arrays
                foreach ($this->$attr_name as &$attr_array_value) {
                    if (is_object($attr_array_value)) {
                        $attr_array_value = clone $attr_array_value;
                    }
                    unset($attr_array_value);
                }
            }
        }
        $this->setId(0);
    }

    /**
     * Get the model primary DB key, usually ID
     */
    public function getId(): null|string|int
    {
        $type = $this->getMapper()->getPrimaryType();
        if ($type) {
            // get the value from the object
            return ObjectUtil::getPropertyValue($this, $type->getProperty());
        }
        return null;
    }

    protected function setId(int|string $id): Model
    {
        $type = $this->getMapper()->getPrimaryType();
        if ($type) {
            ObjectUtil::setPropertyValue($this, $type->getProperty(), $id);
        }
        return $this;
    }

    /**
     * Returns the current id if > 0 or the `nextInsertId` if == 0
     */
    public function getVolatileId(): string|int
    {
        if (!$this->getId()) {
            try {
                return Db::getNextInsertId(self::getMapperInstance()->getTable());
                //return self::getMapperInstance()->getDb()->getNextInsertId(self::getMapperInstance()->getTable());
            } catch (\Exception $e) {
                \Tk\Log::warning('Volatile ID not found!');
            }
        }
        return $this->getId();
    }

    protected function dispatchEvent(DbEvent $e, string $eventName): DbEvent
    {
        $this->getSystem()->getFactory()->getEventDispatcher()?->dispatch($e, $eventName);
        return $e;
    }

    /**
     * Insert the object into the DB
     */
    public function insert(): int
    {
        $e = $this->dispatchEvent(new DbEvent($this), DbEvents::MODEL_INSERT);
        $id = 0;
        if (!$e->isQueryStopped()) {
            $id = $this->getMapper()->insert($this);
            $this->setId($id);
        }
        $this->dispatchEvent($e, DbEvents::MODEL_POST_INSERT);
        return $id;
    }

    /**
     * Update the object into the DB
     */
    public function update(): int
    {
        $e = $this->dispatchEvent(new DbEvent($this), DbEvents::MODEL_UPDATE);
        $r = 0;
        if (!$e->isQueryStopped()) {
            $r = $this->getMapper()->update($this);
        }
        $this->dispatchEvent($e, DbEvents::MODEL_POST_UPDATE);
        return $r;
    }

    /**
     * A Utility method that checks the id and does and insert
     * or an update  based on the objects current state
     */
    public function save(): void
    {
        $e = $this->dispatchEvent(new DbEvent($this), DbEvents::MODEL_SAVE);
        if (!$e->isQueryStopped()) {
            $this->getMapper()->save($this);
        }
        $this->dispatchEvent($e,DbEvents::MODEL_POST_SAVE);
    }

    /**
     * Delete the object from the DB
     */
    public function delete(): int
    {
        $e = $this->dispatchEvent(new DbEvent($this),DbEvents::MODEL_DELETE);
        $r = 0;
        if (!$e->isQueryStopped()) {
            $r = self::getMapperInstance()->delete($this);
        }
        $this->dispatchEvent($e,DbEvents::MODEL_POST_DELETE);
        return $r;
    }

}