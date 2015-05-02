$(function(){
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