<?php
/**
 * BB Test Case
 * test "clear" methods of BB library
 */



App::uses('bb', 'BB.Utility');

class BBClearTest extends CakeTestCase {
	
	
	
	
	
	
	/**
	 * clearEmpty()
	 * "value mode"
	 */
	public function testClearEmpty() {
		$assocTest = array(
			'not empty value',
			'key' => 'not empty key',
			'null' => null,
			'emptyValue' => '',
			'emptyValue1' => "",
			'true' => true,
			'false' => false,
			'zeroInt' => 0,
			'zeroString' => "0",
			'zeroString1' => '0',
			'minus1' => -1
		);
		$assocResult = array(
			'not empty value',
			'key' => 'not empty key',			
			'true' => true,
			'false' => false,
			'zeroInt' => 0,
			'zeroString' => "0",
			'zeroString1' => '0',
			'minus1' => -1
		);
		$this->assertEqual(BB::clearEmpty($assocTest), $assocResult);
		
		$vectorTest = array(
			'not empty string',
			"not empty string",
			22,
			22.34,
			0,
			"0",
			'0',
			null,
			'',
			"",
			true,
			false,
			-1
		);
		$vectorResult = array(
			'not empty string',
			"not empty string",
			22,
			22.34,
			0,
			"0",
			'0',
			true,
			false,
			-1
		);
		$this->assertEqual(BB::clearEmpty($vectorTest), $vectorResult);
		
		$mixedTest = array(
			'not empty string',
			'not empty' => 'string',
			"not empty string",
			"not empty " => " string",
			1,
			'int' => 1,
			0,
			"intZero" => 0,
			null,
			"null" => null,
			'',
			"void" => '',
			22.123,
			"float" => 22.123,
			true,
			"true bool" => true,
			false,
			"false bool" => false,
			-1,
			"minus int" => -1
		);
		
		$mixedResult = array(
			'not empty string',
			'not empty' => 'string',
			"not empty string",
			"not empty " => " string",
			1,
			'int' => 1,
			0,
			"intZero" => 0,
			22.123,
			"float" => 22.123,
			true,
			"true bool" => true,
			false,
			"false bool" => false,
			-1,
			"minus int" => -1
		);
		$this->assertEqual(count(BB::clearEmpty($mixedTest)), count($mixedResult));
	}
	
	
	
	
	
	/**
	 * clearEmpty()
	 * "strict mode"
	 */
	public function testClearEmptyStrict() {
		
		$assocTest = array(
			'not empty value',
			'key' => 'not empty key',
			'null' => null,
			'emptyValue' => '',
			'emptyValue1' => "",
			'true' => true,
			'false' => false,
			'zeroInt' => 0,
			'zeroString' => "0",
			'zeroString1' => '0',
			'minus1' => -1
		);
		$assocResult = array(
			'not empty value',
			'key' => 'not empty key',			
			'true' => true,
			'minus1' => -1
		);
		$this->assertEqual(BB::clearEmpty($assocTest,true), $assocResult);
		
		$vectorTest = array(
			'not empty string',
			"not empty string",
			22,
			22.34,
			0,
			"0",
			'0',
			null,
			'',
			"",
			true,
			false,
			-1
		);
		$vectorResult = array(
			'not empty string',
			"not empty string",
			22,
			22.34,
			true,
			-1
		);
		$this->assertEqual(BB::clearEmpty($vectorTest,true), $vectorResult);
		
		
		
		$mixedTest = array(
			'not empty string',
			'not empty' => 'string',
			"not empty string",
			"not empty " => " string",
			1,
			'int' => 1,
			0,
			"intZero" => 0,
			null,
			"null" => null,
			'',
			"void" => '',
			22.123,
			"float" => 22.123,
			true,
			"true bool" => true,
			false,
			"false bool" => false,
			-1,
			"minus int" => -1
		);
		
		$mixedResult = array(
			'not empty string',
			'not empty' => 'string',
			"not empty string",
			"not empty " => " string",
			1,
			'int' => 1,
			22.123,
			"float" => 22.123,
			true,
			"true bool" => true,
			-1,
			"minus int" => -1
		);
		$this->assertEqual(count(BB::clearEmpty($mixedTest,true)), count($mixedResult));
	}
	
	
	
	public function testClearVector() {
		$test = array('banana', 'orange', 'apple', 'pear', 'blackberry', 'strawberry');
		$clear = array('orange', 'pear');
		$result = array('banana', 'apple', 'blackberry', 'strawberry');
		$this->assertEqual(BB::clear($test, $clear), $result);
		// + clear empty
		$test = array('banana', 'orange', 'apple', null, '', 'pear', 'blackberry', 'strawberry');
		$clear = array('orange', 'pear');
		$result = array('banana', 'apple', 'blackberry', 'strawberry');
		$this->assertEqual(BB::clear($test, $clear), $result);
		// + clear empty strict mode
		$test = array('banana', 'orange', 'apple', null, '', 0, 'pear', 'blackberry', 'strawberry');
		$clear = array('orange', 'pear');
		$result = array('banana', 'apple', 'blackberry', 'strawberry');
		$this->assertEqual(BB::clear($test, $clear, true), $result);
	}
	
	public function testClearAssoc() {
		$test = array('name' => 'Mark', 'surname' => 'Sheepkeeper', 'age' => 31);
		$clear = array('name', 'age');
		$result = array('surname' => 'Sheepkeeper');
		$this->assertEqual(BB::clear($test, $clear), $result);
		// string remove param
		$test = array('name' => 'Mark', 'surname' => 'Sheepkeeper', 'age' => 31);
		$clear = 'name';
		$result = array('surname' => 'Sheepkeeper', 'age' => 31);
		$this->assertEqual(BB::clear($test, $clear), $result);
		// + clear empty
		$test = array('name' => 'Mark', null, 'test'=>'', 'surname' => 'Sheepkeeper', 'age' => 31);
		$clear = 'name';
		$result = array('surname' => 'Sheepkeeper', 'age' => 31);
		$this->assertEqual(BB::clear($test, $clear), $result);
		// + clear empty (strict mode)
		$test = array('name' => 'Mark', null, 'test'=>'', 0, 'zero' => 0, 'surname' => 'Sheepkeeper', 'age' => 31);
		$clear = 'name';
		$result = array('surname' => 'Sheepkeeper', 'age' => 31);
		$this->assertEqual(BB::clear($test, $clear, true), $result);
	}
	
	
	
	
	
	
	
	
	public function testClearValues() {
		// vector
		$test = array('banana', 'orange', 'apple', 'pear', 'blackberry', 'strawberry');
		$clear = array('orange', 'pear');
		$result = array('banana', 'apple', 'blackberry', 'strawberry');
		$this->assertEqual(BB::clearValues($test, $clear), $result);
		// assoc
		$test = array('name' => 'Mark', 'surname' => 'Sheepkeeper', 'age' => 31);
		$clear = array('Mark', 31);
		$result = array('surname' => 'Sheepkeeper');
		$this->assertEqual(BB::clearValues($test, $clear), $result);
	}
	
	
	
	public function testClearAll() {
		$test = array('aa', 'name' => 'aa', null);
		$this->assertEqual(BB::clearAll($test, 'aa'), array());
		
		$test = array('aa', 'name' => 'aa', null, 0, '', 'a'=>'', 'b'=>null);
		$this->assertEqual(BB::clearAll($test, 'aa', true), array());	
	}
	
	
}

