$(function(){
	$('body').on('click', '.notify > .close', function(e){
		e.preventDefault();

		$(this).closest('.notify').fadeOut('fast', function(){
			$(this).remove();
		});
	});
});