/*
 * WBS UI Splitter
 *
 * Copyright (c) 2010
 *
 * Depends:
 *	jquery.ui.core.js
 *	jquery.ui.popup.js
 *	jquery.ui.widget.js
 */

var dragged = false;
$.widget("ui.wbsSearchlist", {
	options: {
		filter: 'input',
		source: '?m=contacts&act=list',
		filterSource: false,
		loadOnCreate: false,
		disableScroll: false,
		hideInput: false,
		onSelect: function(){return false}
	},
	createItems: function($this, count){
		var self = this, o = this.options, limit, val;
		if (!count) {
			limit = '30'
		} else {
			limit = count + ', 30' 
		}
		
		if ($('input',$this).length) {
			val = $('input:first',$this).val();
			$('input:first',$this).addClass('ui-autocomplete-loading');
		} else {
			val = $this.val();
			$this.addClass('ui-autocomplete-loading');
		}
		if (o.filter != 'input' && !self.content.find('option').length && !self.content.find('.empty-results').length)
			self.content.append('<span class="empty-results">[`Loading`]...</span>')
		if (self.content.find('option').length){
			self.select.append('<span class="empty-results">[`Loading`]...</span>')
		}
		$.wbs.ajaxRequest({
			url: o.source,
			data: {
				'text': val,
				'limit': limit
			},
			callback: function(data){
				self.content.find('.empty-results').remove()
				$this.removeClass('ui-autocomplete-loading');
				$('input:first',$this).removeClass('ui-autocomplete-loading');
				if (!self.content.find('select').length) {
					var $select = $("<select multiple='multiple' style='width:100%;height:100px'/>")
					self.content.append($select);
					self.select = $select;
					if (!o.disableScroll){
						self.select.unbind();
						self.select.scroll(function(){
							var $this = $(this);
							if (!self.appending_rows){
								if ((this.scrollHeight-100) - this.scrollTop < 10) {
									self.appending_rows = true;
									self.createItems(self.input, $this.find('option').length);
								}
							}
						})
					}
				}
				for (key in data){
					var current = data[key],
						$curStr = $("<option/>");
					$curStr.append(current); 
					$curStr.addClass('ui-state-default');
					$curStr.dblclick(function(){
						self.selectButton.click();
					})
					$curStr.click(function(){
						self.selectButton.button('enable');
					})
					var scrollTop = self.select.scrollTop();
					self.select.append($curStr);
					self.select.scrollTop(scrollTop);
				}
				if (!self.content.find('option').length) {
					self.select.remove();
					self.content.append('<span class="empty-results">[`no contacts`]</span>')
				}
				self.appending_rows = false;
			}
		})
		return true;
	},
	
	
	_create: function() {
		var self = this, o = this.options;

		this.content = $("<div class='box ui-widget-content'/>");
		this.btnPane = $("<div class='ui-widget-content'/>");
		
		if (o.filter == 'input'){
			var $searchBtn = $("<button>[`Search`]</button>").button(),
				$input = $("<input class='ui-autocomplete-input' style='width:315px' />");
			$input.val('[`Search by name or email`]');
			$input.focus(function(){this.value=''})
			$input.keypress(function(e){
				if (e.which == 13){
					$searchBtn.click()
				}
			})
			this.input = $('<div />').append($input).append($searchBtn)
			
			$searchBtn.click(function(e){
				self.content.empty();
				self.createItems($input);
			})
			
			if (o.loadOnCreate) {
				$input.val('');
				if (!self.content.find('.empty-results').length)
					self.content.append('<span class="empty-results">[`Loading`]...</span>')
				self.createItems($input);
			}
			if (o.hideInput) {
				this.input.hide()
			}
		} else {
			this.input = $("<select class='input-p100 ui-autocomplete-input'></select>");
			this.input.append("<option value='0' selected='selected'>[`All folders`]</option>");
			if (o.filterSource) {
				if (!self.content.find('.empty-results').length)
					self.content.append('<span class="empty-results">[`Loading`]...</span>')
				$.wbs.ajaxRequest({
					url: o.filterSource,
					callback: function(data){
						self.content.find('.empty-results').remove();
						self.input.removeClass('ui-autocomplete-loading');
						for (key in data){
							var current = data[key],
								$curStr = $("<option/>"), curName = '';
							current = current.split('|');
							for (var i=0;i<parseInt(current[0]);i++){
								curName += '&nbsp;&nbsp;';
							}
							curName += current[1];
							$curStr.append(curName); 
							$curStr.val(key);
							self.input.append($curStr);
						}
						self.createItems(self.input);
					}
				})
			}
			this.input.change(function(val){
				self.content.empty();
				self.createItems(self.input);
			})
		}

		$selectButton = $("<button class='select-btn'>[`Select`]</button>");
		this.selectButton = $selectButton;
		this.btnPane.append($selectButton).append('<span class="use-ctrl-msg">[`Use Ctrl+click or Shift+click to select multiple contacts`]</span>');
		
		this.element.addClass("ui-widget");
		
		$selectButton.button();
		if (!$.browser.msie) $selectButton.button('disable');
		var $form = $('#contact-search-form.ui-popup');
		this.element.prepend($('<a href="#" style="position:absolute; top:5px; right: 5px;">[`Close`]</a>').click(function(){
			$form.wbsPopup('close');
		}))
		
		$selectButton.click(function(){
			var $current = $('option:selected', self.content),
				values = [];
			$current.each(function(i){
				values[i] = $.wbs.trim($(this).text());
			})
			values = $.wbs.implode(', ', values);
			var $input = $form.wbsPopup('option','selectedParent').prev();
			if ($input.get(0).tagName.toLowerCase() != 'input') {
				$input = $('input', $form.wbsPopup('option','selectedParent').prev());
			}
			if ($input.val() != ''){
				$input.val($input.val() + ', ' + values);
			} else {
				$input.val(values);
			}
			if ($input.val().length > 55){
				$input.css('width',$('#action-container').width() - 500)
			}
			$form.wbsPopup('close');
		})
		self.appending_rows = false;
		
		//this.content.append(this.input);
		
		this.element.append(this.input);

		this.input.wrap("<div class='input-wrapper'>");

		this.element.append(this.content);
		this.element.append(this.btnPane);
	}
});
