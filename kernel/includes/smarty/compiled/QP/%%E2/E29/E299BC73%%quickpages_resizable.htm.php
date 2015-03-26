<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:47
         compiled from quickpages_resizable.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'wbs_initLayout', 'quickpages_resizable.htm', 4, false),array('function', 'wbs_errorBlock', 'quickpages_resizable.htm', 43, false),array('function', 'wbs_splitterPanelHeader', 'quickpages_resizable.htm', 54, false),array('block', 'wbs_pageLayout', 'quickpages_resizable.htm', 41, false),array('block', 'wbs_splitter', 'quickpages_resizable.htm', 47, false),array('block', 'wbs_splitterLeftPanel', 'quickpages_resizable.htm', 50, false),array('block', 'wbs_splitterScrollableArea', 'quickpages_resizable.htm', 60, false),array('block', 'wbs_splitterRightPanel', 'quickpages_resizable.htm', 66, false),)), $this); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<?php echo smarty_function_wbs_initLayout(array('splitter' => true,'toolbar' => true,'needExt' => true), $this);?>


		<script src="../../../common/html/classic/tree_templates/tree_functions.js"></script>
		<script src="../../../common/html/cssbased/pageelements/ajax/common_dialog.js"></script>

		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "../../../QP/html/cssbased/qp_js.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		
		<style>
			#SplitterRightPanelContent {overflow: hidden; overflow-y: hidden}
		</style>
		
		<script>
			function resizeIframe() {
				var frame = document.getElementById("myFrame");
				if (frame) {
					var sc = document.getElementById("SplitterRightPanelContent");
					frame.style.height= sc.offsetHeight + "px";
					frame.style.width= sc.offsetWidth + "px";
				}
			}
			
			function delayedResizeIframe() {
				window.setTimeout("resizeIframe()", 50);
			}
			
			function setPagePublished(published) {
				document.getElementById("pubBtn").innerHTML = published ? "<?php echo $this->_tpl_vars['qpStrings']['qpo_mark_unpub_btn']; ?>
" : "<?php echo $this->_tpl_vars['qpStrings']['qpo_mark_pub_btn']; ?>
";
				document.getElementById("pubBtn").parentNode.href = published ? "javascript:processTextButton('unpublbtn', 'form')" : "javascript:processTextButton('publbtn', 'form')" ;
			}
			
			
			Event.observe(window, 'resize', delayedResizeIframe);
			RegisterOnLoad(delayedResizeIframe);
		</script>
	</head>
	<body onLoad="autoFocusFormControl( '<?php echo $this->_tpl_vars['invalidField']; ?>
', 'folderData' )">

	<?php $this->_tag_stack[] = array('wbs_pageLayout', array('toolbar' => "qp_toolbar.htm")); $_block_repeat=true;smarty_block_wbs_pageLayout($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>

	<?php echo smarty_function_wbs_errorBlock(array(), $this);?>


		<?php if (! $this->_tpl_vars['fatalError']): ?>

			<?php $this->_tag_stack[] = array('wbs_splitter', array()); $_block_repeat=true;smarty_block_wbs_splitter($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>

				<?php if (! $this->_tpl_vars['hideLeftPanel']): ?>
					<?php $this->_tag_stack[] = array('wbs_splitterLeftPanel', array('width' => $this->_tpl_vars['treePanelWidth'],'hide' => $this->_tpl_vars['treePanelHide'])); $_block_repeat=true;smarty_block_wbs_splitterLeftPanel($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>

						<?php $this->assign('hideFoldersHint', $this->_tpl_vars['qpStrings']['app_hidefolders_hint']); ?>

						<?php echo smarty_function_wbs_splitterPanelHeader(array('caption' => $this->_tpl_vars['qpStrings']['qp_treepages_text'],'captionLink' => $this->_tpl_vars['leftPanelHeaderLink'],'id' => 'FoldersHeadersPanel','active' => true,'headerControls' => "../../../common/html/cssbased/pageelements/ajax/catalog_folder.close.htm"), $this);?>

						<?php $this->_tag_stack[] = array('wbs_splitterScrollableArea', array('width' => $this->_tpl_vars['treePanelWidth'])); $_block_repeat=true;smarty_block_wbs_splitterScrollableArea($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
							<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "../../../QP/html/cssbased/catalog_panel.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
						<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_splitterScrollableArea($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
					<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_splitterLeftPanel($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
				<?php endif; ?>

				<?php $this->_tag_stack[] = array('wbs_splitterRightPanel', array()); $_block_repeat=true;smarty_block_wbs_splitterRightPanel($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
						<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "quickpages_rightpanel.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

				<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_splitterRightPanel($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>

			<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_splitter($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>

		<?php endif; ?>

	<input type="hidden" name="curFolderID" value="<?php echo $this->_tpl_vars['curFolderID']; ?>
">
	<input type="hidden" name="currentPage" value="<?php echo $this->_tpl_vars['currentPage']; ?>
">
	<input type="hidden" id="currentBookID" name="currentBookID" value="<?php echo $this->_tpl_vars['currentBookID']; ?>
">

	<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_pageLayout($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>

</body>
</html>