<?php
/**
 * Search for messages containing a specific search criteria
 * Returns an array of message numbers that contain the specified search criteria
 * List of search criteria: https://www.php.net/manual/en/function.imap-search.php
 */

$searchCriteria = "SUBJECT \"test\"";

$searchResults = $emailReader->search($searchCriteria);