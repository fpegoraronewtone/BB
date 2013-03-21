<?php
/**
 * BB - BlackBeard Core Plugin
 * static library to build menus of hierarchical items.
 * 
 * It uses BB utility to read/write hierarchical data.
 * 
 */


class BBMenu {
	
	protected static $_bbKey = 'BbMenu';
	protected static $_defaults = array(
		'show' => '',
		'url' => '',
		'params' => '',
		'active' => false
	);
	
	
/**	
 * 
 * Used by self::tree() to build the menuItem data as expected by CakePHP's TreeHelper.
 * 
 * @var string
 */
	public static $displayModel 		= 'BbMenu';

	
/**
 * 
 * Used to store item's childrenhood data and to output it by the "tree()" method.
 * @var unknown_type
 */
	public static $children		= 'children';
	
	
	
	
	
	
// ----------------------------------------------- //
// ---[[   P U B L I C   I N T E R F A C E   ]]--- //
// ----------------------------------------------- //
	
	public static function check($path = '') {
		return BB::check(self::_path($path));
	}
	
	public static function append($path = '', $name = '', $data = array()) {
		if (BB::isAssoc($name)) {
			foreach ($name as $key=>$val) {
				self::append($path, $key, $val);
			}
			return;
		}
		$full = self::_path($path, true);
		BB::extendKey($full, self::_make($name, $data));
	}
	
	/**
	 * Insert new item in relation to a key path.
	 */
	public static function insert($path = '', $name = '', $data = array(), $insertBefore = false) {
		if (BB::isAssoc($name)) {
			foreach ($name as $key=>$val) {
				self::insert($path, $key, $val, $insertBefore);
			}
			return;
		}
		$parent = self::_parentPath($path);
		$splice = self::_lastTrunk($path);
		// rebuild parent inserting new item AFTER $splice position
		$old = BB::read($parent);
		$new = array('$__BbMenu__$' => $old['$__BbMenu__$']);
		unset($old['$__BbMenu__$']);
		// prepare string data
		if (!is_array($data)) {
			$data = array(
				'show' => $name,
				'url' => $data
			);
		}
		foreach($old as $key=>$val) {
			if ($key == $splice) {
				if ($insertBefore) {
					$new[$name] = self::_make($data);
					$new[$key] = $val;
				} else {
					$new[$key] = $val;
					$new[$name] = self::_make($data);
				}
			} else {
				$new[$key] = $val;
			}
		}
		BB::write($parent, $new);
	}
	
	public static function after($path = '', $name = '', $data = array()) {
		self::insert($path, $name, $data);
	}
	
	public static function before($path = '', $name = '', $data = array()) {
		self::insert($path, $name, $data, true);
	}
	
	/**
	 * Remove a key from menu.
	 * accepts multiple kinds of inputs:
	 * - single key to remove
	 * - an array of keys
	 * - a list of arguments, one key per argument
	 */
	public static function remove() {
		foreach (func_get_args() as $arg) {
			if (is_array($arg)) {
				foreach($arg as $tmp) {
					BB::delete(self::_path($tmp));
				}
			} else {
				BB::delete(self::_path($arg));
			}
		}
	}
	
	/**
	 * activate or deactivate items by their dotted path.
	 * should recurse for an amount of levels
	 */
	public static function active($path = '', $status = true, $recursive = 0) {
		if (empty($path)) {
			return;
		}
		$full = self::_path($path . '.$__BbMenu__$.active');
		if (BB::check($full)) {
			BB::write($full, $status);
		}
		// "true" means no stop levels
		if ($recursive === true) {
			$recursive = 99; // 99 sub levels??? naah!
		}
		// recursion inside sub menus - decrease $recursive to stop
		// recursion if desired
		if ($recursive > 0) {
			$recursive--;
			foreach (array_keys(BB::read(self::_path($path))) as $sub) {
				if ($sub == '$__BbMenu__$') continue;
				self::active($path.'.'.$sub, $status, $recursive);
			}
		}
		
	}
	
	public static function tree($path = '', $recursive = true) {
		$bbk = self::_path($path);
		$data = BB::read($bbk);
		return self::_tree($data, $recursive);
	}
	
	public static function debugTree($path = '', $recursive = true) {
		debug(self::tree($path, $recursive));
	}
	
	public static function ddebugTree($path = '', $recursive = true) {
		ddebug(self::tree($path, $recursive));
	}
	
	public static function debug($path = '') {
		debug(BB::read(self::_path($path)));
	}
	
	public static function ddebug($path = '') {
		ddebug(BB::read(self::_path($path)));
	}
	
	
	
// ----------------------------------------------- //
// ---[[   I N T E R N A L   M E T H O D S   ]]--- //
// ----------------------------------------------- //
	
	private static function _path($path = '', $touch = false) {
		if (empty($path)) {
			return self::$_bbKey;
		}
		if ($touch) {
			$partial = '';
			foreach(explode('.', $path) as $sub) {
				if (!empty($partial)) {
					$partial.= '.';
				}
				$partial .= $sub;
				BB::extendKey(self::_path($partial), array(
					'$__BbMenu__$' => self::$_defaults
				));
			}
		}
		return self::$_bbKey . '.' . $path;
	}
	
	private static function _parentPath($path = '', $full = true) {
		if (empty($path)) {
			return $path;
		}
		$path = explode('.', $path);
		array_pop($path);
		if ($full) {
			$path = self::_path(implode('.', $path));
		}
		return $path;
	}
	
	private static function _lastTrunk($path = '') {
		if (empty($path)) {
			return $path;
		}
		$path = explode('.', $path);
		return array_pop($path);
	}
	
	/**
	 * compose a menu item, apply default values
	 * it handles sub-menus in "menu" key.
	 */
	private static function _make($name = '', $data = array()) {
		if (is_array($name)) {
			// extract optional sub menu
			if (array_key_exists('menu', $name)) {
				$menu = $name['menu'];
				unset($name['menu']);
			}
			// apply defaults menu data options
			$data = array('$__BbMenu__$' => BB::extend(self::$_defaults, $name));
			// add sub menus - recursion to build sub items
			if (!empty($menu)) {
				foreach ($menu as $key=>$val) {
					if (!is_array($val)) {
						$val = array(
							'show' => $key,
							'url' => $val
						);
					}
					$data[$key] = self::_make($val);
				}
			}
			return $data;
		} else {
			if (!is_array($data)) {
				$data = array(
					'show' => $name,
					'url' => $data
				);
			}
			return array($name => self::_make($data));
		}
	}
	
	private static function _tree($data, $recursive) {
		if (empty($data)) {
			$data = array();
		}
		if ($recursive === true) {
			$recursive = 99;
		}
		$items = array();
		foreach ($data as $key=>$val) {
			if ($key == '$__BbMenu__$') continue;
			$item = array(
				self::$displayModel => $val['$__BbMenu__$'],
				self::$children => array()
			);
			if ($recursive) {
				$item[self::$children] = self::_tree($val, $recursive-1);
			}
			$items[] = $item;
		}
		return $items;
	}
	
	
}
