<?php

class articles extends ComponentModule  {
	
	function __construct(){
		$this->CategoryParent = (int)$_GET['CategoryParent'];
		$this->CurrentPage = (int)$_GET['page'];
		
		$this->CategorySlug = $_GET['CategorySlug'];
		$this->postSlug 	= $_GET['postSlug'];
		
		$this->ArticleID = ($this->postSlug)?GetArticleIDBySlug($this->postSlug):(int)$_GET['ArticleID'];
		$this->CategoryID = ($this->CategorySlug)?GetArticleCategoryIDBySlug($this->CategorySlug):(int)$_GET['CategoryID'];
		
		parent::__construct();
	}
	
	
	function initInterfaces(){
	
		$this->__registerInterface('articles_admin', 'articles_admin', INTCALLER, 'ArticleDisplayToAdmin');
		$this->__registerInterface('articles_admin_comments', 'articles_admin_comments', INTCALLER, 'ArticlesCommentDisplayToAdmin');
		$this->__registerInterface('articles_category_edit', 'articles_category_edit', INTCALLER, 'ArticleCategoryEdit');
		$this->__registerInterface('articles_category_tree', 'articles_category_tree', INTCALLER, 'ArticleCategoryTree');
		$this->__registerInterface('article_edit', 'article_edit', INTCALLER, 'ArticleEdit');
		$this->__registerComponent('articles', 'articles', array('general_layout', 'home_page'), 'ArticleDisplayToPublic');
		$this->__registerComponent('articles_short_list', 'articles_short_list', array('general_layout', 'home_page'),'Articles_short', 
			array(		
				'articles_by_categoryes' => array(
					'type' => 'select',
					'params' => array(
						'name' => 'articles_by_categoryes', 
						'title' => 'pgn_articles_by_categoryes', 
						'options' => array(
							'0' => 		'pgn_choose_value',
							'true' => 	'pgn_yes',
							'false' => 	'pgn_no' 
							),
						'onchange' => '
							getLayer("cpt-layer-articles_colomns").style.display = select_getCurrValue(this)=="true"?"":"none";
							getLayer("cpt-layer-articles_count_categoryes").style.display = select_getCurrValue(this)=="true"?"":"none";
							getLayer("cpt-layer-articles_count_without_categoryes").style.display = select_getCurrValue(this)=="false"?"":"none";	
							if(select_getCurrValue(this)=="0"){select_selectOptionByValue(getLayer("cpts-articles_categoryes-articles_categoryes"),"all")};
							getLayer("cpt-layer-articles_sub_categoryes").style.display = select_getCurrValue(this)!="0"?"":"none";
							getLayer("cpt-layer-articles_categoryes").style.display = select_getCurrValue(this)!="0"?"":"none";
							getLayer("cpt-layer-articles_order").style.display = select_getCurrValue(this)!="0"?"":"none";
							getLayer("cpt-layer-articles_categoryes_select").style.display = select_getCurrValue(this)=="0"?"none":getLayer("cpt-layer-articles_categoryes_select").style.display;
						', 
					)
				),
				'articles_count_categoryes' => array(
					'type' => 'text', 
					'params' => array(
						'name' => 'articles_count_categoryes', 
						'title'=> translate('pgn_articles_count_categoryes').'<script type="text/javascript">if(select_getCurrValue(getLayer("cpts-articles_by_categoryes-articles_by_categoryes"))!="true"){getLayer("cpt-layer-articles_count_categoryes").style.display = "none";}</script>', 
						'value'=> '3'
						)
				),
				'articles_count_without_categoryes' => array(
					'type' => 'text', 
					'params' => array(
						'name' => 'articles_count_without_categoryes', 
						'title'=> translate('pgn_articles_count_without_categoryes').'<script type="text/javascript">if(select_getCurrValue(getLayer("cpts-articles_by_categoryes-articles_by_categoryes"))!="false"){getLayer("cpt-layer-articles_count_without_categoryes").style.display = "none";}</script>', 
						'value'=> '10'
						)
				),
				'articles_sub_categoryes' => array(
					'type' => 'checkboxgroup',
					'params' => array(
						'name' => 'articles_sub_categoryes', 
						'title' => '', 
						'options' => array(
							'yep' => 'pgn_articles_sub_categoryes'
							),
						'before_load' => '
							<script type="text/javascript">
	if(select_getCurrValue(getLayer("cpts-articles_by_categoryes-articles_by_categoryes"))=="0"){getLayer("cpt-layer-articles_sub_categoryes").style.display = "none";}						
							</script>'
					)
				), 
				'articles_categoryes' => array(
					'type' => 'select',
					'params' => array(
						'name' => 'articles_categoryes', 
						'title' => 'pgn_articles_categoryes', 
						'options' => array(
							'all' => 'pgn_all_articles_categoryes', 
							'selected' => 'pgn_choose_categoryes'
							), 
						'default_value' => 'all',	
						'onchange' => '
							getLayer("cpt-layer-articles_categoryes_select").style.display=select_getCurrValue(this)=="all"?"none":"";	
						',
						'onload' => '
							<script type="text/javascript">						
	if(select_getCurrValue(getLayer("cpts-articles_by_categoryes-articles_by_categoryes"))=="0"){getLayer("cpt-layer-articles_categoryes").style.display = "none";}						
							</script>'
					)
				), 
				'articles_categoryes_select' => array(
					'type' => 'articles_categoryes_select', 
					'params' => array(
						'name' => 'articles_categoryes_select', 
						'title'=> 'pgn_articles_categoryes',
						'value'=> '', 
						'options'=> array(), 
						'before_load' => '
							<script type="text/javascript">
	if(select_getCurrValue(getLayer("cpts-articles_categoryes-articles_categoryes"))=="all"){getLayer("cpt-layer-articles_categoryes_select").style.display = "none";}		
							</script>'
					)
				),	
				'articles_colomns' => array(
					'type' => 'text', 
					'params' => array(
						'name' => 'articles_colomns', 
						'title'=> translate('pgn_articles_colomns').'<script type="text/javascript">if(select_getCurrValue(getLayer("cpts-articles_by_categoryes-articles_by_categoryes"))=="0"){getLayer("cpt-layer-articles_colomns").style.display = "none";}</script>', 
						'value'=> '1'
						)
				),
				'articles_order' => array(
					'type' => 'radiogroup',
					'params' => array(
						'name' => 'articles_order', 
						'title' => translate('pgn_sort').'<script type="text/javascript">if(select_getCurrValue(getLayer("cpts-articles_by_categoryes-articles_by_categoryes"))=="0"){getLayer("cpt-layer-articles_order").style.display = "none";}</script>', 
						'options' => array(
							'sort' => 'pgn_sort_proirity', 
							'rand' => 'pgn_sort_rand', 
							'date' => 'pgn_sort_last'
							), 
						'default_value' => 'sort'
					)
				), 
			)
		);	
	}	

	

