<?php
require( LIB_PATH . 'mysql.php' );
class ModelBase extends MysqlModel {
	function ModelBase() {
		parent::MysqlModel();
		$this->provider->debug = FALSE;
		$this->provider->properties = $GLOBALS['AppConfig']['db'];
  }
}

?>
