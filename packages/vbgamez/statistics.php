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
// vBGamez Stats Data Builder
class vBGamez_Stats_Builder
{
           static $registry;
           static $server;

           public static function instance($vbulletin, $server)
           {
                  return new vBGamez_Stats_Builder($vbulletin, $server);
           }

           function vBGamez_Stats_Builder($vbulletin, $server)
           {
                  $this->server =& $server;
                  $this->registry =& $vbulletin;
           }

           function buildNow()
           {
                  if(($this->registry->options['vbgamez_fast_fetch_graphics'] AND THIS_SCRIPT == 'vbgamez') OR THIS_SCRIPT == 'cron' OR (VB_AREA == 'AdminCP' AND $_REQUEST['do'] == 'runcron')) 
                  {
                                  $stats = vBGamez_Stats_Builder::fetch_Stats($this->server['o']['id']);

                  }else{
                                  $stats = unserialize($this->server['i']['statistics']);
                  }

                  if(!empty($stats))
                  {
                                  $need_update = true; 
                  }

                  if(empty($stats) AND $this->registry->options['vbgamez_fast_fetch_graphics'])
                  {
                                  vBGamez_Stats_Builder::insert_Stats($this->server);
                                  $need_update = true;
                                  $stats[$this->registry->db->insert_id()] = array('players' => $this->server['s']['players'], 'dateline' => TIMENOW, 'serverid' => $this->server['o']['id']);
                  }

                  if($need_update)
                  {
                                 vBGamez_Stats_Builder::updateServerStats($stats, $this->server['o']['id']);
                  }

                  return serialize($stats);
          }

          function updateServerStats($stats, $serverid)
          {
                       	$this->registry->db->query_write("UPDATE " . TABLE_PREFIX . "vbgamez SET statistics = '" . $this->registry->db->escape_string(serialize($stats)) . "' WHERE id = " . intval($serverid) . ""); 
						   $updated = 1;
					   
          }


          function fetch_Stats($serverid)
          {
                       $fetch_stats = $this->registry->db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez_statistics WHERE serverid = " . intval($serverid) . "");
                       while($gmserver = $this->registry->db->fetch_array($fetch_stats))
                       {
                                $stats[] = $gmserver;
                       }

                       return $stats;
          }

          function insert_Stats($server)
          {
                       $this->registry->db->query_write("INSERT INTO " . TABLE_PREFIX . "vbgamez_statistics (serverid, players, dateline) VALUES (" . intval($server['o']['id']) . ", " . intval($server['s']['players']) . ", " . TIMENOW . ")");
          }
}

// vBGamez Stats Displayer
class vBGamez_Stats_Display
{
           static $registry;
           static $server;
	   static $start_at;
	   const MONTH_STATS = 2;
	   const MONTH_6_STATS = 3;
	   const YEAR_STATS = 4;
	   const HOURS_24_STATS = 1;

           // create renderning function
           public static function create($registry, $server)
           {
                    return new vBGamez_Stats_Display($registry, $server);
           }

           // constructor 
           function vBGamez_Stats_Display($registry, $server)
           {
                   $this->registry =& $registry;
                   $this->server =& $server;
           }

           // render template
           function render($graphicId)
           {
                  global $vbphrase, $show, $vbulletin;

		  $popular_maps = trim(vBGamez_Stats_MapHistory::instance($this->server)->renderPopularMaps());
		  $history_maps = trim(vBGamez_Stats_MapHistory::instance($this->server)->renderHistoryMaps());
		  if(!empty($popular_maps))
		  {
			   $vbulletin->vbg_haspopularmaps = true;
		  }
		  if(!empty($history_maps))
		  {
			   $vbulletin->vbg_hashistory = true;
		  }
		  if(!empty($popular_maps) AND !empty($history_maps))
		  {
			$show['vbg_table_full'] = true;
		  }

                  if(vB_vBGamez::is_vb4())
                  {
                        $html = vB_Template::create('vbgamez_graphics');
                        $html->register('server', $this->server);
			$html->register('graphicId', $graphicId);
			$html->register('popular_maps', $popular_maps);
			$html->register('history_maps', $history_maps);
                        return $html->render();
                  }else{
                       $server =& $this->server;

                       eval('$html .= "' . fetch_template('vbgamez_graphics') . '";');  
                       return $html;
                  }
           }

