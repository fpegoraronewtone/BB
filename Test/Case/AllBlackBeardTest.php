<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AllBbTest
 *
 * @author mpeg
 */
class AllBlackBeardTest extends CakeTestSuite {
	
	public static function suite() {
		$suite = new CakeTestSuite("BlackBeard Core Plugin Test Suite!");
		$suite->addTestDirectory(dirname(__FILE__) . DS . 'Controller' . DS . 'Component');
		$suite->addTestDirectory(dirname(__FILE__) . DS . 'Utility');
		return $suite;
	}
	
}
