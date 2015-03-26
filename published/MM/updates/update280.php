<?php

	$queries = array(

		"DROP TABLE IF EXISTS MMACCOUNT;",
		"CREATE TABLE MMACCOUNT (
			MMA_ID int(11) NOT NULL auto_increment,
			MMA_NAME varchar(128) default NULL,
			MMA_EMAIL varchar(128) default NULL,
			MMA_DOMAIN varchar(128) default NULL,
			MMA_SERVER varchar(128) default NULL,
			MMA_LOGIN varchar(128) default NULL,
			MMA_PASSWORD varchar(128) default NULL,
			MMA_PORT int(11) default NULL,
			MMA_PROTOCOL varchar(16) default NULL,
			MMA_SECURE int(11) default NULL,
			MMA_ACCESS int(11) default NULL,
			MMA_INTERNAL int(11) default NULL,
			MMA_USERID varchar(50) default NULL,
			PRIMARY KEY (MMA_ID),
			UNIQUE KEY MMA_LOGIN (MMA_EMAIL,MMA_DOMAIN)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

		"DROP TABLE IF EXISTS MMCACHE;",
		"CREATE TABLE MMCACHE (
			MMC_UID varchar(128) NOT NULL default '',
			MMC_ACCOUNT varchar(128) NOT NULL default '',
			MMC_PRIORITY int(11) NOT NULL DEFAULT '3',
			MMC_DATETIME datetime NOT NULL,
			MMC_FROM varchar(128) default NULL,
			MMC_TO text,
			MMC_CC text,
			MMC_SUBJECT varchar(255) default NULL,
			MMC_LEAD varchar(128) default NULL,
			MMC_CONTENT text,
			MMC_ATTACHMENT text,
			MMC_IMAGES text,
			MMC_SIZE int(11) default NULL,
			MMC_FLAG varchar(128) default NULL,
			MMC_HEADER text,
			PRIMARY KEY (MMC_UID, MMC_ACCOUNT)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;"

	);
	
	foreach ($queries as $query) {
		$res = mysql_query($query);
	}

?>