<?php
	
	$sql = "SELECT `MMC_REPLY_TO` FROM `MMCACHE` WHERE 0";
	if (!mysql_query($sql)) {
		@mysql_query("ALTER TABLE `MMCACHE` ADD `MMC_REPLY_TO` VARCHAR( 128 ) NULL AFTER `MMC_FROM`");
	}
?>