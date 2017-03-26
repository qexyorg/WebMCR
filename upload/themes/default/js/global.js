var mcr = {
	debug: true, // Включение/отключение логирования ошибок в консоль [true|false]

	// Получение информации, загружаемой с сервера
	meta_data: JSON.parse($('meta[name="data"]').attr('content')),

	// Функция показа/скрытия информации о загрузке
	loading: function(status){

		if(status!==false){
			if($('#js-loader').is(':hidden')){ $('#js-loader').fadeIn(500); }
		}else{
			if($('#js-loader').is(':visible')){ $('#js-loader').fadeOut(500); }
		}

		return (status===false) ? false : true;
	},

	/*
	 * Оповещение - показывает блок оповещения с различной информацией и автоматически выключает показ информации о загрузке (предыдущую функцию)
	 *
	 * @param title - Название блока
	 *
	 * @param message - Сообщение оповещения
	 *
	 * @param type - Тип оповещения 2-Ошибка(красный блок)|3-Успех(зеленый блок)|4-информация(синий блок)|остальное-примечание(оранжевый блок)
	 *
	 * @param result - Возвращаемый результат [true|false]
	 */
	notify: function(title, message, type, result){

		var that = this;

		type = (type===undefined) ? 0 : parseInt(type);

		switch(type){
			case 2: type = 'alert-error'; break;
			case 3: type = 'alert-success'; break;
			case 4: type = 'alert-info'; break;

			default: type = ''; break;
		}

		$('#js-notify').removeClass('alert-error alert-success alert-info').addClass(type);

		$('#js-notify > #title').html(title);
		$('#js-notify > #message').html(message);

		$('#js-notify').fadeIn(300);

		that.loading(false);

		$('html, body').animate({ scrollTop: $('#js-notify').offset().top-50}, 'fast');

		if(typeof timeout != 'undefined'){ clearTimeout(timeout); }

		timeout = setTimeout(function(){ that.notify_close(); }, 2500);

		return (result===true) ? true : false;
	},

	// Скрывает оповещение и очищает его содержимое
	notify_close: function(){
		$('#js-notify').fadeOut(500);
	},

	// Логгер ошибок в консоль
	logger: function(data){
		if(this.debug){ console.log(data); }
	},

	// Получение параметра из URL по ключу
	getUrlParam: function(name){
		name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
		var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
			results = regex.exec(location.search);
		return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
	},

	// Получение всех параметров из URL
	getUrlParams: function(){
		var string = location.search.split('?')[1];

		var result = {};

		string = decodeURIComponent(string);

		if(string==undefined || string=='undefined'){ return result; }

		$.each(string.split('&'), function(key, val){
			expl = val.split('=');

			result[expl[0]] = expl[1];
		});

		return result;
	},

	// Изменение параметра url
	changeUrlParam: function(json){
		var get = this.getUrlParams();

		$.each(json, function(key, value){
			if(get[key]===undefined || value!==false){ get[key] = value; }
			if(value===false && get[key]!==undefined){ delete get[key]; }
		});

		if(Object.keys(get).length<=0){ location.search = ''; return false; }

		var string = '?';

		$.each(get, function(key, val){
			string = string+key+'='+val+'&';
		});

		string = string.substring(0, string.length - 1);

		location.search = string;

		return true;
	},

	/*
	 * Результирующий запрос - запрос, возвращающий результат.
	 *
	 * @param method - метод отправки запроса [GET|POST]
	 *
	 * @param url - Адрес на который будет отправляться запрос
	 *
	 * @param params - Параметры запроса [key1=param&key2=param2...]
	 *
	 */
	send_ret_req: function(method, url, params){
		var req = null;
		try { req = new ActiveXObject("Msxml2.XMLHTTP"); } catch (e) {
			try { req = new ActiveXObject("Microsoft.XMLHTTP"); } catch (e) {
				try { req = new XMLHttpRequest(); } catch(e) {}
			}
		}
		if(req == null){ throw new Error(lng.e_xmlhr); }

		req.open(method, url, false);
		req.send(params);

		return req.responseText;
	},

	// Получение информации о откртых и закрытых спойлерах
	spl_items: Cookies.getJSON('spl_items'),

	init_filemanager: function(pge){
		var that = this;

		var loadpage = (pge===undefined) ? 1 : pge;

		that.loading();

		var formdata = new FormData();
		
		formdata.append('mcr_secure', that.meta_data.secure);
		formdata.append('page', loadpage);

		$.ajax({
			url: "index.php?mode=ajax&do=filemanager",
			dataType: "json",
			type: 'POST',
			contentType: false,
			processData: false,
			data: formdata,
			error: function(data){
				that.logger(data);
				that.notify(lng.error, lng.e_filemanager);
			},

			success: function(data){

				if(!data._type){ that.loading(false); return; }

				$('.file-manager > .lastfiles').empty();

				$.each(data._data, function(key, ar){

					if(ar.size < 1024){
						var size = ar.size+' '+lng.b;
					}else if(ar.size < 1048576){
						var size = (ar.size / 1024).toFixed(2);
						size = size+' '+lng.kb;
					}else if(ar.size < 1073741824){
						var size = (ar.size / 1024 / 1024).toFixed(2);
						size = size+' '+lng.mb;
					}else{
						var size = (ar.size / 1024 / 1024 / 1024).toFixed(2);
						size = size+' '+lng.gb;
					}

					$('.file-manager > .lastfiles').append('<div class="file-line" id="'+ar.uniq+'">'+
							'<div class="line-uniq"><a href="'+ar.link+'">'+ar.uniq+'</a> <a href="#" rel="tooltip" title="'+lng.change+'" class="file-edit icon-edit"></a></div>'+
							'<div class="line-oldname">'+ar.oldname+'</div>'+
							'<div class="line-size">'+size+'</div>'+
							'<div class="line-downloads"><i class="icon-download" rel="tooltip" title="'+lng.count_downloads+'"></i> '+ar.downloads+'</div>'+
							'<div class="line-info"><i class="icon-info-sign" rel="tooltip" title="'+lng.added+': '+ar.login+' | '+lng.date+': '+ar.date+'"></i></div>'+
							'<div class="line-act"><a href="#" rel="tooltip" title="'+lng.delete+'" class="file-remove icon-remove"></a></div>'+
						'</div>');

					that.loading(false);
				});

				$('.file-manager > .lastfiles').append('<div class="pagination" id="'+loadpage+'"><ul>'+
						'<li><a href="#" class="ajax-pagin-left"><</a></li>'+
						'<li><a href="#" class="ajax-pagin-right">></a></li>'+
					'</ul></div>');
			},

			complete:function(){

				if(pge!==undefined){
					$('html, body').stop().animate({
						scrollTop: $('.file-manager').offset().top
					}, 0);
				}
			}
		});
	}
};

