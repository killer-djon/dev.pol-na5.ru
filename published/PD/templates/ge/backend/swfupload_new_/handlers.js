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
	try {
		document.fu.totalFileSize += 1;
		document.fu.allSize += file.size;
		
		$('.count-files').html('Файлов выбранно: '+document.fu.totalFileSize+' ('+ $.filesizeformat(document.fu.allSize)+')');
		
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setStatus("Pending... size= "+ $.filesizeformat(file.size) );
		progress.toggleCancel(true, this);

	} catch (ex) {
		this.debug(ex);
	}

}

function fileQueueError(file, errorCode, message) {
	try {
		if (errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
			alert("You have attempted to queue too many files.\n" + (message === 0 ? "You have reached the upload limit." : "You may select " + (message > 1 ? "up to " + message + " files." : "one file.")));
			return;
		}

		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setError();
		progress.toggleCancel(false);

		switch (errorCode) {
		case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
			progress.setStatus("File is too big.");
			this.debug("Error Code: File too big, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
			progress.setStatus("Cannot upload Zero Byte files.");
			this.debug("Error Code: Zero byte file, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
			progress.setStatus("Invalid File Type.");
			this.debug("Error Code: Invalid File Type, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		default:
			if (file !== null) {
				progress.setStatus("Unhandled Error");
			}
			this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		}
	} catch (ex) {
        this.debug(ex);
    }
}

function fileDialogComplete(numFilesSelected, numFilesQueued) {	
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
			
			$('.start-container').show();
	
			if ( jQuery('#radio-new-album').attr('checked') ) {
				jQuery.post('backend.php?controller=ajax&action=createAlbum', {albumName:jQuery('#album-new-name').val()}, function(data){
					document.fu.uploader.setPostParams({
						'albumId': data,
						"PHPSESSID" : document.sessionId
					});				
					hideSwf();
				});
			}
			else {
				document.fu.uploader.setPostParams({
					'albumId': $('#select-album-list option:selected').attr('value'),
					"PHPSESSID" : document.sessionId				
				});
				hideSwf();
			}
		}
		
		/* I want auto start the upload and I can do that here */
		
	} catch (ex)  {
        console.debug(ex);
	}
}

function uploadStart(file) {
	try {
		/* I don't want to do any file validation or anything,  I'll just update the UI and
		return true to indicate that the upload should start.
		It's important to update the UI here because in Linux no uploadProgress events are called. The best
		we can do is say we are uploading.
		 */
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setStatus("Uploading...");
		progress.toggleCancel(true, this);
		
		if (document.fu.actualFileSize == 0 ) {
			this.addPostParam('isAlbumThumb', '1');
		}
		else {
			this.removePostParam('isAlbumThumb');
		}		
	}
	catch (ex) {}
	
	return true;
}

function uploadProgress(file, bytesLoaded, bytesTotal) {	
	try {
		var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);

		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setProgress(percent);
		
		var size = document.fu.actualFileSize + bytesLoaded;
		
		var proc = size / document.fu.allSize;
		
		$('#progressBarStripe').css({left: proc * 200});
		$('.progressValue').text( Math.ceil(parseInt( proc * 100)) + '%' );
//		$('#divStatus').text('Загруженно: '+$.filesizeformat(size) +" из "+ $.filesizeformat(document.fu.allSize));
		
		progress.setStatus("Uploading... "+ percent);
		if ( percent == 100 ) {
			
		}
				
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadSuccess(file, serverData) {
	try {
		
		document.fu.actualFileSize += file.size;
		
//		var proc = document.fu.actualFileSize / document.fu.totalFileSize;
//		$('#progressBarStripe').css({left: proc * 200});
//		$('.progressValue').text( Math.ceil(parseInt( proc * 100)) + '%' );		
		
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setComplete();
		progress.setStatus("Complete.");
		progress.toggleCancel(false);
		this.startUpload();
				
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadError(file, errorCode, message) {
	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setError();
		progress.toggleCancel(false);

		switch (errorCode) {
		case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
			progress.setStatus("Upload Error: " + message);
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
			progress.setStatus("Unhandled Error: " + errorCode);
			this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		}
	} catch (ex) {
        this.debug(ex);
    }
}

function uploadComplete(file) {	
	if (this.getStats().files_queued === 0) {
		document.getElementById(this.customSettings.cancelButtonId).disabled = true;
	}
}

// This event comes from the Queue Plugin
function queueComplete(numFilesUploaded) {
	
	$('.progress-container').hide();
//	$('.divStatus').text('Файлов агруженно: '+numFilesUploaded);
	
	window.parent.refresh();
	
	document.fu.actualFileSize = 0;		
	document.fu.totalFileSize = 0;
	var proc = document.fu.actualFileSize / document.fu.totalFileSize;
	$('#progressBarStripe').css({left: 0});
	$('.progressValue').text( '0%' );	

	var status = document.getElementById("divStatus");
	status.innerHTML = numFilesUploaded + " file" + (numFilesUploaded === 1 ? "" : "s") + " uploaded.";
	
	$('.upload-button').show();
	var offset = $('#fsUploadProgress').offset();
	$('#SWFUpload_0').css({
			//'z-index': 500,
			position: 'absolute',
			//top: 50 
			left: 130,//parseInt(offset.left) + $('#fsUploadProgress').width() / 2 - 150,
			top: 50//parseInt(offset.top) + $('#fsUploadProgress').height() / 2 - 20 - 20 
			
	});
	
}
