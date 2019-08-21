<?php


namespace Utilities;

use Utilities\EmailReaderError;

class EmailReader
{
    private $mailBox = null;
    private $host = null;
    //Link to optional flags: https://www.php.net/manual/en/function.imap-open.php
    private $flags = null;
    private $username = null;
    private $password = null;
    private $htmlMessage = null;
    private $plainMessage = null;
    private $charset = null;
    private $attachments = null;
    public $port = null;

    /**
     * EmailMessage reader is used to read emails from an email server..
     * @param $host
     * @param $port
     * @param $username
     * @param $password
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
     * Gets back a mail box handle for all future processing
     * @param string $flags
     * @param null $folderName
     * @return resource|\Utilities\EmailReaderError|null
     */
    function openMailBox($flags = "/imap/ssl", $folderName = null)
    {
        if (!empty($flags)) {
            $this->flags = $flags;
        }
        if (function_exists("imap_open")) {

            $errors = $this->handleErrors();
            $this->mailBox = imap_open("{{$this->host}:{$this->port}{$this->flags}}{$folderName}", $this->username, $this->password);

            if (isset($this->mailBox)) {
                return $this->mailBox;
            } else {

                return new EmailReaderError (EMAIL_ERROR_IMAP_STREAM, EMAIL_ERROR_IMAP_STREAM_MESSAGE, $errors);
            }


        } else {

            return new EmailReaderError (EMAIL_ERROR_IMAP_ERROR, EMAIL_ERROR_IMAP_ERROR_MESSAGE);
        }

    }

    /**
     * Gets a list of mailbox folders
     * @param null $mailBox
     * @return array|\Utilities\EmailReaderError
     */
    function getMailBoxFolders($mailBox = null)
    {
        $errors = $this->handleErrors();
        if (isset($mailBox) && !empty($mailBox)) {

            $folders = imap_list($mailBox, "{{$this->host}}", "*");
            $parsedFolders = [];
            foreach ($folders as $id => $folder) {
                $tempName = explode("}", $folder); //Comes in the form {server}Folder
                $parsedFolders[] = $tempName[1];
            }

            return $parsedFolders;
        } else {
            return new EmailReaderError (EMAIL_ERROR_IMAP_STREAM, EMAIL_ERROR_IMAP_STREAM_MESSAGE, $errors);
        }
    }

    /**
     * Opens a mailbox stream in a specific mailbox folder
     * @param null $folderName
     * @return bool|resource|string|EmailReaderError|null
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
     * Gets the message numbers of all messages that contain the parsed criteria
     * @param $searchCriteria
     * @param null $mailBox
     * @return array|\Utilities\EmailReaderError
     */
    function search($searchCriteria, $mailBox = null)
    {
        $errors = $this->handleErrors();
        if (isset($mailBox) && !empty($mailBox)) {
            $searchResult = imap_search($mailBox, $searchCriteria);

            return $searchResult;
        } else {
            return new EmailReaderError (EMAIL_ERROR_IMAP_STREAM, EMAIL_ERROR_IMAP_STREAM_MESSAGE, $errors);
        }
    }

    /**
     * Uses the message numbers to get and put all the message headers into an array
     * @param $searchResult
     * @param null $mailBox
     * @return array|\Utilities\EmailReaderError
     */
    function getSearchResultHeaders($searchResult, $mailBox = null)
    {
        $errors = $this->handleErrors();
        if (isset($mailBox) && !empty($mailBox)) {
            $searchResultHeaders = array();

            foreach ($searchResult as $messageNumber) {

                $searchResultHeaders[] = imap_header($mailBox, $messageNumber);
            }

            return $searchResultHeaders;

        } else {
            return new EmailReaderError (EMAIL_ERROR_IMAP_STREAM, EMAIL_ERROR_IMAP_STREAM_MESSAGE, $errors);
        }
    }

    /**
     * Gets all the headers in the mailbox folder
     * @param null $mailBox
     * @return array|\Utilities\EmailReaderError
     */
    function getMailBoxHeaders($mailBox = null)
    {
        $errors = $this->handleErrors();
        if (isset($mailBox) && !empty($mailBox)) {

            $mailBoxHeaders = imap_headers($mailBox);

            return $mailBoxHeaders;
        } else {
            return new EmailReaderError (EMAIL_ERROR_IMAP_STREAM, EMAIL_ERROR_IMAP_STREAM_MESSAGE, $errors);
        }
    }

    /**
     * Gets all the message numbers from the parsed search headers
     * @param $searchHeaders
     * @return array|\Utilities\EmailReaderError
     */
    function getMessageNumbersForSearch($searchHeaders)
    {
        if (isset($searchHeaders) && !empty($searchHeaders)) {
            $objectToArray = [];

            foreach ($searchHeaders as $object) {
                $objectToArray[] = get_object_vars($object);
            }

            $messageNumbersFromObject = array_column($objectToArray, "Msgno");

            return $messageNumbersFromObject;

        } else {
            return new EmailReaderError (EMAIL_ERROR_SEARCH_HEADERS, EMAIL_ERROR_SEARCH_HEADERS_MESSAGE);
        }
    }

