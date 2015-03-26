var Bar = function (column, config) {
	if (!config)
		config = {};
	
	this.config = config;
	this.column = column;
	this.init ();
}

Bar.prototype = {
	config: null,
	column: null,	
	
	init: function  () {
		this.days = "&nbsp;"
		this.addword = "";	
	},
	getHTML: function (posData) {
		
	},
	getText: function () {
		return "";
	},
		
	buildText: function (template, dateFrom, dateTo) {
		return this.column.buildText(template, dateFrom, dateTo);
	}
}

var ImageBar = function (column, type, date, config) {
	this.date = date;
	this.type = type;
	ImageBar.superclass.constructor.call (this, column, config);
}

ImageBar.BAR_START = "start";
ImageBar.BAR_DUE = "due";
ImageBar.BAR_END = "end";

Ext.extend (ImageBar, Bar, {
	offset: 0,
	type: null,
	
	init: function() {
		ImageBar.superclass.init.call (this);
		
		this.date.setHours(12); 
		var grid = this.column.grid;
		
		var offset = 0;
		var img = "";
		switch (this.type) {
			case ImageBar.BAR_START:
				offset = -3;
				img = grid.rootPath + "img/corner.gif";
				break;
			case ImageBar.BAR_END:
				offset = -3;
				img = grid.rootPath + "img/rcorner.gif";
				break;
			case ImageBar.BAR_DUE:
				offset = -5;
				img = grid.rootPath + "img/rhomb.gif";
				break;
		}
		this.offset = offset;
		this.imgSrc = img;
	},
	
	getHTML: function (posData) {
		var columnWidth = this.column.width;
		var fpx, tpx, wpx, res;
  	res = "";
  	if (this.date) {
   		fpx = this.column.getDatePosition (this.date, columnWidth);
   		posData.itemOffset = this.offset;
   		wpx = 10;
	   	if (fpx + wpx > columnWidth)
	   		wpx = columnWidth - fpx;
	   	posData.width = wpx;
	   	posData.pos = fpx;
	   	if (wpx > 0) {
   	 		if (document.printMode)
   	 			res += "<div style='" + this.column.getStyleForPos(posData) + ";height: 15px; '><img style='margin-top: 3px' src='" + this.imgSrc + "'></div>";
   	 		else
   	 			res += "<div class='bar' style='"+ this.column.getStyleForPos(posData) +"'><img style='margin-top: 3px' src='" + this.imgSrc + "'></div>";
   	 	}
   	}
   	return res;
	},
	
	getText: function () {
		var days = 0;
		switch (this.type) {
			case ImageBar.BAR_START:
				return this.buildText(pmStrings.pm_startsin_note, this.date);
			case ImageBar.BAR_DUE:
				return this.buildText(pmStrings.pm_duein_note, this.date);
			case ImageBar.BAR_END:
				return "";
		}
		return "";
	}
});

var ProgressBar = function (column, color, fromDate, toDate, config) {
	this.fromDate = fromDate;
	this.toDate = toDate;
	this.color = color;
	ProgressBar.superclass.constructor.call (this, column, config);
}

ProgressBar.BAR_BLUE = "blue";
ProgressBar.BAR_RED = "red";
ProgressBar.BAR_GREEN = "green";

