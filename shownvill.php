<?php






	require( '.' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'boot.php' );
	class GPage extends SecureGamePage {
		var $saved = null;
		var $siteNews = null;

		function GPage() {
			parent::securegamepage();
			$this->layoutViewFile = 'layout' . DIRECTORY_SEPARATOR . 'form.phtml';
			$this->viewFile = 'shownvill.phtml';
			$this->contentCssClass = 'messages';
			$this->checkForNewVillage = FALSE;
		}

		function load() {
			parent::load();

			if (( intval( $this->data['create_nvil'] ) == 0 || $this->player->isSpy )) {
				$this->redirect( 'village1.php' );
				return null;
			}

			$this->globalModel->resetNewVillageFlag( $this->player->playerId );
		}
	}

	
	$p = new GPage();
	$p->run();
?>



