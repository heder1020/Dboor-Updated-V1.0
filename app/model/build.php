<?php

class BuildModel extends ModelBase {
	function getVillageOases($villageOasesid) {
		if ($villageOasesid == '') {
			return NULL;
		}

		return $this->provider->fetchResultSet( 'SELECT
				v.id,
				v.rel_x, v.rel_y,
				v.image_num,
				v.allegiance_percent
			FROM
				p_villages v
			WHERE
				v.id IN (%s)', array( $villageOasesid ) );
	}

	function getChildVillagesFor($villageIds) {
		if ($villageIds == '') {
			return NULL;
		}

		return $this->provider->fetchResultSet( 'SELECT
				v.id,
				v.village_name,
				v.people_count,
				v.rel_x, v.rel_y,
				DATE_FORMAT(v.creation_date, \'%%Y/%%m/%%d\') creation_date
			FROM
				p_villages v
			WHERE
				v.id IN (%s)', array( $villageIds ) );
	}

	function getVillagesCp($villages_id) {
		return $this->provider->fetchResultSet( 'SELECT
				v.cp,
				TIME_TO_SEC(TIMEDIFF(NOW(), v.last_update_date)) elapsedTimeInSeconds
			FROM p_villages v
			WHERE v.id IN (%s)', array( $villages_id ) );
	}

	function getVillageDataByName($villagesName) {
		return $this->provider->fetchRow( 'SELECT v.id, v.rel_x, v.rel_y, v.village_name, v.player_id, v.player_name FROM p_villages v WHERE v.is_oasis=0 AND v.village_name=\'%s\'', array( $villagesName ) );
	}

	function getVillageDataById($villagesId) {
		return $this->provider->fetchRow( 'SELECT v.id, v.rel_x, v.rel_y, v.village_name, v.player_id, v.player_name FROM p_villages v WHERE v.id=\'%s\' AND NOT ISNULL(v.player_id) AND v.is_oasis=0', array( $villagesId ) );
	}

	function getVillageName($villageId) {
		return $this->provider->fetchScalar( 'SELECT v.village_name FROM p_villages v WHERE v.id=%s', array( $villageId ) );
	}

	function getPlayerName($playerId) {
		return $this->provider->fetchScalar( 'SELECT p.name FROM p_players p WHERE p.id=%s', array( $playerId ) );
	}

	function getPlayType($player_id) {
		return $this->provider->fetchScalar( 'SELECT p.player_type FROM p_players p WHERE p.id=%s', array( $player_id ) );
	}

	function getPlayerAllianceId($playerId) {
		return $this->provider->fetchScalar( 'SELECT p.alliance_id FROM p_players p WHERE p.id=%s', array( $playerId ) );
	}

	function getOffers($villageId) {
		return $this->provider->fetchResultSet( 'SELECT m.* FROM p_merchants m WHERE m.village_id=%s ORDER BY m.id ASC', array( $villageId ) );
	}

	function getAllOffersCount($villageId, $x, $y, $radius, $speed) {
		$angle = $radius / 180;
		$x /= $angle;
		$y /= $angle;
		return $this->provider->fetchScalar( 'SELECT
				COUNT(*)
			FROM p_merchants m
			WHERE
				m.village_id!=%s
				AND IF(m.max_time>0,
					((ACOS(SIN(%s * PI() / 180) * SIN(m.village_x/%s * PI() / 180) + COS(%s * PI() / 180) * COS(m.village_x/%s * PI() / 180) * COS((%s - m.village_y/%s) * PI() / 180)) * 180 / PI()) * %s)/%s*3600
					<=m.max_time*3600,
				1)', array( $villageId, $x, $angle, $x, $angle, $y, $angle, $angle, $speed ) );
	}

	function getAllOffers($villageId, $x, $y, $radius, $speed, $pageIndex, $pageSize) {
		$angle = $radius / 180;
		$x /= $angle;
		$y /= $angle;
		return $this->provider->fetchResultSet( 'SELECT
				m.*,
				((ACOS(SIN(%s * PI() / 180) * SIN(m.village_x/%s * PI() / 180) + COS(%s * PI() / 180) * COS(m.village_x/%s * PI() / 180) * COS((%s - m.village_y/%s) * PI() / 180)) * 180 / PI()) * %s)/m.merchants_speed*3600  timeInSeconds
			FROM p_merchants m
			HAVING
				m.village_id!=%s
				AND IF(m.max_time>0, timeInSeconds*m.merchants_speed/%s<=m.max_time*3600,1)
			ORDER BY timeInSeconds ASC
			LIMIT %s,%s', array( $x, $angle, $x, $angle, $y, $angle, $angle, $villageId, $speed, $pageIndex * $pageSize, $pageSize ) );
	}

	function getOffer($offerId, $playerId, $villageId) {
		return $this->provider->fetchRow( 'SELECT m.* FROM p_merchants m WHERE id=%s AND player_id=%s AND village_id=%s', array( $offerId, $playerId, $villageId ) );
	}

	function getOffer2($offerId, $x, $y, $radius) {
		$angle = $radius / 180;
		$x /= $angle;
		$y /= $angle;
		return $this->provider->fetchRow( 'SELECT
				m.*,
				((ACOS(SIN(%s * PI() / 180) * SIN(m.village_x/%s * PI() / 180) + COS(%s * PI() / 180) * COS(m.village_x/%s * PI() / 180) * COS((%s - m.village_y/%s) * PI() / 180)) * 180 / PI()) * %s)/m.merchants_speed*3600  timeInSeconds
			FROM p_merchants m
			WHERE
				id=%s', array( $x, $angle, $x, $angle, $y, $angle, $angle, $offerId ) );
	}

	function removeMerchantOffer($offerId, $playerId, $villageId) {
		$merchants_num = intval( $this->provider->fetchScalar( 'SELECT merchants_num FROM p_merchants WHERE id=%s', array( intval( $offerId ) ) ) );

		if ($merchants_num <= 0) {
			return null;
		}

		$this->provider->executeQuery( 'UPDATE p_villages v SET
			v.offer_merchants_count=IF(v.offer_merchants_count-%s<0, 0, v.offer_merchants_count-%s)
			WHERE
				v.id=%s', array( $merchants_num, $merchants_num, $villageId ) );
		$this->provider->executeQuery( 'DELETE FROM p_merchants WHERE id=%s AND player_id=%s AND village_id=%s', array( intval( $offerId ), $playerId, $villageId ) );
	}

	function addMerchantOffer($playerId, $playerName, $villageId, $x, $y, $merchantNum, $offer, $allianceOnly, $maxTime, $merchantsSpeed) {
		$this->provider->executeQuery( 'INSERT INTO p_merchants SET
			player_id=%s,
			player_name=\'%s\',
			village_id=%s,
			village_x=%s,
			village_y=%s,
			offer=\'%s\',
			alliance_only=%s,
			max_time=%s,
			merchants_num=%s,
			merchants_speed=%s', array( $playerId, $playerName, $villageId, $x, $y, $offer, ($allianceOnly ? 1 : 0), $maxTime, $merchantNum, $merchantsSpeed ) );
		$this->provider->executeQuery( 'UPDATE p_villages v SET
			v.offer_merchants_count=v.offer_merchants_count+%s
			WHERE
				v.id=%s', array( $merchantNum, $villageId ) );
	}

	function makeVillageAsCapital($playerId, $villageId) {
		$mq = new QueueJobModel(  );
		$capitalRow = $this->provider->fetchRow( 'SELECT v.id, v.buildings FROM p_villages v WHERE  v.player_id=%s AND v.is_capital=1', array( $playerId ) );
		$buildingArr = explode( ',', $capitalRow['buildings'] );
		$c = 0;
		foreach ($buildingArr as $buildingItem) {
			++$c;
			list( $item_id, $level, $update_state ) = explode( ' ', $buildingItem );

			if ($item_id == 0) {
				continue;
			}

			$max_lvl_in_non_capital = $GLOBALS['GameMetadata']['items'][$item_id]['max_lvl_in_non_capital'];

			if (( $max_lvl_in_non_capital == NULL || $level + $update_state <= $max_lvl_in_non_capital )) {
				continue;
			}

			$dropLevels = $level + $update_state - $max_lvl_in_non_capital;

			while (0 < $dropLevels--) {
				$mq->upgradeBuilding( $capitalRow['id'], $c, $item_id, TRUE );
			}
		}

		$this->provider->executeQuery( 'UPDATE p_villages v SET v.is_capital=0 WHERE v.player_id=%s', array( $playerId ) );
		$this->provider->executeQuery( 'UPDATE p_villages v SET v.is_capital=1 WHERE v.id=%s AND v.player_id=%s', array( $villageId, $playerId ) );
	}

	function changeHeroName($playerId, $heroName) {
		$this->provider->executeQuery( 'UPDATE p_players p SET p.hero_name=\'%s\' WHERE p.id=%s', array( $heroName, $playerId ) );
	}

	function decreaseGoldNum($playerId, $goldCost) {
		$this->provider->executeQuery( 'UPDATE p_players p SET p.gold_num=p.gold_num-%s WHERE p.id=%s', array( $goldCost, $playerId ) );
	}

	function allianceExists($allianceName) {
		return 0 < intval( $this->provider->fetchScalar( 'SELECT a.id FROM p_alliances a WHERE a.name=\'%s\'', array( $allianceName ) ) );
	}

	function createAlliance($playerId, $allianceName, $allianceName2, $maxPlayer) {
		$allianceRoles = ( ALLIANCE_ROLE_SETROLES | ALLIANCE_ROLE_REMOVEPLAYER | ALLIANCE_ROLE_EDITNAMES | ALLIANCE_ROLE_EDITCONTRACTS | ALLIANCE_ROLE_SENDMESSAGE | ALLIANCE_ROLE_INVITEPLAYERS ) . ' ' . alliance_creator;
		$this->provider->executeQuery( 'INSERT INTO p_alliances
			SET
				name=\'%s\',
				name2=\'%s\',
				creator_player_id=%s,
				rating=0,
				creation_date=NOW(),
				player_count=1,
				max_player_count=%s,
				players_ids=\'%s\'', array( $allianceName, $allianceName2, $playerId, $maxPlayer, $playerId ) );
		$aid = $this->provider->fetchScalar( 'SELECT LAST_INSERT_ID() FROM p_alliances' );
		$this->provider->executeQuery( 'UPDATE p_players p SET p.alliance_id=%s, p.alliance_name=\'%s\', p.alliance_roles=\'%s\' WHERE p.id=%s', array( $aid, $allianceName, $allianceRoles, $playerId ) );
		$this->provider->executeQuery( 'UPDATE p_villages v SET v.alliance_id=%s, v.alliance_name=\'%s\' WHERE v.player_id=%s', array( $aid, $allianceName, $playerId ) );
		return $aid;
	}

	function acceptAllianceJoining($playerId, $allianceId) {
		$row = $this->provider->fetchRow( 'SELECT a.* FROM p_alliances a WHERE a.id=%s', array( $allianceId ) );

		if ($row == NULL) {
			return 0;
		}


		if ($row['max_player_count'] <= $row['player_count']) {
			return 1;
		}

		$allianceName = $row['name'];
		$players_ids = $row['players_ids'];

		if ($players_ids != '') {
			$players_ids .= ',';
		}

		$players_ids .= $playerId;
		$this->provider->executeQuery( 'UPDATE p_alliances a SET a.player_count=a.player_count+1, a.players_ids=\'%s\' WHERE a.id=%s', array( $players_ids, $allianceId ) );
		$this->provider->executeQuery( 'UPDATE p_players p SET p.alliance_id=%s, p.alliance_name=\'%s\' WHERE p.id=%s', array( $allianceId, $allianceName, $playerId ) );
		$this->provider->executeQuery( 'UPDATE p_villages v SET v.alliance_id=%s, v.alliance_name=\'%s\' WHERE v.player_id=%s', array( $allianceId, $allianceName, $playerId ) );
		return 2;
	}

	function _getNewInvite($invitesString, $removeId) {
		if ($invitesString == '') {
			return '';
		}

		$result = '';
		$arr = explode( '
', $invitesString );
		foreach ($arr as $invite) {
			list( $id, $name ) = explode( ' ', $invite, 2 );

			if ($id == $removeId) {
				continue;
			}


			if ($result != '') {
				$result .= '
';
			}

			$result .= $id . ' ' . $name;
		}

		return $result;
	}

	function removeAllianceInvites($playerId, $allianceId) {
		$pRow = $this->provider->fetchRow( 'SELECT p.name, p.invites_alliance_ids FROM p_players p WHERE p.id=%s', array( $playerId ) );
		$aRow = $this->provider->fetchRow( 'SELECT a.name, a.invites_player_ids FROM p_alliances a WHERE a.id=%s', array( $allianceId ) );
		$pInvitesStr = $this->_getNewInvite( trim( $pRow['invites_alliance_ids'] ), $allianceId );
		$aInvitesStr = $this->_getNewInvite( trim( $aRow['invites_player_ids'] ), $playerId );
		$this->provider->executeQuery( 'UPDATE p_players p SET p.invites_alliance_ids=\'%s\' WHERE p.id=%s', array( $pInvitesStr, $playerId ) );
		$this->provider->executeQuery( 'UPDATE p_alliances a SET a.invites_player_ids=\'%s\' WHERE a.id=%s', array( $aInvitesStr, $allianceId ) );
	}

	function getVillageData2ById($villageId) {
		return $this->provider->fetchRow( 'SELECT v.id, v.tribe_id, v.is_oasis, v.village_name, v.player_id, v.player_name FROM p_villages v WHERE v.id=%s', array( $villageId ) );
	}

	function getOasesDataById($villagesId) {
		return $this->provider->fetchResultSet( 'SELECT v.id, v.tribe_id, v.rel_x, v.rel_y, v.troops_num, v.player_id, v.player_name FROM p_villages v WHERE v.id IN(%s)', array( $villagesId ) );
	}
}

?>
