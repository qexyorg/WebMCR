<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

/**
 *
 * @author Qexy
 *
 * @contact admin@qexy.org
 *
 * @version 1.0.0
 *
 * @copyright Qexy.org
 *
 * @license https://github.com/qexyorg/BB-Code-Parser/blob/master/LICENSE
 *
 */

class bbcode{

	// URL до папки со смайликами
	private $smiles_url = '/qx_upload/api/smiles/';

	private $core;

	public function __construct($core){
		$this->core		= $core;
		$this->smiles_url = UPLOAD_URL.'smiles/';
	}

	public function get_smiles_bb(){
		include(MCR_TOOL_PATH.'libs/smiles.php');

		ob_start();

		foreach($smiles as $key => $img){

			$data = array(
				"NAME" => $key,
				"IMG" => $img
			);

			echo $this->core->sp(MCR_THEME_PATH.'default_sp/smile-id.html', $data);
		}

		return ob_get_clean();
	}

	public function bb_panel($for=''){

		$data['PANEL_ID'] = $for;
		$data['SMILES'] = $this->get_smiles_bb();

		return $this->core->sp(MCR_THEME_PATH.'default_sp/bb-panel.html', $data);
	}

	// Шаблоны обычных ББ-кодов (рекурсивные)
	public $codes = array(
		'b' => array(
			'left_tag' => '<b>',
			'right_tag' => '</b>'
		),
		
		'i' => array(
			'left_tag' => '<i>',
			'right_tag' => '</i>'
		),
		
		'u' => array(
			'left_tag' => '<u>',
			'right_tag' => '</u>'
		),
		
		's' => array(
			'left_tag' => '<s>',
			'right_tag' => '</s>'
		),
		
		'code' => array(
			'left_tag' => '<div class="qxbb-code">',
			'right_tag' => '</div>'
		),
		
		'quote' => array(
			'left_tag' => '<div class="qxbb-quote">',
			'right_tag' => '</div>'
		),
		
		'offtop' => array(
			'left_tag' => '<span class="qxbb-offtop" title="Offtop">',
			'right_tag' => '</span>'
		),
		
		'spoiler' => array(
			'left_tag' => '<div class="qxbb-spoiler"><button type="button" class="btn qxbb-spoiler-btn">Спойлер</button><div class="qxbb-spoiler-body">',
			'right_tag' => '</div></div>'
		),
		
		'reverse' => array(
			'left_tag' => '<bdo class="qxbb-reverse" dir="rtl">',
			'right_tag' => '</bdo>'
		),
		
		'left' => array(
			'left_tag' => '<div class="qxbb-left">',
			'right_tag' => '</div>'
		),
		
		'center' => array(
			'left_tag' => '<div class="qxbb-center">',
			'right_tag' => '</div>'
		),
		
		'right' => array(
			'left_tag' => '<div class="qxbb-right">',
			'right_tag' => '</div>'
		),
	);

