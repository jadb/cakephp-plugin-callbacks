<?php
/**
 * PluginCallbacks Test Case
 *
 * @copyright (c)2005-2009, WDT Media Corp (http://wdtmedia.net)
 * @author jad
 * @package plugin_callbacks
 * @subpackage plugin_callbacks.tests.cases.models.behaviors
 * @since 1.0.0
 */
class PluginCallbacksTestCase extends CakeTestCase {
	public $autoFixtures = false;
	/**
	 * Start test
	 *
	 * @return void
	 * @access public
	 */
	public function startTest($method = null) {
		$path = APP . 'plugins' . DS . 'plugin_callbacks' . DS . 'tests' . DS . 'mock_app' . DS;
		App::import('Model', 'User', 'Model', array(), $path . 'plugins' . DS . 'my_custom_plugin' . DS . 'models' . DS . 'user.php');
		$this->MockedPluginModel =& ClassRegistry::init('User');
		$this->MockedPluginModel->Behaviors->attach('PluginCallbacks.Callbacks', compact('path'));
		parent::startTest($method);
	}
	/**
	 * End test
	 *
	 * @return void
	 * @access public
	 */
	public function endTest($method = null) {
		unset($this->MockedPluginModel);
		ClassRegistry::flush();
		parent::endTest($method);
	}
	/**
	 * Test Correct Instances
	 *
	 * @return void
	 * @access public
	 */
	public function testCorrectInstances() {
		$this->assertIsA($this->MockedPluginModel, 'User');
		$this->assertIsA($this->MockedPluginModel->Behaviors->Callbacks, 'CallbacksBehavior');

	}
	/**
	 * Test correct settings loaded
	 *
	 * @return void
	 * @access public
	 */
	public function testSetup() {
		$expected = APP . 'plugins' . DS . 'plugin_callbacks' . DS . 'tests' . DS . 'mock_app' . DS;
		$this->assertEqual($expected, $this->MockedPluginModel->Behaviors->Callbacks->path);

		$expected = array('my_custom_plugin' => array('app' => 'AppMyCustomPlugin'));
		$this->assertEqual($expected, $this->MockedPluginModel->Behaviors->Callbacks->settings);
	}
	/**
	 * Basic test
	 *
	 * @return void
	 * @access public
	 */
	public function testMockAppCustomCallbackOnCustomPluginUserSuspend() {
		$this->MockedPluginModel->testTrace = array();
		$data = array('suspended' => true);
		$this->MockedPluginModel->suspend($data);
		$expected = array(
			'Passed in app before suspend user method',
			'Passed in plugin model method',
			'Passed in app after suspend model method',
		);
		$this->assertEqual($expected, $this->MockedPluginModel->testTrace);


		$this->MockedPluginModel->testTrace = array();
		$data = array('suspended' => false);
		$this->MockedPluginModel->suspend($data);
		$expected = array(
			'Passed in app before suspend user method',
			'Passed in plugin model method',
			'Passed in app to handle an extra case',
		);
		$this->assertEqual($expected, $this->MockedPluginModel->testTrace);
	}
}
?>