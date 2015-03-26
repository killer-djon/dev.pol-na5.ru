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
	this.fileNames = new Array ();
	
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
			this.parentDialog.fileNames = Array ();
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
			this.parentDialog.fileNames[this.parentDialog.fileNames.length] = fileObj.name;
		}
		
		uploader.afterFileError = function (fileObj, error_code, message) {
			var parentDlg = this.parentDialog;
			parentDlg.errorFilesCount++;
			parentDlg.completeFilesCount--;
			
			// Limit error occurred
			if (!parentDlg.cancelByError && error_code == SWFUpload.UPLOAD_ERROR.HTTP_ERROR && parentDlg.fullMessages[message] != null) {
				parentDlg.state = WbsUploadDlg.STATE_LIMIT_ERROR;
				parentDlg.fullErrorMessage = parentDlg.fullMessages[message];
				parentDlg.messageCode = message;
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
	 			if (this.messageCode == 502) {
	 				document.getElementById("limitErrorActions").style.display = "";
	 				blocks.completeStatusLabel.update ("<font color='red'>" + ddStrings.lbl_account_limit + "</font>");
	 			} else {
	 				document.getElementById("limitErrorActions").style.display = "none";
	 				blocks.completeStatusLabel.update ("<font color='red'>" + ddStrings.lbl_error + "</font>");
	 			}
	 			
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

			Ext.Ajax.request ({
				url: "ajax/send_notes.php",
				params: {
					"fid": document.ddApplication.currentFolder.Id,
					"files[]": this.fileNames
				},
				success: function (response) {}.bind(this)
			});

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

function dump(obj, obj_name)
{
	var result = "";
	for(var i in obj)
		result += obj_name + "." + i + " = " + obj[i] + "\n";
	return result;
}

//extend(WbsUploadDlg, WbsDlg);