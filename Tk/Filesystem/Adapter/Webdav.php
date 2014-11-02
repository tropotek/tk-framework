<?php

/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 * Original class details
 * @author Christian Juerges <christian.juerges@xwave.ch>, Xwave GmbH, Josefstr. 92, 8005 Zuerich - Switzerland.
 * @version 0.1.3
 */

namespace Tk\Filesystem\Adapter;


/**
 * class webdav client. a php based nearly rfc 2518 conforming client.
 *
 * <code>
 * <?php
 * if (!class_exists('webdav_client')) {
 *  require('./class_webdav_client.php');
 * }
 *
 * $wdc = new WebdavClient();
 * $wdc->setServer('demo.webdav.ch');
 * $wdc->setPort(80);
 * $wdc->setUser('demo');
 * $wdc->setPass('demodemo');
 *
 * // use HTTP/1.1
 * $wdc->setProtocol(1);
 *
 *
 * if (!$wdc->open()) {
 *   print 'Error: could not open server connection';
 *   exit;
 * }
 *
 * // check if server supports webdav rfc 2518
 * if (!$wdc->checkWebdav()) {
 *   print 'Error: server does not support webdav or user/password may be wrong';
 *   exit;
 * }
 *
 * //  OR
 *
 * //The checks are also made, exceptions thrown in create if there is a problem.
 * $wdc = WebdavClient::create('http://demo:demodemo@demo.webdav.ch:80/base/dir/');
 *
 * $dir = $wdc->ls('/');
 * ?>
 * <h1>class_webdav_client Test-Suite:</h1><p>
 * Using method webdav_client::ls to get a listing of dir /:<br>
 * <table summary="ls" border="1">
 * <th>Filename</th><th>Size</th><th>Creationdate</th><th>Resource Type</th><th>Content Type</th><th>Activelock Depth</th><th>Activelock Owner</th><th>Activelock Token</th><th>Activelock Type</th>
 * <?php
 * foreach($dir as $e) {
 *   $ts = $wdc->iso8601totime($e['creationdate']);
 *   $line = sprintf('<tr><td>%s&nbsp;</td><td>%s&nbsp;</td><td>%s&nbsp;</td><td>%s&nbsp;</td><td>%s&nbsp;</td><td>%s&nbsp;</td><td>%s&nbsp;</td><td>%s&nbsp;</td><td>%s&nbsp;</td></tr>',
 *           $e['href'],
 *           $e['getcontentlength'],
 *           date('d.m.Y H:i:s',$ts),
 *           $e['resourcetype'],
 *           $e['getcontenttype'],
 *           $e['activelock_depth'],
 *           $e['activelock_owner'],
 *           $e['activelock_token'],
 *           $e['activelock_type']
 *           );
 *   print urldecode($line);
 * }
 * ?>
 * </table>
 * <p>
 * Create a new collection (Directory) using method webdav_client::mkcol...
 * <?php
 * $test_folder = '/wdc test 1 folder';
 * print '<br>creating collection ' . $test_folder .' ...<br>';
 * $http_status  = $wdc->mkcol($test_folder);
 * print 'webdav server returns ' . $http_status . '<br>';
 *
 * print 'removing collection just created using method webdav_client::delete ...<br>';
 * $http_status_array = $wdc->delete($test_folder);
 * print 'webdav server returns ' . $http_status_array['status'] . '<br>';
 *
 * print 'let`s see what`s happening when we try to delete the same nonexistent collection again....<br>';
 * $http_status_array = $wdc->delete($test_folder);
 * print 'webdav server returns ' . $http_status_array['status'] . '<br>';
 *
 * print 'let`s see what`s happening when we try to delete an existent locked collection....<br>';
 * $http_status_array = $wdc->delete('/packages.txt');
 * print 'webdav server returns ' . $http_status_array['status'] . '<br>';
 *
 *
 * $test_folder = '/wdc test 2 folder';
 * print 'let`s create a second collection ...' . $test_folder . '<br>';
 * $http_status  = $wdc->mkcol($test_folder);
 * print 'webdav server returns ' . $http_status . '<br>';
 *
 * // put a file to webdav collection
 * $filename = './Testfiles/test_ref.rar';
 * print 'Let`s put the file ' . $filename . ' using webdav::put into collection...<br>';
 * $handle = fopen ($filename, 'r');
 * $contents = fread ($handle, filesize ($filename));
 * fclose ($handle);
 * $target_path = $test_folder . '/test 123 456.rar';
 * $http_status = $wdc->putData($target_path, $daata);
 * print 'webdav server returns ' . $http_status .'<br>';
 * // ---
 * $filename = './Testfiles/Chiquita.jpg';
 * print 'Let`s Test second put method...<br>';
 * $target_path = $test_folder . '/picture.jpg';
 * $http_status = $wdc->put($srcPath, destPath);
 * print 'webdav server returns ' . $http_status . '<br>';
 *
 * // ---
 * print 'Let`s get file just putted...';
 * $http_status = $wdc->get($test_folder . '/picture.jpg', $buffer);
 * print 'webdav server returns ' . $http_status . '. Buffer is filled with ' . strlen($buffer). ' Bytes.<br>';
 *
 * // ---
 * print 'Let`s use method webdav_client::copy to create a copy of file ' . $target_path . ' the webdav server<br>';
 * $new_copy_target = '/copy of picture.jpg';
 * $http_status = $wdc->copy_file($target_path, $new_copy_target, true);
 * print 'webdav server returns ' . $http_status . '<br>';
 *
 * // --
 * print 'Let`s use method webdav_client::copy to create a copy of collection ' . $test_folder . ' the webdav server<br>';
 * $new_copy_target = '/copy of wdc test 2 folder';
 * $http_status = $wdc->copy_coll($test_folder, $new_copy_target, true);
 * print 'webdav server returns ' . $http_status . '<br>';
 *
 *
 * // ---
 * print 'Let`s move/rename a file in a collection<br>';
 * $new_target_path = $test_folder . '/picture renamed.jpg';
 * $http_status = $wdc->move($target_path, $new_target_path, true);
 * print 'webdav server returns ' . $http_status . '<br>';
 *
 * // ---
 * print 'Let`s move/rename a collection<br>';
 * $new_target_folder = '/wdc test 2 folder renamed';
 * $http_status = $wdc->move($test_folder, $new_target_folder, true);
 * print 'webdav server returns ' .  $http_status . '<br>';
 *
 * // ---
 * print 'Let`s lock this moved collection<br>';
 * $http_status_array = $wdc->lock($new_target_folder);
 * print 'webdav server returns ' . $http_status_array['status'] . '<br>';
 *
 * print 'locktocken is ' . $http_status_array[0]['locktoken']. '<br>';
 * print 'Owner of lock is ' . $http_status_array[0]['owner'] . '<br>';
 * // ---
 * print 'Let`s unlock this collection with a wrong locktoken<br>';
 * $http_status = $wdc->unlock($new_target_folder, 'wrongtoken');
 * print "webdav server returns $http_status<br>";
 *
 * print 'Let`s unlock this collection with the right locktoken<br>';
 * $http_status = $wdc->unlock($new_target_folder, $http_status_array[0]['locktoken']);
 * print 'webdav server returns ' . $http_status .'<br>';
 *
 * // --
 * print 'Let`s remove/delete the moved collection ' . $new_target_folder . '<br>';
 * $http_status_array = $wdc->delete($new_target_folder);
 * print 'webdav server returns ' . $http_status_array['status'] . '<br>';
 *
 * $wdc->close();
 * flush();
 * ?>
 * </code>
 *
 * This class implements methods to get access to an webdav server.
 * Most of the methods return false on error, an passtrough integer (http response status) on success
 * or an array in case of a multistatus response (207) from the webdav server.
 * It's your responsibility to handle the webdav server responses in an proper manner.
 *
 * @package \Tk\Filesystem\Adapter
 */
