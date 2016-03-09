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

/**
 * VBGamEz генерация юзербара
 *
 * @package vBGamEz
 * @author GiveMeABreak aka Developer, LGSL author
 * @version $Revision: 200 $
 * @copyright GiveMeABreak
 */

class vBGamez_Userbar
{
	static $initilizated = false;
         function isGraphicString($string)
         {
			  return FALSE; // TODO
	          $string = trim($string);

	          if(substr($string, 0, 8) == '{graphic' AND substr($string, vbstrlen($string) -1, 1) == '}')
	          {
		         return true;
	          }
	          return false;
         }

	 /*========================================================================
         *
	 * Инитилизация
	 *
	 */

         function init($type = '', $game = '')
         {
				if(self::$initilizated == false)
				{
					self::$initilizated = true;

                	global $vbulletin, $vbphrase, $db;

                	$type = vB_vBGamez::parse_game($type);
                	$game = vB_vBGamez::parse_game($game);
                	$gamesql = $this->replace_games($type, $game);

                	if($game == $gamesql)
                	{
                        $sql_query_add = "OR fieldname LIKE " . $db->sql_prepare('%'.$type.'%') . "";
                	}

                	if($userid = $vbulletin->userinfo['userid'])
                	{
                          $user_query = "AND (userid = '0' OR userid = '$userid')";
                	}else{
                          	$user_query = 'AND userid = 0';
                		}

                	$get_userbars = $db->query("SELECT * FROM " . TABLE_PREFIX . "vbgamez_userbar
                                                WHERE (fieldname LIKE " . $db->sql_prepare('%global%') . " OR fieldname LIKE " . $db->sql_prepare('%'.$gamesql.'%') . " " . $sql_query_add . ")  $user_query " . iif(THIS_SCRIPT == 'vbgamez', 'AND enabled = 1') . "
                                                ORDER BY `order`");
                							while($userbar = $db->fetch_array($get_userbars))
                	{
                         $this->userbarcache[$userbar['userbarid']] = $userbar;
                	}
				}
         }

	 /*========================================================================
         *
	 * Нанесение текста на юзербар
	 *
	 */
         
         function generate_userbar_fields($im, $foreach)
         {          
                 if(empty($foreach)) { return false; }

                 foreach($foreach AS $config)
                 { 
		     if($_REQUEST['id'] == $config['configid'])
		     {
				continue;
		     }

                     if($config['text'] == '{mapimage}')
                     {
                            $mapimage = vB_vBGamez::vbgamez_image_map($this->serverinfo['b']['status'], $this->serverinfo['b']['type'], $this->serverinfo['s']['game'], $this->serverinfo['s']['map']);

                            vB_vBGamez::vbgamez_resize_image($im, $mapimage, iif($config['width'], $config['width'], '45'), 100, $config['repeat_x'], $config['repeat_y'], 0, 0);

                     }else if($this->isGraphicString($config['text']))
                     {
							
                            global $vbulletin, $vbphrase;
		
          		   if(!$vbulletin->options['vbgamez_graphics'])
          		   {
                 		   continue;
          		   }

			   vB_vBGamez::loadClassFromFile('statistics');

          		   $id = intval($_REQUEST['id']);

          		   $lookup = $this->additionalinfo;

			   $_REQUEST['type'] = 1;

          		   if(!$lookup) { continue; }

          		   $lookup['o']['id'] = $lookup['id'];
          		   $lookup['i']['statistics'] = $lookup['statistics'];
          		   $lookup['s']['players'] = $lookup['cache_players'];

          		   $instance = vBGamez_Stats_Display::create($vbulletin, $lookup);

          		   $stats = $instance->renderData($_REQUEST['type']);
          		   $phrase = $instance->getDisplayPhrase($_REQUEST['type']);
          		   $phrase_x = $instance->getXphrase($_REQUEST['type']);
          		   $phrase_y = $instance->getYphrase($_REQUEST['type']);
          		   $phrase_desc = $instance->getDescphrase($_REQUEST['type']);

          		   include("./packages/vbgamez/3rd_party_classes/pchart/pChart.class");  

          		   $desc = array("Position" => "Name", "Format"=> array("X" => "number", "Y" => "number"), "Unit" => array("X" => NULL, "Y" => ""),
                        		   "Values" => array(0 =>"Serie1"), "Description" => array("Serie1" => vB_vBGamez::vbg_set_charset($phrase_desc)),
                        		   "Axis" => array("Y" => vB_vBGamez::vbg_set_charset($phrase_y), "X" => vB_vBGamez::vbg_set_charset($phrase_x)));

	  		   if($stats == 'unknown')
	  		   {
				   continue;
	  		   }

          		   for($i=1; $i<count($stats['data']) + 1; $i++)
          		   {
                          		   $data[$i] = array('Serie1' => $stats['data'][$i], 'Name' => $instance->getXPositionsName($i, $_REQUEST['type']));
          		   }

	  		   $_REQUEST['width'] = intval($_REQUEST['width']);
	  		   $_REQUEST['height'] = intval($_REQUEST['height']);
	  		   if(!$_REQUEST['width'])
	  		   {
				   $_REQUEST['width'] = 100;
	  		   }
	  		   if(!$_REQUEST['height'])
	  		   {
				   $_REQUEST['height'] = 30;
	  		   }
 	  		   // Initialise the graph
 	  		   $Test = new pChart($_REQUEST['width'],$_REQUEST['height']);
 	  		   $Test->setFontProperties("./packages/vbgamez/3rd_party_classes/pchart/tahoma.ttf",8);
 	  		   $Test->drawFilledRoundedRectangle(2,2,$_REQUEST['width']-2,$_REQUEST['height']-2,2,230,230,230);
 	  		   $Test->setGraphArea(5,5,$_REQUEST['width']-5,$_REQUEST['height']-5);
 	  		   $Test->drawGraphArea(255,255,255);
 	  		   $Test->drawScale($data,$desc,SCALE_NORMAL,220,220,220,FALSE);

 	  		   // Draw the line graph
 	  		   $Test->drawLineGraph($data,$desc);

        		   imagecopy($im, $Test->Picture, 10,10,10,10,10,10);

                     }else if($config['text'] == '{icon}')
                     {

                            $gameimage = vB_vBGamez::vbgamez_icon_game($this->serverinfo['b']['type'], $this->serverinfo['s']['game']);

                            vB_vBGamez::vbgamez_resize_image($im, $gameimage, iif($config['width'], $config['width'], '16'), 100, $config['repeat_x'], $config['repeat_y'], 0, 0);

                     }else{
                            $config['text'] = vB_vBGamez::vbgamez_charset_convert($config['text'], vB_vBGamez::fetch_stylevar('charset'));

                            $bgcolors = explode(',', str_replace('rgb(', '', str_replace(')', '', iif($config['fontcolor'], $config['fontcolor'], $config['textcolor']))));

                            foreach($bgcolors AS $key => $val)
                            {
                                       $bgcolor[$key] = $val;
                            } 
                     
                            $color = imagecolorallocate($im, $bgcolor['0'], $bgcolor['1'], $bgcolor['2']);

                            if(!file_exists($config['font']))
                            {
                                            $config['font'] = '';
                            }

                            if(empty($config['font']) AND empty($config['defaultfont']))
                            {
                                         $config['defaultfont'] = 'images/vbgamez/fonts/userbar.ttf';
                            }

                            @imagettftext($im, iif($config['fontsize'], $config['fontsize'], $config['defaultfontsize']), $config['radius'], $config['repeat_x'], $config['repeat_y'], $color, iif($config['font'], $config['font'], $config['defaultfont']), $this->replace_variables($config['text'])); 
                     }

                     if($config['ispreview'])
                     { 
                                     global $vbulletin;

                                     require_once('./packages/vbgamez/manager/userbar.php');
                                     $userbar_dm = new vBGamEz_Userbar_Manager($vbulletin);
                                     $userbar_dm->delete_userbar_location($config['configid']);
                                     continue;
                     }
                 }
        }
        
        
	 /*========================================================================
         *
	 * Замена переменных
         * с {x} на $x
	 *
	 */

        function replace_variables($text)
        { 
               $serverinfo =& $this->serverinfo;
               $advinfo =& $this->additionalinfo;
				
			   if($advinfo['city'])
			   {
				      $country = $advinfo['country'].'-'.$advinfo['city'];
			   }elseif($advinfo['country'])
			   {
				      $country = $advinfo['country'];
			   }else{
					  global $vbphrase;
					  $country = $vbphrase['n_a'];
			   }
			   global $vbulletin;
			
			   $siteurl = str_replace(array('http://', 'www.'), '', $_SERVER['HTTP_HOST']);
			   if(strlen($siteurl) < 15)
				{
					$siteurl = '       '.$siteurl;
				}
               return str_replace(array('{ip}',
 					'{name}',
 					'{players}',
 					'{map}',
 					'{online}',
 					'{offline}',
 					'{rating}',
 					'{views}',
 					'{playerscount}',
 					'{playersmax}',
 					'{comments}', '{country}', '{siteurl}'),

						
                                        array($serverinfo['b']['ip'].":".$serverinfo['b']['c_port'], $serverinfo['s']['name'], $serverinfo['s']['players']."/".$serverinfo['s']['playersmax'], $serverinfo['s']['map'], iif($serverinfo['b']['status'], 'Online'), iif(!$serverinfo['b']['status'], 'Offline'), $advinfo['rating'], $advinfo['views'], $advinfo['cache_players'], $advinfo['cache_payersmax'], $advinfo['comments'], $country, $siteurl), $text);
        }

	 /*========================================================================
         *
	 * Замена типов игр
	 *
	 */

         function replace_games($type, $value)
         {
			// new
            return vB_vBGamez::replaceGameTypesForAddons($type, $value);
        }


	 /*========================================================================
         *
	 * Конструирование списка юзербаров для отображения в деталях сервера
	 *
	 */

        function construct_userbarbits($serverid)
        {
              global $vbphrase, $vbulletin, $show;

              $this->init($this->serverinfo['b']['type'], $this->serverinfo['s']['game']);

              if(empty($this->userbarcache))
              {
                             if(vB_vBGamez::is_vb4())
                             {
                                            return '<br /><br />'.$vbphrase['vbgamez_nouserbars'].'<br /><br />';
                             }else{
                                            return '<td class="alt1" align="center"><br /><br />'.$vbphrase['vbgamez_nouserbars'].'<br /><br /></td>'; 
                             }
              }

              foreach($this->userbarcache AS $userbar)
              {
                     if(empty($userbar['background'])) { continue; }
					 $userbarCount++;
					
                     if($userbarCount > 1 AND vB_vBGamez::vb_call()->options['vbgamez_show_full_userbars'] == 0) { break; }
                     $url['url'] = vB_vBGamez::fetch_codes_url('sig', $serverid, $userbar['userbarid']);
                     $url['img'] = vB_vBGamez::fetch_codes_url('sigimg', $serverid, $userbar['userbarid']);

                     $userbar['name'] = htmlspecialchars_uni($userbar['name']);
                     

                     if($vbulletin->options['vbgamez_createuserbar_allow_createexample'])
                     {
                     		if(!in_array($userbar['userbarid'], explode(',', $vbulletin->options['vbgamez_create_example_userbar_exlude'])))
                     		{
                                 	$show['can_create_example'] = true;
                     		}   
                     }
                     $templater = vB_Template::create('vbgamez_userbarbit');
                     $templater->register('userbar', $userbar);
                     $templater->register('serverid', $serverid);
                     $templater->register('url', $url);
                     $userbarContent = $templater->render();

                     if(VBG_IS_VB4)
                     {
                              $templater = vB_Template::create('vbgamez_userbarbits');
                              $templater->register('userbar', $userbar);
                              $templater->register('serverid', $serverid);
                              $templater->register('userbarcontent', $userbarContent);
							  $templater->register('userbar_ajax_select', $this->getUserbarSelect($userbar['userbarid']));
                              $userbarbits .= $templater->render();
                     }else{
                              eval('$userbarbits .= "' . fetch_template('vbgamez_userbarbits') . '";');
                     }
              } 

          return $userbarbits;

        }
		function getUserbarSelect($selected_id)
		{
			global $vbulletin, $vbphrase;
			$this->init($this->serverinfo['b']['type'], $this->serverinfo['s']['game']);
			$serverid = intval($_REQUEST['id']);
			$userbarlist = '';
			foreach($this->userbarcache AS $userbar)
            {
	            	if(empty($userbar['background'])) { continue; }
    				if($userbar['userid'] == $vbulletin->userinfo['userid'] AND $vbulletin->userinfo['userid'] != 0 AND $vbulletin->options['vbgamez_allow_create_userbar'])
					{
						$userbar['name'] .= ' - '.$vbphrase['vbgamez_your_userbar'];
					}
					$userbarlist .= '<option value="' . $userbar['userbarid'] . '">' . htmlspecialchars_uni($userbar['name']) . '</option>';
					$userbarcount++;
			}
			if($userbarcount == 1)
			{
				return '';
			}
			if(VBG_IS_VB4)
			{
				$tpl = vB_Template::create('vbgamez_userbar_ajax_select');
				$tpl->register('userbarlist', $userbarlist);
				$tpl->register('serverid', $serverid);
				return $tpl->render();
			}else{
				return 'need convert userbar_ajax_select for vb3';
			}
		}
		function loadUserbar($id, $serverid)
		{
			  global $vbphrase, $vbulletin, $show;

              $this->init($this->serverinfo['b']['type'], $this->serverinfo['s']['game']);
			  $userbar = $this->userbarcache[$id];
			  $serverid = intval($_REQUEST['serverid']);
			
			  if(empty($userbar))
			  {
				return $id;
			  }
              if(empty($userbar['background'])) { return false; }

              $url['url'] = vB_vBGamez::fetch_codes_url('sig', $serverid, $userbar['userbarid']);
              $url['img'] = vB_vBGamez::fetch_codes_url('sigimg', $serverid, $userbar['userbarid']);

              $userbar['name'] = htmlspecialchars_uni($userbar['name']);

              if($vbulletin->options['vbgamez_createuserbar_allow_createexample'] AND $vbulletin->options['vbgamez_allow_create_userbar'])
              {
                     		if(!in_array($userbar['userbarid'], explode(',', $vbulletin->options['vbgamez_create_example_userbar_exlude'])))
                     		{
                                 	$show['can_create_example'] = true;
                     		}   
              }
			  $is_owner = false;
			
			  if($userbar['userid'] == $vbulletin->userinfo['userid'] AND $vbulletin->userinfo['userid'])
			  {
					$is_owner = true;
			  }
              $templater = vB_Template::create('vbgamez_userbarbit');
              $templater->register('userbar', $userbar);
              $templater->register('serverid', $serverid);
              $templater->register('url', $url);
              $userbarContent = $templater->render();

             return vB_vBGamez::jsonEncode(array('content' => vB_vBGamez::toUTF8($userbarContent), 'title' => vB_vBGamez::toUTF8($userbar['name']), 'examplecreate' => $show['can_create_example'], 'is_owner' => $is_owner));
		}
	 /*========================================================================
         *
	 * Конструирование юзербара
	 *
	 */

       function construct_userbar($userbarid)
       {
           global $vbulletin, $vbphrase, $db;

           $select_userbarinfo = $db->query("SELECT vbgamez_userbar_config.*, vbgamez_userbar.fontsize AS defaultfontsize, vbgamez_userbar.font AS defaultfont, vbgamez_userbar.textcolor, vbgamez_userbar.background
                                                 FROM " . TABLE_PREFIX . "vbgamez_userbar_config AS vbgamez_userbar_config
                                                 LEFT JOIN " . TABLE_PREFIX . "vbgamez_userbar AS vbgamez_userbar ON(vbgamez_userbar.userbarid = vbgamez_userbar_config.userbarid)
                                                 WHERE vbgamez_userbar_config.userbarid = '" . intval($userbarid) . "' AND vbgamez_userbar_config.enabled = 1 " . iif(THIS_SCRIPT == 'vbgamez', 'AND vbgamez_userbar.enabled = 1') . "");

           while($userbarinfo = $db->fetch_array($select_userbarinfo))
           {
                  if(vB_vBGamez_Userbar_dm::fetchColorType($userbarinfo['textcolor']) == 'hex')
                  {
                             $userbarinfo['textcolor'] = vB_vBGamez_Userbar_dm::html2rgb($userbarinfo['textcolor']);
                  }

                  if(vB_vBGamez_Userbar_dm::fetchColorType($userbarinfo['fontcolor']) == 'hex')
                  {
                             $userbarinfo['fontcolor'] = vB_vBGamez_Userbar_dm::html2rgb($userbarinfo['fontcolor']);
                  }

                  $userbar_fields[$userbarinfo['configid']] = $userbarinfo;
                  $userbar_info = $userbarinfo;
           }

           if(empty($userbar_info))
           {
                      $userbar_info = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "vbgamez_userbar AS vbgamez_userbar WHERE userbarid = '" . intval($userbarid) . "'");
           }

           if(empty($userbar_info['userbarid']) OR empty($userbar_info['background'])) { return false; }

           $im = vB_vBGamez::vbgamez_fetch_userbar_image($userbar_info['background']);

           $this->generate_userbar_fields($im, $userbar_fields);  
                        
           vB_vBGamez::vbgamez_print_userbar_image($im, $userbar_info['background'], $vbulletin->options['vbgamez_userbar_quality']);
                 
           @imagedestroy($im);

       }
}

/**
 * VBGamEz загрузка фонового изображения
 *
 * @package vBGamEz
 * @author GiveMeABreak aka Developer, LGSL author
 * @version $Revision: 43 $
 * @copyright GiveMeABreak
 */

class vB_Upload_Userbar_Background
{
     public static $errors;
     public static $data;

