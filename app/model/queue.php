<?php

class QueueModel extends ModelBase {
	var $page = null;
	var $tasksInQueue = array( 'buildsNum' => 0, 'fieldsNum' => 0, 'out_merchants_num' => 0, 'merchant_travel' => array(  ), 'merchant_coming' => array(  ), 'war_troops' => array( 'to_village' => array(  ), 'from_village' => array(  ), 'to_oasis' => array(  ) ), 'war_troops_summary' => array( 'total_number' => 0, 'to_me' => array( 'attacks' => array( 'number' => 0, 'min_time' => -1 ), 'reinforce' => array( 'number' => 0, 'min_time' => -1 ) ), 'from_me' => array( 'attacks' => array( 'number' => 0, 'min_time' => -1 ), 'reinforce' => array( 'number' => 0, 'min_time' => -1 ) ), 'to_my_oasis' => array( 'attacks' => array( 'number' => 0, 'min_time' => -1 ), 'reinforce' => array( 'number' => 0, 'min_time' => -1 ) ) ) );

	function finishTasks($playerId, $goldCost, $troopTrain = FALSE) {
		if ($this->page->data['is_special_village']) {
			return null;
		}

		$tasksTypeArray = ($troopTrain ? array( QS_TROOP_TRAINING ) : array( QS_BUILD_CREATEUPGRADE, QS_TROOP_RESEARCH, QS_TROOP_UPGRADE_ATTACK, QS_TROOP_UPGRADE_DEFENSE ));
		$updatesCount = $this->provider->executeQuery2( 'UPDATE
				p_queue q
			SET
				q.end_date=NOW()
			WHERE
				q.player_id=%s
				AND q.village_id=%s
				AND q.proc_type IN(%s)
				AND IF(q.proc_type=%s, q.building_id!=25 AND q.building_id!=26, 1)', array( $playerId, $this->page->data['selected_village_id'], implode( ',', $tasksTypeArray ), QS_BUILD_CREATEUPGRADE ) );

		if (0 < $updatesCount) {
			$this->page->data['gold_num'] -= $goldCost;
			$this->provider->executeQuery( 'UPDATE p_players p SET p.gold_num=p.gold_num-%s WHERE p.id=%s', array( $goldCost, $playerId ) );
			$this->_changeUpdateKey(  );
		}

	}

	function addTask($task) {
		$extraTime = 0;
		$justUpdate = FALSE;
		switch ($task->taskType) {
		case QS_PLUS1: {
				$justUpdate = isset( $this->tasksInQueue[QS_PLUS1] );
				break;
			}

		case QS_PLUS2: {
				$justUpdate = isset( $this->tasksInQueue[QS_PLUS2] );
				break;
			}

		case QS_PLUS3: {
				$justUpdate = isset( $this->tasksInQueue[QS_PLUS3] );
				break;
			}

		case QS_PLUS4: {
				$justUpdate = isset( $this->tasksInQueue[QS_PLUS4] );
				break;
			}

		case QS_PLUS5: {
				$justUpdate = isset( $this->tasksInQueue[QS_PLUS5] );
			}
		}

		$qid = 0;

		if ($justUpdate) {
			$this->provider->executeQuery( 'UPDATE p_queue q
				SET
					q.execution_time=q.execution_time+%s,
					q.end_date=(q.end_date + INTERVAL %s SECOND)
				WHERE
					q.player_id=%s
					%s
					AND q.proc_type=%s', array( $task->executionTime, $task->executionTime * $task->threads + $extraTime, $task->playerId, (0 < intval( $task->villageId ) ? 'AND q.village_id=' . $task->villageId : ''), $task->taskType ) );
			$this->_changeUpdateKey(  );
			$this->tasksInQueue[$task->taskType][0]['execution_time'] += $task->executionTime;
			$this->tasksInQueue[$task->taskType][0]['remainingSeconds'] += $task->executionTime * $task->threads + $extraTime;
		}
		else {
			switch ($task->taskType) {
			case QS_BUILD_CREATEUPGRADE: {
					$dualBuild = $GLOBALS['GameMetadata']['tribes'][$this->page->data['tribe_id']]['dual_build'];

					if (isset( $this->tasksInQueue[$task->taskType] )) {
						if ($dualBuild) {
							foreach ($this->tasksInQueue[$task->taskType] as $_qt) {
								if (( ( $task->buildingId <= 4 && $_qt['building_id'] <= 4 ) || ( 4 < $task->buildingId && 4 < $_qt['building_id'] ) )) {
									$extraTime = $_qt['remainingSeconds'];
									continue;
								}
							}
						}
						else {
							$_qt = $this->tasksInQueue[$task->taskType][sizeof( $this->tasksInQueue[$task->taskType] ) - 1];
							$extraTime = $_qt['remainingSeconds'];
						}
					}

					break;
				}

			case QS_TROOP_TRAINING: {
					if (( ( ( isset( $this->tasksInQueue[$task->taskType] ) && isset( $this->tasksInQueue[$task->taskType][$task->buildingId] ) ) && $task->buildingId != 29 ) && $task->buildingId != 30 )) {
						$_qt = $this->tasksInQueue[$task->taskType][$task->buildingId][sizeof( $this->tasksInQueue[$task->taskType][$task->buildingId] ) - 1];
						$extraTime = $_qt['remainingSeconds'];
					}
				}
			}

			$this->provider->executeQuery( 'INSERT p_queue
				SET
					player_id=%s,
					village_id=%s,
					to_player_id=%s,
					to_village_id=%s,
					proc_type=%s,
					proc_params=\'%s\',
					threads=%s,
					building_id=%s,
					execution_time=%s,
					end_date=(NOW() + INTERVAL %s SECOND)', array( $task->playerId, (0 < intval( $task->villageId ) ? $task->villageId : 'NULL'), (0 < intval( $task->toPlayerId ) ? $task->toPlayerId : 'NULL'), (0 < intval( $task->toVillageId ) ? $task->toVillageId : 'NULL'), $task->taskType, $task->procParams, $task->threads, (0 < intval( $task->buildingId ) ? $task->buildingId : 'NULL'), $task->executionTime, $task->executionTime * $task->threads + $extraTime ) );
			$qid = $this->provider->fetchScalar( 'SELECT LAST_INSERT_ID() FROM p_queue' );
		}

		switch ($task->taskType) {
		case QS_ACCOUNT_DELETE: {
				break;
			}

		case QS_BUILD_CREATEUPGRADE: {
				$neededResources = $task->tag;
				foreach ($task->tag as $k => $v) {
					$this->page->resources[$k]['current_value'] -= $v;
				}

				$this->page->buildings[$task->procParams]['item_id'] = $task->buildingId;
				++$this->page->buildings[$task->procParams]['update_state'];

				if ($task->buildingId == 40) {
					$this->page->buildings[26]['item_id'] = $task->buildingId;
					++$this->page->buildings[26]['update_state'];
					$this->page->buildings[29]['item_id'] = $task->buildingId;
					++$this->page->buildings[29]['update_state'];
					$this->page->buildings[30]['item_id'] = $task->buildingId;
					++$this->page->buildings[30]['update_state'];
					$this->page->buildings[33]['item_id'] = $task->buildingId;
					++$this->page->buildings[33]['update_state'];
				}

				$this->_updateVillage( TRUE );
				break;
			}

		case QS_BUILD_DROP: {
				break;
			}

		case QS_TROOP_TRAINING_HERO: {
				list( $targetTroopId, $vid ) = explode( ' ', $task->procParams );
			}

		case QS_TROOP_RESEARCH: {
			}

		case QS_TROOP_UPGRADE_ATTACK: {
			}

		case QS_TROOP_UPGRADE_DEFENSE: {
			}

		case QS_TROOP_TRAINING: {
			}

		case QS_TOWNHALL_CELEBRATION: {
			}

		case QS_MERCHANT_GO: {
				$neededResources = $task->tag;
				foreach ($task->tag as $k => $v) {
					$this->page->resources[$k]['current_value'] -= $v;
				}

				$this->_updateVillage( FALSE, ( $task->taskType != QS_TROOP_TRAINING && $task->taskType != QS_MERCHANT_GO ), ($task->taskType == QS_TROOP_TRAINING_HERO ? $this->_getNewTroops( array( $targetTroopId => 1 ) ) : NULL) );
				break;
			}

		case QS_MERCHANT_BACK: {
				break;
			}

		case QS_WAR_REINFORCE: {
			}

		case QS_WAR_ATTACK: {
			}

		case QS_WAR_ATTACK_PLUNDER: {
			}

		case QS_WAR_ATTACK_SPY: {
			}

		case QS_CREATEVILLAGE: {
				$neededResources = $task->tag['resources'];

				if ($neededResources != NULL) {
					foreach ($neededResources as $k => $v) {
						$this->page->resources[$k]['current_value'] -= $v;
					}
				}


				if ($task->tag['troops'] != NULL) {
					$this->_updateVillage( FALSE, FALSE, $this->_getNewTroops( $task->tag['troops'] ) );
				}
				else {
					if (( $task->taskType == QS_WAR_REINFORCE && isset( $task->tag['troopsCropConsume'] ) )) {
						$this->provider->executeQuery( 'UPDATE p_villages v SET v.crop_consumption=v.crop_consumption+%s WHERE v.id=%s', array( $task->tag['troopsCropConsume'], $task->toVillageId ) );
						$this->provider->executeQuery( 'UPDATE p_villages v SET v.crop_consumption=v.crop_consumption-%s WHERE v.id=%s', array( $task->tag['troopsCropConsume'], $task->villageId ) );
					}
				}


				if ($task->tag['hasHero']) {
					$this->provider->executeQuery( 'UPDATE p_players p SET p.hero_in_village_id=NULL WHERE p.id=%s', array( $task->playerId ) );
				}

				break;
			}

		case QS_LEAVEOASIS: {
				$this->_changeUpdateKey(  );
				break;
			}

		case QS_PLUS1: {
			}

		case QS_PLUS2: {
			}

		case QS_PLUS3: {
			}

		case QS_PLUS4: {
			}

		case QS_PLUS5: {
				$this->page->data['gold_num'] -= $task->tag;
				$this->provider->executeQuery( 'UPDATE p_players p SET p.gold_num=p.gold_num-%s WHERE p.id=%s', array( $task->tag, $task->playerId ) );

				if (!$justUpdate) {
					switch ($task->taskType) {
					case QS_PLUS1: {
							$this->page->data['active_plus_account'] = TRUE;
							$this->provider->executeQuery( 'UPDATE p_players p SET p.active_plus_account=1 WHERE p.id=%s', array( $task->playerId ) );
							break;
						}

					case QS_PLUS2: {
							$this->_setProductionPlusFeature( 1 );
							break;
						}

					case QS_PLUS3: {
							$this->_setProductionPlusFeature( 2 );
							break;
						}

					case QS_PLUS4: {
							$this->_setProductionPlusFeature( 3 );
							break;
						}

					case QS_PLUS5: {
							$this->_setProductionPlusFeature( 4 );
						}
					}
				}

				break;
			}

		case QS_TATAR_RAISE: {
			}

		case QS_SITE_RESET: {
				$justUpdate = TRUE;
			}
		}


		if (!$justUpdate) {
			$this->_enqueueTask( array( 'proc_type' => $task->taskType, 'id' => $qid, 'building_id' => $task->buildingId, 'player_id' => $task->playerId, 'village_id' => $task->villageId, 'to_player_id' => $task->toPlayerId, 'to_village_id' => $task->toVillageId, 'proc_params' => $task->procParams, 'threads' => $task->threads, 'execution_time' => $task->executionTime, 'remainingSeconds' => $task->executionTime * $task->threads + $extraTime, 'elapsedTime' => 0 ), TRUE );
		}

		return $qid;
	}

	function cancelTask($playerId, $taskId) {
		$GameMetadata = $GLOBALS['GameMetadata'];
		$row = $this->provider->fetchRow( 'SELECT
				q.id,
				q.player_id,
				q.village_id,
				q.to_player_id,
				q.to_village_id,
				q.building_id,
				q.proc_type,
				q.proc_params,
				q.threads,
				(q.execution_time - TIME_TO_SEC(TIMEDIFF(q.end_date, NOW()))) elapsedTime,
				TIME_TO_SEC(TIMEDIFF(q.end_date, NOW())) remainingSeconds
			FROM p_queue q
			WHERE
				q.id=%s AND q.player_id=%s', array( $taskId, $playerId ) );

		if ($row == NULL) {
			return null;
		}

		$taskType = $row['proc_type'];
		$elapsedTime = $row['elapsedTime'];

		if (!QueueTask::iscancelabletask( $taskType )) {
			return null;
		}

		$timeout = QueueTask::getmaxcanceltimeout( $taskType );

		if (0 - 1 < $timeout) {
			if ($timeout < $elapsedTime) {
				return null;
			}
		}


		if (( ( ( $taskType != QS_WAR_REINFORCE && $taskType != QS_WAR_ATTACK ) && $taskType != QS_WAR_ATTACK_PLUNDER ) && $taskType != QS_WAR_ATTACK_SPY )) {
			$this->provider->executeQuery( 'DELETE FROM p_queue WHERE id=%s', array( $taskId ) );
		}
		else {
			if ($taskType == QS_WAR_REINFORCE) {
				$proc_params = $row['proc_params'];
				$arr = explode( '|', $proc_params );

				if ($arr[sizeof( $arr ) - 1] == 1) {
					return null;
				}
			}
		}


		if ($taskType == QS_LEAVEOASIS) {
			unset( $this->tasksInQueue[$taskType][$row['building_id']] );
		}
		else {
			$i = 0;
			$c = sizeof( $this->tasksInQueue[$taskType] );

			while ($i < $c) {
				$qtask = $this->tasksInQueue[$taskType][$i];

				if ($qtask['id'] == $taskId) {
					if (( $taskType == QS_BUILD_CREATEUPGRADE && isset( $this->tasksInQueue[QS_BUILD_CREATEUPGRADE] ) )) {
						$dualBuild = $GameMetadata['tribes'][$this->page->data['tribe_id']]['dual_build'];
						$j = $i + 1;

						while ($j < $c) {
							$_qt = $this->tasksInQueue[$taskType][$j];

							if ($_qt['remainingSeconds'] <= $qtask['remainingSeconds']) {
								continue;
							}


							if ($dualBuild) {
								if (( ( $qtask['building_id'] <= 4 && $_qt['building_id'] <= 4 ) || ( 4 < $qtask['building_id'] && 4 < $_qt['building_id'] ) )) {
									$this->tasksInQueue[$taskType][$j]['remainingSeconds'] -= $qtask['remainingSeconds'];
								}
							}
							else {
								$this->tasksInQueue[$taskType][$j]['remainingSeconds'] -= $qtask['remainingSeconds'];
							}

							++$j;
						}
					}

					unset( $this->tasksInQueue[$taskType][$i] );
					break;
				}

				++$i;
			}
		}


		if (sizeof( $this->tasksInQueue[$taskType] ) == 0) {
			unset( $this->tasksInQueue[$taskType] );
		}
		else {
			if ($taskType != QS_LEAVEOASIS) {
				usort( $this->tasksInQueue[$taskType], array( $this, '_compareTask' ) );
			}
		}

		switch ($taskType) {
		case QS_ACCOUNT_DELETE: {
				break;
			}

		case QS_BUILD_DROP: {
				$this->_changeUpdateKey(  );
				break;
			}

		case QS_BUILD_CREATEUPGRADE: {
				--$this->tasksInQueue[(4 < intval( $row['building_id'] ) ? 'buildsNum' : 'fieldsNum')];
				$buildingLevels = $GameMetadata['items'][$row['building_id']]['levels'];
				$curResources = (0 < $this->page->buildings[$row['proc_params']]['level'] ? $buildingLevels[$this->page->buildings[$row['proc_params']]['level'] - 1]['resources'] : array( '1' => 0, '2' => 0, '3' => 0, '4' => 0 ));
				$nextResources = $buildingLevels[$this->page->buildings[$row['proc_params']]['level'] - 1 + $this->page->buildings[$row['proc_params']]['update_state']]['resources'];
				foreach ($nextResources as $k => $v) {
					$this->page->resources[$k]['current_value'] += $v - $curResources[$k];
				}


				if (( ( $this->page->buildings[$row['proc_params']]['level'] == 0 && $this->page->buildings[$row['proc_params']]['update_state'] == 1 ) && 4 < $this->page->buildings[$row['proc_params']]['item_id'] )) {
					$this->page->buildings[$row['proc_params']]['item_id'] = 0;

					if ($row['building_id'] == 40) {
						$this->page->buildings[26]['item_id'] = 0;
						$this->page->buildings[29]['item_id'] = 0;
						$this->page->buildings[30]['item_id'] = 0;
						$this->page->buildings[33]['item_id'] = 0;
					}
				}

				--$this->page->buildings[$row['proc_params']]['update_state'];

				if ($row['building_id'] == 40) {
					--$this->page->buildings[26]['update_state'];
					--$this->page->buildings[29]['update_state'];
					--$this->page->buildings[30]['update_state'];
					--$this->page->buildings[33]['update_state'];
				}

				$this->_updateVillage( TRUE );
				$dualBuild = $GameMetadata['tribes'][$this->page->data['tribe_id']]['dual_build'];
				$expr = '';

				if ($dualBuild) {
					$expr = (4 < $row['building_id'] ? ' AND q.building_id>4' : ' AND q.building_id<=4');
				}

				$this->provider->executeQuery( 'UPDATE p_queue q
					SET
						q.end_date=(q.end_date - INTERVAL %s SECOND)
					WHERE
						q.proc_type=%s AND q.player_id=%s AND q.village_id=%s
						AND TIME_TO_SEC(TIMEDIFF(q.end_date, NOW())) > %s' . $expr, array( $row['remainingSeconds'], $row['proc_type'], $row['player_id'], $row['village_id'], $row['remainingSeconds'] ) );
				break;
			}

		case QS_WAR_REINFORCE: {
			}

		case QS_WAR_ATTACK: {
			}

		case QS_WAR_ATTACK_PLUNDER: {
			}

		case QS_WAR_ATTACK_SPY: {
				$proc_params = $row['proc_params'];
				$arr = explode( '|', $proc_params );
				$arr[sizeof( $arr ) - 1] = 1;
				$proc_params = implode( '|', $arr );
				$this->provider->executeQuery( 'UPDATE p_queue q
					SET
						q.player_id=%s,
						q.village_id=%s,
						q.to_player_id=%s,
						q.to_village_id=%s,
						q.proc_type=%s,
						q.proc_params=\'%s\',
						q.execution_time=%s,
						q.end_date=(NOW() + INTERVAL %s SECOND)
					WHERE
						q.id=%s', array( (intval( $row['to_player_id'] ) == 0 ? 'NULL' : $row['to_player_id']), (intval( $row['to_village_id'] ) == 0 ? 'NULL' : $row['to_village_id']), (intval( $row['player_id'] ) == 0 ? 'NULL' : $row['player_id']), (intval( $row['village_id'] ) == 0 ? 'NULL' : $row['village_id']), QS_WAR_REINFORCE, $proc_params, $row['elapsedTime'], $row['elapsedTime'], $taskId ) );
				--$this->tasksInQueue['war_troops_summary']['total_number'];

				if (isset( $this->tasksInQueue['war_troops']['from_village'] )) {
					$i = 0;
					$_c = sizeof( $this->tasksInQueue['war_troops']['from_village'] );

					while ($i < $_c) {
						if ($this->tasksInQueue['war_troops']['from_village'][$i]['id'] == $taskId) {
							unset( $this->tasksInQueue['war_troops']['from_village'][$i] );
							break;
						}

						++$i;
					}
				}

				$tmp = $row['to_player_id'];
				$row['to_player_id'] = $row['player_id'];
				$row['player_id'] = $tmp;
				$tmp = $row['to_village_id'];
				$row['to_village_id'] = $row['village_id'];
				$row['village_id'] = $tmp;
				$row['proc_type'] = QS_WAR_REINFORCE;
				$row['proc_params'] = $proc_params;
				$row['execution_time'] = $row['remainingSeconds'] = $row['elapsedTime'];
				$row['elapsedTime'] = 0;
				$this->_enqueueTask( $row );

				if (0 < sizeof( $this->tasksInQueue['war_troops']['to_village'] )) {
					usort( $this->tasksInQueue['war_troops']['to_village'], array( $this, '_compareTask' ) );
				}

				break;
			}

		case QS_LEAVEOASIS: {
			}
		}

		unset( $row );
	}

	function fetchQueue($playerId) {
		$expr = '';

		if (trim( $this->page->data['village_oases_id'] ) != '') {
			$expr = sprintf( ' OR q.to_village_id IN (%s)', $this->page->data['village_oases_id'] );
		}

		$result = $this->provider->fetchResultSet( 'SELECT
				q.id, q.player_id, q.village_id, q.to_player_id, q.to_village_id, q.building_id, q.proc_type, q.proc_params, q.threads, q.execution_time,
				(q.execution_time - TIME_TO_SEC(TIMEDIFF(q.end_date, NOW()))) elapsedTime,
				TIME_TO_SEC(TIMEDIFF(q.end_date, NOW())) remainingSeconds
			FROM p_queue q
			WHERE
				(q.player_id=%s  AND ( ISNULL(q.village_id) OR q.village_id=%s ))
				OR (q.to_player_id=%s AND (q.to_village_id=%s%s))
			ORDER BY
				q.end_date ASC', array( $playerId, $this->page->data['selected_village_id'], $playerId, $this->page->data['selected_village_id'], $expr ) );

		while ($result->next(  )) {
			$this->_enqueueTask( $result->row );
		}

	}

	function _compareTask($a, $b) {
		$a = $a['remainingSeconds'];
		$b = $b['remainingSeconds'];

		if ($a == $b) {
			return 0;
		}

		return ($a < $b ? 0 - 1 : 1);
	}

	function _enqueueTask($row, $doSorting = FALSE) {
		$taskType = $row['proc_type'];

		if (!isset( $this->tasksInQueue[$taskType] )) {
			$this->tasksInQueue[$taskType] = array(  );
		}


		if (( $taskType == QS_TROOP_TRAINING || $taskType == QS_LEAVEOASIS )) {
			if ($taskType == QS_LEAVEOASIS) {
				$oasisId = $row['building_id'];
				$ownOasis = FALSE;

				if (trim( $this->page->data['village_oases_id'] ) != '') {
					$oArr = explode( ',', trim( $this->page->data['village_oases_id'] ) );
					foreach ($oArr as $oid) {
						if ($oid == $oasisId) {
							$ownOasis = TRUE;
							break;
						}
					}
				}


				if (!$ownOasis) {
					if (sizeof( $this->tasksInQueue[$taskType] ) == 0) {
						unset( $this->tasksInQueue[$taskType] );
					}

					return null;
				}
			}


			if (!isset( $this->tasksInQueue[$taskType][$row['building_id']] )) {
				$this->tasksInQueue[$taskType][$row['building_id']] = array(  );
			}

			$this->tasksInQueue[$taskType][$row['building_id']][] = array( 'id' => $row['id'], 'building_id' => $row['building_id'], 'player_id' => $row['player_id'], 'village_id' => $row['village_id'], 'to_player_id' => $row['to_player_id'], 'to_village_id' => $row['to_village_id'], 'proc_type' => $row['proc_type'], 'proc_params' => $row['proc_params'], 'threads' => $row['threads'], 'execution_time' => $row['execution_time'], 'remainingSeconds' => $row['remainingSeconds'], 'elapsedTime' => $row['elapsedTime'] );

			if (( $doSorting && 0 < sizeof( $this->tasksInQueue[$taskType][$row['building_id']] ) )) {
				usort( $this->tasksInQueue[$taskType][$row['building_id']], array( $this, '_compareTask' ) );
			}
		}
		else {
			$merchantBack = FALSE;
			$akey = $taskType;

			if (( $taskType == QS_MERCHANT_GO || $taskType == QS_MERCHANT_BACK )) {
				if (( $taskType == QS_MERCHANT_BACK && $this->page->data['selected_village_id'] != $row['village_id'] )) {
					return null;
				}

				$merchantBack = $taskType == QS_MERCHANT_BACK;
				$akey = ($this->page->data['selected_village_id'] == $row['village_id'] ? 'merchant_travel' : 'merchant_coming');

				if ($akey == 'merchant_travel') {
					list( $merchanNum, $res ) = explode( '|', $row['proc_params'] );
					$this->tasksInQueue['out_merchants_num'] += $merchanNum;
				}
			}

			$_newTask = array( 'id' => $row['id'], 'building_id' => $row['building_id'], 'player_id' => $row['player_id'], 'village_id' => $row['village_id'], 'to_player_id' => $row['to_player_id'], 'to_village_id' => $row['to_village_id'], 'proc_type' => $row['proc_type'], 'proc_params' => $row['proc_params'], 'threads' => $row['threads'], 'execution_time' => $row['execution_time'], 'remainingSeconds' => $row['remainingSeconds'], 'elapsedTime' => $row['elapsedTime'], 'merchantBack' => $merchantBack );
			$this->tasksInQueue[$akey][] = $_newTask;

			if (( $doSorting && 0 < sizeof( $this->tasksInQueue[$akey] ) )) {
				usort( $this->tasksInQueue[$akey], array( $this, '_compareTask' ) );
			}
		}


		if ($taskType == QS_BUILD_CREATEUPGRADE) {
			++$this->tasksInQueue[(4 < intval( $row['building_id'] ) ? 'buildsNum' : 'fieldsNum')];
		}


		if (( ( ( ( $taskType == QS_WAR_REINFORCE || $taskType == QS_WAR_ATTACK ) || $taskType == QS_WAR_ATTACK_PLUNDER ) || $taskType == QS_WAR_ATTACK_SPY ) || $taskType == QS_CREATEVILLAGE )) {
			if (( $taskType == QS_WAR_ATTACK_SPY && $row['village_id'] != $this->page->data['selected_village_id'] )) {
				return null;
			}


			if (( $taskType == QS_WAR_REINFORCE && $row['village_id'] == $this->page->data['selected_village_id'] )) {
				$_arr = explode( '|', $row['proc_params'] );

				if ($_arr[sizeof( $_arr ) - 1] == 1) {
					return null;
				}
			}

			$_key = ($taskType == QS_WAR_REINFORCE ? 'reinforce' : 'attacks');
			$fkey = ($row['village_id'] == $this->page->data['selected_village_id'] ? 'from_me' : ($row['to_village_id'] == $this->page->data['selected_village_id'] ? 'to_me' : 'to_my_oasis'));

			if ($taskType != QS_CREATEVILLAGE) {
				++$this->tasksInQueue['war_troops_summary']['total_number'];
				$war = &$this->tasksInQueue['war_troops_summary'][$fkey][$_key];

				++$war['number'];

				if (( $war['min_time'] == 0 - 1 || $row['remainingSeconds'] < $war['min_time'] )) {
					$war['min_time'] = $row['remainingSeconds'];
				}
			}


			if ($fkey == 'to_my_oasis') {
				if (!isset( $this->tasksInQueue['war_troops']['to_oasis'][$row['to_village_id']] )) {
					$this->tasksInQueue['war_troops']['to_oasis'][$row['to_village_id']] = array(  );
				}

				$this->tasksInQueue['war_troops']['to_oasis'][$row['to_village_id']][] = $_newTask;
				return null;
			}

			$warKey = ($row['village_id'] == $this->page->data['selected_village_id'] ? 'from_village' : 'to_village');
			$this->tasksInQueue['war_troops'][$warKey][] = $_newTask;
		}

	}

	function _setProductionPlusFeature($itemId) {
		$this->page->resources[$itemId]['prod_rate_percentage'] += 25;
		$this->page->resources[$itemId]['calc_prod_rate'] = floor( $this->page->resources[$itemId]['prod_rate'] * ( 1 + $this->page->resources[$itemId]['prod_rate_percentage'] / 100 ) ) - ($itemId == 4 ? $this->page->data['crop_consumption'] : 0);
		$this->_updateVillage( FALSE );
	}

	function _getNewUpdateKey() {
		$this->page->data['update_key'] = substr( md5( $this->page->data['update_key'] ), 2, 5 );
		return ;
	}

	function _changeUpdateKey() {
		$this->provider->executeQuery( 'UPDATE p_villages v SET v.update_key=\'%s\' WHERE v.id=%s', array( $this->_getNewUpdateKey(  ), $this->page->data['selected_village_id'] ) );
	}

	function _getNewTroops($decreaseTroops) {
		$newTroops = '';
		$t_arr = explode( '|', $this->page->data['troops_num'] );
		foreach ($t_arr as $t_str) {
			if ($newTroops != '') {
				$newTroops .= '|';
			}

			$t2_arr = explode( ':', $t_str );

			if ($t2_arr[0] == 0 - 1) {
				$newTroops .= $t2_arr[0] . ':';
				$vtroops = '';
				$t3_arr = explode( ',', $t2_arr[1] );
				foreach ($t3_arr as $t2_str) {
					list( $tid, $tnum ) = explode( ' ', $t2_str );

					if (isset( $decreaseTroops[$tid] )) {
						$tnum -= $decreaseTroops[$tid];

						if ($tnum < 0) {
							$tnum = 0;
						}
					}


					if ($vtroops != '') {
						$vtroops .= ',';
					}

					$vtroops .= $tid . ' ' . $tnum;
				}

				$newTroops .= $vtroops;
				continue;
			}

			$newTroops .= $t_str;
		}

		return $newTroops;
	}

	function _updateVillage($updateBuilding, $updateKey = TRUE, $newTroops = NULL) {
		$expr = '';
		$resources = '';
		foreach ($this->page->resources as $k => $v) {
			if ($resources != '') {
				$resources .= ',';
			}

			$resources .= sprintf( '%s %s %s %s %s %s', $k, $v['current_value'], $v['store_max_limit'], $v['store_init_limit'], $v['prod_rate'], $v['prod_rate_percentage'] );
		}

		$cp = $this->page->cpValue . ' ' . $this->page->cpRate;

		if ($updateBuilding) {
			$expr .= '';
			foreach ($this->page->buildings as $k => $v) {
				if ($expr != '') {
					$expr .= ',';
				}


				if ($v['update_state'] < 0) {
					$v['update_state'] = 0;
				}

				$expr .= sprintf( '%s %s %s', $v['item_id'], $v['level'], $v['update_state'] );
			}

			$expr = 'v.buildings=\'' . $expr . '\',';
		}


		if ($updateKey) {
			$this->_getNewUpdateKey(  );
			$expr .= 'v.update_key=\'' . $this->page->data['update_key'] . '\',';
		}


		if ($newTroops != NULL) {
			$expr .= 'v.troops_num=\'' . $newTroops . '\',';
		}

		$this->provider->executeQuery( 'UPDATE p_villages v
			SET
				' . $expr . '
				v.resources=\'%s\',
				v.cp=\'%s\',
				v.last_update_date=NOW()
			WHERE
				v.id=%s AND v.player_id=%s', array( $resources, $cp, $this->page->data['selected_village_id'], $this->page->player->playerId ) );
	}
}

?>
