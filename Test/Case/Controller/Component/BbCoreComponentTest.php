<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('BbCoreComponent', 'BB.Controller/Component');


class TestBbCoreComponentController extends Controller {
	public $paginate = null;
}

class BbCoreComponentTest extends CakeTestCase {
	
	public $BbCoreComponent = null;
    public $Controller = null;
	
	public function setUp() {
		parent::setUp();
		$Collection = new ComponentCollection();
		$this->BbCoreComponent = new BbCoreComponent($Collection);
		$CakeRequest = new CakeRequest();
		$CakeResponse = new CakeResponse();
		$this->Controller = new TestBbCoreComponentController($CakeRequest, $CakeResponse);
		$this->BbCoreComponent->startup($this->Controller);
	}
	
	/**
	 * test component initialization to alias BbHtml Helper to the 
	 * controller's helpers
	 */
	public function testInitialize() {
		$this->BbCoreComponent->initialize($this->Controller);
		$this->assertArrayHasKey('Html', $this->Controller->helpers);
		$this->assertArrayHasKey('className', $this->Controller->helpers['Html']);
		$this->assertEqual('BB.BbHtml', $this->Controller->helpers['Html']['className']);
	}
	
}
