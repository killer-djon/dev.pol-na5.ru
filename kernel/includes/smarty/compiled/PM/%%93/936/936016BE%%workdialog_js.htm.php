<?php /* Smarty version 2.6.26, created on 2014-08-08 13:46:31
         compiled from workdialog_js.htm */ ?>
<script src='../../../common/html/cssbased/pageelements/ajax/items_selector.js'></script>
<script src='../../../common/html/cssbased/pageelements/ajax/users_selector.js'></script>

<style>
	#task-desc-fieldset legend {display: none}	
	#work-window .x-form-item {margin-bottom: 0px !important}
	.pmform-date-field .x-form-element {margin-left: 102px}
	.pmform-date-field label {white-space: nowrap}
	
	.ext-ie .pmform-date-field .x-form-element {margin-left: 0px}
</style>

<script>
	function getWorkDialog () {
		if (document.workDialog)
			return document.workDialog;
		
		var workDialog = new SimpleDialog ("work-window");
			
		workDialog.config.width = 465;
		workDialog.config.height = 420;
		workDialog.config.modal = true;
		var form = getWorkForm ();
		workDialog.form = form;
		workDialog.config.items = [form];
		workDialog.config.title = pmStrings.pm_edittask_title;
		workDialog.saveFunction = function (closeOnSave) {
			if (this.form.getForm().findField("currency").value) {
      		Ext.state.Manager.set("defaultCurrency", this.form.getForm().findField("currency").value);
      	}
      	
      	this.closeOnSave = closeOnSave;
      	if (this.mode == "modify") {
      		this.form.getForm().baseParams.action = "edit";
      		this.form.getForm().baseParams.id = this.record.get("id");
      	} else {
      		this.form.getForm().baseParams.action = "new";
      	}
      	this.form.getForm().baseParams["assignedStr"] = this.usersSelector.getToValue ().join(",");
      	if (this.form.getForm().isValid() && this.form.validateDates())
      		this.form.getForm().submit();
    };
		
		workDialog.config.buttons = new Array ();
		workDialog.config.buttons.push({text: CommonStrings.app_save_btn, handler: function () {this.saveFunction(true)}, scope: workDialog, tabIndex: 7});
		if (!document.P_ID)
			workDialog.config.buttons.push({text: pmStrings.amr_addanother_btn, handler: function () {this.saveFunction(false)}, scope: workDialog});
		workDialog.config.buttons.push({text: CommonStrings.app_cancel_btn, handler: function() {this.dialog.hide()}, scope: workDialog, tabIndex: 8});
	  
	  workDialog.setMode = function (mode, record) {
	  	this.mode = mode;
	  	this.record = record;
	  }
	  
	  workDialog.displayRecordId = function (id) {
	  	Ext.getDom("work-dialog-record-id").innerHTML = id;
	  }
	  
	  workDialog.onAfterInit = function() {
	  	this.form.getForm().on("actioncomplete", function(form, action) {
	  		var result = Ext.decode(action.response.responseText);
	  		if (this.mode == "new") {
		  		var work = new Work ({id: result.PW_ID});
		  		work.id = result.PW_ID;
		  		form.updateRecord(work);
		  		if (document.worksGrid) {
			  		document.worksGrid.store.addSorted (work);
			  		document.worksGrid.store.totalLength++;
			  		document.worksGrid.getSelectionModel().selectRecords([work]);
			  	}
		  		//document.worksGrid.getView.focus ();
		  		var assigned = this.usersSelector.getToValue ();
		  		if (assigned && assigned.length > 0) {
		  			work.set("assigned", assigned.join(","));
		  		}
		  		
		  		if (document.worksGrid)
		  			document.worksGrid.getView().focusRow(document.worksGrid.store.indexOf(work));
		  	} else {
		  		form.updateRecord(this.record);
		  		var work = this.record;
		  		this.record.set("assigned", this.usersSelector.getToValue ().join(","));
		  	}
		  	
		  	if (document.worksGrid)
		  		document.worksGrid.getBottomToolbar().updateInfo();		  	
		  	//document.worksGrid.rowDateChanged (work.get("start"), null);
		  	
		  	if (document.worksGrid) {
			  	document.worksGrid.ganttColumn.recalcDates();
					document.worksGrid.ganttColumn.refreshView();
				} else {
					processAjaxButton ("refresh");
				}
		  	if (this.closeOnSave) {
			  	this.dialog.hide ();
			  } else {
			  	this.mode = "new";
			  	this.onBeforeShow ();
			  	this.record = null;
			  	this.form.getForm().reset();
					this.afterLoad ();
			  }
	  	}, this);
	  	
	  	this.form.getForm().on("actionfailed", function(form, action) {
	  		var result = Ext.decode(action.response.responseText);
				AjaxLoader.ajaxServerError(result);
	  	});
	  }
	  
	  workDialog.onBeforeShow = function () {
	  	if(this.mode == "new") {
	  		workDialog.dialog.setTitle (pmStrings.pm_addtask_title);
	  	} else {
	  		workDialog.dialog.setTitle (pmStrings.pm_edittask_title);
	  	}
	  	workDialog.form.endField.disable ();
	  }
	  
	  workDialog.usersSelector = new UsersSelector ({renderTo: "users-selector", toTitle: pmStrings.amr_assigned_title, fromTitle: pmStrings.amr_notassigned_title}) ;//new GenericItemsSelector("availableUsersSelect", "assignedUsersSelect", availableUsers, "usersSelectorLeftBtn", "usersSelectorRightBtn");
	  workDialog.usersSelector.loadItems (availableUsers);
	  workDialog.afterLoad = function () {
	  	if (!this.usersSelector.initialized)
	  		this.usersSelector.initialize();
	  	var assigned = (this.record) ? this.record.get("assigned") : null;
	  	this.usersSelector.setToValue(assigned);
	  	
	  	if (this.record)
	  		workDialog.displayRecordId ("ID: " + workDialog.record.id);
	  	else
	  		workDialog.displayRecordId ("new");
	  		
	  	workDialog.form.getForm().baseParams["fields[P_ID]"] = document.projectId;
	  	
	  	document.wd = this;
			window.setTimeout ("document.wd.form.getForm().findField('desc').getEl().focus()", 1000);
			
			if (this.record && this.record.get("currency")) {
				workDialog.form.getForm().findField("currency").setValue (this.record.get("currency"));
			} else {
				var defaultCurrency = Ext.state.Manager.get("defaultCurrency", currencies.getAt(0).get("id"));
				workDialog.form.getForm().findField("currency").setValue (defaultCurrency);
			}
			
			if (this.record && this.record.get("end")) {
				workDialog.form.getForm().findField("end").enable();
				workDialog.form.getForm().findField("isComplete").setValue(1);
			} else {
			workDialog.form.getForm().findField("isComplete").setValue(0);
				workDialog.form.getForm().findField("end").disable();
			}
			
			if (!this.record)
				workDialog.form.getForm().findField("start").setValue (new Date(globalToday));
	  }
	  
	  document.workDialog = workDialog;
	  return workDialog;
  }
  
  
  
  
  function getWorkForm () {
	
		var curCombo = new Ext.form.ComboBox({
        fieldLabel: "", oldLabel: pmStrings.amr_costcur_label,
        labelSeparator: "", 
        store: currencies,
        valueField: 'id',
        displayField:'id',
        typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        forceSelection: true,
        width: 60,
        id: 'currency',
        name: "fields[PW_COSTCUR]",
        tabIndex: 5, hideLabel: true
    });
    
    var assignsCombo = new Ext.Panel({
    	border: false,
    	width: '100%',
    	html: "<div id='users-selector'></div>",
    	tabIndex: 6
    });
    
    var labelStyle = Ext.isIE ? "width: 102px" : "";
    
    var descField = { xtype:'textarea', id:'desc', name: 'fields[PW_DESC]', fieldLabel:pmStrings.pm_summary_title + '<div id="work-dialog-record-id" style="position: absolute; top: 8px; right: 23px"></div>', allowBlank: false, height:50, anchor:'98%', tabIndex: 1};
    var today = new Date(); today = today.format(dateDisplayFormat);
    var startField = new Ext.form.DateField({ id: 'start', xtype:'datefield', fieldLabel: pmStrings.amr_startdate_label, name: 'fields[PW_STARTDATE]', format: dateDisplayFormat, tabIndex: 1, validationEvent: 'blur', itemCls: 'pmform-date-field', labelStyle: labelStyle, value: today});
    var dueField = new Ext.form.DateField({ id: 'due', xtype:'datefield', style:'padding-left: 2px', fieldLabel: pmStrings.amr_duedate_label, name: 'fields[PW_DUEDATE]', format: dateDisplayFormat , tabIndex: 2, validationEvent: 'blur', itemCls: 'pmform-date-field', labelStyle: labelStyle});
    var endField = new Ext.form.DateField({ id: 'end', xtype:'datefield', fieldLabel: pmStrings.amr_completedate_label, name: 'fields[PW_ENDDATE]', format: dateDisplayFormat, tabIndex: 3, validationEvent: 'blur', itemCls: 'pmform-date-field' , labelStyle: labelStyle});
    var isCompleteField = new Ext.form.Checkbox({xtype:'checkbox',id: 'isComplete', hideLabel: false, fieldLabel: pmStrings.amr_taskcomplete_label, name: 'fields[IS_COMPLETE]', value: 1, tabIndex: 5, labelStyle: 'width: 130px' });
    var billableField = { xtype:'checkbox', id: 'sbillable', /*itemCls: 'item-billable',*/ fieldLabel: pmStrings.amr_billable_label, name: 'fields[PW_BILLABLE]', hideLabel: false, value: 1, tabIndex: 5, labelStyle: 'width: 110px;'};
    var estimateField = { id: 'estimate', xtype:'numberfield', name: 'fields[PW_COSTESTIMATE]', hideLabel: true, width: 55, tabIndex: 4, labelStyle: 'width: ' + Math.ceil(pmStrings.amr_cost_label.length * 7.2) + "px"};
    
    startField.on("invalid", invalidDate);
    dueField.on("invalid", invalidDate);
    endField.on("invalid", invalidDate);
    
    var estimateLabelWidth = pmStrings.amr_cost_label.length > 4 ? .38 : .20;
    
    var estCurField = {layout:'column', border: false, items: [    
    	{layout: 'form', columnWidth: estimateLabelWidth, border: false, items: [{border: false, html: "<label class='x-form-item-label x-form-item'>" + pmStrings.amr_cost_label + ":</label> "}]},
    	{layout: 'form', columnWidth: .3, border: false, items: [estimateField]},
    	{layout: 'form', columnWidth: .32, border: false, items: [curCombo]}
    ]};
    
    var form = new Ext.FormPanel ({
			id: 'work-form',
			baseParams: {"fields[P_ID]": document.projectId},
			url: '../../../PM/html/ajax/project_addwork.php',
			border: false,
			bodyStyle: "padding: 5px",
			border: false,
			frame:false,
			anchor: '99%',
			labelWidth: 1,
			labelAlign: "left",
			items: [
				new Ext.form.FieldSet ({id:'task-desc-fieldset', labelAlign: 'top', items: descField, height: 80, 	autoHeight: false, border: false, style: 'padding: 0px; padding-top: 0px; padding-bottom: 0px'}),
				{layout:'column', border: false, height: 130, items: [
					{layout: 'form',columnWidth:.55, items: [startField, dueField, isCompleteField, endField], border: false}
					,
					{layout: 'form',columnWidth:.44, items: [billableField, estCurField], border: false}
					]
				},
				assignsCombo
      ]
		});
		
		form.endField = endField;
		isCompleteField.on("check", function (field, checked) {
			if (checked) {form.endField.enable(); if (form.endField.getRawValue() == null || form.endField.getRawValue() == "") form.endField.setValue(new Date(globalToday)); }else { form.endField.disable (); form.endField.setValue (null); }
		});
		
		
		form.validateDates = function () {
			var isComplete = this.getForm().findField("isComplete").getValue();
			
			var start = Date.parseDate(this.getForm().findField("start").getRawValue(), dateDisplayFormat);
			var due = Date.parseDate(this.getForm().findField("due").getRawValue(), dateDisplayFormat);
			var end = Date.parseDate(this.getForm().findField("end").getRawValue(), dateDisplayFormat);
			
			if (isComplete && !end) {
				alert(pmStrings.amt_taskcompleteempty_message);
				return false;
			}
			
			return CheckRecordDates (start, due, end);
		}
		
		return form;
	}
	
	function openWork (work) {
		if (isCompleteProject(true))
			return;
		var workDialog = getWorkDialog ();
		workDialog.setMode("modify", work);
		workDialog.showDialog ();	
		workDialog.form.getForm().loadRecord(work);
		workDialog.afterLoad ();
	};
	
	
 </script>