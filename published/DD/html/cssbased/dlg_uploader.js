DDUploaderDialog = function(dialogContentId) {
	this.dialogContentId = dialogContentId;
	this.strings = new Array ();
}
DDUploaderDialog.prototype = new CommonDialog;

DDUploaderDialog.prototype.tryCreate = function (params) {
	AjaxLoader.doRequest ("../ajax/uploader_create.php", this.tryCreateCompleted,
		{wgName: params.name, folderId: params.folderId}, {scope: this});
}

DDUploaderDialog.prototype.tryCreateCompleted = function (response, options) {
	var result = Ext.decode(response.responseText);
	if(result.success) {
		widgetsNode = foldersTree.getWidgetsNode();
		widgetsNode.loader.on ("load", function() {
			widgetsNode.expand ();
			var newNode = foldersTree.getNodeById("wg-" + result.wgId);
			if (newNode != null) { 
				newNode.select ();
				foldersTree.loadSelectedNode();
			}
			widgetsNode.loader.purgeListeners();
		});
		widgetsNode.reload ();
	} else {
		alert(result.errorStr);
	}
	this.dialog.hide ();
}

var ddUploaderDialog = new DDUploaderDialog ("ddlist-uploader");
ddUploaderDialog.tabs = new Array ();
ddUploaderDialog.tabs.push (
	{'id' : 'uploader-create', 
	 'nextBtnText': CommonStrings.wg_create_btn,
	 onShow: function (dialog) {
	 	if (Ext.getDom("currentFolderName") != null)
	 		document.getElementById("uploader-name-input").value = Ext.getDom("currentFolderName").value;
	 	dialog.nextBtn.enable ();
	 },
	 onNext: function(dialog){
		 	nameValue = document.getElementById("uploader-name-input").value;
		 if (nameValue == "")
		 {
		 	alert(CommonStrings.wg_widgetemptyname_error);
		 	return;		 		
		 }
		 folderId = document.getElementById("currentFolderId").value;
		 var params = {"name" : nameValue, folderId : folderId};
		 dialog.tryCreate(params);
	 }		 
	}	
);
ddUploaderDialog.tabs.push ({'id' : 'share'});

	

document.dialogs["DDUploaderInplace"] = ddUploaderDialog;