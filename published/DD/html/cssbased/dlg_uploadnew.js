function WbsUploadDlg (config) {
	WbsUploadDlg.superclass.constructor.call(this, config);
	
	WbsUploadDlg.STATE_START = "START";
	WbsUploadDlg.STATE_PROCESS = "PROCESS";
	WbsUploadDlg.STATE_COMPLETED = "COMPLETED";
	WbsUploadDlg.STATE_LIMIT_ERROR = "LIMIT_ERROR";
	
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
		//this.btnCancel = Ext.getDom("btnCancel");
		
		this.btnStart.onclick = function() {
			if (this.value == "Cancel") {
				dlg.pressCancel.call(dlg);
			} else {
				dlg.startQueue.call(dlg);
			}
		}
		this.btnStart.setDisabled = function(value) {
			this.disabled = value;
		}
		
		this.btnStart.setValue = function(value) {
			this.value = value;
		}
		
		/*this.btnCancel.onclick = function() {
			dlg.pressCancel.call(dlg);
		}
		this.btnCancel.setDisabled = function(value) {
			this.disabled = value;
		}*/
	}
	
	this.pressCancel = function() {
		if (this.state == WbsUploadDlg.STATE_PROCESS) {
			this.state = WbsUploadDlg.STATE_COMPLETED;
			this.cancelQueue ();
			document.ddApplication.refreshData();
		} else {
			this.hide ();
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
			post_params: {"PHPSESSID" : this.config.sessID, folderID: null},

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
			this.parentDialog.completeFilesCount = 0;
			this.parentDialog.state = WbsUploadDlg.STATE_START;
			this.parentDialog.displayCurrentState ();
		}	
		
		uploader.afterCompleted = function () {
			var parentDlg = this.parentDialog;
			var sleep = (parentDlg.errorFilesCount > 0) ? 3000 : 300;
			window.setTimeout (function () {
				document.ddApplication.refreshData();
				if (parentDlg.state != WbsUploadDlg.STATE_LIMIT_ERROR)
					parentDlg.state = WbsUploadDlg.STATE_COMPLETED;
				parentDlg.displayCurrentState ();
				parentDlg.resetState ();
			}, sleep);
		}
		
		uploader.afterFileComplete = function (fileObj) {
			this.parentDialog.completeFilesCount++;
			this.parentDialog.upgradeFilesCount ();
		}
		
		uploader.afterFileError = function (fileObj, error_code, message) {
			var parentDlg = this.parentDialog;
			parentDlg.errorFilesCount++;
			
			// Limit error occurred
			if (!parentDlg.cancelByError && error_code == SWFUpload.UPLOAD_ERROR.HTTP_ERROR && parentDlg.fullMessages[message] != null) {
				parentDlg.state = WbsUploadDlg.STATE_LIMIT_ERROR;
				parentDlg.fullErrorMessage = parentDlg.fullMessages[message];
				parentDlg.errorFilesCount += this.getStats().files_queued;
				parentDlg.cancelByError = true;
				parentDlg.cancelQueue ();
			}
			this.parentDialog.upgradeFilesCount ();
		}
		
		uploader.afterFileCancelled = function () {
			if (!this.parentDialog.cancelByError)
				this.parentDialog.displayCurrentState ();
		}
			
		return uploader;
	}
	
	this.show = function() {
		WbsUploadDlg.superclass.show.call(this);
		this.cancelQueue ();
		this.resetState ();
		this.displayCurrentState ();
		this.uploader.setPostParams({folderID: document.ddApplication.getCurrentFolderId()});
	}
	
	
	this.displayCurrentState = function() {
		var state = this.state;
		this.cancelByError = false;
		
		var elems = new Array (); 
		var blocks = new Array ();
		
		elems.selectMoreLink = Ext.get("selectMoreLink");
		elems.afterSelectMoreLink = Ext.get("afterSelectMoreLink");
		elems.completedFilesCount = Ext.get("completedFilesCount");
		elems.errorFilesCount = Ext.get("errorFilesCount");
		elems.errorFilesBlock = Ext.get("errorFilesBlock");
		
		blocks.afterUpload = Ext.get ("blockAfterUpload");
		blocks.limitError = Ext.get ("blockLimitError");
		blocks.noSelectedFiles = Ext.get ("blockNoSelectedFiles");
		blocks.selectedFiles = Ext.get ("blockSelectedFiles");
		
		//if (this.state == WbsUploadDlg.STATE_PROCESS) 
			//elems.selectMoreLink.setDisplayed(false);
		
		var queueFilesCount = this.getQueueFilesCount ();
	 	this.upgradeFilesCount ();
	 	elems.completedFilesCount.update(this.completeFilesCount);
	 	
	 	if (this.startQueueFilesCount != null && this.completeFilesCount != null)
	 		this.errorFilesCount = this.startQueueFilesCount - this.completeFilesCount;
	 	elems.errorFilesCount.update(this.errorFilesCount);
	 	elems.errorFilesBlock.setDisplayed (this.errorFilesCount > 0);
	 	
	 	if (queueFilesCount == 0) {
	 		this.btnStart.setDisabled(true);
	 		elems.selectMoreLink.setDisplayed(false);
	 		this.btnStart.setValue("Start");
	 	} else if (state == WbsUploadDlg.STATE_PROCESS) {
	 		this.btnStart.setDisabled(false);
	 		this.btnStart.setValue("Cancel");
	 		elems.selectMoreLink.setDisplayed(false);
	 	} else {
	 		this.btnStart.setDisabled(false);
	 		this.btnStart.setValue("Start");
	 		elems.selectMoreLink.setDisplayed(true);
	 	}
	 	//elems.selectMoreLink.dom.style.display = "none";
	 		
		if (state == WbsUploadDlg.STATE_COMPLETED || state == WbsUploadDlg.STATE_LIMIT_ERROR) {
			
			blocks.afterUpload.setDisplayed (true);
			blocks.noSelectedFiles.setDisplayed(false);
			blocks.selectedFiles.setDisplayed(false);
			
	 		if (state == WbsUploadDlg.STATE_LIMIT_ERROR) {
	 			blocks.limitError.update(this.fullErrorMessage);
	 			blocks.limitError.setDisplayed (true);
	 			elems.afterSelectMoreLink.setDisplayed(false);
	 		}
	 		
	 		if (state == WbsUploadDlg.STATE_COMPLETED) {
	 			blocks.limitError.setDisplayed (false);
	 			elems.afterSelectMoreLink.setDisplayed(true);
	 		}
	 		
		} else {
		
			blocks.afterUpload.setDisplayed(false);
			if (queueFilesCount > 0) {
	 			blocks.selectedFiles.setDisplayed(true);
	 			blocks.noSelectedFiles.setDisplayed(false);
	 			//elems.selectMoreLink.setDisplayed(true);
			} else {
				blocks.selectedFiles.setDisplayed(false);
				blocks.noSelectedFiles.setDisplayed(true);
			}
		}
	}
	
	this.startQueue = function () {
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
			Ext.get("queueFilesCount").update (currentFileNum + "/" + this.startQueueFilesCount );
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
extend(WbsUploadDlg, WbsDlg);