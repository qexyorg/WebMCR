<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class cloak{
	private $mp = 22;

	private $core, $lng, $db, $user;
	
	public function __construct($core, $obj){
		$this->core = $core;
		$this->user = $core->user;
		$this->db	= $core->db;
		$this->lng	= $core->lng;

		if(!is_writable(MCR_CLOAK_PATH)){ $this->core->notify($this->lng['e_msg'], $this->lng['e_load_cloak_folder'], 2, '?mode=profile'); }

		$size = intval($obj['size']);
		$tmp = $obj['tmp_name'];

		switch(intval($obj['error'])){
			case 0: break;

			case 1:
			case 2: $this->core->notify($this->lng['e_msg'], $this->lng['e_load_size'], 2, '?mode=profile'); break;

			case 3:
			case 4: $this->core->notify("", $this->lng['e_load_cloak'], 2, '?mode=profile'); break;

			case 6: $this->core->notify($this->lng['e_msg'], $this->lng['e_load_temp'], 2, '?mode=profile'); break;

			case 7: $this->core->notify($this->lng['e_msg'], $this->lng['e_load_perm'], 2, '?mode=profile'); break;

			default: $this->core->notify("", $this->lng['e_load_undefined'], 2, '?mode=profile'); break;
		}

		if(($size/1024)>$this->user->permissions->sys_max_file_size){ $this->core->notify($this->lng['e_msg'], $this->lng['e_load_size'], 2, '?mode=profile'); }
		
		if(!file_exists($tmp)){
			$this->core->notify($this->lng['e_msg'], $this->lng['e_load_tempfile'], 2, '?mode=profile');
		}

		if(substr($obj['name'], -4)!='.png'){ $this->core->notify($this->lng['e_msg'], $this->lng['e_load_png'], 2, '?mode=profile'); }

		$get_size = @getimagesize($tmp);

		if(!$get_size){ $this->core->notify($this->lng['e_msg'], $this->lng['e_load_png'], 2, '?mode=profile'); }

		$width = $get_size[0];
		$height = $get_size[1];

		if(!$this->is_cloak_valid($get_size)){ $this->core->notify($this->lng['e_msg'], $this->lng['e_load_format'], 2, '?mode=profile'); }

		// Resave head of skin +
		if(!file_exists(MCR_SKIN_PATH.'interface/'.$this->user->login.'_mini.png')){
			if(!copy(MCR_SKIN_PATH.'interface/.default_mini.png', MCR_SKIN_PATH.'interface/'.$this->user->login.'_mini.png')){ $this->core->notify("", $this->lng['e_load_save'], 2, '?mode=profile'); }
		}
		// Resave head of skin -

		// Create and save preview of cloak +
		$new_preview = $this->create_preview($tmp);

		if($new_preview===false){ $this->core->notify($this->lng['e_msg'], $this->lng['e_load_png'], 2, '?mode=profile'); }

		imagepng($new_preview, MCR_SKIN_PATH.'interface/'.$this->user->login.'.png');
		// Create and save preview of cloak -

		// Save new cloak +
		if(!file_exists(MCR_CLOAK_PATH.$this->user->login.'.png')){
			if(!copy($tmp, MCR_CLOAK_PATH.$this->user->login.'.png')){ $this->core->notify("", $this->lng['e_load_save'], 2, '?mode=profile'); }
		}
		// Save new cloak -
	}

	/**
	  * Создание миниатюры
	  * @param $path - путь к изображению
	  * @param $size - размер
	  * @return resource
	  */
	public function create_preview($path, $size=224){
		//header('Content-Type: image/png');
		$size_x = 32;

		$image = @imagecreatefrompng($path);

		if(!$image){ return false; }

		if(file_exists(MCR_SKIN_PATH.'interface/'.$this->user->skin.'.png')){
			$skin_path_int = MCR_SKIN_PATH.'interface/'.$this->user->skin.'.png';
			$skin_path = MCR_SKIN_PATH.$this->user->skin.'.png';
		}else{
			$skin_path_int = MCR_SKIN_PATH.'interface/.default.png';
			$skin_path = MCR_SKIN_PATH.'.default.png';
		}

		$skin_string = file_get_contents($skin_path_int);

		$skin = imagecreatefromstring($skin_string);

		$skin_size = @getimagesize($skin_path);

		$multiple = $skin_size[0] / 64;
		
		$mp_x_h = ($this->mp > $multiple) ? ($size_x * $this->mp) / 2 : ($size_x * $multiple) / 2;
		$mp_result = ($this->mp > $multiple) ? $this->mp : $multiple;

		$preview = imagecreatetruecolor($size_x * $mp_result, 32 * $mp_result);
		$transparent = imagecolorallocatealpha($preview, 255, 255, 255, 127);
		imagefill($preview, 0, 0, $transparent);

		imagecopyresized(
			$preview, // result image
			$image, // source image
			round(3 * $mp_result), // start x point of result
			round(8 * $mp_result), // start y point of result
			round(12 * $multiple), // start x point of source img
			round(1 * $multiple), // start y point of source img
			round(10 * $mp_result), // result <- width ->
			round(16 * $mp_result), // result /|\ height \|/
			round(10 * $multiple), // width of cloak img (from start x \ y) 
			round(16 * $multiple) // height of cloak img (from start x \ y) 
		);

		imagecopyresized($preview, $skin, 0, 0, 0, 0, imagesx($preview), imagesy($preview), imagesx($skin), imagesy($skin));

		imagecopyresized(
			$preview,
			$image,
			$mp_x_h + 3 * $mp_result,
			round(8 * $mp_result),
			round(1 * $multiple),
			round(1 * $multiple),
			round(10 * $mp_result),
			round(16 * $mp_result),
			round(10 * $multiple),
			round(16 * $multiple)
		);

		$fullsize = imagecreatetruecolor($size, $size);

		imagesavealpha($fullsize, true);
		$transparent = imagecolorallocatealpha($fullsize, 255, 255, 255, 127);
		imagefill($fullsize, 0, 0, $transparent);

		imagecopyresized($fullsize, $preview, 0, 0, 0, 0, imagesx($fullsize), imagesy($fullsize), imagesx($preview), imagesy($preview));

		imagedestroy($preview);
		imagedestroy($image);
		imagedestroy($skin);

		return $fullsize;
	}

	/**
	  * Валидация формата изображения
	  * @param $tmp - путь к изображению
	  * @return boolean
	  * - Проверяет права на максимальный размер изображения
	  */
	public function is_cloak_valid($size){
		$formats = $this->core->get_array_formats();

		$max_ratio = $this->user->permissions->max_ratio;

		if($max_ratio<=0){ return false; }

		$width = $formats[$max_ratio]["cloak_w"];
		$height = $formats[$max_ratio]["cloak_h"];

		/*
		if($this->is_skin){
			if($size[0]!=22 || $size[1]!=17){ return false; }
		}else{
			$skin_size = @getimagesize(MCR_UPL_PATH.'skins/'.$this->user->login.'.png');
			$key = $this->core->find_in_formats($skin_size[0], $skin_size[1]);
			if(!$key){ return false; }
			if($formats[$key]["cloak_w"]!==$width || $formats[$key]["cloak_h"]!==$height){ return false; }
		}
		*/

		if($size[0]>$width || $size[1]>$height){ return false; }

		if($width<22 || $height<17){ return false; }

		if(round($size[0]/$size[1], 2) != 1.29){ return false; }

		return true;
	}
	
}

?>