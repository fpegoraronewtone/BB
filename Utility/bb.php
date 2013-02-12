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
				public static function apply($key) {
								$tmp = self::read($key, array());
								
								$args = func_get_args();
								array_shift($args);
								
								foreach($args as $arg) {
												$tmp = Set::merge($tmp, $arg);
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
								return (0 !== array_reduce(
												array_keys($arr),
												function($a, $b) {return ($b === $a ? $a + 1 : 0);},
												0
        ));
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
																				if ( $val === 0 || $val === '0' || $val === false || !empty($val) ) {
																								return true;
																				}
																				return false;
																};
																break;
												// remove every "non true" values.
												case 'strict':
																$callback = function($val) {
																				return !empty($val) && $val;
																};
																break;
								}
								
								// custom callback
								if (gettype($mode) == 'function') {
												$callback = $mode;
								}
								
								return Hash::filter($arr, $callback);
				}
				
				/**
				 * remove a list of keys ($remove) from given array then clear it
				 */
				public static function clear($arr = array(), $remove = array(), $mode = 'val') {
								if (!is_array($remove)) {
												$remove = array($remove);
								}
								if (!empty($remove)) {
												foreach($remove as $tmp) {
																unset($arr[$tmp]);
												}
								}
								// prevent filtering empty values
								if ($mode == false) {
												return $arr;
								}
								return self::clearEmpty($arr, $mode);
				}
				
				/**
     * alter array content by merging with other arrays or values
				 * 
				 * '$__key' => 'will reset "key" in $a array
				 * '$++key' => 'will append "key" in $b to $a[key]
				 * 'key' => $__remove__$ -> will remove "key" from $a
				 * 
				 */
				public static function extend() {
								$args = func_get_args();
								
								$a = current($args);
								while( ($b = next($args)) !== false ) {
												
												// skip empty extension objects
												if (empty($b)) continue;
												
												// scalar values overrides array and array overrides scalar values!
												if (!is_array($a) || !is_array($b)) {
																$a = $b;
																continue;
												}
												
												// both scalar array are merged preventing duplication of values!
												if (self::isVector($a) && self::isVector($b)) {
																foreach ($b as $tmp) {
																				if (!in_array($tmp,	$a)) {
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
																				if (array_key_exists($ovKey,	$b)) {
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
																if (substr($key,	0, 3) == '$__') {
																				$akey = substr($key, 3);
																				$a[$akey] = $b[$key];
																				continue;
																}
																
																// append strings or sum integers
																if (substr($key,	0, 3) == '$++') {
																				$akey = substr($key, 3);
																				if (!array_key_exists($akey,	$a)) {
																								$a[$akey] = $b[$key];
																				} else {
																								$a[$akey].= $b[$key];
																				}
																				continue;
																}
																
																// remove key from $a array
																if ($b[$key] == '$__remove__$') {
																				unset($a[$key]);
																				continue;
																}
																
																// add non existing keys
																if (!array_key_exists($key,	$a) || empty($a[$key])) {
																				$a[$key] = $b[$key];
																
																// recursive extends keys
																} else {
																				$a[$key] = self::extend($a[$key], $b[$key]);
																}
												}			
								}
								return $a;
				}
    
}