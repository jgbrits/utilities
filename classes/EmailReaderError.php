<?php


namespace Utilities;

/**
 * Class EmailReaderError
 * @package Utilities
 * @todo EmailReaderError is only returning the error code not the error message
 */
class EmailReaderError
{
    public $code = false;

    function __construct($code)
    {
        $this->code = $code;
        define("EMAIL_ERROR_IMAP_ERROR", "Imap is not installed");
        define("EMAIL_ERROR_IMAP_STREAM", "Failed to open imap stream");
        define("EMAIL_ERROR_IMAP_LIST", "Failed to get a list of mailbox folders");
        define("EMAIL_ERROR_MAILBOX_FOLDER", "Failed to open specific folder in mailbox");
        define("EMAIL_ERROR_MAILBOX_HEADERS", "Failed to get mailbox headers");
        define("EMAIL_ERROR_MESSAGE_HEADER", "Failed to get message header");
        define("EMAIL_ERROR_MESSAGE_DATA", "Failed to get message data");
        define("EMAIL_ERROR_EDIT_MESSAGE_FLAGS", "Failed to edit message flags");

        if (defined($this->code)) {
            return constant("{$this->code}");
        } else {
            return false;
        }
    }
}