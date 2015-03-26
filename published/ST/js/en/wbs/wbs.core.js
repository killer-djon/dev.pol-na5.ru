$.wbs = $.extend(true, $.wbs, $.ui);

$.wbs = $.extend(true, $.wbs, {
	version: '1.0', 
	ajaxComplete: function(response, params){
		if (response) {
			if (response.status == 'ERR' &&  typeof(response.error) == "string") {
				if (typeof(console) != "undefined"){
					console.log("Ajax request:", params);
					console.log("Ajax error:", response.error);
				}
			}
			if (response.status == 'OK' &&  response.data !== undefined) {
				return response.data;
			}
		} else {
			if (typeof(console) != "undefined"){
				console.log("Ajax request:", params);
				console.log("Ajax error:", response.error);
			}
		}
		return false;
	},
	
	ajaxRequest: function(options) {
		var defaults = {
			async: true, 
	    	ifModified: false,
	    	dataType: 'json',
	    	requestMethod: 'get',
	    	url: '/',
	    	data: []
		};
	    var o = $.extend({}, defaults, options);

		$.ajax({
			async: o.async,
			url: o.url,
			dataType: o.dataType,
			type:  o.requestMethod,
			ifModified: o.ifModified,
			data: o.data,
			error: function(obj, msg){
				if (typeof(console) != "undefined") { console.log( "Ajax error: ", msg); }
			},
			success: function(response){    
				data = $.wbs.ajaxComplete(response, this);
				if (typeof(o.callback)=="function" && data !== null) o.callback(data);
			}
		});
		
	    return true;
	},
	getById: function(nodeId, $root){
		if ($root && $root.attr('id')!=nodeId) {
			return $('#'+nodeId, $root);
		} else {
			return $('#'+nodeId);
		}
	},
	getData: function(objId){
		return $.extend(true, $('#'+objId), $.data(document.getElementById(objId)) )
	},
	setData: function(objId, key, val){
		return $.data(document.getElementById(objId), key, val);
	},
	get: function(key){
		return $.data(document.body, key);
	},
	set: function(key, val){
		return $.data(document.body, key, val);
	},
	getLayout: function(objId){
		return this.getData(objId)['layout'];
	},
	getClosestLayout: function(objId){
		var layout = $('#'+objId).closest('.ui-layout');
		return this.getData(layout.attr('id'))['layout'];
	},
	getFirstChild: function(obj){
		for(var id in obj){
			return {id: id, value: obj[id]};
		}
		return {id: false, value: false};
	},
	in_array: function(el, arr){
		for(var id in arr){
			if (arr[id] == el) return id;
		}
		return false;
	},
	cnt: 0,
	getObjByKey: function(key, obj, recurrent){
		if (!recurrent){
			if (obj.hasOwnProperty(key)) {
				return obj[key];
			}
			return false;
		} else {
			for(var tmpKey in obj){
				if (tmpKey!='data')
				if (!(this.getObjByKeyResult && (this.getObjByKeyResult.id == key)))
				if (typeof(obj[tmpKey])=="object"){
					if (obj[tmpKey].id != key){
						this.getObjByKey(key, obj[tmpKey], true)
					} else {
						this.getObjByKeyResult = obj[tmpKey];
						break;
					}
				}
			}
			return this.getObjByKeyResult;
		}
	},
	collectSiblings: function(siblings){
		var $tmpContainer = $('<div/>');
		for (var id in siblings){
			$tmpContainer.append(siblings[id])
		}
		return $tmpContainer.children();
	},
	override: function(parentObj, newObj){
		return $.extend(true, $.extend(true, {}, parentObj), newObj);
	},
	trim: function(str, type){
		str = str.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
		if (type === true) {
			return encodeURIComponent(str); 
		} else if (type === false) {
			return decodeURIComponent(str);
		}
		return str;
	},
	is_numeric: function(mixed_var) {
		return (typeof(mixed_var) === 'number' || typeof(mixed_var) === 'string') && mixed_var !== '' && !isNaN(mixed_var);
	},
	camelCase: function(strArr){
		var str = "";
		for (key in strArr){
			if (key == 0) {
				str = strArr[key].substr(0,1).toLowerCase() + strArr[key].substr(1);
			} else {
				str += strArr[key].substr(0,1).toUpperCase() + strArr[key].substr(1);
			}
		}
		return str;
	},
	/*getScrollBarWidth: function() {
	     var $inner = $('<p/>',{css:{width:'100%',height:200}});  
	   
	     var $outer = $('<div/>',
	    		 {css:{
	    	 		position:'absolute',top:0,left:0,visibility:'hidden',width:200,height:150,overflow:'hidden'
	    	 	}});  
	     $outer.append($inner);  
	   
	     $('body').append($outer);  
	     var w1 = $inner.width();  
	     $outer.css('overflow','scroll');  
	     var w2 = $inner.width(); 
	     if (w1 == w2) w2 = $outer.width();  
	   
	     $outer.remove();
	   
	     return (w1 - w2);  
	 },*/
	 sizeType: function(){
		 return {'width': 0, 'height': 0};
	 },
	 classType: function(){
		 return {'name': 0, 'value': 0}
	 },
	encHTML: function(html) {
	  return html.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); 
	},
	decHTML: function(html) { 
	  return html.replace(/&amp;/g,'&').replace(/&lt;/g,'<').replace(/&gt;/g,'>'); 
	},
	loadHash: function(link){
		var hash = link;
		hash = hash.replace(/\/\//g, "/");
		hash = hash.replace(/^.*#/, '');
		if (location.hash.length < 2){
			location = location.protocol +'//'+ location.host + location.pathname +'#'+ hash;
			return false;
		}
		//alert("Current hash:" + link);
		if (parent && !$.browser.msie) {
			parent.window.location.hash = hash;
		} else {
			location.hash = hash;
		}
		return true;
		//$.historyLoad(hash);
	},
	toggleHashParam: function(param){
		var hash = location.hash;
		if (hash.search(param) == -1){
			this.addToHash(param);
		} else {
			this.removeFromHash(param);
		}
	},
	addToHash: function(param){
		var hash = location.hash;
		if (hash.search(param) == -1){
			hash+='/'+param+'/';
		}
		this.loadHash(hash);
	},
	removeFromHash: function(param){
		var hash = location.hash;
		if (hash.search(param) > -1){
			hash = hash.replace(param, "")
		}
		this.loadHash(hash);
	},
	 implode: function(glue, pieces) {
	    var i = '', retVal='', tGlue='';
	    if (arguments.length === 1) {
	    	pieces = glue;
	        glue = '';
	    }
	    if (typeof(pieces) === 'object') {
	        if (pieces instanceof Array) {
	        	return pieces.join(glue);
	        } else {
	            for (i in pieces) {
	                retVal += tGlue + pieces[i];               
	                tGlue = glue;
	            }
	            return retVal;
	        }
	    } else {
	        return pieces;
	    }
	}
});

$.fn.extend({
    insertAtCaret: function(myValue){
	  this.each(function(i) {
	    if (document.selection) {
	      this.focus();
	      sel = document.selection.createRange();
	      sel.text = myValue;
	      this.focus();
	    }
	    else if (this.selectionStart || this.selectionStart == '0') {
	      var startPos = this.selectionStart;
	      var endPos = this.selectionEnd;
	      var scrollTop = this.scrollTop;
	      this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
	      this.focus();
	      this.selectionStart = startPos + myValue.length;
	      this.selectionEnd = startPos + myValue.length;
	      this.scrollTop = scrollTop;
	    } else {
	      this.value += myValue;
	      this.focus();
	    }
	  })
	},
	appendToDeepest: function(obj) {
		var $last = $(obj).find(":last");
		if ($last.length > 0) {
			$last.append(this);
		} else {
			$(obj).append(this);
		}
	},
	hasVisibleScrollbar: function(){
		
		var childrenSize = $.wbs.sizeType();
		
		this.children().each(function(){
			var $this = $(this);
			$this.rightPoint = $this.outerWidth(true)// + $this.position().left;
			if (childrenSize.width < $this.rightPoint){
				childrenSize.width = $this.rightPoint;
			}
			$this.bottomPoint = $this.outerHeight(true)// + $this.position().top;
			if (childrenSize.height < $this.bottomPoint){
				childrenSize.height = $this.bottomPoint;
			}
		});
		//console.log(this, 'childrenHeight', childrenSize.height, 'thisHeight', this.height())
	},
	selectAndFocus: function(){
		$(this).select();
		$(this).focus();
	},
	addError: function(type, params){
		var $this = $(this), value = '', label = '';

		if ($this.hasClass('ui-editor-editablefield')) {
			label = $('label[for="'+$this.parent().attr('id')+'"]').text();
		} else {
			label = $('label[for="'+$this.attr('id')+'"]').text();
		}
		label = label.replace(":", "");

		$this.keyup(function(){
			if ($this.is(':input')) {
				value = $this.val().trim();
			} else {
				value = $this.text().trim();
			}
			
			switch (type){
				case 'required':
					var errorId = 'err_'+$this.attr('id');
					if (value == "") {
						if (!$('#'+errorId).length){
							$('.errors-msgbox',$this.closest("form"))
							.append('<p id="'+errorId+'">Field "'+label+'" is required</p>');
							$this.addClass("error ui-state-error");
						}
					} else {
						$('#err_'+$this.attr('id')).remove(); 
						$this.removeClass("error ui-state-error");
					}
					break;
				case 'any':
					var emptySiblings = true, siblingsValue = "";
					for (key in params) {
						var $el = params[key];
						if ($el.is(':input')) {
							siblingsValue = $el.val().trim();
						} else {
							siblingsValue = $el.text().trim();
						}
						if (siblingsValue != "") emptySiblings = false;
					}

					var error = "", siblingLabel="", siblingLabels=[], errorId = "";
					error = $('<p>One of this fields should not be empty: </p>');

					siblingLabels.push('"'+label+'"');
					for (key in params) {
						if (params[key].hasClass('ui-editor-editablefield')) {
							siblingLabel = $('label[for="'+params[key].parent().attr('id')+'"]').text();
						} else {
							siblingLabel = $('label[for="'+params[key].attr('id')+'"]').text();
						}
						siblingLabel = siblingLabel.replace(":", "");
						siblingLabels.push('"'+siblingLabel+'"');
					}
					errorId = 'err_' + $.wbs.implode("", siblingLabels.sort()).replace(/"/gi, "");
					
					if (value == "" && emptySiblings){
						if (!$('#'+errorId).length){
							error.append($.wbs.implode(", ", siblingLabels.sort()));
							error.attr('id',errorId);
							$('.errors-msgbox',$this.closest("form")).append(error);
							$this.addClass("ui-state-error");
							for (key in params) {
								params[key].addClass("ui-state-error");
							}
						}
						
					} else {
						$('#'+errorId).remove(); 
						$this.removeClass("ui-state-error");
						for (key in params) {
							params[key].removeClass("ui-state-error");
						}
					}
					break;
			}
		})
		
		$this.blur(function(){$(this).keyup()})
	}
});

var focusedElement = null;
$( ':input').live('focus', function() {
	focusedElement = this;
});
$( ':input').live('blur', function() {
	focusedElement = null;
});

jQuery.focused = function() {
	if (focusedElement) return $(focusedElement).is(':visible');
	return focusedElement;
}

jQuery.cookie = function(name, value, options) {
    if (typeof value != 'undefined') { // name and value given, set cookie
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = 1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        // CAUTION: Needed to parenthesize options.path and options.domain
        // in the following expressions, otherwise they evaluate to undefined
        // in the packed version for some reason...
        var path = options.path ? '; path=' + (options.path) : '';
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get cookie
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jQuery.trim(cookies[i]);
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
}

