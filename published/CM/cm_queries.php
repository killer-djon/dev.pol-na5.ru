<?php

	//
	// Contact Manager queries
	//

	$qr_cm_updatConcactLocation = "UPDATE CONTACT SET C_MODIFYDATETIME=now(), C_MODIFYUSERNAME='!C_MODIFYUSERNAME!', CF_ID='!CF_ID!' WHERE C_ID='!C_ID!'";

	$qr_cm_getFolderUserCount = "SELECT COUNT(*) FROM WBS_USER U, CONTACT C WHERE C.C_ID=U.C_ID AND C.CF_ID='!CF_ID!'";

	$qr_cm_selectFolderUsers = "SELECT U.U_ID, C.C_ID FROM WBS_USER U, CONTACT C WHERE C.C_ID=U.C_ID AND C.CF_ID='!CF_ID!'";

	$qr_cm_resetContactFolder = "UPDATE CONTACT SET CF_ID=NULL WHERE C_ID='!C_ID!'";

	$qr_cm_updateContactFolder = "UPDATE CONTACT SET CF_ID='!CF_ID!' WHERE C_ID='!C_ID!'";

	$qr_cm_selectFolderContacts = "SELECT DL.*, U.U_ID FROM CONTACT DL LEFT JOIN WBS_USER U ON U.C_ID=DL.C_ID WHERE DL.CF_ID='!CF_ID!' AND (U.U_STATUS IS NULL OR U.U_STATUS <> 1) ORDER BY %s %s";

	$qr_cm_selectContactColumns = "SHOW COLUMNS FROM CONTACT";

	$qr_cm_updateContactTypeSettings = "UPDATE CTYPE SET CT_SETTINGS='!CT_SETTINGS!' WHERE CT_ID='!CT_ID!'";

	$qr_cm_createField = "ALTER TABLE CONTACT ADD COLUMN %s %s";

	$qr_cm_alterField = "ALTER TABLE CONTACT CHANGE COLUMN %s %s %s";

	$qr_cm_deleteField = "ALTER TABLE CONTACT DROP COLUMN %s";

	/*$qr_cm_selectAvailableContacts = "SELECT CN.C_ID, CN.C_FIRSTNAME, CN.C_MIDDLENAME, CN.C_LASTNAME, CN.C_NICKNAME, CN.C_EMAILADDRESS, CF.CF_NAME,
										(SELECT IF ( UA.AR_VALUE IS NULL, 0, UA.AR_VALUE ) |IF ( (SELECT BIT_OR( UG.AR_VALUE ) FROM UG_ACCESSRIGHTS AS UG, UGROUP_USER AS UGU WHERE UG.AR_ID=UGU.UG_ID AND UGU.U_ID=UA.AR_ID AND UA.AR_OBJECT_ID=UG.AR_OBJECT_ID AND UA.AR_PATH=UG.AR_PATH GROUP BY UGU.U_ID) IS NULL, 0, (SELECT BIT_OR( UG.AR_VALUE ) FROM UG_ACCESSRIGHTS AS UG, UGROUP_USER AS UGU WHERE UG.AR_ID=UGU.UG_ID AND UGU.U_ID=UA.AR_ID AND UA.AR_OBJECT_ID=UG.AR_OBJECT_ID AND UA.AR_PATH=UG.AR_PATH GROUP BY UGU.U_ID) ) FROM U_ACCESSRIGHTS AS UA WHERE AR_ID='!U_ID!' AND AR_PATH='/ROOT/CM/FOLDERS' AND AR_OBJECT_ID=CF.CF_ID) AS TREE_ACCESS_RIGHTS FROM ( CONTACT CN, CFOLDER CF )
										LEFT JOIN WBS_USER U ON U.C_ID=CN.C_ID WHERE CF.CF_ID=CN.CF_ID HAVING TREE_ACCESS_RIGHTS > 0 ORDER BY %s";*/
/*
	$qr_cm_selectAvailableContacts =  "SELECT CN.C_ID, CN.C_FIRSTNAME, CN.C_MIDDLENAME, CN.C_LASTNAME, CN.C_NICKNAME, CN.C_EMAILADDRESS, CF.CF_NAME	FROM ( CONTACT CN, CFOLDER CF ) WHERE CF.CF_ID=CN.CF_ID AND CN.C_ID NOT IN ('!EXCLUD!') AND CF.CF_ID IN ( SELECT UA.AR_OBJECT_ID FROM U_ACCESSRIGHTS AS UA, WBS_USER AS U WHERE UA.AR_ID=U.U_ID AND U.U_ID='!U_ID!' AND UA.AR_PATH='/ROOT/CM/FOLDERS' AND UA.AR_VALUE>0  UNION  SELECT UGA.AR_OBJECT_ID FROM UG_ACCESSRIGHTS AS UGA, UGROUP_USER AS UGU WHERE UGA.AR_ID=UGU.UG_ID AND UGU.U_ID='!U_ID!' AND UGA.AR_PATH='/ROOT/CM/FOLDERS' AND UGA.AR_VALUE>0) 	ORDER BY %s ";
*/

