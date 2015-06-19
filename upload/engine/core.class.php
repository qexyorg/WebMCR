<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class core{
	// Set default scope and values
	public $config		= array();

	public $bc, $title, $header, $r_block, $l_block, $menu;

	public $def_header	= '';

	public $db			= false;

	public $user		= false;

	public $lng			= array();

	public $csrf_time	= 1800;

	public $captcha		= array(
		0 => "---",
		1 => "ReCaptcha",
		2 => "KeyCaptcha"
	);

	public function __construct(){

		// Load class config
		require_once(MCR_TOOL_PATH.'config.class.php');

		// Create & set new object of config
		$this->config = new config();

		if(!file_exists(MCR_LANG_PATH.$this->config->main['s_lang'].'/main.php')){ exit("Language path not found"); }

		// Load language package
		require_once(MCR_LANG_PATH.$this->config->main['s_lang'].'/main.php');

		// Set language var
		$this->lng = $lng;

		$this->title = $lng['t_main'];

		// Load database class
		require_once(MCR_TOOL_PATH.'db/'.$this->config->db['backend'].'.class.php');

		// Create & set new object of database
		$this->db = new db($this->config, $lng);

		// Load user class
		require_once(MCR_TOOL_PATH.'user.class.php');

		// Create & set new object of user
		$this->user = new user($this);

		// Load menu class
		require_once(MCR_TOOL_PATH.'menu.class.php');

		// Create & set new object of menu
		$this->menu = new menu($this);

		// Generate CSRF Secure key
		define("MCR_SECURE_KEY", $this->gen_csrf_secure());
	}

	/**
	 * Генерация защиты от CSRF
	 * @return String - ключ защиты
	 */
	public function gen_csrf_secure(){

		$time = time();

		$new_key = $time.'_'.md5($this->user->ip.$this->config->main['mcr_secury'].$time);

		if(!isset($_COOKIE['mcr_secure'])){
			setcookie("mcr_secure", $new_key, time()+$this->csrf_time, '/');
			return $new_key;
		}

		$cookie = explode('_', $_COOKIE['mcr_secure']);

		$old_time = intval($cookie[0]);

		$old_key = md5($this->user->ip.$this->config->main['mcr_secury'].$old_time);

		if(!isset($cookie[1]) || $cookie[1] !== $old_key || ($old_time+$this->csrf_time)<$time){
			setcookie("mcr_secure", $new_key, time()+$this->csrf_time, '/');
			return $new_key;
		}

		return $_COOKIE['mcr_secure'];
	}

	/**
	 * Генерация AJAX оповещений
	 * @param String $message - Сообщение
	 * @param Boolean $status - Статус ошибки (true|false - Истина|Ложь)
	 * @param Array $data - Основное содержимое оповещения и доп. поля
	 * @return JSON exit
	 */
	public function js_notify($message='', $status=false, $data=array()){

		$data = array(
			"_status" => $status,
			"_message" => $message,
			"_data" => $data
		);

		echo json_encode($data);

		exit;
	}

	/**
	 * Генерация основных оповещений движка
	 * @param String $title - Название оповещения
	 * @param String $text - Текст оповещения
	 * @param Integer $type - Тип оповещения (1 - Warning | 2 - Error | 3 - Success | 4 - Info)
	 * @param String $url - URL путь, куда будет направлено оповещение
	 * @param Boolean $out - указывается, если URL является внешним и будет начинаться с http
	 */
	public function notify($title='', $text='', $type=2, $url='', $out=false){

		$new_url = (!$out) ? $this->base_url().$url : $url;

		if($url === true){ $new_url = $_SERVER['REQUEST_URI']; }

		if($out || (empty($title) && empty($text))){ header("Location: ".$new_url); exit; }

		switch($type){
			case 2: $_SESSION['notify_type'] = 'alert-error'; break;
			case 3: $_SESSION['notify_type'] = 'alert-success'; break;
			case 4: $_SESSION['notify_type'] = 'alert-info'; break;

			default: $_SESSION['notify_type'] = ''; break;
		}

		$_SESSION['mcr_notify'] = true;
		$_SESSION['notify_title'] = $title;
		$_SESSION['notify_msg'] = $text;

		header("Location: ".$new_url);

		exit;
	}

	/**
	 * Адрес сайта по умолчанию
	 * @return String - адрес сайта
	 */
	public function base_url(){

		$pos = strripos($_SERVER['PHP_SELF'], 'install/index.php');

		if($pos===false){
			$pos = strripos($_SERVER['PHP_SELF'], 'index.php');
		}


		return mb_substr($_SERVER['PHP_SELF'], 0, $pos, 'UTF-8');
	}

	/**
	 * pagination(@param) - Pagination method
	 *
	 * @param Integer $res - Кол-во результатов на страницу
	 * @param String $page - Адрес страниц без идентификаторов (YOUR_PAGE)
	 * @param Integer $count - Кол-во результатов в базе
	 * @param String $theme - нестандартный шаблон
	 *
	 * @return String - результаты
	 *
	*/
	public function pagination($res=10, $page='', $count=0, $theme=''){
		

		if($this->db===false){ return; }

		$pid = (isset($_GET['pid'])) ? intval($_GET['pid']) : 1;

		$start	= $pid * $res - $res; if($page===0 && $count===0){ return $start; }

		$max	= intval(ceil($count / $res));

		if($pid<=0 || $pid>$max){ return; }

		if($max>1){

			$path = (empty($theme)) ? MCR_THEME_PATH."pagination/" : $theme;

			// First page +
			$fp_data = array(
				"URL" => BASE_URL.$page.'1',
				"VALUE" => "<<"
			);

			$page_first = $this->sp($path."page-id.html", $fp_data);
			// First page +

			// Prev pages +
			$page_prev = '';

			for($pp = $this->config->pagin['arrows']; $pp > 0; $pp--){

				if($pid-$pp <= 0){ continue; }

				$pp_data = array(
					"URL" => BASE_URL.$page.($pid-$pp),
					"VALUE" => $pid-$pp
				);

				$page_prev .= $this->sp($path."page-id.html", $pp_data);
			}
			// Prev pages -

			// Selected page +
			$tp_data = array(
				"URL" => BASE_URL.$page.$pid,
				"VALUE" => $pid
			);

			$page_this = $this->sp($path."page-id-this.html", $tp_data);
			// Selected page -

			// Next pages +
			$page_next = '';

			for($np = 1; $np <= $this->config->pagin['arrows']; $np++){

				if($pid+$np > $max){ continue; }

				$np_data = array(
					"URL" => BASE_URL.$page.($pid+$np),
					"VALUE" => $pid+$np
				);

				$page_next .= $this->sp($path."page-id.html", $np_data);
			}
			// Next pages -

			// Last page +
			$lp_data = array(
				"URL" => BASE_URL.$page.$max,
				"VALUE" => ">>"
			);

			$page_last = $this->sp($path."page-id.html", $lp_data);
			// Last page -

			$data = array(
				"PAGE_FIRST" => $page_first,
				"PAGE_PREV" => $page_prev,
				"PAGE_THIS" => $page_this,
				"PAGE_NEXT" => $page_next,
				"PAGE_LAST" => $page_last
			);

			return $this->sp($path."object.html", $data);
		}

		return;
	}

	/**
	 * Загрузка класса BB кодов
	 * @return object
	 */
	public function load_bb_class(){
		include(MCR_TOOL_PATH.'libs/bb.class.php');

		return new bb($this);
	}

	/**
	 * Валидатор защиты от CSRF атаки
	 * При ошибке возвращается на главную страницу с сообщение "Hacking Attempt!"
	 */
	public function csrf_check(){
		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(!isset($_POST['mcr_secure'])){ $this->notify('Hacking Attempt!'); }

			$secure_key = explode('_', $_POST['mcr_secure']);

			if(!isset($secure_key[1])){ $this->notify('Hacking Attempt!'); }

			$secure_time = intval($secure_key[0]);

			if(($secure_time+$this->csrf_time)<time()){ $this->notify('Hacking Attempt!'); }

			$secure_var = $secure_key[1];
			
			$mcr_secure = $secure_time.'_'.md5($this->user->ip.$this->config->main['mcr_secury'].$secure_time);

			if($mcr_secure!==$_POST['mcr_secure']){ $this->notify('Hacking Attempt!'); }
		}
	}

	/**
	 * Генератор случайной строки
	 * @param $length - длина строки (integer)
	 * @param $safe - По умолчанию строка будет состоять только из латинских букв и цифр (boolean)
	 * @return String
	 */
	public function random($length=10, $safe = true) {
		$chars	= "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
		if(!$safe){ $chars .= '$()#@!'; }

		$string	= "";

		$len	= strlen($chars) - 1;  
		while (strlen($string) < $length){
			$string .= $chars[mt_rand(0,$len)];  
		}

		return $string;
	}

	/**
	 * Генератор списка хлебных крошек
	 * @param Array $array - массив элементов списка
	 * @return Buffer string
	 */
	private function gen_bc_list($array=array()){

		if(empty($array)){ return; }

		$count = count($array)-1;
		$i = 0;
		$string = '';
		
		ob_start();

		foreach($array as $title => $url){
			if($count==$i){
				echo $this->sp(MCR_THEME_PATH."breadcrumbs/id-active.html", array("TITLE" => $title));
			}else{
				$data['TITLE'] = $title;
				$data['URL'] = $url;
				echo $this->sp(MCR_THEME_PATH."breadcrumbs/id-inactive.html", $data);
			}
			$i++;
		}

		return ob_get_clean();
	}

	/**
	 * Генератор хлебных крошек
	 * @param Array $array - массив элементов списка
	 * @return Buffer string
	 */
	public function gen_bc($array=array()){
		if(!$this->config->func['breadcrumbs']){ return false; }

		$data['LIST'] = $this->gen_bc_list($array);

		return $this->sp(MCR_THEME_PATH."breadcrumbs/list.html", $data);
	}

	/**
	 * Подгрузчик модулей
	 * @param String $mode - название модуля
	 * @return Object
	 */
	public function load_mode($mode){
		if(!file_exists(MCR_MODE_PATH.$mode.".php")){ $this->title = $this->lng['e_mode_found']; return $this->sp(MCR_THEME_PATH."default_sp/404.html"); }
		
		include_once(MCR_MODE_PATH.$mode.".php");

		if(!class_exists("module")){ return $this->lng['e_mode_class']; }

		$module = new module($this);
		
		if(!method_exists($module, "content")){ return $this->lng['e_mode_method']; }

		return $module->content();
	}

	/**
	 * Системный генератор хэшей паролей пользователей
	 * @param String $string - исходный пароль
	 * @param String $salt - соль
	 * @param Integer $crypt - метод шифрования (По умолчанию md5)
	 * @return String
	 */
	public function gen_password($string='', $salt='', $crypt=false){
		if($crypt===false){ $crypt = $this->config->main['crypt']; }

		switch($crypt) {
			case 1: return sha1($string); break;

			case 2: return hash('sha256', $string); break;

			case 3: return hash('sha512', $string); break;

			case 4: return md5(md5($string)); break;

			case 5: return md5($string.$salt); break; // Joomla

			case 6: return md5($salt.$string); break; // osCommerce, TBDev

			case 7: return md5(md5($salt).$string); break; // vBulletin, IceBB, Discuz

			case 8: return md5(md5($string).$salt); break;

			case 9: return md5($string.md5($salt)); break;

			case 10: return md5($salt.md5($string)); break;

			case 11: return sha1($string.$salt); break;

			case 12: return sha1($salt.$string); break;

			case 13: return md5(md5($salt).md5($string)); break; // ipb, MyBB

			case 14: return hash('sha256', $string.$salt); break;

			case 15: return hash('sha512', $string.$salt); break;

			default: return md5($string); break;
		}
	}

	/**
	 * Подгрузчик модулей по умолчанию (прямой загрузчик без лишних проверок)
	 * @param String $mode - название модуля
	 */
	public function load_def_mode($mode){
		
		include_once(MCR_MODE_PATH.$mode.".php");

		$module = new module($this);

		return $module->content();
	}

	/**
	 * Загрузчик блоков боковой панели
	 * @return Buffer string or false
	 */
	public function load_def_blocks(){
		$list = scandir(MCR_SIDE_PATH);

		if(empty($list)){ return false;; }

		ob_start();

		foreach($list as $key => $file){
			if($file=='.' || $file=='..' || substr($file, -4)!='.php'){ continue; }

			include_once(MCR_SIDE_PATH.$file);

		}

		return ob_get_clean();
	}

	/**
	 * Загрузка статической страницы
	 * @param String $path - путь к файлу
	 * @param Array $data - параметры, передаваемые через массив
	 * @return Buffer string
	 */
	public function sp($path, $data=array()){
		ob_start();
		
		include($path);

		return ob_get_clean();
	}

	/**
	 * Загрузка советов
	 * @return string
	 */
	public function advice(){

		if(!$this->config->func['advice']){ return ''; }

		$data = file(MCR_THEME_PATH."default_sp/advice.txt");
		$size = count($data);
		$sp_data["ADVICE"] = ($size<=0) ? $this->lng['e_advice_found'] : $data[rand(0, $size-1)];

		return $this->sp(MCR_THEME_PATH."default_sp/advice.html", $sp_data);
	}

	/**
	  * Поиск размеров скина или плаща по форматам
	  * @param $width - width of skin
	  * @param $height - height of skin
	  * @return key of format (integer) or false (boolean)
	  *
	  */
	public function find_in_formats($width, $height){
		foreach($this->core->get_array_formats() as $key => $value){
			if($value["skin_w"] == $width && $value["skin_h"] == $height){ return $key; }
		}

		return false;
	}

	/**
	 * Поворот изображения по заданым параметрам из исходного изображения
	 */
	public function imageflip(&$result, &$img, $rx = 0, $ry = 0, $x = 0, $y = 0, $size_x = null, $size_y = null){
		if($size_x < 1){
			$size_x = imagesx($img);
		}

		if($size_y < 1){
			$size_y = imagesy($img);
		}

		imagecopyresampled($result, $img, $rx, $ry, ($x + $size_x - 1), $y, $size_x, $size_y, 0 - $size_x, $size_y);
	}

	/**
	  * Получить массив доступных форматов скинов и плащей
	  * @param formats (array)
	  *
	  */
	public function get_array_formats(){

		$w = 64;
		$h = 32;

		$c_w = 22;
		$c_h = 17;

		$i = 1;

		$array = array();

		$skin_h = $h;
		$skin_w = $w;
		$cloak_w = $c_w;
		$cloak_h = $c_h;

		while($i<=32){

			$skin_w = $i*$w;
			$skin_h = $i*$h;

			$cloak_w = $i*$c_w;
			$cloak_h = $i*$c_h;

			$array[$i] = array(
				"skin_w" => $skin_w,
				"skin_h" => $skin_h,
				"cloak_w" => $cloak_w,
				"cloak_h" => $cloak_h
			);

			$i = ($i<2) ? $i+1 : $i+2;
		}

		return $array;
	}

	/**
	  * Отправка почты через PHPMailer
	  * @param String $to - кому
	  * @param String $subject - тема письма
	  * @param String $message - текст сообщения
	  * @param String $altmessage - альтернативное сообщение
	  * @param Boolean $smtp - отправка почты через SMTP
	  * @param Boolean $cc - отправлять копию письма
	  * @return Boolean
	  */
	public function send_mail($to, $subject='[WebMCR]', $message='', $altmassage='', $smtp=false, $cc=false){
		require(MCR_TOOL_PATH.'smtp/PHPMailerAutoload.php');

		PHPMailerAutoload('smtp');

		include_once(MCR_TOOL_PATH.'smtp/class.phpmailer.php');

		$mail = new PHPMailer;

		//$mail->SMTPDebug = 3;

		if($this->config->mail['smtp']){
			$mail->isSMTP();
			$mail->Host = $this->config->mail['smtp_host'];			// Specify main and backup SMTP servers
			$mail->SMTPAuth = true;									// Enable SMTP authentication
			$mail->Username = $this->config->mail['smtp_user'];		// SMTP username
			$mail->Password = $this->config->mail['smtp_pass'];		// SMTP password
			$mail->SMTPSecure = 'tls';								// Enable TLS encryption, `ssl` also accepted
			$mail->Port = 587;										// TCP port to connect to
		}

		$mail->CharSet = 'UTF-8';
		$mail->setLanguage('ru', MCR_TOOL_PATH.'smpt/language/');
		$mail->From = ($this->config->mail['smtp']) ? $this->config->mail['smtp_user'] : $this->config->mail['from'];
		$mail->FromName = $this->config->mail['from_name'];
		if(is_array($to)){
			foreach($to as $key => $value){ $mail->addAddress($value); }
		}else{
			$mail->addAddress($to);
		}
		
		$mail->addReplyTo($this->config->mail['reply'], $this->config->mail['reply_name']);
		if($this->config->mail['cc']){ $mail->addCC($this->config->mail['from']); }
		//$mail->addBCC($this->config->mail['bcc']);

		$mail->isHTML(true);										// Set email format to HTML

		$mail->Subject = $subject;
		$mail->Body    = $message;
		$mail->AltBody = $altmassage;

		return $mail->send();
	}

	public function captcha_check(){

		if(!isset($this->captcha[$this->config->main['captcha']])){ return true; }

		switch($this->config->main['captcha']){
			case 1:
				$response = @$_POST['g-recaptcha-response'];
				$request = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$this->config->main['rc_private']."&response=".$response."&remoteip=".$this->user->ip);
				$request = json_decode($request);

				if(!$request->success){ return false; }

				return true;
			break;

			case 2:
				$response = @$_POST['capcode'];
				require(MCR_TOOL_PATH.'libs/keycaptcha.php');
				$kc = new KeyCAPTCHA_CLASS('', $this);

				if(!$kc->check_result($response)){ return false; }

				return true;
			break;


			default: return true; break;
		}
	}

	public function captcha(){
		switch($this->config->main['captcha']){
			case 1: $content = $this->sp(MCR_THEME_PATH."captcha/recaptcha.html"); break;

			case 2: require(MCR_TOOL_PATH.'libs/keycaptcha.php'); $kc = new KeyCAPTCHA_CLASS('', $this);
				$data["CONTENT"] = $kc->render_js();
				$content = $this->sp(MCR_THEME_PATH."captcha/keycaptcha.html", $data);
			break;


			default: return; break;
		}

		return $content;
	}

	public function safestr($string=''){
		$string = trim(strip_tags($string));

		return $this->db->HSC($string);
	}

	public function filter_int_array($array){
		if(empty($array)){ return false; }

		$new_array = array();

		foreach($array as $key => $value){
			$new_array[] = intval($value);
		}

		return $new_array;
	}

	public function is_access($name=''){
		if(empty($name)){ return false; }

		if(!@$this->user->permissions_v2[$name]){ return false; }

		return true;
	}

	private function search_array($active = 'news'){
		if(empty($this->config->search)){ return; }

		ob_start();

		foreach($this->config->search as $key => $value){
			if(!$this->is_access($value['permissions'])){ continue; }

			$data = array(
				"ID" => $key,
				"TITLE" => $value['title'],
				"ACTIVE" => ($key==$active) ? 'active' : ''
			);

			echo $this->sp(MCR_THEME_MOD."search/elem-id.html", $data);
		}

		return ob_get_clean();
	}

	public function search(){
		if(!$this->is_access('sys_search')){ return; }

		$type = (isset($_GET['type'])) ? $_GET['type'] : 'news';

		$data['SEARCH_ELEMENTS'] = $this->search_array($type);

		return $this->sp(MCR_THEME_MOD."search/form.html", $data);
	}

	public function perm_list($selected=''){
		$query = $this->db->query("SELECT title, `value` FROM `mcr_permissions` ORDER BY title ASC");

		if(!$query || $this->db->num_rows($query)<=0){ return; }

		ob_start();

		while($ar = $this->db->fetch_assoc($query)){
			$title = $this->db->HSC($ar['title']);
			$value = $this->db->HSC($ar['value']);

			$select = ($value==$selected) ? 'selected' : '';

			echo "<option value=\"$value\" $select>$title</option>";
		}

		return ob_get_clean();
	}
	
	public function savecfg($cfg=array(), $file='main.php', $var='main'){

		if(empty($cfg)){ return false; }

		$filename = MCR_ROOT."configs/".$file;

		$txt  = '<?php'.PHP_EOL;
		$txt .= '$'.$var.' = '.var_export($cfg, true).';'.PHP_EOL;
		$txt .= '?>';

		if(file_exists($filename) && !is_writable($filename)){ return false; }

		$result = file_put_contents($filename, $txt);

		if (is_bool($result) and $result == false){return false;}

		return true;
	}

	public function validate_perm($perm){
		$perm = $this->db->safesql($perm);

		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_permissions` WHERE `value`='$perm'");
		if(!$query){ return false; }

		$ar = $this->db->fetch_array($query);

		return ($ar[0]<=0) ? false : true;
	}
}

?>