<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:51
         compiled from ../../../IT/html/cssbased/ilist_list.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'math', '../../../IT/html/cssbased/ilist_list.htm', 5, false),array('function', 'makeLink', '../../../IT/html/cssbased/ilist_list.htm', 79, false),array('modifier', 'default', '../../../IT/html/cssbased/ilist_list.htm', 72, false),array('modifier', 'cat', '../../../IT/html/cssbased/ilist_list.htm', 72, false),array('modifier', 'htmlsafe', '../../../IT/html/cssbased/ilist_list.htm', 119, false),)), $this); ?>
<?php $this->assign('popupindex', 0); ?>	

<?php $this->assign('issueCols', 5); ?>
<?php if ($this->_tpl_vars['viewdata']['SHOWSENDER'] || $this->_tpl_vars['viewdata']['SHOWASSIGNEE']): ?>
	<?php echo smarty_function_math(array('equation' => "x+1",'x' => $this->_tpl_vars['issueCols'],'assign' => 'issueCols'), $this);?>

<?php endif; ?>
<?php if ($this->_tpl_vars['viewdata']['DISPLAYSENDLINKS']): ?>
	<?php echo smarty_function_math(array('equation' => "x+1",'x' => $this->_tpl_vars['issueCols'],'assign' => 'issueCols'), $this);?>

<?php endif; ?>

<?php if ($this->_tpl_vars['manyProjects']): ?>
<style>
	table.it_TaskTable tr.ListSubheader td {border-bottom: 0px}
</style>
<?php endif; ?>

<?php $this->assign('prevoutProjectName', ""); ?>
<?php $this->assign('prevPW_ID', -1); ?>

<?php if (! $this->_tpl_vars['onlyIssues']): ?>
	<input type='hidden' id='OffsetChange' value=0>
	<div id='JustAddedIssues' style='display: none'>

		<table class="it_TaskTable" width="100%">
			<tr class="ListSubheader">
				<td width="10">&nbsp;</td>
				<td><font style='font-weight: normal'><?php echo $this->_tpl_vars['itStrings']['il_justadded_lbl']; ?>
</font></td>
			</tr>
		</table>
	</div>
<?php endif; ?>

<?php $_from = $this->_tpl_vars['issueList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['issueLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['issueLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['rowIndex'] => $this->_tpl_vars['listRecord']):
        $this->_foreach['issueLoop']['iteration']++;
?>

	<?php $this->assign('listIndex', $this->_foreach['issueLoop']['iteration']); ?>

	<?php if ($this->_tpl_vars['listRecord']['PW_ID'] != $this->_tpl_vars['prevPW_ID']): ?>
		<?php $this->assign('P_ID', $this->_tpl_vars['listRecord']['P_ID']); ?>
		<?php $this->assign('PW_ID', $this->_tpl_vars['listRecord']['PW_ID']); ?>
		<?php $this->assign('outProject', $this->_tpl_vars['outWorkList'][$this->_tpl_vars['P_ID']]); ?>
		<?php $this->assign('workRecord', $this->_tpl_vars['outProject'][$this->_tpl_vars['PW_ID']]); ?>
		<?php if (isset ( $this->_tpl_vars['workRecord']['userIsProjman'] )): 
 $this->assign('userIsProjman', $this->_tpl_vars['workRecord']['userIsProjman']); 
 endif; ?>
		
		<!-- task record -->
		<?php $this->assign('issueIndex', 0); ?>
		
		
		<?php if (! ( isset ( $this->_tpl_vars['hideP_ID'] ) && $this->_tpl_vars['hideP_ID'] == $this->_tpl_vars['workRecord']['P_ID'] && $this->_tpl_vars['hidePW_ID'] == $this->_tpl_vars['workRecord']['PW_ID'] )): ?>
			<?php if ($this->_tpl_vars['manyProjects'] && $this->_tpl_vars['workRecord']['PROJECT_NAME'] != $this->_tpl_vars['prevoutProjectName'] && $this->_tpl_vars['workRecord']['P_ID'] != 0 && ( $this->_tpl_vars['hideP_ID'] != $this->_tpl_vars['workRecord']['P_ID'] )): ?>
				<table class="it_TaskTable" width="100%">
						<tr class="ListSuperheader">
							<td><div style='padding: 5px'><?php echo $this->_tpl_vars['workRecord']['PROJECT_NAME']; ?>
</div></td>
							<?php $this->assign('prevoutProjectName', $this->_tpl_vars['workRecord']['PROJECT_NAME']); ?>					
						</tr>
				</table>	
			<?php endif; ?>

		
			<table class="it_TaskTable" width="100%">
				<tbody>
					<tr class="ListSubheader">
												
						<!-- checkbox -->
						<td width="10" class="ValignTop"><!--input type=checkbox name="selectAllDocsCB" onClick="selectIssues(<?php echo $this->_tpl_vars['workRecord']['PW_ID']; ?>
, this)"-->&nbsp;</td>
						<!-- task description -->
						<td>
							<?php if ($this->_tpl_vars['workRecord']['PW_ID'] != 0): ?>
								<?php if ($this->_tpl_vars['workRecord']['editWorkURL']): ?>
									<a style='font-weight: normal' href="javascript:void(0)" onClick="openIssuesWork('<?php echo ((is_array($_tmp=@$this->_tpl_vars['workRecord']['P_ID'])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['P_ID']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['P_ID'])); ?>
', '<?php echo $this->_tpl_vars['workRecord']['PW_ID']; ?>
')"><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['itStrings']['il_task_label'])) ? $this->_run_mod_handler('cat', true, $_tmp, ' ') : smarty_modifier_cat($_tmp, ' ')))) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['workRecord']['PW_ID']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['workRecord']['PW_ID'])))) ? $this->_run_mod_handler('cat', true, $_tmp, ": ") : smarty_modifier_cat($_tmp, ": ")))) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['workRecord']['PW_DESC']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['workRecord']['PW_DESC'])); ?>
</a>
								<?php else: ?>
									<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['itStrings']['il_task_label'])) ? $this->_run_mod_handler('cat', true, $_tmp, ' ') : smarty_modifier_cat($_tmp, ' ')))) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['workRecord']['PW_ID']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['workRecord']['PW_ID'])))) ? $this->_run_mod_handler('cat', true, $_tmp, ": ") : smarty_modifier_cat($_tmp, ": ")))) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['workRecord']['PW_DESC']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['workRecord']['PW_DESC'])); ?>

								<?php endif; ?>
							<?php else: ?>
								<?php echo $this->_tpl_vars['itStrings']['app_freeissues_label']; ?>

							<?php endif; ?>
							<!--<?php echo smarty_function_makeLink(array('text' => ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['itStrings']['il_task_label'])) ? $this->_run_mod_handler('cat', true, $_tmp, ' ') : smarty_modifier_cat($_tmp, ' ')))) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['workRecord']['PW_ID']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['workRecord']['PW_ID'])))) ? $this->_run_mod_handler('cat', true, $_tmp, ": ") : smarty_modifier_cat($_tmp, ": ")))) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['workRecord']['PW_DESC']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['workRecord']['PW_DESC'])),'href' => "javascript:void(0)",'onClick' => "\'openIssuesWork(\'|cat:<?",'oldHref' => $this->_tpl_vars['workRecord']['editWorkURL']), $this);?>
