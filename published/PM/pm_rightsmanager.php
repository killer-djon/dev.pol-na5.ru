<?php

	define( 'PM_SELECTED_MANAGER', 'SELECTED_MANAGER' );

	/**
	 * pm_rightsManager manages the user access rights to the PM projects
	 */
	class pm_rightsManager extends UR_RightsObject
	{
		var $selectProjectListQuery = "SELECT P.P_ID, P.P_DESC, P.P_ENDDATE, C.C_NAME, (P_ENDDATE <> '' and P_ENDDATE <= CURDATE()) as CLOSED FROM PROJECT P, CUSTOMER C WHERE P.C_ID = C.C_ID AND C.C_STATUS = 0 ORDER BY (P_ENDDATE <> '' and P_ENDDATE <= CURDATE()), C.C_NAME, P.P_DESC";

		var $selectProjectManager = "SELECT U_ID_MANAGER FROM PROJECT WHERE P_ID='!P_ID!'";
		var $selectManagerProjects = "SELECT P_ID FROM PROJECT WHERE U_ID_MANAGER='!U_ID_MANAGER!'";

		/**
		 * Keeps the array of the project managers
		 * @var array
		 */
		var $__managerCache = array();

		/**
		 * Defines the possible values of user rights agains the projects
		 * @var array
		 */
		var $__rightsArr = array( UR_TREE_READ=>0, UR_TREE_WRITE=>0, UR_TREE_FOLDER=>0 );

		/**
		 * Defines the names of user rights
		 * @var array
		 */
		var $__rightsNames = array( UR_TREE_READ=>'app_rightview_name', UR_TREE_WRITE=>'app_righttask_name', UR_TREE_FOLDER=>'app_rightproject_name' );

		/**
		 * PRIVATE: Values for saving
		 *
		 * @var array
		 */
		var $__values = array();

		function __RenderStart( &$data )
		{
/*			$numcols = $this->__root->__renderOptions["NUMCOLS"];
			$fullPath = $this->GetFullPath();
			$offset = UR_CONTAINER_OFFSET;

			$ret = "<tr class='RightsSubContainer'><th scope='' colspan=$numcols><div style='margin-left: ". $offset ."px;'>". Localization::get( $this->__name, $this->__application );
			$ret .= "<script language=JavaScript> var appRightsControl". $this->__application." = new new_appRightsContrainer(); </script>";
			$ret .= "</div></th></tr>\n";

			return $ret; */

			return "<script language=JavaScript> var appRightsControl". $this->__application." = new new_appRightsContrainer(); </script>";
		}

		function __RenderFinish( &$data )
		{
			$projectList = $this->__GetProjectsList( );
			if ( PEAR::isError( $projectList ) )
				return $projectList;

			$ids = $this->__root->__renderOptions[UR_ID];
			if ( !is_array( $ids ) )
				$ids = array( 0=>$ids );

			$ret = $this->__RenderHeader( );

			if ( PEAR::isError( $result = $this->__RenderProjects( $projectList, $ids, $data ) ) )
				return $result;

			$ret .= $result;

			return $ret;
		}

		/**
		 * Returns the list of projects
		 *
		 * @return array Projects list
		 */
		function __GetProjectsList( )
		{
			$res = db_query( $this->selectProjectListQuery, array( ) );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( Localization::get( ERR_QUERYEXECUTING ) );

			$projectList = array();

			while ( $row = db_fetch_array( $res ) )
				$projectList[$row["P_ID"]] = $row;

			db_free_result($res);

			return $projectList;
		}

		function __RenderHeader( )
		{
			$ret = "";
			$numcols = $this->__root->__renderOptions["NUMCOLS"];
			$fullPath = $this->GetFullPath();
			$offset = (substr_count($fullPath,UR_DELIMITER) - 2)*UR_CONTAINER_OFFSET;

			$rVal = $this->__rightsArr;

			if ( $this->__root->__renderOptions[UR_ACTION] == UR_ACTION_EDITGROUP || $this->__root->__renderOptions[UR_ACTION] == UR_ACTION_EDITUSER )
 			{
				$id = $this->__root->__renderOptions[UR_ID];

//				$ret = "<tr><td colspan=$numcols class=\"RightsSeparator\">&nbsp;</td></tr>";

				$ret .= "<tr class='RightsSubContainer'><th><div style='margin-left: ". $offset ."px;'>".Localization::get('app_availprojects_title', $this->__application)."</div></th><th>";

				if ( $this->__root->__renderOptions[UR_ACTION] == UR_ACTION_EDITUSER)
 					$disabled = $this->__root->IsUserGlobalAdministrator( $this->__root->__renderOptions[UR_ID] ) ? " disabled " : "";
				else
					if ( $this->__root->__renderOptions[UR_ACTION] == UR_ACTION_EDITGROUP )
					{
						$disabled = $this->__root->CheckMask( $this->__root->GetGroupRightValue( $this->__root->__renderOptions[UR_ID], $this->MakePath( array( UR_ROOT, UR_ADMINISTRATOR ) ) ), UR_FULL_RIGHTS ) ?  " disabled " : "";
					}

 				$ret .= "<table width=100% cellsapcing=0 cellpadding=0 class='RightsTable'><tr>";

 				foreach( $rVal as $key=>$value )
					$ret .= "<td  width=33% class=\"AlignCenter\"><script language=JavaScript>toggleCbRightsControl{$this->__root->__renderOptions[UR_FIELD]}.addCheckbox( 'userRightsCBAll$key{$this->__application}') </script><div class=\"RightCbColumn\"><input onClick='appRightsControl".$this->__application.".setAppRights(this, $key)' type=checkbox id='userRightsCBAll$key{$this->__application}' name='userRightsCBAll[$key]' value='1' $disabled><br><span>".Localization::get($this->__rightsNames[$key], $this->__application)."</span></div></td>";

				$ret .= "</tr>\n</table></td>";

				if ( $numcols > 2 )
					$ret .= "<td></td>";

				$ret .= "</tr>\n";
 			} else
				$ret .= "<tr class='RightsSubContainer'><th colspan='$numcols'><div class=noPadding style='margin-left: ".$offset."px!important;'>".Localization::get('app_availprojects_title', $this->__application)."</div></th></tr>";

 			return $ret;
		}

		function __RenderProjects( &$projectList, $ids, &$data )
		{
			$ret = "";

			$edited = $this->__root->__renderOptions[UR_EDITED];
			$fullPath = $this->GetFullPath();
			$offset = (substr_count($fullPath,UR_DELIMITER) - 1)*UR_CONTAINER_OFFSET;
			$numcols = $this->__root->__renderOptions["NUMCOLS"];

			if ( count($projectList) )
			{
				foreach( $projectList as $project_id=>$row )
				{

					$name = prepareStrToDisplay($row["P_DESC"]);

					$ret .= "<tr>";
					$ret .= "<th scope='col' ><div style='margin-left: ".$offset."px;'>$name</div></th>";

					foreach( $ids as $id )
					{
						switch( $this->__root->__renderOptions[UR_ACTION] )
						{
							case UR_ACTION_EDITGROUP:
								$admin = $this->__root->CheckMask( $this->__root->GetGroupRightValue( $id, $this->MakePath( array( UR_ROOT, UR_ADMINISTRATOR ) ) ), UR_FULL_RIGHTS );

								$ret .= $this->__showCheckBoxes( $name, $id, $project_id, $data, UR_GROUP_ID, $edited, $admin );
								break;

							case UR_ACTION_EDITUSER:

								$admin = $this->__root->IsUserGlobalAdministrator( $id );

								$ret .= $this->__showCheckBoxes( $name, $id, $project_id, $data, UR_USER_ID, $edited, $admin );
								break;

							case UR_ACTION_VIEWUSER:

								$admin = $this->__root->IsUserGlobalAdministrator( $id );

								$ret .= $this->__showViewOption( $name, $id, $project_id, UR_USER_ID );

								break;

							case UR_ACTION_VIEWGROUP:

								$ret .= $this->__showViewOption( $name, $id, $project_id, UR_GROUP_ID );

								break;
						}
					}

					$ret .= "</tr>\n";
				}
			} else
			{
				$ret = "<tr class=\"NoRecords\"><td colspan=\"$numcols\" class=\"AlignCenter\">".prepareStrToDisplay(Localization::get('app_noprojects_label', $this->__application), true, true)."</td></tr>";
			}

			return $ret;
		}

		/**
		 * Render checkboxes for projects
		 *
		 * @param string $name Object Name
		 * @param string $id User or Group ID
		 * @param string $project_id Project ID
		 * @param array $data Data array
		 * @param string $type IDT_USER, IDT_GROUP
		 * @param string $edited Edited flag
		 * @return string or PEAR::Error
		 */
		function __showCheckBoxes( $name, $id, $project_id, &$data, $type, $edited, $admin = false )
		{
			$projectPath = $this->GetFullPath( ).UR_DELIMITER.$project_id;

			$valueRow = $this->__root->__GetRightValue( $id, $projectPath, $type );
			if ( PEAR::isError( $valueRow ) )
				return $valueRow;

			$rVal = $this->__rightsArr;

			if ( !$edited || $admin )
			{
				$value = is_null( $valueRow ) ? 0 : $valueRow[UR_VALUE];

				if ( $this->CheckMask( $value, UR_TREE_READ ) )
					$rVal[UR_TREE_READ] = 1;

				if ( $this->CheckMask( $value, UR_TREE_WRITE ) )
					$rVal[UR_TREE_WRITE] = 1;

				if ( $this->CheckMask( $value, UR_TREE_FOLDER ) )
					$rVal[UR_TREE_FOLDER] = 1;
			}
			else
			{
				if ( isset( $data[$id][$projectPath] ) )
					foreach( $data[$id][$projectPath] as $key=>$value )
						$rVal[intval($key)] = $value;
			}

			$ret = "<td>";

			$isProjMan = $this->getProjectManager($project_id) == $id;
			$ret .= "<table width=100% cellsapcing=0 cellpadding=0 class='RightsTable'><tr>";

			$disabledStr = $admin ? " disabled" : "";

			if ( !$isProjMan )
			{
				foreach( $rVal as $key=>$value )
				{
						$checked = ( $value == 1 ) ? " checked" : "";
						$ret .= "<td class=\"AlignCenter\"><script language=JavaScript> appRightsControl".$this->__application.".addCheckbox( $key, '".$this->__root->__renderOptions[UR_FIELD]."[$id][$projectPath][$key]'); toggleCbRightsControl{$this->__root->__renderOptions[UR_FIELD]}.addCheckbox( '".$this->__root->__renderOptions[UR_FIELD]."[$id][$projectPath][$key]') </script><div class=\"RightCbColumn\"><input type=checkbox name='".$this->__root->__renderOptions[UR_FIELD]."[$id][$projectPath][$key]' id='".$this->__root->__renderOptions[UR_FIELD]."[$id][$projectPath][$key]' value='1' $checked onClick=\"new_updateMultiAppFolderCb( this, '".$this->__root->__renderOptions[UR_FIELD]."', '$id', '$projectPath', $key )\" $disabledStr></div></td>";
				}
			} else {
				$ret .= "<td colspan=\"3\" class=\"AlignCenter\">";
				$ret .= Localization::get("app_projmanager_label", $this->__application);
				foreach( $rVal as $key=>$value )
				{
					$ret .= "<input type=hidden name='".$this->__root->__renderOptions[UR_FIELD]."[$id][$projectPath][$key]' id='".$this->__root->__renderOptions[UR_FIELD]."[$id][$projectPath][$key]' value='1'\>";
				}
				$ret .= "</td>";
			}

			$ret .= "</tr>";

			$ret .= "\n</table></td>";

			if ( $type == UR_USER_ID )
			{
				$ret .= "<td class=\"AlignCenter\">";

				$value = is_null( $valueRow ) ? 0 : $valueRow[UR_VIEWGROUPVALUE];

				$res = $this->CheckMask( $value, UR_TREE_READ ) ? substr(Localization::get($this->__rightsNames[UR_TREE_READ], $this->__application), 0, 1) : "";
				$res .= $this->CheckMask( $value, UR_TREE_WRITE ) ? substr(Localization::get($this->__rightsNames[UR_TREE_WRITE], $this->__application), 0, 1) : "";
				$res .= $this->CheckMask( $value, UR_TREE_FOLDER ) ? substr(Localization::get($this->__rightsNames[UR_TREE_FOLDER], $this->__application), 0, 1) : "";

				$ret .= $res =="" ? "-" : $res;
				$ret .= "</td>";
			}

			return $ret;
		}


		/**
		 * Render option for view only
		 *
		 * @param string $name Object Name
		 * @param string $id User or Group ID
		 * @param string $type IDT_USER, IDT GROUP
		 * @return string or PEAR::Error
		 */
		function __showViewOption( $name, $id, $project_id, $type )
		{
			$projectPath = $this->GetFullPath( ).UR_DELIMITER.$project_id;

			$ret = "<td class=\"AlignCenter\">";

			$result = null;

			if ( $type == UR_USER_ID )
				$result = $this->__root->GetUserRightValue( $id, $projectPath );
			else
			if ( $type == UR_GROUP_ID )
				$result = $this->__root->GetGroupRightValue( $id, $projectPath );

			if ( PEAR::isError( $result ) )
				return $result;

			$ProjMan = $this->getProjectManager($project_id);
			if ($ProjMan != $id)
			{
				$tret = "";
				$tret .= $this->CheckMask( $result, UR_TREE_READ ) ? substr(Localization::get($this->__rightsNames[UR_TREE_READ], $this->__application), 0, 1) : "";
				$tret .= $this->CheckMask( $result, UR_TREE_WRITE ) ? substr(Localization::get($this->__rightsNames[UR_TREE_WRITE], $this->__application), 0, 1) : "";
				$tret .= $this->CheckMask( $result, UR_TREE_FOLDER ) ? substr(Localization::get($this->__rightsNames[UR_TREE_FOLDER], $this->__application), 0, 1) : "";

				$ret .= strlen( $tret ) ? $tret : "-";
			} else
				$ret .= Localization::get("app_projmanager_label", $this->__application);

			$ret .= "</td>";

			return $ret;
		}

		function SaveItem( &$data )
		{
			$items = $this->__root->__cachedItems;
			$edited = $this->__root->__renderOptions[UR_EDITED];

			$folderPath = $this->__root->__renderOptions[UR_PATH] .UR_DELIMITER. $this->__root->__renderOptions[UR_OBJECTID];

			if ( !$edited )
				return false;

			foreach( $items as $id=>$row )
			{
				$value = isset( $data[$id][$folderPath] ) ? $data[$id][$folderPath] : null;

				$value = 0;
				foreach( array_keys( $this->__rightsArr ) as $key )
						$value |= isset( $data[$id][$folderPath]["$key"] ) ? intval( $key ): 0;

				switch( $this->__root->__renderOptions[UR_ACTION] )
				{
						case UR_ACTION_EDITGROUP:
							$type = UR_GROUP_ID;
							break;

						case UR_ACTION_EDITUSER:
							$type = UR_USER_ID;
							break;
				}

				$savePath  = ( $this->__root->__renderOptions[UR_OBJECTID] != UR_SYS_ID ) ? $folderPath : $this->__root->__renderOptions[UR_PATH] .UR_DELIMITER. $this->__root->__renderOptions[UR_REAL_ID];
				$ret = $this->__root->__SaveRightValue( $savePath, $id, $type, $value );

				if ( PEAR::isError( $ret ) )
					return $ret;
			}

			return true;
		}

		function __RenderItemHeader( )
		{
			$ret = "";
			$rVal = $this->__rightsArr;

			$ret = "<thead><tr>";

			if ( $this->__root->__renderOptions[UR_ACTION] == UR_ACTION_EDITUSER )
				$ret .= "<th>".Localization::get('app_treename_title')."</th><th align=left>".Localization::get('app_treeuserid_title')."</th>";
			else
				$ret .= "<th>".Localization::get('app_treegroupname_label')."</th>";

			foreach( $rVal as $key=>$value )
				$ret .= "<th width=\"20%\"><nobr><input onClick='appRightsControl".$this->__application.$this->__root->__renderOptions[UR_ACTION].".setAppRights(this, $key)' type=checkbox name='userRightsCBAll[$key]' value='1'>".Localization::get($this->__rightsNames[$key], $this->__application)."</nobr></th>";

			$ret .= "</tr></thead>\n";
			return $ret;
		}

		function RenderItem( &$data )
		{
			$items = $this->__root->__cachedItems;
			$edited = $this->__root->__renderOptions[UR_EDITED];

			$folderPath = $this->__root->__renderOptions[UR_PATH] .UR_DELIMITER. $this->__root->__renderOptions[UR_OBJECTID];

			$render = "<script language=JavaScript> var appRightsControl". $this->__application.$this->__root->__renderOptions[UR_ACTION]." = new new_appRightsContrainer(); </script>";

			$render .= "<table width=100% class='SimpleList RightsItemTable'>";

			$render .= $this->__RenderItemHeader();
			$isManager = false;

			foreach( $items as $id=>$valueRow )
			{
				if ( isset($data[PM_SELECTED_MANAGER]) && $this->__root->__renderOptions[UR_ACTION] == UR_ACTION_EDITUSER )
					$isManager = $data[PM_SELECTED_MANAGER] == $id;

				if ( $this->__root->__renderOptions[UR_ACTION] == UR_ACTION_EDITUSER )
					$render .= "<tr><td>".$valueRow[UR_ITEMNAME]."</td><td>".$valueRow['CODE']."</td>";
				else
					$render .= "<tr><td>".$valueRow[UR_ITEMNAME]."</td>";


				$rVal = $this->__rightsArr;

				if ( !$edited )
				{
					$value = is_null( $valueRow ) ? 0 : $valueRow[UR_VALUE];

					if ( ( $value & UR_TREE_READ ) == UR_TREE_READ)
						$rVal[UR_TREE_READ] = 1;

					if ( ( $value & UR_TREE_WRITE ) == UR_TREE_WRITE)
						$rVal[UR_TREE_WRITE] = 1;

					if ( ( $value & UR_TREE_FOLDER ) == UR_TREE_FOLDER )
						$rVal[UR_TREE_FOLDER] = 1;
				}
				else
				{
					if ( isset( $data[$id][$folderPath] ) )
						foreach( $data[$id][$folderPath] as $key=>$value )
							$rVal[intval($key)] = $value;

				}

				if ( !$isManager )
				foreach( $rVal as $key=>$value )
				{
					$checked = ( $value == 1 ) ? " checked" : "";
					$render .= "<td><script language=JavaScript> appRightsControl".$this->__application.$this->__root->__renderOptions[UR_ACTION].".addCheckbox( $key, '".$this->__root->__renderOptions[UR_FIELD]."[$id][$folderPath][$key]') </script><input type=checkbox name='".$this->__root->__renderOptions[UR_FIELD]."[$id][$folderPath][$key]' id='".$this->__root->__renderOptions[UR_FIELD]."[$id][$folderPath][$key]' value='1' $checked onClick=\"new_updateMultiAppFolderCb( this, '".$this->__root->__renderOptions[UR_FIELD]."', '$id', '$folderPath', $key )\"></td>";
				} else
				{

					$render .= "<td colspan=\"3\" class=\"AlignCenter\">";
					$render .= Localization::get("app_projmanager_label", $this->__application);
					foreach( $rVal as $key=>$value )
					{
						$render .= "<input type=hidden name='".$this->__root->__renderOptions[UR_FIELD]."[$id][$folderPath][$key]' id='".$this->__root->__renderOptions[UR_FIELD]."[$id][$folderPath][$key]' value='1'\>";
					}
					$render .= "</td>";
				}

				$render .= "</tr>";
			}

			$render .= "</table>";

			return $render;
		}

		/**
		 * Grants or Revokes Projects Permissions
		 *
		 * @param string $ID
		 * @param string $IDtype
		 * @param string $action
		 */
		function __SetGlobalRights( $ID, $IDtype, $action )
		{
			$projectsList = $this->__GetProjectsList( );
			if ( PEAR::isError( $projectsList ) )
				return $projectsList;

			foreach( $projectsList as $project_id=>$row )
			{
				$projectPath = $this->GetFullPath( ).UR_DELIMITER.$project_id;

				$ret = $this->__root->__SaveRightValue( $projectPath, $ID, $IDtype, ( $action == UR_GRANT ) ? UR_TREE_FOLDER | UR_TREE_READ | UR_TREE_WRITE  : 0 );

				if ( PEAR::isError( $ret ) )
					return $ret;
			}

			return true;
		}

		function Validate( &$data )
		{
			$projectsList = $this->__GetProjectsList( );
			if ( PEAR::isError( $projectsList ) )
				return $projectsList;

			$id = $this->__root->__renderOptions[UR_ID];

			foreach( $projectsList as $project_id=>$row )
			{
				$projectPath = $this->GetFullPath( ).UR_DELIMITER.$project_id;

				$result = 0;
				foreach( array_keys( $this->__rightsArr ) as $key )
					$result |= isset( $data[$id][$projectPath]["$key"] ) ? intval( $key ): 0;

				$this->__values[$projectPath]= $result;
			}

			return true;
		}

		/**
		 * Saves object data to the database
		 *
		 * @param array $data
		 * @return boolean or PEAR::Error
		 */
		function __SaveMe( $data )
		{
			$id = $this->__root->__renderOptions[UR_ID] != UR_SYS_ID ? $this->__root->__renderOptions[UR_ID] : $this->__root->__renderOptions[UR_REAL_ID];

			$admin = false;

			switch( $this->__root->__renderOptions[UR_ACTION] )
			{
					case UR_ACTION_EDITGROUP:
						$type = UR_GROUP_ID;
						break;

					case UR_ACTION_EDITUSER:
						$admin = $this->__root->IsUserGlobalAdministrator( $id );
						$type = UR_USER_ID;
						break;
			}

			if ( $admin )
				return true;

			foreach( $this->__values as $path=>$value )
			{
				$ret = $this->__root->__SaveRightValue( $path, $id, $type, $value );

				if ( PEAR::isError( $ret ) )
					return $ret;
			}

			return true;
		}

		function listProjectUserRights( $P_ID, &$kernelStrings )
		//
		// Returns a complete list of users and their rights to a specified project
		//
		//		Parameters:
		//			$P_ID - project identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns array or PEAR_Error
		//
		{
			global $qr_pm_select_users_project_rights;

			$qr = db_query( $qr_pm_select_users_project_rights, array( 'P_ID'=>$P_ID ) );
			if ( PEAR::isError($qr) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$result = array();

			while ( $row = db_fetch_array($qr, DB_FETCHMODE_OBJECT ) ) {
				$right = ( strlen($row->PA_RIGHTS) ) ? $row->PA_RIGHTS : PM_RIGHT_NORIGHTS;

				$result[$row->U_ID]['RIGHTS'] = $right;
				$result[$row->U_ID]['USER_NAME'] = getArrUserName((array)$row, true);
			}

			db_free_result($qr);

			return $result;
		}

		function listProjectGroupsRights( $P_ID, &$kernelStrings )
		//
		// Returns a complete list of groups and its rights to a specified folder
		//
		//		Parameters:
		//			$P_ID - project identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns array or PEAR_Error
		//
		{
			global $qr_pm_select_groups_project_rights;

			$qr = db_query( $qr_pm_select_groups_project_rights, array( 'P_ID'=>$P_ID ) );
			if ( PEAR::isError($qr) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$result = array();

			while ( $row = db_fetch_array($qr, DB_FETCHMODE_OBJECT ) ) {
				$right = ( strlen($row->PA_RIGHTS) ) ? $row->PA_RIGHTS : PM_RIGHT_NORIGHTS;

				$result[$row->UG_ID]['RIGHTS'] = $right;
				$result[$row->UG_ID]['GROUP_NAME'] = $row->UG_NAME;
				$result[$row->UG_ID]['COUNT'] = $row->USERCOUNT;
			}

			db_free_result($qr);

			return $result;
		}

		function setProjectRights( $P_ID, $rightList, $groupRightList, &$kernelStrings )
		//
		// Saves users and groups rights for a specified project
		//
		//		Parameters:
		//			$P_ID - project identifier
		//			$rightList - personal rights list
		//			$groupRightList - group rights list
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			global $qr_pm_delete_project_user_rights;
			global $qr_pm_delete_project_group_rights;
			global $qr_pm_insert_project_user_right;
			global $qr_pm_insert_project_group_right;

			$params = array( 'P_ID'=>$P_ID );

			// Delete any project rights
			//
			db_query( $qr_pm_delete_project_user_rights, $params );
			db_query( $qr_pm_delete_project_group_rights, $params );

			// Insert user rights
			//
			foreach( $rightList as $U_ID=>$RIGHT ) {
				$params['U_ID'] = $U_ID;
				$params['PA_RIGHTS'] = $RIGHT;

				if ( $RIGHT != PM_RIGHT_NORIGHTS ) {
					$res = db_query( $qr_pm_insert_project_user_right, $params );
					if ( PEAR::isError($res) )
						return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
				}
			}

			// Insert group rights
			//
			foreach( $groupRightList as $UG_ID=>$RIGHT ) {
				$params['UG_ID'] = $UG_ID;
				$params['PA_RIGHTS'] = $RIGHT;

				if ( $RIGHT != PM_RIGHT_NORIGHTS ) {
					$res = db_query( $qr_pm_insert_project_group_right, $params );
					if ( PEAR::isError($res) )
						return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
				}
			}

			return null;
		}

		function getUserProjects( $U_ID, &$kernelStrings )
		//
		// Returns an ordered list of projects available for the specified user
		//
		//		Parameters:
		//			$U_ID - user identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns array or PEAR_Error
		//
		{
			global $qr_pm_select_user_projects_ordered;
			global $qr_pm_select_user_projects_global_ordered;
			global $UR_Manager;

			$globalAdmin = $UR_Manager->IsGlobalAdministrator( $U_ID );
			$sql = $globalAdmin ? $qr_pm_select_user_projects_global_ordered : $qr_pm_select_user_projects_ordered;

			$myProjects = $globalAdmin ? array() : $this->getManagerProjects( $U_ID );

			if ( PEAR::isError($myProjects) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$qr = db_query( $sql, array('U_ID'=>$U_ID) );
			if ( PEAR::isError($qr) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$result = array();

			while ( $row = db_fetch_array($qr) )
			{
				if ($row["P_ID"] == 0)
					continue;
				if ( $globalAdmin || $row["PA_RIGHT"] > UR_NO_RIGHTS || in_array( $row["P_ID"], $myProjects ) )
					$result[] = $row;
			}

			db_free_result($qr);

			return $result;
		}

		function evaluateUserProjectRights( $U_ID, $P_ID, &$kernelStrings )
		//
		// Returns the rights value for the specified user in the specified project
		//
		//		Parameters:
		//			$U_ID - user identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns integer or PEAR_Error
		//
		{
			global $UR_Manager;

			$globalAdmin = $UR_Manager->IsGlobalAdministrator( $U_ID );
			if ( $globalAdmin )
				return TREE_READWRITEFOLDER;

			$result = $UR_Manager->GetUserRightValue( $U_ID, "/ROOT/PM/PROJECTS/".$P_ID );

			if ( !strlen($result) )
				$result = PM_RIGHT_NORIGHTS;

			if ( $this->getProjectManager( $P_ID ) == $U_ID )
				return PM_RIGHT_READWRITEFOLDER;

			return $result;
		}

		/**
		 * Returns the identifier of the project manager
		 * @param integer $P_ID Project identifier
		 * @return string
		 */
		function getProjectManager( $P_ID )
		{
			if ( isset($this->__managerCache[$P_ID]) )
				return $this->__managerCache[$P_ID];

			$Manager = db_query_result( $this->selectProjectManager, DB_FIRST, array('P_ID'=>$P_ID) );
			$this->__managerCache[$P_ID] = $Manager;

			return $Manager;
		}

		/**
		 * Returns the array with projects ID which are managed by $U_ID_MANAGER
		 * @param integer $U_ID_MANAGER Manager identifier
		 * @return array
		 */
		function getManagerProjects( $U_ID_MANAGER )
		{

			$qr = db_query( $this->selectManagerProjects, array('U_ID_MANAGER'=>$U_ID_MANAGER) );

			if ( PEAR::isError($qr) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$result = array();

			while ( $row = db_fetch_array($qr) )
				$result[] = $row["P_ID"];

			db_free_result($qr);

			return $result;
		}


	}

?>