     public static function upload($formname, $allowed_types = 'jpg gif png')
     {
          global $vbphrase, $vbulletin;

          $vbulletin->input->clean_array_gpc('f', array($formname => TYPE_FILE));

          $filename = $vbulletin->GPC[$formname]['name'];
 
          if(!$filename) { return false; }

          $ext = strtolower(substr($filename, strrpos($filename, '.')+1));
 
          if (!$error AND !strstr("|".str_replace(" ","|",$allowed_types)."|",$ext))
          {
                  self::$errors = $error = construct_phrase($vbphrase['vbgamez_invalid_file_type_bg'], str_replace(' ', ', ', $allowed_types));
          }
          
          if(!$error AND !vB_vBGamez_Userbar_dm::verify_image_file($vbulletin->GPC[$formname]['tmp_name']))
          {
                  self::$errors = $error = construct_phrase($vbphrase['vbgamez_invalid_file_type_bg'], str_replace(' ', ', ', $allowed_types));
          }

          if(!$error AND ($_FILES[$formname]['size'] / 1024) > $vbulletin->options['vbgamez_createuserbar_max_background_size'])
          {
                  self::$errors = $error = construct_phrase($vbphrase['vbgamez_invalid_bg_userbar_size'], $vbulletin->options['vbgamez_createuserbar_max_background_size']);
          }

          if(empty($error))
          {
                   $uploaddir = './images/vbgamez/userbars/';

                   $uploadfile = $uploaddir.$vbulletin->userinfo['userid'].'_'.TIMENOW.'_' . str_replace('.'.$ext, '', $filename) . '.'.$ext;

                   @mkdir($uploaddir, 0777);

                   if(@is_writable($uploaddir) AND @is_dir($uploaddir))
                   {
                       copy($vbulletin->GPC[$formname]['tmp_name'], $uploadfile);
                   }

                   self::$data['filename'] = $uploadfile;
         }
    }

