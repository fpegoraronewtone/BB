<?php

App::uses('Controller', 'Controller');
App::uses('View', 'View');
App::uses('BbHtmlHelper', 'BB.View/Helper');




/**
 * Method and class are both used to test conditional tag with callbacks
 */
function BBtestHtmlTagFunc($p) {
	return $p;
}
class BBtestTagCondClass {
	public static function test($p) {
		return $p;
	}
}


class BBHtmlTagTest extends CakeTestCase {
	
	public $Html = null;
	public $Obj = null;
	
	public function setUp() {
		parent::setUp();
		$Controller = new Controller();
		$View = new View($Controller);
		$this->Html = new BbHtmlHelper($View);
		$this->Obj = new BBtestTagCondClass();
	}
	
	
	
	/**
	 * Test for default DIV tag to be used when tagName missing
	 */
	public function test01() {
		$this->assertEqual(
			$this->Html->tag('', 'Test'),
			'<div>Test</div>'
		);
		$this->assertEqual(
			$this->Html->tag(null, 'Test'),
			'<div>Test</div>'
		);
		$this->assertEqual(
			$this->Html->tag(array('Test')),
			'<div>Test</div>'
		);
		$this->assertEqual(
			$this->Html->tag(array('show' => 'Test')),
			'<div>Test</div>'
		);
		$this->assertEqual(
			$this->Html->tag(array('content' => 'Test')),
			'<div>Test</div>'
		);
	}
	
	/**
	 * Test for correct tag to be applied
	 */
	public function test02() {
		$this->assertEqual(
			$this->Html->tag('h1', 'Test'),
			'<h1>Test</h1>'
		);
		$this->assertEqual(
			$this->Html->tag(array('tag' => 'h1', 'Test')),
			'<h1>Test</h1>'
		);
		$this->assertEqual(
			$this->Html->tag(array('tag' => 'h1', 'show' => 'Test')),
			'<h1>Test</h1>'
		);
		$this->assertEqual(
			$this->Html->tag(array('tag' => 'h1', 'content' => 'Test')),
			'<h1>Test</h1>'
		);
	}
	
	/**
	 * Test for class/style string attributes declaration
	 */
	public function testClassStringAttr03() {
		$this->assertEqual(
			$this->Html->tag('p', 'test', 'foo-class'),
			'<p class="foo-class">test</p>'
		);
		$this->assertEqual(
			$this->Html->tag('p', 'test', array('class' => 'foo-class')),
			'<p class="foo-class">test</p>'
		);
		$this->assertEqual(
			$this->Html->tag(array('tag' => 'p', 'class' => 'foo-class', 'test')),
			'<p class="foo-class">test</p>'
		);
		$this->assertEqual(
			$this->Html->tag(array('tag' => 'p', 'class' => 'foo-class', 'show' => 'test')),
			'<p class="foo-class">test</p>'
		);
		$this->assertEqual(
			$this->Html->tag(array('tag' => 'p', 'class' => 'foo-class', 'content' => 'test')),
			'<p class="foo-class">test</p>'
		);
	}
	public function testStyleStringAttr03() {
		$this->assertEqual(
			$this->Html->tag('p', 'test', 'color:red'),
			'<p style="color:red">test</p>'
		);
		$this->assertEqual(
			$this->Html->tag('p', 'test', array('style' => 'color:red')),
			'<p style="color:red">test</p>'
		);
		$this->assertEqual(
			$this->Html->tag(array('tag' => 'p', 'style' => 'color:red', 'test')),
			'<p style="color:red">test</p>'
		);
		$this->assertEqual(
			$this->Html->tag(array('tag' => 'p', 'style' => 'color:red', 'show' => 'test')),
			'<p style="color:red">test</p>'
		);
		$this->assertEqual(
			$this->Html->tag(array('tag' => 'p', 'style' => 'color:red', 'content' => 'test')),
			'<p style="color:red">test</p>'
		);
	}
	
	
	/**
	 * Test various kinkds of tag names
	 */
	public function testTagNames() {
		$tags = array(
			'div','p', 'head', 'body', 'html', 'header', 'footer', 'section', 'article', 'aside',
			'table', 'thead', 'tbody', 'tfoot', 'th', 'td', 'tr',
			'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
			'i','b','u','span','sup','sub','small',
			'a',
			'script'
		);
		foreach($tags as $tag) {
			$this->assertEqual(
				$this->Html->tag($tag, 'test'),
				'<'.$tag.'>test</'.$tag.'>'
			);
		}
	}
	
