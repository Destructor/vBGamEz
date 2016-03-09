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
// TODO
require_once(DIR . '/packages/vbgamez/dbgames.php');
require_once(DIR . '/packages/vbgamez/functions.php');
require_once(DIR . '/packages/vbgamez/datastore.php');
require_once(DIR . '/packages/vbgamez/route.php');

/**
 * VBGamEz основные функции
 *
 * @package vBGamEz
 * @author GiveMeABreak aka Developer, LGSL author
 * @version $Revision: 277 $
 * @copyright GiveMeABreak
 */

class vB_vBGamez extends vB_vBGamez_Datastore
{
         // vBGamEz Version.

         private static $vbgamez_version = '6.0 Beta 4';


	 /*========================================================================
         *
	 * Установка основных данных
	 *
	 */ 

         public static function bootstrap($is_widget = false)
         {
                       global $vbulletin, $vbphrase;

                       require_once(DIR . '/includes/functions_user.php');
                       require_once(DIR . '/includes/adminfunctions.php');
                       require_once(DIR . '/includes/functions_editor.php');

                       vB_vBGamez_Route::setUrls();

                       define('VBG_IS_VB4', vB_vBGamez::is_vb4());

                       if (!$vbulletin->options['vbgamez_enable'] AND !$is_widget AND !defined('VBGAMEZ_DO_NOT_DISABLE_VBGAMEZ')) 
                       { 
                                  standard_error(fetch_error('vbgamez_closed'));
                       }
		       vB_vBGamez::loadClassFromFile('gamelist');
		       vB_vBGamez_GameList::checkGameList();
                       if(VBG_IS_VB4)
                       {
                                $copyright_phrase = 'powered_by_vbulletin';
                       }else{
                                $copyright_phrase = 'powered_by_vbgamez';
                       }

                       $vbphrase[$copyright_phrase] = vB_vBGamez::fetch_vbg_version();

                       if(defined('VBG_DOMAIN') AND !VBG_IS_VB4 AND VBG_PACKAGE == true AND THIS_SCRIPT != 'index' AND !$is_widget)
                       { 
				global $stylevar, $headinclude;
                                $search_head = '<meta http-equiv="Content-Type" content="text/html; charset=' . $stylevar['charset'] . '" />';
                                $headinclude = str_replace($search_head, '<base href="' . $vbulletin->options['bburl'] . '/">'.$search_head, $headinclude);
                       }
 
                       if($vbg_keywords = $vbulletin->options['vbgamez_meta_keywords'] AND THIS_SCRIPT != 'index' AND !$is_widget)
                       {
                                  $vbulletin->options['keywords'] = $vbg_keywords;
                       }

                       if($vbg_description = $vbulletin->options['vbgamez_meta_description'] AND THIS_SCRIPT != 'index' AND !$is_widget)
                       {
                                  $vbulletin->options['description'] = $vbg_description;
                       }
         }

	 /*========================================================================
         *
	 * Отправка ПМ
	 *
	 */ 

         public static function send_pm($type, $data)
         {
                     global $vbulletin, $permissions, $vbphrase;

                     if($type == 'Comment')
                     {
                               $need_send_pm_condition = $vbulletin->options['vbgamez_commentpm'] AND $vbulletin->options['vbgamez_addcommentadminids'] AND $vbulletin->options['vbgamez_addcommentoptions'] AND vB_vBGamez::check_permissions($data);
                               if($vbulletin->options['vbgamez_addcommentoptions'] == 'moderate' AND $vbulletin->options['vbgamez_comments_moderation'])
                               {
                                                 $pm_message = $vbphrase['vbgamez_comment_moderation_pm'];
                                                 $pm_title = $vbphrase['vbgamez_comment_moderation_pm_title'];
         
                               }else if($vbulletin->options['vbgamez_addcommentoptions'] == 'onlyadd' AND !$vbulletin->options['vbgamez_comments_moderation'])
                               {
    
                                                 $pm_message = $vbphrase['vbgamez_comment_pm'];
                                                 $pm_title = $vbphrase['vbgamez_comment_pm_title'];

                               }else if($vbulletin->options['vbgamez_addcommentoptions'] == 'always')
                               {   

                                                  if($vbulletin->options['vbgamez_comments_moderation'])
                                                  {
                                                                      $pm_message = $vbphrase['vbgamez_comment_moderation_pm'];
                                                                      $pm_title = $vbphrase['vbgamez_comment_moderation_pm_title'];

                                                  }else{
                                                                      $pm_message = $vbphrase['vbgamez_comment_pm'];
                                                                      $pm_title = $vbphrase['vbgamez_comment_pm_title'];
                                                  }
 
                                }

                                if($vbulletin->options['vbgamez_send_pm_to_admin'] AND !empty($data['userid']) AND $data['userid'] != $vbulletin->userinfo['userid'])
                                {
                                                  $explode_userids = explode(",", $vbulletin->options['vbgamez_addcommentadminids'].",".$data['userid']);
                                }else{
                                                  $explode_userids = explode(",", $vbulletin->options['vbgamez_addcommentadminids']);
                                }

                     }elseif($type == 'UploadMap') 
                     {
                               $need_send_pm_condition = $vbulletin->options['vbgamez_uploadmappm'] AND $vbulletin->options['vbgamez_uploadmapadminids'] AND $vbulletin->options['vbgamez_uploadmapoptions'] AND vB_vBGamez::check_permissions();
                               $link_to_map = $vbulletin->options['bburl']."/".$vbulletin->config['Misc']['modcpdir']."/vbgamez_moderate.php?do=custom_maps";

                               if($vbulletin->options['vbgamez_uploadmapoptions'] == 'moderate' AND $vbulletin->options['vbgamez_uploadmap_moderation'])
                               {
                                                 $pm_message = $vbphrase['vbgamez_map_added_moderated'];
                                                 $pm_title = $vbphrase['vbgamez_map_added_moderated_title'];
         
                               }else if($vbulletin->options['vbgamez_uploadmapoptions'] == 'onlyadd' AND !$vbulletin->options['vbgamez_uploadmap_moderation'])
                               {
                                                 $pm_message = $vbphrase['vbgamez_map_added'];
                                                 $pm_title = $vbphrase['vbgamez_map_added_title'];

                               }else if($vbulletin->options['vbgamez_uploadmapoptions'] == 'always')
                               {   

                                                  if($vbulletin->options['vbgamez_uploadmap_moderation'])
                                                  {
                                                                   $pm_message = $vbphrase['vbgamez_map_added_moderated'];
                                                                   $pm_title = $vbphrase['vbgamez_map_added_moderated_title'];
                                                  }else{
                                                                   $pm_message = $vbphrase['vbgamez_map_added'];
                                                                   $pm_title = $vbphrase['vbgamez_map_added_title'];
               
                                                  }
 
                                }

                                if($vbulletin->options['vbgamez_uploadmapadminids'])
                                {
                                                  $explode_userids = explode(",", $vbulletin->options['vbgamez_uploadmapadminids']);
                                }
                     }

                     require_once(DIR . '/includes/functions_misc.php');

                     if($pm_title AND $pm_message AND !empty($explode_userids) AND $need_send_pm_condition)
                     {
                              $sended_to_userids = array();

                              foreach($explode_userids AS $userid)
                              {
                                     # нафига отправлять уведомление если юзер и так знает что он сделал?
                                     if($userid == $vbulletin->userinfo['userid'] OR in_array($userid, $sended_to_userids))
                                     {
                                                   continue;
                                     }

                                     $touserinfo = fetch_userinfo($userid);

                                     if($type == 'Comment')
                                     {
                                             $pm_title = construct_phrase($pm_title, vB_vBGamez::vbgamez_string_html($data['cache_name']));
                                             $pm_message = construct_phrase($pm_message, $touserinfo['username'], vB_vBGamez::vbgamez_string_html($data['cache_name']), $vbulletin->userinfo['username'], $vbulletin->options['vbgamez_path'].'?do=view&id='.$data['id'].'#comments', $vbulletin->options['bburl'].'/member.php?u='.$vbulletin->userinfo['userid'], $vbulletin->options['vbgamez_path'].'?do=view&id='.$data['id']);
                                     }elseif($type == 'UploadMap')
                                     {
                                             $pm_message = construct_phrase($pm_message, $data, $link_to_map);
                                     }

                                     $fromusername = $vbulletin->userinfo['username'];
                                     $fromuserid = $vbulletin->userinfo['userid'];

                                     if(empty($vbulletin->userinfo['userid']))
                                     {
                                             $fromuserid = $userid;
                                             $fromusername = $touserinfo['username'];
                                     }

                                     $pmdm =& datamanager_init('PM', $vbulletin, ERRTYPE_ARRAY);
                                     $pmdm->set('fromuserid', $fromuserid);
                                     $pmdm->set('fromusername', $fromusername);
                                     $pmdm->set('title', $pm_title);
                                     $pmdm->set('message', $pm_message);
                                     $pmdm->set_recipients($touserinfo['username'], $permissions);
                                     $pmdm->set('dateline', TIMENOW);
                                     $pmdm->set_info('savecopy',0);
                                     if(empty($pmdm->errors))
                                     {
                                               $pmdm->save();
                                               $sended_to_userids[] = $userid;
                                     }
                              }
                     }
         }