        public static function fetch_filename()
        {
              return vB_Upload_Userbar_Background::$data['filename'];
        }
}

/**
 * VBGamEz загрузка шрифта
 *
 * @package vBGamEz
 * @author GiveMeABreak aka Developer, LGSL author
 * @version $Revision: 34 $
 * @copyright GiveMeABreak
 */

class vB_Upload_Userbar_Font
{
     public static $errors;
     public static $data;

     public static function upload($formname, $allowed_types = 'ttf')
     {
          global $vbphrase, $vbulletin;

          $vbulletin->input->clean_array_gpc('f', array($formname => TYPE_FILE));

          $filename = $vbulletin->GPC[$formname]['name'];

          if(!$filename) { return false; }

          if(empty($filename))
          {
                  self::$errors = $error =  $vbphrase['vbgamez_invalid_file_type_font'];
          }

          $ext = strtolower(substr($filename, strrpos($filename, '.')+1));
 
          if (!$error AND !strstr("|".str_replace(" ","|",$allowed_types)."|",$ext))
          {
                  self::$errors = $error = $vbphrase['vbgamez_invalid_file_type_font'];
          }

          if(empty($error))
          {
                   $uploaddir = './images/vbgamez/userbars/';

                   $uploadfile = $uploaddir.$vbulletin->userinfo['userid'].'_'.TIMENOW.'_font_' . str_replace('.'.$ext, '', $filename) . '.'.$ext;

                   @mkdir($uploaddir, 0777);

                   if(@is_writable($uploaddir) AND @is_dir($uploaddir))
                   {
                       copy($vbulletin->GPC[$formname]['tmp_name'], $uploadfile);
                   }

                   self::$data['filename'] = $uploadfile;
         }
    }

