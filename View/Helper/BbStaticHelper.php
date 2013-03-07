<?php
/**
 * BB - BlackBeard Core Plugin
 * 
 * Utilities for stati pages support
 * 
 */
class BbStaticHelper extends AppHelper {
	
	
	
	
	/**
	 * Try to load some controller/action/contry/language based informations
	 * to be applied / rendered before any view logic.
	 * 
	 * This may me useful for menus, logos, footer, etc.
	 * These informations are stored into Elements to fit Cake standards.
	 * 
	 */
	public function beforeRender($viewFile) {
		$this->loadSettings();
	}
	
	
	
	
	/**
	 * Render Markdown Blocks
	 */
	public function afterLayout($file) {
		if (empty($this->_View->Markdown) || !is_callable(array($this->_View->Markdown, 'render'))) return;
		
		// fix: search cross multiple lines
		$html = str_replace("\n", '$__nl__$', $this->_View->__get('output'));

		$html = $this->parseMarkdown($html);
		
		// output parsed source with new line fixed
		$this->_View->__set('output', str_replace('$__nl__$', "\n", $html));
	}
	
	
	
	/**
	 * Load a settings element from "Element/BbStatic" based on controller,
	 * action, country and language params.
	 * 
	 * It falls back to a global settings element!
	 */
	public function loadSettings($controller = '', $action = '') {
		
		if (empty($controller)) $controller = $this->_View->request->params['controller'];
		if (empty($action)) $action = $this->_View->request->params['action'];
		
		$path = array('BbStatic');
		$path[] = Inflector::camelize($controller);
		$path[] = Inflector::camelize($action);
		
		// Plugin.BbStatic/Controller/Action/
		$spath = implode('/', $path) . '/'; 
		if (!empty($this->_View->request->params['plugin'])) $spath = Inflector::camelize($this->_View->request->params['plugin']) . '.' . $spath;
		
		if (!empty($this->_View->request->params['country']) && !empty($this->_View->request->params['language'])) {
			$tmp = $spath . "{$this->_View->request->params['country']}-{$this->_View->request->params['language']}";
			if ($this->_View->elementExists($tmp)) {
				$this->_View->assign('bbStaticBefore', $this->_View->element($tmp));
				return;
			}
		}
		
		if (!empty($this->_View->request->params['language'])) {
			$tmp = $spath . $this->_View->request->params['language'];
			if ($this->_View->elementExists($tmp)) {
				$this->_View->assign('bbStaticBefore', $this->_View->element($tmp));
				return;
			}
		}
		
		$tmp = $spath . 'globals';
		if ($this->_View->elementExists($tmp)) {
			$this->_View->assign('bbStaticBefore', $this->_View->element($tmp));
			return;
		}
		
		// Plugin.BbStatic/Controller/
		array_pop($path);
		$spath = implode('/', $path) . '/'; 
		if (!empty($this->_View->request->params['plugin'])) $spath = Inflector::camelize($this->_View->request->params['plugin']) . '.' . $spath;
		
		if (!empty($this->_View->request->params['country']) && !empty($this->_View->request->params['language'])) {
			$tmp = $spath . "{$this->_View->request->params['country']}-{$this->_View->request->params['language']}";
			if ($this->_View->elementExists($tmp)) {
				$this->_View->assign('bbStaticBefore', $this->_View->element($tmp));
				return;
			}
		}
		
		if (!empty($this->_View->request->params['language'])) {
			$tmp = $spath . $this->_View->request->params['language'];
			if ($this->_View->elementExists($tmp)) {
				$this->_View->assign('bbStaticBefore', $this->_View->element($tmp));
				return;
			}
		}
		
		$tmp = $spath . 'globals';
		if ($this->_View->elementExists($tmp)) {
			$this->_View->assign('bbStaticBefore', $this->_View->element($tmp));
			return;
		}
		
		// Plugin.BbStatic/
		array_pop($path);
		$spath = implode('/', $path) . '/'; 
		if (!empty($this->_View->request->params['plugin'])) $spath = Inflector::camelize($this->_View->request->params['plugin']) . '.' . $spath;
		
		if (!empty($this->_View->request->params['country']) && !empty($this->_View->request->params['language'])) {
			$tmp = $spath . "{$this->_View->request->params['country']}-{$this->_View->request->params['language']}";
			if ($this->_View->elementExists($tmp)) {
				$this->_View->assign('bbStaticBefore', $this->_View->element($tmp));
				return;
			}
		}
		
		if (!empty($this->_View->request->params['language'])) {
			$tmp = $spath . $this->_View->request->params['language'];
			if ($this->_View->elementExists($tmp)) {
				$this->_View->assign('bbStaticBefore', $this->_View->element($tmp));
				return;
			}
		}
			
		$tmp = $spath . 'globals';
		if ($this->_View->elementExists($tmp)) {
			$this->_View->assign('bbStaticBefore', $this->_View->element($tmp));
			return;
		}	
	}
	
	
	
