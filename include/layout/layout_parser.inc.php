<?php

/**
 * layout parser class
 *
 * @author blue
 */
class layout_parser {
	/**
	 * contains the name of the current layout
	 *
	 * @var string
	 */
	protected $layout = '';

	/**
	 * counter which is increased each time a new
	 * "noParseBlock" is assigned
	 *
	 * @var integer
	 */
	protected $noParseBlocks = 0;

	/**
	 * contains all the variables that are assigned
	 *
	 * @var array
	 */
	protected $vars = array();

	/**
	 * contains a list of addons that are currently
	 * registered
	 *
	 * @var array
	 */
	protected $addons = array();

	/**
	 * all the templates we included
	 *
	 * @var array
	 */
	protected $templates = array();

	/**
	 * the current template we are editing
	 *
	 * @var string
	 */
	protected $template = '';

	/**
	 * constructor
	 *
	 * @return NULL;
	 */
	public function __construct() {
		$this->addons = $this->get_addons();			//get all the addons installed

		return NULL;
	}

	/**
	 * sets the current layout
	 *
	 * @param string $layout
	 * @return NULL
	 */
	public function setLayout($layout) {
		$this->layout = $layout;

		return NULL;
	}

	/**
	 * sets the to be parsed template
	 *
	 * @param string $template
	 * @return NULL
	 */
	public function setTemplate($template) {
		$this->template = $template;

		return NULL;
	}

	/**
	 *  returns all the addons available in the form of an array
	 *
	 *  @return array
	 */
	protected function get_addons() {
		//preinitialize addon array
		$addons = array();

		//get a dir object on the addon directory
		$addon_directory = D_INCLUDE.'layout/addons/';
		$addon_directory_object = dir($addon_directory);

		//read through each file
		while (false !== ($file = $addon_directory_object->read())) {
			//skip the unix virtual . and .. files
			if ($file === '.' or $file === '..') continue;

			//get the type of the file, include it and save it to an array
			//example: function.abc.php
			$filetype = explode('.', $file);
			switch ($filetype[0]) {
				//function
				case 'function':
					include_once($addon_directory.$file);
					if (function_exists("layout_".$filetype[1])) {
						array_push($addons, $filetype[1]);
					} else {
						trigger_error('Addon '.htmlentities($file, ENT_QUOTES).' included but not recognized.', E_USER_NOTICE);
					}
					break;
				//other
				case 'other':
					include_once($addon_directory.$file);
					break;
				default:
					trigger_error('Addon '.htmlentities($file, ENT_QUOTES).' not recognized.', E_USER_NOTICE);
					break;
			}
		}

		return $addons;
	}

