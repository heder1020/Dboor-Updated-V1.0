<?php

class NewsModel extends ModelBase {
	function getSiteNews() {
		return $this->provider->fetchScalar( 'SELECT g.news_text FROM g_summary g' );
	}

	function setSiteNews($news) {
		$this->provider->executeQuery( 'UPDATE g_summary g SET g.news_text=\'%s\'', array( $news ) );
	}

	function getGlobalSiteNews() {
		return $this->provider->fetchScalar( 'SELECT g.gnews_text FROM g_summary g' );
	}

	function setGlobalPlayerNews($news) {
		$this->provider->executeQuery( 'UPDATE g_summary g SET g.gnews_text=\'%s\'', array( $news ) );
		$flag = (trim( $news ) != '' ? 1 : 0);
		$this->provider->executeQuery( 'UPDATE p_players p SET p.new_gnews=%s', array( $flag ) );
	}
}

?>
