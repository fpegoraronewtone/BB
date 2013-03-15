<?php
/**
 * BB - BlackBeard Core Plugin
 * 
 * Main BlackBeard Component
 * using this component
 * 
 */
class BbCoreComponent extends Component {
	
	protected $_Collection = null;
	protected $_Controller = null;
	
	public function __construct(ComponentCollection $collection, $settings = array()) {
		$this->_Collection = $collection;
		$this->_Controller = $collection->getController();
	}
	
	public function initialize(Controller $Controller) {
		// Alias BB's components extensions
		$this->loadComponent(array(
			'Session' => array('className' => 'BB.BbSession')
		), true);
		
		// Inject BB's helpers extensions
		$this->loadHelper(array(
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
	
	
	/**
	 * Load Helper Utility
	 */
	function loadHelper($list = array()) {
		if (!is_array($list)) {
			$list = array($list);
		}
		$this->_Controller->helpers = BB::extend($this->_Controller->helpers, $list);
	}
	

	/**
	 * Load Component Utility
	 */
	public function loadComponent($list = array(), $force = false) {
		if (!is_array($list)) {
			$list = array($list);
		}
		foreach ($list as $component=>$config) {
			if (is_int($component)) {
				$component = $config;
				$config = null;
			}
			
			// define name and alias
			list($plugin, $componentName) = pluginSplit($component);
			$aliasPlugin = $plugin;
			$aliasComponent = $component;
			$aliasComponentName = $componentName;
			// apply aliased className to loading settings
			if (!empty($config['className'])) {
				$aliasComponent = $config['className'];
				list($aliasPlugin, $aliasComponentName) = pluginSplit($aliasComponent);
			}
			
			
			// search inside controller's loaded components for existing
			// instance
			if (isset($this->_Controller->{$componentName}) && !$force) {
				continue;
			}
			
			// import class and create instance of the new component
			// it uses "alias" version!
			App::import('Component', $aliasComponent);
			$componentFullName = $aliasComponentName.'Component';
			$component = new $componentFullName($this->_Collection, $config);

			if (method_exists($component, 'initialize')) {
				$component->initialize($this->_Controller);
			}
			if (method_exists($component, 'startup')) {
				$component->startup($this->_Controller);
			}
			
			// insert into controller's components list and return instance.
			$this->_Controller->{$componentName} = $component;
			return $this->_Controller->{$componentName};
		}
	}
	
	
	
	
}
