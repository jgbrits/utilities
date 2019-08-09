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
        $emailReader = new \Utilities\EmailReader("gmail.com", "usernamer123", "password123");

        return $emailReader;
    }

    public function testOpen()
    {

        $emailReader = $this->testCreate();

        $mailBox = $emailReader->open("INBOX");

        return $mailBox;
    }


    public function testClose()
    {

        $emailReader = $this->testCreate();

        $emailReader->close();


    }

    public function testSearch()
    {
        $emailReader = $this->testCreate();
        $mailBox = $this->testOpen();

        $searchSubject = "Article";

        $emailReader->search($mailBox, "SUBJECT \"{$searchSubject} \"");
    }

    public function testOpenEmail()
    {
        $mbox = imap_open("{imap.gmail.com:993/imap/ssl}", "creeperama01@gmail.com", "Toffee01");

        codecept_debug("<h1>Mailboxes</h1>\n");
        $folders = imap_listmailbox($mbox, "{imap.gmail.com:993/imap/ssl}", "*");

        if ($folders == false) {
            codecept_debug("Call failed<br />\n");
        } else {
            foreach ($folders as $val) {
                codecept_debug($val . "<br />\n");
            }
        }

        codecept_debug("<h1>Headers in INBOX</h1>\n");
        $headers = imap_headers($mbox);

        if ($headers == false) {
            codecept_debug("Call failed<br />\n");
        } else {
            foreach ($headers as $val) {
                codecept_debug($val . "<br />\n");
            }
        }

        imap_close($mbox);
    }

}