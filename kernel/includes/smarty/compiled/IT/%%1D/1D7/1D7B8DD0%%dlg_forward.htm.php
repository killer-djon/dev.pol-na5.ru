<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:51
         compiled from dlg_forward.htm */ ?>
<style>
	#forward-form legend {display: none}
</style>

<script>

	function getForwardDialog () {
		if (document.forwardDialog)
			return document.forwardDialog;
			
		var config = {width: 480, height: 260, title: "<?php echo $this->_tpl_vars['itStrings']['il_dlgforward_title']; ?>
", modal: true};
		
		var forwardDialog = new SimpleDialog ("forward-window");
		
		var form = getForwardForm ();
		//issueDialog.usersSelector = new UsersSelector ({renderTo: "users-selector", toTitle: "Assigned", fromTitle: "Not Assigned"}) ;//new GenericItemsSelector("availableUsersSelect", "assignedUsersSelect", availableUsers, "usersSelectorLeftBtn", "usersSelectorRightBtn");		
		forwardDialog.form = form;		
		
		config.items = [
			form
		];
		config.buttons = [
			{text: "<?php echo $this->_tpl_vars['itStrings']['is_forward_btn']; ?>
", handler: function () {this.trySave ()}, scope: forwardDialog},
			{text: "<?php echo $this->_tpl_vars['kernelStrings']['app_cancel_btn']; ?>
", handler: function () {this.dialog.hide ()}, scope: forwardDialog}
		];
		
		forwardDialog.loadConfig(config);
		forwardDialog.loadState = function (P_ID, PW_ID, issues) {
			this.form.getForm().reset ();
			this.form.getForm().baseParams = {P_ID: P_ID, PW_ID: PW_ID, "issues[]": issues, viewP_ID: document.P_ID };
			this.form.getForm().load({url:'../ajax/issues_state.php'});
		}
		
		forwardDialog.trySave = function () {
			//alert (this.form.descField.getRawValue());
			if (this.form.descField.getRawValue() == "")
				this.form.descField.getEl().dom.value = "";
			this.form.getForm().submit ();
		}
		
		
		forwardDialog.loadAssignments = function(data, resetValue) {
			var field = forwardDialog.form.getForm().findField("U_ID_TOASSIGN");
			field.store.loadData(data);
			if (resetValue)
				field.setValue ("");
		}
		
		forwardDialog.loadTransitions = function (data, resetValue) {
			var field = forwardDialog.form.getForm().findField("ITL_STATUS");
			field.store.loadData(data);
			if (resetValue)
				field.setValue ("");
		}
		
		form.on("actioncomplete", function (frm, action) {
			if (action.type == "load") {
				forwardDialog.showDialog ();
				
				var field = frm.findField("U_ID_TOASSIGN");
				forwardDialog.loadAssignments (action.result.assignments);
				if (!action.result.data.U_ID_ASSIGNED)
				field.setValue ("");
				else
					field.setValue (action.result.data.U_ID_ASSIGNED);
				
				var field = frm.findField("ITL_STATUS");
				forwardDialog.loadTransitions (action.result.transitions);
				field.setValue (action.result.data.ITL_STATUS);
				
			} else {
				var res = action.result;
				for (var i = 0; i < res.issues.length; i++) {
					var I_ID = res.issues[i];
					if (res.issuesHTML[I_ID] == null || res.issuesHTML[I_ID] == "")
						moveOffsetChange(-1);
					updateIssueBlock(res.P_ID, res.PW_ID, I_ID, res.issuesHTML[I_ID]);
				}
				//refreshList ();
				forwardDialog.dialog.hide ();			
			}
		});
		
		form.on("actionfailed", function(form, action) {
			AjaxLoader.ajaxServerError(action.result);
		});
		
		
		forwardDialog.onAfterShow = function () {
			this.alreadyLoaded = true;
			//if (!this.usersSelector.initialized)
	  		//this.usersSelector.initialize();
	  }
	  
	  document.forwardDialog = forwardDialog;
		return forwardDialog;
	}
	
	function getForwardForm () {
	
		var statusStore = new Ext.data.SimpleStore({fields: ['value']});
		
		var usersStore = new Ext.data.SimpleStore ({fields: ['id', 'name']});
		
		//var usersSelector = new UsersSelector("availableUsersSelect", "assignedUsersSelect", availableUsers, "usersSelectorLeftBtn", "usersSelectorRightBtn");
		
		var descField = new Ext.form.TextArea({ xtype:'textarea', id:'ITL_DESC', name: 'fields[ITL_DESC]', /*fieldLabel: 'Comments to this Transition' + '<div id="issue-dialog-record-id" style="position: absolute; top: 8px; right: 23px"></div>', */ hideLabel: true, emptyText: '<?php echo $this->_tpl_vars['itStrings']['il_addcoment_text']; ?>
', allowBlank: true, height:80, anchor:'98%', tabIndex: 1});
		
		var statusField = new Ext.form.ComboBox({
        id: 'ITL_STATUS', fieldLabel: "<?php echo $this->_tpl_vars['itStrings']['fwi_sendtostatus_label']; ?>
",  hiddenName: "fields[ITL_STATUS]",
        store: statusStore, valueField: 'value', displayField:'value',
        typeAhead: true, mode: 'local', triggerAction: 'all', selectOnFocus:true, forceSelection: true,
        width: 150
    });
    
    var assignsCombo = new Ext.form.ComboBox ({
    	id: 'U_ID_TOASSIGN', hiddenName: 'fields[U_ID_ASSIGNED]', fieldLabel: '<?php echo $this->_tpl_vars['itStrings']['ami_assignto_label']; ?>
',
    	store: usersStore, valueField: 'id', displayField: 'name',
    	typeAhead: true, mode: 'local', triggerAction: 'all', selectOnFocus:true, forceSelection: true,width: 265, hideTrigger:false
    });
    
    statusField.on("change", function(field, newValue) {
    	var form = document.forwardDialog.form.getForm();
    	form.baseParams.STATUS = newValue;
    	form.load({url:'../ajax/issues_state.php'});
    });
    
    
    var form = new Ext.FormPanel ({
			//baseParams: {"fields[P_ID]": "<?php echo $this->_tpl_vars['projectData']['P_ID']; ?>
"},
			id: 'forward-form',
			url: '../ajax/issues_send.php',
			border: false,
			bodyStyle: "padding: 5px",
			border: false,
			frame:false,
			fileUpload: false, 
			anchor: '99%',
			labelAlign: "left",
			items: [
				//{layout:'column', border: false, items: [{layout:'form', border: false, columnWidth: .4, items: [statusField]}, {layout:'form', border: false, columnWidth: .6, items: [assignsCombo]}]},
				statusField, assignsCombo,
				new Ext.form.FieldSet ({id:'desc-fieldset', labelAlign: 'top', items: descField, height: 110, 	autoHeight: false, border: false, style: 'padding: 0px; padding-top: 0px; padding-bottom: 0px'})
      ]
		});	
		
		form.descField = descField;
		
		return form;
	}
	
	
	function issueForwardDialog (pId, pwId, issues, issuesNums) {
		var dialog = getForwardDialog ();
		dialog.loadState (pId, pwId, issues);
		if (!dialog.alreadyLoaded) {
			dialog.showDialog ();		
			dialog.dialog.hide ();
		} 
		
		if (issuesNums.length > 1)
			dialog.dialog.setTitle ("<?php echo $this->_tpl_vars['itStrings']['il_dlgforwardmany_title']; ?>
 (" + issuesNums.length + ")");
		else
			dialog.dialog.setTitle ("<?php echo $this->_tpl_vars['itStrings']['il_dlgforward_title']; ?>
 " + issuesNums[0]);
		
		return false;
	}
	
	function issuesForwardSelected () {
		var paramsArray = confirmSend();
		if (paramsArray === false)
			return false;
		var pId = paramsArray[0];
		var pwId = paramsArray[1];
		if (pwId === false)
			return false;
			
		var issues = new Array ();
		var issuesNums = new Array ();
		for ( i = 0; i < thisForm.elements.length; i++ ) {
			if (thisForm.elements[i].type == 'checkbox') {
				if (thisForm.elements[i].checked) {
					el = thisForm.elements[i];

					if ( el.name.substr( 0, 8 ) != 'document' )
						continue;

					len = el.name.length;

					I_ID = el.name.substr( 9, len-10 );
					issues.push (I_ID);
					issuesNums.push (el.getAttribute("issueDisplayNum"));
				}
			}
		}
		return issueForwardDialog (pId, pwId, issues, issuesNums);
	}
	
</script>