<?php





require( '.' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'boot.php' );
require_once( MODEL_PATH . 'report.php' );
class GPage extends SecureGamePage {
   var $showList = null;
   var $selectedTabIndex = null;
   var $reportData = null;
   var $dataList = null;
   var $pageSize = 10;
   var $pageCount = null;
   var $pageIndex = null;

   function GPage() {
      parent::securegamepage();
      $this->viewFile = 'report.phtml';
      $this->contentCssClass = 'reports';
   }

   function load() {
      parent::load();
      $this->showList = !( isset( $_GET['id'] ) && 0 < intval( $_GET['id'] ) );
      $this->selectedTabIndex = (( ( ( ( $this->showList && isset( $_GET['t'] ) ) && is_numeric( $_GET['t'] ) ) && 1 <= intval( $_GET['t'] ) ) && intval( $_GET['t'] ) <= 4 ) ? intval( $_GET['t'] ) : 0);
      $m = new ReportModel();

      if (!$this->isPost()) {
         if (!$this->showList) {
            $this->selectedTabIndex = 0;
            $reportId = intval( $_GET['id'] );
            $result = $m->getReport( $reportId );

            if ($result->next()) {
               $readStatus = $result->row['read_status'];
               $deleteStatus = $result->row['delete_status'];
               $this->reportData = array();
               $this->reportData['messageDate'] = $result->row['mdate'];
               $this->reportData['messageTime'] = $result->row['mtime'];
               $this->reportData['from_player_id'] = $from_player_id = intval( $result->row['from_player_id'] );
               $this->reportData['to_player_id'] = $to_player_id = intval( $result->row['to_player_id'] );
               $this->reportData['from_village_id'] = intval( $result->row['from_village_id'] );
               $this->reportData['to_village_id'] = intval( $result->row['to_village_id'] );
               $this->reportData['from_player_name'] = $result->row['from_player_name'];
               $this->reportData['to_player_name'] = $result->row['to_player_name'];
               $this->reportData['to_village_name'] = $result->row['to_village_name'];
               $this->reportData['from_village_name'] = $result->row['from_village_name'];
               $this->reportData['rpt_body'] = $result->row['rpt_body'];
               $this->reportData['rpt_cat'] = $result->row['rpt_cat'];
               $this->reportData['mdate'] = $result->row['mdate'];
               $this->reportData['mtime'] = $result->row['mtime'];
               $this->reportData['to_player_alliance_id'] = $m->getPlayerAllianceId( $to_player_id );
               switch ($this->reportData['rpt_cat']) {
               case 1: {
                     $this->reportData['resources'] = explode( ' ', $this->reportData['rpt_body'] );
                     break;
                  }

               case 2: {
                     $this->reportData['cropConsume'] = explode( '|', $this->reportData['rpt_body'] );
                     $this->reportData['cropConsume'] = $this->reportData['cropConsume'][1];
                     $troopsStr = explode( "|", $this->reportData['rpt_body'] );
                     list( $troopsStr ) = $troopsStr;
                     $this->reportData['troopsTable'] = array( 'troops' => array(), 'hasHero' => FALSE );
                     $troopsStrArr = explode( ',', $troopsStr );
                     foreach ($troopsStrArr as $t) {
                        list( $tid, $tnum ) = explode( ' ', $t );

                        if ($tnum == 0 - 1) {
                           $this->reportData['troopsTable']['hasHero'] = TRUE;
                           continue;
                        }

                        $this->reportData['troopsTable']['troops'][$tid] = $tnum;
                     }

                     break;
                  }

               case 3: {
                     $bodyArr = explode( '|', $this->reportData['rpt_body'] );
                     list( $attackTroopsStr, $defenseTableTroopsStr, $total_carry_load, $harvestResources ) = $bodyArr;
                     $wallDestructionResult = (isset( $bodyArr[4] ) ? $bodyArr[4] : '');
                     $catapultResult = (isset( $bodyArr[5] ) ? $bodyArr[5] : '');
                     $oasisResult = (isset( $bodyArr[6] ) ? $bodyArr[6] : '');
                     $captureResult = (isset( $bodyArr[7] ) ? $bodyArr[7] : '');
                     $this->reportData['total_carry_load'] = $total_carry_load;
                     $this->reportData['total_harvest_carry_load'] = 0;
                     $this->reportData['harvest_resources'] = array();
                     $res = explode( ' ', $harvestResources );
                     foreach ($res as $r) {
                        $this->reportData['total_harvest_carry_load'] += $r;
                        $this->reportData['harvest_resources'][] = $r;
                     }

                     $attackTroopsStrArr = explode( ',', $attackTroopsStr );
                     $this->reportData['attackTroopsTable'] = array( 'troops' => array(), 'heros' => array( 'number' => 0, 'dead_number' => 0 ) );
                     $totalAttackTroops_live = 0;
                     $totalAttackTroops_dead = 0;
                     $attackWallDestrTroopId = 0;
                     $attackCatapultTroopId = 0;
                     $kingTroopId = 0;
                     foreach ($attackTroopsStrArr as $s) {
                        list( $tid, $num, $deadNum ) = explode( ' ', $s );
                        $totalAttackTroops_live += $num;
                        $totalAttackTroops_dead += $deadNum;

                        if (( ( ( ( $tid == 7 || $tid == 17 ) || $tid == 27 ) || $tid == 106 ) || $tid == 57 )) {
                           $attackWallDestrTroopId = $tid;
                        }
                        else {
                           if (( ( ( ( $tid == 8 || $tid == 18 ) || $tid == 28 ) || $tid == 107 ) || $tid == 58 )) {
                              $attackCatapultTroopId = $tid;
                           }
                           else {
                              if (( ( ( ( $tid == 9 || $tid == 19 ) || $tid == 29 ) || $tid == 108 ) || $tid == 59 )) {
                                 $kingTroopId = $tid;
                              }
                           }
                        }


                        if ($tid == 0 - 1) {
                           $this->reportData['attackTroopsTable']['heros']['number'] = $num;
                           $this->reportData['attackTroopsTable']['heros']['dead_number'] = $deadNum;
                           continue;
                        }

                        $this->reportData['attackTroopsTable']['troops'][$tid] = array( 'number' => $num, 'dead_number' => $deadNum );
                     }

                     $this->reportData['all_attackTroops_dead'] = $totalAttackTroops_live <= $totalAttackTroops_dead;
                     $this->reportData['defenseTroopsTable'] = array();
                     $troopsTableStrArr = (trim( $defenseTableTroopsStr ) == '' ? array() : explode( '#', $defenseTableTroopsStr ));
                     $j = 0 - 1;
                     foreach ($troopsTableStrArr as $defenseTableTroopsStr2) {
                        ++$j;
                        $defenseTroopsStrArr = explode( ',', $defenseTableTroopsStr2 );
                        $this->reportData['defenseTroopsTable'][$j] = array( 'troops' => array(), 'heros' => array( 'number' => 0, 'dead_number' => 0 ) );
                        foreach ($defenseTroopsStrArr as $s) {
                           list( $tid, $num, $deadNum ) = explode( ' ', $s );

                           if ($tid == 0 - 1) {
                              $this->reportData['defenseTroopsTable'][$j]['heros']['number'] = $num;
                              $this->reportData['defenseTroopsTable'][$j]['heros']['dead_number'] = $deadNum;
                              continue;
                           }

                           $this->reportData['defenseTroopsTable'][$j]['troops'][$tid] = array( 'number' => $num, 'dead_number' => $deadNum );
                        }
                     }


                     if ($captureResult != '') {
                        $wstr = '';

                        if ($captureResult == '+') {
                           $wstr = report_p_villagecaptured;
                        }
                        else {
                           $warr = explode( '-', $captureResult );
                           $wstr = report_p_allegiancelowered . ' ' . $warr[0] . ' ' . report_p_to . ' ' . $warr[1];
                        }


                        if ($wstr != '') {
                           $wstr = '<img src="assets/x.gif" class="unit u' . $kingTroopId . '" align="center" /> ' . $wstr;
                        }

                        $this->reportData['captureResult'] = $wstr;
                     }


                     if ($oasisResult != '') {
                        $wstr = '';

                        if ($oasisResult == '+') {
                           $wstr = report_p_oasiscaptured;
                        }
                        else {
                           $warr = explode( '-', $oasisResult );
                           $wstr = report_p_allegiancelowered . ' ' . $warr[0] . ' ' . report_p_to . ' ' . $warr[1];
                        }


                        if ($wstr != '') {
                           $wstr = '<img src="assets/x.gif" class="unit uhero" align="center" /> ' . $wstr;
                        }

                        $this->reportData['oasisResult'] = $wstr;
                     }


                     if ($wallDestructionResult != '') {
                        $wstr = '';

                        if ($wallDestructionResult == '-') {
                           $wstr = report_p_wallnotdestr;
                        }
                        else {
                           if ($wallDestructionResult == '+') {
                              $wstr = report_p_nowall;
                           }
                           else {
                              $warr = explode( '-', $wallDestructionResult );

                              if (intval( $warr[1] ) == 0) {
                                 $wstr = report_p_walldestr;
                              }
                              else {
                                 $wstr = report_p_walllowered . ' ' . $warr[0] . ' ' . report_p_to . ' ' . $warr[1];
                              }
                           }
                        }


                        if ($wstr != '') {
                           $wstr = '<img src="assets/x.gif" class="unit u' . $attackWallDestrTroopId . '" align="center" /> ' . $wstr;
                        }

                        $this->reportData['wallDestructionResult'] = $wstr;
                     }


                     if ($catapultResult != '') {
                        $bdestArr = array();

                        if ($catapultResult == '+') {
                           $bdestArr[] = '<img src="assets/x.gif" class="unit u' . $attackCatapultTroopId . '" align="center" /> ' . report_p_totallydestr;
                        }
                        else {
                           $catapultResultArr = explode( '#', $catapultResult );
                           foreach ($catapultResultArr as $catapultResultInfo) {
                              list( $itemId, $fromLevel, $toLevel ) = explode( ' ', $catapultResultInfo );

                              if ($toLevel == 0 - 1) {
                                 $bdestArr[] = '<img src="assets/x.gif" class="unit u' . $attackCatapultTroopId . '" align="center" /> ' . report_p_notdestr . ' ' . constant( 'item_' . $itemId );
                                 continue;
                              }


                              if ($toLevel == 0) {
                                 $bdestArr[] = '<img src="assets/x.gif" class="unit u' . $attackCatapultTroopId . '" align="center" /> ' . report_p_wasdestr . ' ' . constant( 'item_' . $itemId );
                                 continue;
                              }

                              $bdestArr[] = '<img src="assets/x.gif" class="unit u' . $attackCatapultTroopId . '" align="center" /> ' . report_p_waslowered . ' ' . constant( 'item_' . $itemId ) . ' ' . report_p_fromlevel . ' ' . $fromLevel . ' ' . report_p_to . ' ' . $toLevel;
                           }
                        }

                        $this->reportData['buildingDestructionResult'] = $bdestArr;
                     }

                     break;
                  }

               case 4: {
                     list( $attackTroopsStr, $defenseTableTroopsStr, $harvestResources, $harvestInfo, $spyType ) = explode( '|', $this->reportData['rpt_body'] );

                     if (( trim( $harvestResources ) != '' && $spyType == 1 )) {
                        $this->reportData['harvest_resources'] = explode( ' ', trim( $harvestResources ) );
                     }


                     if (( trim( $harvestInfo ) != '' && $spyType == 2 )) {
                        list( $itemId, $level ) = explode( ' ', $harvestInfo );
                        $this->reportData['harvest_info'] = constant( 'item_' . $itemId ) . ' ' . level_lang . ' ' . $level;
                     }

                     $this->reportData['all_spy_dead'] = FALSE;

                     if ($spyType == 3) {
                        $this->reportData['all_spy_dead'] = TRUE;
                        $this->reportData['harvest_info'] = report_p_allkilled;
                     }

                     $attackTroopsStrArr = explode( ',', $attackTroopsStr );
                     $this->reportData['attackTroopsTable'] = array( 'troops' => array(), 'heros' => array( 'number' => 0, 'dead_number' => 0 ) );
                     foreach ($attackTroopsStrArr as $s) {
                        list( $tid, $num, $deadNum ) = explode( ' ', $s );

                        if ($tid == 0 - 1) {
                           $this->reportData['attackTroopsTable']['heros']['number'] = $num;
                           $this->reportData['attackTroopsTable']['heros']['dead_number'] = $deadNum;
                           continue;
                        }

                        $this->reportData['attackTroopsTable']['troops'][$tid] = array( 'number' => $num, 'dead_number' => $deadNum );
                     }

                     $this->reportData['defenseTroopsTable'] = array();
                     $troopsTableStrArr = (trim( $defenseTableTroopsStr ) == '' ? array() : explode( '#', $defenseTableTroopsStr ));
                     $j = 0 - 1;
                     foreach ($troopsTableStrArr as $defenseTableTroopsStr2) {
                        ++$j;
                        $defenseTroopsStrArr = explode( ',', $defenseTableTroopsStr2 );
                        $this->reportData['defenseTroopsTable'][$j] = array( 'troops' => array(), 'heros' => array( 'number' => 0, 'dead_number' => 0 ) );
                        foreach ($defenseTroopsStrArr as $s) {
                           list( $tid, $num, $deadNum ) = explode( ' ', $s );

                           if ($tid == 0 - 1) {
                              $this->reportData['defenseTroopsTable'][$j]['heros']['number'] = $num;
                              $this->reportData['defenseTroopsTable'][$j]['heros']['dead_number'] = $deadNum;
                              continue;
                           }

                           $this->reportData['defenseTroopsTable'][$j]['troops'][$tid] = array( 'number' => $num, 'dead_number' => $deadNum );
                        }
                     }
                  }
               }

               $isDeleted = FALSE;

               if (!$isDeleted) {
                  $canOpenReport = TRUE;

                  if (( $this->player->playerId != $from_player_id && $this->player->playerId != $to_player_id )) {
                     $canOpenReport = ( 0 < intval( $this->data['alliance_id'] ) && ( $this->data['alliance_id'] == $m->getPlayerAllianceId( $to_player_id ) || $this->data['alliance_id'] == $m->getPlayerAllianceId( $from_player_id ) ) );
                  }


                  if ($canOpenReport) {
                     if (!$this->player->isSpy) {
                        if ($to_player_id == $this->player->playerId) {
                           if (( $readStatus == 0 || $readStatus == 2 )) {
                              $m->markReportAsReaded( $this->player->playerId, $to_player_id, $reportId, $readStatus );
                              --$this->data['new_report_count'];
                           }
                        }
                        else {
                           if ($from_player_id == $this->player->playerId) {
                              if (( $readStatus == 0 || $readStatus == 1 )) {
                                 $m->markReportAsReaded( $this->player->playerId, $to_player_id, $reportId, $readStatus );
                                 --$this->data['new_report_count'];
                              }
                           }
                        }
                     }
                  }
                  else {
                     $this->showList = TRUE;
                  }
               }
               else {
                  $this->showList = TRUE;
               }
            }
            else {
               $this->showList = TRUE;
            }

            $result->free();
         }
      }
      else {
         if (isset( $_POST['dr'] )) {
            if (isset( $_POST['dr'] )) {
               foreach ($_POST['dr'] as $reportId) {
                  if ($m->deleteReport( $this->player->playerId, $reportId )) {
                     --$this->data['new_report_count'];
                     continue;
                  }
               }
            }
         }
      }


      if ($this->showList) {
         $rowsCount = $m->getReportListCount( $this->player->playerId, $this->selectedTabIndex );
         $this->pageCount = (0 < $rowsCount ? ceil( $rowsCount / $this->pageSize ) : 1);
         $this->pageIndex = (( ( isset( $_GET['p'] ) && is_numeric( $_GET['p'] ) ) && intval( $_GET['p'] ) < $this->pageCount ) ? intval( $_GET['p'] ) : 0);
         $this->dataList = $m->getReportList( $this->player->playerId, $this->selectedTabIndex, $this->pageIndex, $this->pageSize );

         if (0 < $this->data['new_report_count']) {
            $this->data['new_report_count'] = $m->syncReports( $this->player->playerId );
         }
      }

      $m->dispose();
   }

