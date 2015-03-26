<?php

include dirname(__FILE__)."/../../../system/lib/localization/init.php";

$lc = new LocalizationCompiler();
$lc->domains = array(
	'ru' => 'webasystMM',
	'en' => 'webasystMM',
	'de' => 'webasystMM'
);
$dir = dirname(__FILE__);
$lc->source_path = $dir;
$lc->backup_path = false;
$lc->compile_path = $dir;
		
$lc->files_include = ".+\.(php|js|html)";
$lc->files_compile = ".+\.(js)";
$lc->files_words = ".+\.(js|html)";

$lc->dirs_exclude = "(\.svn|\.xml|locale)";

$lc->locale_path = $dir.DIRECTORY_SEPARATOR."locale";

$lc->split_on_subfolder = false;
$lc->update_files = false;
$lc->update_locale = true;
$lc->update_complile = true;
$lc->recursive = 3;

$lc->exec();

?>