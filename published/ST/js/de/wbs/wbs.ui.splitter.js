/*
 * WBS UI Splitter
 *
 * Copyright (c) 2010
 *
 * Depends:
 *	jquery.ui.core.js
 *	jquery.ui.mouse.js
 *	jquery.ui.widget.js
 */

$.widget("ui.wbsSplitter", $.ui.mouse, {
	options: {
		initSize: 100,
		max: 1000,
		min: -1000,
		zIndex: 1000,
		orientation: 'vertical',
		step: 1,
		value: 0,
		collapsed: false,
		collapsedValue: 0,
		handleSize: 7,
		handleBorder: 2
	},
	_create: function() {
		var self = this, o = this.options;
		//console.log('Splitter creation', 'first:', o.firstDiv, 'second:', o.secondDiv, 'container:', self.element.attr('id'));
		this._detectOrientation();
		this._mouseInit();

		this.element
			.addClass("ui-widget")
			.addClass("ui-widget-content")
			.addClass("ui-splitter"
				+ " ui-splitter-" + this.orientation);
		
		if (o.disabled) {
			this.element.addClass('ui-splitter-disabled ui-disabled');
		}

		o.value = o.initSize;
		this.element.css({
			'position': 'relative'
		});
		if (o.orientation == 'vertical') {
			this.element.css({
				cursor: 'e-resize',
				'z-index': o.zIndex
			});
		} else {
			this.element.css({
				cursor: 'n-resize',
				'z-index': 2000
			});
		}
		this.dragged = false;
		
		this.element
			.click(function(event) {
				event.preventDefault();
				//dragged = false;
			})
			.dblclick(function(event) {
				event.preventDefault();
				//self.collapse();
			})
			.hover(function() {
				if (!o.disabled) {
					$(this).addClass('ui-state-hover');
				}
			}, function() {
				$(this).removeClass('ui-state-hover');
			})
			.focus(function() {
				if (!o.disabled) {
					$(".ui-splitter.ui-state-focus").removeClass('ui-state-focus'); $(this).addClass('ui-state-focus');
				} else {
					$(this).blur();
				}
			})
			.blur(function() {
				$(this).removeClass('ui-state-focus');
			});
		
	},

	destroy: function() {

		this.handles.remove();
		this.range.remove();

		this.element
			.removeClass("ui-splitter"
				+ " ui-splitter-horizontal"
				+ " ui-splitter-vertical"
				+ " ui-splitter-disabled"
				+ " ui-widget"
				+ " ui-widget-content"
				+ " ui-corner-all")
			.removeData("slider")
			.unbind(".slider");

		this._mouseDestroy();

		return this;
	},
	_mouseCapture: function(event) {
		var o = this.options; 
		var mouseOverHandle = $(event.target).is('#'+$(this.element).attr('id'));
		if (mouseOverHandle /*&& this.dragged*/) {
			this.startOffset = this.element.offset()
			this.nextElSize = {width: this.element.next().width(), height: this.element.next().height()}
			this.prevElSize = {width: this.element.prev().width(), height: this.element.prev().height()}
			this.dragged = !this.dragged;
			/*if (o.disabled)
				return false;*/

			this.elementSize = {
				width: this.element.outerWidth(),
				height: this.element.outerHeight()
			};
			this.elementOffset = this.element.offset();

			var position = { x: event.pageX, y: event.pageY };
			var self = this;

			
			this._start(event);

			this.element
				.addClass("ui-state-active")
				.focus();
			
			this._clickOffset = {left: position.x - this.startOffset.left, top: position.y - this.startOffset.top};

			normValue = this._normValueFromMouse(position);
			this._split(event, normValue);
			return true;
		} else {
			return false;
		}
	},

	_mouseStart: function(event) {
		return true;
	},

	_mouseDrag: function(event) {

		var position = { x: event.pageX, y: event.pageY };
		var normValue = this._normValueFromMouse(position);
		this._split(event, normValue);

		return false;

	},

	_mouseStop: function(event) {

		dragged = false;
		this.element.removeClass("ui-state-focus");
		this.element.removeClass("ui-state-active");
		this._stop(event);

		this._change(event);
		this._clickOffset = null;
		return false;
	},
	
	_detectOrientation: function() {
		this.orientation = this.options.orientation == 'vertical' ? 'vertical' : 'horizontal';
	},

	_normValueFromMouse: function(position) {

		var pixelTotal, pixelMouse;
		if ('vertical' == this.orientation) {
			pixelMouse = position.x - this.elementOffset.left - (this._clickOffset ? this._clickOffset.left : 0);
		} else {
			pixelMouse = position.y - this.elementOffset.top - (this._clickOffset ? this._clickOffset.top : 0);
		}
		return pixelMouse;

	},

	_start: function(event, index) {
		this._trigger("_start", event, this.value());
	},

	_split: function(event, newVal) {
		if (newVal != this.value()) {
			var allowed = this._trigger("split", event, newVal);
			if (allowed !== false) {
				this.value(newVal);
			}
		}
	},

	_stop: function(event, index) {
		var self = this;

		//$next = this.element.next();
		$prev = this.element.prev();
		$prev.id = $prev.attr('id');
		var container = $.wbs.getClosestLayout($prev.id);
		var prevEl = $.wbs.getObjByKey($prev.id, container.layout,true);
		//$('#title').html($('#title').html()+$prev.id+' ')
		
		if (this.options.orientation == 'vertical') {
			this.element.css({left: 0});
			prevEl.width = (this.prevElSize.width + this.options.value).toString();
		} else {
			this.element.css({top: 0});
			prevEl.height = (this.prevElSize.height + this.options.value).toString();
		}

		
		container.resizeAll();
		if (typeof($.cookie)=="function" && this.element.attr('id')=='splitter2'){
			$.cookie('grid-height',$('#grid .ui-grid-wrapper:first').height()+30)
		}
		this._trigger("_stop", event, {value: this.value(), id: self.element.attr("id")});
	},

	_change: function(event, index) {
		this._trigger("_change", event, this.value());
	},

	value: function(newValue) {

		if (arguments.length) {
			this.options.value = this._trimValue(newValue);
			this.refresh();
			this._change(null, 0);
		}

		return this._value();

	},

	_setOption: function(key, value) {

		$.Widget.prototype._setOption.apply(this, arguments);

		switch (key) {
			case 'disabled':
				if (value) {
					this.handles.filter(".ui-state-focus").blur();
					this.handles.removeClass("ui-state-hover");
					this.handles.attr("disabled", "disabled");
					this.element.addClass("ui-disabled");
				} else {
					this.handles.removeAttr("disabled");
					this.element.removeClass("ui-disabled");
				}
			case 'orientation':

				this._detectOrientation();
				
				this.element
					.removeClass("ui-splitter-horizontal ui-splitter-vertical")
					.addClass("ui-splitter-" + this.orientation);
				this.refresh();
				break;
			case 'value':
				this.refresh();
				break;
		}

	},

	_step: function() {
		var step = this.options.step;
		return step;
	},

	_value: function() {
		var val = this.options.value;
		val = this._trimValue(val);

		return val;
	},

	_trimValue: function(val) {
		var o = this.options, self = this;

		if (val < this._valueMin()) val = this._valueMin();
		if (val > this._valueMax()) val = this._valueMax();
		
		if (self.orientation == 'horizontal' && val > $(self.element).parent().width() - o.handleSize) val = $(self.element).parent().width() - o.handleSize;
		if (self.orientation == 'vertical' && val > $(self.element).parent().height() - o.handleSize) val = $(self.element).parent().height() - o.handleSize;

		return val;
	},

	_valueMin: function() {
		var valueMin = this.options.min;
		return valueMin;
	},

	_valueMax: function() {
		var valueMax = this.options.max;
		return valueMax;
	},
	
	refresh: function(value) {
		var o = this.options, self = this;
		if (typeof(value) == "undefined"){
			var value = this.value();
		}
		if (o.orientation == 'vertical') {
			this.element.css({left: value});
		} else {
			this.element.css({top: value});
		}
		
		//this._resizePanels(value);
	}
	
});

//$.extend($.ui.splitter, {
//	eventPrefix: "split"
//});

