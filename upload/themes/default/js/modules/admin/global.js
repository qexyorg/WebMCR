$(function(){
	var search_param = mcr.getUrlParam('search');
	var sort_param = mcr.getUrlParam('sort');

	if(search_param!=''){
		$($('.adm-search').attr('data-for')).val(search_param);
	}

	if(sort_param!=''){
		var sort_split = sort_param.split(' ');
		$('.sort-trigger[data-field="'+sort_split[1]+'"]').attr('data-order', sort_split[0]);
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

	$('body').on('click', '.sort-trigger', function(){

		var field = $(this).attr('data-field');
		var order = ($(this).attr('data-order')=='asc') ? 'desc' : 'asc';

		mcr.changeUrlParam({sort: order+'+'+field});

		return false;
	});
});