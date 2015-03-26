<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:53
         compiled from ../../../common/html/cssbased/pageelements/ajax/catalog_folder.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', '../../../common/html/cssbased/pageelements/ajax/catalog_folder.htm', 8, false),array('function', 'wbs_foldersTreeNodes', '../../../common/html/cssbased/pageelements/ajax/catalog_folder.htm', 24, false),)), $this); ?>
<script>
	var foldersTree;
	var foldersEditor;
	
	var cf = {enableDD: <?php if ($this->_tpl_vars['enableDD']): ?>true<?php else: ?>false<?php endif; ?>};
	cf.currentNodeId = <?php if (( $this->_tpl_vars['currentFolder'] == TREE_AVAILABLE_FOLDERS )): ?>""<?php else: ?>"<?php echo $this->_tpl_vars['currentFolder']; ?>
"<?php endif; ?>;
	cf.enableEdit = <?php if ($this->_tpl_vars['enableEdit']): ?>true<?php else: ?>false<?php endif; ?>;
	cf.avIconCls = '<?php echo ((is_array($_tmp=@$this->_tpl_vars['avIconCls'])) ? $this->_run_mod_handler('default', true, $_tmp, "my-folder") : smarty_modifier_default($_tmp, "my-folder")); ?>
';
	
	cf.strings = new Array ();
	cf.strings.widgetsLabel = '<?php echo $this->_tpl_vars['kernelStrings']['app_widgetsmenu_label']; ?>
';
	cf.strings.searchResultsLabel = '<?php echo $this->_tpl_vars['kernelStrings']['app_searchresult_title']; ?>
';
	cf.strings.folderActionRightsError = '<?php echo $this->_tpl_vars['kernelStrings']['app_folderactionrights_error']; ?>
';
	cf.strings.folderRenameNoRightsError = '<?php echo $this->_tpl_vars['kernelStrings']['app_folderrenamenorights_error']; ?>
';
	cf.strings.emptySearchString = "<?php echo $this->_tpl_vars['kernelStrings']['app_emptysearchstring_error']; ?>
";
	cf.strings.actions = {copy: "<?php echo $this->_tpl_vars['kernelStrings']['app_copying_label']; ?>
", move: "<?php echo $this->_tpl_vars['kernelStrings']['app_moving_label']; ?>
", deleting: "<?php echo $this->_tpl_vars['kernelStrings']['app_deleting_label']; ?>
"};
	cf.strings.copy = "<?php echo $this->_tpl_vars['kernelStrings']['app_copy_btn']; ?>
";
	cf.strings.move = "<?php echo $this->_tpl_vars['kernelStrings']['app_move_btn']; ?>
";
	cf.strings.deleteBtn = "<?php echo $this->_tpl_vars['kernelStrings']['app_delete_btn']; ?>
";
	cf.strings.cancel = "<?php echo $this->_tpl_vars['kernelStrings']['app_cancel_btn']; ?>
";
	cf.strings.confirm = "<?php echo $this->_tpl_vars['kernelStrings']['app_confirm_label']; ?>
";
	
	cf.nodes = new Array (
		<?php echo smarty_function_wbs_foldersTreeNodes(array('folders' => $this->_tpl_vars['folders'],'hierarchy' => $this->_tpl_vars['hierarchy'],'parentId' => 0,'avIconCls' => $this->_tpl_vars['avIconCls'],'unavIconCls' => $this->_tpl_vars['unavIconCls']), $this);?>

	);
	
	var foldersTreeConfig = cf;
</script>
<style>
	.x-panel-bwrap {overflow:visible;}
	#tree-div .x-panel-body {overflow:visible;}
</style>


<script src='../../../common/html/cssbased/pageelements/ajax/tree_classes.js'></script>
<script src='../../../common/html/cssbased/pageelements/ajax/folders_tree.js'></script>

<div id="tree-div" style=''></div>

<div id="tree-copy-move" class='x-hidden'>
	<div id='send-select-tab' style='padding: 10px'>
    <?php echo $this->_tpl_vars['kernelStrings']['app_copymovefolder_label']; ?>
 <span style='font-weight: bold' id='tree-copy-move-toname'></span>?
		<br>
		<table id='tree-copy-move-process' style='margin-top: 5px; width: 98%'><tr><td width="50%" style='font-weight: bold' align='right'><span id='tree-copy-move-process-text'></span>...</td><td width="50%" align='left'><img id='' src='../../../common/html/res/images/progress.gif'></td></tr></table>
  </div>
</div>

<div id="tree-folder-delete" class='x-hidden'>
	<div id='tree-folder-delete-tab' style='padding: 10px'>
		<span id='tree-folder-delete-message'><?php echo $this->_tpl_vars['kernelStrings']['app_deletefolder_label']; ?>
</span>
		<br>
		<table id='tree-folder-delete-process' style='width: 98%'><tr><td width="60%" style='font-weight: bold' align='right'><span id='tree-folder-delete-process-text'><?php echo $this->_tpl_vars['kernelStrings']['app_deleting_label']; ?>
</span>...</td><td width="40%" align='left'><img id='' src='../../../common/html/res/images/progress.gif'></td></tr></table>
  </div>
</div>