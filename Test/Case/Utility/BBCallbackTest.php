<?php
/**
 * BB Test Case
 * test "callback" method of BB library
 * 
 * BB::callback() is a unified interface to run callback logic with
 * infinite params
 */

App::uses('bb', 'BB.Utility');

function BB_testCallbackFunc($p = '') {
	return "BB_testCallbackFunc: $p";
}

class BB_testCallbackClass {
	
	public function byInstance($p = '') {
		return "BB_testCallbackClass::byInstance($p)";
	}
	
	public static function byStatic($p = '') {
		return "BB_testCallbackClass::byStatic($p)";
	}
	
}


class BBCallbackTest extends CakeTestCase {
	
	public $testCallback = null;
	
	public function setUp() {
		parent::setUp();
		$this->testCallback = new BB_testCallbackClass();
	}
	
	
	public function testNonExisting() {
		$this->assertNull(BB::callback('iThinkThisNotExists'));
		$this->assertNull(BB::callback(array('iThinkThisNotExists', 'method')));
		$this->assertNull(BB::callback('iThinkThisNotExists', 'method', 'foo1', 'foo2'));
	}
	
	public function testClosure() {
		$this->assertEqual(
			BB::callback(function() { return 'aaa'; }),
			'aaa'
		);
		$this->assertEqual(
			BB::callback(function($p) { return $p; }, 'aaa'),
			'aaa'
		);
	}
	
	public function testFunction() {
		$this->assertEqual(
			BB::callback('BB_testCallbackFunc', 'foo'),
			'BB_testCallbackFunc: foo'
		);
		$this->assertEqual(
			BB::callback('BB_testCallbackFunc', 22),
			'BB_testCallbackFunc: 22'
		);
	}
	
	public function testStaticMethod() {
		$this->assertEqual(
			BB::callback(array('BB_testCallbackClass', 'byStatic'), 'foo'),
			'BB_testCallbackClass::byStatic(foo)'
		);
		$this->assertEqual(
			BB::callback('BB_testCallbackClass', 'byStatic', 22),
			'BB_testCallbackClass::byStatic(22)'
		);
	}
	
	public function testInstanceMethod() {
		$this->assertEqual(
			BB::callback(array($this->testCallback, 'byInstance'), 'foo'),
			'BB_testCallbackClass::byInstance(foo)'
		);
		$this->assertEqual(
			BB::callback($this->testCallback, 'byInstance', 22),
			'BB_testCallbackClass::byInstance(22)'
		);
	}
	
	public function testStaticMethodByString() {
		$this->assertEqual(
			BB::callback('BB_testCallbackClass::byStatic', 'foo'),
			'BB_testCallbackClass::byStatic(foo)'
		);
		$this->assertEqual(
			BB::callback('BB_testCallbackClass::byStatic', 22),
			'BB_testCallbackClass::byStatic(22)'
		);
	}
	
}