		function buildMonthsIds()
		{
			$month = self::prepareHour(date('m', time()));
			if($month == 1)
			{
				return  array(9,10,11,12,1,2);
			}elseif($month == 2)
			{
				return  array(10,11,12,1,2,3);
			}elseif($month == 3)
			{
				return  array(11,12,1,2,3,4);
			}elseif($month == 4)
			{
				return  array(12,1,2,3,4,5);
			}elseif($month == 5)
			{
				return  array(1,2,3,4,5,6);
			}elseif($month == 6)
			{
				return  array(2,3,4,5,6,7);
			}elseif($month == 7)
			{
				return  array(3,4,5,6,7,8);
			}elseif($month == 8)
			{
				return  array(4,5,6,7,8,9);
			}elseif($month == 9)
			{
				return array(5,6,7,8,9,10);
			}elseif($month == 10)
			{
				return  array(6,7,8,9,10,11);
			}elseif($month == 11)
			{
				return  array(7,8,9,10,11,12);
			}elseif($month == 12)
			{
				return  array(8,9,10,11,12,1);
			}
		}
		
		function isContinueDay($true = false)
		{
			if(!$true)
			{
				return array(2,4,6,8,10,12,14,16,18,20,22,24,26,28,30);
			}else{
				return array(2,4,6,7,8,10,12,13,14,16,17,18,19,20,22,24,26,28,29,30);
			}
		}
           // render graphics data
           function renderData($Type)
           {
                 $server =& $this->server;

                 if(empty($server['i']['statistics']))
                 {
                      $server['i']['statistics'] = vBGamez_Stats_Builder::instance($this->registry, $server)->buildNow();
                 }

				  if(is_array($stats))
				  {
                 		$stats = $server['i']['statistics'];
				  }else{
						$stats = unserialize($server['i']['statistics']);
				}

		  $current_year = date('Y', TIMENOW);
		  $current_month = date('m', TIMENOW);
		  $current_day = date('d', TIMENOW);

                  if($Type == self::MONTH_STATS AND vB_vBGamez::vb_call()->options['vbgamez_enabled_graphics_bitfield'] & 1)
		  {
			if(is_array($stats))
			{
				foreach($stats AS $statid => $statdata)
				{
					if(date('m', $statdata['dateline']) == $current_month AND date('Y', $statdata['dateline']) == $current_year)
					{
						$day = $this->prepareHour(date('d', $statdata['dateline']));
						if($stat_precache[$day] >= $statdata['players'])
						{
							//nothing
						}else{
							$stat_precache[$day] = $statdata['players'];
						}
					}
				}
			}

		 	$day = 31; // 31 days in month
			for($i=1; $i < $day + 1; $i++)
			{
				$counter++;
				$statistic['data'][$counter] = intval($stat_precache[$i]);
			}

		  }elseif($Type == self::MONTH_6_STATS AND vB_vBGamez::vb_call()->options['vbgamez_enabled_graphics_bitfield'] & 2)
		  {
			$monthsNumbers = self::buildMonthsIds();
			if(is_array($stats))
			{
				foreach($stats AS $statid => $statdata)
				{
					if(date('Y', $statdata['dateline']) == $current_year AND in_array(self::prepareHour(date('m', $statdata['dateline'])), $monthsNumbers))
					{
						$day = $this->prepareHour(date('d', $statdata['dateline']));
						$month = $this->prepareHour(date('m', $statdata['dateline']));
						if($stat_precache[$day][$month] >= $statdata['players'])
						{
							//nothing
						}else{
							$stat_precache[$day][$month] = $statdata['players'];
						}
					}
				}
			}

			foreach($monthsNumbers AS $monthnum)
			{
				for($i=1; $i < 32; $i++)
				{
					if(in_array($i, self::isContinueDay()))
					{
						continue;
					}
					$counter++;
					if(!empty($stat_precache[$i][$monthnum]))
					{
							$statistic['data'][$counter] = intval($stat_precache[$i][$monthnum]);
					}else{
						$statistic['data'][$counter] = 0;
					}
				}
			}

		  }elseif($Type == self::YEAR_STATS AND vB_vBGamez::vb_call()->options['vbgamez_enabled_graphics_bitfield'] & 4)
		  {
			$monthsNumbers = array(1,2,3,4,5,6,7,8,9,10,11,12);
			if(is_array($stats))
			{
				foreach($stats AS $statid => $statdata)
				{
					if(date('Y', $statdata['dateline']) == $current_year AND in_array(self::prepareHour(date('m', $statdata['dateline'])), $monthsNumbers))
					{
						$day = $this->prepareHour(date('d', $statdata['dateline']));
						$month = $this->prepareHour(date('m', $statdata['dateline']));
						if($stat_precache[$day][$month] >= $statdata['players'])
						{
							//nothing
						}else{
							$stat_precache[$day][$month] = $statdata['players'];
						}
					}
				}
			}

			foreach($monthsNumbers AS $monthnum)
			{
				for($i=1; $i < 32; $i++)
				{
					if(in_array($i, self::isContinueDay(true)))
					{
						continue;
					}
					$counter++;
					if(!empty($stat_precache[$i][$monthnum]))
					{
							$statistic['data'][$counter] = intval($stat_precache[$i][$monthnum]);
					}else{
						$statistic['data'][$counter] = 0;
					}
				}
			}
			
			
		  }elseif($Type == self::HOURS_24_STATS AND vB_vBGamez::vb_call()->options['vbgamez_enabled_graphics_bitfield'] & 8)
		  {
			if(is_array($stats))
			{
				foreach($stats AS $statid => $statdata)
				{
					if(date('m', $statdata['dateline']) == $current_month AND date('Y', $statdata['dateline']) == $current_year AND date('d', $statdata['dateline']) == $current_day)
					{
						$hour = $this->prepareHour(date('H', $statdata['dateline']));

						if($stat_precache[$hour] >= $statdata['players'])
						{
							//nothing
						}else{
							$stat_precache[$hour] = $statdata['players'];
						}
					}
				}
			}
			$hours = 25; // hours count
			for($i=1; $i < $hours + 1; $i++)
			{
				$counter++;
				$statistic['data'][$i] = intval($stat_precache[$i]);
			}
		  }else{
			return 'unknown';
		  }

              return $statistic;
           }
	
