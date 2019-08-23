<?php
/**
 * Use the array of search result message numbers to get their respective headers
 */

$searchCriteria = "SUBJECT \"test\"";

$searchResult = $emailReader->search($searchCriteria);

$emailReader->getSearchResultHeaders($searchResult);