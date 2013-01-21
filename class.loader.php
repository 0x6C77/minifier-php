<?php
/*
 * -- MIT license -- 
 * Copyright (c) 2002 Douglas Crockford  (www.crockford.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * The Software shall be used for Good, not Evil.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * --
 *
 * @author Luke Ward <flabbyrabbit@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link https://github.com/flabbyrabbit/minifier-php
 */

require_once("class.minijs.php");

class PHPMinifier {

	/*
	 * Base directories can be assigned for both javascript and css
	 * when all files reside within a common directory. A min folder
	 * will be generated at these locations to store generated files
	 */
	var $js_base = '/javascript';
	var $css_base = '/css';

	/*
	 * Default file arrays for both JS and CSS contain common files
	 * across the application. These will be generated seperately to any
	 * files that are specific to portions of the application
	 */
	var $default_js = Array('/main.js');
	var $default_css = Array('/reset.css', '/main.css');


	/*
	 * Generates and stores minified versions of selected javascript and css files
	 * Prints link and script tags for generated files
	 */
	public function load() {
		//Build default CSS file
		$path = "{$this->css_base}/min/main.css";
		if ($this->generate($path, $this->default_css, 'css')) {
			$includes = "<link rel='stylesheet' href='{$path}' type='text/css'/>\n";
		}
		
		//Build custom CSS file, if required
		if (is_array($this->custom_css) && count($this->custom_css)) {
			//generate filename to reflect contents
			$id = substr(md5(implode($this->custom_css)),0,10);
			$path = "{$this->css_base}/min/extra_{$id}.css";

			if ($this->generate($path, $this->custom_css, 'css')) {
				$includes .= "<link rel='stylesheet' href='{$path}' type='text/css'/>\n";
			}
		}


		//Build default JS file
		$path = "{$this->js_base}/min/main.js";
		if ($this->generate($path, $this->default_js, 'js')) {
			$includes .= "<script type='text/javascript' src='{$path}'></script>\n";
		}

		//Build custom JS, if required
		if (is_array($this->custom_js) && count($this->custom_js)) {
			//generate filename to reflect contents
			$id = substr(md5(implode($this->custom_js)),0,10);
			$path = "{$this->js_base}/min/extra_{$id}.css";
			
			if ($this->generate($path, $this->custom_js, 'js')) {
				$includes .= "<script type='text/javascript' src='{$path}'></script>\n";
			}
		}

		echo $includes;
	}

	/*
	 *
	 *
	 */
	public function add_file($filename, $type) {
		if ($type == 'js') {
			if (!is_array($this->custom_js) || !count($this->custom_js)) {
				$this->custom_js = array();
			}

			array_merge($this->custom_js, (array)$filename);
		} else if ($type == 'css') {
			if (!is_array($this->custom_css) || !count($this->custom_css)) {
				$this->custom_css = array();
			}

			array_merge($this->custom_css, (array)$filename);
		}
	}
	

	/*
	 *
	 *
	 */
	private function generate($filename, $file_array, $type) {
		if ($type == 'js') {
			$base = $this->js_base;
		} else if ($type == 'css') {
			$base = $this->css_base;
		} else {
			return false;
		}

		/*
		 * check if generated file already exists
		 * if so store last modified time for comparison
		 */
		if (file_exists($filename)) {
			$modified = filemtime($filename);

			foreach ($file_array as $file) {
				$filepath = $base.$file;
				if ((file_exists($filepath)) && (filemtime($filepath) > $modified)) {
					$generate = true;
					break;
				}
			}
		} else {
			$generate = true;
		}

		if ($generate) {
			// load and concatenate file contents
			foreach ($file_array as $file) {
				$filepath = $base.$file;
				if (file_exists($filepath)) {
					$contents .= file_get_contents($filepath) . "\n";
				}
			}

			// select minification rountine
			if ($type == 'js') {
				$contents = $this->minify_js($contents);
			} else if ($type == 'css') {
				$contents = $this->minify_css($contents);
			}

			// store file
			file_put_contents($filepath, $contents);
		}

		return true;
	}

	/*
	 *
	 *
	 */
	private function minify_js($contents) {
		$jsmin = new JSMin($contents);
		$contents = $jsmin->min();
		return $contents;
	}

	/*
	 *
	 *
	 */	
	private function minify_css($contents) {
		$contents = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $contents);
		$contents = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $contents);
		return $contents;
	}
}

?>
