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

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'vbgamez');
if($_REQUEST['do'] == 'view' AND $_REQUEST['ajax'] == 1)
{
	define('CSRF_PROTECTION', false);
}else{
	define('CSRF_PROTECTION', true);
}

define('GET_EDIT_TEMPLATES', true);
define('VBG_PACKAGE', true);

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array('posting',
                      'search',
                      'user',
                      'vbgamez',
                      'inlinemod',
                      'threadmanage',
                      'forumdisplay');

// get special data templates from the datastore
$specialtemplates = array('vbgamez_fieldcache', 'vbgamez_framecache', 'vbgamez_framestylevarcache');
if($_REQUEST['do'] == 'view')
{
       $specialtemplates[] = 'smiliecache';
       $specialtemplates[] = 'bbcodecache';
}
// pre-cache templates used by specific actions
$globaltemplates = array('vbgamez_detalis', 
	   'vbgamez_detalis_main',
	   'vbgamez_commentbits',
	   'vbgamez_listbits',
	   'vbgamez_listservers',
	   'vbgamez_uploadmap',
	   'vbgamez_sortarrow',
	   'vbgamez_viewinfo',
	   'vbgamez_whoplaying',
	   'vbgamez_whoplayingbits',
           'vbgamez_playersbits',
           'vbgamez_infobits',
           'vbgamez_jump',
           'vbgamez_jump_link',
           'vbgamez_emptyservers',
           'vbgamez_rating_verify',
           'vbgamez_top',
           'vbgamez_userbarbits',
           'vbgamez_moderation_popup',
           'vbgamez_moderation_actions',
           'vbgamez_top_bits',
           'vbgamez_top_bits_maps',
           'vbgamez.css',
           'vbgamez_banned_list', 
           'vbgamez_graphics',
           'vbgamez_detalis_fieldbits',
           'vbgamez_framebits',
           'vbgamez_startpage',
	   'vbgamez_sidebar_gamebit',
	   'vbgamez_startpage_gamebit',
	   'vbgamez_humanverify_image',
	   'vbgamez_popular_maps',
	   'vbgamez_history_maps',
	   'vbgamez_frame_ajax_select',
	   'vbgamez_framebit',
	   'vbgamez_userbar_ajax_select',
	   'vbgamez_userbarbit'
 );

// ######################### REQUIRE BACK-END ############################
// URL Parser
$vbgpathinfo = explode('/', $_SERVER["SCRIPT_NAME"]);
$vbgscriptname = $vbgpathinfo[count($vbgpathinfo) - 1];
$server_url_data = explode('::', preg_replace('#userbar/([0-9]+)/([0-9]+).jpg#si', '\1::\2', preg_replace('#(.*)/' . $vbgscriptname . '/(.*)#si', '\2', $_SERVER["PHP_SELF"])));

if(empty($_REQUEST['do']) AND !empty($server_url_data[0]) AND !empty($server_url_data[1]))
{
          $_REQUEST['do'] = 'sig';
          $_REQUEST['server'] = $server_url_data[0];
          $_REQUEST['sid'] = $server_url_data[1];
}

if($_REQUEST['do'] == 'sig' OR $_REQUEST['do'] == 'iframe' OR $_REQUEST['do'] == 'showmap')
{
          define('NOHEADER', 1);
          define('NOZIP', 1);
          define('NOCOOKIES', 1);
          define('NOPMPOPUP', 1);
          define('NONOTICES', 1);
          define('NOSHUTDOWNFUNC', 1);
          define('LOCATION_BYPASS', 1);
          define('SKIP_SESSIONCREATE', 1);
	  define('VBGAMEZ_DO_NOT_DISABLE_VBGAMEZ', 1);
}

if(defined('VBG_STYLEID') AND VBG_STYLEID != '' AND defined('VBG_DOMAIN'))
{
    $_REQUEST['styleid'] = VBG_STYLEID;
}
if($_REQUEST['do'] == 'view' AND $_REQUEST['ajax'] == 1)
{
    define('DIRECT_UPDATE_SERVER_CACHE', 1);
}

require_once('./global.php');
require_once(DIR . '/packages/vbgamez/bootstrap.php');
vB_vBGamez::loadClassFromFile('share');
vB_vBGamez::loadClassFromFile('comments');
vB_vBGamez::loadClassFromFile('statistics');
vB_vBGamez::loadClassFromFile('field');
vB_vBGamez::loadClassFromFile('userbar');

// ############################# vBGamEz START ENGINE #####################
if (empty($_REQUEST['do']))  
{ 
     if($vbulletin->options['vbgamez_enable_start_page'])
     {
              $_REQUEST['do'] = 'startpage';
     }else{
              $_REQUEST['do'] = 'allservers';
     }
}

if (!empty($_POST['ajax']) AND isset($_POST['uniqueid']))
{
	$_REQUEST['do'] = 'lightbox';
}

vB_vBGamez::bootstrap();
$vBG_FieldsController = new vBGamEz_FieldsController($vbulletin);

// ############################# START PAGE #####################

if($_REQUEST['do'] == 'startpage')
{
        if(!$vbulletin->options['vbgamez_enable_start_page'])
        {
                      exec_header_redirect($vbulletin->options['vbgamez_path']);
        }

        $navbits[] = $vbphrase['vbgamez'];
        $navbits = construct_navbits($navbits);
        $total = vB_vBGamez::vbgamez_cached_totals();

        $select_cacheservers = $db->query_read("SELECT type, cache_game
						FROM " . TABLE_PREFIX . "vbgamez AS vbgamez
						WHERE disabled = 0 AND valid = 0 " . iif($vbulletin->options['vbgamez_show_offline'], 'AND status = 1') . "");

        while($server = $db->fetch_array($select_cacheservers))
        {
				$server['cache_game'] = strtolower($server['cache_game']);
				$server['type'] = strtolower($server['type']);
				
            	   if(!vB_vBGamez_GameList::gameIsEnabled($server['type']))
	    	   {
 				continue;
	    	   }

			if($prepare = vB_vBGamez::integrateGameType($server['type'], $server['cache_game']))
			{
				$gameString = $prepare[1].'|_|'.$prepare[0];
			}else{
		   		if(vB_vBGamez::fetch_additional_game_type($server['type']) == array())
		   		{
                           $gameString = $server['type'].'|_|'.$server['type'];
		   		}else{
			   				$gameString = $server['cache_game'].'|_|'.$server['type'];
	  	   		}
			}

                   $called_types[$server['type']]++;

                   $cache_count[$gameString]++; 
        }

        if(!empty($cache_count))
        {
                     ksort($cache_count);
                     reset($cache_count);

                     $listcounter = '';
                     $gamecounter = '';

                     foreach($cache_count AS $data => $key)
                     {
                                $listcounter++;
                                $gamecounter++;
                                $showAddType = false;
                                $span = '';

                                $explode_data = explode('|_|', $data); 
                                $type = $explode_data[1];
                                $game = $explode_data[0];

                                $additinal_game_type = vB_vBGamez::fetch_additional_game_type($type);

                                if(!empty($additinal_game_type[$game]))
                                {
                                             $showAddType = true;
                                }

                                if($listcounter == 3 OR $gamecounter == 1)
                                {
                                         $span = iif($gamecounter != 1, '</span>').'<span id="startpage_games">';
                                         $listcounter = 0;
                                }

                                if(VBG_IS_VB4)
                                {
                                         $tpl = vB_Template::create('vbgamez_startpage_gamebit');
                                         $tpl->register('key', $key);
                                         $tpl->register('icon', vB_vBGamez::vbgamez_icon_game($type, $game));
                                         $tpl->register('gametype', vB_vBGamez::vbgamez_text_game($type, $game));
                                         $tpl->register('type', $type);
                                         $tpl->register('showAddType', $showAddType);
                                         $tpl->register('game', $game);
                                         $games .= $span.$tpl->render();
                                }else{
                                         $games .= "Need convert vbgamez_startpage_gamebit template render";
                                }
                     }                   

                     $gameListTotal = $gamecounter/3;

                     if (@strpos($gameListTotal, "."))
                     {
                             $games .= '<span>&nbsp;</span></span>';
                     }

        }else{
                     $games = '<span id="startpage_games">'.$vbphrase['vbgamez_empty_servers'] . '<br /></span>';
        }





        if(!empty($cache_count))
        {
			arsort($cache_count); reset($cache_count);
	        foreach($cache_count AS $data => $key)
            {
			    
	            $explode_data = explode('|_|', $data); 
                $type = $explode_data[1];
                $game = $explode_data[0];

    	     	if(!vB_vBGamez_GameList::gameIsEnabled($type))
	     		{
						continue;
	     			}
		     $counter++;
			 if($counter > $vbulletin->options['vbgamez_start_page_top'])
			 {
				continue;
			 }
             $showAddType = false;

             $additinal_game_type = vB_vBGamez::fetch_additional_game_type($type);

             if(!empty($additinal_game_type[$game]))
             {
                              $showAddType = true;
             }

             if(VBG_IS_VB4)
             {
                          $tpl = vB_Template::create('vbgamez_sidebar_gamebit');
                          $tpl->register('key', $key);
                          $tpl->register('icon', vB_vBGamez::vbgamez_icon_game($type, $game));
                          $tpl->register('gametype', vB_vBGamez::vbgamez_text_game($type, $game));
                          $tpl->register('game', $game);
						  $tpl->register('type', $type);
                          $tpl->register('showAddType', $showAddType);
                          $top10games .= $tpl->render();
             }else{
                          $top10games .= "Need convert vbgamez_sidebar_gamebit template render";
             }
       }
	  }

        if(VBG_IS_VB4)
        {
                 $navbar = render_navbar_template($navbits);

	         $templater = vB_Template::create('vbgamez_startpage');
		         $templater->register_page_templates();
		         $templater->register('navbar', $navbar);
		         $templater->register('total', $total);
		         $templater->register('top10games', $top10games);
		         $templater->register('games', $games);
	         print_output($templater->render());
        }else{

	   eval('$navbar = "' . fetch_template('navbar') . '";');
	   eval('print_output("' . fetch_template('vbgamez_startpage') . '");'); 
        }
}

// ######################### DOWNLOAD Realmlist ##################
if($_REQUEST['do'] == 'realmlist')
{
        require_once('./includes/functions_file.php');

        $lookup = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "vbgamez WHERE ip = ".$db->sql_prepare($_REQUEST['ip'])." AND c_port = ".$db->sql_prepare($_REQUEST['port']));

        if(empty($lookup['id'])) { print 'Server not in \'' . $vbulletin->options['bbtitle'] . '\' database'; exit; }

        $data = "set realmlist $lookup[ip] \r\n";
        $data .= "set patchlist $lookup[ip]";

        file_download($data, 'realmlist.wtf');
}

