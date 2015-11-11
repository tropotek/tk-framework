<?php
namespace Tk\Db;

/**
 * Interface Serializable
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
interface Mappable {

    /**
     * Map the data from a DB row to the required object
     *
     * Input: array (
     *   'tblColumn' => 'columnValue'
     * )
     *
     * Output: Should return an \stdClass or \Tk\Model object
     *
     * @param Model|\stdClass|array $row
     * @return Model|\stdClass
     * @since 2.0.0
     */
    public function map($row);

    /**
     * Un-map an object to an array ready for DB insertion.
     * All filds and types must match the required DB types.
     *
     * Input: This requires a \Tk\Model or \stdClass object as input
     *
     * Output: array (
     *   'tblColumn' => 'columnValue'
     * )
     *
     * @param Model|\stdClass $obj
     * @return array
     * @since 2.0.0
     */
    public function unmap($obj);

}