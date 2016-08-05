<?php

class ProfileModel extends ModelBase {
   function getPlayerIdByName($playerName) {
      return $this->provider->fetchScalar( 'SELECT p.id FROM p_players p WHERE p.name=\'%s\'', array( $playerName ) );
   }

   function getPlayerAgentForById($playerId) {
      return $this->provider->fetchScalar( 'SELECT p.agent_for_players FROM p_players p WHERE p.id=%s', array( $playerId ) );
   }

   function getPlayerMyAgentById($playerId) {
      return $this->provider->fetchScalar( 'SELECT p.my_agent_players FROM p_players p WHERE p.id=%s', array( $playerId ) );
   }

   function setMyAgents($playerId, $playerName, $agents, $newAgentId) {
      $agentStr = '';
      foreach ($agents as $agentId => $agentName) {
         if ($agentStr != '') {
            $agentStr .= ',';
         }

         $agentStr .= $agentId . ' ' . $agentName;
      }

      $this->provider->executeQuery( 'UPDATE p_players p SET p.my_agent_players=\'%s\' WHERE p.id=%s', array( $agentStr, $playerId ) );
      $agentFor = $playerId . ' ' . $playerName;
      $this->provider->executeQuery( 'UPDATE p_players p SET p.agent_for_players=IF(ISNULL(p.agent_for_players) OR p.agent_for_players=\'\', \'%s\', CONCAT_WS(\',\', p.agent_for_players, \'%s\')) WHERE p.id=%s', array( $agentFor, $agentFor, $newAgentId ) );
   }

   function removeMyAgents($playerId, $agents, $aid) {
      $agentStr = '';
      foreach ($agents as $agentId => $agentName) {
         if ($agentStr != '') {
            $agentStr .= ',';
         }

         $agentStr .= $agentId . ' ' . $agentName;
      }

      $this->provider->executeQuery( 'UPDATE p_players p SET p.my_agent_players=\'%s\' WHERE p.id=%s', array( $agentStr, $playerId ) );
      $agentForStr = $this->getPlayerAgentForById( $aid );
      $agentForPlayers = (trim( $agentForStr ) == '' ? array(  ) : explode( ',', $agentForStr ));
      $i = 0;
      $c = sizeof( $agentForPlayers );

      while ($i < $c) {
         $agent = $agentForPlayers[$i];
         list( $agentId, $agentName ) = explode( ' ', $agent );

         if ($agentId == $playerId) {
            unset( $agentForPlayers[$i] );
         }

         ++$i;
      }

      $agentForStr = implode( ',', $agentForPlayers );
      $this->provider->executeQuery( 'UPDATE p_players p SET p.agent_for_players=\'%s\' WHERE p.id=%s', array( $agentForStr, $aid ) );
   }

   function removeAgentsFor($playerId, $agents, $aid) {
      $agentStr = '';
      foreach ($agents as $agentId => $agentName) {
         if ($agentStr != '') {
            $agentStr .= ',';
         }

         $agentStr .= $agentId . ' ' . $agentName;
      }

      $this->provider->executeQuery( 'UPDATE p_players p SET p.agent_for_players=\'%s\' WHERE p.id=%s', array( $agentStr, $playerId ) );
      $agentForStr = $this->getPlayerMyAgentById( $aid );
      $agentForPlayers = (trim( $agentForStr ) == '' ? array(  ) : explode( ',', $agentForStr ));
      $i = 0;
      $c = sizeof( $agentForPlayers );

      while ($i < $c) {
         $agent = $agentForPlayers[$i];
         list( $agentId, $agentName ) = explode( ' ', $agent );

         if ($agentId == $playerId) {
            unset( $agentForPlayers[$i] );
         }

         ++$i;
      }

      $agentForStr = implode( ',', $agentForPlayers );
      $this->provider->executeQuery( 'UPDATE p_players p SET p.my_agent_players=\'%s\' WHERE p.id=%s', array( $agentForStr, $aid ) );
   }

