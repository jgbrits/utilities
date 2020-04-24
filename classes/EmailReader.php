<?php

/**
 * EmailReader is used read and interact with emails from an email server
 */
namespace Utilities;

use Utilities\EmailReaderError;


/**
 * Defining constants for setting and clearing flags
 */
define("EMAIL_SEEN", "\\Seen");
define("EMAIL_FLAGGED", "\\Flagged");
define("EMAIL_DELETED", "\\Deleted");
define("EMAIL_DRAFT", "\\Draft");
define("EMAIL_ANSWERED", "\\Answered");

/**
 * Class EmailReader Used to hold all the functions required for use
 * @package Utilities Namespace
 */
class EmailReader
{
    /**
     * @var IMAPStream Keeps a handle to a mailbox
     */
    private $mailBox = null;
    /**
     * @var String The hostname or address of the email server
     */
    private $host = null;

    /**
     * @var String Flags passed for specific mail box
     *
     * Link to optional flags: [https://www.php.net/manual/en/function.imap-open.php]
     */
    private $flags = null;

    /**
     * @var String $username Username
     */
    private $username = null;

    /**
     * @var String $password Password, don't commit your passwords into your code repo
     */
    private $password = null;

    /**
     * @var String object variable Holds the HTML messages
     */
    private $htmlMessage = null;

    /**
     * @var String object variable Holds plain messages
     */
    private $plainMessage = null;

    /**
     * @var String object variable Holds charset
     */
    private $charset = null;

    /**
     * @var object Holds attachments file names and data
     */
    private $attachments = null;

    /**
     * @var Integer $port Port to connect to
     */
    public $port = null;


    /**
     * * EmailReader constructor.
     * @param String $host Hostname of the email server
     * @param String $username Username
     * @param String $password Password, don't commit your passwords into your code repo
     * @param Integer $port Port to connect to
     * @example examples/exampleOpen.php
     */
    function __construct($host, $username, $password, $port = 993)
    {

        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
    }

    /**
     * * Handles any IMAP errors or alerts
     * @return array ["errors" => , "alerts" => ]
     */
    function handleErrors()
    {
        $errors = imap_errors();
        $alerts = imap_alerts();

        return ["errors" => $errors, "alerts" => $alerts];
    }

    /**
     * * Gets back a mail box handle for all future processing
     * @param String $flags Use URL to see available flags
     * @param null|String $folderName Folder name of opened mailbox folder when called by openMailBoxFolder()
     * @return resource|\Utilities\EmailReaderError|null IMAPStream or error message on failure
     * @example examples/exampleOpen.php
     */
    function openMailBox($flags = "/imap/ssl", $folderName = null)
    {
        if (!empty($flags)) {
            $this->flags = $flags;
        }
        if (function_exists("imap_open")) {

            $this->mailBox = imap_open("{{$this->host}:{$this->port}{$this->flags}}{$folderName}", $this->username, $this->password);

            // Check for errors only after mailbox was tried to be opened
            $errors = $this->handleErrors();

            // imap_open returns false if mailbox could not be opened (isset does not work here)
            if ($this->mailBox) {
                return $this->mailBox;
            } else {

                return new EmailReaderError (EMAIL_ERROR_IMAP_STREAM, EMAIL_ERROR_IMAP_STREAM_MESSAGE, $errors);
            }
        } else {

            return new EmailReaderError (EMAIL_ERROR_IMAP_ERROR, EMAIL_ERROR_IMAP_ERROR_MESSAGE);
        }
    }

    /**
     * * Gets a list of mailbox folders
     * @param null|resource $mailBox IMAPStream
     * @return array|\Utilities\EmailReaderError array[ [0] => , [1] => ] or error message on failure
     * @example examples/exampleFolders.php
     */
    function getMailBoxFolders($mailBox = null)
    {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();
        if (isset($mailBox) && !empty($mailBox) && is_resource($mailBox)) {

            $folders = imap_list($mailBox, "{{$this->host}}", "*");
            $parsedFolders = [];
            foreach ($folders as $id => $folder) {
                $tempName = explode("}", $folder); //Comes in the form {server}Folder
                if (isset($tempName[1])) {
                    $parsedFolders[] = $tempName[1];
                } else {
                    $parsedFolders[] = $folder;
                }

            }

            return $parsedFolders;
        } else {
            return new EmailReaderError (EMAIL_ERROR_IMAP_STREAM, EMAIL_ERROR_IMAP_STREAM_MESSAGE, $errors);
        }
    }