-->
													</td>
						<!-- add issue link -->
						<!--td class="AlignRight ValignBottom NoBreak NormalWeight">
							<?php if (! $this->_tpl_vars['workRecord']['CLOSED']): ?>
									<?php if ($this->_tpl_vars['userIsProjman']): ?>
										[<em><a href="<?php echo $this->_tpl_vars['workRecord']['ITSSETUPLINK']; ?>
"><small><?php echo $this->_tpl_vars['itStrings']['il_workflow_btn']; ?>
</small></a></em>]
									<?php endif; ?>
									[<em><a onClick='return issueAddDialog("<?php echo $this->_tpl_vars['workRecord']['P_ID']; ?>
-<?php echo $this->_tpl_vars['workRecord']['PW_ID']; ?>
");' href="javascript:void(0)"><small><?php echo $this->_tpl_vars['itStrings']['il_addissue_btn']; ?>
</small></a></em>]
							<?php else: ?>
								[<small><?php echo $this->_tpl_vars['itStrings']['il_completetask_label']; ?>
</small>]
							<?php endif; ?>
						</td-->
					</tr>
				</tbody>
			</table>
			
		<?php endif; ?>

	<?php endif; ?>
	
	<!-- issue record -->
	<?php if (! ($this->_foreach['issueLoop']['iteration'] == $this->_foreach['issueLoop']['total'])): ?>
		<?php echo smarty_function_math(array('equation' => "x+1",'x' => $this->_tpl_vars['rowIndex'],'assign' => 'nextRowIndex'), $this);?>

		<?php $this->assign('nextRecord', $this->_tpl_vars['issueList'][$this->_tpl_vars['nextRowIndex']]); ?>
	<?php endif; ?>

	<div id='ISSUEBLOCK_<?php echo $this->_tpl_vars['listRecord']['P_ID']; ?>
_<?php echo $this->_tpl_vars['listRecord']['PW_ID']; ?>
_<?php echo $this->_tpl_vars['listRecord']['I_ID']; ?>
' class='it_IssueList'>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "ilist_oneissue.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>	
	</div>
	
	<?php echo smarty_function_math(array('equation' => "x+1",'x' => $this->_tpl_vars['issueIndex'],'assign' => 'issueIndex'), $this);?>

	
	<?php $this->assign('prevPW_ID', $this->_tpl_vars['listRecord']['PW_ID']); ?>

<?php endforeach; else: ?>
	<table class="Grid it_IssueList" width="100%">
		<tbody>
			<tr class="NoRecords">
				<td colspan="<?php echo $this->_tpl_vars['visibleColumnNum']; ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['itStrings']['il_noissues_text'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)); ?>
</td>
			</tr>
		</tbody>
	</table>
<?php endif; unset($_from); ?>


<?php if ($this->_tpl_vars['issueList']): ?>
	<div id='NextRecordsBlock' style='width: 100%; padding-top: 20px; border-top: 1px solid #DDDDDD; padding-bottom: 30px; padding-left: 40px'>
		<?php echo $this->_tpl_vars['issuesCountMessage']; ?>

		<BR>
		<?php echo $this->_tpl_vars['showMoreLink']; ?>

	</div>
<?php endif; ?>