   function getVillageName($playerId, $villageName) {
      return (0 < intval( $playerId ) ? $villageName : '<span class="none">[?]</span>');
   }

   function preRender() {
      parent::prerender();

      if (isset( $_GET['id'] )) {
         $this->villagesLinkPostfix .= '&id=' . intval( $_GET['id'] );
      }


      if (isset( $_GET['p'] )) {
         $this->villagesLinkPostfix .= '&p=' . intval( $_GET['p'] );
      }


      if (0 < $this->selectedTabIndex) {
         $this->villagesLinkPostfix .= '&t=' . $this->selectedTabIndex;
      }

   }

   function getNextLink() {
      $text = '»';

      if ($this->pageIndex + 1 == $this->pageCount) {
         return $text;
      }

      $link = '';

      if (0 < $this->selectedTabIndex) {
         $link .= 't=' . $this->selectedTabIndex;
      }


      if ($link != '') {
         $link .= '&';
      }

      $link .= 'p=' . ( $this->pageIndex + 1 );
      $link = 'report.php?' . $link;
      return '<a href="' . $link . '">' . $text . '</a>';
   }

   function getPreviousLink() {
      $text = '«';

      if ($this->pageIndex == 0) {
         return $text;
      }

      $link = '';

      if (0 < $this->selectedTabIndex) {
         $link .= 't=' . $this->selectedTabIndex;
      }


      if (1 < $this->pageIndex) {
         if ($link != '') {
            $link .= '&';
         }

         $link .= 'p=' . ( $this->pageIndex - 1 );
      }


      if ($link != '') {
         $link = '?' . $link;
      }

      $link = 'report.php' . $link;
      return '<a href="' . $link . '">' . $text . '</a>';
   }
}

$p = new GPage();
$p->run();
?>

