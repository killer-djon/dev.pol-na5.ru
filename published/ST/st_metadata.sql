DROP TABLE IF EXISTS `st_action`;
CREATE TABLE `st_action` (
  `id` int(11) NOT NULL auto_increment,
  `name` text NOT NULL,
  `log_name` text NOT NULL,
  `type` varchar(16) NOT NULL,
  `group` enum('system','user','client') NOT NULL,
  `state_id` int(11) NOT NULL default '0',
  `assigned_c_id` int(11) NOT NULL default '0',
  `properties` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `st_action_param`;
CREATE TABLE `st_action_param` (
  `action_id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY  (`action_id`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `st_class`;
CREATE TABLE `st_class` (
  `id` int(11) NOT NULL auto_increment,
  `class_type_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `sorting` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `st_class_type`;
CREATE TABLE `st_class_type` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `sorting` int(11) NOT NULL default '0',
  `multiple` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `st_kbase`;
CREATE TABLE `st_kbase` (
  `id` mediumint(9) NOT NULL auto_increment,
  `type` tinyint(4) NOT NULL default '0',
  `title` varchar(255) NOT NULL,
  `data` varchar(255) NOT NULL,
  `search` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `st_request`;
CREATE TABLE `st_request` (
  `id` int(11) NOT NULL auto_increment,
  `message_id` varchar(255) NULL default NULL,
  `source_type` enum('user','email','form','cabinet') NOT NULL,
  `source` varchar(255) NOT NULL,
  `app_id` varchar(3) NULL DEFAULT NULL,
  `datetime` datetime default NULL,
  `state_id` int(11) default NULL,
  `priority` int(11) NOT NULL,
  `client_from` varchar(255) NOT NULL,
  `client_c_id` int(11) NOT NULL,
  `assigned_c_id` int(11) NOT NULL,
  `subject` varchar(255) default NULL,
  `text` text NOT NULL,
  `attachments` text NULL DEFAULT NULL,
  `read` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE `message_id` (`message_id`),
  KEY `assigned_c_id` (`assigned_c_id`,`read`),
  KEY `client_c_id` (`client_c_id`),
  KEY `subject` (`subject`),
  KEY `state_id` (`state_id`,`datetime`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `st_request_class`;
CREATE TABLE `st_request_class` (
  `request_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  PRIMARY KEY  (`request_id`,`class_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `st_request_log`;
CREATE TABLE `st_request_log` (
  `id` int(11) NOT NULL auto_increment,
  `message_id` varchar(255) NULL default NULL,
  `request_id` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `action_id` int(11) NOT NULL,
  `actor_c_id` int(11) NOT NULL,
  `state_id` int(11) NOT NULL,
  `text` text NOT NULL,
  `attachments` text NOT NULL,
  `to` text NOT NULL,
  `assigned_c_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE `message_id` (`message_id`),
  KEY `request` (`request_id`,`datetime`),
  KEY `action_id` (`action_id`),
  FULLTEXT KEY `text` (`text`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `st_rule`;
CREATE TABLE `st_rule` (
  `id` int(11) NOT NULL auto_increment,
  `condition` text NOT NULL,
  `action` text NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `st_source`;
CREATE TABLE `st_source` (
  `id` int(11) NOT NULL auto_increment,
  `type` varchar(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `st_source_param`;
CREATE TABLE `st_source_param` (
  `source_id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY  (`source_id`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `st_state`;
CREATE TABLE `st_state` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `group` enum('-100','-101','-102') NOT NULL,
  `properties` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `st_state_action`;
CREATE TABLE `st_state_action` (
  `state_id` int(11) NOT NULL,
  `action_id` int(11) NOT NULL,
  `sorting` int(11) NOT NULL,
  PRIMARY KEY  (`state_id`,`action_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `st_action` (`id`, `name`, `log_name`, `type`, `group`, `state_id`, `assigned_c_id`, `properties`) VALUES
(1, 'Accept to process', 'accepts to process', 'ACCEPT', 'user', 1, -1, ''),
(2, 'Change assignment', 'changes assignment', 'ASSIGN', 'user', 0, 0, ''),
(3, 'Specify classifiers', 'specifies classifiers', 'CLASSIFY', 'user', 0, 0, ''),
(4, 'Add comment', 'adds comment', 'COMMENT', 'user', 0, 0, ''),
(5, 'Reply', 'replies to client', 'REPLY', 'user', 3, -1, '{"autocomplete": "users"}'),
(6, 'Forward', 'forwards', 'FORWARD', 'user', 2, -1, '{"autocomplete": "users"}'),
(7, 'Close without reply', 'closes without reply', '', 'user', 3, -1, ''),
(8, 'Reopen', 'reopens request', '', 'user', 4, 0, ''),
(9, 'Delete', 'deletes request', 'DELETE', 'user', -1, 0, ''),
(10, 'Restore', 'restores request', 'RESTORE', 'user', 0, 0, ''),
(11, 'Remove forever', '', 'REMOVE', 'user', 0, 0, ''),
(12, 'Reopen', 'reopens request', 'CLIENT-REOPEN', 'client', 4, 0, ''),
(14, 'Revoke this request', 'revokes request', 'CLIENT-CANCEL', 'client', 5, 0, ''),
(15, 'Confirmation by client', 'confirmed by client', 'CONFIRM-CLIENT', 'system', 1, 0, ''),
(16, 'Accepting without confirmation', '', 'ACCEPT', 'system', 1, 0, ''),
(17, 'Sender = Client (open requests)', 'writes', 'EMAIL-CLIENT', 'system', 0, 0, ''),
(18, 'Sender = Client (closed requests)', 'reopens request', 'EMAIL-CLIENT', 'system', 4, 0, ''),
(19, 'Sender != Client (open requests)', 'writes', 'EMAIL-NOT-CLIENT', 'system', 0, 0, ''),
(20, 'Sender != Client (closed requests)', 'writes', 'EMAIL-NOT-CLIENT', 'system', 4, 0, '');

INSERT INTO `st_action_param` (`action_id`, `name`, `value`) VALUES
(6, 'template', '<br /><br />\n--<br />\n{MY_NAME}<br />\n[`Request`]: <a href="{REQUEST_URL}">{REQUEST_ID}</a>\n<br /><br />\n{REQUEST_HISTORY_CLIENT}\n'),
(5, 'template', '');

INSERT INTO `st_state` (`id`, `name`, `group`, `properties`) VALUES
(-1, 'Deleted', '-100', '{"css":"color:rgb(128,128,128)"}'),
(0, 'Pending', '-100', '{"css":"font-style:italic"}'),
(1, 'New', '-101', '{"css":"color:rgb(0,128,0)"}'),
(2, 'Discussion', '-101', '{"css":"color:rgb(0,84,255)"}'),
(3, 'Closed', '-102', '{"css":"color:rgb(162,42,42)"}'),
(4, 'Reopened', '-101', '{"css":"color:rgb(128,128,0)"}'),
(5, 'Canceled', '-102', '');

INSERT INTO `st_state_action` (`state_id`, `action_id`, `sorting`) VALUES
(0, 1, -1),
(-101, 2, 1),
(-101, 3, 5),
(0, 11, -2),
(-101, 4, 1),
(-101, 5, -5),
(-101, 6, -3),
(-101, 7, 3),
(-102, 8, -1),
(-101, 9, 5),
(-1, 10, -1),
(-1, 11, -1),
(-102, 12, -1),
(-102, 13, -1),
(-101, 14, -1),
(-101, 17, -1),
(-102, 18, -1),
(0, 15, -1),
(-101, 19, -1),
(-102, 20, -1);

