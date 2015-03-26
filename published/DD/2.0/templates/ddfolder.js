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
		if (oldId != this.Id) {
			this.titleControl.setViewMode();
			this.fireEvent("changed", this);
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
		
	tryDelete: function() {
		if (!confirm(commonStrings.message_confirm_folder_delete))
			return false;
		
		Ext.Ajax.request ({
			url: document.ddApplication.getOldUrl("ajax/folder_delete.php"),
			params: {folderId: this.Id},
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
		
	showCopyMoveDlg: function(action) {
		var title = (action == "copy") ? commonStrings.title_copy_folder : commonStrings.title_move_folder;
		var description = WbsLocale.getCommonStr("lbl_folder") + ": " + this.Name;
		var rootFolderAvailable = document.ddApplication.config.canCreateRootFolder;
		var dlg = new DDCopyMoveDlg({mode: "folder", rootFolderAvailable: rootFolderAvailable, selectedFolderId: this.Id,action: action, actionObject: this, contentElemId: "dlg-copymove-content", title: title, description: description});
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
		var titleControl = new WbsEditableLabel({elem: elem, clickToEdit: true});
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
			{id: "link_data", hidden: linkHidden, iconCls: 'item-link-data', cls: "nopadding-nolink", html: "<span class='smalllabel'>" + ddStrings.lbl_link_to_this_folder + "</span><BR><input onClick='this.select()' class='shared-url' value='" + this.folder.SHARE_LINK_URL + "'><img style='margin-left: 5px; cursor: pointer' title='" + commonStrings.lbl_open_in_new_window + "' src='img/link-go.gif' onClick='window.open(this.previousSibling.value)'>"},
			{id: "link_delete", label: ddStrings.action_link_delete, disabled: disabledFolder, onClickNoHide: true, onClick: (function() {this.folder.createRemoveLink("remove", this.showHideLink.bind(this))}).bind(this) },
			{label: ddStrings.action_create_widget, disabled: disabledFolder, onClick: this.folder.showCreateWidgetDlg, scope: this.folder, iconCls: 'item-widget'}
		];
		
		if (document.ddApplication.config.canManageUsers) {
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
			{label: commonStrings.action_rename, disabled: !this.folder.canWrite(), onClick: this.folder.showRename.bind(this.folder)}, 
			"-",
			{label: commonStrings.action_copy, onClick: function() {this.folder.showCopyMoveDlg("copy")}.bind(this) },
			{label: commonStrings.action_move, onClick: function() {this.folder.showCopyMoveDlg("move")}.bind(this), disabled: !this.folder.canWrite() },
			{label: commonStrings.action_delete, disabled: !this.folder.canWrite(), onClick: this.folder.tryDelete.bind(this.folder)}
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