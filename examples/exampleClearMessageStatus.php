<?php
/**
 * Clear flags to a message in the mailbox folder
 * If you don't know message flags, use the predefined constants: EMAIL_SEEN, EMAIL_FLAGGED, EMAIL_DELETED, EMAIL_DRAFT or EMAIL_ANSWERED
 */

$sequence = "2,5"; //message numbers 2 to 5

$emailReader->clearMessageStatus($sequence, EMAIL_SEEN); //Message now becomes flagged as "\\Unseen"

/**
 * If you want to clear a flag of a message in a different mailbox folder
 * Note: If you do this you might have to close the newly opened IMAPStream
 */

$otherMailBoxFolder = $emailReader->openMailBoxFolder($folders[2]);

$messageHeader2 = $emailReader->clearMessageStatus($sequence, EMAIL_ANSWERED, $otherMailBoxFolder);
//$emailReader->close($otherMailBoxFolder);