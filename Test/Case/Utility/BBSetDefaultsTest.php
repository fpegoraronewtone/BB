<?php
/**
 * BB Test Case
 * test "setDefaults" methods of BB library
 */



App::uses('bb', 'BB.Utility');

class BBSetDefaultsTest extends CakeTestCase {
	
	
	/**
	 * Both are associative array, just defaults() with inverted params order!
	 */
	public function testSet01() {
		$a = array('name' => 'Marco');
		$b = array('name' => 'user name', 'surname' => 'user surname');
		$this->assertEqual(BB::setDefaults($a, $b), array(
			'name' => 'Marco',
			'surname' => 'user surname'
		));
	}
	
	/**
	 * First param is a string and it is driven to be param "name"
	 */
	public function testSet02() {
		$a = 'Marco';
		$b = array('name' => 'user name', 'surname' => 'user surname');
		$this->assertEqual(BB::setDefaults($a, $b, 'name'), array(
			'name' => 'Marco',
			'surname' => 'user surname'
		));
	}
	
	/**
	 * Implement a driver object to teach where to put different types
	 * of inputs
	 */
	public function testSet03() {
		
		// tell "setDefaults()" how to drive non-array $origin values
		$driverOptions = array(
			'integer' => 'age',
			'boolean' => 'isMale',
			'name'
		);
		
		$origin = 'Marco';
		$defaults = array('name' => 'user name', 'surname' => 'user surname', 'age' => 31, 'isMale' => false );
		$this->assertEqual(BB::setDefaults($origin, $defaults, $driverOptions), array(
			'name' => 'Marco',
			'surname' => 'user surname',
			'age' => 31,
			'isMale' => false
		));
		
		$origin = 22;
		$defaults = array('name' => 'user name', 'surname' => 'user surname', 'age' => 31, 'isMale' => false );
		$this->assertEqual(BB::setDefaults($origin, $defaults, $driverOptions), array(
			'name' => 'user name',
			'surname' => 'user surname',
			'age' => 22,
			'isMale' => false
		));
		
		$origin = true;
		$defaults = array('name' => 'user name', 'surname' => 'user surname', 'age' => 31, 'isMale' => false );
		$this->assertEqual(BB::setDefaults($origin, $defaults, $driverOptions), array(
			'name' => 'user name',
			'surname' => 'user surname',
			'age' => 31,
			'isMale' => true
		));
	}
	
	
	
	/**
	 * setDefaultAttrs() is derived from set() and it is intended to format $options
	 * param for HtmlHelper::tag() method!
	 */
	public function testSetAttr01() {
		$this->assertEqual(BB::setDefaultAttrs('className'), array(
			'id' => '',
			'class' => 'className',
			'style' => ''
		));
		$this->assertEqual(BB::setDefaultAttrs('background:red'), array(
			'id' => '',
			'class' => '',
			'style' => 'background:red'
		));
		$this->assertEqual(BB::setDefaultAttrs('className', array('foo' => 'aaa')), array(
			'id' => '',
			'class' => 'className',
			'style' => '',
			'foo' => 'aaa'
		));
		$this->assertEqual(BB::setDefaultAttrs('background:red', array('foo' => 'aaa')), array(
			'id' => '',
			'class' => '',
			'style' => 'background:red',
			'foo' => 'aaa'
		));
	}
	
	
}

