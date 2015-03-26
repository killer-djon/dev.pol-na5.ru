$.wbs.request = {
		
	parseEmails: function(){
	    $(".action-to").find('b').each(function(){
	        var $this = $(this), text = $this.html(),
	        arr = text.split(',');
			text = [];
	        for (key in arr){
	            var arr2 = arr[key].split('; ');
	            for (key2 in arr2){
					arr2[key2] = "<span title='"+arr2[key2]+"'>"+arr2[key2]+"</span>"
				}
	            text.push($.wbs.implode(", ",arr2));
			}
			$this.html($.wbs.implode(", ",text));
	    });
		$(".action-to").find('b span').each(function(){
			var $this = $(this), text = $this.html(),
			arr = text.split(' ');
			if (arr.length > 1){
				$this.html('');
				for (key in arr){
		            if (arr[key].indexOf('@')<2){
						$this.html($this.html()+' '+arr[key]+'');
					}
				}
			}
		})
	},
	
	contactPanel: function(){
		var self = this;
	    $("#client_requests_link").click(function(){
			$.wbs.loadHash('#requests/advsearch/clientid/' + self.data.client_c_id + '/id/' + self.data.id);
		})
		$('#client_name').click(function(){
			$('.contact-panel').toggle();
		})
	},

	requestSidebar: function(){
        $('.ticket-sidebar').wbsPopup({
			padding:3,
			parentShadow: false,
			parent: $('.show-right-column'),
			absolute: true,
			appendToBody: true,
		    my: "right top",
		    at: "right bottom",
			open:function(){
        		$('.show-right-column').html('hide details'); 
        	},
			close:function(){
        		$('.show-right-column').html('show details'); 
        	}
        })
        $('.ticket-sidebar').click(function(){
        	$(this).wbsPopup('close');
        })
	    $('td:first', $('tr',$('.right-panel-block table'))).css({
	        'text-align':'right',
	        'padding-right':'5px'
	    });
	},
	
	topBtnEvents: function(){
		var $gridPanel, $gridSplitter;
        $('#detail').click(function(){
            $('#detail').text('Loading...'); 
            $.wbs.widgets.toggleTabPanel();
            return 0;
        })
		$('.backlink').click(function(){$('#close:first').click()});
        if (!$('#gridPanel').is(':visible')){
            $('.maximize').html('Minimize');
            $('.maximize').button({text: false, icons: {
                primary: 'ui-icon-arrowreturnthick-1-s'
            }});
            $('.backlink').show();
        } else {
            $('.maximize').html('Expand');
            $('.maximize').button({text: false, icons: {
                 primary: 'ui-icon-extlink'
            }});
            $('.backlink').hide();
        }
        maximize_click = function(){
            $('.backlink').toggle();
            if ($('#gridPanel').is(':visible')){
                $( ".maximize" ).button( "option", "label", "Minimize" );
                $('.maximize span').addClass('ui-icon-arrowreturnthick-1-s').removeClass('ui-icon-extlink');
            } else {
                $( ".maximize" ).button( "option", "label", "Expand" );
                $('.maximize span').addClass('ui-icon-extlink').removeClass('ui-icon-arrowreturnthick-1-s');
            }
            var mini = $.wbs.widgets.toggleTopPanel();
			if (mini) $.cookie('single-panel-view', 'false')
            $.wbs.getClosestLayout("textarea").resizeAll();
        }
        $('#new_window', '#toolbarbuttons').button({text: false, icons: {
            primary: 'ui-icon-newwin'
            }}).click(function(){
            window.open($.wbs.currentUrl);
        })
        $('#close', '#toolbarbuttons').button({text: false, icons: {
            primary: 'ui-icon-closethick'
        }}).click(function(){
            $.wbs.widgets.toggleBottomPanel();
        })
	},

	otherActionsBtn: function(){
		var self = this, items = $('.act-items:first'), cloneItems = items.clone(true);
		$('#assignMsg').hide();
		$('button').button();

		if (!$.browser.msie){
		    toolbarClone = $('.ticket-toolbar').clone(true);
		}
		items.wbsPopup({
		    parent: $('#other-actions'),
			myCorners: "ui-corner-bottom ui-corner-tl",
		    fitParent:false,
		    padding:0,
			appendToBody:true,
		    absolute:true,
		    my: "right top",
		    at: "right bottom"
		})

		$('.ticket-toolbar').addClass('ticket-toolbar-top');
		if (!$.browser.msie){
			$("#other-actions",toolbarClone).attr('id','other-actions-clone');
			toolbarClone.addClass('ticket-toolbar-bottom');
		    toolbarClone.removeClass('ticket-toolbar-top');
		    $("#toolbarbuttons",toolbarClone).hide();
			if ($('#container').length){
			    $('#ticket').after(toolbarClone);
			} else {
			    $('body').append(toolbarClone);
		        $(".backlink").remove();
			}
			if ($('.ticket-log-container').length > 0){
				var $selectSort = $('<select></select>')
					.append('<option value="ASC">oldest entries first</option>')
					.append('<option value="DESC">newest entries first</option>'),
				$sortPane = 
					$('<div class="log-order-div"/>')
					.append('<span>Sort request history:</span>')
					.append($selectSort);
				toolbarClone.append($sortPane);
				$('option[value=' + self.data.order + ']',$selectSort).attr('selected','selected');
				$selectSort.change(function(){
					$.post('?m=requests&act=save', {id: self.data.id, order: $selectSort.val()},
					 function(){
                        if ($(".ui-editor-editablefield").length){
							$('#textarea .ui-editor-editablefield').load('?m=requests&act=info&id='+self.data.id, function(){
								$(".ui-editor-editablefield").scrollTo($(".log-order-div"));
                            });
                        } else {
			            	$(document).scrollTop($(document)[0].scrollHeight);
                        	location.reload();
                        }
					 });
				})
			}
			$('#other-actions-clone .menu-label').next().html('▲');
			
			cloneItems.wbsPopup({
			    parent: $('#other-actions-clone', toolbarClone),
			    parentCorners: "ui-corner-bottom",
			    myCorners: "ui-corner-top ui-corner-bl",
			    fitParent:false,
			    padding:0,
		        appendToBody:true,
			    absolute:true,
			    my: "right bottom",
			    at: "right top"
			})
			
		    toolbarClone.find('.act-items:last').remove();

		} else {
		    toolbarClone = $('<div/>');
			if (!$('#container').length){
				$(".backlink").remove();
			}
		}
	},
	
	/*title окна браузера*/
	setDocTitle: function(){
	    var docTitle = this.data.from_name + ' — ' + this.data.subject;
	    docTitle = docTitle.replace(/\&quot\;/gi,'"');
	    document.title = docTitle;
	},

    // Toggle original text and quotes
	toggleQuotes: function(){
	    var show_quote = $('<div class="show-quote" href="javascript:void(0)">- Display original message -</div>').click(function () {
	        if ($(this).hasClass('open')) {
	            $(this).next().hide();
	            $(this).html('- Display original message -').removeClass('open');
	        } else {
	            $(this).next().show();
	            $(this).html('- Hide original message -').addClass('open');
	        }
	    });
	    $("#ticket div.text blockquote").each(function () {
	    	$(this).hide();
	        show_quote.clone(true).insertBefore(this);
	    });
	},
	removeSignatures: function(){
		var $first = $(".wa-st-signature blockquote:first")
		$first.parent().parent().append($first.prev()).append($first);
	    /*$(".wa-st-signature blockquote").each(function () {
	    	var $this = $(this); 
	    	$this.prev().show();
	    	$first.append($this.prev()).append($this.hide());
	    });*/
	    $(".wa-st-signature").each(function () {
	    	if ($(this).prev().length){
	    		$(this).hide();
	    	}
	    });
	},
	
	info: function(){
		var self = this;
		this.parseEmails();
		this.contactPanel();
		this.requestSidebar();
		this.topBtnEvents();
		this.otherActionsBtn();
		this.setDocTitle();
		this.removeSignatures();
		this.toggleQuotes();

		$('.request-content a, .ticket-log-container a').attr('target', '_blank');
		
		/*for fullscreen mode*/
	    if (!$('#container').length){
	        document.getElementById("toolbarbuttons").style.display = "none";
		    $('#ticket-wrapper').append(toolbarClone);
	        $('#ticket-wrapper').css('margin-top','40px');
	        $('#ticket').css('margin-top','10px');
	        $('.ticket-toolbar-top').css({
				'position':'fixed',
				'top':'40px'
			});
	    } else {
	    	if ($.browser.msie && parseInt($.browser.version) < 8){
		        $('#ticket').css('position','relative');
		        $('#ticket').css('top','+40px');
		        $('#textarea').css('padding-top',0);
		        $('#textarea > div').css('padding-bottom',40);
		        $('.ticket-toolbar').css('width',$('.ticket-toolbar').width()-16);
		        $(window).resize(function(){
			        $('.ticket-toolbar').css('width',$(window).width()-16);
		        })
	    	}
	    }

	    
	    $id = self.data.id;
	    $.wbs.set('loaded-request-id',$id);
	    $.wbs.set('last_req_log_id', self.data.last_req_log_id);
	    $defaultContent = $('#ticket').clone(true);

	    $("a[href='#']").click(function(e){
	        return false;
	    });
	},
	
	header: function(){
		var self = this, classifyBtnClick = false; 
		var $searchFormClone = $('#ticket-wrapper #contact-search-form').clone()
		$('body > .ticket-sidebar, body > .act-items').remove()
		$(".request-action").click(function () {
			
			if ($('#contact-search-form.ui-popup').length){
				$('body > #contact-search-form').remove()
				$('#ticket-wrapper').append($searchFormClone)
			}
			
			$('.act-items').wbsPopup('close');
		    var action_id = $(this).attr('rel').replace(/action/, '');
		    var actions = $.wbs.get('request-actions');
		    var action = actions[action_id];
		    var type = action.type; 

		    if (type == 'CLASSIFY') {
				if (!classifyBtnClick) {
					classifyBtnClick = true;
		            $.wbs.widgets.classChanger($id)
				}
		        return false;
		    }
		    
		    $("div.menu div.items").hide();
			
		    autocompleteClass = 'contacts';
		    var to = '';
		    var properties = {};
			if (action.hasOwnProperty('properties')){
				if (action.properties.length>0){
			        properties = $.parseJSON(action.properties);
					
				    if (properties && properties.hasOwnProperty('autocomplete')) {
			            autocompleteClass = properties.autocomplete;
					}
				    
				    if (properties && properties.hasOwnProperty('to') && properties.to == 'users') {
				    	to = users;
				    }
				}
			};
			if (!to) {
				to = '<input type="text" class="autocomplete-'+autocompleteClass+' required" name="to" autocomplete="off" /><button class="contact-search-btn">Select contact</button>';
			}

		    var content = '';
		    switch (type) {
		        case 'REPLY': 
		            to = '<div class="reply-to-msg">' + self.data.client_from + '<input name="to" autocomplete="off" value="' + self.data.client_from + '" type="hidden"/></div>';
				case 'FORWARD':
		            content = '<table width="100%" cellpadding="0" cellspacing="0" border="0" class="form-with-editor">'+             
		            '<tr><td width="1%" class="inp-label">To: </td><td>' + 
		            to +    
		            '</td></tr><tr class="copy" style="display: none">'+
		            '<td width="1%" class="inp-label" style="margin-right:10px">Copy: </td>' + 
		            '<td><input type="text" id="copy-input" class="copy-input autocomplete-'+autocompleteClass+'" name="cc" autocomplete="off" /><button class="contact-search-btn">Select contact</button></td>' +
		            '</tr><tr class="bcc" style="display: none">' + 
		            '<td width="1%" class="inp-label">&nbsp;Bcc: </td>' +
		            '<td><input type="text" id="bcc-input" class="bcc-input autocomplete-'+autocompleteClass+'" name="bcc" autocomplete="off" /><button class="contact-search-btn">Select contact</button></td>' + 
		            '</tr><tr><td></td>'+
		            '<td><a href="javascript:void(0)" onclick="$.wbs.request.showChild(this, \'copy\'); $(\'#copy-input\')[0].focus();">Copy</a>'+
		            '<a href="javascript:void(0)" style="margin-left:10px" onclick="$.wbs.request.showChild(this, \'bcc\'); $(\'#bcc-input\')[0].focus(); ">Bcc</a>' + 
		            '</td></tr><tr><td></td>' + 
		            '<td><div class="wbs-editor"></div></td></tr>' +
		            '<tr><td></td><td><div class="error" style="display:none"></div>' + 
		                '<div class="button-pane">'+
						'<button class="save">Send</button>' + 
		                '<button class="cancel">Cancel</button>' +
		            //    '<button class="knowledge" style="float:right">Find answer in Knowledge base</button>' +
						'</div>'+  
		            '</td></tr>' + 
		            '</table>';
		            break; 
		        case 'FORWARD-ASSIGN':
		            content = '<table width="100%" cellpadding="0" cellspacing="0" border="0" class="form-with-editor">' + 
		            '<tr><td width="1%" class="inp-label"><span class="assign">To</span>: </td><td>' + 
		            users +    
		            '</td></tr><tr class="copy" style="display: none">'+
		            '<td width="1%" class="inp-label" style="margin-right:10px">Copy: </td>' + 
		            '<td><input type="text" id="copy-input" class="copy-input autocomplete-'+autocompleteClass+'" name="cc" autocomplete="off" /><button class="contact-search-btn">Select contact</button></td>' +
		            '</tr><tr><td></td>'+
		            '<td><a href="javascript:void(0)" onclick="$.wbs.request.showChild(this, \'copy\'); $(\'#copy-input\')[0].focus();">Copy</a>'+
		            '</td></tr><tr><td></td>' + 
		            '<td>';
					if (action.hasOwnProperty('properties')){
						if (action.properties.length>0){
					        properties = $.parseJSON(action.properties);
						    if (properties && properties.hasOwnProperty('comment')) {
					            content += '<div style="margin-bottom: 10px">' + properties.comment + '</div>';
							}
						}
					}		            
		            content += '<textarea class="comment-text" name="text" rows="5" style="width: 98%"></textarea></td></tr>' +
		            '<tr><td></td><td><div class="error" style="display:none"></div>' + 
		                '<div class="button-pane">'+
						'<button class="save">Send</button>' + 
		                '<button class="cancel">Cancel</button>' +
		            //    '<button class="knowledge" style="float:right">Find answer in Knowledge base</button>' +
						'</div>'+  
		            '</td></tr>' + 
		            '</table>';
		            break; 
		            
		            break;		            
		        case 'ASSIGN': 
		            $to = $(users).css("width", "100%");
		            $editor = $("<textarea rows='5' style='width:420px;padding:10px;' name='text'></textarea>");
					$to.change(function(){
						 $('option[value=0]',$to).remove();
						if ($to.val()== 0){
			                $('.ui-dialog .ui-button:first').button( "disable" );
						} else {
							$('.ui-dialog .ui-button:first').button( "enable" );
						}
					})
		            $label = $('<label for="sendAssignMsg">Send notification</label>')

		            content = $("<div style='padding-left:10px'/>").append($to).append($("<div/>").append($editor));
					
		            break;
		        case 'COMMENT':
		        	
		        	content = '';
		        	if (properties.copy) {
			            content = '<table width="100%" cellpadding="0" cellspacing="0" border="0" class="form-with-editor">' + 
			            '<tr class="copy">'+
			            '<td width="1%" class="inp-label" style="margin-right:10px">Copy: </td>' + 
			            '<td><input type="text" id="copy-input" class="copy-input autocomplete-'+autocompleteClass+'" name="cc" autocomplete="off" /><button class="contact-search-btn">Select contact</button></td>' +
			            '</tr></table>';
		        	}
		        	
		        	if (properties.editor && properties.editor == 'html') {
		        		content += '<div class="wbs-editor comment-text"></div>';
		        	} else {
		        		content += '<textarea class="comment-text" name="text" rows="5" style="width: 98%"></textarea>';
		        	}
		            content += '<div class="button-pane">' + 
		            '<button class="save">Send</button>' + 
		            '<button class="cancel">Cancel</button>' + 
		            '</div>';
		            
		            break;
		        default: 
		        break;
		    }

		    if (content) {
		    	$('.ticket-toolbar-bottom').hide();
		        var html = '<div class="action-header header" style="border-bottom-color:'+
				(
				$.wbs.get('data').states[$.wbs.get('data').actions[action_id].state_id] ? 
				$.wbs.get('data').states[$.wbs.get('data').actions[action_id].state_id].properties.css.replace("color:","")
				: ""
				)		
				+'">' + ((self.data.current_user.photo != '')?'<div class="userpick"><img height="32" src="'+self.data.current_user.photo+'&size=96"></div>':'') + '<p class="action-info">'+
		        '<span title="" class="author">'+self.data.current_user.fullname+'</span> '+
		        '<span class="action">'+$.wbs.get('data').actions[action_id].log_name+'</span>'+
				'</p></div>' + 
		        '<div class="action-form text">' + 
		        '<form class="ajax-form" method="post" enctype="multipart/form-data" action="?m=requests&act=save">' +
		            '<input type="hidden" name="id" value="'+self.data.id+'" />' +
		            '<input type="hidden" name="action_id" value="' + action_id + '" />' +
		        '</form>' +
		        '</div>';
		        html = $(html); 
		        var form = html.find('form');
		        form.append(content);
				if (type=='COMMENT' && !(properties.editor && properties.editor == 'html')) {
				var 
		            $attach_wrapper = $("<div class='ui-editor-attachments'/>")
		            $attach_link = $('<a href="javascript:void(0)" id="attach_link" class="attach_link">Attach file</a>'),
		            $attach_div = $("<div id='attach_div' class='attach_div'/>"),
		            $input = $("<input type='file' name='files[]' />");

		            $attach_wrapper.append($attach_link);
		            form.find('.comment-text').after($attach_wrapper);
		            $attach_link.after($attach_div.append($input));
		            $input.css({'height': '1.4em'});
		            $attach_div.css({
		                'opacity':0,
		                'position':'relative',
		                'top': '-1.3em',
		                'width': 200,
		                'height':  '1.4em',
		                'overflow': 'hidden'
		            })
		            $attach_div.hover(function(){
		                $attach_link.css('text-decoration','underline')
		            },function(){
		                $attach_link.css('text-decoration','none')
		            })
		            $input.change(function(){
		                var 
		                $attach_div = form.find('#attach_div'),
		                $input = $attach_div.find('input'),
		                $deleteBtn = $("<button>Delete file</button>").button({text: false, icons: {
		                    primary: 'ui-icon-circle-close'
		                }}).click(
		                    function(){
		                        $parent.remove();
		                    }
		                ),
		                $parent = $("<div class='attached-file'/>").append($input).append("<span>"+$input.val()+"</span>").append($deleteBtn);
		                $input.hide();
		                form.find('#attach_link').before($parent);
		                $attach_div.append($input.clone(true).val('').show());
		            })
		        }
		        form.find('.cancel').click(function () {
		            $("#toolbarbuttons .buttons").show();
		            $("#action-container").empty().hide();
		            form.find('div.wbs-editor').wbsEditor('resize');
		        });
		        form.find('div.wbs-editor').wbsEditor({fill:true, withFrame:true, attachFiles: true, menuOffset:true, name:'text'});
		        if (type == 'REPLY' || type == 'FORWARD') {
		            form.find('div.wbs-editor').wbsEditor('setContent', $("#template-" + action_id).val());
		        }
		        
		        /*Autocomplete*/
		        form.find("input.autocomplete-contact").autocomplete('?m=contacts&act=list&email=1');
				form.find('input.autocomplete-users').autocomplete('?m=users&act=list&email=1');
				form.find('.contact-search-btn').button({text: false, icons: {
                    primary: 'ui-icon-person'
                }})
                form.find('input[name=to]').blur(function(){
                	form.find('.wbs-editor').wbsEditor('focus')
                })
                
		        var submitByIframe = function(){
		            var id = 'iframe-'+Math.round(Math.random() * 1000000 + 1),
		                iframe = jQuery('<iframe id="' + id + '" name="' + id + '"></iframe>').css('display', 'none').appendTo('body');     

		            form.attr('target', iframe.attr('name'));
		            
		            setTimeout(function () {
		                iframe.load(function(){
		                    var data = iframe.contents().find('body').html();
							if (data){
								//data = data.substring(0,data.indexOf('}}}')+3);    
				                $(".assignDialog").remove();
				                if ($('#textarea > .ui-editor-editablefield').length > 0){
				                    var response = $.parseJSON(data);
				                    if (response.status == 'OK') {
				                        if (response.data.success){
				                            $("#toolbarbuttons .buttons").show();
				                            $(".assignDialog").dialog('close');
				                            $('.ui-editor-editablefield').load('?m=requests&act=info&id='+self.data.id, function(){
		                                        $.wbs.widgets.toggleLoading($('#textarea'));
				                                $(".assignDialog").dialog('close')
				                                $('#textarea').wbsEditor('resize');
				                                if ($(".ui-editor-editablefield").length){
				                                	if (self.data.order=="ASC"){
				                                		$(".ui-editor-editablefield").scrollTo($(".ticket-log-container:last"));
				                                	} else {
				                                		$(".ui-editor-editablefield").scrollTo($(".ticket-log-container:first"));
				                                	}
				                                }
				                            });
				                        }
				                    } else {
				                        $("<div>"+response.error+"</div>").dialog(
				                            {    
				                                modal: true,
				                                title: 'Error',
				                                buttons: {
				                                    OK: function() {
				                                        $(this).dialog('close');
				                                        $.wbs.widgets.toggleLoading($('#textarea'));
				                    		    		$('#grid').wbsGrid('selectBlock',$.wbs.get('current-request-id'));
				                                    }
				                                },
				                                zIndex: 10000
				                            }
				                        )
				                    }
				                } else {
		                            $.wbs.widgets.toggleLoading($('body'));
									location.reload();
								}
				                if (typeof(gridData) != 'undefined') gridData.updateBlock(self.data.id); 
							}
		                });
		            }, 100);
		        } 
				submitByIframe();
				
				form.submit(function () {
			    	$('.ticket-toolbar-bottom').show();
					var error = false;
		            $(this).find('.required').each (function () {
		            	
		                if (!$(this).val() || $(this).val() == '0') {
		                    error = true;
		                    $(this).addClass('error').focus(function () {
		                        $(this).removeClass('error');
		                    });
		                }   
		            });
		            if (error) {
		                $(this).find('div.error').html('Please, fill required fields.').show();
		                return false;
		            } else {
		                $(this).find('div.error').empty().hide();
		            }

	                if ($('#textarea > .ui-editor-editablefield').length > 0){
	                	$.wbs.widgets.toggleLoading($('#textarea'));
	                } else {
	                	$.wbs.widgets.toggleLoading($('body'));
	                }
					
		        });  
				
		        if (type!="ASSIGN"){
		            $("#action-container").addClass("ticket-log-container").empty().append(html);
		            if (self.data.order=="ASC"){
						$("#ticket").append($("#action-container"));
			            if ($("#textarea").length){
			               setTimeout(function(){
			            	   $("#textarea .ui-editor-editablefield").scrollTo($("#action-container"));
			               }, 100)
			            } else {
			            	$(document).scrollTop($('body').height());
			            }
		            } else {
						$("#info-container").after($("#action-container"));
			            if ($("#textarea").length){
			               setTimeout(function(){
			            	   $("#textarea .ui-editor-editablefield").scrollTo($("#action-container"));
			               }, 100)
			            } else {
			            	$(document).scrollTop(0);
			            }
		            }
					$("#action-container").show();
		            $('textarea',form).focus();
		        } else {
		            var $dialog = $(".assignDialog").clone();
		            $dialog.empty();
		            $dialog.append(html);
		            $dialog.dialog({
		                title: 'Change assigment',
		                width: 500,
						modal: true,
						buttons: {
						    "Assign": function(){
		                        $('.ajax-form .ui-editor').wbsEditor('setTextareaContent');
								form.submit();
		                        $dialog.remove();
						    },
						   "Cancel": function(){
				                $dialog.remove();
						    }
						},
		                zIndex: 10000
		            });
		            $('textarea',html).focus();
					if (self.data.assigned_c_id == 0) {
		                $('.ui-dialog .ui-button:first').button( "disable" );
					}
		            $dialog.css('padding',0);
		            $(".action-header",$dialog).hide();
		            $dialog.css('min-height',
		             $(".action-form",$dialog).height()
		            );
		        }
		        $('.save').click(function(){
		            $('.wbs-editor').wbsEditor('setTextareaContent');
					form.submit()
				})
				
		        $('.cancel').click(function(){
			    	if ($('.ticket-toolbar-bottom').length) $('.ticket-toolbar-bottom').show();
		            $("#textarea").wbsEditor("resize")
		        })
		        
				/*toggle knowledge base*/
		        $('.knowledge').click(function(){
			        if ($('#container').length > 0){
						if (!$("#knowledge").is(":visible")){
							$("#knowledge, #splitter3").show();
			                $("#textarea").css("width", '50%');
							$.wbs.getClosestLayout("knowledge").resizeAll();
							$.wbs.widgets.loadKnowledge($("#knowledge"));
						} else {
			                $("#knowledge, #splitter3").hide();
			                $("#textarea").css("width", '100%');
						}
					} else {
						if (!$('#textarea').length){
		                    $('body').append("<div id='knowledge' style='float: left; display:none;position:relative'/>");
						}
			            if (!$("#knowledge").is(":visible")){
			                $("#knowledge").show();
			                $("#ticket-wrapper, #knowledge").css({"width": '50%',"float": "left"});
			                $.wbs.widgets.loadKnowledge($("#knowledge"));
			            } else {
			                $("#knowledge").hide();
			                $("#textarea").css("width", '100%');
			            }
					}
		        })
				
		        $('.save, .cancel, .knowledge').button();
		    } else {
		        var params = {id: self.data.id, action_id: action_id};
		        $.post("?m=requests&act=save", params, function (response) {
		            if (response.status == 'OK') {
		                switch (response.data.type) {
		                    case 'REMOVE': 
		                    case 'DELETE': 
		                        if ($('.ui-editor-editablefield').length > 0){
		                           $('.ui-editor-editablefield:first').html('<p class="request-deleted-msg" style="text-align:center">Request has been deleted.</p>');
		                        } else {
		                            $('#ticket').html('Request has been deleted.');
		                        }
		                        if (typeof(gridData) !== 'undefined') gridData.deleteBlock(self.data.id);
		                        break;
		                    default:
		                        if ($('.ui-editor-editablefield').length > 0){
		                           $('.ui-editor-editablefield').load('?m=requests&act=info&id='+self.data.id);
		                        } else {
		                          location.reload();
		                        }    
		                }
		            } else {
		                $("<div>"+response.error+"</div>").dialog(
		                    {    
		                        modal: true,
		                        buttons: {
		                            OK: function() {
		                                $(this).dialog('close');
		                            }
		                        },
		                        zIndex: 100000
		                    }
		                )
		            }
		            
		            if (typeof(gridData) !== 'undefined') {
		                gridData.updateBlock(self.data.id); 
		            }
		        }, "json");         
		    }
		    var $formContent = $('#contact-search-form-content:first');
		    
			$('#tabs-search-browse a').click(function(){
		        var $searchForm = $('<div/>');
		        $.cookie('last-contact-search-tab','#tabs-search-browse');
				$formContent.empty().append($searchForm);
		        $searchForm.wbsSearchlist({
					filter: 'input'
				});
		        $searchForm.find('input:first').focus();
		    });

			$('#tabs-search-contacts a').click(function(){
		        var $searchForm = $('<div/>');
		        $.cookie('last-contact-search-tab','#tabs-search-contacts');
		        $formContent.empty().append($searchForm);
		        $searchForm.wbsSearchlist({
		            filter: 'select',
					filterSource: '?m=contacts&act=folderlist',
					source: '?m=contacts&act=list&byfolder=1'
		        });
		    });
				
			$('#tabs-search-users a').click(function(){
		        var $searchForm = $('<div/>');
		        $formContent.empty().append($searchForm);
		        $.cookie('last-contact-search-tab','#tabs-search-users');
		        $searchForm.wbsSearchlist({
					source: '?m=users&act=list&noid=1&email=1',
					loadOnCreate: true,
					hideInput:true,
					disableScroll:true
		        });
		    });
			$('.tabs-nav li a').click(function(){
				$(this).parent().parent().children().removeClass('tabs-selected');
				$(this).parent().addClass('tabs-selected');
			})
			
			$('#contact-search-form').wbsPopup({
		        parent: $('.contact-search-btn'),
				padding:5,
				absolute: true,
				appendToBody:true,
				width: 400,
				load: function(){
					var tab = $.cookie('last-contact-search-tab');
					if (!tab) tab = '#tabs-search-contacts';
					$(tab+' a').click()
				}
		    })

		    if (typeof($to) != "undefined") $to.wrap('<div class="input-wrapper"></div>');
		    return false;
		});

	},

	showChild: function(obj, cl) {
	    $(obj).parents('table').find('.' + cl).show();
	    $(obj).hide();
	},
	
	bigElementsResize: function(){
		$('.ticket-log-container, .request-content').find('img, table, td').each(function(){
		    var $this = $(this);
		    if ($('.request-content').length && $this.css('width') != '100%' && $this.width() > 0.7 * $('.request-content').width()){
		        $this.css({
		            'width':'100%',
		            'height':'auto'
		        })
		        if ($this.attr('src')) {
		        	$this.wrap("<a href='"+$this.attr('src')+"' target='_blank'></a>");
		        }
		    }
		});
		$('.ticket-log-container, .request-content table[align="left"]').removeAttr('align');
	},
	
	data: false,
	
	create: function(){
		this.header();
		this.info();
		this.bigElementsResize();
		// setTimeout(this.bigElementsResize, 5000);
	}
}
