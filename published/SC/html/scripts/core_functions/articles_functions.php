<?php
	
	
	#============================
	#         Настройки			#
	#============================
	
		
		function settingCONF_ARTICLE_URL(){

			if ( isset($_POST["save"]) && isset($_POST["settingCONF_ARTCLE_URL"]) )
			{	
				$value = make_slug($_POST["settingCONF_ARTCLE_URL"]);
				$isNotExist = !intval(db_phquery_fetch(DBRFETCH_FIRST, 'SELECT 1 FROM ?#DIVISIONS_TBL WHERE `xUnicKey`=? AND `xName`<>?', $value, 'pgn_articles'));
				if(!$isNotExist){
					return "<div>".translate('err_conf_artcle_url')."</div><br><input type='text' value='".xHtmlSpecialChars(_getSettingOptionValue( 'CONF_ARTCLE_URL' ))."' name='settingCONF_ARTCLE_URL' size='40' />";
				}else{
					if(CONF_ARTCLE_URL != $value){
						if(preg_match('/[a-zA-Z_]+/',CONF_ARTCLE_URL)){
							$path = DIR_SITEMAP.DIRECTORY_SEPARATOR.CONF_ARTCLE_URL.'.xml';
							if(file_exists($path)){
								$res = unlink($path);
							}
						}
					}
					_setSettingOptionValue( 'CONF_ARTCLE_URL', $value );
					$sql = 'UPDATE ?#DIVISIONS_TBL SET `xUnicKey`=?  WHERE `xName`=?';		
					db_phquery_array($sql,$value,'pgn_articles');	
				}
			}
			return "<input type='text' value='".xHtmlSpecialChars(_getSettingOptionValue( 'CONF_ARTCLE_URL' )).
			"' name='settingCONF_ARTCLE_URL' size='40' />";
		}	
		
		
		function settingCONF_ARTCLE_YANDEX_SHARE_SERVICES(){
			if ( isset($_POST["save"]) && isset($_POST["settingCONF_ARTCLE_YANDEX_SHARE_SERVICES"]) )
			{	
				$m_checked = array_keys(scanArrayKeysForID($_POST, 'settingCONF_ARTCLE_YANDEX_SHARE_SERVICES'));
				$value = implode(',',$m_checked);
				_setSettingOptionValue( 'CONF_ARTCLE_YANDEX_SHARE_SERVICES', $value );
			}
			$services = array('yaru','vkontakte','facebook','twitter','odnoklassniki','moimir','lj','friendfeed','moikrug','gplus');
			$checked = explode(',',xHtmlSpecialChars(_getSettingOptionValue( 'CONF_ARTCLE_YANDEX_SHARE_SERVICES' )));
			$checked_services = array();
			foreach($services as $service){
				if(in_array($service,$checked)){
					$checked_services[$service] = 'checked="checked"';
				}
			}
			return '
			<input type="hidden" value="yep" name="settingCONF_ARTCLE_YANDEX_SHARE_SERVICES" >	
			<ul style="list-style: none;">
				<li><label><input type="checkbox" value="yaru" name="settingCONF_ARTCLE_YANDEX_SHARE_SERVICES_yaru" '.$checked_services['yaru'].' >'.translate('pgn_articles_by_categoryes').'</label></li>
				<li><label><input type="checkbox" value="vkontakte"  name="settingCONF_ARTCLE_YANDEX_SHARE_SERVICES_vkontakte" '.$checked_services['vkontakte'].' >'.translate('pgn_vkontakte').'</label></li>
				<li><label><input type="checkbox" value="facebook"  name="settingCONF_ARTCLE_YANDEX_SHARE_SERVICES_facebook" '.$checked_services['facebook'].' >'.translate('pgn_facebook').'</label></li>
				<li><label><input type="checkbox" value="twitter"  name="settingCONF_ARTCLE_YANDEX_SHARE_SERVICES_twitter" '.$checked_services['twitter'].' >'.translate('pgn_twitter').'</label></li>
				<li><label><input type="checkbox" value="odnoklassniki" name="settingCONF_ARTCLE_YANDEX_SHARE_SERVICES_odnoklassniki" '.$checked_services['odnoklassniki'].' >'.translate('pgn_odnoklassniki').'</label></li>
				<li><label><input type="checkbox" value="moimir"  name="settingCONF_ARTCLE_YANDEX_SHARE_SERVICES_moimir" '.$checked_services['moimir'].' >'.translate('pgn_moimir').'</label></li>
				<li><label><input type="checkbox" value="lj" name="settingCONF_ARTCLE_YANDEX_SHARE_SERVICES_lj" '.$checked_services['lj'].' >'.translate('pgn_livejournal').'</label></li>
				<li><label><input type="checkbox" value="friendfeed" name="settingCONF_ARTCLE_YANDEX_SHARE_SERVICES_friendfeed" '.$checked_services['friendfeed'].' >'.translate('pgn_friendfeed').'</label></li>
				<li><label><input type="checkbox" value="moikrug" name="settingCONF_ARTCLE_YANDEX_SHARE_SERVICES_moikrug" '.$checked_services['moikrug'].' >'.translate('pgn_moikrug').'</label></li>
				<li><label><input type="checkbox" value="gplus" name="settingCONF_ARTCLE_YANDEX_SHARE_SERVICES_gplus" '.$checked_services['gplus'].' >'.translate('pgn_Google').'</label></li>
			</ul>
			';
		}
		
		
		function settingCONF_ARTCLE_COMMENTS_SERVICES(){
			if ( isset($_POST["save"]) && isset($_POST["settingCONF_ARTCLE_COMMENTS_SERVICES"]) )
			{		
				$m_checked = array_keys(scanArrayKeysForID($_POST, 'settingCONF_ARTCLE_COMMENTS_SERVICES'));
				$value = implode(',',$m_checked);
				_setSettingOptionValue( 'CONF_ARTCLE_COMMENTS_SERVICES', $value );
			}
			$services = array('system','vkontakte','facebook');
			$checked = explode(',',xHtmlSpecialChars(_getSettingOptionValue( 'CONF_ARTCLE_COMMENTS_SERVICES' )));
			$checked_services = array();
			foreach($services as $service){
				if(in_array($service,$checked)){
					$checked_services[$service] = 'checked="checked"';
				}
			}
			return '
			<input type="hidden" value="yep" name="settingCONF_ARTCLE_COMMENTS_SERVICES" >	
			<ul style="list-style: none;">
				<li><label><input type="checkbox" value="system" name="settingCONF_ARTCLE_COMMENTS_SERVICES_system" '.$checked_services['system'].' >'.translate('pgn_system').'</label></li>
				<li><label><input type="checkbox" value="vkontakte"  name="settingCONF_ARTCLE_COMMENTS_SERVICES_vkontakte" '.$checked_services['vkontakte'].' >'.translate('pgn_vkontakte').'</label></li>
				<li><label><input type="checkbox" value="facebook"  name="settingCONF_ARTCLE_COMMENTS_SERVICES_facebook" '.$checked_services['facebook'].' >'.translate('pgn_facebook').'</label></li>
			</ul>
			';
		}

		
		function settingCONF_ARTCLE_COMMENTS_VK_ATTACH(){
			if ( isset($_POST["save"]) && isset($_POST["settingCONF_ARTCLE_COMMENTS_VK_ATTACH"]) )
			{	
				$m_checked = array_keys(scanArrayKeysForID($_POST, 'settingCONF_ARTCLE_COMMENTS_VK_ATTACH'));
				$value = implode(',',$m_checked);
				_setSettingOptionValue( 'CONF_ARTCLE_COMMENTS_VK_ATTACH', $value );
			}
			$services = array('graffiti','photo','video','audio','link');
			$checked = explode(',',xHtmlSpecialChars(_getSettingOptionValue( 'CONF_ARTCLE_COMMENTS_VK_ATTACH' )));
			$checked_services = array();
			foreach($services as $service){
				if(in_array($service,$checked)){
					$checked_services[$service] = 'checked="checked"';
				}
			}
			return '
			<input type="hidden" value="yep" name="settingCONF_ARTCLE_COMMENTS_VK_ATTACH" >	
			<ul style="list-style: none;">
				<li><label><input type="checkbox" value="graffiti" name="settingCONF_ARTCLE_COMMENTS_VK_ATTACH_graffiti" '.$checked_services['graffiti'].' >Граффити</label></li>
				<li><label><input type="checkbox" value="photo"  name="settingCONF_ARTCLE_COMMENTS_VK_ATTACH_photo" '.$checked_services['photo'].' >Фотографии</label></li>
				<li><label><input type="checkbox" value="video"  name="settingCONF_ARTCLE_COMMENTS_VK_ATTACH_video" '.$checked_services['video'].' >Видео</label></li>
				<li><label><input type="checkbox" value="audio"  name="settingCONF_ARTCLE_COMMENTS_VK_ATTACH_audio" '.$checked_services['audio'].' >Аудио</label></li>
				<li><label><input type="checkbox" value="link"  name="settingCONF_ARTCLE_COMMENTS_VK_ATTACH_link" '.$checked_services['link'].' >Ссылки</label></li>
			</ul>
			';
		}

	
		function __getEnabledArticlesCategorys(){	
			$sql = 'SELECT '.LanguagesManager::sql_prepareField('CategoryTitle').' AS name, `CategoryID` AS `id`, `CategorySlug` FROM ?#ARTICLES_CATEGORY_TABLE WHERE `CategoryEnable`=1 AND `CategoryParent`=1 ORDER BY `CategoryID` ASC';
			$Register = &Register::getInstance();
			$DBHandler = &$Register->get(VAR_DBHANDLER);	
			$DBRes = $DBHandler->ph_query($sql);			
			$pages = $DBRes->fetchArrayAssoc();	
			$root[] = array('name' => 'ROOT', 'id' => 1, 'CategorySlug' => CONF_ARTCLE_ROOT_URL );
			$pages = array_merge($root,$pages);
			return $pages;
		}


		function cptsettingview_articles_categoryes_select($params){
			$pages = __getEnabledArticlesCategorys();
			$params['options'] = array();
			foreach ($pages as $page){
				$params['options'][$page['id']] = $page['name'];
			}
			if(is_string($params['value']))$params['value'] = explode(':', $params['value']);
			return cptsettingview_checkboxgroup($params);
		}

		
		function cptsettingserializer_articles_categoryes_select($params, $post){
			$Register = &Register::getInstance();
			if(!$Register->is_set('__AUXNAV_SERIALIZED') && is_array($post[$params['name']])){
				$post[$params['name']] = implode(':', $post[$params['name']]);
				$reg = 1;
				$Register->set('__AUXNAV_SERIALIZED', $reg);
			}
			return cptsettingserializer_checkboxgroup($params, $post);
		}
	
	
	#============================
	#         Категории			#
	#============================
	
		
		function GetRecursiveArticlesCategorys( $CategoryParent, $level, $enabled = false, $expandedArticlesCategoryID_Array ){	
			$sql = "
				SELECT * FROM `?#ARTICLES_CATEGORY_TABLE` 
				WHERE `CategoryParent`=? ";
			if($enabled==false)$sql.=" AND `CategoryEnable` = '1'";
			$sql.= "ORDER BY `CategorySort`, ".LanguagesManager::sql_prepareField('CategoryTitle');
			$q = db_phquery($sql,$CategoryParent);
			
			$result = array();
			while ($row = db_fetch_row($q)){
				LanguagesManager::ml_fillFields(ARTICLES_CATEGORY_TABLE, $row);
				$row["level"] = $level;
				if ( $expandedArticlesCategoryID_Array != null ) {
					foreach( $expandedArticlesCategoryID_Array as $CategoryID )	{
						if ( (int)$CategoryID == (int)$row["CategoryID"] )	{
							$row["ExpandedCategory"] = true;
							break;
						}
					}
				}else{
					$row["ExpandedCategory"] = false;
				}
				$row['CountArticles'] = GetArticlesCountInCategory($row['CategoryID'], false, $row["ExpandedCategory"]);
				$row['CountArticlesAll'] = GetArticlesCountInCategory($row['CategoryID'], true, $row["ExpandedCategory"]);
				
				$row['products_count'] = GetArticlesCountInCategory($row['categoryID'], false, $row["ExpandedCategory"]);
				$row['products_count_admin'] = GetArticlesCountInCategory($row['categoryID'], true, $row["ExpandedCategory"]);
				
				$count = GetSubArticleCategoryNumber((int)$row['CategoryID']);
				$row['ExistSubCategories'] = ( $count != 0 );	
				$result[] = $row;
			
				if ( $row["ExpandedCategory"] ){
					$subcategories = GetRecursiveArticlesCategorys( $row["CategoryID"], $level+1, $enabled, $expandedArticlesCategoryID_Array   );
					for ($j=0; $j<count($subcategories); $j++)
					$result[] = $subcategories[$j];
				}
			}
			return $result;
		}

		
		function GetArticleCategoryByID( $CategoryID, $enabled = false ){
			$CategoryID = (int)$CategoryID;
			if(empty($CategoryID)) return false;	
			$sql="SELECT *";
			$sql.=" FROM ?#ARTICLES_CATEGORY_TABLE";
			$sql.=" WHERE `CategoryID`=?";	
			if($enabled==false)$sql.=" AND `CategoryEnable` = '1'";

			$Category = db_phquery_fetch(DBRFETCH_ASSOC, $sql, $CategoryID);
			if(empty($Category))return false;
			LanguagesManager::ml_fillFields(ARTICLES_CATEGORY_TABLE, $Category);

			return $Category;
		}
		
		
		function GetArticleCategoryIDBySlug( $CategorySlug ){	
			if($CategorySlug==CONF_ARTCLE_ROOT_URL) return 1;	
			if(empty($CategorySlug)) return false;	
			$CategoryID = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT `CategoryID` FROM ?#ARTICLES_CATEGORY_TABLE WHERE `CategorySlug`=?',$CategorySlug);
			return  $CategoryID;
		}
		
		
		function GetArticleCategoryExists( $CategoryID ){
			$CategoryID = (int)$CategoryID;
			if(empty($CategoryID)) return false;
			$r = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT COUNT(*) FROM ?#ARTICLES_CATEGORY_TABLE WHERE `CategoryID`=?',$CategoryID);
			return  ( $r!= 0 );
		}	
		
		
		function GetShowSubArticles( $CategoryID ){
			$CategoryID = (int)$CategoryID;
			if(empty($CategoryID)) return false;
			$r = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT `CategoryShowSub` FROM ?#ARTICLES_CATEGORY_TABLE WHERE `CategoryID`=?',$CategoryID);
			return  ( $r!= 0 );
		}
	
	
		function GetArticlesCountInCategory( $ArticleCategoryID, $enabled = false, $subcats = false ){
			$ArticleCategoryID = (int)$ArticleCategoryID;
	
			if($subcats && $ArticleCategoryID != 1){
				$sql = 'SELECT COUNT(*) FROM ?#ARTICLES_TABLE WHERE `ArticleCategoryID`=?';
				if($enabled==false)$sql.=' AND `ArticleEnable` = 1';
				$count = db_phquery_fetch(DBRFETCH_FIRST, $sql,$ArticleCategoryID);
			}elseif($ArticleCategoryID == 1 && $enabled==false){
				$sql = 'SELECT COUNT(*) FROM `?#ARTICLES_TABLE`';
				if($enabled==false)$sql.=' WHERE `ArticleEnable` = 1';
				$count = db_phquery_fetch(DBRFETCH_FIRST, $sql);
			}elseif($ArticleCategoryID == 1 && $enabled==true){
				$sql = 'SELECT COUNT(*) FROM `?#ARTICLES_TABLE` WHERE `ArticleCategoryID`=?';
				if($enabled==false)$sql.=' WHERE `ArticleEnable` = 1';
				$count = db_phquery_fetch(DBRFETCH_FIRST, $sql,$ArticleCategoryID);
			}else{
				$categories = GetArticleSubCategories($ArticleCategoryID);
				$categories[] = $ArticleCategoryID;
				$sql = 'SELECT COUNT(*) FROM `?#ARTICLES_TABLE` WHERE `ArticleCategoryID` IN (?@)';
				if($enabled==false)$sql.=' AND `ArticleEnable` = 1';
				$count = db_phquery_fetch(DBRFETCH_FIRST, $sql,$categories);
			}
			return (int)$count;
		}
	
	
		function GetSubArticleCategoryNumber( $CategoryID ){
			static $cache = false;
			if($cache === false){
				$cache = array();
				$sql ="SELECT COUNT(1) AS `cnt`,`CategoryParent` FROM `?#ARTICLES_CATEGORY_TABLE` GROUP BY `CategoryParent`";
				$q = db_phquery($sql);
				while($row = db_fetch_assoc($q)){
					$row = array_map('intval',$row);
					$cache[$row['CategoryParent']] = $row['cnt'];
				}
			}
			return isset($cache[$CategoryID])?$cache[$CategoryID]:0;
		}
		
		
		function CalculatePathToArticleCategory( $CategoryID ){
			$CategoryID = (int)$CategoryID;
			if (!$CategoryID) return NULL;
			static $cached_path = array();
			if(isset($cached_path[$CategoryID])){
				$path = $cached_path[$CategoryID];
			}else{
				$path = array();
				$q = db_query("select count(*) from ".ARTICLES_CATEGORY_TABLE.
						" where `CategoryID`=$CategoryID ");
				$row = db_fetch_row($q);
				if ( $row[0] == 0 )
				return $path;
				$curr = intval($CategoryID);
				do{
					$q = db_query(
						"SELECT `CategoryID`, `CategorySlug`, `CategoryParent`, ".LanguagesManager::sql_prepareField('CategoryTitle')." AS `CategoryTitle` FROM ".ARTICLES_CATEGORY_TABLE." 
						WHERE `CategoryID`={$curr}");
					$row = db_fetch_row($q);
					$path[] = $row;
					if ( $curr <= 1 )
					break;
					$curr = intval($row["CategoryParent"]);
				}
				while ( 1 );
				$path = array_reverse($path);
				$cached_path[$CategoryID] = $path;
			}
			return $path;
		}
		
		
		function GetArticleSubCategories( $CategoryID ){
			$sql = 'SELECT `CategoryID` FROM `?#ARTICLES_CATEGORY_TABLE` WHERE `CategoryID`>0 AND (`CategoryParent` IN ( ?@ ))';
			$CategoryID = is_array($CategoryID)?$CategoryID:array($CategoryID);
			$CategoryID = array_map('intval',$CategoryID);
			$categories = db_phquery_fetch(DBRFETCH_FIRST_ALL,$sql,$CategoryID);
			$categories =  array_map('intval',$categories);
			if($categories){
				$categories = array_merge($categories,GetArticleSubCategories($categories));
			}
			return $categories;
		}

		
		function GetArticleCategoryisAvailableSlug($CategorySlug,$CategoryID ){
			if($CategorySlug == CONF_ARTCLE_ROOT_URL or $CategorySlug == 'rss') return 0;
			return !intval(db_phquery_fetch(DBRFETCH_FIRST, 'SELECT 1 FROM ?#ARTICLES_CATEGORY_TABLE WHERE `CategorySlug`=? AND `CategoryID`<>?', $CategorySlug, $CategoryID));
		}

					
		function GetArticleCategoriesParent(){
			$sql = 'SELECT `CategoryID` FROM `?#ARTICLES_CATEGORY_TABLE` WHERE `CategoryParent`=1';
			$categories = db_phquery_fetch(DBRFETCH_FIRST_ALL,$sql);
			$categories =  array_map('intval',$categories);
			$root[] = 1;
			$categories = array_merge($root,$categories);	
			return $categories;
		}
		
		
		function AddArticleCategory( $Array ){	
			$title_inj = LanguagesManager::sql_prepareFields('CategoryTitle', $Array, true);
			$desc_inj = LanguagesManager::sql_prepareFields('CategoryDescription', $Array,true);
			$mtitle_inj = LanguagesManager::sql_prepareFields('CategoryMetaTitle', $Array,true);
			$mkeywords_inj = LanguagesManager::sql_prepareFields('CategoryMetaKey', $Array,true);
			$mdescription_inj = LanguagesManager::sql_prepareFields('CategoryMetaDescription', $Array,true);
			
			$fields=$title_inj['fields_list'].','.$desc_inj['fields_list'].',';
			$fields.=$mtitle_inj['fields_list'].','.$mkeywords_inj['fields_list'].','.$mdescription_inj['fields_list'];
			
			$values_place=str_repeat('?,',
				count($title_inj['values'])+count($desc_inj['values'])+
				count($mtitle_inj['values'])+count($mkeywords_inj['values'])+count($mdescription_inj['values']));
				
			$sql = "INSERT ?#ARTICLES_CATEGORY_TABLE ( {$fields}, `CategoryParent`, `CategoryEnable`, `CategorySlug`, `CategorySort`,`CategoryShowSub` ) ";
			$sql.="VALUES({$values_place}?,?,?,?,?)";
			db_phquery_array($sql,$title_inj['values'],$desc_inj['values'],$mtitle_inj['values'],$mkeywords_inj['values'],$mdescription_inj['values'], $Array['CategoryParent'], $Array['CategoryEnable'],$Array['CategorySlug'],$Array['CategorySort'],$Array['CategoryShowSub']);		
			return db_insert_id();	
		}
		
		
		function UpdateArticleCategory( $Array ){
			
			$title_inj = LanguagesManager::sql_prepareFields('CategoryTitle', $Array);
			foreach ($title_inj['fields'] as $field) $fields.=$field.'=?,';
			$desc_inj = LanguagesManager::sql_prepareFields('CategoryDescription', $Array);
			foreach ($desc_inj['fields'] as $field) $fields.=$field.'=?,';
			$mtitle_inj = LanguagesManager::sql_prepareFields('CategoryMetaTitle', $Array);
			foreach ($mtitle_inj['fields'] as $field) $fields.=$field.'=?,';
			$mkeywords_inj = LanguagesManager::sql_prepareFields('CategoryMetaKey', $Array);
			foreach ($mkeywords_inj['fields'] as $field) $fields.=$field.'=?,';		
			$mdescription_inj = LanguagesManager::sql_prepareFields('CategoryMetaDescription', $Array);
			foreach ($mdescription_inj['fields'] as $field) $fields.=$field.'=?,';		
			
		
			$sql = 'UPDATE ?#ARTICLES_CATEGORY_TABLE SET '.$fields.'`CategoryParent`=?, `CategoryEnable`=?, `CategorySlug`=?, `CategorySort`=?, `CategoryShowSub`=?	WHERE CategoryID=?';		
			db_phquery_array($sql,$title_inj['values'],$desc_inj['values'],$mtitle_inj['values'],$mkeywords_inj['values'],$mdescription_inj['values'], $Array['CategoryParent'], $Array['CategoryEnable'],$Array['CategorySlug'],$Array['CategorySort'],$Array['CategoryShowSub'], $Array['CategoryID']);	
			
		}	
	
	
		function DeleteArticleCategory( $CategoryID ){
			$error = '';
			$CategoryID = (int)$CategoryID;
			if(empty($CategoryID) or $CategoryID == '1') return false;	
			$categories = GetArticleSubCategories($CategoryID);
			$categories[] = $CategoryID;

			$sql = 'SELECT `CategoryDefaultPicture` FROM `?#ARTICLES_CATEGORY_TABLE` WHERE `CategoryID` IN (?@) AND `CategoryID`>1';
			$q = db_phquery($sql,$categories);
			while($r = db_fetch_row($q)){
				if ($r["CategoryDefaultPicture"] && file_exists(DIR_ARTICLES_PICTURES."/".$r["CategoryDefaultPicture"])){
					$res = Functions::exec('file_remove', array(DIR_ARTICLES_PICTURES."/".$r["CategoryDefaultPicture"]));
					if(PEAR::isError($res)){
						$error .= $res->getMessage();
					}
				}
			}
			$sqls = array();
			$sqls[] = 'UPDATE `?#ARTICLES_TABLE` SET `ArticleCategoryID`=1 WHERE `ArticleCategoryID` IN (?@) AND `ArticleCategoryID`>1';
			$sqls[] = 'DELETE FROM `?#ARTICLES_CATEGORY_TABLE` WHERE `CategoryID` IN (?@) AND `CategoryID`>1';
			foreach($sqls as $sql){
				db_phquery($sql,$categories);
			}

			if($error){
				return PEAR::raiseError($error);
			}
		}
			
			
	
	#============================
	#          Статьи			#
	#============================
	

		function GetArticlesShort($params){		
			$LIMIT = (int)$params['count'];
			if($params['order']=='sort'){
				$ORDER = ' ORDER BY  t1.`ArticleSort`+1 DESC, '.LanguagesManager::sql_prepareField('ArticleTitle').' ';
			}else if($params['order']=='date'){
				$ORDER = ' ORDER BY  t1.`ArticleDate`+1 DESC, '.LanguagesManager::sql_prepareField('ArticleTitle').' ';
			}else if($params['order']=='rand'){
				$ORDER = ' ORDER BY RAND() ';
			}

			if($params['groupByCategory']){
				$categorys = array();
				$categorys = ($params['categoryes']=='all')?GetArticleCategoriesParent():explode(':',$params['categoryes_select']);	
				if(!empty($categorys)){
					foreach($categorys as $key => $CategoryID){
						$Out[$key] = GetArticleCategoryByID($CategoryID);
						if(!empty($params['sub_categorys']) && $CategoryID>1)
							$WHERE_field = GetArticleSubCategories($CategoryID);
						$WHERE_field[] = $CategoryID;
						$WHERE = "WHERE t1.`ArticleCategoryID` in (?@)";
						$WHERE.= " AND t1.`ArticleEnable` = 1";
						
						$sql = "SELECT t2.CategorySlug as CategorySlug, ".LanguagesManager::sql_prepareField('CategoryTitle')." as CategoryTitle, t1.ArticleID, t1.ArticleCategoryID, t1.ArticleSlug, ".LanguagesManager::sql_prepareField('ArticleTitle')." as ArticleTitle, ".LanguagesManager::sql_prepareField('ArticleBriefDescription')." as ArticleBriefDescription, t1.ArticleDefaultSmallPicture, t1.ArticleDate FROM ?#ARTICLES_CATEGORY_TABLE t2 RIGHT JOIN ?#ARTICLES_TABLE t1 ON(t2.CategoryID = t1.ArticleCategoryID) ".$WHERE.$ORDER." limit 0,? ";
						
						$Articles = db_phquery_fetch(DBRFETCH_ASSOC_ALL, $sql, $WHERE_field, $LIMIT);
						
						foreach($Articles as $key2 => $Article){	
							$Articles[$key2]['CategorySlug'] = ($Articles[$key2]['CategorySlug'])?$Articles[$key2]['CategorySlug']:CONF_ARTCLE_ROOT_URL;	
						}
						$Out[$key]['Articles'] = $Articles;	
					}
				}
			}else{
				$WHERE_field = array();
				$WHERE_field = ($params['categoryes']=='all')?GetArticleCategoriesParent():explode(':',$params['categoryes_select']);	
				
				if(!empty($WHERE_field)){
					if(!empty($params['sub_categorys'])){
						foreach($WHERE_field as $key => $CategoryID){
							if($CategoryID>1)
								$WHERE_field = array_merge($WHERE_field,GetArticleSubCategories($CategoryID));	
						}
					}
				}	
				$WHERE = "WHERE t1.`ArticleCategoryID` in (?@)";
				$WHERE.= " AND t1.`ArticleEnable` = 1";
				
				$sql = "SELECT t2.CategorySlug as CategorySlug, ".LanguagesManager::sql_prepareField('CategoryTitle')." as CategoryTitle, t1.ArticleID, t1.ArticleCategoryID, t1.ArticleSlug, ".LanguagesManager::sql_prepareField('ArticleTitle')." as ArticleTitle, ".LanguagesManager::sql_prepareField('ArticleBriefDescription')." as ArticleBriefDescription, t1.ArticleDefaultSmallPicture, t1.ArticleDate FROM ?#ARTICLES_CATEGORY_TABLE t2 RIGHT JOIN ?#ARTICLES_TABLE t1 ON(t2.CategoryID = t1.ArticleCategoryID) ".$WHERE.$ORDER." limit 0,? ";
				
				$Articles = db_phquery_fetch(DBRFETCH_ASSOC_ALL, $sql, $WHERE_field, $LIMIT);
				foreach($Articles as $key => $Article){	
					$Articles[$key]['CategorySlug'] = ($Articles[$key]['CategorySlug'])?$Articles[$key]['CategorySlug']:CONF_ARTCLE_ROOT_URL;	
				}
				$Out = $Articles;	
			}
			//print_r($Out);
			
			return $Out;
		}

		
		function GetArticlesRSS(){			
			$sql = "SELECT t2.`CategorySlug` as `CategorySlug`, t1.`ArticleID`, t1.`ArticleCategoryID`, t1.`ArticleSlug`, ".LanguagesManager::sql_prepareField('ArticleTitle')." as `ArticleTitle`, ".LanguagesManager::sql_prepareField('ArticleBriefDescription')." as `ArticleBriefDescription`, t1.`ArticleDefaultSmallPicture`, t1.`ArticleDefaultBigPicture`, t1.`ArticleDate` FROM ?#ARTICLES_CATEGORY_TABLE t2 RIGHT JOIN ?#ARTICLES_TABLE t1 ON(t2.`CategoryID` = t1.`ArticleCategoryID`) where t1.`ArticleEnable` = 1  ORDER BY `ArticleDate` DESC";
			$Articles = db_phquery_fetch(DBRFETCH_ASSOC_ALL, $sql);
			foreach($Articles as $key => $Article){	
				$Articles[$key]['CategorySlug'] = ($Articles[$key]['CategorySlug'])?$Articles[$key]['CategorySlug']:CONF_ARTCLE_ROOT_URL;	
			}
			return $Articles;
		}
	
	
		function GetArticleByID( $ArticleID, $enabled = false , $showReleatedArticles = true ){
			$ArticleID = (int)$ArticleID;
			if(empty($ArticleID)) return false;	
			$sql='SELECT c.*, s.`CategorySlug` FROM ?#ARTICLES_TABLE c left join ?#ARTICLES_CATEGORY_TABLE s ON c.`ArticleCategoryID`=s.`CategoryID` where c.`ArticleID` = ?';
			if($enabled==false)$sql.=' AND c.`ArticleEnable` = 1';
			$Article = db_phquery_fetch(DBRFETCH_ASSOC, $sql, $ArticleID);
			LanguagesManager::ml_fillFields(ARTICLES_TABLE, $Article);
			$Article['ArticleProducts_array'] = $Article['ArticleProducts'];
			$Article['ArticleArticles_array'] = $Article['ArticleArticles'];
			$Article['ArticleProducts'] = GetArticlesProducts($Article['ArticleProducts']);	
			if($showReleatedArticles){
				$Article['ArticleArticles'] = GetArticlesArticles($Article['ArticleArticles'], $ArticleID, $enabled);	
			}
			$Article['CategorySlug'] = ($Article['CategorySlug'])?$Article['CategorySlug']:CONF_ARTCLE_ROOT_URL;
			return $Article;
		}

		
		function GetArticlesByCategoryID( $params, &$count = 0, $navigatorParams = null){

			$CategoryID = $params['CategoryID'];
			$CategoryID = (int)$CategoryID;
			if(empty($CategoryID)) return false;	
				
			$enabled = $params['enabled'];
			$DoNotShowSub = $params['DoNotShowSub'];
			$OnlyForNextPrev = ($params['OnlyForNextPrev'])?$params['OnlyForNextPrev']:false;
			$ShowSubArticles = (!$DoNotShowSub)?GetShowSubArticles($CategoryID):false;
			
			if(!$OnlyForNextPrev){
				$sql='SELECT c.*, s.`CategoryID`, s.`CategorySlug` FROM ?#ARTICLES_TABLE c LEFT JOIN ?#ARTICLES_CATEGORY_TABLE s ON c.`ArticleCategoryID`=s.`CategoryID`';
			}else{
				$sql='SELECT c.ArticleID, c.ArticleSlug, s.`CategoryID`, s.`CategorySlug` FROM ?#ARTICLES_TABLE c LEFT JOIN ?#ARTICLES_CATEGORY_TABLE s ON c.`ArticleCategoryID`=s.`CategoryID`';
			}
			
			if($ShowSubArticles){
				$categories = GetArticleSubCategories($CategoryID);
				$categories[] = $CategoryID;
				$sql.=' WHERE c.`ArticleCategoryID` IN (?@)';
				$Fields_sql = $categories;
				
			}elseif($CategoryID != 1 || $enabled==true){
				$sql.=' WHERE c.`ArticleCategoryID`=?';
				$Fields_sql = $CategoryID;
			}
				
			if($enabled==false)$sql.=' AND c.`ArticleEnable` = 1';
			$sql.=" ORDER BY c.`ArticleSort`+1, c.`ArticleDate` DESC, ".LanguagesManager::sql_prepareField('ArticleTitle');
			
			
			if($count&&isset($navigatorParams['offset'])&&($count<$navigatorParams['offset'])){
				$navigatorParams['offset'] = $navigatorParams['CountRowOnPage']*intval($count/$navigatorParams['CountRowOnPage']);
			}
			$limit = $navigatorParams != null?' LIMIT '.(int)$navigatorParams['offset'].','.(int)$navigatorParams['CountRowOnPage']:'';
			$sql.= $limit;
			
			$Articles = db_phquery_fetch(DBRFETCH_ASSOC_ALL, $sql, $Fields_sql);
			
			foreach($Articles as $key => $Article){	
				if(!$OnlyForNextPrev){
					LanguagesManager::ml_fillFields(ARTICLES_TABLE, $Articles[$key]);
					$Article['ArticleProducts_array'] = $Article['ArticleProducts'];
					$Article['ArticleArticles_array'] = $Article['ArticleArticles'];
					$Articles[$key]['ArticleProducts'] = GetArticlesProducts($Article['ArticleProducts']);
					$Articles[$key]['ArticleArticles'] = GetArticlesArticles($Article['ArticleArticles'], $Article['ArticleID']);
					
				}
				$Articles[$key]['CategoryID'] = ($Articles[$key]['CategoryID'])?$Articles[$key]['CategoryID']:1;	
				$Articles[$key]['CategorySlug'] = ($Articles[$key]['CategorySlug'])?$Articles[$key]['CategorySlug']:CONF_ARTCLE_ROOT_URL;
				
			}
	
			$count = GetArticlesCountInCategory( $CategoryID, $enabled, ($ShowSubArticles)?false:true);

			return $Articles;
		}

		
		function GetArticlesProducts($productsIn){
			$products = array();
			$ArticleProducts = array();
			$products = unserialize($productsIn);
			if(is_array($products)){
			foreach($products as $key=>$product){
				$ArticleProducts[$key] = GetProduct($product);
				$RelatedPictures = GetPictures($product);
				foreach($RelatedPictures as $_RelatedPicture){
					if(!$_RelatedPicture['default_picture'])continue;
					if(!file_exists(DIR_PRODUCTS_PICTURES."/".$_RelatedPicture['thumbnail']))break;
					$ArticleProducts[$key]['picture'] = $_RelatedPicture;
					break;
				}
				$ArticleProducts[$key]['Price_s'] = show_price($ArticleProducts[$key]['Price']);
				$ArticleProducts[$key]['list_Price_s'] = show_price($ArticleProducts[$key]['list_price']);
			}
			}
			return $ArticleProducts;		
		}
	
	
		function GetArticlesArticles($articlesIn, $ArticleID, $enabled = false){
			$articles = array();
			$ArticleArticles = array();
			$articles = unserialize($articlesIn);
			if(is_array($articles)){
				foreach($articles as $key=>$article){
					$ArticleArticles[$key] = GetArticleByID($article, true, false);
				}
			}else if(CONF_ARTCLE_RELEATED_ARTICLES == 'True' && !$enabled){	
				$ArticlesRand = db_phquery_fetch(DBRFETCH_ASSOC_ALL, 'SELECT `ArticleID` FROM ?#ARTICLES_TABLE WHERE `ArticleID`<>? order by RAND() limit 0,?',$ArticleID, CONF_ARTCLE_RELEATED_ARTICLES_COUNT);
				foreach($ArticlesRand as $key=>$article){
					$ArticleArticles[$key] = GetArticleByID($article['ArticleID'], true, false);
				}
			}
			return $ArticleArticles;		
		}
		
		
		function GetArticleIDBySlug($ArticleSlug){
			if(empty($ArticleSlug)) return false;
			$ArticleID = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT `ArticleID` FROM ?#ARTICLES_TABLE WHERE `ArticleSlug`=?',$ArticleSlug);
			return  $ArticleID;
		}
		
		
		function GetArticleExists($ArticleID){
			$ArticleID = (int)$CategoryID;
			if(empty($ArticleID)) return false;
			$r = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT COUNT(*) FROM ?#ARTICLES_TABLE WHERE `ArticleID`=?',$ArticleID);
			return  ( $r!= 0 );
		}
	
	
		function GetArticleisAvailableSlug($ArticleSlug,$ArticleID ){
			return !intval(db_phquery_fetch(DBRFETCH_FIRST, 'SELECT 1 FROM ?#ARTICLES_TABLE WHERE `ArticleSlug`=? AND `ArticleID`<>?', $ArticleSlug, $ArticleID));
		}
		
	
		function GetArticleNextAndPrevoius($ArticleID ){
			$ArticleID = (int)$ArticleID;
			if(empty($ArticleID)) return false;	
			
			$Article = GetArticleByID($ArticleID);
			$CategoryID = $Article['ArticleCategoryID'];	

			$params = array(
				'CategoryID'=>$CategoryID,
				'enabled'=>false,
				'DoNotShowSub'=>true,
				'OnlyForNextPrev'=>true
			);
			$Articles = GetArticlesByCategoryID($params);
			foreach($Articles as $key=> $Article){
				if($Article['ArticleID']==$ArticleID){
					$PreviousArticle = (!empty($Articles[$key-1]))?$Articles[$key-1]:false;
					$NextArticle = (!empty($Articles[$key+1]))?$Articles[$key+1]:false;
				}
			}
			return array('Previous'=>$PreviousArticle,'Next'=>$NextArticle);
		}
		
	
		function AddArticle( $Array ){	
			$title_inj = LanguagesManager::sql_prepareFields('ArticleTitle', $Array, true);
			$brief_desc_inj = LanguagesManager::sql_prepareFields('ArticleBriefDescription', $Array,true);
			$desc_inj = LanguagesManager::sql_prepareFields('ArticleDescription', $Array,true);
			$author_inj = LanguagesManager::sql_prepareFields('ArticleAuthor', $Array,true);
			$mtitle_inj = LanguagesManager::sql_prepareFields('ArticleMetaTitle', $Array,true);
			$mkeywords_inj = LanguagesManager::sql_prepareFields('ArticleMetaKey', $Array,true);
			$mdescription_inj = LanguagesManager::sql_prepareFields('ArticleMetaDescription', $Array,true);
			
			
			$fields=$title_inj['fields_list'].','.$brief_desc_inj['fields_list'].','.$desc_inj['fields_list'].','.$author_inj['fields_list'].',';
			$fields.=$mtitle_inj['fields_list'].','.$mkeywords_inj['fields_list'].','.$mdescription_inj['fields_list'];
			
			$values_place=str_repeat('?,',
				count($title_inj['values'])+count($brief_desc_inj['values'])+count($desc_inj['values'])+count($author_inj['values'])+
				count($mtitle_inj['values'])+count($mkeywords_inj['values'])+count($mdescription_inj['values']));
			
			$sql = "INSERT ?#ARTICLES_TABLE ( {$fields}, `ArticleCategoryID`, `ArticleEnable`, `ArticleDate`, `ArticleSlug`, `ArticleProducts`, `ArticleSort`, `ArticleArticles`) ";
			$sql.="VALUES({$values_place}?,?,?,?,?,?,?)";
			db_phquery_array($sql,$title_inj['values'],$brief_desc_inj['values'],$desc_inj['values'],$author_inj['values'],$mtitle_inj['values'],$mkeywords_inj['values'],$mdescription_inj['values'], $Array['ArticleCategoryID'], $Array['ArticleEnable'],$Array['ArticleDate'],$Array['ArticleSlug'], $Array['ArticleProducts'], $Array['ArticleSort'],$Array['ArticleArticles']);		
			return db_insert_id();	
		}
		
		
		function UpdateArticle( $Array ){
			
			$title_inj = LanguagesManager::sql_prepareFields('ArticleTitle', $Array);
			foreach ($title_inj['fields'] as $field) $fields.=$field.'=?,';
			$brief_desc_inj = LanguagesManager::sql_prepareFields('ArticleBriefDescription', $Array);
			foreach ($brief_desc_inj['fields'] as $field) $fields.=$field.'=?,';
			$desc_inj = LanguagesManager::sql_prepareFields('ArticleDescription', $Array);
			foreach ($desc_inj['fields'] as $field) $fields.=$field.'=?,';
			$author_inj = LanguagesManager::sql_prepareFields('ArticleAuthor', $Array);
			foreach ($author_inj['fields'] as $field) $fields.=$field.'=?,';		
			$mtitle_inj = LanguagesManager::sql_prepareFields('ArticleMetaTitle', $Array);
			foreach ($mtitle_inj['fields'] as $field) $fields.=$field.'=?,';		
			$mkeywords_inj = LanguagesManager::sql_prepareFields('ArticleMetaKey', $Array);
			foreach ($mkeywords_inj['fields'] as $field) $fields.=$field.'=?,';		
			$mdescription_inj = LanguagesManager::sql_prepareFields('ArticleMetaDescription', $Array);
			foreach ($mdescription_inj['fields'] as $field) $fields.=$field.'=?,';		
	
			$sql = 'UPDATE ?#ARTICLES_TABLE SET '.$fields.'`ArticleCategoryID`=?, `ArticleEnable`=?, `ArticleDate`=?, `ArticleSlug`=?, `ArticleProducts`=?, `ArticleSort`=?, `ArticleArticles`=?  WHERE ArticleID=?';		
			db_phquery_array($sql,$title_inj['values'],$brief_desc_inj['values'],$desc_inj['values'],$author_inj['values'],$mtitle_inj['values'],$mkeywords_inj['values'],$mdescription_inj['values'], $Array['ArticleCategoryID'], $Array['ArticleEnable'],$Array['ArticleDate'],$Array['ArticleSlug'], $Array['ArticleProducts'], $Array['ArticleSort'], $Array['ArticleArticles'], $Array['ArticleID']);	
			
		}	
		
		
		function DeleteArticle( $ArticleID ){
			$error = '';
			$ArticleID = (int)$ArticleID;
			if(empty($ArticleID)) return false;	
	
			$old_pictures = db_phquery_fetch(DBRFETCH_ASSOC, 'SELECT `ArticleDefaultBigPicture`, `ArticleDefaultSmallPicture`  FROM ?#ARTICLES_TABLE WHERE `ArticleID`=?', $ArticleID);
			if($old_pictures['ArticleDefaultBigPicture'])Functions::exec('file_remove', array(DIR_ARTICLES_PICTURES."/{$old_pictures['ArticleDefaultBigPicture']}"));
			if($old_pictures['ArticleDefaultSmallPicture'])Functions::exec('file_remove', array(DIR_ARTICLES_PICTURES."/{$old_pictures['ArticleDefaultSmallPicture']}"));
					
			$sqls = array();
			$sqls[] = 'DELETE FROM `?#ARTICLES_TABLE` WHERE `ArticleID`=?';
			$sqls[] = 'DELETE FROM `?#ARTICLES_COMMENTS_TABLE` WHERE `ArticleID`=?';
			foreach($sqls as $sql){
				db_phquery($sql,$ArticleID);
			}
			if($error){
				return PEAR::raiseError($error);
			}
		}
			
			
		function GetArticleCommentsTypes(){
			$services = array('system','vkontakte','facebook');
			$checked = explode(',',xHtmlSpecialChars(_getSettingOptionValue( 'CONF_ARTCLE_COMMENTS_SERVICES' )));
			$checked_services = array();
			foreach($services as $service){
				if(in_array($service,$checked)){
					$checked_services[$service] = 'true';
				}
			}
			return $checked_services;
		}
		
		
	#============================
	#       Комментарии			#
	#============================

	
		function CheckDataArticleComments($postData){
			if(!$postData['ArticleID']) return translate("err_article_id");
			if(!$postData['CommentAuthor'] or $postData['CommentAuthor']==translate("str_your_name")) return translate("err_input_nickname");
			if(!$postData['CommentText'] or $postData['CommentText']==translate("prddiscussion_body")) return translate("err_input_message");
			return false;		
		}
		
		
		function AddArticleComments($postData){
			$sql = "INSERT ?#ARTICLES_COMMENTS_TABLE ( `ArticleID`, `CommentAuthor`, `CommentText`, `CommentDate`) VALUES(?,?,?,?)";
			db_phquery_array($sql,$postData['ArticleID'],$postData['CommentAuthor'],$postData['CommentText'], Time::datetime());		
			return db_insert_id();	
		}
		
		
		function GetArticleCommentsByArticleID($ArticleID){
			$ArticleID = (int)$ArticleID;
			if(empty($ArticleID)) return false;	
			$sql = "SELECT * FROM ?#ARTICLES_COMMENTS_TABLE WHERE `ArticleID`=? ORDER BY `CommentDate` DESC";
			$Comments = db_phquery_fetch(DBRFETCH_ASSOC_ALL, $sql, $ArticleID);
			return $Comments;	
		}
	
	
		function GetArticleCommentsByCommentID($CommentID){
			$CommentID = (int)$CommentID;
			if(empty($CommentID)) return false;	
			$sql = "SELECT * FROM ?#ARTICLES_COMMENTS_TABLE WHERE `CommentID`=?";
			$Comments = db_phquery_fetch(DBRFETCH_ASSOC, $sql, $CommentID);
			return $Comments;	
		}
		
		
		function DeleteArticleComment( $CommentID ){
			$error = '';
			$CommentID = (int)$CommentID;
			if(empty($CommentID)) return false;	
			$sql = 'DELETE FROM `?#ARTICLES_COMMENTS_TABLE` WHERE `CommentID`=?';
			db_phquery($sql,$CommentID);
			if($error){
				return PEAR::raiseError($error);
			}
		}


?>