    /**
     * * Opens a mailbox stream in a specific mailbox folder
     * @param String $folderName Folder name
     * @return resource|\Utilities\EmailReaderError IMAPStream in the opened mailbox folder or error message on failure
     * @example examples/exampleOpenFolder.php
     */
    function openMailBoxFolder($folderName = null)
    {
        $errors = $this->handleErrors();
        if (isset($folderName) && !empty($folderName)) {
            $this->mailBox = $this->openMailBox($this->flags, $folderName);

            return $this->mailBox;
        } else {
            return new EmailReaderError (EMAIL_ERROR_MAILBOX_FOLDER, EMAIL_ERROR_MAILBOX_FOLDER_MESSAGE, $errors);
        }
    }

    /**
     * * Gets the message numbers of all messages that contain the parsed criteria
     * @param String $searchCriteria Search criteria. List of search criteria: https://www.php.net/manual/en/function.imap-search.php
     * @param null|resource $mailBox IMAPStream
     * @return array|\Utilities\EmailReaderError array[ [0] => , [1]=> ] or error message on failure
     * @example examples/exampleSearch
     */
    function search($searchCriteria, $mailBox = null)
    {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();

        if (isset($searchCriteria) && !empty($searchCriteria)) {
            if (isset($mailBox) && !empty($mailBox) && is_resource($mailBox)) {
                $searchResult = imap_search($mailBox, $searchCriteria);

                if (isset($searchResult)) {

                    return $searchResult;
                } else {
                    return new EmailReaderError (EMAIL_ERROR_SEARCH_FAIL, EMAIL_ERROR_SEARCH_FAIL_MESSAGE, $errors);
                }
            } else {
                return new EmailReaderError (EMAIL_ERROR_IMAP_STREAM, EMAIL_ERROR_IMAP_STREAM_MESSAGE, $errors);
            }
        } else {
            return new EmailReaderError (EMAIL_ERROR_SEARCH_CRITERIA, EMAIL_ERROR_SEARCH_CRITERIA_MESSAGE, $errors);
        }
    }

    /**
     * * Uses the message numbers to get and put all the message headers into an array
     * @param array $searchResult Array of search results from search()
     * @param null|resource $mailBox IMAPStream
     * @return array|\Utilities\EmailReaderError array[ [0] => , [1] => ] or error message on failure
     * @example examples/exampleSearchResultHeaders.php
     */
    function getSearchResultHeaders($searchResult, $mailBox = null)
    {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();
        if (isset($searchResult) && !empty($searchResult)) {
            if (isset($mailBox) && !empty($mailBox) && is_resource($mailBox)) {
                $searchResultHeaders = [];

                foreach ($searchResult as $messageNumber) {

                    $searchResultHeaders[] = imap_header($mailBox, $messageNumber);
                }
                if (!empty($searchResultHeaders)) {

                    return $searchResultHeaders;
                } else {
                    return new EmailReaderError (EMAIL_ERROR_SEARCH_HEADERS_FAIL, EMAIL_ERROR_SEARCH_HEADERS_FAIL_MESSAGE, $errors);
                }

            } else {
                return new EmailReaderError (EMAIL_ERROR_IMAP_STREAM, EMAIL_ERROR_IMAP_STREAM_MESSAGE, $errors);
            }
        } else {
            return new EmailReaderError (EMAIL_ERROR_SEARCH_HEADERS_RESULT, EMAIL_ERROR_SEARCH_HEADERS_RESULT_MESSAGE, $errors);
        }
    }

    /**
     * * Gets all the headers in the mailbox folder
     * @param null|resource $mailBox IMAPStream
     * @return array|\Utilities\EmailReaderError [ [0] => , [1] => ] or error message on failure
     * @example examples/exampleMailBoxHeaders.php
     */
    function getMailBoxHeaders($mailBox = null)
    {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();
        if (isset($mailBox) && !empty($mailBox) && is_resource($mailBox)) {

            $mailBoxHeaders = imap_headers($mailBox);

            return $mailBoxHeaders;
        } else {
            return new EmailReaderError (EMAIL_ERROR_IMAP_STREAM, EMAIL_ERROR_IMAP_STREAM_MESSAGE, $errors);
        }
    }