$qr_cm_selectAvailableContacts = <<< QUERY
SELECT
    CONTACT.C_ID,
    CONTACT.C_FIRSTNAME,
    CONTACT.C_MIDDLENAME,
    CONTACT.C_LASTNAME,
    CONTACT.C_NICKNAME,
    CONTACT.C_EMAILADDRESS,
    CFOLDER.CF_NAME
FROM
    CONTACT
    JOIN CFOLDER ON (CFOLDER.CF_ID = CONTACT.CF_ID)
    
WHERE
    CONTACT.C_ID %s IN (%s) AND
    CFOLDER.CF_ID IN (
        SELECT
            U_ACCESSRIGHTS.AR_OBJECT_ID
        FROM
            U_ACCESSRIGHTS,
            WBS_USER
        WHERE
            U_ACCESSRIGHTS.AR_ID = WBS_USER.U_ID AND
            WBS_USER.U_ID = '!U_ID!' AND
            U_ACCESSRIGHTS.AR_PATH = '/ROOT/CM/FOLDERS' AND
            U_ACCESSRIGHTS.AR_VALUE > 0

        UNION

        SELECT
            UG_ACCESSRIGHTS.AR_OBJECT_ID
        FROM
            UG_ACCESSRIGHTS,
            UGROUP_USER
        WHERE
            UG_ACCESSRIGHTS.AR_ID = UGROUP_USER.UG_ID AND
            UGROUP_USER.U_ID = '!U_ID!' AND
            UG_ACCESSRIGHTS.AR_PATH = '/ROOT/CM/FOLDERS' AND
            UG_ACCESSRIGHTS.AR_VALUE > 0
    )
ORDER BY %s
%s
QUERY;


	$qr_cm_selectAvailableContactsCount = 
	"SELECT count(*) as count 
	FROM ( CONTACT CN, CFOLDER CF ) 
	WHERE CF.CF_ID=CN.CF_ID AND 
	    CF.CF_ID IN (SELECT UA.AR_OBJECT_ID FROM U_ACCESSRIGHTS AS UA, WBS_USER AS U WHERE UA.AR_ID=U.U_ID AND U.U_ID='!U_ID!' AND UA.AR_PATH='/ROOT/CM/FOLDERS' AND UA.AR_VALUE>0 UNION SELECT UGA.AR_OBJECT_ID FROM UG_ACCESSRIGHTS AS UGA, UGROUP_USER AS UGU WHERE UGA.AR_ID=UGU.UG_ID AND UGU.U_ID='!U_ID!' AND UGA.AR_PATH='/ROOT/CM/FOLDERS' AND UGA.AR_VALUE>0)";

$qr_cm_selectAvailableContactsGlobal = <<< QUERY
SELECT
    CN.C_ID,
    CN.C_FIRSTNAME,
    CN.C_MIDDLENAME,
    CN.C_LASTNAME,
    CN.C_NICKNAME,
    CN.C_EMAILADDRESS,
    CF.CF_NAME,
    7 as TREE_ACCESS_RIGHTS
FROM
    ( CONTACT CN, CFOLDER CF )
    LEFT JOIN WBS_USER U ON U.C_ID=CN.C_ID

WHERE
    CONTACT.C_ID %s IN (%s) AND
    CF.CF_ID = CN.CF_ID
