var PROJECTS_NODE_SPECIAL_STATUS = 11;
var PROJECT_NODE_SPECIAL_STATUS = 2;

function openTrash() {
	var tree = document.ddApplication.tree;
	if(tree.trashNode) {
		var root = tree.getNode("ROOT");
		root.expand();
		tree.selectNode("trash");
		tree.onNodeClick(tree.trashNode);
	}
}

function openFolder (folderId) {
	var app = document.ddApplication;
	if (!folderId || !app.tree.selectNode(folderId)) {
		app.tree.selectNode(app.tree.rootNode.Id);
	}
	if (app.navBar.activeBlock.id != "folders") {
		app.folderLoadStarted = true;
		app.navBar.setActiveBlock("folders");
		app.folderLoadStarted = false;
	}
}

function closeSubframe() {
	document.ddApplication.closeSubframe();
}

//DDApplication
DDApplication = newClass(WbsApplication, {
	constructor: function(config) {
		this.config = config;
		
		this.currentFolder = new DDFolder();
		this.currentFolder.addListener("changed", function(folder) {this.folderChanged();}, this);
		this.currentFolder.addListener("modified", function(folder) {this.folderModified();}, this);
		this.currentFolder.addListener("deleted", function(folder) {this.folderDeleted();}, this);
		//this.layout = new DDApplicationLayout (this);
		
		// Create tree
		this.tree = new DDTree({elemId: "folders-tree"}, this);
		this.tree.loadNodes(document.treeNodes, true);
		this.tree.render();
		
		// Create navigation bar
		this.navBar = new WbsNavBar({id: "DD", saveSize: true,  contentElemId: "nav-bar", expanderElemId: "nav-bar-expander"});	
		this.navBar.addListener("blockActivated", this.navBarBlockActivated, this);
		
		// Create new folder menu button
		this.newFolderBtn = this.createNewFolderBtn();
		
		this.folderStore = this.createFolderStore();
		
		
		// Search panel
		if (!config.projectId) {
			this.searchPanel = new SearchPanel({el: "dd-search-panel", app: this, searchCallback: this.doSearch.bind(this)});
		}
		
		// Page container (for center scrollable part)
		this.container = new WbsFlexContainer ({
			elem: $("main-container"), 
			headerElem: $("main-header"), 
			contentElem: $("main-content")
		});
			
		this.viewSettings = {};			
			
		// Create control panel
		this.controlPanel = new DDControlPanel(this, {});
		this.controlPanel.render();
		
		// Create table
		this.table = new DDTable({id: "dd-table", elem: $("main-content"), store: this.folderStore, pager: true, selection: true, sortingToCookie: true, defaultSorting: {column: "DL_FILENAME", direction: "asc"}});
		this.table.render();
		this.controlPanel.setTableToControl(this.table);
		
		this.superclass().constructor.call(this);
		this.init();
	},
		
	init: function() {
		this.selectFolder(null);
		this.resize();
		
		// init upload flash
		this.getUploadDlg();
	},
		
	resize: function() {
		this.container.resize();
		this.table.container.resize();
	},		
	createFolderStore: function() {
		// Create data reader
		var reader = new WbsReader ({
			url: "ajax/files_list.php",
			baseParams: {},
			recordsProperty: 'files',
			totalProperty: 'total'
		});
			

		reader.addListener("success", function(responseData) {
			this.closeSubframe();
			if (responseData.viewMode)
				responseData.folder.viewMode = responseData.viewMode;
			this.currentFolder.load(responseData.folder);
		}, 
		this);
		
		// If reader is changed params - close uploader panel
		reader.addListener("changeParams", function() {
			var dlg = this.getUploadDlg();
			if (dlg.isVisible() && !dlg.isActive())
				dlg.close();
		}, this);
			
		// Create data store
		var store = new WbsDataStore({reader: reader, idProperty: "DL_ID", recordClass: DDFile});
		
		store.addListener("startLoading", function() {this.startLoading();}, this);
		store.addListener("finishLoading", function() {this.finishLoading();}, this);
		
		return store;
	},
		
	selectFolder: function(folderId, fromTree) {
		if (folderId != null) {
			this.table.resetPage();			
			this.table.resetHighlightWord();
			this.folderStore.setParams({mode: "folder",folderId: folderId});
			this.folderStore.load ();
		} else {
			this.currentFolder.reset();			
		}
	},
		
	doSearch: function(searchString) {
		this.table.resetPage();			
		this.table.setHighlightWord(searchString);
		this.folderStore.setParams({mode: "search", searchString: searchString});
		this.folderStore.load ();
		this.currentFolder.reset();			
	},
		
	getCurrentFolder: function() {
		return this.currentFolder;
	},
		
	folderChanged: function() {
		this.closeSubframe();
		this.controlPanel.folderChanged();
		this.resize();
	},
	
	folderModified: function() {
		this.tree.folderModified();		
		this.controlPanel.folderDataModified();
		this.resize();
	},
	
	folderDeleted: function() {
		this.tree.folderDeleted();
	},		
		
	navBarBlockActivated: function (block) {
		if (block.id == "folders") {
			if (!document.ddApplication.folderLoadStarted)
				this.tree.selectNode(this.tree.getSelectedNode().Id);
			//this.table.reload();
		}
		if (block.id == "search") 
			this.searchPanel.activate();
		if ((block.id == "links" || block.id == "widgets")) {
			var table = this.getWidgetsTable(block);
			if (!block.loaded) {
				table.load();
				table.addListener("afterLoad", function() {
					block.loaded = true;
					table.selectDefault();
				});
			} else {
				table.selectDefault();
			}
		} 
	},
		
	getWidgetsTable: function(block) {
		if (!block.table) {
			var url = (block.id == "links") ? "ajax/tree.shared_files.php" : "../../WG/html/ajax/tree.widgets.php?fapp=DD";
			url = document.ddApplication.getOldUrl(url);
			var table = new WbsWidgetsTable(block.id, block.getContentElem(), url);
			table.render();
			block.table = table;
		}
		return block.table;
	},
		
	selectWidget: function(wgId, navBarBlock) {
		var block = this.navBar.getBlock(navBarBlock);
		var table = this.getWidgetsTable(block);
		
		var handler = function() {
			table.selectWidget(wgId);
			this.removeListener("afterLoad", handler);				
		}	
		
		table.addListener("afterLoad", handler, table);
		table.load();
		block.activate();
		table.resize();
	},
	
		
	openUploadDlg: function() {
		document.ddApplication.getUploadDlg().selectFiles();
	},
	
	getUploadDlg: function () {
		if (this.uploadDlg == null)
			this.uploadDlg = new WbsUploadDlg({
				contentElemId: 'dlg-upload-content',
				cls:"upload-dlg", 
				contentElemId: "dlg-upload-content",
				uploadURL: "http:" + "//" + document.location.host + WbsCommon.getPublishedUrl() + "DD/html/ajax/file_upload.php",
				ieUploadURL: document.location.protocol + "//" + document.location.host + WbsCommon.getPublishedUrl() + "DD/html/ajax/file_upload.php",
				swfURL: "../../common/html/res/swfupload_new/swfupload.swf",
				sessID: document.sessionId
			});
		return this.uploadDlg;
	},
		
	getCreateWidgetDlg: function() {
		if (this.createWidgetDlg == null)
			this.createWidgetDlg = new WbsCreateWidgetDlg ({appId: "DD", url: this.getOldUrl("../../WG/html/ajax/widget_types.php"), createUrl: this.getOldUrl("../../WG/html/ajax/wg_create.php")});
		
		var files = this.table.getSelectedRecords();
		var filesIds = [];
		for (var i = 0; i < files.getCount(); i++)
			filesIds.push(files.getRecord(i).id);
		
		this.createWidgetDlg.setParams({files: filesIds, folder: this.getCurrentFolder().Id, wgName: this.getCurrentFolder().Name});
		
		this.createWidgetDlg.afterTypesShowed = null;
		return this.createWidgetDlg;
	},
		
	openCreateWidgetDlg: function(preselectedSubjectId) {
		var dlg = this.getCreateWidgetDlg();
		dlg.show(preselectedSubjectId);
		dlg.afterWidgetCreate = function(widgetId) {
			this.selectWidget(widgetId, "widgets");
		}.bind(this);
		dlg.afterTypesLoaded = null;
		return dlg;
	},
		
	refreshData: function() {
		this.folderStore.load();
	},
		
	setViewSettings: function(settings) {
		this.viewSettings.extend(settings);
		this.table.pager.setItemsOnPage(this.viewSettings.itemsOnPage);
	},
		
	getViewSettings: function() {
		return this.viewSettings;
	},
		
	createNewFolderBtn: function() {
		var btn = new WbsMenuButton({
			el: "new-folder-btn", 
			label: ddStrings.action_folder_new, 
			getMenu: this.tree.getNewFolderMenu, scope: this.tree}
		);
		return btn;
	}
});




SearchPanel = newClass(WbsObservable, {
	constructor: function(config) {
		this.app = config.app;
		this.config = config;
		this.superclass.constructor.call(this);				
		
		this.elem = (typeof(config.el) == "string") ? $(config.el) : config.el;
		
		this.buildPanel(this.elem);
	},
		
	buildPanel: function(elem) {
		this.textInput = createElem("input", null, {type: "text", size: 20} );
		elem.appendChild(this.textInput);
		if (this.app.config.lastSearchString)
			this.textInput.value = this.app.config.lastSearchString;
		
		this.textInput.onkeydown = (function(e) {
			if (!e && window.event)
				e = window.event;
			if (e && e.keyCode == 13)
				this.trySearch();
		}).bind(this);
		
		this.searchBtn = createElem("input", null, {type: "button", value: commonStrings.action_search} );
		this.searchBtn.onclick = this.trySearch.bind(this);
		elem.appendChild(this.searchBtn);
	},
		
	trySearch: function () {
		if (this.textInput.value == "") {
			alert(ddStrings.message_empty_search_string);
			return false;
		}
		if (this.config.searchCallback)
			this.config.searchCallback(this.textInput.value);
	},
		
	activate: function() {
		if (this.textInput.value != "")
			this.trySearch();
		this.textInput.select();			
	}
});












