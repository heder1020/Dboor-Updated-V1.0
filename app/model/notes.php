<?php
class NotesModel extends ModelBase {
	function changePlayerNotes($playerId, $notes) {
		$this->provider->executeQuery( 'UPDATE p_players p SET p.notes=\'%s\' WHERE p.id=%s', array( $notes, $playerId ) );
	}
}

?>
