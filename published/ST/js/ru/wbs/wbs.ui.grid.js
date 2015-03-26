/*
 * WBS UI Grid
 *
 * Copyright (c) 2010
 *
 * Depends:
 *	jquery.ui.core.js
 *	jquery.ui.widget.js
 */

var abstractView = {
	layoutMarkup:
		"<div id='wrapper'>" +		
			"<div id='tableWrapper'>" +
				"<div id='gridBody'>" +
					"<div id='headerWrapper'><div id='headers' class='wrapped'/></div>" +
					"<div id='blocksBody'><div id='blocks' class='multiply wrapped'/></div>" +
				"</div>" +
			"</div>" +
			//"<div id='spacer'/>" +
			//"<div id='footer'/>" +
		"</div>",
	create: function(o){
		var self = this;
		this.root = $("<div/>");
		this.layout = $("<div id='root'>" + this.layoutMarkup + "</div>");
		this.options = o;
		
		$('div', this.layout).each(function(){
			var parentId = $(this).parent().attr('id'),
			thisId = $(this).attr('id');
			if (!$(this).hasClass('multiply')){
				if (typeof(self[$.wbs.camelCase(['create',thisId])]) == 'function')
					self[thisId] = self[$.wbs.camelCase(['create',thisId])](o[thisId]);
			} else {
				self[thisId] = [];
				for(var dataId in o[thisId].data){
					if (typeof(self[$.wbs.camelCase(['create',thisId])]) == 'function')
					self[$.wbs.camelCase(['create',thisId])](o[thisId].data[dataId]);
				}
			}
			
			self.addParams($(self[thisId]), o[thisId]);
			var $thisObj = $.wbs.getObjByKey(thisId, self),
			$parentObj = $.wbs.getObjByKey(parentId, self);
			
			if (!$(this).hasClass('wrapped')){
				$parentObj.append($thisObj);
			} else {
				$.wbs.collectSiblings($thisObj).appendToDeepest($parentObj);
			}
		});
		return this.root.children();
	},
	createHeaders: function(headers){
		var headerContainer = [];
		for (headerId in headers.data){
			var header = headers.data[headerId];
			var $header = this.createHeader(header);
			this.addParams($header, headers);
			this.addParams($header, header);
			headerContainer[headerContainer.length] = $header;
		}
		return headerContainer;
	},
	createBlocks: function(blockContent){
		$blockWrapper = this.createBlockWrapper();
		this.addParams($blockWrapper, this.options.blockWrapper);
		if (typeof(blockContent) != "undefined"){
			for (blockElemId in blockContent.data){
				var $block = this.createBlock(blockContent.data[blockElemId]);
				this.addParams($block, this.options.blocks);
				this.addParams($block, blockContent);
				this.addParams($block, blockContent.data[blockElemId]);
				$block.appendTo($blockWrapper);
				//$block.trigger('blockload');
				gridData.tdOnLoad($block);
				$block.wrapInner("<div/>");
				this.blocks[this.blocks.length] = $blockWrapper;
			}
		}
		return $blockWrapper;
	},
	addParams: function($el, o){
		if (o && o.options){
			o = o.options;
			if (o.css) {
				if (typeof(o.css)=="string"){
					$el.css($.parseJSON(o.css));
				} else {
					$el.css(o.css);
				}
				}
			if (o.after) $el.append(o.after);
			if (o.before) $el.prepend(o.before);
			if (o.cssClass) $el.addClass(o.cssClass);
			if (o.attrs){
				for (attrKey in o.attrs){
					var attr = o.attrs[attrKey];
					$el.attr(attrKey, attr);
				}
			}
			if (o.linked){
				$el.addClass('linked-'+o.linked);
			}
			/*if (o.events){
				for (eventKey in o.events){
					var e = o.events[eventKey];
					$el.bind(eventKey, e);
				}
			}*/
		}
	},
	renderBlock: function(blockId, data){
		$(".ui-grid-column-id[rel='"+blockId+"']")
		.parent().replaceWith(this.createBlocks(data));

		if (blockId == $.wbs.get('current-request-id')) {
			var $el = $(".ui-grid-column-id[rel='"+blockId+"']").parent();
			$el.mousedown();

		}
		//$('.ui-grid-wrapper').scrollTo($el);
	},
	deleteBlock: function(blockId, moveToNext){
		if ($(".ui-grid-column-id").length>1 && moveToNext) {
			if ($(".ui-grid-column-id[rel='"+blockId+"']").parent().next().length) {
				$('td:first',$(".ui-grid-column-id[rel='"+blockId+"']").parent().next()).mousedown();
			} else {
				$('td:first',$(".ui-grid-column-id[rel='"+blockId+"']").parent().prev()).mousedown();
			}
		}
		$(".ui-grid-column-id[rel='"+blockId+"']").parent().remove();
		if (parseInt($('#requests-count').html())-1 >=0 )
			$('#requests-count').html(parseInt($('#requests-count').html())-1)
		if ($('#alertPanel').length){
			$.wbs.ajaxRequest({
				url: '?m=requests&act=count',
				callback: function(data){
					if (data.limit > data.count) location.reload();
				}
			})
		}
	},
	addBlock: function(data){
		var self = this, insertedId = parseInt(data.data.id.data, 10),
		prevId = 0,
		order = gridData.requestData.order;
		if (!$('.grid-tbody tr').length){
			$('.grid-tbody').append(
					self.createBlocks(data)
			);
		} else {
			if (
				(order!='asc' && 
				 insertedId > parseInt($('.grid-tbody .ui-grid-column-id:first').attr('rel'), 10))
				||
				(order=='asc' && 
				 insertedId < parseInt($('.grid-tbody .ui-grid-column-id:first').attr('rel'), 10))
			){
				if (!$(".ui-grid-column-id[rel='"+insertedId+"']").length){
					$('.grid-tbody').prepend(
						self.createBlocks(data)
					);
				}
			} else {
				if (
						((order!='asc' && 
						 insertedId < parseInt($('.grid-tbody .ui-grid-column-id:last').attr('rel'), 10))
						||
						(order=='asc' && 
						 insertedId > parseInt($('.grid-tbody .ui-grid-column-id:last').attr('rel'), 10)))
						 && ((gridData.requestData.limit > parseInt($('#requests-count').html()))
							 || 
							 ($('.grid-tbody .ui-grid-column-id').length == parseInt($('#requests-count').html()))
							)
					){
						$('.grid-tbody .ui-grid-column-id:last').parent().after(
							self.createBlocks(data)
						);
				} else {
					var stop = false;
					$('.grid-tbody tr').each(function(i){
						if (!stop){
							var $this = $(this),
							id = parseInt($('.ui-grid-column-id', $this).attr('rel'), 10);
							if (
								(order!='asc' && prevId > insertedId && id < insertedId)
								||
								(order=='asc' && prevId < insertedId && id > insertedId)
							) {
								$this.before(self.createBlocks(data));
								stop = true;
							}
							prevId = id;
						}
					})
				}
			}
		}
	},
	appendBlocks: function(blocks){
		this.blocks = [];
		for(var dataId in blocks){
			this.createBlocks(blocks[dataId]);
		}
		$('.grid-tbody:first').append($.wbs.collectSiblings(this.blocks));
	},
	renderBlocks: function(blocks){
		$('tbody').find('*').remove();
		this.blocks = [];
		for(var dataId in blocks){
			this.createBlocks(blocks[dataId]);
		}
		$('.grid-tbody:first').append($.wbs.collectSiblings(this.blocks));
	},
	createTableWrapper: function(el){
		el = $("<div class='ui-grid-wrapper' style='overflow-y: scroll; zoom:100%;'></div>");
		el.scroll(function(){gridData.scrollEvent(this)})
		return el;
	},
	createGridBody: function(el){
		return $("<table cellspacing='0' border='0' class='ui-grid-content ui-widget-content'></table>");
	},
	createHeaderWrapper: function(el){
		return $("<thead><tr></tr></thead>");
	},
	createHeader: function(el){
		return $("<th nowrap='nowrap' onmousedown='gridData.thMouseDown(this)' class='ui-state-default'>"+el.data+(el.data!="&nbsp;"?"<span></span>":"")+"</th>")
	},
	createBlocksBody: function(el){
		return $("<tbody class='grid-tbody'></tbody>")
	},
	createBlockWrapper: function(el){
		return $("<tr onmousedown='gridData.trMouseDown(this)'></tr>")
	},
	createBlock: function(el){
		if (el.data) el.data = el.data.replace(/\&amp\;/g,'&');
		return $("<td onmousedown='gridData.tdMouseDown(this)'>"+el.data+"</td>")
	},
	/*createSpacer: function(el){
		return $("<div></div>")
	},*/
	createWrapper: function(el){
		el = $("<div class='ui-widget'></div>");
		return el;
	},
	createFooter: function(el){
		 $footer = $("<div class='ui-grid-footer ui-widget-header' style='position:relative;'>"+
			"<span id='requests-count-label'>Запросы:</span><span id='requests-count'></span>"+
			"<span id='assigned-filter-label'>Показать:</span>"+
			"<div id='assigned-filter'>"+
			"<a id='assigned-open' href='#'>открытые</a> | <a id='assigned-all' href='#'>все</a>"+
			"</div>"+
			/*"<div id='pages-label'><span>Страницы:</span> <span id='pager'></span></div>"+
			"<div id='grid-size-label' style='font-size:11px;margin-top:1px'><span>Показать</span>" +
			"<select id='grid-size'>" +
			"<option value='100'>100</option>" +
			"<option value='50'>50</option>" +
			"<option value='20'>20</option>" +
			"<option value='10'>10</option>" +
			"</select></div>"+
			"<span>запросов на странице</span>"+*/
			"</div>");
		    if (location.hash.search('assigned')>-1){
		    	if (location.hash.search('all')>-1){
					$('#assigned-all',$footer).addClass('active');
				} else {
		    		$('#assigned-open',$footer).addClass('active');
				}
		    } else {
		    	$('#assigned-filter',$footer).remove();
		    	$('#assigned-filter-label',$footer).remove();
		    }
		    $('#assigned-open',$footer).click(function(){
		    	$.wbs.removeFromHash('all');
		    })
		    $('#assigned-all',$footer).click(function(){
		    	$.wbs.addToHash('all');
		    })
		return $footer;
	}
}


