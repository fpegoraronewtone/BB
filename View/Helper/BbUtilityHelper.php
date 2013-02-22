<?php
/**
 * BB - BlackBeard Plugin
 * General Utility Helper
 *
 * @author mpeg
 */
class BbUtilityHelper extends AppHelper {

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
		if ( !empty($plugin) && BB::keyExists('plugin.'.$plugin.'.path') ) {
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
	
	
}