	 /*========================================================================
         *
	 * Установка кодировки для строки
	 *
	 */ 

         public static function vbg_set_charset($text)
         {
              if(vB_vBGamez::fetch_stylevar('charset') != 'UTF-8')
              {
                          if(function_exists('mb_convert_encoding'))
                          {
                                return mb_convert_encoding($text, 'UTF-8', vB_vBGamez::fetch_stylevar('charset'));
                          }
                          else if(function_exists('iconv'))
                          {
                                return iconv(vB_vBGamez::fetch_stylevar('charset'), 'UTF-8', $text);
                          }
              }else{
                          return $text;
              }
         }

	 /*========================================================================
         *
	 *  DO NOT REMOVE COPYRIGHTS!!
	 *
	 */ 

         public static function fetch_vbg_version()
         {
              global $vbphrase;

              $copyright_text = 'Powered by <a href="http://vbgamez.com/">vBGamEz&trade;</a> Version ' . vB_vBGamez::$vbgamez_version . ', developed by <a href="http://vbgamez.com">vBGamEz Team</a><br /><br />';

              if(vB_vBGamez::is_vb4() AND (VB_AREA != 'AdminCP' AND VB_AREA != 'ModCP'))
              {
                        return $copyright_text.$vbphrase['powered_by_vbulletin'];
              }else{
                        return $copyright_text;
              }
         }

         // simply caller $vbulletin
         public static function vb_call()
         {
                   global $vbulletin;
                   return $vbulletin;
         }

         // simply caller $vbphrase
         public static function vbphrase_call()
         {
                   global $vbphrase;
                   return $vbphrase;
         }

	 public static function error($error)
	 {
		   if(VB_AREA == 'AdminCP' OR VB_AREA == 'ModCP')
		   {
				print_cp_message($error, 0);
		   }else{
				eval(standard_error($error));
		   }
	 }

         public static function loadClassFromFile($filename)
         {
		   $full_filename = DIR .'/packages/vbgamez/' . $filename . '.php';

                   if(file_exists($full_filename))
                   {
				require_once($full_filename);
		   }else{
				trigger_error('File not found: ' . $full_filename . '');
 		   }

	 }
                      
	 /*========================================================================
         *
	 * Уменьшение размера загружаемой карты
	 *
	 */ 

         public static function resize_map_image($filename)
         {
		  if(empty(vB_vBGamez::vb_call()->options['vbgamez_uploadmap_resize_height']))
                  {
                            return false;
                  }

                  if(empty($filename))
                  {
                            return false;
                  }


                  require_once(DIR .'/packages/vbgamez/3rd_party_classes/resize/resize.php');
                  $resizer = new SimpleImage();
                  $resizer->load($filename);
                  if(empty(vB_vBGamez::vb_call()->options['vbgamez_uploadmap_resize_width']))
                  {
                  		$resizer->resizeToHeight(vB_vBGamez::vb_call()->options['vbgamez_uploadmap_resize_height']);
		  }else{
                                $resizer->resize(vB_vBGamez::vb_call()->options['vbgamez_uploadmap_resize_height'], vB_vBGamez::vb_call()->options['vbgamez_uploadmap_resize_width']);
                  }

                  $resizer->save($filename, $resizer->image_type, 100);
         }

	 /*========================================================================
         *
	 * Перевод поля доп. настроек
	 *
	 */

         public static function vbgamez_translate_field($name)
         {
                global $vbulletin;

                $explode_all = explode("\r\n", $vbulletin->options['vbgamez_replace_detalis']);

                if(empty($explode_all)) { return $name; }

                foreach($explode_all AS $key => $value)
                {
                           $explode = explode('|', $value);
                           if($name == $explode[0])
                           {
                                   return $explode[1];
                           }

                }

                return $name;
         }


	 /*========================================================================
         * 
	 * Получение имени скрипта
	 *
	 */

         public static function fetch_scriptname()
         {
              global $vbulletin;

              //if(defined('VBG_DOMAIN'))
              //{
              //      $url = VBG_DOMAIN."/".VBG_SCRIPTNAME;
              //}else{
              //      $url = $vbulletin->options['bburl']."/".$vbulletin->options['vbgamez_scriptname'];
              //}

              $url = $vbulletin->options['vbgamez_path'];
              return $url;
         }

	 /*========================================================================
         * 
	 * Получение изображения
	 *
	 */

         public static function fetch_image($path)
         {
                   global $vbulletin;
 
                   if(!$vbulletin->options['bburl'])
                   {
                                 $vbulletin->options['bburl'] = 'http://'.$_SERVER['SERVER_NAME'];
                   }

                   $path = str_replace($vbulletin->options['bburl']."/", '', $path);

                   if(defined('VBG_DOMAIN'))
                   {
                            return $vbulletin->options['bburl']."/".$path;
                   }else{
                            return $path;
                   }
         }

	 /*========================================================================
         * 
	 * Is vB4 
	 *
	 */

         public static function is_vb4()
         {
                   global $vbulletin;

                   return iif($vbulletin->versionnumber >= 4, true, false);    
         }

	 /*========================================================================
         * 
	 * Имя полей игроков
	 *
	 */

         public static function fetch_player_fields_names($value)
         {
            global $vbphrase;

            $fields_names = array("name" => $vbphrase['username'],
                                  "score" => $vbphrase['vbgamez_frags'],
                                  "deaths" => $vbphrase['vbgamez_deaths'],
                                  "team" => $vbphrase['vbgamez_team'],
                                  "ping" => $vbphrase['vbgamez_ping'],
                                  //"bot" => $vbphrase['vbgamez_bot'],
                                  "time" => $vbphrase['time'],
                                  "race" => $vbphrase['vbgamez_race'],
                                  "class" => $vbphrase['vbgamez_class'],
                                  "level" => $vbphrase['vbgamez_level'],
                                  "fr" => $vbphrase['vbgamez_fractions'],
                                 // "teamindex" => "Team Index",
                                 // "pid" => "PID",
                                 // "pbid" => "PB GUID"
								);

           return $fields_names[$value];
         }

	 /*========================================================================
         * 
	 * Поля игроков
	 *
	 */

