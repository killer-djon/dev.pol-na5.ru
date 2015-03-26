GanttGrid = function () {
	this.showCompleteTasks = true;
	
	this.rootPath = document.printMode ? "../html/" : "../";
	
	// shorthand alias
  var fm = Ext.form;

  // the column model has information about grid columns
  // dataIndex maps the column to the specific data field in
  // the data store (created below)
  var defaultSortable = GanttGrid.printMode ? false : true;
  
  this.ganttColumn = new Ext.grid.GanttColumn ({id: 'gantt', header: pmStrings.pm_gantt_title, dataIndex: 'estimate', sortable: false});
  
  var sm = new Ext.grid.CheckboxSelectionModel ();	
  
  var startDateField = new fm.DateField({format: dateDisplayFormat});
  startDateField.on("invalid", invalidDate);
  
  var canEdit = document.userRights > 1;
  
  
  var cm = new Ext.grid.ColumnModel([
  	sm,
  	{
  		header: "&nbsp;", dataIndex: 'dd', width: 30, id: 'CONTROL', resizable: false, sortable: false, fixed: true, hideable: false, renderer: function (v,p,record) {if (!canEdit) return "";  return "<div onClick='document.worksGrid.openWorkWindow(" + record.get("id") + ")' class='menu-btn'>&nbsp;</div>"}
		},{
			id: 'id', header: pmStrings.pm_id_title, dataIndex: 'id', width: 50, sortable: defaultSortable, resizable: false
		},{
       id:'desc', header: pmStrings.pm_description_title, hideable: false, dataIndex: 'desc', minWidth: 100, width: 250, sortable: defaultSortable, renderer: Ext.util.Format.htmlEncode
       	,editor: new fm.TextArea({allowBlank: false, grow: true})
    },{
       id: 'start', header: pmStrings.pm_start_title, dataIndex: 'start', width: 90, renderer: this.formatDate, resizable: true, sortable: defaultSortable, fixed: false, resizable: false, 
       editor: startDateField
    },{
       id: 'due', header: pmStrings.pm_due_title, dataIndex: 'due', width: 90, renderer: this.formatDate, resizable: true, sortable: defaultSortable, fixed: false, resizable: false,
       editor: new fm.DateField({format: dateDisplayFormat})
    },{
       id: 'complete', header: pmStrings.pm_complete_title, dataIndex: 'end', width: 90, renderer: this.formatDate, hidden: false, resizable: true, sortable: defaultSortable, fixed: false, resizable: false, 
       editor: new fm.DateField({format: dateDisplayFormat})
    },{
       id: 'estimate', header: pmStrings.pm_cost_title,
       dataIndex: 'estimate', width: 70, align: 'right', renderer: 'money', resizable: true, hidden: true, sortable: defaultSortable,
       renderer: function (v, params, record) {if (v) return "<div style='width: 100%; text-overflow: ellipsis; white-space: nowrap; overflow: hidden'>" + v.toFixed(2) + " " + record.get("currency") + "</div>"; else return "";},
       editor: new fm.NumberField({allowBlank: true, allowNegative: true})
    },{
    	id: 'status', header: pmStrings.pm_status_title, dataIndex: 'status', width: 80, resizable: false, sortable: false, hideable: true, 
    	renderer: function (v,p, record) { 
    		var status = Work.getStatus(record);
    		return "<span class='status " + status + "'>" + pmStrings["pm_status_" + status] + "</span>";
    	}
    },{
    	id: 'assigned', header: pmStrings.pm_assignments_title, sortable: defaultSortable,
    	dataIndex: "assigned",
    	resizable: false, 
    	width: 50, minWidth: 50, align: "center",
    	renderer: function (v, params, record) {
    		var users = new Array ();
    		var usersStr = "";
    		if (v) {
	    		var s = v.split(",");
	    		for (i = 0; i < s.length; i++) {
	    			if (availableUsers[s[i]])
	    				users.push(availableUsers[s[i]][1]);
	    		}
    		}
    		users.sort ();
    		if(users.length > 0) {
    			usersStr = "<li>";
    			usersStr += users.join("<li>");
    		}
    		return "<a href='javascript:void(0)' onClick='ShowAssignmentsPanel(this, " + users.length + ",\"" + usersStr +" \", " + record.get("id")  +", " + canEdit + " )'>" + (users.length > 0 ? users.length : "&lt;" + pmStrings.pm_noassignmentsshort_label + "&gt;") + "</a>";
    	}    	
    }, 
    this.ganttColumn
  ]);
  
	
	// by default columns are sortable
  cm.defaultSortable = true;

  // create the Data Store
  var url = this.rootPath + 'ajax/project_works.php';
  var store = new Ext.data.Store({
      url: url,
      reader: new Ext.data.JsonReader({id: 'PW_ID', record: 'work', root: 'works', totalProperty: 'totalCount'}, Work),
      sortInfo: {field:'start', direction:'ASC'},
      remoteSort: true,
      baseParams: {projectId: document.getElementById("project").value, showComplete: document.showComplete}
  });
  
  this.showHideCompleteTasks = function (hideCompleteTasks) {
  	this.store.baseParams.showComplete = hideCompleteTasks ? 0 : 1;
  	this.store.load ();
  }
  
  
  // Call constructor
  var border = GanttGrid.printMode ? true :false;
  var autoHeight = GanttGrid.printMode ? true :false;
  GanttGrid.superclass.constructor.call (this, {
		id: 'works-grid',
		stateId: 'v2-works-grid',
		store: store,
    cm: cm,
    layout: "fit",
    autoExpandColumn:'gantt', 
    renderTo: "works-grid-parent",
    border: border,
    trackMouseOver: true, 
    layout: "fit",
    autoHeight: autoHeight,
    width: GetDocumentWidth(),
    height: 80,
    loadMask: {msg:pmStrings.pm_loading_label+'...', msgCls: 'x-mask-loading works-mask-loading'},
    maskDisabled: false, 
    frame:false,
    sm: sm,
    clicksToEdit:"auto",
    plugins:[this.ganttColumn], 
    view: new GanttGridView ({emptyText: pmStrings.pm_notasks_title }),
  	bbar: new Ext.PagingToolbar({
          pageSize: document.worksOnPage,
          height: 30,
          store: store,
          displayInfo: true,
          cls: 'works-paging-toolbar',
          displayMsg: pmStrings.pm_tasks_label + ": {0}-{1} " + pmStrings.pm_of_label + " {2}",
          emptyMsg: pmStrings.pm_tasks_label + ": 0 " + pmStrings.pm_of_label + " 0"
      })
	});
	
	document.worksGrid = this;
  
  store.on("load", function() {
  	this.ganttColumn.projectMinDate = (this.store.reader.jsonData.minDate) ? Date.parseDate(this.store.reader.jsonData.minDate, dateDisplayFormat) : null;
  	this.ganttColumn.projectMaxDate = (this.store.reader.jsonData.maxDate) ? Date.parseDate(this.store.reader.jsonData.maxDate, dateDisplayFormat) : null;
  	this.ganttColumn.recalcDates ();
  	this.updateGantt ();
  }, this);
  
  // trigger the data store load
  store.load();
  
  this.on("afteredit", function (e) {
  	var start = e.record.get("start");
  	var due = e.record.get("due");
  	var end = e.record.get("end");
  	
  	if (!CheckRecordDates(start, due, end)) {
  		e.record.set(e.field, e.originalValue);
  		this.ganttColumn.updateTodayLine ();
  		return;
  	}
		
		this.rowDateChanged(e.value, e.originalValue);
		
		this.updateRowData(e.record, e.field, e.value, e.originalValue);
	}, this);
	
  
  Event.observe(window, 'resize', window.OnResizeHandler);
  UpdateMainGrid ();
}

