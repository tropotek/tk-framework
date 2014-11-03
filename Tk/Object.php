<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsuds
 */
namespace Tk;

/**
 * This object is the base for all objects
 *
 * @package Tk
 */
abstract class Object implements Observable
{
    /* Common Data Types */
    const T_BOOLEAN = 'boolean';
    const T_INTEGER = 'integer';
    const T_DOUBLE = 'double';
    const T_FLOAT = 'float';
    const T_STRING = 'string';
    const T_STRING_ENCRYPT = 'encryptString';
    const T_ARRAY = 'array';


    static protected $staticInstanceIdx = 1;
    /**
     * @var mixed
     */
    protected $instanceId = 0;

    /**
     * @var \Tk\ObservableSlave
     */
    private $observable = null;

    /**
     * @var bool
     */
    protected $notifyEnabled = true;


    //////////////////////// Helper Functions //////////////////////////////
    /**
     * Get the request object
     *
     * @return \Tk\Request
     */
    public function getRequest()
    {
        return $this->getConfig()->getRequest();
    }

    /**
     * Get the session object
     *
     * @return \Tk\Session
     */
    public function getSession()
    {
        return $this->getConfig()->getSession();
    }

    /**
     * Get the request Uri \Tk\Url object
     *
     * @return \Tk\Url
     */
    public function getUri()
    {
        return $this->getRequest()->getRequestUri();
    }

    /**
     * Get the config object
     *
     * @return \Tk\Config
     */
    public function getConfig()
    {
        return Config::getInstance();
    }


    ////////////////////////////////////////////////////////////////////////


    /**
     * Take a class in the form of Tk_Some_Class
     * And convert it to a namespace class like \Tk\Some\Class
     *
     *
     * @param string $class
     * @return string
     */
    static function toNamespace($class)
    {
        if (strpos($class, '\\') != -1 && strpos($class, '_') > -1) {
            $class = '\\'.str_replace('_', '\\', $class);
        }
        return $class;
    }

    /**
     * Take a class in the form of \Tk\Some\Class
     * And convert it to a namespace class like Tk_Some_Class
     *
     *
     * @param string $class
     * @return string
     */
    static function fromNamespace($class)
    {
        if (strpos($class, '_') != -1 && strpos($class, '\\') > -1) {
            $class = str_replace('\\', '_', $class);
            if ($class[0] == '_')
                $class = substr($class, 1);
        }
        return $class;
    }

    /**
     * Get the path of a class
     *
     * @param string|Object $class
     * @return string
     */
    static function classpath($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        $rc = new \ReflectionClass($class);
        return $rc->getFileName();
    }

    /**
     * Get the url path of a class
     *
     * @param string|Object $class
     * @return string
     */
    static function classurl($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        $rc = new \ReflectionClass($class);
        $path = $rc->getFileName();
        if (startsWith($path, \Tk\Config::getInstance()->getSitePath())) {
            return str_replace(\Tk\Config::getInstance()->getSitePath(), '' , $path);
        }
        return basename($path);
    }


    /**
     * Get the Observable control object
     *
     * @return \Tk\ObservableSlave
     */
    public function getObservable()
    {
        if (!$this->observable) {
            $this->observable = new ObservableSlave($this);
        }
        return $this->observable;
    }


    /**
     * Notify all observers of the uncaught Exception
     * so they can handle it as needed.
     *
     * @param string $name          (Optional) The name of the event to notify/fire
     * @return \Tk\Object
     */
    public function notify($name = '')
    {
        if ($this->notifyEnabled) {
            $this->getObservable()->notify($name);
        }
        return $this;
    }

    /**
     * If set to false then config parameters can be set
     * without calling the observers attached.
     * Used when setting config parameters from observer objects.
     *
     *
     * @param bool $b
     */
    public function enableNotify($b = true)
    {
        $this->notifyEnabled = $b;
    }


    /**
     * Attaches an SplObserver to
     * the ExceptionHandler to be notified
     * when an uncaught Exception is thrown.
     *
     * @param \Tk\Observer $obs      The observer to attach
     * @param string $name          (optional) The event name to attache the observer to
     * @param int $idx          (optional) The position to insert the observer into
     * @return \Tk\Observer
     */
    public function attach(Observer $obs, $name = '', $idx = null)
    {
        $this->getObservable()->attach($obs, $name, $idx);
        return $obs;
    }

