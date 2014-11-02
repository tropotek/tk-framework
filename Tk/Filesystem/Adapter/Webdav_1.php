<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Filesystem\Adapter;


/**
 * class webdav client. a php based nearly rfc 2518 conforming client.
 *
 * <code>
 * <?php
 *
 * ?>
 * </code>
 *
 * This class implements methods to get access to an webdav server.
 * Most of the methods return false on error, an passtrough integer (http response status) on success
 * or an array in case of a multistatus response (207) from the webdav server.
 * It's your responsibility to handle the webdav server responses in an proper manner.
 *
 * @package Tk
 */
class Webdav extends \Tk\Object implements Iface
{
    /**
     * @var \Tk\HttpClient
     */
    private $http = null;

    /**
     * @var \Tk\Url
     */
    private $url = null;

    /**
     * @var bool
     */
    private $debug = false;


    /**
     * Constructor
     *
     * @param \Tk\Url $url
     */
    public function __construct($url)
    {
        $this->http = new \Tk\HttpClient();
        $this->url = $url->makeFrom('/', $url->getPath());

    }
    /**
     * Create a Webdav adapter object
     *
     * @param \Tk\Url $url
     * @return Webdav
     */
    static function create($url)
    {
        $w = new self($url);
        return $w;
    }

    /**
     * getHttpClient
     *
     * @return \Tk\HttpClient
     */
    public function getHttpClient()
    {
        return $this->http;
    }

    /**
     * Set debug log to On/Off
     *
     * @param bool $b
     * @return Webdav
     */
    public function enableDebug($b = true)
    {
        if ($this->http) $this->http->enableDebug ($b);
        $this->debug = $b;
        return $this;
    }

    /**
     * prepare a path by appending the local site path
     *
     * @param string $path
     * @return \Tk\Url
     */
    protected function prepPath($path)
    {
        if ($path[0] != '/') {
            $path = '/' . $path;
        }
        rtrim($path, DIRECTORY_SEPARATOR);
        $path = str_replace(array('../', './', '//', '\\'), DIRECTORY_SEPARATOR, $path);
        return $this->url->makeFrom($path);
    }


    /**
     * Close the remote connection.
     */
    public function close()
    {
        $this->http->disconnect();
    }

    /**
     * Delete a file from the remote filesystem
     *
     * @param string $remoteFile
     * @return bool
     */
    public function unlink($remoteFile)
    {
        if ($this->http->delete($this->prepPath($remoteFile)) == '204') {
            return true;
        }
        return false;
    }

    /**
     * delete a remote directory
     * TODO: look at deep removal if contains files
     *
     * @param string $remoteDir
     * @return bool
     */
    public function rmdir($remoteDir) {   }

    /**
     * Create a directory on a remote filesystem.
     *
     * @param string $remoteDir
     * @return bool
     */
    public function mkdir($remoteDir) {   }

    /**
     *
     * @param type $remoteFile
     * @param string $mode Eg mode: 0755
     * @return bool
     */
    public function chmod($remoteFile, $mode) {   }

    /**
     * rename a file/dir on the remote filesystem
     *
     * @param string $remoteSrc  The full path to the source file
     * @param string $remoteDest    The site relative path to the destination
     * @return bool
     */
    public function rename($remoteSrc, $remoteDest) {   }

    /**
     * Copy a file or directory on the remote filesystem
     *
     * @param string $remoteSrc  The full path to the source file
     * @param string $remoteDest    The site relative path to the destination
     * @return bool
     */
    public function copy($remoteSrc, $remoteDest) {   }

    /**
     * get a list of the remote filesystem
     *
     * @link http://php.net/manual/en/function.scandir.php
     * @param string $remoteSrc
     * @param int $sortingOrder
     * @return array
     */
    public function scandir($remoteSrc, $sortingOrder = 0) {   }




    /**
     * Upload a file or directory to the remote filesystem
     *
     * @param string $localSrc
     * @param string $remoteDest
     * @return bool
     */
    public function put($localSrc, $remoteDest)
    {
        $data = file_get_contents($localSrc);
        $url = $this->prepPath($remoteDest);
        if ($this->http->put($url, $data) == '201') {
            return true;
        }
        return false;
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
        $res = $this->http->put($this->prepPath($remoteSrc));
        @file_put_contents($localDest, $res);
        if (!$res) {
            return false;
        }
        return true;
    }





    public function isFile($remotePath) {   }
    public function isDir($remotePath) {   }
    public function isLink($remotePath) {   }

    public function fileGroup($remoteFile) {   }
    public function fileOwner($remoteFile) {   }
    public function InvalidfilePerms($remoteFile) {   }

    public function isWritable($remotePath) {   }
    public function isReadable($remotePath) {   }
    public function isExecutable($remotePath) {   }

    public function fileExists($remoteFile) {   }
    public function fileAccessed($remoteFile) {   }
    public function fileCreated($remoteFile) {   }
    public function fileModified($remoteFile) {   }
    public function fileSize($remoteFile) {   }
    public function fileType($remoteFile) {   }










    /**
     * Private method log
     *
     * @param string $err
     * @param int $n
     */
    private function log($err, $n = \Tk\Log\Log::NOTICE)
    {
        if ($this->debug)
            return \Tk\Log\Log::write($err, $n);
    }

}