        public static function fetch_filename()
        {
              return vB_Upload_Userbar_Font::$data['filename'];
        }
}


class vB_vBGamez_Userbar_dm
{

	 /*========================================================================
         *
	 * Является ли фон изображением?
	 *
	 */

 	  public static function verify_image_file($filename)
	  {
                global $vbulletin;
		require_once('./includes/class_image.php');
                $imageverify = vB_Image::fetch_library($vbulletin);

                if($imageinfo = $imageverify->fetch_image_info($filename))
                {
                            return true;
                }else{
                            return false;
                }
                 
	   }

	   /*========================================================================
           *
	   * ПОлучение информации о юзербаре
	   *
	   */

           public static function fetch_userbarinfo($userbarid, $check = false, $userid = 0)
           {
                      global $db;

                      $userbarid = intval($userbarid);
						$user_query = '';
					 if($check)
					 {
	                	if($userid)
	                	{
	                          $user_query = "AND (userid = '0' OR userid = '$userid')";
	                	}else{
	                          	$user_query = 'AND userid = 0';
	                	}
					}
                      return $db->query_first("SELECT * FROM " . TABLE_PREFIX . "vbgamez_userbar WHERE userbarid = '" . $userbarid . "' $user_query");

           }

	   /*========================================================================
           *
	   * ПОлучение информации о локации
	   *
	   */

