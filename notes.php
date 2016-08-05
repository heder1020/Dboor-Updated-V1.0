<?php






	require( '.' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'boot.php' );
	require_once( MODEL_PATH . 'notes.php' );
	class GPage extends SecureGamePage {
		var $saved = null;

		function GPage() {
			parent::securegamepage();
			$this->viewFile = 'notes.phtml';
			$this->contentCssClass = 'messages';
		}

		function load() {
			parent::load();

			if (!$this->data['active_plus_account']) {
				exit( 0 );
				return null;
			}

			$this->saved = FALSE;

			if (( $this->isPost() && isset( $_POST['notes'] ) )) {
				$this->data['notes'] = $_POST['notes'];
				
				$m = new NotesModel();
				$m->changePlayerNotes( $this->player->playerId, $this->data['notes'] );
				$m->dispose();
				$this->saved = TRUE;
			}

		}
	}

	
	$p = new GPage();
	$p->run();
?>



