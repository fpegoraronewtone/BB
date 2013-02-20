<?php
/**
 * BB - BlackBeard Plugin
 * Localization Component
 *
 * @author mpeg
 */
class BbLocaleComponent extends Component {
	
	protected $Controller = null;
	
	public $settings = array();
	
	public function __construct(ComponentCollection $collection, $settings = array()) {
		$this->Controller = $collection->getController();
		parent::__construct($collection, BB::extend($this->settings, $settings));
	}
	
	public function startup(Controller $Controller) {
		$this->_checkLocale();
		$this->_setLocale();
	}
	
	
	
	/**
	 * Check url params to contains localization instructions or
	 * throw internal auto-localization logic.
	 */
	protected function _checkLocale() {
		
		if (empty($this->Controller->request->params['country'])) {
			$this->Controller->request->params['country'] = $this->_getCountry();
			$redirect = true;
		}
		
		if (empty($this->Controller->request->params['lang'])) {
			$this->Controller->request->params['lang'] = $this->_getCountryLanguage();
			$redirect = true;
		}
		
		if (isset($redirect)) {
			$redirect = array(
				'country' => $this->Controller->request->params['country'],
				'lang' => $this->Controller->request->params['lang'],
				'controller' => strtolower(Inflector::underscore($this->Controller->name)),
				'action' => $this->Controller->action
			);
			
			if ( !empty($this->Controller->request->params['pass']) ) {
				$redirect = BB::extend($redirect, $this->Controller->request->params['pass']);
			}
			
			if ( !empty($this->Controller->request->params['named']) ) {
				$redirect = BB::extend($redirect, $this->Controller->request->params['named']);
			}
			
			$callback = array($this->Controller, 'bbLocaleRedirect');
			if (!BB::callback($callback, $redirect, $this->Controller->request->params['country'], $this->Controller->request->params['lang'])) {		
				$this->Controller->redirect($redirect);
			}
		}
		
	}
	
	
	/**
	 * Setup application with url based localization params
	 * it tries to throw a public setLocale() method on related controller.
	 */
	protected function _setLocale() {
		$callback = array($this->Controller, 'bbSetLocale');
		if (BB::callback($callback, $this->Controller->request->params['country'], $this->Controller->request->params['lang']) == true) return;
	}
	
	/**
	 * Geolocalization services attaches here
	 */
	protected function _getCountry() {
		$callback = array($this->Controller, 'bbGetCountry');
		if ($country = BB::callback($callback) !== null) {
			return $country;
		}
		return 'it';
	}
	
	/**
	 * Language auto selection services attaches here
	 */
	protected function _getCountryLanguage() {
		$callback = array($this->Controller, 'bbGetCountryLanguage');
		if ($lang = BB::callback($callback, $this->Controller->request->params['country']) !== null) {
			return $lang;
		}
		return $this->Controller->request->params['country'];
	}
	
}
