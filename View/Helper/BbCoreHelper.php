<?php
/**
 * BB - BlackBeard Plugin
 * General Utility Helper
 *
 * @author mpeg
 */
class BbCoreHelper extends AppHelper {
	
	/**	
	 * CakePower Inherited
	 * Utility Method
	 * take an asset path as used for the HtmlHelper::css() (or similar) and output the 
	 * full file path (if exists) or false if does not.
	 * 
	 * @param unknown_type $path
	 * @param unknown_type $options
	 */
	public function assetPath( $path, $options = array() ) {
		
		$options = BB::extend(array(
			'exists' => true
		), $options);
		
		// Absolute url path
		if (strpos($path, '://') !== false) return $path;

		if (!array_key_exists('plugin', $options) || $options['plugin'] !== false) {
			list($plugin, $path) = $this->_View->pluginSplit($path, false);
		}
		
		
		// Find the base path for the requested file.
		// It may use plugin notation (Plugin.asset) to refer to an asset hosted into a plugin package.
		// 
		// NOTE: an asset hosted in a plugin package may exists internally to the plugin or may
		// exists copied to the webroot in a plugin's named folder (for optimization)
		// The "base" points to the optimized folder
		// The "fallback" points to the plugin package folder
		$base = $fallback = WWW_ROOT;
		if ( !empty($plugin) && BB::check('plugin.'.$plugin.'.path') ) {
			$base 		= $base . strtolower($plugin) . DS;
			$fallback 	= BB::read('plugin.'.$plugin.'.path') . 'webroot' . DS;
		}
		
		// Add the path prefix to the asset request.
		if (!empty($options['pathPrefix']) && $path[0] !== '/') {
			$base 		.= $options['pathPrefix'];
			$fallback 	.= $options['pathPrefix'];
		}
		
		// Add the extension to the file path if required.
		if (
			!empty($options['ext']) &&
			strpos($path, '?') === false &&
			substr($path, -strlen($options['ext'])) !== $options['ext']
		) {
			$path .= $options['ext'];
		}
		
		// Check for files existance and return correct path.
		if ( file_exists($base.$path) ) 		return $base.$path;
		if ( file_exists($fallback.$path) ) 	return $fallback.$path;
		
		if ( $options['exists'] ) return false;
		return $fallback.$path;
	
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
	public function getParam($name = '') {
		if (empty($name)) return $name;
		if (isset($this->_View->request->params[$name])) {
			return $this->_View->request->params[$name];
		}
		if (isset($this->_View->request->params['named'][$name])) {
			return $this->_View->request->params['named'][$name];
		}
		if (isset($this->_View->request->data[$name])) {
			return $this->_View->request->data[$name];
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
	}
	
	
	/**
	 * Transfrom a generic string into a comma separated keywords meta tag
	 * string.
	 * 
	 * empty words or words less than 3 chars are skipped.
	 * you can give a dictionary of words to skip as 3rd params
	 * you can also give a BB::callback() to skip words
	 */
	public static function keywords($str = '', $callback = null) {
		if (empty($str)) return '';
		$keywords = array();
		foreach(explode(" ", strtolower($str)) as $word) {
			$word = trim($word);
			if (empty($word) || strlen($word) <= 2) {
				continue;
			}
			if (is_callable($callback)) {
				if (!BB::callback($callback, $word)) {
					continue;
				}
			} elseif (is_array($callback)) {
				if (in_array($word, $callback)) {
					continue;
				}
			}
			$keywords[] = $word;
		}
		return implode(', ', $keywords);
	}
	
}
