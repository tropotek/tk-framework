<?php


// Set this in your config.ini
// This system uses PDO for its database engine
// See the PHP docs for more about the type value
// NOTICE: This system has only been tested with
//         the mysql type. Other types may produce
//         unknown results
//
//    ; Database connection parameters
//    [db.connect.default]
//    type                        = mysql
//    host                        = localhost
//    dbname                      = dbname
//    user                        = dbuser
//    pass                        = dbpass
//    ;prefix                     = ""
//
//
$config['db.connect.default'] = array(
    'type' => 'mysql',
    'host' => 'localhost',
    'dbname' => '',
    'user' => '',
    'pass' => '',
    'prefix' => ''
);


