<?php
/**
 * BB - BlackBeard Core Plugin
 * 
 * Extends core Html helper adding some very useful things!
 * 
 */
App::import('View/Helper', 'HtmlHelper');

class BbHtmlHelper extends HtmlHelper {
	
	public function foo() {
		echo "BbHelper::foo()";
	}
	
	
	/**
	 * CakePHP Override
	 * implement a full or partial array configuration to nested tags
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
		
		
		// $TEXT AS ARRAY MEANS SUB TAGS
		if (is_array($text)) {
			$text = $this->tag($text);
		}
		
		// Apply default internal options to create complex behaviors
		$options = BB::setAttr($options, $internalOptions = array(
			'allowEmpty' => false,
			'if' => true
		));
		
		// Prevent empty tags
		if (empty($text) && !$options['allowEmpty']) {
			return;
		}
		
		// super::tag() with cleaned options array
		return parent::tag($name, $text, BB::clear($options, array_keys($internalOptions)));
	}
	
	protected function _tagWithArrayConfig($options) {
		
		$options = BB::extend(array(
			'tag' => 'div',
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
			// list of sub-tags declarations
			if (BB::isVector($options['content'])) {
				$options['show'] = '';
				foreach($options['content'] as $childOptions) {
					if (is_array($childOptions)) {
						$childOptions = $this->tag($childOptions);
					}
					$options['show'].= $childOptions;
				}
			} else {
				$options['show'] = $options['content'];
			}
		}
		
		return $this->tag($options['tag'], $options['show'], BB::clear($options,array('tag', 'content', 'show')));
	}
	
}

