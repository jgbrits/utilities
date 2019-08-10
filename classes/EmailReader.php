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
     *
     */
    function open()
    {
        $this->mailBox = null;
    }


    /**
     *
     */
    function search()
    {

    }

    /**
     *
     */
    function getMailBoxHeaders()
    {

    }

    function getMessageHeader()
    {

    }

    /**
     *
     */
    function close()
    {

    }
}