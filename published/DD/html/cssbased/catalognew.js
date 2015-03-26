// Classes


function DDApplication () {
	this.init = function() {
		var scrollPage = new WbsScrollPage ({
				wrapperElemId: 'screen-content-block', 
				footer: true, header: true, 
				contentElemId: 'files-list'				
		});
				
		var table = new DDTable ({renderTo: "files-list", scrollPage: scrollPage, selection: true});
		this.table = table;
		
		var folderControls = document.ddApplication.getControlsToolbar();		
		scrollPage.headerElem.appendChild(folderControls.elem);
		scrollPage.addItem(folderControls);
		
		var viewmodeSelectorElem = document.getElementById("viewmode-selector-wrapper");
		var viewModeSelector = new WbsViewmodeSelector(table, {renderElem: viewmodeSelectorElem});
		viewModeSelector.onModeSelected = function(selector, mode) {
			setCookie("DD_selectedViewMode", mode);
		}
		//folderControls.elem.appendChild(viewmodeSelectorElem,scrollPage.headerElem.lastChild);
		scrollPage.addItem(viewModeSelector);
		
		var reader = new WbsReader ({
			url: "../ajax/files_list.php",
			baseParams: {},
			recordsProperty: 'files',
			totalProperty: 'totalFiles',
			onSuccess: function(responseData, records) {
					var folderData = responseData.folder;
					table.setTitle (folderData.NAME);
			}
		});
		
		var store = new WbsDataStore({reader: reader});
		this.store = store;
		
		
		//var footer = new WbsFooter({cls: "screen-content-footer", contentElemId: "screen-content-block"});
		
		var pager = new WbsPager ({renderElem: scrollPage.footerElem, store: store});
		table.addPager(pager);
		table.setStore(store);
		table.render();
		
		var tree = new WbsTree({elemId: "folders-tree", iconCls: "my-folder"});
		tree.onNodeClick = function(node, e) {
			document.ddApplication.selectedFolderId = node.id;
			store.reader.baseParams.id = node.id;
			store.load();
		}
		
		tree.selectFolder = function(folderId) {
			this.selectNode(folderId);			
		}

		tree.init();
		tree.loadNodes(document.treeNodes);
		
		var rootNode = tree.treePanel.getRootNode();
		//rootNode.childNodes[0].appendChild(new Ext.tree.TreeNode({id: "88.6.", text: "TEST"}));
		
		tree.render();
		this.tree = tree;
		
		scrollPage.render();
		
		var selectedViewMode = getCookie("DD_selectedViewMode");
		if (!selectedViewMode)
			selectedViewMode = "columns";
		viewModeSelector.selectMode(selectedViewMode);
	}
	
	this.refreshData = function() {
		this.store.load();
	}
	
	this.getCurrentFolderId = function() {
		return this.selectedFolderId;		
	}
	
	this.openUploadDlg = function() {
		var uploadDlg = this.getUploadDlg();
		uploadDlg.show();
	}
	
	this.getUploadDlg = function () {
		if (this.uploadDlg == null)
			this.uploadDlg = new WbsUploadDlg({
				contentElemId: 'dlg-upload-content',
				cls:"upload-dlg", 
				contentElemId: "dlg-upload-content",
				uploadURL: "../../../../DD/html/ajax/file_upload.php",
				ieUploadURL: "../../../DD/html/ajax/file_upload.php",
				swfURL: "../../../common/html/res/swfupload/swfupload.swf",
				sessID: document.sessionId,
				title: "Upload Files"
			});
		return this.uploadDlg;
	}
	
	this.getControlsToolbar = function() {
		var controls = function() {
			this.elem = document.getElementById("wbs-table-controls");
			this.render = function() {
			}
		}		
		
		return new controls;
	}
	
	this.deleteFile = function (id) {
		Ext.Ajax.request ({
			url: "../ajax/files_deletenew.php",
			params: {"documents[]": [id]},
			success: function (response) {
				document.ddApplication.refreshData();
			}
		});
	}
	
	this.getLinkForFile = function (id, menu) {
		Ext.Ajax.request ({
			url: "../ajax/file_createlink.php",
			params: {"id": id},
			success: function (response) {
				var result = Ext.decode(response.responseText);
				menu.record.SHARE_LINK_URL = result.link;
				menu.showHideLink();
				//document.ddApplication.refreshData();
				document.ddApplication.table.refreshRecordBlock(menu.record.id);
			}
		});
	}
	
	this.removeLinkForFile = function (id, menu) {
		Ext.Ajax.request ({
			url: "../ajax/file_removelink.php",
			params: {"id": id},
			success: function (response) {
				var result = Ext.decode(response.responseText);
				menu.record.SHARE_LINK_URL = null;
				menu.showHideLink();
				//document.ddApplication.refreshData();
				document.ddApplication.table.refreshRecordBlock(menu.record.id);
			}
		});
	}
	
	this.changeCheckStatusFile = function (id, menu, status) {
		Ext.Ajax.request ({
			url: "../ajax/file_changecheckstatus.php",
			params: {"id": id, "status" : status? "checkout" : "checkin"},
			success: function (response) {
				var result = Ext.decode(response.responseText);
				if (result.success) {
					menu.record.CHECKED_OUT = result.CHECKED_OUT;
					menu.record.CHECKED_OUT_INFO = result.CHECKED_OUT_INFO;
					menu.showHideCheckedStatus();
					document.ddApplication.table.refreshRecordBlock(menu.record.id);
				} else {
					alert(result.errorStr);
				}
			}
		});
	}
	
	this.editDescription = function(descElem) {
  	var descEditor = new DescEditor(descElem);
  }
}

