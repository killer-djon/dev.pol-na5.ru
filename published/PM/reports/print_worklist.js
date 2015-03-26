Ext.onReady (function () {
	Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
	
	var pg = new PrintGrid ();
});


var PrintGrid = function() {
	this.rootPath = "../html/";
	
	this.managerData = (Ext.state.Manager.get("v2-works-grid")) ? 
		Ext.state.Manager.get("v2-works-grid") : 
		{sort: {field: "start", direction: "ASC"}, columns: [{id: 'id', width: 10}, {id: 'desc', width: 400}, {id: 'start', width: 60}, {id: 'due', width: 70}, {id: 'complete', width: 60}, {id: 'status', width: 70}, {id: 'assigned', width: 70}, {id: 'gantt', width: 300}]};
	
	this.store = new Ext.data.Store({
      url: "../html/ajax/project_works.php",
      reader: new Ext.data.JsonReader({id: 'PW_ID', record: 'work', root: 'works', totalProperty: 'totalCount'}, Work),
      sortInfo: this.managerData.sort, remoteSort: true, 
      baseParams: {projectId: document.getElementById("project").value, showComplete: document.showComplete, noPaging: true}
  });
  
  this.formatDate = dateDisplayFormat;
  
  this.ganttColumn = new PrintGanttColumn ({id: 'gantt', header: "-", dataIndex: 'estimate', sortable: false});
  
  var dateRenderer = function (v) {return Ext.util.Format.date(v, this.formatDate)};
  var ganttRenderer = function (v, params, record, rowNumber) {
  	var left = (parseInt(rowNumber) * 15);
  	return "<div style=''><img height=15 style='position: relative; background: blue; left: " + left + "%; width: 4%;' src='../../common/html/res/images/blue.gif'></div>";
  }
  this.ganttColumn.grid = this;
  
  var allColumns = {
  	id: {id: 'id', header: pmStrings.pm_id_title, dataIndex: 'id', width: 40},
  	desc: {id:'desc', header: pmStrings.pm_description_title, dataIndex: 'desc', width: 220, renderer: Ext.util.Format.htmlEncode},
  	start: {id: 'start', header: pmStrings.pm_start_title, dataIndex: 'start', width: 90, formatDate: this.formatDate, renderer: dateRenderer},
    due: {id: 'due', header: pmStrings.pm_due_title, dataIndex: 'due', width: 90, formatDate: this.formatDate, renderer: dateRenderer},
    complete: {id: 'complete', header: pmStrings.pm_complete_title, dataIndex: 'end', width: 90, formatDate: this.formatDate, renderer: dateRenderer},
    status: {
    	id: 'status', header: pmStrings.pm_status_title, dataIndex: 'status', width: 80,
    	renderer: function (v,p, record) { 
    		var status = Work.getStatus(record);
    		return "<span class='status " + status + "'>" + pmStrings["pm_status_" + status] + "</span>";
    	}
    },
    estimate: {id: 'estimate', header: pmStrings.pm_cost_title, dataIndex: 'estimate', width: 70, align: 'right', renderer: 'money',
       renderer: function (v, params, record) {if (v) return v.toFixed(2) + " " + record.get("currency"); else return "";}},
    assigned: {
    	id: "assigned",
    	header: pmStrings.pm_assignments_title, 
    	dataIndex: "assigned",
    	align: "center",
    	width: 50,
    	renderer: function (v) {
    		if (!v)
    			return "&lt;" + pmStrings.pm_noassignmentsshort_label + "&gt";
    		else
    			return v.split(",").length;
    	}
    }, gantt: this.ganttColumn
  };
  
  var totalWidth = 0;
  for (var i = 0; i < this.managerData.columns.length; i++) {
  	var column = this.managerData.columns[i];
  	if (!column.hidden && allColumns[column.id] && column.width)
  		totalWidth += column.width;
  }
  
	var columnsArray = new Array ();
	for (var i = 0; i < this.managerData.columns.length; i++) {
		var column = this.managerData.columns[i];
		if (!column.hidden && allColumns[column.id]) {
			allColumns[column.id].width = Math.round((column.width / totalWidth) * 100) + "%"; 
			columnsArray.push (allColumns[column.id]);
		}
		
		//this.cm.setHidden (printColumnIndex, column.hidden ? true : false);
	}
	
	this.cm = new Ext.grid.ColumnModel(columnsArray);
  
  this.store.load ();
  
  printTableBuilder = new PrintTableBuilder (this, {renderTo: 'works-grid-div'});
  this.store.on("load", printTableBuilder.build, printTableBuilder);
  
  document.worksGrid = this;
}

