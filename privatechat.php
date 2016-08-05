<?php






	require( '.' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'boot.php' );
	require_once( MODEL_PATH . 'privatechat.php' );
	require_once( MODEL_PATH . 'wordsfilter.php' );
	class GPage extends SecureGamePage {
		var $chats = null;
		var $Filter = NULL;

		function GPage() {
			$this->customLogoutAction = TRUE;
			parent::securegamepage();

			if ($this->player == NULL) {
				exit( 0 );
				return null;
			}

			$this->layoutViewFile = $this->viewFile = NULL;
			$_GET['_a1_'] = '';
		}

		function load() {
			parent::load();
			
			$this->Filter = new FilterWordsModel();

			if (( isset( $_GET['action'] ) && $_GET['action'] == 'chatheartbeat' )) {
				$this->chatHeartbeat();
			}


			if (( isset( $_GET['action'] ) && $_GET['action'] == 'sendchat' )) {
				$this->sendChat();
			}


			if (( isset( $_GET['action'] ) && $_GET['action'] == 'closechat' )) {
				$this->closeChat();
			}


			if (( isset( $_GET['action'] ) && $_GET['action'] == 'startchatsession' )) {
				$this->startChatSession();
			}


			if (!isset( $_SESSION['chatHistory'] )) {
				$_SESSION['chatHistory'] = array();
			}


			if (!isset( $_SESSION['openChatBoxes'] )) {
				$_SESSION['openChatBoxes'] = array();
			}

		}

		function chatHeartbeat() {
			
			$m = new PrivateChatModel();
			$chat = $m->GetFromChat( $this->player->playerId );
			$items = '';
			$chatBoxes = array();

			while ($chat->next()) {
				if (( !isset( $_SESSION['openChatBoxes'][$chat->row['from']] ) && isset( $_SESSION['chatHistory'][$chat->row['from']] ) )) {
				}

				$chat->row['message'] = $this->sanitize( $chat->row['message'] );
				$items .= '' . '						   {
				"s": "0",
				"f": "' . $chat->row['from'] . '",
				"img": "' . $chat->row['from_img'] . '",
				"id": "' . $chat->row['from_id'] . '",
				"m": "' . $chat->row['message'] . '"
		   },';

				if (!isset( $_SESSION['chatHistory'][$chat->row['from']] )) {
					$_SESSION['chatHistory'][$chat->row['from']] = '';
				}

				$_SESSION['chatHistory']->$chat->row['from'] .= '' . '							   {
				"s": "0",
				"f": "' . $chat->row['from'] . '",
				"img": "' . $chat->row['from_img'] . '",
				"id": "' . $chat->row['from_id'] . '",
				"m": "' . $chat->row['message'] . '"
		   },';
				unset( $_SESSION['tsChatBoxes'][$chat->row['from']] );
				$_SESSION['openChatBoxes'][$chat->row['from']] = $chat->row['sent'];
			}

			$m->dispose();

			if (!empty( $_SESSION['openChatBoxes'] )) {
				foreach ($_SESSION['openChatBoxes'] as $chatbox => $time) {
					if (!isset( $_SESSION['tsChatBoxes'][$chatbox] )) {
						$now = time() - strtotime( $time );
						$time = date( 'g:iA M dS', strtotime( $time ) );
						$message = '' . 'Sent at ' . $time;

						if (180 < $now) {
							$items .= '' . '	{
	"s": "2",
	"f": "' . $chatbox . '",
	"img": "' . $chat->row[$chatbox . '_img'] . '",
	"id": "' . $chat->row[$chatbox . '_id'] . '",
	"m": "' . $message . '"
	},';

							if (!isset( $_SESSION['chatHistory'][$chatbox] )) {
								$_SESSION['chatHistory'][$chatbox] = '';
							}

							$_SESSION['chatHistory']->$chatbox .= '' . '			{
	"s": "2",
	"f": "' . $chatbox . '",
	"img": "' . $chat->row[$chatbox . '_img'] . '",
	"id": "' . $chat->row[$chatbox . '_id'] . '",
	"m": "' . $message . '"
	},';
							$_SESSION['tsChatBoxes'][$chatbox] = 1;
							continue;
						}

						continue;
					}
				}
			}


			if ($items != '') {
				$items = substr( $items, 0, 0 - 1 );
			}

			header( 'Content-type: application/json' );
			echo '	{
			"items": [
				';
			echo $items;
			echo '			]
	}
	
	';
			exit( 0 );
		}

		function chatBoxSession($chatbox) {
			$items = '';

			if (isset( $_SESSION['chatHistory'][$chatbox] )) {
				$items = $_SESSION['chatHistory'][$chatbox];
			}

			return $items;
		}

		function startChatSession() {
			$items = '';

			if (!empty( $_SESSION['openChatBoxes'] )) {
				foreach ($_SESSION['openChatBoxes'] as $chatbox => $void) {
					$items .= $this->chatBoxSession( $chatbox );
				}
			}


			if ($items != '') {
				$items = substr( $items, 0, 0 - 1 );
			}

			header( 'Content-type: application/json' );
			echo '		{
				"username": "';
			echo $this->data['name'];
			echo '",
                "image": "';
			echo $this->data['avatar'];
			echo '",
                "id": "';
			echo $this->player->playerId;
			echo '",
				"items": [
					';
			echo $items;
			echo '				]
		}
		
		';
			exit( 0 );
		}

		function sendChat() {
			$from = $this->data['name'];
			$to = (isset( $_POST['to'] ) ? $_POST['to'] : '');
			$message = (isset( $_POST['message'] ) ? $_POST['message'] : '');
			$_SESSION['openChatBoxes'][$to] = date( 'Y-m-d H:i:s', time() );
			$messagesan = $this->sanitize( $message );

			if (!isset( $_SESSION['chatHistory'][$to] )) {
				$_SESSION['chatHistory'][$to] = '';
			}

			$_SESSION['chatHistory']->$to .= '' . '						   {
				"s": "1",
				"f": "' . $to . '",
				"img": "' . $this->data['avatar'] . '",
				"id": "' . $this->player->playerId . '",
				"m": "' . $messagesan . '"
		   },';
			unset( $_SESSION['tsChatBoxes'][$to] );
			
			$m = new PrivateChatModel();
			$m->SendToChat( $from, $this->player->playerId, $this->data['avatar'], $to, $_POST['to_id'], $message );
			$m->dispose();
			echo '1';
			exit( 0 );
		}

		function closeChat() {
			unset( $_SESSION['openChatBoxes'][$_POST['chatbox']] );
			echo '1';
			exit( 0 );
		}

		function sanitize($text) {
			$text = htmlspecialchars( $text, ENT_QUOTES );
			$text = str_replace( '
', '
', $text );
			$text = str_replace( '
', '
', $text );
			$text = str_replace( '
', '<br>', $text );
			$text = $this->Filter->FilterWords( $text );
			return $text;
		}
	}

	
	$p = new GPage();
	$p->run();
?>



