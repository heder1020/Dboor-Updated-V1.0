<?php
require_once( MODEL_PATH . 'register.php' );
require_once( MODEL_PATH . 'queue.php' );
class SetupModel extends ModelBase {
   function processSetup($map_size, $adminEmail) {
    $this->_emptyTables(); // lets empty tables instead of creating them over and over with every new round
	$this->_insertBasics(); // insert default values we should have in our g_summary , g_settings, g_banner setings.
	
      $this->_createMap( $map_size );

      if ($this->_createAdminPlayer( $map_size, $adminEmail )) {
         $raiseTime = $GLOBALS['GameMetadata']['natar_rise'];
         $raiseTime = intval( $raiseTime );


         $queueModel = new QueueModel(  );
         new  QueueTask ($queueModel->addTask( QS_TATAR_RAISE, 0, $raiseTime ) );
         GameLicense::set( WebHelper::getdomain());
      }

   }

   function _emptyTables() {
		$this->provider->executeQuery("truncate table `g_summary`");
		$this->provider->executeQuery("truncate table `g_settings`");
		$this->provider->executeQuery("truncate table `g_summary`");
		$this->provider->executeQuery("truncate table `p_alliances`");
		$this->provider->executeQuery("truncate table `p_merchants`");
		$this->provider->executeQuery("truncate table `p_msgs`");
		$this->provider->executeQuery("truncate table `p_players`");
		$this->provider->executeQuery("truncate table `p_queue`");
		$this->provider->executeQuery("truncate table `p_rpts`");
		$this->provider->executeQuery("truncate table `p_villages`");
		$this->provider->executeQuery("truncate table `g_chat`");
		$this->provider->executeQuery("truncate table `g_comment`");
		$this->provider->executeQuery("truncate table `g_profile`");
		$this->provider->executeQuery("truncate table `p_friends`");
		$this->provider->executeQuery("truncate table `privatechat`");
   }
   
