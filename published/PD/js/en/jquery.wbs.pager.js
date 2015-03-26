/**
 * widget Pager
 */
(function($) {
	$.fn.wbsPager = function (param) {
		
		var limit = param.limit || 0;
		var count = param.count || 0;
		var current = param.current || 1;
		
		if(count == 0) return false;
		
		var pagegoto = function (num) {
			alert('goto page '+num);
		};
		pagegoto = param.pagegoto || pagegoto;
		
		return this.each(function() {
			
			
			if( count <= 1 ) return false;
			
			var $ul = $('<ul class="pager-select"/>')
			
			if ( current != 1 )
			$('<li class="page-arrow">&#8592;</li>').appendTo($ul).click(function(){pagegoto( current - 1 )});

			if ( limit >= parseInt(count) - 2  )
				
				for (var i=1; i<=count; i++) {
					
					if ( i != current ) {						
						$('<li>'+i+'</li>').appendTo($ul).click(function(){pagegoto( this )}.bind(i));						
					}
					else
						$ul.append('<li class="current-page">'+i+'</li>');
					
				}
				
			else if ( limit < count ) {				
				if ( current < parseInt(limit) + 1 ) {
					for (var i=1; i<= parseInt(limit) + 1; i++) {
						if ( i != current )
							$('<li>'+i+'</li>').appendTo($ul).click(function(){pagegoto( this )}.bind(i));
						else
							$ul.append('<li class="current-page">'+i+'</li>');
					}
					
					$ul.append('<li class="pager-space">...</li>');
					$('<li>'+count+'</li>').appendTo($ul).click(function(){pagegoto( this )}.bind(count));
				}
				else if ( current > count - limit ) {
					$('<li>1</li>').appendTo($ul).click(function(){pagegoto( 1)});
					
					
					$ul.append('<li class="pager-space">...</li>');
					for (var i=(count - limit); i<=count; i++) {
						if ( i != current )
							$('<li>'+i+'</li>').appendTo($ul).click(function(){pagegoto( this )}.bind(i));
						else
							$ul.append('<li class="current-page">'+i+'</li>');
					}
				}
				else {
					$('<li>1</li>').appendTo($ul).click(function(){pagegoto( 1 )});
					$ul.append('<li class="pager-space">...</li>');
					for (var i=(current - 1); i<=(current + 1); i++) {
						if ( i != current )
							$('<li>'+i+'</li>').appendTo($ul).click(function(){pagegoto( this )}.bind(i));
						else
							$ul.append('<li class="current-page">'+i+'</li>');
					}
					$ul.append('<li class="pager-space">...</li>');
					$('<li>'+count+'</li>').appendTo($ul).click(function(){pagegoto( this )}.bind(count));
				}
				
				
				
			}
				
			if ( current != count )
			$('<li class="page-arrow">&#8594;</li>').appendTo($ul).click(function(){pagegoto( current + 1 )});
			
			$(this).append($ul);
		});
	};
})(jQuery);