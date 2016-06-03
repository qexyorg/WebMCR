$(function(){

	$('body').on('click', '.panel-modules #disable, .panel-modules #enable', function(){

		var status = $(this).attr('id');
		var list = $('input.'+$(this).attr('data-for')+':checked');
		var items = '';

		var new_status = (status=='enable') ? 'icon_status_on' : 'icon_status_off';

		if(list.length<=0){ return mcr.notify(lng.error, lng_mod.not_selected); }

		list.each(function(){
			items += $(this).val()+',';
		});

		items = items.substr(0, items.length-1);

		var formData = new FormData();

		formData.append('mcr_secure', mcr.meta_data.secure);
		formData.append('act', status);
		formData.append('ids', items);

		$.ajax({
			url: "index.php?mode=ajax&do=admin_modules_status",
			dataType: "json",
			type: "POST",
			async: true,
			cache: false,
			contentType: false,
			processData: false,
			data: formData,
			error: function(data){
				mcr.logger(data);
				mcr.notify(lng.error, lng_mod.e_change_status);
			},
			success: function(data){

				if(!data._type){ return mcr.notify(data._title, data._message); }

				list.each(function(){
					$(this).closest('tr').find('td.status i').attr('class', new_status);
				});
			}
		});

		return false;
	});
});