gridData = {
	source: 'ajax',
	url: '?m=requests&act=list',
	limit: 20,
	pageNumber: 1,
	requestData: new Object,
	grid: false,
	appendRows: function(offset, callback){
		var self = this;
		$.wbs.set('appending-rows', true);
		var $loading = ("<tr class='loading-row'><td colspan='10'><div style='width:100%; min-height:20px; text-align: center;'><img src='img/ajax-loader-w.gif'/>Загрузка&hellip;</div></td></tr>")
		$('tbody', self.grid).append($loading);
		var filter = $.wbs.get('grid-filter').split('=')
		if (typeof(filter[1]) != "undefined"){
			self.requestData[filter[0]] = filter[1];
		}
		$.wbs.ajaxRequest({
	    	url: self.url,
	    	data: $.extend(self.requestData, {offset: offset}),
	    	callback: function(data){
				$('.loading-row').remove();
				abstractView.appendBlocks(data.requests);
				if (typeof(callback)=='function') callback();
				if (data.requests.length > 0) {
					$.wbs.set('appending-rows', false);
				} else {
					$('tbody', self.grid).append("<tr class='ui-grid-no-rows'><td colspan='10'><div>-- конец списка --</div></td></tr>")
				}
				self.cloneHeader();
			}
		})
	},
	updatePage: function(callback){
		var self = this;
		$.wbs.set('appending-rows', false);
		self.requestData.offset = 0;

		var rowsNumber = Math.ceil(($(window).height() - 150) / 20);
		if (rowsNumber < 12) rowsNumber = 12;
		self.requestData.limit = rowsNumber;
		
		$.wbs.ajaxRequest({
	    	url: self.url,
	    	data: self.requestData,
	    	callback: function(data){
				abstractView.renderBlocks(data.requests);	
				if (typeof(callback)=='function') callback();
				self.cloneHeader();
				self.setFooterData(data);
			}
		})
	},
	checkForIrrelevantRow: function(fromDom, id, state_id, assigned_c_id){
		
		var curUname = '',
		users = $.wbs.get('users'), 
		currentuserid = $.wbs.get('current-user-id');

		for (key in $.wbs.get('users')){
			if (users[key].id == currentuserid) {
				curUname = users[key].name
			}
		}
		var check = function(id, state_id, assigned_c_id){
			var states = $.wbs.get('data').states;
			var h = parent ? parent.window.location.hash : location.hash;
			if ((state_id == "0" && h.indexOf('verification') > 0) || 
				(state_id == "-1" && h.indexOf('trash') > 0)
			) {
				return true;
			}
			if ((id != $.wbs.get('current-request-id')) &&
				(state_id == "-1"  
				 ||
				 (states[state_id].group != "-101" && h.indexOf('archive') == -1)
				 ||
				 (states[state_id].group != "-102" && h.indexOf('archive') > 0)
				 ||
				 (state_id != $.wbs.controller.currentActionAttr[0]
					&& h.indexOf('state') > 0
				 )
				 ||
				 (assigned_c_id != curUname && h.indexOf('/assigned') > 0)
				 ||
				 (assigned_c_id != '' && h.indexOf('/notassigned') > 0)
				)) {
					abstractView.deleteBlock(id, false);
		        	gridData.insertNewBlocks(id,$.wbs.get('last_req_id'));
				}
		}
		if (fromDom) {
			$('.grid-tbody tr').each(function(i){
				var $this = $(this), state_id, states = $.wbs.get('data').states,
				state = $('.ui-grid-column-state_id div', $this).text();
				for (var key in states){
					if (states[key].name == state) {
						state_id = states[key].id
					}
				}
				//console.log($.wbs.get('data').states[state_id].group);
				//console.log($('.ui-grid-column-id', $this).attr('rel'), state_id, $('.ui-grid-column-assigned_c_id div', $this).text())
				if ($('.ui-grid-column-id', $this).length){
					check($('.ui-grid-column-id', $this).attr('rel'), state_id, $('.ui-grid-column-assigned_c_id div', $this).text())
				}
			})
		} else {
			check(id, state_id, assigned_c_id)
		}
	},
	updateBlock: function(rowId, callback){
		var self = this,
		hasRow = $('.ui-grid-column-id[rel='+rowId+']', self.grid).length;
		if (hasRow) {
			$.wbs.ajaxRequest({
		    	url: '?m=requests&act=list&singlerow='+rowId,
		    	data: self.requestData,
		    	callback: function(data){
					abstractView.renderBlock(rowId, data.requests[0]);	
					if (typeof(callback)=='function') callback();
					self.cloneHeader();
				}
			})
		}
	},

	updateBlockByData: function(data, callback){
		var self = this,
		hasRow = $('.ui-grid-column-id[rel='+data.data.id.data+']', self.grid).length;
		if (hasRow) {
			abstractView.renderBlock(data.data.id.data, data);
			self.cloneHeader();
			if (typeof(callback)=='function') callback();
		}
	},
	deleteBlock: function(rowId, callback){
		var self = this;
		abstractView.deleteBlock(rowId, true);
		if (typeof(callback)=='function') callback();
		/*
		$.wbs.ajaxRequest({
	    	url: '?m=requests&act=list',
	    	data: self.requestData,
	    	callback: function(data){
				if (data.requests[0]){
					if (typeof(callback)=='function') callback();
				}
			}
		})
		*/
	},

	insertNewBlocks: function(refreshedIds, lastid){
		var self = this;
		if (refreshedIds) {
			delete self.requestData.timestamp;
			var data = $.extend(self.requestData, {refreshed: refreshedIds});
		} else {
			delete self.requestData.refreshed;
			var data = $.extend(self.requestData, {lastid: lastid});
		}
		delete data.offset; delete data.limit;
		$.wbs.ajaxRequest({
	    	url: self.url,
	    	data: data,
	    	callback: function(data){
				for (var key in data.requests){
					abstractView.addBlock(data.requests[key]);
				}
				self.setFooterData(data);
				//var $el = $(".ui-grid-column-id[rel='"+$.wbs.get('current-request-id')+"']:first").parent();
				//$el.mousedown();
				//if ($el.length) $('.ui-grid-wrapper').scrollTo($el);
				//self.setFooterData(data);
				if (typeof(callback)=='function') callback();
				if (self.requestData.timestamp) delete self.requestData.timestamp;
				if (self.requestData.refreshed) delete self.requestData.refreshed;
			}
		})
	},
	setFooterData: function(data){
		var self = this;
		gridData.count = Math.ceil(data.count / gridData.limit);
		$('#requests-count').html(data.count);
		if (data.count != '0'){
            if ($('.first-message:visible').length) $.wbs.widgets.toggleBottomPanel(false, true);
			$('.first-message').hide();
			$('#first-message').remove();
			$('#table').show();
		}
		if ($.wbs.get('use_limit') == 1){
			$.wbs.ajaxRequest({
				url: '?m=requests&act=count',
				callback: function(data){
					if (data.limit > 0){
						if (data.limit >= data.count) {
							if ($('#alertPanel').length){
								location.reload();
							}
						} else {
							if (!$('#alertPanel').length){
								location.reload();
							}
						}
					}
				}
			})
		}
		
		/*$("#pager").pager({ 
			pagenumber: this.pageNumber, 
			pagecount: gridData.count, 
			buttonClickCallback: function(pagenumber){gridData.updatePage(pagenumber, function(){
				self.grid.wbsGrid('selectBlock', $.wbs.get('grid-rownumber'), true);
			})} 
		});*/
	},
	cloneHeader: function(){
		if (!$.browser.msie){
			var hasClone = false;
			if ($('.ui-grid-header-clone').length){
				$('.ui-grid-header-clone').parent().remove();
				hasClone = true;
			}
			var $headerClone = $('.ui-grid-wrapper table', self.grid).clone(true)
			.removeAttr('id').addClass('ui-grid-header-clone');
			if (hasClone){
				$headerClone.css({
					'position':'relative',
					'top':'0'
				});
			}
			//$('tbody',$headerClone).remove();
			$('tbody',$headerClone).removeClass('grid-tbody');
			
			/*var $tbody = $('.ui-grid-wrapper tbody', self.grid).clone();
			$('td', $tbody).removeAttr('class');
			$tbody.removeAttr('class');
			$('td', $tbody).removeClass('ui-grid-column-id');
			$('tr', $tbody).removeAttr('class');
			$('thead',$headerClone).after($tbody);
			$('tbody',$headerClone).css({'visibility': 'hidden'});*/
			$('.ui-grid-wrapper', this.grid).before($headerClone);
			$headerClone.wrap("<div style='padding-right:16px; border:none; overflow:hidden; height:" + 
					$(".ui-table thead tr:first").outerHeight(true) + "px' class='ui-state-default'></div>")
			if (!hasClone){
				$('.ui-grid-wrapper table', this.grid).css({
					'position':'relative',
					'overflow':'hidden',
					'top':'-30px'
				});
			}
		}
	},
	loadPage: function(callback){
		var self = this;
		if (typeof($.wbs.get('grid-filter')) != "undefined") {
			var filter = $.wbs.get('grid-filter').split('=')
			self.requestData[filter[0]] = filter[1];
		} else {
			delete self.requestData.filter;
		}
		delete abstractView.layout;
		$.wbs.set('appending-rows', false);
		$('.requests-count-div').css('display','none');

		//if ($.wbs.controller.currentAction == "requestsInfo"){
		//$.cookie($.cookie('currentAction')+'-id', $.wbs.get('current-request-id'));
		//}
		self.grid.html('<div style="height:'+$('#mainPage').height()+'px; margin-left:20px" class="grid-loading">Загрузка&hellip;</div>');
		
		val = $.cookie($.cookie('currentAction')+'-sort');
		if (val != null){
			self.requestData.sort = val;
			self.requestData.order = $.cookie($.cookie('currentAction')+'-order');
		} else {
			self.requestData.sort = 'datetime';
			self.requestData.order = 'desc';
		}
		var rowsNumber = Math.ceil(($(window).height() - 150) / 20);
		if (rowsNumber < 12) rowsNumber = 12;
		self.requestData.limit = rowsNumber;

		//$('#textarea').wbsEditor('setContent', '');

		$.wbs.ajaxRequest({
	    	url: self.url,
	    	data: self.requestData, //$.extend(self.requestData, {limit: rowsNumber}),
	    	callback: function(data){
				self.grid.find('*').remove();
				self.grid.append(abstractView.create(self.data));
				abstractView.renderBlocks(data.requests);	
				self.setFooterData(data);
				self.grid.find('*').each(function(){
					$(this).trigger('load')
				});

				gridH = $.cookie('grid-height');
				if (!gridH || gridH > ($('#mainPage').height() - 100)){
					gridH = Math.ceil($('#mainPage').height() / 3)
				}
				gridH = parseInt(gridH,10);
				$.cookie('grid-height', gridH);
				$(".ui-grid-wrapper").height(gridH);
				
				$('#container').layout('resizeAll');
				
				$this = $('.ui-grid-th-'+self.requestData.sort);
				$this.addClass('ui-state-active');
				$this.addClass('ui-grid-order-'+self.requestData.order);
				
				if (typeof(callback)=='function') callback();
				
				self.cloneHeader();
				$('.requests-count-div').css('display','inline');
				$.wbs.widgets.toggleLoading('body','hide');
				var $firstMsg = $('#first-message');
				if ($('#requests-count').text() == '0' && $firstMsg.length){
					if ($firstMsg.length) {
						$('#table').hide();
						var $clone = $firstMsg.clone().removeAttr('id').show().appendTo($('.ui-grid-wrapper'));
						//$clone.height($(window).height() - Math.round($('.ui-grid-wrapper').position().top));
						var height = $(window).height() - Math.round($('.ui-grid-wrapper').position().top);
						if ($.browser.msie){
							$clone.css('height',height);
						} else {
							$clone.css('height','100%');
						}
						var valign = $clone.find('.v-align');
						valign.css('padding-top','15%');
						if ($('#bottomPanel').is(':visible')) $.wbs.widgets.toggleBottomPanel(false, true);
					} else {
						$('#table').show();
					}
					if ($.wbs.in_array($.wbs.controller.currentAction, ['requestsTrash', 'requestsVerification'])){
			    		$('.empty-bin-link').remove();
					}
				} else {
					$firstMsg.hide();
					$('#table').show();
				}

				// gridData.count = Math.ceil(data.count / gridData.limit);
				// $("#pager").pager({ pagenumber: pageNumber, pagecount: gridData.count, buttonClickCallback: gridData.loadPage });
				// $.wbs.get('grid-pagenumber',pageNumber)
				// abstractView.renderBlocks(data.requests)	
				// if (typeof(callback)=='function') callback();
			}
		})
	},
	thMouseOver: function(event){
		$(this).addClass('ui-state-highlight ui-grid-th-over');
	},
	thMouseOut: function(event){
		$(this).removeClass('ui-state-highlight ui-grid-th-over');
	}, 
	thMouseDown:function(el){
		if(
			$(el).hasClass('ui-grid-th-id') || $(el).parent().hasClass('ui-grid-th-id')
			||
			$(el).hasClass('ui-grid-th-datetime') || $(el).parent().hasClass('ui-grid-th-datetime')
		  ){
			var $this = $(el),
			className = $this.attr('class'),
			reColumnId = /ui-grid-th-(\w+)/,
			columnId = reColumnId.exec(className);
			$this = $('.'+columnId[0]);

			$this.parent().children().removeClass('ui-state-active');
			$this.parent().children().removeClass('ui-grid-order-asc');
			$this.parent().children().removeClass('ui-grid-order-desc');
		    //$(this).parent().find('span.ui-icon').remove();
			$this.addClass('ui-state-active');
			
			var reOrder = /ui-grid-order-(\w+)/,
			order = reOrder.exec(className);
			sort = columnId[1];
			if (!sort) sort = $.cookie($.cookie('currentAction')+'-sort');
			if (!order) {
				order = $.cookie($.cookie('currentAction')+'-order');
				if (!order) order = 'asc';
			} else {
				$this.removeClass('ui-grid-order-' + order[1]);
				if (order[1] == 'desc') {
					order = 'asc';
				} else {
					order = 'desc';
				}
			}
			$this.addClass('ui-grid-order-loading'); 

			$.cookie($.cookie('currentAction')+'-order', order);
			$.cookie($.cookie('currentAction')+'-sort', sort);
			
			$.extend(gridData.requestData, {sort: columnId[1], order: order});
			//pageNumber = gridData.pageNumber;
			gridData.updatePage(function(){
				$this.removeClass('ui-grid-order-loading');
				$this.addClass('ui-grid-order-' + order); 
				var val = $('#table tbody tr:first').find('.ui-grid-column-id').attr('rel');

				$('#grid').wbsGrid('selectBlock', val, true);
				/*if (val){
					var hash = '/requests/info/'+val;
		    		$.wbs.loadHash(hash);
				}*/
			});
		}
	},
	scrollEvent: function(el){
		if (!$.wbs.get('appending-rows')){
			var $el = $(el),
				hEl = $('table', $el).height() - $el.height(),
				offset = $('table tr', $el).length;
			
			if (hEl - el.scrollTop - 30 < hEl * 0.05) {
				gridData.appendRows(offset);
			}
		}
	},
	disableRowClick : false,
	trMouseDown: function(el){
		if (!this.disableRowClick) {
			if(!$(el).hasClass('ui-grid-column-new_window')){
			var $this = $(el), $td = $('td',el);
			$('td.ui-state-highlight',$(el).parent()).removeClass('ui-state-highlight');
			$td.removeClass('ui-state-highlight');
			$td.removeClass('ui-grid-row-over');
			if ($td.hasClass('ui-grid-row-unread')){
				$td.removeClass('ui-grid-row-unread');
				var unread =  parseInt($.wbs.get('unread_count'), 10);
				if (unread>1){
					$.wbs.set('unread_count', unread - 1);
					$('#myRequestsTitle').css('font-weight', 'bold');
					if (!$('#myRequestsTitle span').length) {
						$('#myRequestsTitle').append('<span/>')
					}
					$('#myRequestsTitle span').html('('+$.wbs.get('unread_count')+')');
				} else {
					$.wbs.set('unread_count', 0);
					$('#myRequestsTitle span').remove();
					$('#myRequestsTitle').css('font-weight', 'normal');
				}
				if ($('#title h1').text() == $('#myRequestsTitle').text()){
					$('#title h1').html($('#myRequestsTitle').text());
				}
			}
			$td.addClass('ui-state-highlight');	
			}
		}
	},
	tdMouseDown: function(el){
		if(!$(el).hasClass('ui-grid-column-new_window')){
			this.disableRowClick = false;
			if(el.parentNode.tagName == 'TR'){
				var val = $('.ui-grid-column-id',$(el.parentNode)).attr('rel');
			} else {
				var val = $('.ui-grid-column-id',$(el.parentNode).parent()).attr('rel');
			}
			
			$.cookie($.cookie('currentAction')+'-id', val);
			$.wbs.set('current-request-id', val);
			var hash = $.wbs.controller.currentHash + 'id/' + val;
			var h = parent ? parent.window.location.hash : location.hash;
			if (h == '#'+hash || h == hash) {
				$.wbs.controller.request(hash);
			} else {
				$.wbs.loadHash(hash);
			}
		} else {
			this.disableRowClick = true;
			window.open($(el).find('a').attr('rel'))
		}
		return false;
	},
	tdOnLoad: function(el){
		var $this = $(el);
	
		if ($this.hasClass('ui-grid-column-new_window')){
			$this.attr('title','Открыть в новом окне');
		}
		if ($this.hasClass('ui-grid-column-state_id')){
			var id = $this.html(),
				states = $.wbs.get('data')['states'];
			if (states[id] && states[id]['name']) {
				$this.html(states[id]['name']);
				if (states[id]['properties'] && states[id]['properties']['css']) {
					$this.parent().children().attr('style',
						states[id]['properties']['css']);
				}
			}
			if ($('#verificationTitle .ui-state-highlight, #trashTitle .ui-state-highlight').length > 0){
				$('td', $this.parent()).attr('style',false);
			}
		}
	},
	data: {
		gridBody: {
			data: '',
			options: {
				cssClass: 'ui-table',
				css: {
					'border-collapse': 'collapse',
					width: '100%'
				},
				attrs:{
					id: 'table'
				}
			}
		},
		spacer:{
			options: {
				cssClass: 'ui-grid-spacer'
			}
		},
		tableWrapper:{
			options: {
			}
		},
		headerWrapper:{data: ''},
		headers:{
			options: {
				cssClass: 'ui-grid-th'
			}, 
			data:[
			    {
			    	options: {
						cssClass: 'ui-grid-th-id',
						css: {'cursor': 'pointer'}
					},
					data: 'Номер'
				},{
			    	options: {
						cssClass: 'ui-grid-th-datetime',
						css: {'cursor': 'pointer'}
					},
					data: 'Дата'
				},{
			    	options: {
						cssClass: 'ui-grid-th-client_from'
					},
					data: 'От кого'
				},{
			    	options: {
						cssClass: 'ui-grid-th-subject'
					},
					data: 'Тема'
				},{
			    	options: {
						cssClass:'ui-grid-th-state_id'
					},
					data: 'Статус'
				},{
			    	options: {
						cssClass: 'ui-grid-th-assigned_c_id'
					},
					data: 'Назначен'
				},{
			    	options: {
						cssClass: 'ui-grid-th-new_window',
						attrs:{
							'title': 'Открыть в новом окне'
						}
					},
					data: '&nbsp;'
				}
			]
		},
		blockWrapper:{
			options: {
			}
		},
		blocks:{
			options: {
				cssClass: 'ui-grid-td'
			}, 
			data:[]
		},
		footer:{
			data: 'footer',
			options: {
				events:{
					'load': function(){
		    			$("#pager",this).pager({ 
		    				pagenumber: gridData.pageNumber,
		    				pagecount: gridData.count, 
		    				buttonClickCallback: function(pagenumber){gridData.updatePage(pagenumber)}
		    			});
					}
				}
			}
		}
	}
}

