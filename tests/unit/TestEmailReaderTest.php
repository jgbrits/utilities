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
        $mailBox = $this->testCreate()->open("INBOX");

        return $mailBox;
    }


    public function testSearch()
    {
        $searchSubject = "Article";

        $searchData = $this->testCreate()->search("SUBJECT \"{$searchSubject} \"", $this->testOpen());

        return $searchData;
    }

    public function testGetMailBoxHeaders()
    {
        $mailBoxHeaders = $this->testCreate()->getMailBoxHeaders($this->testOpen());

        return $mailBoxHeaders;
    }

    public function testGetMessageHeader()
    {
        $messageHeader = $this->testCreate()->getMessageHeader($this->testGetMailBoxHeaders(), $this->testOpen());

        return $messageHeader;
    }


    public function testClose()
    {
        $emailReader = $this->testCreate();

        $emailReader->close($this->testOpen());

    }


}