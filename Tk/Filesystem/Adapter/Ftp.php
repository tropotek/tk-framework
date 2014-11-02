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
class Ftp extends \Tk\Object implements Iface
{

    /**
     * @var
     */
    protected $ftp = null;

    /**
     * @var array
     */
    protected $cfg = array();

    /**
     * @var array
     */
    protected $listCache = array();

    /**
     * @var bool
     */
    private $debug = false;






    /**
     * __construct
     *
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param int $port
     * @param string $remotePath
     */
    public function __construct($host, $user, $pass, $remotePath = '', $port = 21, $retries = 3, $ftpPasv = 0)
    {
        $this->cfg['host'] = $host;
        $this->cfg['user'] = $user;
        $this->cfg['pass'] = $pass;
        $this->cfg['port'] = $port;
        $this->cfg['remotePath'] = rtrim($remotePath, '/');
        $this->cfg['retries'] = $retries;
        $this->cfg['ftpPasv'] = $ftpPasv;
        $this->cfg['showHidden'] = true;
    }

    /**
     * __destruct
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Connect to an FTP host
     *
     * @return ftp_resource
     * @throws Tk\Filesystem\Exception
     */
    public function connect()
    {
        $retries = 0;

        while (!$this->ftp && ($retries == 0 || ($retries > 0 && $this->cfg['retries'] < $retries))) {
            $this->ftp = ftp_connect($this->cfg['host'], $this->cfg['port']);
            if ($this->ftp) {
                $login = ftp_login($this->ftp, $this->cfg['user'], $this->cfg['pass']);
                if ($login) {
                    ftp_pasv($this->ftp, $this->cfg['ftpPasv']);
                } else {
                    //Tk\Log::write('  Unable to login to ' . $this->cfg['host'] . ' using ' . $this->cfg['user'] . ':' . $this->cfg['pass'], \Tk\Log\Log::NOTICE);
                    throw new \Tk\Filesystem\Exception('Unable to login to ' . $this->cfg['host'] . ' using ' . $this->cfg['user'] . ':' . $this->cfg['pass']);
                }
            } else {
                  $this->log('  Unable to establish FTP connection to ' . $this->cfg['host'], \Tk\Log\Log::NOTICE);
            }
            sleep(5);
            $retries++;
        }

        if (!$this->ftp) {
            $this->ftp = null;
            throw new Tk\Filesystem\Exception('Retry Timeout: Unable to establish FTP connection to ' . $this->cfg['host']);
        } else {
            $this->log('  FTP Connection Successful on ' . $this->cfg['host'] . ' at ' . ftp_pwd($this->ftp));

            $pwd = ftp_pwd($this->ftp);
            $this->log('  Login Path: ' . $pwd );
            if ($this->cfg['remotePath']) {
                if (@ftp_chdir($this->ftp, $this->cfg['remotePath'])) {
                    $this->log('  Site Remote Path: ' . $this->cfg['remotePath'] );
                } else {
                    throw new \Tk\Filesystem\Exception('Unable to chdir into site remote path at: ' . $this->cfg['remotePath']);
                }
            }
        }

        return $this->ftp;
    }

    /**
     *
     *
     * @return bool
     */
    protected function isConnected()
    {
        // ping server for timeout etc....
        if (!$this->ftp || !$this->isAlive()) {
            $this->reconnect();
        }
        if ($this->ftp) {
            return true;
        }
        return false;
    }


    public function isAlive()
    {
        if (is_array(ftp_nlist($this->ftp, '.'))) {
            return true;
        }
        return false;
    }


    /**
     * Close and then open the ftp connection
     */
    public function reconnect()
    {
        $this->close();
        $this->connect();
    }

    /**
     * Close Ftp Connection
     */
    public function close()
    {
        if ($this->ftp) {
            ftp_close($this->ftp);
            $this->log('  FTP Connection Closed on ' . $this->cfg['host']);
        }
        $this->ftp = null;
        return true;
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
        $path = $this->cfg['remotePath'] . $path;
        $path = str_replace(array('../', './', '//', '\\'), DIRECTORY_SEPARATOR, $path);
        return $path;
    }


    /**
     * Clear the list cache, sending null as a parameter will clear the entire cache
     *
     * @param string $key
     * @return void
     */
    public function clearListCache($key = null)
    {
        if ($key && isset($this->listCache[$key])) {
            unset($this->listCache[$key]);
            return true;
        }
        if ($key === null) {
            $this->listCache = array();
            return true;
        }
        return false;
    }

    /**
     * Set debug log to On/Off
     *
     * @param bool $b
     */
    public function enableDebug($b = true)
    {
        $this->debug = $b;
        return $this;
    }






    public function unlink($remoteFile)
    {
        if (!$this->isConnected()) {
            return false;
        }
        $this->clearListCache(dirname($remoteFile));

        $this->log('    ftp_delete(' . $remoteFile . ')');
        return ftp_delete($this->ftp, $this->prepPath($remoteFile));
    }

