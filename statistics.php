<?php






	require( '.' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'boot.php' );
	require_once( MODEL_PATH . 'statistics.php' );
	class GPage extends SecureGamePage {
		var $selectedTabIndex = null;
		var $selectedRank = null;
		var $dataList = null;
		var $pageSize = 20;
		var $pageCount = null;
		var $pageIndex = null;
		var $generalData = null;
		var $top10Result = null;
		var $isAdmin = FALSE;
		var $adminActionMessage = '';
		var $_tb = null;
		var $tatarRaised = null;

		function GPage() {
			parent::securegamepage();
			$this->viewFile = 'statistics.phtml';
			$this->contentCssClass = 'statistics';
		}

		function load() {
			parent::load();
			$this->selectedTabIndex = (( ( ( isset( $_GET['t'] ) && is_numeric( $_GET['t'] ) ) && 0 <= intval( $_GET['t'] ) ) && intval( $_GET['t'] ) <= 11 ) ? intval( $_GET['t'] ) : 0);
			$this->isAdmin = $this->data['player_type'] == PLAYERTYPE_ADMIN;
			$this->_tb = (isset( $_GET['tb'] ) ? intval( $_GET['tb'] ) : 0);
			
			$m = new StatisticsModel();
			$this->tatarRaised = $m->tatarRaised();

			if (( $this->selectedTabIndex == 11 && !$this->tatarRaised )) {
				$this->selectedTabIndex = 0;
			}

			$this->selectedRank = 0;

			if ($this->selectedTabIndex == 0) {
				if (( ( $this->isPost() && isset( $_POST['rank'] ) ) && isset( $_POST['name'] ) )) {
					if (trim( $_POST['name'] ) != '') {
						$this->selectedRank = intval( $m->getPlayerRankByName( trim( $_POST['name'] ), $this->_tb ) );
					} 
else {
						if (0 < intval( $_POST['rank'] )) {
							$this->selectedRank = intval( $_POST['rank'] );
						}
					}
				} 
else {
					if (!isset( $_GET['p'] )) {
						$this->selectedRank = (( 0 < $this->_tb && $this->data['tribe_id'] != $this->_tb ) ? 1 : intval( $m->getPlayerRankById( $this->player->playerId, $this->_tb ) ));
					}
				}


				if ($this->isAdmin) {
					if (!$this->isPost()) {
						if (( isset( $_GET['_cs'] ) && 0 < intval( $_GET['_cs'] ) )) {
							$m->togglePlayerStatus( intval( $_GET['_cs'] ) );
							$this->adminActionMessage = statistics_p_playerstatusch;
						} 
else {
							if (( isset( $_GET['_dp'] ) && 0 < intval( $_GET['_dp'] ) )) {
								if ($m->getPlayerType( intval( $_GET['_dp'] ) ) == PLAYERTYPE_NORMAL) {
									
									$qm = new QueueJobModel();
									$qm->deletePlayer( intval( $_GET['_dp'] ) );
									$this->adminActionMessage = statistics_p_playerdeleted;
								}
							} 
else {
								if (( ( ( isset( $_GET['_gd'] ) && 0 < intval( $_GET['_gd'] ) ) && isset( $_GET['_g'] ) ) && 0 <= intval( $_GET['_g'] ) )) {
									$m->setPlayerGold( intval( $_GET['_gd'] ), intval( $_GET['_g'] ) );
									$this->adminActionMessage = statistics_p_goldwaschanged;
								}
							}
						}
					}
				}
			} 
else {
				if ($this->selectedTabIndex == 1) {
					if (( ( $this->isPost() && isset( $_POST['rank'] ) ) && isset( $_POST['name'] ) )) {
						if (trim( $_POST['name'] ) != '') {
							$this->selectedRank = intval( $m->getAllianceRankByName( trim( $_POST['name'] ) ) );
						} 
else {
							if (0 < intval( $_POST['rank'] )) {
								$this->selectedRank = intval( $_POST['rank'] );
							}
						}
					} 
else {
						if (!isset( $_GET['p'] )) {
							$this->selectedRank = intval( $m->getAllianceRankById( intval( $this->data['alliance_id'] ) ) );
						}
					}
				} 
else {
					if ($this->selectedTabIndex == 2) {
						if (( ( $this->isPost() && isset( $_POST['rank'] ) ) && isset( $_POST['name'] ) )) {
							if (trim( $_POST['name'] ) != '') {
								$this->selectedRank = intval( $m->getVillageRankByName( trim( $_POST['name'] ) ) );
							} 
else {
								if (0 < intval( $_POST['rank'] )) {
									$this->selectedRank = intval( $_POST['rank'] );
								}
							}
						} 
else {
							if (!isset( $_GET['p'] )) {
								$this->selectedRank = intval( $m->getVillageRankById( $this->data['selected_village_id'] ) );
							}
						}
					} 
else {
						if ($this->selectedTabIndex == 3) {
							if (( ( $this->isPost() && isset( $_POST['rank'] ) ) && isset( $_POST['name'] ) )) {
								if (trim( $_POST['name'] ) != '') {
									$this->selectedRank = intval( $m->getHeroRankByName( trim( $_POST['name'] ) ) );
								} 
else {
									if (0 < intval( $_POST['rank'] )) {
										$this->selectedRank = intval( $_POST['rank'] );
									}
								}
							} 
else {
								if (!isset( $_GET['p'] )) {
									$this->selectedRank = intval( $m->getHeroRankById( $this->player->playerId ) );
								}
							}
						} 
else {
							if (( $this->selectedTabIndex == 6 || $this->selectedTabIndex == 7 )) {
								if (( ( $this->isPost() && isset( $_POST['rank'] ) ) && isset( $_POST['name'] ) )) {
									if (trim( $_POST['name'] ) != '') {
										$this->selectedRank = intval( $m->getPlayersPointsByName( trim( $_POST['name'] ), $this->selectedTabIndex == 6 ) );
									} 
else {
										if (0 < intval( $_POST['rank'] )) {
											$this->selectedRank = intval( $_POST['rank'] );
										}
									}
								} 
else {
									if (!isset( $_GET['p'] )) {
										$this->selectedRank = intval( $m->getPlayersPointsById( $this->player->playerId, $this->selectedTabIndex == 6 ) );
									}
								}
							} 
else {
								if (( $this->selectedTabIndex == 9 || $this->selectedTabIndex == 10 )) {
									if (( ( $this->isPost() && isset( $_POST['rank'] ) ) && isset( $_POST['name'] ) )) {
										if (trim( $_POST['name'] ) != '') {
											$this->selectedRank = intval( $m->getAlliancePointsRankByName( trim( $_POST['name'] ), $this->selectedTabIndex == 9 ) );
										} 
else {
											if (0 < intval( $_POST['rank'] )) {
												$this->selectedRank = intval( $_POST['rank'] );
											}
										}
									} 
else {
										if (!isset( $_GET['p'] )) {
											$this->selectedRank = intval( $m->getAlliancePointsRankById( intval( $this->data['alliance_id'] ), $this->selectedTabIndex == 9 ) );
										}
									}
								}
							}
						}
					}
				}
			}


			if ($this->selectedTabIndex == 0) {
				$rowsCount = $m->getPlayerListCount( $this->_tb );
				$this->pageCount = (0 < $rowsCount ? ceil( $rowsCount / $this->pageSize ) : 1);
				$this->pageIndex = (0 < $this->selectedRank ? floor( ( $this->selectedRank - 1 ) / $this->pageSize ) : (( isset( $_GET['p'] ) && is_numeric( $_GET['p'] ) ) ? intval( $_GET['p'] ) : 0));

				if ($this->pageCount <= $this->pageIndex) {
					$this->pageIndex = $this->pageCount - 1;
					$this->selectedRank = 0 - 1;
				}

				$this->dataList = $m->getPlayerList( $this->pageIndex, $this->pageSize, $this->_tb );
			} 
else {
				if ($this->selectedTabIndex == 1) {
					$rowsCount = $m->getAllianceListCount();
					$this->pageCount = (0 < $rowsCount ? ceil( $rowsCount / $this->pageSize ) : 1);
					$this->pageIndex = (0 < $this->selectedRank ? floor( ( $this->selectedRank - 1 ) / $this->pageSize ) : (( isset( $_GET['p'] ) && is_numeric( $_GET['p'] ) ) ? intval( $_GET['p'] ) : 0));

					if ($this->pageCount <= $this->pageIndex) {
						$this->pageIndex = $this->pageCount - 1;
						$this->selectedRank = 0 - 1;
					}

					$this->dataList = $m->getAlliancesList( $this->pageIndex, $this->pageSize );
				} 
else {
					if ($this->selectedTabIndex == 2) {
						$rowsCount = $m->getVillageListCount();
						$this->pageCount = (0 < $rowsCount ? ceil( $rowsCount / $this->pageSize ) : 1);
						$this->pageIndex = (0 < $this->selectedRank ? floor( ( $this->selectedRank - 1 ) / $this->pageSize ) : (( isset( $_GET['p'] ) && is_numeric( $_GET['p'] ) ) ? intval( $_GET['p'] ) : 0));

						if ($this->pageCount <= $this->pageIndex) {
							$this->pageIndex = $this->pageCount - 1;
							$this->selectedRank = 0 - 1;
						}

						$this->dataList = $m->getVillagesList( $this->pageIndex, $this->pageSize );
					} 
else {
						if ($this->selectedTabIndex == 3) {
							$rowsCount = $m->getHeroListCount();
							$this->pageCount = (0 < $rowsCount ? ceil( $rowsCount / $this->pageSize ) : 1);
							$this->pageIndex = (0 < $this->selectedRank ? floor( ( $this->selectedRank - 1 ) / $this->pageSize ) : (( isset( $_GET['p'] ) && is_numeric( $_GET['p'] ) ) ? intval( $_GET['p'] ) : 0));

							if ($this->pageCount <= $this->pageIndex) {
								$this->pageIndex = $this->pageCount - 1;
								$this->selectedRank = 0 - 1;
							}

							$this->dataList = $m->getHerosList( $this->pageIndex, $this->pageSize );
						} 
else {
							if ($this->selectedTabIndex == 4) {
								$this->generalData = $m->getGeneralSummary();
							} 
else {
								if (( $this->selectedTabIndex == 6 || $this->selectedTabIndex == 7 )) {
									$rowsCount = $m->getPlayersPointsListCount();
									$this->pageCount = (0 < $rowsCount ? ceil( $rowsCount / $this->pageSize ) : 1);
									$this->pageIndex = (0 < $this->selectedRank ? floor( ( $this->selectedRank - 1 ) / $this->pageSize ) : (( isset( $_GET['p'] ) && is_numeric( $_GET['p'] ) ) ? intval( $_GET['p'] ) : 0));

									if ($this->pageCount <= $this->pageIndex) {
										$this->pageIndex = $this->pageCount - 1;
										$this->selectedRank = 0 - 1;
									}

									$this->dataList = $m->getPlayersPointsList( $this->pageIndex, $this->pageSize, $this->selectedTabIndex == 6 );
								} 
else {
									if (( $this->selectedTabIndex == 9 || $this->selectedTabIndex == 10 )) {
										$rowsCount = $m->getAlliancePointsListCount();
										$this->pageCount = (0 < $rowsCount ? ceil( $rowsCount / $this->pageSize ) : 1);
										$this->pageIndex = (0 < $this->selectedRank ? floor( ( $this->selectedRank - 1 ) / $this->pageSize ) : (( isset( $_GET['p'] ) && is_numeric( $_GET['p'] ) ) ? intval( $_GET['p'] ) : 0));

										if ($this->pageCount <= $this->pageIndex) {
											$this->pageIndex = $this->pageCount - 1;
											$this->selectedRank = 0 - 1;
										}

										$this->dataList = $m->getAlliancePointsList( $this->pageIndex, $this->pageSize, $this->selectedTabIndex == 9 );
									} 
else {
										if (( $this->selectedTabIndex == 5 || $this->selectedTabIndex == 8 )) {
											$this->top10Result = array( 'URL' => ($this->selectedTabIndex == 5 ? 'profile.php?uid=' : 'alliance.php?id='), 'TARGETNAME' => ($this->selectedTabIndex == 5 ? $this->data['name'] : $this->data['alliance_name']), 'TARGETID' => ($this->selectedTabIndex == 5 ? $this->player->playerId : intval( $this->data['alliance_id'] )), 'TARGEPOINT_ATTACK' => ($this->selectedTabIndex == 5 ? $this->data['week_attack_points'] : $m->getAlliancePoint( intval( $this->data['alliance_id'] ), 'week_attack_points' )), 'TARGEPOINT_DEFENSE' => ($this->selectedTabIndex == 5 ? $this->data['week_defense_points'] : $m->getAlliancePoint( intval( $this->data['alliance_id'] ), 'week_defense_points' )), 'TARGEPOINT_DEV' => ($this->selectedTabIndex == 5 ? $this->data['week_dev_points'] : $m->getAlliancePoint( intval( $this->data['alliance_id'] ), 'week_dev_points' )), 'TARGEPOINT_THIEF' => ($this->selectedTabIndex == 5 ? $this->data['week_thief_points'] : $m->getAlliancePoint( intval( $this->data['alliance_id'] ), 'week_thief_points' )), 'ATTACK' => $m->getTop10( $this->selectedTabIndex == 5, 'week_attack_points' ), 'DEFENSE' => $m->getTop10( $this->selectedTabIndex == 5, 'week_defense_points' ), 'DEV' => $m->getTop10( $this->selectedTabIndex == 5, 'week_dev_points' ), 'THIEF' => $m->getTop10( $this->selectedTabIndex == 5, 'week_thief_points' ) );
										} 
else {
											if ($this->selectedTabIndex == 11) {
												$this->dataList = $m->getTatarVillagesList();
											}
										}
									}
								}
							}
						}
					}
				}
			}

			$m->dispose();
		}

		function preRender() {
			parent::prerender();

			if (0 <= $this->selectedTabIndex) {
				$this->villagesLinkPostfix .= '&t=' . $this->selectedTabIndex;
			}

		}

		function getNextLink() {
			$text = text_nextpage_lang . ' »';

			if ($this->pageIndex + 1 == $this->pageCount) {
				return $text;
			}

			$link = '';

			if (0 < $this->selectedTabIndex) {
				$link .= 't=' . $this->selectedTabIndex;
			}


			if ($link != '') {
				$link .= '&';
			}

			$link .= 'p=' . ( $this->pageIndex + 1 );

			if (0 < $this->_tb) {
				$link .= '&tb=' . $this->_tb;
			}

			$link = 'statistics.php?' . $link;
			return '<a href="' . $link . '">' . $text . '</a>';
		}

		function getPreviousLink() {
			$text = '« ' . text_prevpage_lang;

			if ($this->pageIndex == 0) {
				return $text;
			}

			$link = '';

			if (0 < $this->selectedTabIndex) {
				$link .= 't=' . $this->selectedTabIndex;
			}


			if (0 < $this->pageIndex) {
				if ($link != '') {
					$link .= '&';
				}

				$link .= 'p=' . ( $this->pageIndex - 1 );
			}


			if (0 < $this->_tb) {
				if ($link != '') {
					$link .= '&';
				}

				$link .= 'tb=' . $this->_tb;
			}


			if ($link != '') {
				$link = '?' . $link;
			}

			$link = 'statistics.php' . $link;
			return '<a href="' . $link . '">' . $text . '</a>';
		}

		function getWonderLandLevel($builds) {
			$b_arr = explode( ',', $builds );
			$indx = 0;
			foreach ($b_arr as $b_str) {
				++$indx;
				$b2 = explode( ' ', $b_str );
				$itemId = $b2[0];
				$level = $b2[1];

				if ($itemId == 40) {
					return $level;
				}
			}

			return 0;
		}
	}

	
	$p = new GPage();
	$p->run();
?>



