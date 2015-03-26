DROP TABLE IF EXISTS PIXEXIF;
CREATE TABLE `PIXEXIF` (
  `PL_ID` int(11) NOT NULL,
  `PE_WIDTH` int(5) NOT NULL,
  `PE_HEIGHT` int(5) NOT NULL,
  `PE_DATETIME` datetime NOT NULL,
  `PE_FILENAME` varchar(40) NOT NULL,
  `PE_FILESIZE` int(11) NOT NULL,
  `PE_MAKE` varchar(100) NOT NULL,
  `PE_MODEL` varchar(100) NOT NULL,
  `PE_EXPOSURETIME` varchar(50) NOT NULL,
  `PE_FNUMBER` varchar(50) NOT NULL,
  `PE_ISOSPEEDRATINGS` varchar(50) NOT NULL,
  `PE_FOCALLENGTH` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS PIXFOLDER;
CREATE TABLE `PIXFOLDER` (
  `PF_ID` int(11) NOT NULL auto_increment,
  `PF_ID_PARENT` varchar(255) default 'ROOT',
  `PF_NAME` varchar(255) default NULL,
  `PF_STATUS` int(11) default '2',
  `PF_CREATEDATETIME` datetime default NULL,
  `PF_CREATEUSERNAME` varchar(50) default NULL,
  `PF_MODIFYDATETIME` datetime default NULL,
  `PF_MODIFYUSERNAME` varchar(50) default NULL,
  `PF_SORT` int(11) NOT NULL default '1',
  `PF_DATESTR` varchar(255) NOT NULL,
  `PF_THUMB` int(11) NOT NULL,
  `PF_IMAGE_COUNT` int(11) default '0',
  `PF_LINK` varchar(100) default NULL,
  `PF_DESC` text NOT NULL,
  `PF_SETTING` text NOT NULL,
  `C_ID` int(11) NOT NULL,
  PRIMARY KEY  (`PF_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8  ;

DROP TABLE IF EXISTS PIXLIST;
CREATE TABLE `PIXLIST` (
  `PL_ID` int(11) NOT NULL auto_increment,
  `PF_ID` int(11) NOT NULL,
  `PL_DESC` text,
  `PL_FILENAME` varchar(255) default NULL,
  `PL_FILETYPE` varchar(10) default NULL,
  `PL_FILESIZE` int(11) default NULL,
  `PL_UPLOADDATETIME` datetime default NULL,
  `PL_UPLOADUSERNAME` varchar(50) default NULL,
  `PL_MIMETYPE` varchar(50) default NULL,
  `PL_DISKFILENAME` varchar(255) default NULL,
  `PL_MODIFYDATETIME` datetime default NULL,
  `PL_MODIFYUSERNAME` varchar(50) default NULL,
  `PL_STATUSINT` int(1) default NULL,
  `PL_DELETE_U_ID` varchar(20) default NULL,
  `PL_DELETE_DATETIME` datetime default NULL,
  `PL_DELETE_USERNAME` varchar(50) default NULL,
  `PL_CHECKSTATUS` char(3) default NULL,
  `PL_CHECKDATETIME` datetime default NULL,
  `PL_CHECKUSERID` varchar(20) default NULL,
  `PL_VERSIONCOMMENT` varchar(255) default NULL,
  `PL_SORT` int(11) default '0',
  `PL_ROTATE` int(11) NOT NULL default '1',
  `PL_WIDTH` int(5) default '100',
  `PL_HEIGHT` int(5) default '100',
  `C_ID` int(11) NOT NULL,
  PRIMARY KEY  (`PL_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8  ;

DROP TABLE IF EXISTS PIXCOMMENTS;
CREATE TABLE `PIXCOMMENTS` (
  `PC_ID` int(11) NOT NULL auto_increment,
  `PC_OWNER_IMAGE` int(11) default '0',
  `PC_OWNER_ALBUM` int(11) default NULL,
  `PC_AUTHER` varchar(20) NOT NULL,
  `PC_EMAIL` varchar(20) NOT NULL,
  `PC_TEXT` text NOT NULL,
  `PC_DATEADD` datetime NOT NULL,
  `PC_TYPE` int(1) default '3',
  PRIMARY KEY  (`PC_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

INSERT INTO `USER_SETTINGS` VALUES ('', 'PD', 'SharpenUsaged', '1');