    /**
     * * Gets the headers of a specific message
     * @param Integer $messageNumber Message number
     * @param null|resource $mailBox IMAPStream
     * @return object
     * @example examples/exampleMessageHeader.php
     */
    function getMessageHeader($messageNumber, $mailBox = null)
    {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();
        if (isset($messageNumber) && !empty($messageNumber)) {
            if (isset($mailBox) && !empty($mailBox) && is_resource($mailBox)) {

                $messageHeader = imap_header($mailBox, $messageNumber);

                return $messageHeader;
            } else {
                return new EmailReaderError (EMAIL_ERROR_IMAP_STREAM, EMAIL_ERROR_IMAP_STREAM_MESSAGE, $errors);

            }
        } else {
            return new EmailReaderError (EMAIL_ERROR_MESSAGE_NUMBER, EMAIL_ERROR_MESSAGE_NUMBER_MESSAGE, $errors);
        }
    }

    /**
     * * Returns the ending result data array containing all the message's data
     * @param Integer $messageNumber Message number
     * @param null|resource $mailBox IMAPStream
     * @return object {"htmlMessage" => , "plainMessage" => , "charset" => , "attachments" => };
     * @example examples/exampleMessageData.php
     */
    function getMessageData($messageNumber, $mailBox = null)
    {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();
        if (isset($messageNumber) && !empty($messageNumber)) {
            if (isset($mailBox) && !empty($mailBox) && is_resource($mailBox)) {
                $emailMessage = (object)[];

                $structure = imap_fetchstructure($mailBox, $messageNumber);

                if (!$structure->parts) {
                    $emailMessage = $this->addMessageDataToArray($messageNumber, $structure, 0, $mailBox);
                } else {
                    foreach ($structure->parts as $partNumber0 => $p) {
                        $emailMessage = $this->addMessageDataToArray($messageNumber, $p, $partNumber0 + 1, $mailBox);
                    }
                }

                // Clear email message and attachments from email class object, to be empty for when next message is read
                $this->plainMessage = null;
                $this->htmlMessage = null;
                $this->attachments = null;

                return $emailMessage;

            } else {
                return new EmailReaderError (EMAIL_ERROR_IMAP_STREAM, EMAIL_ERROR_IMAP_STREAM_MESSAGE, $errors);

            }
        } else {
            return new EmailReaderError (EMAIL_ERROR_MESSAGE_NUMBER, EMAIL_ERROR_MESSAGE_NUMBER_MESSAGE, $errors);
        }
    }

    /**
     * * Returns the object containing the accumulated message parts
     * @param Integer $messageNumber Message number of specified message
     * @param Object $part Object of message part
     * @param Integer $partNumber Part number
     * @param null|resource $mailBox IMAPStream
     * @return object|\Utilities\EmailReaderError
     */
    function addMessageDataToArray($messageNumber, $part, $partNumber, $mailBox = null)
    {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();
        if (isset($messageNumber) && !empty($messageNumber)) {
            if (isset($mailBox) && !empty($mailBox) && is_resource($mailBox)) {

                $data = ($partNumber) ? imap_fetchbody($mailBox, $messageNumber, $partNumber) : imap_body($mailBox, $partNumber);


                $params = [];
                if ($part->parameters) {
                    foreach ($part->parameters as $parameters) {
                        $params[strtolower($parameters->attribute)] = $parameters->value;
                    }
                }
                if ($part->ifdparameters == 1) {
                    foreach ($part->dparameters as $dparameter) {
                        $params[strtolower($dparameter->attribute)] = $dparameter->value;
                    }

                    if ($params["filename"] || $params["name"]) {

                        $filename = ($params["filename"]) ? $params["filename"] : $params["name"];

                        $this->attachments[] = (object)array("encoding" => $part->encoding, "fileName" => $filename, "data" => $data);

                    }
                }

                if ($part->encoding == ENCQUOTEDPRINTABLE) {
                    $data = quoted_printable_decode($data);
                } elseif ($part->encoding == ENCBASE64) {
                    $data = base64_decode($data);
                }

                if ($part->type == 0 && $data) {
                    if (strtolower($part->subtype) == "plain") {
                        $this->plainMessage .= trim($data) . "\n\n";
                    } else {
                        $this->htmlMessage .= $data;
                        $this->charset = $params["charset"];
                    }
                }

                if (isset($part->parts)) {
                    foreach ($part->parts as $partNo0 => $p2) {
                        $this->addMessageDataToArray($messageNumber, $p2, $partNumber . "." . ($partNo0 + 1), $mailBox);
                    }
                }

                return (object)["htmlMessage" => $this->htmlMessage, "plainMessage" => $this->plainMessage, "charset" => $this->charset, "attachments" => $this->attachments];

            } else {
                return new EmailReaderError (EMAIL_ERROR_IMAP_STREAM, EMAIL_ERROR_IMAP_STREAM_MESSAGE, $errors);

            }
        } else {
            return new EmailReaderError (EMAIL_ERROR_MESSAGE_NUMBER, EMAIL_ERROR_MESSAGE_NUMBER_MESSAGE, $errors);
        }
    }

