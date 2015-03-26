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
	}
});

DDFileMenu = newClass (WbsPopmenu, {
	constructor: function(record, type) {
		this.record = record;
		var writeDisabled = !record.canWrite();
		
		var linkHidden = record.SHARE_LINK_URL ? false : true;
		var checkOutHidden = record.CHECKED_OUT ? true : false;
		var items = [];
		items = items.concat([
			{label: ddStrings.action_open, onClick: function() {window.open(record.OPEN_URL); return false;}},
			{label: ddStrings.action_download, onClick: function() {location.href = record.DOWNLOAD_URL; return false;}},
			"-"
		]);
			
		var noHide = (type) ? false : true;
		var linkItems = [
			{id: "link_data", hidden: linkHidden, iconCls: 'item-link-data', cls: "nopadding-nolink", html: "<span class='smalllabel'>" +  ddStrings.lbl_link_to_file + "</span><BR><input onClick='this.select()' class='shared-url' value='" + record.SHARE_LINK_URL + "'><img style='margin-left: 5px; cursor: pointer' title='" + commonStrings.lbl_open_in_new_window + "' src='img/link-go.gif' onClick='window.open(this.previousSibling.value)'>"},
			{id: "link_create", disabled: writeDisabled, hidden: !linkHidden, iconCls: 'item-link-data', label: ddStrings.action_get_link_to_file, onClickNoHide: noHide, onClick: function() {this.record.createRemoveLink("create", this.showHideLink.bind(this))}},
			{id: "link_delete", disabled: writeDisabled, hidden: linkHidden, label: ddStrings.action_remove_link_to_file, onClickNoHide: noHide, onClick: function() {this.record.createRemoveLink("remove", this.showHideLink.bind(this))}}];
			
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
			{label: commonStrings.action_delete, disabled: writeDisabled, onClick: function() {(new DDFilesList([this.record])).tryDelete("move")}}
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
		
		var writeDisabled = !this.recordsList.canWrite();
		var noRecords = this.recordsList.isEmpty();
		
		config.items = [
			{label: ddStrings.selected_files_count.sprintf(recordsList.getCount()), cls: "unactive"},
			{label: ddStrings.action_create_link_to_files, iconCls: 'item-link-data', disabled: noRecords, onClick: recordsList.showCreateSmartlinkDlg, scope: recordsList},
			{label: ddStrings.action_create_widget, disabled: noRecords, onClick: recordsList.showWidgetDlg, scope: recordsList, iconCls: 'item-widget'},
			"-",
			{label: ddStrings.action_send_by_email, disabled: noRecords, onClick: recordsList.showSendDlg, scope: recordsList, iconCls: "item-email"},
			"-",
			{label: commonStrings.action_copy, disabled: noRecords, onClick: recordsList.showCopyDlg, scope: recordsList},
			{label: commonStrings.action_move, disabled: writeDisabled || noRecords, onClick: recordsList.showMoveDlg, scope: recordsList},
			{label: commonStrings.action_delete, disabled: writeDisabled || noRecords, onClick: recordsList.tryDelete, scope: recordsList}
		];
		config.withImages = true;
		
		this.superclass().constructor.call(this, config);
	}
});