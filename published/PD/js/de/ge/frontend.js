$(function(){
	
	if ( $('.highslide-overlay').size() == 0 )
	if ( $('.pages').size() > 0 ) {
		
		if ($('#arrow-left').size() > 0) {
			$(document).bind('keydown', 'ctrl+left' , function(){
				window.location = $('#arrow-left a').attr('href');
			});
		}
		if ($('#arrow-right').size() > 0) {
			
			$(document).bind('keydown', 'ctrl+right' , function(){
				window.location = $('#arrow-right a').attr('href');				
			});
		}
	}
	
	if ( frontend == 3 ) {
		$(document).bind('keydown', 'left' , function(){
			return hs.previous();
		});
		$(document).bind('keydown', 'right' , function(){
			return hs.next();
		});
		$(document).bind('keydown', 'esc' , function(){
			return hs.close();
		});
	}
	
	$('.over-original').hover(
		function(){
			$(this).find('.save-orig').show(); 
		},
		function(){
			$(this).find('.save-orig').hide();
		}
	);
	
	
	
	
	// preupload image
	
	var list = $('img[newsrc]').get();
		
	var create = function(image_obj){
		var img = new Image();
		$(img).load(function(){
			if ( !$.browser.safari )
				$(image_obj).attr('src', $(image_obj).attr('newsrc')+ "&rnd="+Math.ceil(Math.random()*1000) );
			else {
				var newsrc = $(image_obj).attr('newsrc')+ "&rnd="+Math.ceil(Math.random()*1000);
				var $parent = $(image_obj).parent();
				$parent.empty();
				$parent.append('<img src="'+newsrc+'"/>');
			}
				
			if ( list.length > 0 )
				setTimeout(function(){
					create(list.shift()) 
				}, 300);
		});
		img.src = $(image_obj).attr('newsrc');
	};

	if ( list.length > 0 )
		setTimeout(function(){
			create(list.shift()) 
		}, 300);
	
});
