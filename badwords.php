<?php






	require( '.' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'boot.php' );
	require_once( MODEL_PATH . 'badwords.php' );
	require_once( MODEL_PATH . 'wordsfilter.php' );
	class GPage extends SecureGamePage {
		var $isAdmin = FALSE;
		var $BadWords = array();
		var $pageSize = 20;
		var $pageIndex = null;
		var $pageCount = null;

		function GPage() {
			parent::securegamepage();
			$this->viewFile = 'badwords.phtml';
			$this->contentCssClass = 'player';
		}

		function load() {
			parent::load();
			$this->pageIndex = (( isset( $_GET['p'] ) && is_numeric( $_GET['p'] ) ) ? intval( $_GET['p'] ) : 0);
			$this->isAdmin = $this->data['player_type'] == PLAYERTYPE_ADMIN;

			if (!$this->isAdmin) {
				exit( 0 );
				return null;
			}

			
			$m = new BadWordsModel();
			$rowsCount = $m->getBadWordsCount();
			$this->pageCount = (0 < $rowsCount ? ceil( $rowsCount / $this->pageSize ) : 1);

			if (( isset( $_GET['Dword'] ) && !empty( $_GET['Dword'] ) )) {
				$wordID = mysql_real_escape_string( trim( $_GET['Dword'] ) );

				if ($wordID != '') {
					$m->DeleteBadWords( $wordID );
					$m->dispose();
					$this->redirect( 'badwords.php' );
					return null;
				}
			}


			if ($this->isPost()) {
				$i = 0;

				while ($i < count( $_POST['words'] )) {
					$words = mysql_real_escape_string( trim( $_POST['words'][$i] ) );

					if ($words == '') {
						continue;
					}

					$this->BadWords[] = $words;
					++$i;
				}

				$m->addBadWords( $this->BadWords );
				$m->dispose();
				$this->redirect( 'badwords.php' );
				return null;
			}

			$this->BadWords = $m->GetBadWords( $this->pageIndex, $this->pageSize );
			$m->dispose();
		}

		function getNextLink() {
			$text = text_nextpage_lang . ' »';

			if ($this->pageIndex + 1 == $this->pageCount) {
				return $text;
			}

			$link = 'p=' . ( $this->pageIndex + 1 );
			$link = 'badwords.php?' . $link;
			return '<a href="' . $link . '">' . $text . '</a>';
		}

		function getPreviousLink() {
			$text = '« ' . text_prevpage_lang;

			if ($this->pageIndex == 0) {
				return $text;
			}

			$link = '';

			if (0 < $this->pageIndex) {
				if ($link != '') {
					$link .= '&';
				}

				$link .= 'p=' . ( $this->pageIndex - 1 );
			}


			if ($link != '') {
				$link = '?' . $link;
			}

			$link = 'badwords.php' . $link;
			return '<a href="' . $link . '">' . $text . '</a>';
		}
	}

	
	$p = new GPage();
	$p->run();
?>



