<?php






	require( '.' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'boot.php' );
	class GPage extends ProcessVillagePage {
		var $troops = array();
		var $heroCount = 0;

		function GPage() {
			parent::processvillagepage();
			$this->viewFile = 'village1.phtml';
			$this->contentCssClass = 'village1';
		}

		function load() {
			if (( isset( $_GET['_gn_'] ) && !$this->player->isSpy )) {
				require_once( MODEL_PATH . 'profile.php' );
				$mprof = new ProfileModel();
				$mprof->resetGNewsFlag( $this->player->playerId );
			}

			parent::load();
			$this->heroCount = ($this->data['hero_in_village_id'] == $this->data['selected_village_id'] ? 1 : 0);
			$t_arr = explode( '|', $this->data['troops_num'] );
			foreach ($t_arr as $t_str) {
				$t2_arr = explode( ':', $t_str );
				$t2_arr = explode( ',', $t2_arr[1] );
				foreach ($t2_arr as $t2_str) {
					list( $tid, $tnum ) = explode( ' ', $t2_str );

					if (( $tid == 99 || $tnum == 0 )) {
						continue;
					}


					if ($tnum == 0 - 1) {
						$this->heroCount++;
						continue;
					}


					if (isset( $this->troops[$tid] )) {
						$this->troops += $tid = $tnum;
						continue;
					}

					$this->troops[$tid] = $tnum;
				}
			}

			ksort( $this->troops, SORT_NUMERIC );
		}

		function getBuildingName($id) {
			return htmlspecialchars( constant( 'item_' . $this->buildings[$id]['item_id'] ) . ' ' . level_lang . ' ' . $this->buildings[$id]['level'] );
		}

		function getBuildingTitle($id) {
			$name = $this->getBuildingName( $id );
			return 'title="' . $name . '" alt="' . $name . '"';
		}
	}

	$p = new GPage();
	$p->run();
?>



