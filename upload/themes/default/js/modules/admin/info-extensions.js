function load_extensions(){

	$.ajax({
		url: "http://api.webmcr.com/?do=extensions&limit=12",
		dataType: "json",
		type: "GET",
		async: true,
		cache: false,
		contentType: false,
		processData: false,
		beforeSend: function(){ $(".adm-info-modules .thumbnails").html(mcr.loader); },
		success: function(json){
			$(".adm-info-modules .thumbnails").text('');

			if(json.type=='success'){
				var len = json.data.length;

				for(var i = 0; i < len; i++){

					var ar = json.data[i];

					var btn_name = (ar.pay.status) ? lng_ie.buy+' ('+ar.pay.price+' р.)' : lng_ie.download;

					$(".adm-info-modules .thumbnails").append('<li class="span4"><div class="thumbnail"><img src="'
						+ ar.img + '" alt="img"><div class="title">'
						+ ar.title + '</div><div class="read-more"><a href="'
						+ ar.url.full + '" class="btn btn-block" target="_blank">'+lng_ie.readmore+'</a></div><div class="get-link"><a href="'
						+ ar.url.get + '" class="btn btn-block btn-info" target="_blank">'+btn_name+'</a></div></div></li>');
				}
			}else{
				$(".adm-info-modules .thumbnails").text(json.message);
			}
		}
	});
}

$(function(){

	if(!navigator.onLine){ notify(lng.error, lng_ie.e_connection, 1); return; }

	$(".adm-info-modules .thumbnails").html("∞");

	load_extensions();

});
