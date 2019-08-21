<?php


namespace Utilities;

define("EMAIL_ERROR_IMAP_ERROR", "80001");
define("EMAIL_ERROR_IMAP_STREAM", "80002");
define("EMAIL_ERROR_IMAP_LIST", "80003");
define("EMAIL_ERROR_MAILBOX_FOLDER", "80004");
define("EMAIL_ERROR_MAILBOX_HEADERS", "80005");
define("EMAIL_ERROR_MESSAGE_HEADER", "80006");
define("EMAIL_ERROR_MESSAGE_DATA", "80007");
define("EMAIL_ERROR_EDIT_MESSAGE_FLAGS", "80008");
define("EMAIL_ERROR_SEARCH_HEADERS", "80009");
define("EMAIL_ERROR_MESSAGE_NUMBER", "80010");
define("EMAIL_ERROR_IMAP_CLOSE_FAILURE", "80011");
define("EMAIL_ERROR_EDIT_MESSAGE_SET_FLAGS_AND_CLEAR_FLAGS", "80012");
define("EMAIL_ERROR_DUMP_ATTACHMENTS_DATA", "80013");
define("EMAIL_ERROR_DUMP_ATTACHMENTS_DIRECTORY", "80014");
define("EMAIL_ERROR_DUMP_ATTACHMENTS_NOT_EXIST", "80015");
define("EMAIL_ERROR_EDIT_MESSAGE_STATUS_SEQUENCE", "80016");
define("EMAIL_ERROR_EDIT_MESSAGE_STATUS_NEW_MESSAGE_STATUS", "80017");

define("EMAIL_ERROR_IMAP_ERROR_MESSAGE", "Imap is not installed");
define("EMAIL_ERROR_IMAP_STREAM_MESSAGE", "Failed to open imap stream");
define("EMAIL_ERROR_IMAP_LIST_MESSAGE", "Failed to get a list of mailbox folders");
define("EMAIL_ERROR_MAILBOX_FOLDER_MESSAGE", "Failed to open specific folder in mailbox");
define("EMAIL_ERROR_MAILBOX_HEADERS_MESSAGE", "Failed to get mailbox headers");
define("EMAIL_ERROR_MESSAGE_HEADER_MESSAGE", "Failed to get message header");
define("EMAIL_ERROR_MESSAGE_DATA_MESSAGE", "Failed to get message data");
define("EMAIL_ERROR_EDIT_MESSAGE_FLAGS_MESSAGE", "Failed to edit message flags");
define("EMAIL_ERROR_SEARCH_HEADERS_MESSAGE", "No search headers parsed");
define("EMAIL_ERROR_MESSAGE_NUMBER_MESSAGE", "No message number parsed");
define("EMAIL_ERROR_IMAP_CLOSE_FAILURE_MESSAGE", "Failed to close imap stream");
define("EMAIL_ERROR_EDIT_MESSAGE_SET_FLAGS_AND_CLEAR_FLAGS_MESSAGE", "Both setFlags() and clearFlags() have the same parameters");
define("EMAIL_ERROR_DUMP_ATTACHMENTS_DATA_MESSAGE", "No parsed message data");
define("EMAIL_ERROR_DUMP_ATTACHMENTS_DIRECTORY_MESSAGE", "No parsed directory");
define("EMAIL_ERROR_DUMP_ATTACHMENTS_NOT_EXIST_MESSAGE", "No attachments found for parsed message data");
define("EMAIL_ERROR_EDIT_MESSAGE_STATUS_SEQUENCE_MESSAGE", "No valid sequence number(s) parsed");
define("EMAIL_ERROR_EDIT_MESSAGE_STATUS_NEW_MESSAGE_STATUS_MESSAGE", "No valid new message statuses parsed");

define("EMAIL_ERROR_NO_ERROR", "No Error");


/**
 * Class EmailReaderError
 * @package Utilities
 */
class EmailReaderError
{
    public $code = null;
    public $message = null;
    public $imapErrors = null;

    /**
     * EmailReaderError constructor.
     * @param $code
     * @param $message
     * @param null $imapErrors
     */
    function __construct($code, $message, $imapErrors = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->imapErrors = $imapErrors;
    }


    /**
     * Get Error returns an object with the error code and message
     * @return string
     */
    public function getError()
    {
        if ($this->code !== null && $this->message !== null) {
            return (object)["errorCode" => $this->code, "errorMessage" => $this->message, "imapErrors" => $this->imapErrors];
        } else {
            return (object)["errorCode" => 0, "errorMessage" => NO_ERROR];
        }

    }
}
