<?php


namespace Utilities;


class EmailReader
{
    private $mailBox = null;
    private $host = null;
    private $flags = null;
    private $username = null;
    private $password = null;
    private $port = null;
    private $folder = null;


    /**
     * Email reader is used to read emails from an email server..
     * @param $host
     * @param $flags
     * @param $username
     * @param $password
     * @param int $port
     */
    function __construct($host, $port, $flags, $username, $password)
    {
        $this->host = $host;
        $this->flags = $flags;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
    }

    /**
     * @return resource|null
     */
    function openMailBox()
    {
        $this->mailBox = imap_open("{{$this->host}:{$this->port}{$this->flags}", $this->username, $this->password);

        return $this->mailBox;
    }

    /**
     * @param $folder
     */
    function openMailBoxFolder($folder)
    {
        $this->folder = null;
    }


    /**
     * @param $searchSubject
     * @param null $mailBox
     */
    function search($searchSubject, $mailBox = null)
    {
        $this->searchSubject = null;
    }

    /**
     * @param null $mailBox
     */
    function getMailBoxHeaders($mailBox = null)
    {
    }

    /**
     * @param $messageNumber
     * @param null $mailBox
     */
    function getMessageHeader($messageNumber, $mailBox = null)
    {
        $this->messageNumber = null;
    }

    /**
     * @param $mailBox
     */
    function close($mailBox = null)
    {
    }
}