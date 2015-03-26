/*
 * WBS Controller
 *
 * Copyright (c) 2010
 */

(function($) {
	$.wbs.controller = {
		request: function(hash){
			var self = this;
			if (hash) {
				hash = hash.replace(/^.*#/, '');
	    		$('.ui-tree .ui-state-highlight').removeClass('ui-state-highlight');
	    		$('.empty-bin-link').remove();
	    		$('.refresh-link').show();
				hash = hash.split('/');
		        if (hash[0]) {
		        	this.currentHash = '#';
		        	for (var key in hash){
		        		if (!(hash[key] == "id" || hash[key-1] == "id")){
		        			this.currentHash += hash[key] + '/';
		        		}
		        	}

		        	var root = '#/' + hash[0] + '/' 
		        	if (hash.length>1) root += hash[1] +'/';
		        	if ($.wbs.in_array('assigned', hash) || $.wbs.in_array('state', hash))  root += hash[2] +'/';
		        	if (root.indexOf('search')==-1) $.cookie('last-action-hash', root);
		    		//$currentLeftLink = $('<div/>');
		        	$('.ui-tree a').each(
		    			function(){
		    				if ($(this).attr('href').indexOf(root) > -1) {
		    					$(this).addClass('ui-state-highlight');
		    					var title = $(this).text();
		    		    		$('#title h1').html(title)
		    		    	    document.title = title + ' — Поддержка';
		    				}
		    			}
		    		)
		    		//$currentLeftLink = $('.ui-tree a[href^="'+ root +'"]:first')
		    		
		        	var actionName = "";
		        	var attrMarker = hash.length;
		        	for(hashId in hash) {
		        		var h = hash[hashId];
		        		if (/*parseInt(h) != Number(h)*/ hashId < 2){
		        			if (hashId==0){
		        				actionName = h;
		        			} else {
		        				actionName += h.substr(0,1).toUpperCase() + h.substr(1);
		        			}
		        		} else {
		        			attrMarker = hashId;
		        			break;
		        		}
		        	}

					for (var key in gridData.data.headers.data){
						var el = gridData.data.headers.data[key];
						if (el.options.cssClass.search('ui-grid-th-assigned_c_id')!=-1 ||
							el.options.cssClass.search('ui-grid-th-state_id')!=-1 ||
							el.options.cssClass.search('ui-grid-th-new_window')!=-1) {
				        	if ($.wbs.in_array(actionName,
									['requestsVerification',
									 'requestsTrash'])) {
				        		if (el.options.cssClass.indexOf("ui-hidden")<0)
				        			el.options.cssClass = el.options.cssClass + " ui-hidden";
				        	} else {
				        		el.options.cssClass = el.options.cssClass.replace("ui-hidden","");
				        	}
						}
					}

			        var attr = hash.slice(attrMarker);
		        	if (self[actionName+'Action']){
		    			var id = parseInt($.wbs.in_array('id',attr),10);
	    				id = attr[id+1];
	    				$.wbs.set('current-request-id', id);
	    				
		    			if (actionName != this.currentAction){
			    			//delete gridData.requestData.filter;
			    			delete gridData.requestData.search;
		    			}
		    			this.currentAction = actionName;
		    			this.currentActionAttr = attr;
		    			//$.cookie('currentAction', actionName);
		        		self[ actionName+'Action'](attr);
		        	} else {
		        		if (console) console.log('Invalid action name:', actionName+'Action')
		        	}
		        } else {
		        	self.defaultAction();
		        }
	        } else {
	        	self.defaultAction();
	        }
		},
		onLoad: function(){
			var self = this;
        	//$.wbs.scrollBarWidth = $.wbs.getScrollBarWidth();
			if ($("#container").length){
				$.wbs.widgets.toggleLoading('body');
				$("#container").width($(window).width());
	            $("#container").height($(window).height());
				//currentWindowWidth = $(window).width();
				//$.wbs.scrollBarWidth = $.wbs.getScrollBarWidth();
				var resizeTimer = null,
				resizeWindow = function(){
		            $("#container").width($(window).width());
		            $("#container").height($(window).height());
					$("#container").layout('resizeAll')
				},
				isMozilla = $.browser.mozilla;
				$(window).resize(function(){
					if (isMozilla) {
						if (resizeTimer) clearTimeout(resizeTimer);
						resizeTimer = setTimeout(resizeWindow, 50);
					} else {
						resizeWindow()
					}
				});
			}
			var toggleFocus = false;
			$("input[type=text]").live('click', function(){
				if (!toggleFocus && !$(this).hasClass('disable-selection-on-focus')) {
					this.select();
				}
				toggleFocus = true;
			});
			$("input[type=text]").live('blur', function(){
				toggleFocus = false;
			});

			$("input[type=button], button").live('click', function(e){
				return false;
			});

			$('.tabs-nav li a').live('click',function(){
				$(this).parent().parent().children().removeClass('tabs-selected');
				$(this).parent().addClass('tabs-selected');
			})
			
			$('body').ajaxComplete(function(event,request, settings){
				if (request.responseText.search("SESSION_TIMEOUT")>0){
					var redirect = request.responseText, re = /redirectUrl: '(\S+)'/;
					redirect = re.exec(redirect);
					if (redirect[1]) {
						if (window && window.parent) {
							var d = window.parent.document;
						} else {
							var d = document;
						}
						d.location.href = redirect[1];
					}
				}
			});

			var timeout = 60 * 1000;
			setInterval(function(){
				if ($('#grid').is(':visible') || location.hash.indexOf('full')>0){
					$.wbs.widgets.showChangesInRequests($.wbs.get('timestamp'))
				}
			}, timeout);
			var showAlertMsg = function(msg){
				$.wbs.widgets.showAlertMsg($('#topPanel'), msg)
			}
			var checkRequests = function () {
				$.get("?m=requests&act=check", function(response){
					response = $.parseJSON(response);
					if (response.status == 'ERR') {
						if (typeof(response.error) == "object") {
							for (key in response.error){
								showAlertMsg(response.error[key])
							}
						} else {
							if (typeof(response.error) == "string") showAlertMsg(response.error)
						}
					}
				});
			};
			checkRequests();
			var currentTime = new Date().getTime();
			setInterval(checkRequests, timeout);

			$(document).keypress(function(e){
				if ($.focused() !== true){
					switch(e.which)
					{
						case 56:
							if ($('#bottomPanel').is(':visible')){
								var $el = $("#table .ui-grid-column-id[rel='"+
										parseInt($("#table .ui-grid-column-id.ui-state-highlight").text())+
									"']").parent().prev();
								$('#grid').wbsGrid('selectBlock', parseInt($('td:first',$el).text(),10), true)
							} else {
								$(".ui-grid-wrapper").scrollTo( '-=20px');
							}
						break;
						case 50:
							if ($('#bottomPanel').is(':visible')){
								var val = $('td:first',$("#table .ui-grid-column-id[rel='"+
										parseInt($("#table .ui-grid-column-id.ui-state-highlight").text())
									+"']").parent().next()).text()
								$('#grid').wbsGrid('selectBlock', parseInt(val,10), true)
							} else {
								$(".ui-grid-wrapper").scrollTo( '+=20px');
							}
						break;
					}
				}
			});
			$.wbs.set('current-hash',"#/requests/all/");
		},
		init: function(options){
			var self = this;

			if ($("#container").length){
				var settings = {
					hiddenPanels: [],
					hiddenElements: []
				}
				$.extend(true, settings, options);
				var mainLayoutOptions = {
					layoutMarkupId: 'mainLayout',
					onResize : function(){
		                $('#grid').wbsGrid('resize');
		                $('#textarea').wbsEditor('resize');
	                    $('#contentPanel').layout('resizeAll');
					}
				};
				$.extend(true, mainLayoutOptions, settings);

	            $("#container").layout(mainLayoutOptions);
			}
			if (typeof($.History) != "undefined"){
				$.History.bind(function (hash) {
		    	//	if (typeof(console) != "undefined") console.log("Load hash:", hash);
					self.request(hash)
			    });
				var h = parent ? parent.window.location.hash : location.hash;
				if (h.length < 2) {
		        	self.defaultAction();
				} else {
					$.wbs.loadHash(h);
				}
			}
    		$("a[href='#']").live('click',function(e){
    			return false;
    		});
    		$("a[href^='#']").live('click',function(){
    		//	if (typeof(console) != "undefined") console.log("Current href:", this.href);
	    		$.wbs.loadHash(this.href);
	    		return false;
    		});

    		//$.wbs.set('current-request-id', 0);
		},
		
		/*requestsAction: function(attr){
    		$('.ui-tree a[href="'+$.wbs.get('current-hash')+'"]').addClass('ui-state-highlight');
			$.wbs.set('current-request-id',attr[0]);
			//$.wbs.widgets.showChangesInRequests();
			$.wbs.pages.mainPage(attr);
	        
		},*/
		
		requestsSearchAction: function(attr){
			if ($.cookie('single-panel-view') == 'true'){
				$.cookie('single-panel-view','false')
	        }
			delete gridData.requestData.filter;
			attr[0] = $.wbs.trim(attr[0])
			if (decodeURIComponent) attr[0] = decodeURIComponent(attr[0]);
			gridData.requestData.search = attr[0];
			$.wbs.pages.mainPage('search='+attr[0]);
			$('#searchRequestInput').val(attr[0]);
			$('#title h1').html('Результаты поиска для "'+attr[0]+'"');
    	    document.title = 'Результаты поиска — Поддержка';
		},
		
		requestsAdvsearchAction: function(attr){
			if ($.cookie('single-panel-view') == 'true'){
				$.cookie('single-panel-view','false')
	        }
			var search = '', search_str = '', has_subject = false; 
        	for (var key in attr){
        		attr[key] = $.wbs.trim(attr[key], false);
        		if (key>0 && attr[key-1] == "name"){
        			search += '|name|'+attr[key];
        				search_str += 'и имя клиента <b>'+attr[key]+'</b> ';
        			$("#adv-search-name").val(attr[key]);
        		}
        		if (key>0 && attr[key-1] == "email"){
        			search += '|email|'+attr[key];
        			search_str += 'и email клиента <b>'+attr[key]+'</b> ';
        			$("#adv-search-email").val(attr[key]);
        		}
        		if (key>0 && attr[key-1] == "clientid"){
        			search += '|clientid|'+attr[key];
        			search_str += 'и номер клиента <b>'+attr[key]+'</b> ';
        			$("#adv-search-id").val(attr[key]);
        		}
        		if (key>0 && attr[key-1] == "subject"){
        			search += '|subject|'+attr[key];
        			has_subject = true;
            		search_str += 'и слова <b>'+attr[key]+'</b> ';
            		if (!$.wbs.in_array('words',attr)){
            			search_str += 'в тема';
            		}
        			$("#adv-search-in-subject").attr('checked','checked');
        			$('#adv-search-in-request').removeAttr('checked');
					$('.adv-search-words-mode').removeAttr('disabled');
        			$("#adv-search-words").val(attr[key]);
        		}
        		if (key>0 && attr[key-1] == "words"){
        			search += '|words|'+attr[key];
        			if (!has_subject)
        				search_str += 'и слова <b>'+attr[key]+'</b> ';
        			$("#adv-search-words").val(attr[key]);
					$('.adv-search-words-mode').removeAttr('disabled');
        		}
        		if (key>0 && attr[key-1] == "wmode"){
        			search += '|wmode|'+attr[key];
        			var subject = '';
        			if (has_subject) subject = 'тема и ';
        			switch (attr[key]){
        				case '1':
                			search_str += 'в '+ subject +'тексте запроса';
                			$('#adv-search-in-request').attr('checked','checked');
                			break;
        				case '2':
                			search_str += 'в '+ subject +'ответах и обсуждениях';
                			$('#adv-search-in-log').attr('checked','checked');
                			break;
        				case '3':
        					if (has_subject) {
        						search_str += 'в тема, тексте запроса, ответах и обсуждениях';
        					} else {
        						search_str += 'в тексте запроса, ответах и обсуждениях';
        					}
                			$('#adv-search-in-log').attr('checked','checked');
                			$('#adv-search-in-request').attr('checked','checked');
                			break;
        			}
        			search_str += ' ';
        		}
        		if (key>0 && attr[key-1] == "user"){
        			search += '|user|'+attr[key];
        			var uname = '' , users =  $.wbs.get('users');
        			for(var id in users){
        				if (users[id].id == attr[key]) uname = users[id].name;
        			}
        			search_str += 'и пользователь <b>'+uname+'</b> ';
        			$("#adv-search-users").val(attr[key]);
        		}
        		if (key>0 && attr[key-1] == "umode"){
        			search += '|umode|'+attr[key];

        			switch (attr[key]){
        				case '1':
                			search_str += 'ответил на запрос';
                			$('#adv-users-reply').attr('checked','checked');
                			break;
        				case '2':
                			search_str += 'выполнял другие действия';
                			$('#adv-users-comments').attr('checked','checked');
                			break;
        				case '3':
                			search_str += 'ответил на запрос или выполнял другие действия';
                			$('#adv-users-comments').attr('checked','checked');
                			$('#adv-users-reply').attr('checked','checked');
                			break;
        			}
        			search_str += ' ';
        		}
        	}

        	delete gridData.requestData.filter;
        	search = search.substr(1,search.length);
        	search_str = search_str.replace('и','');
			gridData.requestData.search = search;
			$.wbs.pages.mainPage(search);
			$('#title h1').html('Результаты поиска');
			$search_params = $('<div id="search-params"><span>'+search_str+'</span></div>');
			$('label, span', $search_params).click(function(){
				$('#adv-search').wbsPopup('open');
			})
			if (!$('#search-params').length) $('#title').append($search_params);
			$('#adv-search-btn-search').button('enable')
    	    document.title = 'Результаты поиска — Поддержка';
		},
		requestsAllAction: function(attr){
    		//$('.ui-tree a[href="#/requests/"]').addClass('ui-state-highlight');
			//$.wbs.set('current-hash',"#/requests/");
			//delete gridData.requestData.filter;
			//delete gridData.requestData.search;
			$.wbs.pages.mainPage('filter=open');
		},

		requestsStateAction: function(attr){
			$.wbs.pages.mainPage('filter=state-'+attr[0]);
		},
		
		showEmptyBinDialog: function(state){
    		$('.refresh-link').hide();
			if (state == '-1') {
				var defaultText = 'Очистить корзину';
			} else {
				var defaultText = 'Очистить эту папку';
			}
			var $emptyBinLink = $('<a class="empty-bin-link" href="javascript:void(0)">'+defaultText+'</a>').css('float','right'),
			$dialog = $(
				'<p>Все запросы будут удалены без возможности восстановления.<br/><br/>'+
				'Удалить запросы?</p>'
			);
			$emptyBinLink.click(function(){
				if (!$('.grid-tbody tr').length){
					$("<p>Папка пуста</p>").dialog({
						modal:true,
						title: 'Подтверждение',
	                    buttons: {
	                        OK: function() {
	                            $(this).dialog('close');
	                        }
	                    },
	                    zIndex: 10000
					})
				} else {
					$dialog.dialog({
						modal:true,
						title: 'Подтверждение',
	                    buttons: {
	                        'Да': function() {
								$.wbs.ajaxRequest({
									url: '?m=requests&act=emptybin',
									requestMethod: 'post',
									data: {
										'state': state
									},
									callback: function(){
										gridData.updatePage();
										$('#textarea > div').empty();
										$dialog.dialog('close');
									}
								})
	                        },
	                        'Нет': function() {
	                            $(this).dialog('close');
	                        }
	                    },
	                    zIndex: 10000
					})
				}
			})
			$emptyBinLink.css({
				'top': $('#mainPage').offset().top + 10
			})
			$('body').append($emptyBinLink);
		},
		
		requestsTrashAction: function(attr){
			$.wbs.pages.mainPage('filter=trash');
			this.showEmptyBinDialog(-1)
		},
		requestsVerificationAction: function(attr){
			$.wbs.pages.mainPage('filter=verification');
			this.showEmptyBinDialog(0)
		},
		
		requestsArchiveAction: function(attr){
			$.wbs.pages.mainPage('filter=archive');
    		$('.refresh-link').hide();
		},

		requestsNotassignedAction: function(attr){
			$.wbs.pages.mainPage('filter=notassigned');
		},
		
		requestsMyAction: function(attr){
			$.wbs.pages.mainPage('filter=my');
		},

		requestsAssignedAction: function(attr){
			var filter = 'filter=assigned-'+attr[0];
			if ($.wbs.in_array('all', attr)) {
				filter += '-all';
			}
			$.wbs.pages.mainPage(filter);
		},

		requestsUnreadAction: function(attr){
			$.wbs.pages.mainPage('filter=unread');
		},
		
		requestsAddAction: function(attr){
			$.wbs.pages.loadPage('?m=requests&act=add');
		},
		
		sourcesAction: function () {
			$.wbs.pages.loadPage('?m=sources');
		},
		
		settingsAction: function () {
			$.wbs.pages.loadPage('?m=settings');
		},
		
		
		sourcesEditAction: function (params) {
			$.wbs.pages.loadPage('?m=sources&act=edit&id=' + params[0]);
		},
		sourcesDeleteAction: function (params) {
			$.wbs.pages.loadPage('?m=sources&delete=' + params[0]);
		},
		
		sourcesAddAction: function (params) {
			if (!params.length){
				$.wbs.pages.loadPage('?m=sources&act=add');
			} else {
				$.wbs.pages.loadPage('?m=sources&act=add&inner=1');
			}
		},		

		usersAction: function () {
			$.wbs.pages.loadPage('?m=users');
		},
		
		formsAction: function () {
			$.wbs.pages.loadPage('?m=forms');
		},
		
		formsAddAction: function () {
			$.post('?m=forms&act=save', {'add':1, 'name': 'Запрос в службу поддержки'}, function (response) {
				if (response.status == 'OK') {
					$.wbs.loadHash('/forms/info/' + response.data.id);
				}
			}, "json");
			return false;
		},				
		
		formsInfoAction: function(params) {
			$("#forms-controls").show();
			var callback = function () {
				$(".form-preview").show();
				$(".form-edit").hide();					
			};
			if ($("#form-tabs").length) {
				callback();
			} else {
				$.wbs.pages.loadPage('?m=forms&act=info&id=' + params[0], callback);
			}
		},
		
		formsEditAction: function(params) {
			var callback = function () {
				$("#forms-controls").hide();
				var tab = params[1] || 'fields';
				$("#form-tabs li").removeClass('tabs-selected');
				$("#form-tabs li.tab-" + tab).addClass('tabs-selected');
				$(".tabs-body:visible").hide();
				$("#form-" + tab).show();
				$(".form-preview").hide();
				$(".form-edit").show();				
			}
			if ($("#form-tabs").length) {
				callback();
			} else {
				$.wbs.pages.loadPage('?m=forms&act=info&id=' + params[0], callback);
			}			
		},
		formsDeleteAction: function (params) {
			$.wbs.pages.loadPage('?m=forms&delete=' + params[0]);
		},
		
		classesAction: function (params) {
			$.wbs.pages.loadPage('?m=classes','classesContainer');
		},
		
		defaultAction: function(attr){
        	var root = $.cookie('last-action-hash');
        	if (!root){
        		root = "#/requests/all/"
        	}
        	this.currentHash = root;
    		$currentLeftLink = $('.ui-tree a[href^="'+ root +'"]:first');
    		$currentLeftLink.addClass('ui-state-highlight');
			$.wbs.loadHash(root);
			
			if (parent) {
				parent.window.location.hash = root;
			} else {
				location.hash = root;
			}
			
		}
	}
})(jQuery);