         public static function fetch_player_fields($server)
         {

              $fields_show  = array("name", "score", "deaths", "team", "ping", "bot", "time");
              $fields_hide  = array("teamindex", "pid", "pbguid");

              $fields_other = true;

              $fields_list = array();

              if (!is_array($server['p']))
              {
                      return $fields_list;
              }

              foreach ($server['p'] as $player)
              {
                      foreach ($player as $field => $value)
                      {
							  $value = trim($value);
                              if ($value == "")
                              {
                                             continue;
                              }

                              if (in_array($field, $fields_list))
                              {
                                              continue;
                              }

                              if (in_array($field, $fields_hide))
                              {
                                               continue;
                              }

			      if(!self::fetch_player_fields_names($field))
			      {
						continue;
			      }

                              $fields_list[$field] = $field;
                      }
              }

              $fields_show = array_intersect($fields_show, $fields_list);

              if ($fields_other == FALSE)
              {

                           return $fields_show;
              }

              $fields_list = array_diff($fields_list, $fields_show);

              return array_merge($fields_show, $fields_list);

         }


	 /*========================================================================
         *
	 * Парсинг типа игры
	 *
	 */

         public static function parse_game($game)
         {
                      return preg_replace("/[^A-Za-z0-9_]/", "_", strtolower($game));
         }

	 /*========================================================================
         *
	 * Получение stylevars
	 *
	 */

         public static function fetch_stylevars()
         {

           if(!vB_vBGamez::fetch_stylevar('imgdir_statusicon'))
           {
                  vB_vBGamez::add_stylevar('imgdir_statusicon', 'images/statusicon');
           }

           if(!vB_vBGamez::fetch_stylevar('imgdir_misc'))
           {
                  vB_vBGamez::add_stylevar('imgdir_misc', 'images/misc');
           }

         }

	 /*========================================================================
         *
	 * Добавление stylevar
	 *
	 */

         public static function add_stylevar($var, $value)
         {
              global $stylevar;

              if(vB_vBGamez::is_vb4())
              {
                        return vB_Template_Runtime::addStyleVar($var, $value);
              }else{
                        return $stylevar[$var] = $value;
              }

         }

	 /*========================================================================
         *
	 * Получение stylevar
	 *
	 */

         public static function fetch_stylevar($var)
         {

              if(vB_vBGamez::is_vb4())
              {
                        return vB_Template_Runtime::fetchStyleVar($var);
              }else{
                        global $stylevar; return $stylevar[$var];
              }

         }

	 /*========================================================================
         *
	 * Парсинг ICQ 
	 *
	 */

         public static function handle_bbcode_icq($number)
         {
                 global $vbphrase;

                 $number = intval($number);

                 if(!$number) { return false; }

                 // получаем Stylevar'ы
                 vB_vBGamez::fetch_stylevars();

                 return '<a href="javascript://" onclick="vbg_imwindow(\'' . $number . '\'); return false;"><img src="' . vB_vBGamez::fetch_stylevar('imgdir_misc') . '/im_icq.gif" alt="' . construct_phrase($vbphrase['send_message_via_icq_to_x'], $vbphrase['vbgamez_to_admin']) . '" style="vertical-align: middle;" border="0"/></a> ' . $number . '';
         }


	 /*========================================================================
         *
	 * Установка перпейджа
	 *
	 */

         public static function sanitize_perpage($perpage, $max, $default = 25)
         {
	       $perpage = intval($perpage);

	       if ($perpage == 0)
	       {
		       return $default;
	       }
	       else if ($perpage < 1)
	       {
		       return 1;
	       }
	       else if ($perpage > $max)
	       {
		       return $max;
	       }
	       else
	       {
		       return $perpage;
	       }
         }

	 /*========================================================================
         *
	 * Проверка прав на удаление комментариев
	 *
	 */

        public static function vbg_check_delete_comments_permissions($serverid, $adminuserid = '', $userid = '')
         {
 
             global $vbulletin;

             if($vbulletin->options['vbgamez_comments_enable'])
             {

                   if(!vB_vBGamez::vbg_can_moderate_comments())
                   {

                           if($vbulletin->options['vbgamez_del_user_comments'])
                           {
                                 if(empty($adminuserid))
                                 {
                                             $fetch = $vbulletin->db->query_read("SELECT userid FROM " . TABLE_PREFIX . "vbgamez WHERE id = '" . intval($serverid) . "'");
                                             $user = $vbulletin->db->fetch_array($fetch);

                                 }else{
                                             $user['userid'] = $adminuserid;
                                 }

                                if(!empty($user['userid']) AND $user['userid'] == $vbulletin->userinfo['userid'])
                                {
                                             $show['delete'] = true;
                                }
                          }

                          if(!$show['delete'] AND $vbulletin->options['vbgamez_del_comments'] AND $vbulletin->userinfo['userid'] == $userid AND !empty($userid))
                          {
                               $show['delete'] = true;
                          }

                 }else{
                          $show['delete'] = true;
                 }

                return $show['delete'];
            }
         }

	 /*========================================================================
         *
	 * Проверка прав на модерирование комментариев
	 *
	 */

         public static function vbg_can_moderate_comments()
         {
                   global $vbulletin;

                   $explode_groups = explode(",", $vbulletin->options['vbgamez_comment_adminid']); 

                   if(in_array($vbulletin->userinfo['usergroupid'], $explode_groups))
                   {
                                  return true;
                   }else{
                                  return false;
                   }
         }

	 /*========================================================================
         *
	 * Проверка прав на редактирование комментариев
	 *
	 */

         public static function vbg_check_edit_comments_permissions($userid)
         {

           global $vbulletin;

            $show['edit'] = false;

            if($vbulletin->options['vbgamez_comments_enable'])
            {

             if(!vB_vBGamez::vbg_can_moderate_comments())

             {

                 if(!empty($userid) AND $userid == $vbulletin->userinfo['userid'] AND $vbulletin->options['vbgamez_edit_user_comments'])
                 {
                        $show['edit'] = true;
                 }else{
                        $show['edit'] = false;
                 }

               }else{
                        $show['edit'] = true;
              }

            return $show['edit'];

            }
          }

	 /*========================================================================
         *
	 * Проверка включены ли комментарии у сервера
	 *
	 */

         public static function vbg_check_comments_enable($value)
          {

            global $vbulletin;

            if($vbulletin->options['vbgamez_comments_enable'])
             {
 
               if($vbulletin->options['vbgamez_comments_userdisable'])
               {

                  if($value == '1')
                   {
                          $show['commentsenable'] = true;
                   }else{ 
                         $show['commentsenable'] = false;
                   }

                }else{
                          $show['commentsenable'] = true;
                }

              return $show['commentsenable'];
             }
           }

	 /*========================================================================
         *
	 * Проверка включены ли комментарии у сервера
	 *
	 */

          public static function vbg_ajax_show_steam($game, $array = '')
           {
        
	         $enablesteam = "Steam <input type=\"checkbox\" name=\"steam\" value=\"1\" id=\"steam\" " . iif($array['steam'], 'checked="checked"') . "/> non-Steam <input type=\"checkbox\" name=\"nonsteam\" value=\"1\" id=\"nonsteam\" " . iif($array['nonsteam'], 'checked="checked"') . "/>";

                 if($game == 'halflife' OR $game == 'halflifewon' OR $game == 'source')
                 {
                     return $enablesteam;
                 }else{
                     return false;
                 }
            }

	 /*========================================================================
         *
	 * Обновление рейтинга у сервера
	 *
	 */

         public static function vbg_ajax_update_rating($serverid)
          {

            global $vbulletin, $vbphrase;


            $result_update = $vbulletin->db->query_read("SELECT rating FROM " . TABLE_PREFIX . "vbgamez WHERE id = " . $serverid . "");
            $update = $vbulletin->db->fetch_Array($result_update);
            $update['rating'] = intval($update['rating']);

            if(!empty($serverid))
            {
             print $update['rating']; exit;
            }

           }

	 /*========================================================================
         *
	 * Получение дополнительного дополнения игры
	 *
	 */

