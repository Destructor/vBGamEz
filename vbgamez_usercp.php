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
define('THIS_SCRIPT', 'vbgamez_usercp');
define('VBG_PACKAGE', true);
define('CSRF_PROTECTION', true);

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array('posting', 'user', 'vbgamez');

// get special data templates from the datastore
$specialtemplates = array('vbgamez_fieldcache', 'vbgamez_framestylevarcache');

// pre-cache templates used by specific actions
$globaltemplates = array('vbgamez_addserver',
                         'vbgamez_sortarrow',
                         'vbgamez_userservers',
                         'vbgamez_userserversbits',
                         'vbgamez_infobits',
                         'vbgamez_viewinfo',
                         'vbgamez_editserver',
                         'vbgamez_fieldbits',
                         'vbgamez_configure_frame',
                         'vbgamez_configure_frame_preview', 
                         'vbgamez_frame_options'
);

if(in_array($_REQUEST['do'], array('pay', 'dopay')))
{
	$globaltemplates = array_merge($globaltemplates, array('vbgamez_pay_prepare', 'vbgamez_pay'));
}
// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/packages/vbgamez/bootstrap.php');
require_once(DIR . '/packages/vbgamez/field.php');
require_once(DIR .'/packages/vbgamez/paid.php');
	
vB_vBGamez::bootstrap();
$vBG_FieldsController = new vBGamEz_FieldsController($vbulletin);
if(!$_REQUEST['do'])
{
	$_REQUEST['do'] = 'myservers';
}
// ############################# ADD SERVER TO DATABASE #####################
if($_REQUEST['do'] == 'addserver')
{

       if(!$vbulletin->options['vbgamez_guest_addserver'])
       {
	if (!$vbulletin->userinfo['userid'])
        {
           print_no_permission();
        }
       }

        if (!$vbulletin->options['vbgamez_user_addserver']) 
        { 
           standard_error (fetch_error ('vbgamez_addserver_disabled'));
        }

        if($vbulletin->options['vbgamez_myservers_enable'])
        {
         if($vbulletin->userinfo['servers'] >= $vbulletin->options['vbgamez_servers'])
         { 
    	     standard_error(fetch_error('vbgamez_maximum_server_already', $vbulletin->options['vbgamez_usercp_path']));
         }
        }

        $game_types = vB_vBGamez::Fetch_Game_Types('yes');

	$navbits['' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . ''] = $vbphrase['vbgamez'];
	$navbits[] = $vbphrase['vbgamez_add'];

        $custom_required_fields = $vBG_FieldsController->getDisplayView(null, true);
        $custom_additional_fields = $vBG_FieldsController->getDisplayView(null);

        if($custom_additional_fields)
        {
                   $show['additionalfields_line'] = true;
        }

        $js_array_db_games = vBGamez_dbGames_Bootstrap::get_js_db_gameTypes();
        $js_array_db_games .= vBGamez_dbGames_Bootstrap::get_js_fields();

        if(VBG_IS_VB4)
        {
              $templater = vB_Template::create('vbgamez_addserver');

              $navbits = construct_navbits($navbits);
              $navbar = render_navbar_template($navbits);
              $templater->register_page_templates();
              $templater->register('navbar', $navbar);
              $templater->register('game_types', $game_types);
              $templater->register('custom_required_fields', $custom_required_fields);
              $templater->register('custom_additional_fields', $custom_additional_fields);
              $templater->register('js_array_db_games', $js_array_db_games);
              print_output($templater->render());

        }else{

	      $navbits = construct_navbits($navbits);
	      eval('$navbar = "' . fetch_template('navbar') . '";');
	      eval('print_output("' . fetch_template('vbgamez_addserver') . '");');
        }
}