	   function prepareHour($hour)
	   {
                  $search = array('00', '01', '02', '03', '04', '05', '06', '07', '08', '09');
                  $replace = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		  return str_replace($search, $replace, $hour);
	   }

	   function getDisplayPhrase($Type)
	   {
		  global $vbphrase;

                  if($Type == self::MONTH_STATS)
		  {
		 	$val = 'graphic_1_month';
		  }elseif($Type == self::MONTH_6_STATS)
		  {
		 	$val = 'graphic_6_month';
		  }elseif($Type == self::YEAR_STATS)
		  {
		 	$val = 'graphic_1_year';
		  }elseif($Type == self::HOURS_24_STATS)
		  {
		 	$val = 'graphic_24_hours';
		  }else{
			return false;
		  }

		  return $vbphrase['vbgamez_' . $val];
          }

	  function getXphrase($Type)
	  {
                  global $vbphrase;

                  if($Type == self::MONTH_STATS)
		  {
		 	$val = 'day_title';
		  }elseif($Type == self::MONTH_6_STATS)
		  {
		 	$val = 'month_title';
		  }elseif($Type == self::YEAR_STATS)
		  {
		 	$val = 'month_title';
		  }elseif($Type == self::HOURS_24_STATS)
		  {
		 	$val = 'hours';
		  }else{
			return false;
		  }

		  return $vbphrase['vbgamez_' . $val];
          }

	  function getYphrase($Type)
	  {
                  global $vbphrase;
		  return $vbphrase['vbgamez_players'];
          }
	  function getDescphrase($Type)
	  {
                  global $vbphrase;
		  return $vbphrase['vbgamez_players'];
          }

