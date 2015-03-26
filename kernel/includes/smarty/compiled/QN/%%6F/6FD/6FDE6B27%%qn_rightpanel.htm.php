<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:53
         compiled from qn_rightpanel.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'htmlsafe', 'qn_rightpanel.htm', 5, false),array('function', 'wbs_splitterPanelHeader', 'qn_rightpanel.htm', 20, false),array('block', 'wbs_splitterScrollableArea', 'qn_rightpanel.htm', 42, false),)), $this); ?>
<?php if ($this->_tpl_vars['searchString'] != ""): ?>
	<?php $this->assign('hideBottomPanel', 1); ?>
	<?php $this->assign('rightPanelHeader', $this->_tpl_vars['qnStrings']['qn_sreen_searchresult_title']); ?>
<?php else: ?>
	<?php $this->assign('rightPanelHeader', ((is_array($_tmp=$this->_tpl_vars['curFolderName'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true))); ?>
<?php endif; ?>

<?php if ($this->_tpl_vars['statisticsMode']): ?>
	<?php if ($this->_tpl_vars['searchString'] == ""): ?>
		<?php $this->assign('hideBottomPanel', 1); ?>
		<?php $this->assign('rightPanelHeader', $this->_tpl_vars['kernelStrings']['app_treeavailfolders_name']); ?>
		<?php $this->assign('hidePages', 1); ?>
		<?php $this->assign('docsColumnName', $this->_tpl_vars['qnStrings']['qn_sreen_summarydoc_title']); ?>
	<?php endif; ?>
<?php endif; ?>



<?php $this->assign('rightPanelCaptionControls', "../../../common/html/cssbased/pageelements/ajax/catalog_folder.showbtn.htm"); ?>
<?php echo smarty_function_wbs_splitterPanelHeader(array('caption' => $this->_tpl_vars['rightPanelHeader'],'active' => true,'id' => 'RightPanelHeader','captionControls' => $this->_tpl_vars['rightPanelCaptionControls'],'headerControls' => $this->_tpl_vars['rightPanelControls']), $this);?>


<?php if (! $this->_tpl_vars['statisticsMode']): ?>
	<?php $this->assign('numDocumentsLabel', $this->_tpl_vars['qnStrings']['qn_screen_numdocs_label']); ?>
	<?php if ($this->_tpl_vars['viewMode'] != 0): ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "../../../QN/html/cssbased/notelist_header.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<?php endif; ?>
	<?php $this->assign('rightPanelContent', "../../../QN/html/cssbased/notelist.htm"); ?>
<?php else: ?>
	<?php $this->assign('rightPanelContent', "../../../common/html/cssbased/pageelements/folders_summary.htm"); ?>

	<?php $this->assign('hideBottomPanel', 1); ?>
	<?php $this->assign('hidePages', 1); ?>

	<?php $this->assign('docsColumnName', $this->_tpl_vars['qnStrings']['qn_sreen_summarydoc_title']); ?>
<?php endif; ?>

<?php $this->_tag_stack[] = array('wbs_splitterScrollableArea', array()); $_block_repeat=true;smarty_block_wbs_splitterScrollableArea($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['rightPanelContent'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_splitterScrollableArea($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "../../../QN/html/cssbased/notelist_footer.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<input type='hidden' id='currentFolderId' value='<?php echo $this->_tpl_vars['currentFolder']; ?>
'>