// ############################# DO ADD SERVER TO DATABASE #####################
if ($_REQUEST['do'] == 'doaddserver')
{
       if(!$vbulletin->options['vbgamez_guest_addserver'])
       {
	if (!$vbulletin->userinfo['userid'])
        {
           print_no_permission();
        }
       }

       if(!$vbulletin->userinfo['userid'])
       {
            $vbulletin->options['vbgamez_myservers_enable'] = 0;
       }

        if (!$vbulletin->options['vbgamez_user_addserver']) 
        { 
                standard_error (fetch_error ('vbgamez_addserver_disabled'));
        }

        if($vbulletin->options['vbgamez_myservers_enable'])
        {
         if($vbulletin->userinfo['servers'] >= $vbulletin->options['vbgamez_servers'])
         { 
    	    standard_error(fetch_error('vbgamez_maximum_server_already'));
         }
        }

	$vbulletin->input->clean_array_gpc('r', array(
		'game' 	=> TYPE_STR,
		'steam' 	=> TYPE_INT,
		'pirated' 	=> TYPE_INT,
		'nonsteam' 	=> TYPE_INT,
		'address' 	=> TYPE_STR,
		'port' 	=> TYPE_INT,
		'enable_server_comments' 	=> TYPE_STR,
		'db_address' 	=> TYPE_STR,
		'db_user' 	=> TYPE_STR,
		'db_password' 	=> TYPE_STR,
                'db_name' => TYPE_STR,
                'server_name' => TYPE_STR,
                'server_ip' => TYPE_STR));

	$vbulletin->GPC['game'] = trim($vbulletin->GPC['game']);
	$vbulletin->GPC['steam'] = intval($vbulletin->GPC['steam']);
	$vbulletin->GPC['pirated'] = intval($vbulletin->GPC['pirated']);
	$vbulletin->GPC['nonsteam'] = intval($vbulletin->GPC['nonsteam']);
	$vbulletin->GPC['address'] = trim($vbulletin->GPC['address']);
	$vbulletin->GPC['port'] = intval($vbulletin->GPC['port']);
	$vbulletin->GPC['db_address'] = trim($vbulletin->GPC['db_address']);
	$vbulletin->GPC['db_user'] = trim($vbulletin->GPC['db_user']);
	$vbulletin->GPC['db_password'] = trim($vbulletin->GPC['db_password']);
	$vbulletin->GPC['db_name'] = trim($vbulletin->GPC['db_name']);
	$vbulletin->GPC['server_name'] = trim($vbulletin->GPC['server_name']);
	$vbulletin->GPC['server_ip'] = trim($vbulletin->GPC['server_ip']);

        $errors = '';
        $vbgamez_protocol_list = vbgamez_protocol_list();

	if (empty($vbulletin->GPC['game']))
        {
           $errors = $vbphrase['vbgamez_error_select_game'];
        }

	if (!$errors AND !$vbgamez_protocol_list[$vbulletin->GPC['game']])
        {
           $errors = $vbphrase['vbgamez_error_invalid_game'];
        }

        if(!vBGamez_dbGames_Bootstrap::vbgamez_is_db_game($vbulletin->GPC['game']))
        {
	          if (!$errors AND empty($vbulletin->GPC['address']))
                  {
                     $errors = $vbphrase['vbgamez_error_enter_address'];
                  }

                  if(!$errors AND preg_match("/[^0-9a-z\.\-\[\]\:]/i", $vbulletin->GPC['address']))
                  {
                       $errors = $vbphrase['vbgamez_invalid_ip'];
                  }

	          if (!$errors AND empty($vbulletin->GPC['port']))
                  {
                     $errors = $vbphrase['vbgamez_error_enter_port'];
                  }

	          if (!$errors)
                  {
					 $port_settings = vbgamez_port_conversion($vbulletin->GPC['game'], $vbulletin->GPC['port'], 0, 0);
                     $vbulletin->GPC['port'] = $port_settings[0];
					 $vbulletin->GPC['q_port'] = $port_settings[1];

	             if ($vbulletin->GPC['port'] < 1 || $vbulletin->GPC['port'] > 99999)
                     {
                        $errors = $vbphrase['vbgamez_error_invalid_port'];
                     }
                  }

	          if (!$errors)
                  {
                     $select = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez WHERE ip = " . $vbulletin->db->sql_prepare($vbulletin->GPC['address']) . " AND c_port = " . $vbulletin->db->sql_prepare($vbulletin->GPC['port']) . "");
                     if($vbulletin->db->num_rows($select) > 0)
                     {
                        $errors = $vbphrase['vbgamez_error_server_already_added'];
                     }
                  }

	          if (!$errors)
                  {

                    $server_query = vbgamez_query_live($vbulletin->GPC['game'], $vbulletin->GPC['address'], $vbulletin->GPC['port'], $vbulletin->GPC['q_port'], $vbulletin->GPC['port'], "s");

                     if (!$server_query['b']['status'])
                     {
                       $errors = $vbphrase['vbgamez_error_server_is_offline'];
                     }
                  }

        }else{

	          if (!$errors AND empty($vbulletin->GPC['db_address']))
                  {
                     $errors = $vbphrase['vbgamez_error_enter_address_db'];
                  }

                  if(!$errors AND empty($vbulletin->GPC['db_user']))
                  {
                       $errors = $vbphrase['vbgamez_error_enter_user_db'];
                  }

                  if(!$errors AND empty($vbulletin->GPC['db_password']))
                  {
                       $errors = $vbphrase['vbgamez_error_enter_db_password'];
                  }

                  if(!$errors AND !vBGamez_dbGames_Bootstrap::vbgamez_verify_dbsettings($vbulletin->GPC['db_address'], $vbulletin->GPC['db_user'], $vbulletin->GPC['db_password']))
                  {
                       $errors = $vbphrase['vbgamez_error_invalid_db_data'];
                  }

                  if(!$errors AND vBGamez_dbGames_Bootstrap::fieldIsRequired($vbulletin->GPC['game'], 'showdbname') AND empty($vbulletin->GPC['db_name']))
                  {
                       $errors = $vbphrase['vbgamez_error_invalid_dbname'];
                  }

                  if(!$errors AND vBGamez_dbGames_Bootstrap::fieldIsRequired($vbulletin->GPC['game'], 'showservername') AND empty($vbulletin->GPC['server_name']))
                  {
                       $errors = $vbphrase['vbgamez_error_invalid_servername'];
                  }

                  if(!$errors AND vBGamez_dbGames_Bootstrap::fieldIsRequired($vbulletin->GPC['game'], 'showserverip') AND empty($vbulletin->GPC['server_ip']))
                  {
                       $errors = $vbphrase['vbgamez_error_invalid_serverip'];
                  }

	          if (!$errors)
                  {
                    $dbinfo = array();

                    $dbinfo['address'] = $vbulletin->GPC['db_address'];
                    $dbinfo['user'] = $vbulletin->GPC['db_user'];
                    $dbinfo['password'] = $vbulletin->GPC['db_password'];

                    $dbinfo['db_name'] = $vbulletin->GPC['db_name'];
                    $dbinfo['server_name'] = $vbulletin->GPC['server_name'];
                    $dbinfo['server_ip'] = $vbulletin->GPC['server_ip'];

                    $dbconnection = vBGamez_dbGames_Bootstrap::vbgamez_encode_db_info($dbinfo);

                    $dbgame = vBGamez_dbGames_Bootstrap::fetchClassLibary($vbulletin->GPC['game'], $dbconnection);

                    $serverdata = $dbgame->fetch_info();

                    if(!$dbgame->verify_db_tables())
                    {
                               eval(standard_error($vbphrase['vbgamez_error_invalid_tables']));
                    }

                    if(!($serverinfo = $serverdata))
                    {
                           $errors = $vbphrase['vbgamez_error_invalid_db_data'];
                    }else{

                     $server_query = vbgamez_query_live($vbulletin->GPC['game'], $serverinfo['s']['address'], $serverinfo['s']['port'], $serverinfo['s']['port'], $serverinfo['s']['port'], "s");

                        $select = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez WHERE ip = " . $vbulletin->db->sql_prepare($serverinfo['s']['address']) . " AND c_port = " . $vbulletin->db->sql_prepare($serverinfo['s']['port']) . "");

                        if($vbulletin->db->num_rows($select) > 0)
                        {
                           $errors = $vbphrase['vbgamez_error_server_already_added'];
                        }

                     if (!$errors AND !$server_query['b']['status'])
                     {
                       $errors = $vbphrase['vbgamez_error_server_is_offline'];
                     }
                  }
                 }

                 $vbulletin->GPC['address'] = $serverinfo['s']['address'];
                 
                 $vbulletin->GPC['port'] = $serverinfo['s']['port'];
				 $vbulletin->GPC['q_port'] = $vbulletin->GPC['port'];

        }

        $verify_fields = $vBG_FieldsController->verifyPostFields();
        if(!$errors AND $vBG_FieldsController->errors)
        {
                      $errors = $vBG_FieldsController->errors;
        }

        if(!$vbulletin->options['vbgamez_comments_userdisable'])
        {
               $vbulletin->GPC['enable_server_comments'] = '0';
        }

        if(!empty($errors))
        {
          eval (standard_error(fetch_error('vbgamez_errors', "<br />".$errors)));

        }else{
	      vB_vBGamez::loadClassFromFile('geo');
  	      if(vB_vBGamez_Geo_db::check_settings() AND $vbulletin->options['vbgamez_server_location_enable'])
  	      {
                  $servergeo = vB_vBGamez_Geo_db::fetchInfo(@gethostbyname($vbulletin->GPC['address']));
		  $server_location = $servergeo->country_code;
		  $server_city = $servergeo->city;
		  $server_country = $servergeo->country_name;
  	      } 

	      $vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "vbgamez (userid, status, ip, q_port, c_port, type, cache_game, steam, nonsteam, valid, zone, disabled, commentsenable, dbinfo, pirated, location, city, country) 
                                                 VALUES (" . $vbulletin->db->sql_prepare($vbulletin->userinfo['userid']) . ",
                                                         '1',
                                                         " . $vbulletin->db->sql_prepare($vbulletin->GPC['address']) . ",
                                                         " . $vbulletin->db->sql_prepare($vbulletin->GPC['q_port']) . ",
                                                         " . $vbulletin->db->sql_prepare($vbulletin->GPC['port']) . ",
                                                         " . $vbulletin->db->sql_prepare($vbulletin->GPC['game']) . ",
                                                         " . $vbulletin->db->sql_prepare($vbulletin->GPC['game']) . ",
                                                         " . $vbulletin->db->sql_prepare($vbulletin->GPC['steam']) . ",
                                                         " . $vbulletin->db->sql_prepare($vbulletin->GPC['nonsteam']) . ",
                                                         " . $vbulletin->db->sql_prepare(iif(vB_vBGamez::check_permissions(), $vbulletin->options['vbgamez_moderwait'], 0)) . ",
                                                         '0',
                                                         '0',
                                                         " . $vbulletin->db->sql_prepare($vbulletin->GPC['enable_server_comments']) . ",
                                                         " . $vbulletin->db->sql_prepare(iif($dbconnection, $dbconnection, '')) . ",
                                                         " . $vbulletin->db->sql_prepare($vbulletin->GPC['pirated']) . ",
                                                         " . $vbulletin->db->sql_prepare($server_location) . ",
                                                         " . $vbulletin->db->sql_prepare($server_city) . ",
                                                         " . $vbulletin->db->sql_prepare($server_country) . ")");
              $serverid = $db->insert_id();

              $lookup = vB_vBGamez::vbgamez_verify_id($serverid, true);
	      define('DIRECT_UPDATE_SERVER_CACHE', 1);
              $serverinfo = vB_vBGamez::vBG_Datastore_Cache($vbulletin->GPC['address'], $vbulletin->GPC['q_port'], $vbulletin->GPC['port'], $vbulletin->GPC['port'], $vbulletin->GPC['game'], 's', $lookup);

              $vBG_FieldsController->save_info($serverid);

              if($vbulletin->userinfo['userid'])
              {
                        vB_vBGamez::vbg_update_userinfo();
              }

// TODO: move to send_pm function
if($vbulletin->options['vbgamez_addserverpm'] AND $vbulletin->options['vbgamez_addserveradminids'] AND $vbulletin->options['vbgamez_addserveroptions'] AND vB_vBGamez::check_permissions())
{

      if($vbulletin->options['vbgamez_addserveroptions'] == 'moderate' AND $vbulletin->options['vbgamez_moderwait'])
      {
         $pm_message = $vbphrase['vbgamez_server_added_moderated'];
         $pm_title = $vbphrase['vbgamez_server_added_moderated_title'];

         if(!can_administer())
         {
               $link_to_server = $vbulletin->options['bburl']."/".$vbulletin->config['Misc']['modcpdir']."/vbgamez_moderate.php?do=moderate";
         }else{
               $link_to_server = $vbulletin->options['bburl']."/".$vbulletin->config['Misc']['admincpdir']."/vbgamez_admin.php?do=moderate";
         }

      }else if($vbulletin->options['vbgamez_addserveroptions'] == 'onlyadd' AND !$vbulletin->options['vbgamez_moderwait']){    
         $pm_message = $vbphrase['vbgamez_server_added'];
         $pm_title = $vbphrase['vbgamez_server_added_title'];
         $link_to_server = $vbulletin->options['vbgamez_path']."?do=view&id=".$serverid;


      }else if($vbulletin->options['vbgamez_addserveroptions'] == 'always'){   

             if($vbulletin->options['vbgamez_moderwait'])
             {
                              $pm_message = $vbphrase['vbgamez_server_added_moderated'];
                              $pm_title = $vbphrase['vbgamez_server_added_moderated_title'];
                              if(!can_administer())
                              {
                                    $link_to_server = $vbulletin->options['bburl']."/".$vbulletin->config['Misc']['modcpdir']."/vbgamez_moderate.php?do=moderate";
                              }else{
                                    $link_to_server = $vbulletin->options['bburl']."/".$vbulletin->config['Misc']['admincpdir']."/vbgamez_admin.php?do=moderate";
                              }

             }else{
                              $pm_message = $vbphrase['vbgamez_server_added'];
                              $pm_title = $vbphrase['vbgamez_server_added_title'];
                              $link_to_server = $vbulletin->options['vbgamez_path']."?do=view&id=".$serverid;
             }
 
      }

            if($pm_title AND $pm_message)
            {
                  $explode_userids = explode(",", $vbulletin->options['vbgamez_addserveradminids']);
                  require_once(DIR . '/includes/functions_misc.php');

                  foreach($explode_userids AS $userid)
                  {
                        $touserinfo = fetch_userinfo($userid);

                       if(empty($vbulletin->userinfo['userid']))
                       {
                              $vbulletin->userinfo['userid'] = $userid;
                              $vbulletin->userinfo['username'] = $touserinfo['username'];
                       }

                        $pmdm =& datamanager_init('PM', $vbulletin, ERRTYPE_ARRAY);
                        $pmdm->set('fromuserid', $vbulletin->userinfo['userid']);
                        $pmdm->set('fromusername', $vbulletin->userinfo['username']);
                        $pmdm->set('title', $pm_title);
                        $pmdm->set('message', construct_phrase($pm_message, $serverinfo['s']['name'], $link_to_server));
                        $pmdm->set_recipients($touserinfo['username'], $permissions);
                        $pmdm->set('dateline', TIMENOW);
                        $pmdm->set_info('savecopy',0);
                        if(empty($pmdm->errors))
                        {
                                      $pmdm->save();
                        }
                  }
            }
}

      if($vbulletin->options['vbgamez_moderwait'] AND vB_vBGamez::check_permissions())
      {
         if(!$vbulletin->options['vbgamez_myservers_enable'])
         {
          standard_error(fetch_error('vbgamez_server_send_to_moder', $vbulletin->options['vbgamez_path'])); 
         }

       }else{
 
         if(!$vbulletin->options['vbgamez_myservers_enable'])
         {
          standard_error(fetch_error('vbgamez_addserver_added')); 
         }
      }

      exec_header_redirect('' . $vbulletin->options['vbgamez_usercp_path'] . '?' . $vbulletin->session->vars['sessionurl'] . "do=myservers");

   }

}

