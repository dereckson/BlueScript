<?php

class layout_cache {
	/**
	 * stores the xml we are currently working on (current layout)
	 *
	 * @var object
	 */
	protected $xml;

	/**
	 * contains the current layout we are using
	 *
	 * @var string
	 */
	protected $layout;

	/**
	 * stores the overall xml object
	 *
	 * @var object
	 */
	protected $xml_ref;

	/**
	 * constructor, initializes all objects
	 *
	 * @return NULL
	 */
	public function __construct($layout) {														//get the settings object
		$this->layout = $layout;																//get the current layout
		$this->xml_ref = new SimpleXMLElement(file_get_contents(D_INCLUDE.'layout/cache.xml'));	//get the current cache file

		$layout = $this->layout;
		//check if layout is in the cache if not add it
		if (!(bool)$this->xml_ref->$layout) {
			$this->xml_ref->addChild($layout);
		}

		$this->xml = $this->xml_ref->$layout;
		return NULL;
	}

	/**
	 * destructor, saves the xml file before ending the script
	 *
	 * @return NULL
	 */
	public function __destruct() {
		$this->xml_ref->asXml(D_INCLUDE.'layout/cache.xml');

		return NULL;
	}

	/**
	 * checks whether a template is cached or not
	 * @param string $template
	 * @return bool
	 */
	public function checkCache($template) {
		if ((bool)$this->xml->$template) {
			$time = time();
			$template_time = $this->xml->$template;

			if ($time >= $template_time) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	/**
	 * updates the currently cached source for a given template
	 *
	 * @param string $template_id
	 * @param string $source
	 * @param integer $cachetime
	 * @return NULL
	 */
	public function updateCache($template_id, $source, $cachetime) {
		if (!(bool)$this->xml->$template_id) {
			$this->xml->addChild($template_id);
		}

		$this->xml->$template_id = time() + $cachetime;

		file_put_contents(D_LAYOUTS.$this->layout.'/templates_c/'.$template_id.'.tplc', $source);

		return NULL;
	}

	/**
	 * get the current cache of a template
	 *
	 * @param string $template_id
	 * @return string
	 */
	public function getCache($template_id) {
		if (is_file(D_LAYOUTS.$this->layout.'/templates_c/'.$template_id.'.tplc')) {
			return file_get_contents(D_LAYOUTS.$this->layout.'/templates_c/'.$template_id.'.tplc');
		} else {
			trigger_error('Template cache for '.$template_id.' does not exist.', E_USER_ERROR);
		}
	}
}
?>