    public function rmdir($remoteDir)
    {
        if (!$this->isConnected()) {
            return false;
        }
        $this->clearListCache($remoteDir);

        $this->log('    ftp_rmdir(' . $remoteDir . ')');
        return ftp_rmdir($this->ftp, $this->prepPath($remoteDir));
    }

    public function mkdir($remoteDir)
    {
        if (!$this->isConnected()) {
            return false;
        }
        $this->clearListCache($remoteDir);

        $this->log('    ftp_mkdir(' . $remoteDir . ')');
        return ftp_mkdir($this->ftp, $this->prepPath($remoteDir));
    }

    public function chmod($remoteFile, $mode)
    {
        if (!$this->isConnected()) {
            return false;
        }
        $this->clearListCache();

        $this->log('    ftp_chmod(' . $remoteFile . ')');
        return @ftp_chmod($this->ftp, $mode, $this->prepPath($remoteFile));
    }



    /**
     * rename
     *
     * @param string $remoteSrc  The full path to the source file
     * @param string $remoteDest    The site relative path to the destination
     * @return bool
     */
    public function rename($remoteSrc, $remoteDest)
    {
        if (!$this->isConnected()) {
            return false;
        }
        $this->clearListCache();
        $this->log('    ftp_rename(' . $remoteSrc . ' , ' . $remoteDest . ')');
        return ftp_rename($this->ftp, $this->prepPath($remoteSrc), $this->prepPath($remoteDest));
    }

    /**
     * copy
     * TODO: copy file on remote system if directory do deep copy of directory....
     *
     *
     * @param string $remoteSrc  The full path to the source file
     * @param string $remoteDest    The site relative path to the destination
     * @return bool
     */
    public function copy($remoteSrc, $remoteDest)
    {
        throw new \Exception('Method not implemented yet.');

//        if (!$this->isConnected()) {
//            return false;
//        }
//        $this->clearListCache();
//        // TODO: Auto detect text files and use FTP_TEXT
//        $this->log('    ~_copy(' . $remoteSrc . ' , ' . $remoteDest . ')');
//
//
//        return ftp_($this->ftp, $this->prepPath($remoteDest), $this->prepPath($remoteSrc), FTP_BINARY);
//
//        $tmpPath = $this->getConfig()->getTempPath();
//        if(ftp_get($this->ftp, $tmpPath.$img, $pathftp.'/'.$img ,FTP_BINARY)){
//                if(ftp_put($this->ftp, $pathftpimg.'/'.$img ,$tmpPath.$img , FTP_BINARY)){
//                        unlink($tmpPath.$img) ;
//                }
//        }


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
        throw new Exception('Not Implemented Yet!');
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
        throw new Exception('Not Implemented Yet!');
    }






    /**
     * listdir
     *
     * @param string $dirname
     * @param int $sorting_order
     * @return array
     */
    public function ftpListdir($dirname, $sortingOrder = 0)
    {
        if (!$this->isConnected()) {
            return array();
        }
        $flags = '';
        if ($this->cfg['showHidden']) {
            $flags .= '-A';
        }
        $flags .= ' ';

        $list = ftp_rawlist($this->ftp, $flags . $this->prepPath($dirname));

        if (!$list) return array();

        // return cached result if exists
        if (isset($this->listCache[$dirname])) {
            return $this->listCache[$dirname];
        }

        $structure = array();
        foreach($list as $current) {
            $name = substr($current, 55, strlen($current) - 55);
            $structure[$name]['perms'] = substr($current, 0, 10);
            $structure[$name]['permsn'] = $this->chmodnum(substr($current, 0, 10));
            $structure[$name]['type'] = $this->getPermsType(substr($current, 0, 10));
            $structure[$name]['number'] = trim(substr($current, 11, 3));
            $structure[$name]['owner'] = trim(substr($current, 15, 8));
            $structure[$name]['group'] = trim(substr($current, 24, 8));
            $structure[$name]['size'] = trim(substr($current, 33, 8));
            $structure[$name]['month'] = trim(substr($current, 42, 3));
            $structure[$name]['day'] = trim(substr($current, 46, 2));
            $structure[$name]['time'] = substr($current, 49, 5);
            $structure[$name]['name'] = $name;
            $structure[$name]['path'] = str_replace('//', '/', $dirname . '/' . $name);
            $structure[$name]['raw'] = $current;
        }

        // Cache list data
        $this->listCache[$dirname] = $structure;
        return $structure;
    }


    /**
     * listdir
     *
     * @param string $directory
     * @param int $sorting_order
     * @return array
     */
    public function scandir($dirname, $sortingOrder = 0)
    {
        $rawList = $this->ftpListdir($dirname, $sortingOrder);
        $list = array_merge(array('.', '..'), array_keys($rawList));
        return $list;
    }


    /**
     * getPermsType
     *
     * @param string $perms
     * @return string
     */
    private function getPermsType($perms)
    {
        if (substr($perms, 0, 1) == 'd') {
            return 'dir';
        } elseif (substr($perms, 0, 1) == 'l') {
            return 'link';
        } else {
            return 'file';
        }
    }

