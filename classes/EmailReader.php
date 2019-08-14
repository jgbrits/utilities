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

    /*
     * @param $messageNumber
     * @param $part
     * @param $partNumber
     * @param null $mailBox
     * @todo ignore this

        function getMessagePartTest($messageNumber, $part, $partNumber, $mailBox = null)
        {
            global $htmlMsg, $plainMsg, $charset, $attachments;

            $data = ($partNumber) ? imap_fetchbody($mailBox, $messageNumber, $partNumber) : imap_body($mailBox, $partNumber);

            if ($part->encoding == 4) {
                $data = quoted_printable_decode($data);
            } elseif ($part - encoding == 3) {
                $data = base64_decode($data);
            }

            $params = [];
            if ($part->parameters) {
                foreach ($part->parameters as $x) {
                    $params[strtolower($x->attribute)] = $x->value;
                }
            }
            if ($part->dparameters) {
                foreach ($part->dparameters as $x) {
                    $params[strtolower($x->attribute)] = $x->value;
                }
            }

            if ($params['filename'] || $params['name']) {
                $filename = ($params['filename']) ? $params['filename'] : $params['name'];

                $attachments[$filename] = $data;
            }

            if ($part->type == 0 && $data) {
                if (strtolower($part->subtype) == 'plain') {
                    $plainMsg .= trim($data) . "\n\n";
                } else {
                    $htmlMsg .= $data . "<br><br>";
                    $charset = $params['charset'];
                }
            }

            if ($part->parts) {
                foreach ($part->parts as $partNo0 => $p2) {
                    $this->getMessagePart($messageNumber, $p2, $partNumber . "." . ($partNo0 + 1), $mailBox);
                }
            }

        }


     * @param $messageNumber
     * @param null $mailBox
     * @todo ignore this

    function getMessageTest($messageNumber, $mailBox = null)
    {
        global $charset, $htmlMsg, $plainMsg, $attachments;
        $htmlMsg = $plainMsg = $charset = '';

        $header = $this->getMessageHeader($messageNumber, $mailBox);

        $structure = imap_fetchstructure($mailBox, $messageNumber);

        if (!$structure->parts) {
            $this->getMessagePart($messageNumber, $structure, 0, $mailBox);
        } else {
            foreach ($structure->parts as $partNumber0 => $p) {
                $this->getMessagePart($messageNumber, $p, $partNumber0 + 1, $mailBox);
            }
        }
    }
*/

    /*
         *@todo ignore this - another test method

            function create_part_array($structure, $prefix = "")
            {
                //print_r($structure);
                if (sizeof($structure->parts) > 0) {    // There some sub parts
                    foreach ($structure->parts as $count => $part) {
                        add_part_to_array($part, $prefix . ($count + 1), $part_array);
                    }
                } else {    // Email does not have a seperate mime attachment for text
                    $part_array[] = array('part_number' => $prefix . '1', 'part_object' => $obj);
                }
                return $part_array;
            }

        // Sub function for create_part_array(). Only called by create_part_array() and itself.
            function add_part_to_array($obj, $partno, & $part_array)
            {
                $part_array[] = array('part_number' => $partno, 'part_object' => $obj);
                if ($obj->type == 2) { // Check to see if the part is an attached email message, as in the RFC-822 type
                    //print_r($obj);
                    if (sizeof($obj->parts) > 0) {    // Check to see if the email has parts
                        foreach ($obj->parts as $count => $part) {
                            // Iterate here again to compensate for the broken way that imap_fetchbody() handles attachments
                            if (sizeof($part->parts) > 0) {
                                foreach ($part->parts as $count2 => $part2) {
                                    add_part_to_array($part2, $partno . "." . ($count2 + 1), $part_array);
                                }
                            } else {    // Attached email does not have a seperate mime attachment for text
                                $part_array[] = array('part_number' => $partno . '.' . ($count + 1), 'part_object' => $obj);
                            }
                        }
                    } else {    // Not sure if this is possible
                        $part_array[] = array('part_number' => $prefix . '.1', 'part_object' => $obj);
                    }
                } else {    // If there are more sub-parts, expand them out.
                    if (sizeof($obj->parts) > 0) {
                        foreach ($obj->parts as $count => $p) {
                            add_part_to_array($p, $partno . "." . ($count + 1), $part_array);
                        }
                    }
                }
            }

        */
    function getMessageData($messageNumber, $mailBox = null)
    {
        $messageDataArray = [];

        $structure = imap_fetchstructure($mailBox, $messageNumber);

        if (sizeof($structure->parts) > 0) {
            $messageDataArray[] = $this->addMessageDataToArray();
        } else {
            $messageDataArray[] = $this->addMessageDataToArray();
        }

        return $messageDataArray;
    }

    function addMessageDataToArray()
    {
        $data = null;

        return $data;
    }

    function editFlags()
    {

    }

    /**
     * Closes the mailbox stream
     * @param null $mailBox
     * @return bool
     */
    function close($mailBox = null)
    {
        $closeResult = imap_close($mailBox);

        return $closeResult;
    }
}