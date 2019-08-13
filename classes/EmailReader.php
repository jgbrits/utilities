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
    function __construct($host, $port, $username, $password)
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
     * @return resource|null
     */
    function openMailBoxFolder($folderName = null)
    {
        $mailBoxFolder = $this->openMailBox($folderName);

        return $mailBoxFolder;
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
     * Gets the headers of a specific message
     * @param $messageNumber
     * @param null $mailBox
     * @todo still busy
     */
    function getMessageHeader($messageNumber, $mailBox = null)
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