DDTree = newClass(WbsTree, {
	constructor: function(config, app) {
		this.app = app;
		var config = {elemId: config.elemId, iconCls: "my-folder", rootVisible: true};
		this.superclass().constructor.call(this, config);
		
		this.nodeMap = {
			id : 0,
			text: 1,
			rights: 2, 
			specialStatus: 3,
			children: 4
		}
		
		this.init();
	},
		
	onBeforeNodeSelect: function( selectionModel, newNode, oldNode ) {
		if (newNode.DF_SPECIALSTATUS != PROJECTS_NODE_SPECIAL_STATUS && newNode.Rights && newNode.Rights == 0)
			return false;
		return true;
	},
		
	getNewFolderMenu: function() {
		var node = this.getSelectedNode();
		
		var subfolderDisabled = !WbsRightsMask.canFolder(node.Rights);
		var items = [
			{label: ddStrings.lbl_root_folder, onClick: this.createRootFolder, scope: this, disabled: !this.app.config.canCreateRootFolder},
			{label: ddStrings.lbl_subfolder, onClick: this.createSubFolder, scope: this, disabled: subfolderDisabled}
		];				
		return new WbsPopmenu({items: items});
	},
		
	createRootFolder: function() {
		var node = this.getNode("ROOT");
		this.tryCreateFolder(node);
	},
		
	createSubFolder: function() {
		var node = this.getSelectedNode();
		this.tryCreateFolder(node);
	},
		
	tryCreateFolder: function (parentNode) {
		Ext.Ajax.request ({
				url: this.app.getOldUrl("ajax/folder_create.php"),
				params: {parentId: parentNode.Id},
				success: function (response) {
					var result = Ext.decode(response.responseText);
					if (!result.success) {
						WbsCommon.showError(result);
						return;
					} else {
						var nodeData = {};
						nodeData[this.nodeMap.id] = result.newID;
						nodeData[this.nodeMap.text] = result.name;
						nodeData[this.nodeMap.rights] = 7;
						nodeData[this.nodeMap.specialStatus] = result.specialStatus;
						nodeData[this.nodeMap.children] = null;
						var newNode = this.addNode(nodeData);
						if (parentNode.Id == "ROOT" && this.trashNode) {
							parentNode.insertBefore(newNode, this.trashNode);
						} else {
							parentNode.appendChild(newNode);
						}
						
						var currentFolder = this.app.getCurrentFolder();
						this.selectNode(newNode.Id);
						var showRename = function() {
							currentFolder.showRename();
							currentFolder.removeListener("changed", showRename);
						}						
						currentFolder.addListener("changed", showRename, currentFolder);
					}
				},
				scope: this
		});
	},
		
	folderModified: function() {
		var folder = this.app.currentFolder;
		var node = this.getNode(folder.Id);
		if (node)
			 node.setText(folder.Name);
	},
	
	folderDeleted: function() {
		var folder = this.app.currentFolder;
		var node = this.getNode(folder.Id);
	
		var cur = node.previousSibling || node.parentNode || this.rootNode;
		
		if (node) {
			this.removeNode(node);
		}
		
		if (cur) {
			this.selectNode(cur.Id);
		}		
	},	
		
	addNode: function(nodeData) {
		var addConfig = null;
		if (nodeData[this.nodeMap.rights] == 0 && nodeData[this.nodeMap.id] != "ROOT")
			addConfig = {iconCls: "folder-norights"};
		else if (nodeData[this.nodeMap.rights] == 1)
			addConfig = {iconCls: "folder-readonly"};
		if (nodeData[this.nodeMap.specialStatus] > 0)
			addConfig = {iconCls: "folder-projects"};
					
		var node = this.superclass().addNode.call(this, nodeData, addConfig);
		node.Rights = nodeData[this.nodeMap.rights];
		node.DF_SPECIALSTATUS = nodeData[this.nodeMap.specialStatus];
		return node;
	},
		
	onAfterRender: function() {
		if (this.app.config.ftpFolder && !this.app.config.projectId) {
			var rootNode = this.treePanel.getRootNode();
			this.ftpFolder = rootNode.appendChild(new Ext.tree.TreeNode({id: "ftp", text: ddStrings.ftp_folder, iconCls: "folder-ftp"}));
			this.nodes["ftp"] = this.ftpFolder;
		}
		if (this.app.config.canTools && !this.app.config.projectId) {
			var rootNode = this.treePanel.getRootNode();
			this.trashNode = rootNode.appendChild(new Ext.tree.TreeNode({id: "trash", text: ddStrings.screen_recycle, iconCls: "trash-node"}));
			this.nodes["trash"] = this.trashNode;
		}
	},
	
	onNodeClick: function(node, e) {
		if (node.id == "trash") {
			this.app.openSubframe("scripts/service.php?curScreen=0", true);
			return;
		} else if (node.id == "ftp") {
			this.app.openSubframe("../html/scripts/service.php?actionName=showFtpFolder");
		} else if (node.id == "ROOT") {
			this.app.openSubframe("backend_folders.php");
			return;
		} else if (node.DF_SPECIALSTATUS == PROJECTS_NODE_SPECIAL_STATUS) {
			this.app.openSubframe("backend_projects_files.php");
			return;
		}
		if (node.Rights>0)
			this.app.selectFolder(node.id, true);
	}
});





DDControlPanel = newClass(WbsObservable, {
	constructor: function(app, config) {
		this.app = app;
		this.config = config;
		this.contentEl = document.getElementById("control-panel");
		this.items = [];
		
		// Create title elem
		var titleWrapElem = createDiv("title-wrapper");
		$("folder-title-container").insertBefore(titleWrapElem, $("folder-title-container").firstChild);
		var titleElem = createDiv("title");
		titleWrapElem.appendChild(titleElem);
		this.titleControl = this.app.currentFolder.createTitleControl(titleElem);
		this.addItem(this.titleControl);
		
		this.folderInfoElem = createDiv("folder-info-block");
		$("folder-title-container").appendChild(this.folderInfoElem);
		
		this.folderLinkElem = createDiv("folder-link");
		this.folderInfoElem.appendChild(this.folderLinkElem);
		
		this.folderRightsElem = createDiv("folder-rights");
		this.folderInfoElem.appendChild(this.folderRightsElem);
		
		
		this.viewmodeSelector =  this.createViewmodeSelector ();
		
		// Create buttons
		this.uploadBtn = new WbsButton({
			el: "upload-btn",
			/*onClick: function() {
				this.app.openUploadDlg();	
			}.bind(this),*/
			advIconUrl: "img/upload.gif"
		});
		var flashSpan = createElem("span", null);
		flashSpan.style.display = "block";
		flashSpan.style.float = "left";
		flashSpan.innerHTML = "<span id='btn-upload-span'></span>";
		this.uploadBtn.btnElem.insertBefore(flashSpan, this.uploadBtn.btnElem.firstChild);
		this.selectedActionsBtn = new WbsMenuButton({el: "actions-btn", getMenu: this.getSelectedFilesMenu.bind(this)});
		this.folderActionsBtn = new WbsMenuButton ({el: 'folder-actions-btn', getMenu: this.getFolderMenu.bind(this)});
		
		// Customize view link
		this.viewSettingsBtn = new WbsLinkButton({el: "view-settings-btn", iconUrl: WbsCommon.getPublishedUrl("common/templates/img/customize_view.gif"), onClick: this.showViewSettings.bind(this)});
	},
		
	addItem: function(item) {
		this.items.push(item);		
	},
	
	createViewmodeSelector: function() {
		var viewModeSelector = new WbsViewmodeSelector(null, {elem: $("viewmode-selector-wrapper")});
		
		viewModeSelector.addListener("viewmodeChanged", 
			function(mode) {
				Ext.Ajax.request ({
				url: "ajax/files_set_viewmode.php",
				params: {folderId: this.app.currentFolder.Id, mode: mode},
				success: function (response) {
					var result = Ext.decode(response.responseText);
				}.bind(this)
			});
			},
			this
		);
		
		//var currentMode = getCookie("DD_selectedViewMode");
		//if (!currentMode)
			//currentMode = "columns";
		//viewModeSelector.setMode(currentMode);
		
		this.addItem(viewModeSelector);
		return viewModeSelector;
	},
		
	setTableToControl: function(table) {
		this.viewmodeSelector.setTable(table);
	},
		
	folderDataModified: function() {
		var folder = this.app.currentFolder;
		this.titleControl.setValue(folder.Name);
		if (folder.SHARE_LINK_URL)
			this.folderLinkElem.innerHTML = "<label>" + ddStrings.lbl_link_to_this_folder + ": </label>" + "<input readonly='true' onClick='this.select()' value='" + folder.SHARE_LINK_URL + "'> <a target='_blank' href='" + folder.SHARE_LINK_URL + "'><img title='" + commonStrings.lbl_open_in_new_window + "' src='img/link-go.gif'></a>";
		else {
			this.folderLinkElem.innerHTML = "<label>" + ddStrings.lbl_nolink_to_this_folder + ".</label> ";
			if (WbsRightsMask.canFolder(folder.Rights)) {
				var createLink = createElem("a",null, {href:"javascript:void(0)"});
				createLink.innerHTML = ddStrings.lbl_createlink;
				createLink.onclick = function() {folder.createRemoveLink("create")};
				this.folderLinkElem.appendChild(createLink);
				this.folderLinkElem.appendChild(document.createTextNode("."));
			}
		}
		this.folderRightsElem.innerHTML = "<label>" + commonStrings.lbl_your_access_rights + ":</label> <b>" + WbsRightsMask.getRightsStr(folder.Rights + "</b>");
		this.folderInfoElem.style.visibility = folder.isSearchMode() ? "hidden" : "visible";
	},
		
	folderChanged: function() {
		this.folderDataModified();		
		var folder = this.app.currentFolder;
				
		if (folder.Data && folder.Data.viewMode)
			this.viewmodeSelector.setMode(folder.Data.viewMode);
		
		this.uploadBtn.setDisabled(!folder.canWrite());
		this.uploadBtn.setDisplayed(!folder.isSearchMode() && folder.canWrite());
		this.folderActionsBtn.setDisplayed(!folder.isSearchMode() && WbsRightsMask.canFolder(folder.Rights));
	},
		
	render: function() {
		for (var i = 0; i < this.items.length; i++)
			this.items[i].render();
	},
		
	getSelectedFilesMenu: function() {
		var selectedFiles = this.app.table.getSelectedRecords();
		return selectedFiles.getMenu();
	},
		
	getFolderMenu: function() {
		return this.app.getCurrentFolder().getMenu();
	},
		
	getShareMenu: function() {
		return this.app.getCurrentFolder().getShareMenu();
		//this.getCurrentFolder().getMenu();
	},
		
	showViewSettings: function (e) {
		//if (!this.viewSettingsWindow) {
		//}
		
		var viewSettingsWindow = new DDViewSettingsPopwindow({el: this.viewSettingsBtn.getElem(), closeMode: "hide"});
		viewSettingsWindow.setViewSettings(this.app.getViewSettings());
		viewSettingsWindow.addListener("currentViewChanged", function() {
			this.app.table.pager.setItemsOnPage(this.app.viewSettings.itemsOnPage);
			this.app.table.reload()
		}, this);
		viewSettingsWindow.show(e);
	}
});






var getFilenameStr = function(value) {
	if (value.length < 50)
		return value;
	var parts = value.split(" ");
	for (var i = 0; i < parts.length; i++) 
		if (parts[i].length >= 50 && parts[i].indexOf("-") == -1 && parts[i].indexOf("_") == -1)
			parts[i] = parts[i].truncate(50);
	return parts.join(" ");
}

DDCopyMoveDlg = newClass(WbsDlg, {
	constructor: function(config) {
		var actionBtnLabel = (config.action == "copy") ? WbsLocale.getCommonStr("action_copy") : WbsLocale.getCommonStr("action_move");
		config.buttons = [
			{label: actionBtnLabel, onClick: this.doAction, scope: this},
			{label: WbsLocale.getCommonStr("action_cancel"), onClick: this.hide, scope: this}
		];
		config.hideCloseBtn = true;
		this.mode = config.mode;
		
		this.superclass().constructor.call(this, config);
		
		this.folderSelector = $("copymove-folders-select");
		this.descElem = $("copymove-desc");
		
		this.excludeId = config.excludeId || false;
		this.action = config.action;
		this.actionObject = config.actionObject;
		
		this.descElem.innerHTML = this.config.description ? this.config.description : "";
		
		this.buildFoldersSelect();
	},
		
	doAction: function() {
		var folderId = this.folderSelector.value;
		if (folderId == 0)
		{
			alert(WbsLocale.getCommonStr("message_not_selected_folder"));
			return;
		}
		this.actionObject.doCopyMove(folderId, this.action, this.afterAction.bind(this));
	},
		
	
		
	buildFoldersSelect: function() {
		clearNode(this.folderSelector);
		
		var emptyOption = createElem("option");
		emptyOption.innerHTML = WbsLocale.getCommonStr("lbl_select_folder").htmlSpecialChars();
		emptyOption.value = 0;
		this.folderSelector.appendChild(emptyOption);
		
		this.addFolder(document.ddApplication.tree.rootNode, 0);
		//if (this.config.selectedFolderId)
			//this.folderSelector.value = this.config.selectedFolderId;
	},
		
	addFolder: function(folderData, level) {
		var option = createElem("option");
		var label = "";
		for (var j = 0; j < level; j++)
			label += "&nbsp;";
		label += folderData.Name;
		if (folderData.Id == "ROOT")
			label = WbsLocale.getCommonStr("lbl_desc_root_folder").htmlSpecialChars();
		option.value = folderData.Id;
		option.innerHTML = label.truncate(50);
		
		var minRights = (this.mode == "folder") ? 7 : 3;			
		
		if (folderData.Rights >= minRights || (this.config.rootFolderAvailable && folderData.Id == "ROOT"))
			this.folderSelector.appendChild(option);
		if (folderData.childNodes) {
			for (var i = 0; i < folderData.childNodes.length; i++) {
				if (folderData.childNodes[i].Id != this.excludeId) {
					this.addFolder(folderData.childNodes[i], level+1);
				}
			}
		}
	},
		
	afterAction: function() {
		this.hide();
	}
});