         public static function fetch_additional_game_type($type)
         {
            global $vbphrase;

            if($type == 'bf1942')
             {

                  return array("desertcombat" => "Desert combat", "interstate" => "Interstate");
             }

            if($type == 'farcry')
             {

                  return array("obsidianedge" => "Obsidianedge", "farcry" => "Far Cry");
             }

            if($type == 'halflife')
             {

                  return array("cstrike" => "Counter-Strike 1.6", "czero" => "Counter-Strike: Condition Zero", "dod" => "Half-Life: Day of Defeat", "frontline" => "Half-Life: Frontline", "halflife" => "Half-Life", "ns" => "Half-Life: NS", "nsp" => "Half-Life: NSP", "ricochet" => "Half-Life: Ricochet", "svencoop" => "Half-Life: Svencoop", "tfc" => "Team Fortress: Classic", "ts" => "TS", "valve" => "Valve");
             }

            if($type == 'halflifewon')
             {

                  return array("cstrike" => "Counter-Strike 1.5", "halflifewon" => "Half-Life: WON");
             }

            if($type == 'source')
             {

                  return array("ark_survival_evolved" => "ARK: Survival Evolved", "cstrike" => "Counter-Strike: Source", "dod" => "Half-Life 2: Day of Defeat", "hl2mp" => "Half-Life 2: Multiplayer", "left4dead" => "Left 4 Dead ", "left4dead2" => "Left 4 Dead 2", "tf" => "Team Fortress 2" , 'garrysmod' => 'Half-Life 2: Garry\'s Mod',// "" => 'Half-Life 2',
 'cspromod' => 'CSS: ProMod', 'ageofchivalry' => 'Half-Life 2: Age Of Chivalry', 'dystopia' => 'Half-Life 2: Dystopia', 'esmod' => 'Half-Life 2: Eternal Silence', 'insurgency' => 'Half-Life 2: Insurgency', 'synergy' => 'Half-Life 2: Synergy', 'zps' => 'Zombie Panic: Source', 'empires' => 'Half-Life 2: Empires', 'hidden' => 'Hidden: Source', 'zombie_master' => 'Half-Life 2: Zombie Master', "svencoop" => "Half-Life 2: Svencoop", 'source' => "Half-Life 2: Multiplayer");
             }

             return array();
        }


	         public static function replaceGameTypesForAddons($type, $game)
	         {
	            global $vbphrase;

	            if($type != $game)
				 {
					return $type.'_'.$game;
				}
				return $type;
	        }
	
	 /*========================================================================
         *
	 * Обновление профиля
	 *
	 */

         public static function vbg_update_userinfo($userid = '')
         {
            global $vbulletin;

             if($userid == '')
             {
                  $userid = $vbulletin->userinfo['userid'];
             }

             $get_counter = $vbulletin->db->query_read("SELECT count(id) FROM " . TABLE_PREFIX ."vbgamez WHERE userid = '" . intval($userid) . "' AND disabled = 0");

             $counter = $vbulletin->db->fetch_row($get_counter);

             $vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET servers = '" . intval($counter[0]) . "' WHERE userid = '" . intval($userid) . "'");

          }

	 /*========================================================================
         *
	 * Получение тип игр
	 *
	 */

	 public static function fetch_game_types($foredit = '')
	 {

   	 if($foredit == true)
   	 {
         	 global $lookup;

         	 $game_types = '<option value="">---</option>';

         	 foreach(vbgamez_type_list() AS $key => $type)
         	 {
              	           $game_types .= '<option value="' . $key . '" ' . iif($lookup['type'] == $key, 'selected="selected"') . '>' . $type . '</option>';
         	 }

   	 }else{

         	 $game_types = '<option value="">---</option>';

         	 foreach(vbgamez_type_list() AS $key => $type)
         	 {
              	           $game_types .= '<option value="' . $key . '">' . $type . '</option>';
         	 }
   	 }
   	    return $game_types;

 	 }

	 /*========================================================================
         *
	 * Конструирование быстрого перехода по серверам
	 *
	 */

         public static function construct_servers_jump($cached_servers)
         {

                 global $vbulletin, $vbphrase;

                 if(!$vbulletin->options['vbgamez_jump'] OR !$cached_servers)
                 { 
                     return false;
                 }

                 foreach($cached_servers AS $server)
                 {
                        if(vB_vBGamez::is_vb4())
                        {
                              $templater = vB_Template::create('vbgamez_jump_link');
                              $templater->register('name', vB_vBGamez::vbgamez_string_html($server['cache_name']));
                              $templater->register('id', $server['id']);
                              $serverbits .= $templater->render();
                        }else{
                              eval('$serverbits .= "' . fetch_template('vbgamez_jump_link') . '";');
                        }
                 }

                 if(vB_vBGamez::is_vb4())
                 {
                              $templater = vB_Template::create('vbgamez_jump');
                              $templater->register('serverbits', $serverbits);

                              return $templater->render();
                 }else{
                              eval('$return = "' . fetch_template('vbgamez_jump') . '";');
                              return $return;
                 }
          
         }

	 /*========================================================================
         *
	 * Определение кодировки
	 *
	 */

         public static function vbgamez_charset_detect($server)
         {
                    if (!function_exists("mb_detect_encoding")) { return "AUTO"; }

                    $test = $server['s']['name'];

                    if (is_array($server['p']))
                    {
                      foreach ($server['p'] as $player)
                      {
                        $test .= " {$player['name']}";
                      }
                    }

                    $charset = @mb_detect_encoding($server['s']['name'], "UTF-8, Windows-1252, ISO-8859-1, ISO-8859-15");

                    return $charset ? $charset : "AUTO";
         }

	 /*========================================================================
         *
	 * Конвертирование информации о сервере
	 *
	 */

         public static function vbgamez_charset_convert($server, $charset)
         {
                 if(function_exists('iconv'))
                 { 
                            return vB_vBGamez::vbgamez_charset_convert_iconv($server, $charset);
                 }elseif(function_exists('mb_convert_encoding'))
                 {
                            return vB_vBGamez::vbgamez_charset_convert_mbstring($server, $charset);
                 }else{
                            return $server;
                 }
         }


	 /*========================================================================
         *
	 * Конвертирование информации о сервере - mbstring
	 *
	 */

         public static function vbgamez_charset_convert_mbstring($server, $charset)
         {
                  if (!function_exists("mb_convert_encoding")) { return $server; }

                  if (is_array($server))
                  {
                    foreach ($server as $key => $value)
                    {
                      $server[$key] = vB_vBGamez::vbgamez_charset_convert_mbstring($value, $charset);
                    }
                  }else{

                    $server = @mb_convert_encoding($server, "UTF-8", $charset);

                  }

               return $server;
         }

	 /*========================================================================
         *
	 * Конвертирование информации о сервере - iconv
	 *
	 */

         public static function vbgamez_charset_convert_iconv($server, $charset)
         {
                  if (!function_exists("iconv")) { return $server; }

                  if (is_array($server))
                  {
                    foreach ($server as $key => $value)
                    {
                      $server[$key] = vB_vBGamez::vbgamez_charset_convert_iconv($value, $charset);
                    }
                  }else{

                    $server = iconv($charset, "UTF-8", $server);

                  }

               return $server;
         }

	 /*========================================================================
         *
	 * Проверка ID и получение информации о сервере
	 *
	 */

