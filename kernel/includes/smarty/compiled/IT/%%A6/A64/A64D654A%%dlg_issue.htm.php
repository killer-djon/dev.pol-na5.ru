<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:51
         compiled from dlg_issue.htm */ ?>
<script src='../../../common/html/cssbased/pageelements/ajax/items_selector.js'></script>
<script src='../../../common/html/cssbased/pageelements/ajax/users_selector.js'></script>

<style>
	#issue-files ul.files {margin: 0px; padding: 0px}
	#issue-form legend {display: none}
	/*#issue-form #files-wrap-fieldset legend {padding: 0px !important; display: block;}
	#issue-form #files-wrap-fieldset fieldset legend {display: none}*/
	#issue-form .x-form-item {margin-bottom: 10px !important;}
	#issue-form .x-form-item.label-item {margin-bottom: 0px !important;}
	#issue-form label {padding-bottom: 0px !important;}
	/*.ext-ie #issue-form #files-wrap-fieldset {margin-top: 20px !important;}
	.ext-ie #issue-form #files-wrap-fieldset #files-column {margin-top: 8px;}*/
	
	.x-combo-list-inner {overflow-x: hidden !important;}
	
	.assignmentDlgPanel {position: absolute; width: 100px; height: 50px; left: 260px; border: 1px solid red; z-index: 10000}
</style>