DDFile = newClass (WbsRecord, {
	constructor: function(recordData) {
		this.folder = document.ddApplication.getCurrentFolder();
		this.superclass().constructor.call(this, recordData);
		this.filesizeStr = getFilesizeStr(this.DL_FILESIZE);
	},
	getFields: function() {
		return [
			{name: "DL_FILENAME", convert: getFilenameStr}, 
			{name: "DL_CHECKDATETIME"}, 
			{name: "DL_UPLOADUSERNAME"},
			{name: "DL_FILETYPE"}, 
			{name: "DL_FILESIZE"}, 
			{name: "DL_DESC"},
			{name: "THUMBNAIL_URL"}, 
			{name: "ICON_URL"},
			{name: "SMALLICON_URL"},
			{name: "SHARE_LINK_URL"},
			{name: "CHECKED_OUT"},
			{name: "CHECKED_OUT_INFO"},
			{name: "OPEN_URL"},
			{name: "DOWNLOAD_URL"},
			{name: "ZOHOEDIT_URL"},
			{name: "OWNER_USERNAME"},
			{name: "VERSIONS_COUNT"},
			{name: "DF_ID"},
			{name: "Rights"}
		];
	},
	getLockImg: function() {
		var comment = ddStrings.lbl_locked_by.sprintf(this.CHECKED_OUT_INFO);
		return (this.CHECKED_OUT) ? "<img class='lock-icon' alt='" + comment + "' title='" + comment + "' src='img/lock.gif'>" : "";
	},
	getSharedImg: function() {
		return(this.SHARE_LINK_URL) ?
			"<img title='" + ddStrings.lbl_link_to_file + "' class='shared-icon' src='" + WbsCommon.getPublishedUrl("common/templates/img/shared-link.gif") + "'>" : "";
	},
	showMenu: function(e, type) {
		var menu = new DDFileMenu(this, type);
		menu.show(e);
	},
	editDescription: function(editElem) {
		if (!this.canWrite())
			return false;
		if (!editElem.editor) {
			var editor = new WbsEditableText({elem: editElem, emptyText: ddStrings.lbl_add_description, clickToEdit: true, adjustSize: true});
			editor.setValue(this.DL_DESC);
			editor.setEditMode();
			editElem.editor = editor;
			
			editor.saveHandler = this.changeDesc.bind(this);
		}
	},
				
	createRemoveLink: function(action, callback) {
		if (action == "create") url = "ajax/file_createlink.php"; else if (action == "remove") url = "ajax/file_removelink.php"; else return false;
		Ext.Ajax.request ({
			url: url,
			params: {"id": this.id},
			success: function (response) {
				var result = Ext.decode(response.responseText);
				if (result.errorStr) {
					WbsCommon.showError(result);
				} else {
					this.SHARE_LINK_URL = result.link;
					this.fireEvent("modified", this);
					callback(true);
				}
			}.bind(this)
		});
	},
		
	changeCheckStatusFile: function (status, callback) {
		Ext.Ajax.request ({
			url: "ajax/file_changecheckstatus.php",
			params: {"id": this.id, "status" : status? "checkout" : "checkin"},
			success: function (response) {
				var result = Ext.decode(response.responseText);
				if (result.success) {
					this.CHECKED_OUT = result.CHECKED_OUT;
					this.CHECKED_OUT_INFO = result.CHECKED_OUT_INFO;
					this.fireEvent("modified", this);
					callback(true);
				} else {
					WbsCommon.showError(result);
				}
			}.bind(this)
		});
	},
		
	changeDesc: function(newValue, saveSuccessHandler, saveFailedHandler) {
		Ext.Ajax.request ({
			url: "../html/ajax/file_changedesc.php",
			params: {"id": this.id, description: newValue},
			success: function (response) {
				var result = Ext.decode(response.responseText);
				if (!result.success) {
					WbsCommon.showError(result);
					saveFailedHandler(result.errorStr);
					return;
				}
				this.DL_DESC = newValue;
				if (saveSuccessHandler)
					saveSuccessHandler();
			}, 
			scope: this
		});
	},
		
	canWrite: function() {
		return WbsRightsMask.canWrite(this.Rights);
	},
		
	getDescriptionText: function(trunc) {
		var desc = this.DL_DESC;
		if (desc && trunc && trunc > 0)
			desc = desc.truncate(trunc);
		if (!desc)
			desc = (this.canWrite()) ? ddStrings.lbl_add_description : ddStrings.lbl_empty_description;
		return desc;
	},
		
	showVersionHistory: function() {
		var dlg = new DDFileVersionsDlg({record: this});
		dlg.show();		
	},
	
	showExtractFromZIP: function () {
		var dlg = new DDFileExtractFromZIPDlg({record: this});
		dlg.show();
	}
});

DDFileMenu = newClass (WbsPopmenu, {
	constructor: function(record, type) {
		this.record = record;
		var writeDisabled = !record.canWrite();
		var is_project = document.ddApplication.config.projectId > 0;
		var linkHidden = record.SHARE_LINK_URL ? false : true;
		var checkOutHidden = record.CHECKED_OUT ? true : false;
		var items = [];
		items = items.concat([
			{label: ddStrings.action_open, onClick: function() {window.open(record.OPEN_URL); return false;}},
			{label: ddStrings.action_download, onClick: function() {location.href = record.DOWNLOAD_URL; return false;}}
		]);
		if (!is_project) {
			items.push("-");
			
			var noHide = (type) ? false : true;
			var linkItems = [
				{id: "link_data", hidden: linkHidden, iconCls: 'item-link-data', cls: "nopadding-nolink", html: "<span class='smalllabel'>" +  ddStrings.lbl_link_to_file + "</span><BR /><input readonly='true' onClick='this.select()' class='shared-url' value='" + record.SHARE_LINK_URL + "'><img style='margin-left: 5px; cursor: pointer' title='" + commonStrings.lbl_open_in_new_window + "' src='img/link-go.gif' onClick='window.open(this.previousSibling.value)'>"},
				{id: "link_create", disabled: writeDisabled, hidden: !linkHidden, iconCls: 'item-link-data', label: ddStrings.action_get_link_to_file, onClickNoHide: noHide, onClick: function() {this.record.createRemoveLink("create", this.showHideLink.bind(this))}},
				{id: "link_delete", disabled: writeDisabled, hidden: linkHidden, label: ddStrings.action_remove_link_to_file, onClickNoHide: noHide, onClick: function() {this.record.createRemoveLink("remove", this.showHideLink.bind(this))}}
			];
		} else {
			linkItems = [];
		}
		var checkItems = [
			{id: "check_data", hidden: !checkOutHidden, iconCls: "item-check-out", cls: "nopadding-nolink", html: "<span class='smalllabel'>" + ddStrings.lbl_locked_by.sprintf(this.record.CHECKED_OUT_INFO) +"</span>"},
			{id: "check_out", disabled: writeDisabled, label: ddStrings.action_lock, hidden: checkOutHidden,  iconCls: "item-check-out", onClickNoHide: noHide, onClick: function() {this.record.changeCheckStatusFile(true, this.showHideCheckedStatus.bind(this))}},
			{id: "check_in", disabled: writeDisabled, label: ddStrings.action_unlock, hidden: !checkOutHidden, onClickNoHide: noHide, onClick: function() {this.record.changeCheckStatusFile(false, this.showHideCheckedStatus.bind(this))}},
			{label: ddStrings.action_version_history + ": " + ((this.record.VERSIONS_COUNT > 0) ? this.record.VERSIONS_COUNT : ddStrings.lbl_none_versions), disabled: (this.record.VERSIONS_COUNT == 0), onClick: function() {this.record.showVersionHistory()}}];
			
		items = items.concat(linkItems);
		items.push("-");
		items.push({label: ddStrings.action_send_by_email, onClick: function() {(new DDFilesList([this.record])).showSendDlg()}, iconCls: "item-email"});		
		items.push("-");
		
		items = items.concat(checkItems);
		items.push("-");
		
		var descValue = this.getDescriptionHTML();
		
		items = items.concat([
			{id: "desc_data", cls: "desc_data", html: descValue, disabled: !this.record.canWrite(), onClick: this.showDescEditor, onClickNoHide: true},
			{id: "desc_edit", hidden: true, cls: 'nopadding-nolink', html :"<textarea style='width: 230px; height: 55px'></textarea><BR><input type='button' value='" + commonStrings.action_save + "'> <input type='button' value='" + commonStrings.action_cancel + "'>"},
			"-",
			{label: commonStrings.action_copy, onClick: function() {(new DDFilesList([this.record])).showCopyMoveDlg("copy")}},
			{label: commonStrings.action_move, disabled: writeDisabled, onClick: function() {(new DDFilesList([this.record])).showCopyMoveDlg("move")}},
			{label: commonStrings.action_delete, disabled: writeDisabled, onClick: function() {(new DDFilesList([this.record])).tryDelete()}}
		]);
		
		var types = ["txt", "doc", 'xls', 'ods','odt'];
		//items = items.concat(["-"]);
		if (this.record.ZOHOEDIT_URL && document.ddApplication.config.canZohoEdit && types.indexOf(this.record.DL_FILETYPE.toLowerCase())>=0) {
			if (!document.ddApplication.config.hasZohoKey) {
				items = items.concat([
					"-",
					{label: ddStrings.action_edit_online,	disabled: writeDisabled, onClick: function() {
						tools_tab=document.ddApplication.navBar.getBlock("tools");
						if (typeof tools_tab != "undefined") {
							document.ddApplication.navBar.setActiveBlock("tools");
							document.ddApplication.openSubframe("../html/scripts/service.php?curScreen=5&from=online");
						} else {
							alert(ddStrings.message_zohokey_not_found);
						}
					}}
				]);
			} else {
				items = items.concat([
					"-",
					{label: ddStrings.action_edit_online,	disabled: writeDisabled, onClick: function() {window.open(this.record.ZOHOEDIT_URL)}}
				]);
			}
		}
		if (!document.ddApplication.getCurrentFolder().isSearchMode() && this.record.DL_FILETYPE.toLowerCase() == 'zip') {
			items = items.concat([
			    "-",
			    {label: ddStrings.extract_from_zip, disabled: writeDisabled, onClick: function() {this.record.showExtractFromZIP()}, iconCls: 'item-zip'} 
			]);
		}
		
		if (type == "share")
			items = linkItems;
		if (type == "lock")
			items = checkItems;
				
		this.superclass().constructor.call(this, {items: items, withImages: true});
	},
		
	onAfterShow: function() {
		var descItem = this.getItem("desc_edit");
		if (descItem) {
			this.descTextarea = descItem.getElementsByTagName("textarea")[0];
			this.descTextarea.value = this.record.DL_DESC;
			
			var saveBtn = descItem.getElementsByTagName("input")[0];
			saveBtn.onclick = (function() {this.saveDescChanges(this.hideDescEditor.bind(this));}).bind(this);
			
			var cancelBtn = descItem.getElementsByTagName("input")[1];
			cancelBtn.onclick = this.cancelDescChanges.bind(this);
		}
	},
		
	saveDescChanges: function(callback) {
		if (this.descTextarea && this.descTextarea.value != this.record.DL_DESC)
			this.record.changeDesc(this.descTextarea.value, function() {this.record.fireEvent("modified", this.record); if (callback) callback();}.bind(this));
	},
		
	cancelDescChanges: function() {
		this.descTextarea.value = this.record.DL_DESC;
		this.hideDescEditor();
	},
		
	onClose: function() {
		this.saveDescChanges();
	},
		
	showDescEditor: function() {
		this.hideItem("desc_data");
		this.showItem("desc_edit");
		this.descTextarea.select();
	},
		
	getDescriptionHTML: function() {
		if (this.record.DL_DESC)
			return ddStrings.lbl_file_description + ":<div class='desc_content'>" + this.record.getDescriptionText(60).replace(/\n/g, " ").htmlSpecialChars()+ "</div>";
		else
			return this.record.getDescriptionText(60).htmlSpecialChars();
	},
		
	hideDescEditor: function() {
		this.getItem("desc_data").innerHTML = this.getDescriptionHTML();
		this.showItem("desc_data");
		this.hideItem("desc_edit");
	},
		
	showHideLink: function () {
		this.setItemShareLinkUrl(this.record.SHARE_LINK_URL);
		if (this.record.SHARE_LINK_URL) {
			this.showItem("link_data");
			this.showItem("link_delete");
			this.hideItem("link_create");
		} else {
			this.hideItem("link_data");
			this.hideItem("link_delete");
			this.showItem("link_create");
		}
	},
	
	showHideCheckedStatus: function() {
		this.setCheckoutData(this.record.CHECKED_OUT_INFO);
		if (this.record.CHECKED_OUT) {
			this.showItem("check_data");
			this.showItem("check_in");
			this.hideItem("check_out");
		} else {
			this.hideItem("check_data");
			this.showItem("check_out");
			this.hideItem("check_in");
		}
	},
		
	setItemShareLinkUrl: function (url) {
		var textarea = this.items["link_data"].getElementsByTagName("input")[0];
		textarea.value = url;
	},
		
	setCheckoutData: function (data) {
		this.getItem("check_data").setText("<span class='smalllabel'>" + ddStrings.lbl_locked_by.sprintf(data) + "</span>");
	}
});