	/**
	 * check if a string is registered as an addon
	 *
	 * @param string $addon
	 * @return boolean
	 */
	protected function is_addon($addon) {
		if (array_key_exists($addon, $his->addons['function'])) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * call out an addon
	 *
	 * @param string $addon
	 * @param string $var
	 * @return string
	 */
	protected function call_addon($addon, $var) {
		return eval("return layout_$addon('".$var."');");
	}

	/**
	 * returns the unparsed source of a template
	 *
	 * @param string $template
	 * @return string
	 */
	protected function getTemplate($template) {
		if (!array_key_exists($template, $this->templates)) {
			if (is_file(D_LAYOUTS.$this->layout.'/templates/'.$template.'.tpl')) {
				$this->templates[$template] = file_get_contents(D_LAYOUTS.$this->layout.'/templates/'.$template.'.tpl');
				return $this->templates[$template];
			} else {
				trigger_error('Template '.$template.'.tpl does not exist in '.D_LAYOUTS.$this->layout.'/templates.', E_USER_ERROR);
			}
		} else {
			return $this->templates[$template];
		}
	}

	/**
	 * assigns a new variable to a name
	 *
	 * @param string $name
	 * @param mixed $var
	 * @return NULL
	 */
	public function assign($name, $var) {
		$this->vars[$name] = $var;

		return NULL;
	}

	/**
	 * parses a template
	 *
	 * @param string $template
	 * @return NULL
	 */
	public function parse() {
		//Set the template
		$template = $this->template;

		//Get the template
		$template = $this->getTemplate($template);

		//Handle all noparse blocks
		$template = $this->parseNoParse($template);

		//Handle all php blocks
		$template = $this->parsePhp($template);

		//Handle all comments
		$template = $this->parseComments($template);

		//Handle all loops
		$template = $this->parseLoop($template);

		//Handle all if blocks
		$template = $this->parseIf($template);

		//Handle all vars
		$template = $this->parseVars($template);

		$this->template = $template;

		return NULL;
	}

	/**
	 * parse all noparse blocks
	 *
	 * @param string $template
	 * @return string
	 */
	protected function parseNoParse($template) {
		$template = preg_replace_callback (
			'/\{noparse\}(.*?)\{\/noparse\}/is',
			function ($matches) {
				return $this->assignNoParse($matches[1]);
			},
			$template
		); // {noparse} {/noparse}
		return $template;
	}

	/**
	 * assign a noparse block to a block_ var and replace it
	 *
	 * @param string $block
	 * @return string
	 */
	protected function assignNoParse($block) {
		++$this->noParseBlocks;
		$this->vars['block_'.$this->noParseBlocks] = $block;
		return '{$block_'.$this->noParseBlocks.'}';
	}

	/**
	 * parse an if expression
	 *
	 * @param string $template
	 * @return string
	 */
	protected function parseIf($template) {
		$template = preg_replace_callback(
			'/\{if (.*?)\}(.*?)\{else\}(.*?){\/if\}/is',
			function ($matches) {
				return $this->assignIf($matches[1], $matches[2], $matches[3]);
			},
			$template
		); // {if expression == expression} $content1 {else} $content2 {/if}
		$template = preg_replace_callback(
			'/\{if (.*?)\}(.*?)\{\/if\}/is',
			function ($matches) {
				return $this->assignIf($matches[1], $matches[2]);
			},
			$template
		); // {if expression == expression} $content {/if}
		return stripcslashes($template);
	}

	/**
	 * assign an if expression based on its conditions
	 *
	 * @param string $condition
	 * @param string $if
 	 * @param string $else
	 * @return string
	 */
	protected function assignIf($condition, $if, $else = '') {
		extract($this->vars, EXTR_PREFIX_SAME, 'nonlayout_');

		$condition = eval("return $condition;");
		if ($condition) {
			return $if;
		} else {
			return $else;
		}
	}

	/**
	 * parse php code
	 *
	 * @param string $template
	 * @return string
	 */
	protected function parsePhp($template) {
		$template = preg_replace_callback(
			'/\{php\}(.*?)\{\/php\}/is',
			function ($matches) {
				ob_start();
				eval($matches[1]);
				return ob_get_clean();
			},
			$template
		); // {php} {/php}
		return $template;
	}

	/**
	 * parse a loop defined in the template and assigned as a two-dimensional array
	 *
	 * @param string $template
	 * @return string
	 */
	protected function parseLoop($template) {
		$template = preg_replace_callback(
			'/\{loop \$(.*?)\}(.*?)\{\/loop\}/is',
			function ($matches) {
				return $this->assignLoop($matches[1], $matches[2]);
			},
			$template
		); // {loop $myloop} $myloop_loop['test'] {/loop}
		return $template;
	}

	/**
	 * assign all the vars within a loop
	 *
	 * @param string $name
	 * @param string $template
	 * @return string
	 */
	protected function assignLoop($name, $template) {
		//we need a two-dimensional array to perform a loop
		if (is_array($this->vars[$name][0])) {

			$return = '';
			$template = stripslashes($template);

			foreach ($this->vars[$name] as $current_loop) {
				$this->vars[$name.'_loop'] = $current_loop;
				$return .= $this->parseIf($this->parseVars($template));
			}

			return stripcslashes($return);
		} else {
			return '';
		}
	}

	/**
	 * parse all vars in a template
	 *
	 * @param string $template
	 * @return string
	 */
	protected function parseVars($template) {
		$template = preg_replace_callback(
			'/\{\$(.*?)\}/is',
			function ($matches) {
				return $this->assignVar($matches[1]);
			},
			$template
		); // {$var}
		return $template;
	}

	/**
	 * assign a var to its counterpart
	 *
	 * @param string $var
	 * @return string
	 */
	protected function assignVar($var) {
		extract($this->vars, EXTR_OVERWRITE);

		$var = stripslashes($var);
		$command = explode('|', $var);
		$var = array_shift($command);
		$var = eval("return $".$var.";");

		foreach ($command as $c) {
			$attr = explode('=', $c);

			switch ($attr[0]) {
				case 'capitalize':
					$var = strToUpper(substr($var, 0, 1)).substr($var, 1)." ";
					break;
				default:
					if ($this->is_addon($attr[0])) {
						$var = $this->call_addon($var, self);
					}
					break;

			}
		}

		return $var;
	}

	/**
	 * parse all comments in a template
	 *
	 * @param string $template
	 * @return string
	 */
	protected function parseComments($template) {
		$template = preg_replace ('/\{\$\$(.*?)\$\$\}/is', '', $template); // {$$ ... $$}
		return $template;
	}

	/**
	 * return the parse template
	 *
	 * @return string
	 */
	public function returnTemplate() {
		return $this->template;
	}

}
