<?php
/**
 * Warning Flash Message
 * 
 */
echo $this->element('BB.flash/_structure',array(
	'type' 		=> 'warning',
	'title'		=> !empty($title)	? $title	: '',
	'message' 	=> !empty($message)	? $message	: ''
));