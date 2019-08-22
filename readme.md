## Unit Testing


### How to create a unit test
```sh
vendor\bin\codecept g:test unit <testName>
```

### Test code
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

## How to use code
### Open imap stream
```
$emailReader = new \Utilities\EmailReader("host", "username", "password", "port number (optional: default is 993)");

$mailBox = $emailReader->openMailBox("flags" (optional: default is "/imap/ssl"));
```

### Get an array of mailbox folders to choose from
```
$folders = $emailReader->getMailBoxFolders();
```

#### Open the desired mailbox folder from the returned array
Use the returned folders array to select which mailbox folder you want to open
```
$mailBoxFolder = $emailReader->openMailBoxFolder($folders[0]);
```

#### Get the headers of all the messages in the selected mailbox folder
```
$headers = $emailReader->getMailBoxHeaders($mailBoxFolder);
```

### Read a selected message in the mailbox folder
Select which message number you want to read from the mailbox folder
```
$messageNumber = 418

$email = $emailReader->getMessageData($messageNumber, $mailBoxFolder);
```

### Dump the attached files to a specified directory location
If there is an attachment on the email you want to download: 
Supply the email using the above method and a valid directory path you want the attachments to be downloaded to
```
$directory = "C:\\Users\\user\\Downloads\\";

$emailReader->dumpAttachments($email, $directory);
```

### Set message status
Set flags to messages using predefined constants:
EMAIL_SEEN, EMAIL_FLAGGED, EMAIL_DELETED, EMAIL_DRAFT or EMAIL_ANSWERED
```
$messageNumberSequence = 2;

$emailReader->setMessageStatus($messageNumberSequence, EMAIL_SEEN);
```

### Clear message status
Clear flags from messages using predefined constants:
EMAIL_SEEN, EMAIL_FLAGGED, EMAIL_DELETED, EMAIL_DRAFT or EMAIL_ANSWERED
```
$messageNumberSequence = 2;

$emailReader->setMessageStatus($messageNumberSequence, EMAIL_SEEN);
```

### Move or copy messages to a different mailbox folder
**Important:** Some email servers require specific flags to be set before moving messages to a specific folder. For example: On a Gmail server, in order for messages to be moved to the Drafts folder ([Gmail]/Drafts) the message first needs to be flagged as "\Draft"
#### Move:
```
$messageNumberSequence = 2;

$emailReader->messageMove($messageNumberSequence, $folders[2]);
```

#### Copy:
```
$messageNumberSequence = 2;

$emailReader->messageCopy($messageNumberSequence, $folders[2]);
```

### Delete a message in the mailbox folder
```
$messageNumber = 2;

$emailReader->messageDelete($messageNumber);
```

### Search for messages
#### Search for messages numbers containing a specific criteria
Returns an array of message numbers containing the search criteria
```
$searchCriteria = "test";

$searchResults = $emailReader->search($searchCriteria);
```

#### Get the headers for the searched
Requires the search results from the initial search and returns the headers of all the searched emails
```
$searchResultHeaders = $emailReader->getSearchResultHeaders($searchResults);
```
