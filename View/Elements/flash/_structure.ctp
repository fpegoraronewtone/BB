<?php
/**
 * BlackBeard
 * standard flash message structure 
 */
echo $this->Html->tag(array(
	'class' 	=> 'flash-msg flash-'.$type,
	'content' 	=> array(
		array(
			'tag' 			=> 'h4',
			'content' 		=> $title
		),
		$message
	)
));