    /**
     * * Dumps the parsed message data's attachments to a directory location
     * @param Object $messageData object{"htmlMessage" => , "plainMessage" => , "charset" => , "attachments" => };
     * @param String $directory Directory destination
     * @return bool|\Utilities\EmailReaderError True on success or error message on failure
     * @example examples/exampleDumpAttachments.php
     */
    function dumpAttachments($messageData, $directory)
    {
        if (isset($messageData) && !empty($messageData) && is_object($messageData)) {
            if (isset($directory) && !empty($directory)) {
                if (isset($messageData->attachments) && !empty($messageData->attachments)) {

                    foreach ($messageData->attachments as $attachment) {
                        $fp = fopen($directory . $attachment->fileName, "w+");

                        if ($attachment->encoding == ENCQUOTEDPRINTABLE) {

                            fwrite($fp, quoted_printable_decode($attachment->data));

                        } elseif ($attachment->encoding == ENCBASE64) {

                            fwrite($fp, base64_decode($attachment->data));

                        }
                        fclose($fp);
                    }
                    return true;
                } else {
                    return new EmailReaderError (EMAIL_ERROR_DUMP_ATTACHMENTS_NOT_EXIST, EMAIL_ERROR_DUMP_ATTACHMENTS_NOT_EXIST_MESSAGE);
                }
            } else {
                return new EmailReaderError (EMAIL_ERROR_DUMP_ATTACHMENTS_DIRECTORY, EMAIL_ERROR_DUMP_ATTACHMENTS_DIRECTORY_MESSAGE);
            }
        } else {
            return new EmailReaderError (EMAIL_ERROR_DUMP_ATTACHMENTS_DATA, EMAIL_ERROR_DUMP_ATTACHMENTS_DATA_MESSAGE);
        }
    }

    /**
     * * Sets the message status by setting message flags
     * @param Integer|String $sequence - contains the message number(s) for the flags to be set on. Example: "2,5" - message numbers 2 to 5
     * @param String $newMessageStatus Message of parsed defined constant: EMAIL_SEEN, EMAIL_FLAGGED, EMAIL_DELETED, EMAIL_DRAFT, EMAIL_ANSWERED
     * @param null|resource $mailBox IMAPStream
     * @return bool|\Utilities\EmailReaderError True on success or error message on failure
     * @example examples/exampleSetMessageStatus.php
     */
    function setMessageStatus($sequence, $newMessageStatus, $mailBox = null)
    {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();
        if (isset($sequence) && !empty($sequence)) {
            if (isset($newMessageStatus) && !empty($newMessageStatus)) {
                if (isset($mailBox) && !empty($mailBox) && is_resource($mailBox)) {
                    $setFlagResult = imap_setflag_full($mailBox, $sequence, $newMessageStatus);

                    if (isset($setFlagResult)) {
                        imap_expunge($mailBox);
                        return true;
                    } else {
                        return new EmailReaderError (EMAIL_ERROR_EDIT_MESSAGE_SET_FLAG, EMAIL_ERROR_EDIT_MESSAGE_SET_FLAG, $errors);
                    }

                } else {
                    return new EmailReaderError (EMAIL_ERROR_IMAP_STREAM, EMAIL_ERROR_IMAP_STREAM_MESSAGE, $errors);
                }
            } else {
                return new EmailReaderError (EMAIL_ERROR_EDIT_MESSAGE_STATUS_NEW_MESSAGE_STATUS, EMAIL_ERROR_EDIT_MESSAGE_STATUS_NEW_MESSAGE_STATUS_MESSAGE, $errors);
            }
        } else {
            return new EmailReaderError (EMAIL_ERROR_EDIT_MESSAGE_STATUS_SEQUENCE, EMAIL_ERROR_EDIT_MESSAGE_STATUS_SEQUENCE_MESSAGE, $errors);
        }
    }

