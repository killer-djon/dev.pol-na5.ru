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
		if (selectedRecords.isEmpty()) {
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
			url: document.ddApplication.getOldUrl("ajax/file_deleteversions"),
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