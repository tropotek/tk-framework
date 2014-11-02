<?php

// SESSION
/*
 * Add the class name of the driver to use
 * Defaults to standard PHP filesystem sessions
 * EG:
 *   o `Tk\Session\Adapter\Database`
 *   o `Tk\Session\Adapter\Cookie`
 */
$config['session.driver'] = '';

/*
 * It must contain only alphanumeric characters and underscores. At least one letter must be present.
 */
$config['session.name'] = 'sn_' . substr(md5(dirname(__FILE__)), 0, 16);

/*
 * Enable or disable session encryption.
 * Note: this has no effect on the native session driver.
 * Note: the cookie driver always encrypts session data. Set to TRUE for stronger encryption.
 * TODO: Test that encryption works for large sessions????
 */
$config['session.encryption'] = false;

/*
 * session lifetime. Number of seconds that each session will last.
 * A value of 0 will keep the session active until the browser is closed (with a limit of 24h).
 * gc_maxlifetime
 */
$config['session.expiration'] = 86400;

/*
 * Number of page loads before the session id is regenerated.
 * A value of 0 will disable automatic session id regeneration.
 * NOTE: Still not stable for DB \Tk\Sessions
 */
$config['session.regenerate'] = 0;

/*
 * session.gc_probability in conjunction with session.gc_divisor is used to manage
 * probability that the gc (garbage collection) routine is started.
 * Defaults to 1. See session.gc_divisor for details.
 * Leave at 0 for debian based systems
 */
$config['session.gc_probability'] = 0;

/*
 * session.gc_divisor coupled with session.gc_probability defines the probability that the gc (garbage collection)
 * process is started on every session initialization. The probability is calculated by using
 * gc_probability/gc_divisor, e.g. 1/100 means there is a 1% chance that the GC process starts
 * on each request. session.gc_divisor
 * Defaults to 100.
 */
$config['session.gc_divisor'] = 100;

/*
 * Session parameters to validate: user_agent, ip_address, expiration.
 */
$config['session.validate'] = array('user_agent');



/*
 * Driver: Database
 * This is the database settings group.
 */
$config['session.database.group'] = 'db.connect.default';

/*
 * Driver: Database
 * This is the session table name
 */
$config['session.database.table'] = 'session';