    /**
     * chmodnum
     *
     * @param string $mode
     * @return string
     */
    private function chmodnum($mode)
    {
        $realmode = "";
        $legal = array("", "w", "r", "x", "-");
        $attarray = preg_split("//", $mode);
        for ($i = 0; $i < count($attarray); $i++) {
            if ($key = array_search($attarray[$i], $legal)) {
                $realmode .= $legal[$key];
            }
        }
        $mode = str_pad($realmode, 9, '-');
        $trans = array('-' => '0', 'r' => '4', 'w' => '2', 'x' => '1');
        $mode = strtr($mode, $trans);
        $newmode = '';
        $newmode .= $mode[0] + $mode[1] + $mode[2];
        $newmode .= $mode[3] + $mode[4] + $mode[5];
        $newmode .= $mode[6] + $mode[7] + $mode[8];
        return $newmode;
    }




    /**
     * Test if a file/path exists
     *
     * @param type $filename
     * @return bool
     */
    public function fileExists($filename)
    {
        if (!$this->isConnected()) {
            return false;
        }
        $list = $this->ftpListdir(dirname($filename));
        $file = basename($filename);
        if (isset($list[$file])) {
            return true;
        }
        return false;
    }

    public function fileAccessed($filename)
    {
        $this->log('File Accessed date unavailable in FTP adapter', \Tk\Log\Log::NOTICE);
        return 0;
    }

    public function fileCreated($filename)
    {
        $this->log('File created date unavailable in FTP adapter', \Tk\Log\Log::NOTICE);
        return 0;
    }

    public function fileModified($filename)
    {
        if (!$this->isConnected()) {
            return false;
        }
        $ts = ftp_mdtm($this->ftp, $this->prepPath($filename));

        return $ts;
    }

    public function fileSize($filename)
    {
        if (!$this->isConnected()) {
            return false;
        }
        $list = $this->ftpListdir(dirname($filename));
        $file = basename($filename);
        if (isset($list[$file])) {
            return $list[$file]['size'];
        }
        return false;
    }

    public function fileType($filename)
    {
        if (!$this->isConnected()) {
            return false;
        }
        $list = $this->ftpListdir(dirname($filename));
        $file = basename($filename);
        if (isset($list[$file])) {
            return $list[$file]['type'];
        }
        return false;
    }

    public function fileGroup($filename)
    {
        if (!$this->isConnected()) {
            return false;
        }
        $list = $this->ftpListdir(dirname($filename));
        $file = basename($filename);
        if (isset($list[$file])) {
            return $list[$file]['group'];
        }
        return false;
    }

    public function fileOwner($filename)
    {
        if (!$this->isConnected()) {
            return false;
        }
        $list = $this->ftpListdir(dirname($filename));
        $file = basename($filename);
        if (isset($list[$file])) {
            return $list[$file]['owner'];
        }
        return false;
    }

    public function filePerms($filename)
    {
        if (!$this->isConnected()) {
            return false;
        }
        $list = $this->ftpListdir(dirname($filename));
        $file = basename($filename);
        if (isset($list[$file])) {
            return $list[$file]['perms'];
        }
        return false;
    }

    public function isWritable($filename)
    {
        if (!$this->isConnected()) {
            return false;
        }
        $list = $this->ftpListdir(dirname($filename));
        $file = basename($filename);
        if (isset($list[$file]) && $list[$file]['perms'][2] == 'w') {
            return true;
        }
        return false;
    }

    public function isReadable($filename)
    {
        if (!$this->isConnected()) {
            return false;
        }
        $list = $this->ftpListdir(dirname($filename));
        $file = basename($filename);
        if (isset($list[$file]) && $list[$file]['perms'][1] == 'r') {
            return true;
        }
        return false;
    }

    public function isExecutable($filename)
    {
        if (!$this->isConnected()) {
            return false;
        }
        $list = $this->ftpListdir(dirname($filename));
        $file = basename($filename);
        if (isset($list[$file]) && $list[$file]['perms'][3] == 'x') {
            return true;
        }
        return false;
    }

    public function isFile($filename)
    {
        if (!$this->isConnected()) {
            return false;
        }
        $list = $this->ftpListdir(dirname($filename));
        $file = basename($filename);
        if (isset($list[$file]) && $list[$file]['type'] == 'file') {
            return true;
        }
        return false;
    }

    public function isDir($dirname)
    {
        if (!$this->isConnected()) {
            return false;
        }
        // This method sends a error if no dir found, pain in the ass API
//        try {
//          if (!@ftp_chdir($this->ftp, $this->pre($dirname))) {
//              throw new \Tk\Filesystem\Exception('Dir Not Exist.');
//          }
//          return true;
//        } catch (\Exception $e) {}
//        return false;

        // This method slower, able to use list cache and throws no error.
        $list = $this->ftpListdir(dirname($dirname));
        $file = basename($dirname);
        if (isset($list[$file]) && $list[$file]['type'] == 'dir') {
            return true;
        }
        return false;

    }

    public function isLink($filename)
    {
        if (!$this->isConnected()) {
            return false;
        }
        $list = $this->ftpListdir(dirname($filename));
        $file = basename($filename);
        if (isset($list[$file]) && $list[$file]['type'] == 'link') {
            return true;
        }
        return false;
    }


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