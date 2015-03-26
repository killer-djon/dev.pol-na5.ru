/*
 * WBS UI Portlet
 *
 * Copyright (c) 2010
 *
 * Depends:
 *	jquery.ui.core.js
 *	jquery.ui.widget.js
 */

$.widget("ui.wbsPortlet", {
	options: {
		initSize: 200,
		buttons: ['collapse'],
		title: '<b>HTML</b>',
		content: false,
		collapsed: false,
		disabled: false
	},
	_swapIcons: function(icons){
		icons[2] = icons[0];
		icons[0] = icons[1];
		icons[1] = icons[2];
		return icons;
	},
	_create: function() {
		var self = this, o = this.options;

		this.element.empty()
			.addClass("wbs-resizable ui-portlet ui-widget ui-widget-content ui-helper-clearfix");
		
		if (o.disabled) {
			this.element.addClass('ui-disabled');
		}
		this.header = $("<div/>",{'class': 'ui-portlet-header'})
			.append(o.title)
			.addClass("ui-widget-header");
		for (buttonKey in o.buttons){
			var buttonHtml;
			var iconClicks = [];
			switch(o.buttons[buttonKey]){
				case 'collapse':
					buttonHtml = $('<span class="ui-icon"></span>')
					.addClass('ui-icon-minus')
					.click(function(){
						$(this).toggleClass('ui-icon-minus').toggleClass('ui-icon-plus');
						$(this).parents(".ui-portlet:first").find(".ui-portlet-content").toggle();
					});

					if (o.collapsed){
						buttonHtml.click();
					}				
				  break;
				case 'action':
					buttonHtml = $('<span class="ui-icon"></span>')
					.addClass('ui-icon-triangle-1-s')
					.click(function(){
						alert('action');
					});
				  break;
			}

			this.header.prepend(buttonHtml);
		}

		
		this.content = $("<div/>",{'class': 'ui-portlet-content ui-widget-body'});
		if (o.content) this.content.append($(o.content));
		this.element.append(this.header).append(this.content);
	}
});
