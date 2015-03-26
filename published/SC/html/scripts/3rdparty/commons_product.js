$( document ).ready(function() {
	var _defaultOptionKvm = 63;
	var _option = $.trim($(".block-option").find(".option-value[rel='"+_defaultOptionKvm+"']").text()); // кв,м, в упаковке
	var _kvm = parseFloat( _option.replace(",", ".") );
	
	var _prkvm = $(".block-option").find(".option-value input[name='count_per']");	
	var optText = $('.block-option').find(".option-value").text();
	
	// Math.round(parseFloat(_prkvm.val()) / _kvm )
	
	
	$(".block-option").find(".counts_metr").text( (_prkvm.val() / _kvm).toFixed() + " упаковок." );
	$(".block-option").find(".counts_metr_total").text( (((_prkvm.val() / _kvm).toFixed()) * _kvm).toFixed(4) + " кв.м." );
	
	
	$('.product-info input[name="product_qty"]').val( Math.round( $(_prkvm).val() ) );	
	//var priceDefault = parseFloat($(_prkvm).val()) * parseFloat( $(".block-option").find(".option-value .totalPrice").text() );
	var priceDefault = (( Math.round(parseFloat(_prkvm.val()) / _kvm ) ) * _kvm).toFixed(2) * parseFloat( $(".block-option").find(".option-value .totalPrice").text() );
	$(".block-option").find('.counts_price .totalPrice').text( priceDefault.toFixed(2) );
	$('input[class="product_price"]').val( priceDefault.toFixed(2) );
	
	$(_prkvm).keyup(function(event){
		if( event.keyCode !== undefined ){
			if( (event.keyCode >= 96 && event.keyCode <= 105) 
				|| (event.keyCode >= 48 && event.keyCode <= 57) 
				|| (event.keyCode == 110)
				|| (event.keyCode == 8) ){
				if( parseFloat($(this).val()) !== undefined ){
					var _sum =  ($(this).val() / _kvm); // кол-во упаковок
					
					if( _sum !== undefined && _sum > 0 ){
						$(".block-option").find(".counts_metr").text( _sum.toFixed()  + " упаковок.");
						$(".block-option").find(".counts_metr_total").text( (_sum.toFixed() *_kvm).toFixed(4) + " кв.м." );						
						$('input[name="product_qty"]').val($(this).val());
						var totalPrice = (_sum*_kvm).toFixed(4) * parseFloat($('input[class="_price"]').val());
						$(".block-option").find(".counts_price .totalPrice").text( totalPrice.toFixed(2) );
						
						$('input[class="product_price"]').val( totalPrice.toFixed(2) );
					}
				}
			}
		}
	});
	
	$(".cart_product_quantity").keyup(function(event){
		if( event.keyCode !== undefined ){
			if( (event.keyCode >= 96 && event.keyCode <= 105) 
				|| (event.keyCode >= 48 && event.keyCode <= 57) 
				|| (event.keyCode == 110)
				|| (event.keyCode == 8) ){
					var _quantity = $(this).val();
					var _price = parseFloat($(this).prev('input[name="cart_product_price"]').val());
					var _total = parseFloat(_quantity*_price);
					$(this).closest(".count-cart").next(".total-cart").find(".price-counters > .cost").text( number_format(_total, 2, ".", " ") );
					
					var _totalsum = 0;
					$(".list-products").find(".total-cart .price .price-counters > .cost").each(function(index, item){
						var _sums = $(item).text().replace(" ", "");
						_totalsum = _totalsum + parseFloat(_sums);
						$(".total-amounts").find(".total-price > span:first").text( number_format(_totalsum, 2, ".", " ") );
					});
				}
		}
	});

	
	$("form#choose").submit(function(){
		var $data = $(this).serializeArray();
		
		$.ajax({
			dataType: "json",
			type: "POST",
			data: {
				action: "filterProduct",
				params: $data
			},
			beforeSend: function(){
				$("body").find('.result-products').html('');
			},
			success : function(data){
				
				if( data && data !== undefined ){
					if( data.success === true ){
						$("body").find('.result-products').fadeIn('slow').html(data.form);
					}
				}
				
			},
			error: function(err){
				console.log(err);
			}
		});
		
		return false;
	});
	
	$("span.info-question").click(function(){
		if( $(this).next(".option-description").is(":visible") ){
			$(this).next(".option-description").hide();
			$(this).removeClass('active');
		}else{
			$(this).next(".option-description").show();
			$(this).addClass('active');
		}
	});
		
	var win_href = window.location;
	if( win_href.search == "" && win_href.pathname == '/chooser/' ){
		var _first = $(".chooser-block").find("ul li:first a").attr("href");
		win_href.href = _first;
	}
	$(".slideshow").cycle();
	
	$(".close-cart").click(function(){
		sswgt_CartManager.hide(true);
		
	});
	
	$(".call-me").click(function(){
		var this_rel = $(this).attr("rel");
		var user_name = $("#"+this_rel).find("input[name='user_name']"),
			user_phone = $("#"+this_rel).find('input[name="user_phone"]'),
			user_email = $("#"+this_rel).find('input[name="user_email"]'),
			allFields = $( [] ).add( user_name ).add( user_phone ).add( user_email ),
			tips = $( ".validateTips" );
		
		function updateTips( t ) {
			tips
			.text( t )
			.addClass( "ui-state-highlight" );
			setTimeout(function() {
				tips.removeClass( "ui-state-highlight", 1500 );
			}, 500 );
		}
		
		function checkLength( o, n, min ) {
			if ( o.val().length < min ) {
				o.addClass( "ui-state-error" );
				updateTips( "Длина поля: \"" + n + "\" должна быть больше" +	min + "." );
				return false;
			} else {
				return true;
			}
		}
		
		function checkRegexp( o, regexp, n ) {
			if ( !( regexp.test( o.val() ) ) ) {
				o.addClass( "ui-state-error" );
				updateTips( n );
				return false;
			} else {
				return true;
			}
		}
		
		
		$( "#"+this_rel ).dialog({
			modal: true,
			width: parseInt( $("#"+this_rel).attr("data-width") ),
			buttons: {
				"Отправить запрос" : function(){
					var bValid = true;
					 allFields.removeClass( "ui-state-error" );
					 
					 bValid = bValid && checkLength( user_name, user_name.attr("title"), 2 );
					 if( user_email.val() !== undefined ){
						 bValid = bValid && checkRegexp( user_email, /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i, "пример:. ui@jquery.com" );
					 }

					 bValid = bValid && checkRegexp(user_phone, /^([0-9]{3} [0-9]{7})+$/i, "Неверный формат номера, пример: 925 1111111");
					 
					 if( bValid ){
					 	var _dialog = $(this),
							form = _dialog.children('form[name="form-'+this_rel.trim()+'"]')
						$.ajax({
							url: ORIG_URL + 'phones/',
							dataType: 'json',
							type: 'POST',
							data: $(form).serializeArray(),
							success: function(data){
								if( data && data !== undefined ){
									if( data.success === true ){
										alert(data.msg);										
										$(_dialog).dialog("close");
									}
								}
							},
							error: function(err){
								console.log( err );
							}
						});
					 }
				},
				"Закрыть" : function(){
					$(this).dialog("close");
				}
			}
		});
		return false;
	});
});