// ##################### USER CP SERVER MANAGER ###############################

if ($_REQUEST['do'] == 'myservers')
{

  if (!$vbulletin->userinfo['userid'])
  {
    print_no_permission();
  }

  if(!$vbulletin->options['vbgamez_myservers_enable'])
  {
     standard_error (fetch_error ('vbgamez_myservers_disabled'));
  }

  $perpage = $vbulletin->input->clean_gpc('r', 'perpage', TYPE_UINT);
  $pagenumber = $vbulletin->input->clean_gpc('r', 'pagenumber', TYPE_UINT);
  $sortfield = $vbulletin->input->clean_gpc('r', 'sortfield', TYPE_NOHTML);
  $sortorder = $vbulletin->input->clean_gpc('r', 'sortorder', TYPE_NOHTML);

  $perpage = vB_vBGamez::sanitize_perpage($perpage, 100, $vbulletin->options['vbgamez_myserver_perpage']);

  if (!$pagenumber)
  {
	$pagenumber = 1;
  }

  $limitlower = ($pagenumber - 1) * $perpage + 1;
  $limitupper = $pagenumber * $perpage;

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
          $sortfield = $vbulletin->options['vbgamez_sort_myserver'];
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

  $sorturl = '' . $vbulletin->options['vbgamez_usercp_path'] . '?' . $vbulletin->session->vars['sessionurl'] . 'do=myservers&amp;';
  
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

  $server_list = vB_vBGamez::vBG_Datastore_Cache_all("s", true);
  $total = vB_vBGamez::vbgamez_cached_totals(true);

  $server = array();
  $show['vbg_pay_stick'] = false;
  $show['vbg_can_renew'] = false;

  if(vBGamez_Paid::paidIsEnabled($vbulletin))
   {
		$show['vbg_pay_stick'] = true;
   }

  if(vBGamez_Paid::paidIsEnabled($vbulletin, true))
   {
		$show['vbg_can_renew'] = true;
   }

  foreach ($server_list as $server)
    { 
          $misc   = vB_vBGamez::vbgamez_server_misc($server);
          $server = vB_vBGamez::vbgamez_server_html($server);

          $server['i']['statusid'] = $server['i']['valid'];
          $server['i']['valid'] = vB_vBGamez::fetch_server_status($server['i']['valid']);
		  $show['vbgexpiry'] = false;
		  if($server['i']['expirydate'])
		  {
		  	$server['i']['expirydate'] = vbdate($vbulletin->options['dateformat'].' '.$vbulletin->options['timeformat'], $server['i']['expirydate']);
			$show['vbgexpiry'] = true;
		  }
          $connectlink = vbgamez_software_link($server['b']['type'], $server['b']['ip'], $server['b']['c_port'], $server['b']['q_port'], $server['b']['s_port']);

          if(VBG_IS_VB4)
          {
                $templater = vB_Template::create('vbgamez_userserversbits');
                $templater->register('server', $server);
                $templater->register('connectlink', $connectlink);
                $templater->register('misc', $misc);
                $listbits .= $templater->render(); 
          }else{
                eval('$listbits .= "' . fetch_template('vbgamez_userserversbits') . '";');
          }
    }

  if (!$listbits)
  {
        if ($vbulletin->options['vbgamez_user_addserver']) 
        { 
                     eval(standard_error(construct_phrase($vbphrase['vbgamez_servers_missing'], $vbulletin->options['vbgamez_usercp_path'].'?do=addserver')));
        }else{
                     exec_header_redirect($vbulletin->options['vbgamez_path']);
        }
  }

  // permissions
  $permissions = array();
  $permissions['can_edit'] = iif(!$vbulletin->options['vbgamez_can_edit_server'], $vbphrase['vbgames_ne']);
  $permissions['can_delete'] = iif(!$vbulletin->options['vbgamez_user_delete_server'], $vbphrase['vbgames_ne']);
  $permissions['can_full_delete'] = iif(!$vbulletin->options['vbgamez_user_delete_server_from_db'], $vbphrase['vbgames_ne']);
  $permissions['can_add_servers'] = $vbulletin->options['vbgamez_servers'];
  $permissions['can_disable_comments'] = iif(!$vbulletin->options['vbgamez_comments_userdisable'], $vbphrase['vbgames_ne']);
  $permissions['can_delete_comments'] = iif(!$vbulletin->options['vbgamez_del_user_comments'], $vbphrase['vbgames_ne']);

  $pagenav = construct_page_nav($pagenumber, $perpage, $total['servers'], '' . $vbulletin->options['vbgamez_usercp_path'] . '?' . $vbulletin->session->vars['sessionurl'] . 'do=myservers&amp;sort=' . $sortfield . '&amp;order=' . $oppositeorder . '&amp;pp=' . $perpage . '');

  $navbits['' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . ''] = $vbphrase['vbgamez'];
  $navbits[] = $vbphrase['vbgamez_myservers'];

  if($vbulletin->options['vbgamez_ratingsystem_enable'] AND $vbulletin->options['vbgamez_comments_enable'])
  {
      $vbg_colspan = 10;
  }elseif($vbulletin->options['vbgamez_ratingsystem_enable'] AND !$vbulletin->options['vbgamez_comments_enable'])
  {
      $vbg_colspan = 9;
  }elseif(!$vbulletin->options['vbgamez_ratingsystem_enable'] AND $vbulletin->options['vbgamez_comments_enable'])
  {
      $vbg_colspan = 9;
  }elseif(!$vbulletin->options['vbgamez_ratingsystem_enable'] AND $vbulletin->options['vbgamez_comments_enable'])
  {
      $vbg_colspan = 8;
  }

  if(VBG_IS_VB4)
  {
         
           $templater = vB_Template::create('vbgamez_userservers');

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
           $templater->register('permissions', $permissions);
           print_output($templater->render());
  }else{
	   $navbits = construct_navbits($navbits);
	   eval('$navbar = "' . fetch_template('navbar') . '";');
	   eval('print_output("' . fetch_template('vbgamez_userservers') . '");');
  }
}

