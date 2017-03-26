<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $install, $cfg, $lng, $methods;

	public function __construct($install){
		$this->install		= $install;
		$this->cfg			= $install->cfg;
		$this->lng			= $install->lng;

		$this->methods = array('MD5', 'SHA1', 'SHA256', 'SHA512', 'Double MD5 [ md5(md5(PASS)) ]', 'Salted MD5 [ md5(PASS+SALT) ]',
								'Salted MD5 [ md5(SALT+PASS) ]', 'Salted Double MD5 [ md5(md5(SALT)+PASS) ]', 'Salted Double MD5 [ md5(md5(PASS)+SALT) ]',
								'Salted Double MD5 [ md5(PASS+md5(SALT)) ]', 'Salted Double MD5 [ md5(SALT+md5(PASS)) ]', 'Salted SHA1 [ sha1(PASS+SALT) ]',
								'Salted SHA1 [ sha1(SALT+PASS) ]', 'Triple salted MD5 [ md5(md5(SALT)+md5(PASS)) ]', 'Salted SHA256 [ sha256(PASS+SALT) ]',
								'Salted SHA512 [ sha512(PASS+SALT) ]');

		$this->install->title = $this->lng['mod_name'].' — '.$this->lng['step_2'];
	}

	private function encrypt_methods($selected=0){

		ob_start();

		foreach($this->methods as $key => $title){
			$select = ($key==$selected) ? 'selected' : '';
			echo '<option value="'.$key.'" '.$select.'>'.$title.'</option>';
		}

		return ob_get_clean();
	}

	public function content(){
		if(!isset($_SESSION['step_1'])){ $this->install->notify('', '', 'install/?do=step_1'); }
		if(isset($_SESSION['step_2'])){ $this->install->notify('', '', 'install/?do=step_3'); }

		$time = time();

		$_SESSION['f_login'] = (isset($_POST['login'])) ? $this->install->HSC(@$_POST['login']) : 'admin';

		$_SESSION['f_adm_pass'] = @$_POST['password'];

		$_SESSION['f_repass'] = $this->install->HSC(@$_POST['repassword']);

		$_SESSION['f_email'] = (isset($_POST['email'])) ? $this->install->HSC(@$_POST['email']) : 'admin@'.$_SERVER['SERVER_NAME'];

		$method = intval(@$_POST['method']);

		if($_SERVER['REQUEST_METHOD']=='POST'){

			if(!preg_match("/^[\w\-]{3,}$/i", @$_POST['login'])){
				$this->install->notify($this->lng['e_login_format'], $this->lng['e_msg'], 'install/?do=step_2');
			}

			if(mb_strlen(@$_POST['password'], "UTF-8")<6){
				$this->install->notify($this->lng['e_pass_len'], $this->lng['e_msg'], 'install/?do=step_2');
			}

			if(@$_POST['password'] !== @$_POST['repassword']){
				$this->install->notify($this->lng['e_pass_match'], $this->lng['e_msg'], 'install/?do=step_2');
			}

			if(!filter_var(@$_POST['email'], FILTER_VALIDATE_EMAIL)){
				$this->install->notify($this->lng['e_email_format'], $this->lng['e_msg'], 'install/?do=step_2');
			}

			if(!isset($this->methods[$method])){ $this->install->notify($this->lng['e_method'], $this->lng['e_msg'], 'install/?do=step_2'); }

			$this->cfg['main']['crypt'] = $method;

			if(!$this->install->savecfg($this->cfg['main'], 'main.php', 'main')){
				$this->install->notify($this->lng['e_settings'], $this->lng['e_msg'], 'install/?do=step_2');
			}

			require_once(DIR_ROOT.'engine/db/'.$this->cfg['db']['backend'].'.class.php');

			$db = new db($this->cfg['db']['host'], $this->cfg['db']['user'], $this->cfg['db']['pass'], $this->cfg['db']['base'], $this->cfg['db']['port']);

			$error = $db->error();

			if(!empty($error)){
				$this->install->notify($this->lng['e_connection'].' | '.$db->error(), $this->lng['e_msg'], 'install/?do=step_2');
			}

			$login		= $db->safesql(@$_POST['login']);
			$email		= $db->safesql(@$_POST['email']);

			$salt		= $db->safesql($this->install->random());
			$password	= $this->install->gen_password(@$_POST['password'], $salt, $method);
			$uuid		= $db->safesql($this->install->logintouuid(@$_POST['login']));
			$ip			= $this->install->ip();

			$ctables	= $this->cfg['db']['tables'];

			$ic_f		= $ctables['iconomy']['fields'];
			$us_f		= $ctables['users']['fields'];

			$query = $db->query("INSERT INTO `{$ctables['users']['name']}`
										(`{$us_f['group']}`, `{$us_f['login']}`, `{$us_f['email']}`, `{$us_f['pass']}`, `{$us_f['uuid']}`, `{$us_f['salt']}`, `{$us_f['ip_create']}`, `{$us_f['ip_last']}`, `{$us_f['date_reg']}`, `{$us_f['date_last']}`, `{$us_f['fname']}`, `{$us_f['lname']}`)
									VALUES
										('3', '$login', '$email', '$password', '$uuid', '$salt', '$ip', '$ip', '$time', '$time', '', '')");

			if(!$query){ $this->install->notify($this->lng['e_add_admin'], $this->lng['e_msg'], 'install/?do=step_2'); }

			$query = $db->query("INSERT INTO `{$ctables['iconomy']['name']}`
										(`{$ic_f['login']}`, `{$ic_f['money']}`, `{$ic_f['rm']}`, `{$ic_f['bank']}`)
									VALUES
										('$login', 0, 0, 0)");

			if(!$query){ $this->install->notify($this->lng['e_add_economy'], $this->lng['e_msg'], 'install/?do=step_2'); }

			$_SESSION['step_2'] = true;

			$this->install->notify('', '', 'install/?do=step_3');

		}

		$data = array(
			'METHODS' => $this->encrypt_methods($method),
		);

		return $this->install->sp('step_2.html', $data);
	}

}

?>