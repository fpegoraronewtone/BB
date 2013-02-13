<?php
/**
 * BB - BlackBeard Core Plugin
 * 
 * Main BlackBeard Component
 * using this component
 * 
 */
class BbCoreComponent extends Component {
	
	public function initialize(Controller $Controller) {
		
		// Inject BB's helpers extensions
		$Controller->helpers = BB::extend($Controller->helpers, array(
			'Html' => array('className' => 'BB.BbHtml')
		));
		
	}
	
}