    /**
     * Detaches the SplObserver object from the stack
     *
     * @param \Tk\Observer        The observer to detach
     * @return \Tk\Object
     */
    public function detach(Observer $obs)
    {
        $this->getObservable()->detach($obs);
        return $obs;
    }



    /**
     * Get a unique ID that will remain the same per page load.
     * TODO: Test this, as dynamic object could dissrupt the sequence
     * if they do not exist in the next page load, this can be fixed by
     * not calling this method from those dynamic objects.
     *
     * @return int
     */
    public function getInstanceId()
    {
        if (!$this->instanceId) {
            $this->instanceId = self::$staticInstanceIdx++;
        }
        return $this->instanceId;
    }

    /**
     * Set this instance id to match other instances of modules for syncing
     * object id's
     *
     * @param int $id
     */
    public function setInstanceId($id)
    {
        $this->instanceId = $id;
    }

    /**
     * Create a unique key name for this object
     *
     * @param string $key
     * @return string
     */
    public function getObjectKey($key)
    {
        return self::makeObjectKey($key, $this->getInstanceId());
    }

    /**
     * Create a unique key name for an object using the supplied id
     * This can be nessasery to create event/request names that do not clash
     * Tipically the object Id is used to make the key
     *
     * @param string $key
     * @param int $keyId
     * @return string
     */
    static function makeObjectKey($key, $keyId)
    {
        return $key . '_' . $keyId;
    }

    /**
     * Get a list of constant name value pairs for a passed class name
     *
     * @param string $class A
     * @param string $prefix If set will only return const values whose name starts with this prefix
     * @throws \InvalidArgumentException
     * @return array
     */
    static function getClassConstants($class, $prefix = '')
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException('Class Not Found!');
        }
        $oReflect = new \ReflectionClass($class);
        $constList = $oReflect->getConstants();
        if (!$prefix) {
            return $constList;
        }
        $retList = array();
        foreach ($constList as $k => $v) {
            if (substr($k, 0, strlen($prefix)) == $prefix) {
                $retList[$k] = $v;
            }
        }
        return $retList;
    }

    /**
     * Get a list of constant name value pairs for this class
     *
     * @param string $prefix If set will only return const values whose name starts with this prefix
     * @return array
     */
    public function getConstantList($prefix = '')
    {
        return self::getClassConstants($this->getClassName(), $prefix);
    }

    /**
     * Alias for get_class()
     *
     * @param bool $basenameOnly Returns the class name without the namespace portion
     * @return string
     */
    public function getClassName($basenameOnly = false)
    {
        $class = get_class($this);
        if ($basenameOnly) {
            $c = explode('\\', $class);
            $class = end($c);
        }
        return $class;
    }

    /**
     * Get the top namespace identifyer
     * If $full is true then the entire namespace is returned less the class name
     *
     * @param bool $full
     * @return string
     */
    public function getNamespace($full = false)
    {
        $ns = get_class($this);
        $a = explode('\\', $ns);
        array_pop($a);
        if ($full) {
            return implode('\\', $a);
        }
        return array_shift($a);
    }

    /**
     * Get the class file path
     *
     * @return string
     */
    public function getClassPath()
    {
        return self::classpath($this->getClassName());
    }

    /**
     * Get the class url from the file path
     * NOTE: if the path is outside the project's sitePath then
     * only the basename portion of the path is returned.
     *
     * @return string
     */
    public function getClassUrl()
    {
        return self::classurl($this->getClassName());
    }


    /**
     * Use this to convert an object to a string.
     *
     * @param mixed $obj
     * @return string
     */
    static function ObjToStr($obj)
    {
        if (!is_string($obj)) {
            return $obj->toString();
        }
        return $obj;
    }

    /**
     * Enable isset() for object properties
     */
    public function __isset($key)
    {
        return property_exists($this, $key);
    }

    /**
     * Return a string representation of this object
     * This is the PHP magic method, used as a wrapper for
     * the System toString method.
     *
     * DO NOT CALL in your code, use toString instead.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * System object to String call
     * Override this method for your own custom objects
     *
     * @return string
     */
    public function toString()
    {
        return print_r($this, true);
    }



}