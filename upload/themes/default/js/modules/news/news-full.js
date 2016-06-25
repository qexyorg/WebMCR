$(function(){
	$("body").on("click", "#add_comment", function(){

		mcr.loading();

		var message = $('textarea[name="message"]')[0], nid = parseInt(mcr.getUrlParam('id'));

		var formdata = new FormData();
		
		formdata.append('id', nid);
		formdata.append('message', message.value);
		formdata.append('mcr_secure', mcr.meta_data.secure);

		$.ajax({
			url: "index.php?mode=ajax&do=modules|news|add_comment",
			dataType: "json",
			type: 'POST',
			contentType: false,
			processData: false,
			data: formdata,
			error: function(data){
				mcr.logger(data);
				mcr.notify(lng.error, lng_nf.e_add_comment);
			},

			success: function(data){

				if(!data._type){ return mcr.notify(data._title, data._message); }

				if($(".comment-id").hasClass("none")){ $(".comment-id.none").remove(); }

				$(".comment-list-content").hide().prepend(data._data).fadeIn(400, function(){
					message.value = '';
					var com_count = parseInt($("#comment-count").text())+1;
					$("#comment-count").text(com_count);

					mcr.notify(data._title, data._message, 3);
				});
			}
		});

		return false;
	});

	$("body").on("click", ".del_comment", function(){

		var that = $(this);

		if(!confirm(lng_nf.del_confirm_comment)){ return false; }
		
		mcr.loading();

		var nid = parseInt(mcr.getUrlParam('id')), id = parseInt($(this).attr("data-id"));

		var formdata = new FormData();
		
		formdata.append('id', id);
		formdata.append('nid', nid);
		formdata.append('mcr_secure', mcr.meta_data.secure);

		$.ajax({
			url: "index.php?mode=ajax&do=modules|news|delete_comment",
			dataType: "json",
			type: 'POST',
			contentType: false,
			processData: false,
			data: formdata,
			error: function(data){
				mcr.logger(data);
				mcr.notify(lng.error, lng_nf.e_delete_comment);
			},
			success: function(data){

				if(!data._type){ return mcr.notify(data._title, data._message); }

				that.closest('.comment-id').fadeOut(400, function(){

					$(this).remove();

					var com_count = parseInt($("#comment-count").text())-1;
					$("#comment-count").text(com_count);

					mcr.notify(data._title, data._message, 3);
				});
			}
		});

		return false;
	});

	$("body").on("click", ".get_comment", function(){

		mcr.loading();

		var nid = parseInt(mcr.getUrlParam('id')), id = parseInt($(this).attr("data-id"));

		var formdata = new FormData();
		
		formdata.append('id', id);
		formdata.append('nid', nid);
		formdata.append('mcr_secure', mcr.meta_data.secure);

		$.ajax({
			url: "index.php?mode=ajax&do=modules|news|get_comment",
			dataType: "json",
			type: 'POST',
			contentType: false,
			processData: false,
			data: formdata,
			error: function(data){
				mcr.logger(data);
				mcr.notify(lng.error, lng_nf.e_get_comment);
			},
			success: function(data){

				if(!data._type){ return mcr.notify(data._title, data._message); }

				$('textarea[name="message"]')[0].value += '[quote="'+data._data.login+' | '+data._data.create+'"]'+data._data.text+'[/quote]';

				mcr.loading(false);
			}
		});

		return false;

	});

	$("body").on("click", ".edt_comment", function(){

		mcr.loading();

		var nid = parseInt(mcr.getUrlParam('id')), id = parseInt($(this).attr("data-id"));

		var formdata = new FormData();
		
		formdata.append('id', id);
		formdata.append('nid', nid);
		formdata.append('mcr_secure', mcr.meta_data.secure);

		$.ajax({
			url: "index.php?mode=ajax&do=modules|news|get_comment",
			dataType: "json",
			type: 'POST',
			contentType: false,
			processData: false,
			data: formdata,
			error: function(data){
				mcr.logger(data);
				mcr.notify(lng.error, lng_nf.e_edit_comment);
			},
			success: function(data){

				if(!data._type){ return mcr.notify(data._title, data._message); }

				$(".comment-id#"+id+" .comment-id-content").html('<textarea class="edit-from" id="edit-from-'+id+'">'+data._data.text+'</textarea><a href="#" class="btn btn-primary edt-save" id="'+id+'">'+lng.save+'</a>');
				
				mcr.loading(false);
			}
		});

		return false;

	});

	$("body").on("click", ".edt-save", function(){

		mcr.loading();

		var id = $(this).attr("id");

		var message = $('#edit-from-'+id)[0], nid = parseInt(mcr.getUrlParam('id'));

		var formdata = new FormData();
		
		formdata.append('id', id);
		formdata.append('nid', nid);
		formdata.append('message', message.value);
		formdata.append('mcr_secure', mcr.meta_data.secure);

		$.ajax({
			url: "index.php?mode=ajax&do=modules|news|edit_comment",
			dataType: "json",
			type: 'POST',
			contentType: false,
			processData: false,
			data: formdata,
			error: function(data){
				mcr.logger(data);
				mcr.notify(lng.error, lng_nf.e_save_comment);
			},
			success: function(data){

				if(!data._type){ return mcr.notify(data._title, data._message); }

				$("#edit-from-"+id).next().remove();
				$("#edit-from-"+id).remove();

				$(".comment-id#"+id+" .comment-id-content").hide().prepend(data._data).fadeIn(400, function(){
					$(this).html(data._data);
					mcr.notify(data._title, data._message, 3);
				});
			}
		});

		return false;
	});

	$("body").on("click", ".like, .dislike", function(){

		mcr.loading();

		var nid = $(this)[0].id, value = ($(this).hasClass("like")) ? 1 : 0;

		var formdata = new FormData();

		formdata.append('nid', nid);
		formdata.append('value', value);
		formdata.append('mcr_secure', mcr.meta_data.secure);

		$.ajax({
			url: "index.php?mode=ajax&do=modules|news|news_like",
			dataType: "json",
			type: 'POST',
			contentType: false,
			processData: false,
			data: formdata,
			error: function(data){
				mcr.logger(data);
				mcr.notify(lng.error, lng_nf.e_vote);
			},
			success: function(data){

				if(!data._type){ return mcr.notify(data._title, data._message); }

				$(".block-like#"+nid+" .likes").hide().fadeIn(400, function(){
					$(this).text(data._data.likes);
				});

				$(".block-like#"+nid+" .dislikes").hide().fadeIn(400, function(){
					$(this).text(data._data.dislikes);
				});

				mcr.notify(data._title, data._message, 3);
			}
		});

		return false;
	});
});