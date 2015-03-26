/* Demo Note:  This demo uses a FileProgress class that handles the UI for displaying the file name and percent complete.
The FileProgress class is not part of SWFUpload.
*/


/* **********************
   Event Handlers
   These are my custom event handlers to make my
   web application behave the way I went when SWFUpload
   completes different tasks.  These aren't part of the SWFUpload
   package.  They are part of my application.  Without these none
   of the actions SWFUpload makes will show up in my application.
   ********************** */
function fileQueued(file) {
//	console.debug('fileQueued');
	try {
		//document.fu.totalFileSize += 1;
		//document.fu.allSize += file.size;
		
		FileInfo.addFile(file);
		
		//$('.count-files').html('Select files: '+document.fu.totalFileSize+' ('+ $.filesizeformat(document.fu.allSize)+')');
		
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setStatus($.filesizeformat(file.size) );
		progress.toggleCancel(true, this);

	} catch (ex) {
		this.debug(ex);
	}

}

function fileQueueError(file, errorCode, message) {
//	console.debug('fileQueueError');
	try {
		if (errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
			alert($.sprintf("You have attempted to queue too many files.\nYou may select up to %s files.", message));
			return;
		}

		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setError();
		progress.toggleCancel(false);

		switch (errorCode) {
		case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
			progress.setStatus("File size exceeds maximum allowed by the server.");
			this.debug("Error Code: File too big, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
			progress.setStatus("Cannot upload 0 byte files.");
			this.debug("Error Code: Zero byte file, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
			progress.setStatus("Invalid File Type.");
			this.debug("Error Code: Invalid File Type, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		default:
			if (file !== null) {
				progress.setStatus("Error");
			}
			this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		}
	} catch (ex) {
        this.debug(ex);
    }
}

function fileDialogComplete(numFilesSelected, numFilesQueued) {
//	console.debug('fileDialogComplete');
	try {
		if (numFilesSelected > 0) {
			document.getElementById(this.customSettings.cancelButtonId).disabled = false;
		
			var hideSwf = function () {
				$('.upload-button').hide();
				$('#SWFUpload_0').css({
					position: 'absolute',
					left: 0,
					top: 0 					
				});
			};
			
			hideSwf();
			
			if ( limit != -100 && limit < FileInfo.fileSize ) {
				$('.error-view').text($.sprintf('You have only %s of available disk space left. Not all photos will be uploaded.', $.filesizeformat(limit)));
				$('.error-view').css({color: 'red'});
			}
			
			FileInfo.updateView();
		}
		
		/* I want auto start the upload and I can do that here */
		
	} catch (ex)  {
        this.debug(ex);
	}
}

function uploadStart(file) {
//	console.debug('uploadStart');
	try {
		/* I don't want to do any file validation or anything,  I'll just update the UI and
		return true to indicate that the upload should start.
		It's important to update the UI here because in Linux no uploadProgress events are called. The best
		we can do is say we are uploading.
		 */
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setStatus("Uploading...");
		progress.toggleCancel(true, this);
	}
	catch (ex) {}
	
	return true;
}

function uploadProgress(file, bytesLoaded, bytesTotal) {
//	console.debug('uploadProgress', bytesLoaded, bytesTotal);	
	try {
		var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);

		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setProgress(percent);
		
		if ( percent == 100 ) {
			progress.setStatus("Uploading... Creating thumbnails...");
			$('.progressContainer.green > .progressBarStatus').append('<img src="img/loader1.gif" />');
		}
		
		var size = FileInfo.uploadSize + bytesLoaded;		
		var proc = size / FileInfo.fileSize;
		
		$('#progressBarStripe').css({left: proc * 200});
		$('.progressValue').text( Math.ceil(parseInt( proc * 100)) + '%' );
		$('#divStatus').text($.filesizeformat(size) +" of "+ $.filesizeformat(FileInfo.fileSize));
				
		
		
			
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadSuccess(file, serverData) {
//	console.debug('uploadSuccess', serverData);
	try {
		FileInfo.uploadFile(file);
		if ( serverData.match(/Allowed memory/i) ) {
			var progress = new FileProgress(file, this.customSettings.progressTarget);
			progress.setError();
			progress.setStatus($.sprintf("Not enough memory to perfom operation (memory_limit=%s)", memory_limit));
			progress.toggleCancel(false);
			FileInfo.errorAdd();
		}
		else if ( serverData.match(/File type not supported/i) ) {
			var progress = new FileProgress(file, this.customSettings.progressTarget);
			progress.setError();
			progress.setStatus("Invalid file type.");
			progress.toggleCancel(false);
		}
		else {
			var progress = new FileProgress(file, this.customSettings.progressTarget);
			progress.setComplete();
			progress.setStatus("Complete.");
			progress.toggleCancel(false);
		}
		
		this.startUpload();
				
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadError(file, errorCode, message) {
//	console.debug('uploadError', message);
	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setError();
		//progress.toggleCancel(false);
		
		if ( file.filestatus == -5 ) {
			FileInfo.deleteFile(file);
			FileInfo.updateView();
		}

		switch (errorCode) {
		case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
			if ( message == '500' || errorCode == '500' ) {
				progress.setStatus("Photo was not uploaded. Not enough permissions or out of disk space.: Photo was not uploaded. Not enough permissions or out of disk space.");
			}				
			if ( message == '401'  ) {
				progress.setStatus("403 Forbidden");
			}				
			this.debug("Error Code: HTTP Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
			progress.setStatus("Upload Failed.");
			this.debug("Error Code: Upload Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.IO_ERROR:
			progress.setStatus("Server (IO) Error");
			this.debug("Error Code: IO Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
			progress.setStatus("Security Error");
			this.debug("Error Code: Security Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
			progress.setStatus("Upload limit exceeded.");
			this.debug("Error Code: Upload Limit Exceeded, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
			progress.setStatus("Failed Validation.  Upload skipped.");
			this.debug("Error Code: File Validation Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
			// If there aren't any files left (they were all cancelled) disable the cancel button
			if (this.getStats().files_queued === 0) {
				document.getElementById(this.customSettings.cancelButtonId).disabled = true;
			}
			progress.setStatus("Cancelled");
			progress.setCancelled();
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
			progress.setStatus("Stopped");
			break;
		default:
			progress.setStatus("Error creating thumbnail (possible reason — not enough memory).");
			this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		}
	} catch (ex) {
        this.debug(ex);
    }
}

function uploadComplete(file) {
//	console.debug('uploadComplete');	
	if (this.getStats().files_queued === 0) {
		document.getElementById(this.customSettings.cancelButtonId).disabled = true;
	}
}

// This event comes from the Queue Plugin
function queueComplete(numFilesUploaded) {
//	console.debug('queueComplete');
	
	FileInfo.stopUpload();
	window.parent.refreshToUpload(FileInfo.uploadCount);
	FileInfo.reset();
	FileInfo.updateView();
	
	var proc = document.fu.actualFileSize / document.fu.totalFileSize;

	var status = document.getElementById("divStatus");
	status.innerHTML = numFilesUploaded + " file" + (numFilesUploaded === 1 ? "" : "s") + " uploaded.";
	
	var offset = $('#fsUploadProgress').offset();
	$('#SWFUpload_0').css({
			//'z-index': 500,
			position: 'absolute',
			//top: 50 
			left: 130,//parseInt(offset.left) + $('#fsUploadProgress').width() / 2 - 150,
			top: 50//parseInt(offset.top) + $('#fsUploadProgress').height() / 2 - 20 - 20 
			
	});
	
}
