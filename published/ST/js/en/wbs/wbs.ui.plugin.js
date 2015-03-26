/*
 * WBS UI Splitter
 *
 * Copyright (c) 2010
 *
 * Depends:
 *	jquery.ui.core.js
 *	jquery.ui.position.js
 *	jquery.ui.widget.js
 */
$.widget("ui.wbsPlugin", {
	options: {
		contentBlock: '#ticket',
		tab: false,
		name: false,
		onClick: function(parent){
			return false;
		}
	},
	_create: function() {
		var self = this, o = this.options, $controller = [], hash = '';
		if (o.tab) o.tab = $('#' + o.tab);

        var hash = location.hash, re = /\/plugin\/(\w+)/;
        if (o.name){
        $controller[$.wbs.camelCase(['plugin',o.name,'Action'])] = function(attr){
			o.onClick($(o.contentBlock));
			if (o.tab) {
				if (!o.tab.parent) o.tab = $('#' + o.tab);
				o.tab.parent().children().removeClass('tabs-selected');
	            o.tab.addClass('tabs-selected');
			} else {
				self.element.parent().children().removeClass('tabs-selected');
				self.element.addClass('tabs-selected');
			}
			if (o.name!='request') $('#ticket').height($(window).height() - $(".ticket-toolbar").outerHeight() - $("#tabs-toolbar").outerHeight());

		}
        
	        if (hash == '') hash = '#';
			var test = re.exec(hash);
	
	        if (test && test[1]){
	        	hash = hash.substr(0,hash.search(re))
	        }
        }
        
		if (o.tab){
	        self.element.attr('href', hash+'/plugin/'+o.name)
			$('a', $(o.tab)).attr('href', hash+'/plugin/'+o.name)
		} else {
			$('a', self.element).attr('href', hash+'/plugin/'+o.name)
		}

        if (!o.name){
        	hash = hash.replace(re,'');
        	if (hash=='#') hash = '';
			$('a', self.element).attr('href', hash)
        }
		$.wbs.controller = $.extend($.wbs.controller, $controller);
	}
});
