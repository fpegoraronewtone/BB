<?php

/**
 * BB - BlackBeard Core Plugin
 * 
 * Static utility library to store all BlackBeard's add-ons to CakePHP
 * 
 */
class BB {

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
							return !self::isFalse($val);
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
				if (substr($key, 0, 3) == '$__') {
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
						$a[$akey].= $b[$key];
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
	
	
	public static function set($origin = array(), $defaults = array(), $options = array()) {
		
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
		
		return BB::extend($defaults, $origin);
		
	}
	
	public static function setAttr($origin = array(), $defaults = array()) {
		
		$defaults = BB::extend(array(
			'id' => '',
			'class' => '',
			'style' => ''
		), $defaults);
		
		if (is_array($origin)) {
			return self::set($origin, $defaults);
		} elseif (strpos($origin, ':') !== false) {
			return self::set($origin, $defaults, 'style');
		} else {
			return self::set($origin, $defaults, 'class');
		}
		
	}

}
