$(function(){
	if ($('#arrow-left a').attr('href') == ref  ) {
		var $img = $('.image-list-float img:first');
		$img.parent().addClass('select-thumb');
		
		var next_image = $img.parent().next().find('img').attr('src_orig');
		new Image().src = next_image;
		
		var src  = $img.attr('src_orig');
		var src_h  = $img.attr('src_hash');
		$('.hash_url').attr('href', src_h);
//		src = src.replace(/\.[0-9]+/, "." + document.foto_size);
		
		$('.left-block .photo').append('<img src="'+src+'" />');
		
		$('#desc').html( $img.parent().find('.desc_photo').html() );
	}
	else if ($('#arrow-right a').attr('href') == ref) {
		var $img = $('.image-list-float img:last');
		$img.parent().addClass('select-thumb');
		
		var next_image = $img.parent().next().find('img').attr('src_orig');
		new Image().src = next_image;
		
		var src  = $img.attr('src_orig');
		var src_h  = $img.attr('src_hash');
		$('.hash_url').attr('href', src_h);
//		src = src.replace(/\.[0-9]+/, "." + document.foto_size);
		
		$('.left-block .photo').append('<img src="'+src+'" />');
		
		$('#desc').html( $img.parent().find('.desc_photo').html() );
	}
	else {
		var $img = $('.image-list-float img:first');
		$img.parent().addClass('select-thumb');
		
		var next_image = $img.parent().next().find('img').attr('src_orig');
		new Image().src = next_image;
		
		var src  = $img.attr('src_orig');
		var src_h  = $img.attr('src_hash');
		$('.hash_url').attr('href', src_h);
//		src = src.replace(/\.[0-9]+/, "." + document.foto_size);
		
		$('.left-block .photo').append('<img class="image_big" src="'+src+'" />');
		
		$('#desc').html( $img.parent().find('.desc_photo').html() );
	}
	
	$('.right-block').disableTextSelect();
	
	$('.image-list-float').click(function(){
		if ( $(this).hasClass('select-thumb') ) return false;
		
		$('.left-block').find('.photo').stop(true).animate({opacity: 0}, 200, "linear", function(){
			var src = $(this).find('img').attr('src_orig');
			var src_h  = $(this).find('img').attr('src_hash');
			$('.hash_url').attr('href', src_h);
			
			$('.left-block').find('img:not(.window)').load(function(){
				$('.left-block').find('.photo').animate({opacity: 1}, 200);
			}).attr('src', src);
			
			$('#desc').html( $(this).find('.desc_photo').html() );
			
		}.bind(this) );

		$('.image-list-float').removeClass('select-thumb');
		$(this).addClass('select-thumb');
	});
	document.click_l = function () {
		var $obj = $('.select-thumb');
		if ( $obj.prev().size() > 0 ) {
			var url_image = $obj.removeClass('select-thumb').prev().click().addClass('select-thumb').prev().find('img').attr('src_orig');
			new Image().src = url_image;
		}
		else {
			if ($('#arrow-left').size() > 0) {
				window.location = $('#arrow-left a').attr('href');
			}			
		}
	};

	$(document).bind('keydown', 'left', document.click_l);
	$('.left').click(document.click_l);
	
	
	document.click_ = function(){
		var $obj = $('.select-thumb');
		if ( $obj.next().size() > 0 ) {				
			var url_image = $obj.removeClass('select-thumb').next().click().addClass('select-thumb').next().find('img').attr('src_orig');
			new Image().src = url_image;
		}
		else {
			if ($('#arrow-right').size() > 0) {
				window.location = $('#arrow-right a').attr('href');				
			}
		}
		 
	};
	$(document).bind('keydown', 'right', document.click_);
	$('.photo img').click(document.click_);
	$('.right').click(document.click_);
	
	$('.photo img').hover(
		function(){
			$('.pager .right').addClass('pager_hover');
		},
		function(){
			$('.pager .right').removeClass('pager_hover');
		}
	);
});
