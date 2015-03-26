/*
	Config params:
		appId : application id for load widgets types list
*/
WbsCreateWidgetDlg = newClass (WbsDlg, {
	constructor: function(config) {
		config.title = WbsLocale.getCommonStr("title_create_widget");
		config.cls = "create-widget-dlg";
		config.hideCloseBtn = true;
		
		config.buttons = [
			{id : "create", label: WbsLocale.getCommonStr("action_create"), onClick: this.createPressed, scope: this},
			{id : "cancel", label: WbsLocale.getCommonStr("action_cancel"), onClick: this.hide, scope: this}
		];
		
		this.params = {};
		this.currentStep = null;		
		this.isTypesLoaded = false;
		this.selectedType = null;
		this.subjects = [];
		this.selectedSubject = null;
		this.appId = config.appId;
		this.preselectedSubjectId = null;
		this.superclass().constructor.call(this, config);
	},
		
	show: function(preselectedSubjectId) {
		this.preselectedSubjectId = preselectedSubjectId;
		return this.superclass().show.call(this);
	},
		
	setParams: function(params) {
		this.params = params;
	},
		
	buildContent: function(contentElem) {
		this.showLoading(WbsLocale.getCommonStr("message_loading_widget_types"));
		Ext.Ajax.request ({
			url: this.config.url,
			params: {"fapp": this.appId},
			success: function (response) {
				var result = Ext.decode(response.responseText);
				if (result.success) {
					this.typesLoaded(result.data);
				} else {
					WbsCommon.showError(result);
					return false;
				}
			},
			scope: this
		});
	},
		
	typesLoaded: function(data) {
		this.hideLoading();
		var contentElem = this.getContentElem();
		
		this.createTypeSelector(contentElem, data);
		this.createSubjectSelector(contentElem);
		
		this.isTypesLoaded = true;
		this.resetState();
		
		if (this.afterTypesLoaded)
			this.afterTypesLoaded ();
		
		if (this.afterTypesShowed)
			this.afterTypesShowed();
	},
		
	createTypeSelector: function(contentElem, data) {
		this.typeSelector = createDiv("type-selector");
		var list = createElem("ul", "widgets-types-list");
		this.typeSelector.types = [];
		for (var i = 0; i < data.length; i++) {
			var type = data[i];
			type.elem = this.createTypeElem(type);
			list.appendChild(type.elem);
			this.typeSelector.types.push(type);
		}
		this.typeSelector.appendChild(list);
		
		this.typeSelector.showTypesForSubject = function(subject) {
			for (var i = 0; i < this.typeSelector.types.length; i++) {
				var type = this.typeSelector.types[i];
				if (subject == null)
					type.elem.show();
				else if (type.onlyForFolders && subject != "folder")
					type.elem.hide();
				else
					type.elem.show();					
			}			
		}.bind(this);
		
		contentElem.appendChild(this.typeSelector);
	},
		
	createSubjectSelector: function(contentElem) {
		this.typeLabel = createDiv("wg-type-label");
		
		this.subjectSelector = createDiv("subject-selector");
		var list = createElem("ul", "widgets-subjects-list");
		
		list.appendChild(this.createSubjectElem({id: "files", label: ddStrings.lbl_selected_files}));
		list.appendChild(this.createSubjectElem({id: "folder", label: ddStrings.lbl_current_folder}));
		
		this.subjectSelector.appendChild(this.typeLabel);
		this.subjectSelector.appendChild(list);
		contentElem.appendChild(this.subjectSelector);
	},
		
	onAfterShow: function() {
		this.resetState();
		
		if (this.isTypesLoaded && this.afterTypesShowed)
			this.afterTypesShowed();
	},
		
	resetState: function() {
		this.currentStep = WbsCreateWidgetDlg.SELECT_TYPE_STEP;
		this.buttons["create"].disabled = true;
		if (this.selectedType) {
			this.selectedType.selector.checked = false;
			this.typeSelected(null);
		}
		if (this.selectedSubject) {
			this.selectedSubject.selector.checked = false;
			this.subjectSelected(null);
		}
		this.displayState();
	},
		
	displayState: function() {
		if (!this.isTypesLoaded)
			return;
		
		filesSubject = this.subjects["files"];
		filesSubject.setDisabled(this.params.files.length <= 0);
		filesSubject.setLabel(ddStrings.lbl_selected_files.sprintf(this.params.files.length));
		
		if (this.currentStep != WbsCreateWidgetDlg.SELECT_SUBJECT_STEP) {
			this.typeSelector.show();
			this.typeSelector.showTypesForSubject(this.preselectedSubjectId);
			this.subjectSelector.hide();
		} else {
			if (this.selectedType.onlyForFolders) {
				this.subjectSelected(this.subjects["folder"]);
				this.createPressed();
			} else {
				this.subjectSelected(null);
				this.typeSelector.hide();
				this.subjectSelector.show();
			}
		}
	},
		
	createTypeElem: function(type) {
		var elem = createElem("li");
		
		var selectorId = type.type + "-" + type.subtype;
		if (Ext.isIE) { // fucked browser don't support dynamically created radiobuttons (http://channel9.msdn.com/wiki/internetexplorerprogrammingbugs/)
			var selector = createElem("<input type='radio' name='create-wg-type' id='" + selectorId + "' value='" + selectorId + "'>");
		} else {
			var selector = createElem("input");
			selector.setAttribute("type", "radio");			
			selector.setAttribute("name", "create-wg-type");
			selector.setAttribute("value", selectorId);
			selector.id = selectorId;
		}
		selector.onclick = (function() {this.typeSelected(type)}).bind(this);
		elem.appendChild(selector);
		
		var label = createElem("label");
		label.setAttribute("for", selectorId);
		label.appendChild(document.createTextNode(type.name));		
		elem.appendChild(label);
		
		if (Ext.isIE) // must die
			label.onclick = function() {selector.click();}
		
		var desc = createDiv("wg-desc");
		desc.appendChild(document.createTextNode(type.desc));		
		elem.appendChild(desc);		
		
		type.selector = selector;
		
		return elem;
	},
		
	createSubjectElem: function(subject) {
		this.subjects[subject.id] = subject;
		
		var elem = createElem("li");
		
		var selectorId = subject.id;
		if (Ext.isIE) { // fucked browser don't support dynamically created radiobuttons (http://channel9.msdn.com/wiki/internetexplorerprogrammingbugs/)
			var selector = createElem("<input type='radio' name='create-wg-subject' id='" + selectorId + "' value='" + selectorId + "'>");
		} else {
			var selector = createElem("input");
			selector.setAttribute("type", "radio");			
			selector.setAttribute("name", "create-wg-subject");
			selector.id = selectorId;
			selector.setAttribute("value", selector.id);
		}
		selector.onclick = (function() {this.subjectSelected(subject)}).bind(this);
		elem.appendChild(selector);
		
		var label = createElem("label");
		label.setAttribute("for", selector.id);
		label.appendChild(document.createTextNode(subject.label));
		elem.appendChild(label);
		
		subject.setLabel = function(value) {label.innerHTML = value};
		subject.setDisabled = function(value) {selector.disabled = value;}
		
		if (Ext.isIE) // must die
			label.onclick = function() {selector.click();}
		
		subject.selector = selector;
		
		return elem;
	},
		
	typeSelected: function(type) {
		this.selectedType = type;
		if (this.selectedType) {
			this.buttons["create"].disabled = false;
			this.typeLabel.innerHTML = WbsLocale.getCommonStr("lbl_create_widget_for").sprintf(this.selectedType.name) + ": ";
		} else {
			this.typeLabel.innerHTML = "";
			this.buttons["create"].disabled = true;
		}
	},
		
	subjectSelected: function(subject) {
		this.selectedSubject = subject;
		this.buttons["create"].disabled = (!this.selectedSubject);
	},
		
	createPressed: function() {
		if (this.currentStep == WbsCreateWidgetDlg.SELECT_TYPE_STEP && !this.preselectedSubjectId) {
			this.currentStep = WbsCreateWidgetDlg.SELECT_SUBJECT_STEP;
			this.displayState();
		} else {
			this.createWidget ();
		}
	},
		
	createWidget: function(customType) {
		var type = (customType) ? customType : this.selectedType;
		var subject = (this.preselectedSubjectId) ? this.preselectedSubjectId : this.selectedSubject.id;
			
		this.showLoading(WbsLocale.getCommonStr("message_creating_widget"));
		this.buttons["create"].disabled = true;
		if (!type || !subject || !this.params) {
			alert("Not selected widget type, subject or params");
			return false;
		}
		
		var params = {type: type.type, subtype: type.subtype, fapp: this.appId, wgName: this.params.wgName};
		if (subject == "folder")
			params.folder = this.params.folder;
		if (subject == "files")
			params["files[]"] = this.params.files;
		
		
		Ext.Ajax.request ({
			url: this.config.createUrl,
			params: params,
			success: function (response) {
				var result = Ext.decode(response.responseText);
				this.hideLoading();
				if (result.success) {
				} else {
					WbsCommon.showError(result);
					return false;
				}
				this.hide();
				if (this.afterWidgetCreate)
					this.afterWidgetCreate(result.wgId);
			},
			scope: this
		});
	}
});

