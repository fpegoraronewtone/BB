<?php
/**
 * BB - BlackBeard Core Plugin
 * 
 * Extends core Html helper adding some very useful things!
 * 
 */
App::import('View/Helper', 'HtmlHelper');

class BbHtmlHelper extends HtmlHelper {
	
	/**
	 * list all tags who're allowed to exists with an empty value by default
	 */
	public $allowEmptyTags = 'span,td,th,i,b,img,input,iframe';
	
	protected $_tagInteralOptions = array(
		'xtag' => null,
		'allowEmpty' => '',
		// conditional options
		'if' => true,
		'else' => null,
		// append/prepend contents
		'prepend' => '',
		'append' => '',
		// options to templating with tag's text
		'data' => array(),
		'dataKey' => null,
		'dataOptions' => array(),
		// options to repeat a tag through a dataset
		'repeater' => '$__repeater__$',
		'oddItem' => array(),
		'evenItem' => array()
	);
	
	
	/**
	 * Constructor
	 * used to register xtags
	 */
	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);
		BB::registerXtag('image', array($this, 'xtagImage'));
		BB::registerXtag('link', array($this, 'xtagLink'));
		BB::registerXtag('list', array($this, 'xtagList'));
	}


	/**
	 * CakePHP Override
	 * implement a full or partial array configuration to nested tags by
	 * setting $name as a single array param.
	 * 
	 * $text should be an array, this way tag() recurse in parse $text
	 * as a full array config tag who's result is used as plain text content
	 * for actual tag definition.
	 * 
	 */
	public function tag($name = '', $text = null, $options = array()) {
		
		// FULL ARRAY CONFIGURATION:
		// intercepts first type to handle full array configuration usage
		if (is_array($name) && func_num_args() == 1) {
			// vector $options rapresents a list of tags to render:
			if (BB::isVector($name)) {
				ob_start();
				foreach($name as $tagOptions) {
					if (!is_array($tagOptions)) {
						$tagOptions = array('content' => $tagOptions);
					}
					echo $this->tag($tagOptions);
				}
				return ob_get_clean();
			// associative $options rapresents a single tag configuration object
			} else {
				return $this->_tagWithArrayConfig($name);
			}
		}
		
		// Apply default internal options to create complex behaviors
		$this->_tagInteralOptions['allowEmpty'] = $this->allowEmptyTags;
		$options = BB::setDefaultAttrs($options, $this->_tagInteralOptions);
		
		// xTag: make options
		if (!empty($options['xtag'])) {
			list($name, $text, $options) = BB::xtagCallback('options', $options['xtag'], $name, $text, $options);
		}
		
		// $dataKey filter a subset of orignal dynamic data to reduce
		// data propagation to required tree only
		if (!empty($options['dataKey']) && is_string($options['dataKey']) && BB::isAssoc($options['data'])) {
			$candidateData = Hash::extract($options['data'], $options['dataKey']);
			if (!empty($candidateData) && is_array($candidateData)) {
				$options['data'] = $candidateData;
			} else {
				$options['data'] = array();
			}
		}
		
		// $repeater
		// cycle through lists of data
		if ($options['repeater'] !== '$__repeater__$') {
			// parse non-array values
			if ($options['repeater'] === true || $options['repeater'] === 'self' || $options['repeater'] === 'data') {
				$options['repeater'] = $options['data'];
			} elseif (is_string($options['repeater'])) {
				$options['repeater'] = Hash::extract($options['data'], $options['repeater']);
			}
			// not empty list will cycle each item
			if (!empty($options['repeater']) && BB::isVector($options['repeater'])) {
				$repeater = $options['repeater'];
				$repeaterOdd = BB::setDefaultAttrs($options['oddItem']);
				$repeaterEven = BB::setDefaultAttrs($options['evenItem']);
				$options = BB::clear($options, array('repeater', 'oddItem', 'evenItem'), false);
				
				ob_start();
				foreach ($repeater as $i=>$repeaterItem) {
					
					$itemData = array('$__data' => $repeaterItem);
					if (is_array($itemData['$__data'])) {
						$itemData['$__data']['__$'] = array(
							'i' => $i,
							'di' => ($i+1),
							'type' => ($i%2)?'even':'odd',
							'even' => ($i%2)?true:false,
							'odd' => ($i%2)?false:true,
						);
					}
					
					// build item options with even/odd attributes
					$itemOptions = BB::extend($options, ($i%2)?$repeaterEven:$repeaterOdd, $itemData);
					
					// item's content is replaced by 'data' set to implement
					// a list of sub-configuration tags
					if (!empty($text) && $text === '$__item__$') {
						echo $this->tag($name, $repeaterItem, BB::clear($itemOptions, 'data', false));
					
					// item's content is always the same teplate or data
					// structure and inherits 'data' item as 'data' attrinute
					} else {
						echo $this->tag($name, $text, $itemOptions);
					}
				}
				return ob_get_clean();
			// empty list return nothing
			} else {
				return;
			}
		}
		
		// handle conditional tag option:
		switch(gettype($options['if'])) {
			case 'string':
			case 'object':
			case 'array':
				$options['if'] = $this->_solveConditionalTag($name, $text, $options);
				break;
		}
		// conditional content
		if (!$options['if']) {
			if (!empty($options['else'])) {
				$text = $options['else'];
			} else {
				return;
			}
		}
		
		// xTag: beforeRender
		if (!empty($options['xtag'])) {
			$xtag = BB::xtagCallback('beforeRender', $options['xtag'], $name, $text, $options);
			if (!is_array($xtag) && !is_null($xtag)) return $xtag;
			if (is_array($xtag)) list($name, $text, $options) = $xtag;
		}
		
		// $text as Array means sub-tags to be rendered
		// sub items inherith dynamic data and dataOptions to be able to
		// render sub-templates
		if (is_array($text)) {
			$dataExtend = array('data' => $options['data'], 'dataOptions' => $options['dataOptions']);
			ob_start();
			if (BB::isVector($text)) {
				foreach($text as $childOptions) {
					if (is_array($childOptions)) {
						// fix - accept last integer key as content
						$tmp = array_keys($childOptions);
						if (is_numeric(array_pop($tmp))) $childOptions['content'] = array_pop($childOptions);
						
						$childOptions = $this->tag(BB::defaults($childOptions, $dataExtend));
					}
					echo $childOptions;
				}
			} else {
				// fix - accept last integer key as content
				$tmp = array_keys($text);
				if (is_numeric(array_pop($tmp))) $text['content'] = array_pop($text);
				
				echo $this->tag(BB::defaults($text, $dataExtend));
			}
			$text = ob_get_clean();
		}
		
		// Parse text content as template for dynamic data
		// default context is View's class
		if (!empty($options['data']) && BB::isAssoc($options['data'])) {
			$options['dataOptions'] = BB::extend(array('context' => $this->_View), $options['data']);
			$text = BB::tpl($text, $options['data'], $options['dataOptions']);
		}
		
		// Prevent empty tags
		if (empty($text) && $options['allowEmpty'] !== true) {
			if ($options['allowEmpty'] === false || !in_array($name, explode(',', $options['allowEmpty']))) return;
		}
		
		// xTag: render
		if (!empty($options['xtag'])) {
			$xtag = BB::xtagCallback('render', $options['xtag'], $name, $text, $options);
			if (!is_array($xtag) && !is_null($xtag)) return $xtag;
			if (is_array($xtag)) list($name, $text, $options) = $xtag;
		}
		
		// prepend - append content
		if (!empty($options['prepend'])) {
			if (is_array($options['prepend'])) {
				$text = $this->tag($options['prepend']) . $text;
			} else {
				$text = $this->tag($options['prepend'], '') . $text;
			}
		}
		if (!empty($options['append'])) {
			if (is_array($options['append'])) {
				$text.= $this->tag($options['append']);
			} else {
				$text.= $this->tag($options['append'], '');
			}
		}
		
		// xTag: afterRender
		if (!empty($options['xtag'])) {
			$xtag = BB::xtagCallback('afterRender', $options['xtag'], $name, $text, $options);
			if (!is_array($xtag) && !is_null($xtag)) return $xtag;
			if (is_array($xtag)) list($name, $text, $options) = $xtag;
		}
		
		// super::tag() with cleaned options array
		// "div" tag is applied as default tag type.
		return parent::tag(!empty($name)?$name:'div', $text, BB::clear($options, array_keys($this->_tagInteralOptions)));
	}
	
	protected function _tagWithArrayConfig($options) {
		
		$options = BB::extend(array(
			'tag' => '',
			'content' => '',
			'show' => ''
		), $options);
		
		// fetch last item as content for the array
		if (empty($options['show']) && empty($options['content'])) {
			$keys = array_keys($options);
			if (is_numeric(array_pop($keys))) {
				$options['content'] = array_pop($options);
			}
		}
		
		// parse content only if empty a direct "show" value
		if (empty($options['show'])) {
			
			// direct sub-tag configuration array
			if (BB::isAssoc($options['content'])) {
				$options['content'] = array($options['content']);
			}
			
			$options['show'] = $options['content'];
		}
		
		// does not remove null or empty options... tag() may need NULL
		// informations! (es "if" conditions)
		return $this->tag($options['tag'], $options['show'], BB::clear($options,array('tag', 'content', 'show'), false));
	}
	
	/**
	 * Logics to solves conditional tag callbacks with closures or
	 * array format callbacks given 
	 * 
	 */
	protected function _solveConditionalTag($name, $text, $options) {
		
		// plain named coditions:
		if (is_string($options['if'])) {
			$testName = $options['if'];
			$testVal = $options['data'];
		// named conditions with param:
		} elseif (is_array($options['if']) && is_string($options['if'][0])) {
			if (count($options['if']) < 2) return false;
			$testName = $options['if'][0];
			$testVal = Hash::extract($options['data'], $options['if'][1]);
		}
		
		// test named conditions:
		if (isset($testName)) {
			switch($testName) {
				case 'dataNotEmpty': 
				case 'dataKeyNotEmpty':
					return !empty($testVal);
				case 'dataIsEmpty': 
				case 'dataKeyIsEmpty': 
					return empty($testVal);
				case 'dataIsAssoc': 
				case 'dataKeyIsAssoc': 
					return BB::isAssoc($testVal);
				case 'dataIsVector': 
				case 'dataKeyIsVector': 
					return BB::isVector($testVal);
			}
		}
		
		
		if (!is_array($options['if'])) {
			$args = array($options['if']);
		} else {
			$args = $options['if'];
		}
		
		$args = BB::extend($args, array($name, $text, BB::clear($options, 'if', false)));
		
		$res = BB::callback($args);
		if ($res === null) {
			// complex type of conditional input that returns a "null" value
			// when evaluated as callbacks means a callback does not exists
			// so they return a false value!
			if (is_array($options['if']) || is_object($options['if'])) {
				return false;
			} else {
				return $options['if'];
			}
		} else {
			return $res;
		}	
	}
	
	
	
	
	/**
	 * xTag - Link
	 */
	public function xtagLink($mode, $name, $text, $options) {
		switch ($mode) {
			case 'options':
				return array($name, $text, BB::extend(array(
					'href' => '',
					'escape' => false
				), $options));
			case 'render':
				$clear = BB::extend(array_keys($this->_tagInteralOptions), array(
					'href'
				));
				$mandatory = array(
					'title' => ''
				);
				return $this->link($text, $options['href'], BB::extend($mandatory, BB::clear($options, $clear)));
		}
	}
	
	public function xtagImage($mode, $name, $text, $options) {
		switch ($mode) {
			case 'options':
				return array($name, $text, BB::extend(array(
					'src' => ''
				), $options, array(
					'allowEmpty' => true
				)));
			case 'render':
				$clear = BB::extend(array_keys($this->_tagInteralOptions), array(
					'src'
				));
				$mandatory = array(
					'alt' => is_string($options['src']) ? $options['src'] : Router::url($options['src'])
				);
				return $this->image($options['src'], BB::extend($mandatory, BB::clear($options, $clear)));
		}
	}
	
	public function xtagList($mode, $name, $text, $options) {
		switch ($mode) {
			case 'options':
				
				$items = array();
				if (isset($options['items'])) {
					$items = $options['items'];
				} elseif (BB::isVector($text)) {
					$items = $text;
				}
				
				$options = BB::extend(array(
					'evenItem' => null,
					'oddItem' => null
				), $options);
				
				$text = array(
					'tag' => 'li',
					'repeater' => $items,
					'content' => '$__item__$',
					'evenItem' => $options['evenItem'],
					'oddItem' => $options['oddItem']
				);
				
				return array('ul', $text, BB::clear($options, array('items', 'oddItem', 'evenItem'), false));
		}
	}
	
	
}
