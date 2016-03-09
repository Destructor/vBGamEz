<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBGamEz 6.0 Beta 4
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2008-20011 vBGamEz Team. All Rights Reserved.            ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBGAMEZ IS NOT FREE SOFTWARE ------------------ # ||
|| # http://www.vbgamez.com                                           # ||
|| #################################################################### ||
\*======================================================================*/

if (!isset($GLOBALS['vbulletin']->db))
{
	exit;
}

/**
 * VBGamEz контроллер кэширования
 *
 * @package vBGamEz
 * @author GiveMeABreak aka Developer, LGSL author 
 * @version $Revision: 344 $
 * @copyright GiveMeABreak
 */

class vB_vBGamez_Datastore
{
         public static function vBG_Datastore_Cache_all($request, $userid = '', $serverid = '', $custom_query = '')
         {
                global $sqlsort, $sortorder, $perpage, $pos, $selectedgame, $selectedtype;
                global $vbulletin, $vbphrase, $db, $pagenumber;

				$server_list  = array();
				$exclude_list = array();
				// ####### START STICKED SERVERS
				if($_REQUEST['do'] == 'allservers' AND ((!$vbulletin->options['vbgamez_sticked_on_all_pages'] AND $pagenumber == 1) OR $vbulletin->options['vbgamez_sticked_on_all_pages']))
				{
					
					$db_query_stick = "SELECT * FROM " . TABLE_PREFIX . "vbgamez AS vbgamez WHERE disabled = 0 ";

					if($selectedtype)
					{
					            $db_query_stick .= " AND type = " . $db->sql_prepare($selectedtype);
					}

					if($selectedgame)
					{
					            $db_query_stick .= " AND (cache_game = " . $db->sql_prepare($selectedgame);
						
								if($prepare = vB_vBGamez::integrateGameType2($selectedtype, $selectedgame))
								{
									$db_query_stick .= ' OR cache_game = '.$db->sql_prepare($prepare[1]);
								}
								$db_query_stick .= ') ';

					}elseif($selectedtype)
					{
								$db_query_stick .= vB_vBGamez::getExcludeGameTypesQuery($selectedtype);
					}


					if($vbulletin->options['vbgamez_show_offline'])
					{
						$db_query_stick .= " AND status = 1 ";
					}
					
					$db_query_stick .= ' AND valid = 0 AND stick = 1 ';
					
					$db_query_stick .= " ORDER BY $sqlsort $sortorder";
					
					$fetch_servers = $vbulletin->db->query_read($db_query_stick);
		        	while ($serverinfo = $db->fetch_array($fetch_servers))
		        	{ 
		                    $server = vB_vBGamez::vBG_Datastore_Cache($serverinfo['ip'], $serverinfo['q_port'], $serverinfo['c_port'], $serverinfo['s_port'], $serverinfo['type'], "sep", $serverinfo);
		                    $server_list[] = $server;
							$exclude_list[] = $serverinfo['id'];
		         		}
						//if($vbulletin->options['vbgamez_sticked_on_all_pages'])
						//{
							//$vbulletin->vbgamez_decount = count($exclude_list);
						//}
			 	}
			
			    // ####### END STICKED SERVERS
			
				$db_query = "SELECT * FROM " . TABLE_PREFIX . "vbgamez AS vbgamez";
				
				$db_query .= '  WHERE disabled = 0 ';
				
                if($selectedtype)
                {
                            $db_query .= " AND type = " . $db->sql_prepare($selectedtype);
                }

                if($selectedgame)
                {
                            $db_query .= " AND (cache_game = " . $db->sql_prepare($selectedgame);

							if($prepare = vB_vBGamez::integrateGameType2($selectedtype, $selectedgame))
							{
								$db_query .= ' OR cache_game = '.$db->sql_prepare($prepare[1]);
							}
							$db_query .= ') ';
                }elseif($selectedtype)
				{
					$db_query .= vB_vBGamez::getExcludeGameTypesQuery($selectedtype);
				}

				if($vbulletin->options['vbgamez_show_offline'] AND !$userid)
				{
					$db_query .= " AND status = 1 ";
				}
				
				if(!empty($exclude_list))
				{
							$db_query .= " AND vbgamez.id NOT IN (" . implode(',', $exclude_list) . ") ";
				}

                if(empty($custom_query))
                {
                            if(!empty($userid))
                            {
                                           $db_query .= ' AND vbgamez.userid = ' . $vbulletin->userinfo['userid'] . '';
                            }else{
                                           $db_query .= ' AND valid = 0';
                            }

                            if(!empty($serverid))
                            {
                                           $db_query .= " AND vbgamez.id = '" . $serverid . "' AND vbgamez.userid = '" . $vbulletin->userinfo['userid'] . "'";
                            }else{
                                           $db_query .= " ORDER BY $sqlsort $sortorder LIMIT $pos, $perpage";
                            }

                }else{
                            $db_query .= $custom_query;
                }

				$fetch_servers = $vbulletin->db->query_read($db_query);

		        while ($serverinfo = $db->fetch_array($fetch_servers))
		        {
		                    $server = vB_vBGamez::vBG_Datastore_Cache($serverinfo['ip'], $serverinfo['q_port'], $serverinfo['c_port'], $serverinfo['s_port'], $serverinfo['type'], "sep", $serverinfo);
		                    $server_list[] = $server;
		         }
		
		  		if($vbulletin->options['vbgamez_not_use_all_list'] AND count($server_list) == 1 AND $_REQUEST['do'] == 'allservers' AND (empty($_REQUEST['page']) OR $_REQUEST['page'] == 0 OR $_REQUEST['page'] == 1) AND empty($_REQUEST['pp']))
		  		{
			     	exec_header_redirect($vbulletin->options['vbgamez_path'] . $vbulletin->session->vars['sessionurl'] . '?do=view&amp;id='.$server_list[0]['o']['id']);
 		  		}

                  return $server_list;
         }


