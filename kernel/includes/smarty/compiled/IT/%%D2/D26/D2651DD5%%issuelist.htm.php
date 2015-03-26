<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:51
         compiled from issuelist.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'wbs_initLayout', 'issuelist.htm', 4, false),array('function', 'wbs_errorBlock', 'issuelist.htm', 20, false),array('function', 'wbs_splitterPanelHeader', 'issuelist.htm', 55, false),array('block', 'wbs_pageLayout', 'issuelist.htm', 19, false),array('block', 'wbs_splitter', 'issuelist.htm', 48, false),array('block', 'wbs_splitterLeftPanel', 'issuelist.htm', 51, false),array('block', 'wbs_splitterScrollableArea', 'issuelist.htm', 61, false),array('block', 'wbs_splitterRightPanel', 'issuelist.htm', 68, false),)), $this); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<?php echo smarty_function_wbs_initLayout(array('splitter' => true,'toolbar' => true,'needExt' => true), $this);?>

		<link href="../../../IT/html/cssbased/res/it.css" rel="stylesheet" type="text/css"/>
		<script src="../../../common/html/classic/tree_templates/tree_functions.js"></script>
		<script src='../../../common/html/cssbased/pageelements/ajax/common_dialog.js'></script>
		
		<script>
			function isCompleteProject (pId) {
				return false;
			}
		</script>

		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "../../../IT/html/cssbased/issuelist_js.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		
	</head>
	<body onLoad="autoFocusFormControl( '<?php echo $this->_tpl_vars['invalidField']; ?>
', 'folderData' )">
		<?php $this->_tag_stack[] = array('wbs_pageLayout', array('toolbar' => "issuelist_toolbar.htm")); $_block_repeat=true;smarty_block_wbs_pageLayout($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
			<?php echo smarty_function_wbs_errorBlock(array(), $this);?>

			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "../../../common/html/cssbased/pageelements/common_js.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "../../../PM/html/cssbased/worklist_common.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "../../../PM/html/cssbased/workdialog_js.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			
			<input type="hidden" name="PREV_P_ID" value="<?php echo $this->_tpl_vars['PREV_P_ID']; ?>
"/>
			<input type="hidden" name="PREV_PW_ID" value="<?php echo $this->_tpl_vars['PREV_PW_ID']; ?>
"/>

			<?php if ($this->_tpl_vars['deleteStatus'] == 1): ?>
				<input type="hidden" name="savedDocList" value="<?php echo $this->_tpl_vars['savedDocList']; ?>
">
				<input type="hidden" name="savedP_ID" value="<?php echo $this->_tpl_vars['P_ID']; ?>
">

				<script language="JavaScript">
						if ( confirm('<?php echo $this->_tpl_vars['itStrings']['il_invworkflissuedel_message']; ?>
'+'\n\n'+'<?php echo $this->_tpl_vars['itStrings']['il_deleteforbconfirm_message']; ?>
') )
							processTextButton('deleteanywaybtn', 'form');
				</script>
			<?php elseif ($this->_tpl_vars['deleteStatus'] == 2): ?>
				<script language="JavaScript">
					alert( '<?php echo $this->_tpl_vars['itStrings']['il_unabletodelissues_message']; ?>
' );
				</script>
			<?php endif; ?>

			<?php if (! $this->_tpl_vars['fatalError']): ?>
			
				<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "dlg_issue.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
				<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "dlg_forward.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
				<?php if (! $this->_tpl_vars['pmDisabled']): 
 $this->assign('headerTpl', "issuelist_header.htm"); 
 endif; ?>
				
				<?php $this->_tag_stack[] = array('wbs_splitter', array('header' => $this->_tpl_vars['headerTpl'])); $_block_repeat=true;smarty_block_wbs_splitter($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>

					<?php if (! $this->_tpl_vars['hideLeftPanel']): ?>
						<?php $this->_tag_stack[] = array('wbs_splitterLeftPanel', array('width' => $this->_tpl_vars['treePanelWidth'])); $_block_repeat=true;smarty_block_wbs_splitterLeftPanel($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>

							<?php $this->assign('hideFoldersHint', $this->_tpl_vars['itStrings']['il_hidefilters_hint']); ?>

							<?php echo smarty_function_wbs_splitterPanelHeader(array('caption' => $this->_tpl_vars['itStrings']['il_filters_title'],'id' => 'FoldersHeadersPanel','active' => true,'headerControls' => "../../../common/html/cssbased/splittercontrols/headerclosebtn.htm"), $this);?>


							<?php $this->_tag_stack[] = array('wbs_splitterScrollableArea', array('width' => $this->_tpl_vars['treePanelWidth'])); $_block_repeat=true;smarty_block_wbs_splitterScrollableArea($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
								<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "filters.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
							<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_splitterScrollableArea($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>

						<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_splitterLeftPanel($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
					<?php endif; ?>

					<?php $this->_tag_stack[] = array('wbs_splitterRightPanel', array()); $_block_repeat=true;smarty_block_wbs_splitterRightPanel($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
						<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "issuelist_rightpanel.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
					<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_splitterRightPanel($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>

				<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_splitter($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
			<?php else: ?>

			<?php endif; ?>

		<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_pageLayout($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
	</body>
</html>