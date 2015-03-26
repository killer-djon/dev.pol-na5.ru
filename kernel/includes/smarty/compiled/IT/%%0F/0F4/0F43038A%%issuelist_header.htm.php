<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:51
         compiled from issuelist_header.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'wbs_label', 'issuelist_header.htm', 5, false),array('function', 'html_options', 'issuelist_header.htm', 8, false),)), $this); ?>
<?php if (! $this->_tpl_vars['pmDisabled']): ?>
	<table class="FormLayout">
		<tr>
				<?php if (! $this->_tpl_vars['inplaceScreen']): ?>
				<td><?php echo smarty_function_wbs_label(array('for' => 'P_ID','text' => $this->_tpl_vars['itStrings']['il_project_label']), $this);?>
</td>
				<td>
					<select title="" name="P_ID" id="P_ID" class="FormControl" style="width: 400px" onChange="this.form.submit()">
						<?php echo smarty_function_html_options(array('values' => $this->_tpl_vars['project_ids'],'selected' => $this->_tpl_vars['P_ID'],'output' => $this->_tpl_vars['project_names']), $this);?>

					</select>
				</td>
			<?php else: ?>
				<input type='hidden' name='P_ID' value='<?php echo $this->_tpl_vars['P_ID']; ?>
'>
			<?php endif; ?>
			<?php if (! $this->_tpl_vars['manyProjects'] && ! $this->_tpl_vars['freeIssues'] && $this->_tpl_vars['P_ID'] != 0): ?>
				<td><?php echo smarty_function_wbs_label(array('for' => 'PW_ID','text' => $this->_tpl_vars['itStrings']['il_task_label']), $this);?>
</td>
				<td>
					<select title="" name="curPW_ID" id="PW_ID" class="FormControl" style="width: 300px" onChange="this.form.submit()">
						<?php echo smarty_function_html_options(array('values' => $this->_tpl_vars['work_ids'],'selected' => $this->_tpl_vars['curPW_ID'],'output' => $this->_tpl_vars['work_names']), $this);?>

					</select>
				</td>
			<?php else: ?>
				<input type='hidden' name='PW_ID' value='<?php echo $this->_tpl_vars['curPW_ID']; ?>
'>
			<?php endif; ?>
		</tr>
	</table>
<?php endif; ?>