<?php






	require( '.' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'boot.php' );
	class GPage extends SecureGamePage {
		var $packageIndex = -1;
		var $plusTable = null;

		function GPage() {
			parent::securegamepage();
			$this->viewFile = 'plus.phtml';
			$this->contentCssClass = 'plus';
			$this->plusTable = $this->gameMetadata['plusTable'];
			$i = 0;
			$c = sizeof( $this->plusTable );

			while ($i < $c) {
				if (0 < $this->plusTable[$i]['time']) {
					$this->plusTable[$i]['time'] = ceil( $this->plusTable[$i]['time'] / $this->gameMetadata['game_speed'] );
				}

				++$i;
			}

		}

		function load() {
			parent::load();
			$this->selectedTabIndex = (( ( ( isset( $_GET['t'] ) && is_numeric( $_GET['t'] ) ) && 0 <= intval( $_GET['t'] ) ) && intval( $_GET['t'] ) <= 2 ) ? intval( $_GET['t'] ) : 0);

			if ($this->selectedTabIndex == 0) {
				$this->packageIndex = (( ( ( isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) && 0 < intval( $_GET['id'] ) ) && intval( $_GET['id'] ) <= sizeof( $this->appConfig['plus']['packages'] ) ) ? intval( $_GET['id'] ) - 1 : 0 - 1);
				return null;
			}


			if ($this->selectedTabIndex == 2) {
				if (( ( ( ( ( isset( $_GET['a'] ) && isset( $_GET['k'] ) ) && $_GET['k'] == $this->data['update_key'] ) && $this->plusTable[intval( $_GET['a'] )]['cost'] <= $this->data['gold_num'] ) && !$this->isGameTransientStopped() ) && !$this->isGameOver() )) {
					switch (intval( $_GET['a'] )) {
						case 0: {
						}

						case 1: {
						}

						case 2: {
						}

						case 3: {
						}

						case 4: {
							$taskType = constant( 'QS_PLUS' . ( intval( $_GET['a'] ) + 1 ) );
							
							$newTask = new QueueTask($taskType, $this->player->playerId, $this->plusTable[intval( $_GET['a'] )]['time'] * 86400 );
							$newTask->villageId = (0 < intval( $_GET['a'] ) ? $this->data['selected_village_id'] : '');
							$newTask->tag = $this->plusTable[intval( $_GET['a'] )]['cost'];
							$this->queueModel->addTask( $newTask );
							break;
						}

						case 5: {
						}

						case 7: {
							$this->queueModel->finishTasks( $this->player->playerId, $this->plusTable[intval( $_GET['a'] )]['cost'], intval( $_GET['a'] ) == 7 );
						}
					}
				}
			}

		}

		function preRender() {
			parent::prerender();

			if (0 < $this->selectedTabIndex) {
				$this->villagesLinkPostfix .= '&t=' . $this->selectedTabIndex;
			}

		}

		function getRemainingPlusTime($action) {
			$time = 0;
			$tasks = $this->queueModel->tasksInQueue;

			if (isset( $tasks[constant( 'QS_PLUS' . ( $action + 1 ) )] )) {
				$time = $tasks[constant( 'QS_PLUS' . ( $action + 1 ) )][0]['remainingSeconds'];
			}

			return (0 < $time ? time_remain_lang . ' <span id="timer1">' . WebHelper::secondstostring( $time ) . '</span> ' . time_hour_lang : '');
		}

		function getPlusAction($action) {
			if ($this->data['gold_num'] < $this->plusTable[$action]['cost']) {
				return '<span class="none">' . plus_text_lowgold . '</span>';
			}


			if (( $action == 5 || $action == 7 )) {
				return '<a href="plus.php?t=2&a=' . $action . '&k=' . $this->data['update_key'] . '">' . plus_text_activatefeature . '</a>';
			}


			if ($action == 6) {
				return ($this->hasMarketplace() ? '<a href="build.php?bid=17&t=3">' . plus_text_gotomarket . '</a>' : '<span class="none">' . plus_text_gotomarket . '</span>');
			}

			$tasks = $this->queueModel->tasksInQueue;
			return (isset( $tasks[constant( 'QS_PLUS' . ( $action + 1 ) )] ) ? '<a href="plus.php?t=2&a=' . $action . '&k=' . $this->data['update_key'] . '">' . plus_text_extendfeature . '</a>' : '<a href="plus.php?t=2&a=' . $action . '&k=' . $this->data['update_key'] . '">' . plus_text_activatefeature . '</a>');
		}

		function hasMarketplace() {
			$b_arr = explode( ',', $this->data['buildings'] );
			foreach ($b_arr as $b_str) {
				$b2 = explode( ' ', $b_str );

				if ($b2[0] == 17) {
					return TRUE;
				}
			}

			return FALSE;
		}
	}

	
	$p = new GPage();
	$p->run();
?>