// ############################# VIEW DETALIS SERVER #####################

if($_REQUEST['do'] == 'view')
{

	$vbulletin->input->clean_array_gpc('r', array(
		'id' 	=> TYPE_INT,
                'perpage' => TYPE_UINT,
                'page' => TYPE_UINT));

  $_REQUEST['sort_by_field'] = htmlspecialchars($_REQUEST['sort_by_field']);

  $vbulletin->GPC['id'] = intval($vbulletin->GPC['id']);
  $vbulletin->GPC['perpage'] = vB_vBGamez::sanitize_perpage($vbulletin->GPC['perpage'], 100, $vbulletin->options['vbgamez_comments_perpage']);

  if (!$vbulletin->GPC['page'])
  {
	$vbulletin->GPC['page']  = 1;
  }

  $pos = ($vbulletin->GPC['page'] - 1) * $vbulletin->GPC['perpage'];

  $lookup = vB_vBGamez::vbgamez_verify_id($vbulletin->GPC['id'], false, true);

  if (!$lookup)
  {
	     if($_REQUEST['ajax'])
	     {
             		exit;
	     }else{
			exec_header_redirect('' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . "");
	     }
  }

  if(!$_REQUEST['ajax'])
  {
  	if(empty($_REQUEST['sort_by_field']))
  	{
             $_REQUEST['sort_by_field'] = 'name';
             $vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "vbgamez SET views = views +1 WHERE id = " . $vbulletin->GPC['id'] . "");
             $lookup['views'] += 1;
  	}
  }else{
	$_REQUEST['sort_by_field'] = $_POST['sort_by_field'];
  }

  $server = vB_vBGamez::vBG_Datastore_Cache($lookup['ip'], $lookup['q_port'], $lookup['c_port'], $lookup['s_port'], $lookup['type'], "sep", $lookup);
  $misc   = vB_vBGamez::vbgamez_server_misc($server);
  $server = vB_vBGamez::vbgamez_server_html($server);
  $server = SortServerFields($server);

  $connectlink = vbgamez_software_link($lookup['type'], $lookup['ip'], $lookup['c_port'], $lookup['q_port'], $lookup['s_port']);

  $server['o']['id'] = $lookup['id'];

  $server['i']['views'] = intval($lookup['views']);
  $server['i']['comments'] = intval($lookup['comments']);
  $server['i']['rating'] = intval($lookup['rating']);
  $server['i']['userid'] = intval($lookup['userid']);
  $server['i']['id'] = intval($lookup['id']);
  $server['i']['steam'] = intval($lookup['steam']);
  $server['i']['username'] = fetch_musername($lookup);
  $server['i']['userid'] = intval($lookup['userid']);
  $server['s']['name_short'] = vB_vBGamez::substr($server['s']['name'], 65);

  if(!$_REQUEST['ajax'])
  {
	
	require_once('./includes/class_bbcode.php');

  	if(VBG_IS_VB4)
  	{
             require_once(DIR . '/includes/functions_video.php');
  	}

	$bbcode_parser = new vB_BbCodeParser($vbulletin, fetch_tag_list());
	
	vB_vBGamez::loadClassFromFile('geo');

  	if(vB_vBGamez_Geo_db::check_settings() AND $vbulletin->options['vbgamez_google_map_enable'])
  	{
            $info['country'] = $lookup['country'];
            $info['city'] = $lookup['city'];

            if(empty($info['city']))
            {
                        $show['googlemap'] = false;
            }else{
                        $show['googlemap'] = true;
            }
  	}

  	$show['commentsenable'] = vB_vBGamez::vbg_check_comments_enable($lookup['commentsenable']);

  	if($vbulletin->options['vbgamez_comments_enable'] AND $show['commentsenable'])
  	{

  		$result_comments = $vbulletin->db->query_read("SELECT user.userid, user.usergroupid, user.avatarrevision
                                 " . ($vbulletin->options['avatarenabled'] ? ",avatar.avatarpath, NOT ISNULL(customavatar.userid) AS hascustomavatar, customavatar.dateline AS avatardateline,customavatar.width AS avwidth,customavatar.height AS avheight, customavatar.width_thumb AS avwidth_thumb, customavatar.height_thumb AS avheight_thumb, filedata_thumb, NOT ISNULL(customavatar.userid) AS hascustom" : "") . ", vbgamez_comments.*, user.username
		                 FROM " . TABLE_PREFIX . "vbgamez_comments AS vbgamez_comments
		                 LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = vbgamez_comments.userid)
                                 " . ($vbulletin->options['avatarenabled'] ? "LEFT JOIN " . TABLE_PREFIX . "avatar AS avatar ON(avatar.avatarid = user.avatarid) LEFT JOIN " . TABLE_PREFIX . "customavatar AS customavatar ON(customavatar.userid = vbgamez_comments.userid)" : "") . "
		                 WHERE serverid = " . $vbulletin->GPC['id'] . "
		                 ORDER by dateline LIMIT $pos, " . $vbulletin->GPC['perpage'] . "");

		while ($comment = $vbulletin->db->fetch_Array($result_comments))
		{   
					if(!$comment['username'])
					{
						 $comment['username'] = $vbphrase['unregistered'];
					}
               		if($comment['onmoderate'] AND !vB_vBGamez::can_view_comment($server, $comment))
               		{
                   		continue;
               		}

               		if($comment['deleted'] AND !vB_vBGamez::vbg_check_delete_comments_permissions($comment['serverid'], $lookup['userid']))
               		{
                   		continue;
               		}

               		$show['edit'] = vB_vBGamez::vbg_check_edit_comments_permissions($comment['userid']);
               		$show['delete'] = vB_vBGamez::vbg_check_delete_comments_permissions($lookup['id'], $lookup['userid'], $comment['userid']);
               		$show['delete_array'] = vB_vBGamez::vbg_check_delete_comments_permissions($lookup['id'], $lookup['userid']);

               		$commentbits .= vBGamez_Comments::fetch_comment($comment);
		}

     		if(vB_vBGamez::vbg_check_delete_comments_permissions($comment['serverid'], $lookup['userid']))
     		{
                  	$server['i']['comments'] = vB_vBGamez::fetch_count_comments_with_nopublished($vbulletin->GPC['id']);
     		}
 	}

  	$pagenav = construct_page_nav($vbulletin->GPC['page'], $vbulletin->GPC['perpage'], $server['i']['comments'], '' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . 'do=view&amp;id=' . $lookup['id'] . '&amp;pp=' . $vbulletin->GPC['perpage'], '', 'comments');
  }

  if (!$server['p'])
  {
    $show['players'] = false;

  }else{
    
    if(VBG_IS_VB4)
    {
      $bgclasses = array('vbgamez_table_lrSubHeader vbgamez_table_gridliner_tr', 'vbgamez_table_gridliner_tr');
      $bgclasses_bits = array('vbgamez_table_lrSRow vbgamez_table_gridliner_td', 'vbgamez_table_lrSRow vbgamez_table_gridliner_td');

    }else{

      $bgclasses = array('alt1', 'alt2');
      $bgclasses_bits = array('alt1', 'alt2');
    }

      $bgcolornum = 0; 
      $bgcolornum_bits = 0; 

      foreach ($server['p'] as $player_key => $player)
      {
        $bgclassnum = 1 - $bgclassnum;
        $bgclass = $bgclasses[$bgclassnum]; 

        $bgclassnum_bits = 1 - $bgclassnum_bits;
        $bgclass_bits = $bgclasses_bits[$bgclassnum_bits]; 

        $playerfields = '';

        if(empty($player['name']) OR str_replace(' ', '', $player['name']) == '') { $player['name'] = 'Unknown name'; }

        if(VBG_IS_VB4)
        {
           foreach (vB_vBGamez::fetch_player_fields($server) as $field_key => $field)
           {

              $playerfields[] = $player[$field];
           }

           $templater = vB_Template::create('vbgamez_playersbits');
           $templater->register('player', $player);
           $templater->register('playerfields', $playerfields);
           $templater->register('bgclass', $bgclass);
           $templater->register('bgclass_bits', $bgclass_bits);
           $players .= $templater->render();

        }else{

           foreach (vB_vBGamez::fetch_player_fields($server) as $field_key => $field)
           {
              $playerfields .= '<td class="'.$bgclass_bits.'">'.$player[$field].'</td>';
           }

           eval('$players .= "' . fetch_template('vbgamez_playersbits') . '";');
        }
      }    

    $show['players'] = true;
  }

  if(VBG_IS_VB4)
  {
         foreach (vB_vBGamez::fetch_player_fields($server) as $field_key => $field)
         {
                    $headfields[$field] = vB_vBGamez::fetch_player_fields_names($field);
         }
  }else{
         $headfields = '';
         foreach (vB_vBGamez::fetch_player_fields($server) as $field_key => $field)
         {
                    $headfields .= '<td class="thead smallfont"><strong>' . vB_vBGamez::fetch_player_fields_names($field) . '</strong></td>';
         }
  }

  $request_url = '' . $vbulletin->options['vbgamez_path'] . '?'.$_SERVER['QUERY_STRING'];

  if (!$vbulletin->userinfo['userid'] AND $vbulletin->options['vbgamez_allow_uploadmaps_user'] AND $vbulletin->options['vbgamez_maps_upload'])
  {
            $allow_upload = true;
  }

  if ($vbulletin->userinfo['userid'] AND $vbulletin->options['vbgamez_maps_upload'])
  {
            $allow_upload = true;
  }

  if($misc['image_map'] == vB_vBGamez::fetch_image('images/vbgamez/map_no_image.jpg') AND $allow_upload)
  {
            $show['map_upload'] = true;
  }

  if(!$vbulletin->userinfo['userid'] AND !$vbulletin->options['vbgamez_comments_from_guests'])
  {
	    $show['can_post_comments'] = false;
  }else{
	    $show['can_post_comments'] = true;
  }

  $show['can_view_comments'] = ($vbulletin->options['vbgamez_comments_enable'] == 1 AND $show['commentsenable']);
  $show['can_view_post_comments'] = ($show['can_view_comments'] AND $show['can_post_comments']);
  $show['show_comments_block'] = ($commentbits OR ($show['can_post_comments'] AND $show['commentsenable']));

  $navbits['' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl_q']] = $vbphrase['vbgamez'];
  $navbits['' . $vbulletin->options['vbgamez_path'] . ''] = $vbphrase['vbgamez_view_server']." ".$server['s']['name'];

  if(!$_REQUEST['ajax'])
  {
	$additional_fields = $vBG_FieldsController->getDisplayViewDetalisInfo($lookup);

  	if(vB_vBGamez::vbg_check_delete_comments_permissions($lookup['id'], $lookup['userid']))
  	{
            $show['comments_delete'] = true;
            $show['moderation_popup'] = vB_vBGamez::construct_moderation_popup();
            $show['moderation_popup_actions'] = vB_vBGamez::construct_moderation_popup_actions();
  	}

  	$userbar = new vBGamez_Userbar();
  	$userbar->serverinfo = $server;
  	$userbarbits = $userbar->construct_userbarbits($vbulletin->GPC['id']);
	vB_vBGamez::loadClassFromFile('frame');

  	$frame = new vBGamez_FrameView();
  	$frames = $frame->construct_framebits($vbulletin->GPC['id']);

  	if($vbulletin->options['vbgamez_graphics'])
  	{
           $server['i']['statistics'] =& $lookup['statistics'];
	   $statisticsClass = vBGamez_Stats_Display::create($vbulletin, $server);
	   $graphicId = $statisticsClass->getDefaultGraphicId();
	   $graphics = $statisticsClass->render($graphicId);

  	}

  	$show['vbg_moderation'] = iif(!vB_vBGamez::moderate_comment_before_add(), 1, 0);

  	if(can_administer())
  	{
             $show['vbg_is_admin'] = true;
             $admincpdir =& $vbulletin->config['Misc']['admincpdir'];
  	}

  	if($vbulletin->options['vbgamez_comments_from_guests'] AND !$vbulletin->userinfo['userid'])
  	{
		require_once(DIR . '/includes/class_humanverify.php');
		$verify =& vB_HumanVerify::fetch_library($vbulletin);
		$human_verify = vB_vBGamez::prepareHumanVerifyRender($verify->output_token());
  	}

	if($vbulletin->options['vbgamez_share'])
	{
		$show = vB_vBGamez_Share::registerPublishersToShowArray($show);
		$publishtitle = vB_vBGamez_Share::preparePublishTitle(construct_phrase($vbphrase['vbgamez_publish_title'], $server['s']['name'], $server['b']['ip'].":".$server['b']['c_port']));
		$publishurl = urlencode($vbulletin->options['vbgamez_path'].'?do=view&amp;id=' . $vbulletin->GPC['id']);
		$show['vbg_publishers'] = true;
	}
  }

  if(!empty($_REQUEST['page']))
  {
                $show['vbg_goto'] = true;
  }

  if($_REQUEST['order'] == 'desc')
  {
	  $playersorder = 'desc';
	  $playersorder2 = 'asc';
  }else{
	  $playersorder = 'asc';
	  $playersorder2 = 'desc';
  }

  if(!$vbulletin->vbg_haspopularmaps AND !$vbulletin->vbg_hashistory AND !$graphicId)
  {
		$graphics = ''; // unset class, etc... for hide Statistics tab
  }
  if(VBG_IS_VB4)
  {
		$maintemplate = vB_Template::create('vbgamez_detalis_main');
        $templater = vB_Template::create('vbgamez_detalis');
        $templater->register_page_templates();
        $navbits = construct_navbits($navbits);
        $navbar = render_navbar_template($navbits);

		$now_register =& $maintemplate;
		for($i=0;$i<2;$i++)
		{
        	$now_register->register('navbar', $navbar);
        	$now_register->register('frames', $frames);
        	$now_register->register('admincpdir', $admincpdir);
        	$now_register->register('playersorder', $playersorder);
        	$now_register->register('playersorder2', $playersorder2);
        	$now_register->register('page', $vbulletin->GPC['page']);
        	$now_register->register('pagenumber', $pagenumber);
        	$now_register->register('perpage', $perpage);
        	$now_register->register('sortfield', $sortfield);
        	$now_register->register('pagenav', $pagenav);
        	$now_register->register('server', $server);
        	$now_register->register('misc', $misc);
        	$now_register->register('headfields', $headfields);
        	$now_register->register('players', $players);
        	$now_register->register('commentbits', $commentbits);
        	$now_register->register('connectlink', $connectlink);
        	$now_register->register('additional_fields', $additional_fields);
        	$now_register->register('request_url', $request_url);
        	$now_register->register('userbarbits', $userbarbits);
        	$now_register->register('lookup', $lookup);
        	$now_register->register('info', $info);
        	$now_register->register('graphics', $graphics);
        	$now_register->register('human_verify', $human_verify);
        	$now_register->register('publishtitle', $publishtitle);
        	$now_register->register('publishurl', $publishurl);
			$now_register =& $templater;
 		}
		if($_REQUEST['ajax'])
		{
			print_output($maintemplate->render());
		}else{
			$templater->register('maindetalis', $maintemplate->render());
			print_output($templater->render());
		}
  }else{
	   $navbits = construct_navbits($navbits);
	   eval('$navbar = "' . fetch_template('navbar') . '";');
	   eval('$vbg_css = "' . fetch_template('vbgamez.css') . '";');
	   eval('print_output("' . fetch_template('vbgamez_detalis') . '");'); 
  }
}

