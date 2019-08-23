<?php
/**
 * Dump the attachments in the message data object from getMessageData() to a specified directory location
 */

$directoryLocation = "C:\\Users\\user\\Downloads\\";

$emailReader->dumpAttachments($messageData, $directoryLocation);