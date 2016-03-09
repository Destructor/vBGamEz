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

class vB_vBGamez_Sorter
{
     function by_name($player_a, $player_b)
     {
    		return self::string_compare($player_a, $player_b, 'name');
     }

     function by_score($player_a, $player_b)
     {
    		return self::intval_compare($player_a, $player_b, 'score');
     }

     function by_deaths($player_a, $player_b)
     {
    		return self::intval_compare($player_a, $player_b, 'deaths');
     }

     function by_team($player_a, $player_b)
     {
    		return self::string_compare($player_a, $player_b, 'team');
     }

     function by_ping($player_a, $player_b)
     {
    		return self::intval_compare($player_a, $player_b, 'ping');
     }

     function by_bot($player_a, $player_b)
     {
    		return self::intval_compare($player_a, $player_b, 'bot');
     }

     function by_time($player_a, $player_b)
     {
    		return self::intval_compare($player_a, $player_b, 'time');
     }

     function by_race($player_a, $player_b)
     {
    		return self::intval_compare($player_a, $player_b, 'race');
     }

     function by_class($player_a, $player_b)
     {
    		return self::intval_compare($player_a, $player_b, 'class');
     }

     function by_level($player_a, $player_b)
     {
    		return self::intval_compare($player_a, $player_b, 'level');
     }

     function by_fr($player_a, $player_b)
     {
    		return self::intval_compare($player_a, $player_b, 'fr');
     }

     function by_teamindex($player_a, $player_b)
     {
    		return self::intval_compare($player_a, $player_b, 'teamindex');
     }
     function by_pid($player_a, $player_b)
     {
    		return self::intval_compare($player_a, $player_b, 'pid');
     }

     function by_pbid($player_a, $player_b)
     {
    		return self::intval_compare($player_a, $player_b, 'pbid');
     }


     function sort_by_percent($array_a, $array_b)
     {
    		return self::intval_compare($array_a, $array_b, 'percent');
     }

     function sort_by_playerscount($array_a, $array_b)
     {
    		return self::intval_compare($array_a, $array_b, 'playersmax');
     }


     // COMPARE FUNCTIONS

     function intval_compare($player_a, $player_b, $key)
     {
			$vars = self::ascDesc();
    		if ($player_a[$key] == $player_b[$key]) { return 0; }
    		return ($player_a[$key] < $player_b[$key]) ? $vars[0] : $vars[1];
     }

     function string_compare($player_a, $player_b, $key)
     {
    		$name_a = preg_replace("/[\x{00}-\x{2F}\x{3A}-\x{40}\x{5B}-\x{60}\x{7B}-\x{7F}]/", "", $player_a[$key]);
    		$name_b = preg_replace("/[\x{00}-\x{2F}\x{3A}-\x{40}\x{5B}-\x{60}\x{7B}-\x{7F}]/", "", $player_b[$key]);
			$askDesc = self::ascDesc();
			
    		if (function_exists("mb_convert_case"))
    		{
      			$name_a = @mb_convert_case($name_a, MB_CASE_LOWER, "UTF-8");
      			$name_b = @mb_convert_case($name_b, MB_CASE_LOWER, "UTF-8");
				$check = strcmp($name_a, $name_b);
      			if($check == 0)
				{
						return 0;
				}elseif($check < 0)
				{
						return $askDesc[0];
				}elseif($check > 0)
				{
						return $askDesc[1];
				}
    		}
    		else
    		{ 
      			$check = strcasecmp($name_a, $name_b);
      			if($check == 0)
				{
						return 0;
				}elseif($check < 0)
				{
						return $askDesc[0];
				}elseif($check > 0)
				{
						return $askDesc[1];
				}
    		}
     }

	 function ascDesc()
	 {
		if($_REQUEST['order'] == 'desc')
		{
			return array(1, -1);
		}else{
			return array(-1, 1);
		}
	 }
}

