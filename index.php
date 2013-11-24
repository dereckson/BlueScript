<?php

//settings
$blocked = array('.', '..', 'images', 'include', 'layouts', 'not', 'index.php', 'index.html');
$path = './';

/** ONLY MODIFY STUFF BELOW IF YOU KNOW WHAT YOU ARE DOING **/

//folder definitons
define('D_HOME', dirname(__FILE__).'/');
define('D_INCLUDE', D_HOME.'include/');
define('D_LAYOUTS', D_HOME.'layouts/');

//include classes
require_once(D_INCLUDE.'functions.php');
require_once(D_INCLUDE.'layout/layout.inc.php');
require_once(D_INCLUDE.'layout/layout_parser.inc.php');
require_once(D_INCLUDE.'layout/layout_cache.inc.php');

$layout = new layout('blue');

//get cache for no includes
if (!array_key_exists('p', $_GET)) {
	$cache = $layout->getCache('index', '_ni');
	if (!$cache) {
		$parser = $layout->getParser('index', '_ni', 3600);
		$parser->assign('directory', get_list(list_directory($path), $path));
		$parser->assign('include', false);
		$parser->parse();
		echo $parser->returnTemplate();
	} else {
		echo $cache;
	}
} else {
	//check for intrusion
	if (can_include($_GET['p'], $path)) {
		//get filename
		$file = explode('/', $_GET['p']);
		$object = array_pop($file).array_pop($file);

		//check cache and output
		$cache = $layout->getCache('index', $object);
		if (!$cache) {
			$parser = $layout->getParser('index', $object, 3600);
			$parser->assign('directory', get_list(list_directory($path), $path));
			$parser->assign('include', file_get_contents($_GET['p']));
			$parser->parse();
			echo $parser->returnTemplate();
		} else {
			echo $cache;
		}
	} else {
		die ("[ BlueScript | Page not found in the web directory. ]");
	}
}
