<?php


namespace Utilities;


class EmailReader
{
    private $mailBox = null;
    private $host = null;
    //Link to optional flags: https://www.php.net/manual/en/function.imap-open.php
    private $flags = "/imap/ssl";
    private $username = null;
    private $password = null;
    private $port = null;

    /**
     * @var EmailReaderError
     */

    //
    private $error1;
    private $error2;
    private $error3;
    private $error4;
    private $error5;

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

        $this->error1 = new EmailReaderError();
        $this->error1->code = 1;
        $this->error1->message = "Failed to open mailbox";

        $this->error2 = new EmailReaderError();
        $this->error2->code = 2;
        $this->error2->message = "Failed to set and clear flags";

        $this->error3 = new EmailReaderError();
        $this->error3->code = 3;
        $this->error3->message = "Failed to set flags";

        $this->error4 = new EmailReaderError();
        $this->error4->code = 4;
        $this->error4->message = "Failed to clear flags";

        $this->error5 = new EmailReaderError();
        $this->error5->code = 5;
        $this->error5->message = "Only NULL values have been parsed";
    }

    /**
     * @param null $folderName
     * @return bool|resource|string|null
     */
    function openMailBox($folderName = null)
    {
            $this->mailBox = imap_open("{{$this->host}:{$this->port}{$this->flags}}{$folderName}", $this->username, $this->password);

            if (isset($this->mailBox)) {

                return $this->mailBox;
            } else {

                return $this->error1->getError();
            }
    }

    /**
     * Gets a list of mailbox folders
     * @param null $mailBox
     */
    function getMailBoxFolders($mailBox = null)
    {
        $folders = \imap_listmailbox($mailBox, "{{$this->host}}", "*");

        return $folders;
    }

    /**
     * Opens a mailbox stream in a specific mailbox folder
     * @param null $folderName
     * @return bool|resource|null
     */
    function openMailBoxFolder($folderName = null)
    {
        if (isset($folderName) && !empty($folderName)) {
            $mailBoxFolder = $this->openMailBox($folderName);

            return $mailBoxFolder;
        } else {
            return false;
        }
    }

    /**
     * Gets the message numbers of all messages that contain the parsed criteria
     * @param $searchCriteria
     * @param null $mailBox
     * @return array
     */
    function search($searchCriteria, $mailBox = null)
    {
        $searchResult = imap_search($mailBox, $searchCriteria);

        return $searchResult;
    }

    /**
     * Uses the message numbers to get and put all the message headers into an array
     * @param $searchResult
     * @param null $mailBox
     * @return array
     */
    function getSearchResultHeaders($searchResult, $mailBox = null)
    {
        $searchResultHeaders = array();

        foreach ($searchResult as $messageNumber) {
            $searchResultHeaders[] = imap_header($mailBox, $messageNumber);
        }

        return $searchResultHeaders;
    }

    /**
     * Gets all the headers in the mailbox folder
     * @param null $mailBox
     * @return array
     */
    function getMailBoxHeaders($mailBox = null)
    {
        $mailBoxHeaders = imap_headers($mailBox);

        return $mailBoxHeaders;
    }

    /**
     * Gets all the message numbers from the parsed search headers
     * @param $searchHeaders
     * @return array
     */
    function getMessageNumbersForSearch($searchHeaders)
    {
        $objectToArray = [];

        foreach ($searchHeaders as $object) {
            $objectToArray[] = get_object_vars($object);
        }

        $messageNumbersFromObject = array_column($objectToArray, "Msgno");

        return $messageNumbersFromObject;
    }

    /**
     * Gets the headers of a specific message
     * @param $messageNumber
     * @param null $mailBox
     * @return object
     */
    function getMessageHeader($messageNumber, $mailBox = null)
    {
        $messageHeader = imap_header($mailBox, $messageNumber);

        return $messageHeader;
    }

    /**
     * Returns the ending result data array containing all the message's data
     * @param $messageNumber
     * @param null $mailBox
     * @return object
     */
    function getMessageData($messageNumber, $mailBox = null)
    {
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
    }

    /**
     * Returns the array containing the accumulated message parts
     * @param $messageNumber
     * @param $part
     * @param $partNumber
     * @param null $mailBox
     * @return object
     */
    function addMessageDataToArray($messageNumber, $part, $partNumber, $mailBox = null)
    {
        global $htmlMsg, $plainMsg, $charset, $attachments;

        $data = ($partNumber) ? imap_fetchbody($mailBox, $messageNumber, $partNumber) : imap_body($mailBox, $partNumber);

        if ($part->encoding == 4) {
            $data = quoted_printable_decode($data);
        } elseif ($part->encoding == 3) {
            $data = base64_decode($data);
        }

        $params = [];
        if ($part->parameters) {
            foreach ($part->parameters as $x) {
                $params[strtolower($x->attribute)] = $x->value;
            }
        }
        if ($part->ifdparameters == 1) {
            foreach ($part->dparameters as $x) {
                $params[strtolower($x->attribute)] = $x->value;
            }

            if ($params['filename'] || $params['name']) {

                $filename = ($params['filename']) ? $params['filename'] : $params['name'];

                //@todo doesn't store data for PNG attachments - $attachments[] = array($filename => $data);
                //Temporarily stores only the file names with no data
                $attachments[] = $filename;

            }
        }


        if ($part->type == 0 && $data) {
            if (strtolower($part->subtype) == 'plain') {
                $plainMsg .= trim($data) . "\n\n";
            } else {
                $htmlMsg .= $data;
                $charset = $params['charset'];
            }
        }

        if (isset($part->parts)) {
            foreach ($part->parts as $partNo0 => $p2) {
                $this->addMessageDataToArray($messageNumber, $p2, $partNumber . "." . ($partNo0 + 1), $mailBox);
            }
        }

        $messageParts = (object)["htmlMessage" => $htmlMsg, "plainMessage" => $plainMsg, "charset" => $charset, "attachments" => $attachments];

        return $messageParts;
    }

    /**
     * $sequence contains the message number(s) for the flags to be set on. Example: "2,5" - message numbers 2 to 5
     * @param $sequence
     * @param null $setFlags
     * @param null $clearFlags
     * @param null $mailBox
     * @return bool|EmailReaderError
     */
    function editFlags($sequence, $setFlags = null, $clearFlags = null, $mailBox = null)
    {
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
                    return $this->error2->getError();
                }

            } elseif (isset($setFlags) && !isEmpty($setFlags) && is_null($clearFlags)) {

                $editResult = imap_setflag_full($mailBox, $sequence, $setFlags);

                if ($editResult) {
                    return true;
                } else {
                    return $this->error3->getError();
                }

            } elseif (isset($clearFlags) && !isEmpty($clearFlags) && is_null($setFlags)) {

                $editResult = imap_clearflag_full($mailBox, $sequence, $clearFlags);

                if ($editResult) {
                    return true;
                } else {
                    return $this->error4->getError();
                }

            } else {
                return $this->error5->getError();
            }
        } else {
            return $this->error1->getError();
        }

    }

    /**
     * Closes the mailbox stream
     * @param null $mailBox
     * @return bool
     */
    function close($mailBox = null)
    {
        $closeResult = imap_close($mailBox);

        if ($closeResult) {
            return $closeResult;
        } else {
            return false;
        }
    }
}

/**
 * Class EmailReaderError
 * @package Utilities
 */
class EmailReaderError
{
    public $code = false;
    public $message = false;

    function getError()
    {
        if ($this->code !== false && $this->message !== false) {
            return "Error {$this->code}: {$this->message}";
        }

        return false;
    }
}
