<?php

require "./vendor/autoload.php";


/**
 * Instantiate the constructor and open the IMAPStream
 */
$host = "";
$username = "";
$password = "";
$port = ""; //(optional: default is 993)
$flags = ""; //(optional: default is "/imap/ssl")

$emailReader = new \Utilities\EmailReader($host, $username, $password, $port);

$mailBox = $emailReader->openMailBox($flags);

/**
 * If your port number is 993 and your flags are "/imap/ssl"
 */

$emailReader = new \Utilities\EmailReader($host, $username, $password);

$mailBox = $emailReader->openMailBox();