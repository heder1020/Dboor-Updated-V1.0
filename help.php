<?php






	require( '.' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'boot.php' );
	class GPage extends PopupPage {
		var $state = 0;
		var $id = null;
		var $tribeId = null;
		var $buildingGroup = null;
		var $build = null;
		var $troopId = null;
		var $troop = null;
		var $plusIndex = null;
		var $nextLink = null;
		var $previousLink = null;

		function GPage() {
			parent::popuppage();
			$this->viewFile = 'help.phtml';
		}

		function load() {
			parent::load();
			$this->nextLink = '';
			$this->previousLink = '';
			$this->state = (( ( ( isset( $_GET['c'] ) && is_numeric( $_GET['c'] ) ) && 0 <= intval( $_GET['c'] ) ) && intval( $_GET['c'] ) <= 7 ) ? intval( $_GET['c'] ) : 0);
			$id = (( isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) ? $_GET['id'] : 0);
			switch ($this->state) {
				case 1: {
					if (( ( ( ( $id != 1 && $id != 2 ) && $id != 3 ) && $id != 6 ) && $id != 7 )) {
						$this->state = 0;
					} 
else {
						$this->tribeId = $id;

						if ($id == 1) {
							$next = 2;
							$prev = 7;
						} 
else {
							if ($id == 2) {
								$next = 3;
								$prev = 1;
							} 
else {
								if ($id == 3) {
									$next = 6;
									$prev = 2;
								} 
else {
									if ($id == 6) {
										$next = 7;
										$prev = 3;
									} 
else {
										if ($id == 7) {
											$next = 1;
											$prev = 6;
										}
									}
								}
							}
						}

						$this->nextLink = '?c=1&id=' . $next;
						$this->previousLink = '?c=1&id=' . $prev;
					}

					break;
				}

				case 2: {
					if (( $id <= 0 || 4 < $id )) {
						$this->state = 0;
					} 
else {
						$this->buildingGroup = $id;

						if ($id == 1) {
							$next = 2;
							$prev = 3;
						} 
else {
							if ($id == 2) {
								$next = 3;
								$prev = 1;
							} 
else {
								if ($id == 3) {
									$next = 1;
									$prev = 2;
								}
							}
						}

						$this->nextLink = '?c=2&id=' . $next;
						$this->previousLink = '?c=2&id=' . $prev;
					}

					break;
				}

				case 3: {
					if (!isset( $this->gameMetadata['troops'][$id] )) {
						$this->state = 0;
					} 
else {
						$this->troopId = $id;
						$this->troop = $this->gameMetadata['troops'][$id];

						if ($id == 1) {
							$next = 2;
							$prev = 109;
						} 
else {
							if ($id == 30) {
								$next = 51;
								$prev = 29;
							} 
else {
								if ($id == 51) {
									$next = 52;
									$prev = 30;
								} 
else {
									if ($id == 60) {
										$next = 100;
										$prev = 59;
									} 
else {
										if ($id == 100) {
											$next = 101;
											$prev = 60;
										} 
else {
											if ($id == 109) {
												$next = 1;
												$prev = 108;
											} 
else {
												$next = $id + 1;
												$prev = $id - 1;
											}
										}
									}
								}
							}
						}

						$this->nextLink = '?c=3&id=' . $next;
						$this->previousLink = '?c=3&id=' . $prev;
					}

					break;
				}

				case 4: {
					if (!isset( $this->gameMetadata['items'][$id] )) {
						$this->state = 0;
					} 
else {
						$this->itemId = $id;
						$this->build = $this->gameMetadata['items'][$id];

						if ($id == 1) {
							$next = 2;
							$prev = 40;
						} 
else {
							if ($id == 14) {
								$next = 16;
								$prev = 13;
							} 
else {
								if ($id == 16) {
									$next = 19;
									$prev = 14;
								} 
else {
									if ($id == 19) {
										$next = 20;
										$prev = 16;
									} 
else {
										if ($id == 22) {
											$next = 29;
											$prev = 21;
										} 
else {
											if ($id == 29) {
												$next = 30;
												$prev = 22;
											} 
else {
												if ($id == 30) {
													$next = 36;
													$prev = 29;
												} 
else {
													if ($id == 36) {
														$next = 37;
														$prev = 30;
													} 
else {
														if ($id == 37) {
															$next = 42;
															$prev = 36;
														} 
else {
															if ($id == 42) {
																$next = 15;
																$prev = 37;
															} 
else {
																if ($id == 15) {
																	$next = 17;
																	$prev = 42;
																} 
else {
																	if ($id == 17) {
																		$next = 18;
																		$prev = 15;
																	} 
else {
																		if ($id == 18) {
																			$next = 23;
																			$prev = 17;
																		} 
else {
																			if ($id == 23) {
																				$next = 24;
																				$prev = 18;
																			} 
else {
																				if ($id == 26) {
																					$next = 28;
																					$prev = 25;
																				} 
else {
																					if ($id == 28) {
																						$next = 34;
																						$prev = 26;
																					} 
else {
																						if ($id == 34) {
																							$next = 35;
																							$prev = 28;
																						} 
else {
																							if ($id == 35) {
																								$next = 38;
																								$prev = 34;
																							} 
else {
																								if ($id == 38) {
																									$next = 39;
																									$prev = 35;
																								} 
else {
																									if ($id == 39) {
																										$next = 41;
																										$prev = 38;
																									} 
else {
																										if ($id == 41) {
																											$next = 40;
																											$prev = 39;
																										} 
else {
																											if ($id == 40) {
																												$next = 1;
																												$prev = 41;
																											} 
else {
																												$next = $id + 1;
																												$prev = $id - 1;
																											}
																										}
																									}
																								}
																							}
																						}
																					}
																				}
																			}
																		}
																	}
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}

						$this->nextLink = '?c=4&id=' . $next;
						$this->previousLink = '?c=4&id=' . $prev;
					}

					break;
				}

				case 5: {
					$this->plusIndex = $id;
					break;
				}

				case 6: {
				}

				case 7: {
					$this->id = $id;
				}
			}

		}
	}

	
	$p = new GPage();
	$p->run();
?>



