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
	
	
	public function count($v) {return count($v);}
	public function pad($v) {return "000$v";}
	
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
		$options = BB::setDefaultAttrs($options, $internalOptions = array(
			'allowEmpty' => $this->allowEmptyTags,
			'if' => true,
			'else' => null,
			'prepend' => '',
			'append' => '',
			'repeater' => null,
			// options to templating with tag's text
			'data' => array(),
			'dataOptions' => array()
		));
		
		// ***** UNDER DEVELOPE ******
		// -- REPEATER LISTS --
		if (!empty($options['repeater'])) {
			$repeater = $options['repeater'];
			$options = BB::clear($options, 'repeater', false);
			ob_start();
			foreach ($repeater as $repeaterItem) {
				echo $this->tag($name, $text, BB::extend($options, array('data' => $repeaterItem)));
			}
			return ob_get_clean();
		}
		// ***** UNDER DEVELOPE ******
		
		// handle conditional tag option:
		switch( gettype($options['if']) ) {
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
		
		// $text as Array means sub-tags to be rendered
		if (is_array($text)) {
			ob_start();
			if (BB::isVector($text)) {
				foreach($text as $childOptions) {
					if (is_array($childOptions)) {
						$childOptions = $this->tag(BB::defaults($childOptions, array('data' => $options['data'], 'dataOptions' => $options['dataOptions'])));
					}
					echo $childOptions;
				}
			} else {
				echo $this->tag(BB::defaults($text, array('data' => $options['data'], 'dataOptions' => $options['dataOptions'])));
			}
			$text = ob_get_clean();
		}
		
		// ***** UNDER DEVELOPE ******
		// -- APPLY DYNAMIC DATA --
		if (!empty($options['data'])) {
			$text = BB::tpl($text, $options['data'], $options['dataOptions']);
		}
		// ***** UNDER DEVELOPE ******
		
		// Prevent empty tags
		if (empty($text) && $options['allowEmpty'] !== true) {
			if (!in_array($name, explode(',', $options['allowEmpty']))) return;
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
		
		// super::tag() with cleaned options array
		// "div" tag is applied as default tag type.
		return parent::tag(!empty($name)?$name:'div', $text, BB::clear($options, array_keys($internalOptions)));
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
	
	
	
}

