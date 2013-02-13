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
	
}