   function _insertBasics(){
	   $this->provider->executeBatchQuery('
			INSERT INTO `g_settings`(`start_date`,`license_key`) VALUES (NOW(),NULL);
			INSERT INTO `g_summary`(`players_count`,`active_players_count`,`Arab_players_count`,`Roman_players_count`,`Teutonic_players_count`,`Gallic_players_count`,`news_text`) VALUES ( \'0\',\'0\',\'0\',\'0\',\'0\',\'0\',NULL);
			OPTIMIZE TABLE `g_banner`, `g_chat`, `g_settings`, `g_summary`, `g_words`, `privatechat`, `p_alliances`, `p_comment`, `p_friends`, `p_merchants`, `p_msgs`, `p_players`, `p_profile`, `p_queue`, `p_rpts`, `p_villages`;
	   ');
   }

   function _createMap($map_size) {
      $maphalf_size = floor( $map_size / 2 );
      $oasis_troop_ids = array(  );
      foreach ($GLOBALS['GameMetadata']['troops'] as $k => $v) {
         if ($v['for_tribe_id'] == 4) {
            $oasis_troop_ids[] = $k;
            continue;
         }
      }

      $i = 0;

      while ($i < $map_size) {
         $queryBatch = array(  );
         $j = 0;

         while ($j < $map_size) {
            $rel_x = ($maphalf_size < $i ? $i - $map_size : $i);
            $rel_y = ($maphalf_size < $j ? $j - $map_size : $j);
            $troops_num = '';
            $field_maps_id = 0;
            $rand_num = 'NULL';
            $creation_date = 'NULL';

            if (( $rel_x == 0 && $rel_y == 0 )) {
               $r = 1;
            }
            else {
               $r_arr = array( 1, 1, 1, 1, 1, 1, 0, 1, mt_rand( 0, 1 ), mt_rand( 0, 1 ), 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, mt_rand( 0, 1 ) );
               $r = $r_arr[mt_rand( 0, 48 )];
            }


            if ($r == 1) {
               $image_num = mt_rand( 0, 9 );
               $is_oasis = 0;
               $tribe_id = 0;

               if (( $rel_x == 0 && $rel_y == 0 )) {
                  $field_maps_id = 3;
               }
               else {
                  $fr_arr = array( 3, mt_rand( 1, 12 ), 3, mt_rand( 1, 4 ), mt_rand( 1, 5 ), 3, mt_rand( 1, 12 ), 3, mt_rand( 7, 11 ), mt_rand( 7, 12 ), 3, 3, mt_rand( 1, 12 ) );
                  $field_maps_id = $fr_arr[mt_rand( 0, 12 )];
               }


               if ($field_maps_id == 3) {
                  $pr_arr = array( 0, 1, 0, 0, mt_rand( 0, 1 ) );
                  $pr = $pr_arr[mt_rand( 0, 4 )];
                  $rand_num = ($pr == 1 ? abs( $rel_x ) + abs( $rel_y ) : 310);
               }
            }
            else {
               $image_num = mt_rand( 1, 12 );
               $is_oasis = 1;
               $tribe_id = 4;
               $creation_date = 'NOW()';
               $troops_num = $oasis_troop_ids[mt_rand( 0, 2 )] . ' ' . mt_rand( 1, 5 );
               $troops_num .= ',' . $oasis_troop_ids[mt_rand( 3, 5 )] . ' ' . mt_rand( 2, 6 );
               $troops_num .= ',' . $oasis_troop_ids[mt_rand( 6, 8 )] . ' ' . mt_rand( 3, 7 );

               if (mt_rand( 0, 1 ) == 1) {
                  $troops_num .= ',' . $oasis_troop_ids[9] . ' ' . mt_rand( 2, 8 );
               }

               $troops_num = '-1:' . $troops_num;
            }

            $queryBatch[] = '(' . $rel_x . ',' . $rel_y . ',' . $image_num . ',' . $rand_num . ',' . $field_maps_id . ',' . $tribe_id . ',' . $is_oasis . ',\'' . $troops_num . '\',' . $creation_date . ')';
            ++$j;
         }

         $this->provider->executeQuery( 'INSERT INTO p_villages (rel_x,rel_y,image_num,rand_num,field_maps_id,tribe_id,is_oasis,troops_num,creation_date) VALUES' . implode( ',', $queryBatch ) );
         unset( $queryBatch );
         $queryBatch = NULL;
         ++$i;
      }
   $this->provider->executeQuery("UPDATE `p_villages` SET `resources` = '1 ". 2001 * $GLOBALS['GameMetadata']['capacity']." ". 2000 * $GLOBALS['GameMetadata']['capacity']." ". 2000*$GLOBALS['GameMetadata']['speed']." ". 1000*$GLOBALS['GameMetadata']['capacity']." 0,2 ". 2001 * $GLOBALS['GameMetadata']['capacity']." ". 2000 * $GLOBALS['GameMetadata']['capacity']." ". 2000*$GLOBALS['GameMetadata']['speed']." ". 1000*$GLOBALS['GameMetadata']['capacity']." 0,3 ". 2001 * $GLOBALS['GameMetadata']['capacity']." ". 2000 * $GLOBALS['GameMetadata']['capacity']." ". 2000*$GLOBALS['GameMetadata']['speed']." ". 1000*$GLOBALS['GameMetadata']['capacity']." 0,4 ". 2001 * $GLOBALS['GameMetadata']['capacity']." ". 2000 * $GLOBALS['GameMetadata']['capacity']." ". 2000*$GLOBALS['GameMetadata']['speed']." ". 1000*$GLOBALS['GameMetadata']['capacity']." 0' WHERE `p_villages`.`is_oasis` = 1");
   }

   function _createAdminPlayer($map_size, $adminEmail) {
      $m = new RegisterModel(  );
      $adminName = $GLOBALS['AppConfig']['system']['adminName'];
      $result = $m->createNewPlayer( $adminName, $adminEmail, $GLOBALS['AppConfig']['system']['adminPassword'], 1, 0, $adminName, $map_size, PLAYERTYPE_ADMIN );

      if ($result['hasErrors']) {
         return FALSE;
      }

      $m->dispose(  );
      return TRUE;
   }
}

?>