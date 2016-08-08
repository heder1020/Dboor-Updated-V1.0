<?php






	require( '.' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'boot.php' );
	require_once( MODEL_PATH . 'payment.php' );
	class GPage extends WebService {
		function load() {
			$AppConfig = $GLOBALS['AppConfig'];

			if ($this->isPost()) {
				$usedPackage = NULL;
				foreach ($AppConfig['plus']['packages'] as $package) {
					if ($package['cost'] == $_POST['amount']) {
						$usedPackage = $package;
						continue;
					}
				}

				$merchant_id = $AppConfig['plus']['payments']['cashu']['merchant_id'];
				$usedPayment = NULL;
				foreach ($AppConfig['plus']['payments'] as $payment) {
					if ($payment['merchant_id'] == $merchant_id) {
						$usedPayment = $payment;
						continue;
					}
				}


				if (!isset( $_GET[$usedPayment['returnKey']] )) {
					return null;
				}


				if (( ( $usedPackage != NULL && $usedPayment != NULL ) && $_POST['token'] == md5( sprintf( '%s:%s:%s:%s', $merchant_id, $_POST['amount'], strtolower( $_POST['currency'] ), ($_POST['test_mode'] ? $usedPayment['testKey'] : $usedPayment['key']) ) ) )) {
					$playerId = base64_decode( $_POST['session_id'] );
					$goldNumber = $usedPackage['gold'];
					$m = new PaymentModel();
					$m->incrementPlayerGold( $playerId, $goldNumber );
					$m->dispose();
					echo '<h2 style="color:#00ff00;">success</h2>';
					return null;
				}

				echo '<h2 style="color:#ff0000;">failed</h2>';
			}

		}
	}

	$p = new GPage();
	$p->run();
?>



