$(document).ready(function(){
	var resizeTimer = null;
	$(window).resize(function(){
		if (resizeTimer) clearTimeout(resizeTimer);
		resizeTimer = setTimeout(function(){
		$(".ui-splitter").trigger('resize');
		}, 100);
	});
	$('#switcher').themeswitcher();
	
	$("#grid tbody").click(function(event) {
		$(oTable.fnSettings().aoData).each(function (){
			$(this.nTr).removeClass('ui-state-highlight');
		});
		$(event.target.parentNode).addClass('ui-state-highlight');
	});
	
	oTable = $('#grid').dataTable({
		"bJQueryUI": true,
		"sPaginationType": "full_numbers",
		"iDisplayLength": 17
	});
	
	$("#container").trigger('resize');
	$("#right-container").trigger('resize');

});