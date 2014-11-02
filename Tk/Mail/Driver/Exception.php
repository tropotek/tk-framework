<?php
/* ~ class.phpmailer.php
  .---------------------------------------------------------------------------.
  |  Software: PHPMailer - PHP email class                                    |
  |   Version: 5.2.1                                                          |
  |      Site: https://code.google.com/a/apache-extras.org/p/phpmailer/       |
  | ------------------------------------------------------------------------- |
  |     Admin: Jim Jagielski (project admininistrator)                        |
  |   Authors: Andy Prevost (codeworxtech) codeworxtech@users.sourceforge.net |
  |          : Marcus Bointon (coolbru) coolbru@users.sourceforge.net         |
  |          : Jim Jagielski (jimjag) jimjag@gmail.com                        |
  |   Founder: Brent R. Matzelle (original founder)                           |
  | Copyright (c) 2010-2012, Jim Jagielski. All Rights Reserved.              |
  | Copyright (c) 2004-2009, Andy Prevost. All Rights Reserved.               |
  | Copyright (c) 2001-2003, Brent R. Matzelle                                |
  | ------------------------------------------------------------------------- |
  |   License: Distributed under the Lesser General Public License (LGPL)     |
  |            http://www.gnu.org/copyleft/lesser.html                        |
  | This program is distributed in the hope that it will be useful - WITHOUT  |
  | ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or     |
  | FITNESS FOR A PARTICULAR PURPOSE.                                         |
  '---------------------------------------------------------------------------'
 */
namespace Tk\Mail\Driver;

/**
 * PHPMailer - PHP email transport class
 * NOTE: Requires PHP version 5 or later
 * @package PHPMailer
 * @author Andy Prevost
 * @author Marcus Bointon
 * @author Jim Jagielski
 * @copyright 2010 - 2012 Jim Jagielski
 * @copyright 2004 - 2009 Andy Prevost
 * @version $Id: class.phpmailer.php 450 2010-06-23 16:46:33Z coolbru $
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */
class Exception extends \Exception
{

    public function errorMessage()
    {
        $errorMsg = '<strong>' . $this->getMessage() . "</strong><br />\n";
        return $errorMsg;
    }

}

?>
