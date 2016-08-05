<?php

class PasswordModel extends ModelBase {
	function isPlayerIdExists($playerId) {
		return 0 < $this->provider->fetchScalar( 'SELECT COUNT(*) FROM p_players p WHERE p.id=%s', array( $playerId ) );
	}

	function isPlayerIdHasEmail($playerId, $email) {
		return 0 < $this->provider->fetchScalar( 'SELECT COUNT(*) FROM p_players p WHERE p.id=%s AND p.email=\'%s\'', array( $playerId, $email ) );
	}

	function getPlayerName($playerId) {
		return $this->provider->fetchScalar( 'SELECT p.name FROM p_players p WHERE p.id=%s', array( $playerId ) );
	}

	function setPlayerPassword($playerId, $password) {
		$this->provider->executeQuery( 'UPDATE p_players p SET p.pwd=\'%s\' WHERE p.id=%s', array( md5( $password ), $playerId ) );
	}
}

?>
