<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:51
         compiled from issuelist_toolbar.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'wbs_toolbarItem', 'issuelist_toolbar.htm', 1, false),array('function', 'wbs_button', 'issuelist_toolbar.htm', 2, false),)), $this); ?>
<?php $this->_tag_stack[] = array('wbs_toolbarItem', array()); $_block_repeat=true;smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<?php echo smarty_function_wbs_button(array('caption' => $this->_tpl_vars['itStrings']['il_refresh_btn'],'ajax' => true,'name' => 'refreshbtn'), $this);?>

<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>

<?php if (! $this->_tpl_vars['noProjects']): ?>
	<?php $this->_tag_stack[] = array('wbs_toolbarItem', array()); $_block_repeat=true;smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
		<?php echo smarty_function_wbs_button(array('caption' => $this->_tpl_vars['itStrings']['il_issue_menu'],'menu' => $this->_tpl_vars['issueMenu']), $this);?>

	<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>

	<?php $this->_tag_stack[] = array('wbs_toolbarItem', array()); $_block_repeat=true;smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
		<?php echo smarty_function_wbs_button(array('caption' => $this->_tpl_vars['itStrings']['il_view_menu'],'menu' => $this->_tpl_vars['viewMenu']), $this);?>

	<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>

	<?php $this->_tag_stack[] = array('wbs_toolbarItem', array()); $_block_repeat=true;smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
		<?php echo smarty_function_wbs_button(array('caption' => $this->_tpl_vars['itStrings']['il_filters_menu'],'menu' => $this->_tpl_vars['filtersMenu']), $this);?>

	<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>

	<?php if ($this->_tpl_vars['canTools']): ?>
		<?php $this->_tag_stack[] = array('wbs_toolbarItem', array()); $_block_repeat=true;smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
			<?php echo smarty_function_wbs_button(array('caption' => $this->_tpl_vars['itStrings']['il_workflow_menu'],'menu' => $this->_tpl_vars['toolsMenu']), $this);?>

		<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
	<?php endif; ?>

	<?php if ($this->_tpl_vars['canReports']): ?>
		<?php $this->_tag_stack[] = array('wbs_toolbarItem', array()); $_block_repeat=true;smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
			<?php echo smarty_function_wbs_button(array('caption' => $this->_tpl_vars['kernelStrings']['app_reports_menu'],'menu' => $this->_tpl_vars['reportsMenu']), $this);?>

		<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
	<?php endif; ?>
<?php endif; ?>