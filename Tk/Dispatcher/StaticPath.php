<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Dispatcher;

/**
 * This dispatcher looks through its own list of URI's
 * comparing the $requestUri values until
 * a match is found then that module class name is returned.
 *
 *
 * @package Tk\Dispatcher
 */
class StaticPath extends \Tk\Object implements Iface
{

    /**
     * @var array
     */
    protected $list = array();

    /**
     * @var bool
     */
    private $overwrite = false;

    

    /**
     * Use this method to create and return the module class name
     *
     * @param \Tk\Dispatcher\Dispatcher $obs
     */
    public function update($obs)
    {
        if ($obs->getClass()) {
            return;
        }
        $path = $this->getUri()->getPath(true);
        $path = str_replace(array('./', '../'), '', $path);

        $classArr = $this->get($path);
        if ($classArr && class_exists($classArr['class'])) {
            $output = substr(basename($path), strrpos(basename($path), '.')+1 );
            $obs->setClass($classArr['class']);
            $obs->setOutput($output);
            if (!empty($classArr['params'])) {
                $obs->setParams($classArr['params']);
            }
        }
    }

    /**
     * Get the class if path found
     *
     * @param string $path
     * @return array
     */
    public function get($path)
    {
        $path = rtrim($path, '/');
        $this->overwrite = false;
        if (!$this->exists($path)) {
            $path .= '/index.html';
        }
        if ($this->exists($path)) {
            return $this->list[$path];
        }
    }

    /**
     * Get teh entire class page list.
     *
     * @return array
     */
    public function getList()
    {
        $this->overwrite = false;
        return $this->list;
    }

    /**
     * This can be called just prior to add to overwrite existing paths.
     * <code>
     *   $staticPath->overwrite()->add('/index.html', '\Ext\Module\Login');
     *   $staticPath->add('/index.html', '\Ext\Module\Login');
     * </code>
     * This call would overwrite any existing stored paths.
     * The following call after it would not. The overwrite flag is reset
     * after each call to add()
     *
     *
     * @return StaticPath
     */
    final public function overwrite()
    {
        $this->overwrite = true;
        return $this;
    }

    /**
     * Add a uri to the list
     *
     * @param string $path          The relative request path (EG: /index.html, /viewBlogList.html)
     * @param string $class         The map containing the required resource info.
     * @param array $params         (Optional) 
     * @return \Tk\Dispatcher\StaticPath
     */
    public function add($path, $class, $params = array())
    {
        $path = rtrim($path, '/');
        if (!isset($this->list[$path]) || $this->overwrite) {
        	$this->list[$path] = array('class' => $class, 'params' => $params);
        }
        $this->overwrite = false;
        return $this;
    }

    /**
     * Test if a map exists
     *
     * @param string $path
     * @return bool
     */
    public function exists($path)
    {
        $this->overwrite = false;
        if (isset($this->list[$path])) {
            return true;
        }
        return false;
    }

    /**
     * Remove a map from the list
     *
     * @param string $path
     * @return \Tk\Dispatcher\StaticPath
     */
    public function remove($path)
    {
        $this->overwrite = false;
        if (isset($this->list[$path])) {
            unset($this->list[$path]);
        }
        return $this;
    }


}
