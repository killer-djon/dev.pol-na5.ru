DDFilesSendDlg = newClass(WbsDlg, {
	
	constructor: function(config) {
		
		this.filesList = config.filesList;
		var filesList = this.filesList;
		
		config.cls = "files-send-dlg";
		config.title = ddStrings.title_send_by_email;
		config.height = 385;
		config.width = 500;
		config.buttons = [
			{label: ddStrings.action_send, onClick: this.trySend, scope: this},
			{label: commonStrings.action_cancel, onClick: this.close,scope: this}
		];
		config.closeMode = "close";
		config.hideCloseBtn = true;
		
		var defaultSubject = filesList.getRecord(0).DL_FILENAME;
		if (filesList.getCount() > 1)
			defaultSubject += " " + ddStrings.lbl_and_files.sprintf(filesList.getCount()-1);
		this.defaultSubject = defaultSubject;
		this.defaultMessage = ddStrings.text_enter_message;
		
		this.totalFilesSize = 0;
		for (var i = 0; i < filesList.getCount(); i++) {
			this.totalFilesSize += parseInt(filesList.getRecord(i).DL_FILESIZE);
		}
		
		this.superclass().constructor.call(this, config);
	},
		
	trySend: function() {
		var subject = this.subjectInput.value;
		var sendTo = this.sendToInput.value;
		
		var compress = false;
		this.filesList.sendByEmail(sendTo, subject, this.messageInput.getValue(), compress, this.close.bind(this));
		
	},
		
	onAfterShow: function() {
		this.sendToInput = $("filessend-send-to-input");
		this.subjectInput = $("filessend-subject-input");
		this.messageInput = $("filessend-message-input");
		this.filesText = $("filessend-files-text");
		//this.compressCheckbox = $("filessend-need-compress");
		
		this.subjectInput.value = this.defaultSubject;
		this.messageInput.value = this.defaultMessage;
		this.messageInput.defaultValue = this.defaultMessage;
		this.filesText.innerHTML = ddStrings.lbl_send_files_attachments.sprintf(this.filesList.getCount(), getFilesizeStr(this.totalFilesSize));
		
		this.messageInput.onfocus = function() {
			if (this.value == this.defaultValue)
				this.value = "";
		}
		
		this.messageInput.onblur = function() {
			if (this.value == "")
				this.value = this.defaultValue;
		}
		
		this.messageInput.getValue = function() {
			if (this.value == this.defaultValue) return "";
			return this.value;
		}
	},
		
	buildContent: function (contentElem) {
		html = "";
		html += "<div class='field send-to-field'>" + ddStrings.field_send_to + ":<BR><textarea id='filessend-send-to-input'></textarea><div class='note'>" + ddStrings.field_send_to_comment + "</div></div>";
		html += "<div class='field subject-field'>" + ddStrings.field_subject + ":<BR><input type='text' id='filessend-subject-input'></div>";
		html += "<div class='field message-field'>" + ddStrings.field_message +":<BR><textarea id='filessend-message-input'></textarea></div>";
		html += "<div class='field'>" + ddStrings.lbl_attachments + ": <span id='filessend-files-text'></span></div>";
		
		contentElem.innerHTML = html;		
	}
});