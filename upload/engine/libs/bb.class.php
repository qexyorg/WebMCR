<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class bb{
	private $core, $lng;

	public function __construct($core){
		$this->core		= $core;
		$this->lng		= $core->lng;
	}

	public function bb_panel($for=''){

		$data['PANEL_ID'] = $for;
		$data['SMILES'] = $this->get_smiles_bb();

		ob_start();

		echo $this->core->sp(MCR_THEME_PATH.'default_sp/bb-panel.html', $data);

		return ob_get_clean();
	}

	/**
	 * BBquote(@param) - Recursive function for bb codes
	 *
	 * @param - String
	 *
	 * @return callback function
	 *
	*/
	private function BBquote($text){

		$reg = '#\[quote]((?:[^[]|\[(?!/?quote])|(?R)?)+)\[/quote]#isu';

		if (is_array($text)){$text = '<blockquote>'.$text[1].'</blockquote>';}

		return preg_replace_callback($reg, 'self::BBquote', $text);
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

	private function smile_decode($text){
		include(MCR_TOOL_PATH.'libs/smiles.php');

		foreach($smiles as $key => $value){
			$smiles[$key] = '<img src="'.BASE_URL.'uploads/smiles/'.$value.'" alt="'.$key.'">';
		}

		$search = array_keys($smiles);
		$replace = array_values($smiles);

		return str_replace($search, $replace, $text);
	}

	/**
	 * bb_decode(@param) - Change BB-code to HTML
	 *
	 * @param - String
	 *
	 * @return String
	 *
	*/
	public function decode($text){

		$text = nl2br($text);

		$patern = array(
			'/\[b\](.*?)\[\/b\]/Usi',
			'/\[i\](.*?)\[\/i\]/Usi',
			'/\[s\](.*?)\[\/s\]/Usi',
			'/\[u\](.*?)\[\/u\]/Usi',
			'/\[left\](.*?)\[\/left\]/Usi',
			'/\[center\](.*?)\[\/center\]/Usi',
			'/\[right\](.*?)\[\/right\]/Usi',
			'/\[code\](.*?)\[\/code\]/Usi',
		);

		$replace = array(
			'<b>$1</b>',
			'<i>$1</i>',
			'<s>$1</s>',
			'<u>$1</u>',
			'<p align="left">$1</p>',
			'<p align="center">$1</p>',
			'<p align="right">$1</p>',
			'<code>$1</code>',
		);

		$text = preg_replace($patern, $replace, $text);
		$text = preg_replace("/\[url=(?:&#039;|&quot;|\'|\")((((ht|f)tps?|mailto):(?:\/\/)?)(?:[^<\s\'\"]+))(?:&#039;|&quot;|\'|\")\](.*?)\[\/url\]/Usi", "<a href=\"$1\">$5</a>", $text);
		$text = preg_replace("/\[img\](((ht|f)tps?:(?:\/\/)?)(?:[^<\s\'\"]+))\[\/img\]/Usi", "<img src=\"$1\">", $text);
		$text = preg_replace("/\[color=(?:&#039;|&quot;|\'|\")((\#[a-z0-9]{6})|([a-z]{1,30}))(?:&#039;|&quot;|\'|\")\](.*?)\[\/color\]/Usi", "<font color=\"$1\">$4</font>", $text);
		$text = preg_replace("/\[size=(?:&#039;|&quot;|\'|\")([1-6]{1})(?:&#039;|&quot;|\'|\")\](.*?)\[\/size\]/Usi", "<font size=\"$1\">$2</font>", $text);

		$text = $this->smile_decode($text);

		$text = $this->BBquote($text);

		return $text;

	}

}
?>