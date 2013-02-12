<?php
/**
 * BB - BlackBeard Core Plugin
 * 
 * this piece of logic will include all resourced used by BB plugin.
 * 
 */


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


// CakePHP hard dependencies:
App::import('Utility', 'Folder');

// Import hard dependencies:
App::import('Utility', 'BB.bb');


// Declare lazy loading dependencies:
App::uses('Hash', 'Utility');
App::uses('Set', 'Utility');



