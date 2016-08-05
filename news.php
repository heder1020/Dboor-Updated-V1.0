<?php






	require( '.' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'boot.php' );
	require_once( MODEL_PATH . 'news.php' );
	class GPage extends SecureGamePage {
		var $saved = null;
		var $siteNews = null;

		function GPage() {
			parent::securegamepage();
			$this->viewFile = 'news.phtml';
			$this->contentCssClass = 'messages';
		}

		function load() {
			parent::load();

			if ($this->data['player_type'] != PLAYERTYPE_ADMIN) {
				exit( 0 );
				return null;
			}

			
			$m = new NewsModel();
			$this->saved = FALSE;

			if (( $this->isPost() && isset( $_POST['news'] ) )) {
				$this->siteNews = $_POST['news'];
				$this->saved = TRUE;
				$m->setSiteNews( $this->siteNews );
			} 
else {
				$this->siteNews = $m->getSiteNews();
			}

			$m->dispose();
		}
	}

	
	$p = new GPage();
	$p->run();
?>



