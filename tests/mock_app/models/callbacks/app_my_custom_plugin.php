<?php
class AppMyCustomPluginCallbacks {
	public function onUserBeforeSuspend(&$Model, $data) {
		$Model->testTrace[] = "Passed in app before suspend user method";
	}
	public function onUserAfterSuspend(&$Model, $data) {
		$Model->testTrace[] = "Passed in app after suspend model method";
	}
	public function onUserFailSuspend(&$Model, $data) {
		$Model->testTrace[] = "Passed in app to handle an extra case";
	}
}
?>