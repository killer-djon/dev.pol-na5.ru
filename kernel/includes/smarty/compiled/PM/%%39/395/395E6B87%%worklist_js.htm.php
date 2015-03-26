<?php /* Smarty version 2.6.26, created on 2014-08-08 13:46:31
         compiled from worklist_js.htm */ ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "worklist_common.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<script>
	document.showComplete = "<?php echo $this->_tpl_vars['showComplete']; ?>
";
	document.userRights = "<?php echo $this->_tpl_vars['userRights']; ?>
";
	document.projectIsComplete = <?php if ($this->_tpl_vars['projectIsComplete']): ?>true<?php else: ?>false<?php endif; ?>;
	document.worksOnPage = <?php echo $this->_tpl_vars['worksOnPage']; ?>
;
</script>

<?php if ($this->_tpl_vars['action'] != 'no_project'): ?>
<script src='../cssbased/gantt_column.js'></script>
<script src='../cssbased/assignments_menu.js'></script>
	<?php if ($this->_tpl_vars['projectData']['SCREEN'] == 1): ?>
		<script src='../cssbased/GroupSummary.js'></script>
		<script src='../cssbased/workassignments.js'></script>
	<?php else: ?>
		<script src='../cssbased/worklist_js.js'></script>
	<?php endif; ?>
<script src='../cssbased/worklist_gantt_view.js'></script>
<script src='../../../common/html/cssbased/pageelements/ajax/common_dialog.js'></script>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "workdialog_js.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>