// ############################# EDIT SERVER #####################
if($_REQUEST['do'] == 'editserver')
{

	$vbulletin->input->clean_array_gpc('r', array(
		'id' 	=> TYPE_INT));

	if (!$vbulletin->userinfo['userid'] OR !$vbulletin->options['vbgamez_can_edit_server'])
        {
           print_no_permission();
        }

        $lookup = vB_vBGamez::vbgamez_verify_id($vbulletin->GPC['id'], true);

        if($lookup['userid'] != $vbulletin->userinfo['userid'])
        { 
           print_no_permission();
        }

        if(!$vbulletin->options['vbgamez_myservers_enable'])
        {
           standard_error (fetch_error ('vbgamez_myservers_disabled'));
        }

        $game_types = vB_vBGamez::Fetch_Game_Types(true);

        $lookup['ip'] = htmlspecialchars($lookup['ip']);
        $lookup['port'] = htmlspecialchars($lookup['c_port']);

        $show['show_hide_db_1'] = iif(!vBGamez_dbGames_Bootstrap::vbgamez_is_db_game($lookup['type']), 'style="display:none;"');

        $show['show_hide_db_0'] = iif(vBGamez_dbGames_Bootstrap::vbgamez_is_db_game($lookup['type']), 'style="display:none;"');

        $lookup['address_db'] = '*****';
        $lookup['user_db'] = '*****';
        $lookup['password_db'] = '*****';
        $lookup['server_name'] = vB_vBGamez::vbgamez_string_html($lookup['cache_name']);
        $lookup['server_ip'] = $lookup['ip'].':'.$lookup['c_port'];
        $lookup['db_name'] = '*****';

        if($lookup['commentsenable'] == '1')
        {
              $lookup['enablecomments'] = 'checked="checked"';
        }

        if(vB_vBGamez::vbg_ajax_show_steam($lookup['type'], $lookup))
        {
              $show['steam_style_display'] = '';
              $show['steam_content'] = vB_vBGamez::vbg_ajax_show_steam($lookup['type'], $lookup);
        }else{
              $show['steam_style_display'] = 'style="display:none;"';
        }

	$navbits['' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . ''] = $vbphrase['vbgamez'];
	$navbits[] = $vbphrase['vbgamez_editserver'];

        $custom_required_fields = $vBG_FieldsController->getDisplayView($lookup, true);
        $custom_additional_fields = $vBG_FieldsController->getDisplayView($lookup);

        if($custom_additional_fields)
        {
                   $show['additionalfields_line'] = true;
        }

        $js_array_db_games = vBGamez_dbGames_Bootstrap::get_js_db_gameTypes();
        $js_array_db_games .= vBGamez_dbGames_Bootstrap::get_js_fields();

        $show['showdbname'] = !vBGamez_dbGames_Bootstrap::fieldIsRequired($lookup['type'], 'showdbname');
        $show['showservername'] = !vBGamez_dbGames_Bootstrap::fieldIsRequired($lookup['type'], 'showservername');
        $show['showserverip'] = !vBGamez_dbGames_Bootstrap::fieldIsRequired($lookup['type'], 'showserverip');

        if(VBG_IS_VB4)
        {
               $templater = vB_Template::create('vbgamez_editserver');

               $navbits = construct_navbits($navbits);
               $navbar = render_navbar_template($navbits);
               $templater->register_page_templates();
               $templater->register('navbar', $navbar);
               $templater->register('game_types', $game_types);
               $templater->register('server', $lookup);
               $templater->register('game_types', $game_types);
               $templater->register('custom_required_fields', $custom_required_fields);
               $templater->register('custom_additional_fields', $custom_additional_fields);
               $templater->register('js_array_db_games', $js_array_db_games);
               print_output($templater->render());
        }else{
               $server = $lookup;
	       $navbits = construct_navbits($navbits);
	       eval('$navbar = "' . fetch_template('navbar') . '";');
	       eval('print_output("' . fetch_template('vbgamez_editserver') . '");');
        }
}



