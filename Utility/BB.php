<?php
/**
 * BB - BlackBeard Core Plugin
 * 
 * Static utility library to store all BlackBeard's add-ons to CakePHP
 * 
 */


define('BB_CALLBACK_OPT', 'BB_CALLBACK_OPT');


class BB {

	protected static $_xtags = array();	
	
	public static function version() {
		return '1.0.0';
	}

	
	/**
	 * Test a value to be a real "false" PHP value.
	 * 
	 * @param type $val
	 * @return boolean
	 */
	public static function isFalse($val = '') {
		if (!$val || empty($val) || $val == "0") {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Test a value to be TRUE/POSITIVE value.
	 * - non false value
	 * - numbers grower than Zero
	 */
	public static function isTrue($val = '') {
		if (self::isFalse($val)) return false;
		if (is_numeric($val)) return $val > 0;
		return true;
	}





















// -------------------------------------------------- //				
// ---[[   C O N F I G U R E   D R I V E R S   ]] --- //				
// -------------------------------------------------- //

	/*
	 * following methods uses Configure interface to store key=>value nested
	 * dataset.
	 * 
	 * all keys are contextualized into a common prefix "bb." to isolate them
	 * from other configured data.
	 * 
	 */

	private static function _configureKey($key = '') {
		$base = 'bb';
		if (!empty($key)) {
			return $base . '.' . $key;
		} else {
			return $base;
		}
	}

	public static function write($key, $val) {
		return Configure::write(self::_configureKey($key), $val);
	}

	public static function delete($key) {
		return Configure::delete(self::_configureKey($key));
	}

	public static function check($key) {
		return Configure::check(self::_configureKey($key));
	}

	/**
	 * Fetch a value from data set but allow a fallback value to be
	 * given if an empty value or key does not exists.
	 */
	public static function read($key, $fallback = null) {
		if (empty($key) && empty($fallback)) {
			$fallback = array();
		}

		$tmp = Configure::read(self::_configureKey($key));
		if (empty($tmp)) {
			return $fallback;
		} else {
			return $tmp;
		}
	}

// --------------------------------------------------------------- //
// ---[[   C O N F I G   D A T A   M A N I P U L A T I O N   ]]--- //
// --------------------------------------------------------------- //

	/**
	 * extend a key value with one or more values.
	 * this method wirks fine with associative arrays.
	 * 
	 * Example:
	 * BB::merge('confg.key', $arr1, $arr2, $etc...)
	 */
	public static function extendKey($key) {
		$tmp = self::read($key, array());

		$args = func_get_args();
		array_shift($args);

		foreach ($args as $arg) {
			$tmp = self::extend($tmp, $arg);
		}

		return self::write($key, $tmp);
	}

// ------------------------------------------------------ //				
// ---[[   C O N F I G U R E   D E B U G G I N G   ]] --- //				
// ------------------------------------------------------ //

	public static function debug($key = '') {
		debug(self::read($key));
	}

	public static function ddebug($key = '') {
		ddebug(self::read($key));
	}

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	











// --------------------------------------------- //
// ---[[   A R R A Y   U T I L I T I E S   ]]--- //
// --------------------------------------------- //

	public static function isVector($arr = array()) {
		if (!is_array($arr)) {
			return false;
		}
		foreach (array_keys($arr) as $i => $key) {
			if ($i !== $key)
				return false;
		}
		return true;
	}

	public static function isAssoc($arr = array()) {
		if (!is_array($arr)) {
			return false;
		}
		return !self::isVector($arr);
	}

	/**
	 * remove all empty values from an array
	 */
	public static function clearEmpty($arr = array(), $mode = 'val') {

		// strict true values are shortcuts to "strict" mode
		if ($mode === true || $mode === 1) {
			$mode = 'strict';
		}

		switch ($mode) {
			// remove every empty or null value but allow "zero"
			// and boolean false param values.
			case 'val':
				$callback = function($val) {
					if ($val === 0 || $val === '0' || $val === false || !empty($val)) {
						return true;
					}
					return false;
				};
				break;
			// remove every "non true" values.
			// "-1" is a "non false" value!!!
			case 'strict':
				$callback = function($val) {
							return !BB::isFalse($val);
						};
				break;
		}

		// custom callback
		if (gettype($mode) == 'function') {
			$callback = $mode;
		}
		
		// vector array needs to reset keys after filtering!
		if (self::isVector($arr)) {
			return array_values(Hash::filter($arr, $callback));
		} else {
			return Hash::filter($arr, $callback);
		}
	}

	/**
	 * remove a list of keys ($remove) from given array then clearEmpty values
	 */
	public static function clear($arr = array(), $remove = array(), $mode = 'val') {
		if (!is_array($remove)) {
			$remove = array($remove);
		}
		if (!empty($remove)) {
			if (self::isVector($arr)) {
				$arr = array_values(array_diff($arr, $remove));
			} else {
				foreach ($remove as $tmp) {
					unset($arr[$tmp]);
				}
			}
		}
		if (self::isTrue($mode)) {
			return self::clearEmpty($arr, $mode);
		} else {
			return $arr;
		}
	}
	
	/**
	 * remove a list of values from the given array then clearEmpty values
	 */
	public static function clearValues($arr = array(), $remove = array(), $mode = 'val') {
		if (self::isVector($arr)) {
			return self::clear($arr, $remove, $mode);
		}
		if (!is_array($remove)) {
			$remove = array($remove);
		}
		if (!empty($remove)) {
			foreach ($arr as $key=>$val) {
				if (in_array($val, $remove)) {
					unset($arr[$key]);
				}
			}
		}
		if (self::isTrue($mode)) {
			return self::clearEmpty($arr, $mode);
		} else {
			return $arr;
		}
	}
	
	/**
	 * remove a list of values or keys from the given array then clearEmpty values
	 */
	public static function clearAll($arr = array(), $remove = array(), $mode = 'val') {
		$arr = self::clearValues($arr, $remove, $mode);
		return self::clear($arr, $remove, $mode);
	}

	/**
	 * alter array content by merging with other arrays or values
	 * 
	 * '$__key' => 'will reset "key" in $a array
	 * '$++key' => 'will append "key" in $b to $a[key]
	 * 'key' => $__remove__$ -> will remove "key" from $a
	 * 
	 * $b array should define a "$__overrides__$" key containing a list of
	 * keys to reset or remove in $a.
	 * 
	 * vector array should implement "$b[] = $--value" to remove "value" item
	 * from $a
	 * 
	 */
	public static function extend() {
		
		// pass given arguments searching for "false" items who're not
		// compatible with WHILE cycle of this method
		// "false" params will reset arguments list to a single false item!!!
		$args = array();
		foreach (func_get_args() as $arg) {
			if ($arg === false && count($args)) {
				$args = array(false);
			} else {
				$args[] = $arg;
			}
		}
		
		$a = current($args);
		while (($b = next($args)) !== false) {
			
			// skip empty extension objects
			if (empty($b)) {
				continue;
			}

			// scalar values overrides array and array overrides scalar values!
			if (!is_array($a) || !is_array($b)) {
				$a = $b;
				continue;
			}

			// both scalar array are merged preventing duplication of values!
			if (self::isVector($a) && self::isVector($b)) {
				foreach ($b as $tmp) {
					// remove value operator
					if (is_string($tmp) && substr($tmp, 0, 3) == '$--') {
						$atmp = substr($tmp, 3);
						if (in_array($atmp, $a)) {
							$a = array_values(array_diff($a, array($atmp)));
							continue;
						}
					// prevent values duplication
					} elseif (!in_array($tmp, $a)) {
						$a[] = $tmp;
					}
				}
				continue;
			}

			// $__overrides__$
			// use reset array filled with keys to be resetted before the 
			// extension action. 
			if (array_key_exists('$__overrides__$', $b)) {
				if (!is_array($b['$__overrides__$'])) {
					$b['$__overrides__$'] = array($b['$__overrides__$']);
				}
				foreach ($b['$__overrides__$'] as $ovKey) {
					// try to preserve key position if possible!
					if (array_key_exists($ovKey, $b)) {
						$a[$ovKey] = null;
					} else {
						unset($a[$ovKey]);
					}
				}
				unset($b['$__overrides__$']);
			}
			
			

			// deep extends key by key
			foreach (array_keys($b) as $key) {
				
				// reset key in $a
				// ignore $__key__$ keys!
				if (substr($key, 0, 3) == '$__' && substr($key, strlen($key)-3, 3) != '__$') {
					$akey = substr($key, 3);
					$a[$akey] = $b[$key];
					continue;
				}
				
				// append strings or sum integers
				if (substr($key, 0, 3) == '$++') {
					$akey = substr($key, 3);
					if (!array_key_exists($akey, $a)) {
						$a[$akey] = $b[$key];
					} else {
						if (is_array($a[$akey])) {
							$a[$akey] = BB::extend($a[$akey], $b[$key]);
						} else {
							$a[$akey].= $b[$key];
						}
					}
					continue;
				}
				
				// remove key from $a array
				if ($b[$key] === '$__remove__$') {
					unset($a[$key]);
					continue;
				}
				
				// add non existing keys
				if (!array_key_exists($key, $a) || empty($a[$key]) || $b[$key] === false) {
					$a[$key] = $b[$key];

				// recursive extends keys
				} else {
					$a[$key] = self::extend($a[$key], $b[$key]);
				}
			}
		}
		return $a;
	}
	
	
	/**
	 * It work much like extend but skip overriding when keys exists!
	 * You should override enyway by using "$__" operator 
	 * or "$__overrides_$" array!
	 */
	public static function defaults() {
		
		// need to bybass a "false" value because it may be considered as
		// end of the while{}!
		$args = array();
		foreach (func_get_args() as $arg) {
			if ($arg === false) {
				$arg = '$__false__$';
			}
			$args[] = $arg;
		}
		
		$a = current($args);
		while (($b = next($args)) !== false) {
			
			// reset real "false" value!
			if ($a === '$__false__$') { $a = false; }
			if ($b === '$__false__$') { $b = false; }
			
			// scalar values overrides array and array overrides scalar values!
			if (!is_array($a) || !is_array($b)) {
				if (empty($a) && !is_bool($a) && $a !== 0) {
					$a = $b;
				}
				continue;
			}
			
			// $__overrides__$
			// use reset array filled with keys to be resetted before the 
			// extension action. 
			if (array_key_exists('$__overrides__$', $b)) {
				if (!is_array($b['$__overrides__$'])) {
					$b['$__overrides__$'] = array($b['$__overrides__$']);
				}
				foreach ($b['$__overrides__$'] as $ovKey) {
					// try to preserve key position if possible!
					if (array_key_exists($ovKey, $b)) {
						$a[$ovKey] = '$__overrides__$';
					} else {
						unset($a[$ovKey]);
					}
				}
				unset($b['$__overrides__$']);
			}
			
			// deep extends key by key
			foreach (array_keys($b) as $key) {
				
				// reset key in $a
				if (substr($key, 0, 3) == '$__' && substr($key, strlen($key)-3, 3) != '__$') {
					$akey = substr($key, 3);
					$a[$akey] = $b[$key];
					continue;
				}
				
				// append strings or sum integers
				if (substr($key, 0, 3) == '$++') {
					$akey = substr($key, 3);
					if (!array_key_exists($akey, $a)) {
						$a[$akey] = $b[$key];
					} else {
						if (is_array($a[$akey])) {
							$a[$akey] = BB::extend($a[$akey], $b[$key]);
						} else {
							$a[$akey].= $b[$key];
						}
					}
					continue;
				}
				
				// apply default value or implement overrides
				if (!array_key_exists($key, $a) || $a[$key] === '$__overrides__$') {
					$a[$key] = $b[$key];
				
				} elseif (is_array($a[$key]) && is_array($b[$key])) {
					$a[$key] = self::defaults($a[$key], $b[$key]);
				}	
			}
		}
		return $a;
	}
	
	
	/**
	 * translate a $origin value from various type to an associative array
	 * by assigning each type of var to a defined key
	 * 
	 * set('Mark', 'name') -> array('name' => 'Mark')
	 * set(22, array('integer' => 'age', 'else' => 'name')) -> array('age' => 22)
	 * set('Mark, array('integer' => 'age', 'else' => 'name')) -> array('name' => 'Mark')
	 */
	public static function set($origin = array(), $options = array()) {
		
		// compose data driver array from mishellaneous type of formats
		if (!is_array($options)) {
			$options = array('string' => $options);
		} else {
			$keys = array_keys($options);
			if (is_numeric(array_pop($keys))) {
				$options['else'] = array_pop($options);
			}
		}
		
		if (!is_array($origin)) {
			$otype = gettype($origin);
			foreach(array_keys($options) as $type) {
				if ($type == $otype) {
					$tmp = array();
					$tmp[$options[$type]] = $origin;
					$origin = $tmp;
				}
			}
		}
		
		
		if (!is_array($origin) && !empty($options['else'])) {
			$tmp = array();
			$tmp[$options['else']] = $origin;
			$origin = $tmp;
		}
		
		return $origin;
	}
	
	
	
	/**
	 * extend $defaults data with $data
	 * $data is translated to associative array using set() with $options rules
	 */
	public static function setExtend($defaults = array(), $data = array(), $options = array()) {
		return BB::extend($defaults, self::set($data, $options));
	}
	
	/**
	 * apply some $defaults values to a given $data.
	 * $data is translated to associative array using set() with $options rules
	 */
	public static function setDefaults($data = array(), $defaults = array(), $options = array()) {
		return BB::defaults(self::set($data, $options), $defaults);
	}
	
	public static function setDefaultAttrs($data = array(), $defaults = array()) {
		// extends tag default values with custom given defaults
		$defaults = BB::extend(array(
			'id' => '',
			'class' => '',
			'style' => ''
		), $defaults);
		
		if (is_array($data)) {
			return self::setDefaults($data, $defaults);
		} elseif (strpos($data, ':') !== false) {
			return self::setDefaults($data, $defaults, 'style');
		} else {
			return self::setDefaults($data, $defaults, 'class');
		}
	}
	
	public static function setDefaultAttrsId($data = array(), $defaults = array()) {
		// extends tag default values with custom given defaults
		$defaults = BB::extend(array(
			'id' => '',
			'class' => '',
			'style' => ''
		), $defaults);
		
		if (is_array($data)) {
			return self::setDefaults($data, $defaults);
		} elseif (strpos($data, ':') !== false) {
			return self::setDefaults($data, $defaults, 'style');
		} else {
			return self::setDefaults($data, $defaults, 'id');
		}
	}
	
	
	
	

	
	
	
	
	
// ------------------------------------------------- //
// ---[[   S T R I N G S   U T I L I T I E S   ]]--- //
// ------------------------------------------------- //
	
	public static function tpl($tpl, $data = array(), $options = array()) {
		
		$options = BB::setExtend(array(
			'before' => '{',
			'after' => '}',
			'swipe' => array(),
			'swipeReplace' => '',
			'clear' => false,
			'context' => null,
			'objects' => null
		), $options, array(
			'boolean' => 'clear',
			'object' => 'context'
		));
		
		
		
		// safe source data from objects translating them to associative!
		// flatting data array should speed up values extracting process for
		// end point keys!
		$data = json_decode(json_encode($data), true);
		$fdata = Hash::flatten($data);
		
		// analyze placeholders
		preg_match_all("|" . $options['before'] . "(.*)" . $options['after'] . "|U", $tpl, $matches);
		for ($i=0; $i< count($matches[0]); $i++) {
			
			$val = '$__noValueFound__$';
			$var = BB::tplVarTokenizer($matches[1][$i]);
			
			// fetch full data - to be sent to a modificator
			if ($var['type'] == '$' && $var['path'] == '$') {
				$val = $data;
			
			// fetch flattened data - to be sent to a modificator
			} elseif ($var['type'] == '$' && $var['path'] == '.') {
				$val = $fdata;
			
			// fetch data from flattened dataset
			} elseif (array_key_exists($var['path'], $fdata)) {
				$val = $fdata[$var['path']];
			
			// fetch data from given dataset
			} elseif (Hash::check($data, $var['path'])) {
				$val = Hash::extract($data, $var['path']) ;
				if (empty($val)) $val = '';
			
			// direct value
			} else {
				$val = $var['path'];
			}
			
			// pass throught multiple modifier
			if ($val !== '$__noValueFound__$') {
				foreach ($var['methods'] as $method) {
					ob_start();
					$cbConfig = array($method['callback'], $val);
					if (!empty($options['context']) || !empty($options['objects'])) $cbConfig[] = BB_CALLBACK_OPT;
					if (!empty($options['context'])) $cbConfig[] = $options['context'];
					if (!empty($options['objects'])) $cbConfig[] = $options['objects'];
					$val = BB::callback($cbConfig);
					ob_get_clean();
				}
				$tpl = str_replace($matches[0][$i], $val, $tpl);
			
			// remove non existing values
			} elseif ($options['clear']) {
				$tpl = str_replace($matches[0][$i], '', $tpl);
			}
		}
		return self::swipe($tpl, $options['swipe'], $options['swipeReplace']);
	}
	
	/**
	 * Apply some swipe rules to a given string to remove some pieces of string.
	 */
	public static function swipe($str = '', $find = array(), $replaceAll = '') {
		if (empty($str)) return $str;
		if (empty($find) || !is_array($find)) $find = array();
		
		// build search/replace arrays
		$_s = array();
		$_r = array();
		foreach ($find as $s=>$r) {
			if (is_numeric($s)) {
				$_s[] = $r;
				$_r[] = $replaceAll;
			} else {
				$_s[] = $s;
				$_r[] = $r;
			}
		}
		
		// swipe a list of strings
		if (is_array($str)) {
			foreach ($str as $i=>$item) {
				$str[$i] = str_replace($_s, $_r, $item);
			}
			return $str;
			
		// swipe a single string
		} else {
			return str_replace($_s, $_r, $str);
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
// ----------------------------------------- //
// ---[[   M I S H E L L A N E O U S   ]]--- //
// ----------------------------------------- //
	
	/**
	 * unified interface to run callbacks
	 * (see TestCase to learn how to use!)
	 */
	public static function callback() {
		
		// get arguments
		$args = func_get_args();
		if (empty($args)) {return;}
		
		// allow to give an array of arguments instead of a list of arguments
		// useful when composing a callback programmatically
		if (count($args) == 1 && BB::isVector($args[0])) {
			$args = $args[0];
		}
		
		// extract execution context and execution objects
		$mode = null;
		$objects = array();
		foreach($args as $i=>$arg) {
			
			// switch arguments read mode;
			if ($arg === BB_CALLBACK_OPT) {
				$mode = 'options';
				unset($args[$i]);
				continue;
			}
			
			switch($mode) {
				case 'options':
					if (is_object($arg)) {
						$objects['this'] = $arg;
					} elseif (BB::isAssoc($arg)) {
						$objects = BB::extend($objects, $arg);
					}
					unset($args[$i]);
					break;
			}
			
		}
		
		// Try to run a method inside given execution objects
		foreach($objects as $name=>$context) {
			$vname = '$' . $name . '->';
			if (substr($args[0], 0, strlen($vname)) === $vname && is_object($context)) {
				$tokens = explode('->', $args[0]);
				array_shift($tokens);
				
				foreach($tokens as $token) {
					if (is_callable(array($context, $token))) {
						array_shift($args);
						return call_user_func_array(array($context, $token), $args);
					} else {
						$context = $context->$token;
					}	
				}
			}
		}
		
		// callable closure
		if (is_callable($args[0]) && gettype($args[0]) == 'object') {
			$callback = array_shift($args);
			return call_user_func_array($callback, $args);
		}
		
		// callable array at first params
		if (is_callable($args[0])) {
			$callback = array_shift($args);
			return call_user_func_array($callback, $args);
		}
		
		// array of params: translate first 2 items into a candidate
		// callable array
		if (is_array($args) && count($args) >= 2) {
			$callable = array(array_shift($args), array_shift($args));
			if (is_callable($callable)) {
				return call_user_func_array($callable, $args);
			}
		}
	}
	
	public static function tplVarTokenizer($str) {
		$results = array(
			'path' => '',
			'type' => null,
			'methods' => array()
		);
		
		$tokens = explode('|', $str);
		
		// path setup
		$results['path'] = trim(array_shift($tokens));
		if (substr($results['path'], 0, 1) === '$') {
			$results['path'] = substr($results['path'], 1);
			$results['type'] = '$';
		}
		
		foreach($tokens as $method) {
			$results['methods'][] = BB::tplMethodTokenizer($method);
		}
		
		return $results;
		
	}
	
	public static function tplMethodTokenizer($str) {
		$results = array(
			'callback' => '',
			'params' => array()
		);
		
		// investigate for a static method request:
		if (strpos($str, '::') !== false && strpos($str, '::') === strpos($str, ':')) {
			$results['callback'] = substr($str, 0, strpos($str, ':', strpos($str, '::')+2));
			if (empty($results['callback'])) $results['callback'] = $str;
			$str = substr($str, strlen($results['callback'])+1);
			$tokens = explode(':', $str);
		
		// direct function request:
		} else {
			$tokens = explode(':', $str);
			$results['callback'] = trim(array_shift($tokens));
		}
		
		// build given params
		if (!empty($tokens)) {
			foreach(explode(',', $tokens[0]) as $param) {
				$results['params'][] = trim($param);
			}
		}
		
		return $results;
	}
	
	
	
	
	
	
	/**
	 * xTag Support
	 */
	
	public static function registerXtag($xname, $callback) {
		self::$_xtags[$xname] = $callback;
	}
	
	public static function xtagCallback($callbackName, $xname, $name, $text, $options) {
		if (array_key_exists($xname, self::$_xtags)) {
			return BB::callback(self::$_xtags[$xname], $callbackName, $name, $text, $options);
		} else {
			return array($name, $text, $options);
		}
	}
	
	
	
}
