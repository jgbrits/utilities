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
        $this->emailReader = new \Utilities\EmailReader("imap.gmail.com", "/imap/ssl}", "testemailclass654321@gmail.com", "test!23456789");
    }

    protected function _after()
    {
    }

    public function testOpenMailBox()
    {
        $this->assertNotTrue($this->emailReader->openMailBox(), "Returned FALSE, which means there is no imap stream");
    }

    public function testOpenMailBoxFolder()
    {
        $folder = "INBOX";
        $mailBox = $this->emailReader->openMailBoxFolder("$folder");

        return $mailBox;
    }


    public function testSearch()
    {
        $searchSubject = "Article";

        $searchData = $this->emailReader->search("SUBJECT \"{$searchSubject} \"", $this->testOpenMailBoxFolder());

        return $searchData;
    }

    public function testGetMailBoxHeaders()
    {
        $mailBoxHeaders = $this->emailReader->getMailBoxHeaders($this->testOpenMailBoxFolder());

        return $mailBoxHeaders;
    }

    public function testGetMessageHeader()
    {
        $messageHeader = $this->emailReader->getMessageHeader($this->testGetMailBoxHeaders(), $this->testOpenMailBoxFolder());

        return $messageHeader;
    }


    public function testClose()
    {
        $this->emailReader->close($this->testOpenMailBox());

    }


}