	 public static function vbgamez_verify_id($id, $skipcheck = false, $fetch_username = false)
	 {
                  global $vbulletin, $vbphrase;

                  if($skipcheck == false)
                  {
                                           if(!can_administer())
                                           {
                                                   $where_sql_query = " AND disabled = 0 AND valid = 0 ";
                                           }
                  }

                  if($fetch_username)
                  {
                                           $select_fields = ', usergroup.*, user.username, user.userid, user.usergroupid, user.membergroupids, user.displaygroupid';
                                           $on_sql_query = " LEFT JOIN " . TABLE_PREFIX . "user AS user ON (user.userid = vbgamez.userid) ";
                                           $on_sql_query .= " LEFT JOIN " . TABLE_PREFIX . "usergroup AS usergroup ON (user.usergroupid = user.usergroupid) ";
                  }

                  $query = $vbulletin->db->query_read("SELECT vbgamez.* $select_fields FROM " . TABLE_PREFIX . "vbgamez AS vbgamez 
                                                       $on_sql_query
                                                       WHERE id = '" . intval($id) . "' " . $where_sql_query . " LIMIT 1");
                  return $vbulletin->db->fetch_array($query);
	 }


	 /*========================================================================
         *
	 * Получение страницы на которой находится комментарий
	 *
	 */

         public static function vbgamez_get_comment_page($serverid, $commentid, $type = 'add')
         {
                  global $vbulletin, $vbphrase, $db;

                  $select_server = $db->query("SELECT comments FROM " . TABLE_PREFIX . "vbgamez WHERE id = '" . intval($serverid) . "'");
                  $server = $db->fetch_array($select_server);
                  $total_comments = $server['comments'];

                  if($type == 'add')
                  {
                                    $page = ceil($total_comments / $vbulletin->options['vbgamez_comments_perpage']);
                  }else{

                                    $select_comments = $db->query("SELECT * FROM " . TABLE_PREFIX . "vbgamez_comments WHERE serverid = '" . intval($serverid) . "' ORDER by dateline");
                                    while($comment = $db->fetch_array($select_comments))
                                    {
                                          $comments++;
                                          if($comment['id'] == $commentid)
                                          {
                                                $page = ceil($comments / $vbulletin->options['vbgamez_comments_perpage']);
                                          }
                                    }
                  }
                    if($page == '0' OR $page == '-1')
                    {
                        $page = 1;
                    }

              return $page;
         } 

	 /*========================================================================
         *
	 * Отображение изображения карты
	 *
	 */

         public static function vbgamez_show_map($filename)
         {
             $ext = strtolower(strrchr(basename($filename), ".")); 
             $exts = array('.jpg', '.gif', '.png', '.bmp');
 
             if(!in_array($ext, $exts)) { return false; }

                 switch ($ext) {
                     case '.jpg': header('Content-Type: image/jpg'); $im = @imagecreatefromjpeg($filename); @imagejpeg($im, NULL, 100);
                         break;
                
                     case '.gif': header('Content-Type: image/gif'); $im = @imagecreatefromgif($filename); @imagegif($im);
                         break;
                
                     case '.png': header('Content-Type: image/png'); $im = @imagecreatefrompng($filename); @imagepng($im);
                         break;
                
                     case '.bmp': header('Content-Type: image/bmp'); $im = @imagecreatefromwbmp($filename); @imagewbmp($im);
                         break;
                     }  

             @imagedestroy($im);         

         }

	 /*========================================================================
         *
	 * Создание изображения юзербара
	 *
	 */

         public static function vbgamez_fetch_userbar_image($filename)
         {
             $ext = strtolower(strrchr(basename($filename), ".")); 
             $exts = array('.jpg', '.gif', '.png', '.bmp');
 
             if(!in_array($ext, $exts)) { return false; }

                 switch ($ext) {
                     case '.jpg': header('Content-Type: image/jpg'); $im = @imagecreatefromjpeg($filename); 
                         break;
                
                     case '.gif': header('Content-Type: image/gif'); $im = @imagecreatefromgif($filename); 
                         break;
                
                     case '.png': header('Content-Type: image/png'); $im = @imagecreatefrompng($filename); 
                         break;
                
                     case '.bmp': header('Content-Type: image/bmp'); $im = @imagecreatefromwbmp($filename); 
                         break;
                     }  

             return $im;         

          }

	 /*========================================================================
         *
	 * Создание изображения юзербара без хидера
	 *
	 */

         public static function vbgamez_fetch_userbar_image_without_header($filename)
         {
             $ext = strtolower(strrchr(basename($filename), ".")); 
             $exts = array('.jpg', '.gif', '.png', '.bmp');
 
             if(!in_array($ext, $exts)) { return false; }

                 switch ($ext) {
                     case '.jpg': $im = @imagecreatefromjpeg($filename); 
                         break;
                
                     case '.gif': $im = @imagecreatefromgif($filename); 
                         break;
                
                     case '.png': $im = @imagecreatefrompng($filename); 
                         break;
                
                     case '.bmp': $im = @imagecreatefromwbmp($filename); 
                         break;
                     }  

             return $im;         

          }

	 /*========================================================================
         *
	 * Отображение изображения юзербара
	 *
	 */

         public static function vbgamez_print_userbar_image($im, $filename, $quality = 100)
         {
             $ext = strtolower(strrchr(basename($filename), ".")); 
             $exts = array('.jpg', '.gif', '.png', '.bmp');
 
             if(!in_array($ext, $exts)) { return false; }

                 switch ($ext) {
                     case '.jpg': @imagejpeg($im, NULL, $quality);
                         break;
                
                     case '.gif': @imagegif($im);
                         break;
                
                     case '.png': @imagepng($im);
                         break;
                
                     case '.bmp': @imagewbmp($im);
                         break;
                     }  

          }

	 /*========================================================================
         *
	 * Изменение размера изображения 
	 *
	 */

	 public static function vbgamez_resize_image($im, $filename, $size = 300, $quality = 85, $param1, $param2, $param3, $param4)
	 {
    	 	 $ext = strtolower(strrchr(basename($filename), "."));
    	 	 $exts = array('.jpg', '.gif', '.png', '.bmp');
        
    	 	 if (in_array($ext, $exts)) {           
        	 $percent = $size; 
    
        	 list($width, $height) = getimagesize($filename);
        	 $newheight = $height * $percent;

        	 $newwidth = @($newheight / $width); 

        	 $thumb = @imagecreatetruecolor($percent, $newwidth);
        	 switch ($ext) {
            	 case '.jpg': $source = @imagecreatefromjpeg($filename); break;
            	 case '.gif': $source = @imagecreatefromgif($filename); break;
            	 case '.png': $source = @imagecreatefrompng($filename); break;
            	 case '.bmp': $source = @imagecreatefromwbmp($filename); break;
        	 }

        	 return @imagecopyresized($im, $source, $param1, $param2, $param3, $param4, $percent, $newwidth, $width, $height);
   	 	 }          
 	 }

	 /*========================================================================
         *
	 * Вывод текста с определнием используется ли AJAX
	 *
	 */

         public static function print_or_standard_error($error)
         {
                global $vbulletin, $vbphrase;

                if($vbulletin->GPC['ajax'])
                {
                          if(vB_vBGamez::is_vb4())
                          {
                                 construct_quick_nav();
                          }else{
                                 construct_forum_jump();
                          }

                          print $error; exit;
                }else{
                          standard_error($error);  
                }
         }

	 /*========================================================================
         *
	 * Получение URLs юзербаров
	 *
	 */

         public static function fetch_codes_url($type, $id, $sid = '')
         {
                global $vbulletin, $vbphrase;

                if($type == 'iframe')
                {  
                                    $url = $vbulletin->options['vbgamez_path'].'?do=iframe&server='.$id.'&sid='.$sid;
                }elseif($type == 'sig')
                {  
                                    $url = $vbulletin->options['vbgamez_path'].'?do=view&id='.$id;
                }elseif($type == 'sigimg')
                {  
                                    $url = $vbulletin->options['vbgamez_path'].'/userbar/'.$id.'/'.$sid.'.jpg';
                }

                   return $url;
         }

	 /*========================================================================
         *
	 * Конструирование всплыв. окна модерации
	 *
	 */

         public static function construct_moderation_popup()
         {
                  global $vbulletin, $vbphrase;

                  if(vB_vBGamez::is_vb4())
                  {
                        return vB_Template::create('vbgamez_moderation_popup')->render();
                  }else{
                       eval('$html .= "' . fetch_template('vbgamez_moderation_popup') . '";');  
                       return $html;
                  }
         }

	 /*========================================================================
         *
	 * Конструирование действий всплыв. окна модерации 
	 *
	 */

         public static function construct_moderation_popup_actions()
         {
                  global $vbulletin, $vbphrase;

                  if(vB_vBGamez::is_vb4())
                  {
                        return vB_Template::create('vbgamez_moderation_actions')->render();
                  }else{
                       eval('$html .= "' . fetch_template('vbgamez_moderation_actions') . '";');  
                       return $html;
                  }
         }

	 /*========================================================================
         *
	 * Отображение статуса сервера
	 *
	 */
		function getServerStatuses()
		{
			global $vbphrase;
			return array(0 => $vbphrase['vbgamez_admincp_server_in_list'], 1 => $vbphrase['vbgamez_admincp_server_no_in_list'], 2 => $vbphrase['vbgamez_admincp_server_no_moderate']);
		}
         public static function fetch_server_status($status)
         {
                   $statuses = self::getServerStatuses();
					return $statuses[$status];
         }

	 /*========================================================================
         *
	 * Обновление счетчика комментариев
	 *
	 */

	 public static function update_count_of_comments($serverid)
	 {
    	 	 global $vbulletin;

    	 	 $get_counter = $vbulletin->db->query_read("SELECT count(id) FROM " . TABLE_PREFIX ."vbgamez_comments WHERE serverid = '" . intval($serverid) . "' AND deleted = 0 AND onmoderate = 0");

    	 	 $counter = $vbulletin->db->fetch_row($get_counter);

        	 $vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "vbgamez SET comments = '" . intval($counter[0]) . "' WHERE id = '" . intval($serverid) . "'");

         }