WbsCreateWidgetDlg.SELECT_TYPE_STEP = "type";
WbsCreateWidgetDlg.SELECT_SUBJECT_STEP = "subject";




/* one widget record */
WbsWidgetRecord = newClass(WbsRecord, {
	getFields: function() {
		return [
			{name: "id"}, 
			{name: "text"},
			{name: "link"}
		];
	},
		
	openUrl: function() {
		var url = (this.id != 0) ? document.ddApplication.getOldUrl("scripts/" + this.link + "&interface_wrapper=1") : this.link;
		document.ddApplication.openSubframe(url);
	}
});



WbsWidgetsTable = newClass(WbsTable, {
	constructor: function(type, elem, url) {
		if (WidgetsManager.tables[type])
			return WidgetsManager.tables[type];
		
		var reader = new WbsReader ({
			url: url
		});
		this.type = type;
		
		var store = new WbsDataStore({reader: reader, idProperty: "id", recordClass: WbsWidgetRecord});
		var config = {id: "widgets-table", elem: elem, store: store, pager: false, selection: false, autoHeight: false, dock: true};
		
		store.addListener("dataChanged", 
			function() {
				var label = (type == "widgets") ? WbsLocale.getCommonStr("lbl_what_are_widgets") : WbsLocale.getCommonStr("lbl_what_are_my_links");
				var url = (type == "widgets") ? WbsCommon.getPublishedUrl("WG/html/scripts/widgets.php?app=DD") : document.ddApplication.getOldUrl("scripts/links.php");
				var record = new WbsWidgetRecord({id: 0, text: label, link: url});
				this.unshift(record);
			}		
		,store);
		
		this.superclass().constructor.call(this,config);
		
		this.setView(new WbsWidgetsView(this, {}))
		WidgetsManager.tables[type] = this;	
	},
	
	load: function() {
		this.store.load();
	},
		
	selectDefault: function() {
		var widgetId = getCookie("DD-selected-widget-" + this.type);
		this.selectWidget(widgetId);
	},
		
	selectWidget: function(wgId) {
		setCookie("DD-selected-widget-" + this.type, wgId);
		var res = this.view.selectWidget(wgId);
		if (!res && id !=0) {
			setCookie("DD-selected-widget-" + this.type, 0);
			this.selectWidget(0);
		}
	}
});
	
