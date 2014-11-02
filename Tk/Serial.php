<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk;


/**
 * This is a connector to the serial interface of the computer.
 *
 * This is a handy class for communicating with Arduino projects.
 *
 * <code>
 * <?php
 *    $serial = new Tk_Serial();
 *    $serial->deviceSet('/dev/ttyUSB0');
 *    $serial->confBaudRate(9600);        // Baud rate: 9600
 *    $serial->confParity('none');        // Parity (this is the "N" in "8-N-1")
 *    $serial->confCharacterLength(8);    // Character length - this is the "8" in "8-N-1"
 *    $serial->confStopBits(1);           // Stop bits (this is the "1" in "8-N-1")
 *    $serial->confFlowControl('none');   // Options: none, rts/cts, xon/xoff
 *    $serial->deviceOpen();
 *    $serial->deviceClose();
 *    $serial->deviceOpen();
 *    $data = $serial->readLine();
 *    $serial->deviceClose();
 * ?>
 * </code>
 *
 * @see http://www.phpclasses.org/package/3679-PHP-Communicate-with-a-serial-port.html
 */
class Serial
{
    /**
     * @var int
     */
    const SERIAL_DEVICE_NOTSET = 0;

    /**
     * @var int
     */
    const SERIAL_DEVICE_SET = 1;

    /**
     * @var int
     */
    const SERIAL_DEVICE_OPENED = 2;

    /**
     * @var string
     */
    protected $device = null;

    /**
     * @var string
     */
    protected $windevice = null;

    /**
     * @var resource
     */
    protected $dHandle = null;

    /**
     * @var int
     */
    protected $dState = self::SERIAL_DEVICE_NOTSET;

    /**
     * @var string
     */
    protected $buffer = '';

    /**
     * @var string
     */
    protected $os = '';

    /**
     * This var says if buffer should be flushed by sendMessage (true) or manualy (false)
     *
     * @var bool
     */
    protected $autoflush = true;



    /**
     * Constructor. Perform some checks about the OS and setserial
     *
     */
    public function __construct()
    {
        setlocale(\LC_ALL, 'en_US');
        $sysname = php_uname();
        if (substr($sysname, 0, 5) === 'Linux') {
            $this->os = 'linux';
            if ($this->exec('stty --version') === 0) {
                register_shutdown_function(array($this, 'deviceClose'));
            } else {
                throw new Exception('No stty availible, unable to run.', \E_USER_ERROR);
            }
        } elseif (substr($sysname, 0, 7) === 'Windows') {
            $this->os = 'windows';
            register_shutdown_function(array($this, 'deviceClose'));
        } else {
            throw new Exception('Host OS is neither linux nor windows, unable to run.', \E_USER_ERROR);
            exit();
        }
    }

    /**
     * Device set function : used to set the device name/address.
     *  o linux : use the device address, like /dev/ttyS0
     *  o windows : use the COMxx device name, like COM1 (can also be used with linux)
     *
     * @param string $device the name of the device to be used
     * @return bool
     */
    public function deviceSet($device)
    {
        if ($this->dState !== self::SERIAL_DEVICE_OPENED) {
            if ($this->os === 'linux') {
                if (preg_match('@^COM(\d+):?$@i', $device, $matches)) {
                    $device = '/dev/ttyS' . ($matches[1] - 1);
                }
                if ($this->exec('stty -F ' . $device) === 0) {
                    $this->device = $device;
                    $this->dState = self::SERIAL_DEVICE_SET;
                    return true;
                }
            } elseif ($this->os === 'windows') {
                if (preg_match('@^COM(\d+):?$@i', $device, $matches) and $this->exec(exec('mode ' . $device)) === 0) {
                    $this->windevice = 'COM' . $matches[1];
                    $this->device = '\.\com' . $matches[1];
                    $this->dState = self::SERIAL_DEVICE_SET;
                    return true;
                }
            }
            //throw new Exception('Specified serial port is not valid', E_USER_WARNING);
            Log::write('Specified serial port is not valid', Log::ALERT);
            return false;
        } else {
            //throw new Exception('You must close your device before to set an other one', E_USER_WARNING);
            Log::write('You must close your device before to set an other one', Log::ALERT);
            return false;
        }
    }

