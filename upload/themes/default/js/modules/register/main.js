$(function(){

	$('body').on('click', '.mcr-register #postbut', function(){
		mcr.loading();

		if($('.mcr-register input[name="accept"]:checked').length!=1){ return mcr.notify(lng.error, lng_reg.required_fields); }

		var login = $('.mcr-register #inputLogin').val();
		var email = $('.mcr-register #inputEmail').val();
		var password = $('.mcr-register #inputPassword').val();
		var repassword = $('.mcr-register #inputRePassword').val();
		var rules = $('.mcr-register input[name="accept"]:checked').val();
		var gender = $('.mcr-register #inputGender').val();

		var formData = new FormData();

		formData.append('mcr_secure', mcr.meta_data.secure);
		formData.append('login', login);
		formData.append('email', email);
		formData.append('password', password);
		formData.append('repassword', repassword);
		formData.append('rules', rules);
		formData.append('gender', gender);

		if($('#capcode').length>0){
			formData.append('capcode', $('#capcode').val());
		}else if($('#g-recaptcha-response').length>0){
			formData.append('g-recaptcha-response', $('#g-recaptcha-response').val());
		}

		$.ajax({
			url: "index.php?mode=ajax&do=register",
			dataType: "json",
			type: "POST",
			async: true,
			cache: false,
			contentType: false,
			processData: false,
			data: formData,

			error: function(data){
				mcr.logger(data);
				mcr.notify(lng.error, lng_reg.e_check_form);
			},

			success: function(data){

				if(!data._type){ return mcr.notify(data._title, data._message); }

				setTimeout(function(){
					location.search="";
				}, 4000);

				return mcr.notify(data._title, data._message);
			},
		});

		return false;
	});
});