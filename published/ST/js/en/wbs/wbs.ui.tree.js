/*
 * jQuery UI Tree
 *
 * Version 0.1
 *
 * Depends:
 *	ui.core.js
 */

/*
[
	{
		title : '1',
		className : 'myClass',
		type : 'node', //or 'list' or undefined(check attr children for define)
		expand : 'false',//or true
		img : url,
		children : null
	},
	{
		title : '1',
		url : 'gogogo',
		className : 'myClass',
		children : null
	}
]
*/
var g;
var mas = [];

$.widget("ui.wbsTree", {

	_init: function() {
		var options = $.extend(true, {}, $_ui_tree_defaults, this.options);
		this.options = options;
		var self = this;
		var json = this.options.json ? this.options.json : this._getJSON(this.element);
		var ul = this._createBrunch(json).addClass('ui-tree').addClass('ui-tree-root').addClass('ui-tree-node-children');
		var id = this.element.attr('id');
		if (id) ul.attr('id', id);
		$('ul', ul).hide();
		this.element.replaceWith(ul);
		this.element = ul;
		ul.data('tree', this);
		this.removingElements = [];
		this._setNodeEvents(this.element);
		(this.options.expand && $('li', this.element).each(function() { if ($(this).is(self.options.expand)) self.expand(this); }));
		(this.options.hidden &&	this.element.hide());
		/*$('.ui-state-highlight', this.element).each(function() { 
			self._select(this);
		});*/
	},
	
	after: function(json, node) {
		return this._change(json, node, 'after');
	},
	
	before: function(json, node) {
		return this._change(json, node, 'before');
	},
	
	append: function(json, node) {
		return this._change(json, node, 'append');
	},
	
	remove: function(node) {
		this._remove(node);
	},
	
	title: function(node, title) {
		if (title) this._setTitle(node, title);
		else this._getTitle(node);
	},
	
	attr: function(node, attrName, attrValue) {
	},
	
	_remove: function(node) {
		var ul = this._getLI(node).parent('ul');
		if ($('>li', ul).length == 1) ul.remove();
		else this._getLI(node).remove();
	},
	
	getJSON: function(node) {
		if (node == undefined) node = this.element;
		else node = this._getLI(node);
		return this._getJSON(node);
	},
	
	getSelect: function() {
		var select = $('.ui-tree-selected', this.element);
		if (select.length) return select;
		else return null;
	},
	
	nodeName: function(node) {
		return (node.length ? node.attr('nodeName') : $(node).attr('nodeName')).toLowerCase();
	},

	select : function(node, multiselect) {
		return this._select(node, multiselect, null);
	},
	
	isNode : function(node) {
		var li = this._getLI(node);
		return (li.hasClass('ui-tree-node'));
	},
	
	isList : function(node) {
		var li = this._getLI(node);
		return (li.hasClass('ui-tree-list'));
	},
	
	isExpand : function(node) {
		var li = this._getLI(node);
		return (li.hasClass('ui-tree-expanded'));
	},
	
	isCollapse : function(node) {
		var li = this._getLI(node);
		return (!li.hasClass('ui-tree-expanded'));
	},
	
	expand : function(node) {
		var self = this;
		var li = this._getLI(node);
		var ajax = /^\{url\:[\s\S]*?\}$/i.test($("li>span.ui-tree-title", li).text());
		if (ajax) {
			var url = $("li>div.ui-tree-node-header", li).text().replace(/^\{url\:([\s\S]*?)\}$/gim, '$1');
			$('ul>li', li).html("<div class=\"loading\">" + this.options.ajaxMessage + "</div>");
			var child_ul = $('ul', li);
			$.ajax({
				url: url, 
				success: function(data) {
					child_ul.empty();
					$(data).each(function(){
						(this.localName && child_ul.append(self._createBrunch(self.getJSON(this))));
					});
					self._setNodeEvents(child_ul);
				}
			});
		}
		if (this.isList(li)) return false;
		if (this.isExpand(li)) return false;
		if (!this._trigger('expand', null, this._ui({}, li))) return false;
		var parents = li.parents().map(function() {
			if (self.nodeName(this) == 'li') return this;
		});
		if (!this.options.multiExpand) {
			var expanded = $('>li.ui-tree-expanded:visible', this.element);
			expanded.each(function() {
				var el = this, col = true;
				parents.each(function() {
					if (this == el) col = false;
				});
				(col && self.collapse(this));
			});
		}
		if (!li.hasClass('ui-tree-expanded')) {
			li.addClass('ui-tree-expanded');
			$('a:first',li).addClass('ui-tree-expanded');
			$('a:first',li).removeClass('ui-tree-collapsed');
			parents.map(function() {
				(!$(this).hasClass('ui-tree-expanded') && $(this).addClass('ui-tree-expanded') && self._show($('>ul', this)));
			});
			this._show($('>ul', li));
		}
		this._trigger('_expand', 0, node);
		return true;
	},
	
	collapse : function(node) {
		var li = this._getLI(node);
		if (this.isList(li)) return false;
		if (!this._trigger('collapse', null, this._ui({}, li))) return false;
		if (this.isExpand(li)) {
			this._hide($('>ul', li));
			li.removeClass('ui-tree-expanded');
			$('a:first',li).removeClass('ui-tree-expanded');
			$('a:first',li).addClass('ui-tree-collapsed');
		}
		this._trigger('_collapse', 0, node);
		return true;
	},
	
	toggle : function(node) {
		var li = this._getLI(node);
		if (this.isList(li)) return false;
		if (this.isExpand(li)) return this.collapse(li);
		else return this.expand(li);
	},
	
	_setNodeType : function(node, type) {
		var li = this._getLI(node);
		var removeClass = type == 'node' ? 'ui-tree-list' : 'ui-tree-node';
		var addClass = type == 'node' ? 'ui-tree-node' : 'ui-tree-list';
		li.removeClass(removeClass);
		if (!li.hasClass(addClass)) li.addClass(addClass);
	},
	
	_setNodeState : function(node, expandState) {
	},
	
	_change : function(json, node, changeMode) {
		var li = this._createBrunch(json);
		if (node == undefined) {
			node = this.element;
			changeMode = 'append';
		} else node = this._getLI(node);
		if (node.length == undefined) node = $(node);
		//if (!this._trigger('change', event, this._ui({}, node))) return false;
		switch (changeMode) {
			case 'before': node.before(li); break;
			case 'after': node.after(li); break;
			case 'append': 
				var ul = this._getUL(node);
				if (!ul.length) ul = this._getUL(node.append('<ul></ul>'));
				ul.append(li);
				$('li',ul).removeClass('ui-tree-node-last-visible');
				$('li:first',ul).addClass('ui-tree-node-first-visible');
				$('li:last-child',ul).addClass('ui-tree-node-last-visible');
				
				break;
			default: ;
		}
		
		this._setNodeEvents(li);
		this._trigger('_change', 0, node);
		return li;
	},

	_hover : function(node, leave) {
		//var span = this._getSPAN(node);
		//if (leave) $('.ui-state-hover:first', this.element).removeClass('ui-state-hover');
		//if (!leave) $(node).addClass('ui-state-hover');
		this._trigger('_hover', 0, {node: node, leave: leave});
	},
	
	_select : function(node, multiselect) {
		var div = $(node).parent().parent();
		//var span = this._getSPAN(node);
		if (!this._trigger('select', 0, this._ui({}, node))) return false;
		if (!multiselect || !this.options.multiSelect) {
			$('.ui-tree-selected', $('.ui-tree')).removeClass('ui-tree-selected');
		//	$('.ui-state-highlight', $('.ui-tree')).removeClass('ui-state-highlight');
		}
		//div.addClass('ui-tree-selected');
		//$('a span:first', div).addClass('ui-state-highlight');

		this._trigger('_select', 0, node);

		//this.element.trigger('tree_select');
	},
	
	_show : function(el) {
		if ($.effects) {
			el.show(this.options.expandEffect, this.options.expandOptions, this.options.expandSpeed);
		} else {
			el.show(this.options.expandSpeed);
		}
	},
	
	_hide : function(el) {
		if ($.effects) {
			el.hide(this.options.expandEffect, this.options.expandOptions, this.options.expandSpeed);
		} else {
			el.hide(this.options.expandSpeed);
		}
	},
	
	_getUL : function(node) {
		node = node.length ? node : $(node);
		if (this.nodeName(node) == 'span') return $('>ul', node.parent());
		else if (this.nodeName(node) == 'li') return $('>ul', node);
		else return node;
	},
	
	_getLI : function(node) {
		node = node.length ? node : $(node);
		if (this.nodeName(node) == 'span') return node.parent().parent();
		else if (this.nodeName(node) == 'ul') return node.parent();
		else return node;
	},
	
	_getSPAN : function(node) {
		node = node.length ? node : $(node);
		if (this.nodeName(node) == 'li') return $('>span.ui-tree-title:eq(0)', node);
		else if (this.nodeName(node) == 'ul') return $('span.ui-tree-title:eq(0)', node.parent());
		else return node;
	},
	
	_ui : function(ui, el) {
		ui = ui ? ui : {};
		el = el.length == undefined ? el : $(el);
		return {
			helper : ui.helper,
			position : ui.position,
			offset : ui.offset,
			item : el,
			overState : ui.overState,
			target : this,
			sender : null
		};
	},
	
	_createBrunch : function(json) {
		if (typeof(json) == 'string') json = this._evalJSON(json);
		var brunch = $(this._createLI(json));
		//$('>ul', $('.ui-tree-expand', brunch)).show();
		//$('.ui-tree-expand', brunch).show();
		return brunch;
	},
	
	// bind events
	_setNodeEvents : function(el) {

		var self = this;

		var events = $(this.options.events).not(this.options.selectOn, this.options.expandOn, this.options.collapseOn);
		var createEvent = function(eventName) {
			return function(event) {
				self._trigger(eventName, event, self._ui({}, self._getLI(this)));
			}
		}
		var div = $('div.ui-tree-node-header', el);
		$(events).each(function() {
			div.bind(this, createEvent(this));
		});
		$('a:first', div).bind('click', function(event) {
			var li = $(this).parent().parent();
			if (self.isCollapse(li)) return self.expand(li);
			else if (self.isExpand(li)) return self.collapse(li);
		})
		$('a:first span', div).bind('click', function(event) {
			return false
		})
		if (this.options.expandOn && this.options.expandOn == this.options.collapseOn) {
			$(div).bind(this.options.expandOn, function(event) {
				var li = $(this).parent();
				if (self.isCollapse(li)) return self.expand(li);
				else if (self.isExpand(li)) return self.collapse(li);
			})
		} else {
			(this.options.expandOn && div.bind(this.options.expandOn, function(event) { return self.expand(this); }));
			(this.options.collapseOn && div.bind(this.options.collapseOn, function(event) { return self.collapse(this); }));
		}
		(this.options.selectOn && $('a:first span', div).bind(this.options.selectOn, function(event) {
			return self._select(this, self.options.multiSelectKey ? event[self.options.multiSelectKey] : true, event);
		}));

		(this.options.hoverOn && $('a:first span', div).bind(this.options.hoverOn[0], function(event) {
			return self._hover(this, false);
		}));
		(this.options.hoverOn && $('a:first span', div).bind(this.options.hoverOn[1], function(event) {
			return self._hover(this, true);
		}));
		//div.disableSelection();
		(this.options.createbrunch && this._trigger('createbrunch', null, this._ui({}, this._getLI(el))));
	},
	
	_evalJSON: function(json) {
		return eval('(' + json + ')');
	},
	
	_getTitle : function(node) {
		var title = $(':first', node);
		var html = '';
		if (!title.length) title = $(node);
		html = title.text();
		return html;
	},
	
	_setTitle: function(node, title) {
		this._getSPAN(node).html(title);
	},
	
	_getJSON: function(node, its_child) {
		var json = '';
		var nodeName = this.nodeName(node);
		if (nodeName == 'li') {
			json = '{';
			var title = this._getTitle(node);
			json += "'title' : '" + title + "'";
			var id = node.attr('id');
			if (id) json += ", 'id' : '" + id + "'";
			var className = $.trim(node.attr('className').replace(/ui-[^\s]*/gim, ''));
			if (className) json += ", 'className' : '" + className + "'";
			var url = $('>div a:eq(0)', node);
			if (!url.length) url = $('>a:eq(0)', node);
			if (url.length && url.attr('href')) json += ", 'url' : '" + url.attr('href') + "'";
			var expand = node.hasClass('ui-tree-expanded');
			json += ", 'expand' : " + expand;
			
		} else if (nodeName == 'ul') {
			json += its_child ? ", 'children' : [" : "[";
		}
		var child = node.children(nodeName == 'ul' ? 'li' : (nodeName == 'li' ? 'ul' : 'xyz'));
		if (child.length > 0) {
			for (var i = 0; i < child.length; ) {
				json += this._getJSON($(child.get(i)), true);
				if (++i != child.length) json += ',';
			}
		}
		json += (nodeName == 'ul' ? ']' : (nodeName == 'li' ? '}' : ''));
		return json;
	},

	_createLI: function(obj) {
		if (obj.length) return this._createUL(obj);
		var html = '<li ';
		if (obj.id) html += 'id="' + obj.id + '"';
		html += 'class="';
		//html += obj.children ? 'ui-tree-node' : 'ui-tree-list';
		html += obj.children ? ' ui-tree-node ui-tree-node-empty ui-tree-parent ' : ' ui-tree-node ';
		html += obj.expand ? ' ui-tree-expand ' : ' ui-tree-node-collapsed ';
		
		if (obj.className) html += ' ' + obj.className;
		//<span class="ui-tree-expand-control"/>
		if (!obj.children) {
			var emptyClass = 'ui-tree-node-empty';
		}
		html += '"><div class="ui-tree-node-header ' 
			+ (obj.selected ? ' ui-tree-selected ' : '') 
			+ emptyClass + '">';
		
		if (obj.title) {
			if (obj.url) html += '<a href="' + obj.url + '"><span class="ui-tree-node-text ' + (obj.selected ? ' ui-state-highlight ' : '') + ' ">' + obj.title + '</span></a>';
			else html += '<a rel=""><span class="ui-tree-node-text ' + (obj.selected ? ' ui-state-highlight ' : '') +' ">' + obj.title + '</span></a>';
		}
		html += '</div>';
		if (obj.children) {
			html += this._createUL(obj.children);
		}
		html += '</li>';
		return html;
	},
	
	_createUL: function(obj) {
		var html = '<ul>';
		if (obj.length != undefined) {
			for (var i = 0; i < obj.length; i++) {
				html += this._createLI(obj[i]);
			}
		} else {
			html += this._createLI(obj);
		}
		html += '</ul>';
		var ul = $(html);
		$('li:first',ul).addClass('ui-tree-node-first-visible');
		$('li:last-child',ul).addClass('ui-tree-node-last-visible');
		return '<ul class="ui-tree-node-children">'+ul.html()+'</ul>';
	}
	
});

$.extend($.ui.tree, {
	version: "1.8",
	defaults: {}
});

var $_ui_tree_defaults = {
	// ajax options: object or array of object - not work!
	/*ajaxOptions : {
		element : '*',
		data : 'rel', //string or function(node, tree) {return ...} will be passed to request
		loadConstantly : false, // load constantly data from server on expand folder
		script : 'FileConnector.php'
	},*/
	ajaxMessage : 'Loading...',
	// events from node will be triggered to the tree events
	events : ['click', 'dblclick', 'mousedown', 'mouseup', 'mouseenter', 'mouseleave'],
	json : null,
	acceptFromSelf : true,
	acceptFrom : '.ui-tree',
	multiSelect : true,
	multiSelectKey : 'ctrlKey',
	multiExpand : true,
	expand : '*',
	hoverOn : ['mouseover','mouseleave'],
	selectOn : 'mousedown',
	expandOn : 'dblclick',
	collapseOn : 'dblclick',
	// effect options
	expandEffect : null,// 'blind',
	expandOptions : {},
	expandSpeed : 0,
	collapseEffect : 'blind',
	collapseOptions : {},
	collapseSpeed : 0
};