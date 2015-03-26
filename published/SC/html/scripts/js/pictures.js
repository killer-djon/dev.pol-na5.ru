$(document).ready(function(){
	$("button.selector").click(function(e){
		e.preventDefault();
		var checkboxes = $(".pictures").find('input[type="checkbox"]');
		switch( $(this).attr('name') ){
			case 'unselect_all':
				$(checkboxes).prop('checked', false);
			break;
			
			case 'select_all':
				//$(".pictures").find('input[type="checkbox"]').attr("checked", "checked");
				//if( $(checkboxes).is(":checked") )
				$(checkboxes).prop('checked', true);
			break;
		}
	});
	
	$(".pictures").find("input[type='submit']").click(function(){
		if( $(".pictures").find('input[type="checkbox"]:checked').length > 0 ){
			$(".pictures").children("form").submit();
		}else{
			alert("Вы не отметили ни одной картинки");
			return false;
		}
	});
	
});