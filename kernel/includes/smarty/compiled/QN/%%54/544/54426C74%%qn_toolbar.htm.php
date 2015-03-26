<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:53
         compiled from qn_toolbar.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'wbs_toolbarItem', 'qn_toolbar.htm', 2, false),array('function', 'wbs_button', 'qn_toolbar.htm', 3, false),array('modifier', 'cat', 'qn_toolbar.htm', 22, false),array('modifier', 'htmlsafe', 'qn_toolbar.htm', 26, false),)), $this); ?>
<?php if ($this->_tpl_vars['searchString'] == ""): ?>
	<?php $this->_tag_stack[] = array('wbs_toolbarItem', array()); $_block_repeat=true;smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
		<?php echo smarty_function_wbs_button(array('caption' => $this->_tpl_vars['qnStrings']['qn_screen_folder_menu'],'menu' => $this->_tpl_vars['folderMenu']), $this);?>

	<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
<?php endif; ?>

<?php $this->_tag_stack[] = array('wbs_toolbarItem', array()); $_block_repeat=true;smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<?php echo smarty_function_wbs_button(array('caption' => $this->_tpl_vars['qnStrings']['qn_screen_notes_menu'],'menu' => $this->_tpl_vars['fileMenu']), $this);?>

<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>

<?php $this->_tag_stack[] = array('wbs_toolbarItem', array()); $_block_repeat=true;smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<?php echo smarty_function_wbs_button(array('caption' => $this->_tpl_vars['qnStrings']['qn_screen_view_menu'],'menu' => $this->_tpl_vars['viewMenu']), $this);?>

<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>

<?php if ($this->_tpl_vars['canTools'] && $this->_tpl_vars['searchString'] == ""): ?>
	<?php $this->_tag_stack[] = array('wbs_toolbarItem', array()); $_block_repeat=true;smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
		<?php echo smarty_function_wbs_button(array('caption' => $this->_tpl_vars['kernelStrings']['app_tools_menu'],'menu' => $this->_tpl_vars['toolsMenu']), $this);?>

	<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
<?php endif; ?>

<?php $this->_tag_stack[] = array('wbs_toolbarItem', array()); $_block_repeat=true;smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<div class='TBLabel'><?php echo ((is_array($_tmp=$this->_tpl_vars['qnStrings']['qn_screen_search_label'])) ? $this->_run_mod_handler('cat', true, $_tmp, ":&nbsp;") : smarty_modifier_cat($_tmp, ":&nbsp;")); ?>
</div>
<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>

<?php $this->_tag_stack[] = array('wbs_toolbarItem', array()); $_block_repeat=true;smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<input type="text" ID="Search" name="searchString" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['searchString'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)); ?>
" style="width: 200px" onKeyPress="if (event.keyCode == 13) { doSearch(); return false;}" />
<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>

<?php $this->_tag_stack[] = array('wbs_toolbarItem', array()); $_block_repeat=true;smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<?php echo smarty_function_wbs_button(array('name' => 'searchbtn','caption' => $this->_tpl_vars['qnStrings']['qn_screen_search_btn'],'onClick' => 'doSearch(); return false;'), $this);?>

<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>