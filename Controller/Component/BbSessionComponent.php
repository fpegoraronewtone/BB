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
		
		// AJAX
		
		// REST
		
		// CakePHP standard flash 'n rediret
		$this->flash('success', $options);
	}
	
	public function error() {
		$options = $this->_notificationParams(func_get_args(), __('errorMsg'));
		
		// AJAX
		
		// REST
		
		// CakePHP standard flash 'n rediret
		$this->flash('error', $options);
	}
	
	public function warning() {
		$options = $this->_notificationParams(func_get_args(), __('warningMsg'));
		
		// AJAX
		
		// REST
		
		// CakePHP standard flash 'n rediret
		$this->flash('warning', $options);
	}
	
	public function info() {
		$options = $this->_notificationParams(func_get_args(), __('infoMsg'));
		
		// AJAX
		
		// REST
		
		// CakePHP standard flash 'n rediret
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
		$args = func_get_args();
		$url = array_shift($args);
		$msg = array_shift($args);
		
		if (!is_array($msg)) {
			$options = array(
				'msg' => $msg,
				'title' => array_shift($args),
				'type' => array_shift($args)
			);
		}
		
		$options = BB::extend(array(
			'msg' => null,
			'title' => null,
			'type' => 'info'
		), $options);
		
		// AJAX
		
		// REST
		
		// CakePHP standard request
		if (!empty($options['msg'])) {
			$this->flash($options['type'], $options['msg'], null, $options['title']);
		}
		
		$this->_Controller->redirect($url);
		
	}
	
	
	
	
	
	
	

	
// ------------------------------------------------------- //
// ---[[   T Y P E D   F L A S H   M E S S A G E S   ]]--- //
// ------------------------------------------------------- //
	
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
		$options = $this->_notificationParams($args, __($type.'Msg'));
		$this->setFlash($options['msg'], 'default', BB::clear($options, array('msg', 'url')), $type );
		
		// fetch url to redirect
		$options = BB::extend(array(
			'url' => null,
			'cancelUrl' => null,
			'exitUrl' => null
		), $options);
		
		// intercepts multiple redirec typed by used form action
		if (isset($_POST['form-action-cancel']) && !empty($options['cancel'])) {
			$options['url'] = $options['cancel'];
		}
		
		if (isset($_POST['form-action-save-and-exit']) && !empty($options['exit'])) {
			$options['url'] = $options['exit'];
		}
		
		if (!empty($options['url'])) {
			$this->_Controller->redirect($options['url']);
		}
	}
	
	
	
	
	
	
	
	
	
	/**
	 * create a norification utility configuration object from
	 * various kinfs o
	 */
	protected function _notificationParams($params, $defaults) {
		
		$options = BB::extend(array(
			'msg' => '',
			'url' => ''
		), BB::set($defaults, 'msg'));
		
		if (!empty($params)) {
			if (is_array($params[0])) {
				$options = BB::extend($options, $params[0]);
			} else {
				while(count($params) < 3) {
					$params[] = null;
				}
				list($msg, $url, $data) = $params;
				if (!empty($msg)) {
					$options['msg'] = $msg;
				}
				if (is_array($url)) {
					$data = $url;
					$url = null;
				}
				if (!empty($url)) {
					$options['url'] = $url;
				}
				$options = BB::extend($options, BB::set($data, 'title'));
			}
		}
		
		return $options;
	}
	
	
	
	
}

