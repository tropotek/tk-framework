<?php


/*
 * Any bcc to monitor emails add multiple seperate by comma
 */
$config['system.site.email.bcc'] = '';

/*
 * Set a signiture for all emails.
 * This will be appended to the foot of the emails
 */
$config['mail.sig'] = '';

/* mail.sendMethod
 * Options are:
 *  o mail - Use the php mail() method
 *  o smtp - Use external SMTP server
 *  o sendmail - Use the linux sendmail command
 *  o qmail - Use the qmail sendmail command
 *  o pop3 - Use external pop3 server Requires: mail.smtp.host, mail.smtp.username, mail.smtp.password
 *
 * @TODO: add default config for other mail send options.
 */
$config['mail.method'] = 'mail';

/*
 * A list of valid mail referring domains.
 * Either an array or a comma seperated string.
 */
$config['mail.validReferers'] = array();

/*
 * SMTP Mail sending settings.
 * (optional)
 */
$config['mail.smtp.host'] = '';
$config['mail.smtp.port'] = '25';
$config['mail.smtp.username'] = '';
$config['mail.smtp.password'] = '';
$config['mail.smtp.enableAuth'] = false;
$config['mail.smtp.enableKeepAlive'] = false;
// Options are ssl or tls or blank for no encryption
$config['mail.smtp.secure'] = '';



