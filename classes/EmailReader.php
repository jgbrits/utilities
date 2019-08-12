<?php


namespace Utilities;


class EmailReader
{
    private $mailBox = null;


    /**
     * Email reader is used to read emails from an email server.
     * @param $host
     * @param $username
     * @param $password
     * @param int $port
     */
    function __construct($host, $username, $password, $port = 993)
    {
    }

    /**
     * @param $folder
     */
    function open($folder)
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