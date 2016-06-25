$(function(){

	$("body").on("click", ".news-attach", function(){
		
		mcr.loading();

		var id = $(this).attr('data-id');

		var formdata = new FormData();
		
		formdata.append('id', id);
		formdata.append('mcr_secure', mcr.meta_data.secure);

		$.ajax({
			url: "index.php?mode=ajax&do=modules|news|news_attach",
			dataType: "json",
			type: 'POST',
			contentType: false,
			processData: false,
			data: formdata,
			error: function(data){
				mcr.logger(data);
				mcr.notify(lng.error, lng_na.e_attach);
			},
			success: function(data){

				if(!data._type){ return mcr.notify(data._title, data._message); }

				location.reload();
			}
		});

		return false;
	});

	$("body").on("click", ".news-delete", function(){
		
		mcr.loading();

		if(!confirm(lng_na.del_confirm)){ return false; }

		var id = $(this).attr('data-id');

		var formdata = new FormData();
		
		formdata.append('id', id);
		formdata.append('mcr_secure', mcr.meta_data.secure);

		$.ajax({
			url: "index.php?mode=ajax&do=modules|news|news_delete",
			dataType: "json",
			type: 'POST',
			contentType: false,
			processData: false,
			data: formdata,
			error: function(data){
				mcr.logger(data);
				mcr.notify(lng.error, lng_na.e_delete);
			},
			success: function(data){

				if(!data._type){ return mcr.notify(data._title, data._message); }

				location.reload();
			}
		});

		return false;
	});
});