<?php






	require( '.' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'boot.php' );
	require_once( MODEL_PATH . 'news.php' );
	class GPage extends SecureGamePage {
		var $saved = null;
		var $siteNews = null;

		function GPage() {
			parent::securegamepage();
			$this->layoutViewFile = 'layout' . DIRECTORY_SEPARATOR . 'form.phtml';
			$this->viewFile = 'shownew.phtml';
			$this->contentCssClass = 'messages';
			$this->checkForGlobalMessage = FALSE;
			$this->checkForNewVillage = FALSE;
		}

		function load() {
			parent::load();

			if (( intval( $this->data['new_gnews'] ) == 0 || $this->player->isSpy )) {
				$this->redirect( 'village1.php' );
				return null;
			}

			
			$m = new NewsModel();
			$this->siteNews = $m->getGlobalSiteNews();
			$m->dispose();
		}
	}

	
	$p = new GPage();
	$p->run();
?>



