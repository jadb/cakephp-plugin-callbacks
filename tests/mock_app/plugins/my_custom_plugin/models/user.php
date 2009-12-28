<?php
class User extends Model {
	public $useTable = false;
	public $plugin = 'my_custom_plugin';
	public $testTrace = array();
	public function suspend($data) {
		$this->Behaviors->Callbacks->run($this, 'beforeSuspend', $data);
		$this->testTrace[] = 'Passed in plugin model method';
		extract($data);
		if ($suspended === true) {
			$this->Behaviors->Callbacks->run($this, 'afterSuspend', $data);
			return;
		}
		$this->Behaviors->Callbacks->run($this, 'failSuspend', $data);
	}
}
?>