    /**
     * Opens the device for reading and/or writing.
     *
     * @param string $mode Opening mode : same parameter as fopen()
     * @return bool
     */
    public function deviceOpen($mode = 'r+b')
    {
        if ($this->dState === self::SERIAL_DEVICE_OPENED) {
            //throw new Exception('The device is already opened', E_USER_NOTICE);
            Log::write('The device is already opened', Log::ALERT);
            return true;
        }

        if ($this->dState === self::SERIAL_DEVICE_NOTSET) {
            //throw new Exception('The device must be set before to be open', E_USER_WARNING);
            Log::write('The device must be set before to be open', Log::ALERT);
            return false;
        }

        if (!preg_match('@^[raw]\+?b?$@', $mode)) {
            //throw new Exception('Invalid opening mode : ' . $mode . '. Use fopen() modes.', E_USER_WARNING);
            Log::write('Invalid opening mode : ' . $mode . '. Use fopen() modes.', Log::ALERT);
            return false;
        }

        $this->dHandle = fopen($this->device, $mode);
        if ($this->dHandle !== false) {
            //stream_set_blocking($this->dHandle, 1);
            $this->dState = self::SERIAL_DEVICE_OPENED;
            return true;
        }

        $this->dHandle = null;
        //throw new Exception('Unable to open the device', E_USER_WARNING);
        Log::write('Unable to open the device', Log::ALERT);
        return false;
    }

    /**
     * Closes the device
     *
     * @return bool
     */
    public function deviceClose()
    {
        if ($this->dState !== self::SERIAL_DEVICE_OPENED) {
            return true;
        }

        if (fclose($this->dHandle)) {
            $this->dHandle = null;
            $this->dState = self::SERIAL_DEVICE_SET;
            return true;
        }

        //throw new Exception('Unable to close the device', E_USER_ERROR);
        Log::write('Unable to close the device', Log::ERROR);
        return false;
    }


    /**
     * Sends a string to the device
     *
     * @param string $str string to be sent to the device
     * @param float $waitForReply time to wait for the reply (in seconds)
     */
    public function sendMessage($str, $waitForReply = 0.1)
    {
        $this->buffer .= $str;

        if ($this->autoflush === true)
            $this->flush();

        usleep((int)($waitForReply * 1000000));
    }

    /**
     * Reads the port until no new data is availible, then return the content.
     *
     * @param int $count number of characters to be read (will stop before
     * if less characters are in the buffer)
     * @return string
     */
    public function readPort($count = 0)
    {
        if ($this->dState !== self::SERIAL_DEVICE_OPENED) {
            //throw new Exception('Device must be opened to read it', E_USER_WARNING);
            Log::write('Device must be opened to read it', Log::ALERT);
            return false;
        }

        if ($this->os === 'linux') {
            $content = '';
            $i = 0;

            if ($count !== 0) {
                do {
                    if ($i > $count) {
                        $content .= fread($this->dHandle, ($count - $i));
                    } else {
                        $content .= fread($this->dHandle, 128);
                    }
                } while (($i += 128) === strlen($content));
            } else {
                do {
                    $content .= fread($this->dHandle, 128);
                } while (($i += 128) === strlen($content));
            }

            return $content;
        } elseif ($this->os === 'windows') {
            // Windows port reading procedures still buggy
            $content = '';
            $i = 0;

            if ($count !== 0) {
                do {
                    if ($i > $count)
                        $content .= fread($this->dHandle, ($count - $i));
                    else
                        $content .= fread($this->dHandle, 128);
                } while (($i += 128) === strlen($content));
            } else {
                do {
                    $content .= fread($this->dHandle, 128);
                } while (($i += 128) === strlen($content));
            }

            return $content;
        }

        //throw new Exception('Reading serial port is not implemented for Windows', E_USER_WARNING);
        Log::write('Reading serial port is not implemented for Windows', Log::ALERT);
        return false;
    }


    /**
     * read data until we get to the count size or a newline
     *
     *
     * @param int $count
     * @return string
     */
    public function readLine($count = null)
    {
        if ($this->dState !== self::SERIAL_DEVICE_OPENED) {
            //throw new Exception('Device must be opened to read it', E_USER_WARNING);
            Log::write('Device must be opened to read it', Log::ALERT);
            return false;
        }
        if ($count) {
            return fgets($this->dHandle, $count);
        }
        return fgets($this->dHandle);
    }


