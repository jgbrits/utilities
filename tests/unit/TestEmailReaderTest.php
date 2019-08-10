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

        $emailData = $emailReader->search($mailBox, "SUBJECT \"{$searchSubject} \"");

        return $emailData;
    }

    public function testHeaders()
    {
        $emailReader = $this->testCreate();
        $emailData = $this->testSearch();

        $headers = $emailReader-> headers($emailData);

        return $headers;
    }

    public function testClose()
    {

        $emailReader = $this->testCreate();

        $emailReader->close();

    }


}