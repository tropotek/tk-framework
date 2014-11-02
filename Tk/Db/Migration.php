<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Db;

/**
 *
 *
 * @package Tk\Db
 */
class Migration extends \Tk\Object
{

    /**
     * @var \Tk\Db\Pdo
     */
    protected $db = null;

    /**
     * @var string
     */
    protected $table = '';

    /**
     * @var \Exception
     */
    protected $error = null;


    /**
     * __construct
     *
     */
    public function __construct($table = 'migration')
    {
        $this->table = $table;
        $this->db = $this->getConfig()->getDb();
        if(!$this->db->tableExists($this->table)) {
            $this->installTable();
        }
    }

    /**
     * Return the exception if one occured.
     *
     * @return \Exception
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Return the DB object
     *
     * @return \Tk\Db\Pdo
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * This method receives a list returned from getFileList()
     * That is an array of basic objects containing path and class values...
     * This command will make a backup of the DB and restore on error.
     * NOTE This is the only command that backs up the DB, you have to
     * make backups yourself when using other comands...
     *
     * @param array $list
     * @return bool
     */
    public function executeAll($list)
    {
        $success = false;
        $this->error = null;
        $bakPath = $this->getConfig()->getTmpPath();

        $this->db->createBackup($bakPath);
        try {
            foreach ($list as $o) {
                if ($this->pathExists($o->path)) continue;
                $this->executeFile($o->path, $o->class);
                $this->getConfig()->getLog()->write('  M: ' . $o->path . ' - ' . $o->class);
            }
            $success = true;
            $this->getConfig()->getLog()->write('--------- MIGRATION SUCCESSFUL -----------');
        } catch (\Exception $e) {
            $this->error = $e;
            $success = false;
            $this->db->restoreBackup($bakPath);
            $this->getConfig()->getLog()->write('--------- MIGRATION FAILED -----------');
            $this->getConfig()->getLog()->write($e->getMessage());
        }
        if (is_file($bakPath))
            unlink($bakPath);
        return $success;
    }


    /**
     * Execute a migration class or sql script...
     * the file is then added to the db and cannot be executed again.
     *
     *
     * @param string $path
     * @param string $class
     * @return bool
     */
    public function executeFile($path, $class = '')
    {
        if ($this->pathExists($path)) {
            return false;
        }
        if (preg_match('/\.php$/i', $path)) {
            include $this->getConfig()->getSitePath() . $path;
            $obj = new $class();
            $obj->execute();
        } else {    // is sql
            $sql = file_get_contents($this->getConfig()->getSitePath().$path);
            if ($sql) {
                $this->db->multiQuery($sql);
            }
        }
        $this->insertPath($path, $class);
        return true;
    }

    /**
     * exists
     *
     * @param string $path
     * @return bool
     */
    public function pathExists($path)
    {
        $path = $this->db->escapeString($path);
        $sql = sprintf('SELECT * FROM %s WHERE path = %s LIMIT 1', $this->table, enquote($path));
        $res = $this->db->query($sql);
        if ($res->rowCount()) {
            return true;
        }
        return false;
    }

    /**
     * insert
     *
     * @param string $path
     */
    public function insertPath($path, $class)
    {
        $path = $this->db->escapeString($path);
        $class = $this->db->escapeString($class);
        $sql = sprintf('INSERT INTO %s (path, class) VALUES (%s, %s)', $this->table, enquote($path), enquote($class));
        return $this->db->exec($sql);
    }

    /**
     * delete
     *
     * @param string $path
     */
    public function deletePath($path)
    {
        $path = $this->db->escapeString($path);
        $sql = sprintf('DELETE FROM %s WHERE path = %s LIMIT 1', $this->table, enquote($path));
        return $this->db->exec($sql);
    }


    /**
     * This needs to be setup before the Migrationn Lib can operate
     *
     * @return string
     */
    protected function installTable()
    {
        $sql = <<<SQL
DROP TABLE IF EXISTS {$this->table};
CREATE TABLE {$this->table} (
  path varchar(255) NOT NULL DEFAULT '',
  class varchar(255) NOT NULL DEFAULT '' COMMENT 'for sql files use basename for PHP create classname',
--  base varchar(255) NOT NULL DEFAULT '' COMMENT 'Useful for ordering',
  PRIMARY KEY (path)
) ENGINE=InnoDB;
SQL;
        return $this->db->exec($sql);
    }



    /**
     * Recursivly get all SQL/PHP file in the supplied folder
     * Use an underscore as the files first character to hide the file.
     * Also dot files are ignored
     *
     * @param string $path
     * @return array
     */
    public function getFileList($path)
    {
        $directory = new \RecursiveDirectoryIterator($path);
        $iterator = new \RecursiveIteratorIterator($directory, \RecursiveIteratorIterator::CHILD_FIRST);
        $fileList = array();
        foreach(new \RegexIterator($iterator, '/\/sql$/') as $file) {
            $d2 = new \DirectoryIterator($file->getPathname());
            foreach(new \RegexIterator($d2, '/\.(php|sql)$/') as $f2) {
                if (preg_match('/^(_|\.)/', $f2->getBasename())) continue;
                $sitePath = '';
                if (class_exists('\Tk\Config'))
                    $sitePath = \Tk\Config::getInstance()->getSitePath();
                $o = new \stdClass();
                $o->path = str_replace($sitePath, '', $f2->getPathname());
                $o->class = '';
                if (preg_match('/\.php$/i', $o->path)) {
                    $arr = explode('/', $o->path);
                    array_shift($arr);
                    array_shift($arr);
                    $name = array_pop($arr);
                    if (preg_match('/([0-9]+\-)([a-z0-9_-]+)\.(php)/i', $name, $regs)) {
                        $o->class = '\\' . implode('\\', $arr) . '\\' . $regs[2];
                    }
                } else {
                    $o->class = basename($o->path);
                }
                $fileList[] = $o;
            }
        }
        usort($fileList, function ($a, $b){
            
            $a1 = (int)substr(basename($a->path), 0, strpos(basename($a->path), '-'));
            $b1 = (int)substr(basename($b->path), 0, strpos(basename($b->path), '-'));

            if ($a1 > $b1) {
                return 1;
            }
            if ($a1 < $b1) {
                return -1;
            }
            return 0;
        });
        return $fileList;
    }



}