Ext.extend (ProgressBar, Bar, {
	fromDate: null,
	toDate: null,
	color: null,
	
	init: function () {
		ProgressBar.superclass.init.call (this);
		switch (this.color) {
			case ProgressBar.BAR_BLUE:
				break;
			case ProgressBar.BAR_GREEN:
				break;
			case ProgressBar.BAR_RED:
				break;
		}
	},	
	
	getHTML: function (posData) {
		var fpx, tpx, wpx, res;
		res = "";
  	var columnWidth = this.column.width;
  	if (this.fromDate) {
   		fpx = this.column.getDatePosition (this.fromDate, columnWidth);
   		if (this.toDate) {
	   	 	tpx = this.column.getDatePosition (this.toDate, columnWidth);
	   	 	wpx = tpx-fpx;
	   	 	this.days = Math.round((this.toDate-this.fromDate)/dday);
	   	} else {
	   		wpx = 10;
	   	}
	   	if (fpx + wpx > columnWidth)
	   		wpx = columnWidth - fpx;
	   	//else if (wpx == 0) wpx = 1;
	   	posData.width = wpx;
	   	posData.pos = fpx;
	   	
	   	if (wpx > 0) {
   	 		if (document.printMode) {
 	 				if (Ext.isIE)
 	 					res += "<img src='../../common/html/res/images/" + this.color + ".gif' style='" + this.column.getStyleForPos(posData) + "; height: 15px;'>";
 	 				else
 	 					res += "<div class='bar " + this.color + "' style='" + this.column.getStyleForPos(posData) + "; float:left; height: 15px;'>" + this.days + "</div>";
   	 		} else {
   	 			var dtxt = this.days;
   	 			if (wpx < 13)
   	 				dtxt = "";
   	 			res += "<div class='bar " + this.color + "' style='left: " + fpx + "px;width: " + wpx + "px;'>" + dtxt + "</div>";
   	 		}
   	 	}
   	}
   	
   	/*if (this.color == "blue")
  		bar.addword = (bar.from > this.today) ? "Duration %days" : "In Progress %d days";
  	if (bar.color == "red")
  		bar.addword = "Overdue %d days";
  	if (bar.color == "green")
  		bar.addword = (bar.from >= this.today) ? "Due in %d days" : "%d days ahead schedule";
  	
   	if (!bar.isImage && bar.days != "" && bar.days && bar.days != "&nbsp;")
   		bar.text = bar.addword.replace("%d", bar.days);
   	else 
   		bar.text = "";
   	*/
   	
   	return res;
	},
		
	getText: function () {
		switch (this.color) {
			case ProgressBar.BAR_BLUE:
				return this.buildText (pmStrings.pm_inprogress_note, this.fromDate);
				break;
			case ProgressBar.BAR_RED:
				if (this.toDate< this.column.today)
					return this.buildText (pmStrings.pm_completed_note, this.toDate);
				else
					return this.buildText (pmStrings.pm_overdue_note, this.fromDate, this.toDate);
				break;
			case ProgressBar.BAR_GREEN:
					res = this.buildText (pmStrings.pm_duein_note, this.toDate);
					if (this.fromDate > this.column.today && this.column.getDaysCount(this.fromDate) > 0) {
						res = this.buildText (pmStrings.pm_startsin_note, this.fromDate) + " / " + res;
					}
					return res;
				break;
		}
	}
});

Ext.grid.GanttColumn = function(config){
  config.summaryType = 'gantt';
  Ext.apply(this, config);
  if(!this.id){this.id = Ext.id();}
  this.renderer = this.renderer.createDelegate(this);
  
  this.minDate = null;
	this.maxDate = null;		
	this.actualMinDate = null;
	this.actualMaxDate = null;
	this.todayLeftPx = null;
};