	/*Статистика ========================================================================*/

	/**
	 * Возвращение статистики о всех серверах
	 *
	 */

         public static function vbgamez_cached_totals($userid = false)
         {
                  global $vbulletin, $vbphrase, $db, $selectedtype, $selectedgame;

                  $db_query = "SELECT cache, id, cache_name FROM " . TABLE_PREFIX . "vbgamez AS vbgamez WHERE disabled = 0 ";

                  if(!empty($userid))
                  {
                             $db_query .= ' AND userid = ' . $vbulletin->userinfo['userid'] . '';
                  }else{
                             $db_query .= ' AND valid = 0';
                  }

                  if($selectedtype)
                  {
                             $db_query .= " AND type = " . $db->sql_prepare($selectedtype);
                  }

                  if($selectedgame)
                  {
                            $db_query .= " AND (cache_game = " . $db->sql_prepare($selectedgame);

							if($prepare = vB_vBGamez::integrateGameType2($selectedtype, $selectedgame))
							{
								$db_query .= ' OR cache_game = '.$db->sql_prepare($prepare[1]);
							}
							$db_query .= ') ';
                  }elseif($selectedtype)
				  {
							 $db_query .= vB_vBGamez::getExcludeGameTypesQuery($selectedtype);
				  }
				  if($vbulletin->options['vbgamez_show_offline'] AND !$userid)
				  {
					      $db_query .= " AND status = 1 ";
				  }
				
                  $fetch_cache = $vbulletin->db->query_read($db_query);

                  $total['players']         = 0;
                  $total['playersmax']      = 0;
                  $total['servers']         = 0;
                  $total['servers_online']  = 0;
                  $total['servers_offline'] = 0;

                  while ($mysql_row = $db->fetch_array($fetch_cache))
                  {
                              $server = unserialize($mysql_row['cache']);
                              $total['jump_servers'][$mysql_row['id']]['id'] = $mysql_row['id'];
                              $total['jump_servers'][$mysql_row['id']]['cache_name'] = $mysql_row['cache_name'];

                              $total['players']    += $server['s']['players'];
                              $total['playersmax'] += $server['s']['playersmax'];
							  if($server['s']['playersmax'] > $server['s']['players'])
							  {
                              		$total['freeslots'] += $server['s']['playersmax'] - $server['s']['players'];
							  }
                              $total['servers']++;

                              if ($server['b']['status'])
                              {
                                            $total['servers_online']++;
                              }else{
                                            $total['servers_offline']++;
                              }
                  }
				  if($vbulletin->vbgamez_decount)
				  {
				  		$total['servers'] = $total['servers'] - $vbulletin->vbgamez_decount;
				  }
                  return $total;
         }


	/*Боковая панель и CMS ========================================================================*/

	/**
	 * Возвращяет кэшированную информацую для vBCMS и боковой панели.
	 *
	 */

