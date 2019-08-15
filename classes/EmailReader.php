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
     * Email reader is used to read emails from an email server..
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
     * Opens a mailbox stream
     * @param null $folderName
     * @return resource|null
     */
    function openMailBox($folderName = null)
    {
        $this->mailBox = imap_open("{{$this->host}:{$this->port}{$this->flags}}{$folderName}", $this->username, $this->password);

        return $this->mailBox;
    }

    /**
     * Gets a list of mailbox folders
     * @param null $mailBox
     */
    function getMailBoxFolders($mailBox = null)
    {
        $folders = imap_listmailbox($mailBox, "{{$this->host}}", "*");

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
     * @return array
     */
    function getMessageData($messageNumber, $mailBox = null)
    {
        $messageDataArray = [];

        $structure = imap_fetchstructure($mailBox, $messageNumber);

        if (!$structure->parts) {
            $messageDataArray = $this->addMessageDataToArray($messageNumber, $structure, 0, $mailBox);
        } else {
            foreach ($structure->parts as $partNumber0 => $p) {
                $messageDataArray = $this->addMessageDataToArray($messageNumber, $p, $partNumber0 + 1, $mailBox);
            }
        }

        return $messageDataArray;
    }

    /**
     * Returns the array containing the accumulated message parts
     * @param $messageNumber
     * @param $part
     * @param $partNumber
     * @param null $mailBox
     * @return array
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
                $htmlMsg .= $data . "<br><br>";
                $charset = $params['charset'];
            }
        }

        if (isset($part->parts)) {
            foreach ($part->parts as $partNo0 => $p2) {
                $this->addMessageDataToArray($messageNumber, $p2, $partNumber . "." . ($partNo0 + 1), $mailBox);
            }
        }

        $parts = array("HTML message" => $htmlMsg, "Plain Message" => $plainMsg, "Charset" => $charset, "Attachments" => $attachments);

        return $parts;
    }

    /**
     * @param $sequence
     * $sequence contains the message number(s) for the flags to be set on. Example: "2,5" - message numbers 2 to 5
     * @param null $setFlags
     * @param null $clearFlags
     * @param null $mailBox
     * @return bool|int
     */
    function editFlags($sequence, $setFlags = null, $clearFlags = null, $mailBox = null)
    {
        if (isset($mailBox)) {
            if (isset($setFlags) && isset($clearFlags)) {

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
                    return print "Failed to edit flags";
                }

            } elseif (isset($setFlags) && is_null($clearFlags)) {

                $editResult = imap_setflag_full($mailBox, $sequence, $setFlags);

                if ($editResult) {
                    return true;
                } else {
                    return print "Failed to set flags";
                }

            } elseif (isset($clearFlags) && is_null($setFlags)) {

                $editResult = imap_clearflag_full($mailBox, $sequence, $clearFlags);

                if ($editResult) {
                    return true;
                } else {
                    return print "Failed to clear flags";
                }

            } else {
                return false;
            }
        } else {
            return print "Invalid imap stream";
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
            return print "There was no imap stream to close";
        }
    }
}