	 /*========================================================================
         *
	 * Получение кол-ва комментариев с учетом удаленных и неопубликованных
	 *
	 */

	 public static function fetch_count_comments_with_nopublished($id)
	 {
                 global $vbulletin, $vbphrase;

    	 	 $get_counter = $vbulletin->db->query_read("SELECT count(id) FROM " . TABLE_PREFIX ."vbgamez_comments WHERE serverid = '" . intval($id) . "'");

    	 	 $counter = $vbulletin->db->fetch_row($get_counter);
    	 	 return $counter[0];
	 }

	 /*========================================================================
         *
	 * Получение кол-ва всех комментариев на модерации
	 *
	 */

	 public static function fetch_count_comments_on_moderation()
	 {
                 global $vbulletin, $vbphrase;

    	 	 $get_counter = $vbulletin->db->query_read("SELECT count(id) FROM " . TABLE_PREFIX ."vbgamez_comments WHERE onmoderate = 1");

    	 	 $counter = $vbulletin->db->fetch_row($get_counter);
    	 	 return $counter[0];
	 }

	 /*========================================================================
         *
	 * Получение кол-ва всех удаленных комментариев 
	 *
	 */

	 public static function fetch_count_comments_deleted()
	 {
                 global $vbulletin, $vbphrase;

    	 	 $get_counter = $vbulletin->db->query_read("SELECT count(id) FROM " . TABLE_PREFIX ."vbgamez_comments WHERE deleted = 1");

    	 	 $counter = $vbulletin->db->fetch_row($get_counter);
    	 	 return $counter[0];
	 }

	 /*========================================================================
         *
	 * Проверка прав на просмотр комментария
	 *
	 */

	 public static function can_view_comment($server, $comment)
	 {
                 global $vbulletin, $vbphrase;

     	 	 return iif($comment['userid'] == $vbulletin->userinfo['userid'], true, vB_vBGamez::vbg_check_delete_comments_permissions($comment['serverid'], $server['i']['userid']));
	 }

	 /*========================================================================
         *
	 * Проверка прав
	 *
	 */

	 public static function check_permissions($serverinfo = NULL)
	 {
                 global $vbulletin, $vbphrase;

          	 if(can_administer())
          	 {
                	 return false;
          	 }

          	 if(vB_vBGamez::vbg_can_moderate_comments())
          	 {
                	 return false;
          	 }

                 if($vbulletin->options['vbgamez_send_pm_to_admin'])
                 {
                      if(!empty($serverinfo['userid']) AND $serverinfo['userid'] == $vbulletin->userinfo['userid'])
                      {
                              // не прально: вдруг админ форума захочет написать что написал админ сервера? ну вот...
                               #return false;
                      }
                 }

          	 return true;
	 }

	 /*========================================================================
         *
	 * Может ли юзер модерировать все комменты?
	 *
	 */

	 public static function check_moderate_permissions()
	 {
                 if(!$vbulletin->userinfo['userid']) 
                 {
                         //return false;
                 }

          	 if(can_administer())
          	 {
                	 return true;
          	 }

          	 if(vB_vBGamez::vbg_can_moderate_comments())
          	 {
                	 return true;
          	 }

          	 return false;
	 }

	 /*========================================================================
         *
	 * Проверка: нужно ли отправлять комментарий на модерацию
	 *
	 */

	 public static function moderate_comment_before_add($serverinfo = null)
	 {
                 global $vbulletin, $vbphrase;

     	 	 if($vbulletin->options['vbgamez_comments_moderation'])
     	 	 {
          	 	 if(can_administer())
          	 	 {
                	            return true;
          	         }

          	         if(vB_vBGamez::vbg_can_moderate_comments())
          	         {
                	            return true;
          	         }

          	         if(!empty($serverinfo['userid']) AND $serverinfo['userid'] == $vbulletin->userinfo['userid'])
          	         {
                	         return true;
          	         }

          	 	 return false;
     	 	 }else{
          	 	 return true;
     	 	 }
	 }

	 /*========================================================================
         *
	 * Конвертирование имени сервера
	 *
	 */

          public static function vbgamez_string_html($string, $skip_html = false)
          {
             if(!$skip_html)
             {
                       $string = htmlspecialchars($string, ENT_QUOTES);
             }

             if(function_exists("mb_convert_encoding"))
             {
                    return @mb_convert_encoding($string, "HTML-ENTITIES", "UTF-8");
             }else if(function_exists('iconv'))
             {
                    return iconv('UTF-8', vB_vBGamez::fetch_stylevar('charset'), $string);
             }else{
                    return htmlentities($string, ENT_QUOTES);
             }
          }

	 /*========================================================================
         *
	 * Конвертирование массива информации о сервере
	 *
	 */

          public static function vbgamez_server_html($server)
          {

           if (isset($server['e']) && is_array($server['e']))
           {
             foreach ($server['e'] as $key => $value)
             {
               $server['e'][$key] = vB_vBGamez::vbgamez_word_wrap($value, 90);
             }
           }

           foreach ($server as $key => $value)
           { 
             if (@strpos($value, "img src"))
             {
                   continue;
             }

             $server[$key] = is_array($value) ? vB_vBGamez::vbgamez_server_html($value) : vB_vBGamez::vbgamez_string_html($value);
           }

           return $server;
         }


	 /*========================================================================
         *
	 * Конвертирование текста
	 *
	 */

         public static function vbgamez_word_wrap($string, $length_limit)
         {
           $words = explode(" ", $string);

           foreach ($words as $word)
           {
             $word_length = function_exists("mb_strlen") ? mb_strlen($word, "UTF-8") : strlen($word);

             if ($word_length < $length_limit)
             {
               $words_new[] = $word;
             }
             else
             {
               for ($i=0; $i<$word_length; $i+=$length_limit)
               {
                 $words_new[] = function_exists("mb_substr") ? mb_substr($word, $i, $length_limit, "UTF-8") : substr($word, $i, $length_limit);
               }
             }
           }

           return implode(" ",       $words_new);

        }


