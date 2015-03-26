DDViewSettingsPopwindow = newClass(WbsPopwindow, {
	constructor: function() {
		var config = {
			width: 470, 
			height: 165,
			cls: "view-settings-window"	
		};
		this.viewSettings = null;
		this.superclass().constructor.call(this, config);
		
		this.addEvents({
			currentViewChanged : true
		});
	},
		
	render: function() {
		this.builditemsOnPage();
		this.buildDescTruncate();
		this.buildViewmodeApplyTo();
		
		var elem = this.createFieldBlock("buttons");
		
		var saveBtn = createElem("input", null, {type:"button", value: WbsLocale.getCommonStr("action_save")});
		saveBtn.onclick = this.save.bind(this);
		elem.appendChild(saveBtn);
		
		var cancelBtn = createElem("input", null, {type:"button", value: WbsLocale.getCommonStr("action_cancel")});
		cancelBtn.onclick = this.cancel.bind(this);
		elem.appendChild(cancelBtn);
	},
		
	save: function() {
		this.close();
	},
		
	cancel: function() {
		this.itemsOnPageInput.value = this.viewSettings.itemsOnPage;
		this.descTruncateInput.value = this.viewSettings.descTruncate;
		Html.setRadioGroupValue("viewmode-apply-to", this.viewSettings.viewmodeApplyTo, this.getInnerElem());
		this.close();		
	},
		
	onAfterShow: function() {
		this.itemsOnPageInput = $("records-on-page");
		this.descTruncateInput = $("desc-truncate");
		
		this.itemsOnPageInput.value = this.viewSettings.itemsOnPage;
		this.descTruncateInput.value = this.viewSettings.descTruncate;
		
		Html.setRadioGroupValue("viewmode-apply-to", this.viewSettings.viewmodeApplyTo, this.getInnerElem());
	},
		
	onClose: function() {
		var currentViewChanged =
			this.itemsOnPageInput.value != this.viewSettings.itemsOnPage ||
			this.descTruncateInput.value != this.viewSettings.descTruncate;
		
		var viewmodeApplyTo = Html.getRadioGroupValue("viewmode-apply-to", this.getInnerElem());
		
		var changed = currentViewChanged || viewmodeApplyTo != this.viewSettings.viewmodeApplyTo;
		if (changed) {
			Ext.Ajax.request ({
				url: "ajax/files_set_viewsettings.php",
				params: {itemsOnPage: this.itemsOnPageInput.value, descTruncate: this.descTruncateInput.value, viewmodeApplyTo: viewmodeApplyTo},
				success: function (response, options) {
					var result = Ext.decode(response.responseText);
					if (result.success) {
						this.viewSettings.itemsOnPage = options.params.itemsOnPage;
						this.viewSettings.descTruncate = options.params.descTruncate;
						this.viewSettings.viewmodeApplyTo = options.params.viewmodeApplyTo;
					} else 
						WbsCommon.showError(result);
						
					if (currentViewChanged)
						this.fireEvent("currentViewChanged");
				}.bind(this)
			});
		}
	},
	
	createFieldBlock: function(cls) {
		var elem = this.getInnerElem();
		
		var field = createDiv("field");
		if (cls)
			addClass(field, cls);
		elem.appendChild(field);
		return field;
	},
		
	setViewSettings: function(viewSettings) {
		this.viewSettings	= viewSettings;
	},
		
	builditemsOnPage: function() {
		var elem = this.createFieldBlock("records-on-page");
		elem.innerHTML = ddStrings.lbl_viewsettings_records_on_page + ": <select id='records-on-page'><option value='10'>10<option value='20'>20<option value='30'>30<option value='40'>40<option value='50'>50</select>";
	},
		
	buildDescTruncate: function(elem) {
		var elem = this.createFieldBlock("desc-truncate");
		elem.innerHTML = ddStrings.lbl_viewsettings_truncate_description + ": <input type='text' id='desc-truncate' size='3'> " + ddStrings.lbl_viewsettings_symbols;
	},
		
	buildViewmodeApplyTo: function() {
		var elem = this.createFieldBlock("viewmode-apply-to");
		var html = ddStrings.lbl_viewsettings_apply_viewmode +": <BR>";
		html += "<input type='radio' value='local' name='viewmode-apply-to' id='viewmode-apply-local'><label for='viewmode-apply-local'>" + ddStrings.lbl_viewsettings_current_folder + "</label><BR>";
		html += "<input type='radio' value='global' name='viewmode-apply-to' id='viewmode-apply-global'><label for='viewmode-apply-global'>"+ ddStrings.lbl_viewsettings_all_folders + "</label>";
		elem.innerHTML = html;
	}
});
