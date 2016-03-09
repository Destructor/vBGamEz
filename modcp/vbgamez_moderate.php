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

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('style', 'vbgamez');
$specialtemplates = array('products', 'vbgamez_fieldcache');
 
// ########################## REQUIRE BACK-END ############################
require_once('./global.php');

require_once('./packages/vbgamez/bootstrap.php');

require_once('./packages/vbgamez/manager/userbar.php');
// Field manager
require_once('./packages/vbgamez/manager/field.php');
$field_dm = new vBGamEz_FieldManager($vbulletin);

vB_vBGamez_Route::setUrls();
// Field manager
require_once('./packages/vbgamez/manager/field.php');
$field_dm = new vBGamEz_FieldManager($vbulletin);

// ############################# LOG ACTION ###############################
log_admin_action();

// ############################# ACTIONS ###############################

if (empty($_REQUEST['do']))  
 { 
    $_REQUEST['do'] = 'moderate';
 }

// ############################# Functions ###############################

function admincp_vbg_update_userinfo($userid)
{
    global $vbulletin;

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


// ################## Удаление сервера  ##################

if ($_REQUEST['do'] == 'delete')
{
        $_REQUEST['id'] = intval($_REQUEST['id']);
        
	if($_REQUEST['id'] == '')
	{
		 print_cp_message($vbphrase['vbgamez_admincp_no_selected']);  
	} 
	else 
	{ 
                $userid = admincp_vbg_get_userid($_REQUEST['id']);

		$db->query_write("DELETE FROM " . TABLE_PREFIX . "vbgamez WHERE id = '" . $_REQUEST['id'] . "'");
		$db->query_write("DELETE FROM " . TABLE_PREFIX . "vbgamez_comments WHERE serverid = '" . $_REQUEST['id'] . "'");

                admincp_vbg_update_userinfo($userid);

                if($_REQUEST['moderate'] == '1')
                {
		         print_cp_message($vbphrase['vbgamez_admincp_server_deleted'], 'vbgamez_moderate.php?do=moderate', 1); 
                }else{
		         print_cp_message($vbphrase['vbgamez_admincp_server_deleted'], 'vbgamez_moderate.php?do=list', 1); 
                }

	}
}

// ################## Одобрение сервера  ##################
if ($_REQUEST['do'] == 'approve')
{
   $_REQUEST['id'] = intval($_REQUEST['id']);

   $db->query("UPDATE " . TABLE_PREFIX . "vbgamez SET valid = 0 WHERE id = '" . $_REQUEST['id'] . "'");

   vB_vBGamez::vBG_Datastore_Clear_Cache($_REQUEST['id'], 'valid');

   print_cp_message($vbphrase['vbgamez_admincp_server_complete_add'], 'vbgamez_moderate.php?do=moderate', 1);

}

// ################## Отклонение сервера  ##################
if ($_REQUEST['do'] == 'refuse')
{
   $_REQUEST['id'] = intval($_REQUEST['id']);

   $db->query("UPDATE " . TABLE_PREFIX . "vbgamez SET valid = 2 WHERE id = '" . $_REQUEST['id'] . "'");

   vB_vBGamez::vBG_Datastore_Clear_Cache($_REQUEST['id'], 'valid');

   print_cp_message($vbphrase['vbgamez_admincp_server_deleted'], 'vbgamez_moderate.php?do=moderate', 1);

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

  print_form_header('', '');

  print_table_header($vbphrase['vbgamez_admincp_edit_server']);

	$result = $db->query_read("SELECT vbgamez.*, user.username FROM " . TABLE_PREFIX . "vbgamez AS vbgamez
                                   LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = vbgamez.userid) 
                                   WHERE id = '" . $_REQUEST['id'] . "'");

	if ($db->num_rows($result) > 0) {

		while ($server = $db->fetch_array($result)) {

  if(!vBGamez_dbGames_Bootstrap::vbgamez_is_db_game($server['type']))
  {

                $lookup = $server;

                $game_types = vB_vBGamez::Fetch_Game_Types(true);

		print_select_row($vbphrase['vbgamez_admincp_game_type'], 'game', vbgamez_type_list(), $server['type']);

		print_input_row($vbphrase['vbgamez_admincp_insert_ip'], 'ip',$server['ip']);

		print_input_row($vbphrase['vbgamez_admincp_insert_port'], 'c_port',$server['c_port'], true, 35, 5);

		print_input_row($vbphrase['vbgamez_admincp_insert_qport'], 'q_port',$server['q_port'], true, 35, 5);

		print_input_row($vbphrase['vbgamez_admincp_insert_sport'], 's_port',$server['s_port'], true, 35, 5);

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

  }else{
          define('VBG_HIDE_NOTDB_GAMES', true);

                $lookup = $server;

                $game_types = vB_vBGamez::Fetch_Game_Types(true);

		print_select_row($vbphrase['vbgamez_admincp_game_type'], 'game', vbgamez_type_list(), $server['type']);

		print_input_row($vbphrase['vbgamez_ip_address_db'], 'db_address', '*****');

		print_input_row($vbphrase['vbgamez_ip_user_db'], 'db_user', '*****');

		print_input_row($vbphrase['vbgamez_ip_password_db'], 'db_password', '*****');

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
	}
	print_table_footer();

	$db->free_result($result);        
}
// ################## Список серверов на модерации ##################

if ($_REQUEST['do'] == 'moderate')
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
				case 'approve': page = "vbgamez_moderate.php?do=approve&id="; break;
				case 'serveredit': page = "vbgamez_moderate.php?do=modify&id="; break;
				case 'refuse': page = "vbgamez_moderate.php?do=refuse&id="; break;
				case 'delete': page = "vbgamez_moderate.php?do=delete&moderate=1&id="; break;
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

	$servers = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez AS vbgamez
                                    LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = vbgamez.userid)
                                    WHERE valid = 1  ORDER BY id ASC");

        if(!$db->num_rows($servers))
        {
                    $emptyModerateServers = true;
        }


	$servers2 = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez AS vbgamez
                                    LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = vbgamez.userid)
                                    WHERE valid = 2  ORDER BY id ASC");


        if(!$db->num_rows($servers2))
        {
                    $emptyModerateServers2 = true;
        }
             
        if($emptyModerateServers AND $emptyModerateServers2)
        {
                    print_cp_message($vbphrase['vbgamez_empty_moderate_servers']);
        }

        if(empty($emptyModerateServers))
        {
	         print_form_header('', '', false, true, 'cpform', '90%', 'download');

	         print_table_header($vbphrase['vbgamez_admincp_view_servers_on_moderate'], 6); 

	         print_cells_row(array($vbphrase['username'], $vbphrase['vbgamez_admincp_server_name'], $vbphrase['vbgamez_admincp_game_type'], $vbphrase['ip_address'], $vbphrase['controls']), 1);

	         $i = 0;


	         while ($server = $db->fetch_array($servers))
	         {
                         if(empty($title)) { $tite = $vbphrase['vbgamez_server_unknown_name']; }

		         $title = vB_vBGamez::vbgamez_string_html($server['cache_name']);

		         if ($server['disabled'])
		         {
			         $title = "<strike>$title</strike>";
		         }

		         $options['serveredit'] = $vbphrase['vbgamez_view_adv_info'];
		         $options['approve'] = $vbphrase['vbgamez_admincp_odobrit'];
		         $options['refuse'] = $vbphrase['vbgamez_admincp_otkaz'];
		         $options['delete'] = $vbphrase['delete'];

                         $game_type = '<center><img alt="" src="../' . vB_vBGamez::vbgamez_icon_game($server['type'], $server['cache_game']) . '" title="' . vB_vBGamez::vbgamez_text_type_game($server['type'], $server['cache_game']) . '"/></center>';

                         if(empty($server['username'])) { $server['username'] = $vbphrase['guest']; }

			 if($server['userid'])
			 {
                         	$username = '<a href="userp.php?do=edit&u=' . $server['userid'] . '">' . $server['username'] . '</a>';
			 }else{
				$username = $server['username'];
			 }

                         $i++;

		print_cells_row(array(
			$username,
			$title,
                        $game_type,
			$server['ip'].":".$server['c_port'],
			"<div align=\"" . vB_vBGamez::fetch_stylevar('right') . "\">
				<select name=\"s$server[id]\" id=\"prodsel$i\" onchange=\"js_page_jump($i, '$server[id]')\" class=\"bginput\">
					" . construct_select_options($options) . "
				</select>&nbsp;<input type=\"button\" class=\"button\" value=\"" . $vbphrase['go'] . "\" onclick=\"js_page_jump($i, '$server[id]');\" />
			</div>"
		), false, '', -2);
	       }

                 unset($options);

	         print_table_footer();
        }

	?>

	<script type="text/javascript">
	function js_page_jump2(i, sid)
	{
		var sel = fetch_object("prodsel2" + i);
		var act = sel.options[sel.selectedIndex].value;
		if (act != '')
		{
			switch (act)
			{
				case 'approve': page = "vbgamez_moderate.php?do=approve&id="; break;
				case 'serveredit': page = "vbgamez_moderate.php?do=modify&id="; break;
				case 'delete': page = "vbgamez_moderate.php?do=delete&moderate=1&id="; break;
				default: return;
			}
			document.cpform2.reset();
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


        if(empty($emptyModerateServers2))
        {

	print_form_header('', '', false, true, 'cpform2', '90%', 'download');

	print_table_header($vbphrase['vbgamez_admincp_refuse'], 6); 

	print_cells_row(array($vbphrase['username'], $vbphrase['vbgamez_admincp_server_name'], $vbphrase['vbgamez_admincp_game_type'], $vbphrase['ip_address'], $vbphrase['controls']), 1);

	$i = 0;

	while ($server = $db->fetch_array($servers2))
	{
                if(empty($title)) { $tite = $vbphrase['vbgamez_server_unknown_name']; }

		$title = vB_vBGamez::vbgamez_string_html($server['cache_name']);

		if ($server['disabled'])
		{
			$title = "<strike>$title</strike>";
		}else{
			$title = '<a href="' . $vbulletin->options['vbgamez_path'] . '?do=view&id=' . $server['id'] . '" target="_blank">'.$title.'</a>';
		}

		$options['serveredit'] = $vbphrase['vbgamez_view_adv_info'];
		$options['approve'] = $vbphrase['vbgamez_admincp_odobrit'];
		$options['delete'] = $vbphrase['delete'];

                $game_type = '<center><img alt="" src="../' . vB_vBGamez::vbgamez_icon_game($server['type'], $server['cache_game']) . '" title="' . vB_vBGamez::vbgamez_text_type_game($server['type'], $server['cache_game']) . '"/></center>';

                if(empty($server['username'])) { $server['username'] = $vbphrase['guest']; }

		if($server['userid'])
		{
                        $username = '<a href="user.php?do=edit&u=' . $server['userid'] . '">' . $server['username'] . '</a>';
		}else{
			$username = $server['username'];
		}

                $i++;

		print_cells_row(array(
			$username,
			$title,
                        $game_type,
			$server['ip'].":".$server['c_port'],
			"<div align=\"" . vB_vBGamez::fetch_stylevar('right') . "\">
				<select name=\"s$server[id]\" id=\"prodsel2$i\" onchange=\"js_page_jump2($i, '$server[id]')\" class=\"bginput\">
					" . construct_select_options($options) . "
				</select>&nbsp;<input type=\"button\" class=\"button\" value=\"" . $vbphrase['go'] . "\" onclick=\"js_page_jump2($i, '$server[id]');\" />
			</div>"
		), false, '', -2);
	}

        
	print_table_footer();

        }
}


// ################## Просмотр загруженных карт ##################

if ($_REQUEST['do'] == 'custom_maps')
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
				case 'confirm': page = "vbgamez_moderate.php?do=confirmmap&id="; break;
				case 'delete': page = "vbgamez_moderate.php?do=deletemap&id="; break;
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
	
	$servers = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez_custom_maps AS vbg_cm
                                    LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = vbg_cm.userid)
                                    ORDER BY date DESC");

  if(!$db->num_rows($servers))
  {
           print_cp_message($vbphrase['vbgamez_missing_maps']);
  }
	print_form_header('', '');

	print_table_header($vbphrase['vbgamez_view_uploaded_maps'], 8); 

	print_cells_row(array($vbphrase['username'], $vbphrase['vbgamez_admincp_game_type'], $vbphrase['vbgamez_map'], $vbphrase['date'], $vbphrase['vbgamez_map_path'], $vbphrase['vbgamez_check_status'], $vbphrase['controls']), 1);

	$i = 0;

	while ($server = $db->fetch_array($servers))
	{

                if(empty($server['username'])) { $server['username'] = $vbphrase['guest']; }

		if($server['userid'])
		{

			$title = '<center><a href="user.php?do=edit&u=' . $server['userid'] . '">' . htmlspecialchars_uni($server['username']) . '</a></center>';
		}else{
			$title = '<center>'.$server['username'].'</center>';
		}
		if(!$server['mapname'])
		{
			$server['mapname'] = str_replace(array('.gif', '.png', '.jpg'), '', basename($server['uploadedto']));
		}
		if(!$server['mapname'])
		{
			$server['mapname'] = '---';
		}
		$mapname = '<center>'.htmlspecialchars_uni($server['mapname']).'</center>';

                $date = vbdate($vbulletin->options['timeformat']." ".$vbulletin->options['dateformat'], $server['date']);

                $path = '<a href=".' . $server['uploadedto'] . '" target="_blank" title="' . $server['uploadedto'] . '">' . basename($server['uploadedto']) . '</a>';
 
                if($server['moderation'])
                {
		          $options = array('confirm' => $vbphrase['vbgamez_admincp_odobrit'], 'delete' => $vbphrase['delete']);
                          $status = $vbphrase['vbgamez_map_on_moderation'];
                }else{
		          $options = array('delete' => $vbphrase['delete']);
                          $status = $vbphrase['vbgamez_map_displayed'];
                }

                $game_type = '<center><img alt="" src="../' . vB_vBGamez::vbgamez_icon_game($server['type'], $server['game']) . '" title="' . vB_vBGamez::vbgamez_text_type_game($server['type'], $server['game']) . '"/></center>';


                $i++;

		print_cells_row(array(
			$title,
                        $game_type,
			$mapname,
                        $date,
                        $path,
                        $status,

			"<div align=\"" . vB_vBGamez::fetch_stylevar('right') . "\">
				<select name=\"s$server[id]\" id=\"prodsel$i\" onchange=\"js_page_jump($i, '$server[id]')\" class=\"bginput\">
					" . construct_select_options($options) . "
				</select>&nbsp;<input type=\"button\" class=\"button\" value=\"" . $vbphrase['go'] . "\" onclick=\"js_page_jump($i, '$server[id]');\" />
			</div>"
		), false, '', -2);
	}

	print_table_footer();
}

