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
        $this->emailReader = new \Utilities\EmailReader("imap.gmail.com", 993, "testemailclass654321@gmail.com", "test!23456789");
    }

    protected function _after()
    {
    }

    public function testOpenMailBox()
    {
        $mailBox = $this->emailReader->openMailBox();
        $this->assertNotTrue($mailBox, "Returned FALSE, which means there is no imap stream");
        return $mailBox;
    }

    public function testOpenMailBoxFolder()
    {
        $folderName = "INBOX";
        $mailBoxFolder = $this->emailReader->openMailBoxFolder($folderName);
        $this->assertNotEmpty($mailBoxFolder, "Inbox is empty");

        return $mailBoxFolder;
    }

    //@todo - still busy
    public function testSearch()
    {
        $searchSubject = "Article";

        $searchData = $this->emailReader->search("SUBJECT \"{$searchSubject} \"", $this->testOpenMailBoxFolder());

        return $searchData;
    }


    public function testGetMailBoxHeaders()
    {
        $mailBoxHeaders = $this->emailReader->getMailBoxHeaders($this->testOpenMailBoxFolder());

        $this->assertNotEmpty($mailBoxHeaders, "There are no headers in this array");

        return $mailBoxHeaders;
    }

    //@todo - still busy
    public function testGetMessageHeader()
    {
        $messageHeader = $this->emailReader->getMessageHeader($this->testGetMailBoxHeaders(), $this->testOpenMailBoxFolder());

        return $messageHeader;
    }


    //@todo - still busy
    public function testClose()
    {
        $this->emailReader->close($this->testOpenMailBox());

    }


}