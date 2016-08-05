<?php






	require( '.' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'boot.php' );
	class GPage extends PopupPage {
		var $requestPaymentProvider = FALSE;
		var $providerType = '';
		var $package = null;
		var $payment = null;
		var $secureId = null;
		var $Domain = null;

		function GPage() {
			parent::popuppage();
			$this->viewFile = 'payment.phtml';
		}

		function load() {
			parent::load();
			$this->Domain = webhelper::getbaseurl();

			if (( isset( $_GET['p'] ) && isset( $_GET['pg'] ) )) {
				$this->providerType = trim( $_GET['p'] );
				$this->packageIndex = trim( $_GET['pg'] );

				if (( isset( $this->appConfig['plus']['payments'][$this->providerType] ) && isset( $this->appConfig['plus']['packages'][$this->packageIndex] ) )) {
					$this->title = sprintf( payment_loading . ' %s ...', $this->appConfig['plus']['payments'][$this->providerType]['name'] );
					$this->package = $this->appConfig['plus']['packages'][$this->packageIndex];
					$this->payment = $this->appConfig['plus']['payments'][$this->providerType];
					$this->requestPaymentProvider = isset( $_GET['c'] );

					if ($this->requestPaymentProvider) {
						$this->layoutViewFile = NULL;
						$this->secureId = base64_encode( $this->player->playerId );
						return null;
					}
				} 
else {
					echo '<script type="text/javascript">self.close();</script>';
				}
			}

		}
	}

	$p = new GPage();
	$p->run();
?>