// ################## Удаление карты ##################

if ($_REQUEST['do'] == 'deletemap')
{   
    $mapid = intval($_REQUEST['id']);

    $select_mapinfo = $db->query("SELECT * FROM " . TABLE_PREFIX . "vbgamez_custom_maps WHERE id = '" . $mapid . "'");
    $select_mapinfo = $db->fetch_array($select_mapinfo);

    @unlink($select_mapinfo['uploadedto']);

    $db->query("DELETE FROM " . TABLE_PREFIX . "vbgamez_custom_maps WHERE id = '" . $mapid . "'");

    print_cp_message($vbphrase['vbgamez_admincp_map_deleted'], 'vbgamez_moderate.php?do=custom_maps', 1);
}

// ################## Подтверждение карты ##################

if ($_REQUEST['do'] == 'confirmmap')
{   
    $mapid = intval($_REQUEST['id']);

    $select_mapinfo = $db->query("SELECT * FROM " . TABLE_PREFIX . "vbgamez_custom_maps WHERE id = '" . $mapid . "'");
    $select_mapinfo = $db->fetch_array($select_mapinfo);

    $original_filename = $select_mapinfo['uploadedto'];
    $new_filename = str_replace('_moderate', '', $original_filename);

    @rename($original_filename, $new_filename);

    $db->query("UPDATE " . TABLE_PREFIX . "vbgamez_custom_maps SET uploadedto = ".$db->sql_prepare($new_filename) . ", moderation = 0 WHERE id = '" . $mapid . "'");

    print_cp_message($vbphrase['vbgamez_map_confirmed'], 'vbgamez_moderate.php?do=custom_maps', 1);
}

print "<br /><br /><center>".vB_vBGamez::fetch_vbg_version()."</center>";
print_cp_footer();

?> 