    /**
     * Tk_Configure the Baud Rate
     * Possible rates : 110, 150, 300, 600, 1200, 2400, 4800, 9600, 38400,
     * 57600 and 115200.
     *
     * @param int $rate the rate to set the port in
     * @return bool
     */
    public function confBaudRate($rate = 9600)
    {
        if ($this->dState !== self::SERIAL_DEVICE_SET) {
            //throw new Exception('Unable to set the baud rate : the device is either not set or opened', E_USER_WARNING);
            Log::write('Unable to set the baud rate : the device is either not set or opened', Log::ALERT);
            return false;
        }

        $validBauds = array(110 => 11, 150 => 15, 300 => 30, 600 => 60, 1200 => 12, 2400 => 24, 4800 => 48, 9600 => 96, 19200 => 19, 38400 => 38400, 57600 => 57600, 115200 => 115200);
        $out = array();
        if (isset($validBauds[$rate])) {
            if ($this->os === 'linux') {
                $ret = $this->exec('stty -F ' . $this->device . ' speed ' . (int)$rate, $out);
            } elseif ($this->os === 'windows') {
                $ret = $this->exec('mode ' . $this->windevice . ' BAUD=' . $validBauds[$rate], $out);
            } else
                return false;

            if ($ret !== 0) {
                //throw new Exception('Unable to set baud rate: ' . $out[1], E_USER_WARNING);
                Log::write('Unable to set baud rate: ' . $out[1], Log::ALERT);
                return false;
            }
        }
    }

    /**
     * Tk_Configure parity.
     * Modes : odd, even, none
     *
     * @param string $parity one of the modes
     * @return bool
     */
    public function confParity($parity)
    {
        if ($this->dState !== self::SERIAL_DEVICE_SET) {
            //throw new Exception('Unable to set parity : the device is either not set or opened', E_USER_WARNING);
            Log::write('Unable to set parity : the device is either not set or opened', Log::ALERT);
            return false;
        }

        $args = array('none' => '-parenb', 'odd' => 'parenb parodd', 'even' => 'parenb -parodd');
        $out = array();
        if (!isset($args[$parity])) {
            //throw new Exception('Parity mode not supported', E_USER_WARNING);
            Log::write('Parity mode not supported', Log::ALERT);
            return false;
        }

        if ($this->os === 'linux') {
            $ret = $this->exec('stty -F ' . $this->device . ' ' . $args[$parity], $out);
        } else {
            $ret = $this->exec('mode ' . $this->windevice . ' PARITY=' . $parity{0}, $out);
        }

        if ($ret === 0) {
            return true;
        }

        //throw new Exception('Unable to set parity : ' . $out[1], E_USER_WARNING);
        Log::write('Unable to set parity : ' . $out[1], Log::ALERT);
        return false;
    }

    /**
     * Sets the length of a character.
     *
     * @param int $int length of a character (5 <= length <= 8)
     * @return bool
     */
    public function confCharacterLength($int)
    {
        if ($this->dState !== self::SERIAL_DEVICE_SET) {
            //throw new Exception('Unable to set length of a character : the device is either not set or opened', E_USER_WARNING);
            Log::write('Unable to set length of a character : the device is either not set or opened', Log::ALERT);
            return false;
        }
        $out = array();
        $int = (int)$int;
        if ($int < 5)
            $int = 5;
        elseif ($int > 8)
            $int = 8;

        if ($this->os === 'linux') {
            $ret = $this->exec('stty -F ' . $this->device . ' cs' . $int, $out);
        } else {
            $ret = $this->exec('mode ' . $this->windevice . ' DATA=' . $int, $out);
        }

        if ($ret === 0) {
            return true;
        }

        throw new Exception('Unable to set character length : ' . $out[1], E_USER_WARNING);
        Log::write('Unable to set character length : ' . $out[1], Log::ALERT);
        return false;
    }

    /**
     * Sets the length of stop bits.
     *
     * @param float $length the length of a stop bit. It must be either 1,
     * 1.5 or 2. 1.5 is not supported under linux and on some computers.
     * @return bool
     */
    public function confStopBits($length)
    {
        if ($this->dState !== self::SERIAL_DEVICE_SET) {
            //throw new Exception('Unable to set the length of a stop bit : the device is either not set or opened', E_USER_WARNING);
            Log::write('Unable to set the length of a stop bit : the device is either not set or opened', Log::ALERT);
            return false;
        }

        if ($length != 1 && $length != 2 && $length != 1.5 && !($length == 1.5 && $this->os === 'linux')) {
            //throw new Exception('Specified stop bit length is invalid', E_USER_WARNING);
            Log::write('Specified stop bit length is invalid', Log::ALERT);
            return false;
        }
        $out = array();
        if ($this->os === 'linux') {
            $ret = $this->exec('stty -F ' . $this->device . ' ' . (($length == 1) ? '-' : '') . 'cstopb', $out);
        } else {
            $ret = $this->exec('mode ' . $this->windevice . ' STOP=' . $length, $out);
        }

        if ($ret === 0) {
            return true;
        }

        //throw new Exception('Unable to set stop bit length : ' . $out[1], E_USER_WARNING);
        Log::write('Unable to set stop bit length : ' . $out[1], Log::ALERT);
        return false;
    }