// ############################# LIST GAME SERVERS #####################
if ($_REQUEST['do'] == 'allservers')
{

  $perpage = $vbulletin->input->clean_gpc('r', 'perpage', TYPE_UINT);
  $pagenumber = $vbulletin->input->clean_gpc('r', 'pagenumber', TYPE_UINT);
  $sortfield = $vbulletin->input->clean_gpc('r', 'sortfield', TYPE_NOHTML);
  $sortorder = $vbulletin->input->clean_gpc('r', 'sortorder', TYPE_NOHTML);

  $perpage = vB_vBGamez::sanitize_perpage($perpage, 20, $vbulletin->options['vbgamez_allservers_count']);

  if (!$pagenumber)
  {
	$pagenumber = 1;
  }

  $pos = ($pagenumber - 1) * $perpage;

  if ($sortorder == 'desc')
  {
	$sortorder = 'asc';
        $oppositeorder = 'desc';
  }
  else
  { 
	$sortorder = 'desc';
        $oppositeorder = 'asc';
  }

  if(empty($sortfield))
  {
          $sortfield = $vbulletin->options['vbgamez_sort_server'];
  }

  switch ($sortfield)
  {
	case 'id':
		$sqlsort = 'vbgamez.id';
		break;
	case 'name':
		$sqlsort = 'vbgamez.cache_name';
		break;
	case 'game':
		$sqlsort = 'vbgamez.cache_game';
		break;
	case 'comments':
		$sqlsort = 'vbgamez.comments';
		break;
	case 'map':
		$sqlsort = 'vbgamez.cache_map';
		break;
	case 'players':
		$sqlsort = 'vbgamez.cache_players';
		break;
	case 'views':
		$sqlsort = 'vbgamez.views';
		break;
	case 'rating':
		$sqlsort = 'vbgamez.rating';
		break;
	default:
		$sqlsort = 'rating';
                $sortfield = 'rating';
  }

  if($_REQUEST['type'])
  {
                $gameTypes = vbgamez_type_list();
                if(empty($gameTypes[$_REQUEST['type']]))
                {
                           eval(standard_error(fetch_error('invalidid', $vbphrase['type'])));
                }else{
                           $selectedtype = $_REQUEST['type'];
                }
  }

  if($_REQUEST['addtype'])
  {
                $gameAddTypes = vB_vBGamez::fetch_additional_game_type($_REQUEST['type']);
                if(empty($gameAddTypes[$_REQUEST['addtype']]))
                {
                           eval(standard_error(fetch_error('invalidid', $vbphrase['type'])));
                }else{
                           $selectedgame = $_REQUEST['addtype'];
                }
  }


  $reloadurl = ($perpage != 20 ? "pp=$perpage&amp;" : '') .
	($pagenumber != 1 ? "page=$pagenumber&amp;" : '') .
	($sortfield != 'username' ? "sort=$sortfield&amp;" : '')."order=".$oppositeorder.iif($selectedtype, '&amp;type=' . $selectedtype).iif($selectedgame, '&amp;addtype=' . $selectedgame);

  $reloadurl = preg_replace('#&amp;$#s', '', $reloadurl);

  if (!empty($reloadurl))
  {
	$reloadurl = '' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . 'do=allservers&amp;'.$reloadurl;
  }
  else
  {
	$reloadurl = '' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . 'do=allservers&amp;';
  }

  $sorturl = '' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . 'do=allservers&amp;';
  
  $reloadurl = str_replace('&amp;', '&', $reloadurl);

  if(VBG_IS_VB4)
  {
        $templater = vB_Template::create('vbgamez_sortarrow');
	$templater->register('sortorder', $sortorder);
	$templater->register('pagenumber', $pagenumber);
	$templater->register('perpage', $perpage);
	$templater->register('sortfield', $sortfield);
	$templater->register('sorturl', $sorturl);
        $sortarrow[$sortfield] = $templater->render();
  }else{
        eval('$sortarrow[' . $sortfield . '] = "' . fetch_template('vbgamez_sortarrow') . '";');
  }

  $sort = array($sortfield => 'selected="selected"');
  $order = array($oppositeorder => 'selected="selected"');

  $server_list = vB_vBGamez::vBG_Datastore_Cache_all("s");
  $total = vB_vBGamez::vbgamez_cached_totals();

  $server = array();

  foreach ($server_list as $server)
    {

          $misc   = vB_vBGamez::vbgamez_server_misc($server);
          $server = vB_vBGamez::vbgamez_server_html($server);

          $connectlink = vbgamez_software_link($server['b']['type'], $server['b']['ip'], $server['b']['c_port'], $server['b']['q_port'], $server['b']['s_port']);

          if(VBG_IS_VB4)
          {
                $templater = vB_Template::create('vbgamez_listbits');
                $templater->register('server', $server);
                $templater->register('connectlink', $connectlink);
                $templater->register('misc', $misc);
                $listbits .= $templater->render(); 
          }else{
                eval('$listbits .= "' . fetch_template('vbgamez_listbits') . '";');
          }
    }

  if(empty($listbits))
  {
         $show['listbits'] = false;

         if(VBG_IS_VB4)
         {
                 $emptybits = vB_Template::create('vbgamez_emptyservers')->render();
         }else{
                 eval('$emptybits = "' . fetch_template('vbgamez_emptyservers') . '";');
         }
  }else{
         $show['listbits'] = true;
         $emptybits = '';
  }

  $vbgamez_jump = vB_vBGamez::construct_servers_jump($total['jump_servers']);

  $pagenav = construct_page_nav($pagenumber, $perpage, $total['servers'], '' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . 'do=allservers&amp;sort=' . $sortfield . '&amp;order=' . $oppositeorder . '&amp;pp=' . $perpage . ''.iif($selectedtype, '&amp;type=' . $selectedtype).iif($selectedgame, '&amp;addtype=' . $selectedgame));

  $navbits = array('' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl_q'] => $vbphrase['vbgamez']);

  if($vbulletin->options['vbgamez_ratingsystem_enable'] AND $vbulletin->options['vbgamez_comments_enable'])
  {
      $vbg_colspan = 8;
  }elseif($vbulletin->options['vbgamez_ratingsystem_enable'] AND !$vbulletin->options['vbgamez_comments_enable'])
  {
      $vbg_colspan = 7;
  }elseif(!$vbulletin->options['vbgamez_ratingsystem_enable'] AND !$vbulletin->options['vbgamez_comments_enable'])
  {
      $vbg_colspan = 7;
  }elseif(!$vbulletin->options['vbgamez_ratingsystem_enable'] AND $vbulletin->options['vbgamez_comments_enable'])
  {
      $vbg_colspan = 6;
  }

  if(VBG_IS_VB4)
  {
           $templater = vB_Template::create('vbgamez_listservers');

           $navbits = construct_navbits($navbits);
           $navbar = render_navbar_template($navbits);
           $templater->register_page_templates();
           $templater->register('navbar', $navbar);
           $templater->register('listbits', $listbits);
           $templater->register('total', $total);
           $templater->register('sortorder', $sortorder);
           $templater->register('pagenumber', $pagenumber);
           $templater->register('perpage', $perpage);
           $templater->register('sortfield', $sortfield);
           $templater->register('sorturl', $sorturl);
           $templater->register('reloadurl', $reloadurl);
           $templater->register('sortarrow', $sortarrow);
           $templater->register('pagenav', $pagenav);
           $templater->register('sort', $sort);
           $templater->register('order', $order);
           $templater->register('vbgamez_jump', $vbgamez_jump);
           $templater->register('selectedgametitle', vB_vBGamez::vbgamez_text_game($_REQUEST['type'], $_REQUEST['addtype']));
           $templater->register('selectedtypetitle', vB_vBGamez::vbgamez_text_game($_REQUEST['type'], $_REQUEST['addtype']));
           $templater->register('selectedgame', $selectedgame);
           $templater->register('selectedtype', $selectedtype);

           $templater->register('emptybits', $emptybits);
           print_output($templater->render());
  }else{
	   $navbits = construct_navbits($navbits);
	   eval('$navbar = "' . fetch_template('navbar') . '";');
	   eval('print_output("' . fetch_template('vbgamez_listservers') . '");');
  }

}

// ############################# RATING #####################

if($_REQUEST['do'] == 'ratingplus' OR $_REQUEST['do'] == 'ratingminus')
  {
        if($_REQUEST['do'] == 'ratingminus')
        {
                if(empty($vbulletin->options['vbgamez_rating_minus']))
                {
                             exit;
                }
        }

        if(!$vbulletin->options['vbgamez_ratingsystem_enable']) // enable/disable rating system
        {
                          standard_error (fetch_error ('vbgamez_rating_disabled'));
        }

		if(!$vbulletin->options['vbgamez_rate_daily'])
		{
			$vbulletin->options['vbgamez_rate_daily'] = 5;
		}

	$vbulletin->input->clean_array_gpc('r', array(
		'serverid' 	=> TYPE_INT,
		'ajax' 	=> TYPE_INT,
		'fromverify' 	=> TYPE_INT,
		'fromajax' 	=> TYPE_INT,
		'hash' 	=> TYPE_STR,
                'humanverify' => TYPE_ARRAY));

        $vbulletin->GPC['serverid'] = intval($vbulletin->GPC['serverid']);

  	if (!$vbulletin->userinfo['userid'] AND !$vbulletin->options['vbgamez_disable_rate_guest'])
        {
          vB_vBGamez::print_or_standard_error($vbphrase['vbgamez_rating_user_not_register']);
        }

        $lookup = vB_vBGamez::vbgamez_verify_id($vbulletin->GPC['serverid']);

        if (!$lookup OR !$vbulletin->GPC['serverid'])
        {
                  vB_vBGamez::print_or_standard_error("Invalid ID");
        }

        $day = date('d/m/y', TIMENOW);

          $result_ratingsystem = $vbulletin->db->query_read(
		"SELECT * FROM " . 
		TABLE_PREFIX . "vbgamez_rating WHERE date = " . intval($day) . " AND ipaddress = '" . IPADDRESS . "'"
	   );
       
        while($ratingsystem = $vbulletin->db->fetch_Array($result_ratingsystem))
		{
			$allcountrate++;
			
			if($ratingsystem['serverid'] == $vbulletin->GPC['serverid'])
			{
				$servercount++;
			}
		}

		if($servercount)
		{
                   vB_vBGamez::print_or_standard_error(construct_phrase($vbphrase['vbgamez_rating_once_at_day']));
		}
        if($allcountrate AND $allcountrate >= $vbulletin->options['vbgamez_rate_daily'])
        {
                   vB_vBGamez::print_or_standard_error(construct_phrase($vbphrase['vbgamez_rating_limit_maximum'], $vbulletin->options['vbgamez_rate_daily']));
        }

       if($vbulletin->options['vbgamez_disable_rate_guest'] AND $vbulletin->GPC['fromajax'] AND !$vbulletin->userinfo['userid'])
       {
                   vB_vBGamez::print_or_standard_error("verify");
       }

       if($vbulletin->options['vbgamez_disable_rate_guest'] AND !$vbulletin->userinfo['userid'])
       {
                $navbits['' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . ''] = $vbphrase['vbgamez'];

				require_once(DIR . '/includes/class_humanverify.php');
				$verify =& vB_HumanVerify::fetch_library($vbulletin);
				$human_verify = $verify->output_token();
                $captcha = $human_verify;
                $action = $_REQUEST['do'];

                if(VBG_IS_VB4)
                {
                         $captcha_tpl = vB_Template::create('vbgamez_rating_verify');

                         $navbits = construct_navbits($navbits);
                         $navbar = render_navbar_template($navbits);
                         $captcha_tpl->register_page_templates();
                         $captcha_tpl->register('lookup', $lookup);
                         $captcha_tpl->register('captcha', $human_verify);
                         $captcha_tpl->register('navbar', $navbar);
                         $captcha_tpl->register('action', $action);
                }else{
                         $captcha = str_replace('<input type="text"', '<input type="text" id="imageregt"', $captcha);
                }

            if(!$vbulletin->GPC['fromverify'])
            {
                if(VBG_IS_VB4)
                {
                         print_output($captcha_tpl->render());
                }else{
	                 $navbits = construct_navbits($navbits);
	                 eval('$navbar = "' . fetch_template('navbar') . '";');
	                 eval('print_output("' . fetch_template('vbgamez_rating_verify') . '");');
                }

            }else if($vbulletin->GPC['fromverify'])
            {

		require_once(DIR . '/includes/class_humanverify.php');
		$verify =& vB_HumanVerify::fetch_library($vbulletin);
		if (!$verify->verify_token($vbulletin->GPC['humanverify']))
		{
                         $show['errors'] = true;

                         if(!$vbulletin->GPC['ajax'])
                         {
                                 if(VBG_IS_VB4)
                                 {
                                          print_output($captcha_tpl->render());
                                 }else{
	                                  $navbits = construct_navbits($navbits);
	                                  eval('$navbar = "' . fetch_template('navbar') . '";');
	                                  eval('print_output("' . fetch_template('vbgamez_rating_verify') . '");');
                                 }
                         }else{
                                vB_vBGamez::print_or_standard_error("error");
                         }
		}
            }
       }

         $vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "vbgamez_rating (date, ipaddress, serverid) VALUES (" . intval($day) . ", '" . IPADDRESS . "', '" . $vbulletin->GPC['serverid'] . "')");

         if($_REQUEST['do'] == 'ratingplus')
         {

                 if(empty($vbulletin->options['vbgamez_rating_plus']))
                 {
                              $vbulletin->options['vbgamez_rating_plus'] = 1;
                 }

                         $vbulletin->db->query_write("
		                    UPDATE " . TABLE_PREFIX . "vbgamez
		                    SET rating = rating +" . $vbulletin->options['vbgamez_rating_plus'] . "
		                    WHERE id = " . $vbulletin->GPC['serverid'] . "");

                                   vB_vBGamez::vBG_Datastore_Clear_Cache($vbulletin->GPC['serverid'], 'rating');

                                   if(!$vbulletin->GPC['ajax'])
                                   {
                                              eval(standard_error(construct_phrase($vbphrase['vbgamez_rating_added_plus'], $vbulletin->GPC['serverid'], $vbulletin->options['vbgamez_path'])));
                                   }else{
                                              exit; 
                                   }

         }else{
                         $vbulletin->db->query_write("
		                    UPDATE " . TABLE_PREFIX . "vbgamez
		                    SET rating = rating -" . $vbulletin->options['vbgamez_rating_minus'] . "
		                    WHERE id = " . $vbulletin->GPC['serverid'] . "");

                                   vB_vBGamez::vBG_Datastore_Clear_Cache($vbulletin->GPC['serverid'], 'rating');

                                   if(!$vbulletin->GPC['ajax'])
                                   {
                                              eval(standard_error(construct_phrase($vbphrase['vbgamez_rating_added_minus'], $vbulletin->GPC['serverid'], $vbulletin->options['vbgamez_path'])));
                                   }else{
                                              exit; 
                                   }
         }
 }


// ############################# IFRAME & TEXT & SIGNATURE GENERATION #####################
if($_REQUEST['do'] == 'iframe' OR $_REQUEST['do'] == 'sig')
{

  if(!$vbulletin->options['vbgamez_can_use_codes'])
   {   
           print 'Codes disabled.'; exit;
   }

  $vbulletin->input->clean_array_gpc('r', array(
		'server' 	=> TYPE_INT));

  $id = intval($vbulletin->GPC['server']);
  $lookup = vB_vBGamez::vbgamez_verify_id($id);

  if (!$lookup)
  {
           print 'Server not in \'' . $vbulletin->options['bbtitle'] . '\' database'; exit;
  }

  $server = vB_vBGamez::vBG_Datastore_Cache($lookup['ip'], $lookup['q_port'], $lookup['c_port'], $lookup['s_port'], $lookup['type'], "sep", $lookup);

  $misc   = vB_vBGamez::vbgamez_server_misc($server);
  $server = vB_vBGamez::vbgamez_server_html($server);
  $connectlink = vbgamez_software_link($lookup['type'], $lookup['ip'], $lookup['c_port'], $lookup['q_port'], $lookup['s_port']);

        if($_REQUEST['do'] == 'sig')
        {
                 $userbar = new vBGamez_Userbar();
                 $userbar->serverinfo = $server;
                 $userbar->additionalinfo = $lookup;
                 $userbar->construct_userbar($_REQUEST['sid']);
        }

        if($_REQUEST['do'] == 'iframe')
        {
                 header('Content-type:text/html;charset=' . vB_vBGamez::fetch_stylevar('charset') . ';');

                 vB_vBGamez::loadClassFromFile('frame');
                 $frame = new vBGamez_FrameView();
                 print $frame->construct_frame($_REQUEST['sid'], $server, $misc, $connectlink);
        }
}
if($_REQUEST['do'] == 'loadframe')
{
  if(!$vbulletin->options['vbgamez_can_use_codes'])
   {   
           print vB_vBGamez::jsonEncode('Codes disabled.'); exit;
   }

  $vbulletin->input->clean_array_gpc('p', array(
		'serverid' 	=> TYPE_INT, 'id' => TYPE_INT));

  $id = intval($vbulletin->GPC['serverid']);

  $lookup = vB_vBGamez::vbgamez_verify_id($id);
  $frameid = intval($vbulletin->GPC['id']);
  if (!$lookup)
  {
           print vB_vBGamez::jsonEncode('Server not in \'' . $vbulletin->options['bbtitle'] . '\' database'); exit;
  }

  $server = vB_vBGamez::vBG_Datastore_Cache($lookup['ip'], $lookup['q_port'], $lookup['c_port'], $lookup['s_port'], $lookup['type'], "sep", $lookup);

  $misc   = vB_vBGamez::vbgamez_server_misc($server);
  $server = vB_vBGamez::vbgamez_server_html($server);
  $connectlink = vbgamez_software_link($lookup['type'], $lookup['ip'], $lookup['c_port'], $lookup['q_port'], $lookup['s_port']);

  vB_vBGamez::loadClassFromFile('frame');
  $frame = new vBGamez_FrameView();
  print $frame->loadFrame($frameid, $id);
}
if($_REQUEST['do'] == 'loaduserbar')
{
  if(!$vbulletin->options['vbgamez_can_use_codes'])
   {   
           print vB_vBGamez::jsonEncode('Codes disabled.'); exit;
   }

  $vbulletin->input->clean_array_gpc('p', array(
		'serverid' 	=> TYPE_INT, 'id' => TYPE_INT));

  $id = intval($vbulletin->GPC['serverid']);

  $lookup = vB_vBGamez::vbgamez_verify_id($id);
  $userbarid = intval($vbulletin->GPC['id']);

  if (!$lookup)
  {
           print vB_vBGamez::jsonEncode('Server not in \'' . $vbulletin->options['bbtitle'] . '\' database'); exit;
  }

  $server = vB_vBGamez::vBG_Datastore_Cache($lookup['ip'], $lookup['q_port'], $lookup['c_port'], $lookup['s_port'], $lookup['type'], "sep", $lookup);

  $misc   = vB_vBGamez::vbgamez_server_misc($server);
  $server = vB_vBGamez::vbgamez_server_html($server);
  $connectlink = vbgamez_software_link($lookup['type'], $lookup['ip'], $lookup['c_port'], $lookup['q_port'], $lookup['s_port']);

  $frame = new vBGamez_Userbar();
  print $frame->loadUserbar($userbarid, $id);
}
// ########################## WHO PLAYING ON SERVER ###########################

if($_REQUEST['do'] == 'whoplaying')
{

	$vbulletin->input->clean_array_gpc('r', array(
		'serverid' 	=> TYPE_INT));

  $vbulletin->GPC['serverid'] = intval($vbulletin->GPC['serverid']);

  $lookup = vB_vBGamez::vbgamez_verify_id($vbulletin->GPC['serverid']);

  if (!$lookup)
  {
           eval(standard_error(fetch_error('invalidid', $vbphrase['vbgamez_server'])));
  }

  $server = vB_vBGamez::vBG_Datastore_Cache($lookup['ip'], $lookup['q_port'], $lookup['c_port'], $lookup['s_port'], $lookup['type'], "sep", $lookup);

  $misc   = vB_vBGamez::vbgamez_server_misc($server);
  $server = vB_vBGamez::vbgamez_server_html($server);
  $server = SortServerFields($server);

  if (empty($server['p']))
  {
        $playerfields['name'] = $vbphrase['vbgamez_no_players_info'];

        if(VBG_IS_VB4)
        {
            $player['status'] = 'user-offline.png';

            $templater = vB_Template::create('vbgamez_whoplayingbits');
            $templater->register('player', $player);
            $templater->register('playerfields', $playerfields);
            $players .= $templater->render();

        }else{

            $player['status'] = 'user_offline.gif';

            foreach ($playerfields as $field_key => $field)
            {
                   $playerfield .= ' <span class="shade">|</span> '.$field;
            }

            eval('$players .= "' . fetch_template('vbgamez_whoplayingbits') . '";');
        }

  }else{

      foreach ($server['p'] as $player_key => $player)
      {
        if(empty($player['name']) OR str_replace(' ', '', $player['name']) == '') { $player['name'] = 'Unknown name'; }
        if(VBG_IS_VB4)
        {
            $player['status'] = 'user-online.png';
            $playerfields = '';

            foreach (vB_vBGamez::fetch_player_fields($server) as $field_key => $field)
            {
                   $playerfields[] = $player[$field];
            }

            $templater = vB_Template::create('vbgamez_whoplayingbits');
            $templater->register('player', $player);
            $templater->register('playerfields', $playerfields);
            $players .= $templater->render();

        }else{

            $player['status'] = 'user_online.gif';
            $playerfield = '';

            foreach (vB_vBGamez::fetch_player_fields($server) as $field_key => $field)
            {
                   $playerfield .= " <span class=\"shade\">|</span> ".$player[$field];
            }

            eval('$players .= "' . fetch_template('vbgamez_whoplayingbits') . '";');
        }

      }
  }

  if(!VBG_IS_VB4)
  {
     foreach (vB_vBGamez::fetch_player_fields($server) as $field_key => $field)
     {
            $headfield .= ' <span class="shade">|</span> '.vB_vBGamez::fetch_player_fields_names($field);
     }

  }else{

     foreach (vB_vBGamez::fetch_player_fields($server) as $field_key => $field)
     {
            $headfields[$field] = vB_vBGamez::fetch_player_fields_names($field);
     }
  }

  if(VBG_IS_VB4)
  {
          $templater = vB_Template::create('vbgamez_whoplaying');

          $templater->register('players', $players);
          $templater->register_page_templates();
          $templater->register('players', $players);
          $templater->register('server', $server);
          $templater->register('headfields', $headfields);
          print_output($templater->render());
  }else{
	   eval('print_output("' . fetch_template('vbgamez_whoplaying') . '");');
  }

}


// ############################# TOP #####################
if($_REQUEST['do'] == 'top')
{

       if(!$vbulletin->options['vbgamez_top_enable'])
       {
            print_no_permission();
       }

       if($vbulletin->options['vbgamez_top_rating']) 
       {
              $top_rating_limit = intval($vbulletin->options['vbgamez_top_rating']);
              if(!empty($top_rating_limit))
              {
                       $show['top_rating'] = true;
              }
       }

       if($vbulletin->options['vbgamez_top_visiting']) 
       {
              $top_visiting_limit = intval($vbulletin->options['vbgamez_top_visiting']);
              if(!empty($top_visiting_limit))
              {
                       $show['top_visiting'] = true;
              }
       }


       if($vbulletin->options['vbgamez_top_maps']) 
       {
              $top_maps_limit = intval($vbulletin->options['vbgamez_top_maps']);
              if(!empty($top_maps_limit))
              {
                       $show['top_maps'] = true;
              }
       }

       if($vbulletin->options['vbgamez_top_views']) 
       {
              $top_views_limit = intval($vbulletin->options['vbgamez_top_views']);
              if(!empty($top_views_limit))
              {
                       $show['top_views'] = true;
              }
       }

       if($vbulletin->options['vbgamez_top_comments']) 
       {
              $top_comments_limit = intval($vbulletin->options['vbgamez_top_comments']);
              if(!empty($top_comments_limit))
              {
                       $show['top_comments'] = true;
              }
       }

       if($show['top_rating'] AND $vbulletin->options['vbgamez_ratingsystem_enable'])
       {
	      $ids = vB_vBGamez_Gamelist::prepareIdsToTopList('rating', $top_rating_limit);
              if($ids != '0')
	      {
			$select_servers_1 = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez AS vbgamez WHERE id IN ($ids) ORDER BY rating DESC");
              		while($server = $db->fetch_array($select_servers_1))
              		{
				$server['location_image'] = vB_vBGamez::hasLocationImage($server['location']);
                       		$server['text_icon'] = vB_vBGamez::vbgamez_text_type_game($server['type'], $server['cache_game']);
                       		$server['icon'] = vB_vBGamez::vbgamez_icon_game($server['type'], $server['cache_game']);
                       		$server['connectlink'] = vbgamez_software_link($server['type'], $server['ip'], $server['c_port'], $server['q_port'], $server['s_port']);
                       		$server['cache_name'] = vB_vBGamez::vbgamez_string_html($server['cache_name']);
                       		$server['has_map_image'] = vB_vBGamez::hasImage(vB_vBGamez::vbgamez_image_map($server['status'], $server['type'], $server['cache_game'], $server['cache_map']));
                       		$server['unique_type'] = '1';
                       		if(VBG_IS_VB4)
                       		{
                                 		$topratingserverbits[] = $server;
                       		}else{
                                 		eval('$topratingserverbits .= "' . fetch_template('vbgamez_top_bits') . '";');
                       		}
              		}
		}
       }

       if($show['top_visiting'])
       {
	      $ids = vB_vBGamez_Gamelist::prepareIdsToTopList('visiting', $top_visiting_limit);
	      if($ids != '0')
	      {
              		$select_servers_2 = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez AS vbgamez WHERE id IN ($ids) ORDER BY cache_players DESC");
             		while($server = $db->fetch_array($select_servers_2))
              		{
				$server['location_image'] = vB_vBGamez::hasLocationImage($server['location']);
                       		$server['text_icon'] = vB_vBGamez::vbgamez_text_type_game($server['type'], $server['cache_game']);
                       		$server['icon'] = vB_vBGamez::vbgamez_icon_game($server['type'], $server['cache_game']);
                       		$server['connectlink'] = vbgamez_software_link($server['type'], $server['ip'], $server['c_port'], $server['q_port'], $server['s_port']);
                       		$server['cache_name'] = vB_vBGamez::vbgamez_string_html($server['cache_name']);
                       		$server['unique_type'] = '2';
                       		$server['has_map_image'] = vB_vBGamez::hasImage(vB_vBGamez::vbgamez_image_map($server['status'], $server['type'], $server['cache_game'], $server['cache_map']));
                       		if(VBG_IS_VB4)
                       		{
                                 		$topvisitingserverbits[] = $server;
                       		}else{
                                 		eval('$topvisitingserverbits .= "' . fetch_template('vbgamez_top_bits') . '";');
                       		}
			}
              } 
       }

       if($show['top_maps'])
       {
              		$select_servers_3 = $db->query_read("SELECT vbgamez.*, COUNT(cache_map) AS count, SUM(cache_players) AS players_count, SUM(cache_playersmax - cache_players) AS playersmax_count FROM " . TABLE_PREFIX . "vbgamez AS vbgamez WHERE disabled = 0 AND valid = 0 GROUP BY cache_map HAVING COUNT(cache_map) > 1  ORDER by count DESC");
              		while($server = $db->fetch_array($select_servers_3))
              		{
                                if(empty($server['cache_map']) OR $server['cache_map'] == '---' OR $server['cache_map'] == '-') { continue; }
				$map_counter++;
				if($map_counter > $top_maps_limit) { continue; }
                       		$server['text_icon'] = vB_vBGamez::vbgamez_text_type_game($server['type'], $server['cache_game']);
                       		$server['icon'] = vB_vBGamez::vbgamez_icon_game($server['type'], $server['cache_game']);
                       		$server['connectlink'] = vbgamez_software_link($server['type'], $server['ip'], $server['c_port'], $server['q_port'], $server['s_port']);
                       		$server['unique_type'] = '3';
                       		$server['has_map_image'] = vB_vBGamez::hasImage(vB_vBGamez::vbgamez_image_map($server['status'], $server['type'], $server['cache_game'], $server['cache_map']));
                       		if(VBG_IS_VB4)
                       		{
                                 	$topmapserverbits[] = $server;
                       		}else{
                                 	eval('$topmapserverbits .= "' . fetch_template('vbgamez_top_bits_maps') . '";');
                       		}

              		}
       }

       if($show['top_views'])
       {
	      $ids = vB_vBGamez_Gamelist::prepareIdsToTopList('views', $top_views_limit);
	      if($ids != '0')
	      {
              		$select_servers_4 = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez as vbgamez WHERE id IN ($ids) ORDER BY views DESC");
              		while($server = $db->fetch_array($select_servers_4))
              		{
				$server['location_image'] = vB_vBGamez::hasLocationImage($server['location']);
                       		$server['text_icon'] = vB_vBGamez::vbgamez_text_type_game($server['type'], $server['cache_game']);
                       		$server['icon'] = vB_vBGamez::vbgamez_icon_game($server['type'], $server['cache_game']);
                       		$server['connectlink'] = vbgamez_software_link($server['type'], $server['ip'], $server['c_port'], $server['q_port'], $server['s_port']);
                       		$server['cache_name'] = vB_vBGamez::vbgamez_string_html($server['cache_name']);
                       		$server['unique_type'] = '4';
                       		$server['has_map_image'] = vB_vBGamez::hasImage(vB_vBGamez::vbgamez_image_map($server['status'], $server['type'], $server['cache_game'], $server['cache_map']));
                       		if(VBG_IS_VB4)
                       		{
                                 		$topviewserverbits[] = $server;
                       		}else{
                                 		eval('$topviewserverbits .= "' . fetch_template('vbgamez_top_bits') . '";');
                       		}
              		} 
       		}
       }

       if($show['top_comments'] AND $vbulletin->options['vbgamez_comments_enable'])
       {
	      $ids = vB_vBGamez_Gamelist::prepareIdsToTopList('comments', $top_comments_limit);
	      if($ids != '0')
              {
              		$select_servers_5 = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez as vbgamez WHERE id IN ($ids) ORDER BY comments DESC");
              		while($server = $db->fetch_array($select_servers_5))
              		{	
				$server['location_image'] = vB_vBGamez::hasLocationImage($server['location']);
                       		$server['text_icon'] = vB_vBGamez::vbgamez_text_type_game($server['type'], $server['cache_game']);
                       		$server['icon'] = vB_vBGamez::vbgamez_icon_game($server['type'], $server['cache_game']);
                       		$server['connectlink'] = vbgamez_software_link($server['type'], $server['ip'], $server['c_port'], $server['q_port'], $server['s_port']);
                       		$server['cache_name'] = vB_vBGamez::vbgamez_string_html($server['cache_name']);
                       		$server['unique_type'] = '5';
                       		$server['has_map_image'] = vB_vBGamez::hasImage(vB_vBGamez::vbgamez_image_map($server['status'], $server['type'], $server['cache_game'], $server['cache_map']));
                       		if(VBG_IS_VB4)
                       		{
                                 		$topcommentserverbits[] = $server;
                      		}else{
                                 		eval('$topcommentserverbits .= "' . fetch_template('vbgamez_top_bits') . '";');
                       		}
              		} 
       		}
       }

       if(VBG_IS_VB4)
       {

              if($topratingserverbits)
              {
                   $show['selected_rating'] = 'class="selected"';
              }else if($topvisitingserverbits)
              {
                   $show['selected_visiting'] = 'class="selected"';
              }else if($topviewserverbits)
              {
                   $show['selected_views'] = 'class="selected"';
              }else if($topcommentserverbits)
              {
                   $show['selected_comment'] = 'class="selected"';
              }else if($topmapserverbits)
              {
                   $show['selected_maps'] = 'class="selected"';
              }
      }else{
              if($topratingserverbits)
              {
                   $show['selected_rating'] = true;
              }else if($topvisitingserverbits)
              {
                   $show['selected_visiting'] = true;
              }else if($topviewserverbits)
              {
                   $show['selected_views'] = true;
              }else if($topcommentserverbits)
              {
                   $show['selected_comment'] = true;
              }else if($topmapserverbits)
              {
                   $show['selected_maps'] = true;
              }
      }

      if(empty($topratingserverbits) AND empty($topvisitingserverbits) AND empty($topviewserverbits) AND empty($topcommentserverbits) AND empty($topmapserverbits))
      {
             $show['listbits'] = false;

             if(VBG_IS_VB4)
             {
                  $emptybits = vB_Template::create('vbgamez_emptyservers')->render();
             }
      }else{
             $show['listbits'] = true;
             $emptybits = '';
      }

        $navbits['' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . ''] = $vbphrase['vbgamez'];
        $navbits[] = $vbphrase['vbgamez_top'];

        if($vbulletin->options['vbgamez_ratingsystem_enable'] AND $vbulletin->options['vbgamez_comments_enable'])
        {
            $vbg_colspan = 8;
        }elseif($vbulletin->options['vbgamez_ratingsystem_enable'] AND !$vbulletin->options['vbgamez_comments_enable'])
        {
            $vbg_colspan = 7;
        }elseif(!$vbulletin->options['vbgamez_ratingsystem_enable'] AND !$vbulletin->options['vbgamez_comments_enable'])
        {
            $vbg_colspan = 7;
        }elseif(!$vbulletin->options['vbgamez_ratingsystem_enable'] AND $vbulletin->options['vbgamez_comments_enable'])
        {
            $vbg_colspan = 6;
        }

        if(VBG_IS_VB4)
        {
              $templater = vB_Template::create('vbgamez_top');

              $navbits = construct_navbits($navbits);
              $navbar = render_navbar_template($navbits);
              $templater->register_page_templates();
              $templater->register('navbar', $navbar);
              $templater->register('topratingserverbits', $topratingserverbits);
              $templater->register('topvisitingserverbits', $topvisitingserverbits);
              $templater->register('topviewserverbits', $topviewserverbits);
              $templater->register('topcommentserverbits', $topcommentserverbits);
              $templater->register('topmapserverbits', $topmapserverbits);

              if(!$show['listbits'])
              {
                            $templater->register('emptybits', $emptybits);
              }

              print_output($templater->render());
       }else{
              if(!$show['listbits'])
              {
                            eval('$emptybits = "' . fetch_template('vbgamez_emptyservers') . '";');
              }
  
  	      eval('$vbg_css = "' . fetch_template('vbgamez.css') . '";');
	      $navbits = construct_navbits($navbits);
	      eval('$navbar = "' . fetch_template('navbar') . '";');
	      eval('print_output("' . fetch_template('vbgamez_top') . '");');
       }
}

// ############################# VIEW DETALIS  #####################
if($_REQUEST['do'] == 'viewdetalis')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'id' 		=> TYPE_INT,
	));

        $lookup = vB_vBGamez::vbgamez_verify_id($vbulletin->GPC['id']);

        if (!$lookup)
        {
                    eval(standard_error(fetch_error('invalidid', $vbphrase['vbgamez_server'])));
        }

        if(vBGamez_dbGames_Bootstrap::vbgamez_is_db_game($lookup['type']))
        {
                 $server['s']['name'] = vB_vBGamez::vbgamez_string_html($lookup['cache_name']);

                 $class = vBGamez_dbGames_Bootstrap::fetchClassLibary($lookup['type'],$lookup['dbinfo']);
                 $server['e'] = $class->fetch_info_additional_info();
        }else{

                 $server = vB_vBGamez::vBG_Datastore_Cache($lookup['ip'], $lookup['q_port'], $lookup['c_port'], $lookup['s_port'], $lookup['type'], "sep", $lookup);
        }

        $server = vB_vBGamez::vbgamez_server_html($server);

        if(!empty($server['e']))
        {
          foreach($server['e'] as $field => $value)
          {
            if(!empty($value))
             {
               if($field == 'uptime')
               {
                         $uptime = vBGamez_dbGames_Bootstrap::construct_uptime($value);

                         $value = construct_phrase($vbphrase['vbgamez_uptime_server'], $uptime['day'], $uptime['hour'], $uptime['min'], $uptime['sec']);
               }

               $field = vB_vBGamez::vbgamez_translate_field($field);

               if(VBG_IS_VB4)
               {
                         $templater = vB_Template::create('vbgamez_infobits');
                         $templater->register('key', $field);
                         $templater->register('server', $lookup);
                         $templater->register('val', $value);
                         $infobits .= $templater->render();
               }else{
                         eval('$infobits .= "' . fetch_template('vbgamez_infobits') . '";');
               }
             }
        }
       }

  if(VBG_IS_VB4)
  {
        $templater = vB_Template::create('vbgamez_viewinfo');

        $navbits = construct_navbits($navbits);
        $navbar = render_navbar_template($navbits);
        $templater->register_page_templates();
        $templater->register('server', $server);
        $templater->register('infobits', $infobits);
        print_output($templater->render());
  }else{
	eval('print_output("' . fetch_template('vbgamez_viewinfo') . '");');
  }
}