	 /*========================================================================
         *
	 * Таймер ожидания подключения к серверу
	 *
	 */

         public static function vbgamez_timer($action)
         {
                  global $vbgamez_timer;
                  global $vbulletin;

           if (!$vbgamez_timer)
           {
             $microtime  = microtime();
             $microtime  = explode(' ', $microtime);
             $microtime  = $microtime[1] + $microtime[0];
             $vbgamez_timer = $microtime - 0.01;
           }

           $time_limit = intval($vbulletin->options['vbgamez_time']);
           $time_php   = ini_get("max_execution_time");

           if ($time_limit > $time_php)
           {
             @set_time_limit($time_limit + 5);

             $time_php = ini_get("max_execution_time");

             if ($time_limit > $time_php)
             {
               $time_limit = $time_php - 5;
             }
           }

           if ($action == "limit")
           {
             return $time_limit;
           }

           $microtime  = microtime();
           $microtime  = explode(' ', $microtime);
           $microtime  = $microtime[1] + $microtime[0];
           $time_taken = $microtime - $vbgamez_timer;

           if ($action == "check")
           {
             return ($time_taken > $time_limit) ? TRUE : FALSE;
           }
           else
           {
             return round($time_taken, 2);
           }
         }

	 /*========================================================================
         *
	 * Получение информации о сервере
	 *
	 */

         public static function vbgamez_server_misc($server)
         {
           $misc['icon_game']          = vB_vBGamez::vbgamez_icon_game($server['b']['type'], $server['s']['game']);

           $misc['image_map']          = vB_vBGamez::vbgamez_image_map($server['b']['status'], $server['b']['type'], $server['s']['game'], $server['s']['map']);
           $misc['image_map_password'] = vB_vBGamez::vbgamez_image_map_password($server['b']['status'], $server['s']['password']);

           $misc['text_status']        = vB_vBGamez::vbgamez_text_status($server['b']['status'], $server['s']['password'], $server['b']['pending']);
           $misc['text_type_game']     = vB_vBGamez::vbgamez_text_type_game($server['b']['type'], $server['s']['game']);
           $misc['text_game']          = vB_vBGamez::vbgamez_text_game($server['b']['type'], $server['s']['game']);
           $misc['image_status']       = vB_vBGamez::vbgamez_image_status($server['b']['status'], $server['s']['password'], $server['b']['pending']);
           $misc['has_map_image']       = vB_vBGamez::hasImage($misc['image_map']);
           $misc['location_image']       = vB_vBGamez::hasLocationImage($server['i']['location']);

           return $misc;
         }       

	 public static function hasImage($imageUrl)
	 {
			if($imageUrl == vB_vBGamez::fetch_image("images/vbgamez/map_no_response.jpg"))
			{
				return false;
			}elseif($imageUrl == vB_vBGamez::fetch_image("images/vbgamez/map_no_image.jpg"))
			{
				return false;
			}
			return true;
	 }
	 public static function hasLocationImage($location)
	 {
			if(!vB_vBGamez::vb_call()->options['vbgamez_server_location_enable'])
			{
				return false;
			}
			if(!$location)
			{
				return false;
			}

			$image_url = vB_vBGamez::fetch_image("images/vbgamez/locations/$location.png");
			if(file_exists($image_url))
			{
				return $image_url;
			}
	 }
	 /*========================================================================
         *
	 * Получение иконки игры сервера
	 *
	 */

         public static function vbgamez_icon_game($type, $game)
         {
           global $vbulletin, $vbphrase;

           $type = preg_replace("/[^A-Za-z0-9_]/", "_", strtolower($type));
           $game = preg_replace("/[^A-Za-z0-9_]/", "_", strtolower($game));


           $location = array(
           "images/vbgamez/icons/{$type}/{$game}.gif",
           "images/vbgamez/icons/{$type}/{$game}.png",
           "images/vbgamez/icons/{$type}/{$type}.gif",
           "images/vbgamez/icons/{$type}/{$type}.png");

           foreach ($location as $path)
           {
             if (file_exists($path)) { return vB_vBGamez::fetch_image($path); }
           }

             // получаем Stylevar'ы
             vB_vBGamez::fetch_stylevars();

             if(file_exists(vB_vBGamez::fetch_stylevar('imgdir_misc').'/question_icon.gif'))
             {
                       return vB_vBGamez::fetch_image(vB_vBGamez::fetch_stylevar('imgdir_misc').'/question_icon.gif');
             }else{
                       return vB_vBGamez::fetch_image('images/misc/question_icon.gif');
             }

         }

	 /*========================================================================
         *
	 * Получение изображение статуса
	 *
	 */

         public static function vbgamez_image_status($status, $password, $pending = 0)
         {
           global $vbulletin, $vbphrase;

           // получаем Stylevar'ы
           vB_vBGamez::fetch_stylevars();

           if(vB_vBGamez::is_vb4())
           {
                      if ($pending)
                      {
                                  return vB_vBGamez::fetch_image(vB_vBGamez::fetch_stylevar('imgdir_statusicon').'/user-invisible.png');
                      }

                      if (!$status)
                      {
                                  return vB_vBGamez::fetch_image(vB_vBGamez::fetch_stylevar('imgdir_statusicon').'/user-offline.png');
                      }

                      if ($password)
                      {
                                  return vB_vBGamez::fetch_image(vB_vBGamez::fetch_stylevar('imgdir_statusicon').'/user-invisible.png');
                      } 

                      return vB_vBGamez::fetch_image(vB_vBGamez::fetch_stylevar('imgdir_statusicon').'/user-online.png');
           }else{
                      if ($pending)
                      {
                                  return vB_vBGamez::fetch_image(vB_vBGamez::fetch_stylevar('imgdir_statusicon').'/user_invisible.gif');
                      }

                      if (!$status)
                      {
                                  return vB_vBGamez::fetch_image(vB_vBGamez::fetch_stylevar('imgdir_statusicon').'/user_offline.gif');
                      }

                      if ($password)
                      {
                                  return vB_vBGamez::fetch_image(vB_vBGamez::fetch_stylevar('imgdir_statusicon').'/user_invisible.gif');
                      } 

                      return vB_vBGamez::fetch_image(vB_vBGamez::fetch_stylevar('imgdir_statusicon').'/user_online.gif');
            }

         }

	 /*========================================================================
         *
	 * Получение изображение карты
	 *
	 */

         public static function vbgamez_image_map($status, $type, $game, $map, $check_exists = TRUE)
         {
           global $vbulletin, $vbphrase;

           if (!$status)
           {
             return vB_vBGamez::fetch_image("images/vbgamez/map_no_response.jpg");
           }

           $map = str_replace('$', '_', $map);
           $map = str_replace('&', '_amp_', $map);
           $map = str_replace('&amp;', '_amp_', $map);

           $type = preg_replace("/[^A-Za-z0-9_]/", "_", strtolower($type));
           $game = preg_replace("/[^A-Za-z0-9_]/", "_", strtolower($game));
           $map  = preg_replace("/[^A-Za-z0-9_]/", "_", strtolower($map));

           if (file_exists("images/vbgamez/maps/{$type}/{$game}/{$map}.jpg") || $check_exists == FALSE)
           {
             return vB_vBGamez::fetch_image("images/vbgamez/maps/{$type}/{$game}/{$map}.jpg");
           }

           if ($status)
           {
             $location = array(
             "images/vbgamez/maps/{$type}/{$game}/{$map}.jpg",
             "images/vbgamez/maps/{$type}/{$game}/{$map}.gif",
             "images/vbgamez/maps/{$type}/{$game}/{$map}.png",
             "images/vbgamez/maps/{$type}/{$map}.jpg",
             "images/vbgamez/maps/{$type}/{$map}.gif",
             "images/vbgamez/maps/{$type}/{$map}.png",
             "images/vbgamez/maps/{$type}/map_no_image.jpg",
             "images/vbgamez/maps/{$type}/map_no_image.gif",
             "images/vbgamez/maps/{$type}/map_no_image.png",
             "images/vbgamez/map_no_image.jpg");
           }
           else
           {
             $location = array(
             "images/vbgamez/maps/{$type}/map_no_response.jpg",
             "images/vbgamez/maps/{$type}/map_no_response.gif",
             "images/vbgamez/maps/{$type}/map_no_response.png",
             "images/vbgamez/map_no_response.jpg");
           }

           foreach ($location as $path)
           {
             if (file_exists($path)) { return vB_vBGamez::fetch_image($path); }
           }

           return vB_vBGamez::fetch_image("images/vbgamez/map_no_image.jpg");
         }

