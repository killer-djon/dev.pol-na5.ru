<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:51
         compiled from issuelist_rightpanel.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'wbs_splitterPanelHeader', 'issuelist_rightpanel.htm', 17, false),array('modifier', 'truncate', 'issuelist_rightpanel.htm', 17, false),array('block', 'wbs_splitterScrollableArea', 'issuelist_rightpanel.htm', 26, false),)), $this); ?>
<?php $this->assign('numDocumentsLabel', $this->_tpl_vars['itStrings']['il_records_label']); ?>

<?php $this->assign('rightPanelCaptionControls', null); ?>
<?php $this->assign('rightPanelControls', null); ?>

<?php if ($this->_tpl_vars['hideLeftPanel'] && $this->_tpl_vars['searchString'] == ""): ?>
	<?php $this->assign('rightPanelControls', "../../../IT/html/cssbased/filterSelector.htm"); ?>
	<?php $this->assign('foldersImage', "javascript:processTextButton('showFoldersBtn', 'form')"); ?>
<?php endif; ?>

<?php $this->assign('rightPanelCaptionControls', "../../../common/html/cssbased/pageelements/ajax/catalog_folder.showbtn.notajax.htm"); ?>

<?php $this->assign('showFoldersHint', $this->_tpl_vars['itStrings']['il_showfilters_hint']); ?>

<?php if (! $this->_tpl_vars['noProjects']): 
 $this->assign('template', "issuelist_rightpanelheader.htm"); 
 $this->assign('height', 75); 
 endif; ?>

<?php echo smarty_function_wbs_splitterPanelHeader(array('caption' => ((is_array($_tmp=$this->_tpl_vars['curFilterName'])) ? $this->_run_mod_handler('truncate', true, $_tmp, 30) : smarty_modifier_truncate($_tmp, 30)),'active' => true,'id' => 'RightPanelHeader','captionControls' => $this->_tpl_vars['rightPanelCaptionControls'],'headerControls' => $this->_tpl_vars['rightPanelControls'],'captionTemplate' => $this->_tpl_vars['template'],'height' => $this->_tpl_vars['height']), $this);?>


<?php $this->_tag_stack[] = array('wbs_splitterScrollableArea', array()); $_block_repeat=true;smarty_block_wbs_splitterScrollableArea($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
<div id='IssuesWorksBlock' style='border-top: 1px solid white'>
	<?php if ($this->_tpl_vars['viewMode'] == 'LIST'): ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "../../../IT/html/cssbased/ilist_list.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<?php else: ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "../../../IT/html/cssbased/ilist_grid.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<?php endif; ?>
</div>
<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_splitterScrollableArea($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>