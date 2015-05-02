function is_json(str){
	try{
		JSON.parse(str);
	}catch(e){
		return false;
	}
	return true;
}

function getParameterByName(name) {
	name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
	var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
		results = regex.exec(location.search);
	return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

var base_url = $("base").attr("href");
var style_url = $('meta[name="style_url"]').attr("content");

function notify_closer(){

	if($('.close-alert').length >= 1){
		$('body .close-alert').parent().fadeOut("normal", function(){
			$(this).remove();
		});
	}
}

function notify(title, message, type){
	type = parseInt(type);
	switch(type){
		case 2: type = 'alert-error'; break;
		case 3: type = 'alert-success'; break;
		case 4: type = 'alert-info'; break;

		default: type = ''; break;
	}

	$('html, body').animate({scrollTop:0}, 'normal');

	$(".main-content").prepend('<div class="alert '+type+' ajx"><a href="#" class="close-alert">&times;</a><b>'+title+'</b> '+message+'</div>');

	setTimeout("notify_closer()", 2500);

	return false;
}

function send_ret_req(method, url, params){
	var req = null;
	try { req = new ActiveXObject("Msxml2.XMLHTTP"); } catch (e) {
		try { req = new ActiveXObject("Microsoft.XMLHTTP"); } catch (e) {
			try { req = new XMLHttpRequest(); } catch(e) {}
		}
	}
	if (req == null) throw new Error('XMLHttpRequest not supported');

	req.open(method, url, false);
	req.send(params);

	return req.responseText;
}

$.mcr_session = {
	url: base_url+"?mode=ajax&do=session",

	get: function(name){
		var params = "&name="+name.toString();
		return send_ret_req("GET", this.url+"&op=get"+params, null);
	},

	set: function(name, value){
		var params = "&name="+name.toString()+"&value="+value.toString();
		return send_ret_req("GET", this.url+"&op=set"+params, null);
	},

	remove: function(name){
		var params = "&name="+name.toString();
		return send_ret_req("GET", this.url+"&op=remove"+params, null);
	},
};

$.mcr = {
	is_json: function(str){
		try{
			JSON.parse(str);
		}catch(e){
			return false;
		}
		return true;
	},

	loader: '<img src="'+style_url+'img/loading.gif" alt="loading..." />',
};

function init_monitoring(){

	//if(!navigator.onLine){ notify("Ошибка!", "Мониторинг недоступен. Отсутствует интернет-соединение", 1); return false; }

	$.ajax({
		url: base_url+"?mode=ajax",
		dataType: "json",
		type: 'GET',
		async: true,
		data: "do=monitor",
		error: function(data){
			notify("Ошибка!", "Мониторинг временно недоступен.", 1);
		},
		success: function(data){
			if(!data._status){ $('.js-monitor').text(data._message); return; }

			$('.js-monitor').empty();

			if(data._data.length==0){ $('.js-monitor').text('Мониторинг недоступен'); }

			$.each(data._data, function(i, val){
				$('.js-monitor').append(val.form);

				var element = $('.js-monitor .monitor-id#'+val.id+' .bar');
				element.css({'left': '-'+val.progress+'%', 'width': val.progress+'%'});

				setTimeout(function(){
					element.animate({left:'0%'}, 2000, 'easeInOutQuart');
				}, 500*i);
			});
		}
	});
}

$(function(){

	$('body').tooltip({selector: '[rel="tooltip"]'});

	$('.spl-body.closed').hide();

	// +++ Monitoring loading
	init_monitoring();
	// --- Monitoring loading
	
	$('.spl-btn').on("click", function(){
		var element = $(this).attr("data-click");
		$(".spl-body#"+element).toggleClass("opened").toggleClass("closed").slideToggle("fast");
		$('.spl-btn[data-click="'+element+'"]').toggleClass("opened").toggleClass("closed");

		if($(this).hasClass("session")){
			var session = $.mcr_session.get(element);

			if(session!='true'){
				$.mcr_session.set(element, true);
			}else if(session=='true'){
				$.mcr_session.set(element, false);
			}
		}

		return false;
	});


	
	$('.check-all').on("click", function(){
		var element = $(this).attr("data-for");

		var obj = $("."+element);

		var length = obj.length;

		var inc;

		for(inc=0; inc < length; inc++){

			if($(this)[0].checked==true){
				obj[inc].checked=true;
			}else{
				obj[inc].checked=false;
			}
		}

	});

	$('.remove').click(function(){

		if($(this).attr("data-checkbox")!='false'){
			var element = $(this).attr("data-for");
			var length = $('.'+element+':checked').length;

			if(length<=0){
				notify("Ошибка!", "Не выбрано ни одного пункта для удаления", 1);
				return false;
			}

		}
		
		var text = $(this).attr("data-text");
		if(!confirm(text)){ return false; }

		return true;
	});

	$('body').on("click", '.close-alert', function(){
		$(this).parent().fadeOut("normal", function(){
			$(this).remove();
		});
		return false;
	})

	$(".mcr-debug .action").on("click", function(){
		$(".mcr-debug").toggleClass("open");
		return false;
	});

	$(".bb-panel .bb").on("click", function(){

		var panel_id = $(this).parent().closest(".bb-panel").attr("id");

		var pid = ".bb-panel#"+panel_id;

		var panel_obj = $('textarea[data-for="'+panel_id+'"]')[0];

		var leftcode = $(this).attr("data-left");
		var rightcode = ($(this).attr("data-right")==undefined) ? leftcode : $(this).attr("data-right");

		if(!$(this).hasClass("woborder")){
			leftcode = '['+leftcode+']';
			rightcode = (rightcode=='') ? '' : '[/'+rightcode+']';
		}else{
			rightcode = (rightcode=='') ? '' : rightcode;
		}

		if(document.selection){

			var s = document.selection.createRange();
			if(s.text){
				s.text = leftcode + s.text + rightcode;
			}

		}else{ // Opera, FireFox, Chrome

			var start = (panel_obj.selectionStart==undefined) ? 0 : panel_obj.selectionStart;

			var end = (panel_obj.selectionEnd==undefined) ? 0 : panel_obj.selectionEnd;

			s = panel_obj.value.substr(start,end-start);

			panel_obj.value = panel_obj.value.substr(0, start) + leftcode + s + rightcode + panel_obj.value.substr(end);
		}

		return false;
	});

	$("#search-selector a").click(function(){

		var search_val = $("#search-hidden").val();

		$("#search-selector a#"+search_val).parent().removeClass("active");

		var id = this.id;

		$("#search-hidden").val(id);

		$(this).parent().addClass("active");

		return false;

	});

	$("#close-notify").click(function(){
		$(".block-notify").fadeOut("normal", function(){
			$(this).remove();
		});
		return false;
	});

	$(".edit").click(function(){
		
		var element = $(this).attr("data-for");
		var length = $('.'+element+':checked').length;
		var link = $(this).attr("data-link");

		if(length<=0){
			notify("Ошибка!", "Не выбрано ни одного пункта для удаления", 1);
			return false;
		}else if(length>1){
			notify("Ошибка!", "Для редактирования необходимо выбрать только один пункт из списка", 1);
			return false;
		}

		var id = $('.'+element+':checked').val();

		window.location.href = link+id;
		
		return false;
	});
});