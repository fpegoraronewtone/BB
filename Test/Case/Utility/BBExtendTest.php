<?php
/**
 * BB Test Case
 * test "extend" methods of BB library
 */



App::uses('bb', 'BB.Utility');

class BBExtendTest extends CakeTestCase {
	
	
	
	public function testExtend01() {
		$test = array('banana', 'apple');
		$extend = array('orange', 'strawberry');
		$result = array('banana', 'apple', 'orange', 'strawberry');
		$this->assertEqual(BB::extend($test, $extend), $result);
	}
	
	public function testExtend02() {
		$test = array('banana', 'apple');
		$ext1 = array('orange', 'strawberry');
		$ext2 = array('blackberry');
		$result = array('banana', 'apple', 'orange', 'strawberry', 'blackberry');
		$this->assertEqual(BB::extend($test, $ext1, $ext2), $result);
		
		// extend with scalar value or scalar then new array
		$this->assertEqual(BB::extend($test, $ext1, $ext2, 'foo'), 'foo');
		$this->assertEqual(BB::extend($test, $ext1, 'foo', $ext2), $ext2);
	}
	
	public function testExtend03() {
		$test = array(
			'name' => 'Marco',
			'surname' => 'Sheepkeeper'
		);
		$ext = array(
			'name' => 'Mark',
			'age' => 31
		);
		$this->assertEqual(BB::extend($test, $ext), array(
			'name' => 'Mark',
			'surname' => 'Sheepkeeper',
			'age' => 31
		));
		
		// add sub-array
		$ext1 = array('hobbies' => array(
			'vds',
			'hiking'
		));
		$this->assertEqual(BB::extend($test, $ext, $ext1), array(
			'name' => 'Mark',
			'surname' => 'Sheepkeeper',
			'age' => 31,
			'hobbies' => array(
				'vds',
				'hiking'
			)
		));
		
		// extend sub array and change surname
		$ext2 = array(
			'hobbies' => array('biking'),
			'surname' => 'Eastwood'
		);
		$this->assertEqual(BB::extend($test, $ext, $ext1, $ext2), array(
			'name' => 'Mark',
			'surname' => 'Eastwood',
			'age' => 31,
			'hobbies' => array(
				'vds',
				'hiking',
				'biking'
			)
		));
		
		// reset hobbies, set new hobbies, add skills
		$ext3 = array(
			'$__overrides__$' => 'hobbies',
			'hobbies' => array('skying', 'climbing'),
			'skills' => array('php', 'html5', 'java')
		);
		$this->assertEqual(BB::extend($test, $ext, $ext1, $ext2, $ext3), array(
			'name' => 'Mark',
			'surname' => 'Eastwood',
			'age' => 31,
			'hobbies' => array('skying', 'climbing' ),
			'skills' => array('php', 'html5', 'java'),
		));
		
		// remove java from skills, append second name, reset surname
		$ext4 = array(
			'skills' => array('js', '$--java', 'css'),
			'$++name' => ' Phill',
			'$__surname' => 'Sheepkeeper'
		);
		$this->assertEqual(BB::extend($test, $ext, $ext1, $ext2, $ext3, $ext4), array(
			'name' => 'Mark Phill',
			'surname' => 'Sheepkeeper',
			'age' => 31,
			'hobbies' => array('skying', 'climbing' ),
			'skills' => array('php', 'html5', 'js', 'css'),
		));
		
		// remove "age" key"
		$ext5 = array('age' => '$__remove__$');
		$this->assertEqual(BB::extend($test, $ext, $ext1, $ext2, $ext3, $ext4, $ext5), array(
			'name' => 'Mark Phill',
			'surname' => 'Sheepkeeper',
			'hobbies' => array('skying', 'climbing' ),
			'skills' => array('php', 'html5', 'js', 'css'),
		));
		
	}
	
	
	
}