           public static function fetch_configinfo($configid)
           {
                      global $db;

                      $configid = intval($configid);

                      return $db->query_first("SELECT * FROM " . TABLE_PREFIX . "vbgamez_userbar_config WHERE configid = '" . $configid . "'");

           }

	   /*========================================================================
           *
	   * Проверка прав
	   *
	   */

           public static function verify_permissions($userbar)
           {
                      global $vbulletin, $db;

                      if($userbar['userid'] != $vbulletin->userinfo['userid'])
                      {
                                 return false;
                      }else{
                                 return true;
                      }
           }

	   /*========================================================================
           *
	   * HTML HEx => RGB
	   *
	   */

           public static function html2rgb($color)
           {
               if ($color[0] == '#')
                   $color = substr($color, 1);

               if (strlen($color) == 6)
                   list($r, $g, $b) = array($color[0].$color[1],
                                 $color[2].$color[3],
                                 $color[4].$color[5]);
               elseif (strlen($color) == 3)
                   list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
               else
                   return false;

               $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

               return "rgb($r, $g, $b)";
           }

	   /*========================================================================
           *
	   * HTML HEx <= RGB
	   *
	   */
           public static function rgb2html($r, $g=-1, $b=-1)
           {
               if (is_array($r) && sizeof($r) == 3)
                   list($r, $g, $b) = $r;

               $r = intval($r); $g = intval($g);
               $b = intval($b);

               $r = dechex($r<0?0:($r>255?255:$r));
               $g = dechex($g<0?0:($g>255?255:$g));
               $b = dechex($b<0?0:($b>255?255:$b));

               $color = (strlen($r) < 2?'0':'').$r;
               $color .= (strlen($g) < 2?'0':'').$g;
               $color .= (strlen($b) < 2?'0':'').$b;
               return '#'.$color;
           }

