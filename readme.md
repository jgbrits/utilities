##Unit Testing


### How to create a unit test
```sh
vendor\bin\codecept g:test unit <testName>
```

###Test code
```
vendor\bin\codecept run

```

### Run Documentation

**Important:** Before running the documentation you need to have installed graphviz and have it on your path -
https://graphviz.gitlab.io/about/

For more information about documenting your code - https://phpdoc.org

```
vendor/bin/phpdoc -d classes/ -t documentation/
```

##How to use code
###Open imap stream
```
$emailReader = new \Utilities\EmailReader("host", "username", "password", "port number (optional: default is 993)");

$mailBox = $emailReader->openMailBox("flags" (optional: default is "/imap/ssl"));
```

###Get an array of mailbox folders to choose from
```
$folders = $emailReader->getMailBoxFolders($mailBox);
```

####Open the desired mailbox folder from the returned array
```
$mailBoxFolder = $this->emailReader->openMailBoxFolder($folderName[0]);
```

####Get the headers of all the messages in the selected mailbox folder
```
$headers = $emailReader->getMailBoxHeaders($mailBoxFolder);
```

###Read a selected message in the mailbox folder
```
$messageNumber = 418

$email = $emailReader->getMessageData($messageNumber, $mailBoxFolder);
```

###Dump the attached files to a specified directory location
```
$directory = "C:\\Users\\user\\Downloads\\";

$this->emailReader->dumpAttachments($email, $directory);
```
