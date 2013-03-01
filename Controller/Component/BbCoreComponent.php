<?php
/**
 * BB - BlackBeard Core Plugin
 * 
 * Main BlackBeard Component
 * using this component
 * 
 */
class BbCoreComponent extends Component {
	
	protected $_Controller = null;
	
	public function initialize(Controller $Controller) {
		
		$this->_Controller = $Controller;
		
		// Inject BB's helpers extensions
		$this->_Controller->helpers = BB::extend($Controller->helpers, array(
			'Html' => array('className' => 'BB.BbHtml')
		));
		
	}
	
	
	/**
	 * Search for a named param to exists in a lot of places:
	 * - request's params
	 * - request's named
	 * - request's data
	 * - $_POST
	 * - $_GET
	 * - CakePhp Session
	 * - $_SESSION
	 * - $_COOKIE
	 */
	public function getParam($name = '', $default = null) {
		if (empty($name)) return $name;
		if (isset($this->_Controller->request->params[$name])) {
			return $this->_Controller->request->params[$name];
		}
		if (isset($this->_Controller->request->params['named'][$name])) {
			return $this->_Controller->request->params['named'][$name];
		}
		if (isset($this->_Controller->request->data[$name])) {
			return $this->_Controller->request->data[$name];
		}
		if (isset($_POST[$name])) {
			return $_POST[$name];
		}
		if (isset($_GET[$name])) {
			return $_GET[$name];
		}
		/*
		if ($this->_Controller->Session->Check($name)) {
			return $this->_Controller->Session->Read($name);
		}
		*/
		if (isset($_SESSION[$name])) {
			return $_SESSION[$name];
		}
		if (isset($_COOKIE[$name])) {
			return $_COOKIE[$name];
		}
		return $default;
	}
	
}
