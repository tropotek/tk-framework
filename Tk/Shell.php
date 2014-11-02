<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk;


/**
 * A class that can carry out console commands.
 *
 * Note: In order for ssh commands to work you must have ssh
 * rsa keys on both systems to avoid the password prompt.
 *
 *
 * @package Tk
 */
class Shell
{

    /**
     * scp from the dev derver to a remote server.
     *
     * @param string $srcHostPath
     * @param string $dstHostPath
     */
    static function scp($srcHostPath, $dstHostPath)
    {
        $cmd = sprintf("scp %s %s", escapeshellcmd($srcHostPath), escapeshellcmd($dstHostPath));
        return self::system($cmd);
    }

    /**
     * Execute commands on a remote server using ssh.
     *
     * Note: In order for this to work you must have ssh
     *   rsa keys (private/public) on both systems to avoid the password prompt.
     *
     *  Command: ssh-keygen -t rsa
     *
     * The private key would reside in the webserver user ~/.ssh/id_rsa file and
     * the public key would reside in the $sshUser ~/.ssh/authorized_keys file
     *
     * @param string $cmd
     * @param string $sshUser
     * @param string $sshServer
     * @return String
     * @throws \Tk\RuntimeException
     */
    static function ssh($cmd, $sshUser, $sshServer)
    {
        $error = 0;
        $return = '';
        $sshCmd = sprintf("ssh %s@%s \"%s\" 2>&1", escapeshellcmd($sshUser), escapeshellcmd($sshServer), $cmd);
        exec($sshCmd, $return, $error);
        $return = implode("\n", $return);
        if ($error) {
            throw new RuntimeException($return);
        }
        return $return;
    }

    /**
     * Wrapper for the exe() command
     *
     * @param string $cmd
     * @return string
     * @throws \Tk\RuntimeException
     */
    static function exec($cmd)
    {
        $error = 0;
        $return = '';
        exec($cmd . ' 2>&1', $return, $error);
        $return = implode("\n", $return);
        if ($error) {
            throw new RuntimeException($return);
        }
        return $return;
    }

    /**
     * Wrapper for the exe() command
     *
     * @param string $cmd
     * @return string
     * @throws \Tk\RuntimeException
     */
    static function exeProcess($cmd)
    {
        $retval = '';
        $error = '';

        $descriptorspec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w')
        );

        $resource = proc_open($cmd . ' 2>&1', $descriptorspec, $pipes, null, $_ENV);
        if (is_resource($resource))
        {
            $stdin = $pipes[0];
            $stdout = $pipes[1];
            $stderr = $pipes[2];

            while (! feof($stdout))
            {
                $str = fgets($stdout);
                $retval .= $str;
                print($str);
            }

            while (! feof($stderr))
            {
                $str = fgets($stderr);
                $error .= $str;
                print($str);
            }

            fclose($stdin);
            fclose($stdout);
            fclose($stderr);

            proc_close($resource);
        }

        if (!empty($error)) {
            throw new Tk_Exception($error);
        }
        return $retval;
    }

    /**
     * Wrapper for the exe() command to run commands in the background.
     *
     * @param string $cmd
     * @return int Returns the PID
     */
    static function exeBackground($cmd)
    {
        $command = $cmd . ' > /dev/null 2>&1 & echo $!';
        $op = null;
        $ret = null;
        exec($command, $op, $ret);
        if ($ret != 0) {
            throw new Exception('Error Executing Command: ' . $cmd);
        }

        $pid = (int)$op[0];
        if ($pid != "")
            return $pid;
        return false;
    }
}