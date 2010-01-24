<?php
/**
 * Callbacks Model Behavior
 *
 * Enables callbacks in plugins' models for other than the default Cake defined
 * methods (save, delete, etc.), allowing developers that use a certain plugin to
 * extend it's Models's methods (the ones that allow for that - defined by the
 * plugins' developer)
 *
 * PHP versions 4 and 5, CakePHP 1.2 and 1.3
 *
 * Copyright (c) 2005-2009, WDT Media Corp (http://wdtmedia.net)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright (c)2005-2009, WDT Media Corp (http://wdtmedia.net)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @link http://github.com/jadb/plugin_callbacks
 * @author jad
 * @package plugin_callbacks
 * @subpackage plugin_callbacks.models.behaviors
 */
class CallbacksBehavior extends ModelBehavior {
	/**
	 * Path of where to start searching for callbacks
	 *
	 * @var string
	 * @access public
	 */
	public $path = '/';
	/**
	 * Contains callback settings for use with individual plugins.
	 * Individual model settings should be stored as an associative array,
	 * keyed off of the model name.
	 *
	 * @var array
	 * @access public
	 * @see Model::$alias
	 */
	public $settings = array();
	/**
	 * Initiate Callbacks Behavior
	 *
	 * @param object $Model
	 * @param array $config
	 * @return void
	 * @access public
	 */
	public function setup(&$Model, $config = array()) {
		if (!isset($Model->plugin) || empty($Model->plugin)) {
			trigger_error(sprintf(__('CallbacksBehavior requires that the model it is attached to identifies the plugin it belongs to. Define `plugin` var for %s model.', true), $Model->name), E_USER_ERROR);
		}
		$this->path = APP;
		$this->_set($config);
		$this->load();
	}
	/**
	 * Find all defined callbacks in the app or other plugins
	 *
	 * @param undefined $cached
	 * @return void
	 * @access public
	 */
	public function load() {
		$cached = Cache::read('_plugin_callbacks_', '_cake_models_');
		if ($cached !== false) {
			$this->settings = $cached;
			return $cached;
		}

		App::import('Folder');

		$Folder = new Folder($this->path . 'plugins');
		$folders = current($Folder->ls());
		$files = array();
		foreach ($folders as $folder) {
			if ($Folder->cd($this->path . 'models' . DS . 'callbacks')) {
				$files = $Folder->findRecursive('([a-z_]+)_' . $folder . '.php');
			}
			$files = array_flip($files);
			foreach($folders as $_folder) {
				if ($Folder->cd($this->path . 'plugins' . DS . $_folder . DS . 'models' . DS . 'callbacks')) {
					$files = array_merge($files, array_flip($Folder->findRecursive('([a-z_]+)_' . $folder . '.php')));
				}
			}
			foreach (array_keys($files) as $k => $file) {
				if (!preg_match_all('/models\/callbacks\/([a-z_]+)_' . $folder . '\.php/i', $file, $matches)) {
					continue;
				}
				$plugin = current($matches[1]);
				if (empty($plugin)) {
					$plugin = 'app';
				}
				$callbackName = Inflector::camelize(sprintf('%s_%s', $plugin, $folder));
				$this->settings[$folder][$plugin] = $callbackName;
			}
		}
		Cache::write('_plugin_callbacks_', $this->settings, '_cake_models_');
	}
	/**
	 * Run callback
	 *
	 * @param object $Model
	 * @param string $on
	 * @param mixed $data
	 * @return mixed
	 * @access public
	 */
	public function run(&$Model, $on, $data = array()) {
		$result = null;
		$method = 'on' . $Model->name . Inflector::classify($on);
		foreach ($this->settings[$Model->plugin] as $plugin => $class) {
			$class = $class . 'Callbacks';
			$path = $this->path . 'models' . DS . 'callbacks' . DS . $plugin . '_' . $Model->plugin . '.php';
			if ($plugin != 'app') {
				$path = $this->path . 'plugins' . DS . $plugin . DS . 'models' . DS . 'callbacks' . DS . $plugin . '_' . $Model->plugin . '.php';
			}
			if (!file_exists($path)) {
				continue;
			}

			require_once($path);
			$Callbacks =& new $class;
			if (!in_array($method, get_class_methods($class))) {
				continue;
			}
			array_unshift($data, $Model);
			$result = call_user_func_array(array($Callbacks, $method), $data);
			$this->trace[$Model->plugin][$Model->name][$plugin][$on] = $result;
		}
		return $result;
	}
}
?>