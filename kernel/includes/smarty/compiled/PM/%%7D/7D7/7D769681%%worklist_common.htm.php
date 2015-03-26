<?php /* Smarty version 2.6.26, created on 2014-08-08 13:46:31
         compiled from worklist_common.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'worklist_common.htm', 58, false),)), $this); ?>
<script>
	var pmStrings = {
		amt_taskstartdue_message : "<?php echo $this->_tpl_vars['pmStrings']['amt_taskstartdue_message']; ?>
",
		amt_taskstartcomplete_message : "<?php echo $this->_tpl_vars['pmStrings']['amt_taskstartcomplete_message']; ?>
",
		amt_taskcompletetoday_message : "<?php echo $this->_tpl_vars['pmStrings']['amt_taskcompletetoday_message']; ?>
",
		amt_assignmentstasks_caption: "<?php echo $this->_tpl_vars['pmStrings']['amt_assignmentstasks_caption']; ?>
",
		amt_tasks_caption: "<?php echo $this->_tpl_vars['pmStrings']['amt_tasks_caption']; ?>
",
		amt_assignments_caption: "<?php echo $this->_tpl_vars['pmStrings']['amt_assignments_caption']; ?>
",
		pm_hidecomplete_menu: "<?php echo $this->_tpl_vars['pmStrings']['pm_hidecomplete_menu']; ?>
",
		pm_id_title: "<?php echo $this->_tpl_vars['pmStrings']['pm_id_title']; ?>
",
		pm_description_title: "<?php echo $this->_tpl_vars['pmStrings']['pm_description_title']; ?>
",
		pm_start_title: "<?php echo $this->_tpl_vars['pmStrings']['pm_start_title']; ?>
",
		pm_due_title: "<?php echo $this->_tpl_vars['pmStrings']['pm_due_title']; ?>
",
		pm_complete_title: "<?php echo $this->_tpl_vars['pmStrings']['pm_complete_title']; ?>
",
		pm_billable_title: "<?php echo $this->_tpl_vars['pmStrings']['pm_billable_title']; ?>
",
		pm_cost_title: "<?php echo $this->_tpl_vars['pmStrings']['pm_cost_title']; ?>
",
		pm_gantt_title: "<?php echo $this->_tpl_vars['pmStrings']['pm_gantt_title']; ?>
",
		pm_assignments_title: "<?php echo $this->_tpl_vars['pmStrings']['pm_assignments_title']; ?>
",
		pm_loading_label: "<?php echo $this->_tpl_vars['pmStrings']['pm_loading_label']; ?>
",
		pm_addtask_title: "<?php echo $this->_tpl_vars['pmStrings']['pm_addtask_title']; ?>
",
		pm_edittask_title: "<?php echo $this->_tpl_vars['pmStrings']['pm_edittask_title']; ?>
",
		amr_notassigned_title: "<?php echo $this->_tpl_vars['pmStrings']['amr_notassigned_title']; ?>
",
		amr_assigned_title: "<?php echo $this->_tpl_vars['pmStrings']['amr_assigned_title']; ?>
",
		amr_taskcomplete_label: "<?php echo $this->_tpl_vars['pmStrings']['amr_taskcomplete_label']; ?>
",
		pm_summary_title: "<?php echo $this->_tpl_vars['pmStrings']['pm_summary_title']; ?>
",
		amr_startdate_label: "<?php echo $this->_tpl_vars['pmStrings']['amr_startdate_label']; ?>
",
		amr_duedate_label: "<?php echo $this->_tpl_vars['pmStrings']['amr_duedate_label']; ?>
",
		amr_completedate_label: "<?php echo $this->_tpl_vars['pmStrings']['amr_completedate_label']; ?>
",
		amr_billable_label: "<?php echo $this->_tpl_vars['pmStrings']['amr_billable_label']; ?>
",
		amr_cost_label: "<?php echo $this->_tpl_vars['pmStrings']['amr_cost_label']; ?>
",
		amr_costcur_label: "<?php echo $this->_tpl_vars['pmStrings']['amr_costcur_label']; ?>
",
		pm_showcomplete_menu: "<?php echo $this->_tpl_vars['pmStrings']['pm_showcomplete_menu']; ?>
",
		pm_hidecomplete_menu: "<?php echo $this->_tpl_vars['pmStrings']['pm_hidecomplete_menu']; ?>
",
		pm_week_label: "<?php echo $this->_tpl_vars['pmStrings']['pm_week_label']; ?>
",
		pm_noassignmentsshort_label: "<?php echo $this->_tpl_vars['pmStrings']['pm_noassignmentsshort_label']; ?>
",
		pm_noassignments_label: "<?php echo $this->_tpl_vars['pmStrings']['pm_noassignments_label']; ?>
",
		pm_assignmentsedit_label: "<?php echo $this->_tpl_vars['pmStrings']['pm_assignmentsedit_label']; ?>
",
		pm_notasks_title: "<?php echo $this->_tpl_vars['pmStrings']['pm_notasks_title']; ?>
",
		pm_status_title: "<?php echo $this->_tpl_vars['pmStrings']['pm_status_title']; ?>
",
		pm_status_pending: "<?php echo $this->_tpl_vars['pmStrings']['pm_status_pending']; ?>
",
		pm_status_overdue: "<?php echo $this->_tpl_vars['pmStrings']['pm_status_overdue']; ?>
",		
		pm_status_inprogress: "<?php echo $this->_tpl_vars['pmStrings']['pm_status_inprogress']; ?>
",
		pm_status_complete: "<?php echo $this->_tpl_vars['pmStrings']['pm_status_complete']; ?>
",
		pm_projectcompleted_error: "<?php echo $this->_tpl_vars['pmStrings']['pm_projectcompleted_error']; ?>
",
		amt_taskcompleteempty_message: "<?php echo $this->_tpl_vars['pmStrings']['amt_taskcompleteempty_message']; ?>
",
		amr_addanother_btn: "<?php echo $this->_tpl_vars['pmStrings']['amr_addanother_btn']; ?>
",
		pm_startsin_note: "<?php echo $this->_tpl_vars['pmStrings']['pm_startsin_note']; ?>
",
		pm_duein_note: "<?php echo $this->_tpl_vars['pmStrings']['pm_duein_note']; ?>
",
		pm_inprogress_note: "<?php echo $this->_tpl_vars['pmStrings']['pm_inprogress_note']; ?>
",
		pm_overdue_note: "<?php echo $this->_tpl_vars['pmStrings']['pm_overdue_note']; ?>
",
		pm_completed_note: "<?php echo $this->_tpl_vars['pmStrings']['pm_completed_note']; ?>
",
		pm_aheadschedule_note: "<?php echo $this->_tpl_vars['pmStrings']['pm_aheadschedule_note']; ?>
",
		pm_noassignments_label: "<?php echo $this->_tpl_vars['pmStrings']['pm_noassignments_label']; ?>
",
		pm_tasks_label: "<?php echo $this->_tpl_vars['pmStrings']['pm_tasks_label']; ?>
",
		pm_of_label: "<?php echo $this->_tpl_vars['pmStrings']['pm_of_label']; ?>
"
	};
	var dateDisplayFormat = "<?php echo $this->_tpl_vars['dateDisplayFormat']; ?>
";
	var localDate = new Date(<?php echo ((is_array($_tmp=$this->_tpl_vars['currentTimestamp'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y,%m,%d,%H,%M,%S") : smarty_modifier_date_format($_tmp, "%Y,%m,%d,%H,%M,%S")); ?>
);
	localDate.setMonth(localDate.getMonth()-1);
	var currenciesList = [<?php echo $this->_tpl_vars['currenciesListStr']; ?>
];
	
	var availableUsers = {
  	<?php $_from = $this->_tpl_vars['availableUsers']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['users'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['users']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['user']):
        $this->_foreach['users']['iteration']++;
?>"<?php echo $this->_tpl_vars['user']['id']; ?>
": ["<?php echo $this->_tpl_vars['user']['id']; ?>
","<?php echo $this->_tpl_vars['user']['name']; ?>
"]<?php if (! ($this->_foreach['users']['iteration'] == $this->_foreach['users']['total'])): ?>,<?php endif; 
 endforeach; endif; unset($_from); ?>
  };
  
  <?php if ($this->_tpl_vars['projectData']['P_ID']): ?>document.projectId = "<?php echo $this->_tpl_vars['projectData']['P_ID']; ?>
";<?php endif; ?>
</script>

<script src='../../../PM/html/cssbased/worklist_common.js'></script>