<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class skin{
	private $mp = 64;

	private $core, $lng, $db, $user;
	
	public function __construct($core, $obj){
		$this->core = $core;
		$this->user = $core->user;
		$this->db	= $core->db;
		$this->lng	= $core->lng;

		if(!is_writable(MCR_SKIN_PATH)){ $this->core->notify($this->lng['e_msg'], $this->lng['skin_e_folder_perm'], 2, '?mode=profile'); }
		if(!is_writable(MCR_SKIN_PATH.'interface/')){ $this->core->notify($this->lng['e_msg'], $this->lng['skin_e_intf_perm'], 2, '?mode=profile'); }

		$size = intval($obj['size']);
		$tmp = $obj['tmp_name'];

		switch(intval($obj['error'])){
			case 0: break;

			case 1:
			case 2: $this->core->notify($this->lng['e_msg'], $this->lng['skin_e_size'], 2, '?mode=profile'); break;

			case 3:
			case 4: $this->core->notify("", $this->lng['skin_e'], 2, '?mode=profile'); break;

			case 6: $this->core->notify($this->lng['e_msg'], $this->lng['skin_e_temp'], 2, '?mode=profile'); break;

			case 7: $this->core->notify($this->lng['e_msg'], $this->lng['skin_e_perm'], 2, '?mode=profile'); break;

			default: $this->core->notify("", $this->lng['skin_e_undefined'], 2, '?mode=profile'); break;
		}

		if(($size/1024)>$this->user->permissions->sys_max_file_size){ $this->core->notify($this->lng['e_msg'], $this->lng['skin_e_size'], 2, '?mode=profile'); }
		
		if(!file_exists($tmp)){ $this->core->notify($this->lng['e_msg'], $this->lng['skin_e_tempfile'], 2, '?mode=profile'); }

		if(substr($obj['name'], -4)!='.png'){ $this->core->notify($this->lng['e_msg'], $this->lng['skin_e_png'], 2, '?mode=profile'); }

		$get_size = @getimagesize($tmp);

		if(!$get_size){ $this->core->notify($this->lng['e_msg'], $this->lng['skin_e_png'], 2, '?mode=profile'); }

		$width = $get_size[0];
		$height = $get_size[1];

		if(!$this->is_skin_valid($get_size)){ $this->core->notify($this->lng['e_msg'], $this->lng['skin_e_format'], 2, '?mode=profile'); }

		$multiple = $width / $this->mp;

		// Create and save head of skin +
		$new_head = $this->create_head($tmp, $multiple);

		if($new_head===false){ $this->core->notify($this->lng['e_msg'], $this->lng['skin_e_png'], 2, '?mode=profile'); }

		imagepng($new_head, MCR_SKIN_PATH.'interface/'.$this->user->login.'_mini.png');
		// Create and save head of skin -

		// Create and save preview of skin +
		$new_preview = $this->create_preview($tmp, $multiple);

		if($new_preview===false){ $this->core->notify($this->lng['e_msg'], $this->lng['skin_e_png'], 2, '?mode=profile'); }

		imagepng($new_preview, MCR_SKIN_PATH.'interface/'.$this->user->login.'.png');
		// Create and save preview of skin -

		if(!copy($tmp, MCR_SKIN_PATH.$this->user->login.'.png')){ $this->core->notify("", $this->lng['skin_e_save'], 2, '?mode=profile'); }
		// Save new skin -
	}

	public function create_head($path, $multiple=1, $size=151){

		$image = @imagecreatefrompng($path);

		if(!$image){ return false; }

		$new = imagecreatetruecolor($size, $size);

		imagecopyresized($new, $image, 0, 0, 8 * $multiple, 8 * $multiple, $size, $size, 8 * $multiple, 8 * $multiple);
		imagecopyresized($new, $image, 0, 0, 40 * $multiple, 8 * $multiple, $size, $size, 8 * $multiple, 8 * $multiple);

		imagedestroy($image);

		return $new;
	}

	public function create_preview($path, $multiple=1, $size=224){

		$image = @imagecreatefrompng($path);

		if(!$image){ return false; }

		$preview = imagecreatetruecolor(32 * $multiple, 32 * $multiple);

		$mp_x_h = imagesx($preview) / 2;

		$transparent = imagecolorallocatealpha($preview, 255, 255, 255, 127);

		imagefill($preview, 0, 0, $transparent);

		// Front skin
		imagecopy($preview, $image, 4 * $multiple, 0 * $multiple, 8 * $multiple, 8 * $multiple, 8 * $multiple, 8 * $multiple);
		imagecopy($preview, $image, 0 * $multiple, 8 * $multiple, 44 * $multiple, 20 * $multiple, 4 * $multiple, 12 * $multiple);
		$this->core->imageflip($preview, $image, 12 * $multiple, 8 * $multiple, 44 * $multiple, 20 * $multiple, 4 * $multiple, 12 * $multiple);
		imagecopy($preview, $image, 4 * $multiple, 8 * $multiple, 20 * $multiple, 20 * $multiple, 8 * $multiple, 12 * $multiple);
		imagecopy($preview, $image, 4 * $multiple, 20 * $multiple, 4 * $multiple, 20 * $multiple, 4 * $multiple, 12 * $multiple);
		$this->core->imageflip($preview, $image, 8 * $multiple, 20 * $multiple, 4 * $multiple, 20 * $multiple, 4 * $multiple, 12 * $multiple);
		imagecopy($preview, $image, 4 * $multiple, 0 * $multiple, 40 * $multiple, 8 * $multiple, 8 * $multiple, 8 * $multiple);

		// Back skin
		imagecopy($preview, $image, $mp_x_h + 4 * $multiple, 8 * $multiple, 32 * $multiple, 20 * $multiple, 8 * $multiple, 12 * $multiple);
		imagecopy($preview, $image, $mp_x_h + 4 * $multiple, 0 * $multiple, 24 * $multiple, 8 * $multiple, 8 * $multiple, 8 * $multiple);
		$this->core->imageflip($preview, $image, $mp_x_h + 0 * $multiple, 8 * $multiple, 52 * $multiple, 20 * $multiple, 4 * $multiple, 12 * $multiple);
		imagecopy($preview, $image, $mp_x_h + 12 * $multiple, 8 * $multiple, 52 * $multiple, 20 * $multiple, 4 * $multiple, 12 * $multiple);
		$this->core->imageflip($preview, $image, $mp_x_h + 4 * $multiple, 20 * $multiple, 12 * $multiple, 20 * $multiple, 4 * $multiple, 12 * $multiple);
		imagecopy($preview, $image, $mp_x_h + 8 * $multiple, 20 * $multiple, 12 * $multiple, 20 * $multiple, 4 * $multiple, 12 * $multiple);
		imagecopy($preview, $image, $mp_x_h + 4 * $multiple, 0 * $multiple, 56 * $multiple, 8 * $multiple, 8 * $multiple, 8 * $multiple);


		$fullsize = imagecreatetruecolor($size, $size);

		imagesavealpha($fullsize, true);

		$transparent = imagecolorallocatealpha($fullsize, 255, 255, 255, 127);

		imagefill($fullsize, 0, 0, $transparent);

		imagecopyresized($fullsize, $preview, 0, 0, 0, 0, imagesx($fullsize), imagesy($fullsize), imagesx($preview), imagesy($preview));

		imagedestroy($preview);
		imagedestroy($image);

		return $fullsize;
	}

	/**
	  * Валидация формата изображения
	  * @param $tmp - путь к изображению
	  * @return boolean
	  * - Проверяет права на максимальный размер изображения
	  */
	public function is_skin_valid($size){
		$formats = $this->core->get_array_formats();

		$max_ratio = $this->user->permissions->sys_max_ratio;

		if($max_ratio<=0){ return false; }

		$width = $formats[$max_ratio]["skin_w"];
		$height = $formats[$max_ratio]["skin_h"];

		if($size[0]>$width || $size[1]>$height){ return false; }

		if($width<64 || $height<32){ return false; }

		if($width%$height != 0){ return false; }

		if($size[0]/$size[1] != 2 && $size[0]/$size[1] != 1){ return false; }

		return true;
	}
	
}

?>
