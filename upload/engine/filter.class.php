<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

function filter($var, $type, $opt=''){
	if(!empty($opt)){ $opt = json_decode($opt, true); }

	switch($type){
		case 'int':
			return intval($var);
		break;


		case 'float':
			return floatval($var);
		break;


		case 'bool':
			return filter_var($var, FILTER_VALIDATE_BOOLEAN);
		break;


		case 'hsc':
			return htmlspecialchars($var);
		break;


		case 'chars':
			return preg_replace("/[^\w]+/i", "", $var);
		break;


		case 'nums':
			return preg_replace("/[^\d]+/", "", $var);
		break;


		case 'email':
			return preg_replace("/[^a-z0-9\-\@\.]+/i", "", $var);
		break;


		case 'ipv4':
			return preg_replace("/[^\d\.]+/", "", $var);
		break;


		case 'domain':
			return preg_replace("/[^a-z0-9\-\.]+/i", "", $var);
		break;


		case 'string':
			return preg_replace("/[\'\"\`\>\<\{\\\}\%]+/i", "", $var);
		break;


		case 'num_array':
			$new_array = array();

			if(!is_array($var) || empty($array)){ return $new_array; }

			foreach($var as $key => $value){ $new_array[$key] = (@$opt['float']) ? floatval($value) : intval($value); }

			return $new_array;
		break;


		case 'more_than_zero':
			if(is_array($var)){
				foreach($var as $key => $val){
					if(@$opt['float']){
						$var[$key] = (floatval($var)<=0) ? 1 : floatval($var);
					}else{
						$var[$key] =(intval($var)<=0) ? 1 : intval($var);
					}
				}

				return $var;
			}else{
				if(@$opt['float']){
					return (floatval($var)<=0) ? 1 : floatval($var);
				}else{
					return (intval($var)<=0) ? 1 : intval($var);
				}
			}
		break;

		default: return false;
	}
}

?>