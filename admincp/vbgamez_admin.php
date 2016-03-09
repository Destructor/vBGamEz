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

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
@set_time_limit(0);
ignore_user_abort(true);
// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('style', 'vbgamez', 'profilefield', 'cprofilefield');
$specialtemplates = array('products', 'vbgamez_fieldcache');
 
// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once('./packages/vbgamez/bootstrap.php');

require_once('./packages/vbgamez/manager/userbar.php');
require_once('./includes/adminfunctions_template.php');
define('THIS_SCRIPT', 'ADMINCP_VBG');

// ############################# LOG ACTION ###############################
log_admin_action();

// Load Userbar Manager
require_once('./packages/vbgamez/manager/userbar.php');
$userbar_manager = new vBGamEz_Userbar_Manager($vbulletin);
$userbar_manager->bootstrap($vbphrase);

// Field manager
require_once('./packages/vbgamez/manager/field.php');
$field_dm = new vBGamEz_FieldManager($vbulletin);

// Frame manager
require_once('./packages/vbgamez/manager/frame.php');
$frame_manager = new vBGamEz_Frame_Manager($vbulletin);
$frame_manager->bootstrap($vbphrase);

vB_vBGamez_Route::setUrls();

// ############################# ACTIONS ###############################

if (empty($_REQUEST['do']))  
 { 
   exec_header_redirect('vbgamez_admin.php?' . $vbulletin->session->vars['sessionurl'] . "do=list");
 }

// ############################# Functions ###############################
function admincp_vbg_update_userinfo($userid)
{
    global $vbulletin;

    if(!$userid) { return false; }

    $get_counter = $vbulletin->db->query_read("SELECT count(id) FROM " . TABLE_PREFIX ."vbgamez WHERE userid = '" . intval($userid) . "' AND disabled = 0");

    $counter = $vbulletin->db->fetch_row($get_counter);

    $vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET servers = '" . intval($counter[0]) . "' WHERE userid = '" . intval($userid) . "'");

}

function admincp_vbg_get_userid($serverid)
{
    global $vbulletin;

    $select_userid = $vbulletin->db->query("SELECT userid FROM " . TABLE_PREFIX . "vbgamez WHERE id = '" . $serverid . "'");
    $select_userid = $vbulletin->db->fetch_array($select_userid);


    return $select_userid['userid'];
}

function getRedirectUrl($type = '', $game = '')
{
	global $vbulletin;
	if(!$type)
	{
		return 'list'; 
	}
	if($vbulletin->options['vbgamez_admincp_show_as_types'])
	{
   		if(vB_vBGamez::fetch_additional_game_type($type) == array())
   		{
                   return 'list2&type='.$type;
   		}else{
	   				return 'list2&type='.$type.'&addtype='.$game;
   		}
	}else{
		return 'list2';
	}
}
if(!$vbulletin->options['vbgamez_comments_userdisable'])
{
  $_POST['commentsenable'] = 1;
}

$dig_gS = '<font color="green">';
$dig_gE = '</font>';

$dig_rS = '<font color="red">';
$dig_rE = '</font>';

// ################## Список серверов ##################
if($_REQUEST['do'] == 'list')
{
	if($vbulletin->options['vbgamez_admincp_show_as_types'])
	{
		$_REQUEST['do'] = 'list1';
	}else{
		$_REQUEST['do'] = 'list2';
	}
}
if($_REQUEST['do'] == 'list1')
{
	require_once('./packages/vbgamez/gamelist.php');
print '	<style type="text/css">#startpage_games {
		display:table;
		width:95%;
		padding:10px 10px 0px 10px;

		border-bottom-width:0;
	}

	#startpage_games span {
		display:table-cell;
		width:10%;
	}</style>';
        $total = vB_vBGamez::vbgamez_cached_totals();

        $select_cacheservers = $db->query_read("SELECT type, cache_game
						FROM " . TABLE_PREFIX . "vbgamez AS vbgamez
						WHERE valid = 0 ");

        while($server = $db->fetch_array($select_cacheservers))
        {
				$server['cache_game'] = strtolower($server['cache_game']);
				$server['type'] = strtolower($server['type']);
				
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


                                         $icon = vB_vBGamez::vbgamez_icon_game($type, $game);
                                         $gametype = vB_vBGamez::vbgamez_text_game($type, $game);

                                         $games .= $span.'<span><img src="../' . $icon . '" style="vertical-align: middle;" title="' . $gametype . '" alt="' . $gametype . '"> <a href="vbgamez_admin.php?do=list2&amp;type=' . $type . ''.iif($showAddType, '&amp;addtype=' . $game . '').'">' . $gametype . '</a> <font class="shade">(' . $key . ')</font></span>';
                     }                   

                     $gameListTotal = $gamecounter/3;

                     if (@strpos($gameListTotal, "."))
                     {
                             $games .= '<span>&nbsp;</span></span>';
                     }

        }else{
                     $games = '<span id="startpage_games">'.$vbphrase['vbgamez_empty_servers'] . '<br /></span>';
        }


		print_cp_header($vbphrase['vbgamez_admincp_vbgameztitle']);

		print_form_header('', '', 1);

		print_table_header($vbphrase['vbgamez_admincp_view_all_servers']);

		print_description_row($games);
		print_cells_row(array(
		'<input type="button" class="button" value="' . $vbphrase['vbgamez_add_server_admin'] . '" onclick="window.location=\'vbgamez_admin.php?' . $vbulletin->session->vars['sessionurl'] . 'do=add\';" />', 
		'<span style="float:right;"><input type="button" class="button" value="' . $vbphrase['vbgamez_show_as_list'] . '" onclick="window.location=\'vbgamez_admin.php?' . $vbulletin->session->vars['sessionurl'] . 'do=list2\';" /></div>')
	);
		print_table_footer();
}

if ($_REQUEST['do'] == 'list2')
{
	print_cp_header($vbphrase['vbgamez_admincp_vbgameztitle']);

	?>
	<script type="text/javascript">
	function js_page_jump(i, sid)
	{
		var sel = fetch_object("prodsel" + i);
		var act = sel.options[sel.selectedIndex].value;
		if (act != '')
		{
			switch (act)
			{
				case 'serverdisable': page = "vbgamez_admin.php?do=disable&id="; break;
				case 'serverenable': page = "vbgamez_admin.php?do=enable&id="; break;
				case 'serveredit': page = "vbgamez_admin.php?do=modify&id="; break;
				case 'serverdelete': page = "vbgamez_admin.php?do=delete&id="; break;
				default: return;
			}
			document.cpform.reset();
			jumptopage = page + sid + "&s=<?php echo $vbulletin->session->vars['sessionhash']; ?>";
			window.location = jumptopage;
		}
		else
		{
			alert('<?php echo addslashes_js($vbphrase['invalid_action_specified']); ?>');
		}
	}
	</script>
	<?php
	print_form_header('vbgamez_admin', 'order');


	$i = 0;
	$is_custom = false;
	if($_REQUEST['type'] AND $_REQUEST['addtype'])
	{
		$db_query = 'AND type = ' . $db->sql_prepare($_REQUEST['type']) . ' AND cache_game = ' . $db->sql_prepare($_REQUEST['addtype']) . '';
		$is_custom = true;
	}elseif($_REQUEST['type'] AND !$_REQUEST['addtype'])
	{
		$db_query = 'AND type = ' . $db->sql_prepare($_REQUEST['type']) . '';
		$is_custom = true;
	}
	
	
	print_table_header(iif($is_custom, '<font color="blue">' . vB_vBGamez::vbgamez_text_game($_REQUEST['type'], $_REQUEST['addtype']) . '</font>', $vbphrase['vbgamez_admincp_view_all_servers']), 6); 

	print_cells_row(array($vbphrase['vbgamez_admincp_server_name'], $vbphrase['vbgamez_admincp_game_type'], $vbphrase['ip_address'], $vbphrase['vbgamez_admincp_poryadok'], $vbphrase['controls']), 1);
	
	
	$servers = $db->query_read("SELECT vbgamez.*, user.username FROM " . TABLE_PREFIX . "vbgamez AS vbgamez LEFT JOIN " . TABLE_PREFIX . "user AS user ON (user.userid = vbgamez.userid) WHERE vbgamez.valid = 0 $db_query ORDER BY vbgamez.zone DESC");

	while ($server = $db->fetch_array($servers))
	{
                if(empty($title)) { $tite = $vbphrase['vbgamez_server_unknown_name']; }

		$title = vB_vBGamez::vbgamez_string_html($server['cache_name']);

		if (vbstrlen($title) == 0)
		{
			$title = $vbphrase['vbgamez_server_unknown_name'];
		}
		$username = '';
		if($server['username'])
		{
			$username = ' <span class="shade smallfont">(<a href="user.php?do=edit&u=' . $server['userid'] . '">'.$server['username'].'</a>)</span>';
		}
		if ($server['disabled'])
		{
			$title = "<strike>$title</strike>".$username;
		}else{
			$title = '<a href="' . $vbulletin->options['vbgamez_path'] . '?do=view&id=' . $server['id'] . '" target="_blank">'.$title.'</a>'.$username;
		}

		$options = array('serveredit' => $vbphrase['edit']);

		if (!$server['disabled'])
		{
			$options['serverdisable'] = $vbphrase['disable'];
		}
		else
		{
			$options['serverenable'] = $vbphrase['enable'];
		}

		$options['serverdelete'] = $vbphrase['delete'];

                $game_type = '<center><img alt="" src="../' . vB_vBGamez::vbgamez_icon_game($server['type'], $server['cache_game']) . '" title="' . vB_vBGamez::vbgamez_text_type_game($server['type'], $server['cache_game']) . '"/></center>';
                $i++;

		print_cells_row(array(
			$title,
                        $game_type,
			$server['ip'].":".$server['c_port'],
                        '<center><input name="displayorder[' . $server[id] . ']" value="'.$server['zone'].'" class="bginput" size="4" style="text-align: right;" type="text"></center>',
			"<div align=\"" . vB_vBGamez::fetch_stylevar('right') . "\">
				<select name=\"s$server[id]\" id=\"prodsel$i\" onchange=\"js_page_jump($i, '$server[id]')\" class=\"bginput\">
					" . construct_select_options($options) . "
				</select>&nbsp;<input type=\"button\" class=\"button\" value=\"" . $vbphrase['go'] . "\" onclick=\"js_page_jump($i, '$server[id]');\" />
			</div>"
		), false, '', -2);
	}
		print_cells_row(array(
		'<input type="button" class="button" value="' . $vbphrase['vbgamez_add_server_admin'] . '" onclick="window.location=\'vbgamez_admin.php?' . $vbulletin->session->vars['sessionurl'] . 'do=add\';" />', '', '', '', 
		($i ? '<div align="' . vB_vBGamez::fetch_stylevar('right') . '"><input type="hidden" name="type" value="' . $_REQUEST['type'] . '"><input type="hidden" name="addtype" value="' . $_REQUEST['addtype'] . '"><input type="submit" class="button" accesskey="s" value="' . $vbphrase['save_display_order'] . '" />' : '&nbsp;'))
	);

	print_table_footer();

}

// ################## Удаление сервера  ##################
if($_REQUEST['do'] == 'delete')
{
        $_REQUEST['id'] = intval($_REQUEST['id']);

	if($_REQUEST['id'] == '')
	{
		 print_cp_message($vbphrase['vbgamez_admincp_no_selected']);  
	} 
	else 
	{ 

                 print_cp_header($vbphrase['vbgamez_fieldmanager']);

	         print_form_header('vbgamez_admin', 'killserver');
	         construct_hidden_code('id', $_REQUEST['id']);
	         construct_hidden_code('moderate', $_REQUEST['moderate']);
	         construct_hidden_code('jsredirect', $_REQUEST['jsredirect']);

	         print_table_header($vbphrase['confirm_deletion']);

	         print_description_row($vbphrase['vbgamez_delete_server_confirm']);

	         print_submit_row($vbphrase['yes'], '', 2, $vbphrase['no']);

	}

}

if ($_POST['do'] == 'killserver')
{
        $_POST['id'] = intval($_POST['id']);
        
	if($_POST['id'] == '')
	{
		 print_cp_message($vbphrase['vbgamez_admincp_no_selected']);  
	} 
	else 
	{ 
                $userid = admincp_vbg_get_userid($_POST['id']);
		$serverinfo = vB_vBGamez::vbgamez_verify_id($_POST['id']);
		
		$db->query_write("DELETE FROM " . TABLE_PREFIX . "vbgamez WHERE id = '" . $_POST['id'] . "'");
		$db->query_write("DELETE FROM " . TABLE_PREFIX . "vbgamez_comments WHERE serverid = '" . $_POST['id'] . "'");
		$db->query_write("DELETE FROM " . TABLE_PREFIX . "vbgamez_statistics WHERE serverid = '" . $_POST['id'] . "'");

                admincp_vbg_update_userinfo($userid);

	 	if($_POST['jsredirect'])
		{
			print '<script type="text/javascript">if(window.opener) { window.opener.location.href = \'' . $vbulletin->options['vbgamez_path'] . '\'; window.close(); } </script>'; 
		}

                if($_POST['moderate'] == '1')
                {
		         print_cp_message($vbphrase['vbgamez_admincp_server_deleted'], 'vbgamez_admin.php?do=moderate', 1); 
                }else{
		         print_cp_message($vbphrase['vbgamez_admincp_server_deleted'], 'vbgamez_admin.php?do='. getRedirectUrl($serverinfo['type'], $serverinfo['cache_game']), 1); 
                }

	}
}

