<?
	class ITPMRightsManager {
		
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
			return array ("0" => array ("P_ID" => 0, "P_DESC" => "Free"));
		}
		
		function evaluateUserProjectRights( $U_ID, $P_ID, &$kernelStrings ) {
			return 7;
		}
		
	}
	
?>