// ############################# EDIT SERVER #####################
if($_REQUEST['do'] == 'doeditserver')
{

	$vbulletin->input->clean_array_gpc('r', array(
                'id' => TYPE_INT,
		'game' 	=> TYPE_STR,
		'steam' 	=> TYPE_INT,
		'pirated' 	=> TYPE_INT,
		'nonsteam' 	=> TYPE_INT,
		'address' 	=> TYPE_STR,
		'port' 	=> TYPE_INT,
		'enable_server_comments' 	=> TYPE_STR,
		'db_address' 	=> TYPE_STR,
		'db_user' 	=> TYPE_STR,
		'db_password' 	=> TYPE_STR,
                'db_name' => TYPE_STR,
                'server_name' => TYPE_STR,
                'server_ip' => TYPE_STR));


	if (!$vbulletin->userinfo['userid'] OR !$vbulletin->options['vbgamez_can_edit_server'])
        {
           print_no_permission();
        }

        $lookup = vB_vBGamez::vbgamez_verify_id($vbulletin->GPC['id'], true);

        if($lookup['userid'] != $vbulletin->userinfo['userid'])
        { 
           print_no_permission();
        }

        if(!$vbulletin->options['vbgamez_myservers_enable'])
        {
           standard_error (fetch_error ('vbgamez_myservers_disabled'));
        }

	$vbulletin->GPC['id'] = trim($vbulletin->GPC['id']);
	$vbulletin->GPC['game'] = trim($vbulletin->GPC['game']);
	$vbulletin->GPC['address'] = trim($vbulletin->GPC['address']);
	$vbulletin->GPC['port'] = intval($vbulletin->GPC['port']);
	$vbulletin->GPC['db_address'] = trim($vbulletin->GPC['db_address']);
	$vbulletin->GPC['db_user'] = trim($vbulletin->GPC['db_user']);
	$vbulletin->GPC['db_password'] = trim($vbulletin->GPC['db_password']);
	$vbulletin->GPC['db_name'] = trim($vbulletin->GPC['db_name']);
	$vbulletin->GPC['server_name'] = trim($vbulletin->GPC['server_name']);
	$vbulletin->GPC['server_ip'] = trim($vbulletin->GPC['server_ip']);


        $errors = '';
        $vbgamez_protocol_list = vbgamez_protocol_list();

	if (empty($vbulletin->GPC['game']))
        {
           $errors = $vbphrase['vbgamez_error_select_game'];
        }

	if (!$errors AND !$vbgamez_protocol_list[$vbulletin->GPC['game']])
        {
           $errors = $vbphrase['vbgamez_error_invalid_game'];
        }

        if(!vBGamez_dbGames_Bootstrap::vbgamez_is_db_game($vbulletin->GPC['game']))
        {
	          if (!$errors AND empty($vbulletin->GPC['address']))
                  {
                     $errors = $vbphrase['vbgamez_error_enter_address'];
                  }

                  if(!$errors AND preg_match("/[^0-9a-z\.\-\[\]\:]/i", $vbulletin->GPC['address']))
                  {
                       $errors = $vbphrase['vbgamez_invalid_ip'];
                  }

	          if (!$errors AND empty($vbulletin->GPC['port']))
                  {
                     $errors = $vbphrase['vbgamez_error_enter_port'];
                  }

	          if (!$errors)
                  {
						 $port_settings = vbgamez_port_conversion($vbulletin->GPC['game'], $vbulletin->GPC['port'], 0, 0);
	                     $vbulletin->GPC['port'] = $port_settings[0];
						 $vbulletin->GPC['q_port'] = $port_settings[1];

		             if ($vbulletin->GPC['port'] < 1 || $vbulletin->GPC['port'] > 99999)
	                     {
	                        $errors = $vbphrase['vbgamez_error_invalid_port'];
	                     }
                  }

	          if (!$errors)
                  {
                      $select = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez WHERE ip = " . $vbulletin->db->sql_prepare($vbulletin->GPC['address']) . " AND c_port = " . $vbulletin->db->sql_prepare($vbulletin->GPC['port']) . " AND type = " . $vbulletin->db->sql_prepare($vbulletin->GPC['game']) . " AND id != '" . $vbulletin->GPC['id'] . "' ");
                     if($vbulletin->db->num_rows($select) > 0)
                     {
                        $errors = $vbphrase['vbgamez_error_server_already_added'];
                     }
                  }

	          if (!$errors)
                  {

                    $server_query = vbgamez_query_live($vbulletin->GPC['game'], $vbulletin->GPC['address'], $vbulletin->GPC['port'], $vbulletin->GPC['q_port'], $vbulletin->GPC['port'], "s");
                     if (!$server_query['b']['status'])
                     {
                       $errors = $vbphrase['vbgamez_error_server_is_offline'];
                     }
                  }

        }else{

                  if($vbulletin->GPC['db_address'] != '*****' OR $vbulletin->GPC['db_user'] != '*****' OR $vbulletin->GPC['db_password'] != '*****')
                  {

	                if (!$errors AND empty($vbulletin->GPC['db_address']))
                        {
                           $errors = $vbphrase['vbgamez_error_enter_address_db'];
                        }

                        if(!$errors AND empty($vbulletin->GPC['db_user']))
                        {
                             $errors = $vbphrase['vbgamez_error_enter_user_db'];
                        }

                        if(!$errors AND empty($vbulletin->GPC['db_password']))
                        {
                             $errors = $vbphrase['vbgamez_error_enter_db_password'];
                        }

                        if(!$errors AND !vBGamez_dbGames_Bootstrap::vbgamez_verify_dbsettings($vbulletin->GPC['db_address'], $vbulletin->GPC['db_user'], $vbulletin->GPC['db_password']))
                        {
                             $errors = $vbphrase['vbgamez_error_invalid_db_data'];
                        }

                        if(!$errors AND vBGamez_dbGames_Bootstrap::fieldIsRequired($vbulletin->GPC['game'], 'showdbname') AND empty($vbulletin->GPC['db_name']))
                        {
                             $errors = $vbphrase['vbgamez_error_invalid_dbname'];
                        }

                        if(!$errors AND vBGamez_dbGames_Bootstrap::fieldIsRequired($vbulletin->GPC['game'], 'showservername') AND empty($vbulletin->GPC['server_name']))
                        {
                             $errors = $vbphrase['vbgamez_error_invalid_servername'];
                        }

                        if(!$errors AND vBGamez_dbGames_Bootstrap::fieldIsRequired($vbulletin->GPC['game'], 'showserverip') AND empty($vbulletin->GPC['server_ip']))
                        {
                             $errors = $vbphrase['vbgamez_error_invalid_serverip'];
                        }

	                if (!$errors)
                        {
                          $dbinfo = array();

                          $dbinfo['address'] = $vbulletin->GPC['db_address'];
                          $dbinfo['user'] = $vbulletin->GPC['db_user'];
                          $dbinfo['password'] = $vbulletin->GPC['db_password'];

                          $dbinfo['db_name'] = $vbulletin->GPC['db_name'];
                          $lookup['dbinfo']['server_name'] = $vbulletin->GPC['server_name'];
                          $dbinfo['server_ip'] = $vbulletin->GPC['server_ip'];

                          $dbconnection = vBGamez_dbGames_Bootstrap::vbgamez_encode_db_info($dbinfo);

                          $dbgame = vBGamez_dbGames_Bootstrap::fetchClassLibary($vbulletin->GPC['game'], $dbconnection);

                          $serverdata = $dbgame->fetch_info();

                          if(!$dbgame->verify_db_tables())
                          {
                               eval(standard_error($vbphrase['vbgamez_error_invalid_tables']));
                          }

                          if(!($serverinfo = $serverdata))
                          {
                                 $errors = $vbphrase['vbgamez_error_invalid_db_data'];
                          }else{

                           $server_query = vbgamez_query_live($vbulletin->GPC['game'], $serverinfo['s']['address'], $serverinfo['s']['port'], $serverinfo['s']['port'], $serverinfo['s']['port'], "s");

                              $select = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez WHERE ip = " . $vbulletin->db->sql_prepare($serverinfo['s']['address']) . " AND c_port = " . $vbulletin->db->sql_prepare($serverinfo['s']['port']) . " AND type = " . $vbulletin->db->sql_prepare($vbulletin->GPC['game']) . " AND id != '" . $vbulletin->GPC['id'] . "' ");

                              if($vbulletin->db->num_rows($select) > 0)
                              {
                                 $errors = $vbphrase['vbgamez_error_server_already_added'];
                              }

                           if (!$errors AND !$server_query['b']['status'])
                           {
                             $errors = $vbphrase['vbgamez_error_server_is_offline'];
                           }
                        }
                       }

                 }else{
                          $lookup = vB_vBGamez::vbgamez_verify_id($vbulletin->GPC['id'], true);

                          $lookup['dbinfo'] = vBGamez_dbGames_Bootstrap::vbgamez_fetch_db_info($lookup['dbinfo']);

                          $lookup['dbinfo']['server_name'] = $vbulletin->GPC['server_name'];
                          $lookup['dbinfo']['server_ip'] = $vbulletin->GPC['server_ip'];

                          $lookup['dbinfo'] = vBGamez_dbGames_Bootstrap::vbgamez_encode_db_info($lookup['dbinfo']);

                          $dbconnection = $lookup['dbinfo'];

                          $dbgame = vBGamez_dbGames_Bootstrap::fetchClassLibary($vbulletin->GPC['game'], $dbconnection);

                          $serverdata = $dbgame->fetch_info();

                          if(!$dbgame->verify_db_tables())
                          {
                               eval(standard_error($vbphrase['vbgamez_error_invalid_tables']));
                          }

                          if(!($serverinfo = $serverdata))
                          {
                                 $errors = $vbphrase['vbgamez_error_invalid_db_data'];
                          }else{

                           $server_query = vbgamez_query_live($vbulletin->GPC['game'], $serverinfo['s']['address'], $serverinfo['s']['port'], $serverinfo['s']['port'], $serverinfo['s']['port'], "s");

                              $select = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez WHERE ip = " . $vbulletin->db->sql_prepare($serverinfo['s']['address']) . " AND c_port = " . $vbulletin->db->sql_prepare($serverinfo['s']['port']) . " AND type = " . $vbulletin->db->sql_prepare($vbulletin->GPC['game']) . " AND id != '" . $vbulletin->GPC['id'] . "' ");

                              if($vbulletin->db->num_rows($select) > 0)
                              {
                                 $errors = $vbphrase['vbgamez_error_server_already_added'];
                              }

                           if (!$errors AND !$server_query['b']['status'])
                           {
                             $errors = $vbphrase['vbgamez_error_server_is_offline'];
                           }
                        }

                 }  


                 $vbulletin->GPC['address'] = $serverinfo['s']['address'];
           
                 $vbulletin->GPC['port'] = $serverinfo['s']['port'];

				 $vbulletin->GPC['q_port'] = $vbulletin->GPC['port'];
        }

        $verify_fields = $vBG_FieldsController->verifyPostFields();
        if(!$errors AND $vBG_FieldsController->errors)
        {
                      $errors = $vBG_FieldsController->errors;
        }

        if(!$vbulletin->options['vbgamez_comments_userdisable'])
        {
               $vbulletin->GPC['enable_server_comments'] = '0';
        }

        if(!empty($errors))
        {
          eval (standard_error(fetch_error('vbgamez_errors', "<br />".$errors)));

        }else{

	      vB_vBGamez::loadClassFromFile('geo');
  	      if(vB_vBGamez_Geo_db::check_settings() AND $vbulletin->options['vbgamez_server_location_enable'])
  	      {
                  $servergeo = vB_vBGamez_Geo_db::fetchInfo(@gethostbyname($vbulletin->GPC['address']));
		  $server_location = $servergeo->country_code;
		  $server_city = $servergeo->city;
		  $server_country = $servergeo->country_name;
  	      } 

	      $vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "vbgamez SET ip = " . $vbulletin->db->sql_prepare($vbulletin->GPC['address']) . ",
 										   q_port = " . $vbulletin->db->sql_prepare($vbulletin->GPC['q_port']) . ",
 										   c_port = " . $vbulletin->db->sql_prepare($vbulletin->GPC['port']) . ",
 										   type = " . $vbulletin->db->sql_prepare($vbulletin->GPC['game']) . ",
 										   steam = " . $vbulletin->db->sql_prepare($vbulletin->GPC['steam']) . ",
 										   pirated = " . $vbulletin->db->sql_prepare($vbulletin->GPC['pirated']) . ",
 										   nonsteam = " . $vbulletin->db->sql_prepare($vbulletin->GPC['nonsteam']) . ",
 										   commentsenable = " . $vbulletin->db->sql_prepare($vbulletin->GPC['enable_server_comments']) . ",
 										   dbinfo = " . $vbulletin->db->sql_prepare(iif($dbconnection, $dbconnection, '')) . ",
 										   location = " . $vbulletin->db->sql_prepare($server_location) . ",
 										   city = " . $vbulletin->db->sql_prepare($server_city) . ",
 										   country = " . $vbulletin->db->sql_prepare($server_country) . "
 										   WHERE id = '" . $vbulletin->GPC['id'] . "' and userid = '" . $vbulletin->userinfo['userid'] . "'");

              vB_vBGamez::vBG_Datastore_Clear_Cache($lookup['id'], 'all');

              vB_vBGamez::vBG_Datastore_Cache($vbulletin->GPC['address'], $vbulletin->GPC['q_port'], $vbulletin->GPC['port'], $vbulletin->GPC['port'], $vbulletin->GPC['game'], 's', $lookup);

              $vBG_FieldsController->save_info($vbulletin->GPC['id']);

              exec_header_redirect('' . $vbulletin->options['vbgamez_usercp_path'] . '?' . $vbulletin->session->vars['sessionurl'] . "do=myservers");
       
        }
}