 	  function getDefaultGraphicId()
	  {
		  $select_graphicid = vB_vBGamez::vb_call()->options['vbgamez_default_graphic_id'];
		  $enabled_bitfields = vB_vBGamez::vb_call()->options['vbgamez_enabled_graphics_bitfield'];

		  if($select_graphicid == 2 AND $enabled_bitfields & 1)
		  {
			return 2;
		  }elseif($select_graphicid == 3 AND $enabled_bitfields & 2)
		  {
			return 3;
		  }elseif($select_graphicid == 4 AND $enabled_bitfields & 4)
		  {
			return 4;
		  }elseif($select_graphicid == 1 AND $enabled_bitfields & 8)
	 	  {
			return 1;
		  }else{
			return false;
		  }
	  }
 	  
	  function verifyGraphicId($graphicId)
	  {
		  if(!in_array($graphicId, array(1,2,3,4)))
		  {
			return false;
		  }else{
			return true;
		  }
          }

          function getXPositionsName($i, $type)
	  {
		  global $vbphrase;
		  static $next_month;
		  static $start_at_setted;
		  static $usedMonths;
		  if(self::$start_at AND !$start_at_setted)
		  {
			$next_month = self::$start_at;
			$start_at_setted = true;
		  }

		  if($type == 3 OR $type == 4)
		  {
			$months = array(
					1 => $vbphrase['january'],
					2 => $vbphrase['february'],
					3 => $vbphrase['march'],
					4 => $vbphrase['april'],
					5 => $vbphrase['may'],
					6 => $vbphrase['june'],
					7 => $vbphrase['july'],
					8 => $vbphrase['august'],
					9 => $vbphrase['september'],
					10 => $vbphrase['october'],
					11 => $vbphrase['november'],
					12 => $vbphrase['december']
					);

			if($type == 3)
			{
					$monthsNumbers = self::buildMonthsIds();
					
					if(vbstrlen($i/16) <= 2)
					{
						$usedMonths++;
						$need_value = $usedMonths - 1;
						$monthName = substr($months[$monthsNumbers[$need_value]], 0, 3).".";

						return vB_vBGamez::vbg_set_charset($monthName);
					}
					return '';
			}else{
				$monthsNumbers = array(1,2,3,4,5,6,7,8,9,10,11,12);
				
				if(vbstrlen($i/11) <= 2)
				{
					$usedMonths++;
					$need_value = $usedMonths - 1;
					$monthName = substr($months[$monthsNumbers[$need_value]], 0, 3).".";

					return vB_vBGamez::vbg_set_charset($monthName);
				}
				return '';
				
			}
			
		  }elseif($type == 1){
			$val = $i-1;
			if($val == 0) { $val = '00:00'; }
			if($val == 24) { $val = '23:59'; }
			return $val;
		  }else{
			return $i;
		  }
	}
}

// 18.02. 2011 vBGamez_Stats_MapHistory
class vBGamez_Stats_MapHistory
{
	private $registry;
	private $serverinfo;

	public static function instance($serverinfo)
	{
		return new vBGamez_Stats_MapHistory(vB_vBGamez::vb_call(), $serverinfo);
	}
	public function __construct($registry, $serverinfo)
	{
		$this->registry =& $registry;
		$this->serverinfo =& $serverinfo;

		if($registry->options['vbgamez_popular_detection_mode'] == 'auto')
		{
			define('POPULAR_MAP_COUNT', $this->detectPopularCount($serverinfo));
		}elseif($registry->options['vbgamez_popular_detection_mode'] == 'manual'){ 
			define('POPULAR_MAP_COUNT', $registry->options['vbgamez_popular_map_count']);
		}
		define('POPULAR_MAP_TIME', 30);
		define('POPULAR_MAP_SHOWCOUNT', $registry->options['vbgamez_popular_map_showcount']);
		define('HISTORY_MAP_SHOWCOUNT', $registry->options['vbgamez_history_map_showcount']);
		
	}

