$(function(){
	var search_param = mcr.getUrlParam('search');

	if(search_param!=''){
		$($('.adm-search').attr('data-for')).val(search_param);
	}

	$('body').on('click', '.adm-search', function(){

		var elem = $(this).attr('data-for');

		var val = $(elem).val();

		if($.trim(val)==''){ mcr.changeUrlParam({search: false}); return false; }

		mcr.changeUrlParam({search: val, pid: false});

		return false;
	});

	$('.adm-search-input').on('keydown', function(e){
		if(e.which == 13){
			$(".adm-search").trigger("click");
			return false;
		}
	});
});