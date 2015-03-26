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

(function($) {
var dragged = false;
$.widget("ui.splitter", $.ui.mouse, {
	options: {
		distance: 0,
		initSize: 200,
		max: 100,
		min: 0,
		orientation: 'horizontal',
		step: 1,
		value: 0,
		collapsed: false,
		collapsedValue: 0,
		handleSize: 7,
		handleBorder: 2
	},
	_resizePanels: function(value) {
		var self = this, o = this.options;
		var windowHeight = $(window).height();
		//var elementHeight = $(self.element).height();
		/*var currentHeight = elementHeight;
		if (windowHeight >= elementHeight) */
		currentHeight = windowHeight;

		//currentHeight = currentHeight - windowHeight;
		if (o.orientation == 'horizontal') {
			$(self.element).css({
				height: currentHeight
			});
			
			this.firstDiv.css({
				float: 'left',
				height: currentHeight - o.handleBorder,
				width: value
			});
			this.handle.css({
				height: currentHeight - o.handleBorder
			});

			this.secondDiv.css({
				float: 'left',
				width: this.element.width() - value - o.handleSize - o.handleBorder
			});
		} else {
			$(self.element).css({
				height: currentHeight// - $(self.element).height()
			});

			this.firstDiv.css({
				height: value
			});
			this.secondDiv.css({
				height: this.element.height() - value - o.handleSize - o.handleBorder
			});
			//this.handle.css({
			//	width: self.element.width() - o.handleBorder
			//});

		}
		this._trigger("_resizepanels", 0, value);
	},
	_create: function() {

		var self = this, o = this.options;
		this._detectOrientation();
		this._mouseInit();

		this.element
			.addClass("ui-splitter"
				+ " ui-splitter-" + this.orientation);
		
		if (o.disabled) {
			this.element.addClass('ui-splitter-disabled ui-disabled');
		}


		if (o.firstDiv !== undefined) {
			this.firstDiv = $('#'+o.firstDiv);
			this.firstDiv.addClass('ui-splitter-panel');
			this.element.append(this.firstDiv);
		}

		var handleDiv = $('<div></div>');
		
		if ($(".ui-splitter-handle", this.element).length == 0)
			handleDiv.appendTo(this.element)
				.addClass("ui-splitter-handle")
				.addClass("ui-widget")
				.addClass("ui-widget-content");
		
		if (o.secondDiv !== undefined) {
			this.secondDiv = $('#'+o.secondDiv);
			this.secondDiv.addClass('ui-splitter-panel');
			this.element.append(this.secondDiv);
		}
		
		
		this.handles = $(".ui-splitter-handle", this.element)
			.addClass("ui-corner-all");

		this.handle = this.handles.eq(0);
		if (o.orientation == 'horizontal') {
			this.handle.css({
				float: 'left',
				height: $(self.element).height(),
				width: o.handleSize-o.handleBorder,
				cursor: 'e-resize',
				'z-index': 2
			});
		} else {
			this.handle.css({
				//width: '100%',
				cursor: 'n-resize',
				height: o.handleSize-o.handleBorder,
				'z-index': 2
			});
		}

		this.element.bind(
			'resize',
			function(){
				self._refreshValue(self._getRealValue());
			}
		);
		
		this.handle
			.click(function(event) {
				event.preventDefault();
			})
			.dblclick(function(event) {
				event.preventDefault();
				self.collapse();
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
					$(".ui-splitter .ui-state-focus").removeClass('ui-state-focus'); $(this).addClass('ui-state-focus');
				} else {
					$(this).blur();
				}
			})
			.blur(function() {
				$(this).removeClass('ui-state-focus');
			});

		//this._resizePanels(o.initSize);
		if(o.initSize>0) {
			this.value(o.initSize);
		} else {
			this._refreshValue();
		}

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
		//console.log($(this.element).attr('id'))
		//alert('#'+$(this.element).attr('id')+' .ui-splitter-handle')
		var mouseOverHandle = $(event.target).is('#'+$(this.element).attr('id')+' .ui-splitter-handle');
		if (mouseOverHandle && !dragged) {
			dragged = !dragged;
			if (o.disabled)
				return false;
	
			this.elementSize = {
				width: this.element.outerWidth(),
				height: this.element.outerHeight()
			};
			this.elementOffset = this.element.offset();
	
			var position = { x: event.pageX, y: event.pageY };
			//var normValue = this._normValueFromMouse(position);
	
			var distance = this._valueMax() - this._valueMin() + 1, closestHandle;
			var self = this, index;
			
			closestHandle = this.handle;
			index = 0;
	
			this._start(event, index);
	
			self._handleIndex = index;
	
			closestHandle
				.addClass("ui-state-active")
				.focus();
			
			this._clickOffset = {left: 0, top: 0};
	
			normValue = this._normValueFromMouse(position);
			this._split(event, index, normValue);
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
		this._split(event, this._handleIndex, normValue);

		return false;

	},

	_mouseStop: function(event) {

		dragged = !dragged;
		this.handles.removeClass("ui-state-focus");
		this.handles.removeClass("ui-state-active");
		this._stop(event, this._handleIndex);

		this._change(event, this._handleIndex);
		this._handleIndex = null;
		this._clickOffset = null;

		return false;

	},
	
	_detectOrientation: function() {
		this.orientation = this.options.orientation == 'vertical' ? 'vertical' : 'horizontal';
	},

	_normValueFromMouse: function(position) {

		var pixelTotal, pixelMouse;
		if ('horizontal' == this.orientation) {
		//	pixelTotal = this.elementSize.width;
			pixelMouse = position.x - this.elementOffset.left - (this._clickOffset ? this._clickOffset.left : 0);
		} else {
		//	pixelTotal = this.elementSize.height;
			pixelMouse = position.y - this.elementOffset.top - (this._clickOffset ? this._clickOffset.top : 0);
		}

		//var percentMouse = (pixelMouse / pixelTotal);
		/*if (percentMouse > 1) percentMouse = 1;
		if (percentMouse < 0) percentMouse = 0;
		if ('vertical' == this.orientation)
			percentMouse = 1 - percentMouse;

		var valueTotal = this._valueMax() - this._valueMin(),
			valueMouse = percentMouse * valueTotal,
			valueMouseModStep = valueMouse % this.options.step,
			normValue = this._valueMin() + valueMouse  - valueMouseModStep;

		//if (valueMouseModStep > (this.options.step / 2))
		//	normValue += this.options.step;
			
		return parseFloat(normValue.toFixed(5));*/
		return pixelMouse;

	},

	_start: function(event, index) {
		this._trigger("start", event, this.value());
	},

	_split: function(event, index, newVal) {
		if (newVal != this.value()) {
			var allowed = this._trigger("split", event, newVal);
			if (allowed !== false) {
				this.value(newVal);
			}
		}
	},

	_stop: function(event, index) {
		this._trigger("stop", event, this.value());
	},

	_change: function(event, index) {
		this._trigger("change", event, this.value());
	},

	value: function(newValue) {

		if (arguments.length) {
			this.options.value = this._trimValue(newValue);
			this._refreshValue();
			this._change(null, 0);
		}

		return this._value();

	},
	
	_getRealValue: function() {
		var self = this, o = this.options;
		if (o.orientation == 'horizontal') {
			return self.firstDiv.css('width').replace(/px,*\)*/g,"");
		} else {
			return self.firstDiv.css('height').replace(/px,*\)*/g,"");
		}
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
				this._refreshValue();
				break;
			case 'value':
				this._refreshValue();
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

	collapse: function() {
		var self = this, o = this.options;

		var oldValue = o.value;

		/* new
		if (o.collapsed && o.value < this._valueMin() && o.collapsedValue < this._valueMin()) {
			o.collapsedValue = ( this._valueMin() + (this._valueMin() + this._valueMax()) / 4);
		}
		
		o.collapsed = !o.collapsed;

		this._refreshValue(o.collapsedValue);
		o.collapsedValue = oldValue;
		*/

		this._trigger("collapse", 0, o.value);
		//console.log(o.collapsed, o.collapsedValue);
		
	},
	_trimValue: function(val) {
		var o = this.options, self = this;

		//if (!o.collapsed && val < this._valueMin()) {
		//	o.collapsedValue = this._valueMin();
		//}
		/* new
		if (!o.collapsed && val <= 0) {
			o.collapsed = true;
			this._trigger("collapse", 0, val);
		}

		if (o.collapsed && val >= this._valueMin()) {
			o.collapsed = false;
		}

		if (!(o.collapsed && val < this._valueMin())){
			if (val > this._valueMax()) val = this._valueMax();
		}
		*/
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
	
	_refreshValue: function(value) {
		var o = this.options, self = this;
		if (value === undefined){
			var value = this.value();
		}
		//if (!(value != 0 && value < this._valueMin())) {
			this._resizePanels(value);
		//}
		/*	valueMin = this._valueMin(),
			valueMax = this._valueMax(),
			valPercent = valueMax != valueMin
				? (value - valueMin) / (valueMax - valueMin) * 100
				: 0;
		*/
		/*valPixel = value;
		var _set = {}, _size1 = {}, _size2 = {};
		var widthOrHeight = (self.orientation == 'horizontal' ? 'width' : 'height');
		var leftOrTop = (self.orientation == 'horizontal' ? 'left' : 'top');
		var secondDivSize = (self.orientation == 'horizontal' ?
				self.element.width() - valPixel - o.handleSize : 
				self.element.height() - valPixel - o.handleSize)
		_set[leftOrTop] = valPixel + 'px';
		_size1[widthOrHeight] = valPixel + o.handleSize + 'px';
		_size2[widthOrHeight] = secondDivSize + 'px';
		this.handle.css(_set);
		this.firstDiv.css(_size1);
		this.secondDiv.css(_size2);*/
	}
	
});

//$.extend($.ui.splitter, {
//	eventPrefix: "split"
//});

})(jQuery);