function DescEditor(descElem) {
	this.editElem = createDiv("desc-editor");
	this.descElem = descElem;
	var editor = this;
	
	var textarea = createElem("textarea");
	textarea.value = descElem.record.DL_DESC;
	var textareaHeight = descElem.valueBlock.offsetHeight+4;
	if (textareaHeight < 50)
		textareaHeight = 50;
	textarea.style.height = textareaHeight;
	this.editElem.appendChild(textarea);
	this.editElem.textarea = textarea;
	
	this.saveLink = createElem("a");
	this.saveLink.setAttribute("href", "javascript:void(0)");
	this.saveLink.innerHTML = "Save";
	this.saveLink.onclick = function(){editor.saveDesc()};
	this.editElem.appendChild(this.saveLink);
	
	this.cancelLink = createElem("a");
	this.cancelLink .setAttribute("href", "javascript:void(0)");
	this.cancelLink.innerHTML = "Cancel";
	this.cancelLink.onclick = function(){editor.cancel()};
	this.editElem.appendChild(this.cancelLink);
	
	descElem.appendChild(this.editElem);
	descElem.valueBlock.style.display = "none";
	
	this.cancel = function() {
		descElem.removeChild(this.editElem);
		descElem.valueBlock.style.display = "block";
	}
	
	this.saveDesc = function() {
		var descElem = this.descElem;
		
		Ext.Ajax.request ({
			url: "../ajax/file_changedesc.php",
			params: {"id": this.descElem.record.id, description: this.editElem.textarea.value},
			success: function (response) {
				var result = Ext.decode(response.responseText);
				if (!result.success) {
					alert(result.errorStr);
					return;
				}
				descElem.record.DL_DESC = result.newDesc;
				document.ddApplication.table.refreshRecordBlock(descElem.record.id);
			}
		});
	}
}



