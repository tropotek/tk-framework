<?php
namespace Tk;

/**
 * InstanceKey
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
interface InstanceKey
{

    /**
     * Create request keys with prepended string
     *
     * returns: `{_instanceId}_{key}`
     *
     * @param $key
     * @return string
     */
    public function makeInstanceKey($key);


}