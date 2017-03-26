<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class cloak{
	private $mp = 16;

	private $core, $lng, $db, $user, $cfg;
	
	public function __construct($core, $obj){
		$this->core		= $core;
		$this->user		= $core->user;
		$this->db		= $core->db;
		$this->lng		= $core->lng;
		$this->cfg		= $core->cfg;

		if(!is_writable(MCR_CLOAK_PATH)){ $this->core->notify($this->lng['e_msg'], $this->lng['cloak_e_folder_perm'], 2, '?mode=profile'); }

		$size = intval($obj['size']);
		$tmp = $obj['tmp_name'];

		switch(intval($obj['error'])){
			case 0: break;

			case 1:
			case 2: $this->core->notify($this->lng['e_msg'], $this->lng['cloak_e_size'], 2, '?mode=profile'); break;

			case 3:
			case 4: $this->core->notify("", $this->lng['cloak_e'], 2, '?mode=profile'); break;

			case 6: $this->core->notify($this->lng['e_msg'], $this->lng['cloak_e_temp'], 2, '?mode=profile'); break;

			case 7: $this->core->notify($this->lng['e_msg'], $this->lng['cloak_e_perm'], 2, '?mode=profile'); break;

			default: $this->core->notify("", $this->lng['cloak_e_undefined'], 2, '?mode=profile'); break;
		}

		if(($size/1024)>$this->user->permissions->sys_max_file_size){ $this->core->notify($this->lng['e_msg'], $this->lng['cloak_e_size'], 2, '?mode=profile'); }
		
		if(!file_exists($tmp)){
			$this->core->notify($this->lng['e_msg'], $this->lng['cloak_e_tempfile'], 2, '?mode=profile');
		}

		if(substr($obj['name'], -4)!='.png'){ $this->core->notify($this->lng['e_msg'], $this->lng['cloak_e_png'], 2, '?mode=profile'); }

		$get_size = @getimagesize($tmp);

		if(!$get_size){ $this->core->notify($this->lng['e_msg'], $this->lng['cloak_e_png'], 2, '?mode=profile'); }

		$width = $get_size[0];
		$height = $get_size[1];

		if(!$this->is_cloak_valid($get_size)){ $this->core->notify($this->lng['e_msg'], $this->lng['cloak_e_format'], 2, '?mode=profile'); }

		// Resave head of skin +
		if(!file_exists(MCR_SKIN_PATH.'interface/'.$this->user->login.'_mini.png')){
			if(!copy(MCR_SKIN_PATH.'interface/default_mini.png', MCR_SKIN_PATH.'interface/'.$this->user->login.'_mini.png')){ $this->core->notify("", $this->lng['cloak_e_save'], 2, '?mode=profile'); }
		}
		// Resave head of skin -

		// Create and save preview of cloak +
		$new_preview = $this->create_preview($tmp);

		if($new_preview===false){ $this->core->notify($this->lng['e_msg'], $this->lng['cloak_e_png'], 2, '?mode=profile'); }

		imagepng($new_preview, MCR_SKIN_PATH.'interface/'.$this->user->login.'.png');
		// Create and save preview of cloak -

		// Save new cloak +
		if(!copy($tmp, MCR_CLOAK_PATH.$this->user->login.'.png')){ $this->core->notify("", $this->lng['cloak_e_save'], 2, '?mode=profile'); }
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

		$image = @imagecreatefrompng($path);

		if(!$image){ return false; }

		if(file_exists(MCR_SKIN_PATH.$this->user->skin.'.png')){
			$skin_path = MCR_SKIN_PATH.$this->user->skin.'.png';
		}else{
			$skin_path = MCR_SKIN_PATH.'default.png';
		}

		$skin = @imagecreatefrompng($skin_path);

		$skin_size = @getimagesize($skin_path);
		$cloak_size = @getimagesize($path);

		$multiple = $skin_size[0] / 64;
		$size_x = 32;
		$preview = imagecreatetruecolor($size_x * $multiple, 32 * $multiple);
		$mp_x_h = imagesx($preview) / 2;

		$transparent = imagecolorallocatealpha($preview, 255, 255, 255, 127);
		imagefill($preview, 0, 0, $transparent);

		imagecopy($preview, $skin, 4 * $multiple, 0 * $multiple, 8 * $multiple, 8 * $multiple, 8 * $multiple, 8 * $multiple);
		imagecopy($preview, $skin, 0 * $multiple, 8 * $multiple, 44 * $multiple, 20 * $multiple, 4 * $multiple, 12 * $multiple);
		$this->core->imageflip($preview, $skin, 12 * $multiple, 8 * $multiple, 44 * $multiple, 20 * $multiple, 4 * $multiple, 12 * $multiple);
		imagecopy($preview, $skin, 4 * $multiple, 8 * $multiple, 20 * $multiple, 20 * $multiple, 8 * $multiple, 12 * $multiple);
		imagecopy($preview, $skin, 4 * $multiple, 20 * $multiple, 4 * $multiple, 20 * $multiple, 4 * $multiple, 12 * $multiple);
		$this->core->imageflip($preview, $skin, 8 * $multiple, 20 * $multiple, 4 * $multiple, 20 * $multiple, 4 * $multiple, 12 * $multiple);
		imagecopy($preview, $skin, 4 * $multiple, 0 * $multiple, 40 * $multiple, 8 * $multiple, 8 * $multiple, 8 * $multiple);

		imagecopy($preview, $skin, $mp_x_h + 4 * $multiple, 8 * $multiple, 32 * $multiple, 20 * $multiple, 8 * $multiple, 12 * $multiple);
		imagecopy($preview, $skin, $mp_x_h + 4 * $multiple, 0 * $multiple, 24 * $multiple, 8 * $multiple, 8 * $multiple, 8 * $multiple);
		$this->core->imageflip($preview, $skin, $mp_x_h + 0 * $multiple, 8 * $multiple, 52 * $multiple, 20 * $multiple, 4 * $multiple, 12 * $multiple);
		imagecopy($preview, $skin, $mp_x_h + 12 * $multiple, 8 * $multiple, 52 * $multiple, 20 * $multiple, 4 * $multiple, 12 * $multiple);
		$this->core->imageflip($preview, $skin, $mp_x_h + 4 * $multiple, 20 * $multiple, 12 * $multiple, 20 * $multiple, 4 * $multiple, 12 * $multiple);
		imagecopy($preview, $skin, $mp_x_h + 8 * $multiple, 20 * $multiple, 12 * $multiple, 20 * $multiple, 4 * $multiple, 12 * $multiple);
		imagecopy($preview, $skin, $mp_x_h + 4 * $multiple, 0 * $multiple, 56 * $multiple, 8 * $multiple, 8 * $multiple, 8 * $multiple);
		
		$mp_x = ($this->cfg->main['hd_cloaks']) ? 64 : 22;

		$multiple_c = $cloak_size[0] / $mp_x;

		$mp_x_h = ($multiple_c > $multiple) ? ($size_x * $multiple_c) / 2 : ($size_x * $multiple) / 2;
		$mp_result = ($multiple_c > $multiple) ? $multiple_c : $multiple;

		$preview_cloak = imagecreatetruecolor($size_x * $mp_result, 32 * $mp_result);
		$transparent = imagecolorallocatealpha($preview_cloak, 255, 255, 255, 127);
		imagefill($preview_cloak, 0, 0, $transparent);

		imagecopyresized(
			$preview_cloak, // result image
			$image, // source image
			round(3 * $mp_result), // start x point of result
			round(8 * $mp_result), // start y point of result
			round(12 * $multiple_c), // start x point of source img
			round(1 * $multiple_c), // start y point of source img
			round(10 * $mp_result), // result <- width ->
			round(16 * $mp_result), // result /|\ height \|/
			round(10 * $multiple_c), // width of cloak img (from start x \ y) 
			round(16 * $multiple_c) // height of cloak img (from start x \ y) 
		);

		imagecopyresized($preview_cloak, $preview, 0, 0, 0, 0, imagesx($preview_cloak), imagesy($preview_cloak), imagesx($preview), imagesy($preview));

		imagecopyresized(
			$preview_cloak,
			$image,
			$mp_x_h + 3 * $mp_result,
			round(8 * $mp_result),
			round(1 * $multiple_c),
			round(1 * $multiple_c),
			round(10 * $mp_result),
			round(16 * $mp_result),
			round(10 * $multiple_c),
			round(16 * $multiple_c)
		);

		$fullsize = imagecreatetruecolor($size, $size);

		imagesavealpha($fullsize, true);
		$transparent = imagecolorallocatealpha($fullsize, 255, 255, 255, 127);
		imagefill($fullsize, 0, 0, $transparent);

		imagecopyresized($fullsize, $preview_cloak, 0, 0, 0, 0, imagesx($fullsize), imagesy($fullsize), imagesx($preview_cloak), imagesy($preview_cloak));

		imagedestroy($preview);
		imagedestroy($preview_cloak);
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
		$formats = $this->core->get_array_formats($this->cfg->main['hd_cloaks']);

		$max_ratio = $this->user->permissions->sys_max_ratio;

		if($max_ratio<=0){ return false; }

		$width = $formats[$max_ratio]["cloak_w"];
		$height = $formats[$max_ratio]["cloak_h"];

		if($size[0]>$width || $size[1]>$height){ return false; }

		if(!$this->cfg->main['hd_cloaks']){
			if($width<22 || $height<17){ return false; }
			if(round($size[0]/$size[1], 2) != 1.29){ return false; }
		}else{
			if($width<64 || $height<32){ return false; }
			if(round($size[0]/$size[1], 2) != 2){ return false; }
		}

		return true;
	}
	
}

?>
