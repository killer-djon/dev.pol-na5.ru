<?php

$sql = array();
$sql[] = "ALTER TABLE st_request ADD message_id VARCHAR(255) NULL DEFAULT NULL AFTER id";
$sql[] = "ALTER TABLE st_request ADD UNIQUE(message_id)";
$sql[] = "ALTER TABLE st_request_log ADD message_id VARCHAR(255) NULL DEFAULT NULL AFTER id";
$sql[] = "ALTER TABLE st_request_log ADD UNIQUE(message_id)";

foreach ($sql as $query) {
	@mysql_query($query);
}

?>