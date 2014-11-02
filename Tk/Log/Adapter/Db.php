<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Log\Adapter;

/**
 * A Database logger log observer interface...
 *
 *
 * TODO: FIX IT MAN!!!!!!!!!!!!!!!!! OR REMOVE IT!
 *
 * @package Tk\Log\Adapter
 */
class Db extends Iface
{

    protected $table = 'log';
    
    
    /**
     * update
     *
     * @param \Tk\Log\Log $obj
     */
    public function update($obj)
    {
        if (!($this->getLevel() & $obj->getType()) ) {
            return;
        }

        $from = $to = \Tk\Config::getInstance()->get('site.email');

//        $message = new \Tk\Mail\DomMessage();
//        $message->addTo($to);
//        $message->setFrom($from);
//        $message->setSubject($_SERVER['HTTP_HOST'] . ' | ' . $obj->getHeader());
//        $ps = highlight_string("< ?php\n".$this->getDefaultDump()."\n ? >", true);
//        $ps = str_replace('<span style="color: #0000BB">&lt;?php<br />', '<span style="color: #0000BB;">', $ps);
//        $ps = str_replace('<span style="color: #0000BB">?&gt;</span>', '', $ps);
//        $html = $obj->getMessage()  . ' <p>&#160;</p><hr/><div style="font-family: monospace;font-size: 0.95em;">' . substr($ps, 6, -8).'</div>';
//        $message->setContent($html);
//        $message->send();

    }

    
    public function checkDb()
    {
        $sql = <<<SQL
-- --------------------------------------------------------
--
-- Table structure for table `log`
--
DROP TABLE IF EXISTS `log`;
CREATE TABLE `log` (
  `date` DATETIME NOT NULL DEFAULT NOW(),
  `key` varchar(64) NOT NULL DEFAULT '' COMMENT 'A key to identify the type of comment',
  `level` varchar(10) NOT NULL DEFAULT '',
  `ip` varchar(64) NOT NULL DEFAULT '',
  `message` text,
  PRIMARY KEY (`date`),
  KEY `key` (`key`),
  KEY `level` (`name`),
  KEY `orderBy` (`orderBy`)
) ENGINE=InnoDB;
SQL;
        // TODO: install Table inf not found.
    }
    
}