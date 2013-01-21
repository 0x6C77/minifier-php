PHP Minifier
================
A small PHP class that minifies website resources and reduces requests

* Condense multiple JS or CSS files into single requests
* Minify JS and CSS files on the fly
* Cache multiple responses to avoid reprocessing

Usage
================
$minifier = new PHPMinifier();
$minifier->add_file('news.css', 'css');

$js_files = array('extra.js', 'news.js');
$minifier->add_file($js_files);

$minifier->load();