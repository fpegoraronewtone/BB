<?php
class BbTableHelper extends AppHelper {
	
	public $helpers = array(
		'Html'
	);
	
	private $__defaults = array(
		'model' => '',
		'columns' => array(),
		'actions' => array(),
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
	public $actions = null;
	
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
			unset($options['config'.$config['sid']]);
			unset($options['thead'.$config['sid']]);
			unset($options['tbody'.$config['sid']]);
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
			
			$callable = 'thead'.$config['sid'];
			
			// callable label
			if (is_callable($config['label'])) {
				$label = BB::callback($config['label'], $this, $config);
			
			// generic options callback
			} elseif (!empty($this->options[$callable]) && is_callable($this->options[$callable])) {
				$label = BB::callback($this->options[$callable], $this, $config);
			
			// internal method
			} elseif (method_exists ($this, $callable) && is_callable(array($this, $callable))) {
				$label = BB::callback(array($this, $callable), $config);
				
			// text label
			} else {
				$label = $config['label'];
			}
			
			// default configuration
			$config = BB::extend(array(
				'tag' => 'th',
				'thead' => array()
			), $config);
			
			// apply label
			if (is_array($label)) {
				$config = BB::extend($config, $label);
			} else {
				$config = BB::extend($config, array('label' => $label));
			}
			
			// apply THEAD only options
			if (is_string($config['thead'])) $config['thead'] = BB::setDefaultAttrs($config['thead']);
			$config = BB::extend($config, BB::setDefaultAttrs($this->options['thead']), $config['thead']);
			
			echo $this->Html->tag($config['tag'], $config['label'], BB::clear($config, array('sid', 'tag', 'label', 'thead', 'tbody')));
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
				
				$callable = 'tbody'.$config['sid'];
				
				// callable content
				if (!empty($this->options[$callable]) && is_callable($this->options[$callable])) {
					$cell = BB::callback($this->options[$callable], $this, Set::extract($name, $dataRow), array(
						'dataRow' => $dataRow,
						'dataIdx' => $i,
						'columnName' => $name,
						'columnConfig' => $config
					));
				
				// internal method
				} elseif (method_exists($this, $callable) && is_callable(array($this, $callable))) {
					$cell = BB::callback(array($this, $callable), Set::extract($name, $dataRow), array(
						'dataRow' => $dataRow,
						'dataIdx' => $i,
						'columnName' => $name,
						'columnConfig' => $config
					));

				// static content
				} else {
					$cell = Set::extract($name, $dataRow);
				}
				
				// default configuration
				$config = BB::extend(array(
					'tag' => 'td',
					'tbody' => array()
				), $config);

				// apply cell content
				if (is_array($cell)) {
					$config = BB::extend($config, $cell);
				} else {
					$config = BB::extend($config, array('content' => $cell));
				}

				// apply TBODY only options
				if (is_string($config['tbody'])) $config['tbody'] = BB::setDefaultAttrs($config['tbody']);
				$config = BB::extend($config, BB::setDefaultAttrs($this->options['tbody']), $config['tbody']);

				echo $this->Html->tag($config['tag'], $config['content'], BB::clear($config, array('sid', 'tag', 'label', 'content', 'thead', 'tbody')));
				
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
	
	
	
	/**
	 * Setup table's available columns.
	 * 
	 * // quick string:
	 * options['columns'] = 'name, surname, ..'
	 * 
	 * // quick array:
	 * options['columns'] = array('name', 'surname')
	 * 
	 * // quick label:
	 * options['columns' = array(
	 *		'User.name' => 'Nome:',
	 *		'Books.count' => 'Tot. Acquisti:'
	 * )
	 * 
	 * // configuration as callback:
	 * options['columns'] = array(
	 *		'Books.count' => function() {return array('label' => 'foo');}
	 * )
	 * 
	 * // full config:
	 * options['columns'] = array(
	 *		'Books.count' => array(
	 *			'label' => 'foo',
	 *			'style' => 'background:#ddd'
	 *		)
	 * )
	 * 
	 * NOTE: "actions" column is a particular column name and there are some
	 * already configured callback to handle quick row action buttons.
	 * 
	 */
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
			// add actions column
			$columns['actions'] = array();
		}
		
