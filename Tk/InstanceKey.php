<?php
namespace Tk;

/**
 * Create a unique key for object that need to be uniquely identified.
 * Implement this in objects that need a key to identify their instances
 *  within a session.
 */
interface InstanceKey
{

    /**
     * Create a unique object instance key that can be used
     * to lookup and find it within an array or session, etc
     *
     * @example `{_instanceId}_{key}`
     */
    public function makeInstanceKey(string $key): string;


}