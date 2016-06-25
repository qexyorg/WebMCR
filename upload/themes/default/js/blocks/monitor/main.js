function init_monitoring(){

	if($('.monitor-id').length<=0){ return; }

	mcr.loading();

	var formdata = new FormData();
		
	formdata.append('mcr_secure', mcr.meta_data.secure);

	$.ajax({
		url: "index.php?mode=ajax&do=monitoring",
		dataType: "json",
		type: 'POST',
		contentType: false,
		processData: false,
		data: formdata,
		error: function(data){
			mcr.logger(data);
			mcr.notify(lng.error, lng.e_monitor);
		},

		success: function(data){

			if(!data._type){ return mcr.notify(data._title, data._message); }

			if(data._data.length<=0){ return mcr.loading(false); }

			$.each(data._data, function(key, ar){
				$('.monitor-id#'+ar.id+' .progress-bar').css('width', ar.progress+'%').removeClass('progress-bar-info').removeClass('progress-bar-danger');

				if(ar.status==1){
					$('.monitor-id#'+ar.id+' .progress-bar').addClass('progress-bar-info');
					$('.monitor-id#'+ar.id+' .progress-bar > span').text(ar.online+' / '+ar.slots);
				}else{
					$('.monitor-id#'+ar.id+' .progress-bar').addClass('progress-bar-danger');
					$('.monitor-id#'+ar.id+' .progress-bar > span').text(lng.offline);
				}
			});
				
			mcr.loading(false);
		}
	});
}

$(function(){
	// Загрузка мониторинга
	init_monitoring();
});