    /**
     * Tk_Configures the flow control
     * Availible modes :
     *  o 'none' : no flow control
     *  o 'rts/cts' : use RTS/CTS handshaking
     *  o 'xon/xoff' : use XON/XOFF protocol
     *
     * @param string $mode Set the flow control mode.
     * @return bool
     */
    public function confFlowControl($mode)
    {
        if ($this->dState !== self::SERIAL_DEVICE_SET) {
            //throw new Exception('Unable to set flow control mode : the device is either not set or opened', E_USER_WARNING);
            Log::write('Unable to set flow control mode : the device is either not set or opened', Log::ALERT);
            return false;
        }

        $linuxModes = array('none' => 'clocal -crtscts -ixon -ixoff', 'rts/cts' => '-clocal crtscts -ixon -ixoff', 'xon/xoff' => '-clocal -crtscts ixon ixoff');
        $windowsModes = array('none' => 'xon=off octs=off rts=on', 'rts/cts' => 'xon=off octs=on rts=hs', 'xon/xoff' => 'xon=on octs=off rts=on');

        if ($mode !== 'none' and $mode !== 'rts/cts' and $mode !== 'xon/xoff') {
            //throw new Exception('Invalid flow control mode specified', E_USER_ERROR);
            Log::write('Invalid flow control mode specified', Log::ERROR);
            return false;
        }
        $out = array();
        if ($this->os === 'linux')
            $ret = $this->exec('stty -F ' . $this->device . ' ' . $linuxModes[$mode], $out);
        else
            $ret = $this->exec('mode ' . $this->windevice . ' ' . $windowsModes[$mode], $out);

        if ($ret === 0)
            return true;
        else {
            //throw new Exception('Unable to set flow control : ' . $out[1], E_USER_ERROR);
            Log::write('Unable to set flow control : ' . $out[1], Log::ERROR);
            return false;
        }
    }

    /**
     * Sets a setserial parameter (cf man setserial)
     * NO MORE USEFUL !
     * -> No longer supported
     * -> Only use it if you need it
     *
     * @param string $param parameter name
     * @param string $arg parameter value
     * @return bool
     */
    public function setSetserialFlag($param, $arg = '')
    {
        if (!$this->ckOpened())
            return false;

        $return = exec('setserial ' . $this->device . ' ' . $param . ' ' . $arg . ' 2>&1');

        if ($return{0} === 'I') {
            //throw new Exception('setserial: Invalid flag', E_USER_WARNING);
            Log::write('setserial: Invalid flag', Log::ALERT);
            return false;
        } elseif ($return{0} === '/') {
            //throw new Exception('setserial: Error with device file', E_USER_WARNING);
            Log::write('setserial: Error with device file', Log::ALERT);
            return false;
        }
        return true;
    }

    /**
     * Flushes the output buffer
     *
     * @return bool
     */
    public function flush()
    {
        if (!$this->ckOpened())
            return false;

        if (fwrite($this->dHandle, $this->buffer) !== false) {
            $this->buffer = '';
            return true;
        } else {
            $this->buffer = '';
            //throw new Exception('Error while sending message', E_USER_WARNING);
            Log::write('Error while sending message', Log::ALERT);
            return false;
        }
    }

    /**
     *
     * @return bool
     */
    protected function ckOpened()
    {
        if ($this->dState !== self::SERIAL_DEVICE_OPENED) {
            //throw new Exception('Device must be opened', E_USER_WARNING);
            Log::write('Device must be opened', Log::ALERT);
            return false;
        }

        return true;
    }

    /**
     *
     * @return bool
     */
    protected function ckClosed()
    {
        if ($this->dState !== self::SERIAL_DEVICE_CLOSED) {
            //throw new Exception('Device must be closed', E_USER_WARNING);
            Log::write('Device must be closed', Log::ALERT);
            return false;
        }

        return true;
    }

    /**
     *
     * @param string $cmd
     * @param string $out
     * @return string
     */
    protected function exec($cmd, &$out = null)
    {
        $desc = array(1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
        $pipes = null;
        $proc = proc_open($cmd, $desc, $pipes);
        if (!count($pipes)) return '';  // Or throw error??
        $ret = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $retVal = proc_close($proc);

        if (func_num_args() == 2)
            $out = array($ret, $err);
        return $retVal;
    }

}
