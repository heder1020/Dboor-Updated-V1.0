<?php

class LinksModel extends ModelBase {
	function changePlayerLinks($playerId, $links) {
		$this->provider->executeQuery( 'UPDATE p_players p SET p.custom_links=\'%s\' WHERE p.id=%s', array( $links, $playerId ) );
	}
}

?>
