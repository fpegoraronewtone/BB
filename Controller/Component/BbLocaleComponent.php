<?php
/**
 * BB - BlackBeard Plugin
 * Localization Component
 *
 * @author mpeg
 */
class BbLocaleComponent extends Component {
	
	public $components = array(
		'BB.BbCore'
	);
	
	protected $_Controller = null;
	
	public $settings = array();
	public $country = null;
	public $language = null;
	
	
	
	public function __construct(ComponentCollection $collection, $settings = array()) {
		$this->_Controller = $collection->getController();
		parent::__construct($collection, BB::extend($this->settings, $settings));
	}
	
	public function initialize(Controller $Controller) {
		
		// controller's param to prevent localization and redirecting
		if (isset($this->_Controller->bb_localized) && $this->_Controller->bb_localized === false) {
			if (!defined('BB_LOCALE_DISABLE')) {
				define('BB_LOCALE_DISABLE', true);
			}
		}
		
		// global constant to prevent localization to work
		if (defined('BB_LOCALE_DISABLE')) return;
		
		$this->_Controller->helpers[] = 'BB.BbLocale';
		$this->_checkLocale();
		$this->_setLocale();
		
	}
	
	/**
	 * Check url params to contains localization instructions or
	 * throw internal auto-localization logic.
	 */
	protected function _checkLocale() {
		
		$this->country = $this->BbCore->pval('country');
		$this->language = $this->BbCore->pval('language');
		
		if (empty($this->country)) {
			$this->country = $this->_getCountry();
			$redirect = true;
		}
		
		if (empty($this->language)) {
			$this->language = $this->_getCountryLanguage();
			$redirect = true;
		}
		
		if (isset($redirect)) {
			$redirect = array(
				'country' => $this->country,
				'language' => $this->language,
				'controller' => strtolower(Inflector::underscore($this->_Controller->name)),
				'action' => $this->_Controller->action
			);
			
			if ( !empty($this->_Controller->request->params['pass']) ) {
				$redirect = BB::extend($redirect, $this->_Controller->request->params['pass']);
			}
			
			if ( !empty($this->_Controller->request->params['named']) ) {
				$redirect = BB::extend($redirect, $this->_Controller->request->params['named']);
			}
			
			$callback = array($this->_Controller, 'bbLocaleRedirect');
			if (!BB::callback($callback, $redirect, $this->country, $this->language)) {
				$this->_Controller->redirect($redirect);
			}
		
		// expose country and lang flags to the request object params
		} else {
			$this->_Controller->request->params['country'] = $this->country;
			$this->_Controller->request->params['language'] = $this->language;
		}
		
	}
	
	
	/**
	 * Setup application with url based localization params
	 * it tries to throw a public setLocale() method on related controller.
	 */
	protected function _setLocale() {
		$callback = array($this->_Controller, 'bbSetLocale');
		if (BB::callback($callback, $this->country, $this->language) == true) return;
	}
	
	/**
	 * Geolocalization services attaches here
	 */
	protected function _getCountry() {
		$callback = array($this->_Controller, 'bbGetCountry');
		if ($country = BB::callback($callback) !== null) {
			return $country;
		}
		return 'it';
	}
	
	/**
	 * Language auto selection services attaches here
	 */
	protected function _getCountryLanguage() {
		$callback = array($this->_Controller, 'bbGetCountryLanguage');
		if ($lang = BB::callback($callback, $this->country) !== null) {
			return $lang;
		}
		return $this->country;
	}
	
}
