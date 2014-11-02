<?php
/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Filesystem;

/**
 * An abstracted file system environment to support FTP/SSH/local filesystems
 * This object allows our applications to do file manupulation to site's files
 * and retain its permissions.
 *
 * For example, if the site is setup using suphp and the session is owned
 * by the account user, then we can use the local adapter and local filesystem
 * functions without modifications.
 *
 * However if the server runs apache/php as a user like www-data or apache using
 * local filesystem commands results in the inability to acces files or creates files
 * that cannot be deleted via FTP because they are owned by the wrong user.
 *
 * Since we do not want to encorage mod 777 for the site root directory, as this is
 * insecure and could result is sites being dissabled form some servers, we have an
 * FTP file system adaptor wish will use ftp to do the required file operations.
 *
 * It is important to note that in our framework, the site's data path will remain
 * writable and can be used as a temp storage for adapter file operations.
 *
 * This class has been initially written to facilitate the Orca project in its
 * template and plugin installs and also for its self upgrade facility.
 *
 * NOTICE: All file paths are to be relative to the site path, eg: /data, /lib/Tk/file.php
 * and access to files outside the site root path is considered illeagl and will produce errors.
 *
 * @package \Tk\Filesystem
 */
class Filesystem extends \Tk\Object
{
    /**
     * @var \Tk\Filesystem\Adapter\Iface
     */
    protected $fs = null;

    /**
     * @var string
     */
    protected $rootPath = '';

    /**
     * Ignore list fo methods like copyTree....
     * @var array
     */
    protected $ignore = array('.svn', '.CSV', 'Thumbs.db', 'thumbs.db');

    /**
     * Data for observers
     * @var array
     */
    protected $obsData = array();


    /**
     * __construct
     *
     *
     * @param \Tk\Filesystem\Adapter\Iface $adapter
     * @param string $rootPath (Optional) The local filesystem site path, if set then all other file paths must be relative to this.
     */
    public function __construct(Adapter\Iface $adapter, $rootPath = '')
    {
        $this->fs = $adapter;
        $this->rootPath = $rootPath;
    }

    /**
     * Get copyIgnore arrays
     *
     * @return array
     */
    public function getIgnoreList()
    {
        return $this->ignore;
    }

    /**
     * Set CopyIgnore array
     * This is an array of files to ignore when using copyTree() method
     * Default: array('.svn', '.CSV');
     *
     * @param array $array
     * @return \Tk\Filesystem\Filesystem
     */
    public function setIgnoreList($array)
    {
        if (is_array($array)) {
            $this->ignore = $array;
        }
        return $this;
    }

    /**
     * Get the local filesystem site path.
     *
     * @return string
     */
    public function getSitePath()
    {
       return $this->rootPath;
    }

    /**
     * prepare a path by appending the local site path
     *
     * @param string $path
     * @return string
     */
    protected function prepPath($path)
    {
        $path = rtrim($path, '/');
        if ($path[0] != '/' || $path[0] != '\\') {
            $path = '/'.$path;
        }
        if ($this->rootPath) {
            $path = str_replace($this->rootPath, '', $path);
            $path = $this->rootPath . $path;
        }
        $path = str_replace(array('../', './', '//'), '/', $path);
        return $path;
    }

    /**
     * Close Connection
     *
     * @return bool
     */
    public function close()
    {
        return $this->fs->close();
    }

    /**
     * Used for observers to get relevent data..
     *
     * @return array
     */
    public function getObsData()
    {
        return $this->obsData;
    }


    /**
     * Copy a local source directory tree to the remote filesystem
     *
     * NOTE: The dest path must exist, and the dest will be overwritten if exists.
     *
     * @param string $source A site relative path on the local filesystem to copy EG: '/data/files'
     * @param string $dest A site relative path on the remote filesystem. EG: '/lib/Ext/Dest/'
     * @todo: figure a way to use FTP retries with this method
     */
    public function putTree($source, $dest)
    {
        $dest = rtrim($dest, DIRECTORY_SEPARATOR);
        if (!is_dir($this->prepPath($source))) {
            throw new Exception('Path not a valid directory, cannot copy contents: ' . $this->prepPath($source));
        }
        $d = dir($this->prepPath($source));
        while ($file = $d->read()) {
            if ($file == '.' || $file == '..' || in_array($file, $this->ignore)) {
                continue;
            }
            $this->obsData = array('file' => $file, 'source' => $source, 'dest' => $dest);
            $this->notify();

            //\Tk\Log\Log::write(str_replace($this->getConfig()->getSitePath(), '', $source) . '/' . $file);
            \Tk\Log\Log::write($dest . '/' . $file);
            if (is_dir($this->prepPath($source) . '/' . $file)) {
                if (!$this->isDir($dest . '/' . $file)) {
                    if (!$this->mkdir($dest . '/' . $file)) {
                        return false;
                    }
                }
                if (!$this->putTree($source . '/' . $file, $dest . '/' . $file)) {
                    return false;
                }
            } else {
                if (!$this->put($source . '/' . $file, $dest . '/' . $file)) {
                    return false;
                }
            }
        }
        $d->close();
        return true;
    }