$.widget("ui.wbsGrid", {
	options: {
		fillHeight: true,
		width: "100%",
		model: gridData
	},
	loadPage: function(pageNumber){
		this.model.grid = this.element;
		this.model.loadPage(pageNumber);
	},
	_create: function(){
		var self = this, o = this.options;
		this.setGridData(o.model);
	},
	_destroy: function(){
		this.element
			.removeData("grid")
			.unbind(".grid");
	},
	render: function(view){
		$grid = abstractView.create(this.data);
		this.element.append($grid);
		this.element.find('*').each(function(){
			$(this).trigger('load')
		});
	},
	resize: function(){
		//this.element.height(this.element.parent().height())
		$('.ui-grid-wrapper:first',this.element).height(this.element.height()  - ($.browser.msie ? 0 : $(".ui-table thead tr:first").outerHeight(true)) /*- $(".ui-grid-footer:first").outerHeight(true)*/)
	},
	setSort: function(el){
		
	},
	getCurrentPage: function(val){
		return $('li.pgCurrent').text();
	},
	selectBlock: function(val, scrollToBlock){
		var self = this, $this = [];
		$rows = this.element.find('tr').find('.ui-grid-column-id');
		$rows.each(function(){
			if ($(this).attr('rel') == val) {
				$this = $(this).parent();
			}
		});
		
		if (val>0){
			$('#textarea').wbsEditor('loadContent',{
				ajax: true,
				url: '?m=requests&act=info&id=' + val
			})
			$.wbs.set('current-request-id',val);
		} else {
			$this = self.element.find('tbody tr:first');
			val = $('.ui-grid-column-id',$this).attr('rel');
			if ($this.length){
				$('#textarea').wbsEditor('loadContent',{
					ajax: true,
					url: '?m=requests&act=info&id=' + val
				})
				$.wbs.set('current-request-id',val);
			}
		}
		if ($this.length){
			$this.mousedown();
			var hash = location.hash, re = /\/plugin\/(\w+)/, test = re.exec(hash);

			$.wbs.set('current-request-id',$('.ui-grid-column-id',$this).attr('rel'));
			if (scrollToBlock) {
				var scrollTo = $this.prev().prev()
				if (scrollTo.length) {
					$this.closest('.ui-grid-wrapper').scrollTo(scrollTo);
				} else {
					$this.closest('.ui-grid-wrapper').scrollTo(0);
				}
			}
		}
		return false;
	},
	setGridData: function(callback){
		var self = this;
		gridData.grid = this.element; 
		gridData.loadPage(function(){
			self.selectBlock($.wbs.get('current-request-id'), true)
			if (typeof(callback)=='function') callback();
		});
	}
});