	function ArticleDisplayToAdmin(){
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		$GetVars = &$Register->get(VAR_GET);
		
		if(isset($_GET['show_all'])){
			set_query('show_all=','',true);
			$show_all = true;
			$Register->set('show_all', $show_all);
		}
		
		if ( isset($_GET["expandCat"]) ){
			catExpandCategory( $_GET["expandCat"], "expandedArticlesCategoryID_Array" );
			renderURL('expandCat=', '', true);
		}
		if ( isset($_GET["shrinkCat"]) ){
			catShrinkCategory( $_GET["shrinkCat"], "expandedArticlesCategoryID_Array" );
			renderURL('shrinkCat=', '', true);
		}
		if ( !isset($_SESSION["expandedArticlesCategoryID_Array"]) )
		$_SESSION["expandedArticlesCategoryID_Array"] = array( 1 );
	
		$Categorys 	= GetRecursiveArticlesCategorys( 1, 0, true, $_SESSION["expandedArticlesCategoryID_Array"] );
		$Category 	= GetArticleCategoryByID( $this->CategoryID, true);
		
		
		$offset	= 0;
		$count = 0;
		$params = array(
			'CategoryID'=>($this->CategoryID)?$this->CategoryID:1,
			'enabled'=>true,
			'DoNotShowSub'=>true
		);
			
		$NavigatorHtml =  GetNavigatorHtml(	 'CategoryID='.$CategoryID, CONF_ARTCLE_COUNT_ON_PAGE, 'GetArticlesByCategoryID', $params, $Articles, $offset, $count );

		$smarty->assign('CategoryID',$this->CategoryID);
		$smarty->assign('Categorys',$Categorys);
		$smarty->assign('Category',$Category);
		$smarty->assign('NavigatorHtml',$NavigatorHtml);
		$smarty->assign('Articles',$Articles);
		$smarty->assign('articles_in_root_category',GetArticlesCountInCategory(1, true));
		
		$smarty->assign( "admin_sub_dpt", 'categories_articles.html' );
	}
	
	
	function ArticlesCommentDisplayToAdmin(){
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
	
		if( isset($_GET['answer']) ){
			$discussion = GetArticleCommentsByCommentID( $_GET['answer'] );
			$smarty->assign( 'discussion', $discussion );
			$smarty->assign( 'answer', 1);
		}else{
			$gridEntry = new Grid();
			$gridEntry->rows_num = 30;
			$gridEntry->show_rows_num_select = false;
			
			$gridEntry->registerHeader('prdreview_postaddtime', 'CommentDate', true, 'desc');
			$gridEntry->registerHeader('pgn_post');
			$gridEntry->registerHeader('pgn_name');
			$gridEntry->registerHeader('pgn_text');
			$gridEntry->registerHeader('btn_delete');
			$gridEntry->setRowHandler('
				$row["CommentDate"] = Time::standartTime($row["CommentDate"]);
				return $row;
			');
			
			$gridEntry->show_rows_num_select = false;
			$gridEntry->query_total_rows_num = 'SELECT COUNT(*) FROM ?#ARTICLES_COMMENTS_TABLE';
			$gridEntry->query_select_rows = '
				SELECT t1.*, '.LanguagesManager::sql_prepareField('ArticleTitle').' AS ArticleTitle 
				FROM ?#ARTICLES_COMMENTS_TABLE t1, ?#ARTICLES_TABLE t2 
				WHERE t1.ArticleID=t2.ArticleID
				';
			$gridEntry->prepare();
		}
		$smarty->assign( "admin_sub_dpt", 'article_comments.html' );
	}
	
	
	
	function ArticleCategoryEdit(){
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		$GetVars = &$Register->get(VAR_GET);
		
		$smarty->assign('CategoryID',$this->CategoryID);
		if(!empty($this->CategoryID)){
			$Category = GetArticleCategoryByID( $this->CategoryID, true );
			if ($Category['CategoryDefaultPicture'] != '' && file_exists(DIR_ARTICLES_PICTURES."/".$Category['CategoryDefaultPicture'])){
				list( $width, $height, $type, $attr ) = getimagesize( DIR_ARTICLES_PICTURES."/".$Category['CategoryDefaultPicture'] );
				$Category['picture_href'] = "open_window('".URL_ARTICLES_PICTURES."/{$Category['CategoryDefaultPicture']}',$width,$height);return false;";
			}else {
				$Category['CategoryDefaultPicture'] = '';
			}
			
			$smarty->assign('Category',$Category);
			$CategoryParent = $Category['CategoryParent'];
		}else{
			$CategoryParent = $this->CategoryParent;
		}
		$parent_category = GetArticleCategoryByID($CategoryParent) ;
		$parent_category['calculated_path'] = CalculatePathToArticleCategory($parent_category['CategoryID']);
		$smarty->assign('parent_category',$parent_category);
		
		$smarty->assign( "admin_sub_dpt", 'article_category_edit.html' );
	}
		
		
	function ArticleEdit(){
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		$GetVars = &$Register->get(VAR_GET);	

		$Article = GetArticleByID($this->ArticleID,true);
		if ($Article['ArticleDefaultBigPicture'] != '' && file_exists(DIR_ARTICLES_PICTURES."/".$Article['ArticleDefaultBigPicture'])){
			list( $width, $height, $type, $attr ) = getimagesize( DIR_ARTICLES_PICTURES."/".$Article['ArticleDefaultBigPicture'] );
			$Article['picture_href'] = "open_window('".URL_ARTICLES_PICTURES."/{$Article['ArticleDefaultBigPicture']}',$width,$height);return false;";
		}else {
			$Article['ArticleDefaultBigPicture'] = '';
		}

		$smarty->assign('Article',$Article);
			
		if(!empty($this->CategoryID)){	
			$parent_category = GetArticleCategoryByID($this->CategoryID) ;
		}else{
			$parent_category = GetArticleCategoryByID($Article['ArticleCategoryID']);
		}
		$parent_category['calculated_path'] = CalculatePathToArticleCategory($parent_category['CategoryID']);
		$smarty->assign('parent_category',$parent_category);
		$smarty->assign('CurrentDate',Time::date());
		$smarty->assign( "admin_sub_dpt", 'article_edit.html' );
	}
	

	function ArticleCategoryTree(){
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		$GetVars = &$Register->get(VAR_GET);
		
		$categories = GetRecursiveArticlesCategorys( 1, 0, true, $_SESSION["expandedArticlesCategoryID_Array"] );
		$js_action = isset($GetVars['js_action'])?$GetVars['js_action']:'';
		
		$smarty->assign('categories', $categories);
		$smarty->assign('js_action', $GetVars['js_action']);	
	}
	
	
	
	function ShowRssFeed(){
		header("Content-Type: application/rss+xml; charset=UTF-8");
		$out.='<?xml version="1.0" encoding="UTF-8"?>';	
		$out.="
		<rss xmlns:yandex='http://news.yandex.ru' version='2.0'>
		<channel>
			<title>".translate('pgn_articles')." ".CONF_SHOP_NAME."</title>
			<link xmlns:xi='http://www.w3.org/2001/XInclude' xmlns:lego='".CONF_FULL_SHOP_URL."'>".CONF_FULL_SHOP_URL."</link>
			<description>
				".CONF_DEFAULT_TITLE."
			</description>
		";	
		$Articles = GetArticlesRSS();
		foreach($Articles as $Article){
			$out.="<item>";
				$title = preg_replace("/&/","",$Article['ArticleTitle']);
				$out.="<title>{$title}</title>";
				$description = strip_tags($Article['ArticleBriefDescription']);
				$description = preg_replace("/&#?[a-z0-9]{2,8};/i","",$description);
				
				$image = ($Article['ArticleDefaultSmallPicture'])?'<img style="float: left!important; margin: 10px;"  src="'.URL_ARTICLES_PICTURES.'/'.$Article['ArticleDefaultSmallPicture'].'" />':'';
				$descriptionOut = '<![CDATA['.$image.$description.']]>';
				
				$out.="<description>{$descriptionOut}</description>";
				$out.="<pubDate>{$Article['ArticleDate']}</pubDate>";
				$pubDateUT = date("r",$Article['ArticleDate']);
				$out.="<pubDateUT>$updt</pubDateUT>";

				$out.= "<link>".preg_replace('|/$|','',CONF_FULL_SHOP_URL).set_query("?ukey=".CONF_ARTCLE_URL."&CategoryID={$Article['ArticleCategoryID']}&CategorySlug={$Article['CategorySlug']}&postID={$Article['ArticleID']}&postSlug={$Article['ArticleSlug']}".(MOD_REWRITE_SUPPORT?"&furl_enable=1":""))."</link>";
				
				$out.= "<guid>".preg_replace('|/$|','',CONF_FULL_SHOP_URL).set_query("?ukey=".CONF_ARTCLE_URL."&CategoryID={$Article['ArticleCategoryID']}&CategorySlug={$Article['CategorySlug']}&postID={$Article['ArticleID']}&postSlug={$Article['ArticleSlug']}".(MOD_REWRITE_SUPPORT?"&furl_enable=1":""))."</guid>";
				
			$out.="</item>";
		}		 
		$out.='</channel>
		</rss>';
		print $out;
		exit();
	}
	
	
	function Articles_short($call_settings = null){
		
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		$GetVars = &$Register->get(VAR_GET);
		
		$local_settings = isset($call_settings['local_settings']) ? $call_settings['local_settings'] : array();
		if($local_settings['articles_by_categoryes']=='0')return false;	
		$groupByCategory = ($local_settings['articles_by_categoryes']=='true')?true:false;
		
		if($groupByCategory)
			$count = ($local_settings['articles_count_categoryes'])?$local_settings['articles_count_categoryes']:1;
		else
			$count = ($local_settings['articles_count_without_categoryes'])?$local_settings['articles_count_without_categoryes']:1;
			
		$params = array(
			'count'=>$count,
			'groupByCategory'=>$groupByCategory,
			'sub_categorys' => $local_settings['articles_sub_categoryes'],
			'categoryes' => $local_settings['articles_categoryes'],
			'categoryes_select' => $local_settings['articles_categoryes_select'],
			'order' => $local_settings['articles_order']
		);	
		$Articles = GetArticlesShort($params);
		
		$colomns = ($groupByCategory)?$local_settings['articles_colomns']:1;
		$smarty->assign('colomns',$colomns);
		$smarty->assign('groupByCategory',$groupByCategory);
		$smarty->assign('ShortArticles',$Articles);
		
		$smarty->display('articles.frontend.shortlist.tpl.html'); 
	}

		
	function ArticleDisplayToPublic(){
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		$GetVars = &$Register->get(VAR_GET);
	
		$CategoryID = ($this->CategoryID)?$this->CategoryID:($this->CategorySlug)?false:1;
		if($this->CategoryID){
			$CategoryID = $this->CategoryID;
		}else if(!$this->CategoryID && !$this->CategorySlug){
			$CategoryID = 1;
		}else if(!$this->CategoryID && $this->CategorySlug){
			$smarty->assign("page_not_found404", 'yes');
			return false;
		}
		if(isset($_GET['rss'])){	
			return $this->ShowRssFeed();
		}else if(!$this->ArticleID){
			return $this->ArticleCategoriesDisplay($CategoryID);
		}else{
			return $this->ArticleTopicDisplay();
		}
	}

	
	function ArticleCategoriesDisplay($CategoryID){
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		$GetVars = &$Register->get(VAR_GET);				
				
		$Categorys 	= GetRecursiveArticlesCategorys( $CategoryID, 0, false, false );
		$Category = GetArticleCategoryByID($CategoryID);
		if(!$Category && $CategoryID!=1) {
			$smarty->assign("page_not_found404", 'yes');
		}
		$product_category_path = CalculatePathToArticleCategory($CategoryID);
		
		$offset	= 0;
		$count = 0;
		$params = array(
			'CategoryID'=>$CategoryID,
			'enabled'=>false,
			'DoNotShowSub'=>false
			);
		if(isset($_GET['show_all'])){
			set_query('show_all=','',true);
			$show_all = true;
			$Register->set('show_all', $show_all);
		}
		
		$pathtonavigator = ($CategoryID == 1)?'CategoryID=1&CategorySlug='.CONF_ARTCLE_ROOT_URL:'CategoryID='.$CategoryID.'&CategorySlug='.$this->CategorySlug;
		$NavigatorHtml =  GetNavigatorHtml( $pathtonavigator, CONF_ARTCLE_COUNT_ON_PAGE, 'GetArticlesByCategoryID', $params, $Articles, $offset, $count );

		$smarty->assign( 'NavigatorHtml', $NavigatorHtml);
		$smarty->assign( 'product_category_path', $product_category_path);
		$smarty->assign( 'Category', $Category);
		$smarty->assign( 'Categorys', $Categorys);
		$smarty->assign( 'Articles', $Articles);
		
		$meta_keywords 	  = $Category['CategoryMetaKey'];
		$meta_description = $Category['CategoryMetaDescription'];
		$meta_tags .= '<meta name="description" content="'.$meta_description.'">'."\n";
		$meta_tags .= '<meta name="keywords" content="'.$meta_keywords.'">'."\n";			
		if($meta_keywords or $meta_description){
			$smarty->assign("page_meta_tags", $meta_tags );
		}
		if($Category['CategoryMetaTitle'] || $Category['CategoryTitle'] )
			$smarty->assign("page_title", ($Category['CategoryMetaTitle'])?$Category['CategoryMetaTitle'].' ― '.CONF_SHOP_NAME:$Category['CategoryTitle'].' ― '.CONF_SHOP_NAME);
		
		$smarty->assign( 'main_content_template', 'articles.frontend.list.tpl.html');
	}
	
	
	function ArticleTopicDisplay(){
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		$GetVars = &$Register->get(VAR_GET);		
		$Message = $Register->get(VAR_MESSAGE);			
		if(Message::isMessage($Message) && $Message->is_set() && isset($Message->Comment))$smarty->assign('Comment', $Message->Comment);
			
		$ArticleID = $this->ArticleID;
		$Article = GetArticleByID($ArticleID);
	
		if(!$Article || $Article['ArticleEnable']!=1){
			$smarty->assign("page_not_found404", 'yes');
			return false;
		}
			
			
		$Category = GetArticleCategoryByID($Article['ArticleCategoryID']);
		$product_category_path = CalculatePathToArticleCategory($Article['ArticleCategoryID']);	
		$smarty->assign( 'product_category_path', $product_category_path);
		$smarty->assign( 'Category', $Category);	
		$smarty->assign( 'Article', $Article);	
		

		$CommentsTypes = GetArticleCommentsTypes();
		$smarty->assign( 'CommentsTypes', $CommentsTypes);	
		$Comments = GetArticleCommentsByArticleID($ArticleID);
		$smarty->assign("Comments", $Comments );
			
		$ArticleNextAndPrevoius = GetArticleNextAndPrevoius($ArticleID);	
		$smarty->assign("ArticleNextAndPrevoius", $ArticleNextAndPrevoius );
			
		$meta_keywords 	  = $Article['ArticleMetaKey'];
		$meta_description = $Article['ArticleMetaDescription'];
		$meta_tags .= '<meta name="description" content="'.$meta_description.'">'."\n";
		$meta_tags .= '<meta name="keywords" content="'.$meta_keywords.'">'."\n";			
		if($meta_keywords or $meta_description){
			$smarty->assign("page_meta_tags", $meta_tags );
		}
		$smarty->assign("page_title", ($Article['ArticleMetaTitle'])?$Article['ArticleMetaTitle'].' ― '.CONF_SHOP_NAME:$Article['ArticleTitle'].' ― '.CONF_SHOP_NAME);

		$smarty->assign( 'main_content_template', 'article.frontend.post.tpl.html');
	
	}
	
	
	
}

 
class ArticlesController extends ActionsController{
	