DDFilesList = newClass (WbsRecordset, {
	constructor: function(records){
		this.superclass().constructor.call(this, records);
		this.app = document.ddApplication;
	},
	
	getMenu: function() {
		return new DDFilesListMenu(this);
	},
		
	showCopyDlg: function() {
		this.showCopyMoveDlg("copy");		
	},
		
	showMoveDlg: function() {
		this.showCopyMoveDlg("move");
	},
		
	refreshCallback: function() {
		this.app.refreshData();
	},
		
	tryDelete: function() {
		if (!confirm(ddStrings.message_confirm_delete_files))
			return false;
		
		Ext.Ajax.request ({
			url: this.app.getOldUrl("ajax/files_delete.php"),
			params: {"documents[]": this.getIds()},
			success: function (response) {
				var result = Ext.decode(response.responseText);
				if (result.success) {
					this.refreshCallback();
				} else {
					WbsCommon.showError(result);
				}		
			}.bind(this)
		});
	},
	
	showCompressZIP: function() {
		var dlg = new DDCompressZIPDlg({addMode: 0, folderId: this.app.getCurrentFolder().Id, filesList: this});
		dlg.show();
	},
		
	showCopyMoveDlg: function(action) {
		var title = (action == "copy") ? ddStrings.title_copy_files : ddStrings.title_move_files;
		var description = (this.getCount() > 1) ? ddStrings.lbl_n_files.sprintf(this.getCount()) : ddStrings.lbl_file + ": " + this.getRecord(0).DL_FILENAME;
		var dlg = new DDCopyMoveDlg({mode: "records", selectedFolderId: this.app.getCurrentFolder().Id,action: action, actionObject: this, contentElemId: "dlg-copymove-content", title: title, description: description});
		dlg.show();
	},
		
	doCopyMove: function(folderId, action, callback) {
		if (action != "copy" && action != "move") 
			throw "Error action for doCopyMove: " + action;
		
		Ext.Ajax.request ({
			url: this.app.getOldUrl("ajax/files_copymove.php"),
			params: {folderId: folderId, action: action, "documents[]": this.getIds()},
			success: function (response) {
				var result = Ext.decode(response.responseText);
				if (result.success) {
					if (callback)
						callback();
					this.refreshCallback();
				} else {
					WbsCommon.showError(result);
				}		
			}.bind(this)
		});
	},
		
	showSendDlg: function() {
		var dlg = new DDFilesSendDlg ({filesList: this});
		dlg.show();
	},
		
	sendByEmail: function(sendTo, subject, message, needCompress, callback) {
		Ext.Ajax.request ({
			url: this.app.getOldUrl("ajax/send_files.php"),
			params: {"filesIds[]": this.getIds(), needComress: needCompress, "sendData[subject]": subject, "sendData[message]": message, "sendData[to]": sendTo},
			success: function (response) {
				var result = Ext.decode(response.responseText);
				if (result.success) {
					alert(result.resultStr);
					if (callback)
						callback();
				} else {
					WbsCommon.showError(result);
				}		
			},
			scope: this
		});		
	},
		
	getRights: function() {
		return this.Rights;//document.ddApplication.getCurrentFolder().getRights();
	},
		
	showCreateSmartlinkDlg: function() {
		var dlg = document.ddApplication.getCreateWidgetDlg();
		
		dlg.afterTypesShowed = function() {
			dlg.createWidget({type:"DDList", subtype: "Link"});
			dlg.afterTypesShowed = false;
		}
		dlg.afterWidgetCreate = function(widgetId) {
			document.ddApplication.selectWidget(widgetId, "links");
		}.bind(this);
		dlg.show("files");
	},
		
	showWidgetDlg: function() {
		var dlg = document.ddApplication.openCreateWidgetDlg("files");
	}
});


DDFilesListMenu = newClass(WbsPopmenu, {
	constructor: function(recordsList) {
		this.recordsList = recordsList;
		var config = {};
		
		var is_project = document.ddApplication.config.projectId > 0;
		var writeDisabled = !this.recordsList.canWrite();
		var noRecords = this.recordsList.isEmpty();
		
		config.items = [
			{label: ddStrings.selected_files_count.sprintf(recordsList.getCount()), cls: "unactive"}
		];
		if (!is_project) {
			config.items = config.items.concat([
			{label: ddStrings.action_create_link_to_files, iconCls: 'item-link-data', disabled: noRecords, onClick: recordsList.showCreateSmartlinkDlg, scope: recordsList},
			{label: ddStrings.action_create_widget, disabled: noRecords, onClick: recordsList.showWidgetDlg, scope: recordsList, iconCls: 'item-widget'}
			]);
		}
		config.items = config.items.concat([
			"-",
			{label: ddStrings.action_send_by_email, disabled: noRecords, onClick: recordsList.showSendDlg, scope: recordsList, iconCls: "item-email"},
			"-",
			{label: commonStrings.action_copy, disabled: noRecords, onClick: recordsList.showCopyDlg, scope: recordsList},
			{label: commonStrings.action_move, disabled: writeDisabled || noRecords, onClick: recordsList.showMoveDlg, scope: recordsList},
			{label: commonStrings.action_delete, disabled: writeDisabled || noRecords, onClick: recordsList.tryDelete, scope: recordsList}
		]);
		if (!document.ddApplication.getCurrentFolder().isSearchMode()) {
			config.items = config.items.concat([
			"-",
			{label: ddStrings.compress_as_zip_and_save, disabled: writeDisabled || noRecords, onClick: recordsList.showCompressZIP, scope: recordsList, iconCls: 'item-zip'}
			]);
		}
		config.withImages = true;
		
		this.superclass().constructor.call(this, config);
	}
});

DDFilesSendDlg = newClass(WbsDlg, {
	
	constructor: function(config) {
		
		this.filesList = config.filesList;
		var filesList = this.filesList;
		
		config.cls = "files-send-dlg";
		config.title = ddStrings.title_send_by_email;
		config.height = 385;
		config.width = 500;
		config.buttons = [
			{label: ddStrings.action_send, onClick: this.trySend, scope: this},
			{label: commonStrings.action_cancel, onClick: this.close,scope: this}
		];
		config.closeMode = "close";
		config.hideCloseBtn = true;
		
		var defaultSubject = filesList.getRecord(0).DL_FILENAME;
		if (filesList.getCount() > 1)
			defaultSubject += " " + ddStrings.lbl_and_files.sprintf(filesList.getCount()-1);
		this.defaultSubject = defaultSubject;
		this.defaultMessage = ddStrings.text_enter_message;
		
		this.totalFilesSize = 0;
		for (var i = 0; i < filesList.getCount(); i++) {
			this.totalFilesSize += parseInt(filesList.getRecord(i).DL_FILESIZE);
		}
		
		this.superclass().constructor.call(this, config);
	},
		
	trySend: function() {
		var subject = this.subjectInput.value;
		var sendTo = this.sendToInput.value;
		
		var compress = false;
		this.filesList.sendByEmail(sendTo, subject, this.messageInput.getValue(), compress, this.close.bind(this));
		
	},
		
	onAfterShow: function() {
		this.sendToInput = $("filessend-send-to-input");
		this.subjectInput = $("filessend-subject-input");
		this.messageInput = $("filessend-message-input");
		this.filesText = $("filessend-files-text");
		//this.compressCheckbox = $("filessend-need-compress");
		
		this.subjectInput.value = this.defaultSubject;
		this.messageInput.value = this.defaultMessage;
		this.messageInput.defaultValue = this.defaultMessage;
		this.filesText.innerHTML = ddStrings.lbl_send_files_attachments.sprintf(this.filesList.getCount(), getFilesizeStr(this.totalFilesSize));
		
		this.messageInput.onfocus = function() {
			if (this.value == this.defaultValue)
				this.value = "";
		}
		
		this.messageInput.onblur = function() {
			if (this.value == "")
				this.value = this.defaultValue;
		}
		
		this.messageInput.getValue = function() {
			if (this.value == this.defaultValue) return "";
			return this.value;
		}
	},
		
	buildContent: function (contentElem) {
		html = "";
		html += "<div class='field send-to-field'>" + ddStrings.field_send_to + ":<BR><textarea id='filessend-send-to-input'></textarea><div class='note'>" + ddStrings.field_send_to_comment + "</div></div>";
		html += "<div class='field subject-field'>" + ddStrings.field_subject + ":<BR><input type='text' id='filessend-subject-input'></div>";
		html += "<div class='field message-field'>" + ddStrings.field_message +":<BR><textarea id='filessend-message-input'></textarea></div>";
		html += "<div class='field'>" + ddStrings.lbl_attachments + ": <span id='filessend-files-text'></span></div>";
		
		contentElem.innerHTML = html;		
	}
});