	/**
	 * Fetch markdown blocks from delimiters inside given text
	 */
	public function parseMarkdown($str) {
		
		// search and parses markdown blocks
		$start = '<!-- Markdown -->';
		$end = '<!-- Markdown -->';
		// fetch blocks
		preg_match_all("|" . $start . "(.*)" . $end . "|U", $str, $matches);
		foreach ($matches[0] as $i=>$find) {
			// fetch clean Markdown source
			$md = str_replace('$__nl__$', "\n", $matches[1][$i]);
			
			// apply template behavior to the md block
			$md = BB::tpl($md, $this->_View->viewVars, array('context' => $this->_View));
			
			$html = $this->_View->Markdown->render($md);
			
			// trigger callback on loaded helpers
			foreach ($this->_View->Helpers->loaded() as $helper) {	
				if (method_exists($this->_View->{$helper}, 'bbStaticAfterMarkdown')) {
					$html = BB::callback(array($this->_View->{$helper}, 'bbStaticAfterMarkdown'), $html);
				}
			}
			
			// replace parsed block
			$str = str_replace($find, $html, $str);
		}
		
		return $str;
	}
	
	
	
	/**
	 * Compose a static page url from a list of folders and sub folders
	 * array('sub', 'page') 
	 * -> array(
	 * 		'country' => 'it',
	 * 		'language' => 'it',
	 * 		'controller' => 'pages',
	 * 		'action' => 'display',
	 *		'sub',
	 *		'page'
	 * )
	 */
	public function pageUrl($url, $parse = false, $options = array()) {
		
		// skip absolute urls or translate into an array
		if (is_string($url) && (substr($url, 0, 7) === 'http://' || substr($url, 0, 8) === 'https://')) return $url;
		if (is_string($url) ) $url = explode('/', $url);
		
		$options = BB::extend(array(
			'controller' => $this->request->params['controller'],
			'action' => 'display',
		), BB::set($options, 'controller'));
		
		// fill with static controller info.
		if (BB::isVector($url)) {
			$url = BB::extend(array(
				'controller' => $options['controller'],
				'action' => $options['action'],
			), $url);
		}
		
		// fill with language/country context - demanded to BbLocale Helper
		if (isset($this->_View->BbLocale)) {
			$url = $this->_View->BbLocale->url($url);
		}
		
		// parse
		if ($parse !== false) {
			return Router::url($url);
		} else {
			return $url;
		}
	}
	
	// usefull with BB::tpl()
	public function pageUrl2String($url, $options = array()) {
		return $this->pageUrl($url, true, $options);
	}
	
	public function homeUrl() {
		return $this->pageUrl('/');
	}
	
	public function isHomeUrl($url) {
		if (is_array($url)) {
			$url = Router::url($url);
		}
		if ($url === $this->homeUrl()) {
			return true;
		}
	}
	
	public function isActiveUrl($url) {
		if (is_array($url)) {
			$url = Router::url($url);
		}
		if ($this->isHomeUrl($url)) {
			if ($url === $this->request->here) {
				return true;
			}
		} else {
			if (strpos($this->request->here, $url) !== false) {
				return true;
			}
		}
	}
	
	public function isHome() {
		return $this->isHomeUrl($this->request->here);
	}
	
}

