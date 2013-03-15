<?php
/**
 * Success Flash Message
 * 
 */
echo $this->element('BB.flash/_structure',array(
	'type' 		=> 'success',
	'title'		=> !empty($title)	? $title	: '',
	'message' 	=> !empty($message)	? $message	: ''
));