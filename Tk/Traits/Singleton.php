<?php
/*       -- TkLib Auto Class Builder --
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Tropotek Development
 *
 * Date: 6/23/14
 * Time: 8:38 AM
 */
namespace Tk\Traits;


/**
 * A Singleton Trait
 *
 * Usage:
 * <code>
 * class DbReader extends ArrayObject
 * {
 *   use Singleton;
 *
 *   ...
 * }
 * </code>
 *
 *
 */
trait Singleton
{
    private static $instance;

    public static function getInstance() {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }
        return self::$instance;
    }
}