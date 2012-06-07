<?php
	require_once("class.minijs.php");
	class loader {
		var $js = Array('/javascript/main.js');
		var $css = Array('/css/reset.css',
					'/css/main.css');

		function load() {
			//Build default CSS
			$css_path = "/css/min/main.css";
			$this->buildCSS($css_path, $this->css);
			$return = "<link rel='stylesheet' href='/files".$css_path."' type='text/css'/>\n";
			
			//Build custom CSS
			if (is_array($custom_css) && count($custom_css)) {
				$id = substr(md5(implode($custom_css)),0,10);
				$css_path = "/css/min/extra_$id.css";
				$this->buildCSS($css_path, $custom_css, '/css/');
				$return .= "<link rel='stylesheet' href='/files".$css_path."' type='text/css'/>\n";
			}

			//Build default JS
			$js_path = "/javascript/min/main.js";
			$this->buildJS($js_path, $this->js);
			$return .= "<script type='text/javascript' src='/files".$js_path."'></script>\n";

			//Build custom JS
			if (is_array($custom_js) && count($custom_js)) {
				$id = substr(md5(implode($custom_js)),0,10);
				$js_path = "/javascript/min/extra_$id.js";
				$this->buildJS($js_path, $custom_js, '/javascript/');
				$return .= "<script type='text/javascript' src='/files".$js_path."'></script>\n";
			}

			return $return;
		}
		
		function buildJS($path, $arr, $base='') {
			//Check if the file needs to be rewritten
			$mod = 0;
			if (file_exists("files/".$path))
				$mod = filemtime("files/".$path);
			$change = 0;
			foreach ($arr as $file) {
				if ((file_exists("files/".$base.$file)) &&
					(filemtime("files/".$base.$file) > $mod)) {
						$change++;
				}
			}
			if ($change > 0) {			
				foreach ($arr as $file) {
					if (file_exists("files/".$base.$file))
						$js .= file_get_contents("files/".$base.$file) . "\n";
				}
				$jsmin = new JSMin($js);
		    	$js_min = $jsmin->min();

				//store
				file_put_contents("files/".$path, $js_min);
			}
		}
		
		function buildCSS($path, $arr, $base='') {
			//Check if the file needs to be rewritten		
			$mod = 0;
			if (file_exists("files/".$path))
				$mod = filemtime("files/".$path);
			$change = 0;
			foreach ($arr as $file) {
				if ((file_exists("files/".$base.$file)) &&
					(filemtime("files/".$base.$file) > $mod)) {
						$change++;
				}
			}
			if ($change > 0) {				
				foreach ($arr as $file) {
					if (file_exists("files/".$base.$file))
						$css .= file_get_contents("files/".$base.$file) . "\n";
				}
				$css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
				$css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);
		
				//store
				file_put_contents("files/".$path, $css);
			}
		}
	}
?>
