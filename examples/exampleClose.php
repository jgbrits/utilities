<?php
/**
 * Close the IMAPStream
 */

$emailReader->close(); //Closes current IMAPStream

/**
 * If you want to close a different IMAPSteam you can parse it as a resource
 */
$otherMailBoxFolder = $emailReader->openMailBoxFolder($folders[2]);

$emailReader->close($otherMailBoxFolder); //Closes a different IMAPStream