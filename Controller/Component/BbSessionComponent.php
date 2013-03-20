<?php

App::import('Controller/Component', 'SessionComponent');

class BbSessionComponent extends SessionComponent {
	
	
	protected $_Controller;
	
	public function initialize(Controller $controller) {
		parent::initialize($controller);
		$this->_Controller = $controller;
	}
	
	
	
	
	
	
	
	
	
// ----------------------------------------------------------- //
// ---[[   N O T I F I C A T I O N   U T I L I T I E S   ]]--- //
// ----------------------------------------------------------- //
	
	/**
	 * send notifications back to the client.
	 * - flash 'n redirect for standard CakePHP requests
	 * - jsoned data for ajax requests
	 * - jsoned/XML data for REST requests
	 * 
	 * Examples:
	 * method('message', '/url', 'title for the message')
	 * method('message', '/url', array(
	 *		'data1' => 'aa', 
	 *		'data2' => 'bb'
	 * )
	 */

	public function success() {
		$options = $this->_notificationParams(func_get_args(), __('successMsg'));
		$this->flash('success', $options);
	}
	
	public function error() {
		$options = $this->_notificationParams(func_get_args(), __('errorMsg'));
		$this->flash('error', $options);
	}
	
	public function warning() {
		$options = $this->_notificationParams(func_get_args(), __('warningMsg'));
		$this->flash('warning', $options);
	}
	
	public function info() {
		$options = $this->_notificationParams(func_get_args(), __('infoMsg'));
		$this->flash('info', $options);
	}
	
	
	
	
	
	
	/**
	 * CANCEL FORM REQUEST
	 * 
	 * Intecept a cancel request for a form then redirect following
	 * given instructions.
	 * 
	 * It is able to setup a typed flash message
	 */
	public function cancelForm() {
		
		// check for a cancel request to exists
		if (!isset($_POST['form-action-cancel'])) {
			return;
		}
		
		// compose url and options array
		$args	= func_get_args();
		$url	= array_shift($args);
		$text	= array_shift($args);
		
		if (!is_array($text)) {
			$options = array(
				'text'	=> $text,
				'title' => array_shift($args),
				'type'	=> array_shift($args)
			);
		} else {
			$options = $text;
		}
		
		$options = BB::extend(array(
			'text'	=> null,
			'title' => null,
			'type'	=> 'info'
		), $options);
		
		// AJAX
		$this->ajaxFlash(BB::extend(array(
			'url' => $url
		), $options));
		
		// REST
		
		// CakePHP standard request
		if (!empty($options['text'])) {
			$this->flash($options['type'], $options['text'], null, $options['title']);
		}
		
		$this->_Controller->redirect($url);
		
	}
	
	
	
	
	
	
	

	
// ------------------------------------------- //
// ---[[   F L A S H   M E S S A G E S   ]]--- //
// ------------------------------------------- //
	
	/**
	 * $this->Session->flash('success', 'message', '/url', 'title')
	 * (title is optional)
	 * (url is optional)
	 */
	public function flash() {
		// make args and extract type of flash message
		$args = func_get_args();
		$type = array_shift($args);
		
		// make flash 'n redirect
		$options = BB::extend(array(
			'url'		=> null,
			'cancel'	=> null,
			'exit'		=> null
		), $this->_notificationParams($args, __($type.'Msg')));
		
		
		// REDIRECT: fetch url to redirect
		// intercepts multiple redirec typed by used form action
		if (isset($_POST['form-action-cancel']) && !empty($options['cancel'])) {
			$options['url'] = $options['cancel'];
		}
		if (isset($_POST['form-action-save-and-exit']) && !empty($options['exit'])) {
			$options['url'] = $options['exit'];
		}
		
		// handle AJAX request to send JSON response
		$this->ajaxFlash(BB::extend(array('type' => $type), BB::clear($options, array('cancel', 'exit'))));
		
		// set flash message
		$this->setFlash($options['text'], 'default', BB::clear($options, array('text', 'url')), $type );
		
		// apply redirect url
		if (!empty($options['url'])) {
			$this->_Controller->redirect($options['url']);
		}
	}
	
	public function ajaxFlash($options) {
		if (!$this->_Controller->request->is('ajax')) return;
		
		$options = BB::extend(array(
			'forceRedirect' => isset($_POST['ajax-force-redirect'])
		), $options);
		
		// compose absolute url to redirect then estimate if a 
		if (!empty($options['url'])) {
			$next = Router::url($options['url']);
			if ($this->_Controller->request->here != $next || $options['forceRedirect']) {
				$options['redirect'] = Router::url($options['url'], true);
			}
		}
		
		// JSON headers
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');
		
		
		echo json_encode(BB::clear(array(
			'ajax' => BB::clear($options, 'url'),
			'formErrors' => BB::clear($this->ajaxErrors())
		)));
		exit;
	}
	
	
	/**	
	* Pack all validation errors into one object to be sent via JSON.
	* This object contains 2 sub-objects: "models" and "fields"
	* 
	* models: will contain errors with a CakePHP model/errors[] notation.
	* fields: will contain an array of fieldID generated with a standard CakePHP naming rule.
	*         each field will contain error description
	*/
	public function ajaxErrors() {
		$errors = array('models' => array(), 'fields' => array());
		foreach ($this->_Controller->uses as $modelName) {
			
			// handle models from plugins with dotted notation.
			if (strpos($modelName,'.') !== false) list( $plugin, $modelName ) = explode('.',$modelName);
			
			// skip a model with no errors.
			if (empty($this->_Controller->{$modelName}->validationErrors)) continue;
			
			// add model's errors info to an object
			$errors['models'][$modelName] = $this->_Controller->{$modelName}->validationErrors;
			
			// Calculate each error's field ID. Fastest client usage!
			foreach ($this->_Controller->{$modelName}->validationErrors as $fieldName=>$msgs) {
				$errors['fields'][$modelName.Inflector::camelize($fieldName)] = $msgs[0];
			}
			
		}
		return $errors;
	}
	
	
	
	
	
	
	
	
	/**
	 * create a notification utility configuration object from
	 * various kind of inputs.
	 * 
	 * $params should contain a full configuration array at its first position.
	 * $params should be a list of $text, $url, $options params
	 * 
	 */
	protected function _notificationParams($params, $defaults) {
		
		$options = BB::extend(array(
			'text'	=> '',
			'url'	=> ''
		), BB::set($defaults, 'text'));
		
		if (!empty($params)) {
			if (is_array($params[0])) {
				$options = BB::extend($options, $params[0]);
			} else {
				while(count($params) < 3) {
					$params[] = null;
				}
				list($text, $url, $data) = $params;
				if (!empty($text)) {
					$options['text'] = $text;
				}
				$data = BB::set($data, 'title');
				// "url" should contain redirect url and exit url!
				if (is_array($url) && (array_key_exists('url', $url) || array_key_exists('exit', $url))) {
					$data = BB::extend($data, $url);
					$url = null;
				}
				if (!empty($url)) {
					$options['url'] = $url;
				}
				$options = BB::extend($options, $data);
			}
		}
		
		return $options;
	}
	
	
	
	
}

