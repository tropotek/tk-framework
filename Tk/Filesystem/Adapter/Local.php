<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Filesystem\Adapter;

/**
 *
 *
 *
 * @package \Tk\Filesystem\Adapter
 */
class Local extends \Tk\Object implements Iface
{

    /**
     * @var string
     */
    protected $basePath = '';



    /**
     * __construct
     *
     *
     * @param string $basePath (optional) Set a base path to operate in
     */
    public function __construct($basePath = '')
    {
        if ($basePath) {
            $this->setBasePath($basePath);
        }

        if (!$this->isWritable('/')) {
            throw new \Tk\Filesystem\Exception(get_class($this) . ' cannot write to the target filesystem. Use another adaptor for the path: ' . $basePath);
        }

    }

    /**
     * Create a Webdav adapter object
     *
     * @param \Tk\Url $url
     * @return Local
     */
    static function create($basePath = '')
    {
        return new self($basePath);
    }

    /**
     * prepare a path by appending the local site path
     *
     * @param string $path
     * @return string
     */
    protected function prepPath($path)
    {
        if ($path[0] != '/') {
            $path = '/' . $path;
        }
        rtrim($path, DIRECTORY_SEPARATOR);
        $path = str_replace(array('../', './', '//', '\\'), DIRECTORY_SEPARATOR, $this->basePath . $path);
        return $path;
    }

    /**
     * Set the base path for this adapter to manipulate file in
     * NOTE: Make sure the file permission are set appropriatly
     *
     * @param string $basePath
     * @return Local
     */
    public function setBasePath($basePath)
    {
        if ($basePath[0] != DIRECTORY_SEPARATOR) {
            $basePath = DIRECTORY_SEPARATOR . $basePath;
        }
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
        return $this;
    }
    


    /**
     * Close Connection
     *
     * @return bool
     */
    public function close()
    {
        return true;
    }


    public function unlink($remoteFile)
    {
        return unlink($this->prepPath($remoteFile));
    }

    public function rmdir($remoteDir)
    {
        return rmdir($this->prepPath($remoteDir));
    }

    public function mkdir($remoteDir)
    {
        $path = $this->prepPath($remoteDir);
        if (is_dir($path)) {
            return true;
        } else if(is_file($path)) {
            return false;
        }
        return mkdir($path);
    }

    public function chmod($remoteFile, $mode)
    {
        return chmod($this->prepPath($remoteFile), $mode);
    }

    /**
     * rename
     *
     * @param string $remoteSrc     The full path to the source file
     * @param string $remoteDest    The site relative path to the destination
     * @return bool
     */
    public function rename($remoteSrc, $remoteDest)
    {
        return rename($this->prepPath($remoteSrc), $this->prepPath($remoteDest));
    }

    /**
     * scandir
     *
     * @link http://php.net/manual/en/function.scandir.php
     * @param string $directory
     * @param int $sortingOrder
     * @return array
     */
    public function scandir($remoteSrc, $sortingOrder = 0)
    {
        return scandir($this->prepPath($remoteSrc), $sortingOrder);
    }

    /**
     * copy
     *
     * @param string $remoteSrc  The full path to the source file
     * @param string $remoteDest    The site relative path to the destination
     * @return bool
     */
    public function copy($remoteSrc, $remoteDest)
    {
        return copy($this->prepPath($remoteSrc), $this->prepPath($remoteDest));
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
        return copy($localSrc, $this->prepPath($remoteDest));
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
        return copy($this->prepPath($remoteSrc), $localDest);
    }





    public function isFile($remotePath)
    {
        return is_file($this->prepPath($remotePath));
    }

    public function isDir($remotePath)
    {
        return is_dir($this->prepPath($remotePath));
    }

    public function isLink($remotePath)
    {
        return is_link($this->prepPath($remotePath));
    }


    public function fileGroup($remoteFile)
    {
        return filegroup($this->prepPath($remoteFile));
    }

    public function fileOwner($remoteFile)
    {
        return fileowner($this->prepPath($remoteFile));
    }

    public function filePerms($remoteFile)
    {
        return fileperms($this->prepPath($remoteFile));
    }


    public function isWritable($remotePath)
    {
        return is_writable($this->prepPath($remotePath));
    }

    public function isReadable($remotePath)
    {
        return is_readable($this->prepPath($remotePath));
    }

    public function isExecutable($remotePath)
    {
        return is_executable($this->prepPath($remotePath));
    }


    public function fileExists($remoteFile)
    {
        return file_exists($this->prepPath($remoteFile));
    }

    public function fileAccessed($remoteFile)
    {
        $ts = fileatime($this->prepPath($remoteFile));
        return $ts;
    }

    public function fileCreated($remoteFile)
    {
        $ts = filectime($this->prepPath($remoteFile));
        return $ts;
    }

    public function fileModified($remoteFile)
    {
        $ts = filemtime($this->prepPath($remoteFile));
        return $ts;
    }

    public function fileSize($remoteFile)
    {
        return filesize($this->prepPath($remoteFile));
    }

    public function fileType($filename)
    {
        return filetype($this->prepPath($filename));
    }



}