// ################## Включение сервера  ##################
if ($_REQUEST['do'] == 'enable')
{
        $_REQUEST['id'] = intval($_REQUEST['id']);

	if($_REQUEST['id'] == '')
	{
		 print_cp_message($vbphrase['vbgamez_admincp_no_selected']);  
	}
	$serverinfo = vB_vBGamez::vbgamez_verify_id($_REQUEST['id']);

        $db->query("UPDATE " . TABLE_PREFIX . "vbgamez SET disabled = 0 WHERE  id = '" . $_REQUEST['id'] . "'");

        $userid = admincp_vbg_get_userid($_REQUEST['id']);

        admincp_vbg_update_userinfo($userid);

	 	if($_GET['jsredirect'])
		{
			print '<script type="text/javascript">if(window.opener) { window.opener.location.href = \'' . $vbulletin->options['vbgamez_path'] . '\'; window.close(); } </script>'; 
		}

        print_cp_message($vbphrase['vbgamez_server_enabled'], 'vbgamez_admin.php?do='. getRedirectUrl($serverinfo['type'], $serverinfo['cache_game']), 1);

}

// ################## Выключение сервера  ##################
if ($_REQUEST['do'] == 'disable')
{
        $_REQUEST['id'] = intval($_REQUEST['id']);

	if($_REQUEST['id'] == '')
	{
		 print_cp_message($vbphrase['vbgamez_admincp_no_selected']);  
	}
	$serverinfo = vB_vBGamez::vbgamez_verify_id($_REQUEST['id']);

        $db->query("UPDATE " . TABLE_PREFIX . "vbgamez SET disabled = 1 WHERE  id = '" . $_REQUEST['id'] . "'");

        $userid = admincp_vbg_get_userid($_REQUEST['id']);

        admincp_vbg_update_userinfo($userid);

	 	if($_GET['jsredirect'])
		{
			print '<script type="text/javascript">if(window.opener) { window.opener.location.href = \'' . $vbulletin->options['vbgamez_path'] . '\'; window.close(); } </script>'; 
		}

        print_cp_message($vbphrase['vbgamez_server_disabled'], 'vbgamez_admin.php?do='. getRedirectUrl($serverinfo['type'], $serverinfo['cache_game']), 1);

}

// ################## Сортировка серверов  ##################
if ($_REQUEST['do'] == 'order')
{
	foreach($_POST['displayorder'] AS $id => $order)
	{
            $order = intval($order);
            $id = intval($id);
            $db->query("UPDATE " . TABLE_PREFIX . "vbgamez SET zone = $order WHERE id = $id");
	}

        print_cp_message($vbphrase['vbgamez_servers_ordered'], 'vbgamez_admin.php?do='. getRedirectUrl($_POST['type'], $_POST['addtype']), 1);

}

// ################## Одобрение сервера  ##################
if ($_REQUEST['do'] == 'approve')
{
   $_REQUEST['id'] = intval($_REQUEST['id']);

   $db->query("UPDATE " . TABLE_PREFIX . "vbgamez SET valid = 0 WHERE id = '" . $_REQUEST['id'] . "'");

   vB_vBGamez::vBG_Datastore_Clear_Cache($_REQUEST['id'], 'valid');

   print_cp_message($vbphrase['vbgamez_admincp_server_complete_add'], 'vbgamez_admin.php?do=moderate', 1);

}

// ################## Отклонение сервера  ##################
if ($_REQUEST['do'] == 'refuse')
{
   $_REQUEST['id'] = intval($_REQUEST['id']);

   $db->query("UPDATE " . TABLE_PREFIX . "vbgamez SET valid = 2 WHERE id = '" . $_REQUEST['id'] . "'");

   vB_vBGamez::vBG_Datastore_Clear_Cache($_REQUEST['id'], 'valid');

   print_cp_message($vbphrase['vbgamez_admincp_server_deleted'], 'vbgamez_admin.php?do=moderate', 1);

}

