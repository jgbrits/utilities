<?php

class TestEmailReaderTest extends \Codeception\Test\Unit
{
    //@todo Fix redundancy with $emailReader instantiation

    /**
     * @var \UnitTester
     */
    protected $tester;
    protected $emailReader;


    protected function _before()
    {
        require_once "./classes/EmailReader.php";
        $this->emailReader = new \Utilities\EmailReader("gmail.com", "testemailclass654321@gmail.com", "test!23456789");
    }

    protected function _after()
    {
    }

    // tests
    /*
    public function testCreate()
    {
        $emailReader = new \Utilities\EmailReader("gmail.com", "testemailclass654321@gmail.com", "test!23456789");

        return $emailReader;
    }
*/
    public function testOpen()
    {
        $folder = "INBOX";
        $mailBox = $this->emailReader->open("$folder");

        return $mailBox;
    }


    public function testSearch()
    {
        $searchSubject = "Article";

        $searchData = $this->emailReader->search("SUBJECT \"{$searchSubject} \"", $this->testOpen());

        return $searchData;
    }

    public function testGetMailBoxHeaders()
    {
        $mailBoxHeaders = $this->emailReader->getMailBoxHeaders($this->testOpen());

        return $mailBoxHeaders;
    }

    public function testGetMessageHeader()
    {
        $messageHeader = $this->emailReader->getMessageHeader($this->testGetMailBoxHeaders(), $this->testOpen());

        return $messageHeader;
    }


    public function testClose()
    {
        $this->emailReader->close($this->testOpen());

    }


}