<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:42
         compiled from backend.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'backend.html', 177, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['preproc']->getCommonPath("templates/elements/mainscreen_complex.htm"), 'smarty_include_vars' => array('notJS' => false)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<style>
.list {position:relative;}
</style>

<?php $this->assign('cacheEnabled', true); ?>
<?php if ($this->_tpl_vars['cacheEnabled']): ?>
	<script src="templates/backend_complex.js"></script>
<?php else: ?>
	<script src="templates/ddtable.js"></script>
	<script src="templates/ddviews.js"></script>
	<script src="templates/ddfolder.js"></script>
	<script src="templates/ddfile.js"></script>
	<script src="templates/ddcopymovedlg.js"></script>
	<script src="templates/ddfilessenddlg.js"></script>
	<script src="templates/ddfileversions.js"></script>
	<script src="templates/ddviewsettings.js"></script>
	<script src="templates/widgets.js"></script>
	<script src="templates/backend.js"></script>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "backend_strings.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>


<script>
	document.treeNodes = <?php echo $this->_tpl_vars['foldersJs']; ?>
;
	document.sessionId = "<?php echo $this->_tpl_vars['sessionId']; ?>
";
	
	registerOnLoad(function() {
		document.ddApplication = new DDApplication({
			ftpFolder: "<?php echo $this->_tpl_vars['ftpFolder']; ?>
",
			lastSearchString: "<?php echo $this->_tpl_vars['lastSearchString']; ?>
", 
			canTools: <?php if ($this->_tpl_vars['canTools']): ?>true<?php else: ?>false<?php endif; ?>, 
			canZohoEdit: <?php if ($this->_tpl_vars['canZohoEdit']): ?>true<?php else: ?>false<?php endif; ?>,
			hasZohoKey: <?php if ($this->_tpl_vars['hasZohoKey']): ?>true<?php else: ?>false<?php endif; ?>,
			canCreateRootFolder: <?php if (( $this->_tpl_vars['canCreateRootFolder'] && ! $this->_tpl_vars['projectId'] )): ?>true<?php else: ?>false<?php endif; ?>,
			canManageUsers: <?php if ($this->_tpl_vars['canManageUsers']): ?>true<?php else: ?>false<?php endif; ?>,
			projectId: <?php if ($this->_tpl_vars['projectId']): 
 echo $this->_tpl_vars['projectId']; 
 else: ?>0<?php endif; ?>
					
		});
		
		document.ddApplication.setViewSettings({itemsOnPage: <?php echo $this->_tpl_vars['viewSettings']['itemsOnPage']; ?>
, descTruncate: <?php echo $this->_tpl_vars['viewSettings']['descTruncate']; ?>
, viewmodeApplyTo: "<?php echo $this->_tpl_vars['viewSettings']['viewmodeApplyTo']; ?>
"});

		<?php if ($this->_tpl_vars['currentFolderId'] == 'trash'): ?>
			openTrash();
		<?php elseif ($this->_tpl_vars['currentFolderId']): ?>
			openFolder("<?php echo $this->_tpl_vars['currentFolderId']; ?>
");
		<?php else: ?>
			openFolder();
		<?php endif; ?>
		
		//document.ddApplication.navBar.resize();
		
		
		$("main-screen").style.visibility = "visible";
	});
</script>

<style>
	.x-tree li {list-style: none}
	
	.wbs-table a {text-decoration: none}
	.wbs-table a:hover {text-decoration: underline}
	
	.wbs-table .item .desc {font-size: 0.9em; overflow: hidden; zoom: 1}
	.wbs-table .item .desc div {cursor: pointer; float: left;}
	.wbs-table .highlight {background: yellow; }

	
	.folder-shared-link {float: left; margin-left: 10px; padding-top: 5px}
	.folder-shared-link input {border: 1px solid #DDD; color: black; width: 400px; background: white; margin-left: 3px}	
	
	.wbs-table .columns .fsize {text-align: right; white-space: nowrap }
	.wbs-table .columns .nowrap {white-space: nowrap }
	
	.wbs-table .columns th.shared .icon {width: 12px; height: 12px; background-image: URL("<?php echo $this->_tpl_vars['preproc']->getCommonUrl('templates/img/shared-link-bw.gif'); ?>
"); background-repeat: no-repeat; margin-left: auto; margin-right: auto}
	.wbs-table .columns th.locked .icon {width: 12px; height: 12px; background-image: URL("img/lock-bw.gif"); background-repeat: no-repeat; margin-left: auto; margin-right: auto}
	.wbs-table .columns .shared, .wbs-table .columns .locked {padding-left: 1px; padding-right: 1px; text-align: center}
	* html .wbs-table .columns th.locked .icon, * html .wbs-table .columns th.shared .icon {width: 18px; height: 15px; background-position: 3px 4px}
	

	.wbs-control-panel .title-wrapper {padding:0 5px 0px 0px;}
	.wbs-control-panel #folder-title-container {padding-left: 10px}
	.wbs-control-panel .title-wrapper .title {font-size: 20pt; font-weight: bold}
	.wbs-control-panel #view-settings-block {float: right; margin-top: 2px; width: 180px}
	.wbs-control-panel #viewmode-selector-wrapper {}
	.wbs-control-panel {clear: right;}
	
	.folder-info-block {clear:both; color: #666}
	.folder-info-block {font-size: 8pt}
	.folder-info-block b {color: black;}
	.folder-info-block label {color: #666}
	.folder-info-block  .folder-link input {border: 1px solid #DDD; background: #F0F0F0; font-size: 8pt}
	.folder-info-block  div {float: left; margin-right: 50px; line-height: 170%}
	
	.desc_content {font-size: 9pt; }
	
	
	.item-check-out {background-image: URL("img/lock.gif")}
	.item-email {background-image: URL("<?php echo $this->_tpl_vars['preproc']->getCommonUrl('templates/img/email-attach.gif'); ?>
")}
	.item-widget {background-image: URL("img/widget.gif")}
	.item-zip {background-image: URL("img/zip.gif")}
	.item-link-data {background-image: URL("<?php echo $this->_tpl_vars['preproc']->getCommonUrl('templates/img/shared-link.gif'); ?>
")}
	
	.lock-icon {cursor: pointer}
	.shared-icon {cursor: pointer}
	
	.x-tree img.trash-node {background-image: url("img/trash.gif");}
	.wbs-popmenu textarea {font-family: "Trebuchet MS"; font-size: 14px}
	
	.x-tree .folder-norights {background-image: URL("img/folder-norights.gif") !important}
	.x-tree .folder-readonly {background-image: URL("img/folder-readonly.gif") !important}
	.x-tree .folder-projects {background-image: URL("img/folder-projects.gif") !important}
	.x-tree .folder-ftp {background-image: URL("img/folder-ftp.gif") !important}
	
	#main-screen {visibility: hidden}
	
	.wbs-simplelist-view .item {padding-left: 10px; white-space: nowrap}
	.wbs-simplelist-view .item a {color: black; padding-left: 5px}
	.wbs-simplelist-view .item img {}
	.wbs-simplelist-view .focused {background: #D9E8FB}
	
	.title-wrapper {overflow: hidden; zoom: 1; width: 100%}
	.title-wrapper .wbs-editable-links-block input {margin-top: 5px; margin-bottom: 5px }
	.title-wrapper .wbs-editable-label input {font-size: 20pt; font-weight: bold; font-family: "Trebuchet MS"; border: 1px solid #DDD; width: 80%; }
	.title-wrapper .title div {cursor: pointer; float: left}
	.title-wrapper .title .wbs-editable-label {float: none; }
	
	.view-settings-window .wbs-popwindow-inner {padding: 10px;}
	.view-settings-window .wbs-popwindow-inner .field {margin-bottom: 5px}
	.view-settings-window .wbs-popwindow-inner .buttons {padding-bottom: 10px; text-align: right}
	.view-settings-window .wbs-popwindow-inner .buttons input {margin-left: 5px}
	
	.versions-dlg .wbs-table {margin-top: 5px}
	
	.files-send-dlg #filessend-send-to-input {width: 100%; height: 50px; overflow: auto; overflow-y: auto}
	.files-send-dlg #filessend-subject-input {width: 100%; }
	.files-send-dlg #filessend-message-input {width: 100%; height: 100px; overflow: auto }
	* html .files-send-dlg textarea {width: 480px !important}
	.files-send-dlg .field {margin-bottom: 10px}
	.files-send-dlg .note {font-size: 0.8em; }
	
	.versions-dlg .wbs-dlg-content {margin-left: 10px; margin-right: 10px; border: 1px solid #DDD; }
	
	#main-screen {overflow: hidden}
	.screen-left-block {overflow: hidden;}
	
	.nav-bar #folders .wbs-menu-btn {margin: 0px}
	.nav-bar #folders.active .title {height: 67px; border-bottom: 1px dashed #DDD}
	.nav-bar #folders .title .label {padding-top: 8px; padding-bottom: 8px}
	.create-new-block {display: none}
	.nav-bar #folders.active .create-new-block {display: block}
	
	
	
	#folder-title-container {overflow: hidden; zoom: 1; margin-top: 10px}
	#folder-title-container .title-wrapper {float: left}
	#folder-title-container .wbs-menu-btn {margin: 1px 5px}
	
	#tools .content ul li, #reports .content ul li {margin-top: 3px; padding-left: 10px}
	#tools a, #reports a {color: black; text-decoration:none}
	#tools a:hover, #reports a:hover {text-decoration:underline}
	
	.content .wbs-table {width: 100%}
	
	.view-settings-window {overflow: hidden}
</style>

<div style='height: 100%' id='main-screen'>
	<div class='screen-left-block' style='height: 100%; '>
		
		<!-- Left navigation bar -->
		<div class='nav-bar' id='nav-bar'>
			<div id='folders' class='acc-block'>
				<div class='title'>
					<div class='label'><?php echo ((is_array($_tmp='dd_template_backend->tab_folders')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</div>
					<div class='create-new-block' style='cursor: pointer; overflow: hidden; zoom: 1; margin-bottom: -1px; padding-bottom: 4px; padding-left:18px;padding-top: 0px;border-top: 1px solid #DDD; font-size: 0.9em; background: white;'><div style='zoom: 1; overflow: hidden' id='new-folder-btn'></div></div>
				</div>
				<div class='content'>
					<div id='folders-tree'>
					</div>
				</div>
			</div>
			<?php if (! $this->_tpl_vars['projectId']): ?>
			<div id='search' class='acc-block'>
				<div class='title'><div class='label'><?php echo ((is_array($_tmp='dd_template_backend->tab_search')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</div></div>			
				<div class='content'>
					<div style='padding: 5px'>
						<div id='dd-search-panel'>
						</div>
					</div>
				</div>
			</div>
			<div id='links' class='acc-block'>
				<div class='title'><div class='label'><?php echo ((is_array($_tmp='template_common->tab_links')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</div></div>
				<div class='content'>
				</div>
			</div>
			<?php if ($this->_tpl_vars['canWidgets']): ?>
			<div id='widgets' class='acc-block'>
				<div class='title'><div class='label'><?php echo ((is_array($_tmp='template_common->tab_widgets')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</div></div>
				<div class='content'>
				</div>
			</div>
			<?php endif; ?>
			<?php if ($this->_tpl_vars['canReports']): ?>
			<div id='reports' class='acc-block'>
				<div class='title'><div class='label'><?php echo ((is_array($_tmp='dd_template_backend->tab_reports')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</div></div>
				<div class='content' style='overflow: auto'>
					<div style='padding: 5px'>
						<ul class='links'>
							<li><a href='javascript:void(0)' onClick='return document.ddApplication.openSubframe("scripts/rep_spacebyusers.php", true)'><?php echo ((is_array($_tmp='dd->rep_spacebyusers_title')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a>
							<li><a href='javascript:void(0)' onClick='return document.ddApplication.openSubframe("scripts/rep_recentuploads.php", true)'><?php echo ((is_array($_tmp='dd->rep_recentuploads_title')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a>
							<li><a href='javascript:void(0)' onClick='return document.ddApplication.openSubframe("scripts/rep_folderssummary.php", true)'><?php echo ((is_array($_tmp='dd->rep_folderssummary_title')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a>
							<li><a href='javascript:void(0)' onClick='return document.ddApplication.openSubframe("scripts/rep_filetypesstats.php", true)'><?php echo ((is_array($_tmp='dd->rep_filetypestats_title')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a>
							<li><a href='javascript:void(0)' onClick='return document.ddApplication.openSubframe("scripts/rep_frequpdfiles.php", true)'><?php echo ((is_array($_tmp='dd->rep_frequpd_title')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a>
						</ul>
					</div>				
				</div>
			</div>
			<?php endif; ?>
			<?php endif; ?>
			<?php if ($this->_tpl_vars['canTools'] && ! $this->_tpl_vars['projectId']): ?>
				<div id='tools' class='acc-block'>
					<div class='title'><div class='label'><?php echo ((is_array($_tmp='dd_template_backend->tab_tools')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</div></div>
					<div class='content'>
						<div style='padding: 5px'>
							<ul class='links'>
								<li><a href='javascript:void(0)' name='toolslink' onClick='return document.ddApplication.openSubframe("../html/scripts/service.php?curScreen=5")'><?php echo ((is_array($_tmp='dd->dd_screen_onlineedit_menu')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a>
							</ul>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
	
	<!-- Screen main block (container for table and control panel) -->
	<div class='screen-main-block' style='margin-left: 0; height: 100%; overflow-y: auto; '>
		<div class='screen-content-block' id='screen-content-block' style='overflow-y: hidden; overflow-x: hidden; overflow: hidden'>
			<div id='main-container'>
				<div id='main-header'>
					<div id='control-panel' class='wbs-control-panel'>
						<div id='nav-bar-expander' class='nav-bar-expander' style='margin-left: 10px'></div>
						
						<div id='folder-title-container'>
						</div>
						
						<input type='button' id='upload-btn' value='<?php echo ((is_array($_tmp='dd_template_backend->btn_upload_files')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
'>
						<input type='button' id='actions-btn' value='<?php echo ((is_array($_tmp='dd_template_backend->menu_selected_files')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
'>
						<input type='button' id='folder-actions-btn' value='<?php echo ((is_array($_tmp='template_common->menu_folder')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
'>
						
						<div id='view-settings-block' style='float: right; margin-right: 20px'>
							<div style='float: left; padding-right: 3px; padding-top: 3px'><?php echo ((is_array($_tmp='dd_template_backend->lbl_view')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
:</div> 
							<div id='viewmode-selector-wrapper'></div>
							<div style='float: left; padding-left: 10px; '><div id='view-settings-btn'></div></div> 
						</div>
						
						<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "frm_upload.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
						<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "dlg_copymove.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
					</div>
				</div>
				<div id='main-content'>
				</div>
			</div>
		</div>
	</div>
</div>