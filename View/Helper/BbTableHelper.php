<?php
class BbTableHelper extends AppHelper {
	
	public $helpers = array(
		'Html'
	);
	
	private $__defaults = array(
		'model' => '',
		'columns' => array(),
		'table' => array(),
		'thead' => array(),
		'tbody' => array(),
		'tfoot' => array()
	);
	
	public $defaults = array();
	
	
	
	
	/**
	 * Rendering support properties
	 */
	public $data = null;
	public $options = null;
	public $model = null;
	public $columns = null;
	
	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);
		$this->defaults = BB::extend($this->__defaults, $this->defaults);
	}
	
	public function render($data, $options = array()) {
		
		if (empty($data)) {
			return;
		}
		
		$this->data = $data;
		$this->options = BB::extend($this->defaults, BB::set($options, 'model'));
		
		// seutp internal properties
		$this->_setupModel();
		$this->_setupColumns();
		
		// render table's blocks
		$table = $this->renderCaption();
		$table.= $this->renderThead();
		$table.= $this->renderTbody();
		$table.= $this->renderTfoot();
		
		
		// table options are a mix of custom "table" key options and generic
		$options = BB::extend($this->options['table'], BB::clear($this->options, array_keys($this->__defaults)));
		
		// clear callables
		foreach ($this->columns as $name=>$config) {
			unset($options['thead'.$config['sid']]);
		}
		
		return $this->Html->tag('table', $table, $options);
		
	}
	
	public function renderCaption() {}
	
	
	
	
	/**
	 * THEAD
	 * ===========================
	 * 
	 */
	public function renderThead() {
		
		ob_start();
		foreach ($this->columns as $name=>$config) {
			
			// callable label
			if (is_callable($config['label'])) {
				$label = BB::callback($config['label'], $this);
			
			// generic options callback
			} elseif (!empty($this->options['thead'.$config['sid']]) && is_callable($this->options['thead'.$config['sid']])) {
				$label = BB::callback($this->options['thead'.$config['sid']], $this);
			
			// text label
			} else {
				$label = $config['label'];
			}
			
			echo $this->Html->tag('th', $label);
		}
		
		return $this->Html->tag(array(
			'tag' => 'thead',
			'content' => array(
				'tag' => 'tr',
				'content' => ob_get_clean()
			)
		));
		
	}
	
	
	
	/**
	 * TBODY
	 * =========================
	 */
	public function renderTbody() {
		
		ob_start();
		foreach ($this->data as $i=>$dataRow) {
			
			ob_start();
			foreach ($this->columns as $name=>$config) {
				
				// callable content
				if (!empty($this->options['tbody'.$config['sid']]) && is_callable($this->options['tbody'.$config['sid']])) {
					$cell = BB::callback($this->options['tbody'.$config['sid']], $this, Set::extract($name, $dataRow), array(
						'dataRow' => $dataRow,
						'dataIdx' => $i,
						'columnName' => $name,
						'columnConfig' => $config
					));

				// static content
				} else {
					$cell = Set::extract($name, $dataRow);
				}
				
				echo $this->Html->tag('td', $cell);
			}
			
			echo $this->Html->tag('tr', ob_get_clean());
		}
		
		return $this->Html->tag('tbody', ob_get_clean());
	}
	
	public function renderTfoot() {}
	
	
	
	
	protected function _setupModel() {
		if (!empty($this->options['model'])) {
			$this->model = $this->options['model'];
		} else {
			$tmp = array_keys($this->data[0]);
			$this->model = $tmp[0];
		}
	}
	
	protected function _setupColumns() {
		// import or extract columns from data
		if (!empty($this->options['columns'])) {
			$columns = $this->options['columns'];
		} else {
			$this->columns = array();
			$row = $this->data[0];
			foreach (array_keys($row) as $model) {
				if (empty($row[$model]) || !is_array($row[$model])) {
					continue;
				}
				foreach (array_keys($row[$model]) as $fieldName) {
					$columns[] = $model . '.' . $fieldName;
				}
			}
		}
		
		// column's configuration normalization
		$this->columns = [];
		foreach ($columns as $name=>$config) {
			if (is_numeric($name)) {
				$name = $config;
				$config = array();
			}
			
			// full callable column declaration is moved to label
			if (is_callable($config)) {
				$config = array('label' => $config);
			}
			
			// column's defaults
			$config = BB::extend(array(
				'label' => $name,
				'sid'	=> Inflector::camelize(str_replace('.','_',$name))
			), BB::setStyle($config, 'label'));
			
			$this->columns[$name] = $config;
		}
		#ddebug($this->columns);
	}
	
}

