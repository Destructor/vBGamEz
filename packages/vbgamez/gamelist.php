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

class vB_vBGamez_GameList
{
      public static function gameIsEnabled($type)
      {
		if(in_array($type, explode(',', vB_vBGamez::vb_call()->options['vbgamez_avilable_games'])))
		{
			return true;
		}else{
			return false;
		}
      }

      public static function updateUserServers($userid)
      {
                global $vbulletin;

                if(!$userid) { return false; }

                $get_counter = $vbulletin->db->query_read("SELECT count(id) FROM " . TABLE_PREFIX ."vbgamez WHERE userid = '" . intval($userid) . "' AND disabled = 0");

                $counter = $vbulletin->db->fetch_row($get_counter);

                $vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET servers = '" . intval($counter[0]) . "' WHERE userid = '" . intval($userid) . "'");

      }
      public static function prepareGamesToSQL($types)
      {
		$newTypes = array();
		foreach(explode(',', $types) AS $type)
		{
			$newTypes[] = "'" . $type . "'";
		}
		return implode(',', $newTypes);
      }

      public static function deleteNotUsedServed($value)
      {
		$vbulletin = vB_vBGamez::vb_call();

		$game_types = self::getExcludeGamesQuery($value);
		if(!empty($game_types))
		{
			$sql_types = self::prepareGamesToSQL($game_types);
			$get_ids = $vbulletin->db->query_read("SELECT userid, id FROM " . TABLE_PREFIX . "vbgamez WHERE type IN ($sql_types)");
			while($serv = $vbulletin->db->fetch_array($get_ids))
			{
				$ids[] = $serv['id'];
				if($serv['userid'])
				{
					$userids[] = $serv['userid'];
				}
			}

			if($ids)
			{
				$vbulletin->db->query("DELETE FROM " . TABLE_PREFIX . "vbgamez WHERE id IN (" . implode(',', $ids) . ")");
				foreach($userids AS $userid)
				{
					self::updateUserServers($userid);
				}
			}
		}
      }

      public static function getExcludeGamesQuery($value)
      {
		$game_is_enabled = array();

		foreach($value AS $gametype)
		{
			$game_is_enabled[$gametype] = 1;
		}

		$games_all = vbgamez_type_list(true);
		$disabled_games = array();
		foreach($games_all AS $type => $name)
		{
			if(!$game_is_enabled[$type])
			{
				$disabled_games[] = $type;
			}
		}

		if(!empty($disabled_games))
		{
			$sql = implode(',', $disabled_games);
		}
		if(trim($sql) == '')
		{
			return false;
		}

		return $sql;
      }

      public static function checkGameList()
      {
		global $vbphrase;
 		if(empty(vB_vBGamez::vb_call()->options['vbgamez_avilable_games']))
		{
			eval(standard_error($vbphrase['vbgamez_check_enabled_games']));
		}
      }

      public static function prepareIdsToTopList($type, $limit = 10)
      {
		global $vbulletin;
		$standard_sql = ' WHERE disabled = 0 AND valid = 0 ';

		$ids = array();
		$counter = 0;

		if($type == 'rating')
		{
			$fetch_all_servers = $vbulletin->db->query_read("SELECT type, id, cache_name FROM " . TABLE_PREFIX . "vbgamez AS vbgamez $standard_sql ORDER BY rating DESC");
		}elseif($type == 'visiting')
		{
			$fetch_all_servers = $vbulletin->db->query_read("SELECT type, id, cache_name FROM " . TABLE_PREFIX . "vbgamez AS vbgamez $standard_sql ORDER BY cache_players DESC");
		}elseif($type == 'views')
		{
			$fetch_all_servers = $vbulletin->db->query_read("SELECT type, id, cache_name FROM " . TABLE_PREFIX . "vbgamez AS vbgamez $standard_sql ORDER BY views DESC");
		}elseif($type == 'comments')
		{
			$fetch_all_servers = $vbulletin->db->query_read("SELECT type, id, cache_name FROM " . TABLE_PREFIX . "vbgamez AS vbgamez $standard_sql ORDER BY comments DESC");
		}


		while($server = $vbulletin->db->fetch_array($fetch_all_servers))
		{
            	     	if(!self::gameIsEnabled($server['type']))
	    	     	{
 					continue;
	    	     	}
			$server['cache_name'] = vB_vBGamez::vbgamez_string_html($server['cache_name']);

			if(!$server['cache_name'])
			{
					continue;
			}
			$counter++;

			if($counter == $limit)
			{
					break;
			}
			$ids[] = $server['id'];
		}

		if(empty($ids))
		{
			$ids[] = 0;
		}

		return implode(',', $ids);
    }
}