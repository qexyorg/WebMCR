function load_extensions(){

	$.ajax({
		url: "http://api.webmcr.loc/?do=extensions&limit=12",
		beforeSend: function(){ $(".adm-info-modules .thumbnails").html($.mcr.loader); },
		dataType: "json",
		success: function(json){
			$(".adm-info-modules .thumbnails").text('');

			if(json.type=='success'){
				var len = json.data.length;

				for(var i = 0; i < len; i++){

					var ar = json.data[i];

					var btn_name = (ar.pay.status) ? 'Купить ('+ar.pay.price+' р.)' : 'Скачать';

					$(".adm-info-modules .thumbnails").append('<li class="span4"><div class="thumbnail"><img data-src="holder.js/150x100" src="'
						+ ar.img + '" alt="img"><div class="title">'
						+ ar.title + '</div><div class="read-more"><a href="'
						+ ar.url.full + '" class="btn btn-block" target="_blank">Подробнее</a></div><div class="get-link"><a href="'
						+ ar.url.get + '" class="btn btn-block btn-info" target="_blank">'+btn_name+'</a></div></div></li>');
				}
			}else{
				$(".adm-info-modules .thumbnails").text(json.message);
			}
		}
	});
}

$(function(){

	if(!navigator.onLine){ notify("Ошибка!", "Отсутствует интернет-соединение", 1); return; }

	$(".adm-info-modules .thumbnails").html("∞");

	load_extensions();

});