<?php

// ----------------------
// ENVIRONMENT
// ----------------------

// Attempt to auto discover the site docRoot path
$_su = '';
if (isset($_SERVER['PHP_SELF'])) {
    $_su = dirname($_SERVER['PHP_SELF']);
    if (strlen($_su) == 1) {
        $_su = '';
    }
}

if (empty($config['system.sitePath'])) {
    $config['system.sitePath'] = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
}
if (empty($config['system.siteUrl'])) {
    $config['system.siteUrl'] =  $_su;
}

$config['system.srcPath'] = '/src';
$config['system.vendorPath'] = '/vendor';
$config['system.assetsPath'] = '/assets';
$config['system.dataPath'] = '/data';
$config['system.libPath'] = $config['system.vendorPath'] . '/ttek';
$config['system.cachePath'] = $config['system.dataPath'] . '/sysCache';
$config['system.mediaPath'] = $config['system.dataPath'] . '/media';
$config['system.tmpPath'] = $config['system.sitePath'] . $config['system.dataPath'] . '/tmp';



// ----------------------
// SYSTEM
// ----------------------

// Set the system default title
$config['system.site.title'] = 'Untitled Site';

// Set the default site email
$config['system.site.email'] = 'noreply@example.com';

// The following is just standard system info
$config['system.site.title'] = 'Tropotek Project';
$config['system.site.author'] = 'Tropotek';
$config['system.site.website'] = 'http://www.tropotek.com.au/';
$config['system.site.description'] = '';
$config['system.site.version'] = '1.0';
$config['system.site.released'] = '01-01-1970';

// Load composer package data into config if available.
if (is_file($config['system.sitePath'] . '/composer.json')) {
    $info = json_decode(file_get_contents($config['system.sitePath'] . '/composer.json'));
    if ($info) {
        if (isset($info->name)) $config['system.site.title'] = $info->name;
        if (isset($info->version)) $config['system.site.version'] = $info->version;
        if (isset($info->authors[0]->name)) $config['system.site.author'] = $info->authors[0]->name;
        if (isset($info->homepage)) $config['system.site.website'] = $info->homepage;
        if (isset($info->description)) $config['system.site.description'] = $info->description;
    }
}

// Set to true if you want to use SSL pages
$config['system.enableSsl'] = false;

// Set the default text encoding
$config['system.encoding'] = 'utf-8';

// Set the default language
$config['system.language'] = 'en_AU';

// Set the Default timezone
$config['system.timezone'] = 'Australia/Victoria';

// Set to true if you want to use SSL pages
$config['system.enableSsl'] = false;


// ----------------------
// LOG
// ----------------------

// Leave as is to use system default log.
//$config['system.log.path'] = 'data/error.log';

// Remember to set the log level to capture log data
// To view all logs: ~\Tk\Log\Log::SYSTEM | \Tk\Log\Log::SYSTEM
$config['system.log.level'] = 0;

// Set the log level for sending email logs.
$config['system.log.emailLevel'] = 8;




// ----------------------
// DEBUG
// ----------------------

// Set to true to receive a detailed data dump in the log
// Remember to set the log path and to also set the log level
$config['system.debugMode'] = false;

// Release mode should be set according to what environment you
//  are running the system on.
//   o \Tk\Config::RELEASE_DEV - Used when developing the application
//   o \Tk\Config::RELEASE_TEST - Used when the application is being tested for release
//   o \Tk\Config::RELEASE_LIVE - Use when deploying the application to production servers
//
// These settings allow us to control code that can only be run in these modes
//  It is imperitive that you do not set the wrong mode for the wrong server
//  as things may not run as expected
//
$config['system.releaseMode'] = \Tk\Config::RELEASE_DEV;

// When in debug mode all emails will be sent to this address
// View hte email headers to see the actual recipient list
$config['system.debugEmail'] = 'noreply@example.com';





// ----------------------
// MISC
// ----------------------


// Set the hash method for the auth system
// This can be a user defined function.
// NOTICE: Be sure not to change this after installing the
// site, unless you update existing password hashes or require
// users to re-authenticate their passwords.
$config['system.auth.hashFunction'] = 'md5';
$config['system.auth.userClass'] = '';
// ---------------------------------------------------
// To Disable:
//      $config['system.auth.masterKey'] = false;
// ---------------------------------------------------
$tz = ini_get('date.timezone');
ini_set('date.timezone', 'Australia/Victoria');
$config['system.auth.masterKey'] = date('=d-m-Y=', time());
ini_set('date.timezone', $tz);