// ############################# DELETE SERVER FROM DATABASE #####################
if ($_REQUEST['do'] == 'delserver')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'id' 		=> TYPE_INT,
	));

        $id = $vbulletin->GPC['id'];

        $lookup = vB_vBGamez::vbgamez_verify_id($id, true);

        if($lookup['userid'] != $vbulletin->userinfo['userid'] OR empty($id))
        { 
           print_no_permission();
        }

        if(!$vbulletin->options['vbgamez_myservers_enable'])
        {
            standard_error (fetch_error ('vbgamez_myservers_disabled'));
        }

        if(!$vbulletin->options['vbgamez_user_delete_server'])
        {
            print_no_permission();
        }


        $vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "vbgamez SET userid = 0 WHERE id = '" . $lookup['id'] . "'");

        if($vbulletin->options['vbgamez_user_delete_server_from_db'])
        { 
                $vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "vbgamez WHERE id = '" . $lookup['id'] . "'");
        }

         vB_vBGamez::vbg_update_userinfo($lookup['userid']);

         exec_header_redirect('' . $vbulletin->options['vbgamez_usercp_path'] . '?' . $vbulletin->session->vars['sessionurl'] . "do=myservers");
}


// ############################# VIEW SERVER INFO FROM MY SERVERS #####################
if($_REQUEST['do'] == 'viewinfo')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'id' 		=> TYPE_INT,
	));

	if (!$vbulletin->userinfo['userid'])
        {
           print_no_permission();
        }

        $lookup = vB_vBGamez::vbgamez_verify_id($vbulletin->GPC['id'], true);

        if($lookup['userid'] != $vbulletin->userinfo['userid'])
        { 
           print_no_permission();
        }

        if(!$vbulletin->options['vbgamez_myservers_enable'])
        {
           standard_error (fetch_error ('vbgamez_myservers_disabled'));
        }


        $server = vB_vBGamez::vBG_Datastore_Cache($lookup['ip'], $lookup['q_port'], $lookup['c_port'], $lookup['s_port'], $lookup['type'], "sep", $lookup);

        $misc   = vB_vBGamez::vbgamez_server_misc($server);
        $server = vB_vBGamez::vbgamez_server_html($server);
        $connectlink = vbgamez_software_link($lookup['type'], $lookup['ip'], $lookup['c_port'], $lookup['q_port'], $lookup['s_port']);

        $server['i']['views'] = intval($lookup['views']);
        $server['i']['comments'] = intval($lookup['comments']);
        $server['i']['rating'] = intval($lookup['rating']);
        $server['i']['commentsenable'] = intval($lookup['commentsenable']);
        $server['i']['userid'] = intval($lookup['userid']);
        $server['i']['id'] = intval($lookup['id']);

        $game_types = vbgamez_type_list();

         switch($lookup['steam'])
          {
                  case '1':  $steam = '<img src="images/misc/tick.png" alt="" />'; break;
                  case '0':  $steam = '<img src="images/misc/cross.png" alt="" />'; break;
          }

         switch($lookup['nonsteam'])
          {
                  case '1':  $nonsteam = '<img src="images/misc/tick.png" alt="" />'; break;
                  case '0':  $nonsteam = '<img src="images/misc/cross.png" alt="" />'; break;
          }


        $valid = vB_vBGamez::fetch_server_status($lookup['valid']);
		
        $info = array($vbphrase['vbgamez_search_nameserver'] => vB_vBGamez::vbgamez_string_html($lookup['cache_name']),
                      $vbphrase['vbgamez_address'] => $lookup['ip'].":".$lookup['q_port'],
                      $vbphrase['vbgamez_type'] => $game_types[$lookup['type']],
                      $vbphrase['vbgamez_country'] => $lookup['country'],
		      $vbphrase['vbgamez_city'] => $lookup['city'],
                      "Steam" => $steam,
                      "Non-Steam" => $nonsteam,
                      $vbphrase['vbgamez_statusserver'] => $valid,
                      $vbphrase['vbgamez_map_on_server'] => $lookup['cache_map'],
                      $vbphrase['vbgamez_playersonserver'] => $lookup['cache_players']."/".$lookup['cache_playersmax'],
                      $vbphrase['vbgamez_ratingserver'] => $lookup['rating'],
                      $vbphrase['vbgamez_views'] => $lookup['views'],
                      $vbphrase['vbgamez_comments'] => $lookup['comments'],
                      $vbphrase['vbgamez_server_pirated'] => iif($lookup['pirated'], $vbphrase['yes']));

		if($server['i']['expirydate'])
		{
 				 $info[$vbphrase['vbgamez_sticked_payed']] = vbdate($vbulletin->options['dateformat'].' '.$vbulletin->options['timeformat'], $server['i']['expirydate']);
		}
				
        foreach($info AS $field => $value)
        {
            if(!empty($value))
             {
               if(VBG_IS_VB4)
               {
                            $templater = vB_Template::create('vbgamez_infobits');
                            $templater->register('key', $field);
                            $templater->register('val', $value);
                            $infobits .= $templater->render();
               }else{
                            eval('$infobits .= "' . fetch_template('vbgamez_infobits') . '";');
               }
             }

        }

  $additional_fields = $vBG_FieldsController->getDisplayViewInfo($lookup);

  if(VBG_IS_VB4)
  {
        $templater = vB_Template::create('vbgamez_viewinfo');
		$templater->register_page_templates();
        $templater->register('server', $server);
        $templater->register('infobits', $infobits);
        $templater->register('additional_fields', $additional_fields);
        print_output($templater->render());
  }else{
	eval('print_output("' . fetch_template('vbgamez_viewinfo') . '");');
  }
}

