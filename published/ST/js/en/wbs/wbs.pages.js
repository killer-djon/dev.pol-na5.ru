$.wbs.pages = {
	abstractPage: function(containerId, panel, layout, resizeFunction){
		$panel = $("#"+panel);
		$panel.layout('destroy');
		$panel.empty();
		$panel.layout({
			layoutMarkupId: layout,
			onResize: resizeFunction,
			callback: function(){
    			$('#title h1').html( $('.ui-tree .ui-state-highlight:first').text());
    			$('#leftPanel').show();
    			if ($.cookie('left-panel') == 'hidden') {
    				$.wbs.widgets.toggleLeftPanel();
    			}
			}
		});
	},
	mainPage: function(filter,plugin,callback){
		var container = $.wbs.getLayout('container');
		
		delete gridData.requestData.lastreqlogid;
		delete gridData.requestData.lastreqid;
		delete gridData.requestData.lastlogid;
		delete gridData.requestData.lastid;
		delete gridData.requestData.limit;
		delete gridData.requestData.offset;
		var hash = parent ? parent.window.location.hash : location.hash,
			re = /\/id\//, hashHasId = re.exec(hash);
		if ($('#mainPage', $('#contentPanel')).length==0){
			$.wbs.set('grid-filter',filter);
			this.abstractPage('container', 'contentPanel', 'mainPage', function(){
				$('#grid').wbsGrid('resize');
				$('#textarea').wbsEditor('resize');
			});
        	if ($.cookie('single-panel-view')=='true'){
				$.wbs.widgets.toggleBottomPanel();
				if (hashHasId && $('#topPanel').is(':visible')){
					$.wbs.widgets.toggleBottomPanel();
					$.wbs.widgets.toggleTopPanel();
					$.wbs.set('full-view',true);
		        }
        	}
		} else {
			var grid = $('#grid');
			var old_state_filter = $.wbs.get('grid-filter');
			$.wbs.set('grid-filter',filter);
			$('.act-items').remove();
			$('#textarea').wbsEditor('setContent','');
			if (filter != old_state_filter){
				if ($('#bottomPanel').is(':visible') && !$('#gridPanel').is(':visible')){
			        $.wbs.widgets.toggleBottomPanel();
				} else {
					if (!$('#gridPanel').is(':visible')){
						$.wbs.widgets.toggleTopPanel();
					}
				}
				if (!hashHasId){
					$.wbs.set('current-request-id', 0);
				}
			    $.wbs.set('last_req_log_id', '99999999');
	    		$('#search-params').remove();
				grid.wbsGrid('setGridData');
				$('#textarea').wbsEditor('empty');
			} else {
				if (!$('#bottomPanel').is(':visible')){
					if (hashHasId) {
				        $.wbs.widgets.toggleBottomPanel();
						$.wbs.widgets.toggleTopPanel();
					}
				} else {
					if ($.cookie('single-panel-view')=='true'){
						if ($('#bottomPanel').is(':visible')) $.wbs.widgets.toggleBottomPanel();
					}
				}
				//if ($.wbs.get('loaded-request-id') != $.wbs.get('current-request-id')){
					grid.wbsGrid('selectBlock',$.wbs.get('current-request-id'));
				    $.wbs.set('last_req_log_id', '99999999');
				//}
			}
		}
	},
	loadPage: function(url, callback){
		if ($('#pageContainer', $('#contentPanel')).length==0) 
			this.abstractPage('container', 'contentPanel', 'pageContainer', function(){
				$('#grid').wbsGrid('resize');
				$('#textarea').wbsEditor('resize');
			});
		$('#checkEmail .refresh-link').hide();
		$('#pageContainer').load(url, function(){
			$(':button, :submit, input[type=button]').button();
			if (url=="?m=classes") {
				$.wbs.widgets.classesContainer();
			}
			$.wbs.widgets.toggleLoading('body','hide');
			if (typeof(callback) == 'function') {
				callback();
			}
		})
	}
}
