<?php
/**
 * Created by PhpStorm.
 * User: andrevanzuydam
 * Date: 2019-08-19
 * Time: 09:30
 */

require_once  "vendor/autoload.php";


$emailReader = new \Utilities\EmailReader("oxyros.co.za", "glocell.oxyros", "jct1969", 143);


$mailBox = $emailReader->openMailBox("/notls");

$folders = $emailReader->getMailBoxFolders($mailBox);

$mailBoxFolder = $emailReader->openMailBoxFolder($folders[3]);

$headers = $emailReader->getMailBoxHeaders();

$email = $emailReader->getMessageData(418);

print_r($email);
//Open a connection to server
//Get a list of folders from the server
//Get a list of email headers from one of the folders - how do I know which folder ?
//Choose an email header - read the message from the server
//Save any attachments into attachment folder


//Manual: Check that the above things have worked.


$emailReader = new \Utilities\EmailReader("imap.gmail.com", "username", "password");


$mailBox = $emailReader->openMailBox();

$folders = $emailReader->getMailBoxFolders();

$mailBoxFolder = $emailReader->openMailBoxFolder($folders[0]);

$headers = $emailReader->getMailBoxHeaders();

$email = $emailReader->getMessageData(7);

$sequence = 3;
$destination = "[Gmail]/Drafts";
$moveResult = $emailReader->messageMove($sequence, $destination, $mailBoxFolder);

$copyMessage = $emailReader->messageCopy(1, $destination);




