// Files Table and Views file

DDTable = newClass(WbsTable, {
	constructor: function(config) {
		if (!config)
			config = {};
		
		config.statusBar = true;
		config.nameElements = ddStrings.lbl_files;
		config.hideLoading = true;
		
		config.columns = [
			{name: "CONTROL", width: "20", label: "", custom: true, html: "<SAMP class='control-icon'></SAMP>", sorting: true},
			{name: "SHARED", width: "16", label: "", custom: true, cls: "shared"},
			{name: "LOCKED", width: "16", label: "", custom: true, cls: "locked"},
			{name: "ICON", width: "16", label: "", custom: true, cls: ""},
			{name: "DL_FILENAME", label: ddStrings.column_filename, sorting: true, type: "string" },
			{name: "DL_CHECKDATETIME", label: ddStrings.column_date, width: "14%", sorting: true, type: "date"},
			{name: "DL_FILETYPE", label: ddStrings.column_type, width: "5%", sorting: true, type: "string"},
			{name: "filesizeStr", label: ddStrings.column_size, width: "7%", "cls" : "fsize", sorting: true, type: "int", realSorting: "DL_FILESIZE" },
			{name: "DL_UPLOADUSERNAME", label: ddStrings.column_owner, width: "10%", cls:"fsize", sorting: true, realSorting: "DL_UPLOADUSERNAME", type: "string"}
		];		
		
		this.superclass().constructor.call(this, config);
	},
		
	getColumns: function() {
		if (this.isSearchMode()) {
			return this.columns.concat([{name: "folder", label: ddStrings.column_folder, width: "12%", sorting: false, cls: "nowrap"}]);
		} else {
			return this.columns;			
		}
	},
		
	getViewSettings: function() {
		return document.ddApplication.getViewSettings();
	}, 
		
	renderData: function() {
		this.superclass().renderData.call(this);
		if (this.statusBarElem && document.ddApplication) {
			//if (this.isSearchMode())
				this.statusBarElem.innerHTML = "";
			//else
				//this.statusBarElem.innerHTML = commonStrings.lbl_your_access_rights + ": <b>" + WbsRightsMask.getRightsStr(document.ddApplication.getCurrentFolder().Rights + "</b>");
		}
	},
		
	setHighlightWord: function(word) {
		this.highlightWord = word;
	},
		
	resetHighlightWord: function() {
		this.highlightWord = null;
	},
		
	resetPage: function() {
		this.pager.resetPage();
	},
		
	createView: function (viewMode) {
  	var config = {};
  	switch (viewMode) {
  		case "list":
  			config.iconType = "smallicons";
  			return new DDListView(this, config);
  			break;
  		case "detail":
  			config.iconType = "bigicons";
  			return new DDListView(this, config);
  			break;	
  		case "tile":
  			return new DDTileView(this, config);
  			break;
  		case "columns":
  			return new DDColumnsView(this,config);
  			break;
  	}
  	return null;
  },
  	
  isSearchMode: function() {
  	return (this.highlightWord != null);
  },
  	
  outputValue: function(value) {
  	if (!this.highlightWord)
  		return value;
  	var regex = new RegExp("(" + this.highlightWord.replace(/\s/ig, "|") +")", "ig");
  	return value.replace(regex, "<span class='highlight'>$1</span>");
  },
  	
  updateItemBlock: function (block, record) {
  	this.superclass().updateItemBlock.call(this, block, record);
  	
  	// Find control icon
  	var sampObjects = block.getElementsByTagName("SAMP");
  	for (var i = 0; i < sampObjects.length; i++)
  		if (sampObjects[i].className=="control-icon")
  			controlIconPlace = sampObjects[i];
  	if (!controlIconPlace) 
  		return;
  	
  	var icon = createDiv("control-icon");
  	icon.setAttribute("title", ddStrings.lbl_actions_with_file);
  	addHandler(icon, "click", function(e) {record.showMenu(e)}, record );
  	controlIconPlace.parentNode.replaceChild(icon, controlIconPlace);
  	
  	// Find lock and share icons
  	var imgObjects = block.getElementsByTagName("img");
  	for (var i = 0; i < imgObjects.length; i++) {
  		if(imgObjects[i].className == "lock-icon")
  			addHandler(imgObjects[i], "click", function(e) {record.showMenu(e, "lock")});
  		if(imgObjects[i].className == "shared-icon")
  			addHandler(imgObjects[i], "click", function(e) {record.showMenu(e, "share")});
  	}
  },
  	
  getFolderLink: function (record, truncate) {
  	var node = document.ddApplication.tree.nodes[record.DF_ID];
  	if (!node)
  		return record.DF_ID;
  	var name = node.Name;
  	if (truncate)
  		name = name.truncate(30);
  	return "<a href='javascript:void(0)' onClick='openFolder(\"" + node.Id + "\")'>" + name + "</a>";
  },
  	
  createRecordsList: function() {
  	return new DDFilesList();
  },
  	
  getNoRecordsMessage: function() {
  	if (this.isSearchMode())
  		return ddStrings.lbl_no_finded_files;
  	else
  		return ddStrings.lbl_no_files;
  }
});