<?php /* Smarty version 2.6.26, created on 2014-08-08 13:46:31
         compiled from worklist.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'wbs_initLayout', 'worklist.htm', 6, false),array('function', 'wbs_errorBlock', 'worklist.htm', 162, false),array('block', 'wbs_pageLayout', 'worklist.htm', 150, false),array('block', 'wbs_note', 'worklist.htm', 171, false),array('modifier', 'cat', 'worklist.htm', 153, false),array('modifier', 'lower', 'worklist.htm', 153, false),array('modifier', 'mime_encode', 'worklist.htm', 155, false),)), $this); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<?php if ($this->_tpl_vars['screenEmptyToolbar'] || $this->_tpl_vars['action'] == 'no_project'): 
 $this->assign('fullWidth', false); 
 else: 
 $this->assign('fullWidth', true); 
 endif; ?>
		<?php if ($this->_tpl_vars['screenApp']): ?>
			<?php echo smarty_function_wbs_initLayout(array('splitter' => $this->_tpl_vars['fullWidth'],'noscroll' => false,'toolbar' => true,'needExt' => true), $this);?>

		<?php else: ?>
			<?php echo smarty_function_wbs_initLayout(array('splitter' => $this->_tpl_vars['fullWidth'],'noScroll' => false,'toolbar' => true,'needExt' => true), $this);?>

		<?php endif; ?>
		<script src="../../../common/html/classic/tree_templates/tree_functions.js"></script>
		<link href="../../../PM/html/cssbased/res/pm.css" rel="stylesheet" type="text/css"/>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "../../../common/html/cssbased/pageelements/common_js.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<?php if (! $this->_tpl_vars['screenApp']): ?>
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "worklist_js.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<?php endif; ?>


		<script type="text/javascript">
		<!--
		
			function doSubmit( screen, form ) {
				if ( screen == 1) { // Work assignments
					var srcObj = findObj( "firstIndex" );
					if ( srcObj )
						srcObj.value = 1;

					var shiftObj = findObj( "userShift" );
					if ( shiftObj ) {
						var selObj = shiftObj.options;
						if ( selObj )
							selObj[0].selected = true;
					}
				}

				form.submit();
			}

			function submitForm( obj )
			{
				obj = findObj("projectData[P_ID]");
				if ( !obj )
					return true;

				selected = obj.selectedIndex;
				if ( obj.options[selected].value != "" )
					obj.form.submit();
			}

			function checkManagerRights() 
			{
				<?php if ($this->_tpl_vars['showEditBtn']): ?>
					return true;
				<?php else: ?>
					alert( "<?php echo $this->_tpl_vars['pmStrings']['pm_noprojmanrights_message']; ?>
" );
					return false;
				<?php endif; ?>
			}

			function checkAddWork()
			{
				<?php if (! $this->_tpl_vars['canAddWorks']): ?>
					alert( "<?php echo $this->_tpl_vars['pmStrings']['pm_noaddmodtaskrights_message']; ?>
" );
					return false;
				<?php else: ?>
					<?php if (! $this->_tpl_vars['isActive']): ?>
						alert( "<?php echo $this->_tpl_vars['pmStrings']['pm_comlpraddtask_message']; ?>
" );
						return false;
					<?php endif; ?>
					return true;
				<?php endif; ?>
			}

			function confirmComplete()
			{
				return confirm( '<?php echo $this->_tpl_vars['pmStrings']['amp_projcomplete_message']; ?>
' );
			}

			function confirmDelete( string )
			{
				return confirm( string );
			}

			function confirmResume()
			{
				return confirm( '<?php echo $this->_tpl_vars['pmStrings']['amp_resume_message']; ?>
' );
			}

			function confirmDeletion()
			{
				var Object = findObj( "works_count" );
				var count = Object.value;

				return !( !confirmDelete( "<?php echo $this->_tpl_vars['pmStrings']['amp_projdel_message']; ?>
" ) || ( count > 0 && !confirmDelete( "<?php echo $this->_tpl_vars['confirmation_string']; ?>
" )) );			
			}
			
			function confirmTaskDelete()
			{
				if (!checkTasksSelected())
					return false;
				return confirm("<?php echo $this->_tpl_vars['pmStrings']['pm_tasksdeleteconfirm_message']; ?>
");
			}
			
			function checkTasksSelected () {
				var selectedRecords = document.worksGrid.getSelectionModel().getSelections();
				
				if (!selectedRecords || selectedRecords.length <= 0) {
					alert ( '<?php echo $this->_tpl_vars['pmStrings']['pm_tasksemptyaction_message']; ?>
' );
					return false;
				}
				
				return true;
			}

			var GanttCells = new Array();

			function registerCell( Cell )
			{
				GanttCells.push( $(Cell) );
			}

			function arangeCells()
			{
				if ( !GanttCells.length )
					return;

				var totalWidth = 0;

				for ( var i = 0; i < GanttCells.length; i++ )
				{
					if ( GanttCells[i] )
						totalWidth += GanttCells[i].offsetWidth;
				}

				var cellWidth = parseInt(totalWidth/GanttCells.length);
				if ( cellWidth == 0 )
					cellWidth = 1;

				for ( var i = 0; i < GanttCells.length; i++ )
				{
					if ( GanttCells[i] )
						GanttCells[i].style.width = cellWidth + "px";
				}
			}

		//-->
		</script>
	</head>
	<body>
		<?php if ($this->_tpl_vars['action'] != 'no_project'): ?>
			<?php $this->_tag_stack[] = array('wbs_pageLayout', array('fullWidthContent' => $this->_tpl_vars['fullWidth'],'tabbar' => "main_tabbar.htm",'stoolbar' => "worklist_toolbar.htm")); $_block_repeat=true;smarty_block_wbs_pageLayout($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
				<?php if ($this->_tpl_vars['screenApp']): ?>
					<input name="screenApp" type="hidden" value="<?php echo $this->_tpl_vars['screenApp']; ?>
"/>
					<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp='worklist_')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['screenApp']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['screenApp'])))) ? $this->_run_mod_handler('cat', true, $_tmp, "_screen.htm") : smarty_modifier_cat($_tmp, "_screen.htm")))) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)), 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
				<?php else: ?>
					<input name="sorting" type="hidden" id="sorting" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['sorting'])) ? $this->_run_mod_handler('mime_encode', true, $_tmp) : smarty_modifier_mime_encode($_tmp)); ?>
"/>
					<input name="action" type="hidden" id="action" value="<?php echo $this->_tpl_vars['action']; ?>
"/>
					<input name="projectData[P_STATUS]" type="hidden" id="projectData[P_STATUS]" value="<?php echo $this->_tpl_vars['projectData']['P_STATUS']; ?>
"/>
					<input name="projectData[U_ID_MANAGER]" type="hidden" id="projectData[U_ID_MANAGER]" value="<?php echo $this->_tpl_vars['projectData']['U_ID_MANAGER']; ?>
"/>
					<input name="prevP_ID" type="hidden" value="<?php echo $this->_tpl_vars['prevP_ID']; ?>
"/>
					<input name="works_count" type="hidden" value="<?php echo $this->_tpl_vars['works_count']; ?>
"/>
					
					<?php echo smarty_function_wbs_errorBlock(array(), $this);?>

					<div class='x-hidden'></div>
					<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "worklist_bottom.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
					<div id='work-window' class='x-hidden'></div>
					<div id='work-assignments-panel' class='x-hidden'></div>
				<?php endif; ?>
			<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_pageLayout($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
		<?php else: ?>
			<?php $this->_tag_stack[] = array('wbs_pageLayout', array('toolbar' => "worklist_toolbar.htm")); $_block_repeat=true;smarty_block_wbs_pageLayout($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
				<?php $this->_tag_stack[] = array('wbs_note', array('smallFont' => false,'displayNoteMarker' => false)); $_block_repeat=true;smarty_block_wbs_note($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); 
 echo $this->_tpl_vars['pmStrings']['pm_noprojects_message']; 
 $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_note($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
				<?php $this->_tag_stack[] = array('wbs_note', array('smallFont' => false)); $_block_repeat=true;smarty_block_wbs_note($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); 
 echo $this->_tpl_vars['pmStrings']['pm_noprojects_note']; 
 $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_note($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
			<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_pageLayout($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
		<?php endif; ?>
	</body>
</html>