<?php
	
	$Register = &Register::getInstance();
	$smarty = &$Register->get(VAR_SMARTY);
	
	if( isset( $_GET['specialoffers'] ) ){
		$smarty->assign( 'main_content_template', 'product_list.html');	
	}
	
	
?>