	function __construct(){
		parent::__construct();
	}
	
	
	function expandCategory(){
		if((int)$this->getData('return_subs')){
			global $_RESULT;
			$_RESULT['categories'] = GetRecursiveArticlesCategorys( $this->getData('CategoryID'), 0, true, $_SESSION["expandedArticlesCategoryID_Array"] );
		}
		catExpandCategory($this->getData('CategoryID'), 'expandedArticlesCategoryID_Array');
		die;
	}
	
	
	function collapseCategory(){
		catShrinkCategory($this->getData('CategoryID'), 'expandedArticlesCategoryID_Array');
		die;
	}
	
	
	function getCategoryArticles(){
		global $_RESULT;	
		$per_page = 15;	
		
		$navigatorParams = array('offset' => intval($this->getData('offset')), 'CountRowOnPage' => $per_page);
		$params = array(
			'CategoryID'=>($this->getData('categoryID'))?$this->getData('categoryID'):1,
			'enabled'=>true,
			'DoNotShowSub'=>true
		);
		$_RESULT['articles'] = GetArticlesByCategoryID( $params, $count, $navigatorParams);
		 
		if($count>($navigatorParams['offset'] + $per_page)){
			
			$_RESULT['next_offset'] = $navigatorParams['offset'] + $per_page;
		}
		if($navigatorParams['offset']>0){
			
			$_RESULT['prev_offset'] = $navigatorParams['offset'] - $per_page;
			if($_RESULT['prev_offset']<0)$_RESULT['prev_offset'] = 0;
		} 
		die;
	}
	
	
	function save_article(){

		$res = null;do{
			if(LanguagesManager::ml_isEmpty('ArticleTitle', $this->getData())){
				$res = PEAR::raiseError('catset_empty_name');
				break;
			}
			$ArticleID = isset($_GET['ArticleID'])?intval($_GET['ArticleID']):0;
			
			if(!$this->getData('ArticleSort')){
				$this->setData('ArticleSort', 0);
			}else{
				$sort = $this->getData('ArticleSort');
				$this->setData('ArticleSort', (int)$sort);
			}
			
			if(!$this->getData('ArticleDate')){
				$this->setData('ArticleDate', Time::date());
			}
			$this->setData('ArticleLike', (int)$this->getData('ArticleLike'));
			$this->setData('ArticleDislike', (int)$this->getData('ArticleDislike'));
		

			$make_slug = false;
			if(!$this->getData('ArticleSlug')){
				$make_slug = true;
			}
			if($make_slug){
				$this->setData('ArticleSlug', make_slug(LanguagesManager::ml_getFieldValue('ArticleTitle', $this->getData())));
				$make_slug = $this->getData('ArticleSlug')!=='';
			}else{
				$this->setData('ArticleSlug', make_slug($this->getData('ArticleSlug')));
			}
			$max_i = 50; $_slug = $this->getData('ArticleSlug');
			
			while($max_i-- && !GetArticleisAvailableSlug($_slug, $ArticleID)){
				$_slug = $this->getData('ArticleSlug').'_'.rand_name(2);
			}
			if(!$max_i){
				$_slug .= '_'.rand_name(2);
			}
			$this->setData('ArticleSlug', $_slug);
			$this->setData('ArticleProducts', serialize($this->getData('related_products')));
			$this->setData('ArticleArticles', serialize($this->getData('ArticleArticles')));
			
			if(!$ArticleID){
				$ArticleID = AddArticle($this->getData());
			}else{
				$res = UpdateArticle($this->getData());
				if(PEAR::isError($res))break;
			}
	
	
			if (isset($_FILES["ArticleDefaultBigPicture"]) && $_FILES["ArticleDefaultBigPicture"]["name"] && is_image($_FILES["ArticleDefaultBigPicture"]["name"])){

				$old_pictures = db_phquery_fetch(DBRFETCH_ASSOC, 'SELECT `ArticleDefaultBigPicture`, `ArticleDefaultSmallPicture`  FROM ?#ARTICLES_TABLE WHERE `ArticleID`=?', $ArticleID);
				if($old_pictures['ArticleDefaultBigPicture'])Functions::exec('file_remove', array(DIR_ARTICLES_PICTURES."/{$old_pictures['ArticleDefaultBigPicture']}"));
				if($old_pictures['ArticleDefaultSmallPicture'])Functions::exec('file_remove', array(DIR_ARTICLES_PICTURES."/{$old_pictures['ArticleDefaultSmallPicture']}"));

				$picture_name = str_replace(" ","_", $_FILES["ArticleDefaultBigPicture"]["name"]);
				$picture_name = getUnicFile(2, preg_replace('@\.([^\.]+)$@', '%s.$1', $picture_name), DIR_ARTICLES_PICTURES);
				$picture_name_thm = 'thm_'.getUnicFile(2, preg_replace('@\.([^\.]+)$@', '%s.$1', $picture_name), DIR_ARTICLES_PICTURES);
									
			 	$res = Functions::exec('file_move_uploaded', array($_FILES["ArticleDefaultBigPicture"]["tmp_name"], DIR_TEMP."/$picture_name"));
				if (PEAR::isError($res))Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage());
				
				$res = Functions::exec('img_resize', array(DIR_TEMP."/$picture_name", CONF_ARTCLE_DEFPIC_BIG_SIZE, CONF_ARTCLE_DEFPIC_BIG_SIZE, DIR_TEMP."/$picture_name"));
				if (PEAR::isError($res))Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage());		
				
				$res = Functions::exec('img_resize', array(DIR_TEMP."/$picture_name", CONF_ARTCLE_DEFPIC_SMALL_SIZE, CONF_ARTCLE_DEFPIC_SMALL_SIZE, DIR_TEMP."/$picture_name_thm"));
				if (PEAR::isError($res))Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage());		
		
