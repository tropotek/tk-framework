<?php

// COOKIE
/*
 * Domain, to restrict the cookie to a specific website domain. For security,
 * you are encouraged to set this option. An empty setting allows the cookie
 * to be read by any website domain.
 * The extra dot at the start of the domain includes subdomains
 */
$config['cookie.domain'] = '.' . $_SERVER['HTTP_HOST'];

/*
 * Restrict cookies to a specific path, typically the installation directory.
 */
$config['cookie.path'] = $siteUrl ? $siteUrl : '/';

/*
 * Lifetime of the cookie. A setting of 0 makes the cookie active until the
 * users browser is closed or the cookie is deleted.
 */
$config['cookie.expire'] = 0;

/*
 * Enable this option to only allow the cookie to be read when using the a
 * secure protocol.
 */
$config['cookie.secure'] = false;

/*
 * Enable this option to disable the cookie from being accessed when using a
 * secure protocol. This option is only available in PHP 5.2 and above.
 */
$config['cookie.httponly'] = false;


