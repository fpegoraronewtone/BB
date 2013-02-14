<?php
/**
 * BB Test Case
 * test "defaults" methods of BB library
 */



App::uses('bb', 'BB.Utility');

class BBDefaultsTest extends CakeTestCase {
	
	/**
	 * String values
	 * only real empty or null strings are replaced by default val!
	 */
	public function test01() {
		$this->assertEqual(
			BB::defaults('Mark', 'Luke'), 'Mark'
		);
		$this->assertEqual(
			BB::defaults('', 'Luke'), 'Luke'
		);
		$this->assertEqual(
			BB::defaults(null, 'Luke'), 'Luke'
		);
		$this->assertEqual(
			BB::defaults(false, 'Luke'), false
		);
	}
	
	
	
	/**
	 * Numbers
	 * "0" is not a value to be replaced!
	 * only real empty values are replaced
	 */
	public function test02() {
		$this->assertEqual(
			BB::defaults(1, 2), 1
		);
		$this->assertEqual(
			BB::defaults(0, 2), 0
		);
		$this->assertEqual(
			BB::defaults('', 2), 2
		);
		$this->assertEqual(
			BB::defaults(null, 2), 2
		);
		$this->assertEqual(
			BB::defaults(false, 2), false
		);
	}
	
	
	/**
	 * Booleans
	 */
	public function test03() {
		$this->assertEqual(
			BB::defaults(true, false), true
		);
		$this->assertEqual(
			BB::defaults(false, true), false
		);
		$this->assertEqual(
			BB::defaults(null, true), true
		);
		$this->assertEqual(
			BB::defaults('', true), true
		);
		$this->assertEqual(
			BB::defaults(0, true), 0
		);
		$this->assertEqual(
			BB::defaults(null, false), false
		);
		$this->assertEqual(
			BB::defaults('', false), false
		);
		$this->assertEqual(
			BB::defaults(0, false), 0
		);
	}
	
	/**
	 * Defaults with null value
	 */
	public function test04() {
		$this->assertEqual(
			BB::defaults('Test', null), 'Test'
		);
		$this->assertEqual(
			BB::defaults('Test', ''), 'Test'
		);
		$this->assertEqual(
			BB::defaults('', ''), ''
		);
		$this->assertEqual(
			BB::defaults(null, ''), ''
		);
		$this->assertEqual(
			BB::defaults(null, null), null
		);
	}
	
	
	/**
	 * Apply defaults to two vectors
	 */
	public function test05() {
		$this->assertEqual(
			BB::defaults(array('banana'), array('apple')), array('banana')
		);
		$this->assertEqual(
			BB::defaults(array(), array('apple')), array('apple')
		);
		$this->assertEqual(
			BB::defaults(null, array('apple')), array('apple')
		);
		$this->assertEqual(
			BB::defaults('', array('apple')), array('apple')
		);
		$this->assertEqual(
			BB::defaults(false, array('apple')), false
		);
		$this->assertEqual(
			BB::defaults(0, array('apple')), 0
		);
	}
	
	
	/**
	 * Plain associative
	 */
	public function test06() {
		$a = array(
			'name' => 'Luke'
		);
		$b = array(
			'name' => 'Mark',
			'surname' => 'Skywalker'
		);
		$this->assertEqual(BB::defaults($a, $b), array(
			'name' => 'Luke',
			'surname' => 'Skywalker'
		));
		
		$c = array(
			'age' => null
		);
		$this->assertEqual(BB::defaults($a, $b, $c), array(
			'name' => 'Luke',
			'surname' => 'Skywalker',
			'age' => null
		));
		
		$d = array(
			'age' => 33,
			'hobbies' => array('space', 'laser swords')
		);
		$this->assertEqual(BB::defaults($a, $b, $c, $d), array(
			'name' => 'Luke',
			'surname' => 'Skywalker',
			'age' => null, // key already exists, it does not matter it's contents!
			'hobbies' => array('space', 'laser swords')
		));
		$e = array(
			'hobbies' => array('soul')
		);
		$this->assertEqual(BB::defaults($a, $b, $c, $d), array(
			'name' => 'Luke',
			'surname' => 'Skywalker',
			'age' => null,
			'hobbies' => array('space', 'laser swords')
		));
	}
	
	
	/**
	 * Deep associative
	 */
	public function test07() {
		$a = array(
			'name' => 'Luke',
			'abilities' => array(
				'sword' => 'laser'
			) 
		);
		$b = array(
			'abilities' => array(
				'sword' => 'silver',
				'fly' => 'space ship'
			),
			'surname' => 'Skywalker'
		);
		$this->assertEqual(BB::defaults($a, $b), array(
			'name' => 'Luke',
			'surname' => 'Skywalker',
			'abilities' => array(
				'sword' => 'laser',
				'fly' => 'space ship'
			) 
		));
	}
	
	/**
	 * Operators
	 */
	public function test08() {
		$a = array(
			'name' => 'Luke',
			'abilities' => array(
				'sword' => 'silver'
			) 
		);
		$b = array(
			'$++name' => ' Perry',
			'abilities' => array(
				'$__sword' => 'laser',
				'fly' => 'space ship'
			),
			'surname' => 'Skywalker'
		);
		$this->assertEqual(BB::defaults($a, $b), array(
			'name' => 'Luke Perry',
			'surname' => 'Skywalker',
			'abilities' => array(
				'sword' => 'laser',
				'fly' => 'space ship'
			) 
		));
		
		$c = array(
			'$__overrides__$' => array('abilities', 'name'),
			'name' => 'Luke',
			'abilities' => array('sword', 'space ship')
		);
		$this->assertEqual(BB::defaults($a, $b, $c), array(
			'name' => 'Luke',
			'surname' => 'Skywalker',
			'abilities' => array('sword', 'space ship')
		));
	}
	
}