    /**
     * Gets the headers of a specific message
     * @param $messageNumber
     * @param null $mailBox
     * @return object
     */
    function getMessageHeader($messageNumber, $mailBox = null)
    {
        $errors = $this->handleErrors();
        if (isset($messageNumber) && !empty($messageNumber)) {
            if (isset($mailBox) && !empty($mailBox)) {

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
     * Returns the ending result data array containing all the message's data
     * @param $messageNumber
     * @param null $mailBox
     * @return object
     */
    function getMessageData($messageNumber, $mailBox = null)
    {
        $errors = $this->handleErrors();
        if (isset($messageNumber) && !empty($messageNumber)) {
            if (isset($mailBox) && !empty($mailBox)) {
                $emailMessage = (object)[];

                $structure = imap_fetchstructure($mailBox, $messageNumber);

                if (!$structure->parts) {
                    $emailMessage = $this->addMessageDataToArray($messageNumber, $structure, 0, $mailBox);
                } else {
                    foreach ($structure->parts as $partNumber0 => $p) {
                        $emailMessage = $this->addMessageDataToArray($messageNumber, $p, $partNumber0 + 1, $mailBox);
                    }
                }

                return $emailMessage;

            } else {
                return new EmailReaderError (EMAIL_ERROR_IMAP_STREAM, EMAIL_ERROR_IMAP_STREAM_MESSAGE, $errors);

            }
        } else {
            return new EmailReaderError (EMAIL_ERROR_MESSAGE_NUMBER, EMAIL_ERROR_MESSAGE_NUMBER_MESSAGE, $errors);
        }
    }

    /**
     * Returns the array containing the accumulated message parts
     * @param $messageNumber
     * @param $part
     * @param $partNumber
     * @param null $mailBox
     * @return object|\Utilities\EmailReaderError
     */
    function addMessageDataToArray($messageNumber, $part, $partNumber, $mailBox = null)
    {

        $errors = $this->handleErrors();
        if (isset($messageNumber) && !empty($messageNumber)) {
            if (isset($mailBox) && !empty($mailBox)) {

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
     * Dumps the parsed message data's attachments to a directory location
     * @param $messageData
     * @param $directory
     * @return bool|\Utilities\EmailReaderError
     */
    function dumpAttachments($messageData, $directory)
    {
        if (isset($messageData) && !empty($messageData)) {
            if (isset($directory) && !empty($directory)) {
                if (isset($messageData->attachments) && !isEmpty($messageData->attachments)) {

                    foreach ($messageData->attachments as $attachment) {
                        $fp = fopen($directory . $attachment->fileName, "w+");

                        if ($messageData->encoding == ENCQUOTEDPRINTABLE) {

                            fwrite($fp, quoted_printable_decode($attachment->data));

                        } elseif ($messageData->encoding == ENCBASE64) {

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
     * Sets the message status by setting and clearing message flags
     * @param $sequence - contains the message number(s) for the flags to be set on. Example: "2,5" - message numbers 2 to 5
     * @param $newMessageStatus
     * @param null $mailBox
     * @return bool|\Utilities\EmailReaderError
     */
    function setMessageStatus($sequence, $newMessageStatus, $mailBox = null)
    {
        $errors = $this->handleErrors();
        if (isset($sequence) && !isEmpty($sequence)) {
            if (isset($newMessageStatus) && !isEmpty($newMessageStatus)) {
                if (isset($mailBox) && !empty($mailBox)) {


                    return true;
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

    /*
     * Sets the message status by setting and clearing message flags
     * @param $sequence - contains the message number(s) for the flags to be set on. Example: "2,5" - message numbers 2 to 5
     * @param null $setFlags
     * @param null $clearFlags
     * @param null $mailBox
     * @return bool|EmailReaderError
     * @todo finish this
     *
    function setMessageStatus($sequence, $setFlags = null, $clearFlags = null, $mailBox = null)
    {
        $errors = $this->handleErrors();
        if (isset($mailBox)) {
            if (isset($setFlags) && isset($clearFlags) && !isEmpty($setFlags) && !isEmpty($clearFlags)) {

                $editResult = imap_setflag_full($mailBox, $sequence, $setFlags);
                $editResult2 = imap_clearflag_full($mailBox, $sequence, $clearFlags);

                if ($editResult && $editResult2) {
                    return true;
                } else {
                    if ($editResult) {
                        imap_clearflag_full($mailBox, $sequence, $setFlags);
                    } elseif ($editResult2) {
                        imap_setflag_full($mailBox, $sequence, $clearFlags);
                    }
                    return new EmailReaderError(EMAIL_ERROR_EDIT_MESSAGE_FLAGS, EMAIL_ERROR_EDIT_MESSAGE_FLAGS_MESSAGE, $errors);
                }

            } elseif (isset($setFlags) && !isEmpty($setFlags) && is_null($clearFlags)) {
                $editResult = imap_setflag_full($mailBox, $sequence, $setFlags);

                if ($editResult) {
                    return true;
                } else {
                    return new EmailReaderError(EMAIL_ERROR_EDIT_MESSAGE_FLAGS, EMAIL_ERROR_EDIT_MESSAGE_FLAGS_MESSAGE, $errors);
                }

            } elseif (isset($clearFlags) && !isEmpty($clearFlags) && is_null($setFlags)) {
                $editResult = imap_clearflag_full($mailBox, $sequence, $clearFlags);

                if ($editResult) {
                    return true;
                } else {
                    return new EmailReaderError(EMAIL_ERROR_EDIT_MESSAGE_FLAGS, EMAIL_ERROR_EDIT_MESSAGE_FLAGS_MESSAGE, $errors);
                }

            } else {
                return new EmailReaderError(EMAIL_ERROR_EDIT_MESSAGE_FLAGS, EMAIL_ERROR_EDIT_MESSAGE_FLAGS_MESSAGE, $errors);
            }
        } else {
            return new EmailReaderError(EMAIL_ERROR_IMAP_STREAM, EMAIL_ERROR_IMAP_STREAM_MESSAGE, $errors);
        }

    }
*/
    /**
     * Closes the mailbox stream
     * @param null $mailBox
     * @return bool|\Utilities\EmailReaderError
     */
    function close($mailBox = null)
    {
        $errors = $this->handleErrors();
        if (isset($mailBox)) {
            $closeResult = imap_close($mailBox);

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