class Webdav extends \Tk\Object implements Iface
{

    //private $respStatus;
    //private $tree = null;
    //private $lockRecCdata = null;
    //private $crlf = "\r\n";

    private $fp = null;
    private $server = null;
    private $port = 80;
    private $path = '/';
    private $user = '';
    private $protocol = 'HTTP/1.1';
    private $pass = '';
    private $socketTimeout = 5;
    private $errno = 0;
    private $errstr = '';
    private $userAgent = '';
    private $req = '';
    private $parser = null;
    private $xmltree = null;
    private $ls = array();
    private $lsRef = null;
    private $lsRefCdata = null;
    private $delete = array();
    private $deleteRef = null;
    private $deleteRefCdata = null;
    private $lock = array();
    private $lockRef = null;
    private $null = NULL;
    private $header = '';
    private $body = '';
    private $connectionClosed = false;
    private $maxheaderlength = 10000;
    private $debug = false;
    private $baseUrl = '';

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->userAgent = 'php class ' . $this->getClassName();
    }

    /**
     * Create a Webdav adapter object
     *
     * @param \Tk\Url $url
     * @return Webdav
     */
    static function create($url = null)
    {
        $w = new self();
        if ($url) {
            $url = \Tk\Url::create($url);
            $w->setServer($url->getHost());
            $w->setPort($url->getPort());
            if (strlen($url->getPath()) > 1) {
                $w->baseUrl = rtrim($url->getPath(), '/');
            }
            if ($url->getUser()) {
                $w->setUser($url->getUser());
                $w->setPass($url->getPassword());
            }

            if (!$w->open()) {
                throw new \Tk\Filesystem\Exception('Error: could not open server connection: ' . $url->toString());
            }
            // check if server supports webdav rfc 2518
            if (!$w->checkWebdav()) {
                throw new \Tk\Filesystem\Exception('Error: server does not support webdav or user/password may be wrong');
            }
        }
        return $w;
    }


    /**
     * Set webdav server. FQN or IP address.
     * @param string server
     */
    public function setServer($server)
    {
        $this->server = $server;
        return $this;
    }

    /**
     * Set tcp port of webdav server. Default is 80.
     * @param int port
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * set user name for authentification
     * @param string user
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Set password for authentification
     * @param string pass
     */
    public function setPass($pass)
    {
        $this->pass = $pass;
        return $this;
    }

    /**
     * Set debug log to On/Off
     *
     * @param bool $b
     * @return Webdav
     */
    public function enableDebug($b = true)
    {
        $this->debug = $b;
        return $this;
    }

    /**
     * Set which HTTP protocol will be used.
     * Value 1 defines that HTTP/1.1 should be used (Keeps Connection to webdav server alive).
     * Otherwise HTTP/1.0 will be used.
     * @param int version
     */
    public function setProtocol($version = 1)
    {
        if ($version == 1) {
            $this->protocol = 'HTTP/1.1';
        } else {
            $this->protocol = 'HTTP/1.0';
        }
        $this->log('HTTP Protocol was set to ' . $this->protocol);
    }

    /**
     * Convert ISO 8601 Date and Time Profile used in RFC 2518 to an unix timestamp.
     *
     * @param string iso8601
     * @return unixtimestamp on sucess. Otherwise false.
     */
    public function iso8601totime($iso8601)
    {
        $regs = array();
        /*         [1]        [2]        [3]        [4]        [5]        [6]  */
        if (preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})T([0-9]{2}):([0-9]{2}):([0-9]{2})Z$/', $iso8601, $regs)) {
            return mktime($regs[4], $regs[5], $regs[6], $regs[2], $regs[3], $regs[1]);
        }
        // to be done: regex for partial-time...apache webdav mod never returns partial-time
        return false;
    }

    /**
     * Open's a socket to a webdav server
     * @return bool true on success. Otherwise false.
     */
    public function open()
    {
        // let's try to open a socket
        $this->log('open a socket connection');
        $this->fp = fsockopen($this->server, $this->port, $this->errno, $this->errstr, $this->socketTimeout);
        // set_time_limit(30);
        socket_set_blocking($this->fp, true);
        if (!$this->fp) {
            $this->log("$this->errstr ($this->errno)\n");
            return false;
        } else {
            $this->connectionClosed = false;
            $this->log('socket is open: ' . $this->fp);
            return true;
        }
    }

    /**
     * Closes an open socket.
     */
    public function close()
    {
    }

    /**
     * Closes an open socket.
     */
    public function dissconnect()
    {
        $this->log('closing socket ' . $this->fp);
        $this->connectionClosed = true;
        fclose($this->fp);
    }

    /**
     * Check's if server is a webdav compliant server.
     * True if server returns a DAV Element in Header and when
     * schema 1,2 is supported.
     * @return bool true if server is webdav server. Otherwise false.
     */
    public function checkWebdav()
    {
        $resp = $this->options();
        if (!$resp || !isset($resp['header']['DAV'])) {
            return false;
        }
        $this->log($resp['header']['DAV']);
        // check schema
        if (preg_match('/1,2/', $resp['header']['DAV'])) {
            return true;
        }
        // otherwise return false
        return false;
    }

    /**
     * Get options from webdav server.
     * @return array with all header fields returned from webdav server. false if server does not speak http.
     */
    public function options()
    {
        $this->headerUnset();
        $this->createBasicWebRequest('OPTIONS');
        $this->sendRequest();
        $this->getWebResponse();
        $response = $this->processWebResponse();
        // validate the response ...
        // check http-version
        if ($response['status']['http-version'] == 'HTTP/1.1' ||
        $response['status']['http-version'] == 'HTTP/1.0') {
            return $response;
        }
        $this->log('Response was not even http');
        return false;
    }




    /**
     * delete a remote directory
     *
     * @param string $remoteDir
     * @return bool
     */
    public function rmdir($remoteDir)
    {
        return $this->delete($remoteDir);
    }

    /**
     * Delete a file from the remote filesystem
     *
     * @param string $remoteFile
     * @return bool
     */
    public function unlink($remoteFile)
    {
        return $this->delete($remoteFile);
    }

    /**
     * Deletes a collection/directory on a webdav server
     *
     * @param string $remoteFile
     * @return int status code (look at rfc 2518). false on error.
     */
    public function delete($remoteFile)
    {
        $this->path = $this->translateUri($remoteFile);
        $this->headerUnset();
        $this->createBasicWebRequest('DELETE');
        /* $this->headerAdd('Content-Length: 0'); */
        //$this->headerAdd('');
        $this->sendRequest();
        $this->getWebResponse();
        $response = $this->processWebResponse();

        // validate the response ...
        // check http-version
        if ($response['status']['http-version'] == 'HTTP/1.1' ||
        $response['status']['http-version'] == 'HTTP/1.0') {
            // seems to be http ... proceed
            // We expect a 207 Multi-Status status code
            // print 'http ok<br>';
            switch ($response['status']['status-code']) {
                case 207:
                    // collection was NOT deleted... see xml response for reason...
                    // next there should be a Content-Type: text/xml; charset="utf-8" header line
                    if (preg_match('/text\/xml; ?charset="?utf\-8"?/i', trim($response['header']['Content-Type']))) {
                        //if (strcmp($response['header']['Content-Type'], 'text/xml; charset="utf-8"') == 0) {
                        // ok let's get the content of the xml stuff
                        $this->parser = xml_parser_create_ns();
                        $idx = (int) $this->parser;
                        // forget old data...
                        unset($this->delete[$idx]);
                        unset($this->xmltree[$idx]);
                        xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, 0);
                        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
                        xml_set_object($this->parser, $this);
                        xml_set_element_handler($this->parser, "deleteStartElement", "endElement");
                        xml_set_character_data_handler($this->parser, "deleteCdata");

                        if (!xml_parse($this->parser, $response['body'])) {
                            //throw new \Tk\Filesystem\Exception(sprintf("XML error: %s at line %d", xml_error_string(xml_get_error_code($this->parser)), xml_get_current_line_number($this->parser)));
                            $this->log(sprintf('XML error: %s at line %d', xml_error_string(xml_get_error_code($this->parser)), xml_get_current_line_number($this->parser)));
                        }
                        $this->log(print_r($this->delete[$idx], true));
                        //print_r($this->delete[$idx]);
                        //print "<br/>";

                        // Free resources
                        xml_parser_free($this->parser);
                        $this->delete[$idx]['status'] = $response['status']['status-code'];
                        return $this->delete[$idx];
                    } else {
                        throw new \Tk\Filesystem\Exception('Missing Content-Type: text/xml header in response.');
                    }
                    return false;
                default:
                    // collection or file was successfully deleted
                    $this->delete['status'] = $response['status']['status-code'];
                    return $this->delete;
            }
        }
    }


    /**
     * Public method mkcol
     *
     * Creates a new collection/directory on a webdav server
     * @param string path
     * @return int status code received as reponse from webdav server (see rfc 2518) or false
     */
    public function mkdir($remoteDir)
    {
        $this->path = $this->translateUri($remoteDir);
        $this->headerUnset();
        $this->createBasicWebRequest('MKCOL');
        $this->sendRequest();
        $this->getWebResponse();
        $response = $this->processWebResponse();
        // validate the response ...
        // check http-version
        if ($response['status']['http-version'] == 'HTTP/1.1' ||
            $response['status']['http-version'] == 'HTTP/1.0') {
            /* seems to be http ... proceed
              just return what server gave us
              rfc 2518 says:
              201 (Created) - The collection or structured resource was created in its entirety.
              403 (Forbidden) - This indicates at least one of two conditions: 1) the server does not allow the creation of collections at the given
              location in its namespace, or 2) the parent collection of the Request-URI exists but cannot accept members.
              405 (Method Not Allowed) - MKCOL can only be executed on a deleted/non-existent resource.
              409 (Conflict) - A collection cannot be made at the Request-URI until one or more intermediate collections have been created.
              415 (Unsupported Media Type)- The server does not support the request type of the body.
              507 (Insufficient Storage) - The resource does not have sufficient space to record the state of the resource after the execution of this method.
             */
            return $response['status']['status-code'];
        }
    }

    /**
     *
     * @param type $remoteFile
     * @param string $mode Eg mode: 0755
     * @return bool
     */
    public function chmod($remoteFile, $mode)
    {
        $this->log('command not available!');
        return false;
    }

    /**
     * Public method put
     * Puts raw data into a collection.Data is putted as one chunk!
     *
     * @param string path
     * @param string data
     * @return int status-code read from webdavserver. False on error.
     */
    public function putData($remoteFile, $data)
    {
        $this->path = $this->translateUri($remoteFile);
        $this->headerUnset();
        $this->createBasicWebRequest('PUT');
        // add more needed header information ...
        $this->headerAdd('Content-length: ' . strlen($data));
        $this->headerAdd('Content-type: application/octet-stream');
        $this->headerAdd('Overwrite: T');
        // send header
        $this->sendRequest();
        // send the rest (data)
        fputs($this->fp, $data);
        $this->getWebResponse();
        $response = $this->processWebResponse();

        // validate the response
        // check http-version
        if ($response['status']['http-version'] == 'HTTP/1.1' ||
        $response['status']['http-version'] == 'HTTP/1.0') {
            // seems to be http ... proceed
            // We expect a 200 or 204 status code
            // see rfc 2068 - 9.6 PUT...
            // print 'http ok<br>';
            return $response['status']['status-code'];
        }
        // ups: no http status was returned ?
        return false;
    }

    /**
     * Public method putFile
     *
     * Read a file as stream and puts it chunk by chunk into webdav server collection.
     * Look at php documenation for legal filenames with fopen();
     *
     * @param string $localSrc
     * @param string $remoteDest
     * @return int status code. False on error.
     */
    public function put($localSrc, $remoteDest)
    {
        // try to open the file ...
        $handle = @fopen($localSrc, 'r');
        if ($handle) {
            // $this->fp = pfsockopen ($this->server, $this->port, $this->errno, $this->errstr, $this->socketTimeout);
            $this->path = $this->translateUri($remoteDest);
            $this->headerUnset();
            $this->createBasicWebRequest('PUT');
            // add more needed header information ...
            $this->headerAdd('Content-length: ' . filesize($localSrc));
            $mime = mime_content_type($localSrc);
            if (!$mime) {
                $mime = 'application/octet-stream';
            }
            $this->headerAdd('Content-type: ' . $mime);
            $this->headerAdd('Overwrite: T');
            // send header
            $this->sendRequest();
            while (!feof($handle)) {
                fputs($this->fp, fgets($handle, 4096));
            }
            fclose($handle);
            $this->getWebResponse();
            $response = $this->processWebResponse();

            // validate the response
            // check http-version
            if ($response['status']['http-version'] == 'HTTP/1.1' || $response['status']['http-version'] == 'HTTP/1.0') {
                // seems to be http ... proceed
                // We expect a 200 or 204 status code
                // see rfc 2068 - 9.6 PUT...
                // print 'http ok<br>';
                return $response['status']['status-code'];
            }
            // ups: no http status was returned ?
            return false;
        } else {
            $this->log('could not open ' . $localSrc);
            return false;
        }
    }

    /**
     * Puts multiple files and directories onto a webdav server
     * Param fileList must be in format array("localpath" => "relativeDestpath")
     *
     * @param array filelist
     * @return bool true on success. otherwise int status code on error
     */
    public function mput($filelist)
    {
        $result = true;
        while (list($localPath, $remotePath) = each($filelist)) {

            $localPath = rtrim($localPath, "/");
            //$remotePath = rtrim($remotePath, "/");
            $remotePath = $this->translateUri($remotePath);

            // attempt to create target path
            if (isDir($localPath)) {
                $pathparts = explode('/', $remotePath . '/ '); // add one level, last level will be created as dir
            } else {
                $pathparts = explode('/', $remotePath);
            }
            $checkpath = "";
            for ($i = 1; $i < sizeof($pathparts) - 1; $i++) {
                $checkpath .= '/' . $pathparts[$i];
                if (!($this->isDir($checkpath))) {
                    $result &= ($this->mkdir($checkpath) == 201 );
                }
            }

            if ($result) {
                // recurse directories
                if (isDir($localPath)) {
                    $dp = opendir($localPath);
                    $fl = array();
                    while ($filename = readdir($dp)) {
                        if ((isFile($localPath . '/' . $filename) || isDir($localPath . '/' . $filename)) && $filename != '.' && $filename != '..') {
                            $fl[$localPath . '/' . $filename] = $remotePath . '/' . $filename;
                        }
                    }
                    $result &= $this->mput($fl);
                } else {
                    $result &= ($this->put($localPath, $remotePath) == 201);
                }
            }
        }
        return $result;
    }



    /**
     * Public method get
     *
     * Gets a file from a webdav collection.
     * @param string path, string &buffer
     * @return status code and &$buffer (by reference) with response data from server on success. False on error.
     */
    public function getData($remoteSrc, &$buffer)
    {
        $this->path = $this->translateUri($remoteSrc);
        $this->headerUnset();
        $this->createBasicWebRequest('GET');
        $this->sendRequest();
        $this->getWebResponse();
        $response = $this->processWebResponse();
        // validate the response
        // check http-version
        if ($response['status']['http-version'] == 'HTTP/1.1' ||
        $response['status']['http-version'] == 'HTTP/1.0') {
            // seems to be http ... proceed
            // We expect a 200 code
            if ($response['status']['status-code'] == 200) {
                $this->log('returning buffer with ' . strlen($response['body']) . ' bytes.');
                $buffer = $response['body'];
                return $buffer;
            }
            return $response['status']['status-code'];
        }
        // ups: no http status was returned ?
        return false;
    }

    /**
     * Gets a file from a collection into local filesystem.
     * fopen() is used.
     *
     * @param string $remoteSrc
     * @param string $localDest
     * @return true on success. false on error.
     */
    public function get($remoteSrc, $localDest)
    {
        if ($this->get($remoteSrc, $buffer)) {
            $handle = fopen($localDest, 'w');
            if ($handle) {
                fwrite($handle, $buffer);
                fclose($handle);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Public method mget
     *
     * Gets multiple files and directories
     * FileList must be in format array("remotepath" => "localpath")
     * @param array filelist
     * @return bool true on succes, other int status code on error
     */
    public function mget($filelist)
    {
        $result = true;
        while (list($remotepath, $localpath) = each($filelist)) {

            $localpath = rtrim($localpath, '/');
            //$remotepath = rtrim($remotepath, "/");
            $remotepath = $this->translateUri($remotepath);
            // attempt to create local path
            if ($this->isDir($remotepath)) {
                $pathparts = explode('/', $localpath . '/ '); // add one level, last level will be created as dir
            } else {
                $pathparts = explode('/', $localpath);
            }
            $checkpath = '';
            for ($i = 1; $i < sizeof($pathparts) - 1; $i++) {
                $checkpath .= '/' . $pathparts[$i];
                if (!isDir($checkpath)) {
                    $result &= mkdir($checkpath);
                }
            }

            if ($result) {
                // recurse directories
                if ($this->isDir($remotepath)) {
                    $list = $this->ls($remotepath);
                    $fl = array();
                    foreach ($list as $e) {
                        $fullpath = urldecode($e['href']);
                        $filename = basename($fullpath);
                        if ($filename != '' && $fullpath != $remotepath . '/') {
                            $fl[$remotepath . '/' . $filename] = $localpath . '/' . $filename;
                        }
                    }
                    $result &= $this->mget($fl);
                } else {
                    $result &= ($this->getFile($remotepath, $localpath));
                }
            }
        }
        return $result;
    }

    /**
     * rename a file/dir on the remote filesystem
     *
     * @param string $remoteSrc  The full path to the source file
     * @param string $remoteDest The site relative path to the destination
     * @return int status code (look at rfc 2518). false on error.
     */
    public function rename($remoteSrc, $remoteDest)
    {
        return move($remoteSrc, $remoteDest, true);
    }

    /**
     * Public method move
     *
     * Move a file or collection on webdav server (serverside)
     * If you set param overwrite as true, the target will be overwritten.
     *
     * @param string $remoteSrc  The full path to the source file
     * @param string $remoteDest The site relative path to the destination
     * @param bool $overwrite
     * @return int status code (look at rfc 2518). false on error.
     */
    public function move($remoteSrc, $remoteDest, $overwrite = true)
    {
        $this->path = $this->translateUri($remoteSrc);
        $this->headerUnset();

        $this->createBasicWebRequest('MOVE');
        // dst_path should not be uri translated....
        $this->headerAdd(sprintf('Destination: http://%s%s', $this->server, $remoteDest));
        if ($overwrite) {
            $this->headerAdd('Overwrite: T');
        } else {
            $this->headerAdd('Overwrite: F');
        }
        $this->headerAdd('');
        $this->sendRequest();
        $this->getWebResponse();
        $response = $this->processWebResponse();
        // validate the response ...
        // check http-version
        if ($response['status']['http-version'] == 'HTTP/1.1' ||
            $response['status']['http-version'] == 'HTTP/1.0') {
            /* seems to be http ... proceed
              just return what server gave us (as defined in rfc 2518) :
              201 (Created) - The source resource was successfully moved, and a new resource was created at the destination.
              204 (No Content) - The source resource was successfully moved to a pre-existing destination resource.
              403 (Forbidden) - The source and destination URIs are the same.
              409 (Conflict) - A resource cannot be created at the destination until one or more intermediate collections have been created.
              412 (Precondition Failed) - The server was unable to maintain the liveness of the properties listed in the propertybehavior XML element
              or the Overwrite header is "F" and the state of the destination resource is non-null.
              423 (Locked) - The source or the destination resource was locked.
              502 (Bad Gateway) - This may occur when the destination is on another server and the destination server refuses to accept the resource.

              201 (Created) - The collection or structured resource was created in its entirety.
              403 (Forbidden) - This indicates at least one of two conditions: 1) the server does not allow the creation of collections at the given
              location in its namespace, or 2) the parent collection of the Request-URI exists but cannot accept members.
              405 (Method Not Allowed) - MKCOL can only be executed on a deleted/non-existent resource.
              409 (Conflict) - A collection cannot be made at the Request-URI until one or more intermediate collections have been created.
              415 (Unsupported Media Type)- The server does not support the request type of the body.
              507 (Insufficient Storage) - The resource does not have sufficient space to record the state of the resource after the execution of this method.
             */
            return $response['status']['status-code'];
        }
        return false;
    }

    /**
     * Copy a file or directory on the remote filesystem
     *
     * @param string $remoteSrc  The full path to the source file
     * @param string $remoteDest The site relative path to the destination
     * @return int status code (look at rfc 2518). false on error.
     */
    public function copy($remoteSrc, $remoteDest)
    {
        if($this->isDir($remoteSrc)) {
            return $this->copyColl($remoteSrc, $remoteDest, true);
        }
        return $this->copyFile($remoteSrc, $remoteDest, true);
    }


    /**
     * Public method copyFile
     *
     * Copy a file on webdav server
     * Duplicates a file on the webdav server (serverside).
     * All work is done on the webdav server. If you set param overwrite as true,
     * the target will be overwritten.
     *
     * @param string $remoteSrc  The full path to the source file
     * @param string $remoteDest The site relative path to the destination
     * @return int status code (look at rfc 2518). false on error.
     */
    public function copyFile($remoteSrc, $remoteDest, $overwrite = true)
    {
        $this->path = $this->translateUri($remoteSrc);
        $this->headerUnset();
        $this->createBasicWebRequest('COPY');
        $this->headerAdd(sprintf('Destination: http://%s%s', $this->server, $this->translateUri($remoteDest)));
        if ($overwrite) {
            $this->headerAdd('Overwrite: T');
        } else {
            $this->headerAdd('Overwrite: F');
        }
        //$this->headerAdd('');
        $this->sendRequest();
        $this->getWebResponse();
        $response = $this->processWebResponse();
        // validate the response ...
        // check http-version
        if ($response['status']['http-version'] == 'HTTP/1.1' ||
            $response['status']['http-version'] == 'HTTP/1.0') {
            /* seems to be http ... proceed
              just return what server gave us (as defined in rfc 2518) :
              201 (Created) - The source resource was successfully copied. The copy operation resulted in the creation of a new resource.
              204 (No Content) - The source resource was successfully copied to a pre-existing destination resource.
              403 (Forbidden) - The source and destination URIs are the same.
              409 (Conflict) - A resource cannot be created at the destination until one or more intermediate collections have been created.
              412 (Precondition Failed) - The server was unable to maintain the liveness of the properties listed in the propertybehavior XML element
              or the Overwrite header is "F" and the state of the destination resource is non-null.
              423 (Locked) - The destination resource was locked.
              502 (Bad Gateway) - This may occur when the destination is on another server and the destination server refuses to accept the resource.
              507 (Insufficient Storage) - The destination resource does not have sufficient space to record the state of the resource after the
              execution of this method.
             */
            return $response['status']['status-code'];
        }
        return false;
    }

    /**
     * Public method copyColl
     *
     * Copy a collection on webdav server
     * Duplicates a collection on the webdav server (serverside).
     * All work is done on the webdav server. If you set param overwrite as true,
     * the target will be overwritten.
     *
     * @param string src_path, string dest_path, bool overwrite
     * @return int status code (look at rfc 2518). false on error.
     */
    public function copyColl($remoteSrc, $remoteDest, $overwrite = true)
    {
        $this->path = $this->translateUri($remoteSrc);
        $this->headerUnset();
        $this->createBasicWebRequest('COPY');
        $this->headerAdd(sprintf('Destination: http://%s%s', $this->server, $this->translateUri($remoteDest)));
        $this->headerAdd('Depth: Infinity');
        if ($overwrite) {
            $this->headerAdd('Overwrite: T');
        } else {
            $this->headerAdd('Overwrite: F');
        }
        $xml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
        $xml .= "<d:propertybehavior xmlns:d=\"DAV:\">\r\n";
        $xml .= "  <d:keepalive>*</d:keepalive>\r\n";
        $xml .= "</d:propertybehavior>\r\n";
        $this->headerAdd('Content-length: ' . strlen($xml));
        $this->headerAdd('Content-type: text/xml');
        $this->sendRequest();
        // send also xml
        fputs($this->fp, $xml);
        $this->getWebResponse();
        $response = $this->processWebResponse();
        // validate the response ...
        // check http-version
        if ($response['status']['http-version'] == 'HTTP/1.1' || $response['status']['http-version'] == 'HTTP/1.0') {
            /* seems to be http ... proceed
              just return what server gave us (as defined in rfc 2518) :
              201 (Created) - The source resource was successfully copied. The copy operation resulted in the creation of a new resource.
              204 (No Content) - The source resource was successfully copied to a pre-existing destination resource.
              403 (Forbidden) - The source and destination URIs are the same.
              409 (Conflict) - A resource cannot be created at the destination until one or more intermediate collections have been created.
              412 (Precondition Failed) - The server was unable to maintain the liveness of the properties listed in the propertybehavior XML element
              or the Overwrite header is "F" and the state of the destination resource is non-null.
              423 (Locked) - The destination resource was locked.
              502 (Bad Gateway) - This may occur when the destination is on another server and the destination server refuses to accept the resource.
              507 (Insufficient Storage) - The destination resource does not have sufficient space to record the state of the resource after the
              execution of this method.
             */
            return $response['status']['status-code'];
        }
        return false;
    }


    /**
     * get a list of the remote filesystem
     *
     * @param string $remoteSrc
     * @param int $sortingOrder@param int $sorting_order [optional] <p>
     *   By default, the sorted order is alphabetical in ascending order. If
     *   the optional <i>sorting_order</i> is set to
     *   <b>SCANDIR_SORT_DESCENDING</b>, then the sort order is
     *   alphabetical in descending order. If it is set to
     *   <b>SCANDIR_SORT_NONE</b> then the result is unsorted.
     * @return array
     */
    public function scandir($remoteSrc, $sortingOrder = 'SCANDIR_SORT_ASCENDING')
    {
        $list = $this->ls($remoteSrc);
        $arrDir = array('.', '..');
        $arrFile = array();

        foreach ($list as $i => $v) {
            $url = \Tk\Url::create($v['href'], $this->baseUrl);
            if (!$url->getPath(true)) continue;
            if (isset($v['resourcetype']) && $v['resourcetype'] == 'collection') {  // Is dir
                $arrDir[] = $url->getBasename();
            } else {
                $arrFile[] = $url->getBasename();
            }
        }
        switch ($sortingOrder) {
            case SCANDIR_SORT_NONE:
                break;
            case SCANDIR_SORT_DESCENDING:
                $arrDir = arsort($arrDir);
                $arrFile = arsort($arrFile);
                break;
            case SCANDIR_SORT_ASCENDING:
            default:
                $arrDir = asort($arrDir);
                $arrFile = asort($arrFile);
                break;

        }
        $arr = array_merge($arrDir, $arrFile);
        return $arr;
    }


    /**
     * Public method ls
     *
     * Get's directory information from webdav server into flat a array using PROPFIND
     * @param string path
     * @return array dirinfo, false on error
     */
    public function ls($remoteSrc)
    {
        if (trim($remoteSrc) == '') {
            $this->log('Missing a path in method ls');
            return false;
        }
        $this->path = $this->translateUri($remoteSrc);
        $this->headerUnset();
        $this->createBasicWebRequest('PROPFIND');
        $this->headerAdd('Depth: 1');
        $this->headerAdd('Content-type: text/xml');
        // create profind xml request...
        $xml = "<?xml version=\"1.0\"?>\r\n";
        $xml .= "<A:propfind xmlns:A=\"DAV:\">\r\n";
        // shall we get all properties ?
        $xml .= "    <A:allprop/>\r\n";
        // or should we better get only wanted props ?
        $xml .= "</A:propfind>\r\n";
        $this->headerAdd('Content-length: ' . strlen($xml));
        $this->sendRequest();
        $this->log($xml);
        fputs($this->fp, $xml);
        $this->getWebResponse();
        $response = $this->processWebResponse();
        // validate the response ... (only basic validation)
        // check http-version
        if ($response['status']['http-version'] == 'HTTP/1.1' || $response['status']['http-version'] == 'HTTP/1.0') {
            // seems to be http ... proceed
            // We expect a 207 Multi-Status status code
            // print 'http ok<br>';
            if (strcmp($response['status']['status-code'], '207') == 0) {
                // ok so far
                // next there should be a Content-Type: text/xml; charset="utf-8" header line
                if (preg_match('/text\/xml; ?charset="?utf\-8"?/i', trim($response['header']['Content-Type']))) {
                    //if (strcmp($response['header']['Content-Type'], 'text/xml; charset="utf-8"') == 0) {
                    // ok let's get the content of the xml stuff
                    $this->parser = xml_parser_create_ns();
                    $idx = (int) $this->parser;
                    // forget old data...
                    unset($this->ls[$idx]);
                    unset($this->xmltree[$idx]);
                    xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, 0);
                    xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
                    xml_set_object($this->parser, $this);
                    xml_set_element_handler($this->parser, 'propfindStartElement', 'endElement');
                    xml_set_character_data_handler($this->parser, 'propfindCdata');

                    if (!xml_parse($this->parser, $response['body'])) {
                        die(sprintf('XML error: %s at line %d', xml_error_string(xml_get_error_code($this->parser)), xml_get_current_line_number($this->parser)));
                    }

                    // Free resources
                    xml_parser_free($this->parser);
                    return $this->ls[$idx];
                } else {
                    $this->log('Missing Content-Type: text/xml header in response!!');
                    return false;
                }
            }
        }
        // response was not http
        $this->log('Ups in method ls: error in response from server');
        return false;
    }

    /**
     * Public method isFile
     *
     * Gather whether a path points to a file or not
     * @param string path
     * @return bool true or false
     */
    public function isFile($path)
    {
        $item = $this->gpi($path);
        if ($item === false) {
            return false;
        } else if (!isset($item['resourcetype'])) {
            return true;
        } else {
            return ($item['resourcetype'] != 'collection');
        }
    }

    /**
     * Public method isDir
     *
     * Gather whether a path points to a directory
     * @param string path
     * return bool true or false
     */
    public function isDir($path)
    {
        $item = $this->gpi($path);
        if ($item === false || !isset($item['resourcetype'])) {
            return false;
        } else {
            return ($item['resourcetype'] == 'collection');
        }
    }

    /**
     * Public method gpi
     *
     * Get's path information from webdav server for one element
     * @param string path
     * @return array dirinfo. false on error
     */
    public function gpi($path)
    {
        // split path by last "/"
        $path = rtrim($path, "/");
        $item = basename($path);
        $dir = dirname($path);
        $list = $this->ls($dir);
        // be sure it is an array
        if (is_array($list)) {
            foreach ($list as $e) {
                $fullpath = urldecode($e['href']);
                $filename = basename($fullpath);
                if ($filename == $item && $filename != '' and $fullpath != $dir . '/') {
                    return $e;
                }
            }
        }
        return false;
    }


    // TODO:
    public function isLink($remotePath) { return false; }

    public function fileGroup($remoteFile) {}
    public function fileOwner($remoteFile) {}
    public function filePerms($remoteFile) {}

    public function isWritable($remotePath) {}
    public function isReadable($remotePath) {}
    public function isExecutable($remotePath) {}

    public function fileExists($remoteFile) {}
    public function fileAccessed($remoteFile) {}
    public function fileCreated($remoteFile) {}
    public function fileModified($remoteFile) {}
    public function fileSize($remoteFile) {}
    public function fileType($remoteFile) {}




    /**
     * Public method lock
     *
     * Lock a file or collection.
     *
     * Lock uses this->user as lock owner.
     *
     * @param string path
     * @return int status code (look at rfc 2518). false on error.
     */
    public function lock($path)
    {
        $this->path = $this->translateUri($path);
        $this->headerUnset();
        $this->createBasicWebRequest('LOCK');
        $this->headerAdd('Timeout: Infinite');
        $this->headerAdd('Content-type: text/xml');
        // create the xml request ...
        $xml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
        $xml .= "<D:lockinfo xmlns:D='DAV:'\r\n>";
        $xml .= "  <D:lockscope><D:exclusive/></D:lockscope>\r\n";
        $xml .= "  <D:locktype><D:write/></D:locktype>\r\n";
        $xml .= "  <D:owner>\r\n";
        $xml .= '    <D:href>' . ($this->user) . "</D:href>\r\n";
        $xml .= "  </D:owner>\r\n";
        $xml .= "</D:lockinfo>\r\n";
        $this->headerAdd('Content-length: ' . strlen($xml));
        $this->sendRequest();
        // send also xml
        fputs($this->fp, $xml);
        $this->getWebResponse();
        $response = $this->processWebResponse();
        // validate the response ... (only basic validation)
        // check http-version
        if ($response['status']['http-version'] == 'HTTP/1.1' || $response['status']['http-version'] == 'HTTP/1.0') {
            /* seems to be http ... proceed
              rfc 2518 says:
              200 (OK) - The lock request succeeded and the value of the lockdiscovery property is included in the body.
              412 (Precondition Failed) - The included lock token was not enforceable on this resource or the server could not satisfy the
              request in the lockinfo XML element.
              423 (Locked) - The resource is locked, so the method has been rejected.
             */

            switch ($response['status']['status-code']) {
                case 200:
                    // collection was successfully locked... see xml response to get lock token...
                    if (preg_match('/text\/xml; ?charset="?utf\-8"?/i', trim($response['header']['Content-Type']))) {
                        //if (strcmp($response['header']['Content-Type'], 'text/xml; charset="utf-8"') == 0) {
                        // ok let's get the content of the xml stuff
                        $this->parser = xml_parser_create_ns();
                        $idx = (int) $this->parser;
                        // forget old data...
                        unset($this->lock[$idx]);
                        unset($this->xmltree[$idx]);
                        xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, 0);
                        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
                        xml_set_object($this->parser, $this);
                        xml_set_element_handler($this->parser, 'lockStartElement', 'endElement');
                        xml_set_character_data_handler($this->parser, 'lockCdata');

                        if (!xml_parse($this->parser, $response['body'])) {
                            die(sprintf('XML error: %s at line %d', xml_error_string(xml_get_error_code($this->parser)), xml_get_current_line_number($this->parser)));
                        }

                        // Free resources
                        xml_parser_free($this->parser);
                        // add status code to array
                        $this->lock[$idx]['status'] = 200;
                        return $this->lock[$idx];
                    } else {
                        print 'Missing Content-Type: text/xml header in response.<br>';
                    }
                    return false;
                default:
                    // hmm. not what we expected. Just return what we got from webdav server
                    // someone else has to handle it.
                    $this->lock['status'] = $response['status']['status-code'];
                    return $this->lock;
            }
        }
    }

    /**
     * Public method unlock
     *
     * Unlock a file or collection.
     *
     * @param string path, string locktoken
     * @return int status code (look at rfc 2518). false on error.
     */
    public function unlock($path, $locktoken)
    {
        $this->path = $this->translateUri($path);
        $this->headerUnset();
        $this->createBasicWebRequest('UNLOCK');
        $this->headerAdd(sprintf('Lock-Token: <%s>', $locktoken));
        $this->sendRequest();
        $this->getWebResponse();
        $response = $this->processWebResponse();
        if ($response['status']['http-version'] == 'HTTP/1.1' ||
        $response['status']['http-version'] == 'HTTP/1.0') {
            /* seems to be http ... proceed
              rfc 2518 says:
              204 (OK) - The 204 (No Content) status code is used instead of 200 (OK) because there is no response entity body.
             */
            return $response['status']['status-code'];
        }
        return false;
    }

    /**
     * Private method _endelement
     *
     * a generic endElement method  (used for all xml callbacks)
     * @param resource parser, string name
     *
     */
    public function endElement($parser, $name)
    {
        $idx = (int) $parser;
        $this->xmltree[$idx] = substr($this->xmltree[$idx], 0, strlen($this->xmltree[$idx]) - (strlen($name) + 1));
    }

    /**
     * Private method propfindStartElement
     *
     * Is needed by public method ls.
     * Generic method will called by php xml_parse when a xml start element tag has been detected.
     * The xml tree will translated into a flat php array for easier access.
     * @param resource parser, string name, string attrs
     *
     */
    public function propfindStartElement($parser, $name, $attrs)
    {
        // lower XML Names... maybe break a RFC, don't know ...
        $idx = (int) $parser;
        $propname = strtolower($name);
        if (!isset($this->xmltree[$idx]))
            $this->xmltree[$idx] = '';

        $this->xmltree[$idx] .= $propname . '_';

        // translate xml tree to a flat array ...
        switch ($this->xmltree[$idx]) {
            case 'dav::multistatus_dav::response_':
                // new element in mu
                $this->lsRef = & $this->ls[$idx][];
                break;
            case 'dav::multistatus_dav::response_dav::href_':
                $this->lsRefCdata = &$this->lsRef['href'];
                break;
            case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::creationdate_':
                $this->lsRefCdata = &$this->lsRef['creationdate'];
                break;
            case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::getlastmodified_':
                $this->lsRefCdata = &$this->lsRef['lastmodified'];
                break;
            case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::getcontenttype_':
                $this->lsRefCdata = &$this->lsRef['getcontenttype'];
                break;
            case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::getcontentlength_':
                $this->lsRefCdata = &$this->lsRef['getcontentlength'];
                break;
            case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::lockdiscovery_dav::activelock_dav::depth_':
                $this->lsRefCdata = &$this->lsRef['activelock_depth'];
                break;
            case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::lockdiscovery_dav::activelock_dav::owner_dav::href_':
                $this->lsRefCdata = &$this->lsRef['activelock_owner'];
                break;
            case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::lockdiscovery_dav::activelock_dav::owner_':
                $this->lsRefCdata = &$this->lsRef['activelock_owner'];
                break;
            case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::lockdiscovery_dav::activelock_dav::timeout_':
                $this->lsRefCdata = &$this->lsRef['activelock_timeout'];
                break;
            case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::lockdiscovery_dav::activelock_dav::locktoken_dav::href_':
                $this->lsRefCdata = &$this->lsRef['activelock_token'];
                break;
            case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::lockdiscovery_dav::activelock_dav::locktype_dav::write_':
                $this->lsRefCdata = &$this->lsRef['activelock_type'];
                $this->lsRefCdata = 'write';
                $this->lsRefCdata = &$this->null;
                break;
            case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::resourcetype_dav::collection_':
                $this->lsRefCdata = &$this->lsRef['resourcetype'];
                $this->lsRefCdata = 'collection';
                $this->lsRefCdata = &$this->null;
                break;
            case 'dav::multistatus_dav::response_dav::propstat_dav::status_':
                $this->lsRefCdata = &$this->lsRef['status'];
                break;

            default:
                // handle unknown xml elements...
                $this->lsRefCdata = &$this->lsRef[$this->xmltree[$idx]];
        }
    }

    /**
     * Private method propfindCdata
     *
     * Is needed by public method ls.
     * Will be called by php xml_set_character_data_handler() when xml data has to be handled.
     * Stores data found into class var _lsRefCdata
     * @param resource parser, string cdata
     *
     */
    public function propfindCdata($parser, $cdata)
    {
        if (trim($cdata) <> '') {
            $this->lsRefCdata = $cdata;
        } else {
            // do nothing
        }
    }

    /**
     * Private method deleteStartElement
     *
     * Is used by public method delete.
     * Will be called by php xml_parse.
     * @param resource parser, string name, string attrs)
     *
     */
    public function deleteStartElement($parser, $name, $attrs)
    {
        $idx = (int) $parser;
        // lower XML Names... maybe break a RFC, don't know ...
        $propname = strtolower($name);
        $this->xmltree[$parser] .= $propname . '_';

        // translate xml tree to a flat array ...
        switch ($this->xmltree[$idx]) {
            case 'dav::multistatus_dav::response_':
                // new element in mu
                $this->deleteRef = & $this->delete[$idx][];
                break;
            case 'dav::multistatus_dav::response_dav::href_':
                $this->deleteRefCdata = &$this->lsRef['href'];
                break;

            default:
                // handle unknown xml elements...
                $this->deleteCdata = &$this->deleteRef[$this->xmltree[$idx]];
        }
    }

    /**
     * Private method deleteCdata
     *
     * Is used by public method delete.
     * Will be called by php xml_set_character_data_handler() when xml data has to be handled.
     * Stores data found into class var _deleteRefCdata
     * @param resource parser, string cdata
     *
     */
    private function deleteCdata($parser, $cdata)
    {
        if (trim($cdata) <> '') {
            $this->deleteRefCdata = $cdata;
        } else {
            // do nothing
        }
    }

    /**
     * Private method lockStartElement
     *
     * Is needed by public method lock.
     * Mmethod will called by php xml_parse when a xml start element tag has been detected.
     * The xml tree will translated into a flat php array for easier access.
     * @param resource parser, string name, string attrs
     *
     */
    public function lockStartElement($parser, $name, $attrs)
    {
        $idx = (int) $parser;
        // lower XML Names... maybe break a RFC, don't know ...
        $propname = strtolower($name);
        $this->xmltree[$idx] .= $propname . '_';

        // translate xml tree to a flat array ...
        /*
          dav::prop_dav::lockdiscovery_dav::activelock_dav::depth_=
          dav::prop_dav::lockdiscovery_dav::activelock_dav::owner_dav::href_=
          dav::prop_dav::lockdiscovery_dav::activelock_dav::timeout_=
          dav::prop_dav::lockdiscovery_dav::activelock_dav::locktoken_dav::href_=
         */
        switch ($this->xmltree[$parser]) {
            case 'dav::prop_dav::lockdiscovery_dav::activelock_':
                // new element
                $this->lockRef = & $this->lock[$idx][];
                break;
            case 'dav::prop_dav::lockdiscovery_dav::activelock_dav::locktype_dav::write_':
                $this->lockRefCdata = &$this->lockRef['locktype'];
                $this->lockCdata = 'write';
                $this->lockCdata = &$this->null;
                break;
            case 'dav::prop_dav::lockdiscovery_dav::activelock_dav::lockscope_dav::exclusive_':
                $this->lockRefCdata = &$this->lockRef['lockscope'];
                $this->lockRefCdata = 'exclusive';
                $this->lockRefCdata = &$this->null;
                break;
            case 'dav::prop_dav::lockdiscovery_dav::activelock_dav::depth_':
                $this->lockRefCdata = &$this->lockRef['depth'];
                break;
            case 'dav::prop_dav::lockdiscovery_dav::activelock_dav::owner_dav::href_':
                $this->lockRefCdata = &$this->lockRef['owner'];
                break;
            case 'dav::prop_dav::lockdiscovery_dav::activelock_dav::timeout_':
                $this->lockRefCdata = &$this->lockRef['timeout'];
                break;
            case 'dav::prop_dav::lockdiscovery_dav::activelock_dav::locktoken_dav::href_':
                $this->lockRefCdata = &$this->lockRef['locktoken'];
                break;
            default:
                // handle unknown xml elements...
                $this->lockCdata = &$this->lockRef[$this->xmltree[$idx]];
        }
    }

    /**
     * Private method lockCdata
     *
     * Is used by public method lock.
     * Will be called by php xml_set_character_data_handler() when xml data has to be handled.
     * Stores data found into class var _lockRefCdata
     * @param resource parser, string cdata
     *
     */
    private function lockCdata($parser, $cdata)
    {
        if (trim($cdata) <> '') {
            // $this->log(($this->xmltree[$parser]) . '='. htmlentities($cdata));
            $this->lockRefCdata = $cdata;
        } else {
            // do nothing
        }
    }

    /**
     * Private method headerAdd
     *
     * extends class var array _req
     * @param string string
     *
     */
    private function headerAdd($string)
    {
        $this->req[] = $string;
    }

    /**
     * Private method headerUset
     *
     * unsets class var array _req
     *
     */
    private function headerUnset()
    {
        unset($this->req);
    }

    /**
     * Private method createBasicWebRequest
     *
     * creates by using private method headerAdd an general request header.
     * @param string method
     *
     */
    private function createBasicWebRequest($method)
    {
        // $request = '';
        $this->headerAdd(sprintf('%s %s %s', $method, $this->path, $this->protocol));
        $this->headerAdd(sprintf('Host: %s', $this->server));
        //$request .= sprintf('Connection: Keep-Alive');
        $this->headerAdd(sprintf('Connection: Keep-Alive'));
        $this->headerAdd(sprintf('User-Agent: %s', $this->userAgent));
        $this->headerAdd(sprintf('Authorization: Basic %s', base64_encode("$this->user:$this->pass")));
    }

    /**
     * Private method sendRequest
     *
     * Sends a ready formed http/webdav request to webdav server.
     *
     */
    private function sendRequest()
    {
        // check if stream is declared to be open
        // only logical check we are not sure if socket is really still open ...
        if ($this->connectionClosed) {
            // reopen it
            // be sure to close the open socket.
            $this->dissconnect();
            $this->reopen();
        }

        // convert array to string
        $buffer = implode("\r\n", $this->req);
        $buffer .= "\r\n\r\n";
        $this->log($buffer);
        fputs($this->fp, $buffer);
    }

    /**
     * Private method getWebResponse
     *
     * Read the reponse of the webdav server.
     * Stores data into class vars _header for the header data and
     * _body for the rest of the response.
     * This routine is the weakest part of this class, because it very depends how php does handle a socket stream.
     * If the stream is blocked for some reason php is blocked as well.
     *
     */
    private function getWebResponse()
    {
        $this->log('getWebResponse()');
        // init vars (good coding style ;-)
        $buffer = '';
        $header = '';
        // attention: do not make max_chunk_size to big....
        $max_chunk_size = 8192;
        // be sure we got a open ressource
        if (!$this->fp) {
            $this->log('socket is not open. Can not process response');
            return false;
        }

        // following code maybe helps to improve socket behaviour ... more testing needed
        // disabled at the moment ...
        // socket_set_timeout($this->fp,1 );
        // $socket_state = socket_get_status($this->fp);
        // read stream one byte by another until http header ends

        $i = 0;
        do {
            $header.=fread($this->fp, 1);
            $i++;
        } while (!preg_match('/\\r\\n\\r\\n$/', $header) && $i < $this->maxheaderlength);

        $this->log('-----> ' . $header);

        if (preg_match('/Connection: close\\r\\n/', $header)) {
            // This says that the server will close connection at the end of this stream.
            // Therefore we need to reopen the socket, before are sending the next request...
            $this->log('Connection: close found');
            $this->connectionClosed = true;
        }
        // check how to get the data on socket stream
        // chunked or content-length (HTTP/1.1) or
        // one block until feof is received (HTTP/1.0)
        switch (true) {
            case (preg_match('/Transfer\\-Encoding:\\s+chunked\\r\\n/', $header)):
                $this->log('Getting HTTP chunked data...');
                do {
                    $byte = '';
                    $chunk_size = '';
                    do {
                        $chunk_size.=$byte;
                        $byte = fread($this->fp, 1);
                        // check what happens while reading, because I do not really understand how php reads the socketstream...
                        // but so far - it seems to work here - tested with php v4.3.1 on apache 1.3.27 and Debian Linux 3.0 ...
                        if (strlen($byte) == 0) {
                            $this->log('getWebResponse: warning --> read zero bytes');
                        }
                    } while ($byte != "\r" and strlen($byte) > 0);      // till we match the Carriage Return
                    fread($this->fp, 1);                           // also drop off the Line Feed
                    $chunk_size = hexdec($chunk_size);                // convert to a number in decimal system
                    if ($chunk_size > 0) {
                        $buffer .= fread($this->fp, $chunk_size);
                    }
                    fread($this->fp, 2);                            // ditch the CRLF that trails the chunk
                } while ($chunk_size);                            // till we reach the 0 length chunk (end marker)
                break;

            // check for a specified content-length
            case preg_match('/Content\\-Length:\\s+([0-9]*)\\r\\n/', $header, $matches):
                $this->log('Getting data using Content-Length ' . $matches[1]);
                // check if we the content data size is small enough to get it as one block
                if ($matches[1] <= $max_chunk_size) {
                    // only read something if Content-Length is bigger than 0
                    if ($matches[1] > 0) {
                        $buffer = fread($this->fp, $matches[1]);
                    } else {
                        $buffer = '';
                    }
                } else {
                    // data is to big to handle it as one. Get it chunk per chunk...
                    do {
                        $mod = $max_chunk_size % ($matches[1] - strlen($buffer));
                        $chunk_size = ($mod == $max_chunk_size ? $max_chunk_size : $matches[1] - strlen($buffer));
                        $buffer .= fread($this->fp, $chunk_size);
                        $this->log('mod: ' . $mod . ' chunk: ' . $chunk_size . ' total: ' . strlen($buffer));
                    } while ($mod == $max_chunk_size);
                }
                break;

            // check for 204 No Content
            // 204 responds have no body.
            // Therefore we do not need to read any data from socket stream.
            case preg_match('/HTTP\/1\.1\ 204/', $header):
                // nothing to do, just proceed
                $this->log('204 No Content found. No further data to read..');
                break;
            default:
                // just get the data until foef appears...
                $this->log('reading until feof...' . $header);
                socket_set_timeout($this->fp, 0);
                while (!feof($this->fp)) {
                    $buffer .= fread($this->fp, 4096);
                }
                // renew the socket timeout...does it do something ???? Is it needed. More debugging needed...
                socket_set_timeout($this->fp, $this->socketTimeout);
        }

        $this->header = $header;
        $this->body = $buffer;
        // $this->buffer = $header . "\r\n\r\n" . $buffer;
        $this->log($this->header);
    }

    // --------------------------------------------------------------------------
    // private method processWebResponse ...
    // analyse the reponse from server and divide into header and body part
    // returns an array filled with components
    /**
     * Private method processWebResponse
     *
     * Processes the webdav server respond and detects its components (header, body)
     * and returns data array structure.
     * @return array ret_struct
     *
     */
    private function processWebResponse()
    {
        $lines = explode("\r\n", $this->header);

        $ret_struct = array();
        $ret_struct['status'] = array();
        $ret_struct['header'] = array();
        $header_done = false;
        // $this->log($this->buffer);
        // First line should be a HTTP status line (see http://www.w3.org/Protocols/rfc2616/rfc2616-sec6.html#sec6)
        // Format is: HTTP-Version SP Status-Code SP Reason-Phrase CRLF

        $arr = explode(' ', $lines[0], 3);
        list($ret_struct['status']['http-version'],
             $ret_struct['status']['status-code'],
             $ret_struct['status']['reason-phrase']) = $arr;

        // print "HTTP Version: '$http_version' Status-Code: '$status_code' Reason Phrase: '$reason_phrase'<br>";
        // get the response header fields
        // See http://www.w3.org/Protocols/rfc2616/rfc2616-sec6.html#sec6
        for ($i = 1; $i < count($lines); $i++) {
            if (rtrim($lines[$i]) == '' && !$header_done) {
                $header_done = true;
                // print "--- response header end ---<br>";
            }
            if (!$header_done) {
                // store all found headers in array ...
                list($fieldname, $fieldvalue) = explode(':', $lines[$i]);
                // check if this header was allready set (apache 2.0 webdav module does this....).
                // If so we add the the value to the end the fieldvalue, separated by comma...
                if (!isset($ret_struct['header']))
                    $ret_struct['header'] = array();
                if (!isset($ret_struct['header'][$fieldname])) {
                    $ret_struct['header'][$fieldname] = trim($fieldvalue);
                } else {
                    $ret_struct['header'][$fieldname] .= ',' . trim($fieldvalue);
                }
            }
        }
        // print 'string len of response_body:'. strlen($response_body);
        // print '[' . htmlentities($response_body) . ']';
        $ret_struct['body'] = $this->body;
        return $ret_struct;
    }

    /**
     * Private method reopen
     *
     * Reopens a socket, if 'connection: closed'-header was received from server.
     * Uses public method open.
     *
     */
    private function reopen()
    {
        // let's try to reopen a socket
        $this->log('reopen a socket connection');
        return $this->open();
        /*
          $this->fp = fsockopen ($this->server, $this->port, $this->errno, $this->errstr, 5);
          set_time_limit(180);
          socket_set_blocking($this->fp, true);
          socket_set_timeout($this->fp,5 );
          if (!$this->fp) {
          $this->log("$this->errstr ($this->errno)\n");
          return false;
          } else {
          $this->connectionClosed = false;
          $this->log('reopen ok...' . $this->fp);
          return true;
          }
         */
    }

    /**
     * Private method translateUri
     *
     * translates an uri to raw url encoded string.
     * Removes any html entity in uri
     * @param string uri
     * @return string translated_uri
     *
     */
    private function translateUri($uri)
    {
        $uri = rtrim($uri, '/');
        if (!$uri || $uri[0] != '/' || $uri[0] != '\\') {
            $uri = '/'.$uri;
        }
        if ($this->baseUrl) {
            $uri = str_replace ($this->baseUrl, '', $uri);
            $uri = $this->baseUrl . $uri;
        }
        // remove all html entities...
        $uri = str_replace('//', '/', $uri);
        $native_path = html_entity_decode($uri);
        $parts = explode('/', $native_path);
        for ($i = 0; $i < count($parts); $i++) {
            $parts[$i] = rawurlencode($parts[$i]);
        }
        $pts = implode('/', $parts);
        //vd($pts);
        return $pts;
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