    /**
     * Delete a directory and all of its contents
     *
     * WARNING: Be vary aware when using this function in development.
     *          BACKUP YOUR FILES!, Enough said!
     *          Consider yourself warned...
     *
     * @param string $dirname
     */
    public function rmTree($dirname)
    {
        if (!$this->isDir($dirname)) {
            //throw new Tk\Filesystem\Exception('Path not a valid directory, cannot copy contents');
            \Tk\Log\Log::write('Path not a valid directory: ' . $dirname, \Tk\Log\Log::NOTICE);
            return false;
        }
        $list = $this->scandir($dirname);
        foreach ($list as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if ($this->isDir($dirname . '/' . $file)) {
                $this->rmTree($dirname . '/' . $file);
            } else {
                $this->unlink($dirname . '/' . $file);
            }
        }
        $this->rmdir($dirname);
        return true;
    }





    /**
     * rename a file/dir on the remote filesystem
     *
     * @param string $remoteSrc  The full path to the source file
     * @param string $remoteDest    The site relative path to the destination
     * @return bool
     */
    public function rename($remoteSrc, $remoteDest)
    {
        return $this->fs->rename($remoteSrc, $remoteDest);
    }

    /**
     * Copy a file or directory on the remote filesystem
     *
     * @param string $remoteSrc  The full path to the source file
     * @param string $remoteDest    The site relative path to the destination
     * @return bool
     */
    public function copy($remoteSrc, $remoteDest)
    {
        return $this->fs->copy($remoteSrc, $remoteDest);
    }

    /**
     * Upload a file or directory to the remote filesystem
     *
     * @param string $localSrc
     * @param string $remoteDest
     * @return bool
     */
    public function put($localSrc, $remoteDest)
    {
        $localSrc = $this->prepPath($localSrc);
        return $this->fs->put($localSrc, $remoteDest);
    }

    /**
     * Download a file or directory from the remote filesystem
     *
     * @param string $remoteSrc
     * @param string $localDest
     * @return bool
     */
    public function get($remoteSrc, $localDest)
    {
        $localDest = $this->prepPath($localDest);
        return $this->fs->get($remoteSrc, $localDest);
    }

    /**
     * Delete a file from the remote filesystem
     *
     * @param string $remoteFile
     * return boolean
     */
    public function unlink($remoteFile)
    {
        return $this->fs->unlink($remoteFile);
    }

    /**
     * delete a remote directory
     * TODO: look at deep removal if contains files
     *
     * @param string $remoteDir
     * @return bool
     */
    public function rmdir($remoteDir)
    {
        return $this->fs->rmdir($remoteDir);
    }

    /**
     * Create a directory on a remote filesystem.
     *
     * @param string $remoteDir
     * @return bool
     */
    public function mkdir($remoteDir)
    {
        return $this->fs->mkdir($remoteDir);
    }

    /**
     *
     * @param type $remoteFile
     * @param string $mode Eg mode: 0755
     * @return bool
     */
    public function chmod($remoteFile, $mode)
    {
        return $this->fs->chmod($remoteFile, $mode);
    }

    /**
     * get a list of the remote filesystem
     *
     * @param string $remoteSrc
     * @param int $sortingOrder
     * @return array
     */
    public function scandir($remoteFile = '/', $sortingOrder = 0)
    {
        return $this->fs->scandir($remoteFile, $sortingOrder);
    }





    public function fileExists($remoteFile)
    {
        return $this->fs->fileExists($remoteFile);
    }

    public function fileAccessed($remoteFile)
    {
        return $this->fs->fileAccessed($remoteFile);
    }

    public function fileCreated($remoteFile)
    {
        return $this->fs->fileCreated($remoteFile);
    }

    public function fileModified($remoteFile)
    {
        return $this->fs->fileModified($remoteFile);
    }

    public function fileSize($remoteFile)
    {
        return $this->fs->fileSize($remoteFile);
    }

    public function fileType($remoteFile)
    {
        return $this->fs->fileType($remoteFile);
    }


    public function fileGroup($remoteFile)
    {
        return $this->fs->fileGroup($remoteFile);
    }

    public function fileOwner($remoteFile)
    {
        return $this->fs->fileOwner($remoteFile);
    }

    public function filePerms($remoteFile)
    {
        return $this->fs->filePerms($remoteFile);
    }


    public function isWritable($remotePath)
    {
        return $this->fs->isWritable($remotePath);
    }

    public function isReadable($remotePath)
    {
        return $this->fs->isReadable($remotePath);
    }

    public function isExecutable($remotePath)
    {
        return $this->fs->isExecutable($remotePath);
    }


    public function isFile($remoteFile)
    {
        return $this->fs->isFile($remoteFile);
    }

    public function isDir($remoteFile)
    {
        return $this->fs->isDir($remoteFile);
    }

    public function isLink($remoteFile)
    {
        return $this->fs->isLink($remoteFile);
    }




}
