var Page = function (config) {
	this.config = config;
	
	this.resize = function () {
		var h = $(window).height() - $('#header').height();
		if ($('#footer').is(":visible")) {
			h -= $('#footer').height(); 
		}
		$('#body').height(h);		
	};
	
	this.loading = function () {
		$("#body").html('<div class="loading">[`Loading`]... <span>&nbsp;</span></div>');
	};
	
	this.load = function (url, callback) {
		this.loading();
		$("#body").load(url, function () {
			if (callback) {
				callback();
			}
			$("#body a.history").click(function(){
				var hash = this.href;
				hash = hash.replace(/^.*#/, '');
				$.historyLoad(hash);
				return false;
			});				
		});
		this.resize();
	};
	
	this.setHash = function (hash) {
		location.hash = hash;
	};
	
	this.back = function () {
		if (window.history.length > 1) {
			window.history.back();
		} else {
			this.setHash('');
		}
	};
	
	this.setActiveMenu = function (name) {
		$("#menu li.active").removeClass('active');
		if (name) {
			$("#menu li.menu-" + name).addClass('active');
		}
	};
	
	this.initTabs = function () {
		$("ul.tabs li").click(function () {
			var c = $("ul.tabs li.current").removeClass('current').attr('class');
			$("div.tab-content." + c).hide();
			$("div.tab-content." + $(this).attr('class')).show();
			$(this).addClass('current');
		});
	};
	
	this.settings = function () {
		$("#header td").show();
		$("#header td.back, #footer").hide();
		this.load("?m=settings");
		this.setActiveMenu('settings');
	};
	
	this.requestsAdd = function () {
		$("#footer").hide();
		this.load("?m=requests&act=add");
		this.setActiveMenu();
	};
	
	this.request = function (hash) {
		if (hash[2] == 'add') {
			return this.requestsAdd();
		}		
		$("#header td").show();
		$("#header td.menu, #footer").hide();
		this.load("?m=requests&act=info&id=" + hash[2]);
	};
	
	this.requests = function (hash) {
		$("#header td, #footer").show();
		$("#header td.back").hide();
		if (!requests) {
			requests = new Requests({});
		}		
		this.loading();
		requests.load();
		this.setActiveMenu('tickets');
	};
	
	this.departments = function (hash) {
		$("#header td").show();
		$("#header td.back, #footer").hide();		
		var url = "?m=departments&act=" + hash[2];
		if (hash[3]) {
			url += "&id=" + hash[3];
		}
		this.load(url, this.initTabs);
		this.setActiveMenu('settings');
	}
}

var pageParams = function() {
	var params = {};
	return {
		set: function (obj) {
			$.extend(params, obj);
		},
		get: function (name, defaultValue) {
			if( name == null ) return params;
			return params[name] || defaultValue || null;
		}
	}
}();


var Requests = function (config) {
	this.config = config;
	this.limit = config.limit || 20;
	
	this.init = function () {
		this.pager = new RequestsPager({title: "Requests", count: 0, limit: this.limit, elem: $("#footer"), tickets: this});

	};
	
	this.render = function (data) {
		var html = '<table width="100%" class="tickets"><thead>' + 
		'<tr style="height:25px"><th class="first">[`ID`]</th><th>[`Source`]</th><th>[`Date`]</th><th>[`Client`]</th><th width="100%">[`Subject`]</th><th>[`Status`]</th><th nowrap>[`Assigned user`]</th></tr>' + 
		'</thead><tbody>' + 
		'<tr id="empty-ticket" style="display: none"><td class="first_col"></td><td class="department" nowrap></td><td nowrap></td><td nowrap></td><td></td><td class="state" nowrap></td><td nowrap></td></tr>' + 
		'</tbody></table>';
		$("#body").html(html);
		for (var i = 0; i < data.length; i++) {
			this.renderLine(i, data[i]);
		}
		page.resize();
	};
	
	this.showError = function (error) {
		alert(error);
	};
	

	this.load = function () {
		var _this = this;
		$.get("?m=requests&act=list", {p: this.pager.page}, function (response) {
			if (response.status == 'OK') {
				_this.pager.setCount(response.data.count);
				_this.render(response.data.requests);
			} else if (response.status == 'ERR') {
				_this.showError(response.error);
			}
		}, "json");
	};
	
	
	this.renderLine = function (i, info) {
		if ($('#ticket_' + info[0]).length == 0) {
			var line = $('#empty-ticket').clone();
			line.children().each(function (j) {
				$(this).html(info[j]);
			});
			line.children('.state').html(this.getStateParam(info[5], 'name'));
			line.show().addClass(i % 2 ? 'even' : 'odd').attr('id', 'ticket_' + info[0]);
			line.css(this.getStateParam(info[5], 'properties'));
			line.click(function () {
				$.historyLoad("/request/" + info[0]);
			});
			$('#body table.tickets').append(line);
		}
	};
	
	this.getStateParam = function (state_id, param) {
		var states = pageParams.get('states');
		if (states && states[state_id]) {
			return states[state_id][param];
		} else {
			return "";
		}
	};
	// Constructor
	this.init();
}

var RequestsPager = function (config) {
	this.config = $.extend({title: "Elements"}, config);
	this.requests = config.requests;
	this.page = config.page || 1;
	this.limit = config.limit || 20;
	
	this.render = function () {
		var pager = $('<ul class="footer"><li style="padding-left:10px">' + this.config.title + ': ' + this.count + ' &nbsp;</li><li> Pages: </li></ul>');
		var pages = this.getPages();
		for (var i = 1; i <= pages; i++) {
			pager.append(this.getLink(i));
		}
		this.config.elem.empty().append(pager);
	};
	
	this.setPage = function(page) {
		this.page = page;
	};
	
	this.setLimit = function(limit) {
		this.limit = limit;
	};
	
	this.setCount = function (count) {
		this.count = count;
		this.render();
	};
	
	this.getLink = function (page) {
		if (page == this.page) {
			var l = page;
		} else {
			var l = $('<a href="#">' + page + '</a>');
			var _this = this;
			l.click(function () {
				_this.setPage(page);
				_this.requests.load();
				return false;
			});
		}
		return $('<li></li>').append(l);
	};
	
	this.getPages = function () {
		var n = this.count / this.limit;
		return n < 1 ? 1 : n;
	};
}

var requests;
var page = new Page();

$(document).ready(function () {
	$.historyInit(function (hash) {
	    if (!hash) {
	    	hash = '/requests/';
	    }
	    hash = hash.split('/');
	    if (page[hash[1]]) {
	    	page[hash[1]](hash);
	    } else {
	    	page.requests();
	    }
	});
	$("a.history").click(function(){
		var hash = this.href;
		hash = hash.replace(/^.*#/, '');
		$.historyLoad(hash);
		return false;
	});	
});
$(window).resize(page.resize);




