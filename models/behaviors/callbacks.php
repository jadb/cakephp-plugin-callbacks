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
 * @since 0.1
 */
/**
 * A callback file is a file located inside APP/models/callbacks/, or
 * APP/plugins/ANOTHER_PLUGIN/models/callbacks/ and follow the below
 * naming convention:
 *
 * 	Inside app:
 *
 * 		File: app_{my_plugin}.php
 * 		Class: AppMyPluginCallbacks
 *
 * 	or inside another plugins:
 *
 * 		File: another_plugin_{my_plugin}.php
 * 		Class: AnotherPluginMyPluginCallbacks
 *
 * In the above examples, the plugin requesting the callbacks name is `my_plugin`.
 * In the second example, the other plugin's name is `another_plugin`
 *
 * Callback methods are defined using the following convention:
 *
 * 	onPluginModelNameDefinedPluginCallbackName
 *
 * In this example, the plugin's model requesting the callback is `PluginModelName`
 * and the defined callback is `DefinedPluginCallbackName`
 *
 * To enable a `beforeRefund` callback to be called from the app or any other
 * plugin for `billing` model `Invoice`, add the following line at the beginning of
 * its `refund` method:
 *
 * 	$this->Behaviors->Callbacks->run($this, 'beforeRefund', $data);
 *
 * According to the above example, a callback can be defined in the `app` as follow:
 *
 * 	File: app_billing.php
 * 	Class: AppBillingCallbacks
 * 	Method: onInvoiceBeforeRefund
 *
 * or in `marketing` plugin like this:
 *
 * 	File: marketing_billing.php
 * 	Class: MarketingBillingCallbacks
 * 	Method: onInvoiceBeforeRefund
 *
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
	public function load($cached = true) {
		if ($cached === true) {
			$this->settings = Utility::cached('_suite_callbacks_', '_admin_suite_core_', $this, 'load', false);
			return;
		}
		App::import('Folder');

		// get all plugins
		$Folder = new Folder($this->path . 'plugins');
		$folders = current($Folder->ls());

		// switch directories
		$Folder->cd($this->path);

		// find defined callbacks for each installed plugin
		foreach ($folders as $folder) {
			$files = $Folder->findRecursive('([a-z_]+)_' . $folder . '.php');
			foreach ($files as $k => $file) {
				if (!preg_match_all('/models\/callbacks\/([a-z_]+)_' . $folder . '\.php/i', $file, $matches)) {
					continue;
				}
				// unset($files[$k]);
				$plugin = current($matches[1]);
				if (empty($plugin)) {
					$plugin = 'app';
				}
				$callbackName = Inflector::camelize($plugin) . '.' . Inflector::camelize(sprintf('%s_%s', $plugin, $folder));
				if ($plugin == 'app') {
					$callbackName = preg_replace('/App\./', '', $callbackName);
				}
				$this->settings[$folder][$plugin] = $callbackName;
			}
		}
		return $this->settings;
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
		if (empty($this->settings[$Model->plugin])) {
			return true;
		}
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
			$result = $Callbacks->{$method}($Model, $data);
			$this->trace[$Model->plugin][$Model->name][$plugin][$on] = $result;
		}
		return $result;
	}
}
?>