 public static function VBG_Sidebar($request, $sqlsort, $sortorder, $limit = '', $ids = '', $sticked = false)
 {
         global $vbulletin, $vbphrase, $db;

         $server_list  = array();


		// ####### START STICKED SERVERS
		if($sticked)
		{
			$db_query_stick = "SELECT * FROM " . TABLE_PREFIX . "vbgamez AS vbgamez WHERE disabled = 0 AND valid = 0 AND stick = 1";

		  if($vbulletin->options['vbgamez_show_offline'])
		  {
			      $db_query_stick .= " AND status = 1 ";
		  }
		
		 if($ids)
		 {
			$db_query_stick .= ' AND id IN('.$ids.') ';
		 }

		 if($sqlsort)
		 {
			$db_query_stick .= " ORDER BY $sqlsort $sortorder ";
		 }

			$fetch_servers = $vbulletin->db->query_read($db_query_stick);
        	while ($serverinfo = $db->fetch_array($fetch_servers))
        	{ 
                         $server = vB_vBGamez::vBG_Datastore_Cache($serverinfo['ip'], $serverinfo['q_port'], $serverinfo['c_port'], $serverinfo['s_port'], $serverinfo['type'], 'sep', $serverinfo);
                         $server_list[] = $server;
						$exclude_list[] = $serverinfo['id'];

			}
		 }
				// ####### END STICKED SERVERS
				
			 $query = "SELECT * FROM " . TABLE_PREFIX . "vbgamez AS vbgamez WHERE disabled = 0 AND valid = 0";
			
		  	if($vbulletin->options['vbgamez_show_offline'])
		  	{
			      $db_query .= " AND status = 1 ";
		  	}
			 if($ids)
			 {
				$query .= ' AND id IN('.$ids.') ';
			 }

			if(!empty($exclude_list))
			{
						$query .= " AND id NOT IN (" . implode(',', $exclude_list) . ") ";
			}
			
			 if($sqlsort)
			 {
				$query .= " ORDER BY $sqlsort $sortorder ";
			 }

			 if($limit)
			 {
				$query .= ' LIMIT '.$limit . ' ';
			 }
		

		$fetch_servers = $vbulletin->db->query_read($query);
             
         while ($serverinfo = $db->fetch_array($fetch_servers))
         { 
                     $server = vB_vBGamez::vBG_Datastore_Cache($serverinfo['ip'], $serverinfo['q_port'], $serverinfo['c_port'], $serverinfo['s_port'], $serverinfo['type'], "sep", $serverinfo);
                     $server_list[] = $server;
          }

          return $server_list;
 }


	/*Кэширование NEW========================================================================*/

	/**
	 * Возвращение кешированной информации о сервере NERW
	 *
	 */

