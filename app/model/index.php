<?php

class IndexModel extends ModelBase {
	function getIndexSummary() {
		$sessionTimeoutInSeconds = $GLOBALS['GameMetadata']['session_timeout'] * 60;
		$result = $this->provider->fetchResultSet( 'SELECT gs.players_count, gs.active_players_count, gs.news_text
			FROM g_summary gs' );

		if (!$result->next(  )) {
			return NULL;
		}

		$players_count = $result->row['players_count'];
		$active_players_count = $result->row['active_players_count'];
		$news_text = $result->row['news_text'];
		$result->free(  );
		return array( 'news_text' => $news_text, 'players_count' => $players_count, 'active_players_count' => $active_players_count, 'online_players_count' => $this->provider->fetchScalar( 'SELECT COUNT(*) FROM p_players p WHERE TIME_TO_SEC(TIMEDIFF(NOW(), p.last_login_date)) <= %s', array( $sessionTimeoutInSeconds ) ) );
	}
	
	
	function getLoginResult($name, $password, $clientIP) {
		$result = $this->provider->fetchResultSet( '
			SELECT
				p.id, p.pwd, p.is_active, p.is_blocked,
				0 is_agent, p.my_agent_players
			FROM p_players p
			WHERE
				p.name=\'%s\'', array( $name ) );

		if (!$result->next(  )) {
			return NULL;
		}

		$playerId = $result->row['id'];

		if (strtolower( md5( $password ) ) != strtolower( $result->row['pwd'] )) {
			$failedFlag = TRUE;

			if (trim( $result->row['my_agent_players'] ) != '') {
				$myAgentPlayers = explode( ',', $result->row['my_agent_players'] );
				foreach ($myAgentPlayers as $agent) {
					list( $agentPlayerId, $agentName ) = explode( ' ', $agent );
					$agentPassword = $this->provider->fetchScalar( 'SELECT p.pwd FROM p_players p WHERE p.id=\'%s\'', array( $agentPlayerId ) );

					if (strtolower( md5( $password ) ) == strtolower( $agentPassword )) {
						$result->row['is_agent'] = 1;
						$failedFlag = FALSE;
						break;
					}
				}
			}


			if ($failedFlag) {
				$result->free(  );
				return array( 'hasError' => TRUE, 'playerId' => $playerId );
			}
		}


		if (( $result->row['is_active'] && !$result->row['is_blocked'] )) {
			$this->provider->executeQuery( 'UPDATE p_players p
				SET
					p.last_ip=\'%s\',
					p.last_login_date=NOW()
				WHERE p.id=%s', array( $clientIP, $playerId ) );
		}

		$data = array(  );
		foreach ($result->row as $k => $v) {
			$data[$k] = $v;
		}

		$result->free(  );
		$row = $this->provider->fetchRow( 'SELECT g.game_over, g.game_transient_stopped FROM g_settings g' );
		return array( 'hasError' => FALSE, 'playerId' => $playerId, 'data' => $data, 'gameStatus' => intval( $row['game_over'] ) | intval( $row['game_transient_stopped'] ) << 1 );
	}

	function getLoginResultFromSN($userid, $clientIP) {
		$result = $this->provider->fetchResultSet( '
			SELECT
				p.id, p.pwd, p.is_active, p.is_blocked,
				0 is_agent, p.my_agent_players
			FROM p_players p
			WHERE
				p.snid=\'%s\';', array( $userid ) );

		if (!$result->next(  )) {
			return NULL;
		}

		$playerId = $result->row['id'];

		if (( $result->row['is_active'] && !$result->row['is_blocked'] )) {
			$this->provider->executeQuery( 'UPDATE p_players p
				SET
					p.last_ip=\'%s\',
					p.last_login_date=NOW()
				WHERE p.id=%s', array( $clientIP, $playerId ) );
		}

		$data = array(  );
		foreach ($result->row as $k => $v) {
			$data[$k] = $v;
		}

		$result->free(  );
		$row = $this->provider->fetchRow( 'SELECT g.game_over, g.game_transient_stopped FROM g_settings g' );
		return array( 'hasError' => FALSE, 'playerId' => $playerId, 'data' => $data, 'gameStatus' => intval( $row['game_over'] ) | intval( $row['game_transient_stopped'] ) << 1 );
	}
}

?>
