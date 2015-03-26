DROP TABLE IF EXISTS CUSTOMER;
CREATE TABLE CUSTOMER (
  C_ID int(11) NOT NULL default '0',
  C_NAME varchar(250) default NULL,
  C_ADDRESSSTREET varchar(50) default NULL,
  C_ADDRESSCITY varchar(30) default NULL,
  C_ADDRESSSTATE varchar(30) default NULL,
  C_ADDRESSZIP varchar(10) default NULL,
  C_ADDRESSCOUNTRY varchar(30) default NULL,
  C_CONTACTPERSON varchar(50) default NULL,
  C_PHONE varchar(50) default NULL,
  C_FAX varchar(50) default NULL,
  C_STATUS smallint(6) NOT NULL default '0',
  C_EMAIL varchar(50) default NULL,
  C_MODIFYDATETIME datetime default NULL,
  C_MODIFYUSERNAME varchar(50) default NULL,
  PRIMARY KEY  (C_ID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS PROJECT;
CREATE TABLE PROJECT (
  P_ID int(11) NOT NULL default '0',
  C_ID int(11) default NULL,
  U_ID_MANAGER varchar(20) default NULL,
  P_DESC varchar(250) default NULL,
  P_BILLABLE int(11) default NULL,
  P_STARTDATE date default NULL,
  P_ENDDATE date default NULL,
  P_MODIFYDATETIME datetime default NULL,
  P_MODIFYUSERNAME varchar(50) default NULL,
  DF_ID VARCHAR( 255 ) NOT NULL,
  PRIMARY KEY  (P_ID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS PROJECTWORK;
CREATE TABLE PROJECTWORK (
  P_ID int(11) NOT NULL default '0',
  PW_ID int(11) NOT NULL default '0',
  PW_DESC varchar(250) default NULL,
  PW_STARTDATE date default NULL,
  PW_DUEDATE date default NULL,
  PW_ENDDATE date default NULL,
  PW_BILLABLE int(11) default NULL,
  PW_COSTESTIMATE decimal(15,2) default NULL,
  PW_COSTCUR char(3) default NULL,
  PRIMARY KEY  (P_ID,PW_ID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS WORKASSIGNMENT;
CREATE TABLE WORKASSIGNMENT (
  P_ID int(11) NOT NULL default '0',
  PW_ID int(11) NOT NULL default '0',
  U_ID varchar(20) NOT NULL default '',
  PRIMARY KEY  (P_ID,PW_ID,U_ID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS PACCESS;
CREATE TABLE PACCESS
(
  P_ID INTEGER NOT NULL
,  U_ID VARCHAR(20) NOT NULL
,  PA_RIGHTS INT NOT NULL
,  PRIMARY KEY (P_ID, U_ID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS PGROUPACCESS;
CREATE TABLE PGROUPACCESS
(
  P_ID INTEGER NOT NULL
,  UG_ID INTEGER NOT NULL
,  PA_RIGHTS INTEGER NOT NULL
,  PRIMARY KEY (P_ID, UG_ID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;