// ############################# VIEW BANNED  #####################
if($_REQUEST['do'] == 'viewbanned')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'id' 		=> TYPE_INT,
	));

        $lookup = vB_vBGamez::vbgamez_verify_id($vbulletin->GPC['id']);

        if (!$lookup)
        {
                    eval(standard_error(fetch_error('invalidid', $vbphrase['vbgamez_server'])));
        }

        if(vBGamez_dbGames_Bootstrap::vbgamez_is_db_game($lookup['type']))
        {
                 $server['s']['name'] = vB_vBGamez::vbgamez_string_html($lookup['cache_name']);

                 $class = vBGamez_dbGames_Bootstrap::fetchClassLibary($lookup['type'],$lookup['dbinfo']);
                 $server['e'] = $class->fetch_info_banned();

        }else{

                 $server = vB_vBGamez::vBG_Datastore_Cache($lookup['ip'], $lookup['q_port'], $lookup['c_port'], $lookup['s_port'], $lookup['type'], "sep", $lookup);
        }

        $server = vB_vBGamez::vbgamez_server_html($server);

        if(!empty($class->banned))
        {
           foreach($class->banned as $field => $value)
           {
               if(!$value['username']) { continue; }

               if(VBG_IS_VB4)
               {
                         $templater = vB_Template::create('vbgamez_infobits');
                         $templater->register('key', vB_vBGamez::vbgamez_name_filtered($value['username']));
                         $templater->register('server', $lookup);
                         $templater->register('val', vbdate($vbulletin->options['dateformat'].' '.$vbulletin->options['timeformat'], $value['unbandate'], true));
                         $bannedaccsbits .= $templater->render();
               }else{
                         $value = vbdate($vbulletin->options['dateformat'].' '.$vbulletin->options['timeformat'], $value['unbandate'], true);
                         eval('$bannedaccsbits .= "' . fetch_template('vbgamez_infobits') . '";');
               }
            }
        }

        if(!empty($class->bannedips))
        {
           foreach($class->bannedips as $field => $value)
           {
               if(!$field) { continue; }

               if(VBG_IS_VB4)
               {
                         $templater = vB_Template::create('vbgamez_infobits');
                         $templater->register('key', $field);
                         $templater->register('server', $lookup);
                         $templater->register('val', vbdate($vbulletin->options['dateformat'].' '.$vbulletin->options['timeformat'], $value['unbandate'], true));
                         $bannedipsbits .= $templater->render();
               }else{
                         $value = vbdate($vbulletin->options['dateformat'].' '.$vbulletin->options['timeformat'], $value['unbandate'], true);

                         eval('$bannedipsbits .= "' . fetch_template('vbgamez_infobits') . '";');
               }
           }
        }

  if(VBG_IS_VB4)
  {
        $templater = vB_Template::create('vbgamez_banned_list');

        $navbits = construct_navbits($navbits);
        $navbar = render_navbar_template($navbits);
        $templater->register_page_templates();
        $templater->register('server', $server);
        $templater->register('bannedipsbits', $bannedipsbits);
        $templater->register('bannedaccsbits', $bannedaccsbits);

        print_output($templater->render());
  }else{
	eval('print_output("' . fetch_template('vbgamez_banned_list') . '");');
  }
}

