<?php

/**
 * layout class
 *
 * @author blue@devio.us
 */
class layout {
	/**
	 * the layout_cache object
	 *
	 * @var object
	 */
	protected $layout_cache;

	/**
	 * the layout_parser object
	 *
	 * @var object
	 */
	protected $layout_parser;

	/**
	 * all required layouts
	 *
	 * @var array
	 */
	protected $layouts = array();

	/**
	 * the current layout
	 *
	 * @var string
	 */
	protected $layout = '';

	/**
	 * constructor, initializes all objects and settings
	 *
	 * @return NULL
	 */
	public function __construct($layout) {
		$this->layout = $layout;
		
		$this->layout_parser = new layout_parser();		//init onetime layout parser
		$this->layout_parser->setLayout($this->layout);
		$this->layout_cache = new layout_cache($this->layout);		//init onetime layout cache

		return NULL;
	}

	/**
	 * destructor, saves all templates that are parsed and should be cached
	 *
	 * @return NULL
	 */
	public function __destruct() {
		foreach ($this->layouts as $layout => $layout_content) {
			if ($layout_content['cache'] != 0) {
				$this->layout_cache->updateCache($layout, $layout_content['class']->returnTemplate(), $layout_content['cache']);
			}
		}

		return NULL;
	}

	/**
	 * returns a new layout_parser instance for a given template
	 * you may add a custom template_id for caching and a cache time
	 *
	 * @param string $template
	 * @param string $id
	 * @param integer $cache
	 * @return object
	 */
	public function getParser($template, $id = '', $cache = 0) {
		$this->layouts[$template.$id]['class'] = clone $this->layout_parser;
		$this->layouts[$template.$id]['class']->setTemplate($template);
		$this->layouts[$template.$id]['cache'] = $cache;
		return $this->layouts[$template.$id]['class'];
	}

	/**
	 * returns a string if a template is cached or false if it isn't
	 *
	 * @param string $template
	 * @param string $id
	 * @return mixed
	 */
	public function getCache($template, $id) {
		if ($this->layout_cache->checkCache($template.$id)) {
			return $this->layout_cache->getCache($template.$id);
		} else {
			return false;
		}
	}
}

?>