var DDCompressZIPDlg = newClass(WbsDlg, {
	constructor: function(config) {
		this.folderId = config.folderId;
		this.addMode = config.addMode;
		this.filesList = config.filesList;
		config.width = 300;
		config.height = 150;
		config.title = ddStrings.compress_as_zip_and_save;
		config.buttons = [
		    {label: commonStrings.action_save, onClick: this.compress, scope: this},		                  
		    {label: commonStrings.action_close, onClick: this.hide, scope: this}
		];	
		config.closeMode = "close";
		config.hideCloseBtn = true;
		
		this.superclass().constructor.call(this, config);		
	},
	
	compress: function() {
		if (this.filesList) {
			var documents = this.filesList.getIds();
		} else {
			var documents = "";
		}
		this.getContentElem().innerHTML += "<br />" + ddStrings.wait + '<div class="wbs-loading-icon">&nbsp;</div>';
		Ext.Ajax.request ({
			url: document.ddApplication.getOldUrl("ajax/zip_compress.php"),
			params: {"addMode":this.addMode, "folderId":this.folderId, "fileName": this.filename.value, "documents[]": documents},
			success: function (response) {
				var result = Ext.decode(response.responseText);
				if (result.success) {
					document.ddApplication.refreshData();
					this.hide();
				} else {
					var elem = this.getContentElem();
					elem.innerHTML = result.errorStr;
				}		
			},
			scope: this
		});		
	},
	onAfterShow: function() {
		if (this.addMode == 1) {
			this.filename.value = document.ddApplication.tree.getNode(this.folderId).Name;
			this.filename.select();
		}
		this.filename.focus();
	},
	
	buildContent: function (contentElem) {
		html = "";
		html = ddStrings.column_filename + ": ";
		contentElem.innerHTML = html;
		this.filename = createElem("input");
		this.filename.type = "text";
		contentElem.appendChild(this.filename);
	}	
});

var DDFileExtractFromZIPDlg = newClass(WbsDlg, {
	constructor: function(config) {
		this.record = config.record;
		
		config.width = 300;
		config.height = 150;
		config.title = ddStrings.extract_from_zip;
		
		config.buttons = [
			{label: commonStrings.action_close, onClick: this.hide, scope: this}
		];
		config.closeMode = "close";
		config.hideCloseBtn = true;
		
		this.superclass().constructor.call(this, config);
	},
	
	onAfterShow: function() {
		
		Ext.Ajax.request ({
			url: document.ddApplication.getOldUrl("ajax/zip_extract.php"),
			params: {"DL_ID": this.record.id },
			success: function (response) {
				var result = Ext.decode(response.responseText);
				if (result.success) {
					location.href = location.href;
				} else {
					var elem = this.getContentElem();
					elem.innerHTML = result.errorStr;
				}		
			},
			scope: this
		});		
	},
	
	buildContent: function(contentElem) {
		html = "";
		html += ddStrings.lbl_file + ": " + this.record.DL_FILENAME + "<br />";
		html += ddStrings.wait + '<br />';
		html += '<div class="wbs-loading-icon">&nbsp;</div>';
		contentElem.innerHTML = html;	
	}


});

var DDFileVersionsDlg = newClass(WbsDlg, {
	constructor: function(config) {
		this.record = config.record;
		
		config.width = 500;
		config.title = ddStrings.title_version_history;
		config.cls = "versions-dlg";
		
		config.buttons = [
			{label: ddStrings.action_delete_selected, onClick: this.deleteSelectedVersions, scope: this},
			{label: commonStrings.action_close, onClick: this.hide, scope: this}
		];
		config.hideCloseBtn = true;
		
		this.superclass().constructor.call(this, config);
	},
		
	onAfterShow: function() {
		this.table.reload();
		this.table.resize();		
	},
		
	buildContent: function(contentElem) {
		contentElem.appendChild(createTextSpan(ddStrings.lbl_file + ": " + this.record.DL_FILENAME));
		
		var table = new DDFileVersionTable (contentElem, this.record);
		table.render();
		this.table = table;
	},
		
	deleteSelectedVersions: function() {
		var selectedRecords = this.table.getSelectedRecords();
		if (selectedRecords.getCount() < 1) {
			alert(ddStrings.message_notselected_versions);
			return;
		}
		
		if (!confirm(ddStrings.message_delete_versions_confirm)) 
			return;
		
		var versionsIds = [];
		for (var i = 0; i < selectedRecords.getCount(); i++) {
			versionsIds.push(selectedRecords.getRecord(i).id);	
		}
		
		Ext.Ajax.request ({
			url: document.ddApplication.getOldUrl("ajax/file_deleteversions.php"),
			params: {"DL_ID" : this.record.id,  "versions[]": versionsIds},
			success: function (response) {
				var result = Ext.decode(response.responseText);
				if (result.success) {
					this.table.reload();
				} else {
					WbsCommon.showError(result);
				}
			},
			scope: this
		});
	}
});


DDFileVersionTable = newClass(WbsTable, {
	constructor: function(elem, file) {
		this.file = file;
		
		var reader = new WbsReader ({url: "ajax/file_versions.php"});
		var store = new WbsDataStore({reader: reader, idProperty: "DLH_VERSION", recordClass: DDFileVersion});
		
		store.setParams({"id" : this.file.id});
		
		config = {id: "versions-table", elem: elem, store: store, pager: false, selection: true};
		
		config.columns = [
			/*{name: "DLH_VERSION", width: "20"},*/
			{name: "DESC"}
		];		
		
		config.autoHeight = false;
		
		this.superclass().constructor.call(this,config);
		this.setView(new DDFileVersionsView(this, {header: false}))
	}
});

DDFileVersionsView = newClass (WbsColumnsView, {
	getCellValue: function(column, record) {
		if (column.name == "DESC") {
			var link = document.ddApplication.getOldUrl("scripts/" + record.DOWNLOAD_URL);
			return "<a target='_blank' href='" + link + "'>" + ddStrings.lbl_version_info.sprintf(record.DLH_SIZE, record.DLH_USERNAME, record.DLH_DATETIME) + "</a>";
		}
		return record[column.name];
		//return this.superclass().getCellValue.call(this,column, record);
  }	
});
	
	
DDFileVersion = newClass(WbsRecord, {
	getFields: function() {
		return [{name: "DLH_VERSION"}, {name: "DLH_SIZE", convert: getFilesizeStr}, {name: "DLH_USERNAME"}, {name: "DLH_DATETIME"}, {name: "DOWNLOAD_URL"}];
	}
});

DDFolder = newClass(WbsObservable, {
	constructor: function() {
		
		this.addEvents({"changed" : true, "modified" : true});
		
		this.reset();
	},
		
	reset: function() {
		this.Name = "Loading folder...";
		this.Id = null;
		this.Rights = 0;
		this.SHARE_LINK_URL = null;
		this.fireEvent("changed", this);
	},
	
	load: function(data) {
		var oldId = this.Id;
		this.Data = data;
		this.Name = data.NAME;
		this.Id = data.ID;
		this.Rights = data.RIGHTS;
		this.SHARE_LINK_URL = data.SHARE_LINK_URL;
		this.DF_SPECIALSTATUS = data.DF_SPECIALSTATUS;
		if (oldId != this.Id) {
			this.titleControl.setViewMode();
			this.fireEvent("changed", this);
		}
		if (WbsRightsMask.canFolder(this.Rights) && this.DF_SPECIALSTATUS != PROJECT_NODE_SPECIAL_STATUS) {
			this.titleControl.config.clickToEdit = true;
		} else {
			this.titleControl.config.clickToEdit = false;
		}
	},
		
	isSearchMode: function() {
		return this.Id == "Search";
	},
		
	rename: function (newValue, onSuccess, onFail) {
		Ext.Ajax.request ({
			url: document.ddApplication.getOldUrl("ajax/folder_rename.php"),
			params: {folderID: this.Id, newName: newValue},
			success: function (response) {
				var result = Ext.decode(response.responseText);
				if (result.success) {
					this.Name = newValue;
					document.ddApplication.tree.getNode(this.Id).Name = newValue;
					if (onSuccess) onSuccess();
					this.fireEvent("modified", this);
				} else {
					WbsCommon.showError(result);
					if (onFail) onFail();
				}		
			}.bind(this)
		});	
	}, 
		
	getMenu: function() {
		return new DDFolderMenu({folder: this});
	},
		
	refreshCallback: function() {
		location.href = location.href;
	},
		
	doCopyMove: function(folderId, action, callback) {
		if (action != "copy" && action != "move") 
			throw "Error action for doCopyMove: " + action;
		
		Ext.Ajax.request ({
			url: document.ddApplication.getOldUrl("ajax/folder_move.php"),
			params: {action: action, folderID: this.Id, parentFolderID: folderId},
			success: function (response) {
				var result = Ext.decode(response.responseText);
				if (result.success) {
					if (callback)
						callback();
					this.refreshCallback();
				} else {
					WbsCommon.showError(result);
				}		
			}.bind(this)
		});		
	},
		
	compressZip: function() {
		var dlg = new DDCompressZIPDlg({addMode:1, folderId: this.Id});
		dlg.show();
	},
	tryDelete: function() {
		if (!confirm(commonStrings.message_confirm_folder_delete))
			return false;
		
		Ext.Ajax.request ({
			url: document.ddApplication.getOldUrl("ajax/folder_delete.php"),
			params: {folderId: this.Id},
			success: function (response) {
				var result = Ext.decode(response.responseText);
				if (result.success) {
					this.fireEvent("deleted", this);
					//this.refreshCallback();
				} else {
					WbsCommon.showError(result);
				}		
			}.bind(this)
		});
	},
		
	showCopyMoveDlg: function(action) {
		var title = (action == "copy") ? commonStrings.title_copy_folder : commonStrings.title_move_folder;
		var description = WbsLocale.getCommonStr("lbl_folder") + ": " + this.Name;
		var rootFolderAvailable = document.ddApplication.config.canCreateRootFolder;
		var dlg = new DDCopyMoveDlg({mode: "folder", rootFolderAvailable: rootFolderAvailable, selectedFolderId: this.Id,action: action, actionObject: this, contentElemId: "dlg-copymove-content", title: title, description: description, excludeId: this.Id});
		dlg.show();
	},
		
	getRights: function() {
		return this.Rights;
	},
	
	canWrite: function() {
		return WbsRightsMask.canWrite(this.Rights);
	},
		
	createRemoveLink: function(action, callback) {
		Ext.Ajax.request ({
			url: "ajax/folder_createremovelink.php",
			params: {"id": this.Id, action: action},
			success: function (response) {
				var result = Ext.decode(response.responseText);
				if (result.success) {
					this.SHARE_LINK_URL = (action == "create") ? result.link : null;
				} else
					WbsCommon.showError(result);
				if (callback) 
					callback();
				this.fireEvent("modified", this);
			},
			scope: this
		});
	},
		
	showRename: function() {
		this.titleControl.setEditMode();
	},
		
	createTitleControl: function(elem) {
		var titleControl = new WbsEditableLabel({elem: elem, clickToEdit: false});
		titleControl.addListener("changeMode", function() {
			document.ddApplication.resize();
		}, this);
		
	  titleControl.saveHandler = function(newValue, saveSuccessHandler, saveFailedHandler) {
			this.rename(newValue, saveSuccessHandler, saveFailedHandler);
		}.bind(this);
		this.titleControl = titleControl;
	  return titleControl;
	},
		
	showCreateWidgetDlg: function() {
		document.ddApplication.openCreateWidgetDlg("folder");
	}
});