// ############################# PLAYERS HISTORY  #####################
if($_REQUEST['do'] == 'getgraphic' OR $_REQUEST['do'] == 'getgraphic2')
{
          if(!$vbulletin->options['vbgamez_graphics'])
          {
                 exit;
          }

          $id = intval($_REQUEST['id']);

          $lookup = vB_vBGamez::vbgamez_verify_id($id);

	  if(!$_REQUEST['type'])
	  {
		$_REQUEST['type'] = 1;
	  }

          if(!$lookup) { eval(standard_error(fetch_error('invalidid', $vbphrase['vbgamez_server']))); }

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
		exit;
	  }

          for($i=1; $i<count($stats['data']) + 1; $i++)
          {	
			  if($_REQUEST['do'] == 'getgraphic2' AND $stats['data'][$i] > 30)
			  {
				$stats['data'][$i] = $stats['data'][$i] / 4;
			  }

                          $data[$i] = array('Serie1' => $stats['data'][$i], 'Name' => $instance->getXPositionsName($i, $_REQUEST['type']));
          }
	  
}

if($_REQUEST['do'] == 'getgraphic')
{
          $Test = new pChart(700,230);
          $Test->setFontProperties("./packages/vbgamez/3rd_party_classes/pchart/Fonts/tahoma.ttf",8);   
          $Test->setGraphArea(60,30,680,190);   
          $Test->drawFilledRoundedRectangle(7,7,695,223,5,240,240,240);    // color background 
          $Test->drawRoundedRectangle(5,5,695,225,5,230,230,230);   // color border
          $Test->drawGraphArea(255,255,255,TRUE); // graph area color
          $Test->drawScale($data,$desc,SCALE_NORMAL,150,150,150,TRUE,0,2);   
          $Test->drawGrid(4,TRUE,230,230,230,50);
  
          $Test->setFontProperties("./packages/vbgamez/3rd_party_classes/pchart/Fonts/tahoma.ttf",6);   
          $Test->drawTreshold(0,143,55,72,TRUE,TRUE);   
  
          $Test->drawLineGraph($data,$desc);   
          $Test->drawPlotGraph($data,$desc,3,2,255,255,255);   
  
          $Test->setFontProperties("./packages/vbgamez/3rd_party_classes/pchart/Fonts/tahoma.ttf",8);   
          $Test->drawLegend(75,35,$desc,255,255,255);   
          $Test->setFontProperties("./packages/vbgamez/3rd_party_classes/pchart/Fonts/tahoma.ttf",10);   
          $Test->drawTitle(60,22, vB_vBGamez::vbg_set_charset($phrase),50,50,50,685);   
          $Test->Stroke();

}
if($_REQUEST['do'] == 'getgraphic2')
{
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
	  if(!$_REQUEST['color'] OR $_REQUEST['color'] == 'black')
	  {
		$graph_area_colors = array(56,56,56);
		$graph_area_colors2 = array(50, 50, 50);
		$graph_area_colors3 = array(255, 255, 255);
		$border = 0;
          }else{
		$graph_area_colors = array(255,255,255);
		$graph_area_colors2 = array(230, 230, 230);
		$graph_area_colors3 = array(0, 0, 0);
		$border = 5;
	  }

 	  // Initialise the graph
 	  $Test = new pChart($_REQUEST['width'],$_REQUEST['height']);
	  $Test->setColorPalette(0, $graph_area_colors3[0], $graph_area_colors3[1], $graph_area_colors3[2]);
 	  $Test->setFontProperties("./packages/vbgamez/3rd_party_classes/pchart/tahoma.ttf",8);
 	  $Test->drawFilledRoundedRectangle($border,$border,$_REQUEST['width']+2,$_REQUEST['height']+2,2,$graph_area_colors2[0], $graph_area_colors2[1], $graph_area_colors2[2]);
 	  $Test->setGraphArea(5,5,$_REQUEST['width']-5,$_REQUEST['height']-5);
 	  $Test->drawGraphArea($graph_area_colors[0],$graph_area_colors[1],$graph_area_colors[2], TRUE);
 	  $Test->drawScale($data,$desc,SCALE_NORMAL,$graph_area_colors2[0], $graph_area_colors2[1], $graph_area_colors2[2],FALSE, 0, 2);

          $Test->drawLineGraph($data,$desc);   
 	  $Test->drawLineGraph($data,$desc);
     	  if($Test->ErrorReporting)
      		$Test->printErrors("GD");

     	 /* Save image map if requested */
         if ( $Test->BuildMap )
              $Test->SaveImageMap();

              header('Content-type: image/jpeg');
              imagejpeg($Test->Picture, '', 100);

}
// ############################# ADD SERVER  #####################

