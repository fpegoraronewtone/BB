<?php
/**
 * Flash Message
 * 
 */
echo $this->element('BB.flash/_structure',array(
	'type' 		=> 'info',
	'title'		=> !empty($title)	? $title	: '',
	'message' 	=> !empty($message)	? $message	: ''
));