DDFolderMenu = newClass (WbsPopmenu, {
	constructor: function(config) {
		this.folder = config.folder;
		
		var disabledFolder = !WbsRightsMask.canFolder(this.folder.Rights);
		var linkHidden = !this.folder.SHARE_LINK_URL;
		config.withImages = true;
		
		config.items = [];
		
		
		var shareItems = [
			{id: "link_create", label: ddStrings.action_link_create, disabled: disabledFolder, onClickNoHide: true, onClick: (function() {this.folder.createRemoveLink("create", this.showHideLink.bind(this))}).bind(this) },
			{id: "link_data", hidden: linkHidden, iconCls: 'item-link-data', cls: "nopadding-nolink", html: "<span class='smalllabel'>" + ddStrings.lbl_link_to_this_folder + "</span><BR><input readonly='true' onClick='this.select()' class='shared-url' value='" + this.folder.SHARE_LINK_URL + "'><img style='margin-left: 5px; cursor: pointer' title='" + commonStrings.lbl_open_in_new_window + "' src='img/link-go.gif' onClick='window.open(this.previousSibling.value)'>"},
			{id: "link_delete", label: ddStrings.action_link_delete, disabled: disabledFolder, onClickNoHide: true, onClick: (function() {this.folder.createRemoveLink("remove", this.showHideLink.bind(this))}).bind(this) }
		];
		if (!document.ddApplication.config.projectId) {
			shareItems.push({label: ddStrings.action_create_widget, disabled: disabledFolder, onClick: this.folder.showCreateWidgetDlg, scope: this.folder, iconCls: 'item-widget'});
		}
		
		if (document.ddApplication.config.canManageUsers && this.folder.DF_SPECIALSTATUS == 0) {
			shareItems = shareItems.concat([
				"-",
				{label: ddStrings.action_customize_rights, disabled: disabledFolder, onClick: function() { 
					var app = document.ddApplication;
					app.openSubframe("scripts/addmodfolder.php?action=edit&DF_ID=" + this.folder.Data.ENC_ID, true);
				}}
			]);
		}
		
		config.items = config.items.concat(shareItems);
		config.items.push(["-"]);

		config.items = config.items.concat([
			{label: commonStrings.action_rename, disabled: (!this.folder.canWrite() || this.folder.DF_SPECIALSTATUS == PROJECT_NODE_SPECIAL_STATUS), onClick: this.folder.showRename.bind(this.folder)}, 
			"-",
			{label: commonStrings.action_copy, disabled: (this.folder.DF_SPECIALSTATUS == PROJECT_NODE_SPECIAL_STATUS), onClick: function() {this.folder.showCopyMoveDlg("copy")}.bind(this) },
			{label: commonStrings.action_move, disabled: (!this.folder.canWrite() || this.folder.DF_SPECIALSTATUS == PROJECT_NODE_SPECIAL_STATUS), onClick: function() {this.folder.showCopyMoveDlg("move")}.bind(this)},
			{label: commonStrings.action_delete, disabled: !this.folder.canWrite(), onClick: this.folder.tryDelete.bind(this.folder)},
			"-",
			{label: ddStrings.compress_as_zip_and_save, disabled: !this.folder.canWrite(), onClick: this.folder.compressZip.bind(this.folder), iconCls: 'item-zip'}
	
		]);
			
		/*if (!this.folder.isSearchMode() && this.folder.canWrite())
			config.items = folderItems.concat(config.items);*/		
		
		this.superclass().constructor.call(this, config);
	},
		
	onAfterShow: function() {
		this.showHideLink();
	},
		
	setItemShareLinkUrl: function (url) {
		var textarea = this.items["link_data"].getElementsByTagName("input")[0];
		textarea.value = url;
	},
		
	showHideLink: function() {
		this.setItemShareLinkUrl(this.folder.SHARE_LINK_URL);
		if (this.folder.SHARE_LINK_URL) {
			this.showItem("link_data");
			this.showItem("link_delete");
			this.hideItem("link_create");
		} else {
			this.hideItem("link_data");
			this.hideItem("link_delete");
			this.showItem("link_create");
		}
	}	
});

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

function WbsUploadDlg (config) {
	//WbsUploadDlg.superclass.constructor.call(this, config);
	
	this.config = config;
	WbsUploadDlg.STATE_START = "START";
	WbsUploadDlg.STATE_PROCESS = "PROCESS";
	WbsUploadDlg.STATE_COMPLETED = "COMPLETED";
	WbsUploadDlg.STATE_LIMIT_ERROR = "LIMIT_ERROR";
	
	this.isVisible = false;
	this.dialogContentId = config.contentElemId;
	this.strings = new Array ();
	this.state = "start";
	this.uploader = null;
	this.completeFilesCount = 0;
	this.errorFilesCount = 0;
	this.startQueueFilesCount = 0;
	this.fullErrorMessage = null;
	this.currentFileNum = 0;
	this.cancelByError = false;
	
	this.selectFiles = function() {
		var uploader = this.getUploader();
		uploader.selectFiles ();
	}
	
	this.isVisible = function() {
		return (document.getElementById("frm-upload-block").style.display != "none");
	}
	
	this.isActive = function() {
		return (this.state == WbsUploadDlg.STATE_PROCESS);
	}
	
	this.close = function() {
		this.resetState();
		document.getElementById("frm-upload-block").style.display = "none";
		document.ddApplication.resize();
		this.displayCurrentState();
	}
	
	this.getUploader = function() {
		if (!this.uploader) {
			this.uploader = this.flashInit();
		}
		return this.uploader;
	}
	
	var dlg = this;
	this.init = function() {
		this.getUploader();
		this.btnStart = Ext.getDom("btnStart");
		this.btnCancel = Ext.getDom("btnCancel");
		
		this.btnStart.onclick = function() {
			if (this.value == "Cancel") {
				dlg.pressCancel.call(dlg);
			} else {
				dlg.startQueue.call(dlg);
			}
		}
		this.btnStart.setDisabled = function(value) {
			//this.style.display =  (value) ? "none" : "block";
				
			this.disabled = value;
		}
		
		this.btnStart.setValue = function(value) {
			//this.style.display = this.setDisabled((value == "Cancel"));
			
			//this.value = value;
		}
		
		this.btnCancel.onclick = function() {
			dlg.pressCancel.call(dlg);
		}
		this.btnCancel.setDisabled = function(value) {
			this.disabled = value;
		}
		this.btnCancel.setDisplayed = function(value) {
			this.style.display = value ? "inline" : "none";
		}
		
		if (this.onAfterInit)
			this.onAfterInit();
	}
	
	this.pressCancel = function() {
		if (this.state == WbsUploadDlg.STATE_PROCESS) {
			this.state = WbsUploadDlg.STATE_COMPLETED;
			this.cancelQueue ();
			document.ddApplication.refreshData();
		} else {
			this.cancelQueue ();
			this.displayCurrentState ();
			this.close();
		}
	}
	
	this.flashInit = function () {
		
		var fileTypes = this.fileTypes ? this.fileTypes : "*.*";
		var fileTypesDesc = this.config.fileTypesDesc ? this.config.fileTypesDesc : "All Files";
		
		var uploadURL = this.config.uploadURL;
		if (Ext.isIE && this.config.ieUploadURL != null)
			uploadURL = this.config.ieUploadURL;
		
		var uploader = new SWFUpload({
			// Backend Settings
			upload_url: uploadURL ,
			
			// File Upload Settings
			file_size_limit : "204800",	// 200MB
			file_types : fileTypes,
			file_types_description : fileTypesDesc,
			file_upload_limit : "0",
			file_queue_limit : "0",

			// Event Handler Settings (all my handlers are in the Handler.js file)
			file_dialog_start_handler : fileDialogStart,
			file_queued_handler : fileQueued,
			file_queue_error_handler : fileQueueError,
			file_dialog_complete_handler : fileDialogComplete,
			upload_start_handler : uploadStart,
			upload_progress_handler : uploadProgress,
			upload_error_handler : uploadError,
			upload_complete_handler : uploadComplete,
			file_complete_handler : fileComplete,
				
			button_placeholder_id: "btn-upload-span",
			button_width: ddStrings.btn_upload_files.length * 8 + 20,
			button_height: 23,
			/*button_text: "<span class='theFont'>" + ddStrings.btn_upload_files + "</span>",
			button_text_style: ".theFont {cursor: pointer; font-size: 14pt; font-family: \"Trebuchet MS\"; color: #0043A7;}",*/
			button_text: " ",
			button_text_top_padding: 2,
			button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
			button_cursor: SWFUpload.CURSOR.HAND,
				
				
			// Flash Settings
			flash_url : this.config.swfURL,

			// UI Settings
			ui_container_id : "flashUI1",
			degraded_container_id : "degradedUI1",

			// Debug Settings
			debug: false
		});
			
		uploader.strings = new Array ();
		uploader.parentDialog = this;
		uploader.customSettings.progressTarget = "fsUploadProgress1";	// Add an additional setting that will later be used by the handler.
			
		uploader.afterFilesSelected = function() {
			if (dlg.getQueueFilesCount () == 0)
				return;
			
			this.parentDialog.completeFilesCount = 0;
			this.parentDialog.state = WbsUploadDlg.STATE_START;
			this.parentDialog.displayCurrentState ();
			document.getElementById("frm-upload-block").style.display = "block";
			document.ddApplication.resize();
			dlg.startQueue.call(dlg);
		}	
		
		uploader.afterCompleted = function () {
			var parentDlg = this.parentDialog;
			window.setTimeout (function () {
				document.ddApplication.refreshData();
				if (parentDlg.state != WbsUploadDlg.STATE_LIMIT_ERROR)
					parentDlg.state = WbsUploadDlg.STATE_COMPLETED;
				parentDlg.displayCurrentState ();
				parentDlg.resetState ();
			}, 1);
		}
		
		uploader.afterFileComplete = function (fileObj) {
			this.parentDialog.completeFilesCount++;
			this.parentDialog.upgradeFilesCount ();
			this.parentDialog.displayCurrentState();
		}
		
		uploader.afterFileError = function (fileObj, error_code, message) {
			var parentDlg = this.parentDialog;
			parentDlg.errorFilesCount++;
			parentDlg.completeFilesCount--;
			
			// Limit error occurred
			if (!parentDlg.cancelByError && error_code == SWFUpload.UPLOAD_ERROR.HTTP_ERROR && parentDlg.fullMessages[message] != null) {
				parentDlg.state = WbsUploadDlg.STATE_LIMIT_ERROR;
				parentDlg.fullErrorMessage = parentDlg.fullMessages[message];
				parentDlg.errorFilesCount += this.getStats().files_queued;
				parentDlg.cancelByError = true;
				parentDlg.cancelQueue ();
			}
			this.parentDialog.upgradeFilesCount ();
			parentDlg.displayCurrentState();
		}
		
		uploader.afterFileCancelled = function () {
			if (!this.parentDialog.cancelByError)
				this.parentDialog.displayCurrentState ();
		}
			
		return uploader;
	}
	
	this.show = function() {
		//WbsUploadDlg.superclass.show.call(this);
		this.cancelQueue ();
		this.resetState ();
		this.displayCurrentState ();
		this.uploader.setPostParams({folderID: document.ddApplication.currentFolder.Id});
	}
	
	
	this.displayCurrentState = function() {
		var state = this.state;
		this.cancelByError = false;
		
		var elems = new Array (); 
		var blocks = new Array ();
		
		elems.completedFilesCount = Ext.get("completedFilesCount");
		elems.errorFilesCount = Ext.get("errorFilesCount");
		elems.errorFilesBlock = Ext.get("errorFilesBlock");
		
		blocks.filesArea = Ext.get ("FilesArea");
		blocks.afterUpload = Ext.get ("blockAfterUpload");
		blocks.limitError = Ext.get ("blockLimitError");
		blocks.uploadingBlock = Ext.get("uploadingBlock");
		blocks.completeStatusLabel = Ext.get("completeStatusLabel");
		blocks.noSelectedFiles = Ext.get ("blockNoSelectedFiles");
		blocks.selectedFiles = Ext.get ("blockSelectedFiles");
		this.blocks = blocks;
		
		var queueFilesCount = this.getQueueFilesCount ();
	 	this.upgradeFilesCount ();
	 	elems.completedFilesCount.update(this.completeFilesCount);
	 	
	 	if (state == WbsUploadDlg.STATE_LIMIT_ERROR && (this.startQueueFilesCount != null && this.completeFilesCount != null))
	 		this.errorFilesCount = this.startQueueFilesCount - this.completeFilesCount;
	 	elems.errorFilesCount.update(this.errorFilesCount);
	 	
	 	blocks.completeStatusLabel.setDisplayed(false);
	 	elems.errorFilesBlock.setDisplayed (this.errorFilesCount > 0);
	 	
	 	if (queueFilesCount == 0) {
	 		//document.getElementById("frm-upload-block").style.display = "none";
	 		document.ddApplication.resize();
	 		
	 		this.blocks.uploadingBlock.setDisplayed(false);
	 		this.btnStart.setDisabled(true);
	 		this.btnStart.setValue("Start");
	 	} else if (state == WbsUploadDlg.STATE_PROCESS) {
	 		this.blocks.uploadingBlock.setDisplayed(true);
	 		this.btnStart.setDisabled(false);
	 		this.btnStart.setValue("Cancel");
	 	} else {
	 		this.blocks.uploadingBlock.setDisplayed(false);
	 		this.btnStart.setDisabled(false);
	 		this.btnStart.setValue("Start");
	 		
	 		//document.getElementById("frm-upload-block").style.display = "none";
	 		document.ddApplication.resize();
	 	}
	 	if (state == WbsUploadDlg.STATE_COMPLETED || state == WbsUploadDlg.STATE_LIMIT_ERROR) {
			
			blocks.afterUpload.setDisplayed (true);
			blocks.noSelectedFiles.setDisplayed(false);
			blocks.selectedFiles.setDisplayed(false);
			Ext.get("filesSelectedStr").setDisplayed (false);
			Ext.get("queueFilesCount").update ("");
			this.btnCancel.setDisplayed(false);
			//blocks.filesArea.setDisplayed(false);
			
			blocks.completeStatusLabel.setDisplayed(true);
			
	 		if (state == WbsUploadDlg.STATE_LIMIT_ERROR) {
	 			document.getElementById("limitErrorMessage").innerHTML = this.fullErrorMessage;
	 			blocks.limitError.setDisplayed (true);
	 			blocks.completeStatusLabel.update ("<font color='red'>" + ddStrings.lbl_account_limit + "</font>");
	 			//blocks.afterUpload.setDisplayed(false);
	 		}
	 		
	 		if (state == WbsUploadDlg.STATE_COMPLETED) {
	 			blocks.limitError.setDisplayed (false);
	 			blocks.completeStatusLabel.update ("<font color='green'>" + ddStrings.lbl_upload_complete + "</font>");
	 			blocks.afterUpload.setDisplayed(true);
	 		}
	 		
	 		/*document.getElementById("frm-upload-block").onclick = function() {
	 			document.ddApplication.getUploadDlg().close();
	 			this.onclick = null;	 			
	 		}*/
	 		
		} else {
			
			this.btnCancel.setDisplayed(true);
			//blocks.filesArea.setDisplayed(true);
			
			blocks.completeStatusLabel.setDisplayed(false);
			blocks.completeStatusLabel.update ("");
			
			blocks.afterUpload.setDisplayed(false);
			blocks.limitError.setDisplayed(false);
			if (queueFilesCount > 0) {
	 			blocks.selectedFiles.setDisplayed(true);
	 			blocks.noSelectedFiles.setDisplayed(false);
			} else {
				blocks.selectedFiles.setDisplayed(false);
				blocks.noSelectedFiles.setDisplayed(true);
			}
		}
		
		document.ddApplication.resize();
	}
	
	this.startQueue = function () {
		this.uploader.setPostParams({folderID: document.ddApplication.currentFolder.Id, "PHPSESSID" : this.config.sessID});
		
		this.state = WbsUploadDlg.STATE_PROCESS;
		this.startQueueFilesCount = this.getQueueFilesCount();
		this.displayCurrentState ();
		this.uploader.startUpload ();
		Ext.getDom("FilesArea").scrollTop = 0;
	}
	
	/* state functions */
	this.resetState = function () {
		this.state =  WbsUploadDlg.STATE_START;
		this.cancelQueue ();
		this.completeFilesCount = 0;
		this.errorFilesCount = 0;
		this.currentFileNum = 0;
		this.cancelByError = false;
	}

	this.cancelQueue = function () {
		this.uploader.stopUpload();
		var stats;
		
		if (this.uploader.getStats() != null) {
			do {
				stats = this.uploader.getStats();
				this.uploader.cancelUpload();
			} while (stats.files_queued !== 0);
		}
	}
	
	
	this.upgradeFilesCount = function () {
		var currentQueueCount = this.getQueueFilesCount ();
		var currentFileNum = this.startQueueFilesCount - currentQueueCount+1;
		if (currentFileNum > this.startQueueFilesCount)
			currentFileNum = this.startQueueFilesCount;
			
		
		if (this.state == WbsUploadDlg.STATE_PROCESS) {
			Ext.get("queueFilesCount").update (currentFileNum + " of " + this.startQueueFilesCount );
			Ext.get("filesSelectedStr").setDisplayed (false);
			Ext.get("filesUploadingStr").setDisplayed (true);
		} else {
			Ext.get("queueFilesCount").update(currentQueueCount);
			Ext.get("filesSelectedStr").setDisplayed (true);
			Ext.get("filesUploadingStr").setDisplayed (false);
		}
	}
	
	this.getQueueFilesCount = function () {
		return (this.uploader.getStats() != null) ? this.uploader.getStats().files_queued : 0;
	}
	
	this.init();
}
//extend(WbsUploadDlg, WbsDlg);

