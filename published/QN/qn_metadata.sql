DROP TABLE IF EXISTS QNACCESS;
CREATE TABLE QNACCESS
(
 U_ID VARCHAR(20) NOT NULL,
 QNF_ID VARCHAR(255) NOT NULL,
 QNA_RIGHTS INTEGER NOT NULL,
 PRIMARY KEY (U_ID, QNF_ID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS QNFOLDER;
CREATE TABLE QNFOLDER
(
 QNF_ID VARCHAR(255) NOT NULL,
 QNF_NAME VARCHAR(255) NOT NULL,
 QNF_ID_PARENT VARCHAR(255),
 QNF_STATUS int NOT NULL default '0',
 PRIMARY KEY (QNF_ID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS QUICKNOTES;
CREATE TABLE QUICKNOTES
(
 QN_ID INTEGER NOT NULL,
 QNF_ID VARCHAR(255) NOT NULL,
 QN_SUBJECT VARCHAR(50) NOT NULL,
 QN_CONTENT TEXT NULL,
 QN_ATTACHMENT TEXT NULL,
 QN_MODIFYDATETIME DATETIME NOT NULL,
 QN_MODIFYUSERNAME VARCHAR(20) NOT NULL,
 QN_STATUS INTEGER,
 PRIMARY KEY (QN_ID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS QNGROUPACCESS;
CREATE TABLE QNGROUPACCESS
(
  UG_ID INTEGER NOT NULL,
  QNF_ID VARCHAR(255) NOT NULL,
  QNA_RIGHTS INT NULL,
  PRIMARY KEY (UG_ID, QNF_ID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS QNTEMPLATE;
CREATE TABLE QNTEMPLATE (
 `QNT_ID` int(11) NOT NULL auto_increment,
 `QNT_NAME` varchar(50) NOT NULL default '',
 `QNT_HTML` text NOT NULL,
 `QNT_MODIFYDATETIME` datetime NOT NULL default '0000-00-00 00:00:00',
 `QNT_MODIFYUSERNAME` varchar(20) NOT NULL default '',
 PRIMARY KEY  (`QNT_ID`),
 UNIQUE KEY `QNT_NAME` (`QNT_NAME`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO QNFOLDER(QNF_ID, QNF_ID_PARENT, QNF_NAME, QNF_STATUS) VALUES("1.", "ROOT", "My Notes", 0);
INSERT INTO QNACCESS(U_ID, QNF_ID, QNA_RIGHTS) VALUES ('%SUBSCRIBER_ID%', "1.", 2);
INSERT INTO QNACCESS(U_ID, QNF_ID, QNA_RIGHTS) VALUES ('%SUBSCRIBER_ID%', "ROOT", 2);
INSERT INTO QUICKNOTES(QN_ID, QNF_ID, QN_SUBJECT, QN_CONTENT, QN_MODIFYDATETIME, QN_MODIFYUSERNAME, QN_STATUS) VALUES (1, "1.", "About WebAsyst Quick Notes", "WebAsyst Quick Notes is a web based online notepad where you can jot down any kind of notes and memos, organize them into folders and share those folders with your co-workers.

This is just a sample note. Simply delete it when you start using Quick Notes.", NOW(), '%SUBSCRIBER_NAME%', 0);

INSERT  INTO QNTEMPLATE (QNT_NAME,QNT_HTML,QNT_MODIFYDATETIME,QNT_MODIFYUSERNAME) 
VALUES ('Plain List', 0x3C7370616E207374796C653D22464F4E542D5745494748543A20626F6C64223E3C2F7370616E3E3C666F6E74207374796C653D22464F4E542D5745494748543A20626F6C64222073697A653D2233223E3C7370616E207374796C653D22464F4E542D46414D494C593A20617269616C2C68656C7665746963612C73616E732D7365726966223E255355424A454354253C2F7370616E3E3C6272202F3E3C666F6E74207374796C653D22464F4E542D46414D494C593A20617269616C2C68656C7665746963612C73616E732D7365726966222073697A653D2232223E3C6272202F3E3C2F666F6E743E3C2F666F6E743E3C666F6E742073697A653D2233223E3C666F6E74207374796C653D22464F4E542D46414D494C593A20617269616C2C68656C7665746963612C73616E732D7365726966222073697A653D2232223E25434F4E54454E54253C6272202F3E3C2F666F6E743E3C2F666F6E743E3C6872207374796C653D2257494454483A20313030253B204845494748543A2032707822202F3E, NOW(), '%SUBSCRIBER_NAME%');

INSERT  INTO QNTEMPLATE (QNT_NAME,QNT_HTML,QNT_MODIFYDATETIME,QNT_MODIFYUSERNAME) 
VALUES ('Plain List (with details)', 0x3C703E3C7374726F6E673E3C7370616E207374796C653D22464F4E542D46414D494C593A20617269616C2C68656C7665746963612C73616E732D7365726966223E3C666F6E742073697A653D2233223E255355424A454354253C2F666F6E743E3C2F7370616E3E3C6272202F3E3C2F7374726F6E673E3C666F6E74207374796C653D22464F4E542D46414D494C593A20617269616C2C68656C7665746963612C73616E732D7365726966222073697A653D2232223E3C6272202F3E3C2F666F6E743E3C666F6E742073697A653D2233223E3C666F6E74207374796C653D22464F4E542D46414D494C593A20617269616C2C68656C7665746963612C73616E732D7365726966222073697A653D2232223E25434F4E54454E54253C2F666F6E743E3C2F666F6E743E3C2F703E3C703E3C666F6E742073697A653D222B30223E3C666F6E74207374796C653D22464F4E542D46414D494C593A20617269616C2C68656C7665746963612C73616E732D7365726966223E3C666F6E7420666163653D22617269616C2C68656C7665746963612C73616E732D7365726966222073697A653D2232223E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C222074696D65733D2274696D657322206E65773D226E6577223E3C753E4174746163686D656E74733C2F753E3C2F7370616E3E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C222074696D65733D2274696D657322206E65773D226E6577223E3A203C2F7370616E3E2546494C454C495354253C2F666F6E743E3C2F666F6E743E3C2F666F6E743E3C2F703E3C646976207374796C653D22544558542D414C49474E3A207269676874223E3C666F6E742073697A653D2233223E3C666F6E74207374796C653D22464F4E542D46414D494C593A20617269616C2C68656C7665746963612C73616E732D7365726966222073697A653D2231223E3C693E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A203870743B20464F4E542D46414D494C593A20417269616C223E4D6F6469666965642062793A203C2F7370616E3E3C2F693E25555345524E414D452520254441544554494D45253C6272202F3E4C6F636174696F6E3A2025464F4C44455225203C2F666F6E743E3C2F666F6E743E3C2F6469763E3C6872202F3E, NOW(), '%SUBSCRIBER_NAME%');

INSERT  INTO QNTEMPLATE (QNT_NAME,QNT_HTML,QNT_MODIFYDATETIME,QNT_MODIFYUSERNAME) 
VALUES ('Classic', 0x3C7461626C652077696474683D223130302522206267636F6C6F723D2223363636363636223E3C74626F64793E3C74723E3C74643E3C666F6E7420666163653D2276657264616E612C617269616C2C68656C7665746963612C73616E732D73657269662220636F6C6F723D2223666666666666222073697A653D2234223E255355424A454354253C2F666F6E743E3C2F74643E3C2F74723E3C2F74626F64793E3C2F7461626C653E3C626C6F636B71756F7465206469723D226C747222207374796C653D224D415247494E2D52494748543A20307078223E3C703E3C666F6E7420666163653D2276657264616E612C617269616C2C68656C7665746963612C73616E732D7365726966222073697A653D2232223E25434F4E54454E54253C2F666F6E743E3C2F703E3C2F626C6F636B71756F74653E, NOW(), '%SUBSCRIBER_NAME%');

INSERT  INTO QNTEMPLATE (QNT_NAME,QNT_HTML,QNT_MODIFYDATETIME,QNT_MODIFYUSERNAME) 
VALUES ('Table Classic', 0x3C666F6E7420666163653D22617269616C2C68656C7665746963612C73616E732D7365726966222073697A653D2233223E3C7374726F6E673E255355424A454354253C2F7374726F6E673E3C2F666F6E743E3C6272202F3E3C6272202F3E3C7461626C652063656C6C73706163696E673D2230222063656C6C70616464696E673D2230222077696474683D223130302522206267636F6C6F723D22233333333333332220626F726465723D2230223E3C74626F64793E3C74723E3C74643E3C7461626C652063656C6C73706163696E673D2231222063656C6C70616464696E673D2231222077696474683D22313030252220626F726465723D2230223E3C74626F64793E3C74723E3C7464207374796C653D22424F524445522D544F503A20233333333333332031707820736F6C6964222076616C69676E3D22746F70222077696474683D2232302522206267636F6C6F723D2223666666666666223E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C3B206D736F2D666172656173742D666F6E742D66616D696C793A202754696D6573204E657720526F6D616E273B206D736F2D616E73692D6C616E67756167653A20454E2D55533B206D736F2D666172656173742D6C616E67756167653A20454E2D55533B206D736F2D626964692D6C616E67756167653A2041522D5341223E3C7374726F6E673E3C656D3E436F6E74656E743A3C2F656D3E3C2F7374726F6E673E3C2F7370616E3E3C2F74643E3C7464207374796C653D22424F524445522D544F503A20233333333333332031707820736F6C6964222076616C69676E3D22746F7022206267636F6C6F723D2223666666666666223E3C666F6E7420666163653D22617269616C2C68656C7665746963612C73616E732D7365726966222073697A653D2232223E25434F4E54454E54253C2F666F6E743E3C2F74643E3C2F74723E3C74723E3C74642076616C69676E3D22746F70222077696474683D2232302522206267636F6C6F723D2223666666666666223E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C3B206D736F2D666172656173742D666F6E742D66616D696C793A202754696D6573204E657720526F6D616E273B206D736F2D616E73692D6C616E67756167653A20454E2D55533B206D736F2D666172656173742D6C616E67756167653A20454E2D55533B206D736F2D626964692D6C616E67756167653A2041522D5341223E3C7374726F6E673E3C656D3E4174746163686D656E74733A3C2F656D3E3C2F7374726F6E673E3C2F7370616E3E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C3B206D736F2D666172656173742D666F6E742D66616D696C793A202754696D6573204E657720526F6D616E273B206D736F2D616E73692D6C616E67756167653A20454E2D55533B206D736F2D666172656173742D6C616E67756167653A20454E2D55533B206D736F2D626964692D6C616E67756167653A2041522D5341223E3C7374726F6E673E3C656D3E3C2F656D3E3C2F7374726F6E673E3C2F7370616E3E3C2F74643E3C74642076616C69676E3D22746F7022206267636F6C6F723D2223666666666666223E3C666F6E7420666163653D22617269616C2C68656C7665746963612C73616E732D7365726966222073697A653D2232223E2546494C454C495354253C2F666F6E743E3C2F74643E3C2F74723E3C74723E3C74642076616C69676E3D22746F70222077696474683D2232302522206267636F6C6F723D2223666666666666223E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C3B206D736F2D666172656173742D666F6E742D66616D696C793A202754696D6573204E657720526F6D616E273B206D736F2D616E73692D6C616E67756167653A20454E2D55533B206D736F2D666172656173742D6C616E67756167653A20454E2D55533B206D736F2D626964692D6C616E67756167653A2041522D5341223E3C7374726F6E673E3C656D3E417574686F723A3C2F656D3E3C2F7374726F6E673E3C2F7370616E3E3C2F74643E3C74642076616C69676E3D22746F7022206267636F6C6F723D2223666666666666223E3C666F6E7420666163653D22617269616C2C68656C7665746963612C73616E732D7365726966222073697A653D2232223E25555345524E414D45253C2F666F6E743E3C2F74643E3C2F74723E3C74723E3C7464207374796C653D22424F524445522D424F54544F4D3A20233333333333332031707820736F6C6964222076616C69676E3D22746F70222077696474683D2232302522206267636F6C6F723D2223666666666666223E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C3B206D736F2D666172656173742D666F6E742D66616D696C793A20222074696D65733D2274696D657322206E65773D226E6577223E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C3B206D736F2D666172656173742D666F6E742D66616D696C793A20222074696D65733D2274696D657322206E65773D226E6577223E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C3B206D736F2D666172656173742D666F6E742D66616D696C793A20222074696D65733D2274696D657322206E65773D226E6577223E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C3B206D736F2D666172656173742D666F6E742D66616D696C793A202754696D6573204E657720526F6D616E273B206D736F2D616E73692D6C616E67756167653A20454E2D55533B206D736F2D666172656173742D6C616E67756167653A20454E2D55533B206D736F2D626964692D6C616E67756167653A2041522D5341223E3C7374726F6E673E3C656D3E4D6F6469666965643A3C2F656D3E3C2F7374726F6E673E3C2F7370616E3E3C2F7370616E3E3C2F7370616E3E3C2F7370616E3E3C2F74643E3C7464207374796C653D22424F524445522D424F54544F4D3A20233333333333332031707820736F6C6964222076616C69676E3D22746F7022206267636F6C6F723D2223666666666666223E3C666F6E7420666163653D22617269616C2C68656C7665746963612C73616E732D7365726966222073697A653D2232223E254441544554494D45253C2F666F6E743E3C2F74643E3C2F74723E3C2F74626F64793E3C2F7461626C653E3C2F74643E3C2F74723E3C2F74626F64793E3C2F7461626C653E, NOW(), '%SUBSCRIBER_NAME%');

INSERT  INTO QNTEMPLATE (QNT_NAME,QNT_HTML,QNT_MODIFYDATETIME,QNT_MODIFYUSERNAME) 
VALUES ('Table Colorful', 0x3C7370616E207374796C653D22464F4E542D5745494748543A20626F6C643B20464F4E542D46414D494C593A20617269616C2C68656C7665746963612C73616E732D7365726966223E3C666F6E742073697A653D2233223E255355424A454354253C2F666F6E743E3C2F7370616E3E3C6272202F3E3C6272202F3E3C7461626C652063656C6C73706163696E673D2230222063656C6C70616464696E673D2231222077696474683D223130302522206267636F6C6F723D22236363363630302220626F726465723D2230223E3C74626F64793E3C74723E3C74643E3C7461626C652063656C6C73706163696E673D2231222063656C6C70616464696E673D2231222077696474683D22313030252220626F726465723D2230223E3C74626F64793E3C74723E3C74642076616C69676E3D22746F70222077696474683D2232302522206267636F6C6F723D2223666663633939223E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C3B206D736F2D666172656173742D666F6E742D66616D696C793A202754696D6573204E657720526F6D616E273B206D736F2D616E73692D6C616E67756167653A20454E2D55533B206D736F2D666172656173742D6C616E67756167653A20454E2D55533B206D736F2D626964692D6C616E67756167653A2041522D5341223E3C7374726F6E673E3C656D3E436F6E74656E743A3C2F656D3E3C2F7374726F6E673E3C2F7370616E3E3C2F74643E3C74642076616C69676E3D22746F7022206267636F6C6F723D2223666666326535223E3C666F6E7420666163653D22617269616C2C68656C7665746963612C73616E732D7365726966222073697A653D2232223E25434F4E54454E54253C2F666F6E743E3C2F74643E3C2F74723E3C74723E3C74642076616C69676E3D22746F70222077696474683D2232302522206267636F6C6F723D2223666663633939223E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C3B206D736F2D666172656173742D666F6E742D66616D696C793A202754696D6573204E657720526F6D616E273B206D736F2D616E73692D6C616E67756167653A20454E2D55533B206D736F2D666172656173742D6C616E67756167653A20454E2D55533B206D736F2D626964692D6C616E67756167653A2041522D5341223E3C7374726F6E673E3C656D3E4174746163686D656E74733A3C2F656D3E3C2F7374726F6E673E3C2F7370616E3E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C3B206D736F2D666172656173742D666F6E742D66616D696C793A202754696D6573204E657720526F6D616E273B206D736F2D616E73692D6C616E67756167653A20454E2D55533B206D736F2D666172656173742D6C616E67756167653A20454E2D55533B206D736F2D626964692D6C616E67756167653A2041522D5341223E3C7374726F6E673E3C656D3E3C2F656D3E3C2F7374726F6E673E3C2F7370616E3E3C2F74643E3C74642076616C69676E3D22746F7022206267636F6C6F723D2223666666326535223E3C666F6E7420666163653D22617269616C2C68656C7665746963612C73616E732D7365726966222073697A653D2232223E2546494C454C495354253C2F666F6E743E3C2F74643E3C2F74723E3C74723E3C74642076616C69676E3D22746F70222077696474683D2232302522206267636F6C6F723D2223666663633939223E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C3B206D736F2D666172656173742D666F6E742D66616D696C793A202754696D6573204E657720526F6D616E273B206D736F2D616E73692D6C616E67756167653A20454E2D55533B206D736F2D666172656173742D6C616E67756167653A20454E2D55533B206D736F2D626964692D6C616E67756167653A2041522D5341223E3C7374726F6E673E3C656D3E417574686F723A3C2F656D3E3C2F7374726F6E673E3C2F7370616E3E3C2F74643E3C74642076616C69676E3D22746F7022206267636F6C6F723D2223666666326535223E3C666F6E7420666163653D22617269616C2C68656C7665746963612C73616E732D7365726966222073697A653D2232223E25555345524E414D45253C2F666F6E743E3C2F74643E3C2F74723E3C74723E3C74642076616C69676E3D22746F70222077696474683D2232302522206267636F6C6F723D2223666663633939223E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C3B206D736F2D666172656173742D666F6E742D66616D696C793A20222074696D65733D2274696D657322206E65773D226E6577223E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C3B206D736F2D666172656173742D666F6E742D66616D696C793A20222074696D65733D2274696D657322206E65773D226E6577223E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C3B206D736F2D666172656173742D666F6E742D66616D696C793A20222074696D65733D2274696D657322206E65773D226E6577223E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C3B206D736F2D666172656173742D666F6E742D66616D696C793A202754696D6573204E657720526F6D616E273B206D736F2D616E73692D6C616E67756167653A20454E2D55533B206D736F2D666172656173742D6C616E67756167653A20454E2D55533B206D736F2D626964692D6C616E67756167653A2041522D5341223E3C7374726F6E673E3C656D3E4D6F6469666965643A3C2F656D3E3C2F7374726F6E673E3C2F7370616E3E3C2F7370616E3E3C2F7370616E3E3C2F7370616E3E3C2F74643E3C74642076616C69676E3D22746F7022206267636F6C6F723D2223666666326535223E3C666F6E7420666163653D22617269616C2C68656C7665746963612C73616E732D7365726966222073697A653D2232223E254441544554494D45253C2F666F6E743E3C2F74643E3C2F74723E3C2F74626F64793E3C2F7461626C653E3C2F74643E3C2F74723E3C2F74626F64793E3C2F7461626C653E, NOW(), '%SUBSCRIBER_NAME%');

INSERT  INTO QNTEMPLATE (QNT_NAME,QNT_HTML,QNT_MODIFYDATETIME,QNT_MODIFYUSERNAME) 
VALUES ('Table Elegant', 0x3C7370616E207374796C653D22464F4E542D5745494748543A20626F6C643B20464F4E542D46414D494C593A20617269616C2C68656C7665746963612C73616E732D7365726966223E3C666F6E742073697A653D2233223E255355424A454354253C2F666F6E743E3C2F7370616E3E3C6272202F3E3C6272202F3E3C7461626C65207374796C653D22424F524445522D52494748543A20233030303030302031707820646F75626C653B20424F524445522D544F503A20233030303030302031707820646F75626C653B20424F524445522D4C4546543A20233030303030302031707820646F75626C653B20424F524445522D424F54544F4D3A20233030303030302031707820646F75626C65222063656C6C73706163696E673D2230222063656C6C70616464696E673D2231222077696474683D22313030252220626F726465723D2230223E3C74626F64793E3C74723E3C74643E3C646976207374796C653D224241434B47524F554E443A2023333333333333223E3C7461626C652063656C6C73706163696E673D2231222063656C6C70616464696E673D2231222077696474683D22313030252220626F726465723D2230223E3C74626F64793E3C74723E3C74642076616C69676E3D22746F70222077696474683D2232302522206267636F6C6F723D2223666666666666223E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C3B206D736F2D666172656173742D666F6E742D66616D696C793A202754696D6573204E657720526F6D616E273B206D736F2D616E73692D6C616E67756167653A20454E2D55533B206D736F2D666172656173742D6C616E67756167653A20454E2D55533B206D736F2D626964692D6C616E67756167653A2041522D5341223E3C7374726F6E673E3C656D3E436F6E74656E743A3C2F656D3E3C2F7374726F6E673E3C2F7370616E3E3C2F74643E3C74642076616C69676E3D22746F7022206267636F6C6F723D2223666666666666223E3C666F6E7420666163653D22617269616C2C68656C7665746963612C73616E732D7365726966222073697A653D2232223E25434F4E54454E54253C2F666F6E743E3C2F74643E3C2F74723E3C74723E3C74642076616C69676E3D22746F70222077696474683D2232302522206267636F6C6F723D2223666666666666223E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C3B206D736F2D666172656173742D666F6E742D66616D696C793A202754696D6573204E657720526F6D616E273B206D736F2D616E73692D6C616E67756167653A20454E2D55533B206D736F2D666172656173742D6C616E67756167653A20454E2D55533B206D736F2D626964692D6C616E67756167653A2041522D5341223E3C7374726F6E673E3C656D3E4174746163686D656E74733A3C2F656D3E3C2F7374726F6E673E3C2F7370616E3E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C3B206D736F2D666172656173742D666F6E742D66616D696C793A202754696D6573204E657720526F6D616E273B206D736F2D616E73692D6C616E67756167653A20454E2D55533B206D736F2D666172656173742D6C616E67756167653A20454E2D55533B206D736F2D626964692D6C616E67756167653A2041522D5341223E3C7374726F6E673E3C656D3E3C2F656D3E3C2F7374726F6E673E3C2F7370616E3E3C2F74643E3C74642076616C69676E3D22746F7022206267636F6C6F723D2223666666666666223E3C666F6E7420666163653D22617269616C2C68656C7665746963612C73616E732D7365726966222073697A653D2232223E2546494C454C495354253C2F666F6E743E3C2F74643E3C2F74723E3C74723E3C74642076616C69676E3D22746F70222077696474683D2232302522206267636F6C6F723D2223666666666666223E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C3B206D736F2D666172656173742D666F6E742D66616D696C793A202754696D6573204E657720526F6D616E273B206D736F2D616E73692D6C616E67756167653A20454E2D55533B206D736F2D666172656173742D6C616E67756167653A20454E2D55533B206D736F2D626964692D6C616E67756167653A2041522D5341223E3C7374726F6E673E3C656D3E417574686F723A3C2F656D3E3C2F7374726F6E673E3C2F7370616E3E3C2F74643E3C74642076616C69676E3D22746F7022206267636F6C6F723D2223666666666666223E3C666F6E7420666163653D22617269616C2C68656C7665746963612C73616E732D7365726966222073697A653D2232223E25555345524E414D45253C2F666F6E743E3C2F74643E3C2F74723E3C74723E3C74642076616C69676E3D22746F70222077696474683D2232302522206267636F6C6F723D2223666666666666223E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C3B206D736F2D666172656173742D666F6E742D66616D696C793A20222074696D65733D2274696D657322206E65773D226E6577223E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C3B206D736F2D666172656173742D666F6E742D66616D696C793A20222074696D65733D2274696D657322206E65773D226E6577223E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C3B206D736F2D666172656173742D666F6E742D66616D696C793A20222074696D65733D2274696D657322206E65773D226E6577223E3C7370616E206C616E673D22454E2D555322207374796C653D22464F4E542D53495A453A20313070743B20464F4E542D46414D494C593A20417269616C3B206D736F2D666172656173742D666F6E742D66616D696C793A202754696D6573204E657720526F6D616E273B206D736F2D616E73692D6C616E67756167653A20454E2D55533B206D736F2D666172656173742D6C616E67756167653A20454E2D55533B206D736F2D626964692D6C616E67756167653A2041522D5341223E3C7374726F6E673E3C656D3E4D6F6469666965643A3C2F656D3E3C2F7374726F6E673E3C2F7370616E3E3C2F7370616E3E3C2F7370616E3E3C2F7370616E3E3C2F74643E3C74642076616C69676E3D22746F7022206267636F6C6F723D2223666666666666223E3C666F6E7420666163653D22617269616C2C68656C7665746963612C73616E732D7365726966222073697A653D2232223E254441544554494D45253C2F666F6E743E3C2F74643E3C2F74723E3C2F74626F64793E3C2F7461626C653E3C2F6469763E3C2F74643E3C2F74723E3C2F74626F64793E3C2F7461626C653E, NOW(), '%SUBSCRIBER_NAME%');