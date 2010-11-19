<?php

function get_mime_type($file, $print = false) {
	$types = array(	'txt' => 'txt',
					'css' => 'css',
					'doc' => 'doc',
					'odf' => 'doc',
					'fla' => 'fla',
					'html' => 'html',
					'htm' => 'html',
					'png' => 'image',
					'gif' => 'image',
					'jpg' => 'image',
					'png' => 'image',
					'svg' => 'image',
					'jpeg' => 'image',
					'js' => 'image',
					'url' => 'link',
					'log' => 'log',
					'mov' => 'mov',
					'avi' => 'movie',
					'wmv' => 'movie',
					'mp3' => 'mp3',
					'pdf' => 'pdf',
					'phps' => 'php',
					'php' => 'php',
					'ppt' => 'ppt',
					'psd' => 'psd',
					'rss' => 'rss',
					'sql' => 'sql',
					'swf' => 'swf',
					'xls' => 'xls',
					'xml' => 'xml',
					'zip' => 'zip',
					'rar' => 'zip',
					'tar' => 'zip',
					'bz2' => 'zip',
					'gz' => 'zip'
				   );

	$file = explode('.', $file);
	$type = array_pop($file);

	if ($print) {
		if (array_key_exists($type, $types)) {
			return 'background-image: url(images/mime/'.$types[$type].'.gif); background-repeat: no-repeat; padding-left: 18px;';
		} else {
			return 'background-image: url(images/mime/txt.gif); background-repeat: no-repeat; padding-left: 18px;';
		}
	} else {
		return $type;
	}
}

function list_directory($directory) {
	global $blocked;
	$files = array();

	if (!is_dir($directory)) {
		die('Not a directory: '.$directory);
	}

    $dir = dir($directory);
	while (false !== ($file = $dir->read())) {
		if (in_array($file, $blocked)) {
	   		continue;
	   	}

        if (is_dir($directory.$file)) {
	   		$files[$file] = list_directory($directory.$file.'/');
	   	} else {
	   		array_push($files, $file);
	   	}
	}

	$dir->close();

	arsort($files);
	return $files;
}

function get_list($list, $path = '', $html = '') {
	$images = array('png', 'jpg', 'jpeg', 'gif', 'svg');
	if (!is_array($list)) {
		die('No array present.');
	}

	$html .= "<ul>";

	foreach ($list as $index => $value) {
		if (is_array($value)) {
			$html .= "<li class='folder'>".$index."</li>";
			$html .= get_list($value, $path.$index.'/');
		} else {
			if (in_array(get_mime_type($value), array('html', 'htm'))) {
				$html .= "<li><a style='".get_mime_type($value, true)."' href='index.php?p=".$path.$value."'>".$value."</a></li>";
			} elseif (in_array(get_mime_type($value, false), $images)) {
				$html .= "<li><a class='image' style='".get_mime_type($value, true)."' href='".$path.$value."'>".$value."</a></li>";
			} else {
				$html .= "<li><a style='".get_mime_type($value, true)."' href='".$path.$value."'>".$value."</a></li>";
			}
		}
	}

	$html .= "</ul>";
	return $html;
}

?>
