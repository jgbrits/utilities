<?php

class TestEmailReaderTest extends \Codeception\Test\Unit
{
    //@todo Fix redundancy with $emailReader instantiation

    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        require_once "./classes/EmailReader.php";
    }

    protected function _after()
    {
    }

    // tests
    public function testCreate()
    {
        $emailReader = new \Utilities\EmailReader("gmail.com", "testemailclass654321@gmail.com", "test!23456789");

        return $emailReader;
    }

    public function testOpen()
    {

        $emailReader = $this->testCreate();

        $mailBox = $emailReader->open("INBOX");

        return $mailBox;
    }


    public function testSearch()
    {
        $emailReader = $this->testCreate();
        $mailBox = $this->testOpen();

        $searchSubject = "Article";

        $searchData = $emailReader->search($mailBox, "SUBJECT \"{$searchSubject} \"");

        return $searchData;
    }

    public function testGetMailBoxHeaders()
    {
        $emailReader = $this->testCreate();
        $mailBox = $this->testOpen();

        $mailBoxHeaders = $emailReader->getMailBoxHeaders($mailBox);

        return $mailBoxHeaders;
    }

    public function testGetMessageHeader()
    {
        $mailReader = $this->testCreate();
        $mailBox = $this->testOpen();
        $messageNumber = $this->testGetMailBoxHeaders();

        $messageHeader = $mailReader->getMessageHeader($messageNumber, $mailBox=null);

        return $messageHeader;
    }



    public function testClose()
    {

        $emailReader = $this->testCreate();

        $emailReader->close();

    }


}