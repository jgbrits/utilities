<?php

use Utilities\EmailReaderError;

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
        $this->emailReader = new \Utilities\EmailReader("imap.gmail.com", "username", "password");
    }

    protected function _after()
    {
        $this->emailReader->close();
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

        $errors2 = $this->emailReader->handleErrors();

        $mailBoxArray2 = $this->emailReader->getMailBoxFolders("not an IMAPStream");

        $this->assertEquals(new EmailReaderError (EMAIL_ERROR_IMAP_STREAM, EMAIL_ERROR_IMAP_STREAM_MESSAGE, $errors2), $mailBoxArray2, "No or incorrect error returned");


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

    public function testOpenMailBoxFolderNegative()
    {
        $errors2 = $this->emailReader->handleErrors();
        $folderName2 = "";
        $mailBox2 = $this->emailReader->openMailBox();
        $mailBoxFolder2 = $this->emailReader->openMailBoxFolder($folderName2);

        $this->assertEquals(new EmailReaderError (EMAIL_ERROR_MAILBOX_FOLDER, EMAIL_ERROR_MAILBOX_FOLDER_MESSAGE, $errors2), $mailBoxFolder2, "No or incorrect error returned");

        $this->emailReader->close($mailBoxFolder2);
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

        $errors2 = $this->emailReader->handleErrors();
        $searchResult2 = $this->emailReader->search("", $this->testOpenMailBoxFolder());
        $this->assertEquals(new EmailReaderError (EMAIL_ERROR_SEARCH_CRITERIA, EMAIL_ERROR_SEARCH_CRITERIA_MESSAGE, $errors2), $searchResult2, "No or incorrect error returned");

        return $searchResult;
    }

    public function testGetSearchResultHeaders()
    {

        $messageHeaders = $this->emailReader->getSearchResultHeaders($this->testSearch(), $this->testOpenMailBoxFolder());

        $messageHeadersArrayCount = count($messageHeaders);

        $this->assertIsArray($messageHeaders, "Return was not an array");
        $this->assertNotEmpty($messageHeaders, "Array is empty");
        $this->assertEquals(2, $messageHeadersArrayCount, "Something went wrong in the method");

        $errors2 = $this->emailReader->handleErrors();
        $messageHeaders2 = $this->emailReader->getSearchResultHeaders("", $this->testOpenMailBoxFolder());
        $this->assertEquals(new EmailReaderError (EMAIL_ERROR_SEARCH_HEADERS_RESULT, EMAIL_ERROR_SEARCH_HEADERS_RESULT_MESSAGE, $errors2), $messageHeaders2, "No or incorrect error returned");
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

        $messageNumbers2 = $this->emailReader->getMessageNumbersForSearch($this->testGetSearchResultHeaders());
        $this->assertEquals(new EmailReaderError (EMAIL_ERROR_SEARCH_HEADERS, EMAIL_ERROR_SEARCH_HEADERS_MESSAGE), $messageNumbers2, "No or incorrect error returned");

        return $messageNumbers;
    }

    public function testGetMessageHeader()
    {
        $messageNumber = 3;
        $messageHeader = $this->emailReader->getMessageHeader($messageNumber, $this->testOpenMailBoxFolder());

        $this->assertIsObject($messageHeader, "Return was not an object");

        $messageNumber2 = null;

        $errors2 = $this->emailReader->handleErrors();
        $messageHeader2 = $this->emailReader->getMessageHeader($messageNumber2, $this->testOpenMailBoxFolder());
        $this->assertEquals(new EmailReaderError (EMAIL_ERROR_MESSAGE_NUMBER, EMAIL_ERROR_MESSAGE_NUMBER_MESSAGE, $errors2), $messageHeader2, "No or incorrect error returned");

        $messageNumber3 = 3;

        $errors3 = $this->emailReader->handleErrors();
        $messageHeader3 = $this->emailReader->getMessageHeader($messageNumber3, "Not an IMAPStream");
        $this->assertEquals(new EmailReaderError (EMAIL_ERROR_IMAP_STREAM, EMAIL_ERROR_IMAP_STREAM_MESSAGE, $errors3), $messageHeader3, "No or incorrect error returned");

        return $messageHeader;
    }

    public function testGetMessageData()
    {
        $messageNumber = 4;

        $messageData = $this->emailReader->getMessageData($messageNumber, $this->testOpenMailBoxFolder());

        $this->assertIsObject($messageData, "Returned data is not an object");
        $this->assertNotEmpty($messageData, "Returned array is empty");
        $this->assertObjectHasAttribute("htmlMessage", $messageData, "Returned object doesn't have the attribute");

        $errors2 = $this->emailReader->handleErrors();
        $messageNumber2 = null;
        $messageData2 = $this->emailReader->getMessageData($messageNumber2, $this->testOpenMailBoxFolder());
        $this->assertEquals(new EmailReaderError (EMAIL_ERROR_MESSAGE_NUMBER, EMAIL_ERROR_MESSAGE_NUMBER, $errors2), $messageData2, "No or incorrect error returned");


        return $messageData;
    }

    public function testGetMessageDataNegative()
    {
        $messageNumber2 = null;

        $errors2 = $this->emailReader->handleErrors();
        $messageData2 = $this->emailReader->getMessageData($messageNumber2, $this->testOpenMailBoxFolder());
        $this->assertEquals(new EmailReaderError (EMAIL_ERROR_MESSAGE_NUMBER, EMAIL_ERROR_MESSAGE_NUMBER_MESSAGE, $errors2), $messageData2, "No or incorrect error returned");

        $messageNumber3 = 6;

        $errors3 = $this->emailReader->handleErrors();
        $messageData3 = $this->emailReader->getMessageData($messageNumber3, "Not an IMAPStream");
        $this->assertEquals(new EmailReaderError (EMAIL_ERROR_IMAP_STREAM, EMAIL_ERROR_IMAP_STREAM_MESSAGE, $errors3), $messageData3, "No or incorrect error returned");
    }

    public function testSetMessageStatus()
    {
        $messageNumberSequence = 5;

        $newFlags = $this->emailReader->setMessageStatus($messageNumberSequence, EMAIL_SEEN, $this->testOpenMailBoxFolder());

        $this->assertTrue($newFlags, "Returned was not TRUE, meaning it failed to set flags to the message");

        $messageNumberSequence2 = null;
        $errors2 = $this->emailReader->handleErrors();
        $newFlags2 = $this->emailReader->setMessageStatus($messageNumberSequence2, EMAIL_SEEN, $this->testOpenMailBoxFolder());

        $this->assertEquals(new EmailReaderError (EMAIL_ERROR_EDIT_MESSAGE_STATUS_SEQUENCE, EMAIL_ERROR_EDIT_MESSAGE_STATUS_SEQUENCE_MESSAGE, $errors2), $newFlags2, "No or incorrect error returned");

        $messageNumberSequence3 = 4;
        $errors3 = $this->emailReader->handleErrors();
        $newFlags3 = $this->emailReader->setMessageStatus($messageNumberSequence3, "", $this->testOpenMailBoxFolder());

        $this->assertEquals(new EmailReaderError (EMAIL_ERROR_EDIT_MESSAGE_STATUS_NEW_MESSAGE_STATUS, EMAIL_ERROR_EDIT_MESSAGE_STATUS_NEW_MESSAGE_STATUS_MESSAGE, $errors3), $newFlags3, "No or incorrect error returned");

        return $newFlags;
    }

    public function testClearMessageStatus()
    {
        $messageNumberSequence = 4;

        $newFlags = $this->emailReader->clearMessageStatus($messageNumberSequence, EMAIL_SEEN, $this->testOpenMailBoxFolder());

        $this->assertTrue($newFlags, "Returned was not TRUE, meaning it failed to set flags to the message");

        $messageNumberSequence2 = null;
        $errors2 = $this->emailReader->handleErrors();
        $newFlags2 = $this->emailReader->clearMessageStatus($messageNumberSequence2, EMAIL_SEEN, $this->testOpenMailBoxFolder());

        $this->assertEquals(new EmailReaderError (EMAIL_ERROR_EDIT_MESSAGE_STATUS_SEQUENCE, EMAIL_ERROR_EDIT_MESSAGE_STATUS_SEQUENCE_MESSAGE, $errors2), $newFlags2, "No or incorrect error returned");

        $messageNumberSequence3 = 4;
        $errors3 = $this->emailReader->handleErrors();
        $newFlags3 = $this->emailReader->clearMessageStatus($messageNumberSequence3, "", $this->testOpenMailBoxFolder());

        $this->assertEquals(new EmailReaderError (EMAIL_ERROR_EDIT_MESSAGE_STATUS_NEW_MESSAGE_STATUS, EMAIL_ERROR_EDIT_MESSAGE_STATUS_NEW_MESSAGE_STATUS_MESSAGE, $errors3), $newFlags3, "No or incorrect error returned");

        return $newFlags;
    }


    public function testClose()
    {
        $resultBoolean = $this->emailReader->close($this->testOpenMailBox());

        $this->assertTrue($resultBoolean, "Returned FALSE, this means the close failed");

        $errors2 = $this->emailReader->handleErrors();
        $resultBoolean2 = $this->emailReader->close();

        $this->assertEquals(new EmailReaderError(EMAIL_ERROR_IMAP_STREAM, EMAIL_ERROR_IMAP_STREAM_MESSAGE, $errors2), $resultBoolean2, "No or incorrect error returned");

    }

    public function testDumpAttachments()
    {
        $messageData = $this->testGetMessageData();
        $directory = "C:\\Users\\justi\\Downloads\\";

        $dumpAttachmentsResult = $this->emailReader->dumpAttachments($messageData, $directory);

        $this->assertTrue($dumpAttachmentsResult);

        $messageData2 = null;
        $directory2 = "C:\\Users\\justi\\Downloads\\";
        $dumpAttachmentsResult2 = $this->emailReader->dumpAttachments($messageData2, $directory2);

        $this->assertEquals(new EmailReaderError(EMAIL_ERROR_DUMP_ATTACHMENTS_DATA, EMAIL_ERROR_DUMP_ATTACHMENTS_DATA_MESSAGE, null), $dumpAttachmentsResult2, "No or incorrect error returned");

        $errors3 = $this->emailReader->handleErrors();
        $messageData3 = $this->testGetMessageData();
        $directory3 = null;
        $dumpAttachmentsResult3 = $this->emailReader->dumpAttachments($messageData3, $directory3);

        $this->assertEquals(new EmailReaderError(EMAIL_ERROR_DUMP_ATTACHMENTS_DIRECTORY, EMAIL_ERROR_DUMP_ATTACHMENTS_DIRECTORY_MESSAGE, null), $dumpAttachmentsResult3, "No or incorrect error returned");

    }


    public function testMessageMove()
    {
        $sequence = 12;
        $destination = "[Gmail]/Spam";
        $messageMoveResult = $this->emailReader->messageMove($sequence, $destination, $this->testOpenMailBoxFolder());
        $this->assertTrue($messageMoveResult, "Returned was not true");

        $errors2 = $this->emailReader->handleErrors();
        $sequence2 = null;
        $destination2 = null;
        $messageMoveResult2 = $this->emailReader->messageMove($sequence2, $destination2, $this->testOpenMailBoxFolder());

        $this->assertEquals(new EmailReaderError (EMAIL_ERROR_EDIT_MESSAGE_STATUS_SEQUENCE, EMAIL_ERROR_EDIT_MESSAGE_STATUS_SEQUENCE_MESSAGE, $errors2), $messageMoveResult2, "No or incorrect error returned");

        $errors3 = $this->emailReader->handleErrors();
        $sequence3 = 12;
        $destination3 = null;
        $messageMoveResult3 = $this->emailReader->messageMove($sequence3, $destination3, $this->testOpenMailBoxFolder());

        $this->assertEquals(new EmailReaderError (EMAIL_ERROR_DESTINATION_FOLDER, EMAIL_ERROR_DESTINATION_FOLDER_MESSAGE, $errors3), $messageMoveResult3, "No or incorrect error returned");

    }

    public function testMessageCopy()
    {
        $sequence = 1;
        $destination = "[Gmail]/Spam";
        $messageCopyResult = $this->emailReader->messageCopy($sequence, $destination, $this->testOpenMailBoxFolder());
        $this->assertTrue($messageCopyResult);

        $errors2 = $this->emailReader->handleErrors();
        $sequence2 = null;
        $destination2 = null;
        $messageCopyResult2 = $this->emailReader->messageCopy($sequence2, $destination2, $this->testOpenMailBoxFolder());
        $this->assertEquals(new EmailReaderError (EMAIL_ERROR_EDIT_MESSAGE_STATUS_SEQUENCE, EMAIL_ERROR_EDIT_MESSAGE_STATUS_SEQUENCE_MESSAGE, $errors2), $messageCopyResult2, "No or incorrect error returned");

        $errors3 = $this->emailReader->handleErrors();
        $sequence3 = 13;
        $destination3 = null;
        $messageCopyResult3 = $this->emailReader->messageCopy($sequence3, $destination3, $this->testOpenMailBoxFolder());
        $this->assertEquals(new EmailReaderError (EMAIL_ERROR_DESTINATION_FOLDER, EMAIL_ERROR_DESTINATION_FOLDER_MESSAGE, $errors3), $messageCopyResult3, "No or incorrect error returned");

    }

    public function testMessageDelete()
    {
        $messageNumber = 11;
        $mailBox = $this->testOpenMailBoxFolder();
        $messageDeleteResult = $this->emailReader->messageDelete($messageNumber, $mailBox);
        $this->assertTrue($messageDeleteResult);


        $messageNumber2 = null;

        $errors2 = $this->emailReader->handleErrors();
        $messageDeleteResult2 = $this->emailReader->messageDelete($messageNumber2, $this->testOpenMailBoxFolder());
        $this->assertEquals(new EmailReaderError (EMAIL_ERROR_MESSAGE_NUMBER, EMAIL_ERROR_MESSAGE_NUMBER_MESSAGE, $errors2), $messageDeleteResult2, "No or incorrect error returned");

    }


}
