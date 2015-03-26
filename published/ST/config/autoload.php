<?php

$__autoload = array(
	"STFrontController" => "published/ST/lib/STFrontController.class.php",
	"STRequest" => "published/ST/lib/classes/STRequest.class.php",
	// models
	'STActionModel' => 'published/ST/lib/model/STAction.model.php',
	'STSourceModel' => 'published/ST/lib/model/STSource.model.php',
    'STClassTypeModel' => 'published/ST/lib/model/STClassType.model.php',
    'STClassModel' => 'published/ST/lib/model/STClass.model.php',
    'STKnowledgeModel' => 'published/ST/lib/model/STKnowledge.model.php',
	'STStateModel' => 'published/ST/lib/model/STState.model.php',
	'STStateParamModel' => 'published/ST/lib/model/STStateParam.model.php',
	'STStateActionModel' => 'published/ST/lib/model/STStateAction.model.php',
	'STRequestModel' => 'published/ST/lib/model/STRequest.model.php',
	'STRequestLogModel' => 'published/ST/lib/model/STRequestLog.model.php',
	'STRuleModel' => 'published/ST/lib/model/STRule.model.php',
	'STRequestClassModel' => 'published/ST/lib/model/STRequestClass.model.php',
    'STQPFolderModel' => 'published/ST/lib/model/STQPFolder.model.php',
	// classes
	'STTemplate' => 'published/ST/lib/classes/STTemplate.class.php',
	'STPlugins' => 'published/ST/lib/classes/STPlugins.class.php',
	'STPlugin' => 'published/ST/lib/classes/STPlugin.class.php'
);

Autoload::load($__autoload);