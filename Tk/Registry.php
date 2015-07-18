<?php
/**
 * Created by PhpStorm.
 * User: mifsudm
 * Date: 6/17/15
 * Time: 8:32 AM
 */

namespace Tk;

/**
 * Class Registry
 *
 * This registry class is a specific array type object to contain the
 * applications config and dependency values and functions.
 *
 * It can be used as a standard array it extends the \Tk\ArrayObject
 * Example usage:
 * <code>
 * <?php
 * $request = Request::createFromGlobals();
 * $cfg = new \Tk\Registry();
 *
 * $cfg->setAppPath($appPath);
 * $cfg->setRequest($request);
 * $cfg->setAppUrl($request->getBasePath());
 * $cfg->setAppDataPath($cfg->getAppPath().'/data');
 * $cfg->setAppCachePath($cfg->getAppDataPath().'/cache');
 * $cfg->setAppTempPath($cfg->getAppDataPath().'/temp');
 * // Useful for dependency management to create application objects
 * $cfg->setStdObject(function($test1, $test2, $test3) {
 *     $obj = new \stdClass();
 *     $obj->test1 = $test1;
 *     $obj->test2 = $test1;
 *     $obj->test3 = $test1;
 *     return $obj;
 * });
 *
 * $var = $cfg->getStdObject('test param', 'test2', 'test3');
 * // or
 * $var = $cfg->createStdObject('test param', 'test2', 'test3');
 * // or
 * $var = $cfg->isStdObject('test param', 'test2', 'test3');
 * var_dump($var);
 *
 *
 *  // Output:
 *  //  object(stdClass)[15]
 *  //      public 'test1' => string 'test param' (length=10)
 *  //      public 'test2' => string 'test param' (length=10)
 *  //      public 'test3' => string 'test param' (length=10)
 *
 *  // The following returns the closure object not the result
 *
 * $var = $cfg->get('std.object');
 * var_dump($var);
 *
 * // Output
 * // object(Closure)[14]
 *
 *
 * </code>
 *
 * Internally the Registry values are stored in an array. So to set a value there is a couple of ways to do this:
 *
 *   $cfg->setSitePath($path);
 *   same as
 *   $sfg['site.path'] = $path
 *
 * To get a values stored in the registry you can do the following using the array access methods:
 *
 *   $val = $cfg->getSitePath();
 *   same as
 *   $val = $cfg['site.path']
 *
 * NOTICE: When using the array access methods to get a closure (anonymous function) the
 * closure object will be returned. You must call getClosureObject($params) to execute the closure function
 * and return the executed result. (see above example)
 *
 *
 *
 *
 * @package Tk
 */
class Registry extends ArrayObject
{

    /**
     * Import params from another registry object or array
     *
     * @param Registry|array $params
     * @return $this
     */
    public function import($params)
    {
        foreach($params as $k => $v) {
            $this[$k] = $v;
        }
        return $this;
    }

    /**
     * Allow call to parameters via a get and set
     *
     * For example if the following entries exist in the registry:
     *
     *   array(
     *    'site.path' => '/path/to/site',
     *    'site.url' => '/url/to/site'
     * )
     *
     * Then they can be accessed by the following virtual methods:
     *
     *   $registry->getSitePath();
     *   $registry->setSitePath('/');
     *
     * @param string $func
     * @param array  $argv
     * @return mixed | null
     */
    public function __call($func, $argv)
    {
        $key = preg_replace('/[A-Z]/', '.$0', $func);
        $key = strtolower($key);

        $pos = strpos($key, '.');
        $type = substr($key, 0, $pos);
        $key = substr($key, $pos+1);

        if ($type == 'set') {
            $this->set($key, $argv[0]);
        } else if ($type == 'get' || $type = 'create' | $type = 'is' | $type = 'has') {
            $val = $this->get($key);
            if (is_callable($val)) {
                return call_user_func_array($val, $argv);
            }
            return $val;
        }
        return null;
    }



    /**
     * Return a group of entries from the registry
     *
     * for example if the prefixName = 'app.site'
     *
     * it would return all registry values with the key starting with `app.site.____`
     *
     * @param string $prefixName
     * @param boolean $truncateKey If true then the supplied $prefixName will be removed from the returned keys
     * @return array
     */
    public function getGroup($prefixName, $truncateKey = false)
    {
        $arr = array();
        foreach ($this as $k => $v) {
            if (preg_match('/^' . $prefixName . '\./', $k)) {
                if (!$truncateKey) {
                    $arr[$k] = $v;
                } else {
                    $arr[str_replace($prefixName.'.', '', $k)] = $v;
                }
            }
        }
        return $arr;
    }

}