	/**
	 * Test various kinds of attributes
	 */
	public function testAttributes() {
		$html = $this->Html->tag(array(
			'tag' => 'p',
			'show' => 'test',
			'class' => 'foo-class',
			'style' => 'color:red',
		));
		$this->assertContains('style="color:red"', $html);
		$this->assertContains('class="foo-class"', $html);
		
		$attrs = array(
			'id', 'class', 'style', 'rel', 'href', 'src',
			'data-id', 
			'pippo', 'pluto'
		);
		foreach($attrs as $attr) {
			$this->assertEqual(
				$this->Html->tag('p', 'test', array($attr => 'attr-val')),
				'<p '.$attr.'="attr-val">test</p>'
			);
		}
	}
	
	
	/**
	 * Test clear empty attributes
	 */
	public function testEmptyAttributes() {
		$this->assertEqual(
			$this->Html->tag('p', 'test', array('class' => '', 'id' => '', 'style' => '')),
			'<p>test</p>'
		);
	}
	
	
	/**
	 * Test "allowEmpty" option
	 */
	public function testAllowEmpty() {
		$this->assertEqual(
			$this->Html->tag('p', ''),
			''
		);
		$this->assertEqual(
			$this->Html->tag(array('tag' => 'p')),
			''
		);
		$this->assertEqual($this->Html->tag(), '');
		$this->assertEqual($this->Html->tag(array()), '');
		$this->assertEqual(
			$this->Html->tag(array('tag' => 'p', 'allowEmpty' => true)),
			'<p></p>'
		);
		
		$allowedEmptyTags = explode(',', $this->Html->allowEmptyTags);
		foreach($allowedEmptyTags as $tag) {
			$this->assertEqual(
				$this->Html->tag(array('tag' => $tag)),
				'<'.$tag.'></'.$tag.'>'
			);
		}
	}
	
	/**
	 * Test nested tags definitions
	 */
	public function testNestedTags() {
		$html = $this->Html->tag(array(
			'style' => 'border:1px solid black',
			'content' => array(
				array('tag' => 'h1', 'Test Title'),
				array('tag' => 'ul', array(
					array('tag' => 'li', 'show' => 'Item 01', 'class' => 'li-class'),
					array('tag' => 'li', 'content' => 'Item 02', 'style' => 'background:yellow'),
					array('tag' => 'li', 'Item 03')
				))
			)
		));
		$this->assertContains('<div style="border:1px solid black">', $html);
		$this->assertContains('<h1>Test Title</h1>', $html);
		$this->assertContains('<ul><li', $html);
		$this->assertContains('<li class="li-class">Item 01</li>', $html);
		$this->assertContains('<li style="background:yellow">', $html);
		$this->assertContains('<li>Item 03</li>', $html);
	}
	
	
	public function testIfSimple() {
		$this->assertEmpty($this->Html->tag('h1', 'test', array('if' => false)));
		$this->assertEmpty($this->Html->tag('h1', 'test', array('if' => '')));
		$this->assertEmpty($this->Html->tag('h1', 'test', array('if' => null)));
		$this->assertNotEmpty($this->Html->tag('h1', 'test', array('if' => 'false')));
		$this->assertNotEmpty($this->Html->tag('h1', 'test', array('if' => true)));
	}
	
	public function testIfFunctionCallback() {
		$this->assertEqual(
			$this->Html->tag('h1', 'test', array('if' => array('BBtestHtmlTagFunc', true))),
			'<h1>test</h1>'
		);
		$this->assertEqual(
			$this->Html->tag('h1', 'test', array('if' => array('BBtestHtmlTagFunc', 'false'))),
			'<h1>test</h1>'
		);
		$this->assertEqual(
			$this->Html->tag('h1', 'test', array('if' => array('BBtestHtmlTagFunc', 'null'))),
			'<h1>test</h1>'
		);
		$this->assertEmpty($this->Html->tag('h1', 'test', array('if' => array('BBtestHtmlTagFunc', false))));
		$this->assertEmpty($this->Html->tag('h1', 'test', array('if' => array('BBtestHtmlTagFunc', null))));
		$this->assertEmpty($this->Html->tag('h1', 'test', array('if' => array('BBtestHtmlTagFunc', ''))));
		$this->assertEmpty($this->Html->tag('h1', 'test', array('if' => array('BBtestHtmlTagFunc', 0))));
	}
	
	public function testIfStaticMethodCallback() {
		$this->assertEqual(
			$this->Html->tag('h1', 'test', array('if' => array('BBtestTagCondClass', 'test', true))),
			'<h1>test</h1>'
		);
		$this->assertEqual(
			$this->Html->tag('h1', 'test', array('if' => array(array('BBtestTagCondClass', 'test'), true))),
			'<h1>test</h1>'
		);
		$this->assertEqual(
			$this->Html->tag('h1', 'test', array('if' => array('BBtestTagCondClass::test', true))),
			'<h1>test</h1>'
		);
		$this->assertEmpty($this->Html->tag('h1', 'test', array('if' => array('BBtestTagCondClass', 'test', false))));
		$this->assertEmpty($this->Html->tag('h1', 'test', array('if' => array(array('BBtestTagCondClass', 'test'), null))));
		$this->assertEmpty($this->Html->tag('h1', 'test', array('if' => array('BBtestTagCondClass::test', ''))));
	}
	
	public function testElseStatement() {
		$this->assertEqual(
			$this->Html->tag(array('tag' => 'h1', 'if' => true, 'show' => 'true', 'else' => 'false')),
			'<h1>true</h1>'
		);
		$this->assertEqual(
			$this->Html->tag(array('tag' => 'h1', 'if' => false, 'show' => 'true', 'else' => 'false')),
			'<h1>false</h1>'
		);
	}
	
}