function FileMenu(config) {
	FileMenu.superclass.constructor.call(this,config);
	
	this.showHideLink = function () {
		this.setLiShareLinkUrl(this.record.SHARE_LINK_URL);
		if (this.record.SHARE_LINK_URL) {
			this.showLi("link_data");
			//this.showLi("link_delete");
			this.hideLi("link_create");
		} else {
			this.hideLi("link_data");
			//this.hideLi("link_delete");
			this.showLi("link_create");
		}
	}
	
	this.showHideCheckedStatus = function() {
		if (this.record.CHECKED_OUT) {
			this.showLi("check_in");
			this.hideLi("check_out");
		} else {
			this.showLi("check_out");
			this.hideLi("check_in");
		}
	}
	
	this.setLiShareLinkUrl = function (url) {
		var textarea = this.liElems["link_data"].getElementsByTagName("input")[0];
		textarea.value = url;
	}
	
	/*this.createLinkItem = function(record) {
		var item = null;
		var menu = this;
		if (record.SHARE_LINK_URL)
			item = {id: "link", cls: "nopadding", html: "<b>Link</b><BR><textarea onClick='this.select()' cols='30' rows='1' style='height: 18px; font-size: 0.8em; color: #999; border: 1px solid #DDD; overflow: hidden'>" + record.SHARE_LINK_URL + "</textarea>"};
		else
			item = {id: "link", label: "Get a Link", onClickNoHide: true, onClick: function() {document.ddApplication.getLinkForFile(menu.selectedFile, menu)}};
		return item
	}*/
}
extend(FileMenu, WbsPopmenu);


function DDTable(config) {
    DDTable.superclass.constructor.call(this, config);
    this.scrollPage = config.scrollPage;
    
    this.titleElem = createDiv("title");
    this.scrollPage.headerElem.insertBefore(this.titleElem, this.scrollPage.headerElem.firstChild);
    
    this.renderData = function() {
    	var title = (this.title) ? this.title : "&lt;not selected folder&gt;";
    	this.titleElem.innerHTML = title;
    	DDTable.superclass.renderData.call(this);
    }
    
    this.setTitle = function (title) {
    	this.title = title;
    }
    
    this.getItemControlHTML = function (record) {
    	return "<a href='" + + "'>Control</a>";
    }
    
    this.updateItemBlock = function (block, record) {
    	DDTable.superclass.updateItemBlock.call(this, block, record);
    	
    	var sampObjects = block.getElementsByTagName("SAMP");
    	for (var i = 0; i < sampObjects.length; i++)
    		if (sampObjects[i].className=="control-icon")
    			controlIconPlace = sampObjects[i];
    	if (!controlIconPlace) 
    		return;
    	
    	var icon = document.createElement("div");
    	icon.className = "control-icon";
    	addHandler(icon, "click", 
    		function(e){
    			
    			var linkHidden = record.SHARE_LINK_URL ? false : true;
    			var checkOutHidden = record.CHECKED_OUT ? true : false;
    			var items = [
	    			{label: "Open", onClick: function() {window.open(record.OPEN_URL)}},
	    			{label: "Download", onClick: function() {location.href = record.DOWNLOAD_URL; return false;}},
	    			"-",
	    			{id: "link_data", hidden: linkHidden, cls: "nopadding-nolink", html: "<div style='position: absolute; right: 10px'><a id='link-delete' href='javascript:void(0)'>Unshare</a></div><span class='smalllabel'>This file is shared</span><BR><span class='label'>URL:</span> <input onClick='this.select()' class='shared-url' value='" + record.SHARE_LINK_URL + "'>"},
						{id: "link_create", hidden: !linkHidden, label: "Share", onClickNoHide: true, onClick: function() {document.ddApplication.getLinkForFile(menu.selectedFile, menu)}},
						//{id: "link_delete", hidden: linkHidden, label: "Delete a Link", onClickNoHide: false, onClick: function() {document.ddApplication.removeLinkForFile(menu.selectedFile, menu)}},
	    			"-",
	    			{id: "check_out", label: "Check Out", hidden: checkOutHidden,  onClickNoHide: true, onClick: function() {document.ddApplication.changeCheckStatusFile(menu.selectedFile, menu, true)}},
	    			{id: "check_in", label: "Check In", hidden: !checkOutHidden, onClickNoHide: true, onClick: function() {document.ddApplication.changeCheckStatusFile(menu.selectedFile, menu, false)}},
	    			{label: "Version History", disabled: true},
	    			"-",
	    			{label: "Add Description", disabled: true},
	    			"-",
	    			{label: "Copy", disabled: true},
	    			{label: "Move", disabled: true},
	    			{label: "Delete", onClick: function() {document.ddApplication.deleteFile(menu.selectedFile)}}	    			
					];
    			
    			var menu = new FileMenu({items: items});
    			menu.selectedFile = record.id;
    			menu.record = record;
    			menu.show(e);
    			
    			var linkDelete = menu.liElems.link_data.getElementsByTagName("a")[0];
    			linkDelete.onclick = function() {document.ddApplication.removeLinkForFile(menu.selectedFile, menu)};
    		}, 
    	false);
    	
    	controlIconPlace.parentNode.replaceChild(icon, controlIconPlace);
    }
    
    this.getLockImg = function(record) {
    	var comment = "Checked out by ";
    	return "<img alt='" + comment + "' title='" + comment + "' src='../cssbased/images/lock.gif'>";    	
    }
    
    this.getSharedImg = function() {
    	return "<img src='../../../common/templates/img/shared.gif'>";
    }
    
    this.createView = function (viewMode) {
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
    }
    
    this.getSortingColumns = function() {
    	return [
    		{name: "DL_NAME", label: "Filename"},
    		{name: "DL_FILESIZE", label: "Size"},
    		{name: "DL_FILETYPE", label: "Type"},
    		{name: "DL_MODIFYDATE", label: "Date"}
    	];
    }
}
extend(DDTable, WbsTable);

