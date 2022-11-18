<?php
namespace Tk\Db\Mapper;

/**
 * All Tk DB models that want to use the DB mapper should implement this.
 *
 * I have implemented this so that the framework can use DB models
 * and depend on a set of functions.
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
interface ModelInterface
{

    /**
     * Get the model primary key, usually an ID int value
     * But it could return a string or an array in the case of a multiple primary key
     *
     * Only objects returning an int or string should be updatable/insertable.
     *
     * @return mixed
     */
    public function getId();

    /**
     * Returns the current id if > 0 or the `nextInsertId` if == 0
     *
     * @note models using string|array type as a primary key will return 0
     * @return mixed
     */
    public function getVolatileId();


    /**
     * Insert the object into storage.
     *
     * @return int The insert ID
     */
    public function insert(): int;

    /**
     * Update the object in storage
     *
     * @return int The number of rows updated
     */
    public function update(): int;

    /**
     * A Utility method that checks the id and does and insert
     * or an update  based on the objects current state
     */
    public function save();

    /**
     * Delete the object from the DB
     *
     * @return int The number of rows deleted
     */
    public function delete(): int;

}