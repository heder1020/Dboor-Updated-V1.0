<?php
	require( '.' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'boot.php' );
	require_once( MODEL_PATH . 'register.php' );
	class GPage extends GamePage {
		var $err = array( 0 => '', 1 => '', 2 => '', 3 => '' );
		var $success = null;
		var $SNdata = NULL;
		var $UserID = 0;

		function GPage() {
			parent::gamepage();
			$this->viewFile = 'register.phtml';
			$this->contentCssClass = 'signup';
		}

		function load() {
			parent::load();
			$this->SNdata = 0;
			$this->success = FALSE;

			if ($this->isPost()) {
				if ($this->globalModel->isGameOver()) {
					$this->redirect( 'over.php' );
					return null;
				}

				$name = trim( $_POST['name'] );
				$email = trim( $_POST['email'] );
				$pwd = trim( $_POST['pwd'] );
				$this->err[0] = (strlen( $name ) < 3 ? register_player_txt_notless3 : '');

				if ($this->err[0] == '') {
					$this->err[0] = (preg_match( '/[:,\. \n\r\t\s]+/', $name ) ? register_player_txt_invalidchar : '');
				}


				if (( ( ( ( ( ( ( ( ( $name == '[ally]' || $name == 'admin' ) || $name == 'administrator' ) || $name == 'مدير' ) || $name == 'تتار' ) || $name == 'التتار' ) || $name == 'دعم' ) || $name == 'الدعم' ) || $name == $this->appConfig['system']['adminName'] ) || $name == tatar_tribe_player )) {
					$this->err[0] = register_player_txt_reserved;
				}
                $this->err[1] = !preg_match( "/^[^@]+@[a-zA-Z0-9._-]+\\.[a-zA-Z]+\$/", $email ) ? register_player_txt_invalidemail : ""; 
                $this->err[2] = strlen( $pwd ) < 4 ? register_player_txt_notless4 : ""; 
                $this->err[3] = !isset( $_POST['tid'] ) || $_POST['tid'] != 1 && $_POST['tid'] != 2 && $_POST['tid'] != 3 && $_POST['tid'] != 6 && $_POST['tid'] != 8 && $_POST['tid'] != 7 && $_POST['tid'] != 8 && $_POST['tid'] != 9 ? "<li>".register_player_txt_choosetribe."</li>" : ""; 
                $this->err[3] .= !isset( $_POST['kid'] ) || !is_numeric( $_POST['kid'] ) || $_POST['kid'] < 0 || 4 < $_POST['kid'] ? "<li>".register_player_txt_choosestart."</li>" : ""; 
				if (( ( ( 0 < strlen( $this->err[0] ) || 0 < strlen( $this->err[1] ) ) || 0 < strlen( $this->err[2] ) ) || 0 < strlen( $this->err[3] ) )) {
					return null;
				}

				
				$m = new RegisterModel();
				$this->err[0] = ($m->isPlayerNameExists( $name ) ? register_player_txt_usedname : '');
				$this->err[1] = ($m->isPlayerEmailExists( $email ) ? register_player_txt_usedemail : '');

				if (( 0 < strlen( $this->err[0] ) || 0 < strlen( $this->err[1] ) )) {
					$m->dispose();
					return null;
				}

				$villageName = new_village_name_prefix . ' ' . $name;
				$result = $m->createNewPlayer( $name, $email, $pwd, $_POST['tid'], $_POST['kid'], $villageName, $this->setupMetadata['map_size'], PLAYERTYPE_NORMAL, 1, $this->SNdata );

				if ($result['hasErrors']) {
					$this->err[3] = register_player_txt_fullserver;
					$m->dispose();
					return null;
				}
                                
                                
                                
                                if($this->appConfig['system']['new_user_activaiton']== true){
                                    $web_client = new WebHelper();
                                    $link = $web_client->getbaseurl() . 'activate.php?id=' . $result['activationCode'];
                                    $to = $email;
                                    $from = $this->appConfig['system']['email'];
                                    $subject = register_player_txt_regmail_sub;
                                    $message = sprintf( register_player_txt_regmail_body, $name, $name, $pwd, $link, $link );
                                    $web_client->sendmail( $to, $from, $subject, $message );
                                }
                                    $m->dispose();
                                    $this->success = TRUE;

                                
			}

		}
	}

	
	$p = new GPage();
	$p->run();
?>