// ############################# EDIT FRAME  #####################

if($_REQUEST['do'] == 'editblock')
{
         if(!$vbulletin->options['vbgamez_create_frames'])
         {
                         print_no_permission();
         }

         $sid = intval($_REQUEST['sid']);
         $id = intval($_REQUEST['id']);

         $lookup = vB_vBGamez::vbgamez_verify_id($id);

         if(empty($lookup))
         {
                  eval(standard_error(fetch_error('invalidid', $vbphrase['vbgamez_server'])));
         }

        $navbits['' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . ''] = $vbphrase['vbgamez'];
        $navbits[] = $vbphrase['vbgamez_configure_block'];
        $navbits = construct_navbits($navbits);

        require_once(DIR .'/packages/vbgamez/frame.php');

        $frame_data = new vBGamez_FrameView();
        $jsdata = $frame_data->createJsVars($sid);
        $jsdata_request = $frame_data->createJsRequestVars($sid);

        require_once(DIR .'/packages/vbgamez/manager/frame.php');
        require_once(DIR .'/packages/vbgamez/frame.php');

        $frame_dm = new vBGamEz_Frame_Manager($vbulletin);

        $frame_data = new vBGamez_FrameView();

        $frameinfo = $frame_dm->verify_frame($sid);

        if(!$frame_data->is_configure($frameinfo['frameid'], $frameinfo['is_configure']))
        {
                   print_no_permission();
        }

        $filedbits = $frame_data->createPickerData($sid);

        if(VBG_IS_VB4)
        {
                 $navbar = render_navbar_template($navbits);

	         $templater = vB_Template::create('vbgamez_configure_frame');
		         $templater->register_page_templates();
		         $templater->register('navbar', $navbar);
		         $templater->register('sid', $sid);
		         $templater->register('id', $id);
                         $templater->register('jsdata', $jsdata);
                         $templater->register('jsdata_request', $jsdata_request);
                         $templater->register('filedbits', $filedbits);
	         print_output($templater->render());
        }else{
	   eval('$vbgcss = "' . fetch_template('modifyusercss_headinclude') . '";');

	   eval('$navbar = "' . fetch_template('navbar') . '";');
	   eval('print_output("' . fetch_template('vbgamez_configure_frame') . '");'); 
        }
}

