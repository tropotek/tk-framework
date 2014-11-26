<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Log\Adapter;

/**
 * A log observer interface...
 *
 * @package Tk\Log\Adapter
 */
class Email extends Iface
{

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

        $from = $to = \Tk\Config::getInstance()->getSiteEmail();

        $message = new \Tk\Mail\DomMessage();
        $message->addTo($to);
        $message->setFrom($from);
        $message->setSubject('Mail Log: ' . $_SERVER['HTTP_HOST'] . ' - ' . $obj->getHeader());
        $ps = highlight_string("<?php\n".$this->getDefaultDump()."\n?>", true);
        $ps = str_replace('<span style="color: #0000BB">&lt;?php<br />', '<span style="color: #0000BB;">', $ps);
        $ps = str_replace('<span style="color: #0000BB">?&gt;</span>', '', $ps);
        $html = $obj->getMessage()  . ' <p>&#160;</p><hr/><div style="font-family: monospace;font-size: 0.95em;">' . substr($ps, 6, -8).'</div>';
        $message->setContent($html);
        $message->send();

    }

}