FilesSendEmailDlg.trySend = function () {
	var sendParams = this.getValues();
	if (sendParams == null)
		return;
		
	var filesIds = new Array ();
	for (i = 0; i < this.selectedFiles.length; i++) {
		filesIds.push(this.selectedFiles[i].id);
	}
	var needCompress = document.getElementById("needCompress").checked ? 1 : 0;
	
	AjaxLoader.doRequest("../ajax/send_files.php", 
		this.trySendCompleted , 
		{"sendData[to]": sendParams.sendTo, "sendData[subject]": sendParams.sendSubject, "sendData[message]": sendParams.sendMessage, "filesIds[]": filesIds, needCompress: needCompress}
		, {scope: this}
	);
}

function showSendEmailsDlg () {
	if (!checkEmailList())
		return false;
		
	selectedFiles = new Array ();
	
	thisForm = document.forms[0];
	var maxTotalSizeBytes = document.getElementById ("sendLimitBytes").value;
	var totalSizeBytes = 0;
	for ( i = 0; i < thisForm.elements.length; i++ ) {
		cElem = thisForm.elements[i];
		if (cElem.type == 'checkbox' && cElem.checked  && cElem.getAttribute("filename") != null) {
			selectedFiles.push ({filename: cElem.getAttribute("filename"), filesize: cElem.getAttribute("filesize"), id: cElem.value});
			totalSizeBytes += parseInt(cElem.getAttribute("filesize"));
		}
	}
	
	if (totalSizeBytes > maxTotalSizeBytes) {
		alert (FilesSendEmailDlg.strings.maxAttachmetsLimitError + getFilesizeStr(maxTotalSizeBytes));
		return false;
	}
	
	
	document.getElementById("sendFilesCount").innerHTML = selectedFiles.length;
	document.getElementById("sendFilesSize").innerHTML = getFilesizeStr(totalSizeBytes);
	FilesSendEmailDlg.selectedFiles = selectedFiles;
	FilesSendEmailDlg.subject = getFilesSubject (selectedFiles, FilesSendEmailDlg.strings);
	FilesSendEmailDlg.show ();
	return false;
}

function checkEmailList()
{
	if ( !treeCheckSelection( FilesSendEmailDlg.strings.emptyemailMessage ) )
		return false;

	return true;
}

function getFilesSubject (files, strings) {
	var res = "";
	
	res = files[0].filename + " " + strings.andLabel + " " + (files.length-1) + " " + strings.moreItemsLabel;
	if (files.length == 2)
		res = files[0].filename + " " +  strings.andLabel + " 1 " + strings.moreItemLabel;
	if (files.length == 1)
		res = files[0].filename;
	return res;
}