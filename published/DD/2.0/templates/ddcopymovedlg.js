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
		
		this.addFolder(document.treeNodes, 0);
		//if (this.config.selectedFolderId)
			//this.folderSelector.value = this.config.selectedFolderId;
	},
		
	addFolder: function(folderData, level) {
		var option = createElem("option");
		
		var label = "";
		for (var j = 0; j < level; j++)
			label += "&nbsp;";
		label += folderData[1];
		if (folderData[0] == "ROOT")
			label = WbsLocale.getCommonStr("lbl_desc_root_folder");
		option.value = folderData[0];
		option.innerHTML = label.truncate(50);
		
		var minRights = (this.mode == "folder") ? 7 : 3;			
		
		if (folderData[2] >= minRights || (this.config.rootFolderAvailable && folderData[0] == "ROOT"))
			this.folderSelector.appendChild(option);
		if (folderData[4]) {
			for (var i = 0; i < folderData[4].length; i++) {
				this.addFolder(folderData[4][i], level+1);
			}
		}
	},
		
	afterAction: function() {
		this.hide();
	}
});