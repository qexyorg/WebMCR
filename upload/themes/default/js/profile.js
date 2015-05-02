$(function(){
	$('.file-inputs').bootstrapFileInput();

	$(".skin-uploader, .cloak-uploader").on("change", function(){
		$(this).submit();
	});
});