	public function detectPopularCount($serverinfo)
	{
		$maxplayers = $serverinfo['s']['playersmax'];
		if($maxplayers > 150)
		{
			return $maxplayers - 15;
		}elseif($maxplayers > 100)
		{
			return $maxplayers - 12;
		}elseif($maxplayers > 50)
		{
			return $maxplayers - 7;
		}elseif($maxplayers > 32)
		{
			return $maxplayers - 5;
		}elseif($maxplayers > 20)
		{
			return $maxplayers - 3;
		}elseif($maxplayers > 10)
		{
			return $maxplayers - 2;
		}elseif($maxplayers > 5)
		{
			return $maxplayers - 1;
		}
	}
	public function _getStatsFromCache()
	{
          if(empty($this->serverinfo['i']['statistics']))
          {
              $this->serverinfo['i']['statistics'] = vBGamez_Stats_Builder::instance($this->registry, $this->serverinfo)->buildNow();
          }

		  if(is_array($stats))
		  {
          		$stats = $this->serverinfo['i']['statistics'];
		  }else{
				$stats = unserialize($this->serverinfo['i']['statistics']);
		}
		
		if(empty($stats))
		{
			return array();
		}else{
			return $stats;
		}
	}

	public function _getStats()
	{
		return vBGamez_Stats_Builder::instance($this->registry, $this->serverinfo)->fetch_Stats($this->serverinfo['o']['id']);
	}

	public function getPopularMaps()
	{
		$popular_maps = array();
		$max_players = array();
		$times = array();
		$displayed = array();
		
		foreach($this->_getStatsFromCache() AS $data)
		{
			$mapName = $data['mapname'];
			if(empty($mapName) OR $mapName == '---' OR $mapName == '-' OR vbstrlen($mapName) == 0)
			{
				continue;
			}

			$requiredTime = $times[$mapName] + (60 * POPULAR_MAP_TIME);

			if(!empty($times[$mapName]) AND $data['dateline'] < $requiredTime)
			{
				continue;
			}

			if(in_array($mapName, $displayed))
			{
				continue;
			}
			
			if($data['players'] < POPULAR_MAP_COUNT)
			{
				continue;
			}
			
			$map_counter++;
			
			if(POPULAR_MAP_SHOWCOUNT < $map_counter)
			{
				continue;
			}

			if($data['players'] >= POPULAR_MAP_COUNT)
			{
				$popular_maps[] = $mapName;
			}
			
			$playersPlayed = $data['players'];
			if($playersPlayed > $max_players[$mapName])
			{
				$max_players[$mapName] = $playersPlayed;
			}

			$times[$mapName] = $data['dateline'];
			$displayed[] = $mapName;
		}

		return array('popular_maps' => $popular_maps, 'max_players' => $max_players);
	}


	public function renderPopularMaps()
	{
		$maps = array();
		$__maps = $this->getPopularMaps();

		foreach($__maps['popular_maps'] AS $mapName)
		{
			if($__maps['max_players'][$mapName] == 0)
			{
				continue;
			}

			$maps[$mapName]['name'] = $mapName;
			$maps[$mapName]['imageurl'] = vB_vBGamez::vbgamez_image_map(1, $this->serverinfo['b']['type'], $this->serverinfo['s']['game'], $mapName);
			$maps[$mapName]['playersmax'] = $__maps['max_players'][$mapName];
			$maps[$mapName]['has_image'] = vB_vBGamez::hasImage($maps[$mapName]['imageurl']);
		}
		if(empty($maps))
		{
			return '';
		}
		$_REQUEST['order'] = 'desc';
		$maps = vBGamez_SortArray($maps, 'vB_vBGamez_Sorter::sort_by_playerscount');

		if(VBG_IS_VB4)
		{
			$template = vB_Template::create('vbgamez_popular_maps');
			$template->register('maps', $maps);
			return $template->render();
		}else{
			trigger_error('Need Update renderPopularMaps function for vB3', E_USER_ERROR);
		}
	}



