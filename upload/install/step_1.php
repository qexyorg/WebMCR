<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $core, $db, $config, $lng, $user;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->user		= $core->user;
		$this->config	= $core->config;
		$this->lng		= $core->lng;

		$this->core->title = 'Установка — Шаг #1';

		$bc = array(
			'Установка' => BASE_URL."install/",
			'Шаг #1' => BASE_URL."install/?mode=step_1"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	public function content(){
		if(isset($_SESSION['step_1'])){ $this->core->notify('', '', 4, 'install/?mode=step_2'); }

		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(phpversion()<5.1){ $this->core->notify('Ошибка!', 'Версия PHP не соответствует системным требованиям', 2, 'install/?mode=step_1'); }

			if(@ini_get('register_globals')=='off'){ $this->core->notify('Ошибка!', 'Функция Register Globals не соответствует системным требованиям', 2, 'install/?mode=step_1'); }

			if(@ini_get('allow_url_fopen')=='0' || @ini_get('allow_url_fopen')=='false'){ $this->core->notify('Ошибка!', 'Функция allow_url_fopen() не соответствует системным требованиям', 2, 'install/?mode=step_1'); }

			if(!function_exists('ImageCreateFromJpeg')){ $this->core->notify('Ошибка!', 'Библиотека GD не найдена', 2, 'install/?mode=step_1'); }

			if(!function_exists('mysql_query') && !function_exists('mysqli_query')){ $this->core->notify('Ошибка!', 'MySQL не найдена', 2, 'install/?mode=step_1'); }

			if(!function_exists('ob_start')){ $this->core->notify('Ошибка!', 'Функции буферизации данных недоступны', 2, 'install/?mode=step_1'); }

			if(!is_writable(MCR_ROOT.'configs') || !is_readable(MCR_ROOT.'configs')){ $this->core->notify('Ошибка!', 'Отсутствуют права на чтение или запись папки configs', 2, 'install/?mode=step_1'); }

			if(!is_writable(MCR_ROOT.'configs/db.php') || !is_readable(MCR_ROOT.'configs/db.php')){ $this->core->notify('Ошибка!', 'Отсутствуют права на чтение или запись файла configs/db.php', 2, 'install/?mode=step_1'); }

			if(!is_writable(MCR_ROOT.'configs/functions.php') || !is_readable(MCR_ROOT.'configs/functions.php')){ $this->core->notify('Ошибка!', 'Отсутствуют права на чтение или запись файла configs/functions.php', 2, 'install/?mode=step_1'); }

			if(!is_writable(MCR_ROOT.'configs/mail.php') || !is_readable(MCR_ROOT.'configs/mail.php')){ $this->core->notify('Ошибка!', 'Отсутствуют права на чтение или запись файла configs/mail.php', 2, 'install/?mode=step_1'); }

			if(!is_writable(MCR_ROOT.'configs/main.php') || !is_readable(MCR_ROOT.'configs/main.php')){ $this->core->notify('Ошибка!', 'Отсутствуют права на чтение или запись файла configs/main.php', 2, 'install/?mode=step_1'); }

			if(!is_writable(MCR_ROOT.'configs/pagin.php') || !is_readable(MCR_ROOT.'configs/pagin.php')){ $this->core->notify('Ошибка!', 'Отсутствуют права на чтение или запись файла configs/pagin.php', 2, 'install/?mode=step_1'); }

			if(!is_writable(MCR_ROOT.'configs/search.php') || !is_readable(MCR_ROOT.'configs/search.php')){ $this->core->notify('Ошибка!', 'Отсутствуют права на чтение или запись файла configs/search.php', 2, 'install/?mode=step_1'); }

			if(!is_writable(MCR_ROOT.'uploads') || !is_readable(MCR_ROOT.'uploads')){ $this->core->notify('Ошибка!', 'Отсутствуют права на чтение или запись папки uploads', 2, 'install/?mode=step_1'); }

			if(!is_writable(MCR_ROOT.'uploads/cloaks') || !is_readable(MCR_ROOT.'uploads/cloaks')){ $this->core->notify('Ошибка!', 'Отсутствуют права на чтение или запись папки uploads/cloaks/', 2, 'install/?mode=step_1'); }

			if(!is_writable(MCR_ROOT.'uploads/panel-icons') || !is_readable(MCR_ROOT.'uploads/panel-icons')){ $this->core->notify('Ошибка!', 'Отсутствуют права на чтение или запись папки uploads/panel-icons/', 2, 'install/?mode=step_1'); }

			if(!is_writable(MCR_ROOT.'uploads/skins') || !is_readable(MCR_ROOT.'uploads/skins')){ $this->core->notify('Ошибка!', 'Отсутствуют права на чтение или запись папки uploads/skins/', 2, 'install/?mode=step_1'); }

			if(!is_writable(MCR_ROOT.'uploads/skins/interface') || !is_readable(MCR_ROOT.'uploads/skins/interface')){ $this->core->notify('Ошибка!', 'Отсутствуют права на чтение или запись папки uploads/skins/interface/', 2, 'install/?mode=step_1'); }

			if(!is_writable(MCR_ROOT.'uploads/smiles') || !is_readable(MCR_ROOT.'uploads/smiles')){ $this->core->notify('Ошибка!', 'Отсутствуют права на чтение или запись папки uploads/smiles/', 2, 'install/?mode=step_1'); }

			$_SESSION['step_1'] = true;

			$this->core->notify('Шаг #2', 'Настройки базы', 4, 'install/?mode=step_2');

		}

		$data = array(
			"PHP" => (phpversion()<5.1) ? '<b class="text-error">'.phpversion().'</b>' : '<b class="text-success">'.phpversion().'</b>',
			"REG_GLOB" => (@ini_get('register_globals')=='on') ? '<b class="text-error">Вкл.</b>' : '<b class="text-success">Выкл.</b>',
			"URL_FOPEN" => (@ini_get('allow_url_fopen')=='1' || @ini_get('allow_url_fopen')=='true') ? '<b class="text-success">Вкл.</b>' : '<b class="text-error">Выкл.</b>',
			"GD" => (function_exists('ImageCreateFromJpeg')) ? '<b class="text-success">Да</b>' : '<b class="text-error">Нет</b>',
			"MYSQL" => (function_exists("mysql_query") || function_exists("mysqli_query")) ? '<b class="text-success">Да</b>' : '<b class="text-error">Нет</b>',
			"BUFER" => (function_exists("ob_start")) ? '<b class="text-success">Да</b>' : '<b class="text-error">Нет</b>',

			"FOLDER_CONFIGS" => (is_writable(MCR_ROOT.'configs') && is_readable(MCR_ROOT.'configs')) ? '<b class="text-success">Да</b>' : '<b class="text-error">Нет</b>',

			"FILE_DB" => (is_writable(MCR_ROOT.'configs/db.php') && is_readable(MCR_ROOT.'configs/db.php')) ? '<b class="text-success">Да</b>' : '<b class="text-error">Нет</b>',

			"FILE_FUNCTIONS" => (is_writable(MCR_ROOT.'configs/functions.php') && is_readable(MCR_ROOT.'configs/functions.php')) ? '<b class="text-success">Да</b>' : '<b class="text-error">Нет</b>',

			"FILE_MAIL" => (is_writable(MCR_ROOT.'configs/mail.php') && is_readable(MCR_ROOT.'configs/mail.php')) ? '<b class="text-success">Да</b>' : '<b class="text-error">Нет</b>',

			"FILE_MAIN" => (is_writable(MCR_ROOT.'configs/main.php') && is_readable(MCR_ROOT.'configs/main.php')) ? '<b class="text-success">Да</b>' : '<b class="text-error">Нет</b>',

			"FILE_PAGIN" => (is_writable(MCR_ROOT.'configs/pagin.php') && is_readable(MCR_ROOT.'configs/pagin.php')) ? '<b class="text-success">Да</b>' : '<b class="text-error">Нет</b>',

			"FILE_SEARCH" => (is_writable(MCR_ROOT.'configs/search.php') && is_readable(MCR_ROOT.'configs/search.php')) ? '<b class="text-success">Да</b>' : '<b class="text-error">Нет</b>',

			"FOLDER_UPLOADS" => (is_writable(MCR_ROOT.'uploads') && is_readable(MCR_ROOT.'uploads')) ? '<b class="text-success">Да</b>' : '<b class="text-error">Нет</b>',

			"FOLDER_CLOAKS" => (is_writable(MCR_ROOT.'uploads/cloaks') && is_readable(MCR_ROOT.'uploads/cloaks')) ? '<b class="text-success">Да</b>' : '<b class="text-error">Нет</b>',

			"FOLDER_ICONS" => (is_writable(MCR_ROOT.'uploads/panel-icons') && is_readable(MCR_ROOT.'uploads/panel-icons')) ? '<b class="text-success">Да</b>' : '<b class="text-error">Нет</b>',

			"FOLDER_SKINS" => (is_writable(MCR_ROOT.'uploads/skins') && is_readable(MCR_ROOT.'uploads/skins')) ? '<b class="text-success">Да</b>' : '<b class="text-error">Нет</b>',

			"FOLDER_INTERF" => (is_writable(MCR_ROOT.'uploads/skins/interface') && is_readable(MCR_ROOT.'uploads/skins')) ? '<b class="text-success">Да</b>' : '<b class="text-error">Нет</b>',

			"FOLDER_SMILES" => (is_writable(MCR_ROOT.'uploads/smiles') && is_readable(MCR_ROOT.'uploads/smiles')) ? '<b class="text-success">Да</b>' : '<b class="text-error">Нет</b>',
		);

		return $this->core->sp(MCR_ROOT."install/theme/step_1.html", $data);
	}

}

?>