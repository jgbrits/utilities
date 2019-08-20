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
        $this->emailReader = new \Utilities\EmailReader("imap.gmail.com", "testemailclass654321@gmail.com", "test!23456789");
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

    public function testGetMailBoxFolders()
    {
        $mailBoxArray = $this->emailReader->getMailBoxFolders($this->testOpenMailBox());

        $this->assertIsArray($mailBoxArray, "Return was not an array of mailbox folders");
        $this->assertNotEmpty($mailBoxArray, "Return has no mailbox folders");

        return $mailBoxArray;
    }

    public function testOpenMailBoxFolder()
    {
        $folderName = "INBOX";
        $mailBox = $this->emailReader->openMailBox();
        $mailBoxFolder = $this->emailReader->openMailBoxFolder($folderName);

        $this->assertNotEmpty($mailBoxFolder, "Inbox is empty");
        $this->assertNotFalse($mailBoxFolder, "No mailbox folder parsed");

        return $mailBoxFolder;
    }

    public function testSearch()
    {
        //List of criteria: https://www.php.net/manual/en/function.imap-search.php
        $searchCriteria = "SUBJECT \"test\"";

        $searchResult = $this->emailReader->search($searchCriteria, $this->testOpenMailBoxFolder());

        $searchArrayCount = count($searchResult);

        $this->assertNotFalse($searchResult, "Returned FALSE, which means either incorrect criteria or no messages have been found");
        $this->assertNotEmpty($searchResult, "Method failed");
        $this->assertEquals(2, $searchArrayCount, "Something went wrong with the method");

        return $searchResult;
    }

    public function testGetSearchResultHeaders()
    {

        $messageHeaders = $this->emailReader->getSearchResultHeaders($this->testSearch(), $this->testOpenMailBoxFolder());

        $messageHeadersArrayCount = count($messageHeaders);

        $this->assertIsArray($messageHeaders, "Return was not an array");
        $this->assertNotEmpty($messageHeaders, "Array is empty");
        $this->assertEquals(2, $messageHeadersArrayCount, "Something went wrong in the method");

        return $messageHeaders;
    }


    public function testGetMailBoxHeaders()
    {
        $mailBoxHeaders = $this->emailReader->getMailBoxHeaders($this->testOpenMailBoxFolder());

        $this->assertIsArray($mailBoxHeaders, "Return was not an array");
        $this->assertNotEmpty($mailBoxHeaders, "There are no headers in this array");

        return $mailBoxHeaders;
    }

    public function testGetMessageNumbersForSearch()
    {
        $messageNumbers = $this->emailReader->getMessageNumbersForSearch($this->testGetSearchResultHeaders());

        $this->assertIsArray($messageNumbers, "Returned was not an array of message numbers");
        $this->assertNotEmpty($messageNumbers, "Returned was empty");

        return $messageNumbers;
    }

    public function testGetMessageHeader()
    {
        $messageNumber = 3;

        $messageHeader = $this->emailReader->getMessageHeader($messageNumber, $this->testOpenMailBoxFolder());

        $this->assertIsObject($messageHeader, "Return was not an object");

        return $messageHeader;
    }

    public function testGetMessageData()
    {
        $messageNumber = 7;

        $messageData = $this->emailReader->getMessageData($messageNumber, $this->testOpenMailBoxFolder());

        $this->assertIsObject($messageData, "Returned data is not an object");
        $this->assertNotEmpty($messageData, "Returned array is empty");
        $this->assertObjectHasAttribute("htmlMessage", $messageData, "Returned object doesn't have the attribute");

        return $messageData;
    }

    public function testEditMessageFlags()
    {
        //Flags: https://www.php.net/manual/en/function.imap-setflag-full.php

        $messageNumberSequence = null;
        $setFlags = null;
        $clearFlags = null;

        $newFlags = $this->emailReader->editMessageFlags($messageNumberSequence, $setFlags, $clearFlags, $this->testOpenMailBoxFolder());

        $this->assertTrue($newFlags, "Returned was not TRUE, meaning it failed to set flags to the message");

        return $newFlags;
    }


    public function testClose()
    {
        $resultBoolean = $this->emailReader->close($this->testOpenMailBox());

        $this->assertTrue($resultBoolean, "Returned FALSE, this means the close failed");

    }

    public function testExample()
    {
        $emailReader = new \Utilities\EmailReader("oxyros.co.za", "glocell.oxyros", "jct1969", 143);


        $mailBox = $emailReader->openMailBox("/notls");

        $folders = $emailReader->getMailBoxFolders($mailBox);

        $mailBoxFolder = $emailReader->openMailBoxFolder ($folders[3]);

        $headers = $emailReader->getMailBoxHeaders($mailBoxFolder);

        $email = $emailReader->getMessageData(418, $mailBox);
    }

}
