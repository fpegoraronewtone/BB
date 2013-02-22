<?php
/**
 * BB - BlackBeard Plugin
 * LessCss compiler interface
 *
 * @author mpeg
 */

App::import('Vendor', 'BB.lessc.inc');
App::import('Vendor', 'BB.cssmin');

class BbLessHelper extends AppHelper {
	
	public $helpers = array(
		'BB.BbUtility'
	);
	
	/**
	 * Try to compile a LESS source into a CSS target.
	 * it take the name of the candidate CSS in CakePHP plugin format.
	 * 
	 * Es.	BbLess->compile('css_file')
	 *		BbLess->compile('Plugin.css_file')
	 * 
	 * if DEBUG less source are compiled at every time.
	 * 
	 * if PRODUCTION less source are compiled only if target 
	 * css does not exists. You need to manually remove compiled css to force
	 * a fresh compilation.
	 * 
	 */
	public function compile($css) {
		
		if (defined('CSS_URL')) {
			$CSS_URL = CSS_URL;
		} else {
			$CSS_URL = 'css/';
		}
		
		if (defined('LESS_URL')) {
			$LESS_URL = LESS_URL;
		} else {
			$LESS_URL = 'less/';
		}
		
		$css_path = $this->BbUtility->assetPath($css, array('pathPrefix' => $CSS_URL, 'ext' => '.css', 'exists' => false));
		$less_path = $this->BbUtility->assetPath($css, array('pathPrefix' => $LESS_URL, 'ext' => '.less', 'exists' => false));
		
		if (file_exists($less_path) && (!file_exists($css_path) || Configure::read('debug'))) {
			$this->_compileFile($less_path, $css_path);
		}
		
	}
	
	
	/**
	 * Uses LESSC library to compile a LESS source.
	 */
	protected function _compileFile($source, $target) {
		
		$cacheFld = new Folder(CACHE.'lessc', true);
		if(!is_writable($cacheFld->pwd())) {
			trigger_error(__d('cake_dev', '"%s" directory is NOT writable.', CACHE.'less'), E_USER_NOTICE);
			return;
		}
		
		$cachePath = $cacheFld->pwd() . DS . str_replace(array('/', '.'), '_', $source);
		
		// fix - clear cache file if target does not exists because it
		// means it is mandatory to compile!
		if (!file_exists($target)) {
			@unlink($cachePath);
		}
		
		// do compile
		if (file_exists($cachePath)) {
			$cache = lessc::cexecute(unserialize(file_get_contents($cachePath)));
		} else {
			$cache = lessc::cexecute($source);
		}
		
		// css minification
		if (class_exists('CssMin') && Configure::read('debug') <= BB::read('bb.css.minify.level', 1)) {
			$cache['compiled'] = CssMin::minify($cache['compiled']);
		}
		
		// write files
		file_put_contents($target, $cache['compiled']);
		file_put_contents($cachePath, serialize($cache));
		
	}
	
}

