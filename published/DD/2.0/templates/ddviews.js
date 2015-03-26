/*** 
	Views classes 
***/
DDColumnsView = newClass(WbsColumnsView, {
	
	constructor: function(table, config) {
		this.superclass().constructor.call(this, table, config);
		this.superclass = function() {return WbsColumnsView.prototype}
	},
		
	getCellHeaderValueElem: function(column) {
		if (column.name == "SHARED" || column.name == "LOCKED") {
			var icon = createDiv("icon");
			if (column.name == "SHARED")
				icon.title = "Direct link to this file";
			if (column.name == "LOCKED")
				icon.title = "File is locked";
			return icon;
		}
		return this.superclass().getCellHeaderValueElem.call(this, column);
	},
		
	getCellValue: function(column, record) {
		if (column.name == "CONTROL")
			return column.html;
		if (column.name == "LOCKED")
			return record.CHECKED_OUT ? record.getLockImg() : "";
		
		if (column.name == "ICON") {
			return "<img src='" + record.SMALLICON_URL + "'>";
		}
		
		if (column.name == "SHARED") {
			if (record.SHARE_LINK_URL)
				return record.getSharedImg();
			return "";
		}
		
		if (column.name == "folder") {
			return this.table.getFolderLink(record, true);
		}
			
		
		if (column.name == "DL_FILENAME")
  		return "<a target='_blank' href='" + record.DOWNLOAD_URL + "'>" + this.table.outputValue(this.superclass().getCellValue.call(this, column, record)) + "</a>"; 
  	else
  		return this.superclass().getCellValue.call(this, column, record) ;
  }	
});


DDListView = newClass (WbsListView, {
	constructor: function (table, config) {
		config.header = true;
		this.superclass().constructor.call(this, table, config); 
	},
	
	getClassName: function() {
		if (this.config.iconType)
			return this.modeName + " " + this.config.iconType;
		return this.modeName;
	},
	
	getThumbnailHtml: function (record) {
		return "<div onClick='this.parentNode.click()' class='thumbnail'><table><tr><td>" + "<img src='" + record.THUMBNAIL_URL + "'>" + "</td></tr></table></div>";
	},
	
	buildRecordBlock: function(block, record) {
		var resHTML = "";
		var iconHTML = (this.config.iconType== "bigicons") ? this.getThumbnailHtml(record) : "<img class='icon' src='" + record.ICON_URL + "'>";
		
		folderHTML = (this.table.isSearchMode()) ?
			"<span>Folder: " + this.table.getFolderLink(record) + "</span><BR>" : "";
		
		resHTML += "<div class='controls'><SAMP class='selector'></SAMP><SAMP class='control-icon'></SAMP></div>";
		resHTML += "<a class='wrap-image' target='_blank' href='" + record.DOWNLOAD_URL + "'>" + iconHTML + "</a>";
		resHTML += "<div class='content'>";
			resHTML += "<div class='name'>" + "<a target='_blank' href='" + record.DOWNLOAD_URL + "'>" + this.table.outputValue(record.DL_FILENAME) + "</a>" + "</div>";
			resHTML += "<SAMP id='desc'></SAMP>";
			resHTML += "<div class='small-gray'>" +
				record.getSharedImg() + 
				record.getLockImg() +
				folderHTML + 
				"<span>" + ddStrings.lbl_version_info.sprintf(record.filesizeStr, record.DL_UPLOADUSERNAME, record.DL_CHECKDATETIME) + "</span>" + 
				/*"<span>" + ddStrings.column_type + ": " + record.DL_FILETYPE + "</span>" +
				"<span>" + ddStrings.column_size + ": " + record.filesizeStr + "</span>" + 
				"<span>" + ddStrings.column_date + ": " + record.DL_CHECKDATETIME + "</span>" +
				"<span>" + ddStrings.column_owner + ": " + record.DL_UPLOADUSERNAME + "</span>" +*/
			"</div>";

		resHTML += "</div>";
		
		block.innerHTML = resHTML;
		
		var trunc = this.table.getViewSettings().descTruncate;
		if (this.table.isSearchMode())
			trunc = 0;
		var desc = record.getDescriptionText(trunc);
		
		var sampObjects = block.getElementsByTagName("SAMP");
		var descSamp;
		for (var i = 0; i < sampObjects.length; i++)
			if (sampObjects[i].id=="desc") descSamp = sampObjects[i];
		if (descSamp) {
			var descObj = createDiv("desc");
			if (record.canWrite()) {
				descObj.valueBlock = createDiv();
				var descOutputValue = (this.table.isSearchMode()) ? this.table.outputValue(desc.htmlSpecialChars()) :  desc.htmlSpecialChars();
				descObj.valueBlock.innerHTML = descOutputValue;
				descObj.valueBlock.onclick = function() {record.editDescription(descObj)};
				descObj.appendChild(descObj.valueBlock);
			} else {
				descObj.innerHTML = (this.table.isSearchMode()) ? this.table.outputValue(desc.htmlSpecialChars()) :  desc.htmlSpecialChars();
			}
			descSamp.parentNode.replaceChild(descObj, descSamp);
		}
	}
});


var DDTileView = newClass(WbsTileView, {

	constructor: function(table, config) {
		config.header = true;
		this.superclass().constructor.call(this, table, config); 
	},
	
	getThumbnailHtml: function (record) {
		return "<div onClick='this.parentNode.click()' class='thumbnail'><table><tr><td>" + "<img src='" + record.THUMBNAIL_URL + "'>" + "</td></tr></table></div>";
	},
	
	buildRecordBlock: function(block, record) {
		var resHTML = "";
		
		var controlsHTML = "";
		if  (record.SHARE_LINK_URL)
			controlsHTML += record.getSharedImg();
		if (record.CHECKED_OUT)
			controlsHTML += record.getLockImg();
		controlsHTML += record.filesizeStr;
		
		//resHTML += "<input type='checkbox' class='checker'>";
		resHTML += "<br style='display: none'/>"; // IE bugfix
		resHTML += "<a style='margin-bottom: 0px' class='wrap-image' target='_blank' href='" + record.DOWNLOAD_URL + "'>" + this.getThumbnailHtml(record) + "</a>";
		resHTML += "<div class='content' style='margin: 0px'>";
			resHTML += "<div class='name'>" + "<SAMP class='selector'></SAMP><a target='_blank' href='" + record.DOWNLOAD_URL + "'>" + this.table.outputValue(record.DL_FILENAME.truncate(11)) + "</a>" + "</div>";
			resHTML += "<div class='controls'><SAMP class='control-icon'></SAMP>" + controlsHTML + "</div>" ;
		resHTML += "</div>";
			
		block.innerHTML = resHTML;
	}
});