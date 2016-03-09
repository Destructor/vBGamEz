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
 * VBGamEz ядро поиска
 *
 * @package vBGamEz
 * @author GiveMeABreak aka Developer, LGSL author 
 * @version $Revision: 344 $
 * @copyright GiveMeABreak
 */

class vB_vBGamez_Search_Core
{
	/*========================================================================
         *
	 * Таймер
         * 
	 */

         public static function fetch_microtime()
         {
             require_once(DIR . '/includes/functions_misc.php');

            $time = microtime();
            return number_format($time, 3, '.', '');
         }

	/*========================================================================
         *
	 * Создания поискового запроса
         * 
	 */

         public static function getSearchQuery()
         {
                global $vbulletin, $db;

		$keyword = explode(",", $vbulletin->GPC['query']);
		foreach ($keyword AS $text)
		{
			$text = trim($text);
			if (vbstrlen($text) >= 4)
			{
				$query .= " AND (vbgamez.cache_name LIKE ".$db->sql_prepare('%'.$text.'%')." OR vbgamez.ip LIKE ".$db->sql_prepare('%'.$text.'%').") ";
			}
		}

                $keyword = explode(",",$vbulletin->GPC['map']);
		foreach ($keyword AS $text)
		{
			$text = trim($text);
			if (vbstrlen($text) >= 4)
			{
                           if($vbulletin->GPC['exactmap'])
                           {
                                $query .= " AND vbgamez.cache_map = ".$db->sql_prepare($text);
                           }else{
                                $query .= " AND vbgamez.cache_map LIKE ".$db->sql_prepare('%'.$text.'%');
                           }
			}
		}

	       $vbulletin->GPC['game'] = trim($vbulletin->GPC['game']);
	       $vbulletin->GPC['additional_game'] = trim($vbulletin->GPC['additional_game']);

               if(empty($vbulletin->GPC['additional_game']))
               {
                  if($vbulletin->GPC['game'])
                  {
			if (vbstrlen($vbulletin->GPC['game']) >= 2)
			{
                                $query .= " AND (vbgamez.cache_game = " . $db->sql_prepare($vbulletin->GPC['game']) . " OR vbgamez.type = " . $db->sql_prepare($vbulletin->GPC['game']) . ")";
			}
                  }
                }else{
			if (vbstrlen($vbulletin->GPC['additional_game']) >= 2)
			{
                                $query .= " AND vbgamez.cache_game = " . $db->sql_prepare($vbulletin->GPC['additional_game']) . " AND vbgamez.type = " . $db->sql_prepare($vbulletin->GPC['game']) . " ";
			}
                }

                if($vbulletin->GPC['players'])
                {
                        $slotsoptions = $vbulletin->GPC['slotsoptions'];

			$vbulletin->GPC['players'] = trim($vbulletin->GPC['players']);
			if (vbstrlen($vbulletin->GPC['players']) >= 1)
			{
                         if($slotsoptions == 1)
                         {
                                $query .= " AND (vbgamez.cache_playersmax - vbgamez.cache_players) <= ".$db->sql_prepare($vbulletin->GPC['players']);
			 }
                         if($slotsoptions == 0)
                         {
                                $query .= " AND (vbgamez.cache_playersmax - vbgamez.cache_players) >= ".$db->sql_prepare($vbulletin->GPC['players']);
			 }
                       }
		}

                if($vbulletin->GPC['playersmax'])
                {
                        $playersoptions = $vbulletin->GPC['playersoptions'];

			$vbulletin->GPC['playersmax'] = trim($vbulletin->GPC['playersmax']);
			if (vbstrlen($vbulletin->GPC['playersmax']) >= 1)
			{
                         if($playersoptions == 1) 
                         {
                                $query .= " AND vbgamez.cache_playersmax <= ".$db->sql_prepare($vbulletin->GPC['playersmax']);
			 }
                         if($playersoptions == 0)
                         {
                                $query .= " AND vbgamez.cache_playersmax >= ".$db->sql_prepare($vbulletin->GPC['playersmax']);
			 }
                        }
		}

                if($vbulletin->GPC['rating'])
                {
                        $ratingoptions = $vbulletin->GPC['ratingoptions'];

			$vbulletin->GPC['rating'] = trim($vbulletin->GPC['rating']);
			if (vbstrlen($vbulletin->GPC['rating']) >= 1)
			{
                         if($ratingoptions == 1)
                         {
                                $query .= " AND vbgamez.rating <= ".$db->sql_prepare($vbulletin->GPC['rating']);
			 }
                         if($ratingoptions == 0)
                         {
                                $query .= " AND vbgamez.rating >= ".$db->sql_prepare($vbulletin->GPC['rating']);
			 }
                        }
		}

              if($vbulletin->GPC['views'])
              {
                        $viewoptions = $vbulletin->GPC['viewoptions'];
			$vbulletin->GPC['views'] = trim($vbulletin->GPC['views']);
			if (vbstrlen($vbulletin->GPC['views']) >= 1)
			{
                         if($viewoptions == 1)
                         {
                                $query .= " AND vbgamez.views <= ".$db->sql_prepare($vbulletin->GPC['views']);
			 }
                         if($viewoptions == 0)
                         {
                                $query .= " AND vbgamez.views >= ".$db->sql_prepare($vbulletin->GPC['views']);
                         }
                        }
	      }

              if($vbulletin->GPC['exclude'])
              {
                          $query .= " AND vbgamez.id != ".intval($vbulletin->GPC['exclude']);
	      }

              if($vbulletin->GPC['steam'])
              {
                          $query .= " AND vbgamez.steam = 1 ";
              }

              if($vbulletin->GPC['nonsteam'])
              {
                          $query .= " AND vbgamez.nonsteam = 1 ";
              }


              $fieldObj = new vBGamEz_FieldsSearchController($vbulletin);
              $query .= $fieldObj->get_Search_Field_Query();

              return $query;

          }

}