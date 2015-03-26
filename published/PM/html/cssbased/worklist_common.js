var globalToday = new Date (localDate);
var globalTodayEnd = new Date(localDate); globalTodayEnd.setHours (23); globalTodayEnd.setMinutes(59);
var globalTodayStart = new Date(localDate);  globalTodayStart.setHours (0); globalTodayStart.setMinutes(0); globalTodayStart.setSeconds(0);


var daySeconds = 86000;
var dday = daySeconds * 1000;


var currencies = new Ext.data.SimpleStore({
  fields: ['id'],
  data : currenciesList
});
	

function workEstimateSort (value) {
	if (value == null || value == "") {
		return -1;
	}
	else
		return Ext.data.SortTypes.asFloat(value);	
}

function workDateSort (value) {
	if (value == null || !(value instanceof Date)) {
		return -1;
	}
	else
		return Ext.data.SortTypes.asDate(value);	
}

function assignedSort (value) {
	return value.length;
}

function statusSort (value) {
	return Work.getStatus(document.worksGrid.store.getById(value));
}

	
var Work = Ext.data.Record.create([
 {name: 'id', mapping: 'PW_ID', type: 'int'},
 {name: 'desc', mapping: 'PW_DESC', type: 'string'},
 {name: 'start', mapping: 'PW_STARTDATE', type: 'date', dateFormat: dateDisplayFormat},
 {name: 'due', mapping: 'PW_DUEDATE', type: 'date', dateFormat: dateDisplayFormat},
 {name: 'end', mapping: 'PW_ENDDATE', type: 'date', dateFormat: dateDisplayFormat},
 {name: 'estimate', type: 'float', mapping: 'PW_COSTESTIMATE'},
 {name: 'currency', type: 'string', mapping: 'PW_COSTCUR'},
 {name: 'billable', mapping: 'PW_BILLABLE', type: 'bool'},
 {name: 'status', type: 'string', mapping: 'PW_ID', sortType: statusSort},
 {name: 'url', mapping: 'ROW_URL', type: 'string'},
 {name: 'assigned', mapping: 'ASGN', type: 'string', sortType: assignedSort},
 {name: 'P_ID', mapping: 'P_ID', type: 'int'}
]);

Work.getStatus = function (record) {
	if (record == null)
		return "";
	var status = "pending";
	if (record.get("start") && record.get("start") < globalToday)
		status = "inprogress";
	if (record.get("due") && (globalTodayStart - record.get("due")) > 1000) {
		status = "overdue";
	}
	if (record.get("end"))
		status = "complete";
	return status;
}


/*
var WorkDataStore = function (config) {
	WorkDataStore.superclass.constructor.call(this, config);
}
Ext.extend (WorkDataStore, Ext.data.Store, {
	sortData : function(f, direction){
    this.data._sort = this._sort;
    
    direction = direction || 'ASC';
    var st = this.fields.get(f).sortType;
    this.data.isWorkDateSort = (st == workDateSort);
    this.data.isWorkEstimateSort = (st == workEstimateSort);
    
    var fn = function(r1, r2){
        var v1 = st(r1.data[f]), v2 = st(r2.data[f]);
        if (v1 == v2) return 10000;
        if (v1 == -1) {return 2;}
	      if (v2 == -1) return -2;
	      //if (v1 == v2) return 0;
        //if (v2 == -1) return 1;
        return v1 > v2 ? 1 : (v1 < v2 ? -1 : 0);
    };
    this.data.sort(direction, fn);
    if(this.snapshot && this.snapshot != this.data){
        this.snapshot.sort("ASC", fn);
    }
  },
  	
  _sort : function(property, dir, fn){
  	  var isWorkDateSort = this.isWorkDateSort;
  	  var isWorkEstimateSort = this.isWorkEstimateSort;
  	  var dsc = String(dir).toUpperCase() == "DESC" ? -1 : 1;
  	  fn = fn || function(a, b){
        return a-b;
      };
      var c = [], k = this.keys, items = this.items;
      for(var i = 0, len = items.length; i < len; i++){
      	c[c.length] = {key: k[i], value: items[i], index: i};
      }
      c.sort(function(a, b){
        var v = fn(a[property], b[property]);
        if (isWorkDateSort || isWorkEstimateSort) {
	        if ((v == 2 || v == -2))
	        	v = v / 2;
	        else if (v == 10000)
	        	v = a[property].data.id > b[property].data.id;
	        else v = v * dsc;
	      } else v = v * dsc;
        if(v == 0){
           v = (a.index < b.index ? -1 : 1);
        }
        return v;
      });
      for(var i = 0, len = c.length; i < len; i++){
        items[i] = c[i].value;
        k[i] = c[i].key;
      }
      this.fireEvent("sort", this);
  }
});*/

function invalidDate (field, msg) {
	if (field.testValidate)
		return;
	var nv = msg.substr(0, msg.indexOf(" "));
	
	var sep = "";
	if (field.format.indexOf ("/") > -1) sep = "/";
	if (field.format.indexOf (".") > -1) sep = ".";
	var newVal = "";
	var vals = nv.split(sep);
	if (vals.length != 3)
		return;
	for (var i = 0; i < vals.length; i++) {
		var val = vals[i];
		if (val.length < 1)
				return;
		if (i == 2) {
			if (val.length < 2) return;//val = "200" + val;
			if (val.length < 3) val = "20" + val;
		} else {
			if (val.length == 1) val = "0" + val;
		}
		vals[i]= val;
	}
	newVal = vals.join(sep);
	field.testValidate = true;
	if (field.validateValue(newVal))
		field.setValue (newVal);
	field.testValidate = false;
};

function CheckRecordDates (start, due, end) {
		var today = new Date(globalToday);
		today.setHours(23);
		if (end > today)
		{
			alert(pmStrings.amt_taskcompletetoday_message);
			return false;
		}
	
		if (due && start > due) {
			alert (pmStrings.amt_taskstartdue_message);
			return false;
		}
		
		if (end && start > end) {
			alert (pmStrings.amt_taskstartcomplete_message);
			return false;
		}
		
		return true;
	}