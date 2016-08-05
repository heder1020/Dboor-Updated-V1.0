<?php

	require( '.' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'boot.php' );
	require_once( MODEL_PATH . 'password.php' );
	class GPage extends GamePage {
		var $pageState = -1;
		var $playerId = null;
        public $n = 666;
		public $key = $this->appConfig['system']['securty_key']] );
 		function GPage() {
			parent::gamepage();
			$this->viewFile = 'password.phtml';
			$this->contentCssClass = 'activate';
		}

		function load() {
			parent::load();
			
			$m = new PasswordModel();
             
			if (( ( ( $this->isPost() && isset( $_POST['id'] ) ) && isset( $_POST['email'] ) ) && is_numeric( $_POST['id'] ) )) {
				$playerId = intval( $_POST['id'] );
				$email = $_POST['email'];
				$this->pageState = ($m->isPlayerIdHasEmail( $playerId, $email ) ? 3 : 2);

				if ($this->pageState == 3) {
					$name = $m->getPlayerName( $playerId );
					$newPassword = substr( md5( dechex( $playerId * mt_rand( 10, 666 ) ) ), mt_rand( 1, 5 ), 7 );
					$n = dechex( md5( $newPassword ) ^ hexdec( substr( md5( $name ), 9, 11 ) ) );
					$link = WebHelper::getbaseurl() . 'password.php?id=' . $playerId . '&n=' . $n . '&c=' . substr( md5( dechex( $playerId ) . $name . $this->n ), $this->key, 9 );
					$to = $email;
					$from = $this->appConfig['system']['email'];
					$subject = forget_password_subject;
					$message = sprintf( forget_password_body, $name, $name, $newPassword, $link, $link );
					WebHelper::sendmail( $to, $from, $subject, $message );
				}
			} 
else {
				if (( isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) )) {
					$this->playerId = intval( $_GET['id'] );
					$this->pageState = ($m->isPlayerIdExists( $this->playerId ) ? 1 : 0 - 1);

					if (( ( isset( $_GET['n'] ) && trim( $_GET['n'] ) != '' ) && isset( $_GET['c'] ) )) {
						if ($this->pageState == 1) {
							$name = $m->getPlayerName( $this->playerId );

							if (trim( $_GET['c'] ) == substr( md5( dechex( $this->playerId ) . $name . $this->n ), $this->key, 9 )) {
								$newPassword = dechex( hexdec( $_GET['n'] ) ^ hexdec( substr( md5( $name ), 9, 11 ) ) );
								$m->setPlayerPassword( $this->playerId, $newPassword );
								$this->pageState = 4;
							} 
else {
								$this->pageState = 5;
							}
						} 
else {
							$this->pageState = 5;
						}
					}
				}
			}

			$m->dispose();
		}
	}

	
	$p = new GPage();
	$p->run();
?>