    /**
     * * Sets the message status by clearing message flags
     * @param Integer|String $sequence - contains the message number(s) for the flags to be set on. Example: "2,5" - message numbers 2 to 5
     * @param String $clearedMessageStatus Message of parsed defined constant: EMAIL_SEEN, EMAIL_FLAGGED, EMAIL_DELETED, EMAIL_DRAFT, EMAIL_ANSWERED
     * @param null|resource $mailBox IMAPStream
     * @return bool|\Utilities\EmailReaderError True on success or error message on failure
     * @example examples/exampleClearMessageStatus.php
     */
    function clearMessageStatus($sequence, $clearedMessageStatus, $mailBox = null)
    {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();
        if (isset($sequence) && !empty($sequence)) {
            if (isset($clearedMessageStatus) && !empty($clearedMessageStatus)) {
                if (isset($mailBox) && !empty($mailBox) && is_resource($mailBox)) {
                    $clearFlagResult = imap_clearflag_full($mailBox, $sequence, $clearedMessageStatus);

                    imap_expunge($mailBox);

                    if (isset($clearFlagResult)) {
                        return true;
                    } else {
                        return new EmailReaderError (EMAIL_ERROR_EDIT_MESSAGE_CLEAR_FLAG, EMAIL_ERROR_EDIT_MESSAGE_CLEAR_FLAG_MESSAGE, $errors);
                    }

                } else {
                    return new EmailReaderError (EMAIL_ERROR_IMAP_STREAM, EMAIL_ERROR_IMAP_STREAM_MESSAGE, $errors);
                }
            } else {
                return new EmailReaderError (EMAIL_ERROR_EDIT_MESSAGE_STATUS_NEW_MESSAGE_STATUS, EMAIL_ERROR_EDIT_MESSAGE_STATUS_NEW_MESSAGE_STATUS_MESSAGE, $errors);
            }
        } else {
            return new EmailReaderError (EMAIL_ERROR_EDIT_MESSAGE_STATUS_SEQUENCE, EMAIL_ERROR_EDIT_MESSAGE_STATUS_SEQUENCE_MESSAGE, $errors);
        }
    }

    /**
     * * Moves message(s) to a specified folder
     * @param Integer|String $sequence - contains the message number(s) for the flags to be set on. Example: "2,5" - message numbers 2 to 5
     * @param String $destination Destination mailbox folder name
     * @param null|resource $mailBox IMAPStream
     * @return bool|\Utilities\EmailReaderError True on success or error message on failure
     * @example examples/exampleMessageMove.php
     */
    function messageMove($sequence, $destination, $mailBox = null)
    {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();

        if (isset($sequence) && !empty($sequence)) {
            if (isset($destination) && !empty($destination)) {
                if (isset($mailBox) && !empty($mailBox) && is_resource($mailBox)) {
                    $moveResult = imap_mail_move($mailBox, $sequence, $destination);

                    if (isset($moveResult)) {
                        imap_expunge($mailBox);
                        return true;
                    } else {
                        return new EmailReaderError (EMAIL_ERROR_MESSAGE_MOVE, EMAIL_ERROR_MESSAGE_MOVE_MESSAGE, $errors);
                    }
                } else {
                    return new EmailReaderError (EMAIL_ERROR_IMAP_STREAM, EMAIL_ERROR_IMAP_STREAM_MESSAGE, $errors);
                }
            } else {
                return new EmailReaderError (EMAIL_ERROR_DESTINATION_FOLDER, EMAIL_ERROR_DESTINATION_FOLDER_MESSAGE, $errors);
            }
        } else {
            return new EmailReaderError (EMAIL_ERROR_EDIT_MESSAGE_STATUS_SEQUENCE, EMAIL_ERROR_EDIT_MESSAGE_STATUS_SEQUENCE_MESSAGE, $errors);
        }
    }