// Функции, вызываемые при загрузке
$(function(){
	$('input[type="file"].file-inputs').bootstrapFileInput();

	// Загрузка файлового менеджера(если доступен)
	if($('.file-manager').length > 0){ mcr.init_filemanager(); }

	// Добавление элемента защиты в html код страницы
	$('form[method="post"]').prepend('<input type="hidden" name="mcr_secure" value="'+mcr.meta_data.secure+'">');

	// Включение обработчика tooltip'ов от bootstrap
	$('body').tooltip({selector: '[rel=tooltip]'});
	//$('[rel=tooltip]').tooltip({container: 'body'}); // If jump elements

	// Обработчик закрытия блока оповещений
	$('body').on('click', '#js-notify > #close', function(){

		mcr.notify_close();

		return false;
	});

	$("body").on('click', '#close-notify', function(){
		$(".block-notify").fadeOut("normal", function(){
			$(this).remove();
		});
		return false;
	});
	
	$("body").on("click", ".check-all", function(){
		var element = $(this).attr("data-for");
		var val = false;
		if($(this)[0].checked){ val = true; }
		$("."+element).prop('checked', val);

	});

	$('body').on('click', '.remove', function(){

		if($(this).attr("data-checkbox")!='false'){
			var element = $(this).attr("data-for");
			var length = $('.'+element+':checked').length;

			if(length<=0){
				return mcr.notify(lng.error, lng.not_selected);
			}

		}
		
		var text = $(this).attr("data-text");
		if(!confirm(text)){ return false; }

		return true;
	});

	$(".mcr-debug .action").on("click", function(){
		$(".mcr-debug").toggleClass("open");
		return false;
	});

	// Обработчик клика по ББ-кодам
	$("body").on("click", ".bb-panel .bb", function(){

		if($(this).hasClass('bb-modal')){ return true; }

		// Получает идентификатор панели ББ-кодов и поля ввода
		var panel_id = $(this).closest(".bb-panel").attr("id");

		// Получаем поле ввода
		var panel_obj = $('textarea[data-for="'+panel_id+'"]')[0];

		// Фокусируем поле ввода
		panel_obj.focus();

		// Получаем позиции курсора
		var pos1 = panel_obj.selectionStart, pos2 = panel_obj.selectionEnd;

		// Получаем теги элементов
		var leftcode = ($(this).attr("data-left")==undefined) ? '' : $(this).attr("data-left");
		var rightcode = ($(this).attr("data-right")==undefined) ? '' : $(this).attr("data-right");

		var val = panel_obj.value;

		// Вставка ББ-кода в содержимое поля ввода на места выделения
		panel_obj.value = val.substr(0,pos1) + leftcode + val.substr(pos1,pos2-pos1) + rightcode + val.substr(pos2,val.length);

		// Устанавливаем позиции курсора после вставки ББ-кода
		panel_obj.setSelectionRange(pos1+leftcode.length,pos2+leftcode.length);

		return false;
	});

	$("body").on("click", ".bb-panel .bb.bb-modal", function(){

		// Получает идентификатор панели ББ-кодов и поля ввода
		var panel_id = $(this).closest(".bb-panel").attr("id");

		// Получаем поле ввода
		var panel_obj = $('textarea[data-for="'+panel_id+'"]')[0];

		var modal_id = $(this).attr('href');

		// Фокусируем поле ввода
		panel_obj.focus();

		// Получаем позиции курсора
		var pos1 = panel_obj.selectionStart, pos2 = panel_obj.selectionEnd;

		// Получаем выделенный текст
		var copy = panel_obj.value.substring(pos1, pos2);

		if($(this).attr('data-paste').length>0){
			var insert = parseInt($(this).attr('data-paste'));
			// Вставляем выделенный текст в поле модального окна
			$(modal_id+' .bb-input').eq(insert).val(copy);
		}

	});

	$("body").on("click", ".bb-panel .bb-insert-input", function(){

		// Получает идентификатор панели ББ-кодов и поля ввода
		var panel_id = $(this).closest(".bb-panel").attr("id");

		// Получаем поле ввода
		var panel_obj = $('textarea[data-for="'+panel_id+'"]')[0];

		// Фокусируем поле ввода
		panel_obj.focus();

		// Получаем позиции курсора
		var pos1 = panel_obj.selectionStart;
		var pos2 = panel_obj.selectionEnd;

		var modal = $(this).closest('.modal');

		var inputs = modal.find('.bb-input');
		var code = modal.find('.data-code');
		var leftcode = code.attr('data-left');
		var rightcode = code.attr('data-right');
		var centercode = code.attr('data-center');

		$.each(inputs, function(key, elem){

			leftcode = leftcode.replace('*'+key+'*', elem.value);
			rightcode = rightcode.replace('*'+key+'*', elem.value);
			centercode = centercode.replace('*'+key+'*', elem.value);
		});

		code = leftcode + centercode + rightcode;

		var val = panel_obj.value;

		// Вставка ББ-кода в содержимое поля ввода на места выделения
		panel_obj.value = val.substr(0,pos1) + code + val.substr(pos2,val.length);

		$('#'+modal.attr('id')).modal('hide');

		pos1 = pos1+leftcode.length;
		pos2 = pos1+centercode.length;

		panel_obj.setSelectionRange(pos1,pos2);

		return false;
	});

	// Обработчик клика по очистке формы от ББ-Кодов
	$('body').on('click', '.bb-panel .bb-clear', function(){

		var panel = $(this).closest('.bb-panel');

		var panel_id = panel.attr('id'), elements = panel.find('.bb');

		var panel_obj = $('textarea[data-for="'+panel_id+'"]');

		var new_val = panel_obj.val();

		elements.each(function(){

			var left = $(this).attr('data-left');
			if($(this).closest('.bb-smiles').length<=0){
				var reg = new RegExp('\\[(\\w+)(="\\*(\\d+)\\*")?\\]', 'ig');
				var find = reg.exec(left);
			}else{
				var find = null;
				new_val = new_val.replace(left, '');
			}

			if(find!==null){

				var repl = new RegExp('\\[(\\/)?'+find[1]+'(="([\\w\\s\\-\\.\\:\\;\\+\\|\\,\\?\\&\\=\\/\\*]+)?")?\\]', 'ig');
				new_val = new_val.replace(repl, '');
			}
		});

		panel_obj[0].value = new_val;

		return false;
	});

	$("#search-selector a").click(function(){

		var search_val = $("#search-hidden").val();

		$("#search-selector a#"+search_val).parent().removeClass("active");

		var id = this.id;

		$("#search-hidden").val(id);

		$(this).parent().addClass("active");

		return false;

	});

	$("body").on("click", ".edit", function(){
		
		var element = $(this).attr("data-for");
		var length = $('.'+element+':checked').length, link = $(this).attr("data-link");

		if(length!=1){
			return mcr.notify(lng.error, lng.only_one);
		}

		var id = $('.'+element+':checked').val();

		window.location.href = link+id;
		
		return false;
	});

	// Обработка всех групп меню в цикле
	$('.spl-body').each(function(){
		// ID группы
		var id = $(this).attr('id');

		// Проверка на существование записи о группе в куках
		if(mcr.spl_items===undefined || mcr.spl_items[id]===undefined){ return; }

		// Изменение класса группы при условии
		if(mcr.spl_items[id]===true){
			$(this).toggleClass('closed');
			$('.spl-btn[data-for="'+id+'"]').toggleClass('closed');
		}
	});
	
	// Действие при клике на кнопку спойлера
	$('body').on('click', '.spl-btn', function(){

		var that = $(this);

		if(that.attr('data-block')!==undefined){

			that.closest(that.attr('data-block')).find('.spl-body').slideToggle("fast", function(){
				that.toggleClass('closed');
				$(this).toggleClass('closed');
			});

			return false;
		}

		var element = $(this).attr("data-for");

		if(mcr.spl_items===undefined){ mcr.spl_items = {}; }

		// Изменение класса при нажатии и выставление печенек
		$(".spl-body#"+element).slideToggle("fast", function(){
			that.toggleClass('closed');
			$(this).toggleClass('closed');

			mcr.spl_items[element] = (!mcr.spl_items[element]) ? true : false;
			
			Cookies.set('spl_items', mcr.spl_items, { expires: 365 });
		});

		return false;
	});

	$('body').on('click', '.qxbb-spoiler > .qxbb-spoiler-btn', function(){

		var body = $(this).closest('.qxbb-spoiler').find('.qxbb-spoiler-body');

		if(body.is(':visible')){
			body.fadeOut('fast');
		}else{
			body.fadeIn('normal');
		}

		return false;
	});

	// Класс для ссылок с отменой редиректа
	$("body").on("click", ".false", function(){ return false; });

	$('body').on('input change', '.file-manager input[name="files"]', function(){

		mcr.loading();

		var formdata = new FormData();

		$.each($(this)[0].files, function(key, value){
			formdata.append('files'+key, value);
		});
		
		formdata.append('mcr_secure', mcr.meta_data.secure);

		$.ajax({
			url: "index.php?mode=ajax&do=filemanager&op=upload",
			dataType: "json",
			type: 'POST',
			contentType: false,
			processData: false,
			data: formdata,
			error: function(data){
				mcr.logger(data);
				mcr.notify(lng.error, lng.e_file_load);
			},

			success: function(data){

				if(!data._type){ return mcr.notify(data._title, data._message); }

				if(data._data.errors.length > 0){ mcr.notify(lng.warning, lng.e_files_not_loaded); mcr.logger(data._data.errors); }

				$.each(data._data.data, function(key, ar){

					if(ar.size < 1024){
						var size = ar.size+' б';
					}else if(ar.size < 1048576){
						var size = ar.size+' Кб';
					}else if(ar.size < 1073741824){
						var size = ar.size+' Мб';
					}else{
						var size = ar.size+' Гб';
					}

					$('.file-manager > .lastfiles').prepend('<div class="file-line" id="'+ar.uniq+'">'+
							'<div class="line-uniq"><a href="'+ar.link+'">'+ar.uniq+'</a> <a href="#" rel="tooltip" title="'+lng.change+'" class="file-edit icon-edit"></a></div>'+
							'<div class="line-oldname">'+ar.oldname+'</div>'+
							'<div class="line-size">'+size+'</div>'+
							'<div class="line-downloads"><i class="icon-download" rel="tooltip" title="'+lng.count_downloads+'"></i> '+ar.downloads+'</div>'+
							'<div class="line-info"><i class="icon-info-sign" rel="tooltip" title="'+lng.added+': '+ar.login+' | '+lng.date+': '+ar.date+'"></i></div>'+
							'<div class="line-act"><a href="#" rel="tooltip" title="'+lng.delete+'" class="file-remove icon-remove"></a></div>'+
						'</div>');

					$('.file-manager .file-input-wrapper input[type="file"].file-inputs').attr('title', lng.drop_files_here);
					$('.file-manager .file-input-wrapper > span').text(lng.drop_files_here);
					$('.file-manager .file-input-wrapper input[type="file"].file-inputs').removeAttr('style');

					mcr.loading(false);
				});
			}
		});

		return false;
	});

	$('body').on('click', '.file-manager .file-remove', function(){

		mcr.loading();

		var that = $(this);

		var id_line = that.closest('.file-line').attr('id');

		var formdata = new FormData();

		formdata.append('mcr_secure', mcr.meta_data.secure);
		formdata.append('id', id_line);

		$.ajax({
			url: "index.php?mode=ajax&do=filemanager&op=remove",
			dataType: "json",
			type: 'POST',
			contentType: false,
			processData: false,
			data: formdata,
			error: function(data){
				mcr.logger(data);
				mcr.notify(lng.error, lng.e_file_delete);
			},
			success: function(data){
				if(!data._type){ return mcr.notify(data._title, data._message); }

				that.closest('.file-line').fadeOut('normal', function(){
					$(this).remove();
					mcr.loading(false);
				});
			}
		});

		return false;
	});

	$('body').on('click', '.file-manager .file-edit', function(){

		mcr.loading();

		var that = $(this);

		var text = that.prev('a').text();

		if(!that.hasClass('edit-active')){
			if(that.hasClass('icon-edit')){ that.removeClass('icon-edit'); }
			that.addClass('icon-ok edit-active');
			that.prev('a').remove();
			$('<input class="file-edit-input" type="text" value="'+text+'">').insertBefore(that);
			mcr.loading(false);
			return false;
		}

		var id_line = that.closest('.file-line').attr('id');
		var new_val = that.prev('.file-edit-input').val();

		if(new_val.length <= 0){ return mcr.notify(lng.error, lng.e_id_not_filled); }

		var formdata = new FormData();

		formdata.append('mcr_secure', mcr.meta_data.secure);
		formdata.append('id', id_line);
		formdata.append('val', new_val);

		$.ajax({
			url: "index.php?mode=ajax&do=filemanager&op=edit",
			dataType: "json",
			type: 'POST',
			contentType: false,
			processData: false,
			data: formdata,
			error: function(data){
				mcr.logger(data);
				mcr.notify(lng.error, lng.e_file_edit);
			},
			success: function(data){
				if(!data._type){ return mcr.notify(data._title, data._message); }

				if(that.hasClass('icon-ok')){ that.removeClass('icon-ok'); }
				if(that.hasClass('edit-active')){ that.removeClass('edit-active'); }
				that.addClass('icon-edit');

				that.prev('.file-edit-input').remove();

				$('<a href="'+data._data.link+'">'+data._data.uniq+'</a>').insertBefore(that);

				that.closest('.file-line').attr('id', data._data.uniq);

				mcr.loading(false);
			}
		});

		return false;
	});

	$('body').on('click', '.pagination .ajax-pagin-left, .pagination .ajax-pagin-right', function(){
		var that = $(this);

		var page = $(this).closest('.pagination').attr('id');

		page = ($(this).hasClass('ajax-pagin-left')) ? parseInt(page)-1 : parseInt(page)+1;

		mcr.init_filemanager(page);

		return false;
	});

	$('.cpp').minicolors();

	$('body').on('click', '.is_auth_user', function(){
		if(!mcr.meta_data.is_auth){ mcr.notify(lng.error, lng.e_auth, false); return false; }
	});

	function fd_ac_users(query){
		var formdata = new FormData();

		formdata.append('mcr_secure', mcr.meta_data.secure);
		formdata.append('query', query);

		return formdata;
	}

	$('body').on('input', 'input[type="text"].ac_users', function(){

		var that = $(this);

		var formdata = new FormData();

		formdata.append('mcr_secure', mcr.meta_data.secure);
		formdata.append('query', that.val());

		that.typeahead({
			items: 10,
			minLength: 2,
			source: function(query, process){
				return $.ajax({
					url: "index.php?mode=ajax&do=ac_users",
					dataType: 'json',
					type: 'POST',
					async: true,
					cache: false,
					contentType: false,
					processData: false,
					data: fd_ac_users(query),
					error: function(data){
						mcr.logger(data);
						mcr.notify(lng.error, 'warn');
					},

					success: function(data){
						if(!data._type){ return; }

						process(data._data);
					}
				});
			},

			matcher: function (param){
				return true;
			}
		});
	});
	
});