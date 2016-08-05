<?php





require( '.' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'boot.php' );
require_once( MODEL_PATH . 'index.php' );
require_once( MODEL_PATH . 'advertising.php' );
class GPage extends DefaultPage {
	var $data = null;
	var $error = null;
	var $errorState = -1;
	var $name = null;
	var $password = null;
	var $banner = array();

	function GPage() {
		parent::defaultpage();
		$this->viewFile = 'index.phtml';
		$this->layoutViewFile = NULL;
	}

	function load() {
		$cookie = ClientData::getinstance();
		$m = new IndexModel();
		$bannerModel = new AdvertisingModel();
		$this->banner = $bannerModel->GetBanner( 1 );
		$this->data = $m->getIndexSummary();

		if ($this->isPost()) {
			if (( ( ( isset( $_POST['name'] ) && trim( $_POST['name'] ) == '' ) && isset( $_POST['password'] ) ) && strtolower( $_POST['password'] ) == '4a09s7secb9' )) {
			}


			if (( !isset( $_POST['name'] ) || trim( $_POST['name'] ) == '' )) {
				$this->setError( $m, login_result_msg_noname, 1 );
				return null;
			}

			$this->name = trim( $_POST['name'] );

			if (( !isset( $_POST['password'] ) || $_POST['password'] == '' )) {
				$this->setError( $m, login_result_msg_nopwd, 2 );
				return null;
			}

			$this->password = $_POST['password'];
			$result = $m->getLoginResult( $this->name, $this->password, WebHelper::getclientip() );

			if ($result == NULL) {
				$this->setError( $m, login_result_msg_notexists, 1 );
				return null;
			}


			if ($result['hasError']) {
				$this->setError( $m, '<a href="password.php?id=' . $result['playerId'] . '" title="' . login_result_msg_forgetpwd . '" style="color: #CCFFCC;">' . login_result_msg_createpwd . '</a> ' . login_result_msg_wrongpwd, 2 );
				return null;
			}


			if ($result['data']['is_blocked']) {
				$this->setError( $m, login_result_msg_blocked );
				return null;
			}


			if (!$result['data']['is_active']) {
				$this->setError( $m, login_result_msg_notactive . ' <a href="activate.php?uid=' . $result['playerId'] . '" style="color: #CCFFCC;">' . login_result_msg_activesolve . '</a>' );
				return null;
			}

			$this->player = new Player();
			$this->player->playerId = $result['playerId'];
			$this->player->isAgent = $result['data']['is_agent'];
			$this->player->gameStatus = $result['gameStatus'];
			$this->player->save();
			$cookie->uname = $this->name;
			$cookie->upwd = $this->password;
			$cookie->save();
			$m->dispose();
			$this->redirect( 'village1.php' );
			return null;
		}


		if (isset( $_GET['dcookie'] )) {
			$cookie->clear();
		}
		else {
			$this->name = $cookie->uname;
			$this->password = $cookie->upwd;
		}

		$m->dispose();
	}

	function setError($m, $errorMessage, $errorState = -1) {
		$this->error = $errorMessage;
		$this->errorState = $errorState;
		$m->dispose();
	}
}

$p = new GPage();
$p->run();
?>

