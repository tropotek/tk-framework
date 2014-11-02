<?php

/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

namespace Tk;

/**
 * A Registry object to manage a complex array of objects
 *
 *
 *
 */
class Registry extends ArrayObject
{


    /**
     * load any data from a table and group combo
     * into the config.
     *
     * @param string $table
     * @param string $group
     * @return $this
     */
    public function importFromDb($table = 'config', $group = 'system')
    {
        $this->mergeArray(\Tk\Db\Registry::createDbRegistry($table, $group));
        return $this;
    }

    /**
     *
     *
     * @param null $arr
     * @param string $table
     * @param string $group
     * @return $this
     */
    public function exportToDb($arr = null, $table = 'config', $group = 'system')
    {
        $reg = \Tk\Db\Registry::createDbRegistry($table, $group);
        $reg->importFormArray($arr);
        $reg->saveToDb();
        $this->mergeArray($arr);
        return $this;
    }

    /**
     * Return an array with the keys renamed.
     * Form fields cannot use names like system.value
     * so they need to be converted to system-value
     *
     * @return array
     */
    public function exportFormArray()
    {
        $tmpCfg = array();
        foreach ($this->getArray() as $name => $value) {
            $name = str_replace('.', '-', $name);
            $tmpCfg[$name] = $value;
        }
        return $tmpCfg;
    }

    /**
     *
     * @param array $arr
     */
    public function importFormArray($arr)
    {
        foreach ($arr as $k => $v) {
            if (preg_match('/^_/', $k)) {
                continue;
            }
            if (is_array($v)) {
                $v = implode(',', $v);
            }
            $k = str_replace('-', '.', $k);
            $this->set($k, $v);
        }
    }


    /**
     * Parse a Tk\Config file either XML or INI
     *
     * @param string|\Tk\Path $file
     * @return $this
     * @throws \Tk\RuntimeException
     * @throws \Tk\IllegalArgumentException
     */
    public function parseConfigFile($file)
    {
        $file = Path::create($file);
        if (!$file->isReadable()) {
            return $this;
        }
        $ext = $file->getExtension();

        if ($ext == 'ini') {
            $array = $this->parseIniFile($file);
            $this->load($array);
        } elseif ($ext == 'xml') {
            $array = $this->parseXmlFile($file);
            $this->load($array);
        } elseif ($ext == 'php') {
            $this->parsePhpFile($file);
        } else {
            throw new RuntimeException('Invalid config file: ' . $file);
        }
        return $this;
    }

    /**
     * Load the config values
     *
     * @param array $array The array of parameters to find setters for
     * @param bool $overwrite
     */
    protected function load($array, $overwrite = true)
    {
        foreach ($array as $k => $v) {
            if (is_object($v) && property_exists($v, 'name') && property_exists($v, 'value')) {
                $k = $v->name;
                $v = $v->value;
            }
            if ($overwrite) {
                $this->set($k, $v);
            } else {
                if (!$this->exists($k)) {
                    $this->set($k, $v);
                }
            }
        }
    }

    /**
     * Read and apply the ini file to the registry.
     *
     * @param \Tk\Path|string $iniFile
     * @return array
     */
    protected function parseIniFile($iniFile)
    {
        $array = parse_ini_file($iniFile, true);

        $fin = array();
        foreach ($array as $name => $arr) {
            if (strpos($name, '.') !== false) {
                $fin[$name] = $arr;
            } else {
                $fin = array_merge($fin, $arr);
            }
        }
        return $fin;
    }

    /**
     * read a php \Tk\Config file. The file should be in the following format.
     * <code>
     *
     *   $config = \Tk\Config::getInstance();
     *   $config['database.default.host'] =  'hostname';
     *   $config['system.timezone'] = 'Australia';
     *
     * </code>
     * @param \Tk\Path|string $file
     * @return array
     */
    protected function parsePhpFile($file)
    {
        $config = $this;
        require_once($file);
        return $config;
    }

    /**
     * Parse a \Tk\Config xml file.
     * Example \Tk\Config File:
     *  <code>
     *    <Tk_Config>
     *     <name1>value1</name1>
     *     <name2>value2</name2>
     *     <name3 type="boolean">true</name3>
     *    </Tk_Config>
     * </code>
     *
     * @param \Tk\Path|string $xmlFile
     * @return array
     */
    protected function parseXmlFile($xmlFile)
    {
        $doc = \DOMDocument::load($xmlFile);
        $firstChild = $doc->documentElement;
        $array = array();
        foreach ($firstChild->childNodes as $node) {
            if ($node->nodeType != \XML_ELEMENT_NODE) {
                continue;
            }
            $k = $node->nodeName;
            $v = $node->nodeValue;
            $type = 'string';
            if ($node->hasAttribute('type')) {
                $type = $node->getAttribute('type');
            }
            if ($type == 'boolean') {
                $v = (strtolower($v) == 'true' || strtolower($v) == 'yes' || strtolower($v) == '1');
            }
            $array[$k] = $v;
        }
        return $array;
    }

    /**
     * Return an entry from the registry cache
     *
     * @param string $prefixName
     * @return array
     */
    public function getGroup($prefixName)
    {
        $arr = array();
        foreach ($this as $k => $v) {
            if (preg_match('/^' . $prefixName . '\./', $k)) {
                $arr[$k] = $v;
            }
        }
        return $arr;
    }

}