   function editPlayerProfile($playerId, $data) {
      $selected_village_id = $this->provider->fetchScalar( 'SELECT p.selected_village_id FROM p_players p WHERE p.id=%s', array( $playerId ) );
      $villages_data_arr = array(  );
      $villages_id_arr = explode( '
', $data['villages'] );
      $i = 0;
      $c = sizeof( $villages_id_arr );

      while ($i < $c) {
         list( $vid, $x, $y, $vname ) = explode( ' ', $villages_id_arr[$i], 4 );

         if ($vid == $selected_village_id) {
            $vname = $data['village_name'];
            $villages_id_arr[$i] = $vid . ' ' . $x . ' ' . $y . ' ' . $vname;
         }

         $villages_data_arr[$vname][] = $villages_id_arr[$i];
         ++$i;
      }

      ksort( $villages_data_arr );
      $villages_data = '';
      foreach ($villages_data_arr as $k => $v) {
         foreach ($villages_data_arr[$k] as $v2) {
            if ($villages_data != '') {
               $villages_data .= '
';
            }

            $villages_data .= $v2;
         }
      }

      $this->provider->executeQuery( 'UPDATE p_players p
         SET
            p.birth_date=\'%s\',
            p.gender=%s,
            p.house_name=\'%s\',
            p.description1=\'%s\',
            p.description2=\'%s\',
            p.villages_data=\'%s\',
            p.avatar=\'%s\'
         WHERE p.id=%s', array( $data['birthData'], $data['gender'], $data['house_name'], $data['description1'], $data['description2'], $villages_data, $data['avatar'], $playerId ) );
      $village_name = trim( $data['village_name'] );

      if ($village_name != '' && $village_name == htmlspecialchars($village_name)) {
         $this->provider->executeQuery( 'UPDATE p_villages v SET v.village_name=\'%s\' WHERE v.id=%s', array( $village_name, $selected_village_id ) );
      }

   }

   function changePlayerPassword($playerId, $newPassword) {
      $this->provider->executeQuery( 'UPDATE p_players p SET p.pwd=\'%s\' WHERE p.id=%s', array( $newPassword, $playerId ) );
   }

   function changePlayerEmail($playerId, $newEmail) {
      if (0 < intval( $this->provider->fetchScalar( 'SELECT COUNT(*) FROM p_players p WHERE p.email=\'%s\'', array( $newEmail ) ) )) {
         return null;
      }

      $this->provider->executeQuery( 'UPDATE p_players p SET p.email=\'%s\' WHERE p.id=%s', array( $newEmail, $playerId ) );
   }

   function getPlayerRank($playerId, $score) {
      return $this->provider->fetchScalar( 'SELECT (
            (SELECT
               COUNT(*)
            FROM p_players p
            WHERE p.player_type!=%s AND (p.total_people_count*10+p.villages_count)>%s)
            +
            (SELECT
               COUNT(*)
            FROM p_players p
            WHERE p.player_type!=%s AND p.id<%s AND (p.total_people_count*10+p.villages_count)=%s)
         ) + 1 rank', array( PLAYERTYPE_TATAR, $score, PLAYERTYPE_TATAR, $playerId, $score ) );
   }

   function getWinnerPlayer() {
      $playerId = intval( $this->provider->fetchScalar( 'SELECT gs.win_pid FROM g_settings gs' ) );
      return $this->getPlayerDataById( $playerId );
   }

   function getPlayerDataById($playerId) {
      $protectionPeriod = intval( $GLOBALS['GameMetadata']['player_protection_period'] / $GLOBALS['GameMetadata']['game_speed'] );
      return $this->provider->fetchRow( 'SELECT
            p.id,
            p.tribe_id,
            p.alliance_id,
            p.alliance_name,
            p.house_name,
            p.is_blocked,
            p.birth_date,
            p.gender,
            p.description1, p.description2,
            p.medals,
            p.total_people_count,
            p.villages_count,
            p.name,
            p.avatar,
            p.villages_id,
            DATE_FORMAT(registration_date, \'%%Y/%%m/%%d %%H:%%i\') registration_date,
            TIMEDIFF(DATE_ADD(registration_date, INTERVAL %s SECOND), NOW()) protection_remain,
            TIME_TO_SEC(TIMEDIFF(DATE_ADD(registration_date, INTERVAL %s SECOND), NOW())) protection_remain_sec,
            DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(birth_date)), \'%%Y\')+0 age
         FROM p_players p
         WHERE p.id=%s', array( $protectionPeriod, $protectionPeriod, $playerId ) );
   }

   function getVillagesSummary($villages_id) {
      return $this->provider->fetchResultSet( 'SELECT
            v.id,
            v.rel_x, v.rel_y,
            v.village_name,
            v.people_count,
            v.is_capital
         FROM p_villages v
         WHERE v.id IN (%s)
         ORDER BY v.people_count DESC', array( $villages_id ) );
   }

   function resetGNewsFlag($playerId) {
      $this->provider->executeQuery( 'UPDATE p_players p SET p.new_gnews=0 WHERE p.id=%s', array( $playerId ) );
   }
}

?>