	public function getHistoryMaps()
	{
		$history_maps = array();
		$times = array();

		$_cache_stats = $this->_getStatsFromCache();
		if($percent_count = $this->getPercentCount($_cache_stats))
		{
			$percent_100 = floatval(100 / $percent_count);
		}
		foreach($_cache_stats AS $data)
		{
			$mapName = $data['mapname'];
			if(empty($mapName) OR $mapName == '---' OR $mapName == '-' OR vbstrlen($mapName) == 0)
			{
				continue;
			}

			if(HISTORY_MAP_SHOWCOUNT <= $map_counter)
			{
				continue;
			}

			if(!in_array($mapName, $history_maps))
			{
				$history_maps[] = $mapName;
				$percents[$mapName] = $this->getPercents($percent_100, $this->getMapsCount($_cache_stats, $mapName));
				
				if(empty($prev_map_dateline))
				{
					// TODO 
					$times[$mapName] = $this->getMapTime(POPULAR_MAP_TIME * 60, 0);
				}else{
					$times[$mapName] = $this->getMapTime($data['dateline'], $prev_map_dateline);
				}

				$prev_map_dateline = $data['dateline'];
				
				$map_counter++;
				
			}
		}

		return array('history_maps' => $history_maps, 'times' => $times, 'percent' => $percents);
	}

	public function getMapTime($date, $prev_date)
	{
		global $vbphrase;

		$time = $date - $prev_date;

		$mins = intval($time / 60);

		if($mins >= 60)
		{
			$mins = intval($mins / 60)." " . $vbphrase['vbgamez_stats_hours'] . " ";
		}else{
			$mins .= ' '.$vbphrase['vbgamez_stats_mins'] . ' ';
		}

		return $mins;
	}

	public function getPercentCount($cache)
	{
		foreach($cache AS $data)
		{
			$mapName = $data['mapname'];
			if(empty($mapName) OR $mapName == '---' OR $mapName == '-' OR vbstrlen($mapName) == 0)
			{
				continue;
			}
			
			$map_counter++;

			if(HISTORY_MAP_SHOWCOUNT < $map_counter)
			{
				continue;
			}
		}

		return $map_counter;
	}

	public function getPercents($percent, $count)
	{
		if(empty($percent) OR empty($count))
		{
			return '1';
		}
	   return substr($percent * $count, 0, 4);
	}

	public function getMapList($stat_array)
	{
		if(empty($stat_array))
		{
			return '';
		}
		$mapsArray = array();
		foreach($stat_array AS $statId => $data)
		{
			$mapsArray[] = $data['mapname'];
		}
		return $mapsArray;
	}

	public function getMapsCount($stat_array, $mapName)
	{
		static $maps;
		if(!$maps)
		{
			$maps = $this->getMapList($stat_array);
		}

		if(!$maps)
		{
			return false;
		}
		foreach($maps AS $map)
		{
			if($map != $mapName) { continue; }
			$counter++;
		}

		return $counter;
	}


	public function renderHistoryMaps()
	{
		$maps = array();
		$__maps = $this->getHistoryMaps();

		foreach($__maps['history_maps'] AS $mapName)
		{
			$maps[$mapName]['name'] = $mapName;
			$maps[$mapName]['imageurl'] = vB_vBGamez::vbgamez_image_map(1, $this->serverinfo['b']['type'], $this->serverinfo['s']['game'], $mapName);
			$maps[$mapName]['time'] = $__maps['times'][$mapName];
			$maps[$mapName]['percent'] = $__maps['percent'][$mapName];
			$maps[$mapName]['has_image'] = vB_vBGamez::hasImage($maps[$mapName]['imageurl']);
		}
		if(empty($maps))
		{
			return '';
		}
		$_REQUEST['order'] = 'desc';
		$maps = vBGamez_SortArray($maps, 'vB_vBGamez_Sorter::sort_by_percent');
		
		if(VBG_IS_VB4)
		{
			$template = vB_Template::create('vbgamez_history_maps');
			$template->register('maps', $maps);
			return $template->render();
		}else{
			trigger_error('Need Update renderHistoryMaps function for vB3', E_USER_ERROR);
		}
	}
}