Ext.grid.GanttColumn.prototype = {
  init : function(grid){
    this.grid = grid;
    
    this.grid.on('columnmove', function(index, size) { 
    	this.updateHeader();
    }, this);
    
    this.grid.on('columnresize', function(index, size) { 
    	var cm = this.grid.getColumnModel();
    	var col = cm.getColumnById( cm.getColumnId(index));
			if (col.id == "desc" && size < 100)
				cm.setColumnWidth (index, 100);
    	
    	this.refreshColumn ();
    	this.refreshView ();
		}, this);
		
		var column = this;
		
		this.grid.getView().on("refresh", function(view) {
			if (column.todayLeftPx)
				column.updateTodayLine ();
		});
	
		this.grid.getColumnModel().on('hiddenchange', function(cm, columnIndex, hidden ) { 
			var col = cm.getColumnById( cm.getColumnId(columnIndex));
			if (col.id == "gantt" && !hidden) {
				var totalWidth = cm.getTotalWidth(false);
				cm.setColumnWidth(cm.getIndexById("desc"),  200);
			}
						
			this.refreshColumn ();
			this.refreshView ();
		}, this);
  },
  	
  	
  setMinDate: function  (date) {
		this.actualMinDate = date;
		//this.minDate = new Date (date.getFullYear(), date.getMonth(), 1);
		//this.setActualRegion ();
	},
		
	setMaxDate: function (date) {
		this.actualMaxDate = date;
		//this.maxDate = new Date (date.getFullYear(), date.getMonth()+1, 1);
		//this.maxDate.setDate(0);
		
		//alert ("SETTED: " + date + ":" + this.maxDate);		
		//this.setActualRegion ();
	},
		
	setActualRegion: function () {
		if (this.actualMinDate == null || this.actualMaxDate == null)
			return;
		
		var daysCount = (this.actualMaxDate-this.actualMinDate) / dday;
		if (daysCount < 50) {
			var minDate = new Date(this.actualMinDate);
			while (minDate.getDay() != 1)
				minDate.setDate(minDate.getDate()-1);		
			this.minDate = minDate;
			
			var maxDate = new Date(this.actualMaxDate);
			while (maxDate.getDay() != 0) 
				maxDate.setDate(maxDate.getDate()+1);
			
			this.maxDate = maxDate;
		} else {
			this.minDate = new Date (this.actualMinDate.getFullYear(), this.actualMinDate.getMonth(), 1);
			this.maxDate = new Date (this.actualMaxDate.getFullYear(), this.actualMaxDate.getMonth()+1, 1);
			this.maxDate.setDate(0);
		}
		
		this.minDate.setHours (0);
		this.maxDate.setMinutes(0);
		this.maxDate.setHours(23);
		this.maxDate.setMinutes(59);
	},
		
	recordDateChanged: function (value, oldValue) {
		//if (oldValue == this.actualMinDate || value < this.minDate || oldValue == this.actualMaxDate || value > this.maxDate ) {
			this.recalcDates();
			this.refreshView();
		//}
		this.updateTodayLine ();
	},
  	
  	
 	ensureDateVisible: function (date) {
 		if (!date)
 			return false;
 		
 		var changed = false;
 		if (this.actualMinDate == null || date < this.actualMinDate) {
 			this.setMinDate(date);
 			changed = true;
 		}
 		if (this.actualMaxDate == null || date > this.actualMaxDate) {
 			this.setMaxDate(date); 			
 			changed = true;
 		}
 		
 		return changed;
	},
		
	recalcDates: function () {
		this.minDate = null;
  	this.maxDate = null;
  	this.actualMinDate = this.projectMinDate;
  	this.actualMaxDate = this.projectMaxDate;
  	
  	var allComplete = true;
  	
  	for (var i = 0; i < this.grid.store.data.length; i++) {
  		var rec = this.grid.store.getAt(i);
  		this.ensureDateVisible(rec.get("start"));	
  		this.ensureDateVisible(rec.get("due"));	
  		this.ensureDateVisible(rec.get("end"));	
  		if ((rec.get("start") || rec.get("due")) && !rec.get("end"))
  			allComplete = false;
  	}
  	
  	if (!allComplete)
  		this.ensureDateVisible(globalToday);
  	
  	if (!this.actualMinDate) {
  		var minDate = (this.actualMaxDate) ? new Date(this.actualMaxDate) : new Date(globalToday).setHours(0);
  		this.setMinDate(minDate);
  	}
  	if (!this.actualMaxDate) {
  		var maxDate = new Date(this.actualMinDate);
  		maxDate.setDate(maxDate.getDate()+7);
  		this.setMaxDate(maxDate);
  	}
  	
  	this.setActualRegion ();
	},
  	
  /**************
	* Returns year week number for date
	********************/
	getWeekNo: function (date) {
		var firstDay = new Date(date.getFullYear(), 0,1);
		var daysCount = (date-firstDay)/dday+1;
		return Math.ceil(daysCount / 7);
	},
		
	
	
	summaryRenderer: function (v, params, data) {
		params.css = "cell-gantt";
		var res = "";
		for (var i= 0; i < v.length; i++) {
			var recParams = new Array ();
			res += document.worksGrid.ganttColumn.getRecordHTML (v[i], recParams);
		}
		return "<div style='position: absolute'>" + res + "</div>";
	},
		
	getTodayLeftPx : function (columnWidth) {
		if (!this.todayLeftPx) {
			var today = globalToday;
			this.todayLeftPx = this.grid.ganttColumn.getDatePosition (today, columnWidth);
    }
    return this.todayLeftPx;
	},
  
  renderer : function(v,params,record, rowIndex, colIndex){
    if (this.hidden)
    	return "";
    var recParams = new Array ();
    var res = this.getRecordHTML (record, recParams);
    
    params.css = "cell-gantt";
    
    if (document.printMode)
    	params.style = "background-position: " + this.getTodayLeftPx(100) + "% 0%";
    return (document.printMode) ?
    	"<div>" + res + "</div>" :
    	"<div style='position: absolute' title='" + recParams.title + "'>" + res + "</div>";
  },
  	
  getRecordHTML: function (record, recParams) {
  	var grid = this.grid;
  	if (!this.minDate)
    	return;
    var cm = grid.getColumnModel();
    
   	var res = "";
   	var left = 0; var leftPx = 0;
   	var width = 0; var widthPx = 0;
   	var columnWidth = this.getColumnWidth ();
   	var today = new Date(globalToday);
   	var ganttToday = (today < this.maxDate) ? today : new Date(this.maxDate);
   	
   	var start = null;
   	var due = null;
   	var end = null;
   	
   	if (record.get("start"))
   		start = record.get("start");
   	if (record.get("due")) {
   		due = new Date(record.get("due"));
   		due.setHours(23);due.setMinutes(59);
   	}
   	if (record.get("end")) {
   		end = new Date(record.get("end"));
   		end.setHours(23);end.setMinutes(59);
   	}
   	if (end) {
   		ganttToday = end;
   		today = end;
   	}
   	
   	var bars = new Array ();
   	
   	if (!due) {
   		if (!start && end) {
   			bars.push (this.createImageBar(ImageBar.BAR_END, end));
   			//lbar.from = new Date(end); lbar.offset = -3; lbar.days = "<img style='margin-top: 3px' src='" + '>"; lbar.color = "transparent"; lbar.isImage = true;
   		} else if (start < today) {
   			bars.push (this.createProgressBar(ProgressBar.BAR_BLUE, start, ganttToday));
   		} else {
   			bars.push (this.createImageBar(ImageBar.BAR_START, start));
   			//lbar.from = new Date(start); lbar.from.setHours(12); lbar.offset = -3; lbar.days = "<img style='margin-top: 3px' src='" + grid.rootPath + "img/corner.gif'>"; lbar.color = "transparent"; lbar.isImage = true;
   		}
   	} else {
   		if (!start && end && end <= due) {
   			bars.push (this.createImageBar(ImageBar.BAR_END, end));
   			//rbar.from = new Date(end); rbar.from.setHours(12); rbar.offset = -3; rbar.days = "<img style='margin-top: 3px' src='" + grid.rootPath + "img/rcorner.gif'>"; rbar.color = "transparent"; rbar.isImage = true;
   		} else if (!start && due > today) {
   			bars.push (this.createImageBar(ImageBar.BAR_DUE, due));
   			//rbar.from = new Date(due); rbar.from.setHours(12); rbar.offset = -5; rbar.days = "<img style='margin-top: 3px' src='" + grid.rootPath + "img/rhomb.gif'>";  rbar.color = "transparent";  rbar.isImage = true; rbar.isRhomb = true
   		} else if (start < today && due < today) {
   			if (start)
   				bars.push (this.createProgressBar (ProgressBar.BAR_BLUE, start, due));
   			bars.push (this.createProgressBar (ProgressBar.BAR_RED, due, ganttToday));
   		} else if (start < today && due >= today) {
   			bars.push (this.createProgressBar(ProgressBar.BAR_BLUE, start, ganttToday));
   			bars.push (this.createProgressBar(ProgressBar.BAR_GREEN, ganttToday, due));
   		} else if (start > today) {
   			bars.push (this.createProgressBar(ProgressBar.BAR_GREEN, start, due));
   		}
   	}
   	
   	var posData = {offset: 0, pos: 0, width: 0, borderWidth: 0};
   	//res += this.getBarHTML (lbar, columnWidth, posData);
   	//res += this.getBarHTML (rbar, columnWidth, posData);
   	
   	var titles = new Array ();
   	titles.pushNotEmpty = pushNotEmpty;
   	for (var i = 0; i < bars.length; i++) 
	   		res += this.getBarHTML (bars[i], columnWidth, posData);
	  
	  if (!end) {
	   	for (var i = 0; i < bars.length; i++) 
	   		titles.pushNotEmpty(bars[i].getText());
	  } else {
	  	titles.pushNotEmpty(this.buildText(pmStrings.pm_completed_note, end));
	  	if (due) {
		  	if (due < end)
		  		titles.pushNotEmpty(this.buildText(pmStrings.pm_overdue_note, due, end));
		  	else if (due && this.getDaysCount(end, due) > 0)
		  		titles.pushNotEmpty(this.buildText(pmStrings.pm_aheadschedule_note, due, end));
		  }
	  }
	  
   	recParams.title = titles.join(" / ");
   	
    //if (this.todayLeftPx < columnWidth && this.todayLeftPx > 0)
    	//res += "<div style='height: 15px; position: absolute; left: " + this.todayLeftPx + "px; border-left: 1px dashed darkred'><img src='" + Ext.BLANK_IMAGE_URL + "'></div>";
    
    return res;
  },
  	
  getBarHTML: function (bar, columnWidth, posData) {
  	return bar.getHTML(posData);
  	
  	/*var fpx, tpx, wpx, res;
  	res = "";
  	if (bar.from) {
   		fpx = this.getDatePosition (bar.from, columnWidth);
   		if (bar.to) {
	   	 	tpx = this.getDatePosition (bar.to, columnWidth);
	   	 	wpx = tpx-fpx;
	   	 	bar.days = Math.round((bar.to-bar.from)/dday);
	   	} else {
	   		wpx = 10;
	   	}
	   	if (!document.printMode && bar.isImage && bar.offset)
	   		fpx += bar.offset;
	   	if (fpx + wpx > columnWidth)
	   		wpx = columnWidth - fpx;
	   	//else if (wpx == 0) wpx = 1;
	   	posData.width = wpx;
	   	posData.pos = fpx;
	   	if (wpx > 0) {
   	 		if (document.printMode) {
   	 			if (bar.isImage)
   	 				res += "<div style='margin-left: -10px; " + this.getStyleForPos(posData) + ";height: 15px; '>" + bar.days + "</div>";
   	 			else
   	 				if (Ext.isIE)
   	 					res += "<img src='../../common/html/res/images/" + bar.color + ".gif' style='" + this.getStyleForPos(posData) + "; height: 15px;'>";
   	 				else
   	 					res += "<div class='bar " + bar.color + "' style='" + this.getStyleForPos(posData) + "; float:left; height: 15px;'>" + bar.days + "</div>";
   	 		} else {
   	 			var dtxt = bar.days;
   	 			if (wpx < 13 && !bar.isImage)
   	 				dtxt = "";
   	 			res += "<div class='bar " + bar.color + "' style='left: " + fpx + "px;width: " + wpx + "px;'>" + dtxt + "</div>";
   	 		}
   	 	}
   	}
   	
   	if (bar.color == "blue")
  		bar.addword = (bar.from > this.today) ? "Duration %days" : "In Progress %d days";
  	if (bar.color == "red")
  		bar.addword = "Overdue %d days";
  	if (bar.color == "green")
  		bar.addword = (bar.from >= this.today) ? "Due in %d days" : "%d days ahead schedule";
  	
   	if (!bar.isImage && bar.days != "" && bar.days && bar.days != "&nbsp;")
   		bar.text = bar.addword.replace("%d", bar.days);
   	else 
   		bar.text = "";
   	return res;
   	*/
  },
  	
  /*************
	* Returns x-position for date in column
	**************/
	getDatePosition: function (date, columnWidth, needAlert) {
		var globalDaysCount = (this.maxDate - this.minDate) / dday;
		var daysFromStart = (date-this.minDate)/dday;
		if (columnWidth && !document.printMode)
	 		var pos = (daysFromStart/globalDaysCount)*columnWidth;
	 	else
	 		var pos = (daysFromStart/globalDaysCount)*100;
	 	var res = null;
	 	if (document.printMode)
	 		res = Math.ceil(pos*100)/100;
	 	else
	 		res = Math.round(pos);	 	
	 	if (needAlert)
	 		alert("CW: " + columnWidth + "\ndate: " + date + "\npos:" + res);
	 	return res;
	},
		
		
		
	/**************
	* Shows gantt period in header
	********************/
	updateHeader: function () {
		if (!this.minDate || !this.maxDate)
			return;
		var	ganttLegendHtml = "";
		
		var daysCount = (this.maxDate-this.minDate) / dday;
		var mainItems = null;
		var secondItems = null;
		
		
		var fdinfo = {date: new Date(this.minDate), text: this.minDate.getFullYear() + " - " + this.maxDate.getFullYear()};
		var fullDates = new Array (fdinfo);
		mainItems = fullDates;
		
		
		var ages = new Array ();
		var lastDate = new Date(this.minDate.getFullYear(), 0, 1);
		while (lastDate < this.maxDate) {
			ages.push ({date: new Date(lastDate), text: (Math.round(lastDate.getFullYear() / 100)+1), fullText: "Century " + (Math.round(lastDate.getFullYear() / 100)+1) });
			lastDate.setYear (lastDate.getFullYear()+100);
		}
		secondItems = ages;
		
		if (ages.length < 2) {
			var decYears = new Array ();
			var lastDate = new Date(this.minDate.getFullYear(), 0, 1);
			while (lastDate < this.maxDate) {
				var secondDate = new Date (lastDate);
				secondDate.setYear(secondDate.getFullYear()+9);
				if (secondDate > this.maxDate) secondDate = this.maxDate;
				decYears.push ({date: new Date(lastDate), text: lastDate.getFullYear() + "-" + secondDate.getFullYear() });
				lastDate.setYear (lastDate.getFullYear()+10);
			}
			secondItems = decYears;
			
			if (decYears.length < 2) {
				var years = new Array ();
				var lastDate = new Date(this.minDate.getFullYear(), 0, 1);
				while (lastDate < this.maxDate) {
					years.push ({date: new Date(lastDate), text: lastDate.getFullYear()});
					lastDate.setYear (lastDate.getFullYear()+1);
				}
				
				if (years.length > 3) {
					secondItems = years;
				} else {
					var months = new Array ();
					lastDate = new Date(this.minDate.getFullYear(), this.minDate.getMonth(), 1);
					while (lastDate < this.maxDate) {
						months.push ({date: new Date(lastDate), type: "month", text: Date.monthNames[lastDate.getMonth()]});
						lastDate.setMonth (lastDate.getMonth()+1);
					}
					var lastMonthDate = new Date(lastDate);
					mainItems = years;
					secondItems = months;
					
					if (daysCount <= 265) {
						lastDate = new Date(this.minDate);
						while (lastDate.getDay() != 1)
							lastDate.setDate(lastDate.getDate()-1);
						var weeks = new Array ();
						
						while (lastDate < this.maxDate) {
							var weekNo = this.getWeekNo(lastDate);
							if (weekNo == 53)
								weekNo = "53/1";
							weeks.push ({fullText: pmStrings.pm_week_label + " " + weekNo, text: weekNo, date: new Date(lastDate)});
							lastDate.setDate (lastDate.getDate()+7);
						}
						mainItems = months;
						secondItems = weeks;
						
						if (daysCount <= 40) {
							var days = new Array ();
							var lastDate = new Date (this.minDate);
							while (lastDate <= this.maxDate) {
								var gd = lastDate.getDay();
								var weekend = (gd == 6 || gd == 0);					
								days.push ({text: lastDate.getDate(), weekend: weekend, date: new Date(lastDate)});
								lastDate.setDate(lastDate.getDate()+1);
							}
							secondItems = days;
						}
					}
				}
			}
		}
			
		var columnWidth = this.getColumnWidth ();
			
		
		ganttLegendHtml += this.getLegendHTML (this.getMainLegendHTML (mainItems, columnWidth), "height: 14px", "height: 13px; line-height: 15px; ");
		ganttLegendHtml += this.getLegendHTML (this.getSublegendHTML (secondItems, columnWidth), "height: 13px; font-size: 8pt; border-top: 1px solid #999; overflow: hidden", "");
		
		var ganttHeader = Ext.get("x-grid3-td-value-gantt");
		
		ganttHeader.parent().setStyle ("padding", "0px");
		ganttHeader.parent().setStyle ("padding-top", "0px");
		ganttHeader.parent().setStyle ("padding-bottom", "0px");
		//ganttHeader.setStyle ("overflow-x", "hidden");
		//ganttHeader.setStyle ("height", "25px");
		ganttHeader.dom.innerHTML = ganttLegendHtml;
		
		var today = new Date (globalToday);
		this.today = today;
		this.todayLeftPx = this.grid.ganttColumn.getDatePosition (today, columnWidth);				
	},
		
	getLegendHTML: function (innerHTML, style1, style2) {
		var ganttLegendHtml = "<div style='white-space: nowrap;" + style1 + "'><div style='padding: 0px; margin: 0px; " + style2 + ";white-space: nowrap;position: absolute;'>";
		ganttLegendHtml += innerHTML;
		ganttLegendHtml += "</div></div>";
		return ganttLegendHtml;
	},
		
	getStyleForPos: function (posData) {
		if (document.printMode) {
			
			var marLeft = Math.ceil((posData.pos-posData.offset) * 100)/100;
			if (posData.isLast)
				posData.width -= 1;
			var res =  "position: relative; left: " + marLeft + "%; width: " + (posData.width) + "%; ";
			if (posData.itemOffset)
				res += "margin-left: " + posData.itemOffset + "px";
			posData.offset += posData.width;
			return res;
		}
		else {
			var left = posData.pos;
			if (posData.itemOffset) {
				left += posData.itemOffset;
			}
			return "position: absolute; left: " + left + "px; width: " + posData.width + "px";
		}
	}, 
		
	getMainLegendHTML: function (items, columnWidth) {
		if ((document.printMode))
			columnWidth = 100;
		var itemWidth = Math.floor(columnWidth / items.length);
		var result = "";
		var posData = {offset: 0, pos: 0, width: itemWidth};
		var nextPos = 0;
		for (i = 0; i < items.length; i++) {
			var item = items[i];
			var nextItem = items[i+1];
			nextPos = (nextItem) ? this.getDatePosition (nextItem.date, columnWidth) : columnWidth;				
			posData.pos = this.getDatePosition (item.date, columnWidth);
			if (posData.pos < 0)
				posData.pos = 0;
			posData.width = nextPos - posData.pos;
			
			if (posData.width < 60 && item.type == "month")
				item.text = item.text.substr(0,3);
			
			if (i == items.length - 1)
				posData.isLast = true;
			
			var borderStyle = (i == 0) ? "" : "border-left: 1px solid #999; ";
			var divClass = (i == 0) ? "class='first'" : "";
			result += (document.printMode) ?
				"<div " + divClass + " style='text-align: center; float: left; font-size: 8pt; " + this.getStyleForPos (posData) + "'>" + item.text + "</div>" :
				"<div " + divClass + " style='text-align: center; overflow-x: hidden; " + borderStyle + "font-size: 8pt; " + this.getStyleForPos(posData) + "'>" + item.text + "</div>";
		}	
		return result;
	},
		
	getSublegendHTML: function (items, columnWidth) {
		if (items.length > 70)
			return "&nbsp;";
		
		if (document.printMode)
			columnWidth = 100;
		var itemWidth = Math.floor(columnWidth / items.length);
		var result = "";
		var posData = {offset: 0, pos: 0, width: itemWidth};
		var nextPos = 0;
		for (i = 0; i < items.length; i++) {
			var item = items[i];
			var nextItem = items[i+1];
			nextPos = (nextItem) ? this.getDatePosition (nextItem.date, columnWidth) : columnWidth;
			posData.pos = this.getDatePosition (item.date, columnWidth);
			if (posData.pos < 0)
				posData.pos = 0;
			posData.width = nextPos - posData.pos;
			if (posData.pos + posData.width > columnWidth)
				posData.width = columnWidth - posData.pos;
			if (i == items.length - 1)
				posData.width -= 1;
			
			var borderStyle = (i == 0) ? "" : "border-left: 1px solid #999; ";
			var text = (itemWidth > 40 && item.fullText) ? item.fullText : item.text;
			if (item.type == "month") {
				if (itemWidth < 24) text = "&nbsp;";
				else if (posData.width < 60) text = text.substr(0,3);
			}
			
			if (itemWidth < 16 && !document.printMode)
				text = "&nbsp;";
			
			var classes = new Array ();
			if (item.weekend) classes.push("weekend");
			if (i == 0) classes.push("first");
			
			var classStr = (classes.length > 0) ? "class='" + classes.join(" ") + "'" : "";
				
			
			result += document.printMode ?
				"<div " + classStr + " style='float: left; text-align: center; " + this.getStyleForPos (posData) + "'>" + text + "</div>" :
				"<div " + classStr + " style='top: 1px; position: absolute; overflow-x: hidden; text-align: center; " + borderStyle + this.getStyleForPos(posData) + "'>" + text + "</div>";
		}
		return result;
	},
		
	refreshColumn: function () {
		var cm = this.grid.getColumnModel ();
		var offset = 22;
		
		var totalWidth = cm.getTotalWidth(false);
		var ganttColumnNo = cm.getIndexById("gantt");
		var descColumnNo = cm.getIndexById("desc");
		
		var resizableClass = "x-grid3-column-resizable";
		var descHeader = Ext.get(Ext.get("x-grid3-td-value-desc").dom.parentNode.parentNode);
		if (!cm.isHidden(ganttColumnNo) &&  ganttColumnNo > -1) {
			totalWidth -= cm.getColumnWidth (ganttColumnNo);
			var ganttColumnSize = this.grid.getMyWidth() - totalWidth-offset;
			if (ganttColumnSize < 150)
				ganttColumnSize = 150;
			//alert(ganttColumnSize);
			cm.setColumnWidth(ganttColumnNo, ganttColumnSize);
			if (!descHeader.hasClass(resizableClass))
				descHeader.addClass(resizableClass);			
		} else {
			totalWidth -= cm.getColumnWidth (descColumnNo);
			var descColumnSize = this.grid.getMyWidth () - totalWidth-offset;
			if (descColumnSize < 150)
				descColumnSize = 150;
			cm.setColumnWidth(descColumnNo, descColumnSize);
			if (descHeader.hasClass(resizableClass))
				descHeader.removeClass (resizableClass);
		}
		
		//document.title = "W: " + cm.getTotalWidth(true) + " GS: " + ganttColumnSize;
		document.worksGrid.setWidth (cm.getTotalWidth(false)+22);
	},
	
	refreshView: function () {
		if (!this.hidden) {
			this.updateHeader ();
			document.worksGrid.getView().refresh ();
		}
		this.grid.getView().scroller.setWidth (this.grid.getMyWidth());
	},
		
	updateTodayLine: function () {
		var tds = Ext.query(".cell-gantt", Ext.get("works-grid").dom);
		for (var i = 0; i < tds.length; i++) {
			Ext.get(tds[i]).setStyle("background-position", this.todayLeftPx + "px 0px");
		}
	},
		
	getColumnWidth: function  () {
		var columnWidth = this.width;
		columnWidth  = this.grid.getColumnModel().getColumnWidth (this.grid.getColumnModel().getIndexById("gantt"));
		//alert(columnWidth);
		if (!this.firstTime) {
			columnWidth += 2;
			this.firstTime = true;
		} 
			columnWidth -= 1;
		return columnWidth;
	},
		
	createImageBar: function (type,date) {
		return new ImageBar (this, type, new Date(date));
	},
		
	createProgressBar: function (color, fromDate, toDate) {
		return new ProgressBar (this, color, fromDate, toDate);
	},
		
	getDaysCount: function(from, to) {
		if (!to)
			to = this.today;
		return Math.abs(Math.round((from-to)/dday));
	},
		
	buildText: function (template, dateFrom, dateTo) {
		if (!template)
			return "";
		var daysCount = this.getDaysCount(dateFrom, dateTo);
		if (daysCount > 0)
			return template.replace("%d", daysCount);
		else
			return "";
	}	
};

function pushNotEmpty (val) {
	if (val && val.length > 0)
		this.push (val);
}