// ############################# PREVIEW BLOCK  #####################

if($_REQUEST['do'] == 'previewblock')
{
         if(!$vbulletin->options['vbgamez_create_frames'])
         {
                         print_no_permission();
         }

         $server = intval($_REQUEST['server']);

         $lookup = vB_vBGamez::vbgamez_verify_id($server);

         if(empty($lookup))
         {
                  eval(standard_error(fetch_error('invalidid', $vbphrase['vbgamez_server'])));
         }

         $frameid = intval($_REQUEST['sid']);
        
         require_once(DIR .'/packages/vbgamez/manager/frame.php');
         require_once(DIR .'/packages/vbgamez/frame.php');

         $frame_dm = new vBGamEz_Frame_Manager($vbulletin);

         $frame_data = new vBGamez_FrameView();

         $frameinfo = $frame_dm->verify_frame($frameid);

         if(!$frame_data->is_configure($frameinfo['frameid'], $frameinfo['is_configure']))
         {
                   print_no_permission();
         }

         $width = intval($frameinfo['width'] + $frame_data->getFrameWidth($frameinfo['frameid']));

         $height = intval($frameinfo['height'] + $frame_data->getFrameHeight($frameinfo['frameid']));

		 if($statisheight = $frame_data->setFixedWidthAndHeight('addheight', $frameid))
	 	 {
			 if($statisheight['action'] == 'add')
			 {
			 	$height += $statisheight['value'];
			 }else{
				$height = $statisheight['value'];
			}
		}
		 if($statiswidth = $frame_data->setFixedWidthAndHeight('addwidth', $frameid))
	 	 {
			 if($statiswidth['action'] == 'add')
			 {
			 	$width += $statiswidth['value'];
			 }else{
				$width = $statiswidth['value'];
			}
		 }
         $request_query = $frame_data->createRequestQuery($frameid);

         if(VBG_IS_VB4)
         {
               $templater = vB_Template::create('vbgamez_configure_frame_preview');
               $templater->register_page_templates();
               $templater->register('request_query', $request_query);
               $templater->register('server', $server);
               $templater->register('height', $height);
               $templater->register('width', $width);
               $templater->register('sid', $frameid);
               print_output($templater->render());
         }else{

	       eval('print_output("' . fetch_template('vbgamez_configure_frame_preview') . '");');
         }       
       
}


// ############################## PAID 

if($_REQUEST['do'] == 'pay' OR $_REQUEST['do'] == 'dopay')
{
	$subobj = vBGamez_Paid::instance($vbulletin);

	$id = intval($_REQUEST['id']);
	
	if(!vBGamez_Paid::paidIsEnabled($vbulletin))
	{
		print_no_permission();
	}
	
	if(!$vbulletin->options['vbgamez_myservers_enable'])
	{
		standard_error (fetch_error ('vbgamez_myservers_disabled'));
	}

	if(!$subobj->canPay())
	{
		print_no_permission();
	}
	
	if(!$subobj->isServerOwner($id))
	{
		print_no_permission();
	}
	
	if($subobj->isAlreadySticked())
	{
		standard_error($vbphrase['vbgamez_server_already_sticked']);
	}
	
	
	$navbits['' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . ''] = $vbphrase['vbgamez'];
	$navbits[] = $vbphrase['vbgamez_pay'];
	
	$navbits = construct_navbits($navbits);
	$navbar = render_navbar_template($navbits);
	$ipaddress = $subobj->getServerConnection();
	if($_REQUEST['do'] == 'pay')
	{
		if($subobj->canStickyServer())
		{
			vbsetcookie('vbgamez_pay_id', $id);
			$tpl = vB_Template::create('vbgamez_pay');
			$tpl->register_page_templates();
			$tpl->register('navbar', $navbar);
			$tpl->register('server_name', $subobj->getServerName());
			$tpl->register('ipaddress', $ipaddress);
			$tpl->register('expiry', $subobj->getExpiryDate());
			$tpl->register('id', $id);
			print_output($tpl->render());
		}else{
			$tpl = vB_Template::create('vbgamez_pay_prepare');
			$tpl->register_page_templates();
			$tpl->register('navbar', $navbar);
			$tpl->register('server_name', $subobj->getServerName());
			$tpl->register('pay_options', $subobj->buildPaymentForm());
			$tpl->register('expiry', $subobj->getExpiryDate());
			$tpl->register('ipaddress', $ipaddress);
			$tpl->register('server_name', $subobj->getServerName());
			$tpl->register('id', $id);
			print_output($tpl->render());
		}
	}else if($subobj->canStickyServer() AND $_REQUEST['do'] == 'dopay'){
		vbsetcookie('vbgamez_pay_id', '');
		$subobj->stickyServer($id);
		$subobj->deleteSubscription();
		vB_vBGamez::vBG_Datastore_Clear_Cache($id, 'rating');
		
		$vbulletin->url = $vbulletin->options['vbgamez_path'].'?do=view&id='.$id;
		eval(standard_redirect($vbphrase['vbgamez_server_sticked'], true));
		
	}else{
		print_no_permission();
		
	}
}
