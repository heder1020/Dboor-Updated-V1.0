<?php

class BadWordsModel extends ModelBase {
	function addBadWords($BadWords) {
		$i = 0;

		while ($i < count( $BadWords )) {
			$this->provider->executeQuery( 'INSERT INTO `g_words` SET `word` = \'%s\' ;', array( $BadWords[$i] ) );
			++$i;
		}

	}

	function DeleteBadWords($BadWordID) {
		$this->provider->executeQuery( 'DELETE FROM `g_words` WHERE `ID` = \'%s\' ;', array( $BadWordID ) );
	}

	function GetBadWords($pageIndex, $pageSize) {
		return $this->provider->fetchResultSet( 'SELECT * FROM `g_words` LIMIT %s,%s;;', array( $pageIndex * $pageSize, $pageSize ) );
	}

	function getBadWordsCount() {
		return $this->provider->fetchScalar( 'SELECT COUNT(*) FROM `g_words` ;' );
	}
}

?>
