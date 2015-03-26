DROP TABLE IF EXISTS MMACCESS;
CREATE TABLE MMACCESS (
  U_ID varchar(20) NOT NULL default '',
  MMF_ID varchar(255) NOT NULL default '',
  MMA_RIGHTS int(11) NOT NULL default '0',
  PRIMARY KEY (U_ID,MMF_ID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS MMFOLDER;
CREATE TABLE MMFOLDER (
  MMF_ID varchar(255) NOT NULL default '',
  MMF_NAME varchar(255) NOT NULL default '',
  MMF_ID_PARENT varchar(255) default NULL,
  MMF_STATUS int(11) NOT NULL default '0',
  PRIMARY KEY (MMF_ID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS MMGROUPACCESS;
CREATE TABLE MMGROUPACCESS (
  UG_ID int(11) NOT NULL default '0',
  MMF_ID varchar(255) NOT NULL default '',
  MMA_RIGHTS int(11) default NULL,
  PRIMARY KEY (UG_ID,MMF_ID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS MMSENDER;
CREATE TABLE MMSENDER (
  MMS_ID int(11) NOT NULL auto_increment,
  MMS_FROM varchar(255) NOT NULL default '',
  MMS_EMAIL varchar(255) NOT NULL default '',
  MMS_REPLYTO varchar(255) default NULL,
  MMS_RETURNPATH varchar(255) default NULL,
  MMS_LANGUAGE varchar(20) default NULL,
  MMS_ENCODING varchar(20) default NULL,
  PRIMARY KEY (MMS_ID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS MMACCOUNT;
CREATE TABLE MMACCOUNT (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS MMCACHE;
CREATE TABLE MMCACHE (
  MMC_UID varchar(128) NOT NULL default '',
  MMC_ACCOUNT varchar(128) NOT NULL default '',
  MMC_DATETIME datetime NOT NULL,
  MMC_PRIORITY int(11) NOT NULL DEFAULT '3',
  MMC_FROM varchar(128) default NULL,
  MMC_REPLY_TO varchar(128) default NULL,
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