if($_REQUEST['do'] == 'addserver')
{
             exec_header_redirect($vbulletin->options['vbgamez_usercp_path'].'?do=addserver', 301);
}

// ############################# AJAX SHOW STEAM  #####################

if ($_REQUEST['do'] == 'showsteam')
{

	$vbulletin->input->clean_array_gpc('r', array('game' 	=> TYPE_STR));

        print vB_vBGamez::vbg_ajax_show_steam($vbulletin->GPC['game']); exit;
}

// ------- ######################### Fetch additional game type #############

if ($_POST['do'] == 'fetch_type')
{

	$vbulletin->input->clean_array_gpc('r', array('type' 	=> TYPE_STR, 'selected' => TYPE_STR));

        foreach(vB_vBGamez::fetch_additional_game_type($vbulletin->GPC['type']) AS $type => $game)
        {
                $types .= '<option value="' . $type . '" ' . iif($vbulletin->GPC['selected'] == $type, 'selected="selected"') . '>' . $game . '</option>';
        }

        if(!empty($types))
        {
                print '<select class="textbox" name="additional_game" id="vbgadditional_game" tabindex="1">' . $types . '</select>';
        }

        exit;
}

// ------- ######################### AJAX update server rating ##########
if ($_POST['do'] == 'updrating')
{  
	$vbulletin->input->clean_array_gpc('r', array('serverid' 	=> TYPE_INT));

        vB_vBGamez::vbg_ajax_update_rating($vbulletin->GPC['serverid']);

}