	// Шаблоны ББ-кодов с опциями и тонкими настройками регулярок (рекурсивные)
	private $codes_options = array(
		'quote' => array(
			'pattern' => '/\[(quote)\=\"((\w+) \| (\d{2}\.\d{2}\.\d{4} - \d{2}\:\d{2}\:\d{2}))\"\]((?:[^[]|(?R))*)\[\/quote\]/Usi',
			'replace' => '<div class="qxbb-quote"><div class="qxbb-quote-info">{2}</div>{5}</div>',
			'values' => array(1,2,5),
			'escapes' => false,
		),

		'code' => array(
			'pattern' => '/\[(code)\=\"(php|html|css|javascript)\"\]((?:[^[]|(?R))*)\[\/code\]/Usi',
			'replace' => '<div class="qxbb-code"><div class="qxbb-code-info">Тип: {2}</div>{3}</div>',
			'values' => array(1,2,3),
			'escapes' => 3,
		),

		'color' => array(
			'pattern' => '/\[(color)\=\"(\#[0-9a-f]{6})\"\]((?:[^[]|\[(?!\/?(color)\])|(?R))+)\[\/color\]/Usi',
			'replace' => '<font color="{2}" class="qxbb-color">{3}</font>',
			'values' => array(1,2,3),
			'escapes' => false,
		),

		'background' => array(
			'pattern' => '/\[(background)\=\"(\#[0-9a-f]{6})\"\]((?:[^[]|(?R))*)\[\/background\]/Usi',
			'replace' => '<font style="background-color:{2};" class="qxbb-background">{3}</font>',
			'values' => array(1,2,3),
			'escapes' => false,
		),

		'size' => array(
			'pattern' => '/\[(size)\=\"([1-7])\"\]((?:[^[]|(?R))*)\[\/size\]/Usi',
			'replace' => '<font size="{2}" class="qxbb-size">{3}</font>',
			'values' => array(1,2,3),
			'escapes' => false,
		),

		'font' => array(
			'pattern' => '/\[(font)\=\"(Arial|Arial Black|Comic Sans MS|Courier New|Georgia|Impact|Tahoma|Times New Roman|Trebuchet MS|Verdana)\"\]((?:[^[]|(?R))*)\[\/font\]/Usi',
			'replace' => '<font face="{2}" class="qxbb-font">{3}</font>',
			'values' => array(1,2,3),
			'escapes' => false,
		),

		'url' => array(
			'pattern' => '/\[(url)\=\"(http(s)?\:\/\/[\w\.\/\?\=\&amp\;\%\+\~\*\-]+)\"\]((?:[^[]|(?R))*)\[\/url\]/Usi',
			'replace' => '<a href="{2}" class="qxbb-url">{4}</a>',
			'values' => array(1,2,4),
			'escapes' => false,
		),

		'spoiler' => array(
			'pattern' => '/\[(spoiler)\=\"([\w\s\-\.\:\;\+\|\,]{1,32})\"\]((?:[^[]|(?R))*)\[\/spoiler\]/Usui',
			'replace' => '<div class="qxbb-spoiler"><button type="button" class="btn qxbb-spoiler-btn">{2}</button><div class="qxbb-spoiler-body">{3}</div></div>',
			'values' => array(1,2,3),
			'escapes' => false,
		),
	);

	// Шаблоны ББ-кодов с опциями и тонкими настройками регулярок (не рекурсивные)
	private $codes_once = array(
		'img' => array(
			'pattern' => '/\[img\](http(s)?\:\/\/[\w\.\/\?\=\&amp\;\%\+\~\*\-]+)\[\/img\]/Usi',
			'replace' => '<img src="$1" class="qxbb-img" alt="IMG" />',
		),

		'line' => array(
			'pattern' => '/\[line\]/Usi',
			'replace' => '<hr class="qxbb-line">',
		),

		'indent' => array(
			'pattern' => '/\[indent\]/Usi',
			'replace' => '<span class="qxbb-indent"></span>',
		),

		'url' => array(
			'pattern' => '/\[url\](http(s)?\:\/\/[\w\.\/\?\=\&amp\;\%\+\~\*\-]+)\[\/url\]/Usi',
			'replace' => '<a href="$1" class="qxbb-url">$1</a>',
		),

		'email' => array(
			'pattern' => '/\[email\]([\w\.\-]+\@[a-z0-9\.\-]+)\[\/email\]/Usi',
			'replace' => '<a href="mailto:$1" class="qxbb-email">$1</a>',
		),
	);

	// Шаблоны ББ-кодов для обработки тегов видео ([video]) с тонкими настройками регулярок (не рекурсивные)
	private $codes_video = array(
		'youtube' => array(
			'pattern' => '((youtube\.com\/watch\?v\=([\w\-]+))|(youtu\.be\/([\w\-]+))|(youtube.com\/embed\/([\w\-]+)))',
			'replace' => 'https://www.youtube.com/embed/$5',
		),

		'vk' => array(
			'pattern' => 'vk.com\/video_ext.php\?oid=(\d+)\&amp;id\=(\d+)\&amp;hash\=(\w+)',
			'replace' => 'http://vk.com/video_ext.php?oid=$3&id=$4&hash=$5',
		),

		'vimeo' => array(
			'pattern' => 'vimeo\.com\/(\d+)',
			'replace' => 'https://player.vimeo.com/video/$3',
		),

		'coub' => array(
			'pattern' => 'coub.com\/view\/(\w+)',
			'replace' => 'http://coub.com/embed/$3',
		),

		'twitch' => array(
			'pattern' => 'twitch\.tv\/(\w+)',
			'replace' => 'http://www.twitch.tv/$3/embed',
		),

		'vine' => array(
			'pattern' => 'vine\.co\/v\/(\w+)',
			'replace' => 'https://vine.co/v/$3/embed/simple',
		),
	);

