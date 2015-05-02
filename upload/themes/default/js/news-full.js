$(function(){
	
	$("body").on("click", "#add_comment", function(){

		var message = $('textarea[name="message"]')[0];
		var secure = $('.comment-form-content form input[name="mcr_secure"]').val();
		var nid = parseInt(getParameterByName('id'));

		$.ajax({
			url: base_url+"?mode=news&ajax=true",
			beforeSend: function(){
				$("#add_comment").after('<img class="ajx-l" src="'+style_url+'img/loading.gif" alt="loading..." />');
			},
			dataType: "html",
			type: 'POST',
			data: "id="+nid+"&act=add_comment&message="+message.value+"&mcr_secure="+secure,
			success: function(data){

				$("#add_comment").next(".ajx-l").remove();

				if(!is_json(data)){
					notify("Ошибка", "Произошла непредвиденная ошибка. Попробуйте повторить попытку.", 1);
					return false;
				}

				var jsondata	= JSON.parse(data);

				var status = jsondata._status;
				var content = jsondata._content;

				if(status=='success'){
					if($(".comment-id").hasClass("none")){ $(".comment-id.none").remove(); }

					$(".comment-list-content").hide().prepend(content).fadeIn(400, function(){
						message.value = '';
						var com_count = parseInt($("#comment-count").text())+1;
						$("#comment-count").text(com_count);

						notify("Поздравляем!", "Комментарий успешно добавлен.", 3);
					});
					
				}else{
					notify("", content, 2);
				}
			}
		});

		return false;
	});

	$("body").on("click", ".del_comment", function(){

		var secure = $('.comment-form-content form input[name="mcr_secure"]').val();
		var nid = parseInt(getParameterByName('id'));
		var id = parseInt($(this).attr("data-id"));

		$.ajax({
			url: base_url+"?mode=news&ajax=true",
			beforeSend: function(){
				$('.del_comment[data-id="'+id+'"]').after('<img class="ajx-l" src="'+style_url+'img/loading.gif" alt="loading..." />');
			},
			dataType: "html",
			type: 'POST',
			data: "act=del_comment&id="+id+"&nid="+nid+"&mcr_secure="+secure,
			success: function(data){

				$('.del_comment[data-id="'+id+'"]').next(".ajx-l").remove();

				if(!is_json(data)){
					notify("Ошибка!", "Произошла непредвиденная ошибка. Попробуйте повторить попытку.", 1);
					return false;
				}

				var jsondata	= JSON.parse(data);

				var status = jsondata._status;
				var content = jsondata._content;

				if(status=='success'){

					$(".comment-id#"+id).fadeOut(400, function(){

						$(".comment-id#"+id).remove();

						var com_count = parseInt($("#comment-count").text())-1;
						$("#comment-count").text(com_count);

						notify("Поздравляем!", content, 3);
					});
					
				}else{
					notify("", content, 2);
				}
			}
		});

		return false;
	});

	$("body").on("click", ".get_comment", function(){

		var secure = $('.comment-form-content form input[name="mcr_secure"]').val();
		var nid = parseInt(getParameterByName('id'));
		var id = parseInt($(this).attr("data-id"));

		$.ajax({
			url: base_url+"?mode=news&ajax=true",
			beforeSend: function(){
				$('.get_comment[data-id="'+id+'"]').after('<img class="ajx-l" src="'+style_url+'img/loading.gif" alt="loading..." />');
			},
			dataType: "html",
			type: 'POST',
			data: "act=get_comment&id="+id+"&nid="+nid+"&mcr_secure="+secure,
			success: function(data){

				$('.get_comment[data-id="'+id+'"]').next(".ajx-l").remove();

				if(!is_json(data)){
					notify("Ошибка!", "Произошла непредвиденная ошибка. Попробуйте повторить попытку.", 1);
					return false;
				}

				var jsondata	= JSON.parse(data);

				var status = jsondata._status;
				var content = jsondata._content;

				if(status=='success'){

					$('textarea[name="message"]')[0].value += '[quote]'+content+'[/quote]';
					
				}else{
					notify("Ошибка!", content, 1);
				}
			}
		});

		return false;

	});

	$("body").on("click", ".edt_comment", function(){

		var secure = $('.comment-form-content form input[name="mcr_secure"]').val();
		var nid = parseInt(getParameterByName('id'));
		var id = parseInt($(this).attr("data-id"));

		$.ajax({
			url: base_url+"?mode=news&ajax=true",
			beforeSend: function(){
				$('.edt_comment[data-id="'+id+'"]').after('<img class="ajx-l" src="'+style_url+'img/loading.gif" alt="loading..." />');
			},
			dataType: "html",
			type: 'POST',
			data: "act=get_comment&id="+id+"&nid="+nid+"&mcr_secure="+secure,
			success: function(data){

				$('.edt_comment[data-id="'+id+'"]').next(".ajx-l").remove();

				if(!is_json(data)){
					notify("Ошибка!", "Произошла непредвиденная ошибка. Попробуйте повторить попытку.", 1);
					return false;
				}

				var jsondata	= JSON.parse(data);

				var status = jsondata._status;
				var content = jsondata._content;

				if(status=='success'){

					$(".comment-id#"+id+" .comment-id-content").html('<textarea class="edit-from" id="edit-from-'+id+'">'+content+'</textarea><a href="#" class="btn btn-primary edt-save" id="'+id+'">Сохранить</a>');
					
				}else{
					notify("Ошибка!", content, 1);
				}
			}
		});

		return false;

	});

	$("body").on("click", ".edt-save", function(){

		var id = $(this).attr("id");

		var message = $('#edit-from-'+id)[0];
		var secure = $('.comment-form-content form input[name="mcr_secure"]').val();
		var nid = parseInt(getParameterByName('id'));

		$.ajax({
			url: base_url+"?mode=news&ajax=true",
			beforeSend: function(){
				$(".edt-save").after('<img class="ajx-l" src="'+style_url+'img/loading.gif" alt="loading..." />');
			},
			dataType: "html",
			type: 'POST',
			data: "&act=edt_comment&nid="+nid+"&id="+id+"&message="+message.value+"&mcr_secure="+secure,
			success: function(data){

				$(".edt-save").next(".ajx-l").remove();

				if(!is_json(data)){
					notify("Ошибка!", "Произошла непредвиденная ошибка. Попробуйте повторить попытку.", 1);
					return false;
				}

				var jsondata	= JSON.parse(data);

				var status = jsondata._status;
				var content = jsondata._content;

				if(status=='success'){

					$("#edit-from-"+id).next().remove();
					$("#edit-from-"+id).remove();

					$(".comment-id#"+id+" .comment-id-content").hide().prepend(content).fadeIn(400, function(){
						$(this).html(content);
						notify("Поздравляем!", "Комментарий успешно изменен!", 3);
					});
					
				}else{
					notify("Ошибка!", content, 1);
				}
			}
		});

		return false;
	});

	$("body").on("click", ".like, .dislike", function(){

		var nid = $(this)[0].id; // new id

		var value = ($(this).hasClass("like")) ? 1 : 0;

		var secure = $('form input[name="mcr_secure"]').val();

		$.ajax({
			url: base_url+"?mode=news&ajax=true",
			beforeSend: function(){
				$(".block-like#"+nid).after('<img class="ajx-l" src="'+style_url+'img/loading.gif" alt="loading..." />');
			},
			dataType: "html",
			type: 'POST',
			data: "&act=like&value="+value+"&nid="+nid+"&mcr_secure="+secure,
			success: function(data){

				$(".block-like#"+nid).next(".ajx-l").remove();

				if(!is_json(data)){
					notify("Ошибка!", "Произошла непредвиденная ошибка. Попробуйте повторить попытку.", 1);
					return false;
				}

				var jsondata	= JSON.parse(data);

				var status = jsondata._status;
				var content = jsondata._content;

				if(status=='success'){
					content = content.split('_');
					var dislikes = content[0];
					var likes = content[1];

					$(".block-like#"+nid+" .likes").hide().fadeIn(400, function(){
						$(this).text(likes);
					});

					$(".block-like#"+nid+" .dislikes").hide().fadeIn(400, function(){
						$(this).text(dislikes);
					});

					notify("", "Ваш голос успешно принят", 3);
					
				}else{
					notify("Ошибка!", content, 1);
				}
			}
		});

		return false;
	});
});