        public static function vBG_Datastore_Cache($ip, $q_port, $c_port, $s_port, $type, $request, $cachedinfo = NULL)
        {
            global $vbulletin, $vbphrase, $db, $stylevar;

            // Set int values
            $q_port  = intval($q_port); $c_port = intval($c_port); $s_port  = intval($s_port);

            $cache = unserialize($cachedinfo['cache']);

            $cache_date = explode("_", $cachedinfo['cache_time']);
            $cache_date[0] = intval($cache_date[0]);
            $cache_date[1] = intval($cache_date[1]);
            $cache_date[2] = intval($cache_date[2]);

            if (!isset($cache['b']))
            {
                          $cache      = array();
                          $cache['b'] = array();
                          $cache['b']['status']  = 0;
                          $cache['b']['pending'] = 1;
            }

            if($vbulletin->options['vbgamez_host_to_ip'])
            {
                          $ip = @gethostbyname($ip);
            }

             $cache['b']['type'] = $type;
             $cache['b']['ip'] = $ip;
             $cache['b']['c_port'] = $c_port;
             $cache['b']['q_port'] = $q_port;
             $cache['b']['s_port'] = $s_port;
             $cache['o']['request'] = $request;
             $cache['o']['id'] = $cachedinfo['id'];

             if (!isset($cache['s']))
             {
                           $cache['s'] = array();
                           $cache['s']['game'] = $type;
                           $cache['s']['name'] = vB_vBGamez::vbg_set_charset($vbphrase['vbgamez_server_no_response']);
                           $cache['s']['map'] = '---';
                           $cache['s']['players'] = 0;
                           $cache['s']['playersmax'] = 0;
                           $cache['s']['password'] = 0;
			   $cache['i']['views'] = $cachedinfo['views'];
			   $cache['i']['rating'] = $cachedinfo['rating'];
			   $cache['i']['comments'] = $cachedinfo['comments'];
			   $cache['i']['valid'] = $cachedinfo['valid'];
			   $cache['i']['location'] = $cachedinfo['location'];
			   $cache['i']['city'] = $cachedinfo['city'];
			   $cache['i']['country'] = $cachedinfo['country'];
			   $cache['i']['nonsteam'] = $cachedinfo['nonsteam'];
			   $cache['i']['steam'] = $cachedinfo['steam'];
			   $cache['i']['pirated'] = $cachedinfo['pirated'];

			   $name_set_default = true;
             }
    
             if (!isset($cache['e']))
             {
                           $cache['e'] = array(); 
             }

             if (!isset($cache['p']))
             {
                           $cache['p'] = array();
             }

             $needed = '';

             if (strpos($request, "c") === false)
             {
                           if (strpos($request, "s") !== false AND TIMENOW > ($cache_date[0] + $vbulletin->options['vbgamez_cache'] * 60))
                           {
                                         $needed .= "s";
                           }

                           if (strpos($request, "e") !== false AND TIMENOW > ($cache_date[1] + $vbulletin->options['vbgamez_cache'] * 60))
                           {
                                         $needed .= "e";
                           }

                           if (strpos($request, "p") !== false AND TIMENOW > ($cache_date[2] + $vbulletin->options['vbgamez_cache'] * 60))
                           {
                                         $needed .= "p";
                           }
             }

             if(empty($cache['s']['map']))
             {
                            $cache['s']['map'] = '---';
             }

             if(empty($cache['s']['name']) AND !$name_setup)
             {
                            $cache['s']['name'] = vB_vBGamez::vbg_set_charset($vbphrase['vbgamez_server_no_response']);
             }

             if ($needed AND (THIS_SCRIPT == 'cron' OR VB_AREA == 'AdminCP' OR DIRECT_UPDATE_SERVER_CACHE == 1))
             {
                           $serverid = $cachedinfo['id'];

                           $cache_last_date = TIMENOW + ($vbulletin->options['vbgamez_cache'] * 60 + 10);

                           $packed_times = "{$packed_times}_{$packed_times}_{$packed_times}";

                           $live = vbgamez_query_live($type, $ip, $c_port, $q_port, $s_port, $needed, $cachedinfo['dbinfo']);

                           if (!$live['b']['status'] AND $vbulletin->options['vbgamez_retry_offline'] AND !$vbulletin->options['vbgamez_feed'])
                           {
                                         $live = vbgamez_query_live($type, $ip, $c_port, $q_port, $s_port, $needed, $cachedinfo['dbinfo']);
                           }
          
                           $live = vB_vBGamez::vbgamez_charset_convert($live, vB_vBGamez::vbgamez_charset_detect($live));

                           if (!$live['b']['status'])
                           {
                                         $live['s']['game']       = $cache['s']['game'];
                                         $live['s']['name']       = $cache['s']['name'];
                                         $live['s']['map']        = $cache['s']['map'];
                                         $live['s']['password']   = $cache['s']['password'];
                                         $live['s']['players']    = 0;
                                         $live['s']['playersmax'] = $cache['s']['playersmax'];
                                         $live['e']               = array();
                                         $live['p']               = array();
                           }

                           if (isset($live['b']))
                           {
                                         $cache['b'] = $live['b'];
                           }

                           if (isset($live['s']))
                           {
                                         $cache['s'] = $live['s'];
                                         $cache_date[0] = TIMENOW;
                           }

                           if (isset($live['e']))
                           {
                                         $cache['e'] = $live['e'];
                                         $cache_date[1] = TIMENOW;
                           }

                           if (isset($live['p']))
                           {
                                         $cache['p'] = $live['p'];
                                         $cache_date[2] = TIMENOW;
                           }

                           if(!$cache['b']['status'])
                           {
                                    if(!empty($cachedinfo['offline_lastcheck']))
                                    {
                                               $uptime_time = TIMENOW - $cachedinfo['offline_lastcheck'];
                                               $cachedinfo['offline_lastcheck'] = intval($uptime_time / 60);

                                               $update_uptime = ", offline_time = offline_time + ".$cachedinfo['offline_lastcheck'].", offline_lastcheck = " . TIMENOW;
                                    }else{
                                               $update_uptime = ", offline_lastcheck = " . TIMENOW;
                                    }
                           }else{
                                    $update_uptime = ', offline_time = 0, offline_lastcheck = 0';
                           }
	
                           $cache['i'] = array('valid' => $cachedinfo['valid'], 'views' => $cachedinfo['views'], 'rating' => $cachedinfo['rating'], 'comments' => $cachedinfo['comments'], 'location' => $cachedinfo['location'], 'city' => $cachedinfo['city'], 'country' => $cachedinfo['country'], 'steam' => $cachedinfo['steam'], 'nonsteam' => $cachedinfo['nonsteam'], 'pirated' => $cachedinfo['pirated'], 'stick' => $cachedinfo['stick'], 'expirydate' => $cachedinfo['expirydate']);

                           $packed_cache = serialize($cache);
                           $packed_times = implode("_", $cache_date);

			   if(vbstrlen(trim($cache['s']['name'])) == 0)
			   {
						if($cachedinfo['cache_name'])
						{
				        	$update_cache_name = $cachedinfo['cache_name'];
						}else{
							$update_cache_name = $vbphrase['vbgamez_server_no_response'];
						}
			   }else{
					$update_cache_name = $cache['s']['name'];
			   }

                           $vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "vbgamez SET
                                                                status = ".$vbulletin->db->sql_prepare($cache['b']['status']).",
                                                                cache = ".$vbulletin->db->sql_prepare($packed_cache).",
                                                                cache_time = ".$vbulletin->db->sql_prepare($packed_times).",
                                                                cache_playersmax = ".$vbulletin->db->sql_prepare($cache['s']['playersmax']).",
                                                                cache_players = ".$vbulletin->db->sql_prepare($cache['s']['players']).",
                                                                cache_map = ".$vbulletin->db->sql_prepare($cache['s']['map']).",
                                                                cache_game = ".$vbulletin->db->sql_prepare($cache['s']['game']).",
                                                                cache_name = ".$vbulletin->db->sql_prepare($update_cache_name)."
                                                                $update_uptime
                                                                WHERE id = $serverid");

             }

             if (strpos($request, 's') === FALSE)
             {
                             unset($cache['s']);
             }

             if (strpos($request, 'e') === FALSE)
             {
                              unset($cache['e']);
             }

             if (strpos($request, 'p') === FALSE)
             {
                              unset($cache['p']);
             }

             return $cache;

          }



