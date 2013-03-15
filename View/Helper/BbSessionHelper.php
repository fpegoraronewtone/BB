<?php
/**
 * BbSession Helper
 */

App::import( 'View/Helper', 'SessionHelper' );

class BbSessionHelper extends SessionHelper {
	
	public $flashKeys = array('success', 'error', 'warning', 'info', 'auth');
	
	
	protected static $_flashDefaultElement = 'flash/{key}';
	protected static $_flashFallbackElement = 'BB.flash/{key}';
	
	
	
	/**
	 * CakePHP Override
	 * display all configured flash messages with custom template
	 */
	public function flash($keys = ALL, $options = array()) {
		if ($keys === ALL) {
			$keys = $this->flashKeys;
		}
		if (!is_array($keys)) {
			$keys = array($keys);
		}
		
		// default custom application element
		$options = BB::setDefaults($options, array(
			'element' => self::$_flashDefaultElement
		),'element');
		
		ob_start();
		foreach ($keys as $key) {
			$_options = $options;
			$_options['element'] = BB::tpl($_options['element'], array('key' => $key));
			
			// fallback element
			if (!$this->_View->elementExists($_options['element'])) {
				$_options['element'] = BB::tpl(self::$_flashFallbackElement, array('key' => $key));
			}
			echo parent::flash($key, $_options);
		}
		return ob_get_clean();
	}
	
	
	/**
	 * Public utilities to change standard flash message element template
	 */
	public function setFlashDefaultElement($element) {
		self::$_flashDefaultElement = $element;
	}
	
	public function setFlashFallbackElement($element) {
		self::$_flashFallbackElement = $element;
	}
	
}
