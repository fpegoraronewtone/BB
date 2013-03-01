<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BbLocaleHelper
 *
 * @author mpeg
 */
class BbLocaleHelper extends AppHelper {
	
	public function url($url = NULL, $full = false) {
		
		// skip absolute urls or translate into an array
		if (is_string($url) && (substr($url, 0, 7) === 'http://' || substr($url, 0, 8) === 'https://')) return $url;
		if (is_string($url) ) $url = explode('/', $url);
		
		// fill with language/country context - if params exists!
		if (BB::isAssoc($url)) {
			if (!empty($this->request->params['country'])) {
				$url = BB::extend(array('country' => $this->request->params['country']), $url);
			}
			if (!empty($this->request->params['language'])) {
				$url = BB::extend(array('language' => $this->request->params['language']), $url);
			}
		}
		
		// parse
		if ($full !== false) {
			return Router::url($url);
		} else {
			return $url;
		}
		
	}
	
}

