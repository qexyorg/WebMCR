$(function(){
	$("body").on("click", ".new-id .like, .new-id .dislike", function(){

		mcr.loading();

		var nid = $(this)[0].id, value = ($(this).hasClass("like")) ? 1 : 0;

		var formdata = new FormData();
		
		formdata.append('mcr_secure', mcr.meta_data.secure);
		formdata.append('value', value);
		formdata.append('nid', nid);

		$.ajax({
			url: "index.php?mode=ajax&do=modules|news|news_like",
			dataType: "json",
			type: 'POST',
			contentType: false,
			processData: false,
			data: formdata,
			error: function(data){
				mcr.logger(data);
				mcr.notify(lng.error, lng_nl.e_vote);
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