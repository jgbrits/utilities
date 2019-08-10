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
        $this->mailBox = null;
    }


    /**
     * @param $searchSubject
     * @param $mailBox
     */

    function search($searchSubject, $mailBox)
    {
        $this->searchSubject = null;
    }

    /**
     * @param $mailBox
     */
    function getMailBoxHeaders($mailBox)
    {

    }

    /**
     * @param $messageNumber
     * @param $mailBox
     */
    function getMessageHeader($messageNumber, $mailBox)
    {

    }

    /**
     * @param $mailBox
     */
    function close($mailBox)
    {

    }
}