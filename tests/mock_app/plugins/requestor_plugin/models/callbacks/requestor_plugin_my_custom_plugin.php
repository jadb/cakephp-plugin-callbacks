<?php
class RequestorPluginMyCustomPluginCallbacks {
   public function onUserBeforeSuspend(&$Model, $args) {
      $Model->testTrace[] = "Passed in requestor_plugin before suspend user method";
   }
}
?>