ORDER BY %s
%s
QUERY;
        $qr_cm_selectAvailableContactsGlobalCount = "SELECT count(*) as count FROM ( CONTACT CN, CFOLDER CF ) LEFT JOIN WBS_USER U ON U.C_ID=CN.C_ID WHERE CF.CF_ID=CN.CF_ID ORDER BY %s";

	$qr_cm_getUserEmail = "SELECT C.C_EMAILADDRESS FROM CONTACT C, WBS_USER U WHERE U.U_ID='!U_ID!' AND C.C_ID=U.C_ID";

	$qr_cm_selectfolderdocnum = "SELECT COUNT(*) FROM CONTACT C LEFT JOIN WBS_USER U ON U.C_ID=C.C_ID WHERE CF_ID='!CF_ID!' AND C_STATUS=0 AND (U.U_STATUS IS NULL OR U.U_STATUS <> 1)";

	$qr_cm_selectUserDocumentCount = "SELECT COUNT(*) FROM ( TREE_DOCUMENT_TABLE DL, TREE_FOLDER_TABLE DF, TREE_ACCESS_TABLE DA ) LEFT JOIN WBS_USER U ON U.C_ID=DL.C_ID WHERE DA.FOLDER_ID_FIELD = DF.FOLDER_ID_FIELD AND DA.USER_ID_FIELD='!U_ID!' AND DL.FOLDER_ID_FIELD = DF.FOLDER_ID_FIELD AND (U.U_STATUS IS NULL OR U.U_STATUS <> 1)";

	$qr_cm_selectUserSummaryDocumentCount = "SELECT COUNT(DISTINCT DL.DOCUMENT_ID_FIELD) FROM ( TREE_DOCUMENT_TABLE DL, TREE_GROUP_ACCESS_TABLE DGA, UGROUP_USER UGU ) LEFT JOIN WBS_USER U ON U.C_ID=DL.C_ID WHERE UGU.U_ID='!U_ID!' AND DGA.GROUP_ID_FIELD=UGU.UG_ID AND DL.FOLDER_ID_FIELD = DGA.FOLDER_ID_FIELD AND (U.U_STATUS IS NULL OR U.U_STATUS <> 1)";

	$qr_cm_selectAllUserDocuments = "SELECT DL.DOCUMENT_ID_FIELD FROM ( TREE_DOCUMENT_TABLE DL, TREE_FOLDER_TABLE DF, TREE_ACCESS_TABLE DA ) LEFT JOIN WBS_USER U ON U.C_ID=DL.C_ID WHERE DA.FOLDER_ID_FIELD = DF.FOLDER_ID_FIELD AND DA.USER_ID_FIELD='!U_ID!' AND DL.FOLDER_ID_FIELD = DF.FOLDER_ID_FIELD AND (U.U_STATUS IS NULL OR U.U_STATUS <> 1)";

	$qr_cm_selectAllUserSummaryDocuments = "SELECT DISTINCT DL.DOCUMENT_ID_FIELD FROM ( TREE_DOCUMENT_TABLE DL, TREE_GROUP_ACCESS_TABLE DGA, UGROUP_USER UGU ) LEFT JOIN WBS_USER U ON U.C_ID=DL.C_ID WHERE UGU.U_ID='!U_ID!' AND DGA.GROUP_ID_FIELD=UGU.UG_ID AND DL.FOLDER_ID_FIELD = DGA.FOLDER_ID_FIELD AND (U.U_STATUS IS NULL OR U.U_STATUS <> 1)";

	$qr_cm_selectContactsData = "SELECT * FROM CONTACT";

	$qr_cm_findSubscriberByEmail = "SELECT C.C_ID FROM CONTACT C WHERE (C_SUBSCRIBER=-1 OR C_SUBSCRIBER=1) AND LOWER(C_EMAILADDRESS)='!C_EMAILADDRESS!'";

	$qr_cm_updateProfileFileds = "UPDATE CONTACT SET C_FIRSTNAME='!C_FIRSTNAME!', C_LASTNAME='!C_LASTNAME!', C_MODIFYUSERNAME='!C_MODIFYUSERNAME!', C_MODIFYDATETIME=NOW() WHERE C_ID='!C_ID!'";

	// Unsubscribed emails
	//
	$qr_cm_selectUnsubscribedEmails = "SELECT * FROM UNSUBSCRIBER %s ORDER BY %s";

	//
	// Reports
	$qr_cm_rep_selectActiveSubscribersByDate = "SELECT COUNT(*) AS CNT, C_CREATEDATETIME FROM CONTACT WHERE C_SUBSCRIBER=1 AND C_CREATEDATETIME > ( DATE_SUB(NOW(), INTERVAL %s DAY) ) GROUP BY C_CREATEDATETIME ORDER BY C_CREATEDATETIME";

	$qr_cm_rep_selectInctiveSubscribersByDate = "SELECT COUNT(*) AS CNT, C_CREATEDATETIME FROM CONTACT WHERE C_SUBSCRIBER=-1 AND C_CREATEDATETIME > ( DATE_SUB(NOW(), INTERVAL %s DAY) ) GROUP BY C_CREATEDATETIME ORDER BY C_CREATEDATETIME";

	$qr_cm_rep_selectActiveSubscribersByDateRange = "SELECT COUNT(*) AS CNT, C_CREATEDATETIME FROM CONTACT WHERE C_SUBSCRIBER=1 AND C_CREATEDATETIME >= '%s' AND C_CREATEDATETIME <= '%s' GROUP BY C_CREATEDATETIME ORDER BY C_CREATEDATETIME";

	$qr_cm_rep_selectInctiveSubscribersByDateRange = "SELECT COUNT(*) AS CNT, C_CREATEDATETIME FROM CONTACT WHERE C_SUBSCRIBER=-1 AND C_CREATEDATETIME >= '%s' AND C_CREATEDATETIME <= '%s' GROUP BY C_CREATEDATETIME ORDER BY C_CREATEDATETIME";
	
	global $qr_cm_updateFolderWidgets;
	$qr_cm_updateFolderWidgets = "UPDATE WG_PARAM SET WGP_VALUE='!NEW_FOLDER_ID!' WHERE WGP_VALUE='!OLD_FOLDER_ID!' AND WGP_NAME='FOLDER' AND WG_ID IN (SELECT WG_ID FROM WG_WIDGET WHERE WT_ID='SBSC')";
?>