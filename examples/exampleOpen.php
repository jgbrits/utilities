<?php

require "./vendor/autoload.php";

$host = "";
$username = "";
$password = "";
$port = ""; //(optional: default is 993)
$flags = ""; //(optional: default is "/imap/ssl")

$emailReader = new \Utilities\EmailReader($host, $username, $password, $port);

$mailBox = $emailReader->openMailBox($flags);