	// Обработчик смайликов
	private function parse_smiles($text){

		$smile_list = $smile_replace = array();

		include(MCR_TOOL_PATH.'libs/smiles.php');

		foreach($smiles as $key => $val){
			$smile_list[] = $key;
			$smile_replace[] = '<img src="'.$this->smiles_url.$val.'" alt="'.$key.'" />';
		}

		return str_replace($smile_list, $smile_replace, $text);
	}

	// Обработчик единичных тегов
	private function parse_once($text){

		$replace = $pattern = array();

		foreach($this->codes_once as $key => $value){
			$pattern[] = $value['pattern'];
			$replace[] = $value['replace'];
		}

		return preg_replace($pattern, $replace, $text);
	}

	// Обработчик обычных тегов с открывающими и загрывающими тегами
	private function parse_simple_tags($text){

		$pattern = '/\[('.implode('|', array_keys($this->codes)).')\]((?:[^[]|\[(?!\/?(\\1)\])|(?R))+)\[\/\\1\]/Usi';

		if(is_array($text)){
			$left_tag = $this->codes[$text[1]]['left_tag'];
			$right_tag = $this->codes[$text[1]]['right_tag'];
			$content = $text[2];

			if($text[1]=='code'){
				$content = str_replace(array('[', ']'), array('&#91;', '&#93;'), $content);
			}

			$text = $left_tag.$content.$right_tag; // result
		}else{
			$text = nl2br(htmlspecialchars($text, ENT_NOQUOTES));
		}

		return preg_replace_callback($pattern, array($this, 'parse_simple_tags'), $text);
	}

	// Обработчик тегов с опциями
	private function parse_with_options($text){

		if(is_array($text)){
			$codes = $this->codes_options;

			if(isset($codes[$text[1]])){
				$escape = $codes[$text[1]]['escapes'];

				if($escape!==false){
					$text[$escape] = str_replace(array('[', ']'), array('&#91;', '&#93;'), $text[$escape]);
				}
				
				$replace = $codes[$text[1]]['replace'];

				unset($codes[$text[1]]['values'][0]);

				foreach($codes[$text[1]]['values'] as $key => $value){

					$replace = str_replace('{'.$value.'}', $text[$value], $replace);
				}

				$text = $replace;
			}
		}

		$patterns = array();

		foreach($this->codes_options as $key => $value){
			$patterns[] = $value['pattern'];
		}

		return preg_replace_callback($patterns, array($this, 'parse_with_options'), $text);
	}

	// Обработчик видео тегов
	private function parse_video($text){

		$pattern = $replace = array();

		foreach($this->codes_video as $name => $value){
			$pattern[] = '/\[video\=\"'.$name.'\"\]http(s)?\:\/\/(www\.)?'.$value['pattern'].'\[\/video\]/Usi';
			$replace[] = '<div class="qxbb-iframe-wrapper"><iframe class="qxbb-iframe" src="'.$value['replace'].'" frameborder="0"></iframe></div>';
		}

		return preg_replace($pattern, $replace, $text);
	}

	// Обработчик списковых тегов
	private function parse_list_line($text){

		if(is_array($text)){
			$text = '<li>'.$text[1].'</li>';
		}

		return preg_replace_callback('/\[\*\]((?:[^[]|(?R))*)/si', array($this, 'parse_list_line'), $text);
	}

	private function parse_list($text){

		if(is_array($text)){
			
			$text = ($text['1']=='numbers') ? '<ol class="qxbb-list-numbers">'.$text[2].'</ol>' : '<ul class="qxbb-list-markers">'.$text[2].'</ul>';
		}else{
			$text = $this->parse_list_line($text);
		}

		return preg_replace_callback('/\[list\=\"(markers|numbers)\"\]((?:[^[]|(?R))*)\[\/list\]/Usi', array($this, 'parse_list'), $text);
	}

	public function parse($text){
		$text = $this->parse_simple_tags($text);

		$text = $this->parse_smiles($text);

		$text = $this->parse_once($text);

		$text = $this->parse_video($text);

		$text = $this->parse_list($text);

		return $this->parse_with_options($text);
	}
}

?>