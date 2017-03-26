<?php

if(!defined('MCR')){ exit('Hacking Attempt!'); }

class install{
	public $cfg = array();
	public $lng = array();

	public $title = '';
	public $header = '';

	public function __construct(){
		$https = (@$_SERVER['HTTPS']=='on' || $_SERVER['HTTP_X_FORWARDED_PROTO']=='https') ? 'https' : 'http';

		define('PROGNAME', 'WebMCR Reloaded');
		define('VERSION', 'WebMCR Beta 1.4.1');
		define('FEEDBACK', '<a href="http://webmcr.com" target="_blank">'.PROGNAME.'</a> &copy; 2013-'.date("Y").' Qexy');
		define('URL_ROOT', str_replace('\\', '/', dirname(dirname($_SERVER['PHP_SELF']))));
		define('URL_ROOT_FULL', $https.'://'.$_SERVER['SERVER_NAME'].'/');
		define('URL_INSTALL', $https.'://'.$_SERVER['SERVER_NAME'].'/install/');
		define('DIR_ROOT', dirname(dirname(__FILE__)).'/');
		define('DIR_INSTALL', dirname(__FILE__).'/');
		define('DIR_INSTALL_THEME', DIR_INSTALL.'theme/');

		require_once(DIR_ROOT.'configs/main.php');

		$this->cfg['main'] = $main;

		require_once(DIR_ROOT.'configs/db.php');

		$this->cfg['db'] = $db;

		require_once(DIR_ROOT.'configs/mail.php');

		$this->cfg['mail'] = $mail;

		require_once(DIR_ROOT.'language/'.$main['s_lang'].'/install.php');

		$this->lng = $lng;

		$this->title = $lng['mod_name'];
	}

	public function HSC($string){
		return htmlspecialchars($string);
	}

	public function sp($page, $data=array()){

		ob_start();

		include(DIR_INSTALL_THEME.$page);

		return ob_get_clean();
	}

	public function init_step(){

		$do = (isset($_GET['do'])) ? $_GET['do'] : 'start';

		if(!preg_match("/^[\w\.]+$/i", $do)){ return 'Hacking Attempt'; }

		$modpath = DIR_INSTALL.'modules/'.$do.'.php';

		if(!file_exists($modpath)){ return 'Module not found'; }

		require_once($modpath);

		$module = new module($this);

		return $module->content();
	}

	public function notify($text='', $title='', $url=''){
		$url = URL_ROOT.$url;

		$_SESSION['notify_title'] = $title;
		$_SESSION['notify_text'] = $text;

		header("Location: $url");

		exit();
	}

	public function get_notify(){
		if(!isset($_SESSION['notify_title']) || !isset($_SESSION['notify_text'])){ return; }

		if(empty($_SESSION['notify_title']) && empty($_SESSION['notify_text'])){ unset($_SESSION['notify_title']); unset($_SESSION['notify_text']); return; }

		$data = array(
			'TITLE' => $_SESSION['notify_title'],
			'TEXT' => $_SESSION['notify_text'],
		);

		unset($_SESSION['notify_title']);
		unset($_SESSION['notify_text']);

		return $this->sp('notify.html', $data);
	}

	public function savecfg($cfg=array(), $file='main.php', $var='main'){

		if(!is_array($cfg) || empty($cfg)){ return false; }

		$filename = DIR_ROOT.'configs/'.$file;

		$txt  = '<?php'.PHP_EOL;
		$txt .= '$'.$var.' = '.var_export($cfg, true).';'.PHP_EOL;
		$txt .= '?>';

		$result = file_put_contents($filename, $txt);

		if($result === false){ return false; }

		return true;
	}

	public function gen_password($string='', $salt='', $crypt=false){
		if($crypt===false){ $crypt = $this->cfg['main']['crypt']; }

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

	public function logintouuid($string){
		$string = "OfflinePlayer:".$string;
		$val = md5($string, true);
		$byte = array_values(unpack('C16', $val));

		$tLo = ($byte[0] << 24) | ($byte[1] << 16) | ($byte[2] << 8) | $byte[3];
		$tMi = ($byte[4] << 8) | $byte[5];
		$tHi = ($byte[6] << 8) | $byte[7];
		$csLo = $byte[9];
		$csHi = $byte[8] & 0x3f | (1 << 7);

		if (pack('L', 0x6162797A) == pack('N', 0x6162797A)) {
			$tLo = (($tLo & 0x000000ff) << 24) | (($tLo & 0x0000ff00) << 8) | (($tLo & 0x00ff0000) >> 8) | (($tLo & 0xff000000) >> 24);
			$tMi = (($tMi & 0x00ff) << 8) | (($tMi & 0xff00) >> 8);
			$tHi = (($tHi & 0x00ff) << 8) | (($tHi & 0xff00) >> 8);
		}

		$tHi &= 0x0fff;
		$tHi |= (3 << 12);

		$uuid = sprintf(
			'%08x-%04x-%04x-%02x%02x-%02x%02x%02x%02x%02x%02x',
			$tLo, $tMi, $tHi, $csHi, $csLo,
			$byte[10], $byte[11], $byte[12], $byte[13], $byte[14], $byte[15]
		);
		return $uuid;
	}

	public function ip(){

		if(!empty($_SERVER['HTTP_CF_CONNECTING_IP'])){
			$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
		}elseif(!empty($_SERVER['HTTP_X_REAL_IP'])){
			$ip = $_SERVER['HTTP_X_REAL_IP'];
		}elseif(!empty($_SERVER['HTTP_CLIENT_IP'])){
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}else{
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return mb_substr($ip, 0, 16, "UTF-8");
	}

	public function random($length=10, $safe = true) {
		$chars	= "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
		if(!$safe){ $chars .= '$()#@!'; }

		$string	= "";

		$len	= strlen($chars) - 1;  
		while(strlen($string) < $length){
			$string .= $chars[mt_rand(0,$len)];  
		}

		return $string;
	}
}



?>