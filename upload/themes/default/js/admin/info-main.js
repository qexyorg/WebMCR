function load_last_version(){

	$.ajax({
		url: "http://api.webmcr.loc/?do=versions&limit=1",
		beforeSend: function(){ $("#api-engine-version").html($.mcr.loader); },
		dataType: "json",
		success: function(json){
			data = json.data[0];
			if(json.type=='success'){
				$("#api-engine-version").text(data.title+' '+data.version);
			}else{
				$("#api-engine-version").text(json.message);
			}
		}
	});
}

function load_last_news(){

	$.ajax({
		url: "http://api.webmcr.loc/?do=news&limit=1",
		beforeSend: function(){ $("#api-engine-news").html($.mcr.loader); },
		dataType: "json",
		success: function(json){
			data = json.data[0];
			if(json.type=='success'){
				$("#api-engine-news").html('<p><b>'+data.title+'</b></p>'+data.text+'<p><span class="label">'+data.created+'</span></p>');
			}else{
				$("#api-engine-news").text(json.message);
			}
		}
	});
}

function load_git_version(){
	$.getJSON("https://api.github.com/repos/qexyorg/WebMCR/releases", function(json){

		if($.isEmptyObject(json)){ return; }

		$('#git-engine-version').html('<a href="'+json[0]['html_url']+'" target="_blank">'+json[0]['tag_name']+'</a>');
	});
}


function load_git_dev_version(){
	$.getJSON("https://api.github.com/repos/qexyorg/WebMCR/tags", function(json){

		if($.isEmptyObject(json)){ return; }

		$('#git-dev-version').html('<a href="'+json[0]['zipball_url']+'" target="_blank">'+json[0]['name']+'</a>');
	});
}

$(function(){

	if(!navigator.onLine){ notify("Ошибка!", "Отсутствует интернет-соединение", 1); return; }

	$("#api-engine-news, #api-engine-version, #git-engine-version, #git-dev-version").html("∞");

	load_last_news();
	load_last_version();
	load_git_version();
	load_git_dev_version();

});