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
    private $folderName = null;


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
     * @param null $folderName
     * @return resource|null
     */
    function openMailBox($folderName = null)
    {
        $this->mailBox = imap_open("{{$this->host}:{$this->port}{$this->flags}}{$folderName}", $this->username, $this->password);

        return $this->mailBox;
    }

    /**
     * @param null $folderName
     * @return resource|null
     */
    function openMailBoxFolder($folderName = null)
    {
        $mailBoxFolder = $this->openMailBox($folderName);

        return $mailBoxFolder;
    }

    /**
     * @param $searchSubject
     * @param null $mailBox
     * @todo still busy
     */
    function search($searchSubject, $mailBox = null)
    {
        $this->searchSubject = null;
    }

    /**
     * @param null $mailBox
     * @return array
     */
    function getMailBoxHeaders($mailBox = null)
    {
        $mailBoxHeaders = imap_headers($mailBox);

        return $mailBoxHeaders;
    }

    /**
     * @param $messageNumber
     * @param null $mailBox
     * @todo still busy
     */
    function getMessageHeader($messageNumber, $mailBox = null)
    {
        $this->messageNumber = null;
    }

    /**
     * @param null $mailBox
     * @todo still busy
     */
    function close($mailBox = null)
    {
    }
}