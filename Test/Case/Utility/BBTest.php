<?php
/**
 * BB Test Case
 * tests simple methods of BB Library
 */

App::uses('bb', 'BB.Utility');

class BBTest extends CakeTestCase {
	
	
	public function testIsTrue() {
		$this->assertTrue(BB::isTrue(true));
		$this->assertTrue(BB::isTrue(1));
		$this->assertTrue(BB::isTrue('a'));
		$this->assertTrue(BB::isTrue(0.00001));
		
		$this->assertFalse(BB::isTrue(0));
		$this->assertFalse(BB::isTrue(-1));
		$this->assertFalse(BB::isTrue(false));
		$this->assertFalse(BB::isTrue(null));
		$this->assertFalse(BB::isTrue(''));
		$this->assertFalse(BB::isTrue(""));
	}
	
	public function testIsFalse() {
		$this->assertTrue(BB::isFalse());
		$this->assertTrue(BB::isFalse(0));
		$this->assertTrue(BB::isFalse(''));
		$this->assertTrue(BB::isFalse(""));
		$this->assertTrue(BB::isFalse(false));
		
		$this->assertFalse(BB::isFalse(1));
		$this->assertFalse(BB::isFalse("a"));
		$this->assertFalse(BB::isFalse(0.1));
		$this->assertFalse(BB::isFalse(-1));
		$this->assertFalse(BB::isFalse(true));
	}
	
	
	
	
	
	/**
	 * test array type methods
	 */
	
	public $realVector = array('apple', 'orange', 'blackberry');
	public $realAssoc = array('name' => 'Mark', 'surname' => 'Sheepkeeper');
	
	// keys are explicit defined but rapresents a real scalar array
	public $notRealAssoc = array(0 => 'apple', "1" => 'orange', 2 => 'blackberry');
	// keys are non sequential, there is a missing item between 0-2
	public $notRealVector = array(0 => 'apple', 2 => 'orange', 3 => 'blackberry');
	
	public function testIsVector() {
		$this->assertTrue(BB::isVector($this->realVector));
		$this->assertFalse(BB::isVector($this->realAssoc));
		$this->assertFalse(BB::isVector($this->notRealVector));
		$this->assertTrue(BB::isVector($this->notRealAssoc));
	}
	
	public function testIsAssoc() {
		$this->assertFalse(BB::isAssoc($this->realVector));
		$this->assertTrue(BB::isAssoc($this->realAssoc));
		$this->assertTrue(BB::isAssoc($this->notRealVector));
		$this->assertFalse(BB::isAssoc($this->notRealAssoc));
	}
	
	
	
	
}

