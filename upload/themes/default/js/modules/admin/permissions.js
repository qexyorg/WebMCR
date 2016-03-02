$(function(){
	$('#perm-type').on("change", function(){

		switch($(this).val()){
			case 'boolean':
				$('#perm-default').html('<select name="default" class="span8"><option value="false">FALSE</option><option value="true">TRUE</option></select>');
			break;

			case 'integer':
				$('#perm-default').html('<input type="text" class="span8" name="default" value="1" id="inputDefault" placeholder="'+lng_per.default_val+'">');
			break;

			case 'float':
				$('#perm-default').html('<input type="text" class="span8" name="default" value="1.5" id="inputDefault" placeholder="'+lng_per.default_val+'">');
			break;

			case 'string':
				$('#perm-default').html('<input type="text" class="span8" name="default" value="string" id="inputDefault" placeholder="'+lng_per.default_val+'">');
			break;

			default: return false; break;
		}
	});
});