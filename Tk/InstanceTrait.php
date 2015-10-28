<?php
namespace Tk;

/**
 * Class InstanceTrait
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
trait InstanceTrait
{

    /**
     * @var string
     */
    private $_instanceId = '';


    /**
     * Create request keys with prepended string
     *
     * returns: `{_instanceId}_{key}`
     *
     * @param $key
     * @return string
     */
    public function makeInstanceKey($key)
    {
        if ($this->_instanceId)
            return $this->_instanceId . '_' . $key;
        return $key;
    }

    /**
     * Set the Request instance ID string.
     * This ID will be prepended to all request keys.
     *
     * @param string $id
     * @return $this
     */
    public function setInstanceId($id = '')
    {
        $this->_instanceId = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getInstanceId()
    {
        return $this->_instanceId;
    }


}