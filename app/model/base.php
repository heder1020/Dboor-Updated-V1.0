<?php
require( LIB_PATH . 'mysqli.php' );
class ModelBase extends MysqliModel {
	function ModelBase() {
		parent::MysqliModel();
		$this->provider->debug = FALSE;
		$this->provider->properties = $GLOBALS['AppConfig']['db'];
  }
}

?>