<script>

	function getIssueDialog () {
		if (document.issueDialog)
			return document.issueDialog;
			
		var width = getCookieProvider().get("issueDialogWidth", 500);
		var config = {width: width, height: 300, title: "Issue", modal: true, resizable: true, minWidth: 480, minHeight: 300, layout: 'fit'};
		
		var issueDialog = new SimpleDialog ("issue-window");
		issueDialog.defaultHeight = getCookieProvider().get("issueDialogHeight", config.height);
		
		var form = getIssueForm ();
		//issueDialog.usersSelector = new UsersSelector ({renderTo: "users-selector", toTitle: "Assigned", fromTitle: "Not Assigned"}) ;//new GenericItemsSelector("availableUsersSelect", "assignedUsersSelect", availableUsers, "usersSelectorLeftBtn", "usersSelectorRightBtn");		
		issueDialog.form = form;		
		
		config.items = [
			form
		];
		config.buttons = [
			/*{text: "Add File", handler: function() {this.addFileField ()}, scope: issueDialog},
			{text: "Delete Files", handler: function() {this.form.getForm().submit ({params: {action: "delFiles", P_ID: document.P_ID}});}, scope: issueDialog},
			new Ext.Panel ({border: false, width: 150, html: "&nbsp;"}),*/
			{text: "<?php echo $this->_tpl_vars['kernelStrings']['app_save_btn']; ?>
", handler: function() {this.trySave()}, scope: issueDialog},
			{text: "<?php echo $this->_tpl_vars['itStrings']['ami_addanother_btn']; ?>
", id: "save-and-add-btn", handler: function () {this.trySave (true)}, scope: issueDialog},
			{text: "<?php echo $this->_tpl_vars['kernelStrings']['app_cancel_btn']; ?>
", handler: function () {this.dialog.hide ()}, scope: issueDialog}
		];
		
		
		issueDialog.deletedFiles = new Array ();
		
		issueDialog.loadConfig(config);
		issueDialog.loadIssue = function (P_ID, PW_ID, I_ID) {
			this.form.getForm().baseParams = {P_ID: P_ID, PW_ID: PW_ID, I_ID: I_ID};
			this.form.getForm().load({url:'../ajax/issue_get.php'});
		}
		
		issueDialog.reload = function () {
			this.form.getForm().load({url:'../ajax/issue_get.php'});
		}
		
		issueDialog.setMode = function (mode) {
			this.mode = mode;
			if (mode == "new") {
				issueDialog.files = new Array ();
				issueDialog.deletedFiles = new Array ();
				if (this.form.getForm().addAnotherAfterSave)
					this.form.getForm().reset ();
			}
		}
		
		issueDialog.loadAssignments = function(data, resetValue) {
			issueDialog.assignments = data;
			var field = issueDialog.form.getForm().findField("U_ID_ASSIGNED");
			field.store.loadData(data);
			if (resetValue)
				field.setValue ("");
		}
		
		issueDialog.loadStatuses = function (data, resetValue) {
			var field = issueDialog.form.getForm().findField("I_STATUSCURRENT");
			field.store.loadData(data);
			if (resetValue)
				field.setValue (data[0]);
		}
		
		issueDialog.addFileField = function () {
			var field = new Ext.form.TextField ({name: 'issuefile[]', hideLabel: true, inputType: "file", style: 'padding: 0px; margin-left: 3px', autoCreate: {tag: "input", type: "file", size: "28", autocomplete: "off"} });
			this.form.filesFieldset.insert(0, field);
			this.form.filesFieldset.fileElems.push (field);
			this.form.filesFieldset.doLayout ();
			this.updateHeight ();
			this.outputFiles ();
		}
		
		issueDialog.deleteFiles = function () {
			var fields = Ext.get("issue-files").query("input");
			var delFlag = false;
			for (var i = 0; i < fields.length; i++) {
				var field = fields[i];
				if (field.type != "checkbox" || !field.checked)
					continue;
				this.deletedFiles.push(field.value);
				delFlag = true;
			}
			if (!delFlag) {
				alert("<?php echo $this->_tpl_vars['itStrings']['il_nofilesselected_message']; ?>
");
				return;
			}
				
			this.outputFiles ();
			//this.form.getForm().submit ({params: {action: "delFiles", P_ID: document.P_ID}});
		}
		
		issueDialog.trySave = function  (andAddAnother) {
			if (this.form.getForm().isValid()) {
				if (this.mode == "new") {
					var field = this.form.getForm().findField("COMBO_PW_ID");
					var value = field.getValue();
					if (value == null) {
						alert("<?php echo $this->_tpl_vars['itStrings']['il_notaskselected_message']; ?>
");
						field.markInvalid ("<?php echo $this->_tpl_vars['itStrings']['il_notaskselected_message']; ?>
");
						return;
					}
					this.selectedPwId = value;
				}
				this.form.getForm().addAnotherAfterSave = andAddAnother;
				this.form.getForm().submit ({params: {"delfile[]": issueDialog.deletedFiles, action: issueDialog.mode, addedIssues: document.addedIssues, viewP_ID: document.P_ID}});
			}
		}
		
		issueDialog.updateHeight = function (){ 
			var height = this.defaultHeight;// + this.form.filesFieldset.getInnerHeight();
			height += this.getAddHeight ();
			this.dialog.manualResize = true;
			this.dialog.setHeight (height);
			this.dialog.manualResize = false;
			this.updateSizes ();
		}
		
		issueDialog.outputFiles = function() {
			var files = issueDialog.files;
			var filesHTML = "";
			var filesCount = 0;
			if (files) {
				if (files.length ==0 && this.form.filesFieldset.fileElems.length == 0) 
					filesHTML += "<div style='margin-top: 30px; text-align: center'><?php echo $this->_tpl_vars['itStrings']['il_dlgeditnofiles_label']; ?>
</div>";
				filesHTML += "<ul class='files'>";
				for (var i = 0; i < files.length; i++) {
					var fileInfo = files[i];
					if (issueDialog.deletedFiles.indexOf(fileInfo.delname) > -1)
						continue;
					filesHTML += "<li><input type='checkbox' name='delfile[]' value='" + fileInfo.delname + "'><a target='_blank' href='" + fileInfo.url + "'>" + fileInfo.name + "(" + fileInfo.size + ")</a>";
					filesCount++;
				}
				filesHTML += "</ul>";
			}
			Ext.get("issue-files").update(filesHTML);
			Ext.get("issue-files-count").update ("(" + filesCount + ")");
			
			
			if (filesCount > 0)
				this.btnDelFiles.enable ();
			else
				this.btnDelFiles.disable ();
			//Ext.get("issue-files").innerHTML = filesHTML;
		}
		
		issueDialog.toggleFilesPanel = function  () {
			if (this.form.filesWrapFieldset.hidden)
				this.form.filesWrapFieldset.show ();
			else
				this.form.filesWrapFieldset.hide ();
			this.updateHeight ();
		}
		
		issueDialog.getAddHeight = function () {
			var addHeight = 0;
			if (!issueDialog.form.taskFieldset.hidden)
				addHeight += issueDialog.form.taskFieldset.getInnerHeight();
			if (!issueDialog.form.statusFieldset.hidden)
				addHeight += issueDialog.form.statusFieldset.getInnerHeight();
			if (!issueDialog.form.filesWrapFieldset.hidden)
				addHeight += issueDialog.form.filesWrapFieldset.getInnerHeight();
			return addHeight;
		}
		
		issueDialog.getAssignsManageLink = function () {
			if (this.assignsManageLink)
				return this.assignsManageLink;
			var newEl = document.createElement("div");
			newEl.style.display = "inline";
			newEl.style.paddingLeft = "25px";
			newEl.id = "manageLink";
			//newEl.innerHTML = "<div id='manageLink' href='javascript:void(0)' onClick='openTaskAssignmentsDialog()'></a>";
			document.getElementById("U_ID_ASSIGNED").parentNode.appendChild (newEl);
			this.assignsManageLink = newEl;
			
			var btn = new Ext.Button ({renderTo: "manageLink", handler: openTaskAssignmentsDialog, text: "<?php echo $this->_tpl_vars['itStrings']['il_manage_btn']; ?>
", style: "position: absolute; right: -70px; top: 0px"});
			
			return this.assignsManageLink;
		}
		
		form.on("actioncomplete", function (frm, action) {
			if (action.type == "load") {
				issueDialog.issueData = action.result.data;
				
				issueDialog.showDialog ();
				
				var field = frm.findField("U_ID_ASSIGNED");
				issueDialog.canManageUsers = action.result.canManageUsers;
				issueDialog.loadAssignments (action.result.assignments);
				var assigned = (action.result.data.U_ID_ASSIGNED == null) ? "" : action.result.data.U_ID_ASSIGNED;
				field.setValue (assigned);
				
				issueDialog.files = action.result.files;
				issueDialog.deletedFiles = new Array ();				
				issueDialog.outputFiles ();
				
				issueDialog.form.getForm().findField("I_DESC").focus ();
				issueDialog.form.taskDataLoaded();
					
				//issueDialog.updateHeight();
			} else {
				var callback = function () {
					if (frm.addAnotherAfterSave) {
						issueDialog.setMode("new");
						issueDialog.onAfterShow();
					} else {
						issueDialog.dialog.hide ();
					}
				}
				refreshIssueBlock (action.result.P_ID, action.result.PW_ID, action.result.I_ID, action.result.html, callback);
				//refreshList ();
			}
		});
		
		form.on("actionfailed", function(form, action) {
			var result = Ext.decode(action.response.responseText);
			AjaxLoader.ajaxServerError(result);
		});
		
		issueDialog.updateSizes = function  () {
	    var descHeight = issueDialog.dialog.getInnerHeight() - 112;
	    if (Ext.isSafari)
	    	descHeight -= 30;
	    descHeight -= issueDialog.getAddHeight ();	    	
	    issueDialog.form.descField.setHeight(descHeight);
		}
		
		
		issueDialog.onAfterShow = function () {
			this.alreadyLoaded = true;
			
			
			if (this.mode == "edit") {
				this.form.taskFieldset.hide ();
				this.form.statusFieldset.hide ();
				
				Ext.get("save-and-add-btn").dom.parentNode.style.display = "none";
			} else {
				this.form.taskFieldset.show ();
				this.form.statusFieldset.show ();
				Ext.get("issue-files").update("");
				
				Ext.get("save-and-add-btn").dom.parentNode.style.display = "block";
			}
			
			var filesFieldset = this.form.filesFieldset; 
			for(var i = 0; i < filesFieldset.fileElems.length; i++) {
				var c = filesFieldset.fileElems[i];
				var cWrapper = c.el.dom.parentNode.parentNode;
				cWrapper.parentNode.removeChild(cWrapper);
			}
			filesFieldset.fileElems = new Array ();
			
			this.form.filesWrapFieldset.hide ();
			
			//this.form.getForm().findField("NEW_FILE_FIELD").setValue ("");
			//if (!this.usersSelector.initialized)
	  		//this.usersSelector.initialize();
	  		
	  	if (this.btnAddFile == null) {
		  	this.btnAddFile = new Ext.Button ({text: "<?php echo $this->_tpl_vars['itStrings']['il_dlgedit_addfile_btn']; ?>
", handler: this.addFileField, scope: this, minWidth: 80});
		  	this.btnAddFile.render(document.body, 'btnAddFile');
		  	
		  	this.btnDelFiles = new Ext.Button ({text: "<?php echo $this->_tpl_vars['itStrings']['il_dlgedit_deletefile_btn']; ?>
", handler: this.deleteFiles, scope: this, minWidth: 80});
		  	this.btnDelFiles.render(document.body, 'btnDelFiles');
		  	
		  	this.dialog.on("resize", function (dlg, width, height) {
					if (!issueDialog.alreadyLoaded || !issueDialog.form.descField || dlg.manualResize)
		    		return;
		    		
		    	var defaultHeight = this.getSize().height - issueDialog.getAddHeight ();
		    	var cookieProvider = getCookieProvider ();
		    	cookieProvider.set("issueDialogHeight", defaultHeight);
		    	issueDialog.defaultHeight = defaultHeight;
		    	
		    	cookieProvider.set("issueDialogWidth", width);
		    	
		    	issueDialog.updateSizes ();		    	
		    });
	  	}
	  	
	  	if (this.mode == "new") {
	  		this.dialog.setTitle ("<?php echo $this->_tpl_vars['itStrings']['ami_addissue_title']; ?>
");
	  		this.outputFiles();
	  	} else {
	  		if (this.issueData)
	  			this.dialog.setTitle ("<?php echo $this->_tpl_vars['itStrings']['ami_modissue_title']; ?>
: " + this.issueData.DISPLAY_NUM);
	  	}
	  	
	  	if (this.selectedPwId) {
		  	var field = this.form.getForm().findField("COMBO_PW_ID");
				field.setValue (this.selectedPwId);
				if (this.form.getForm().addAnotherAfterSave)
					field.fireEvent ("select", field, null, null);
	  	}
	  	
	  	issueDialog.updateHeight();
	  }
	  
	  
		document.issueDialog = issueDialog;
		return issueDialog;
	}
	
	
	function getIssueForm () {
	
		var priority = new Ext.data.SimpleStore({fields: ['value', 'label'], data : [[2,"<?php echo $this->_tpl_vars['itStrings']['app_issuehigh_text']; ?>
"], [1,"<?php echo $this->_tpl_vars['itStrings']['app_issuenormal_text']; ?>
"], [0,"<?php echo $this->_tpl_vars['itStrings']['app_issuelow_text']; ?>
"]]});
		var usersStore = new Ext.data.SimpleStore ({fields: ['id', 'name']});
		var descField = new Ext.form.TextArea({ emptyText:'<?php echo $this->_tpl_vars['itStrings']['il_adddescription_text']; ?>
', xtype:'textarea', id:'I_DESC', name: 'fields[I_DESC]', hideLabel: true, /*fieldLabel: 'Description' + '<div id="issue-dialog-record-id" style="position: absolute; top: 8px; right: 23px"></div>', */ allowBlank: false, height:120, anchor:'98%', tabIndex: 1});
		
		var statusStore = new Ext.data.SimpleStore({fields: ['value']});
		
		var priorityField = new Ext.form.ComboBox({
        id: 'I_PRIORITY', fieldLabel: "<?php echo $this->_tpl_vars['itStrings']['ami_priority_label']; ?>
",  hiddenName: "fields[I_PRIORITY]",
        store: priority, valueField: 'value', displayField:'label', value: 1,
        typeAhead: true, mode: 'local', triggerAction: 'all', selectOnFocus:true, forceSelection: true,
        width: 90
    });
    var statusField = new Ext.form.ComboBox ({
    	id: 'I_STATUSCURRENT', hiddenName: 'fields[I_STATUSCURRENT]', fieldLabel: '<?php echo $this->_tpl_vars['itStrings']['ami_status_label']; ?>
',
    	store: statusStore, valueField: 'value', displayField:'value',
    	typeAhead: true, mode: 'local', triggerAction: 'all', selectOnFocus:true, forceSelection: true, width: 290
    });
    var assignsCombo = new Ext.form.ComboBox ({
    	id: 'U_ID_ASSIGNED', hiddenName: 'fields[U_ID_ASSIGNED]', fieldLabel: '<?php echo $this->_tpl_vars['itStrings']['ami_assignto_label']; ?>
',
    	store: usersStore, valueField: 'id', displayField: 'name',
    	typeAhead: true, mode: 'local', triggerAction: 'all', selectOnFocus:true, forceSelection: true, hideTrigger:false, width: 290, allowBlank: false
    });
    //var newFileField = new Ext.form.TextField ({id:"NEW_FILE_FIELD", fieldLabel: "Add File", name: 'issuefile', inputType: "file", autoCreate: {tag: "input", type: "file", size: "76", autocomplete: "off"}});
    
    var taskCombo = new Ext.form.ComboBox ({
    	id: "COMBO_PW_ID", hiddenName: "fields[P_PW]", fieldLabel: '<?php echo $this->_tpl_vars['itStrings']['ami_task_label']; ?>
', editable: false, 
    	store: document.worksStore, valueField: 'P_PW', displayField: 'PW_DESC',
    	typeAhead: true, mode: 'local', triggerAction: 'all', selectOnFocus:true, forceSelection: true, width: 290
    });
    
    taskCombo.on("beforeselect", function(box, record, index) {
    	if (record == null && index != null) {
    		record = box.store.getAt(index);
    	}
    	if (!record)
    		return;
    	
    	record.data['PW_DESC'] = clearHTML(record.data['PW_DESC']);
		});
    
	  taskCombo.on("select", function(field, record, index) {
	    var newValue = field.getValue();
	    var p = newValue.split("-");
	    if (newValue == "notask") {
	    	field.setValue(taskCombo.oldValue);
	    	return false;
	    }
	    var pId = p[0];
	    var pwId = p[1];
	    AjaxLoader.doRequest ("../ajax/task_data.php", 
    		function(response, options) {
    			var result = Ext.decode(response.responseText);
    			var idlg = document.issueDialog;
    			var ifrm = idlg.form;
    			
    			idlg.loadAssignments(result.assignments, true);
    			idlg.workData = result.workData;
					idlg.loadStatuses (result.statuses, true);
					
					var assigned = (result.data == null || result.data.U_ID_ASSIGNED == null) ? "" : result.data.U_ID_ASSIGNED;
					var assignCombo = ifrm.getForm().findField("U_ID_ASSIGNED");
					var ind = findAssignmentIndex(assignsCombo.store, assigned);
					if (ind == -1)
						assignsCombo.setValue("");
					else					
						assignCombo.setValue (assigned);
					
					
					if (!ifrm.getForm().baseParams)
						ifrm.getForm().baseParams = new Array ();
					ifrm.getForm().baseParams.P_ID = pId;
					ifrm.getForm().baseParams.PW_ID = pwId;
					idlg.canManageUsers = result.canManageUsers;
					ifrm.taskDataLoaded();
    		},
    		{P_ID: pId, PW_ID: pwId}
    	);
    	taskCombo.oldValue = newValue;
    });
    
    statusField.on("change", function(field, newValue) {
    	var form = document.issueDialog.form.getForm();
    	form.baseParams.STATUS = newValue;
    	form.load({url:'../ajax/task_data.php'});
    });
    
    var taskFieldset =new Ext.form.FieldSet ({id:'task-fieldset', labelAlign: 'left', items: taskCombo, autoHeight: true, border: false, style: 'padding: 0px; padding-top: 0px; padding-bottom: 0px'});
    var statusFieldset = new Ext.form.FieldSet ({id: 'status-fieldset', labelAlign: 'left', items: statusField, autoHeight: true, border: false, style: 'padding: 0px; padding-top: 0px; padding-bottom: 0px'});
    var filesFieldset = new Ext.form.FieldSet ({id: 'files-fieldset', autoScroll: true, autoHeight: false, style: 'padding: 4px 0px; border: 1px solid #CCC', height: 90, border: false, items: [{xtype: "panel", border: false, style: 'padding: 0px; margin:0px', html: "<div id='issue-files'></div>"}]});
    
    var filesWrapFieldset = new Ext.form.FieldSet ({
			id: 'files-wrap-fieldset', height: 103, border: false, 
			width: 450, style: 'padding: 0px',
			items:
			{layout:'column', border: false, id: 'files-column', items: [
				{layout: 'form', columnWidth: .72, border: false, items: filesFieldset, border: false, style: 'padding-left: 0px'},
				{layout: 'form', columnWidth: .28, border: false, items: [{xtype: 'panel', border: false, html: "<div id='btnAddFile' style='margin-bottom: 5px'></div><div id='btnDelFiles'></div>", style: 'padding-left: 10px; '}]}
				]
			}
		});
    
    var form = new Ext.FormPanel ({
			//baseParams: {"fields[P_ID]": "<?php echo $this->_tpl_vars['projectData']['P_ID']; ?>
"},
			id: "issue-form",
			url: '../ajax/issue_addmod.php',
			bodyStyle: "padding: 5px",
			frame:false,
			fileUpload: true, 
			border: false, 
			anchor: '99%',
			labelAlign: "left",
			items: [
				taskFieldset,
				descField,
				priorityField,
				statusFieldset,
				assignsCombo,
				{xtype: 'panel', html: "<a href='javascript:void(0)' onClick='document.issueDialog.toggleFilesPanel()'><b><?php echo $this->_tpl_vars['itStrings']['ami_files_label']; ?>
 <span id='issue-files-count'></span></b></a>", border: false, cls: 'label-item'},
				filesWrapFieldset
				/*,
				filesFieldset*/
				//new Ext.form.FieldSet ({id:'files-fieldset', labelAlign: 'top', items: newFileField, height: 40, border: false, style:'padding: 0px; margin-top: 20px; margin-bottom: 10px'}),
				//new Ext.Panel({border: false, html: ""})
      ]
		});	
		
		form.taskDataLoaded = function() {
			var link = document.issueDialog.getAssignsManageLink();
			link.style.display = (document.issueDialog.canManageUsers) ? "inline" : "none";
		}
		
		filesFieldset.fileElems = new Array ();
		
		form.taskFieldset = taskFieldset;
		form.filesFieldset = filesFieldset;
		form.statusField = statusField;
		form.statusFieldset = statusFieldset;
		form.descField = descField;
		form.filesWrapFieldset = filesWrapFieldset;
		form.taskCombo = taskCombo;
		
		return form;
	}
	
	
	function issueEditDialog (pId, pwId, iId) {
		var dialog = getIssueDialog ();
		dialog.loadIssue (pId, pwId, iId);
		dialog.setMode ("edit");
		if (!dialog.alreadyLoaded) {
			dialog.showDialog ();		
			dialog.dialog.hide ();
		}
	}
	
	function issueAddDialog (P_PW) {
		var dialog = getIssueDialog ();
		dialog.setMode ("new");
		
		var PW_ID = null;
		if (document.projectId != null && P_PW == null) {
			if (document.projectId != 0 && document.projectId != "GBP" && document.getElementById("PW_ID") != null) {
				PW_ID = document.getElementById("PW_ID").value;
				if (PW_ID != "GBT")
					P_PW = document.projectId + "-" + PW_ID;
			} else if (document.projectId == 0) {
				P_PW = "0-0";			
			} else {
				P_PW = null;
			}
		}
		
		//dialog.selectedPwId = PW_ID;
		
		
		dialog.showDialog ();
		dialog.form.getForm().reset ();
		//dialog.form.getForm().baseParams = {P_ID: document.projectId, PW_ID: PW_ID};
		
		if (P_PW == null && dialog.selectedPwId)
			P_PW = dialog.selectedPwId;
			
			
		
		/*if (P_PW == null && PW_ID == "GBT")  {
			var firstWork = document.worksStore.data.get(0);
			if (firstWork) 
				P_PW = firstWork.get("P_PW");
		}*/
		
		if (P_PW != null) {
			var field = dialog.form.getForm().findField("COMBO_PW_ID");
			field.setValue (P_PW);
			field.fireEvent ("select", field, P_PW, null);
			field.setRawValue(clearHTML(field.getRawValue()));
		} else {
			dialog.canManageUsers = false;
			dialog.loadStatuses (new Array (), true);
			dialog.loadAssignments (new Array (), true);
			dialog.form.taskCombo.oldValue = null;
			dialog.form.taskDataLoaded();
		}
		return false;
	}
	
	function clearHTML(string) {
		return string.replace("&lt;", "<").replace("&gt;", ">");
	}
	
	function findAssignmentIndex(store, userId) {
		return findStoreIndex(store, userId, "id");		
	}
	
	function findStoreIndex (store, value, field) {
		for (var i = 0; i < store.getCount(); i++) {
			var obj = store.getAt(i);
			if (obj.get(field) == value)
				return i;
		}
		return -1;
	}
	
	function getAssignmentsHTML() {
		return "<div id='manage-task-name' style='margin-bottom: 15px'>&nbsp;</div><div id='manage-users-selector'></div>";
		/*var htmls = new Array ();
		for (var key in availableUsers) {
			var user = availableUsers[key];
			htmls.push("<input id='userCheckbox_" + key + "' type='checkbox' name='sendUsers" + user[0] + "'>" + user[1]);
		}
		return htmls.join ("<BR>");*/
	}
	
	
	function openTaskAssignmentsDialog () {
		/*var elem = document.createElement("div");
		elem.innerHTML = "Hello";
		elem.className = "assignmentDlgPanel";
		var link = document.issueDialog.getAssignsManageLink();
		link.parentNode.appendChild (elem);*/
		
		if (!document.taskAssignmentsDialog) {
			var dlg = new SimpleDialog ("issue-window");
			var config = {title: "<?php echo $this->_tpl_vars['itStrings']['il_assignments_label']; ?>
", width: 450, height: 280};
			config.items = [
				new Ext.Panel ({border: false, html: getAssignmentsHTML(), style: 'padding: 10px; height: 210px; overflow: auto'})
			];
			
			dlg.trySave = function () {
				var selectedIds = this.usersSelector.getToValue();
				
				var newAssignments = new Array ();
				if (document.issueDialog.assignments[0][0] == "")
					newAssignments.push(document.issueDialog.assignments[0]);
				for (var key in availableUsers) {
					 if (selectedIds.indexOf(key) != -1)
					 	newAssignments.push (availableUsers[key]);
				}
				
				var params = new Array ();
				params.P_ID = document.issueDialog.form.getForm().baseParams.P_ID;
				params.PW_ID = document.issueDialog.form.getForm().baseParams.PW_ID;
				params["assignments[]"] = selectedIds;
				
				AjaxLoader.doRequest ("../ajax/task_saveassignments.php", 
	    		function(response, options) {
	    			var result = Ext.decode(response.responseText);
	    			if (!result.success)
	    				AjaxLoader.ajaxServerError(result);
    				document.issueDialog.loadAssignments(result.assignments, true);
    				document.issueDialog.workData.assignments = selectedIds;
    				dlg.dialog.hide ();
	    		},
	    		params
	    	);
			}
			
			config.buttons = [
				{text: "<?php echo $this->_tpl_vars['kernelStrings']['app_save_btn']; ?>
", handler: function() {this.trySave()}, scope: dlg},
				{text: "<?php echo $this->_tpl_vars['kernelStrings']['app_cancel_btn']; ?>
", handler: function () {this.dialog.hide ()}, scope: dlg}
			];
			config.modal = true;
			dlg.loadConfig(config);
			document.taskAssignmentsDialog = dlg;
			
			dlg.onAfterShow = function () {
				if (!this.usersSelector || !this.usersSelector.initialized) {
	  			this.usersSelector = new UsersSelector ({renderTo: "manage-users-selector", toTitle: pmStrings.amr_assigned_title, fromTitle: pmStrings.amr_notassigned_title, selectSize: 8}) ;
		  		this.usersSelector.loadItems (availableUsers);
	  			this.usersSelector.initialize();
	  		}
			}
			
			dlg.setValue= function (data) {
				this.usersSelector.setToValue(data.join(","));
				/*if (data == null)
					data = new Array ();
				for (var key in availableUsers) {
					var obj = document.getElementById("userCheckbox_" + key);					
					var checked = false;
					for (var i = 0; i < data.length; i++) {
						var id = data[i][0];
						if (id == key)
							checked = true;
					}
					obj.checked = checked;
				}*/
			}
		}
		
		document.taskAssignmentsDialog.showDialog();
		var obj = document.getElementById("manage-task-name");
		var workData = document.issueDialog.workData;
		if (workData) {
			obj.innerHTML = 
				(workData.P_ID != 0) ? 
					"<?php echo $this->_tpl_vars['itStrings']['il_task_label']; ?>
" + workData.PW_ID + ": " + workData.PW_DESC : "<?php echo $this->_tpl_vars['itStrings']['il_issuesnotlinkedtoproject_label']; ?>
";
		}
		document.taskAssignmentsDialog.setValue(document.issueDialog.workData.assignments);
	}
</script>