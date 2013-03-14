<?php
/**
 * BB - BlackBeard Core Plugin
 * 
 * Extends core Html helper adding some very useful things!
 * 
 */
App::import('View/Helper', 'HtmlHelper');


class BbHtmlHelper extends HtmlHelper {
	
	public $helpers = array(
		'BB.BbCore'
	);
	
	protected $_LessObject = null;
	
	/**
	 * list all tags who're allowed to exists with an empty value by default
	 */
	public $allowEmptyTags = 'span,td,th,i,b,img,input,iframe';
	
	protected $_tagInteralOptions = array(
		'xtag' => null,
		'allowEmpty' => '',
		// send options to children
		'defaults' => array(),
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
	 * dependency injection - LessCss Compiler
	 * Html::css() is able to compile .less sources only if 
	 * BbLess helper was found inside View object
	 */
	public function beforeRender($viewFile) {
		if (!empty($this->_View->BbLess)) {
			$this->_LessObject = $this->_View->BbLess;
		} else {
			$this->_LessObject = new __EMPTY_CLASS__;
		}
	}
	
	
	/**
	 * CakePHP Overrides
	 * CSS include string is made by CakePHP class.
	 * this method pre-manipulate params to add:
	 *
	 * - compile and minify LESS sources
	 */
	public function css($path, $rel = null, $options = array()) {
		
		// 2 params mode intercepting if 2nd param is $rel or $options
		if ((is_bool($rel) || is_array($rel)) && func_num_args() == 2) {
			$options = $rel;
			$rel = null;
		}
		
		// apply default values 
		$options = BB::setDefaults($options, array(
			'if' => null,
			'prepend' => true,
			'inline' => true
		), array(
			'boolean' => 'inline',
			'else' => 'if'
		));
		
		// shift "rel" attribute to options
		$options['rel'] = $rel;
		
		return $this->_assetBlock('css', $path, $options, function($_this, $itemName, $itemOptions, $options) {
			
			// try compile LESS source - if any compatible LessObject installed
			$_this->_LessObject->compile($itemName);
			
			// check for request CSS to exists
			if (!file_exists($_this->BbCore->assetPath($itemName, array('pathPrefix' => CSS_URL, 'ext' => '.css')))) {
				return false;
			}
			
			// render single item
			return $_this->_parent->css($itemName, $options['rel'], BB::clear($itemOptions, 'if'));
		});
		
	}
	
	
	/**
	 * CakePHP Overrides
	 * 
	 */
	public function script($url, $options = array()) {
		
		return $this->_assetBlock('script', $url, $options, function($_this, $itemName, $itemOptions) {
			return $_this->_parent->script($itemName, BB::clear($itemOptions, 'if'));
		});
		
	}
	
	/**
	 * Shared logic - css() and script() - to build a block of assets
	 * 
	 * 
	 * - conditional CSS tags
	 * - by default view's assets are appended AFTER layout's assets!
	 */
	protected function _assetBlock($block, $asset, $options, $callback) {
		
		// apply default values 
		$options = BB::setDefaults($options, array(
			'if' => null,
			'prepend' => true,
			'inline' => true
		), array(
			'boolean' => 'inline',
			'else' => 'if'
		));
		
		// single asset to array conversion
		if (!is_array($asset)) {
			$asset = array($asset);
		}
		
		// compose per-item default options
		$_options = BB::clear($options, array('inline', 'block', 'prepend'));
		
		if (empty($this->_parent)) {
			$this->_parent = new HtmlHelper($this->_View);
		}
		
		$blockHtml = '';
		foreach ($asset as $itemName => $itemOptions) {
			if (is_numeric($itemName)) {
				$itemName = $itemOptions;
				$itemOptions = array();
			}
			
			// default item options
			$itemOptions = BB::extend($_options, BB::set($itemOptions, 'if'), array(
				'$__overrides__$' => array('block'),
				'inline' => true,
			));
			
			$itemHtml = BB::callback($callback, $this, $itemName, $itemOptions, $options);
			if ($itemHtml === false) continue;
			
			if (!empty($itemOptions['if'])) {
				$itemHtml = '<!--[if ' . $itemOptions['if'] . ']>' . $itemHtml . '<![endif]-->';
			}
			
			$blockHtml.= "\n" . $itemHtml;
			
		}
		
		// inherited code - default block name
		if (!$options['inline'] && empty($options['block'])) {
			$options['block'] = $block;
		}
		
		// inherited code - output or append to block?
		if (empty($options['block'])) {
			return $blockHtml;
		} else {
			// prepend - view's css goes AFTER layout's css!
			if ($options['prepend']) {
				$block = $this->_View->fetch($options['block']);
				$this->_View->assign($options['block'], $blockHtml . $block);
			} else {
				$this->_View->append($options['block'], $blockHtml);
			}
		}
		
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
		$options['defaults'] = BB::setDefaultAttrs($options['defaults']);
		
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
					
					// repeater content as callable method
					// callable params are:
					// - $_View (context)
					// - $i (iterator)
					// - $repeaterItem
					// - $itemOptions
					// - $conteiner Options
					// 
					// callback should return STRING as content for item tag
					// or a complete tag configuration array to extends 
					// item settings
					// 
					// @TODO: write tests!
					} elseif (is_callable($text)) {
						$tmp = BB::callback($text, $this->_View, $i, $repeaterItem, $itemOptions, $options);
						if (is_array($tmp)) {
							echo $this->tag(BB::extend(array('tag' => $name), $itemOptions, $tmp));
						} else {
							echo $this->tag($name, $tmp, $itemOptions);
						}
						
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
		
		// callable content logic
		// @TODO: write tests!
		if (is_callable($text)) {
			$tmp = BB::callback($text, $this->_View, $name, $options);
			if (is_array($tmp)) {
				$text = $this->tag(BB::extend(array('tag' => $name), $options, $tmp));
			} else {
				$text = $this->tag($name, $tmp, $options);
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
			$dataExtend = BB::extend($options['defaults'], array('data' => $options['data'], 'dataOptions' => $options['dataOptions']));
			ob_start();
			if (BB::isVector($text)) {
				foreach($text as $childOptions) {
					
					
					// callable content item
					// should return a string or a full configuration tag object
					if (is_callable($childOptions)) {
						$childOptions = BB::callback($childOptions, $this->_View);
						if (is_array($childOptions)) $childOptions = $this->tag($childOptions);
					
					// array content item
					} elseif (is_array($childOptions)) {
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
		if (!empty($options['data']) && (BB::isAssoc($options['data']) || $options['data'] === true)) {
			if ($options['data'] === true) $options['data'] = array();
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
					'confirm' => '',
					'escape' => false
				), $options));
			case 'render':
				$clear = BB::extend(array_keys($this->_tagInteralOptions), array(
					'href',
					'confirm'
				));
				$mandatory = array(
					'title' => ''
				);
				$confirmMessage = $options['confirm'];
				return $this->link($text, $options['href'], BB::extend($mandatory, BB::clear($options, $clear)), $confirmMessage);
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
				
				$src = $options['src'];
				$options = BB::extend($mandatory, BB::clear($options, $clear));
				
				if (!empty($options['href'])) {
					
					$linkDefaults = array(
						'href' => $options['href'],
						'title' => '',
						'rel' => '',
						'target' => '',
						'linkOptions' => array()
					);
					
					$options = BB::extend($linkDefaults, $options);
					$linkOptions = BB::setDefaultAttrs($options['linkOptions']);
					
					if (!empty($options['href']))	$linkOptions['href']	= $options['href'];
					if (!empty($options['title']))	$linkOptions['title']	= $options['title'];
					if (!empty($options['rel']))	$linkOptions['rel']		= $options['rel'];
					if (!empty($options['target'])) $linkOptions['target']	= $options['target'];
					$options = BB::clear($options, array_keys($linkDefaults));
					
					return $this->tag(BB::extend(array(
						'xtag' => 'link',
						'show' => $this->image($src, $options)
					), $linkOptions));
					
				} else {
					return $this->image($src, $options);
				}
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
				
				if (empty($text)) $text = '$__item__$';
				
				$text = array(
					'tag' => 'li',
					'repeater' => $items,
					'content' => $text,
					'evenItem' => $options['evenItem'],
					'oddItem' => $options['oddItem'],
					'defaults' => $options['defaults']
				);
				
				return array('ul', $text, BB::clear($options, array('items', 'oddItem', 'evenItem'), false));
		}
	}
	
	
}
