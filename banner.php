<?php






	require( '.' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'boot.php' );
	require_once( MODEL_PATH . 'advertising.php' );
	class GPage extends SecureGamePage {
		function GPage() {
			parent::securegamepage();
		}

		function load() {
			parent::load();

			if (( isset( $_GET['url'] ) && !empty( $_GET['url'] ) )) {
				$advID = base64_decode( mysql_real_escape_string( trim( $_GET['url'] ) ) );

				if ($advID != '') {
					
					$m = new AdvertisingModel();
					$url = $m->GoToBanner( $advID );
					$m->dispose();
					$this->redirect( $url );
					return null;
				}
			}

		}
	}

	
	$p = new GPage();
	$p->run();
?>