WbsWidgetsView = newClass(WbsDivView, {
	getClassName: function() {
		return "wbs-simplelist-view";
	},
		
	focusRecordBlock: function(recordId) {
		var block = this.getRecordBlock(recordId);
		this.focusBlock(block);		
	},
		
	focusBlock: function(block) {
		this.clearFocus();
		addClass(block, "focused");
		block.focus();
		this.focusedBlock = block;
	},
		
	clearFocus: function() {
		if (this.focusedBlock) {
			removeClass(this.focusedBlock, "focused");
		}
	},
		
	selectWidget: function(wgId) {
		var realId = (wgId == 0) ? 0 : "wg-" + wgId;
		var block = this.getRecordBlock(realId);
		if (!block)
			return false;
		block.openLink();
		this.focusRecordBlock(realId);
		return true;
	},
	
	buildRecordBlock: function(block, record) {
		var resHTML = "";
		
		block.openLink = function() {
			this.focusBlock(block);
			record.openUrl();
		}.bind(this);
		
		if (record.id != 0) {
			var img = createElem("img");
			img.setAttribute("src", "img/" + ((this.table.type == "widgets") ? "widget" : "link") + ".gif");
			img.setAttribute("width", 16);
			img.setAttribute("height", 16);
			block.appendChild(img);
		}
		
		var link = createElem("a");
		link.href = "javascript:void(0)";
		link.onclick = (function() {this.table.selectWidget(record.id ? record.id.replace("wg-", "") : 0)}).bind(this);
		link.innerHTML = record.text;
		
		block.appendChild(link);
	}
});


WidgetsManager = {
	tables: [],
	widgetDeleted: function(id, type) {
		if (WidgetsManager.tables[type]) {
			WidgetsManager.tables[type].load();			
		}		
	}	
};