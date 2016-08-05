<?php






	require( '.' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'boot.php' );
	class GPage extends SecureGamePage {
		function GPage() {
			parent::securegamepage();
			$this->viewFile = 'logout.phtml';
			$this->contentCssClass = 'logout';
		}

		function load() {
			if ($this->player->isSpy) {
				$gameStatus = $this->player->gameStatus;
				$uid = $this->player->prevPlayerId;
				$this->player = new Player();
				$this->player->playerId = $uid;
				$this->player->isAgent = FALSE;
				$this->player->gameStatus = $gameStatus;
				$this->player->save();
				$this->redirect( 'village1.php' );
				return null;
			}

			$this->player->logout();
			unset( $_SESSION );
			$this->player = NULL;
		}

		function preRender() {
		}
	}

	$p = new GPage();
	$p->run();
?>