// ################## Редактирование сервера  ##################
if ($_REQUEST['do'] == 'modify')
{

 $_REQUEST['id'] = intval($_REQUEST['id']);

 if(!$_REQUEST['id'])
  {
            print_cp_message($vbphrase['vbgamez_admincp_no_selected']);  
  }

  print_cp_header($vbphrase['vbgamez_admincp_vbgameztitle']);

  print_form_header('vbgamez_admin', 'domodify');

  print_table_header($vbphrase['vbgamez_admincp_edit_server']);

	$result = $db->query_read("SELECT vbgamez.*, user.username FROM " . TABLE_PREFIX . "vbgamez AS vbgamez
                                   LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = vbgamez.userid) 
                                   WHERE id = '" . $_REQUEST['id'] . "'");

	if ($db->num_rows($result) > 0) {

		while ($server = $db->fetch_array($result)) {

                $lookup = $server;

  if(!vBGamez_dbGames_Bootstrap::vbgamez_is_db_game($server['type']))
  {
		print_select_row($vbphrase['vbgamez_admincp_game_type'], 'game', vbgamez_type_list(), $server['type']);

		print_input_row($vbphrase['vbgamez_admincp_insert_ip'], 'ip',$server['ip']);

		print_input_row($vbphrase['vbgamez_admincp_insert_port'], 'c_port',$server['c_port'], true, 35, 5);

		print_input_row($vbphrase['vbgamez_admincp_insert_qport'], 'q_port',$server['q_port'], true, 35, 5);

		print_input_row($vbphrase['vbgamez_admincp_insert_sport'], 's_port',$server['s_port'], true, 35, 5);

  }else{
                define('VBG_HIDE_NOTDB_GAMES', true);

		print_select_row($vbphrase['vbgamez_admincp_game_type'], 'game', vbgamez_type_list(), $server['type']);

		print_input_row($vbphrase['vbgamez_ip_address_db'], 'db_address', '*****');

		print_input_row($vbphrase['vbgamez_ip_user_db'], 'db_user', '*****');

		print_input_row($vbphrase['vbgamez_ip_password_db'], 'db_password', '*****');

       		print_input_row($vbphrase['vbgamez_db_name'], 'db_name', '*****');


        	print_input_row($vbphrase['vbgamez_server_name'], 'server_name', vB_vBGamez::vbgamez_string_html($lookup['cache_name']));

        	print_input_row($vbphrase['vbgamez_db_server_ip'], 'server_ip', $lookup['ip'].":".$lookup['c_port']);


  }
		print_input_row($vbphrase['vbgamez_admincp_in_table'], 'zone', $server['zone']);

                print_label_row(construct_phrase($vbphrase['vbgamez_admincp_server_active'], '...'),'

		<label for="rb_sw_0"><input type="radio" name="disabled" id="rb_sw_0" value="0" tabindex="0" ' . iif(!$server['disabled'], 'checked="checked"') . '/>' . $vbphrase['yes'] . '</label>
		<label for="rb_sw_1"><input type="radio" name="disabled" id="rb_sw_1" value="1" tabindex="1" ' . iif($server['disabled'], 'checked="checked"') . '/>' . $vbphrase['no'] . '</label>', '', 'top', 'disabled');

  		print_input_row($vbphrase['vbgamez_admincp_username'], 'username', $server['username']);

                print $field_dm->getDisplayView($server);

 		print_checkbox_row('Steam', 'steam', $server['steam']);

 		print_checkbox_row('Non-Steam', 'nonsteam', $server['nonsteam']);


 		print_checkbox_row($vbphrase['vbgamez_server_pirated'], 'pirated', $server['pirated']);


  		if($vbulletin->options['vbgamez_comments_userdisable'])
  		{
 		      print_checkbox_row($vbphrase['vbgamez_enable_comments'], 'commentsenable', $server['commentsenable']);
                }

 		print_input_row($vbphrase['vbgamez_admincp_rating'], 'rating', $server['rating']);
 		print_checkbox_row($vbphrase['vbgamez_set_featured'], 'stick', $server['stick']);
 		print_checkbox_row($vbphrase['vbgamez_enable_timer'], 'timer', iif($server['expirydate'] > TIMENOW, 1, 0));

		print_time_row($vbphrase['vbgamez_payed_to'], 'expirydate', iif($server['expirydate'], $server['expirydate'], TIMENOW+60*60*24*7));
		
		print_select_row($vbphrase['vbgamez_status'], 'valid', vB_vBGamez::getServerStatuses(), $server['valid']);
		construct_hidden_code('id', $server['id']);
		}
	}

	$db->free_result($result);
	construct_hidden_code('jsredirect', $_REQUEST['jsredirect']);
        print_submit_row($vbphrase['vbgamez_admincp_send']);
        
}

// ################## Do Редактирование сервера  ##################
if ($_REQUEST['do'] == 'domodify')
{

  $_POST['id'] = intval($_POST['id']);

  if($_POST['id'] == '')
  {
		 print_cp_message($vbphrase['vbgamez_admincp_no_info_on_fields']);  
  } 

  // process verify fields
  $field_dm->verifyPostFields();

  if($field_dm->errors)
  {
              print_cp_message($field_dm->errors);
  }

if(!vBGamez_dbGames_Bootstrap::vbgamez_is_db_game($_POST['game']))
 {

  if($_POST['ip'] == '' || intval($_POST['c_port']) == '' || $_POST['game'] == '')
  {
		 print_cp_message($vbphrase['vbgamez_admincp_no_info_on_fields']);  
  } 

  if(preg_match("/[^0-9a-z\.\-\[\]\:]/i", $_POST['ip']))
  {
       print_cp_message($vbphrase['vbgamez_invalid_ip']);  
  }

 }else{

                  if($_POST['db_address'] != '*****' OR $_POST['db_user'] != '*****' OR $_POST['db_password'] != '*****')
                  {

                  if (empty($_POST['db_address']))
                  {
                     print_cp_message($vbphrase['vbgamez_error_enter_address_db']);
                  }

                  if(empty($_POST['db_user']))
                  {
                       print_cp_message($vbphrase['vbgamez_error_enter_user_db']);
                  }

                  if(empty($_POST['db_password']))
                  {
                       print_cp_message($vbphrase['vbgamez_error_enter_db_password']);
                  }

                  if(!vBGamez_dbGames_Bootstrap::vbgamez_verify_dbsettings($_POST['db_address'], $_POST['db_user'], $_POST['db_password']))
                  {
                       print_cp_message($vbphrase['vbgamez_error_invalid_db_data']);
                  }

                  if(vBGamez_dbGames_Bootstrap::fieldIsRequired($_POST['game'], 'showdbname') AND empty($_POST['db_name']))
                  {
                       print_cp_message($vbphrase['vbgamez_error_invalid_dbname']);
                  }

                  if(vBGamez_dbGames_Bootstrap::fieldIsRequired($_POST['game'], 'showservername') AND empty($_POST['server_name']))
                  {
                       print_cp_message($vbphrase['vbgamez_error_invalid_servername']);
                  }

                  if(vBGamez_dbGames_Bootstrap::fieldIsRequired($_POST['game'], 'showserverip') AND empty($_POST['server_ip']))
                  {
                       print_cp_message($vbphrase['vbgamez_error_invalid_serverip']);
                  }

                    $dbinfo = array();

                    $dbinfo['address'] = $_POST['db_address'];
                    $dbinfo['user'] = $_POST['db_user'];
                    $dbinfo['password'] = $_POST['db_password'];

                    $dbinfo['db_name'] = $_POST['db_name'];
                    $lookup['dbinfo']['server_name'] = $_POST['server_name'];
                    $dbinfo['server_ip'] = $_POST['server_ip'];


                    $dbconnection = vBGamez_dbGames_Bootstrap::vbgamez_encode_db_info($dbinfo);


                    $dbgame = vBGamez_dbGames_Bootstrap::fetchClassLibary($_POST['game'], $dbconnection);

                    $serverdata = $dbgame->fetch_info();

                    if(!($serverinfo = $serverdata))
                    {
                           print_cp_message($vbphrase['vbgamez_error_invalid_db_data']);
                    }else{

                     $server_query = vbgamez_query_live($_POST['game'], $serverinfo['s']['address'], $serverinfo['s']['port'], $serverinfo['s']['port'], $serverinfo['s']['port'], "s");

                        $select = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez WHERE ip = " . $vbulletin->db->sql_prepare($serverinfo['s']['address']) . " AND c_port = " . $vbulletin->db->sql_prepare($serverinfo['s']['port']) . " AND id != '" . $_POST['id'] . "' ");

                        if($vbulletin->db->num_rows($select) > 0)
                        {
                           print_cp_message($vbphrase['vbgamez_error_server_already_added']);
                        }

                     if (!$server_query['b']['status'])
                     {
                       print_cp_message($vbphrase['vbgamez_error_server_is_offline']);
                     }
                  }

                 $_POST['ip'] = $serverinfo['s']['address'];
                 
                 $_POST['c_port'] = $serverinfo['s']['port'];

                 }else{

                    $lookup = vB_vBGamez::vbgamez_verify_id($_POST['id']);

                    $lookup['dbinfo'] = vBGamez_dbGames_Bootstrap::vbgamez_fetch_db_info($lookup['dbinfo']);

                    $lookup['dbinfo']['server_name'] = $_POST['server_name'];
                    $lookup['dbinfo']['server_ip'] = $_POST['server_ip'];

                    $lookup['dbinfo'] = vBGamez_dbGames_Bootstrap::vbgamez_encode_db_info($lookup['dbinfo']);

                    $dbconnection = $lookup['dbinfo'];

                    $dbgame = vBGamez_dbGames_Bootstrap::fetchClassLibary($_POST['game'], $dbconnection);

                    $serverdata = $dbgame->fetch_info();

                    if(!($serverinfo = $serverdata))
                    {
                           print_cp_message($vbphrase['vbgamez_error_invalid_db_data']);
                    }else{

                     $server_query = vbgamez_query_live($_POST['game'], $serverinfo['s']['address'], $serverinfo['s']['port'], $serverinfo['s']['port'], $serverinfo['s']['port'], "s");

                        $select = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez WHERE ip = " . $vbulletin->db->sql_prepare($serverinfo['s']['address']) . " AND c_port = " . $vbulletin->db->sql_prepare($serverinfo['s']['port']) . " AND id != '" . $_POST['id'] . "' ");

                        if($vbulletin->db->num_rows($select) > 0)
                        {
                           print_cp_message($vbphrase['vbgamez_error_server_already_added']);
                        }

                     if (!$server_query['b']['status'])
                     {
                       print_cp_message($vbphrase['vbgamez_error_server_is_offline']);
                     }
                  }

                 $_POST['ip'] = $serverinfo['s']['address'];
                 
                 $_POST['c_port'] = $serverinfo['s']['port'];

                 }  
  }

  if(empty($_POST['q_port']))
  {
	   $port_settings = vbgamez_port_conversion($_POST['game'], $_POST['c_port'], 0, 0);
	   $_POST['q_port'] = $port_settings[1];
  }

  $oldserverinfo = vB_vBGamez::vbgamez_verify_id($_POST['id']);
  
  if(!empty($_POST['username']))
  {
            $select_user = $db->query("SELECT userid AS userid FROM " . TABLE_PREFIX . "user WHERE username = '" . $db->escape_string($_POST['username']) . "'");
            $select_user = $db->fetch_array($select_user);

            if(empty($select_user['userid']))
            {
                   print_cp_message($vbphrase['vbgamez_admincp_user_not_found']); 
            }else{
                   $userid = $select_user['userid']; 
            }
  }
  vB_vBGamez::loadClassFromFile('geo');
  if(vB_vBGamez_Geo_db::check_settings() AND $vbulletin->options['vbgamez_server_location_enable'])
  {
                  $servergeo = vB_vBGamez_Geo_db::fetchInfo(@gethostbyname($_POST['ip']));
		  $server_location = $servergeo->country_code;
		  $server_city = $servergeo->city;
		  $server_country = $servergeo->country_name;
  } 

  if($_POST['timer'] AND $_POST['stick'])
  {

	$expirydate = mktime($_POST['expirydate']['hour'], $_POST['expirydate']['minute'], $_POST['expirydate']['second'], $_POST['expirydate']['month'], $_POST['expirydate']['day'], $_POST['expirydate']['year']);
  }
  $db->query("UPDATE " . TABLE_PREFIX . "vbgamez SET 
              type = ".$db->sql_prepare($_POST['game']).",
              ip = ".$db->sql_prepare($_POST['ip']).",
              c_port = ".$db->sql_prepare($_POST['c_port']).",
              q_port = ".$db->sql_prepare($_POST['q_port']).",
              s_port = ".$db->sql_prepare($_POST['s_port']).",
              zone = ".$db->sql_prepare($_POST['zone']).",
              disabled = ".$db->sql_prepare($_POST['disabled']).",
              steam = ".$db->sql_prepare($_POST['steam']).",
              pirated = ".$db->sql_prepare($_POST['pirated']).",
              nonsteam = ".$db->sql_prepare($_POST['nonsteam']).",
              commentsenable = ".$db->sql_prepare($_POST['commentsenable']).",
              rating = ".$db->sql_prepare($_POST['rating']).",
              userid = ".$db->sql_prepare($userid).",
              dbinfo = " . $vbulletin->db->sql_prepare(iif($dbconnection, $dbconnection, '')) . ",
 	      location = " . $vbulletin->db->sql_prepare($server_location) . ",
 	      city = " . $vbulletin->db->sql_prepare($server_city) . ",
 	      country = " . $vbulletin->db->sql_prepare($server_country) . ",
          stick = " . intval($_POST['stick']) . ",
          expirydate = '" . $db->escape_string($expirydate) . "',
		  valid = '" . intval($_POST['valid']) . "'
              WHERE id = '" . $_POST['id'] . "'");

              admincp_vbg_update_userinfo($userid);
              admincp_vbg_update_userinfo($oldserverinfo['userid']);
              $field_dm->save_info($_POST['id']);

              vB_vBGamez::vBG_Datastore_Clear_Cache($_POST['id'], 'all');

              $lookup = vB_vBGamez::vbgamez_verify_id($_POST['id']);

              vB_vBGamez::vBG_Datastore_Cache($_POST['ip'], $_POST['q_port'], $_POST['c_port'], $_POST['s_port'], $_POST['game'], 's', $lookup);

	 	if($_POST['jsredirect'])
		{
			print '<script type="text/javascript">if(window.opener) { window.opener.location.href = \'' . $vbulletin->options['vbgamez_path'] . '?do=view&id= ' . $_POST['id'] . '\'; window.close(); } </script>'; 
		}
        $lookup = vB_vBGamez::vbgamez_verify_id($_POST['id']);
		if($_POST['valid'] == 0)
		{
			$gotourl = 'vbgamez_admin.php?do='.getRedirectUrl($lookup['type'], $lookup['cache_game']);
		}else
		{
			$gotourl = 'vbgamez_admin.php';
		}
	      print_cp_message($vbphrase['vbgamez_admincp_server_updated'], $gotourl, 1); 

}

// ################## Добавление сервера  ##################
if ($_REQUEST['do'] == 'add')
{

  if(empty($_REQUEST['type']))
  {

	print_cp_header($vbphrase['vbgamez_admincp_vbgameztitle']);

	print_form_header('vbgamez_admin', 'add', false, true, 'cpform', '90%', '', true, 'get');

	print_table_header($vbphrase['vbgamez_admincp_add_server']);

	print_description_row($vbphrase['vbgamez_admincp_addserver_select_type']);

        print_submit_row($vbphrase['vbgamez_admincp_send']);

	print_table_footer();

  }elseif($_REQUEST['type'] == '1')
  {
          print_cp_header($vbphrase['vbgamez_admincp_vbgameztitle']);

          print_form_header('vbgamez_admin', 'doadd');

          print_table_header($vbphrase['vbgamez_admincp_add_server']);

          print_select_row($vbphrase['vbgamez_admincp_game_type'], 'game', vbgamez_type_list());

          print_input_row($vbphrase['vbgamez_admincp_insert_ip'], 'ip');

          print_input_row($vbphrase['vbgamez_admincp_insert_port'], 'c_port', '', true, 35, 5);

          print_input_row($vbphrase['vbgamez_admincp_insert_qport'], 'q_port', '', true, 35, 5);

	  print_input_row($vbphrase['vbgamez_admincp_insert_sport'], 's_port', '', true, 35, 5);

          print_input_row($vbphrase['vbgamez_admincp_in_table'], 'zone');

          print_label_row(construct_phrase($vbphrase['vbgamez_admincp_server_active'], '...'),'

		<label for="rb_sw_0"><input type="radio" name="disabled" id="rb_sw_0" value="0" tabindex="0" checked/>' . $vbphrase['yes'] . '</label>
		<label for="rb_sw_1"><input type="radio" name="disabled" id="rb_sw_1" value="1" tabindex="1" />' . $vbphrase['no'] . '</label>', '', 'top', 'disabled');

          print_input_row($vbphrase['vbgamez_admincp_username'], 'username');

          print $field_dm->getDisplayView();

          print_checkbox_row('Steam', 'steam', false);

          print_checkbox_row('Non-Steam', 'nonsteam', false);

 	  print_checkbox_row($vbphrase['vbgamez_server_pirated'], 'pirated', $server['pirated']);


          if($vbulletin->options['vbgamez_comments_userdisable'])
          {
                print_checkbox_row($vbphrase['vbgamez_enable_comments'], 'commentsenable');
          }
		print_checkbox_row($vbphrase['vbgamez_set_featured'], 'stick', $server['stick']);
		print_checkbox_row($vbphrase['vbgamez_enable_timer'], 'timer', iif($server['expirydate'] > TIMENOW, 1, 0));

		print_time_row($vbphrase['vbgamez_payed_to'], 'expirydate', iif($server['expirydate'], $server['expirydate'], TIMENOW+60*60*24*7));
				print_select_row($vbphrase['vbgamez_status'], 'valid', vB_vBGamez::getServerStatuses(), 0);
	  construct_hidden_code('type', 1);

          print_submit_row($vbphrase['vbgamez_admincp_send']);


  }elseif($_REQUEST['type'] == '2')
  {
          define('VBG_HIDE_NOTDB_GAMES', true);

          print_cp_header($vbphrase['vbgamez_admincp_vbgameztitle']);

          print_form_header('vbgamez_admin', 'doadd');

          print_table_header($vbphrase['vbgamez_admincp_add_server']);

          print_select_row($vbphrase['vbgamez_admincp_game_type'], 'game', vbgamez_type_list());

          print_input_row($vbphrase['vbgamez_ip_address_db'], 'db_address');

          print_input_row($vbphrase['vbgamez_ip_user_db'], 'db_user');

          print_input_row($vbphrase['vbgamez_ip_password_db'], 'db_password');

          print_input_row($vbphrase['vbgamez_db_name'], 'db_name');

          print_input_row($vbphrase['vbgamez_server_name'], 'server_name');

          print_input_row($vbphrase['vbgamez_db_server_ip'], 'server_ip');


          print_input_row($vbphrase['vbgamez_admincp_in_table'], 'zone');

          print_label_row(construct_phrase($vbphrase['vbgamez_admincp_server_active'], '...'),'

		<label for="rb_sw_0"><input type="radio" name="disabled" id="rb_sw_0" value="0" tabindex="0" checked/>' . $vbphrase['yes'] . '</label>
		<label for="rb_sw_1"><input type="radio" name="disabled" id="rb_sw_1" value="1" tabindex="1" />' . $vbphrase['no'] . '</label>', '', 'top', 'disabled');

          print_input_row($vbphrase['vbgamez_admincp_username'], 'username');

          print $field_dm->getDisplayView();

 	  print_checkbox_row($vbphrase['vbgamez_server_pirated'], 'pirated', $server['pirated']);

          if($vbulletin->options['vbgamez_comments_userdisable'])
          {
                print_checkbox_row($vbphrase['vbgamez_enable_comments'], 'commentsenable');
          }

		print_checkbox_row($vbphrase['vbgamez_set_featured'], 'stick', $server['stick']);
		print_checkbox_row($vbphrase['vbgamez_enable_timer'], 'timer', iif($server['expirydate'] > TIMENOW, 1, 0));

		print_time_row($vbphrase['vbgamez_payed_to'], 'expirydate', iif($server['expirydate'], $server['expirydate'], TIMENOW+60*60*24*7));
		
		print_select_row($vbphrase['vbgamez_status'], 'valid', vB_vBGamez::getServerStatuses(), 0);
	  construct_hidden_code('type', 2);

          print_submit_row($vbphrase['vbgamez_admincp_send']);
  }

}

// ################## Do добавление сервера  ##################
if ($_POST['do'] == 'doadd')
{

if(!vBGamez_dbGames_Bootstrap::vbgamez_is_db_game($_POST['game']))
 {

  if($_POST['ip'] == '' || intval($_POST['c_port']) == '' || $_POST['game'] == '')
  {
		 print_cp_message($vbphrase['vbgamez_admincp_no_info_on_fields']);  
  } 

  if(preg_match("/[^0-9a-z\.\-\[\]\:]/i", $_POST['ip']))
  {
       print_cp_message($vbphrase['vbgamez_invalid_ip']);  
  }

 }else{

                  if (empty($_POST['db_address']))
                  {
                     print_cp_message($vbphrase['vbgamez_error_enter_address_db']);
                  }

                  if(empty($_POST['db_user']))
                  {
                       print_cp_message($vbphrase['vbgamez_error_enter_user_db']);
                  }

                  if(empty($_POST['db_password']))
                  {
                       print_cp_message($vbphrase['vbgamez_error_enter_db_password']);
                  }

                  if(!vBGamez_dbGames_Bootstrap::vbgamez_verify_dbsettings($_POST['db_address'], $_POST['db_user'], $_POST['db_password']))
                  {
                       print_cp_message($vbphrase['vbgamez_error_invalid_db_data']);
                  }

                  if(vBGamez_dbGames_Bootstrap::fieldIsRequired($_POST['game'], 'showdbname') AND empty($_POST['db_name']))
                  {
                       print_cp_message($vbphrase['vbgamez_error_invalid_dbname']);
                  }

                  if(vBGamez_dbGames_Bootstrap::fieldIsRequired($_POST['game'], 'showservername') AND empty($_POST['server_name']))
                  {
                       print_cp_message($vbphrase['vbgamez_error_invalid_servername']);
                  }

                  if(vBGamez_dbGames_Bootstrap::fieldIsRequired($_POST['game'], 'showserverip') AND empty($_POST['server_ip']))
                  {
                       print_cp_message($vbphrase['vbgamez_error_invalid_serverip']);
                  }

                    $dbinfo = array();

                    $dbinfo['address'] = $_POST['db_address'];
                    $dbinfo['user'] = $_POST['db_user'];
                    $dbinfo['password'] = $_POST['db_password'];

                    $dbinfo['db_name'] = $_POST['db_name'];
                    $dbinfo['server_name'] = $_POST['server_name'];
                    $dbinfo['server_ip'] = $_POST['server_ip'];

                    $dbconnection = vBGamez_dbGames_Bootstrap::vbgamez_encode_db_info($dbinfo);

                    $dbgame = vBGamez_dbGames_Bootstrap::fetchClassLibary($_POST['game'], $dbconnection);

                    $serverdata = $dbgame->fetch_info();

                    if(!($serverinfo = $serverdata))
                    {
                           print_cp_message($vbphrase['vbgamez_error_invalid_db_data']);
                    }else{

                     $server_query = vbgamez_query_live($_POST['game'], $serverinfo['s']['address'], $serverinfo['s']['port'], $serverinfo['s']['port'], $serverinfo['s']['port'], "s");

                        $select = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez WHERE ip = " . $vbulletin->db->sql_prepare($serverinfo['s']['address']) . " AND c_port = " . $vbulletin->db->sql_prepare($serverinfo['s']['port']) . " AND id != '" . $_POST['id'] . "' ");

                        if($vbulletin->db->num_rows($select) > 0)
                        {
                           print_cp_message($vbphrase['vbgamez_error_server_already_added']);
                        }

                     if (!$server_query['b']['status'])
                     {
                       print_cp_message($vbphrase['vbgamez_error_server_is_offline']);
                     }
                  }

                 $_POST['ip'] = $serverinfo['s']['address'];
                 
                 $_POST['c_port'] = $serverinfo['s']['port'];
  }

  if(empty($_POST['q_port']))
  {
	   $port_settings = vbgamez_port_conversion($_POST['game'], $_POST['c_port'], 0, 0);
	   $_POST['q_port'] = $port_settings[1];
  }

  if(!empty($_POST['username']))
  {
            $select_user = $db->query("SELECT userid AS userid FROM " . TABLE_PREFIX . "user WHERE username = '" . $db->escape_string($_POST['username']) . "'");
            $select_user = $db->fetch_array($select_user);

            if(empty($select_user['userid']))
            {
                   print_cp_message($vbphrase['vbgamez_admincp_user_not_found']); 
            }else{
                   $userid = $select_user['userid']; 
            }
  }

  // process verify fields
  $field_dm->verifyPostFields();

  if($field_dm->errors)
  {
              print_cp_message($field_dm->errors);
  }

  vB_vBGamez::loadClassFromFile('geo');
  if(vB_vBGamez_Geo_db::check_settings() AND $vbulletin->options['vbgamez_server_location_enable'])
  {
                  $servergeo = vB_vBGamez_Geo_db::fetchInfo(@gethostbyname($_POST['ip']));
		  $server_location = $servergeo->country_code;
		  $server_city = $servergeo->city;
		  $server_country = $servergeo->country_name;
  } 

  if($_POST['timer'] AND $_POST['stick'])
  {
	$expirydate = mktime($_POST['expirydate']['hour'], $_POST['expirydate']['minute'], $_POST['expirydate']['second'], $_POST['expirydate']['month'], $_POST['expirydate']['day'], $_POST['expirydate']['year']);
  }

  $db->query("INSERT INTO " . TABLE_PREFIX . "vbgamez 
             (type, ip, c_port, q_port, s_port, zone, disabled, steam, nonsteam, commentsenable, userid, dbinfo, pirated, location, city, country, stick, expirydate, valid) VALUES 
             (".$db->sql_prepare($_POST['game']).",
              ".$db->sql_prepare($_POST['ip']).",
              ".$db->sql_prepare($_POST['c_port']).",
              ".$db->sql_prepare($_POST['q_port']).",
              ".$db->sql_prepare($_POST['s_port']).",
              ".$db->sql_prepare($_POST['zone']).",
              ".$db->sql_prepare($_POST['disabled']).",
              ".$db->sql_prepare($_POST['steam']).",
              ".$db->sql_prepare($_POST['nonsteam']).",
              ".$db->sql_prepare($_POST['commentsenable']).",
              ".$db->sql_prepare($userid).",
              " . $vbulletin->db->sql_prepare(iif($dbconnection, $dbconnection, '')) . ",
              " . $vbulletin->db->sql_prepare($_POST['pirated']) . ",
              " . $vbulletin->db->sql_prepare($server_location) . ",
              " . $vbulletin->db->sql_prepare($server_city) . ",
              " . $vbulletin->db->sql_prepare($server_country) . ",
			  " . intval($_POST['stick']) . ",
			  '" . $db->escape_string($expirydate) . "',
			  '" . intval($_POST['valid']) . "')");

              $serverid = $db->insert_id();
              $field_dm->save_info($serverid);

              if(!empty($_POST['username']))
              {
                              $userid = admincp_vbg_get_userid($serverid);

                              admincp_vbg_update_userinfo($userid);
              }

              $lookup = vB_vBGamez::vbgamez_verify_id($serverid);

              vB_vBGamez::vBG_Datastore_Cache($_POST['ip'], $_POST['q_port'], $_POST['c_port'], $_POST['s_port'], $_POST['game'], 's', $lookup);
              $lookup = vB_vBGamez::vbgamez_verify_id($serverid);

			if($_POST['valid'] == 0)
			{
				$gotourl = 'vbgamez_admin.php?do='.getRedirectUrl($lookup['type'], $lookup['cache_game']);
			}else
			{
				$gotourl = 'vbgamez_admin.php';
			}
	      print_cp_message($vbphrase['vbgamez_admincp_server_added'], $gotourl, 1); 

}




// ################## Диагностика ##################

if ($_REQUEST['do'] == 'diagnostic')
{
	print_cp_header($vbphrase['vbgamez_admincp_vbgameztitle']);

	print_form_header('', '', 1);

	print_table_header($vbphrase['vbgamez_admincp_diagnostic_functions']);

	print_description_row($vbphrase['vbgamez_admincp_diagnostic_functions_desc']);

	print_cells_row(array('<a href="http://php.net/fsockopen">FSOCKOPEN</a>: '.(function_exists("fsockopen") ?  $dig_gS.$vbphrase['yes'].$dig_gE : $dig_rS.$vbphrase['no'].$dig_rE)), false, '', -2);

	print_cells_row(array('<a href="http://php.net/curl">CURL</a>: '.((function_exists("curl_init") && function_exists("curl_setopt") && function_exists("curl_exec")) ?  $dig_gS.$vbphrase['yes'].$dig_gE : $dig_rS.$vbphrase['no'].$dig_rE)), false, '', -2);

	print_cells_row(array('<a href="http://php.net/mbstring">MBSTRING</a>: '.(function_exists("mb_convert_encoding") ?  $dig_gS.$vbphrase['yes'].$dig_gE : $dig_rS.$vbphrase['no'].$dig_rE)), false, '', -2);

	print_cells_row(array('<a href="http://php.net/bzip2">BZIP2</a>: '.(function_exists("bzdecompress") ?  $dig_gS.$vbphrase['yes'].$dig_gE : $dig_rS.$vbphrase['no'].$dig_rE)), false, '', -2);

	print_cells_row(array('<a href="http://php.net/ICONV">ICONV</a>: '.(function_exists("iconv") ?  $dig_gS.$vbphrase['yes'].$dig_gE : $dig_rS.$vbphrase['no'].$dig_rE)), false, '', -2);

	print_table_footer();

	print_form_header('vbgamez_admin', 'docomments');

	print_table_header($vbphrase['vbgamez_paid_check']);
	require_once('./packages/vbgamez/paid.php');
	$check_result = vBGamez_Paid::paidIsEnabled($vbulletin);
	if($check_result == 1)
	{
		$result_phrase = $dig_gS.$vbphrase['vbgamez_paid_check_all_ok'].$dig_gE;
	}else{
		$result_phrase = $dig_rS.$vbphrase[$GLOBALS['vbgamez_errorid']].$dig_rE;
	}
	print_cells_row(array($vbphrase['vbgamez_check_result'].': '.$result_phrase), false, '', -2);

	print_table_footer();
	
	
	print_form_header('vbgamez_admin', 'docomments');

	print_table_header($vbphrase['vbgamez_admincp_comments']);

	print_description_row($vbphrase['vbgamez_admincp_comment_counter']);

	print_submit_row($vbphrase['submit']);

	print_form_header('vbgamez_admin', 'dousers');

	print_table_header($vbphrase['vbgamez_admincp_users']);

	print_description_row($vbphrase['vbgamez_admincp_userservers_counter']);

	print_input_row($vbphrase['vbgamez_admincp_number_of_users'], 'perpage', 1000);

	print_submit_row($vbphrase['submit']);
}

// ################## Диагностика ##################

if ($_REQUEST['do'] == 'docomments')
{
    print_cp_header($vbphrase['vbgamez_admincp_vbgameztitle']);

    $result = $db->query("SELECT * FROM " . TABLE_PREFIX . "vbgamez");

    while ($server = $db->fetch_array($result))
    {

     $get_counter = $db->query("SELECT count(*) FROM " . TABLE_PREFIX . "vbgamez_comments WHERE serverid = '" . $server['id'] . "' and onmoderate = 0 and deleted = 0");
     $counter = $db->fetch_row($get_counter);
     $count = $counter[0];
     print "SET comments = $count for server " . vB_vBGamez::vbgamez_string_html($server['cache_name']) . " <br />";

     $db->query("UPDATE " . TABLE_PREFIX . "vbgamez SET comments = '" . $count . "' WHERE id = '" . $server['id'] . "'");
    }

    print_cp_message($vbphrase['vbgamez_admincp_comment_finished'], 'vbgamez_admin.php?do=diagnostic', 1);
}


// ################## Очистка кэша ##################

if ($_REQUEST['do'] == 'clearcache')
{   
    vB_vBGamez::vBG_Datastore_Clear_Cache();

    print_cp_message($vbphrase['vbgamez_admincp_cache_cleared']);
}


// ################## Очистка графиков ##################

if ($_REQUEST['do'] == 'clearcachestats')
{   
    $db->query("DELETE FROM " . TABLE_PREFIX . "vbgamez_statistics");
    $db->query("UPDATE " . TABLE_PREFIX . "vbgamez SET statistics = ''");

    print_cp_message($vbphrase['done']);
}

// ################## Загрузка GEO базы ##################

if ($_REQUEST['do'] == 'geobase')
{   
    require_once('./packages/vbgamez/geo.php');

    print_cp_header($vbphrase['vbgamez_admincp_vbgameztitle']);

    print_form_header('', '', 1);

    if(@file_exists('./packages/vbgamez/3rd_party_classes/geodb/GeoLiteCity.dat') AND empty($_REQUEST['skip']))
    {
            print_cp_message($vbphrase['vbgamez_geobase_exists']);
    }

    print '<p align="center">' . $vbphrase['vbgamez_base_downloading'] . '<br><br>[<span class="progress_dots" id="dspan">:</span>]</p>';

    vbflush(); 

    vB_vBGamez_Geo_db::downloadDatabase($_REQUEST['skip']);

    print_cp_message($vbphrase['vbgamez_downloadbase_complete'], 'options.php?do=options&dogroup=vbgamez', 1);
}

// ################## Диагностика ##################

if ($_REQUEST['do'] == 'dousers')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'startat'		=> TYPE_UINT,
		'perpage'	=> TYPE_UINT));

	print_cp_header($vbphrase['vbgamez_admincp_vbgameztitle']);
	print_form_header('', '', 1);

        if(empty($vbulletin->GPC['startat']))
        {
                $vbulletin->GPC['startat'] = 0;
        }

	if (empty($vbulletin->GPC['perpage']))
	{
		$vbulletin->GPC['perpage'] = 1;
	}

	$finishat = $vbulletin->GPC['startat'] + $vbulletin->GPC['perpage'];

	$users = $db->query_read("
		SELECT *
		FROM " . TABLE_PREFIX . "user
		WHERE userid >= " . $vbulletin->GPC['startat'] . "
		ORDER BY userid
		LIMIT " . $vbulletin->GPC['perpage']
	);

	$finishat = $vbulletin->GPC['startat'];

	while ($user = $db->fetch_array($users))
	{
     		$get_counter = $db->query("SELECT count(*) FROM " . TABLE_PREFIX . "vbgamez WHERE userid = '" . $user['userid'] . "'");

     		$counter = $db->fetch_row($get_counter);

     		$db->query("UPDATE " . TABLE_PREFIX . "user SET servers = '" . $counter[0] . "' WHERE userid = '" . $user['userid'] . "'");

		echo construct_phrase($vbphrase['processing_x'], $user['userid']) . "<br />\n";
		vbflush();

		$finishat = ($user['userid'] > $finishat ? $user['userid'] : $finishat);
	}

	$finishat++;

	if ($checkmore = $db->query_first("SELECT userid FROM " . TABLE_PREFIX . "user WHERE userid >= $finishat LIMIT 1"))
	{
		print_cp_redirect("vbgamez_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=dousers&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
		echo "<p><a href=\"vbgamez_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=dousers&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{
                      print_cp_message($vbphrase['vbgamez_admincp_users_finished'], 'vbgamez_admin.php?do=diagnostic', 1);
	}

}

/*======================================================================*\
|| #################################################################### ||
   USERBAR MANAGER
\*======================================================================*/

if ($_REQUEST['do'] == 'manageuserbar')
{
      $userbar_manager->userbars_list();
} 
if($_REQUEST['do'] == 'adduserbar')
{
       $userbar_manager->create_userbar();
}
if($_REQUEST['do'] == 'doadduserbar')
{       
       $_POST['name'] = trim($_POST['name']);

       if(empty($_POST['name']))
       {
            print_cp_message($vbphrase['vbgamez_userbarmgr_emptytitle']); exit;

       }
       $userbar_manager->do_add_userbar($_POST['name'], $_POST['description'], $_POST['order']);
       
       print_cp_message($vbphrase['vbgamez_userbarmgr_added'], 'vbgamez_admin.php?do=manageuserbar', 1);
}
if($_REQUEST['do'] == 'edituserbar')
{
       if(!$userbar_manager->verify_userbar($_REQUEST['id']))
       {
               print_cp_message('Invalid ID');
       }

       $userbar_manager->edit_userbar($_REQUEST['id']);
}
if($_REQUEST['do'] == 'doedituserbar')
{
       $_POST['name'] = trim($_POST['name']);

       if(empty($_POST['name']))
       {
            print_cp_message($vbphrase['vbgamez_userbarmgr_emptytitle']); exit;

       }

       if(!$userbar_manager->verify_userbar($_POST['id']))
       {
               print_cp_message('Invalid ID');
       }

       $userbar_manager->do_edit_userbar($_POST['id'], $_POST['name'], $_POST['description'], $_POST['order'], $_POST['enabled']);
       
       print_cp_message($vbphrase['vbgamez_userbarmgr_edited'], 'vbgamez_admin.php?do=manageuserbar', 1);
}
if($_REQUEST['do'] == 'deleteuserbar')
{
       if(!$userbar_manager->verify_userbar($_REQUEST['id']))
       {
               print_cp_message('Invalid ID');
       }

       $userbar_manager->delete_userbar($_REQUEST['id']);
       
       print_cp_message($vbphrase['vbgamez_userbarmgr_deleted'], 'vbgamez_admin.php?do=manageuserbar', 1);
}
if ($_REQUEST['do'] == 'userbarorder')
{
        $userbar_manager->save_order($_POST['displayorder']);
  
        print_cp_message($vbphrase['vbgamez_userbarmgr_sorted'], 'vbgamez_admin.php?do=manageuserbar', 1);
}
if($_REQUEST['do'] == 'enableuserbar')
{
       if(!$userbar_manager->verify_userbar($_REQUEST['id']))
       {
               print_cp_message('Invalid ID');
       }

       $userbar_manager->enable_userbar($_REQUEST['id']);
       
       print_cp_message($vbphrase['vbgamez_userbarmgr_enabled'], 'vbgamez_admin.php?do=manageuserbar', 1);
}
if($_REQUEST['do'] == 'disableuserbar')
{
       if(!$userbar_manager->verify_userbar($_REQUEST['id']))
       {
               print_cp_message('Invalid ID');
       }

       $userbar_manager->disable_userbar($_REQUEST['id']);
       
       print_cp_message($vbphrase['vbgamez_userbarmgr_disabled'], 'vbgamez_admin.php?do=manageuserbar', 1);
}
if($_REQUEST['do'] == 'configureuserbar')
{
       if(!$userbar_manager->verify_userbar($_REQUEST['id']))
       {
               print_cp_message('Invalid ID');
       }

       $userbar_manager->configure_userbar($_REQUEST['id']);
}
if($_REQUEST['do'] == 'doconfigureuserbar')
{
       $_POST['background'] = trim($_POST['background']);
       $_POST['textcolor'] = trim($_POST['textcolor']);
       $_POST['font'] = trim($_POST['font']);
       $_POST['fontsize'] = intval($_POST['fontsize']);

       if(empty($_POST['background']) OR empty($_POST['textcolor']) OR empty($_POST['font']) OR empty($_POST['fontsize']))
       {
            print_cp_message($vbphrase['vbgamez_userbarmgr_emptyfields']); exit;

       }

       if(!@file_exists($_POST['background']))
       {
            print_cp_message($vbphrase['vbgamez_userbarmgr_emptybackground']); exit;
       }

       if(!@file_exists($_POST['font']))
       {
            print_cp_message($vbphrase['vbgamez_userbarmgr_emptyfont']); exit;
       }

       if(!$userbar_manager->verify_userbar($_POST['id']))
       {
               print_cp_message('Invalid ID');
       }

       $userbar_manager->do_configure_userbar($_POST['id'], $_POST['background'], $_POST['textcolor'], $_POST['font'], $_POST['fontsize']);
       print_cp_message($vbphrase['vbgamez_userbarmgr_saved'], 'vbgamez_admin.php?do=configureuserbar&id='.$_POST['id'], 1);
}
if($_REQUEST['do'] == 'adduserbarlocation')
{
       if(!$userbar_manager->verify_userbar($_REQUEST['id']))
       {
               print_cp_message('Invalid ID');
       }

       $userbar_manager->add_userbar_location($_REQUEST['id']);
}
if($_REQUEST['do'] == 'doadduserbarlocation')
{
       if(!$userbar_manager->verify_userbar($_POST['id']))
       {
               print_cp_message('Invalid ID');
       }

       $_POST['text'] = trim($_POST['text']);
       $_POST['repeat_x'] = intval($_POST['repeat_x']);
       $_POST['repeat_y'] = intval($_POST['repeat_y']);

       if(empty($_POST['text']) OR empty($_POST['repeat_x']) OR empty($_POST['repeat_y']))
       {
            print_cp_message($vbphrase['vbgamez_userbarmgr_emptyfieldslocation']); exit;

       }

       $userbar_manager->do_add_userbar_location($_POST['id'], $_POST['text'], $_POST['radius'], $_POST['repeat_x'], $_POST['repeat_y'], $_POST['font'], $_POST['fontsize'], $_POST['fontcolor'], $_POST['width']);
       print_cp_message($vbphrase['vbgamez_userbarmgr_location_addded'], 'vbgamez_admin.php?do=configureuserbar&id='.$_POST['id'], 1);
}
if($_REQUEST['do'] == 'deleteuserbarcfg')
{
       if(!$userbar_manager->verify_config($_REQUEST['id']))
       {
               print_cp_message('Invalid ID');
       }

       $userbarid = $userbar_manager->get_userbarid_by_configid($_REQUEST['id']);
       $userbar_manager->delete_userbar_location($_REQUEST['id']);
       print_cp_message($vbphrase['vbgamez_userbarmgr_location_deleted'], 'vbgamez_admin.php?do=configureuserbar&id='.$userbarid, 1);
}
if($_REQUEST['do'] == 'edituserbarcfg')
{
       if(!$userbar_manager->verify_config($_REQUEST['id']))
       {
               print_cp_message('Invalid ID');
       }

       $userbar_manager->edit_userbar_location($_REQUEST['id']);
}
if($_REQUEST['do'] == 'doedituserbarlocation')
{
       if(!$userbar_manager->verify_config($_POST['configid']))
       {
               print_cp_message('Invalid ID');
       }

       $_POST['text'] = trim($_POST['text']);
       $_POST['repeat_x'] = intval($_POST['repeat_x']);
       $_POST['repeat_y'] = intval($_POST['repeat_y']);

       if(empty($_POST['text']) OR empty($_POST['repeat_x']) OR empty($_POST['repeat_y']))
       {
            print_cp_message($vbphrase['vbgamez_userbarmgr_emptyfieldslocation']); exit;

       }

       $userbar_manager->do_edit_userbar_location($_POST['configid'], $_POST['text'], $_POST['radius'], $_POST['repeat_x'], $_POST['repeat_y'], $_POST['font'], $_POST['fontsize'], $_POST['fontcolor'], $_POST['enabled'], $_POST['width']);
       print_cp_message($vbphrase['vbgamez_userbarmgr_saved'], 'vbgamez_admin.php?do=configureuserbar&id='.$userbar_manager->get_userbarid_by_configid($_POST['configid']), 1);
}
if($_REQUEST['do'] == 'disableuserbarcfg')
{
       if(!$userbar_manager->verify_config($_REQUEST['id']))
       {
               print_cp_message('Invalid ID');
       }

       $userbar_manager->disable_location($_REQUEST['id']);
       print_cp_message($vbphrase['vbgamez_userbarmgr_location_disabled'], 'vbgamez_admin.php?do=configureuserbar&id='.$userbar_manager->get_userbarid_by_configid($_REQUEST['id']), 1);
}
if($_REQUEST['do'] == 'enableuserbarcfg')
{
       if(!$userbar_manager->verify_config($_REQUEST['id']))
       {
               print_cp_message('Invalid ID');
       }

       $userbar_manager->enable_location($_REQUEST['id']);
       print_cp_message($vbphrase['vbgamez_userbarmgr_location_enabled'], 'vbgamez_admin.php?do=configureuserbar&id='.$userbar_manager->get_userbarid_by_configid($_REQUEST['id']), 1);
}

if($_REQUEST['do'] == 'downloaduserbar')
{
       if(!$userbar_manager->verify_userbar($_REQUEST['id']))
       {
               print_cp_message('Invalid ID');
       }

       $userbar_manager->download_userbar($_REQUEST['id']);
}

if ($_POST['do'] == 'upload')
{
	ignore_user_abort(true);

	$vbulletin->input->clean_array_gpc('p', array(
		'serverfile'   => TYPE_STR
	));

	$vbulletin->input->clean_array_gpc('f', array(
		'userbarfile' => TYPE_FILE
	));

	if (file_exists($vbulletin->GPC['userbarfile']['tmp_name']))
	{
		$xml = file_read($vbulletin->GPC['userbarfile']['tmp_name']);
	}

	else if (file_exists($vbulletin->GPC['serverfile']))
	{
		$xml = file_read($vbulletin->GPC['serverfile']);
	}

	else
	{
		print_stop_message('no_file_uploaded_and_no_local_file_found');
	}

	$userbar_manager->upload_userbar($xml);

        print_cp_message($vbphrase['vbgamez_userbarmgr_userbar_imported'], "vbgamez_admin.php?do=manageuserbar", 1);

}

if($_REQUEST['do'] == 'testuserbar')
{
                 require_once('./packages/vbgamez/userbar.php');

                 $userbar = new vBGamez_Userbar();

                 $server['s']['name'] = 'Test Server';
                 $server['s']['players'] = '15';
                 $server['s']['playersmax'] = '30';
                 $server['s']['map'] = 'de_dust2';
                 $server['b']['status'] = '1';
                 $server['b']['type'] = 'halflife';
                 $server['s']['game'] = 'cstrike';
                 $server['b']['ip'] = '127.0.0.1';
                 $server['b']['c_port'] = '27015';
                 $server['a']['rating'] = '500';
                 $server['a']['views'] = '313';
                 $server['a']['comments'] = '10';
                 $server['a']['playerscount'] = $server['s']['players'];
                 $server['o']['id'] = 0;

                 $userbar->serverinfo = $server;
                 $userbar->additionalinfo = $server['a'];
                 $userbar->construct_userbar($_REQUEST['sid']);
}
/*======================================================================*\
|| #################################################################### ||
   END USERBAR MANAGER
\*======================================================================*/

// ################## менеджер полей  ##################

if($_REQUEST['do'] == 'fields')
{
	print_cp_header($vbphrase['vbgamez_fieldmanager']);

	?>

	<script type="text/javascript">
	function js_page_jump(i, sid)
	{
		var sel = fetch_object("prodsel" + i);
		var act = sel.options[sel.selectedIndex].value;
		if (act != '')
		{
			switch (act)
			{
				case 'edit': page = "vbgamez_admin.php?do=editfield&id="; break;
				case 'delete': page = "vbgamez_admin.php?do=killfield&id="; break;
				default: return;
			}
			document.cpform.reset();
			jumptopage = page + sid + "&s=<?php echo $vbulletin->session->vars['sessionhash']; ?>";
			window.location = jumptopage;
		}
		else
		{
			alert('<?php echo addslashes_js($vbphrase['invalid_action_specified']); ?>');
		}
	}
	</script>
	<?php
	print_form_header('vbgamez_admin', 'savefieldorder');

	print_table_header($vbphrase['vbgamez_fieldmanager'], 5); 

	print_cells_row(array($vbphrase['title'], $vbphrase['type'], $vbphrase['description'], $vbphrase['vbgamez_admincp_poryadok'], $vbphrase['manage']), 1);

	$i = 0;

	$fields = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez_textfields AS vbgamez_textfields ORDER BY sortorder ASC");

	while ($field = $db->fetch_array($fields))
	{
		$title = htmlspecialchars_uni($field['title']);

		if (!$title['enabled'])
		{
			$title = "<strike>$title</strike>";
		}

		$options['edit'] = $vbphrase['edit'];
		$options['delete'] = $vbphrase['delete'];

                $i++;
                $field['description'] = iif(!$field['description'], '---', $field['description']);

		print_cells_row(array(
			$title,
			$field_dm->fetch_field_name($field['type']),
			htmlspecialchars_uni($field['description']),
                        '<center><input name="displayorder[' . $field[fieldid] . ']" value="'.$field['sortorder'].'" class="bginput" size="4" style="text-align: right;" type="text"></center>',

			construct_link_code($vbphrase['edit'], 'vbgamez_admin.php?' . $vbulletin->session->vars['sessionurl'] . 'do=editfield&amp;id=' . $field['fieldid']) .
				construct_link_code($vbphrase['delete'], 'vbgamez_admin.php?' . $vbulletin->session->vars['sessionurl'] . 'do=killfield&amp;id=' . $field['fieldid'])
		), false, '', -2);
	}

	print_cells_row(array(
		'<input type="button" class="button" value="' . $vbphrase['vbgamez_add_field'] . '" onclick="window.location=\'vbgamez_admin.php?' . $vbulletin->session->vars['sessionurl'] . 'do=addfield\';" />', '', '', '', 
		($i ? '<div align="' . vB_vBGamez::fetch_stylevar('right') . '"><input type="submit" class="button" accesskey="s" value="' . $vbphrase['save_display_order'] . '" />' : '&nbsp;'))
	);

	print_table_footer();
}

// ################## Сортировка полей  ##################
if ($_REQUEST['do'] == 'savefieldorder')
{
	foreach($_POST['displayorder'] AS $id => $order)
	{
            $order = intval($order);
            $id = intval($id);
            $db->query("UPDATE " . TABLE_PREFIX . "vbgamez_textfields SET sortorder = $order WHERE fieldid = $id");
	}

	$field_dm->build_field_datastore();

        print_cp_message($vbphrase['vbgamez_fields_ordered'], 'vbgamez_admin.php?do=fields', 1);

}

// ################## Удаление полей  ##################
if ($_POST['do'] == 'deletefield')
{
        $_POST['id'] = intval($_POST['id']);

        $db->query("DELETE FROM " . TABLE_PREFIX . "vbgamez_textfields WHERE fieldid = " . $_POST['id'] . "");

        $newFieldId = 'field'.$_POST['id'];

        $db->query("ALTER TABLE " . TABLE_PREFIX . "vbgamez DROP $newFieldId");

	$field_dm->build_field_datastore();

        print_cp_message($vbphrase['vbgamez_fields_delete'], 'vbgamez_admin.php?do=fields', 1);
}

if($_REQUEST['do'] == 'killfield')
{
                 $_REQUEST['id'] = intval($_REQUEST['id']);

                 print_cp_header($vbphrase['vbgamez_fieldmanager']);

	         print_form_header('vbgamez_admin', 'deletefield');
	         construct_hidden_code('id', $_REQUEST['id']);

	         print_table_header($vbphrase['confirm_deletion']);

	         print_description_row($vbphrase['vbgamez_delete_field_confirm']);

	         print_submit_row($vbphrase['yes'], '', 2, $vbphrase['no']);
}

// ################## Добавление поля  ##################
if ($_REQUEST['do'] == 'addfield')
{

  $select_count_fields = $db->query("SELECT COUNT(fieldid) AS count FROM " . TABLE_PREFIX . "vbgamez_textfields");
  $count_fields = $db->fetch_array($select_count_fields);

  print_cp_header($vbphrase['vbgamez_addfield']);

  vbg_print_form_header('vbgamez_admin', 'doaddfield');

  print_table_header($vbphrase['vbgamez_addfield']);

  print '<script type="text/javascript" src="../clientscript/vbulletin_ajax_vbgamez_admin.js"></script>';

  print_input_row($vbphrase['title'], 'title');

  print_textarea_row($vbphrase['description'], 'description');

  print_label_row($vbphrase['enabled'],'

		<label for="rb_sw_1"><input type="radio" name="enabled" id="rb_sw_1" value="1" tabindex="1" ' . iif(!$field['enabled'], 'checked="checked"') . '/>' . $vbphrase['yes'] . '</label>
		<label for="rb_sw_0"><input type="radio" name="enabled" id="rb_sw_0" value="0" tabindex="0" ' . iif($field['enabled'], 'checked="checked"') . '/>' . $vbphrase['no'] . '</label>', '', 'top', 'enabled');

  vbg_print_select_row($vbphrase['type'], 'type', $field_dm->types, $field['type']);

  print_input_row($vbphrase['vbgamez_admincp_poryadok'], 'sortorder', $count_fields['count'] + 1);

  // OBJID : 1
  //if ($field['type'] == 'input')
  //{
        start_ajax_obj();
        print_input_row($vbphrase['vbgamez_default_value'], 'defaultvalue', '', 0);
        end_ajax_obj();
  //}

  // OBJID : 2
  //if ($field['type'] == 'textarea')
  //{
       start_ajax_obj();
       print_textarea_row($vbphrase['vbgamez_default_value'], 'defaultvalue', '', 10, 40, 0);
       end_ajax_obj();
  //}

  // OBJID : 3
  //if ($field['type'] == 'textarea' OR $field['type'] == 'input')
  //{
       start_ajax_obj(); 
       print_input_row($vbphrase['max_length_of_allowed_user_input'], 'maxchars', 100);
       print_input_row($vbphrase['field_length'], 'fieldsize', 45);
       end_ajax_obj();
  //}

  // OBJID : 4
  //if ($field['type'] == 'textarea')
  //{
       start_ajax_obj();
       print_input_row($vbphrase['text_area_height'], 'rows', 4);
       end_ajax_obj();
  //}

  // OBJID : 5
  //if ($field['type'] == 'select')
  //{
       start_ajax_obj();
       print_textarea_row(construct_phrase($vbphrase['x_enter_the_options_that_the_user_can_choose_from'], $vbphrase['options']), 'options', '', 10, 40, 0);
       print_select_row($vbphrase['set_default_if_yes_first'], 'set_first_default', array(0 => $vbphrase['none'], 1 => $vbphrase['yes_including_a_blank'], 2 => $vbphrase['yes_but_no_blank_option']), 1);
       end_ajax_obj();
  //}

  // OBJID : 6
  //if ($field['type'] == 'radio')
  //{
       start_ajax_obj();
       print_textarea_row(construct_phrase($vbphrase['x_enter_the_options_that_the_user_can_choose_from'], $vbphrase['options']), 'options', '', 10, 40, 0);
       print_yes_no_row($vbphrase['set_default_if_yes_first'], 'set_first_default', true);
       end_ajax_obj();
  //}

  // OBJID : 7
  //if ($field['type'] == 'checkbox')
  //{
        start_ajax_obj();
        print_input_row($vbphrase['limit_selection'], 'max_selects', 0);
        print_textarea_row(construct_phrase($vbphrase['x_enter_the_options_that_the_user_can_choose_from'], $vbphrase['options']) . "<br /><dfn>$vbphrase[note_max_31_options]</dfn>", 'options', '', 10, 40, 0);
        end_ajax_obj();
  //}

  // OBJID : 8
  //if ($field['type'] == 'select_multiple')
  //{
        start_ajax_obj();
        print_input_row($vbphrase['limit_selection'], 'max_selects', 0);
        print_input_row($vbphrase['box_height'], 'rows', 0);
        print_textarea_row(construct_phrase($vbphrase['x_enter_the_options_that_the_user_can_choose_from'], $vbphrase['options']) . "<br /><dfn>$vbphrase[note_max_31_options]</dfn>", 'options', '', 10);
        end_ajax_obj();
   //}

  print_select_row($vbphrase['field_required'], 'required', array(
		0 => $vbphrase['no'],
		1 => $vbphrase['vbgamez_onlyonaddserver'],
		2 => $vbphrase['vbgamez_onlyoneditserver'],
		3 => $vbphrase['yes_always']), $field['required']);

  print_label_row($vbphrase['searchable'],'

		<label for="rb_sws_1"><input type="radio" name="enablesearch" id="rb_sws_1" value="1" tabindex="1" ' . iif(!$field['enablesearch'], 'checked="checked"') . '/>' . $vbphrase['yes'] . '</label>
		<label for="rb_sws_0"><input type="radio" name="enablesearch" id="rb_sws_0" value="0" tabindex="0" ' . iif($field['enablesearch'], 'checked="checked"') . '/>' . $vbphrase['no'] . '</label>', '', 'top', 'enablesearch');

  print_label_row($vbphrase['field_hidden_on_profile'],'

		<label for="rb_swsq_1"><input type="radio" name="private" id="rb_swsq_1" value="1" tabindex="1" ' . iif($field['private'], 'checked="checked"') . '/>' . $vbphrase['yes'] . '</label>
		<label for="rb_swsq_0"><input type="radio" name="private" id="rb_swsq_0" value="0" tabindex="0" ' . iif(!$field['private'], 'checked="checked"') . '/>' . $vbphrase['no'] . '</label>', '', 'top', 'private');

  print_select_row($vbphrase['field_editable_by_user'], 'canedit', array(
		1 => $vbphrase['yes'],
		0 => $vbphrase['no'],
		2 => $vbphrase['vbgamez_onlyonaddserver']
	), 1);

  print_select_row($vbphrase['vbgamez_fieldverify'], 'verifymethod', $field_dm->verifyTypes);
  print_input_row($vbphrase['vbgamez_iconpath'], 'iconpath', $field['iconpath']);

  echo '<script type="text/javascript">vBG_showFieldContnent(fetch_object(\'sel_type_3\').value);</script>';

  print_submit_row($vbphrase['vbgamez_admincp_send']);
}


if ($_REQUEST['do'] == 'doaddfield')
{
    $_POST['title'] = trim($_POST['title']);

    if(empty($_POST['title']))
    {
               print_cp_message($vbphrase['vbgamez_admincp_no_info_on_fields']);
    }

    if($_POST['type'] == 'radio' OR $_POST['type'] == 'select' OR $_POST['type'] == 'select_multiple' OR $_POST['type'] == 'checkbox')
    {
                if(empty($_POST['options']))
                {
                               print_cp_message($vbphrase['vbgamez_admincp_empty_options']);
                }
    }

    $db->query_write("INSERT INTO " . TABLE_PREFIX . "vbgamez_textfields (title, description, enabled, type, sortorder, required, enablesearch, private, canedit, verifymethod, rows, set_first_default, max_selects, options, defaultvalue, fieldsize, iconpath, maxchars) VALUES
                      (".$db->sql_prepare($_POST['title']).",
                       ".$db->sql_prepare($_POST['description']).",
                       ".$db->sql_prepare($_POST['enabled']).",
                       ".$db->sql_prepare($_POST['type']).",
                       ".$db->sql_prepare($_POST['sortorder']).",
                       ".$db->sql_prepare($_POST['required']).",
                       ".$db->sql_prepare($_POST['enablesearch']).",
                       ".$db->sql_prepare($_POST['private']).",
                       ".$db->sql_prepare($_POST['canedit']).",
                       ".$db->sql_prepare($_POST['verifymethod']).",
                       ".$db->sql_prepare($_POST['rows']).",
                       ".$db->sql_prepare($_POST['set_first_default']).",
                       ".$db->sql_prepare($_POST['max_selects']).",
                       ".$db->sql_prepare($_POST['options']).",
                       ".$db->sql_prepare($_POST['defaultvalue']).",
                       ".$db->sql_prepare($_POST['fieldsize']).",
                       ".$db->sql_prepare($_POST['iconpath']).",
                       ".$db->sql_prepare($_POST['maxchars']).")");
    
    $newFieldId = 'field'.$db->insert_id();

    $db->query("ALTER TABLE " . TABLE_PREFIX . "vbgamez ADD $newFieldId TEXT");

    $field_dm->build_field_datastore();

    print_cp_message($vbphrase['vbgamez_field_added'], 'vbgamez_admin.php?do=fields', 1); 
    
}


// ################## Редактирование поля  ##################
if ($_REQUEST['do'] == 'editfield')
{
  
  $_REQUEST['id'] = intval($_REQUEST['id']);

  $field = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "vbgamez_textfields WHERE fieldid = " . $_REQUEST['id'] . "");

  print_cp_header($vbphrase['vbgamez_editfield']);

  print '<script type="text/javascript" src="../clientscript/vbulletin_ajax_vbgamez_admin.js"></script>';

  vbg_print_form_header('vbgamez_admin', 'doeditfield');

  print_table_header($vbphrase['vbgamez_editfield'].': <span class="normal">'.htmlspecialchars_uni($field['title']).'</span>');

  print_input_row($vbphrase['title'], 'title', $field['title']);

  print_textarea_row($vbphrase['description'], 'description', $field['description']);

  print_label_row($vbphrase['enabled'],'

		<label for="rb_sw_1"><input type="radio" name="enabled" id="rb_sw_1" value="1" tabindex="1" ' . iif($field['enabled'], 'checked="checked"') . '/>' . $vbphrase['yes'] . '</label>
		<label for="rb_sw_0"><input type="radio" name="enabled" id="rb_sw_0" value="0" tabindex="0" ' . iif(!$field['enabled'], 'checked="checked"') . '/>' . $vbphrase['no'] . '</label>', '', 'top', 'enabled');

  vbg_print_select_row($vbphrase['type'], 'type', $field_dm->types, $field['type']);

  print_input_row($vbphrase['vbgamez_admincp_poryadok'], 'sortorder', $field['sortorder']);


  // OBJID : 1
  //if ($field['type'] == 'input')
  //{
        start_ajax_obj();
        print_input_row($vbphrase['vbgamez_default_value'], 'defaultvalue', $field['defaultvalue']);
        end_ajax_obj();
  //}

  // OBJID : 2
  //if ($field['type'] == 'textarea')
  //{
       start_ajax_obj();
       print_textarea_row($vbphrase['vbgamez_default_value'], 'defaultvalue', $field['defaultvalue'], 10, 40, 0);
       end_ajax_obj();
  //}

  // OBJID : 3
  //if ($field['type'] == 'textarea' OR $field['type'] == 'input')
  //{
       start_ajax_obj(); 
       print_input_row($vbphrase['max_length_of_allowed_user_input'], 'maxchars', $field['maxchars']);
       print_input_row($vbphrase['field_length'], 'fieldsize', $field['fieldsize']);
       end_ajax_obj();
  //}

  // OBJID : 4
  //if ($field['type'] == 'textarea')
  //{
       start_ajax_obj();
       print_input_row($vbphrase['text_area_height'], 'rows', $field['rows']);
       end_ajax_obj();
  //}

  // OBJID : 5
  //if ($field['type'] == 'select')
  //{
       start_ajax_obj();
       print_textarea_row(construct_phrase($vbphrase['x_enter_the_options_that_the_user_can_choose_from'], $vbphrase['options']), 'options', $field['options'], 10, 40, 0);
       print_select_row($vbphrase['set_default_if_yes_first'], 'set_first_default', array(0 => $vbphrase['none'], 1 => $vbphrase['yes_including_a_blank'], 2 => $vbphrase['yes_but_no_blank_option']),  $field['set_first_default']);
       end_ajax_obj();
  //}

  // OBJID : 6
  //if ($field['type'] == 'radio')
  //{
       start_ajax_obj();
       print_textarea_row(construct_phrase($vbphrase['x_enter_the_options_that_the_user_can_choose_from'], $vbphrase['options']), 'options', $field['options'], 10, 40, 0);
       print_yes_no_row($vbphrase['set_default_if_yes_first'], 'set_first_default', $field['set_first_default']);
       end_ajax_obj();
  //}

  // OBJID : 7
  //if ($field['type'] == 'checkbox')
  //{
        start_ajax_obj();
        print_input_row($vbphrase['limit_selection'], 'max_selects', $field['max_selects']);
        print_textarea_row(construct_phrase($vbphrase['x_enter_the_options_that_the_user_can_choose_from'], $vbphrase['options']) . "<br /><dfn>$vbphrase[note_max_31_options]</dfn>", 'options', $field['options'], 10, 40, 0);
        end_ajax_obj();
  //}

  // OBJID : 8
  //if ($field['type'] == 'select_multiple')
  //{
        start_ajax_obj();
        print_input_row($vbphrase['limit_selection'], 'max_selects', $field['max_selects']);
        print_input_row($vbphrase['box_height'], 'rows', $field['rows']);
        print_textarea_row(construct_phrase($vbphrase['x_enter_the_options_that_the_user_can_choose_from'], $vbphrase['options']) . "<br /><dfn>$vbphrase[note_max_31_options]</dfn>", 'options', $field['options'], 10);
        end_ajax_obj();
   //}


  print_select_row($vbphrase['field_required'], 'required', array(
		0 => $vbphrase['no'],
		1 => $vbphrase['vbgamez_onlyonaddserver'],
		2 => $vbphrase['vbgamez_onlyoneditserver'],
		3 => $vbphrase['yes_always']), $field['required']);

  print_label_row($vbphrase['searchable'],'

		<label for="rb_sws_1"><input type="radio" name="enablesearch" id="rb_sws_1" value="1" tabindex="1" ' . iif($field['enablesearch'], 'checked="checked"') . '/>' . $vbphrase['yes'] . '</label>
		<label for="rb_sws_0"><input type="radio" name="enablesearch" id="rb_sws_0" value="0" tabindex="0" ' . iif(!$field['enablesearch'], 'checked="checked"') . '/>' . $vbphrase['no'] . '</label>', '', 'top', 'enablesearch');

  print_label_row($vbphrase['field_hidden_on_profile'],'

		<label for="rb_swsq_1"><input type="radio" name="private" id="rb_swsq_1" value="1" tabindex="1" ' . iif($field['private'], 'checked="checked"') . '/>' . $vbphrase['yes'] . '</label>
		<label for="rb_swsq_0"><input type="radio" name="private" id="rb_swsq_0" value="0" tabindex="0" ' . iif(!$field['private'], 'checked="checked"') . '/>' . $vbphrase['no'] . '</label>', '', 'top', 'private');

  print_select_row($vbphrase['field_editable_by_user'], 'canedit', array(
		1 => $vbphrase['yes'],
		0 => $vbphrase['no'],
		2 => $vbphrase['vbgamez_onlyonaddserver']
	), $field['canedit']);

  construct_hidden_code('id', $field['fieldid']);

  print_select_row($vbphrase['vbgamez_fieldverify'], 'verifymethod', $field_dm->verifyTypes, $field['verifymethod']);
  print_input_row($vbphrase['vbgamez_iconpath'], 'iconpath', $field['iconpath']);

  echo '<script type="text/javascript">vBG_showFieldContnent(fetch_object(\'sel_type_3\').value);</script>';

  print_submit_row($vbphrase['vbgamez_admincp_send']);
}

if ($_REQUEST['do'] == 'doeditfield')
{
    $_POST['id'] = intval($_POST['id']);

    $_POST['title'] = trim($_POST['title']);

    if(empty($_POST['title']))
    {
               print_cp_message($vbphrase['vbgamez_admincp_no_info_on_fields']);
    }

    if($_POST['type'] == 'radio' OR $_POST['type'] == 'select' OR $_POST['type'] == 'select_multiple' OR $_POST['type'] == 'checkbox')
    {
                if(empty($_POST['options']))
                {
                               print_cp_message($vbphrase['vbgamez_admincp_empty_options']);
                }
    }

    $db->query_write("UPDATE " . TABLE_PREFIX . "vbgamez_textfields SET 
                      title = ".$db->sql_prepare($_POST['title']).",
                      description = ".$db->sql_prepare($_POST['description']).",
                      enabled = ".$db->sql_prepare($_POST['enabled']).",
                      type = ".$db->sql_prepare($_POST['type']).",
                      sortorder = ".$db->sql_prepare($_POST['sortorder']).",
                      required = ".$db->sql_prepare($_POST['required']).",
                      enablesearch = ".$db->sql_prepare($_POST['enablesearch']).",
                      private = ".$db->sql_prepare($_POST['private']).",
                      canedit = ".$db->sql_prepare($_POST['canedit']).",
                      verifymethod = ".$db->sql_prepare($_POST['verifymethod']).",
                      rows = ".$db->sql_prepare($_POST['rows']).",
                      set_first_default = ".$db->sql_prepare($_POST['set_first_default']).",
                      max_selects = ".$db->sql_prepare($_POST['max_selects']).",
                      options = ".$db->sql_prepare($_POST['options']).",
                      defaultvalue = ".$db->sql_prepare($_POST['defaultvalue']).",
                      fieldsize = ".$db->sql_prepare($_POST['fieldsize']).",
                      iconpath = ".$db->sql_prepare($_POST['iconpath']).",
                      maxchars = ".$db->sql_prepare($_POST['maxchars'])."
                      WHERE fieldid = " . $_POST['id'] . "");
    
    $field_dm->build_field_datastore();

    print_cp_message($vbphrase['vbgamez_field_edited'], 'vbgamez_admin.php?do=fields', 1); 
}

/*======================================================================*\
|| #################################################################### ||
   Frame MANAGER
\*======================================================================*/

if ($_REQUEST['do'] == 'framemanager')
{
      $frame_manager->frame_list();
} 
if($_REQUEST['do'] == 'addframe')
{
       $frame_manager->create_frame();
}
if($_REQUEST['do'] == 'doaddframe')
{       
       $_POST['name'] = trim($_POST['name']);
       $_POST['code'] = trim($_POST['code']);

       if(empty($_POST['name']) OR empty($_POST['code']))
       {
            print_cp_message($vbphrase['vbgamez_framemgr_emptytitle']); exit;

       }
       $frame_manager->do_add_frame($_POST['name'], $_POST['description'], $_POST['order'], $_POST['code'], $_POST['codeplayers'], $_POST['codenoplayers'], $_POST['width'], $_POST['height'], $_POST['is_configure']);

       $frameid = $db->insert_id();

       $frame_manager->build_frame_datastore();

       $frame_manager->build_frame_template($frameid, compile_template($_POST['code']), $_POST['code']);

       $frame_manager->build_frame_template($frameid.'_players', compile_template($_POST['codeplayers']), $_POST['codeplayers']);

       $frame_manager->build_frame_template($frameid.'_noplayers', compile_template($_POST['codenoplayers']), $_POST['codenoplayers']);

       print_cp_header($vbphrase['vbgamez_framemanager']);
       build_all_styles(0, 0, 'vbgamez_admin.php?do=framedone&gotoedit=0');
       print_table_footer();

       print_cp_redirect('vbgamez_admin.php?do=framedone&gotoedit=0&id='.$frameid);

}

if($_REQUEST['do'] == 'framedone')
{
       if(empty($_REQUEST['gotoedit']))
       {
                  print_cp_message($vbphrase['vbgamez_framemgr_added'], 'vbgamez_admin.php?do=editframe&id='.$_GET['id'], 1);
       }else{
                  print_cp_message($vbphrase['vbgamez_framemgr_edited'], 'vbgamez_admin.php?do=editframe&id='.$_GET['id'], 1);
       }
}

if($_REQUEST['do'] == 'editframe')
{
       if(!$frame_manager->verify_frame($_REQUEST['id']))
       {
               print_cp_message('Invalid ID');
       }

       $frame_manager->edit_frame($_REQUEST['id']);
}
if($_REQUEST['do'] == 'doeditframe')
{
       $_POST['name'] = trim($_POST['name']);
       $_POST['code'] = trim($_POST['code']);

       if(empty($_POST['name']) OR empty($_POST['code']))
       {
            print_cp_message($vbphrase['vbgamez_framemgr_emptytitle']); exit;

       }

       if(!$frame_manager->verify_frame($_POST['id']))
       {
               print_cp_message('Invalid ID');
       }

       $frame_manager->do_edit_frame($_POST['id'], $_POST['name'], $_POST['description'], $_POST['order'], $_POST['enabled'], $_POST['code'], $_POST['codeplayers'], $_POST['codenoplayers'], $_POST['width'], $_POST['height'], $_POST['is_configure']);

       $frameid = $_POST['id'];

       $frame_manager->build_frame_datastore();

       $frame_manager->build_frame_template($frameid, compile_template($_POST['code']), $_POST['code']);

       $frame_manager->build_frame_template($frameid.'_players', compile_template($_POST['codeplayers']), $_POST['codeplayers']);

       $frame_manager->build_frame_template($frameid.'_noplayers', compile_template($_POST['codenoplayers']), $_POST['codenoplayers']);

       print_cp_header($vbphrase['vbgamez_framemanager']);

       build_all_styles(0, 0, 'vbgamez_admin.php?do=framedone&gotoedit=1&id='.$_POST['id']);
       print_table_footer();

       print_cp_redirect('vbgamez_admin.php?do=framedone&gotoedit=1&id='.$_POST['id']);
}
if($_REQUEST['do'] == 'deleteframe')
{
       if(!$frame_manager->verify_frame($_REQUEST['id']))
       {
               print_cp_message('Invalid ID');
       }

       $frame_manager->delete_frame($_REQUEST['id']);
       $frame_manager->build_frame_datastore();

       print_cp_message($vbphrase['vbgamez_framemgr_deleted'], 'vbgamez_admin.php?do=framemanager', 1);
}
if ($_REQUEST['do'] == 'frameorder')
{
        $frame_manager->save_order($_POST['displayorder']);
        $frame_manager->build_frame_datastore();

        print_cp_message($vbphrase['vbgamez_framemgr_sorted'], 'vbgamez_admin.php?do=framemanager', 1);
}
if($_REQUEST['do'] == 'enableframe')
{
       if(!$frame_manager->verify_frame($_REQUEST['id']))
       {
               print_cp_message('Invalid ID');
       }

       $frame_manager->enable_frame($_REQUEST['id']);
       $frame_manager->build_frame_datastore();

       print_cp_message($vbphrase['vbgamez_framemgr_enabled'], 'vbgamez_admin.php?do=framemanager', 1);
}
if($_REQUEST['do'] == 'disableframe')
{
       if(!$frame_manager->verify_frame($_REQUEST['id']))
       {
               print_cp_message('Invalid ID');
       }

       $frame_manager->disable_frame($_REQUEST['id']);
       $frame_manager->build_frame_datastore();

       print_cp_message($vbphrase['vbgamez_framemgr_disabled'], 'vbgamez_admin.php?do=framemanager', 1);
}

if($_REQUEST['do'] == 'downloadframe')
{
       if(!$frame_manager->verify_frame($_REQUEST['id']))
       {
               print_cp_message('Invalid ID');
       }

       $frame_manager->download_frame($_REQUEST['id']);
}

if ($_POST['do'] == 'uploadframe')
{
	ignore_user_abort(true);

	$vbulletin->input->clean_array_gpc('p', array(
		'serverfile'   => TYPE_STR
	));

	$vbulletin->input->clean_array_gpc('f', array(
		'framefile' => TYPE_FILE
	));

	if (file_exists($vbulletin->GPC['framefile']['tmp_name']))
	{
		$xml = file_read($vbulletin->GPC['framefile']['tmp_name']);
	}

	else if (file_exists($vbulletin->GPC['serverfile']))
	{
		$xml = file_read($vbulletin->GPC['serverfile']);
	}

	else
	{
		print_stop_message('no_file_uploaded_and_no_local_file_found');
	}

	$frame_manager->upload_frame($xml);
        $frame_manager->build_frame_datastore();

        print_cp_message($vbphrase['vbgamez_framemgr_frame_imported'], "vbgamez_admin.php?do=framemanager", 1);

}

if ($_REQUEST['do'] == 'stylevarorder')
{
	foreach($_POST['displayorder'] AS $id => $order)
	{
            $order = intval($order);
            $id = intval($id);
            $db->query("UPDATE " . TABLE_PREFIX . "vbgamez_frame_stylevar SET `order` = $order WHERE stylevarid = $id");
	}

        $frame_manager->build_frame_datastore();

        print_cp_message($vbphrase['vbgamez_stylevars_ordered'], 'vbgamez_admin.php?do=editframe&id=' . $_POST['frameid'], 1);

}

if($_REQUEST['do'] == 'deletestylevar')
{
       $frameinfo = & $frame_manager->verify_stylevar($_REQUEST['id']);

       if(!$frameinfo)
       {
               print_cp_message('Invalid ID');
       }

       $frame_manager->delete_stylevar($_REQUEST['id']);

       $frame_manager->build_frame_datastore();

       print_cp_message($vbphrase['vbgamez_framemgr_deletedstylevar'], 'vbgamez_admin.php?do=editframe&id='.$frameinfo['frameid'], 1);
}

if($_REQUEST['do'] == 'enablestylevar')
{
       $frameinfo = & $frame_manager->verify_stylevar($_REQUEST['id']);

       if(!$frameinfo)
       {
               print_cp_message('Invalid ID');
       }

       $frame_manager->enable_stylevar($_REQUEST['id']);

       $frame_manager->build_frame_datastore();

       print_cp_message($vbphrase['vbgamez_framemgr_enablestylevar'], 'vbgamez_admin.php?do=editframe&id='.$frameinfo['frameid'], 1);
}

if($_REQUEST['do'] == 'disablestylevar')
{
       $frameinfo = & $frame_manager->verify_stylevar($_REQUEST['id']);

       if(!$frameinfo)
       {
               print_cp_message('Invalid ID');
       }

       $frame_manager->disable_stylevar($_REQUEST['id']);

       $frame_manager->build_frame_datastore();

       print_cp_message($vbphrase['vbgamez_framemgr_disabledstylevar'], 'vbgamez_admin.php?do=editframe&id='.$frameinfo['frameid'], 1);
}

if($_REQUEST['do'] == 'addstylevar')
{
       $frame_manager->create_stylevar($_REQUEST['frameid']);
}

if($_REQUEST['do'] == 'doaddstylevar')
{       
       $_POST['title'] = trim($_POST['title']);
       $_POST['variable'] = trim($_POST['variable']);
       $_POST['default'] = trim($_POST['default']);

       if(empty($_POST['title']) OR empty($_POST['variable']))
       {
            print_cp_message($vbphrase['vbgamez_framemgr_emptystylevarinfo']); exit;
       }

       $frame_manager->do_add_stylevar($_POST['id'], $_POST['title'], $_POST['description'], $_POST['order'], $_POST['default'], $_POST['variable'], $_POST['type'], $_POST['addwidth'], $_POST['addheight']);

       $frame_manager->build_frame_datastore();

       print_cp_message($vbphrase['vbgamez_framemgr_stylevaradded'], 'vbgamez_admin.php?do=editframe&id='.$_POST['id'], 1);
}

if($_REQUEST['do'] == 'editstylevar')
{ 
       $frameinfo = $frame_manager->verify_stylevar($_REQUEST['id']);

       $frame_manager->create_stylevar($_REQUEST['id'], $frameinfo);
}

if($_REQUEST['do'] == 'doeditstylevar')
{       
       $_POST['title'] = trim($_POST['title']);
       $_POST['variable'] = trim($_POST['variable']);
       $_POST['default'] = trim($_POST['default']);

       if(empty($_POST['title']) OR empty($_POST['variable']))
       {
            print_cp_message($vbphrase['vbgamez_framemgr_emptystylevarinfo']); exit;
       }

       $frame_manager->do_edit_stylevar($_POST['id'], $_POST['title'], $_POST['description'], $_POST['order'], $_POST['default'], $_POST['variable'], $_POST['enabled'], $_POST['type'], $_POST['addwidth'], $_POST['addheight']);

       $frame_manager->build_frame_datastore();

       $frameinfo = & $frame_manager->verify_stylevar($_POST['id']);

       print_cp_message($vbphrase['vbgamez_framemgr_stylevaredited'], 'vbgamez_admin.php?do=editframe&id='.$frameinfo['frameid'], 1);
}
/*======================================================================*\
|| #################################################################### ||
   END Frame MANAGER
\*======================================================================*/


if ($_REQUEST['do'] == 'setfeatured')
{
        $_REQUEST['id'] = intval($_REQUEST['id']);
        
	if($_REQUEST['id'] == '')
	{
		 print_cp_message($vbphrase['vbgamez_admincp_no_selected']);  
	} 
	else 
	{ 
                $db->query("UPDATE " . TABLE_PREFIX . "vbgamez SET stick = 1, expirydate = 0 WHERE id = " . intval($_REQUEST['id']) . "");

                vB_vBGamez::vBG_Datastore_Clear_Cache($_REQUEST['id'], 'rating');

	 	if($_GET['jsredirect'])
		{
			print '<script type="text/javascript">if(window.opener) { window.opener.location.href = \'' . $vbulletin->options['vbgamez_path'] . '?&do=view&id=' . $_REQUEST['id'] . '\'; window.close(); } </script>'; 
		}

                print_cp_message($vbphrase['vbgamez_setted_featured'], "vbgamez_admin.php", 1);
	}
}

if ($_REQUEST['do'] == 'unsetfeatured')
{
        $_REQUEST['id'] = intval($_REQUEST['id']);
        
	if($_REQUEST['id'] == '')
	{
		 print_cp_message($vbphrase['vbgamez_admincp_no_selected']);  
	} 
	else 
	{ 
                $db->query("UPDATE " . TABLE_PREFIX . "vbgamez SET stick = 0, expirydate = 0 WHERE id = " . intval($_REQUEST['id']) . "");
                vB_vBGamez::vBG_Datastore_Clear_Cache($_REQUEST['id'], 'rating');

	 	if($_GET['jsredirect'])
		{
			print '<script type="text/javascript">if(window.opener) { window.opener.location.href = \'' . $vbulletin->options['vbgamez_path'] . '?&do=view&id=' . $_REQUEST['id'] . '\'; window.close(); } </script>'; 
		}

                print_cp_message($vbphrase['vbgamez_unsetted_featured'], "vbgamez_admin.php", 1);
	}
}


if($_REQUEST['do'] == 'viewtypes')
{
	print_cp_header($vbphrase['vbgamez_admincp_game_type']);

	print_form_header('', '');

	print_table_header($vbphrase['vbgamez_admincp_game_type'], 3); 

	print_cells_row(array($vbphrase['vbgamez_admincp_game_type'], $vbphrase['vbgamez_original_gametype']), 1);
	
	$types = vbgamez_type_list();

	foreach($types AS $key => $title)
	{
		print_cells_row(array(
			$key, $title), false, '', -2);
			$child = vB_vBGamez::fetch_additional_game_type($key);
			foreach($child AS $type => $title2)
			{
				print_cells_row(array(
					" &nbsp; - ".vB_vBGamez::replaceGameTypesForAddons($key, $type), " &nbsp; - ".$title2), false, '', -2);
			}
	}
	
	
	print_table_footer();

print_cp_footer();
}
if($_REQUEST['do'] == 'subscribtions')
{
	print_cp_header($vbphrase['vbgamez_admincp_vbgameztitle']);
	
	$getAll = $db->query("SELECT vbgamez_subscribe.*, user.username, user.userid, vbgamez.cache_name FROM " . TABLE_PREFIX . "vbgamez_subscribe AS vbgamez_subscribe
						LEFT JOIN " . TABLE_PREFIX . "user AS user ON (user.userid = vbgamez_subscribe.userid)
					 	LEFT JOIN " . TABLE_PREFIX . "vbgamez AS vbgamez ON (vbgamez.id = vbgamez_subscribe.serverid)");
	if(!$db->num_rows($getAll))
	{
		print_cp_message($vbphrase['vbgamez_empty_logs']);
	}
	
	print_form_header('', '');

	print_table_header($vbphrase['vbgamez_please_read'], 4); 

	print_description_row($vbphrase['vbgamez_subscriptions_log_desc']);
	print_table_footer();
	
	print_form_header('', '');

	print_table_header($vbphrase['vbgamez_transactions_stats'], 5); 

	print_cells_row(array($vbphrase['vbgamez_server_name'], $vbphrase['username'], $vbphrase['date'], $vbphrase['vbgamez_pay_finish_date'], $vbphrase['manage']), 1);
	
	$types = vbgamez_type_list();


	while($sub = $db->fetch_array($getAll))
	{
		$sub['cache_name'] = vB_vBGamez::vbgamez_string_html($sub['cache_name']);

		if (vbstrlen($sub['cache_name']) == 0)
		{
			$sub['cache_name'] = $vbphrase['vbgamez_server_unknown_name'];
		}
		$sub['cache_name'] = '<a href="' . $vbulletin->options['vbgamez_path'] . '?do=view&id=' . $sub['serverid'] . '" target="_blank">' . $sub['cache_name'] . '</a>';
		$sub['username'] = '<center><a href="user.php?do=edit&u=' . $sub['userid'] . '">' . $sub['username'] . '</a></center>';
		print_cells_row(array($sub['cache_name'], $sub['username'], "<center>".vbdate($vbulletin->options['dateformat'].' '.$vbulletin->options['timeformat'], $sub['date'])."</center>", "<center>".vbdate($vbulletin->options['dateformat'].' '.$vbulletin->options['timeformat'], $sub['expirydate'])."</center>", '<div align="right">[<a href="vbgamez_admin.php?do=dellog&subid=' . $sub['subid'] . '">' . $vbphrase['vbgamez_log_del'] . '</a>]</div>'), false, '', -2);
	}
	print_table_footer();

print_cp_footer();
}

if($_REQUEST['do'] == 'dellog')
{
       $_REQUEST['subid'] = intval($_REQUEST['subid']);

	   $db->query("DELETE FROM " . TABLE_PREFIX . "vbgamez_subscribe WHERE subid = $_REQUEST[subid]");

       print_cp_message($vbphrase['vbgamez_deleted_from_log'], 'vbgamez_admin.php?do=subscribtions', 1);
}
print "<br /><br /><center>".vB_vBGamez::fetch_vbg_version()."</center>";
print_cp_footer();

?> 