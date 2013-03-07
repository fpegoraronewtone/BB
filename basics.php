<?php
/**
 * BB - BlackBeard Core Plugin
 * 
 * this piece of logic will include all resourced used by BB plugin.
 * 
 */


/**
 * Start time used to debug execution time
 */
if (!defined('BB_START')) {
	define('BB_START', microtime());
}


/**
 * debug() then die.
 */
if (!function_exists('ddebug')) {    
    function ddebug($var, $showHtml = null, $showFrom = true) {
        if (Configure::read('debug') > 0) {
            debug($var, $showHtml, $showFrom);
            exit;
        }
    }
}


/**
 * Return elapsed time from BB initialization.
 */
function bbTime( $print = true, $die = true ) {
	$time 	= round(microtime() - BB_START, 4);
	$stime	= $time . 's.';
	if ($print === 2 && Configure::read('debug')) {
		debug($stime); 
	} elseif ($print) {
		echo $stime;
	}
	if ($die) {
		die();
	}
	return $time;
}

/**
 * Dump a stack of timer results into a log file in TMP folder
 * to elaborate average time of given breakpoint.
 */
function bbAvgTime($print = true, $die = true) {
	$time = bbTime(false,false);
	$_logPath = TMP . 'bbAvgTime.txt';
	
	if (file_exists($_logPath)) {
		$log = unserialize(file_get_contents($_logPath));
		if (!is_array($log)) unset($log);
	}
	
	if (empty($log)) {
		$log = array(
			'sum' 	=> 0,
			'tot'	=> 0,
			'avg'	=> 0,
			'time' 	=> array(),
		);
	}
	
	$log['time'][] = $time;
	$log['sum'] += $time;
	$log['tot'] += 1;
	$log['avg'] = round($log['sum'] / $log['tot'], 4);
	
	@file_put_contents($_logPath, serialize($log));
	
	if ($print === 3 && Configure::read('debug')) { debug($log); }
	elseif ($print === 3) { print_r($log); }
	elseif ($print === 2 && Configure::read('debug')) { debug($log['avg'].' s.'); }
	elseif ($print) { echo $log['avg'].' s.'; }
	
	if ($die) die();
	return $log;
}

function bbAvgReset() {
	@unlink( TMP . 'bbAvgTime.txt' );	
}



/**
 * This is a generic "do nothing" function useful as callback default value.
 */
if (!function_exists('foo')) {
	function foo() {}
}

/**
 * This is an empty class with a catch all method declared.
 * It may be useful for dependency injection defaults
 */
if (!class_exists('__EMPTY_CLASS__')) {
	class __EMPTY_CLASS__ {
		public function __call($method, $args) {}
	}
}





// CakePHP hard dependencies:
App::import('Utility', 'Folder');

// Import hard dependencies:
App::import('Utility', 'BB.BB');
App::import('Utility', 'BB.BbMenu');


// Declare lazy loading dependencies:
App::uses('Hash', 'Utility');
App::uses('Set', 'Utility');



