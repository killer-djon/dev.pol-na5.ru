//Class
/*
 * elem: DOMElement,
 * widthCss: Number,
 * startValue: Number,
 * endValue: Number,
 * defaultValue: Number,
 * onChange: function(e) {
 * 		e.pos
 * }
 * onChangeComplite: function(e) {
 * 		e.pos
 * }
 */
 
(function( $ ){
	$.fn.slider = function(options){
		
		config = {
			widthCss: 100,
			onChangeComplite: function(){}
		};
		
		if(options) {
            $.extend(options, config);
        }
        
        var isThumbDrag = false;
        var pos = 0;
        var kof = 0;
		
		return this.each(function() {
			var $sliderBasic = $('<div></div>').addClass('slider-basic');
			$sliderBasic.css({width: config.widthCss});
			
			$(this).append($sliderBasic);
			
			var $sliderHorz = $('<div></div>').addClass('slider-horz');
			$sliderBasic.append($sliderHorz);
			
			var $sliderHorzEnd = $('<div></div>').addClass('slider-horz-end');
			$sliderHorz.append($sliderHorzEnd);
			
			var $sliderHorzInner = $('<div></div>').addClass('slider-horz-inner');
			$sliderHorzEnd.append($sliderHorzInner);
			
			var $sliderHorzThumb = $('<div></div>').addClass('slider-horz-thumb');		
			$sliderHorzInner.append($sliderHorzThumb);
			
			var $aTag = $('<a href="#"></a>').addClass('slider-focus');
			$sliderHorzInner.append($aTag);
			
			$(document).mouseup(function(e){
				isThumbDrag = false;
				
				config.onChangeComplite({pos: this.pos});				
			});
			$sliderHorzThumb.mousedown(function(e){
				isThumbDrag = true;
			});
			$(document).mousemove(function(e){
				if( isThumbDrag ) {
					var x = getMouseOffset($sliderBasic.get(0), e).x;
					x = ( x < 7 ) ? 7 : x;
					x = ( x > config.widthCss - 7 ) ? config.widthCss - 7 : x;
					x -= 14;
					$sliderHorzThumb.css({left: x});
					
					pos = config.startValue + (x+7)/kof;
					config.onChange({
						x: x + 7,
						pos: this.pos
					});
				}
			});			
		});
	};
})( jQuery );

 
function WsbSlider(config) {
	this.config = config;
	this.isThumbDrag = false;
	this.widthCss = (config.widthCss) ? config.widthCss  : 100;
	this.onChange = config.onChange;
	this.onChangeComplite = config.onChangeComplite;

	this.startValue = (config.startValue) ? config.startValue : 0;
	this.endValue = (config.endValue) ? config.endValue : this.startValue + this.widthCss;
	
	var widthV = this.endValue - this.startValue;
	this.kof = this.widthCss / widthV;	
	
	
	this.render = function() {
		var contener = this.config.elem;
		
		var sliderBasic = createDiv("slider-basic");
		sliderBasic.style.width = this.widthCss + "px";
		contener.appendChild(sliderBasic);
		
//		$(sliderBasic).click(function(e){
//			
////			var left = e.clientX - $(this).position().left + 7;
////			$('.slider-horz-thumb').css({left: left});
//			$('.slider-horz-thumb').get(0).moveThis(e);			
//			return false;
//		});
		
		this.baseContener = sliderBasic;
		
		var sliderHorz = createDiv("slider-horz");
		sliderBasic.appendChild(sliderHorz);
		
		var sliderHorzEnd = createDiv("slider-horz-end");
		sliderHorz.appendChild(sliderHorzEnd);
		
		var sliderHorzInner = createDiv("slider-horz-inner");
		sliderHorzEnd.appendChild(sliderHorzInner);
		
		var sliderHorzThumb = createDiv("slider-horz-thumb");		
		sliderHorzInner.appendChild(sliderHorzThumb);
		this.thumbElem = sliderHorzThumb;
		this.thumbElem.moveThis = function(e){

			this.thumbMouseDownHandle(e);
			this.thumbMouseMoveHandle(e);
			this.thumbMouseUpHandle(e, true);
		}.bind(this);
		
		var aTag = createElem("a", "slider-focus");
		sliderHorzInner.appendChild(aTag);
	};
	
	this.thumbMouseUpHandle = function (e, isClick) {	
		if (this.isThumbClick || this.isSliderClick) {
			this.isThumbDrag = false;
			this.isThumbClick = false;
			this.isSliderClick = false;
			
			var x = getMouseOffset(this.baseContener, e).x;
				x = ( x < 7 ) ? 7 : x;
				x = ( x > this.widthCss - 7 ) ? this.widthCss - 7 : x;
				x -= 14;
				this.thumbElem.style.left = x + "px";
				
			this.pos = this.startValue + (x+7)/this.kof;
			if (this.onChange) this.onChange({
				x: x + 7,
				pos: this.pos
			});
			
			if (this.onChangeComplite) 
				this.onChangeComplite({pos: this.pos});
		}
	}.bind(this);
	
	this.UpHandle = function(e){
		this.isThumbDrag = false;
		this.onChangeComplite({pos: this.pos});
	}.bind(this),
	
	this.thumbMouseDownHandle = function (e) {
		this.isThumbDrag = true;
		this.isThumbClick = true;
		
		//addClass(this.thumbElem, "slider-horz-thumb-over");
		if (e)
		e.preventDefault();

	}.bind(this);
	
	this.sliderMouseDownHandle = function () {
		this.isSliderClick = true;
	}.bind(this);
	
	this.thumbMouseMoveHandle = function (e) {
		if( this.isThumbClick ) {
			var x = getMouseOffset(this.baseContener, e).x;
			x = ( x < 7 ) ? 7 : x;
			x = ( x > this.widthCss - 7 ) ? this.widthCss - 7 : x;
			x -= 14;
			this.thumbElem.style.left = x + "px";
			
			this.pos = this.startValue + (x+7)/this.kof;
			if (this.onChange) this.onChange({
				x: x + 7,
				pos: this.pos
			});
		}
	}.bind(this);	
	
	this.setPosition = function (pos) {		
		var x = (pos - this.startValue) * this.kof - 7;
		this.thumbElem.style.left = x + "px";
	}
	
	this.init = function () {		
		$(document).bind('mouseup', this.thumbMouseUpHandle);
		
		$(this.thumbElem).bind('mousedown', this.thumbMouseDownHandle);
		$(this.baseContener).bind('mousedown', this.sliderMouseDownHandle);
		
		$(document).bind('mousemove', this.thumbMouseMoveHandle);
	};
	
	this.destroy = function() {
		$(document).unbind('mouseup', this.thumbMouseUpHandle);
		
		$(this.thumbElem).unbind('mousedown', this.thumbMouseDownHandle);
		$(this.baseContener).unbind('mousedown', this.sliderMouseDownHandle);
		
		$(document).unbind('mousemove', this.thumbMouseMoveHandle);
	};
	
	this.render();
	this.init();
	if (this.config.defaultValue)
		this.setPosition(this.config.defaultValue);

}

