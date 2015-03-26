var PROJECTS_NODE_SPECIAL_STATUS = 11;

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
	if (folderId == "undefined") {
		app.tree.selectNode();
	}
	if (!app.tree.selectNode(folderId)) {
		alert("66");
		//app.tree.selectNode("ROOT");
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

// ��� ����� ��������� �� ��������, ��� �������� ���������� ����� DDApplication
DDApplication = newClass(WbsApplication, {
	constructor: function(config) {
		this.config = config;
		
		this.currentFolder = new DDFolder();
		this.currentFolder.addListener("changed", function(folder) {this.folderChanged();}, this);
		this.currentFolder.addListener("modified", function(folder) {this.folderModified();}, this);
		//this.layout = new DDApplicationLayout (this);
		
		// Create tree
		this.tree = new DDTree({elemId: "folders-tree"}, this);
		this.tree.loadNodes(document.treeNodes, false);
		this.tree.render();
		
		// Create navigation bar
		this.navBar = new WbsNavBar({id: "DD", saveSize: true,  contentElemId: "nav-bar", expanderElemId: "nav-bar-expander"});	
		this.navBar.addListener("blockActivated", this.navBarBlockActivated, this);
		this.navBar.addListener("horizontalResize", this.resize, this);
		
		// Create new folder menu button
		this.newFolderBtn = this.createNewFolderBtn();
		
		this.folderStore = this.createFolderStore();
		
		
		// Search panel
		this.searchPanel = new SearchPanel({el: "dd-search-panel", app: this, searchCallback: this.doSearch.bind(this)});
		
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
				uploadURL: "../../../../DD/html/ajax/file_upload.php",
				ieUploadURL: "../../DD/html/ajax/file_upload.php",
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
		var config = {elemId: config.elemId, iconCls: "my-folder", rootVisible: false};
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
						nodeData[this.nodeMap.specialStatus] = 0;
						nodeData[this.nodeMap.children] = null;
						var newNode = this.addNode(nodeData);
						parentNode.appendChild(newNode);
						
						
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
		if (this.app.config.canTools) {
			var rootNode = this.treePanel.getRootNode();
			this.trashNode = rootNode.appendChild(new Ext.tree.TreeNode({id: "trash", text: ddStrings.screen_recycle, iconCls: "trash-node"}));
			this.nodes["trash"] = this.trashNode;
		}
	},
	
	onNodeClick: function(node, e) {
		if (node.id == "trash") {
			this.app.openSubframe("scripts/service.php?curScreen=0", true);
			return;
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
			this.folderLinkElem.innerHTML = "<label>" + ddStrings.lbl_link_to_this_folder + ": </label>" + "<input onClick='this.select()' value='" + folder.SHARE_LINK_URL + "'> <a target='_blank' href='" + folder.SHARE_LINK_URL + "'><img title='" + commonStrings.lbl_open_in_new_window + "' src='img/link-go.gif'></a>";
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