	/*Очистка кэша========================================================================*/

	/**
	 * Очистка кэша
	 *
	 */

        public static function vBG_Datastore_Clear_Cache($serverid = '', $mode = 'all')
        {
               global $vbulletin, $vbphrase;

	 	if(!$serverid)
		{
              		 $vbulletin->db->query("UPDATE " . TABLE_PREFIX . "vbgamez SET cache = '', cache_time = '', cache_name = " . $vbulletin->db->sql_prepare(vB_vBGamez::vbg_set_charset($vbphrase['vbgamez_server_unknown_name'])) . ", cache_players = '', cache_playersmax = '', cache_map = '', cache_game = ''");
		}

	        if($serverid AND $mode)
	        {
			$serverinfo = vB_vBGamez::vbgamez_verify_id($serverid);
			if($serverinfo)
			{
				if($mode == 'all')
				{
					$vbulletin->db->query("UPDATE " . TABLE_PREFIX . "vbgamez SET cache = '', cache_time = '', cache_name = '', cache_players = '', cache_playersmax = '', cache_map = '', cache_game = '' WHERE id = '" . intval($serverid) . "'");
					$serverinfo['cache'] = $serverinfo['cache_time'] = $serverinfo['cache_players'] = $serverinfo['cache_playersmax'] = $serverinfo['cache_map'] = $serverinfo['cache_game'] = '';
					$serverinfo['cache_name'] = $vbphrase['vbgamez_server_unknown_name'];
					define('DIRECT_UPDATE_SERVER_CACHE', 1);
					vB_vBGamez::vBG_Datastore_Cache($serverinfo['ip'], $serverinfo['q_port'], $serverinfo['c_port'], $serverinfo['c_port'], $serverinfo['type'], 's', $serverinfo);
				}else{
					if($mode == 'rating')
					{
						$newservercache = unserialize($serverinfo['cache']);
						foreach(array('rating', 'stick', 'expirydate') AS $Type)
						{
							$newservercache['i'][$Type] = $serverinfo[$Type];
							
				    	}
				
						$newCache = serialize($newservercache);
						$vbulletin->db->query("UPDATE " . TABLE_PREFIX . "vbgamez SET cache = '" . $vbulletin->db->escape_string($newCache) . "' WHERE id = '" . intval($serverid) . "'");
						
					}else{
						$newservercache = unserialize($serverinfo['cache']);

						$newservercache['i'][$mode] = $serverinfo[$mode];

						$newCache = serialize($newservercache);
						$vbulletin->db->query("UPDATE " . TABLE_PREFIX . "vbgamez SET cache = '" . $vbulletin->db->escape_string($newCache) . "' WHERE id = '" . intval($serverid) . "'");
					}
				}
			}
		}

               return true;
        }

}
?>