		// column's configuration normalization
		$this->columns = [];
		foreach ($columns as $name=>$config) {
			
			if (is_numeric($name)) {
				$name = $config;
				$config = array();
			}
			
			$name = trim($name);
			if (strpos($name, '.') === false && $name !== 'actions') {
				$name = $this->model . '.' . $name;
			}
			$sid = Inflector::camelize(str_replace('.','_',$name));
			$callable = 'config' . $sid;
			
			// apply basic actions
			if ($name === 'actions' && is_array($config) && empty($config['items'])) {
				$config = BB::extend(array('items' => 'read, edit, delete'), BB::setDefaultAttrs($config));
			}
			
			// full callable column declaration is moved to label
			if (is_callable($config)) {
				$config = BB::callback($config, $this);
				
			// options configuration callback
			} elseif (!empty($this->options[$callable]) && is_callable($this->options[$callable])) {
				$config = BB::callback($this->options[$callable], $this, $config);
				
			// internal method
			} elseif (method_exists ($this, $callable) && is_callable(array($this, $callable))) {
				$config = BB::callback(array($this, $callable), $config);
			}
			
			
			// column's defaults
			$config = BB::extend(array(
				'label' => __($name),
				'sid'	=> $sid
			), BB::setStyle($config, 'label'));
			
			$this->columns[$name] = $config;
		}
		#ddebug($this->columns);
	}
	
	
	/**
	 * Take a list of actions and fill actions configuration array.
	 * 
	 */
	protected function _setupActions($actions) {
		
		$this->actions = array();
		
		if (is_string($actions)) {
			$actions = explode(',', $actions);
		}
		
		foreach ($actions as $actionName=>$actionConfig) {
			if (is_numeric($actionName)) {
				$actionName = $actionConfig;
				$actionConfig = array();
			}
			
			$actionName = trim($actionName);
			$this->actions[$actionName] = $actionConfig;
		}
		#ddebug($this->actions);
	}
	
	
	
	
	
	
	
	
	
	
	
	/**
	 * Configurate "actions" column.
	 * this column contain rules to setup automagically action buttons into 
	 * a column.
	 * 
	 * // quick string setup
	 * 'actions' => 'save, edit, delete'
	 * 
	 * // customized setup
	 * 'actions' => array(
	 *		'label' => 'Actions:',
	 *		'items' => 'save, edit, delete'
	 * )
	 * 
	 * NOTE: items list should be a comma separated string or a full array
	 * of items definitions. see "_setupActions()" for details
	 * 
	 */
	public function configActions($config) {
		
		if (empty($config)) {
			return array();
		}
		
		if (is_string($config)) {
			$actions = $config;
			$config = array();
		} else {
			$config = BB::extend(array('items' => array()), $config);
			$actions = $config['items'];
			unset($config['items']);
			
		}
		
		$this->_setupActions($actions);
		return $config;
	}
	
	
	
	/**
	 * Create actions cell content
	 */
	public function tbodyActions($val, $data) {
		
		$actions = array();
		foreach ($this->actions as $name=>$config) {
			switch (strtolower($name)) {
				case 'read':
					$actions[] = $this->actionRead($config, $data['dataRow'], $data['dataIdx']);
					break;
				case 'edit':
					$actions[] = $this->actionEdit($config, $data['dataRow'], $data['dataIdx']);
					break;
				case 'delete':
					$actions[] = $this->actionDelete($config, $data['dataRow'], $data['dataIdx']);
					break;
			}
		}
		
		return implode(' | ', $actions);
	}
	
	
	public function actionUrl($url, $row, $idx) {
		$tplData = BB::extend(array('Model' => $row[$this->model]), $row);
		
		if (is_array($url)) {
			foreach($url as $key=>$val) {
				$href[$key] = BB::tpl($val, $tplData);
			}
		} else {
			$url = BB::tpl($url, $tplData);
		}
		return $url;
	}
	
	public function actionRead($options, $row, $idx) {
		$options = BB::extend(array(
			'xtag'	=> 'link',
			'show'	=> __('read'),
			'title' => __('read item'),
			'href'	=> array(
				'action' => 'read',
				$row[$this->model]['id']
			)
		), BB::setStyle($options, 'show'));
		
		$options['href'] = $this->actionUrl($options['href'], $row, $idx);
		return $this->Html->tag($options);
	}
	
	public function actionEdit($options, $row, $idx) {
		$options = BB::extend(array(
			'xtag'	=> 'link',
			'show'	=> __('edit'),
			'title' => __('edit item'),
			'href'	=> array(
				'action' => 'edit',
				$row[$this->model]['id']
			)
		), BB::setStyle($options, 'show'));
		
		$options['href'] = $this->actionUrl($options['href'], $row, $idx);
		return $this->Html->tag($options);
	}
	
	public function actionDelete($options, $row, $idx) {
		$options = BB::extend(array(
			'xtag'	=> 'link',
			'show'	=> __('delete'),
			'title' => __('delete item'),
			'confirm' => 'confirm?',
			'href'	=> array(
				'action' => 'delete',
				$row[$this->model]['id']
			)
		), BB::setStyle($options, 'show'));
		
		$options['href'] = $this->actionUrl($options['href'], $row, $idx);
		return $this->Html->tag($options);
	}
	
	
	
	
	
}

