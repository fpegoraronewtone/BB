<?php
/**
 * Error Flash Message
 * 
 */
echo $this->element('BB.flash/_structure',array(
	'type' 		=> 'error',
	'title'		=> !empty($title)	? $title	: '',
	'message' 	=> !empty($message)	? $message	: ''
));