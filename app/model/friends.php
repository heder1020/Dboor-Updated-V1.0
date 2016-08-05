<?php

class FriendsModel extends ModelBase {
	function SendInvitation($post) {
		$PlayerCounter = intval( $this->provider->fetchScalar( 'SELECT `id` FROM `p_players` WHERE `name` LIKE \'%s\';', array( $post['playerName'] ) ) );
		$count = $this->provider->fetchScalar( 'SELECT COUNT(*) FROM `p_friends` WHERE
		(`playerid1` = \'%s\' AND `playerid2` = \'%s\') OR
		(`playerid2` = \'%s\' AND `playerid1` = \'%s\') ;', array( $post['playerId1'], $PlayerCounter, $post['playerId1'], $PlayerCounter ) );

		if ($count == 0) {
			if ($PlayerCounter != 0) {
				$this->provider->executeQuery( 'INSERT INTO `p_friends` SET
					`playerid1` = \'%s\',
					`playername1` = \'%s\',
					`playerid2` = \'%s\',
					`playername2` = \'%s\',
					`accept` = \'0\',
					`date` = \'%s\'
					 ;', array( $post['playerId1'], $post['myname'], $PlayerCounter, $post['playerName'], time(  ) ) );
			}
		}

	}

	function ConfirmInvitation($FriendID, $playerId) {
		$this->provider->executeQuery( 'UPDATE `p_friends` SET
			`accept` = \'1\'
			WHERE `ID` = \'%s\' AND (`playerid1` = \'%s\' OR `playerid2` = \'%s\');', array( $FriendID, $playerId, $playerId ) );
	}

	function DeleteFriend($FriendID, $playerId) {
		$this->provider->executeQuery( 'DELETE FROM `p_friends` WHERE `ID` = \'%s\' AND (`playerid1` = \'%s\' OR `playerid2` = \'%s\') ;', array( $FriendID, $playerId, $playerId ) );
	}

	function GetFriends($playerId, $pageIndex, $pageSize) {
		return $this->provider->fetchResultSet( 'SELECT * FROM `p_friends` WHERE `playerid1` = \'%s\' OR `playerid2` = \'%s\' LIMIT %s,%s;', array( $playerId, $playerId, $pageIndex * $pageSize, $pageSize ) );
	}

	function getFriendsCount($playerId) {
		return $this->provider->fetchScalar( 'SELECT COUNT(*) FROM `p_friends` WHERE `playerid1` = \'%s\' OR `playerid2` = \'%s\';', array( $playerId, $playerId ) );
	}
}

?>
