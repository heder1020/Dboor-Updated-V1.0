<?php
	require( '.' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'boot.php' );
	class GPage extends ProcessVillagePage {
		var $showLevelsStr = null;

		function GPage() {
			parent::processvillagepage();
			$this->viewFile = 'village2.phtml';
			$this->contentCssClass = 'village2';
		}

		function load() {
			parent::load();
			// hot-fix for non static methods
			$cookie_client = new ClientData();
			$cookie = $cookie_client->getinstance();
			$this->showLevelsStr = ($cookie->showLevels ? 'on' : 'off');
		}

		function getWallCssName() {
			if (( $this->buildings[40]['level'] == 0 && $this->buildings[40]['update_state'] == 0 )) {
				return 'd2_0';
			}

			return $this->gameMetadata['tribes'][$this->data['tribe_id']]['wall_css'];
		}

		function getBuildingName($id) {
			$emptyName = '';
			switch ($id) {
				case 39: {
					$emptyName = buildin_place_railpoint;
					break;
				}

				case 40: {
					$emptyName = buildin_place_wall;
					break;
				}

				default: {
					$emptyName = (( $this->data['is_special_village'] && ( ( ( ( $id == 25 || $id == 26 ) || $id == 29 ) || $id == 30 ) || $id == 33 ) ) ? buildin_place_topbuild : buildin_place_empty);
					break;
				}
			}

			return htmlspecialchars( ($this->buildings[$id]['item_id'] == 0 ? $emptyName : constant( 'item_' . $this->buildings[$id]['item_id'] ) . ' ' . level_lang . ' ' . $this->buildings[$id]['level']) );
		}

		function getBuildingCssName($id) {
			$cssName = '';
			switch ($id) {
				case 39: {
					$e = '';

					if (( $this->buildings[$id]['level'] == 0 && 0 < $this->buildings[$id]['update_state'] )) {
						$e = 'b';
					} 
else {
						if ($this->buildings[$id]['level'] == 0) {
							$e = 'e';
						}
					}

					$cssName = 'g' . $this->buildings[$id]['item_id'] . $e;
					break;
				}

				case 25: {
				}

				case 26: {
				}

				case 29: {
				}

				case 30: {
				}

				case 33: {
					if ($this->data['is_special_village']) {
						$cssName = 'g40';

						if (20 <= $this->buildings[$id]['level']) {
							$cssName .= '_' . floor( $this->buildings[$id]['level'] / 20 );
						}

						break;
					}
				}

				default: {
					$e = (( $this->buildings[$id]['level'] == 0 && 0 < $this->buildings[$id]['update_state'] ) ? 'b' : '');
					$cssName = ($this->buildings[$id]['item_id'] == 0 ? 'iso' : 'g' . $this->buildings[$id]['item_id'] . $e);
					break;
				}
			}

			return $cssName;
		}

		function getBuildingTitle($id) {
			$name = $this->getBuildingName( $id );
			return 'title="' . $name . '" alt="' . $name . '"';
		}

		function getBuildingTitleClass($id) {
			$name = $this->getBuildingName( $id );
			$cssClass = $this->getBuildingCssName( $id );
			return $cssClass . '" alt="' . $name;
		}
	}


	$p = new GPage();
	$p->run();
?>



