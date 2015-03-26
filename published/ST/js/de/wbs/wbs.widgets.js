$.wbs.widgets = {
		load: function(widgetName, $el, params){
			$el = this[widgetName]($el, params);
			if (params && params.innerWidget){
				$newEl = $("<div/>");
				if (this[params.innerWidget.name]) {
					$newEl = this.load(params.innerWidget.name, $newEl, params.innerWidget.params);
					var $insertionPoint = $('.ui-widget-body', $el);
					if ($insertionPoint.length) {
						$insertionPoint.append($newEl);
					} else {
						$el.append($newEl);
					}
				}
			}
			return $el;
		},
		toggleLoading: function($el, force){
			$el = $($el);
			if (force == 'hide') {
				$el.find('.request-loading').remove();
				return true;
			}
			if (!$el.find('.request-loading').length || force == 'show'){
				var $loadingDiv = $('<div class="request-loading"><img src="img/ajax-loader32.gif"/><span>Loading&hellip;</span></div>');
				$el.append($loadingDiv);
				$loadingDiv.css({
					opacity:'0.75',
					'z-index':'10000',
					'position':'absolute',
					'left':0,
					'top':0,
					'margin':0,
					'padding-top':'2.8em',
					'background': '#F0F3F5',
					'width':$el.width(),
					'height':$el.height(),
					'text-align':'center'
				});
			} else {
				$el.find('.request-loading').remove();
			}
		},
		showAlertMsg: function($parent, msg){
			msg = '<p>'+msg+'</p>';
			var $msg = $('<div class="alertmsg">'+msg+'</div>');
			$closeBtn = $('<div style="float:right; cursor:pointer; position: absolute; top:20px; right:20px;" class="close-log-message-btn ui-icon ui-icon-closethick"></div>');
			$closeBtn.click(function(){
				$msg.remove();
				if ($('#requests-count').length){
					var h = $('#requests-count').position().top;
					if (h == 0) h = 65;
					$('#checkEmail .refresh-link, .empty-bin-link').css('top', h + 2);
				}
				$('#container').layout('resizeAll');
			});

			if ($parent.find('.alertmsg p').length <= 8){
				if ($parent.find('.alertmsg').length){
					var alreadySet = false;
					$parent.find('.alertmsg p').each(function(){
						if ($(msg).text() == $(this).text()) alreadySet = true;
					})
					if (!alreadySet){
						$msg.width($parent.width() - 50);
						$parent.find('.alertmsg:first').append(msg);
					}
				} else {
					$msg.width($parent.width() - 50);
					$parent.prepend($msg.append($closeBtn));
				}
			}

			if ($('#requests-count').length){
				var h = $('#requests-count').position().top ;
				if (h == 0) h = 65;
				$('#checkEmail .refresh-link, .empty-bin-link').css('top', h + 2);
			}
			
			$('#container').layout('resizeAll');
		},
		leftMenu: function(el){
			/*el.sortable({
				handle: '.ui-portlet-header',
				connectWith: '.id\\:leftMenu'
			});*/

			var $menuHeader = $('<div id="toggle-main-menu"/>'),
			$toggleButton = $("<button style='float:right;' id='toggle-main-menu-btn'></button>").button();
			$('.ui-button-text',$toggleButton).remove();
			$toggleButton.append("<span class='ui-icon ui-icon-closethick'></span>");
			el.prepend($menuHeader.prepend($toggleButton));
			$toggleButton.click(this.toggleLeftPanel);
			/*
			$('.ui-tree',el).bind('tree_select', function(e, node){
				var $parent = $('span:first', $(node).closest('ul:not(.ui-tree-root)').parent());
				var title;
				if ($parent.length > 0) {
					title = $parent.html() + " -> " + $(node).html();
				} else {
					title = $(node).html();
				}
				$('body').trigger('header-change', title);
			});
			*/
		},
		portlet: function($el, params){
			$el.wbsPortlet(params);
			return $el;
		},
		requests: function(el){
			//$('body').append(treeEl);
			/*treeEl.wbsTree({
				json : [
					{ title : 'All open', url: '#/requests/'},
					{ title : 'My requests', url: '#/requests/my/'},
					{ id: 'unreadTitle', title : 'Unread', url: '#/requests/unread/'},
					{ id: 'verificationTitle', title : 'Waiting verification', url: '#/requests/verification/'},
					{ id: 'trashTitle', title : 'Trash', url: '#/requests/trash/'},
				]
			});*/
			var $div = $("<div/>");
			var $ul = $("<ul class='ui-tree ui-widget-content'></ul>")
			.append($("<li class='ui-tree-child'><a href='#/requests/all/'>All open</a></li>")
					.append($("<ul/>")
							.append("<li><a href='#/requests/assigned/"+$.wbs.get('current-user-id')+"/' id='myRequestsTitle' "+(parseInt($.wbs.get('unread_count')) > 0?" style='font-weight:bold' ":"")+">My requests "+(parseInt($.wbs.get('unread_count')) > 0?"<span>("+$.wbs.get('unread_count')+")</span>":"")+"</a></li>")
							.append("<li><a href='#/requests/notassigned/'>Not assigned</a></li>")
					)		
			)
			.append("<li class='delimeter'><span></span></li>");
			var states = $.wbs.get('data').states, $ul_child = $("<ul class='ui-tree-child'/>");
			for (var key in states){
				if (states[key]['id']>0 && states[key]['group'] == -101){
					$ul_child.append("<li><a href='#/requests/state/"+states[key]['id']+"/'>"+states[key]['name']+"</a></li>")
				}
			}
			$ul.append($("<li class='ui-tree-child'><a href='javascript:void(0)' class='ui-tree-child-non-active'>by status:</a></li>").append($ul_child))
			.append("<li class='delimeter'><span></span></li>")
			.append($("<li class='ui-tree-child'><a href='#/requests/archive/'>Archive (closed requests)</a></li>")
					.append($("<ul id='trash-list'/>")
							.append("<li><a href='#/requests/verification/' id='verificationTitle'>Waiting verification</a></li>")
							.append("<li><a href='#/requests/trash/' id='trashTitle'>Trash</a></li>")
					)
			)
			//$ul.append("<li class='delimeter'><span></span></li>")

			
			$div.append($ul);
			el.wbsPortlet({
				title: 'Requests',
				buttons: ['collapse'],
				content: $div
			});
			if (!$('#settingsPanel').length) $('#trash-list').remove()
		},
		/*assigners: function(el){
			var $div = $("<div/>"),
			$ul = $("<ul class='ui-tree ui-widget-content'></ul>")
			.append("<li><a href='#/requests/assigned/"+$.wbs.get('current-user-id')+"/' id='myRequestsTitle'>My requests (<span>"+$.wbs.get('unread_count')+"</span>)</a></li>");
			for (var key in $.wbs.get('users')){
				if ($.wbs.get('current-user-id') != $.wbs.get('users')[key]['id'])
					$ul.append("<li><a href='#/requests/assigned/"+ 
							$.wbs.get('users')[key]['id']+"/'>"+$.wbs.get('users')[key]['name']+"</a></li>");
			}
			$div.append($ul)
			el.wbsPortlet({
				title: 'Assignments',
				buttons: ['collapse'],
				content: $div
			});
			$('a', $ul).hover(function(){
				$(this).addClass('ui-state-hover');
			},function(){
				$(this).removeClass('ui-state-hover');
			})
		},*/
		settings: function(el){
			var $div = $("<div/>"),
			$ul = $("<ul class='ui-tree ui-widget-content'></ul>")
			.append("<li><a href='#/sources/'>Email boxes</a></li>")
			.append("<li><a href='#/forms/'>Forms</a></li>")
			//.append("<li><a href='#/classes/'>Classifiers</a></li>");
			$div.append($ul);
			el.wbsPortlet({
				title: 'Settings',
				buttons: ['collapse'],
				content: $div
			});
		},
		toggleLeftPanel: function(){
        	if (!$('#leftPanel').is(':visible')){
        		$('#leftPanel').show();
        		$('#splitter1').show();
        		$('#main-menu-wrapper').wbsPopup('close');
        		$('#toggle-main-menu-btn span').toggleClass('ui-icon-closethick')
        		$('#toggle-main-menu-btn span').toggleClass('ui-icon-pin-s')
        		$('#menuBtnDiv').hide();
    			$('#searchRequest').css('padding-left','30px');
    			$.cookie('left-panel','visible');
        	} else {
        		$('#leftPanel').hide();
        		$('#splitter1').hide();
        		$('#toggle-main-menu-btn span').toggleClass('ui-icon-closethick')
        		$('#toggle-main-menu-btn span').toggleClass('ui-icon-pin-s')
        		$('#menuBtnDiv').show();
    			$('#searchRequest').css('padding-left','80px');
    			$.cookie('left-panel','hidden');
        	}
    		$('#container').layout('resizeAll');
		},
		menuBtn: function(el){
			var $wrapper = $('<div id="main-menu-div"/>').css({'position':'absolute','margin-top':'6px'});
			//el.append("<div id='main-menu-width'>&nbsp;</div>");
			el.append($wrapper);
			var $btn = $("<button class='main-menu-btn'/>").button();
			$('.ui-button-text',$btn).remove();
			$btn.append("<span class='ui-button-text'>Folders▼</span>");
			$menu = $("<div id='main-menu'/>"),
			$items = $('#leftMenu').children().clone(true);
			
			$wrapper.append($btn);
			$('.ui-portlet-header span', $items).remove();
			var $menuWrapper = $("<div id='main-menu-wrapper'/>");
			$menuWrapper.append($menu);
			$menu
			.append($items)
			//$menu.css({overflow: 'visible'});
			var intervalId = false;

			$menuWrapper.wbsPopup({
	            parent: $btn,
	            absolute: true,
	            padding: 0,
				fitParent: false,
				open:function(){
					$menu.css({
						overflow:'visible'
					})
					var menuH = $menuWrapper.height(), childH = 0;
					if (menuH > $(window).height() - 100) {
						menuH = $(window).height() - 100;
						$menu.css({
							height: menuH,
							overflow:'hidden'
						})
						
						var 
						$upArr = $("<div id='main-menu-up-arr' class='ui-corner-tr'><a href='#'>▲</a></div>"),
						$downArr = $("<div id='main-menu-down-arr' class='ui-corner-bottom'><a href='#'>▼</a></div>");
						
						$menu.before($upArr);
						$menu.after($downArr);
						$downArr.hover(function(){
							intervalId = setInterval(
							function(){
								$menu.scrollTop($menu.scrollTop()+10);
							},
							35);
						}, function(){
							clearInterval(intervalId);
						})
						$upArr.hover(function(){
							intervalId = setInterval(
							function(){
								$menu.scrollTop($menu.scrollTop()-10);
							},
							50);
						}, function(){
							clearInterval(intervalId);
						})
					}
				},
				close: function(){
					clearInterval(intervalId);
					$('#main-menu-up-arr, #main-menu-down-arr').remove();
				}
	        });
			
			$menuWrapper.prepend($('#toggle-main-menu',$menu));
			$('a', $items).click(function(){
				$menuWrapper.wbsPopup('close')
			})
			//$('#main-menu-width').width($btn.outerWidth());
			el.hide();
		},
		searchRequest: function(el){
			var defaultText = "Search by request number or client1s name";
			defaultText = defaultText.replace("1","'");
			var $input =  $('<input style="width:300px; padding:4px;margin-right:5px;"'+
					'value="'+defaultText+'" type="text" id="searchRequestInput" />'),
				$btn = $("<button>Search</button>");

			$btn.button({
	            icons: {
	                primary: 'ui-icon-search'
	            }
	        });
			$input.click(function(){
				if ($(this).val() == defaultText){
					$(this).val('')
				}
			}).blur(function(){
				if ($(this).val().split(' ').join('') == '')
					$(this).val(defaultText);
			}).keypress(function(e){
				if (e.which == 13){
					$btn.click()
				}
			})
			$btn.click(function(){
				gridData.requestData.offset = 0;
				if ($input.val() != defaultText && $input.val().split(' ').join('') != ''){
					var hash = '#/requests/search/'+$.wbs.trim($input.val())+'/';
		    		$.wbs.loadHash(hash);
		    	//	$input.val(defaultText);
				} else {
					delete gridData.requestData.search;
					$input.val(defaultText);
					//gridData.updatePage();
				}
			})
			var $advanced = $('<a href="javascript:void(0)" id="adv-search-link">Advanced search</a>'),
			$adv_search = $('<div id="adv-search" />');
			$adv_search.append("<h2>Advanced search</h2>");

			var $usersList = $("<select id='adv-search-users'></select>");
			for (key in $.wbs.get('users')){
				$usersList.append("<option value="+$.wbs.get('users')[key].id+">"+$.wbs.get('users')[key].name+"</option>")
			}
			Array.prototype.sort.call(
			    $('option',$usersList),
			    function(a,b) {
			      return $(a).text().toLowerCase() > $(b).text().toLowerCase() ? 1 : -1;
			    }
			).appendTo($usersList); 
			$usersList.prepend('<option value="-1" selected="selected">&lt;anyone&gt;</option>');
			
			$usersList.change(function(){
				if ($(this).val() == "-1") {
					$('.adv-search-users-mode').attr('disabled','disabled');
					$('.adv-search-users-mode').removeAttr('checked');
				} else {
					$('.adv-search-users-mode').removeAttr('disabled');
				}
			})
			$adv_search.append($("<div class='adv-search-div'/>")
				.append($("<div class='adv-search-fields-div'/>")
						.append("<p class='with-field'><label for='adv-search-name'>Client name:</label><input id='adv-search-name' /></p>")
						.append("<p class='with-field'><label for='adv-search-email'>Client email:</label><input id='adv-search-email' /></p>")
						.append("<p class='with-field'><label for='adv-search-id'>Client Id:</label><input id='adv-search-id' /></p>")
				)
				.append($("<div class='adv-search-words-div'/>")
					.append("<p class='with-field'><label for='adv-search-words'>Words:</label><input id='adv-search-words' /></p>")
					.append("<p class='with-check'><input type='checkbox' value='0' checked='checked' class='adv-search-words-mode' disabled='disabled' id='adv-search-in-subject' /><label for='adv-search-in-subject'>in subject</label></p>")
					.append("<p class='with-check'><input type='checkbox' value='1' checked='checked' class='adv-search-words-mode w' disabled='disabled' id='adv-search-in-request' /><label for='adv-search-in-request'>in request text</label></p>")
					.append("<p class='with-check'><input type='checkbox' value='2' class='adv-search-words-mode w' disabled='disabled' id='adv-search-in-log' /><label for='adv-search-in-log'>in replies and discussions</label></p>")
				)
				.append($("<div class='adv-search-users-div'/>")
					.append($("<p class='with-field' />")
							.append("<label for='adv-search-users' id='user-list-label'>User:</label>")
							.append($usersList)
							)
					.append("<p class='with-check'><input type='checkbox' value='0' class='adv-search-users-mode' disabled='disabled' id='adv-users-reply' /><label for='adv-users-reply'>replied to request</label></p>")
					.append("<p class='with-check'><input type='checkbox' value='1' class='adv-search-users-mode' disabled='disabled' id='adv-users-comments' /><label for='adv-users-comments'>performed other actions</label></p>")
				)
			)
			var getSearchParams = function(){
				var str_words = false, str_users = false, words_val = $.wbs.trim($('#adv-search-words').val(), true);
				if (words_val != ''){
					$('.adv-search-words-mode').removeAttr('disabled');
					if ($('.adv-search-words-mode.w:checked').length > 0) {
							str_words = '/words/'+$.wbs.trim($('#adv-search-words').val(), true);
						if ($('#adv-search-in-log').is(':checked') && $('#adv-search-in-request').is(':checked')) {
							str_words += '/wmode/3/'
						} else {
							if ($('#adv-search-in-log').is(':checked')) {
								str_words += '/wmode/2/'
							}
							if ($('#adv-search-in-request').is(':checked')) {
								str_words += '/wmode/1/'
							}
						}
					}
				} else {
					$('.adv-search-words-mode').attr('disabled','disabled');
				}
				if ($('.adv-search-users-mode:checked').length > 0 && $('#adv-search-users').val() != '-1') {
					str_users = '/user/'+$('#adv-search-users').val();
					if ($('#adv-users-reply').is(':checked') && $('#adv-users-comments').is(':checked')) {
						str_users += '/umode/3/'
					} else {
						if ($('#adv-users-comments').is(':checked')) {
							str_users += '/umode/2/'
						} else {
							str_users += '/umode/1/'
						}
					}
				}
				var params = ($.wbs.trim($('#adv-search-name').val())?'/name/'+$.wbs.trim($('#adv-search-name').val()):'')+
					($.wbs.trim($('#adv-search-email').val())?'/email/'+$.wbs.trim($('#adv-search-email').val()):'')+
					($.wbs.trim($('#adv-search-id').val())?'/clientid/'+$.wbs.trim($('#adv-search-id').val()):'')+
					(words_val != '' && $('#adv-search-in-subject').is(':checked')?'/subject/'+$.wbs.trim($('#adv-search-words').val(), true):'')+
					(str_words?str_words:'')+
					(str_users?str_users:'');
				return $.wbs.trim(params);
			},
			$adv_search_btn_search = $("<button id='adv-search-btn-search'>Search</button>").button().click(function(){
				var params = getSearchParams()
				if (params != '') {
					$.wbs.loadHash('/requests/advsearch/'+params);
					$adv_search.wbsPopup('close');
				}
			});

			$adv_search_btn_search.button('disable');
			$adv_search.find('input').keyup(function(e){
				if (getSearchParams() == '') {
					$adv_search_btn_search.button('disable')
				} else {
					$adv_search_btn_search.button('enable')
					if (e.which == 13){
						$adv_search_btn_search.click()
					}
				}
			}).bind('paste', function(e) {
			        var el = $(this);
			        setTimeout(function() {
						if (getSearchParams() == '') {
							$adv_search_btn_search.button('disable')
						} else {
							$adv_search_btn_search.button('enable')
						}
			        }, 100);
			});
			$adv_search_btn_cancel = $("<button>Cancel</button>").button().click(function(){
				var $cl = $adv_search_clone;
				$adv_search.wbsPopup('close');
				$('#adv-search-name').val($('#adv-search-name',$cl).val())
				$('#adv-search-email').val($('#adv-search-email',$cl).val())
				$('#adv-search-id').val($('#adv-search-id',$cl).val())
				$('#adv-search-words').val($('#adv-search-words',$cl).val())
				if ($('#adv-search-in-subject',$cl).is(':checked')) {
					$('#adv-search-in-subject').attr('checked', 'checked')
				} else {
					$('#adv-search-in-subject').removeAttr('checked')
				}
				if ($('#adv-search-in-request',$cl).is(':checked')) {
					$('#adv-search-in-request').attr('checked', 'checked')
				} else {
					$('#adv-search-in-request').removeAttr('checked')
				}
				if ($('#adv-search-in-log',$cl).is(':checked')) {
					$('#adv-search-in-log').attr('checked', 'checked')
				} else {
					$('#adv-search-in-log').removeAttr('checked')
				}
				$('#adv-search-users').val($('#adv-search-users',$cl).val())
				if ($('#adv-users-reply',$cl).is(':checked')) {
					$('#adv-users-reply').attr('checked', 'checked')
				} else {
					$('#adv-users-reply').removeAttr('checked')
				}
				if ($('#adv-users-comments',$cl).is(':checked')) {
					$('#adv-users-comments').attr('checked', 'checked')
				} else {
					$('#adv-users-comments').removeAttr('checked')
				}
			});
			$adv_search.append($("<div class='button-pane' />").append($adv_search_btn_search).append($adv_search_btn_cancel));
			el.append($input).append($btn).append($advanced);
			var $adv_search_clone = $adv_search.clone(true);
			$adv_search.wbsPopup({
				padding:3,
				parentShadow: false,
				parent: $advanced,
				absolute: true,
				appendToBody: true,
				open: function(){
					$adv_search_clone = $adv_search.clone(true);
				}
			});
			$('.adv-search-users-mode, #adv-search-users').change(function(){
				if (getSearchParams() == '') {
					$adv_search_btn_search.button('disable')
				}  else {
					$adv_search_btn_search.button('enable')
				}
			})
		},
		checkEmail: function($el){
			
			var $linkText = $("<a href='javascript:void(0)'>"+"Refresh"+"</a>"),
			$linkLoading = $("<img src='img/ajax-loader-w.gif'/><span>&nbsp;Loading&hellip;</span>")
			$link = $("<div class='refresh-link'/>");
			$link.append($linkText);
			$el.append($link);
			$link.click(function(){
				if($link.find('a').length){
					$link.empty().append($linkLoading);
					$.get("?m=requests&act=check&force=1", function(response){
						response = $.parseJSON(response);
						$link.empty().append($linkText);
						
						if (response.status == 'ERR') {
							for (key in response.error){
								$.wbs.widgets.showAlertMsg($('#topPanel'), response.error[key])
							}
							gridData.updatePage();
						} else {
							//$('#grid').wbsGrid('setGridData');
							gridData.updatePage();
						}
					});
				}
			})
			return $el
		},
		button: function($el, params){
			$el.button(params);
			return $el
		},

		transform: function($el, params){
			var classes = $el.attr('class');
			$el = $("<"+params.tag+"/>");
			for (var key in params){
				if (typeof($el[key]) == "function")
					$el[key](params[key]);
			}
			$el.attr('class', classes);
			return $el
		},
		
		/*buttons1: function(el){
			var $req = $("<button id='new-request'><b>Add a new request</b></button>");
			$req.button().click(function(){
	    		var hash = '#/requests/add';
	    		hash = hash.replace(/^.*#/, '');
	    		$.historyLoad(hash);
			});
			var $btn = $("<button id='refresh-grid'><b>Refresh</b></button>");
			$btn.button().click(function(){
				$.get("?m=requests&act=check", "", function (response) {
					  $('#grid').wbsGrid('setGridData');
				});
			})
			el.append($req)
			el.append($btn);//.append($switcher);
		},*/
		title: function(el){
			var $content =  $('<h1></h1>'+
					'<div class="requests-count-div" style="display:none">('+
					'<span id="requests-count">0</span>'+
					'<span id="requests-count-label"> requests</span>)</div>');
					/*+
					((location.hash.search('assigned')>-1)?
					"<div id='assigned-filter'>"+
					"<a id='assigned-open' href='#'>open</a> | <a id='assigned-all' href='#'>all</a>"+
					"</div>":""));*/
			/*$('body').bind('header-change',function(e, txt){
				$content.html(txt);
			});*/
	    
			el.css('width','100%');
			el.append($content)

	    	/*if (location.hash.search('all')>-1){
				$('#assigned-all',$content).addClass('active');
			} else {
	    		$('#assigned-open',$content).addClass('active');
			}
		    $('#assigned-open',$content).click(function(){
		    	$.wbs.removeFromHash('all');
		    })
		    $('#assigned-all',$content).click(function(){
		    	$.wbs.addToHash('all');
		    })*/
		    
		},
		grid: function(el){
			el.wbsGrid();
		},
		toggleTabPanel:function(){
        	if (!$('#tabs-toolbar').length){
	        	$('.ticket-toolbar').before('<div id="tabs-toolbar"></div>');
				$.ajax({
	            	url:'?m=requests&act=tabs',
	            	data:{id: $.wbs.get('current-request-id')},
	            	dataType:'html',
	            	success:function(data){
			            $('#tabs-toolbar').css({
			            	position:'relative',
				            display:'block',
				            height:'32px',
				            'z-index':'100'
			            })
			            var newmsg = 'hide details';
			            $('#detail').text(newmsg); 
		            	$('#tabs-toolbar').append(data);
		            	$('#tabs-toolbar').append($('#toolbarbuttons'));
		            	//$('#ticket').height($(window).height() - $(".ticket-toolbar").outerHeight() - $("#tabs-toolbar").outerHeight() - 58);
		            	$('#textarea').wbsEditor('resize');
	            	}
	            })
            	$('#textarea').wbsEditor('resize');
        	} else {
	            var newmsg = 'show details';
    			if (typeof($defaultContent) != "undefined") {
    				var content = $defaultContent.clone(true);
    				$('#ticket').replaceWith(content);
    			}
	            $('#detail').text(newmsg); 
            	$('.buttons').prepend($('#toolbarbuttons'));
	        	$('#tabs-toolbar').remove();
            	$('#info-container').show();
            	$('.ticket-toolbar').show();
            	$('#textarea').wbsEditor('resize');
        	}
        	
		},
		toggleBottomPanel: function(force, notSetCookie){
	        if ($('#bottomPanel').is(':visible') || force=='hide'){
	       // if ($('#bottomPanel').length){
	        	if (!$('#gridPanel').is(':visible')) {
	        		this.toggleTopPanel();
	        	} else {
	        		//$('#grid tbody td.ui-state-highlight').removeClass('ui-state-highlight');
	        	}
	            $bottomPanel = $('#bottomPanel');
	            $gridSplitter = $('#splitter2');
	            //$('#bottomPanel').detach();
	            //$('#splitter2').detach();
	            $('#bottomPanel').hide();
	            $('#splitter2').hide();
	            gridPanelHeight = $('#gridPanel').height();
	            $('#gridPanel').height($('#mainPage').height()-$('#toolbar').outerHeight());
	            $('#grid').wbsGrid('resize');
	            if (typeof(scrollTop) != "undefined" && $('.ui-grid-wrapper').length) $('.ui-grid-wrapper')[0].scrollTop = scrollTop;
	            //$('.ui-grid-wrapper').scrollTo();

	            $('#grid').addClass('tr-cursor-pointer');

	            if (!notSetCookie) $.cookie('single-panel-view', 'true');
	            return ('Close');
	        }
	        if (!$('#bottomPanel').is(':visible') || force=='show'){
	            $('#bottomPanel').show();
	            $('#splitter2').show();
	        	$('#gridPanel').height(gridPanelHeight);
	            $('#grid').wbsGrid('resize');
	            if (!notSetCookie) $.cookie('single-panel-view', 'false');

	            return ('Maximize');
	        }
		},
		toggleTopPanel: function(force){
	        if ($('#gridPanel').is(':visible') || force=='hide'){
	        	$toolbar = $('#toolbar');
	            $gridPanel = $('#gridPanel');
	            $gridSplitter = $('#splitter2');
            	if ($('.ui-grid-wrapper').length) 
            		scrollTop = $('.ui-grid-wrapper')[0].scrollTop;
	            $('#toolbar').hide();
	            $('#gridPanel').hide();
	            $('#splitter2').hide();
	            $('#bottomPanel').height($('#mainPage').height());
            	$('#textarea').wbsEditor('resize');

	            $('#grid').addClass('tr-cursor-pointer');
	            $.cookie('single-panel-view', 'true');


	            return false;
	        }
	        if (!$('#gridPanel').is(':visible') || force=='show'){

	            $('#toolbar').show();
	            $('#gridPanel').show();
	            $('#splitter2').show();
	            
    			if (typeof($defaultContent) != "undefined") {
		    		var content = $defaultContent.clone(true);
		    		$('#ticket').replaceWith(content);
    			}
	            $('#info-container').show();
	            $('.ticket-toolbar').show();

            	//$('#textarea').wbsEditor('resize');

	            $('#grid').removeClass('tr-cursor-pointer');
				$('#gridPanel').height(parseInt($.cookie('grid-height'),10))
	            $.cookie('single-panel-view', 'true');
	            
	            return true;
	        }
		},
		textarea: function(el){
			el.wbsEditor({
				fill: true,
				showToolbar: false,
				editable: false
			});
		},
		splitter1: function(el){
			el.wbsSplitter();
		},
		splitter2: function(el){
			el.wbsSplitter({orientation:'horizontal'});
		},
		splitter3: function(el){
			el.wbsSplitter({'zIndex':1});
		},
		knowledge: function(el){
		},
		loadKnowledge: function($el){
			if(!$('#kb-wrapper').length){
				$el.load("?m=knowledge", function(){
					$(':button, :submit, input[type=button]').button();
				})
			}
		},
		border: function(el){
			el.addClass('ui-splitter');
		},
		logTemplate: function(response){
			if (response.data) {
				var data = response.data.info,
				action_name = (response.data.type).toLowerCase();
			} else {
				if (response.info){
					var data = response.info;
				} else {
					var data = response;
				}
				var action_name = (response.type).toLowerCase();
			}
		    msg = $('<div class="ticket-log-container log-state'+
		    data.state_id +'">' +
	        '<div class="header ui-corner-top '+(data.text ? '': 'ui-corner-bottom')+'">' +
	        '<span class="date"> ' +
	        data.datetime +
			'</span>' +
	        '<span title="" class="author"> ' +
	        data.contact + ' </span>' +
	        '<span class="action"> '+data.log_name+' </span>' +
	        (data.to ? '<span class="action">' + data.to + '</span>' : '') +
	        '</div>' +
	        (data.text ?
			'<div class="text ui-corner-bottom">' +
	        data.text +
	        '</div>' : "") +
	        '</div>');
			return msg;
		},
		showChangesInRequests: function(){
		    var date = new Date(), curTimeStamp = date.getTime();
	        $btn = $('<a href="#">Reload page</a>');
	        $closeBtn = $('<div style="float:right; cursor:pointer" class="close-log-message-btn ui-icon ui-icon-closethick"></div>');
			var unreadCount = 0,
			unreadTitle = 'Unread (0)',
			actions = $.wbs.get('data').actions;
			$.get('?m=requests&act=changes',{
				id: $.wbs.get('current-request-id'),
				lastreqlogid: $.wbs.get('last_req_log_id'),
				lastreqid: $.wbs.get('last_req_id'),
				lastlogid: $.wbs.get('last_log_id')
				//Math.floor( (new Date().getTime() - 60000) / 1000)
				}, function(data){
				data = $.parseJSON(data).data;
				unreadCount = data.count;
				$.wbs.set('unread_count', unreadCount);

				if (unreadCount == "0"){
					$('#myRequestsTitle').css('font-weight', 'normal');
					$('#myRequestsTitle span').remove()
				} else {
					$('#myRequestsTitle').css('font-weight', 'bold');
					if (!$('#myRequestsTitle span').length) 
						$('#myRequestsTitle').append("<span/>");
					unreadCount = "("+unreadCount+")";
				}
				$('#myRequestsTitle span').html(unreadCount);
				if ($('#title h1').text() == $('#myRequestsTitle').text()){
					$('#title h1').html($('#myRequestsTitle').text());
				}
				//--
				log = data.log;
                for (var key in log) {
                    if (key != 'actions') {
                        var l = log[key],
                        msg =
                        '<div class="log-message ticket-log-container">' +
	                        '<p class="action-info">' +
	                        '<span title="" class="author"> ' + l.contact +'</span>'+
	                        '<span class="action"> '+actions[l.action_id].log_name+' </span></p>' +
	                        //(l.to ? '<p class="action-to"><span class="to-label">To:</span><b>'+l.to+'</b></p>' : '')+
	                    '</div>';
                    }
                    
	                var $msg = $(msg);
	                
	                if (!$('#info-container .ticket-log-container').length){
	                	$msg.append($btn);
	                	$msg.prepend($closeBtn)
	                    $('#info-container').append($msg);
	                	$closeBtn.click(function(){
		    	        	$msg.remove()
		    	        })
	                	//$('#ticket').height($('#ticket').height() - $msg.outerHeight())
	                } else {
	                    $('.info-container .ticket-log-container').replaceWith($msg);
	                }
                }

    		    $btn.click(function(){
                    /*for (var key in log) {
                        if (key != 'actions') {
                            var l = log[key];
                            l.type = actions[l.action_id].type;
                            l.log_name = actions[l.action_id].log_name;
                            var log_msg = $.wbs.widgets.logTemplate(l);
	                        $('.tickets-left').append(log_msg);
                        }
                    }
                    $('#info-container .ticket-log-container').remove();
                    $('#ticket').height($('#ticket').height() - $msg.outerHeight())
    		    	return false;*/
    		    	$btn.html('Loading...');
    		    	if ($('#grid').length){
    		    		$('#grid').wbsGrid('selectBlock',$.wbs.get('current-request-id'));
    		    	} else {
    		    		location.reload();
    		    	}
    		    });
    		    
                //--
    		    if ($.wbs.controller.currentAction != 'requestsAdvsearch' 
    		    	&& 
    		    	$.wbs.controller.currentAction != 'requestsSearch') {
	                if (data.updated != "") {
	                	if (typeof(gridData) != "undefined") {
	                		var refreshedIds = '';
		                	for (var key in data.updated){
		                		if ($(".ui-grid-column-id[rel='"+data.updated[key].data.id.data+"']").length) {
		                			gridData.updateBlockByData(data.updated[key]);
		                		} else {
		                			refreshedIds += data.updated[key].data.id.data+',';
		                		}
		                	}
		                	if (refreshedIds != ''){
		                		gridData.insertNewBlocks(refreshedIds,$.wbs.get('last_req_id'));
		                	}
	                	}
	                	var h = parent ? parent.window.location.hash : location.hash;
                		gridData.checkForIrrelevantRow(true);
	                }
	                //--
	                if (data.newRequests[0]['COUNT(*)'] > 0) {
	                	if (typeof(gridData) != "undefined") {
	                		gridData.requestData.offset=0;
	                		gridData.insertNewBlocks(false, $.wbs.get('last_req_id'));
	                	}
	                	gridData.checkForIrrelevantRow(true);
	                }
    		    }
				$.wbs.set('last_req_id',data.last_req_id);
				$.wbs.set('last_log_id',data.last_log_id);
                //--
			})
		},
		classChanger: function(id){
			$dialog = $('<div/>');
			$.wbs.ajaxRequest({
				url: '?m=classes&act=list&id='+id,
				callback: function(data){
					classifyBtnClick = false;
					var outputStr = '';
					for(key in data){
						var classType = data[key], sel = '';
						
						classTypeStr = '<div class="classTypeDiv ui-widget-content ui-corner-all">'+
						'<h4 class="ui-state-default ui-corner-top">'+classType.name+'</h4>';
						
						for(classKey in classType.classes){
							var _class = classType.classes[classKey];
							classTypeStr += '<p><input type="'+((classType.multiple==1)?'checkbox':'radio')+'" id="classes_'+_class.id+'" name="classes['+((classType.multiple==0)?classType.id:'')+']" '+
					        'value="'+_class.id+'" '+((_class.selected==1)?'checked':'')+' />'+
					        '<label for="classes_'+_class.id+'">'+_class.name+'</label></p>';
							if (_class.selected==1) {sel = _class.id}
						}
						if (classType.multiple==0) 
							classTypeStr += '<input class="selectedvalue" value="'+sel+'" type="hidden">';
						classTypeStr += '</div>';
						outputStr += classTypeStr;
					}
					outputStr = '<form>'+outputStr+'</form>';
					$btnSave = $('<button>Save</button>').button();
					$btnCancel = $('<button>Cancel</button>').button();
					$btnSave.click(function(){
						$btnSave.attr('disabled',true);
						$.wbs.ajaxRequest({
							url: '?m=classes&act=requestsave&id='+id,
							data: $('form',$dialog).serialize(),
							callback: function(data){
								/*var msg="";
								for (key in data.classes){
									var el = data.classes[key];
									msg += '<div class="classTypeDiv">'+
						            '<h4>'+el.name+'</h4>'+
						            '<ul class="classes-list">';
									for (clKey in el.classes){
										msg += '<li>'+el.classes[clKey].class_name+'</li>'
									}
						            msg += '</ul></div>';
								}
								if (data.classes==0){
									msg = '<p>not specified</p>';
								}
						        $('#classes_block').html(msg);
						        if ($('#classes_block').next()[0].tagName=="P")
						        	$('#classes_block').next().remove();

								msg = $.wbs.widgets.logTemplate(data);
						        $('.tickets-left').append(msg);*/
							$('.ui-editor-editablefield').load('?m=requests&act=info&id='+$.wbs.get('current-request-id'), function(){
								$dialog.remove();
                                //$('#textarea').wbsEditor('resize');
							});
							
							}
						});
					})
					$btnCancel.click(function(){
						$dialog.remove();
					})
					$dialog.append(outputStr).append($btnSave).append($btnCancel);
					
					$('form',$dialog).css({
						'max-height': $(window).height()-150,
						'overflow':'auto'
					})
					$('form',$dialog).children().css('margin-bottom',10)
					
					$('form input[type="radio"]',$dialog).click(function(){
						var parent = $(this).parent().parent();
				        if ($(this).val() == $('.selectedvalue', parent).val()) {
							$(this).attr('checked', false);
				            $('.selectedvalue', parent).val('');
						} else {
				            $('.selectedvalue', parent).val($(this).val());
						}
				    })
				    
					$dialog.dialog({
						"modal": true,
						"title": 'Change classifiers',
						"zIndex": 10000,
						"open": function(event, ui) {
							$parent = $(event.target).parent();
							var oldWidth = $parent.width(),
								newWidth = Math.round($(window).width() / 2),
								lambda = (newWidth-oldWidth) / 2;
							$parent.width(newWidth)
							$parent.offset({left:$parent.offset().left - lambda})
						}
					});
				}
			})
		}
}