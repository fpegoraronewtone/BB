<?php
/**
 * BB - BlackBeard Plugin
 * utilities to controllers who wants to implement some static content pages
 *
 * @author mpeg
 */
class BbStaticComponent extends Component {
	
	public $settings = array(
		'action'	=> 'display',
		'lang'		=> 'lang',
		'root'		=> null,
		'home'		=> 'home',
		'index'		=> 'index'
	);
	
	protected $Controller = null;
	
	public function __construct(ComponentCollection $collection, $settings = array()) {
		$this->Controller = $collection->getController();
		parent::__construct($collection, BB::extend($this->settings, $settings));
	}
	
	// Inject BbStaticHelper into controller's helpers
	public function initialize(Controller $Controller) {
		$this->Controller->helpers[] = 'BB.BbStatic';
	}
	
	/**
	 * Test for display action to exists inside Controller.
	 * if action does not exists then auto-render static view in place of
	 * Controller's logic.
	 * 
	 * This way controllers who implements BbStatic should not write 
	 * any display logic.
	 */
	public function startup(Controller $Controller) {
		if ($this->Controller->request->params['action'] == $this->settings['action'] && !is_callable(array($this->Controller, $this->settings['action']))) {
			$html = $this->render();
			if (!empty($html)) {
				echo $html;
				exit;
			}
		}
	}
	
	
	/**
	 * Render requested action (with sub/action capabilities) searching inside
	 * language contextualized folders then into general view root.
	 * 
	 * Default view root is the name of the controller.
	 */
	public function render() {
		$path = func_get_args();
		if (empty($path)) {
			$path = $this->Controller->request->params['pass'];
		}
		
		// seupt home page path
		$count = count($path);
		if (!$count) {
			$path = array($this->settings['home']);
			$count = 1;
		}
		
		// export path info to the view
		$this->Controller->set(compact('path'));
		
		// try to render translated view - if a lang param exists!
		if (isset($this->Controller->request->params[$this->settings['lang']]) && !empty($this->Controller->request->params[$this->settings['lang']])) {
			$lpath = BB::extend(array($this->Controller->request->params[$this->settings['lang']]), $path);
			$lpath = $this->getViewPath($lpath);
			if ($lpath !== false) return $this->Controller->render($lpath);
		}
		
		// render default computed view
		if (($render = $this->getViewPath($path)) !== false) {
			return $this->Controller->render($render);
		
		// fallback to CakePHP default view path
		} else {
			return $this->Controller->render(implode('/', $path));
		}
		
	}
	
	
	/**
	 * Test a path to exists in both 'path/sub.ctp' and 'path/sub/index.ctp' format
	 * return existing complete path or false if nothing exists.
	 * 
	 * root view folder and index file name are configurable through 
	 * component's settings
	 */
	public function getViewPath($path) {
		// fetch settings
		$root = !empty($this->settings['root']) ? $this->settings['root'] : $this->Controller->name;
		$index = $this->settings['index'];
		// search for views
		$spath = implode('/', $path);
		foreach(App::path('View') as $base) {
			if (file_exists($base . $root . DS . $spath . '.ctp')) {
				return implode('/', $path);
			}
			if (file_exists($base . $root . DS . $spath . DS . $index . '.ctp')) {
				return implode('/', BB::extend($path, array($index)));
			}
		}
		return false;
	}
	
}