PrintGrid.prototype = {
	getColumnModel: function() {
		return this.cm;
	}
}



PrintTableBuilder = function (grid, config) {
	this.store = grid.store;
	this.config = config;
	this.grid = grid;
	
	this.cm = grid.cm;
}

PrintTableBuilder.prototype = {
	
	build: function (store) {
		//this.loadParams ();
		this.grid.ganttColumn.recalcDates ();
		Ext.getDom(this.config.renderTo).innerHTML = this.buildHTML ();
		this.grid.ganttColumn.updateHeader ();
	},
		
	buildHTML: function () {
		var res = "";
		res += "<table id='works-grid'>";
		
		res += this.getHeaderHTML ();
		res += this.getBodyHTML ();
		
		res += "</table>";
		return res;
	},
		
	getHeaderHTML: function () {
		var res = "";
		
		res += "<thead>";
		
		var cols = this.cm.getColumnsBy(function (columnConfig) {return true});
		for (var i = 0; i < cols.length; i++) {
			var col = cols[i];
			if (col.hidden)
				continue;
			
			var tdattributes = new Array ();
			if (col.width)
				tdattributes.push("width='" + col.width + "'"		);
			if (col.align)
				tdattributes.push("align='" + col.align + "'"		);
			tdattributes.push("id='" + "x-grid3-td-value-" + col.id + "'");
			
			res += "<td " + tdattributes.join (" ") + ">" + col.header + "</td>";
		}
		
		
		res += "</thead>";		
		
		return res;
	},
	
	
	getBodyHTML: function () {
		var res = "";
		
		res += "<tbody>";
		for (var i = 0; i < this.store.data.length; i++) {
			res += this.getRecordHTML (this.store.getAt(i), i	);
		}		
		res += "</tbody>";		
		
		return res;
	},
		
	getRecordHTML: function (record, rowNum) {
		var res = "";
		res += "<tr>";
		
		var cols = this.cm.getColumnsBy(function (columnConfig) {return true});
		for (var i = 0; i < cols.length; i++) {
			var params = new Array ();
			var col = cols[i];
			if (col.hidden)
				continue;
			var value = record.get(col.dataIndex);
			var renderedValue = value;
			if (col.renderer)
				renderedValue = col.renderer(value, params, record, rowNum);
			
			var tdattributes = new Array ();
			if (col.width)
				tdattributes.push("width='" + col.width + "'"		);
			
			var cssClass = "td-" + col.id;
			if (params.css)
				cssClass += (" " +  params.css);
			tdattributes.push ("class='" + cssClass + "'");
			
			if (params.style)
				tdattributes.push ("style='" +  params.style + "'");
			
			res += "<td " + tdattributes.join (" ") + ">" + renderedValue + "</td>";
		}
		
		res += "</tr>";
		return res;
	}
};










var PrintGanttColumn = function (config) {
	PrintGanttColumn.superclass.constructor.call (this, config);
}

Ext.extend (PrintGanttColumn, Ext.grid.GanttColumn, {
	getLegendHTML: function (innerHTML, style1, style2) {
		var ganttLegendHtml = "<div style='" + style1 + "'><div style='width: 100%; float: left; padding: 0px; margin: 0px; " + style2 + "'>";
		ganttLegendHtml += innerHTML;
		ganttLegendHtml += "</div></div>";
		return ganttLegendHtml;
	}
});