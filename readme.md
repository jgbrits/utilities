##Unit Testing

### How to create a unit test
```sh
vendor\bin\codecept g:test unit <testName>
```

###Test code
```
vendor\bin\codecept run
```

##How to use code
###Open imap stream
```
$emailReader = new \Utilities\EmailReader("host", "username", "password", "port number (optional: default is 993)");

$mailBox = $emailReader->openMailBox();
```

###Get an array of mailbox folders to choose from
```
$folders = $emailReader->getMailBoxFolders($mailBox);
```

####Open the desired mailbox folder
```
$mailBoxFolder = $this->emailReader->openMailBoxFolder($folderName[0]);
```

####Get the headers of all the messages in the selected mailbox folder
```
$headers = $emailReader->getMailBoxHeaders($mailBoxFolder);
```

###Read a selected message in the mailbox folder
```
$email = $emailReader->getMessageData(418, $mailBoxFolder);
```