           public static function vbg_fetch_font_name($origfilename)
           {
                   global $vbulletin;
                   return htmlspecialchars_uni(preg_replace('#./images/vbgamez/userbars/' . $vbulletin->userinfo['userid'] . '_(.*)_font_(.*)#si', '\2', $origfilename));
           }

           public static function vbg_fetch_background_name($origfilename)
           {
                   global $vbulletin;
                   $data = preg_replace('#./images/vbgamez/userbars/' . $vbulletin->userinfo['userid'] . '_(.*)_(.*)#si', '\2', $origfilename);

                   return htmlspecialchars_uni($data);
           }


           public static function createExampleUserbar($userbarid)
           {
                       global $vbulletin, $db, $vbphrase;

                       if(!$vbulletin->options['vbgamez_createuserbar_allow_createexample'])
                       {
                                    print_no_permission();
                       }

                       $userbarid = intval($userbarid);

                            $userbarinfo = vB_vBGamez_Userbar_dm::fetch_userbarinfo($userbarid, true, $vbulletin->userinfo['userid']);

                            if(empty($userbarinfo))
                            {
                                         eval(standard_error(fetch_error('invalidid', 'Userbar')));
                            }

                            if(in_array($userbarinfo['userbarid'], explode(',', $vbulletin->options['vbgamez_create_example_userbar_exlude'])))
                            {
                                         eval(standard_error($vbphrase['vbgamez_can_create_example_from_userbar']));
                            }

                            require_once('./packages/vbgamez/manager/userbar.php');
                            $userbar_dm = new vBGamEz_Userbar_Manager($vbulletin);

                            $uploaddir = './images/vbgamez/userbars/';

                            $oldbackground_name = $userbarinfo['background'];

                            $ext = strtolower(substr($oldbackground_name, strrpos($oldbackground_name, '.')+1));

                            $filename = vB_vBGamez_Userbar_dm::vbg_fetch_background_name($oldbackground_name);

                            $filename = preg_replace('#(.*)/(.*)#si', '\2', $filename);

                            $newbackground_name = $uploaddir.$vbulletin->userinfo['userid'].'_'.TIMENOW.'_' . str_replace('.'.$ext, '', $filename).".".$ext;

                            @mkdir($uploaddir, 0777);

                            if(@is_writable($uploaddir) AND @is_dir($uploaddir) AND file_exists($oldbackground_name))
                            {
                                   copy($oldbackground_name, $newbackground_name);
                            }

                            if($userbarinfo['font'])
                            {

                                           $oldfont_name = $userbarinfo['font'];

                                           $ext = strtolower(substr($oldfont_name, strrpos($oldfont_name, '.')+1));

                                           $fontfilename = vB_vBGamez_Userbar_dm::vbg_fetch_font_name($oldfont_name);

                                           $fontfilename = preg_replace('#(.*)/(.*)#si', '\2', $fontfilename);

                                           $newfont_name = $uploaddir.$vbulletin->userinfo['userid'].'_'.TIMENOW.'_font_' . str_replace('.'.$ext, '', $fontfilename).".".$ext;

                                           @mkdir($uploaddir, 0777);

                                           if(@is_writable($uploaddir) AND @is_dir($uploaddir) AND file_exists($oldfont_name))
                                           {
                                               @copy($oldfont_name, $newfont_name);
                                           }
                            }

                            $vbulletin->db->query("INSERT INTO " . TABLE_PREFIX  . "vbgamez_userbar
                                    (name, enabled, background, textcolor, font, fontsize, fieldname, userid)
                                    VALUES
                                    (".$db->sql_prepare($userbarinfo['name']).",
                                     '1',
                                     ".$db->sql_prepare($newbackground_name).",
                                     ".$db->sql_prepare($userbarinfo['textcolor']).",
                                     ".$db->sql_prepare($newfont_name).",
                                     ".$db->sql_prepare($userbarinfo['fontsize']).",
                                     'global',
                                     ".$vbulletin->userinfo['userid'].")");

                            $newuserbarid = $vbulletin->db->insert_id();

                            $fetch_locations = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez_userbar_config WHERE userbarid = '" . $userbarid . "'");

                            $uploaddir = './images/vbgamez/userbars/';

                            while($config = $vbulletin->db->fetch_array($fetch_locations))
                            {
				       if(!$config['enabled'])
				       {
						continue;
				       }

                                       $oldfont_name = $fontfilename = $newfont_name = '';

                                       if(!empty($config['font']) AND file_exists($config['font']))
                                       {
                                           $oldfont_name = $config['font'];

                                           $ext = strtolower(substr($oldfont_name, strrpos($oldfont_name, '.')+1));

                                           $fontfilename = vB_vBGamez_Userbar_dm::vbg_fetch_font_name($oldfont_name);

                                           $fontfilename = preg_replace('#(.*)/(.*)#si', '\2', $fontfilename);

                                           $newfont_name = $uploaddir.$vbulletin->userinfo['userid'].'_'.TIMENOW.'_font_' . str_replace('.'.$ext, '', $fontfilename).".ttf";

                                           @mkdir($uploaddir, 0777);

                                           if(@is_writable($uploaddir) AND @is_dir($uploaddir) AND file_exists($oldfont_name))
                                           {
                                               copy($oldfont_name, $newfont_name);
                                           }
                                       }

                                       $userbar_dm->do_add_userbar_location($newuserbarid, $config['text'], $config['radius'], $config['repeat_x'], $config['repeat_y'], $newfont_name, $config['fontsize'], $config['fontcolor'], $config['width'], 1, $config['ispreview']);
                            }

                        return $newuserbarid;
           }

           public static function canCreateUserbar()
           {
                            global $vbulletin;

                            $result_count = $vbulletin->db->query_first("
	                                        SELECT COUNT(vbgamez_userbar.userid) AS userbars,
                                                vbgamez_userbar.*
	                                        FROM " . TABLE_PREFIX . "vbgamez_userbar AS vbgamez_userbar 
                                                WHERE userid = '" . $vbulletin->userinfo['userid'] . "'
                                                GROUP BY vbgamez_userbar.userid");

                            if($result_count['userbars'] >= $vbulletin->options['vbgamez_max_count_of_userbars'])
                            {
                                        return false;
                            }else{
                                        return true;
                            }
           }

           public static function fetchExampleUserbars()
           {
                             global $vbulletin, $vbphrase;
 
                             if($vbulletin->options['vbgamez_createuserbar_allow_createexample'])
                             {
                                           if($userbarids = $vbulletin->options['vbgamez_create_example_userbar_exlude'])
                                           {
                                                            $exclude = " AND userbarid NOT IN($userbarids) ";
                                           }

					                	if($userid = $vbulletin->userinfo['userid'])
					                	{
					                          $user_query = "AND (userid = '0' OR userid = '$userid')";
					                	}else{
					                          	$user_query = 'AND userid = 0';
					                		}
					
                                           $fetch_userbars = $vbulletin->db->query("SELECT * FROM " . TABLE_PREFIX . "vbgamez_userbar WHERE enabled = 1 $user_query $exclude ORDER by name");
                                           while($userbar = $vbulletin->db->fetch_array($fetch_userbars))
                                           {
                                                         $userbars .= '<option value="' . $userbar['userbarid'] . '">' . $userbar['name'] . '</option>';
                                           }
                             }
                             
                             if(empty($userbars)) { return false; }else{ return $userbars; }
          }
                             
           public static function fetchColorType($color)
           {
                               if(substr($color, 0, 1) == '#')
                               {
					 if(vbstrlen($color) == 1)
					 {
						return '';
					 }
					 if(vbstrlen($color) > 7)
					 {
						return '';
					 }

                                         return 'hex';
                               }elseif(substr($color, 0, 4) == 'rgb(' AND substr($color, vbstrlen($color) -1, 1) ==')') {
					 $verify_string = str_replace(array('rgb(', ')'), '', $color);
					 $count = 0;
					 foreach(explode(',', $verify_string) AS $string)
					 {
						$string = trim($string);
						$parsedString = intval($string);
						if($string == '')
						{
							return '';
						}
						$count++;
					 }

					 if($count != 3)
				 	 {
						return '';
					 }

                                         return 'rgb';
                               }
                               
           }                    
}