/*
	Config params:
		appId : application id for load widgets types list
*/
WbsCreateWidgetDlg = newClass (WbsDlg, {
	constructor: function(config) {
		config.title = WbsLocale.getCommonStr("title_create_widget");
		config.cls = "create-widget-dlg";
		config.hideCloseBtn = true;
		
		config.buttons = [
			{id : "create", label: WbsLocale.getCommonStr("action_create"), onClick: this.createPressed, scope: this},
			{id : "cancel", label: WbsLocale.getCommonStr("action_cancel"), onClick: this.hide, scope: this}
		];
		
		this.params = {};
		this.currentStep = null;		
		this.isTypesLoaded = false;
		this.selectedType = null;
		this.subjects = [];
		this.selectedSubject = null;
		this.appId = config.appId;
		this.preselectedSubjectId = null;
		this.superclass().constructor.call(this, config);
	},
		
	show: function(preselectedSubjectId) {
		this.preselectedSubjectId = preselectedSubjectId;
		return this.superclass().show.call(this);
	},
		
	setParams: function(params) {
		this.params = params;
	},
		
	buildContent: function(contentElem) {
		this.showLoading(WbsLocale.getCommonStr("message_loading_widget_types"));
		Ext.Ajax.request ({
			url: this.config.url,
			params: {"fapp": this.appId},
			success: function (response) {
				var result = Ext.decode(response.responseText);
				if (result.success) {
					this.typesLoaded(result.data);
				} else {
					WbsCommon.showError(result);
					return false;
				}
			},
			scope: this
		});
	},
		
	typesLoaded: function(data) {
		this.hideLoading();
		var contentElem = this.getContentElem();
		
		this.createTypeSelector(contentElem, data);
		this.createSubjectSelector(contentElem);
		
		this.isTypesLoaded = true;
		this.resetState();
		
		if (this.afterTypesLoaded)
			this.afterTypesLoaded ();
		
		if (this.afterTypesShowed)
			this.afterTypesShowed();
	},
		
	createTypeSelector: function(contentElem, data) {
		this.typeSelector = createDiv("type-selector");
		var list = createElem("ul", "widgets-types-list");
		this.typeSelector.types = [];
		for (var i = 0; i < data.length; i++) {
			var type = data[i];
			type.elem = this.createTypeElem(type);
			list.appendChild(type.elem);
			this.typeSelector.types.push(type);
		}
		this.typeSelector.appendChild(list);
		
		this.typeSelector.showTypesForSubject = function(subject) {
			for (var i = 0; i < this.typeSelector.types.length; i++) {
				var type = this.typeSelector.types[i];
				if (subject == null)
					type.elem.show();
				else if (type.onlyForFolders && subject != "folder")
					type.elem.hide();
				else
					type.elem.show();					
			}			
		}.bind(this);
		
		contentElem.appendChild(this.typeSelector);
	},
		
	createSubjectSelector: function(contentElem) {
		this.typeLabel = createDiv("wg-type-label");
		
		this.subjectSelector = createDiv("subject-selector");
		var list = createElem("ul", "widgets-subjects-list");
		
		list.appendChild(this.createSubjectElem({id: "files", label: ddStrings.lbl_selected_files}));
		list.appendChild(this.createSubjectElem({id: "folder", label: ddStrings.lbl_current_folder}));
		
		this.subjectSelector.appendChild(this.typeLabel);
		this.subjectSelector.appendChild(list);
		contentElem.appendChild(this.subjectSelector);
	},
		
	onAfterShow: function() {
		this.resetState();
		
		if (this.isTypesLoaded && this.afterTypesShowed)
			this.afterTypesShowed();
	},
		
	resetState: function() {
		this.currentStep = WbsCreateWidgetDlg.SELECT_TYPE_STEP;
		this.buttons["create"].disabled = true;
		if (this.selectedType) {
			this.selectedType.selector.checked = false;
			this.typeSelected(null);
		}
		if (this.selectedSubject) {
			this.selectedSubject.selector.checked = false;
			this.subjectSelected(null);
		}
		this.displayState();
	},
		
	displayState: function() {
		if (!this.isTypesLoaded)
			return;
		
		filesSubject = this.subjects["files"];
		filesSubject.setDisabled(this.params.files.length <= 0);
		filesSubject.setLabel(ddStrings.lbl_selected_files.sprintf(this.params.files.length));
		
		if (this.currentStep != WbsCreateWidgetDlg.SELECT_SUBJECT_STEP) {
			this.typeSelector.show();
			this.typeSelector.showTypesForSubject(this.preselectedSubjectId);
			this.subjectSelector.hide();
		} else {
			if (this.selectedType.onlyForFolders) {
				this.subjectSelected(this.subjects["folder"]);
				this.createPressed();
			} else {
				this.subjectSelected(null);
				this.typeSelector.hide();
				this.subjectSelector.show();
			}
		}
	},
		
	createTypeElem: function(type) {
		var elem = createElem("li");
		
		var selectorId = type.type + "-" + type.subtype;
		if (Ext.isIE) { // fucked browser don't support dynamically created radiobuttons (http://channel9.msdn.com/wiki/internetexplorerprogrammingbugs/)
			var selector = createElem("<input type='radio' name='create-wg-type' id='" + selectorId + "' value='" + selectorId + "'>");
		} else {
			var selector = createElem("input");
			selector.setAttribute("type", "radio");			
			selector.setAttribute("name", "create-wg-type");
			selector.setAttribute("value", selectorId);
			selector.id = selectorId;
		}
		selector.onclick = (function() {this.typeSelected(type)}).bind(this);
		elem.appendChild(selector);
		
		var label = createElem("label");
		label.setAttribute("for", selectorId);
		label.appendChild(document.createTextNode(type.name));		
		elem.appendChild(label);
		
		if (Ext.isIE) // must die
			label.onclick = function() {selector.click();}
		
		var desc = createDiv("wg-desc");
		desc.appendChild(document.createTextNode(type.desc));		
		elem.appendChild(desc);		
		
		type.selector = selector;
		
		return elem;
	},
		
	createSubjectElem: function(subject) {
		this.subjects[subject.id] = subject;
		
		var elem = createElem("li");
		
		var selectorId = subject.id;
		if (Ext.isIE) { // fucked browser don't support dynamically created radiobuttons (http://channel9.msdn.com/wiki/internetexplorerprogrammingbugs/)
			var selector = createElem("<input type='radio' name='create-wg-subject' id='" + selectorId + "' value='" + selectorId + "'>");
		} else {
			var selector = createElem("input");
			selector.setAttribute("type", "radio");			
			selector.setAttribute("name", "create-wg-subject");
			selector.id = selectorId;
			selector.setAttribute("value", selector.id);
		}
		selector.onclick = (function() {this.subjectSelected(subject)}).bind(this);
		elem.appendChild(selector);
		
		var label = createElem("label");
		label.setAttribute("for", selector.id);
		label.appendChild(document.createTextNode(subject.label));
		elem.appendChild(label);
		
		subject.setLabel = function(value) {label.innerHTML = value};
		subject.setDisabled = function(value) {selector.disabled = value;}
		
		if (Ext.isIE) // must die
			label.onclick = function() {selector.click();}
		
		subject.selector = selector;
		
		return elem;
	},
		
	typeSelected: function(type) {
		this.selectedType = type;
		if (this.selectedType) {
			this.buttons["create"].disabled = false;
			this.typeLabel.innerHTML = WbsLocale.getCommonStr("lbl_create_widget_for").sprintf(this.selectedType.name) + ": ";
		} else {
			this.typeLabel.innerHTML = "";
			this.buttons["create"].disabled = true;
		}
	},
		
	subjectSelected: function(subject) {
		this.selectedSubject = subject;
		this.buttons["create"].disabled = (!this.selectedSubject);
	},
		
	createPressed: function() {
		if (this.currentStep == WbsCreateWidgetDlg.SELECT_TYPE_STEP && !this.preselectedSubjectId) {
			this.currentStep = WbsCreateWidgetDlg.SELECT_SUBJECT_STEP;
			this.displayState();
		} else {
			this.createWidget ();
		}
	},
		
	createWidget: function(customType) {
		var type = (customType) ? customType : this.selectedType;
		var subject = (this.preselectedSubjectId) ? this.preselectedSubjectId : this.selectedSubject.id;
			
		this.showLoading(WbsLocale.getCommonStr("message_creating_widget"));
		this.buttons["create"].disabled = true;
		if (!type || !subject || !this.params) {
			alert("Not selected widget type, subject or params");
			return false;
		}
		
		var params = {type: type.type, subtype: type.subtype, fapp: this.appId, wgName: this.params.wgName};
		if (subject == "folder")
			params.folder = this.params.folder;
		if (subject == "files")
			params["files[]"] = this.params.files;
		
		
		Ext.Ajax.request ({
			url: this.config.createUrl,
			params: params,
			success: function (response) {
				var result = Ext.decode(response.responseText);
				this.hideLoading();
				if (result.success) {
				} else {
					WbsCommon.showError(result);
					return false;
				}
				this.hide();
				if (this.afterWidgetCreate)
					this.afterWidgetCreate(result.wgId);
			},
			scope: this
		});
	}
});

