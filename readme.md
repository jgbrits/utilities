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

For windows:
vendor\bin\phpdoc -d classes\ -t documentation\
```

##How to use code
###Open imap stream
```
$emailReader = new \Utilities\EmailReader("host", "username", "password", "port number (optional: default is 993)");

$mailBox = $emailReader->openMailBox("flags" (optional: default is "/imap/ssl"));
```

###Get an array of mailbox folders to choose from
```
$folders = $emailReader->getMailBoxFolders();
```

####Open the desired mailbox folder from the returned array
Use the returned folders array to select which mailbox folder you want to open
```
$mailBoxFolder = $emailReader->openMailBoxFolder($folders[0]);
```

####Get the headers of all the messages in the selected mailbox folder
```
$headers = $emailReader->getMailBoxHeaders($mailBoxFolder);
```

###Read a selected message in the mailbox folder
Select which message number you want to read from the mailbox folder
```
$messageNumber = 418

$email = $emailReader->getMessageData($messageNumber, $mailBoxFolder);
```

###Dump the attached files to a specified directory location
If there is an attachment on the email you want to download: 
Supply the email using the above method and a valid directory path you want the attachments to be downloaded to
```
$directory = "C:\\Users\\user\\Downloads\\";

$emailReader->dumpAttachments($email, $directory);
```
