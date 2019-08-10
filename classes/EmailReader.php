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
     * @param $mailBox
     */

    function search($searchSubject, $mailBox)
    {
        $this->searchSubject = null;
        $this->mailBox = null;
    }

    /**
     * @param $mailBox
     */
    function getMailBoxHeaders($mailBox)
    {
        $this->mailBox = null;
    }

    /**
     * @param $messageNumber
     * @param $mailBox
     */
    function getMessageHeader($messageNumber, $mailBox)
    {
        $this->messageNumber = null;
        $this->mailBox = null;
    }

    /**
     * @param $mailBox
     */
    function close($mailBox)
    {
        $this->mailBox = null;
    }
}