function addClass(elem, className) {
	var classes = elem.className.split(" ");
	for (var i = 0; i < classes.length; i++) {
		if (classes[i] == className)
			return;
	}	
	classes[classes.length] = className;
	elem.className = classes.join(" ");
}

function removeClass (elem, className) {
	var classes = elem.className.split(" ");
	var newClasses = new Array ();
	for (var i = 0; i < classes.length; i++) {
		if (classes[i] == className)
			continue;
		newClasses[newClasses.length] = classes[i];
	}	
	elem.className = newClasses.join(" ");
}


var DDColumnsView = function (table, config) {
	DDColumnsView.superclass.constructor.call(this, table, config); 
	this.columns = [
		/*{name: "DL_ID", label: "ID", width : "40"},*/
		{name: "SHARED", width: "20", label: "", custom: true},
		{name: "LOCKED", width: "20", label: "", custom: true},
		//{name: "ICON", width: "20", label: "", custom: true},
		{name: "CONTROL", width: "20", label: "", custom: true, html: "<SAMP class='control-icon'></SAMP>"},
		{name: "DL_FILENAME", label: "Filename"},
		{name: "DL_MODIFYDATETIME", label: "Date", width: "150"},
		{name: "DL_FILETYPE", label: "Type", width: "100"},
		{name: "DL_FILESIZE", label: "Size", width: "60", "cls" : "fsize"}
	];
	
	this.renderRow = function(trElem, record) {
		DDColumnsView.superclass.renderRow.call(this, trElem, record);
	}
		
	this.getCellValue = function(column, record) {
		if (column.name == "CONTROL")
			return column.html;
		if (column.name == "DL_FILESIZE")
			return getFilesizeStr(record.DL_FILESIZE);
		if (column.name == "LOCKED")
			return record.CHECKED_OUT ? this.table.getLockImg() : "";
		
		if (column.name == "ICON") {
			return "<img src='" + record.SMALLICON_URL + "'>";
		}
		
		if (column.name == "SHARED") {
			if (record.SHARE_LINK_URL)
				return this.table.getSharedImg();
			return "";
		}
		
		if (column.name == "DL_FILENAME")
  		return "<a target='_blank' href='" + record.DOWNLOAD_URL + "'>" + DDColumnsView.superclass.getCellValue.call(this, column, record) + "</a>"; 
  	else
  		return DDColumnsView.superclass.getCellValue.call(this, column, record) ;
  }	
}
extend(DDColumnsView, WbsColumnsView);