//Initialise the variables for the resize timer.
var plngResizeTimerID = 0;
var plngResizeTimeout = 0.5; //Seconds

window.OnResizeHandler = function () {
	if (Ext.isIE) {
		if (plngResizeTimerID > 0) {
			//Check this function please - I'm not 100% sure of it's actual
			window.clearTimeout(plngResizeTimerID);
		}
		//Set the timeout handler to fire after the resizing has completed.
		plngResizeTimerID = window.setTimeout('window_resized();', plngResizeTimeout * 1000);
	} else {
		window_resized ();
	}
}

//This function gets fired x seconds after the resizing has completed.
function window_resized(){
	plngResizeTimerID = 0;
	//Do something.
	UpdateMainGrid ();
}


GanttGrid.printMode = false;



/**************
* GanttGrid methods implementation
**************/
var gridClass = (document.printMode || document.userRights <= 1 || document.projectIsComplete) ? Ext.grid.GridPanel : Ext.grid.EditorGridPanel;
Ext.extend (GanttGrid, gridClass, {
	
	setMyWidth: function (x) {
		//this.el.setWidth (x);
		this.mySize =x;
	},
	
	getMyWidth: function() {
		return this.mySize;
	},
	
	/**************
	* Format date
	********************/
	openWorkWindow: function (recordId) {
		var record = this.store.getById(recordId);
	  openWork(record);	        	
	},
	
	
	/**************
	* Format date
	********************/
	formatDate: function(value){
      return value ? value.dateFormat('M d, Y') : '';
  },
  	
  	
  /**************
	* Fires when any row date changed
	********************/
	rowDateChanged: function (value, oldValue) {
		this.ganttColumn.recordDateChanged (value, oldValue);	
	},
		
		
	updateGantt: function  () {
		this.ganttColumn.refreshColumn ();
		this.ganttColumn.refreshView ();
	},
	
	
	updateRowData: function (record, field,value, oldValue) {
		var sendValue = (value && value.dateFormat != null) ? (value.dateFormat(dateDisplayFormat)) : value;
		var mapping = {start: "PW_STARTDATE", due: "PW_DUEDATE", end: "PW_ENDDATE", estimate: "PW_COSTESTIMATE", desc: "PW_DESC"};
		var sendField = mapping[field];
		if (!sendField)
			return;
		
		var defaultCurrency = Ext.state.Manager.get("defaultCurrency", currencies.getAt(0).get("id"));
		var params = {projectId: document.getElementById("project").value, id: record.get("id"), value: sendValue, field: sendField, asgn: record.get("assigned")};
		//if (field == "estimate")
		params.defaultCur = defaultCurrency;
			
		AjaxLoader.doRequest ("../ajax/project_modwork.php", 
			function(response, options) {
				var result = Ext.decode(response.responseText);
				if(result.success) {
					if (result.setCur)
						record.set("currency", result.setCur);
				} else {
					record.set(field, oldValue);
					if (field == "start" || field == "end" || field == "due")
						this.rowDateChanged(oldValue, value);
					AjaxLoader.ajaxServerError(result);
				}
			}
			,
			params, {scope: this}
		);
	},
		
	deleteRecord: function (record) {
		deleteWorks ();
	}
});