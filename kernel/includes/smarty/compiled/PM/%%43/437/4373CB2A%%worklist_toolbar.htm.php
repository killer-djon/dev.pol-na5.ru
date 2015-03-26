<?php /* Smarty version 2.6.26, created on 2014-08-08 13:46:31
         compiled from worklist_toolbar.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'wbs_toolbarItem', 'worklist_toolbar.htm', 1, false),array('function', 'wbs_button', 'worklist_toolbar.htm', 2, false),)), $this); ?>
<?php $this->_tag_stack[] = array('wbs_toolbarItem', array()); $_block_repeat=true;smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<?php echo smarty_function_wbs_button(array('caption' => $this->_tpl_vars['pmStrings']['pm_modproject_btn'],'menu' => $this->_tpl_vars['projectMenu']), $this);?>

<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>

<?php if ($this->_tpl_vars['projectData']['SCREEN'] != 1): ?>
	<?php $this->_tag_stack[] = array('wbs_toolbarItem', array()); $_block_repeat=true;smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
		<?php echo smarty_function_wbs_button(array('caption' => $this->_tpl_vars['pmStrings']['pm_task_menu'],'menu' => $this->_tpl_vars['taskMenu']), $this);?>

	<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
<?php endif; ?>


<?php $this->_tag_stack[] = array('wbs_toolbarItem', array()); $_block_repeat=true;smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<?php echo smarty_function_wbs_button(array('caption' => $this->_tpl_vars['pmStrings']['pm_view_menu'],'menu' => $this->_tpl_vars['intervalMenu']), $this);?>

<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>



<?php if ($this->_tpl_vars['canReports']): ?>
	<?php $this->_tag_stack[] = array('wbs_toolbarItem', array()); $_block_repeat=true;smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
		<?php echo smarty_function_wbs_button(array('caption' => $this->_tpl_vars['kernelStrings']['app_reports_menu'],'menu' => $this->_tpl_vars['reportsMenu']), $this);?>

	<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_toolbarItem($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
<?php endif; ?>

<?php if ($this->_tpl_vars['projectData']['SCREEN'] != 1): ?>
	<li class='TBItem print-view' id="ToolbarItem10">
		<a alt='<?php echo $this->_tpl_vars['pmStrings']['pm_printer_label']; ?>
' title='<?php echo $this->_tpl_vars['pmStrings']['pm_printer_label']; ?>
' href='<?php echo $this->_tpl_vars['printURL']; ?>
' target='_blank' class=""><img src='../../../common/html/res/images/print.gif'/><?php echo $this->_tpl_vars['pmStrings']['pm_printer_label']; ?>
</a>
	</li>
<?php endif; ?>