DROP TABLE IF EXISTS ISSUE;
CREATE TABLE ISSUE (
  I_ID int(11) NOT NULL default '0',
  P_ID int(11) default NULL,
  PW_ID int(11) default NULL,
  I_NUM int(11) NOT NULL default '0',
  I_STATUSCURRENT varchar(20) default NULL,
  U_ID_ASSIGNED varchar(20) default NULL,
  U_ID_SENDER varchar(20) NOT NULL default '',
  I_STATUSCURRENTDATE datetime default NULL,
  I_PRIORITY int(11) default NULL,
  I_STARTDATE date default NULL,
  I_DUEDATE date default NULL,
  I_DESC text,
  I_ATTACHMENT text,
  I_CLOSEDATE date default NULL,
  I_TRANSITION_DESC text,
  U_ID_AUTHOR varchar(20), 
  PRIMARY KEY  (I_ID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS ISSUEFILTER;
CREATE TABLE ISSUEFILTER (
  U_ID varchar(20) NOT NULL default '',
  ISSF_ID int(11) NOT NULL default '0',
  ISSF_NAME varchar(250) NOT NULL default '',
  P_ID int(11) default NULL,
  ISSF_WORKSTATE int(11) default NULL,
  ISSF_HIDDENSTATES varchar(255) default NULL,
  ISFF_U_ID_ASSIGNED varchar(20) default NULL,
  ISFF_U_ID_SENDER varchar(20) default NULL,
  ISFF_DAYSOLD int(11) default NULL,
  ISSF_SEARCHSTRING varchar(255) default NULL,
  ISSF_MODIFYDATETIME timestamp NOT NULL,
  ISSF_PENDING int,
  ISFF_U_ID_AUTHOR varchar(20),
  ISSF_ISSUE_COMPLETE int,
  ISSF_WORKSTATE_CREATEDAY_OPT int,
  ISSF_LASTDAYS int, 
  ISSF_DAYSAGO int,
  PRIMARY KEY  (U_ID,ISSF_ID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS ISSUETRANSITIONLOG;
CREATE TABLE ISSUETRANSITIONLOG (
  I_ID int(11) NOT NULL default '0',
  ITL_ID int(11) NOT NULL default '0',
  ITL_STATUS varchar(20) default NULL,
  ITL_DESC text,
  ITL_OLDCONTENT text,
  ITL_ISRETURN smallint(6) default NULL,
  ITL_ATTACHMENT text,
  U_ID_ASSIGNED varchar(20) default NULL,
  U_ID_SENDER varchar(20) default NULL,
  ITL_DATETIME datetime default NULL,
  PRIMARY KEY  (I_ID,ITL_ID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS ISSUETRANSITIONSCHEMA;
CREATE TABLE ISSUETRANSITIONSCHEMA (
  P_ID int(11) NOT NULL default '0',
  PW_ID int(11) NOT NULL default '0',
  ITS_NUM int(11) NOT NULL default '0',
  ITS_STATUS varchar(20) NOT NULL default '',
  ITS_ALLOW_EDIT int(11) default NULL,
  ITS_ALLOW_DELETE int(11) default NULL,
  ITS_ASSIGNMENTOPTION int(11) default NULL,
  U_ID_ASSIGNED varchar(20) default NULL,
  ITS_COLOR int(11) default NULL,
  ITS_ALLOW_DEST text default NULL,
  ITS_DEFAULT_DEST text,
  PRIMARY KEY  (P_ID,PW_ID,ITS_NUM)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS ISSUETRANSITIONTEMPLATE;
CREATE TABLE ISSUETRANSITIONTEMPLATE (
  ITT_ID int(11) NOT NULL default '0',
  ITT_NAME varchar(100) NOT NULL default '',
  ITT_STATUS int(11) default NULL,
  ITT_DEFAULT int(11) default '0',
  PRIMARY KEY  (ITT_ID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS ISSUETRANSITIONTEMPLATESCHEMA;
CREATE TABLE ISSUETRANSITIONTEMPLATESCHEMA (
  ITT_ID int(11) NOT NULL default '0',
  ITS_NUM int(11) NOT NULL default '0',
  ITTS_STATUS varchar(20) NOT NULL default '0',
  ITTS_ALLOW_EDIT int(11) default NULL,
  ITTS_ALLOW_DELETE int(11) default NULL,
  ITTS_ASSIGNMENTOPTION int(11) default NULL,
  ITTS_ASSIGNED varchar(10) default NULL,
  ITTS_COLOR int(11) default NULL,
  ITTS_ALLOW_DEST text default NULL,
  ITTS_DEFAULT_DEST text,
  PRIMARY KEY  (ITT_ID,ITS_NUM)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

insert  into ISSUETRANSITIONTEMPLATE (ITT_ID, ITT_NAME, ITT_STATUS, ITT_DEFAULT) values (0, 'Generic Workflow 1', 0, 1);
insert  into ISSUETRANSITIONTEMPLATE (ITT_ID, ITT_NAME, ITT_STATUS, ITT_DEFAULT) values (1, 'Generic Workflow 2', 0, 0);
insert  into ISSUETRANSITIONTEMPLATE (ITT_ID, ITT_NAME, ITT_STATUS, ITT_DEFAULT) values (2, 'Generic Workflow 3', 0, 0);

insert  into ISSUETRANSITIONTEMPLATESCHEMA (ITT_ID,
ITS_NUM,
ITTS_STATUS,
ITTS_ALLOW_EDIT,
ITTS_ALLOW_DELETE,
ITTS_ASSIGNMENTOPTION,
ITTS_ASSIGNED,
ITTS_COLOR,
ITTS_ALLOW_DEST,
ITTS_DEFAULT_DEST
) values 
(0, 1, 'Start', 0, 0, null, null, null, 0x496E2050726F6772657373, null), 
(0, 2, 'In Progress', 1, 1, 0, '', 2, 0x436F6D706C657465, null), 
(0, 3, 'Complete', 0, 0, -1, null, 0, null, null), 
(1, 1, 'Start', 0, 0, null, null, null, 0x496E2050726F6772657373, null), 
(1, 2, 'In Progress', 1, 1, 0, null, 2, 0x546F20436865636B, null), 
(1, 3, 'To Check', 1, 0, 1, '!sender!', 5, 0x496E2050726F6772657373215E21436F6D706C657465, 0x436F6D706C657465), 
(1, 4, 'Complete', 0, 0, -1, null, 0, null, null), 
(2, 4, 'To Approve', 0, 1, 0, '!sender!', 8, 0x546F20436865636B215E21436F6D706C657465, 0x436F6D706C657465), 
(2, 3, 'To Check', 1, 0, 1, '!sender!', 5, 0x496E2050726F6772657373215E21546F20417070726F7665, 0x546F20417070726F7665), 
(2, 2, 'In Progress', 1, 1, 0, null, 2, 0x546F20436865636B, null), 
(2, 1, 'Start', 0, 0, null, null, null, 0x496E2050726F6772657373, null), 
(2, 5, 'Complete', 0, 0, -1, null, 0, null, null);