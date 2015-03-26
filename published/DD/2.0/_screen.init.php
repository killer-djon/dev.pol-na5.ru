<?php
	include_once("../../../system/init.php");
	
	AutoLoad::add('DDDataModel', "published/DD/2.0/DDDataModel.php");
	AutoLoad::addRule('substr($class, 0, 2) == "DD"', "published/DD/2.0")
?>