<?php






	require( '.' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'boot.php' );
	class GPage extends DefaultPage {
		function GPage() {
			parent::defaultpage();
			$this->viewFile = 'training.phtml';
		}
	}

	
	$p = new GPage();
	$p->run();
?>