var DDListView = function (table, config) {
	config.header = true;
	DDListView.superclass.constructor.call(this, table, config); 
	
	this.getClassName = function() {
		if (this.config.iconType)
			return this.modeName + " " + this.config.iconType;
		return this.modeName;
	}
	
	this.getThumbnailHtml = function (record) {
		return "<div class='thumbnail'><table><tr><td>" + "<img src='" + record.THUMBNAIL_URL + "'>" + "</td></tr></table></div>";
	}
	
	this.buildRecordBlock = function(block, record) {
		var resHTML = "";
		var iconHTML = (this.config.iconType== "bigicons") ? this.getThumbnailHtml(record) : "<img class='icon' src='" + record.ICON_URL + "'>";
		
		resHTML += "<div class='controls'><SAMP class='selector'></SAMP><SAMP class='control-icon'></SAMP></div>";
		resHTML += "<a class='wrap-image' target='_blank' href='" + record.DOWNLOAD_URL + "'>" + iconHTML + "</a>";
		resHTML += "<div class='content'>";
			resHTML += "<div class='name'>" + "<a target='_blank' href='" + record.DOWNLOAD_URL + "'>" + record.DL_FILENAME + "</a>" + "</div>";
			resHTML += "<SAMP id='desc'></SAMP>";
			resHTML += "<div class='small-gray'>" +
				"<span>Type: " + record.DL_FILETYPE + "</span>" +
				"<span>Size: " + getFilesizeStr(record.DL_FILESIZE) + "</span>" + 
			"</div>";
			if  (record.SHARE_LINK_URL) {
				resHTML += "<div class='shared-info addinfo'>";
				resHTML += this.table.getSharedImg();
				resHTML += "<input class='shared-url' onClick='this.select()' value='" + record.SHARE_LINK_URL + "'>";
				resHTML += "</div>";
			}
			if (record.CHECKED_OUT) {
				resHTML += "<div class='checked-info addinfo'>";
				resHTML += this.table.getLockImg(record);
				resHTML += "Checked out by " + record.CHECKED_OUT_INFO;
				resHTML += "</div>";
			}
		resHTML += "</div>";
		
		block.innerHTML = resHTML;
		
		var desc = record.DL_DESC;
		if (!desc)
			desc = "&lt;add description&gt;";
		
		var sampObjects = block.getElementsByTagName("SAMP");
		var descSamp;
		for (var i = 0; i < sampObjects.length; i++)
			if (sampObjects[i].id=="desc") descSamp = sampObjects[i];
		if (descSamp) {
			var descObj = createDiv("desc");
			descObj.valueBlock = createDiv("desc-value");
			descObj.valueBlock.innerHTML = desc.replace(/\n/g, "<BR>");
			descObj.valueBlock.onclick = function() {document.ddApplication.editDescription(descObj)};
			descObj.appendChild(descObj.valueBlock);
			descObj.record = record;
			descSamp.parentNode.replaceChild(descObj, descSamp);
		}
	}
}
extend(DDListView, WbsListView);


var DDTileView = function(table, config) {
	config.header = true;
	DDTileView.superclass.constructor.call(this, table, config); 
	
	this.getThumbnailHtml = function (record) {
		return "<div class='thumbnail'><table><tr><td>" + "<img src='" + record.THUMBNAIL_URL + "'>" + "</td></tr></table></div>";
	}
	
	this.buildRecordBlock = function(block, record) {
		var resHTML = "";
		
		var controlsHTML = "";
		if (record.CHECKED_OUT)
			controlsHTML += this.table.getLockImg(record);
		if  (record.SHARE_LINK_URL)
			controlsHTML += this.table.getSharedImg();
		controlsHTML += getFilesizeStr(record.DL_FILESIZE);
		
		//resHTML += "<input type='checkbox' class='checker'>";
		resHTML += "<br style='display: none'/>"; // IE bugfix
		resHTML += "<a style='margin-bottom: 0px' class='wrap-image' target='_blank' href='" + record.DOWNLOAD_URL + "'>" + this.getThumbnailHtml(record) + "</a>";
		resHTML += "<div class='content' style='margin: 0px'>";
			resHTML += "<div class='name'>" + "<SAMP class='selector'></SAMP><a target='_blank' href='" + record.DOWNLOAD_URL + "'>" + record.DL_FILENAME.truncate(11) + "</a>" + "</div>";
			resHTML += "<div class='controls'><SAMP class='control-icon'></SAMP>" + controlsHTML + "</div>" ;
		resHTML += "</div>";
			
		block.innerHTML = resHTML;
	}
}
extend(DDTileView, WbsTileView);