    /**
     * * Copies message(s) and pastes it in a specified folder
     * @param Integer|String  $sequence - contains the message number(s) for the flags to be set on. Example: "2,5" - message numbers 2 to 5
     * @param String $destination Destination mailbox folder name
     * @param null|resource $mailBox IMAPStream
     * @return bool|\Utilities\EmailReaderError True on success or error message on failure
     * @example examples/exampleMessageCopy.php
     */
    function messageCopy($sequence, $destination, $mailBox = null)
    {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();

        if (isset($sequence) && !empty($sequence)) {
            if (isset($destination) && !empty($destination)) {
                if (isset($mailBox) && !empty($mailBox) && is_resource($mailBox)) {
                    $copyResult = imap_mail_copy($mailBox, $sequence, $destination);

                    if (isset($copyResult)) {
                        return true;
                    } else {
                        return new EmailReaderError (EMAIL_ERROR_MESSAGE_MOVE, EMAIL_ERROR_MESSAGE_MOVE_MESSAGE, $errors);
                    }
                } else {
                    return new EmailReaderError (EMAIL_ERROR_IMAP_STREAM, EMAIL_ERROR_IMAP_STREAM_MESSAGE, $errors);
                }
            } else {
                return new EmailReaderError (EMAIL_ERROR_DESTINATION_FOLDER, EMAIL_ERROR_DESTINATION_FOLDER_MESSAGE, $errors);
            }
        } else {
            return new EmailReaderError (EMAIL_ERROR_EDIT_MESSAGE_STATUS_SEQUENCE, EMAIL_ERROR_EDIT_MESSAGE_STATUS_SEQUENCE_MESSAGE, $errors);
        }
    }


    /**
     * * Deletes a specific message
     * @param Integer $messageNumber The message number
     * @param null|resource $mailBox IMAPStream
     * @return bool|\Utilities\EmailReaderError True for success or error on failure
     * @example examples/exampleMessageDelete.php
     */
    function messageDelete($messageNumber, $mailBox = null)
    {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();
        if (isset($messageNumber) && !empty($messageNumber)) {
            if (isset($mailBox) && !empty($mailBox) && is_resource($mailBox)) {
                //   $deleteResult = imap_delete($mailBox, $messageNumber);
                $setFlagResult = imap_setflag_full($mailBox, $messageNumber, EMAIL_DELETED);
                if (isset($setFlagResult)) {

                    imap_expunge($mailBox);
                    return true;
                } else {
                    return new EmailReaderError (EMAIL_ERROR_DELETE_MESSAGE, EMAIL_ERROR_DELETE_MESSAGE_MESSAGE, $errors);
                }

            } else {
                return new EmailReaderError (EMAIL_ERROR_IMAP_STREAM, EMAIL_ERROR_IMAP_STREAM_MESSAGE, $errors);
            }
        } else {
            return new EmailReaderError (EMAIL_ERROR_MESSAGE_NUMBER, EMAIL_ERROR_MESSAGE_NUMBER_MESSAGE, $errors);
        }
    }

    /**
     * * Closes the mailbox stream
     * @param null|resource $mailBox IMAPStream
     * @return bool|\Utilities\EmailReaderError True for success or error message on failure
     * @example examples/exampleClose.php
     */
    function close($mailBox = null)
    {
        if (empty($mailBox) && !empty($this->mailBox)) {
            $mailBox = $this->mailBox;
        }
        $errors = $this->handleErrors();
        if (isset($mailBox) && is_resource($mailBox)) {
            $closeResult = imap_close($mailBox, CL_EXPUNGE);

            if ($closeResult) {
                return $closeResult;
            } else {
                return new EmailReaderError(EMAIL_ERROR_IMAP_CLOSE_FAILURE, EMAIL_ERROR_IMAP_CLOSE_FAILURE_MESSAGE, $errors);
            }
        } else {
            return new EmailReaderError(EMAIL_ERROR_IMAP_STREAM, EMAIL_ERROR_IMAP_STREAM_MESSAGE, $errors);
        }
    }
}
