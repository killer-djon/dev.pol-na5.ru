var daySeconds = 86000;
var dday = daySeconds * 1000;

AssignmentsGrid = function () {
	this.rootPath = document.printMode ? "../html/" : "../";
	
	Ext.override(Ext.grid.GroupingView, {interceptMouse : Ext.emptyFn, emptyText: pmStrings.pm_notasks_title});
	
	// shorthand alias
  var fm = Ext.form;

  this.ganttColumn = new Ext.grid.GanttColumn ({id: 'gantt', header: "Gantt", dataIndex: 'estimate', sortable: false, width: 400});
  
  var summary = new Ext.grid.GroupSummary ();
  
  var defaultSortable = false;
  var cm = new Ext.grid.ColumnModel([
  	{
  		header: "Id", dataIndex: "id", width: 70, sortable: defaultSortable, resiable: false
  	},
  	{
    	header: "User", sortable: defaultSortable,
    	dataIndex: "assigned",
    	hidden: true,
    	width: 100, 
  		renderer: function (v) {
    		if (!v || !availableUsers[v])
    			return pmStrings.pm_noassignments_label;
    		return availableUsers[v][1];
    	}
    },						
		{
       id:'desc', header: pmStrings.amt_assignmentstasks_caption, dataIndex: 'desc', width: 150, sortable: defaultSortable
       	,editor: new fm.TextArea({allowBlank: false, autoHeight: true}),
	      summaryType: "count",
	  		summaryRenderer: function(v, params, data){
	          return ((v === 0 || v > 1) ? '(' + v +' Tasks)' : '(1 Task)');
	      }
    }, 
    this.ganttColumn
  ]);
  
	
	// by default columns are sortable
  cm.defaultSortable = true;

  // create the Data Store
  var url = this.rootPath + 'ajax/project_works_assignment.php';
  var store = new Ext.data.GroupingStore({
      url: url,
      reader: new Ext.data.JsonReader({record: 'work'}, Work),
      baseParams: {projectId: document.getElementById("project").value, showComplete: document.showComplete},
      sortInfo: {field:'assigned', direction:'ASC'},
      groupField: 'assigned'
  });
  		
 	this.showHideCompleteTasks = function (hideCompleteTasks) {
  	this.store.baseParams.showComplete = hideCompleteTasks ? 0 : 1;
  	this.store.load ();
  }
  
  
  assignmentView = new Ext.grid.GroupingView({
      forceFit:true, showGroupName: false
      //,groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
  });
  
  assignmentView.renderHeaders = function(){
	  this.templates.hcell = new Ext.Template(
    '<td class="x-grid3-hd x-grid3-cell x-grid3-td-{id}" style="{style}"><div {attr} class="x-grid3-hd-inner x-grid3-hd-{id}" unselectable="on" style="{istyle}">', this.grid.enableHdMenu ? '<a class="x-grid3-hd-btn" href="#"></a>' : '',
    '<span id="x-grid3-td-value-{id}">{value}</span><img class="x-grid3-sort-icon" src="', Ext.BLANK_IMAGE_URL, '" />',
    "</div></td>"
    );
      
    var cm = this.cm, ts = this.templates;
    var ct = ts.hcell;

    var cb = [], sb = [], p = {};

    for(var i = 0, len = cm.getColumnCount(); i < len; i++){
        p.id = cm.getColumnId(i);
        p.value = cm.getColumnHeader(i) || "";
        p.style = this.getColumnStyle(i, true);
        if(cm.config[i].align == 'right'){
            p.istyle = 'padding-right:16px';
        }
        cb[cb.length] = ct.apply(p);
    }
    return ts.header.apply({cells: cb.join(""), tstyle:'width:'+this.getTotalWidth()+';'});
	}

  
  // Call constructor
  var border = document.printMode ? true :false;
  var autoHeight = document.printMode ? true :false;
  AssignmentsGrid.superclass.constructor.call (this, {
		id: 'assgn-works-grid',
		store: store,
    cm: cm,
    layout: "fit",
    autoExpandColumn:'gantt', 
    renderTo: "works-grid",
    border: border,
    layout: "fit",
    autoHeight: autoHeight,
    width:  GetDocumentWidth(),
    height: 80,
    loadMask: {msg:pmStrings.pm_loading_label},
    frame:false,
    sm: new Ext.grid.RowSelectionModel({singleSelect: true }),
    clicksToEdit:2,
    plugins:[this.ganttColumn], 
    view: assignmentView
	});
	
	document.worksGrid = this;
	
  store.on("load", function() {
  	this.ganttColumn.recalcDates ();
  	this.updateGantt ();
  }, this);
  
  // trigger the data store load
  store.load();
  
  Event.observe(window, 'resize', UpdateMainGrid);
  UpdateMainGrid ();
}


document.printMode = false;



/**************
* GanttGrid methods implementation
**************/
var gridClass = Ext.grid.GridPanel;
Ext.extend (AssignmentsGrid, gridClass, {
	
	/**************
	* Format date
	********************/
	formatDate: function(value){
      return value ? value.dateFormat('M d, Y') : '';
  },
  	
	updateGantt: function  () {
		this.ganttColumn.refreshView ();
	},
		
	setMyWidth: function (x) {
		this.mySize =x;
		this.setWidth(x);
	},
	
	getMyWidth: function() {
		return this.mySize;
	}
});