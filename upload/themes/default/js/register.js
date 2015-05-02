$(function(){

	$(".mcr-register #inputLogin, .mcr-register #inputEmail, .mcr-register #inputPassword, .mcr-register #inputRePassword").on("change", function(){
		var value = $(this).val();
		var secure = $('.mcr-register input[name="mcr_secure"]').val();
		var selector = $(this).attr("id");
		var do_post = '';

		switch(selector){
			case 'inputLogin': do_post = 'check_login'; break;
			case 'inputEmail': do_post = 'check_email'; break;
			case 'inputPassword': do_post = 'check_pass'; break;
			case 'inputRePassword': do_post = 'check_repass'; break;

			default: notify("", "Hacking Attempt!", 2); return false; break;
		}

		if($(this).next().hasClass("ajx-l")){
			$(this).next(".ajx-l").remove();	
		}

		if(do_post=='check_pass'){
			if(value.length<6){
				$(".mcr-register input#"+selector).after('<i class="ajx-l icon-remove" rel="tooltip" title="Пароль должен быть не менее 6-ти символов"></i>');
			}else{
				$(".mcr-register input#"+selector).after('<i class="ajx-l icon-ok"></i>');
			}

			return false;
		}else if(do_post=='check_repass'){
			if(value!=$('.mcr-register #inputPassword').val()){
				$(".mcr-register input#"+selector).after('<i class="ajx-l icon-remove" rel="tooltip" title="Пароли не совпадают"></i>');
			}else{
				$(".mcr-register input#"+selector).after('<i class="ajx-l icon-ok"></i>');
			}

			return false;
		}

		$.ajax({
			url: base_url+"?mode=ajax",
			beforeSend: function(){
				$(".mcr-register input#"+selector).after('<img class="ajx-l" src="'+style_url+'img/loading.gif" alt="loading..." />');
			},
			dataType: "json",
			type: 'POST',
			data: "mcr_secure="+secure+"&do="+do_post+"&value="+value,
			error: function(data){
				$(".mcr-register input#"+selector).next(".ajx-l").remove();
				
				$(".mcr-register input#"+selector).after('<i class="ajx-l icon-remove"></i>');
			},

			success: function(data){
				$(".mcr-register input#"+selector).next(".ajx-l").remove();

				if(data._status=='success'){ $(".mcr-register input#"+selector).after('<i class="ajx-l icon-ok"></i>'); }
			}
		});
	});
});