function load_online(){

	if(location.pathname == mcr.meta_data.base_url+'install/'){ return; }

	mcr.loading();

	var formdata = new FormData();
	
	formdata.append('mcr_secure', mcr.meta_data.secure);

	$.ajax({
		url: "index.php?mode=ajax&do=blocks|online|main",
		dataType: "json",
		type: 'POST',
		contentType: false,
		processData: false,
		data: formdata,
		timeout: 2000,
		error: function(data){
			mcr.logger(data);
			mcr.notify(lng.error, 'error');
		},

		success: function(data){

			$('.block-online #onl_list').text('');

			if(!data._type){ return mcr.notify(data._title, data._message); }

			if(data._data.list.length>0 && typeof data._data.list == 'object'){
				$('.block-online #onl_list').append(data._data.list.join(', '));
			}else{
				$('.block-online #onl_list').append(data._data.list);
			}

			$('.block-online #onl_count #onl_all').text(data._data.all);
			$('.block-online #onl_count #onl_users').text(data._data.users);
			$('.block-online #onl_count #onl_guests').text(data._data.guests);

			mcr.loading(false);
		}
		
	});
}

function update_online(){

	if(location.pathname == mcr.meta_data.base_url+'install/'){ return; }
	
	mcr.loading();

	var formdata = new FormData();
	
	formdata.append('mcr_secure', mcr.meta_data.secure);

	$.ajax({
		url: "index.php?mode=ajax&do=blocks|online|update_online",
		dataType: "json",
		type: 'POST',
		contentType: false,
		processData: false,
		data: formdata,
		timeout: 2000,
		error: function(data){
			mcr.logger(data);
			mcr.notify(lng.error, lng.error);
		},

		success: function(){ mcr.loading(false); }
	});
}

$(function(){

	setTimeout(update_online, 300);

	setTimeout(load_online, 700);
});