//////// FUNCTIONS

function mousePageXY(e)
{
  var x = 0, y = 0;

  if (!e) e = window.event;

  if (e.pageX || e.pageY)
  {
    x = e.pageX;
    y = e.pageY;
  }
  else if (e.clientX || e.clientY)
  {
    x = e.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft) - document.documentElement.clientLeft;
    y = e.clientY + (document.documentElement.scrollTop || document.body.scrollTop) - document.documentElement.clientTop;
  }

  return {"x":x, "y":y};
}

Number.prototype.NaN0=function() { return isNaN(this) ? 0 : this; }

var USER_DATA = {

    Browser: {
        KHTML: /Konqueror|KHTML/.test(navigator.userAgent) &&
                !/Apple/.test(navigator.userAgent),
        Safari: /KHTML/.test(navigator.userAgent) &&
                /Apple/.test(navigator.userAgent),
        Opera: !!window.opera,
        MSIE: !!(window.attachEvent && !window.opera),
        Gecko: /Gecko/.test(navigator.userAgent) &&
                !/Konqueror|KHTML/.test(navigator.userAgent)
    },

    OS: {
        Windows: navigator.platform.indexOf("Win") > -1,
        Mac: navigator.platform.indexOf("Mac") > -1,
        Linux: navigator.platform.indexOf("Linux") > -1
    }
}

