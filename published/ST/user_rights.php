<?php

$__ur_STApp = &new UR_RO_Container( "ST", "app_name_long", "ST" );
$UR_Manager->AddChild( $__ur_STApp );

$__ur_STScreens = &new UR_RO_Container( UR_SCREENS, "app_available_pages_name", "ST" );
$__ur_STApp->AddChild( $__ur_STScreens );

$__ur_tmp = &new UR_RO_Screen( "RL", "rl_short_name", "rl_long_name", "index.php", "ST" );
$__ur_STScreens->AddChild( $__ur_tmp );

$__ur_STFuncs = &new UR_RO_Container( UR_FUNCTIONS, "app_available_functions_name" );
$__ur_STApp->AddChild( $__ur_STFuncs );

$__ur_tmp = &new UR_RO_Bool( 'ADMIN', "app_cantools_label", "ST");
$__ur_STFuncs->AddChild( $__ur_tmp );

?>