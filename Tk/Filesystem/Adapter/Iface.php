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
interface Iface
{
   
    
    /**
     * Close the remote connection.
     */
    public function close();

    /**
     * Delete a file from the remote filesystem
     *
     * @param string $remoteFile
     * return boolean
     */
    public function unlink($remoteFile);

    /**
     * delete a remote directory
     * TODO: look at deep removal if contains files
     *
     * @param string $remoteDir
     * @return bool
     */
    public function rmdir($remoteDir);

    /**
     * Create a directory on a remote filesystem.
     *
     * @param string $remoteDir
     * @return bool
     */
    public function mkdir($remoteDir);

    /**
     *
     * @param type $remoteFile
     * @param string $mode Eg mode: 0755
     * @return bool
     */
    public function chmod($remoteFile, $mode);

    /**
     * rename a file/dir on the remote filesystem
     *
     * @param string $remoteSrc  The full path to the source file
     * @param string $remoteDest    The site relative path to the destination
     * @return bool
     */
    public function rename($remoteSrc, $remoteDest);

    /**
     * Copy a file or directory on the remote filesystem
     *
     * @param string $remoteSrc  The full path to the source file
     * @param string $remoteDest    The site relative path to the destination
     * @return bool
     */
    public function copy($remoteSrc, $remoteDest);

    /**
     * get a list of the remote filesystem
     *
     * @link http://php.net/manual/en/function.scandir.php
     * @param string $remoteSrc
     * @param int $sortingOrder
     * @return array
     */
    public function scandir($remoteSrc, $sortingOrder = 0);




    /**
     * Upload a file or directory to the remote filesystem
     *
     * @param string $localSrc
     * @param string $remoteDest
     * @return bool
     */
    public function put($localSrc, $remoteDest);

    /**
     * Download a file or directory from the remote filesystem
     *
     * @param string $remoteSrc
     * @param string $localDest
     * @return bool
     */
    public function get($remoteSrc, $localDest);





    public function isFile($remotePath);
    public function isDir($remotePath);
    public function isLink($remotePath);

    public function fileGroup($remoteFile);
    public function fileOwner($remoteFile);
    public function filePerms($remoteFile);

    public function isWritable($remotePath);
    public function isReadable($remotePath);
    public function isExecutable($remotePath);

    public function fileExists($remoteFile);
    public function fileAccessed($remoteFile);
    public function fileCreated($remoteFile);
    public function fileModified($remoteFile);
    public function fileSize($remoteFile);
    public function fileType($remoteFile);



}