				$res = Functions::exec('file_copy', array(DIR_TEMP."/$picture_name", DIR_ARTICLES_PICTURES."/$picture_name"));
				if (PEAR::isError($res))Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage());
				
				$res = Functions::exec('file_copy', array(DIR_TEMP."/$picture_name_thm", DIR_ARTICLES_PICTURES."/$picture_name_thm"));
				if (PEAR::isError($res))Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage());
							
				Functions::exec('file_remove', array(DIR_TEMP."/$picture_name"));
				Functions::exec('file_remove', array(DIR_TEMP."/$picture_name_thm"));
				SetRightsToUploadedFile( DIR_ARTICLES_PICTURES."/$picture_name" );
				SetRightsToUploadedFile( DIR_ARTICLES_PICTURES."/$picture_name_thm" );
				
				$sql = 'UPDATE ?#ARTICLES_TABLE SET `ArticleDefaultBigPicture`=?, `ArticleDefaultSmallPicture`=? WHERE `ArticleID`=?';
				db_phquery($sql, $picture_name, $picture_name_thm, $ArticleID);
			}
			
			
		}while(0);
			if(PEAR::isError($res)){
				Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage(), '', array('post_data' => $this->getData()));
			}else{
				Message::raiseMessageRedirectSQ(MSG_SUCCESS, '?ukey=articles_admin&CategoryID=', 'msg_information_save');
			}		
	}	
	
		
	function delete_article(){		
		safeMode(true);
		$res = DeleteArticle( $_GET['ArticleID'] );
		RedirectSQ('?ukey=articles_admin&CategoryID=');
	}
	

	function remove_article_picture(){
		safeMode(true);
		$old_pictures = db_phquery_fetch(DBRFETCH_ASSOC, 'SELECT `ArticleDefaultBigPicture`, `ArticleDefaultSmallPicture`  FROM ?#ARTICLES_TABLE WHERE `ArticleID`=?', $_GET['ArticleID']);
		if($old_pictures['ArticleDefaultBigPicture'])Functions::exec('file_remove', array(DIR_ARTICLES_PICTURES."/{$old_pictures['ArticleDefaultBigPicture']}"));
		if($old_pictures['ArticleDefaultSmallPicture'])Functions::exec('file_remove', array(DIR_ARTICLES_PICTURES."/{$old_pictures['ArticleDefaultSmallPicture']}"));
		$sql = 'UPDATE ?#ARTICLES_TABLE SET ArticleDefaultBigPicture="", ArticleDefaultSmallPicture="" WHERE ArticleID=?';
		db_phquery($sql, $_GET['ArticleID']);
		RedirectSQ('');
	}

		
	function duplicate_selected(){
	
		$m_ArticleID = array_keys(scanArrayKeysForID($this->getData(), 'select_articles'));
		$m_ArticleID = array_map('intval',$m_ArticleID);
		$m_ArticleID = array_unique($m_ArticleID);
		
		$duplicated_count = 0;
		foreach ($m_ArticleID as $ArticleID){
			$Article = GetArticleByID($ArticleID);
	
			$max_i = 50; $_slug = $Article['ArticleSlug'];
			while($max_i-- && !GetArticleisAvailableSlug($_slug, 0)){
				$_slug = $Article['ArticleSlug'].'_'.rand_name(2);
			}
			if(!$max_i){
				$_slug .= '_'.rand_name(2);
			}
			$Article['ArticleSlug'] = $_slug;
			
			unset($Article['ArticleProducts']);
			unset($Article['ArticleArticles']);
			$Article['ArticleProducts'] = $Article['ArticleProducts_array'];
			$Article['ArticleArticles'] = $Article['ArticleArticles_array'];
			
	
			$Titles = LanguagesManager::ml_getLangFieldNames('ArticleTitle');
			foreach($Titles as $Title){
				$Article[$Title] = ($Article[$Title])?$Article[$Title].' (1)':'';
			}
			$ArticleID = AddArticle($Article);
			
			
			if($Article['ArticleDefaultBigPicture'] && file_exists(DIR_ARTICLES_PICTURES.'/'.$Article['ArticleDefaultBigPicture'])){
				$duplicate_file_name = getUnicFile(2, preg_replace('@\.([^\.]+)$@', '%s.$1', $Article['ArticleDefaultBigPicture']), DIR_ARTICLES_PICTURES);
				$res = Functions::exec('file_copy', array(DIR_ARTICLES_PICTURES.'/'.$Article['ArticleDefaultBigPicture'], DIR_ARTICLES_PICTURES.'/'.$duplicate_file_name));
				if(PEAR::isError($res)){
					$error = $res;
					break;
				}
				$sql = 'UPDATE ?#ARTICLES_TABLE SET `ArticleDefaultBigPicture`=? WHERE `ArticleID`=?';
				db_phquery($sql, $duplicate_file_name, $ArticleID);
			}
			
			if($Article['ArticleDefaultSmallPicture'] && file_exists(DIR_ARTICLES_PICTURES.'/'.$Article['ArticleDefaultSmallPicture'])){
				$duplicate_file_name = getUnicFile(2, preg_replace('@\.([^\.]+)$@', '%s.$1', $Article['ArticleDefaultSmallPicture']), DIR_ARTICLES_PICTURES);
				$res = Functions::exec('file_copy', array(DIR_ARTICLES_PICTURES.'/'.$Article['ArticleDefaultSmallPicture'], DIR_ARTICLES_PICTURES.'/'.$duplicate_file_name));
				if(PEAR::isError($res)){
					$error = $res;
					break;
				}
				$sql = 'UPDATE ?#ARTICLES_TABLE SET `ArticleDefaultSmallPicture`=? WHERE `ArticleID`=?';
				db_phquery($sql, $duplicate_file_name, $ArticleID);
			}
			$duplicated_count++;
		}
			
		Message::raiseMessageRedirectSQ($error?MSG_ERROR:MSG_SUCCESS, '', ($error?$error->getMessage():sprintf(translate('articles_n_duplicated'),$duplicated_count)).$msg);
	}
	
		
	function delete_selected(){	
		safeMode(true);
		$m_ArticleID = array_keys(scanArrayKeysForID($this->getData(), 'select_articles'));
		$m_ArticleID = array_map('intval',$m_ArticleID);
		$m_ArticleID = array_unique($m_ArticleID);
		foreach ($m_ArticleID as $ArticleID){
			DeleteArticle($ArticleID, 0);
		}
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_information_save');
	}
	
	
	function move_selected(){
		if(isset($_POST['categoryID'])){
			$categoryID = intval($this->getData('categoryID'));
			$m_ArticleID = array_keys(scanArrayKeysForID($this->getData(), 'select_articles'));
			$m_ArticleID = array_map('intval',$m_ArticleID);
			$m_ArticleID = array_unique($m_ArticleID);
		
			$sql = 'UPDATE `?#ARTICLES_TABLE` SET `ArticleCategoryID` =? WHERE `ArticleID` IN (?@)';
			db_phquery($sql,$categoryID,$m_ArticleID);
		}
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '&CategoryID='.$categoryID, 'msg_information_save');
	}
	
		
	function save_articles(){
		safeMode(true);
		$data = scanArrayKeysForID($_POST, array("sort_order" ) );
		foreach( $data as $key => $val ){
			$sqlValues = array();
			if ( isset($val["sort_order"]) ){
				$sqlValues[] = 'ArticleSort = '.intval($val["sort_order"]);
			}
			if(count($sqlValues)){
				$sql = 'UPDATE `'.ARTICLES_TABLE.'` SET '.implode(', ',$sqlValues).' WHERE ArticleID='.intval($key);
				db_query($sql);
			}
		}
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_information_save');
	}
	
		
	function save_category(){
		$res = null;do{
			if(LanguagesManager::ml_isEmpty('CategoryTitle', $this->getData())){
				$res = PEAR::raiseError('catset_empty_name');
				break;
			}
			$CategoryID = isset($_GET['CategoryID'])?intval($_GET['CategoryID']):0;
			
			if(!$this->getData('CategorySort')){
				$this->setData('CategorySort', 0);
			}else{
				$sort = $this->getData('CategorySort');
				$this->setData('CategorySort', (int)$sort);
			}
			$make_slug = false;
			if(!$this->getData('CategorySlug')){
				$make_slug = true;
			}
			if($make_slug){
				$this->setData('CategorySlug', make_slug(LanguagesManager::ml_getFieldValue('CategoryTitle', $this->getData())));
				$make_slug = $this->getData('CategorySlug')!=='';
			}else{
				$this->setData('CategorySlug', make_slug($this->getData('CategorySlug')));
			}
			$max_i = 50; $_slug = $this->getData('CategorySlug');
			
			while($max_i-- && !GetArticleCategoryisAvailableSlug($_slug, $CategoryID)){
				$_slug = $this->getData('CategorySlug').'_'.rand_name(2);
			}
			if(!$max_i){
				$_slug .= '_'.rand_name(2);
			}
			$this->setData('CategorySlug', $_slug);
			

			if(!$CategoryID or $CategoryID==1){
				$CategoryID = AddArticleCategory($this->getData());
			}else{
				$this->setData('CategoryID', $CategoryID);
				$res = UpdateArticleCategory($this->getData());
				if(PEAR::isError($res))break;
			}

			if (isset($_FILES["CategoryDefaultPicture"]) && $_FILES["CategoryDefaultPicture"]["name"] && is_image($_FILES["CategoryDefaultPicture"]["name"])){

				$old_picture = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT CategoryDefaultPicture FROM ?#ARTICLES_CATEGORY_TABLE WHERE CategoryID=? and CategoryID<>0', $CategoryID);
				if($old_picture)Functions::exec('file_remove', array(DIR_ARTICLES_PICTURES."/{$old_picture}"));

				$picture_name = str_replace(" ","_", $_FILES["CategoryDefaultPicture"]["name"]);
				$picture_name = getUnicFile(2, preg_replace('@\.([^\.]+)$@', '%s.$1', $picture_name), DIR_ARTICLES_PICTURES);
				$res = Functions::exec('file_move_uploaded', array($_FILES["CategoryDefaultPicture"]["tmp_name"], DIR_TEMP."/$picture_name"));
				if (PEAR::isError($res))Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage());
				$res = Functions::exec('img_resize', array(DIR_TEMP."/$picture_name", CONF_ARTCLE_CATPIC_SIZE, CONF_ARTCLE_CATPIC_SIZE, DIR_TEMP."/$picture_name"));
				if (PEAR::isError($res))Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage());		
				$res = Functions::exec('file_copy', array(DIR_TEMP."/$picture_name", DIR_ARTICLES_PICTURES."/$picture_name"));
				if (PEAR::isError($res))Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage());
				Functions::exec('file_remove', array(DIR_TEMP."/$picture_name"));
				SetRightsToUploadedFile( DIR_ARTICLES_PICTURES."/$picture_name" );
				$sql = 'UPDATE ?#ARTICLES_CATEGORY_TABLE SET `CategoryDefaultPicture`=? WHERE `CategoryID`=?';
				db_phquery($sql, $picture_name, $CategoryID);
			}
	
		}while(0);
			
			if(PEAR::isError($res)){
				Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage(), '', array('post_data' => $this->getData()));
			}else{
				Message::raiseMessageRedirectSQ(MSG_SUCCESS, '?ukey=articles_admin&CategoryID='.$CategoryID, 'msg_information_save');
			}
	}

	
	function delete_category(){		
		safeMode(true);
		$res = DeleteArticleCategory( $_GET['CategoryID'] );
		RedirectSQ('?ukey=articles_admin');
	}
	
	
	function delete_comment(){		
		safeMode(true);
		$res = DeleteArticleComment( $_GET['CommentID'] );
		RedirectSQ('?ukey=articles_admin_comments');
	}
	
	
	function remove_picture(){
		safeMode(true);
		$sql = 'SELECT `CategoryDefaultPicture` FROM ?#ARTICLES_CATEGORY_TABLE WHERE `CategoryID`=? and `CategoryID`<>1';
		$r = db_phquery_fetch(DBRFETCH_ROW, $sql, $_GET["CategoryID"]);
		if ($r[0] && file_exists(DIR_ARTICLES_PICTURES."/{$r[0]}"))
		Functions::exec('file_remove', array(DIR_ARTICLES_PICTURES."/{$r[0]}"));
		$sql = 'UPDATE ?#ARTICLES_CATEGORY_TABLE SET `CategoryDefaultPicture`="" WHERE `CategoryID`=?';
		db_phquery($sql, $_GET["CategoryID"]);
		RedirectSQ('');
	}

	
	function add_comment(){
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		$Message = $Register->get(VAR_MESSAGE);	
		$err = 0;
		
		$i = new IValidator();
		if(CONF_ENABLE_CONFIRMATION_CODE && !$i->checkCode($this->getData('fConfirmationCode')))
			Message::raiseMessageRedirectSQ(MSG_ERROR, '#Comment',  translate("err_wrong_ccode"), '', array('Comment' => $this->getData()));
		
		$err_msg = CheckDataArticleComments($this->getData());
		if($err_msg)
			Message::raiseMessageRedirectSQ(MSG_ERROR, '#Comment',  $err_msg, '', array('Comment' => $this->getData()));
		
		$CommentID = AddArticleComments($this->getData());
		RedirectSQ('');
	}

}


if(isset($_POST['save_articles'])){
	$_POST['action'] = 'save_articles';
}elseif($_POST['delete_selected']){
	$_POST['action'] = 'delete_selected';
}elseif($_POST['move_selected']){
	$_POST['action'] = 'move_selected';
}elseif($_POST['duplicate_selected']){
	$_POST['action'] = 'duplicate_selected';
}

if(isset($_POST) && $_POST['action']!='CPT_PREPARE_SMARTYCODE' && $_POST['action']!='save_settings' && $_POST['action']!='CPT_PREPARE_HTMLCODE' && $_GET['ukey']!='cpt_constructor'){
	ActionsController::exec('ArticlesController');
}	
		
?>