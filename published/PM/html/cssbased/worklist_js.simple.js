GanttGrid = function () {
		var defaultSortable = false;
		
		var fm = Ext.form;
		
		var cm = new Ext.grid.ColumnModel([{
		  		header: "&nbsp;", dataIndex: 'dd', width: 30, id: 'CONTROL', resizable: false, sortable: false, fixed: true, hideable: false, renderer: function (v,p,record) {if (document.userRights <= 1) return "";  return "<div onClick='document.worksGrid.openWorkWindow(" + record.get("id") + ")' class='menu-btn'>&nbsp;</div>"}
				},{
					id: 'id', header: "Id", dataIndex: 'id', width: 40, sortable: defaultSortable, resizable: false
				},{
		       id:'desc', header: "Description", hideable: false, dataIndex: 'desc', minWidth: 100, sortable: defaultSortable
		       	,editor: new fm.TextArea({allowBlank: false, grow: true})
		    },{
		       id: 'start', header: "Start", dataIndex: 'start', width: 90, renderer: this.formatDate, resizable: true, sortable: defaultSortable, fixed: false, resizable: false, 
		       editor: new fm.DateField({format: dateDisplayFormat, minValue: '01/01/00'})
		    },{
		       id: 'due', header: "Due", dataIndex: 'due', width: 90, renderer: this.formatDate, resizable: true, sortable: defaultSortable, fixed: false, resizable: false,
		       editor: new fm.DateField({format: dateDisplayFormat, minValue: '01/01/00'})
		    }]);
		    
		    
	var url = '../ajax/project_works.php';
  var store = new Ext.data.Store({
      url: url,
      reader: new Ext.data.JsonReader({id: 'PW_ID', record: 'work'}, Work),
      sortInfo: {field:'start', direction:'ASC'},
      baseParams: {projectId: document.getElementById("project").value, showComplete: 1}
  });
  
  GanttGrid.superclass.constructor.call (this, {
		id: 'my-works-grid',
		store: store,
    cm: cm,
    layout: "fit",
    height: 400,
    renderTo: "works-grid",
    trackMouseOver: true, 
    layout: "fit",
    loadMask: {msg:'Loading Works...'},
    frame:false,
    sm: new Ext.grid.CheckboxSelectionModel({}),
    clicksToEdit:"auto"
	});
		
	store.load ();
};

Ext.extend (GanttGrid, Ext.grid.GridPanel, {
});