WbsCreateWidgetDlg.SELECT_TYPE_STEP = "type";
WbsCreateWidgetDlg.SELECT_SUBJECT_STEP = "subject";




/* one widget record */
WbsWidgetRecord = newClass(WbsRecord, {
	getFields: function() {
		return [
			{name: "id"}, 
			{name: "text"},
			{name: "link"}
		];
	},
		
	openUrl: function() {
		var url = (this.id != 0) ? document.ddApplication.getOldUrl("scripts/" + this.link + "&interface_wrapper=1") : this.link;
		document.ddApplication.openSubframe(url);
	}
});



WbsWidgetsTable = newClass(WbsTable, {
	constructor: function(type, elem, url) {
		if (WidgetsManager.tables[type])
			return WidgetsManager.tables[type];
		
		var reader = new WbsReader ({
			url: url
		});
		this.type = type;
		
		var store = new WbsDataStore({reader: reader, idProperty: "id", recordClass: WbsWidgetRecord});
		var config = {id: "widgets-table", elem: elem, store: store, pager: false, selection: false, autoHeight: false, dock: true};
		
		store.addListener("dataChanged", 
			function() {
				var label = (type == "widgets") ? WbsLocale.getCommonStr("lbl_what_are_widgets") : WbsLocale.getCommonStr("lbl_what_are_my_links");
				var url = (type == "widgets") ? WbsCommon.getPublishedUrl("WG/html/scripts/widgets.php?app=DD") : document.ddApplication.getOldUrl("scripts/links.php");
				var record = new WbsWidgetRecord({id: 0, text: label, link: url});
				this.unshift(record);
			}		
		,store);
		
		this.superclass().constructor.call(this,config);
		
		this.setView(new WbsWidgetsView(this, {}))
		WidgetsManager.tables[type] = this;	
	},
	
	load: function() {
		this.store.load();
	},
		
	selectDefault: function() {
		var widgetId = getCookie("DD-selected-widget-" + this.type);
		this.selectWidget(widgetId);
	},
		
	selectWidget: function(wgId) {
		setCookie("DD-selected-widget-" + this.type, wgId);
		var res = this.view.selectWidget(wgId);
		if (!res && id !=0) {
			setCookie("DD-selected-widget-" + this.type, 0);
			this.selectWidget(0);
		}
	}
});
	
WbsWidgetsView = newClass(WbsDivView, {
	getClassName: function() {
		return "wbs-simplelist-view";
	},
		
	focusRecordBlock: function(recordId) {
		var block = this.getRecordBlock(recordId);
		this.focusBlock(block);		
	},
		
	focusBlock: function(block) {
		this.clearFocus();
		addClass(block, "focused");
		block.focus();
		this.focusedBlock = block;
	},
		
	clearFocus: function() {
		if (this.focusedBlock) {
			removeClass(this.focusedBlock, "focused");
		}
	},
		
	selectWidget: function(wgId) {
		var realId = (wgId == 0) ? 0 : "wg-" + wgId;
		var block = this.getRecordBlock(realId);
		if (!block)
			return false;
		block.openLink();
		this.focusRecordBlock(realId);
		return true;
	},
	
	buildRecordBlock: function(block, record) {
		var resHTML = "";
		
		block.openLink = function() {
			this.focusBlock(block);
			record.openUrl();
		}.bind(this);
		
		if (record.id != 0) {
			var img = createElem("img");
			img.setAttribute("src", "img/" + ((this.table.type == "widgets") ? "widget" : "link") + ".gif");
			img.setAttribute("width", 16);
			img.setAttribute("height", 16);
			block.appendChild(img);
		}
		
		var link = createElem("a");
		link.id = record.id;
		link.href = "javascript:void(0)";
		link.onclick = (function() {this.table.selectWidget(record.id ? record.id.replace("wg-", "") : 0)}).bind(this);
		link.innerHTML = record.text;
		
		block.appendChild(link);
	}
});


WidgetsManager = {
	tables: [],
	widgetDeleted: function(id, type) {
		if (WidgetsManager.tables[type]) {
			WidgetsManager.tables[type].load();			
		}		
	}	
};

DDViewSettingsPopwindow = newClass(WbsPopwindow, {
	constructor: function() {
		var config = {
			width: 470, 
			height: 165,
			cls: "view-settings-window"	
		};
		this.viewSettings = null;
		this.superclass().constructor.call(this, config);
		
		this.addEvents({
			currentViewChanged : true
		});
	},
		
	render: function() {
		this.builditemsOnPage();
		this.buildDescTruncate();
		this.buildViewmodeApplyTo();
		
		var elem = this.createFieldBlock("buttons");
		
		var saveBtn = createElem("input", null, {type:"button", value: WbsLocale.getCommonStr("action_save")});
		saveBtn.onclick = this.save.bind(this);
		elem.appendChild(saveBtn);
		
		var cancelBtn = createElem("input", null, {type:"button", value: WbsLocale.getCommonStr("action_cancel")});
		cancelBtn.onclick = this.cancel.bind(this);
		elem.appendChild(cancelBtn);
	},
		
	save: function() {
		this.close();
	},
		
	cancel: function() {
		this.itemsOnPageInput.value = this.viewSettings.itemsOnPage;
		this.descTruncateInput.value = this.viewSettings.descTruncate;
		Html.setRadioGroupValue("viewmode-apply-to", this.viewSettings.viewmodeApplyTo, this.getInnerElem());
		this.close();		
	},
		
	onAfterShow: function() {
		this.itemsOnPageInput = $("records-on-page");
		this.descTruncateInput = $("desc-truncate");
		
		this.itemsOnPageInput.value = this.viewSettings.itemsOnPage;
		this.descTruncateInput.value = this.viewSettings.descTruncate;
		
		Html.setRadioGroupValue("viewmode-apply-to", this.viewSettings.viewmodeApplyTo, this.getInnerElem());
	},
		
	onClose: function() {
		var currentViewChanged =
			this.itemsOnPageInput.value != this.viewSettings.itemsOnPage ||
			this.descTruncateInput.value != this.viewSettings.descTruncate;
		
		var viewmodeApplyTo = Html.getRadioGroupValue("viewmode-apply-to", this.getInnerElem());
		
		var changed = currentViewChanged || viewmodeApplyTo != this.viewSettings.viewmodeApplyTo;
		if (changed) {
			Ext.Ajax.request ({
				url: "ajax/files_set_viewsettings.php",
				params: {itemsOnPage: this.itemsOnPageInput.value, descTruncate: this.descTruncateInput.value, viewmodeApplyTo: viewmodeApplyTo},
				success: function (response, options) {
					var result = Ext.decode(response.responseText);
					if (result.success) {
						this.viewSettings.itemsOnPage = options.params.itemsOnPage;
						this.viewSettings.descTruncate = options.params.descTruncate;
						this.viewSettings.viewmodeApplyTo = options.params.viewmodeApplyTo;
					} else 
						WbsCommon.showError(result);
						
					if (currentViewChanged)
						this.fireEvent("currentViewChanged");
				}.bind(this)
			});
		}
	},
	
	createFieldBlock: function(cls) {
		var elem = this.getInnerElem();
		
		var field = createDiv("field");
		if (cls)
			addClass(field, cls);
		elem.appendChild(field);
		return field;
	},
		
	setViewSettings: function(viewSettings) {
		this.viewSettings	= viewSettings;
	},
		
	builditemsOnPage: function() {
		var elem = this.createFieldBlock("records-on-page");
		elem.innerHTML = ddStrings.lbl_viewsettings_records_on_page + ": <select id='records-on-page'><option value='10'>10<option value='20'>20<option value='30'>30<option value='40'>40<option value='50'>50<option value='100'>100</select>";
	},
		
	buildDescTruncate: function(elem) {
		var elem = this.createFieldBlock("desc-truncate");
		elem.innerHTML = ddStrings.lbl_viewsettings_truncate_description + ": <input type='text' id='desc-truncate' size='3'> " + ddStrings.lbl_viewsettings_symbols;
	},
		
	buildViewmodeApplyTo: function() {
		var elem = this.createFieldBlock("viewmode-apply-to");
		var html = ddStrings.lbl_viewsettings_apply_viewmode +": <BR>";
		html += "<input type='radio' value='local' name='viewmode-apply-to' id='viewmode-apply-local'><label for='viewmode-apply-local'>" + ddStrings.lbl_viewsettings_current_folder + "</label><BR>";
		html += "<input type='radio' value='global' name='viewmode-apply-to' id='viewmode-apply-global'><label for='viewmode-apply-global'>"+ ddStrings.lbl_viewsettings_all_folders + "</label>";
		elem.innerHTML = html;
	}
});