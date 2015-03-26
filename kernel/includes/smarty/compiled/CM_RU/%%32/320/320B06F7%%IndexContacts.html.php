<?php /* Smarty version 2.6.26, created on 2014-08-08 13:46:24
         compiled from IndexContacts.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'csscombine', 'IndexContacts.html', 11, false),array('block', 'jscombine', 'IndexContacts.html', 18, false),)), $this); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Contacts</title>

<link rel="stylesheet" type="text/css" href="<?php echo $this->_tpl_vars['url']['common']; ?>
templates/css/common.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $this->_tpl_vars['url']['common']; ?>
templates/elements/mainscreen_complex.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $this->_tpl_vars['url']['common']; ?>
html/res/ext/resources/css/resizable.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $this->_tpl_vars['url']['common']; ?>
html/res/ext/resources/css/tree.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $this->_tpl_vars['url']['common']; ?>
css/datepicker.css" />
<?php $this->_tag_stack[] = array('csscombine', array('file' => ($this->_tpl_vars['url']['css'])."contacts-index.css")); $_block_repeat=true;smarty_block_csscombine($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<?php echo $this->_tpl_vars['url']['css']; ?>
reset.css
	<?php echo $this->_tpl_vars['url']['css']; ?>
users-common.css
	<?php echo $this->_tpl_vars['url']['css']; ?>
users.css
<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_csscombine($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>

<script type="text/javascript" src="<?php echo $this->_tpl_vars['url']['common']; ?>
js/jquery.js"></script>
<?php $this->_tag_stack[] = array('jscombine', array('file' => ($this->_tpl_vars['url']['js'])."contacts-index.js")); $_block_repeat=true;smarty_block_jscombine($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<?php echo $this->_tpl_vars['url']['js']; ?>
users-common.js
	<?php echo $this->_tpl_vars['url']['common']; ?>
js/datepicker.js
	<?php echo $this->_tpl_vars['url']['common']; ?>
templates/js/common.new.js
	<?php echo $this->_tpl_vars['url']['common']; ?>
html/res/ext/ext-small.js
	<?php echo $this->_tpl_vars['url']['common']; ?>
html/res/ext/build/widgets/Resizable-min.js
	<?php echo $this->_tpl_vars['url']['js']; ?>
complex.js
	<?php echo $this->_tpl_vars['url']['js']; ?>
ug.js
	<?php echo $this->_tpl_vars['url']['js']; ?>
contacts.js
<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_jscombine($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
<?php if ($this->_tpl_vars['user_lang'] == 'rus'): ?><script type="text/javascript" src="<?php echo $this->_tpl_vars['url']['common']; ?>
js/datepicker-rus.js"></script><?php endif; ?>
<script type="text/javascript">
	WbsCommon.setPublishedUrl("<?php echo $this->_tpl_vars['url']['published']; ?>
");
	Ext.BLANK_IMAGE_URL = '<?php echo $this->_tpl_vars['url']['common']; ?>
html/res/ext/resources/images/default/s.gif';

	document.folderNodes = <?php echo $this->_tpl_vars['folders']; ?>
;
	document.listNodes = <?php echo $this->_tpl_vars['lists']; ?>
;
	<?php if ($this->_tpl_vars['right']['admin']): ?>
		document.widgetNodes = <?php echo $this->_tpl_vars['widgets']; ?>
;
	<?php endif; ?>
		
	document.contactTypes = <?php echo $this->_tpl_vars['contact_types']; ?>
;
	document.manageUsers = <?php echo $this->_tpl_vars['right']['users']; ?>
;
	document.dbfields = <?php echo $this->_tpl_vars['dbfields']; ?>
;
	document.listfields = <?php echo $this->_tpl_vars['list_fields']; ?>
;
	document.photoField = <?php echo $this->_tpl_vars['photoField']; ?>
;
	document.contactId = <?php echo $this->_tpl_vars['contact_id']; ?>
;
	
	document.fields = new Array();

	
	$(document).ready(function() {
		$("#main-screen").height($(window).height() - $("#header").height());

		document.getElementById("main-screen").style.visibility = "visible";

		Toogles.init_toogle('#folders-list-toogle div.h', '#folders-list');
		Toogles.init_toogle('#lists-list-toogle div.h', '#lists-list');
		Toogles.init_toogle('#widgets-list-toogle div.h', '#widgets-list');
		
		document.app = new UGApplication({	
			right: <?php echo $this->_tpl_vars['right_js']; ?>
,
			currentFolderId: "<?php echo $this->_tpl_vars['viewParams']['currentFolderId']; ?>
",
			currentSearchId: "<?php echo $this->_tpl_vars['viewParams']['currentSearchId']; ?>
",
			currentListId: "<?php echo $this->_tpl_vars['viewParams']['currentListId']; ?>
",
			currentFormId: "<?php echo $this->_tpl_vars['viewParams']['currentFormId']; ?>
"
			<?php if ($this->_tpl_vars['page']): ?>, page: <?php echo $this->_tpl_vars['page']; 
 endif; ?>
		});
		document.app.setViewSettings({itemsOnPage: <?php if ($this->_tpl_vars['viewSettings']['itemsOnPage']): 
 echo $this->_tpl_vars['viewSettings']['itemsOnPage']; 
 else: ?>30<?php endif; ?>, viewmodeApplyTo: "<?php if ($this->_tpl_vars['viewSettings']['viewmodeApplyTo']): 
 echo $this->_tpl_vars['viewSettings']['viewmodeApplyTo']; 
 endif; ?>"});
		<?php if ($_GET['searchType']): ?>
			document.app.search_type = '<?php echo $_GET['searchType']; ?>
';
		<?php endif; ?>;
					
		init();

		<?php if ($_GET['searchType'] == 'simple'): ?>
		var string = '<?php echo $this->_tpl_vars['search_string']; ?>
';
	  	document.app.doSearch(string, 1);
  	    <?php endif; ?>

  	  	$("#top-search").keydown(function(event){
		  if (event.keyCode == 13) {
				document.app.searchByName(this.value);			  
		  }
		});

		$(window).resize();	

		$("#onload-message").fadeOut(5000, function () {$(this).remove()});
		document.app.navBar.resize();
	});
	$(window).resize(function () {
		var h = $(window).height() - $("#header").height();
		$("#main-screen").height($(window).height() - $("#header").height());
		var id = $("#nav-bar div.acc-block div.content:visible").parent().attr('id');
	});
	
	function openSearch(type, string) {
		$("#list_info").empty();
		document.app.setSearchType(type, 'search=' + string, true);
		setCookie("last_block", "search");
	}
	
</script>
</head>
<body>
<div id="header">
	<div id="toolbar_new" class="ind-tools">
		<table class="top_panel">
		<tr>
            <?php if ($this->_tpl_vars['add_contact']): ?>			
            <td class="btn_td" width="1%">
			<div class="wbs-menu-btn-bg"><div class="wbs-menu-btn-bg_l"><span id="add-new-contact"></span></div></div>
			</td>
			<?php endif; ?>
            <td class="search-td">
            <div class="search-container">
	            <div class="search-block">
	            	<input type="text" class="s-field" id="top-search"  value="" /><input type="button" onclick="document.app.searchByName(this.previousSibling.value);" value="search" />
	            	<span class="other-search">
	            		<a href="javascript:void(0)" onClick="openSearch('advanced');">Advanced search</a> | 
	            		<a href="javascript:void(0)" onClick="openSearch('smart');">Smart search</a>
	            	</span>
	            </div>
	        </div>
            </td>
        </tr>
        </table>
	</div>
</div>
<div id='main-screen'>
	<div class='screen-left-block'>
		<div class='nav-bar' id='nav-bar'>
			<div id="folders" class="acc-block">
				<div class="topfolders title"><ul>
						<li><a class="select-group select-folder" id="ALL-CONTACTS" href="#"><img src="<?php echo $this->_tpl_vars['url']['img']; ?>
i-allusers.png" width="32" height="32"/>All contacts</a></li>
						<?php if ($this->_tpl_vars['right']['admin']): ?>
						<li><a class="select-group select-folder" id="ANALYTICS-CONTACTS" href="#"><img src="<?php echo $this->_tpl_vars['url']['img']; ?>
i-analitics.png" width="32" height="32"/>Analytics</a></li></ul>
						<?php endif; ?>
				</div>			
				<div class="content">
					<div class="create-new-block" id="folders-list-toogle">
						<div class="sub">
							<div class="h" style="cursor: pointer;"><span><img src="../UG/img/rarr.gif" /></span>Folders</div>
							<div style='zoom: 1; overflow: hidden' id='new-folder-btn'></div>
						</div>
					</div>				
					<div id="folders-list">
					</div>
					<div class="create-new-block" id="lists-list-toogle">
						<div class="sub">
							<div class="h" style="cursor: pointer;"><span><img src="../UG/img/rarr.gif" /></span>Lists</div>
							<div style='zoom: 1; overflow: hidden' id='new-list-btn'></div>
						</div>
					</div>
					<div id="lists-list"></div>
					<?php if ($this->_tpl_vars['right']['admin']): ?>
					<div class='create-new-block' id="widgets-list-toogle" >
						<div class="sub">
							<div class="h" style="cursor: pointer;"><span><img src="../UG/img/rarr.gif" /></span>Forms</div>
							<div style='zoom: 1; overflow: hidden' id='new-widget-btn'></div>
						</div>
					</div>				
					<div id='widgets-list' style="height:100%">
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	<div class='screen-main-block' style='margin-left: 0; height: 100%; overflow-y: auto; '>
		<div class='screen-content-block' id='screen-content-block' style='overflow-y: hidden; overflow-x: hidden; overflow: hidden;'>
			<div id='main-container'>
				<div id='main-header'>
					<div id='control-panel' class='wbs-control-panel'>
						<div id='nav-bar-expander' class='nav-bar-expander' style='margin-left: 10px'></div>			
						<div id='group-title-container'></div>			
						<div class='contacts-info'>						
							<div id='view-settings-block'>
								<input type="button" id="users-actions-btn" value="Actions" />
								<div style='text-align:right; float: left; padding-right: 3px; padding-top: 3px; width: 50px; overflow:hidden'>View:</div> 
								<div id='viewmode-selector-wrapper'></div>
								<div id='viewmode-print' class='viewmode-print'><img src="../common/templates/img/printer.gif" title='Print preview'></div>
							</div>
							<div id="list_info"><?php if ($this->_tpl_vars['message']): ?><div id="onload-message" class="info-message onload"><?php echo $this->_tpl_vars['message']; ?>
</div><?php endif; ?></div>
						</div>
						<div class="hidden wbs-dlg-content-inner" id='dlg-content'>
							<div style='margin-top: 5px; margin-bottom: 5px' id='dlg-desc'></div>
						</div>
						<div class="hidden wbs-dlg-content-inner" id='dlg-move-content'>
							<div id='dlg-move-desc' class="dlg-desc"></div>
							<select id='dlg-folders-select' size="13" style="width:100%"></select>
							<div class="add-to-list" style="display:none">
							<br />
							<div class="dlg-desc">Create a new list:</div>
							<input id="add-to-new-list" />
							</div>
						</div>
						<div class="hidden wbs-dlg-content-inner" id='dlg-export-content'></div>
						<div class="hidden wbs-dlg-content-inner" id='dlg-sendsms-content'></div>
					</div>
				</div>
				<div id='main-content'><?php echo $this->_tpl_vars['content']; ?>
</div>
			</div>
		</div>
	</div>
</div>

</body>
</html>