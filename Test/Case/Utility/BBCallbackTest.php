<?php
/**
 * BB Test Case
 * test "callback" method of BB library
 * 
 * BB::callback() is a unified interface to run callback logic with
 * infinite params
 */

App::uses('bb', 'BB.Utility');
App::uses('Controller', 'Controller');
App::uses('View', 'View');
App::uses('BbHtmlHelper', 'BB.View/Helper');

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
	public $Html = null;
	
	public function setUp() {
		parent::setUp();
		
		// Test context
		$this->testCallback = new BB_testCallbackClass();
		
		// HtmlHelper used to test context behavior
		$Controller = new Controller();
		$View = new View($Controller);
		$this->Html = new BbHtmlHelper($View);
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
	
	/**
	 * Test callback's execution context ($this) and execution arguments.
	 * third test implements an aliasing!
	 */
	public function testContext01() {
		$this->assertEqual(
			BB::callback('$this->byInstance', 'Test', BB_CALLBACK_OPT, $this->testCallback),
			'BB_testCallbackClass::byInstance(Test)'
		);
		$this->assertEqual(
			BB::callback('$this->testCallback->byInstance', 'Test', BB_CALLBACK_OPT, $this),
			'BB_testCallbackClass::byInstance(Test)'
		);
		$this->assertEqual(
			BB::callback('$TestObjectName->byInstance', 'Test', BB_CALLBACK_OPT, $this, array('TestObjectName' => $this->testCallback)),
			'BB_testCallbackClass::byInstance(Test)'
		);
	}
	
	
	public function testHtmlHelper() {
		// giving access to class's context
		$this->assertEqual(
			BB::callback('$this->Html->tag', 'h1', 'Test', 'className', BB_CALLBACK_OPT, $this),
			'<h1 class="className">Test</h1>'
		);
		$this->assertEqual(
			BB::callback('$this->Html->tag', 'h1', 'Test', 'color:red', BB_CALLBACK_OPT, $this),
			'<h1 style="color:red">Test</h1>'
		);
		// giving HtmlHelper as execution object
		$this->assertEqual(
			BB::callback('$Html->tag', 'h1', 'Test', 'className', BB_CALLBACK_OPT, array('Html' => $this->Html)),
			'<h1 class="className">Test</h1>'
		);
		$this->assertEqual(
			BB::callback('$Html->tag', 'h1', 'Test', 'color:red', BB_CALLBACK_OPT, array('Html' => $this->Html)),
			'<h1 style="color:red">Test</h1>'
		);
		// giving HtmlHelper as execution context
		$this->assertEqual(
			BB::callback('$this->tag', 'h1', 'Test', 'className', BB_CALLBACK_OPT, $this->Html),
			'<h1 class="className">Test</h1>'
		);
		$this->assertEqual(
			BB::callback('$this->tag', 'h1', 'Test', 'color:red', BB_CALLBACK_OPT, $this->Html),
			'<h1 style="color:red">Test</h1>'
		);
	}
	
	/**
	 * You can give an array of arguments instead of a list of.
	 * It may be useful when composing a callback programmatically!
	 */
	public function testCallWithArrayArguments() {
		$this->assertEqual(
			BB::callback(array('$this->tag', 'h1', 'Test', 'color:red', BB_CALLBACK_OPT, $this->Html)),
			'<h1 style="color:red">Test</h1>'
		);
	}
	
}