<style>
	#SplitterRightPanelContent {overflow: hidden; overflow-x: auto}
	* html div#SplitterRightPanelContent {overflow: hidden; overflow-x: auto; }
	#works-grid  {position: static !important}
	
	.works-mask-loading {top: 40px}

	#works-grid .x-grid3-cell-inner.x-grid3-col-desc {white-space:normal;overflow: visible; overflow-x: hidden}
	#works-grid .x-grid3-cell-inner.x-grid3-col-gantt {padding: 0px;}
	#works-grid .x-grid3-row-table .x-grid3-summary-row td  {line-height: 20px}
	#works-grid .x-grid3-row-over .menu-btn {width: 16px; background: URL('../../../common/html/res/images/edit2.gif') no-repeat; background-position: -2px; cursor: pointer}
	
	#works-grid .task-complete {}
	#works-grid .x-grid3-row.task {border-right: 0px}
	
	#works-grid .cell-gantt {background: #F0F0F0; vertical-align: top; padding-top: 2px; background-image: URL(../img/line-dotted.gif); background-repeat: repeat-y; background-position: 100px 0px;}
	#works-grid .cell-gantt {border-left: 1px solid #999}
	#works-grid .task-complete .cell-gantt {background-color: #E0E0E0; vertical-align: top; padding-top: 2px; }
	#works-grid .x-grid3-hd-inner{padding-bottom: 9px}
	#works-grid .x-grid3-hd-inner .weekend{background: #BBB; color: black}
	#works-grid .bar {height: 15px; text-align: center; color: #EEE; font-weight: bold; font-size: 8pt; position: absolute; text-overflow: ellipsis; overflow-x: hidden}
	#works-grid .bar.green {background: #6ABD2D}
	#works-grid .bar.blue {background: #0EA0F4}
	#works-grid .bar.red {background: #FF5D00}
	#works-grid .task-complete .cell-gantt .bar {
		filter:progid:DXImageTransform.Microsoft.Alpha(opacity=50); /* IE 5.5+*/
		-moz-opacity: 0.5; /* Mozilla 1.6 <*/
		-khtml-opacity: 0.5; /* Konqueror 3.1, Safari 1.1 */
		opacity: 0.5; /* CSS3 - Mozilla 1.7b +, Firefox 0.9 +, Safari 1.2+, Opera 9 */
	}
	
	#works-grid .x-grid3-summary-table {background: #E7E7E7}
	#works-grid .x-grid3-summary-table .cell-gantt {background: #D7D7D7}
	#works-grid .x-grid3-summary-table  .x-grid3-summary-row td  {line-height: 15px}
	
	#works-grid .x-grid3-cell-inner {overflow-x: hidden}
	.x-grid3-cell-inner {white-space: normal; overflow-x: hidden}
	.x-grid3-hd-row td, .x-grid3-row td, .x-grid3-summary-row td {font-family:"Trebuchet MS"; font-size:13px;}
	.x-small-editor .x-form-field {font-family:"Trebuchet MS"; font-size:13px;}
	
	.add-menu-item {background-image: URL("../../../common/html/res/images/add.gif")}
	.edit-menu-item {background-image: URL("../../../common/html/res/images/edit.gif")}
	.delete-menu-item {background-image: URL("../../../common/html/res/images/delete.gif")}
	
	.x-grid3-hd-row .x-grid3-td-desc.x-grid3-column-resizable, .x-grid3-hd-row .x-grid3-td-estimate { border-right:1px dashed #666;}
	
	/* remove the trigger and the mouse-cursor-hand from groupable-grid-headers */
	.x-grid-group-hd {
	    border-bottom: 1px solid #DDD;
	    border-top: 2px solid #99bbe8;
	    cursor:default;
	    padding-top:6px;
	}
	.x-grid-group-hd div {
	    background-image: none;
	    padding:4px 4px 4px 4px;
	    color:#3764a0;
	    font:bold 11px tahoma, arial, helvetica, sans-serif;
	    font-size: 15px; color: #333; font-family: "Trebuchet MS"
	}
	
	.asgmt-tooltip {padding: 5px; width: 200px; }
	.asgmt-tooltip {font-size: 13px; font-family: "Trebuchet MS"}
	.asgmt-tooltip ul {border-bottom: 1px solid #DDD; padding: 0px; margin: 0px; margin-bottom: 5px}
	.asgmt-tooltip ul li {padding: 0px; margin: 0px; margin-bottom: 2px}
	
	.x-grid-empty {text-align: center; font-size: 0.9em }
	
	#works-grid .status.pending {color: green}
	#works-grid .status.inprogress {color: blue}
	#works-grid .status.overdue {color: red}
	#works-grid .status.complete {color: #444}
	
	.x-mask-loading div {font-size: 15px;font-weight: bold }
	.ext-el-mask-msg {border: 0px; padding: 0px}
	.x-mask-loading div{padding:15px 15px 15px 35px; background-position:10px 15px; border: 1px solid #999; }
	.x-paging-info {color: #000}
	
	.x-toolbar {background-color: #E2E9E9}
	.x-toolbar label {fon-family: "Trebuchet MS"; font-size: 13px}
	
	.works-paging-toolbar .x-paging-info {left: 10px; width: 140px}
	.works-paging-toolbar table {margin-left: 140px}
	.works-paging-toolbar table table {margin-left: 0px}
	
	.works-paging-toolbar .x-tbar-loading {display: none}
</style>

<script>
	/*function moveOption (from, to, index) {
		var opt = from.options[index];
		to.options[to.options.length] = new Option(opt.text, opt.value);
		from.remove(index);
	}*/
	
	
	function isCompleteProject (alertMessage) {
		if (document.projectIsComplete) {
			alert (pmStrings.pm_projectcompleted_error);
			return true;
		}
		return false;		
	}



	function addWork (btn) {
		if (isCompleteProject(true))
			return;
		
		var workDialog = getWorkDialog ();
		workDialog.setMode("new");
		workDialog.showDialog (btn);	
		workDialog.form.getForm().reset();
		workDialog.afterLoad ();
	};
	
	function openWork (work) {
		if (isCompleteProject(true))
			return;
		var workDialog = getWorkDialog ();
		workDialog.setMode("modify", work);
		workDialog.showDialog ();	
		workDialog.form.getForm().loadRecord(work);
		workDialog.afterLoad ();
	};
	
	function deleteWorks () {
		if (isCompleteProject(true))
			return;
		if (!confirmTaskDelete())
			return false;
			
		var selectedRecords = document.worksGrid.getSelectionModel().getSelections();	
		var ids = new Array ();
		for (var i = 0; i < selectedRecords.length; i++)
			ids.push (selectedRecords[i].id);
		
		AjaxLoader.doRequest ("../ajax/project_delwork.php", 
			function(response, options) {
				var result = Ext.decode(response.responseText);
				if(result.success) {
					/*for (var i = 0; i < ids.length; i++) {
						document.worksGrid.store.remove(document.worksGrid.store.getById(ids[i]));
						}*/
					//document.worksGrid.ganttColumn.recalcDates ();
					//document.worksGrid.updateGantt();
				} else {
					AjaxLoader.ajaxServerError(result);
				}
				document.worksGrid.store.reload ();
			},
			{projectId: document.getElementById("project").value, "ids[]": ids}, {scope: this}
		);
	}
	
    function exportWorks()
    {
        var selectedRecords = document.worksGrid.getSelectionModel().getSelections();   
        var ids = new Array ();
        for (var i = 0; i < selectedRecords.length; i++)
            ids.push (selectedRecords[i].id);
        var projectId = document.getElementById("project").value;
        
        window.location = '<?php echo @PAGE_PM_EXPORT_TASKS; ?>
?project_id='+projectId+'&works_ids='+ids.join('|');
        return true;
    }
	  
	
	<?php if ($this->_tpl_vars['action'] != 'no_project'): ?>
		Ext.onReady(function(){
			//Ext.QuickTips.init();
			//return;
			Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
			
			<?php if ($this->_tpl_vars['projectData']['SCREEN'] == 1): ?>
				grid = new AssignmentsGrid ();
			<?php else: ?>
				grid = new GanttGrid ();
			<?php endif; ?>
			
			grid.getView().getRowClass = function (record, index, rowParams, store) {
		  	if (record.get("end"))
		  		return "task-complete";
		  	else
		  		return "task";
		  }
		});
	<?php endif; ?>
	
	function UpdateMainGrid () {
		if (!document.worksGrid)
			return;
		if (document.printMode) {
			document.worksGrid.setWidth (900);
		} else {
			var splitterHeight = GetSplitterHeight();
			document.worksGrid.setHeight (splitterHeight);
			var splitterWidth = SkinProvider.CustomSplitterGetSplitterWidth();
			//document.worksGrid.setWidth (splitterWidth + 300);
			document.worksGrid.setMyWidth (splitterWidth);
			document.worksGrid.updateGantt ();
		}
	}
	
	function ShowAssignmentsPanel (fromEl, usersCount, usersStr, recordId, canEdit) {
		if (!document.assignmentsPanel) {
			document.assignmentsPanel = new Ext.ux.AsgmtMenu ({});
		}
		
		if (document.assignmentsPanel.rendered) {
			document.assignmentsPanel.update (usersCount, usersStr, recordId, canEdit);
			document.assignmentsPanel.show (fromEl);
		} else {
			document.assignmentsPanel.show (fromEl);
			document.assignmentsPanel.rendered = true;
			document.assignmentsPanel.update (usersCount, usersStr, recordId, canEdit);			
		}
	}
	
	
	function ShowHideCompleteTasks (link) {
		document.showComplete = Math.abs(document.showComplete - 1);
		document.worksGrid.showHideCompleteTasks (document.showComplete == 0);
		link.innerHTML = (document.showComplete == 0) ? pmStrings.pm_showcomplete_menu : pmStrings.pm_hidecomplete_menu;
	}

	

</script>