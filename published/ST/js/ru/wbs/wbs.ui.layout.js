/*
 * WBS UI Layout
 *
 * Copyright (c) 2010
 *
 * Depends:
 *	jquery.ui.core.js
 *	jquery.ui.widget.js
 *	wbs.ui.splitter.js
 */

(function( $ ) {
$.widget("ui.layout", {
	options: {
		hiddenPanels: [],
		hiddenElements: [],
		resizeTimeout: 500,
		callback: false,
		onResize: function(){return false}
	},
	_parseClassName: function(cls){
		cls = cls.split(":");
		return {name: cls[0], value: cls[1]}
	},
	_applyAttrs: function(el){
		for (key in attr){
			el.addClass(key+':'+attr[key])
		}
		return el;
	},
	_buildDom: function(attr){
		var el = $("<div class='ui-layout-panel'></div>");
		/*for (key in attr){
			el.addClass(key+':'+attr[key])
		}*/
		el.attr('id',attr.id);
		return el;
	},
	_parsePanel: function($panelMarkup){
		var className = $panelMarkup.attr("class");
		var classArr = className.split(" ");
		var attr = [];

		attr['id'] = $panelMarkup.attr('id').replace(/-markup/,"");
		for(classEl in classArr){
			classArr[classEl] = $.trim(classArr[classEl]);
			var cls = classArr[classEl];
			if (cls != "") {
				var clsPair = this._parseClassName(classArr[classEl]);
				attr[clsPair.name] = clsPair.value;
			}
		}
		return attr;
	},
	clsSel: function(key,val){
		return '.'+key+'\\:'+val;
	},
	createPanelTree: function($markup, tree){
		for(var i=0;i<$markup.length;i++){
			var $panel = $($markup[i]);
			//this.layout = []
			$panel.attr = this._parsePanel($panel);
			tree[$panel.attr.id] = new Object;
			tree[$panel.attr.id].attr = $panel.attr;
			var $children = $panel.children();
			if($children.length > 0){
				this.createPanelTree($children, tree[$panel.attr.id]);
			}
		}
		return tree;
	},
	_cssValParse: function(val,parentVal){
		if (val) {
			val = val.replace(/medium/,"1");
			
			if (val.indexOf('%')>0) {
				val = val.replace(/\%/g,"");
				val = Math.round((parentVal) * (val / 100));
			} else {
				val = val.replace(/px/g,"");
			}
			return parseInt(val,10)
		} else {
			return 0
		}
	},
	_getContentSize: function($el){
		return {
			width: $el.outerWidth(),
			height: $el.outerHeight()
		}
	},
	getMaxChildrenSize: function($node){

		var $parent = $node.parent(),
			childrenSize = {width: 0, height: 0};
		
		$node.children().each(function(){
			var $this = $(this);
			if (!$this.hasClass('ui-layout-panel')){
				$this.rightPoint = $this.outerWidth(true) + $this.position().left;
				if (childrenSize.width < $this.rightPoint){
					childrenSize.width = $this.rightPoint;
				}
				$this.bottomPoint = $this.outerHeight(true) + $this.position().top;
				if (childrenSize.height < $this.bottomPoint){
					childrenSize.height = $this.bottomPoint;
				}
			}
			
		});
		var $nodePosition = $node.position();
		if (!$nodePosition) $nodePosition = {left: 0, top: 0}
		childrenSize.width = Math.floor(childrenSize.width - $nodePosition.left +2);
		childrenSize.height = Math.floor(childrenSize.height - $nodePosition.top);

		return childrenSize;
	},
	getOverallChildrenSize: function($node){

		var childrenSize = {width: 0, height: 0};
		
		$node.children().children().each(function(){
			var $this = $(this);
				childrenSize.width += $this.outerWidth(true);
			}
		);
		return childrenSize;
	},
	getEmptySpace: function($node){
		var self = this,
			$parent = $node.parent(),
			siblingsSize = {width: 0, height: 0},
			$siblings = $parent.children(),
			parent = $.wbs.getObjByKey($parent.attr('id'),this.layout,true),
			node = $.wbs.getObjByKey($node.attr('id'),self.layout,true),
			curWidth = node.width,
			curHeight = node.height,
			considerPercentWidth = !(curWidth && curWidth.search('%')),
			considerPercentHeight = !(curHeight && curHeight.search('%'));

			$siblings.each(function(){
				var $this = $(this);
							
				$this.node = $.wbs.getObjByKey($this.attr('id'),self.layout,true);
				$this.curWidth = node.width;
				$this.curHeight = node.height;
				if ($this.is(':visible'))
				if ($this.not( $node ).length 
					&& $this.hasClass('ui-layout-panel')){

					if (parent && parent.stack=="h") {
						if ($this.curWidth && $this.curWidth.search('%')){
							if (considerPercentWidth){
								siblingsSize.width += $this.outerWidth();
							}
						} else {
							siblingsSize.width += $this.outerWidth();
						}
					}
					if (parent && parent.stack!="h") {
					if ($this.curHeight && $this.curHeight.search('%')){
						if (considerPercentHeight){
							siblingsSize.height += $this.outerHeight();
						}
					} else {
						siblingsSize.height += $this.outerHeight();
					}
					}
					//if ($this.curHeight && ($this.curHeight.search('%')))
					//	siblingsSize.height += $this.outerHeight();
				}
			})
			$parent.widthVal = $parent.width() - siblingsSize.width;
			$parent.heightVal = $parent.height() - siblingsSize.height;
			
		return {width: $parent.widthVal, height: $parent.heightVal, siblingsSize: siblingsSize}
	},
	getEmptySpaceBorders: function($node){
		var self = this,
			$parent = $node.parent(),
			bordersSize = {left: 0, right: $parent.position().left + $parent.outerWidth(true)},
			$siblings = $parent.children(),
			rightBorder = bordersSize.right;
		
			$siblings.each(function(){
				var $this = $(this);
				
				if ($this.not( $node ).length && $this.hasClass('ui-layout-panel')){

					$this.leftPoint = $this.position().left;
					$this.rightPoint = $this.outerWidth(true) + $this.position().left;
					//console.log((bordersSize.left < $this.rightPoint) && ($this.rightPoint != ($parent.position().left + $parent.outerWidth(true))));
					if ((bordersSize.left < $this.rightPoint) && $this.rightPoint != rightBorder){
						bordersSize.left = $this.rightPoint;
					}
					if ((bordersSize.right > $this.leftPoint) && $this.rightPoint == rightBorder){
						bordersSize.right = $this.leftPoint;
					}	
				}
			})
		return {left: bordersSize.left, right: bordersSize.right}
	},
	_applyAttr: function(node,parent){
		var self = this,
			attr = node.value.attr,
			$node = $.wbs.getById(node.id,$('#'+parent.id)),
			css = new Object;

			var $parent = $('#'+parent.id);
		
		for (attrKey in attr){
			var val = attr[attrKey];
			switch (attrKey){
			case 'overflow':
				css['overflow'] = val
				break;
			case 'widget':
				if ($.wbs.widgets[val]) $.wbs.widgets[val]($node);
				for (var key in self.options.hiddenElements){
					$('#'+self.options.hiddenElements[key]).hide()
				}
				break;
			case 'heightConstraint':
			case 'widthConstraint':
				var key = attrKey.replace(/Constraint/,"");
				var vals = attr[attrKey].split('-');
				for (valKey in vals){
					vals[valKey] = this._cssValParse(vals[valKey],$parent[key]());
				}
				css['min-'+key] = vals[0];
				break;
			}
		}

		if (node.id == 'grid') css['overflow'] = 'hidden';
		/*if (css.width && (css.width > css['min-width'])) {
			css.width=css['min-width'];
		}

		if (css.height && (css.height > css['min-height'])) {
			css.height=css['min-height'];
		}*/

		if (css) $node.css(css)
		
		if (attr.nowrap){
			var wrapper = $('<div/>');
			$node.children().wrapAll(wrapper);
		}
	//	this._setInnerSize($node,css.width, css.height)
	},
	_append: function(node,root){
		var $nodeDom = this._buildDom(node.value.attr);
		$.wbs.getById(root.id,this.element).append($nodeDom);
		if(root.attr && !root.attr.stack){
			$.wbs.getById(root.id,this.element).append('<div class="ui-layout-clear"></div>');
		}
	},
	_setInnerSize: function($node, param, val){
		if (val){
			val = val -
				this._cssValParse($node.css('border-left-width')) - 
				this._cssValParse($node.css('border-right-width')) - 
				this._cssValParse($node.css('padding-left')) - 
				this._cssValParse($node.css('padding-right'));
			$node.css(param,val);		
		}
	},
	_resize: function(node,parent){
		var self = this,
			attr = node.value.attr,
			css = {'width': false, 'height': false},
			$node = $.wbs.getById(node.id,$('#'+parent.id)),
			$parent = $('#'+parent.id);

		var emptySpace = this.getEmptySpace($node)
		
		/*if (attr.width){
			css.width = this._cssValParse(attr.width,emptySpace['width']);
			this._setInnerSize($node,'width',css.width)
		}
		if (attr.height){
			css.height = this._cssValParse(attr.height,emptySpace['height']);
			this._setInnerSize($node,'height',css.height)
		}*/
		
		switch (attr.fill){
			case 'none': 
				/*var size = this._getContentSize($node)
				if (attr.height) css['height'] = this._cssValParse(attr.height,$parent.height());
				if (attr.width) css['width'] = this._cssValParse(attr.width,$parent.width());
				if (!css['width'])
					css['width'] = size.width;
				if (!css['height'])
					css['height'] = size.height;
				css['overflow'] = 'auto';*/
				css['width'] = attr.width;
				css['height'] = attr.height;
				break;
			case 'all': 
				css['width'] = emptySpace.width;
				css['height'] = emptySpace.height;
				break;
			case 'h':
				css['width'] = emptySpace.width;
				if (attr.height) css['height'] = this._cssValParse(attr.height,emptySpace.height);
				break;
			case 'v': 
				css['height'] = emptySpace.height;
				if (attr.width) css['width'] = this._cssValParse(attr.width,emptySpace.width);
				break;
		}
		if (attr.heightOffset){
			css['height'] = parseInt(emptySpace.height) + parseInt(attr.heightOffset);
		}

		/*if (!attr.nowrap){
			var childrenSize = this.getMaxChildrenSize($node);
			if (attr.fill != "all"){
				if (attr.fill != "h" && childrenSize.width>css.width ) {
					$node.css('min-width', childrenSize.width);
					var parentWidth = childrenSize.width, siblingsSize = {'width': 0, 'height': 0};

						$node.parents().each(function(){
							var $this = $(this);
							if ($this.css('overflow') != "auto"){
								if (parseInt($this.css('min-width')) < childrenSize.width ) {
									parentWidth = 
										siblingsSize.width + parentWidth + $this.outerWidth(true) - $this.width();
									$this.css('min-width', parentWidth);
								}
								siblingsSize = self.getEmptySpace($this).siblingsSize;
							}
						})
				}
			}
		}
		if (parent.value && parent.value.attr.nowrap){
			var childrenSize = this.getOverallChildrenSize($parent);
			$parent.children().css({'min-width': childrenSize.width, 'min-height': childrenSize.height });
		}*/
		if (!css.width){
			delete css.width;
		}
		if (!css.height){
			delete css.height;
		}
		
		if (!(node.id == "gridPanel" && !$('#bottomPanel').is(':visible'))){
			$node.css(css);
		} else {
			$node.css({
				width:'100%',
				height:$('#mainPage').height() - $('#toolbar').height()
			});
		}
		/*this._setInnerSize($node,'width',css.width)
		this._setInnerSize($node,'height',css.height)
		
		switch (attr.fill){
			case 'none':
				$node.css('overflow','auto');
				break;
			case 'v':
			//	if ($node.height() > )
				break;
		}
		
		if (attr.widthGroup){
			$( this.clsSel('widthGroup',attr.widthGroup), this.element).width($node.width());
		}

		switch (attr.align){
		case 'right':
			$node.css('float','right');
			//$parent.css('position','relative');
			//$node.css('position','absolute');
			//$node.css('left',$parent.width()-$node.outerWidth());
			break;
		case 'center':
			$parent.css('position','relative');
			$node.css('position','absolute');
			var borders = this.getEmptySpaceBorders($node);
			borders.right - borders.left
			$node.css('left', borders.left + ((borders.right - borders.left - $node.outerWidth(true)) / 2));
			break;
		}*/
	},
	treeTraversal: function(node,root,functName){
		this[functName](node,root);
		for (var child in node.value){
			if (child!='attr'){
				this.treeTraversal({id:child, value: node.value[child]},{id: node.id, value: node.value},functName)
			}
		}
		/*if (functName=="_append"){
			this.getByName(nodeId).append('<div class="ui-layout-clear"></div>');
		}*/
	},
	create: function(){
		this._create();
	},
	/*destroy: function() {
		var self = this;
		
		self.element
			.unbind( "." + 'layout' )
			.removeData( 'layout' );
		self.widget()
			.unbind( "." + 'layout' )
			.removeAttr( "aria-disabled" )
			.removeClass(
				'ui-layout' + "-disabled " +
				'ui' + "-state-disabled" );

		self.markup.attr('id',self.options.layoutMarkupId);
		
		return self;
	},*/
	resizeAll: function(){
		this.treeTraversal($.wbs.getFirstChild(this.layout), this.layout, '_resize');
		this.options.onResize();
	},
	
	_create: function() {
		var self = this, o = this.options;
		this.element.addClass("ui-layout");
		if (!o.layoutMarkupId) o.layoutMarkupId = this.element.attr('id');
		this.markup = $('#'+o.layoutMarkupId, $('.ui-layout-markup'));
		this.markup.attr('id',this.markup.attr('id')+"-markup")
		this.markup.find('*').each(function(){
			this.id += "-markup";
		})
		this.layout = this.createPanelTree(this.markup, []);
		var root = $.wbs.getFirstChild(this.layout);
		this.layout.id = this.element.attr('id');
		this.treeTraversal(root, this.layout, '_append');
		this.treeTraversal(root, this.layout, '_applyAttr');
		if (typeof(o.callback)=='function') o.callback();
		this.resizeAll();
	}
});


})( jQuery );