// ############################# AJAX UPLOAD MAP #####################
if($_REQUEST['do'] == 'uploadmap')
{
        if (!$vbulletin->userinfo['userid'] AND !$vbulletin->options['vbgamez_allow_uploadmaps_user'])
        {
          print_no_permission();
        }

        if(!$vbulletin->options['vbgamez_maps_upload'])
        {
              print_no_premission();
        }

	$vbulletin->input->clean_array_gpc('p', array(
		'mapname' 	=> TYPE_NOHTML,
		'type' 	=> TYPE_NOHTML,
		'id' 	=> TYPE_INT,
		'game' 	=> TYPE_NOHTML
        ));

        if(VBG_IS_VB4)
        {
                $templater = vB_Template::create('vbgamez_uploadmap');
                $templater->register('mapname', $vbulletin->GPC['mapname']);
                $templater->register('type', $vbulletin->GPC['type']);
                $templater->register('game', $vbulletin->GPC['game']);
                $templater->register('id', $vbulletin->GPC['id']);

                print_output($templater->render());
        }else{
               	eval('print_output("' . fetch_template('vbgamez_uploadmap') . '");'); 
        }
}

if($_REQUEST['do'] == 'douploadmap')
{

     if (!$vbulletin->userinfo['userid'] AND !$vbulletin->options['vbgamez_allow_uploadmaps_user'])
     {
                 print_no_permission();
     }

     if(!$vbulletin->options['vbgamez_maps_upload'])
     {
                 print_no_premission();
     }

     $vbulletin->input->clean_array_gpc('p', array(
		'ajax' 	=> TYPE_INT,
		'id' 	=> TYPE_INT,
		'filename' 	=> TYPE_NOHTML,
		'game' 	=> TYPE_NOHTML,
		'type' 	=> TYPE_NOHTML,
		'mapname' 	=> TYPE_NOHTML
        ));

     if($vbulletin->GPC['ajax'])
     {
          $filename = $vbulletin->GPC['filename'];
     }else{

          $vbulletin->input->clean_array_gpc('f', array('filename' => TYPE_FILE));

          $filename = $vbulletin->GPC['filename']['name'];
     }

     $type = vB_vBGamez::parse_game($vbulletin->GPC['type']); 

     $game = vB_vBGamez::parse_game($vbulletin->GPC['game']);


     $map = vB_vBGamez::parse_game($vbulletin->GPC['mapname']);

     $maxfilesize = $vbulletin->options['vbgamez_upload_map_maxsize'];

     if(empty($maxfilesize))
     { 
            $maxfilesize = ini_size_to_bytes(ini_get('post_max_size')) / 1024;
     }

     if($filename == '')
     {
             $error = $vbphrase['vbgamez_upload_empty_file'];
     }

     $ext = strtolower(substr($filename, strrpos($filename, '.')+1));
 
     if (!$error AND !strstr("|".str_replace(" ","|",'jpg gif png')."|",$ext))
     {
             $error = $vbphrase['vbgamez_upload_map_unknown_file']; 
     }


    $location = array(
      "images/vbgamez/maps/{$type}/{$game}/{$map}.jpg",
      "images/vbgamez/maps/{$type}/{$game}/{$map}.gif",
      "images/vbgamez/maps/{$type}/{$game}/{$map}.png",
      "images/vbgamez/maps/{$type}/{$map}.jpg",
      "images/vbgamez/maps/{$type}/{$map}.gif",
      "images/vbgamez/maps/{$type}/{$map}.png",

      "images/vbgamez/maps/{$type}/{$game}/{$map}_moderate.jpg",
      "images/vbgamez/maps/{$type}/{$game}/{$map}_moderate.gif",
      "images/vbgamez/maps/{$type}/{$game}/{$map}_moderate.png",
      "images/vbgamez/maps/{$type}/{$map}_moderate.jpg",
      "images/vbgamez/maps/{$type}/{$map}_moderate.gif",
      "images/vbgamez/maps/{$type}/{$map}_moderate.png");

    foreach ($location as $path)
    {
      if(!$error AND file_exists($path)) { $error = $vbphrase['vbgamez_upload_exists']; }
    }

    if($vbulletin->GPC['ajax'])
    {
          if(empty($error))
          {
                   print "OK";
          }else{ 
                   vB_vBGamez::print_or_standard_error($error);
          }
    }else{

          $vbgamez_protocol_list = vbgamez_protocol_list();

	  if (!$vbgamez_protocol_list[$type])
          {
                   standard_error(fetch_error('vbgamez_upload_empty_game'));
          }

	 if (($vbulletin->GPC['filename']['size'] / 1024) > $maxfilesize)
	 {
		   eval(standard_error(fetch_error('vbgamez_upload_invalid_filesize', $maxfilesize)));
	 }

         require_once('./includes/class_image.php');
         $imageverify = vB_Image::fetch_library($vbulletin);

         if(!$error AND $imageinfo = $imageverify->fetch_image_info($vbulletin->GPC['filename']['tmp_name']))
         {
                            // good
         }else{
		   eval(standard_error($vbphrase['vbgamez_upload_map_unknown_file']));
         }

         $backtoserverlink = $vbulletin->options['vbgamez_path'].'?do=view&amp;id='.$vbulletin->GPC['id'];

          if(empty($error))
          {
                 if($vbulletin->options['vbgamez_uploadmap_moderation'] AND vB_vBGamez::check_permissions())
                 {

                  $uploadto_with_subdir = "./images/vbgamez/maps/$type/" . iif($game, "$game/") . "" . $map . "_moderate." . $ext . "";
                  $uploadto_without_subdir = "./images/vbgamez/maps/$type/" . $map . "_moderate." . $ext . "";
                  $complete_phrase = 'vbgamez_map_upload_complete_moderation';
		  $map_moderation = 1;
                 }else{

                  $uploadto_with_subdir = "./images/vbgamez/maps/$type/" . iif($game, "$game/") . "$map.$ext";
                  $uploadto_without_subdir = "./images/vbgamez/maps/$type/$map.$ext";
                  $complete_phrase = 'vbgamez_map_upload_complete';
                 }

                  $uploaddir_with_subdir = "./images/vbgamez/maps/$type" . iif($game, "/$game") . "/";
                  $uploaddir_without_subdir = "./images/vbgamez/maps/$type/";

                  $mkdir_folder = DIR ."/images/vbgamez/maps/$type" . iif($game, "/$game") . "";

                   if(@is_writable($uploaddir_with_subdir) AND @is_dir($uploaddir_with_subdir))
                   {
                       $return = @copy($vbulletin->GPC['filename']['tmp_name'], $uploadto_with_subdir);

                       $vbg_uploadmap_dir = $uploadto_with_subdir;
                       $is_uploaded = true;

                   }elseif (@is_writable($uploadto_without_subdir) AND @is_dir($uploaddir_without_subdir)){

                       $return = @copy($vbulletin->GPC['filename']['tmp_name'], $uploadto_without_subdir);

                       $vbg_uploadmap_dir = $uploadto_without_subdir;

                       $is_uploaded = true;

                   }else{

                       @mkdir($mkdir_folder,  0777, true);

                       $return = @copy($vbulletin->GPC['filename']['tmp_name'], $uploadto_with_subdir);

                       $vbg_uploadmap_dir = $uploadto_with_subdir;

                       $is_uploaded = true;
                   }

               if($is_uploaded AND $return)
               {
                       @vB_vBGamez::resize_map_image($vbg_uploadmap_dir);

                       $db->query("INSERT INTO " . TABLE_PREFIX . "vbgamez_custom_maps (userid, type, game, mapname, date, uploadedto, moderation) VALUES
                              ('" . intval($vbulletin->userinfo['userid']) . "',
                               ".$db->sql_prepare($type).",
                               ".$db->sql_prepare($game).",
                                ".$db->sql_prepare($vbulletin->GPC['mapname']).",
                                ".TIMENOW.",
                                ".$db->sql_prepare($vbg_uploadmap_dir).",
                                ".iif(vB_vBGamez::check_permissions(), $vbulletin->options['vbgamez_uploadmap_moderation'], 0).")");

                       vB_vBGamez::send_pm('UploadMap', $filename);
                       if($map_moderation)
                       {
                                      standard_error(fetch_error($complete_phrase, $backtoserverlink));
                       }else{
                                      exec_header_redirect($backtoserverlink);
                       }
               }else{
					eval(standard_error(fetch_error('vbgamez_map_upload_notcomplete')));
				}
          }else{ 
                  standard_error($error);
          }
    }

}

// SHOW MAP
if($_REQUEST['do'] == 'showmap')
{
     if(empty($_REQUEST['type']) OR empty($_REQUEST['game']) OR empty($_REQUEST['map'])) { exit; }

     $mapimage = vB_vBGamez::vbgamez_image_map($_REQUEST['status'], $_REQUEST['type'], $_REQUEST['game'], $_REQUEST['map']);

     vB_vBGamez::vbgamez_show_map($mapimage);
}

if ($_REQUEST['do'] == 'lightbox')
{
	$vbulletin->input->clean_array_gpc('r', array(
                'game' => TYPE_NOHTML,
                'status' => TYPE_INT,
                'type' => TYPE_NOHTML,
                'map' => TYPE_NOHTML,
                'uniqueid' => TYPE_INT
		));

		require_once(DIR . '/includes/class_xml.php');
		$xml = new vB_AJAX_XML_Builder($vbulletin, 'text/xml');

		$imagelink = '' . $vbulletin->options['vbgamez_path'] . '?do=showmap&amp;status='.$vbulletin->GPC['status'].'&amp;type='.$vbulletin->GPC['type'].'&amp;game='.$vbulletin->GPC['game'].'&amp;map='.$vbulletin->GPC['map'];
                $imagelink = str_replace('&amp;', '&', $imagelink);

                $attachmentinfo['time_string'] = '&nbsp;';
                $attachmentinfo['filename'] = $vbulletin->GPC['map'];
                $uniqueid = $vbulletin->GPC['uniqueid'];

                if(VBG_IS_VB4)
                {
		        $templater = vB_Template::create('lightbox');
			$templater->register('attachmentinfo', $attachmentinfo);
			$templater->register('imagelink', $imagelink);
			$templater->register('uniqueid', $uniqueid);
		        $html = $templater->render(true);

                // !- VBSEO BUG FIX
                $html = str_replace(vB_Template_Runtime::fetchStyleVar('imgdir_misc').'/lightbox_progress.gif', $vbulletin->options['bburl'].'/'.vB_Template_Runtime::fetchStyleVar('imgdir_misc').'/lightbox_progress.gif', $html);
                // - VBSEO BUG FIX

                }else{
                        eval('$html = "' . fetch_template('lightbox', 0, 0) . '";');

                        // !- VBSEO BUG FIX
                        $html = str_replace($stylevar['imgdir_misc'].'/lightbox_progress.gif', $vbulletin->options['bburl'].'/'.$stylevar['imgdir_misc'].'/lightbox_progress.gif', $html);
                        // - VBSEO BUG FIX

                }

		$xml->add_group('img');
		$xml->add_tag('html', process_replacement_vars($html));
		$xml->add_tag('link', $imagelink);
		$xml->add_tag('name', $vbulletin->GPC['map']);
		$xml->add_tag('date', '00.00.00');
		$xml->add_tag('time', '00:00');
		$xml->close_group();

		$xml->print_xml();
}
?>