	 /*========================================================================
         *
	 * Получение изображение карты с паролем
	 *
	 */

         public static function vbgamez_image_map_password($status, $password)
         {
           global $vbulletin, $vbphrase;

           if (!$password || !$status)
           {
             return vB_vBGamez::fetch_image("images/vbgamez/map_overlay.gif");
           }

           return vB_vBGamez::fetch_image("images/vbgamez/map_overlay_password.gif");
         }

	 /*========================================================================
         *
	 * Получение текствого статуса
	 *
	 */

         public static function vbgamez_text_status($status, $password, $pending = 0)
         {
           global $vbulletin, $vbphrase;

           if ($pending)
           {
             return $vbphrase['vbgamez_wait_connect'];
           }

           if (!$status)
           {
             return $vbphrase['vbgamez_server_offline'];
           }

           if ($password)
           {
             return $vbphrase['vbgamez_server_online_with_password'];
           }

           return $vbphrase['vbgamez_server_online'];
         }       

	 /*========================================================================
         *
	 * Получение текстового имени сервера
	 *
	 */

         public static function vbgamez_text_type_game($type, $game)
         {
           global $vbulletin, $vbphrase;

           $gametypes = vbgamez_type_list();

	   if($gametypes[$type] == vB_vBGamez::vbgamez_text_game($type, $game))
	   {
           	return $gametypes[$type];
	   }else{
		return vB_vBGamez::vbgamez_text_game($type, $game);
           }
         }

	 /*========================================================================
         *
	 * Получение текстового имени сервера
	 *
	 */

         public static function vbgamez_text_game($type, $game)
         {
           global $vbulletin, $vbphrase;

			if($customname = self::renameGameType($type, $game))
			{
				return $customname;
			}
	
            $realgamename = vB_vBGamez::fetch_additional_game_type($type);
            foreach($realgamename AS $gameid => $gamename)
            {
                      if($gameid == $game)
                      { 
                               return $gamename;

                      }
            }


             $gametypes = vbgamez_type_list();

             return $gametypes[$type];

         }

		public static function renameGameType($type, $game)
		{
			global $vbulletin;
			
           $explode_all = explode("\r\n", $vbulletin->options['vbgamez_replace_games']);

           if(!empty($explode_all))
 			{
           		foreach($explode_all AS $key => $value)
           		{
                          $explode = explode('|', $value);

						  $testExplode = explode('_', $explode[0]);
						  if(count($testExplode) > 1)
						  {
							 $check_type = $testExplode[0];
							 $check_game = $testExplode[1];
							 $check_all = true;
							 if(empty($check_game))
							 {
								$check_all = false;
							}
					      }else{
							 $check_all = false;
						  }

						  if($check_all == true)
						  {
								if($type == $check_type AND $check_game == $game)
								{
                          				return $explode[1];
								}
						 }elseif($explode[0] == $type){
                          		return $explode[1];
						 }
           			}
			}
			return '';
		}

	 /*========================================================================
         *
	 * Фильтрация имени игрока
	 *
	 */

         public static function vbgamez_name_filtered($name)
         {
           $name = preg_replace("/[^\x20-\x7E]/", "", $name); // x20-x7E IS HEX FOR ASCII RANGE
           $name = vB_vBGamez::vbgamez_string_html($name);

           return $name;
         }

	// TODO
	public static function substr($str,$len)
	{
		return $str;
	} 

	public static function unUTF8($text)
	{
		if(vB_vBGamez::fetch_stylevar('charset') == 'UTF-8')
		{
			return $text;
		}else{
			return iconv('UTF-8', 'windows-1251', $text);
		}
	}
	public static function toUTF8($text)
	{
		if(vB_vBGamez::fetch_stylevar('charset') == 'UTF-8')
		{
			return $text;
		}else{
			return iconv('windows-1251', 'UTF-8', $text);
		}
	}
	public static function getServerName($name)
	{
		trigger_error('called older function getServerName');

		global $vbphrase;
		$name = trim($name);
		if(vbstrlen($name) == 0 OR $name == '')
		{
			return $vbphrase['vbgamez_server_no_response'];
		}else{
			return $name;
		}
	}
	
	public static function jsonEncode($data)
	{
		if(function_exists('json_encode'))
        {
              return json_encode($data);
        }else{
              require_once(DIR .'/packages/vbgamez/3rd_party_classes/json/JSON.php');
              $json = new Services_JSON();
              return $json->encode($data);                
        }
	}
	public static function jsonDecode($data)
	{
		if(function_exists('json_decode'))
        {
              return json_decode($data);
        }else{
              require_once(DIR .'/packages/vbgamez/3rd_party_classes/json/JSON.php');
              $json = new Services_JSON();
              return $json->decode($data);                
        }
	}
	

	public static function getExcludeGameTypesQuery($type)
	{
		$additional_types = vB_vBGamez::fetch_additional_game_type($type);
		$exclude_gametypes = array();
		$db_query = '';
		foreach($additional_types AS $type => $name)
		{
			$exclude_gametypes[] = vB_vBGamez::vb_call()->db->sql_prepare($type);
		}
		if(!empty($exclude_gametypes))
		{
			$db_query = ' AND vbgamez.cache_game NOT IN (' . implode(',', $exclude_gametypes) . ') ';
		}
		return $db_query;
	}
	
	public static function integrateGameType($type, $game)
	{
		if(($type == 'source' AND $game == 'source'))
		{
			return array('source', 'hl2mp');
		}
		if($type == 'bf1942')
		{
			return array('bf1942', 'bf1942');
		}
		return array();
	}
	public static function integrateGameType2($type, $game)
	{
		if($type == 'source' AND $game == 'hl2mp')
		{
			return array('source', 'source');
		}
		return array();
	}
	
	// Render Into vbgamez-bad template :( 
	// I dont like div-based html
	function prepareHumanVerifyRender($humanverify)
	{
		return str_replace('<div class="blockrow">', '<div class="vbg_human">', $humanverify);
	}
}
// PHP BUG: cant call usort in Class::method
function vBGamez_SortArray($array, $callback = 'vB_vBGamez_Sorter::method')
{
    		 if (!is_array($array))
		 {
			 return $array;
		 }

		 $_callback = explode('::', $callback);
		 if(!$_callback[1])
		 {
			$call_method = $callback;
	 	 }else{
			$call_method = array($_callback[0], $_callback[1]);
		 }

		 usort($array, $call_method);

    		 return $array;
}

function SortServerFields($server)
{
    		 if (!is_array($server['p']))
		 {
			 return $server;
		 }

                 vB_vBGamez::loadClassFromFile('sorter');

                 $sort_fields = array("name","score","deaths","team","ping","bot","time","race","class","level","fr","teamindex","pid","pbid");

                 if(in_array($_REQUEST['sort_by_field'], $sort_fields))
		 {
			$call_method = 'by_' . $_REQUEST['sort_by_field'];
			usort($server['p'], array('vB_vBGamez_Sorter', $call_method));
		 }

    		return $server;
}

?>