var IS_IE = USER_DATA['Browser'].MSIE;

function getPosition(e){
    var left = 0;
    var top  = 0;

    while (e.offsetParent) {
        left += e.offsetLeft + (e.currentStyle ?
            (parseInt(e.currentStyle.borderLeftWidth)).NaN0() : 0);
        top  += e.offsetTop  + (e.currentStyle ?
            (parseInt(e.currentStyle.borderTopWidth)).NaN0() : 0);
        e = e.offsetParent;
    }

    left += e.offsetLeft + (e.currentStyle ?
            (parseInt(e.currentStyle.borderLeftWidth)).NaN0() : 0);
    top  += e.offsetTop  + (e.currentStyle ?
            (parseInt(e.currentStyle.borderTopWidth)).NaN0(): 0); 	

    return {x:left, y:top};
}

function getAlignedPosition(e) {
    var left = 0;
    var top  = 0;

    while (e.offsetParent) {
        left += e.offsetLeft + (e.currentStyle ?
            (parseInt(e.currentStyle.borderLeftWidth)).NaN0() : 0);
        top  += e.offsetTop  + (e.currentStyle ?
            (parseInt(e.currentStyle.borderTopWidth)).NaN0() : 0);
        e  = e.offsetParent;
        if (e.scrollLeft) {left -= e.scrollLeft; }
        if (e.scrollTop)  {top  -= e.scrollTop; }
    }

    var docBody = document.documentElement ?
        document.documentElement : document.body;

    left += e.offsetLeft +
        (e.currentStyle ?
                (parseInt(e.currentStyle.borderLeftWidth)).NaN0()
                : 0) +
        (IS_IE ? (parseInt(docBody.scrollLeft)).NaN0() : 0) -
        (parseInt(docBody.clientLeft)).NaN0();
    top  += e.offsetTop  +
        (e.currentStyle ?
                (parseInt(e.currentStyle.borderTopWidth)).NaN0()
                :  0) +
        (IS_IE ? (parseInt(docBody.scrollTop)).NaN0() : 0) -
        (parseInt(docBody.clientTop)).NaN0();

    return {x:left, y:top};
}

function mouseCoords(ev) {

    if (ev.pageX || ev.pageY) {
        return {x:ev.pageX, y:ev.pageY};
    }

    var docBody = document.documentElement
                        ? document.documentElement
                        : document.body;

    return {
        x: ev.clientX + docBody.scrollLeft - docBody.clientLeft,
        y: ev.clientY + docBody.scrollTop  - docBody.clientTop
    };
}

function getMouseOffset(target, ev, aligned) {
    ev = ev || window.event;
    if (aligned == null) aligned = false;

    var docPos    = aligned
        ? getAlignedPosition(target)
        : getPosition(target);
    var mousePos  = mouseCoords(ev);

    return {
        x: mousePos.x - docPos.x,
        y: mousePos.y - docPos.y
    };
}

function createDiv(className) {
	return createElem("div", className);
}

function createElem(tag, className) {
	var elem = document.createElement(tag);
	if (className)
		elem.className = className;
	return elem;	
}

function addClass(elem, className) {
	var classes = elem.className.split(" ");
	for (var i = 0; i < classes.length; i++) {
		if (classes[i] == className)
			return;
	}	
	classes[classes.length] = className;
	elem.className = classes.join(" ");
}

function removeClass (elem, className) {
	var classes = elem.className.split(" ");
	var newClasses = new Array ();
	for (var i = 0; i < classes.length; i++) {
		if (classes[i] == className)
			continue;
		newClasses[newClasses.length] = classes[i];
	}	
	elem.className = newClasses.join(" ");
}