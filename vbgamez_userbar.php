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
define('THIS_SCRIPT', 'vbgamez_userbar');
define('CSRF_PROTECTION', true);
define('VBG_PACKAGE', true);

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array('vbgamez', 'pm', 'posting');

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by specific actions
$globaltemplates = array('vbgamez_createuserbar', 'vbgamez_locationbits', 'vbgamez_createuserbar_bits');

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/packages/vbgamez/bootstrap.php');
require_once(DIR . '/packages/vbgamez/userbar.php');
require_once(DIR . '/packages/vbgamez/manager/userbar.php');

// ############################# vBGamEz START ENGINE #####################
 vB_vBGamez::bootstrap();

 if(!$vbulletin->options['vbgamez_allow_create_userbar'])
 {
                 print_no_permission();
 }

 if(!$vbulletin->userinfo['userid'])
 {
                 print_no_permission();
 }

 if (empty($_REQUEST['do']))  
 { 
     $_REQUEST['do'] = 'list';
 }

 # LIGHTBOX
 if (!empty($_POST['ajax']) AND isset($_POST['uniqueid']))
 {
	$_REQUEST['do'] = 'lightbox';
 }

 $userbar_manager = new vBGamEz_Userbar_Manager($vbulletin, $vbphrase);

 $userbar_manager_instance = new vBGamez_Userbar();

// ############################# USERBARS  #####################
if($_REQUEST['do'] == 'list')
{
  	$navbits['' . $vbulletin->options['vbgamez_userbar_path'] . '' . $vbulletin->session->vars['sessionurl'] . ''] = $vbphrase['vbgamez_userbarmgr'];
        $pagetitle = $vbphrase['vbgamez_userbarmgr'];

  	$step = 5;

        $userbars_render = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez_userbar WHERE userid = '" . intval($vbulletin->userinfo['userid']) . "'");

        while($userbar = $db->fetch_array($userbars_render))
        {
		     $userbar['name'] = htmlspecialchars_uni($userbar['name']);

		     if (!$userbar['enabled'])
		     {
			     	$userbar['name'] = "<strike>$userbar[name]</strike>";
		     }

		     $userbar['background'] = vB_vBGamez_Userbar_dm::vbg_fetch_background_name($userbar['background']);
                     $userbar['font'] = vB_vBGamez_Userbar_dm::vbg_fetch_font_name($userbar['font']);
                     $userbar['fontcolor'] = iif($userbar['textcolor'], htmlspecialchars($userbar['textcolor']), '---');
                     $userbar['fontsize'] = iif($userbar['fontsize'], $userbar['fontsize'], '---');

                  if(VBG_IS_VB4)
                  {
                                 $tpl = vB_Template::create('vbgamez_createuserbar_bits');
                                 $tpl->register('userbar', $userbar);
                                 $userbars .= $tpl->render();
                  }else{
                                 eval('$userbars .= "' . fetch_template('vbgamez_createuserbar_bits') . '";');
                  }
       }

  if(VBG_IS_VB4)
  {
        $templater = vB_Template::create('vbgamez_createuserbar');

        $navbits = construct_navbits($navbits);
        $navbar = render_navbar_template($navbits);
        $templater->register_page_templates();
        $templater->register('step', $step);
        $templater->register('navbar', $navbar);
        $templater->register('pagetitle', $pagetitle);
        $templater->register('userbars', $userbars);
        print_output($templater->render());
  }else{
	$navbits = construct_navbits($navbits);
	eval('$navbar = "' . fetch_template('navbar') . '";');
	eval('print_output("' . fetch_template('vbgamez_createuserbar') . '");');
  }
}


// ############################# ADD USERBAR  #####################
if($_REQUEST['do'] == 'create')
{
        if(!vB_vBGamez_Userbar_dm::canCreateUserbar())
        {
                        eval(standard_error(fetch_error('vbgamez_you_have_max_userbars', $vbulletin->options['vbgamez_max_count_of_userbars'])));
        }

        $example_userbars = vB_vBGamez_Userbar_dm::fetchExampleUserbars();

  	$navbits['' . $vbulletin->options['vbgamez_userbar_path'] . '' . $vbulletin->session->vars['sessionurl'] . ''] = $vbphrase['vbgamez_userbarmgr'];
  	$navbits[] = $vbphrase['vbgamez_createuserbar'];
        $pagetitle = $vbphrase['vbgamez_createuserbar'];
  	$step = 1;

	$_REQUEST['fromserverid'] = intval($_REQUEST['fromserverid']);
	$serverinfo = vB_vBGamez::vbgamez_verify_id($_REQUEST['fromserverid']);
	if($serverinfo)
	{
		vbsetcookie('vbg_fromserverid', $serverinfo['id']);
	}else{
		vbsetcookie('vbg_fromserverid', '');
	}

  if(VBG_IS_VB4)
  {
        $templater = vB_Template::create('vbgamez_createuserbar');

        $navbits = construct_navbits($navbits);
        $navbar = render_navbar_template($navbits);
        $templater->register_page_templates();
        $templater->register('step', $step);
        $templater->register('navbar', $navbar);
        $templater->register('pagetitle', $pagetitle);
        $templater->register('example_userbars', $example_userbars);
        print_output($templater->render());
  }else{
	$navbits = construct_navbits($navbits);
	eval('$navbar = "' . fetch_template('navbar') . '";');
	eval('print_output("' . fetch_template('vbgamez_createuserbar') . '");');
  }
}

// ############################# DO CREATE USERBAR  #####################

if($_REQUEST['do'] == 'docreate')
{
        if(!vB_vBGamez_Userbar_dm::canCreateUserbar())
        {
                        eval(standard_error(fetch_error('vbgamez_you_have_max_userbars', $vbulletin->options['vbgamez_max_count_of_userbars'])));
        }

  	$navbits['' . $vbulletin->options['vbgamez_userbar_path'] . '' . $vbulletin->session->vars['sessionurl'] . ''] = $vbphrase['vbgamez_userbarmgr'];
  	$navbits[] = $vbphrase['vbgamez_createuserbar'];

	$vbulletin->input->clean_array_gpc('r', array(
		'name' 	=> TYPE_STR,
		'fontcolor' 	=> TYPE_STR,
		'fontsize' 	=> TYPE_STR));

	$vbulletin->input->clean_gpc('f', 'background', TYPE_FILE);
	$vbulletin->input->clean_gpc('f', 'font', TYPE_FILE);

	$vbulletin->GPC['name'] = trim($vbulletin->GPC['name']);
	$vbulletin->GPC['fontcolor'] = trim($vbulletin->GPC['fontcolor']);
	$vbulletin->GPC['fontsize'] = intval($vbulletin->GPC['fontsize']);

        $errors = '';

	if (empty($vbulletin->GPC['name']))
        {
           $errors = $vbphrase['vbgamez_error_enter_name_userbar'];
        }

	if (!$errors AND empty($vbulletin->GPC['background']['name']))
        {
           $errors = $vbphrase['vbgamez_error_enter_background'];
        }

	if (!$errors AND empty($vbulletin->GPC['fontcolor']))
        {
           $errors = $vbphrase['vbgamez_error_enter_fontcolor'];
        }

	if (!$errors AND empty($vbulletin->GPC['fontsize']))
        {
           $errors = $vbphrase['vbgamez_error_enter_fontsize'];
        }

	if (!$errors AND !vB_vBGamez_Userbar_dm::fetchColorType($vbulletin->GPC['fontcolor']))
        {
           $errors = $vbphrase['vbgamez_userbar_invalid_fonttype'];
        }

        // ############################# UPLOAD BACKGROUND  #####################

        vB_Upload_Userbar_Background::upload('background');

        if(!$errors AND vB_Upload_Userbar_Background::$errors)
        {
                eval(standard_error(vB_Upload_Userbar_Background::$errors));
        }

        $background_name = vB_Upload_Userbar_Background::fetch_filename();


        // ############################# UPLOAD FONT  #####################

        if($vbulletin->GPC['font'])
        {
               vB_Upload_Userbar_Font::upload('font');

               if(!$errors AND vB_Upload_Userbar_Font::$errors)
               {
                       eval(standard_error(vB_Upload_Userbar_Font::$errors));
               }

               $font_name = vB_Upload_Userbar_Font::fetch_filename();
        }

        // ############################# DB QUERY  #####################

        if(!empty($errors))
        {
          eval (standard_error(fetch_error('vbgamez_errors_userbar', "<br />".$errors)));

        }else{
              $db->query("INSERT INTO " . TABLE_PREFIX  . "vbgamez_userbar
                          (name, enabled, background, textcolor, font, fontsize, fieldname, userid)
                          VALUES
                          (".$db->sql_prepare($vbulletin->GPC['name']).",
                           '1',
                           ".$db->sql_prepare($background_name).",
                           ".$db->sql_prepare($vbulletin->GPC['fontcolor']).",
                           ".$db->sql_prepare($font_name).",
                           ".$db->sql_prepare($vbulletin->GPC['fontsize']).",
                           'global',
                           ".$vbulletin->userinfo['userid'].")");

        $vbulletin->url = '' . $vbulletin->options['vbgamez_userbar_path'] . '?do=edit&id='.$db->insert_id().'&'.$vbulletin->session->vars['sessionurl'];

	eval(print_standard_redirect('vbgamez_userbar_added'));

       }
}

// ############################# EDIT USERBAR  #####################
if($_REQUEST['do'] == 'edit')
{
          
         $_REQUEST['id'] = intval($_REQUEST['id']);

		 $_REQUEST['fromserverid'] = intval($_REQUEST['fromserverid']);
	 	 $serverinfo = vB_vBGamez::vbgamez_verify_id($_REQUEST['fromserverid']);
		 if($serverinfo)
		 {
			vbsetcookie('vbg_fromserverid', $serverinfo['id']);
			$_COOKIE[COOKIE_PREFIX.'vbg_fromserverid'] = $serverinfo['id'];
		 }else{
			vbsetcookie('vbg_fromserverid', '');
		 }
		
         $userbarinfo = vB_vBGamez_Userbar_dm::fetch_userbarinfo($_REQUEST['id']);
         $userbarinfo['name'] = htmlspecialchars($userbarinfo['name']);
         $userbarinfo['font'] = vB_vBGamez_Userbar_dm::vbg_fetch_font_name($userbarinfo['font']);
         $userbarinfo['textcolor'] = htmlspecialchars_uni($userbarinfo['textcolor']);

         if(empty($_REQUEST['id']) OR empty($userbarinfo) OR !vB_vBGamez_Userbar_dm::verify_permissions($userbarinfo))
         {
                     print_no_permission();
         }

         if(!vB_vBGamez_Userbar_dm::verify_permissions($userbarinfo))
         {
                 print_no_permission();
         }

         $fetch_locations = $db->query("SELECT * FROM " . TABLE_PREFIX . "vbgamez_userbar_config WHERE userbarid = '" . $userbarinfo['userbarid'] . "'");

         while($location = $db->fetch_array($fetch_locations))
         {
		                 $location['text'] = htmlspecialchars_uni($location['text']);

		                 if (!$location['enabled'])
		                 {
			                $location['text'] = "<strike>$location[text]</strike>";
		                 }
 
                                 $fontlink = '<a href="' . $vbulletin->options['vbgamez_userbar_path'] . '?do=downloadfont&configid=' . $location['configid'] . '">' . vB_vBGamez_Userbar_dm::vbg_fetch_font_name($location['font']) . '</a>';

                                 $location['radius'] = iif($location['radius'], $location['radius'], '---');
                                 $location['font'] = iif($location['font'], $fontlink, '--');
                                 $location['fontsize'] = iif($location['fontsize'], $location['fontsize'], '--');
                                 $location['fontcolor'] = iif($location['fontcolor'], htmlspecialchars_uni($location['fontcolor']), '--');

                  if(VBG_IS_VB4)
                  {
                                 $tpl = vB_Template::create('vbgamez_locationbits');
                                 $tpl->register('location', $location);
                                 $locations .= $tpl->render();
                  }else{
                                 eval('$locations .= "' . fetch_template('vbgamez_locationbits') . '";');
                  }
         }

         $pagetitle = $vbphrase['vbgamez_edituserbar'];

         $navbits['' . $vbulletin->options['vbgamez_userbar_path'] . '' . $vbulletin->session->vars['sessionurl'] . ''] = $vbphrase['vbgamez_userbarmgr'];
         $navbits[] = $vbphrase['vbgamez_edituserbar'];

         $step = 2;
         // DISABLE IMAGE CACHE
         $rand = rand();

	 if($_COOKIE[COOKIE_PREFIX.'vbg_fromserverid'])
	 {
		$fromserverid = intval($_COOKIE[COOKIE_PREFIX.'vbg_fromserverid']);
		$verifyserver = vB_vBGamez::vbgamez_verify_id($fromserverid);

		if($verifyserver)
		{
			$show['vbg_return'] = true;
			$fromservername = vB_vBGamez::vbgamez_string_html($verifyserver['cache_name']);
			$fromserverid = $verifyserver['id'];
		}else{
			vbsetcookie('vbg_fromserverid', '');
		}
	}

  if(VBG_IS_VB4)
  {
        $templater = vB_Template::create('vbgamez_createuserbar');

        $navbits = construct_navbits($navbits);
        $navbar = render_navbar_template($navbits);
        $templater->register_page_templates();
        $templater->register('step', $step);
        $templater->register('navbar', $navbar);
        $templater->register('locations', $locations);
        $templater->register('userbar', $userbarinfo);
        $templater->register('pagetitle', $pagetitle);
        $templater->register('fromservername', $fromservername);
        $templater->register('fromserverid', $fromserverid);
        $templater->register('rand', $rand);
        print_output($templater->render());
  }else{
        $userbar =& $userbarinfo;

	$navbits = construct_navbits($navbits);
	eval('$navbar = "' . fetch_template('navbar') . '";');
	eval('print_output("' . fetch_template('vbgamez_createuserbar') . '");');
  }
 }

// ############################# DO EDIT USERBAR  #####################
if($_REQUEST['do'] == 'doedit')
{
          
         $_REQUEST['id'] = intval($_REQUEST['id']);

         $userbarinfo = vB_vBGamez_Userbar_dm::fetch_userbarinfo($_REQUEST['id']);

         if(empty($_REQUEST['id']) OR empty($userbarinfo) OR !vB_vBGamez_Userbar_dm::verify_permissions($userbarinfo))
         {
                     print_no_permission();
         }

	$vbulletin->input->clean_array_gpc('r', array(
		'name' 	=> TYPE_STR,
		'fontcolor' 	=> TYPE_STR,
		'fontsize' 	=> TYPE_STR));

	$vbulletin->input->clean_gpc('f', 'background', TYPE_FILE);
	$vbulletin->input->clean_gpc('f', 'font', TYPE_FILE);

	$vbulletin->GPC['name'] = trim($vbulletin->GPC['name']);
	$vbulletin->GPC['fontcolor'] = trim($vbulletin->GPC['fontcolor']);
	$vbulletin->GPC['fontsize'] = intval($vbulletin->GPC['fontsize']);
        
        $errors = '';

	if (empty($vbulletin->GPC['name']))
        {
           $errors = $vbphrase['vbgamez_error_enter_name_userbar'];
        }

	if (!$errors AND empty($vbulletin->GPC['fontcolor']))
        {
           $errors = $vbphrase['vbgamez_error_enter_fontcolor'];
        }

	if (!$errors AND empty($vbulletin->GPC['fontsize']))
        {
           $errors = $vbphrase['vbgamez_error_enter_fontsize'];
        }
        
	if (!$errors AND !vB_vBGamez_Userbar_dm::fetchColorType($vbulletin->GPC['fontcolor']))
        {
           $errors = $vbphrase['vbgamez_userbar_invalid_fonttype'];
        }

        // ############################# UPLOAD BACKGROUND  #####################

        if($vbulletin->GPC['background']['tmp_name'])
        {
                vB_Upload_Userbar_Background::upload('background');

                if(!$errors AND vB_Upload_Userbar_Background::$errors)
                {
                        eval(standard_error(vB_Upload_Userbar_Background::$errors));
                }

                $background_name = vB_Upload_Userbar_Background::fetch_filename();
                @unlink($userbarinfo['background']);

        }

        // ############################# UPLOAD FONT  #####################

        if($vbulletin->GPC['font']['tmp_name'])
        {
               vB_Upload_Userbar_Font::upload('font');

               if(!$errors AND vB_Upload_Userbar_Font::$errors)
               {
                       eval(standard_error(vB_Upload_Userbar_Font::$errors));
               }

               $font_name = vB_Upload_Userbar_Font::fetch_filename();
               @unlink($userbarinfo['font']);
        }

        // ############################# DB QUERY  #####################

        if(empty($background_name))
        {
                  $background_name = $userbarinfo['background'];
        }

        if(empty($font_name))
        {
                  $font_name = $userbarinfo['font'];
        }

        if(!empty($errors))
        {
          eval (standard_error(fetch_error('vbgamez_errors_userbar', "<br />".$errors)));

        }else{
              $db->query("UPDATE " . TABLE_PREFIX  . "vbgamez_userbar SET 
                          name = ".$db->sql_prepare($vbulletin->GPC['name']).", background = ".$db->sql_prepare($background_name).", textcolor = ".$db->sql_prepare($vbulletin->GPC['fontcolor']).", font = ".$db->sql_prepare($font_name).", fontsize = ".$db->sql_prepare($vbulletin->GPC['fontsize'])." WHERE userid = ".$vbulletin->userinfo['userid']." AND userbarid = " . $userbarinfo['userbarid'] . "");

        $vbulletin->url = '' . $vbulletin->options['vbgamez_userbar_path'] . '?do=edit&id='.$userbarinfo['userbarid'].'&'.$vbulletin->session->vars['sessionurl'];

	eval(print_standard_redirect('vbgamez_userbar_edit'));

       }

 }
// ############################# VIEW BACKGROUND  #####################
if($_REQUEST['do'] == 'viewbackground')
{
         $_REQUEST['id'] = intval($_REQUEST['id']);

         $userbarinfo = vB_vBGamez_Userbar_dm::fetch_userbarinfo($_REQUEST['id']);

         if(!vB_vBGamez_Userbar_dm::verify_permissions($userbarinfo))
         {
                 print_no_permission();
         }

         if(file_exists($userbarinfo['background']))
         { 
             $im = vB_vBGamez::vbgamez_fetch_userbar_image($userbarinfo['background']);
             vB_vBGamez::vbgamez_print_userbar_image($im, $userbarinfo['background']);

         }else{
              eval(standard_error(fetch_error('vbgamez_userbar_background_not_found')));
         }
 }

// ############################# DOWNLOAD FONT  #####################
if($_REQUEST['do'] == 'downloadfont')
{
         $_REQUEST['id'] = intval($_REQUEST['id']);
         $_REQUEST['configid'] = intval($_REQUEST['configid']);

         if($_REQUEST['id'])
         {

                   $userbarinfo = vB_vBGamez_Userbar_dm::fetch_userbarinfo($_REQUEST['id']);

                   if(!vB_vBGamez_Userbar_dm::verify_permissions($userbarinfo))
                   {
                           print_no_permission();
                   }

                   if(file_exists($userbarinfo['font']))
                   { 
                       require_once('./includes/functions_file.php');
                       file_download($userbarinfo['font'], vB_vBGamez_Userbar_dm::vbg_fetch_font_name($userbarinfo['font']));
                   }else{
                       exit("<h1>Not Found</h1>");
                   }
         }

         if($_REQUEST['configid'])
         {

                   $configinfo = vB_vBGamez_Userbar_dm::fetch_configinfo($_REQUEST['configid']);
                   $userbarinfo = vB_vBGamez_Userbar_dm::fetch_userbarinfo($configinfo['userbarid']);

                   if(!vB_vBGamez_Userbar_dm::verify_permissions($userbarinfo))
                   {
                           print_no_permission();
                   }

                   if(file_exists($configinfo['font']))
                   { 
                       require_once('./includes/functions_file.php');
                       file_download($configinfo['font'], vB_vBGamez_Userbar_dm::vbg_fetch_font_name($configinfo['font']));
                   }else{
                       exit("<h1>Not Found</h1>");
                   }
         }
}

// ############################# DELETE FONT  #####################
if($_REQUEST['do'] == 'deletefont')
{
         $_REQUEST['id'] = intval($_REQUEST['id']);
         $_REQUEST['configid'] = intval($_REQUEST['configid']);

         if($_REQUEST['id'])
         {

                   $userbarinfo = vB_vBGamez_Userbar_dm::fetch_userbarinfo($_REQUEST['id']);

                   if(!vB_vBGamez_Userbar_dm::verify_permissions($userbarinfo))
                   {
                           print_no_permission();
                   }

                   @unlink($userbarinfo['font']);
                   $db->query("UPDATE " . TABLE_PREFIX . "vbgamez_userbar SET font = '' WHERE userbarid = '" . intval($userbarinfo['userbarid']) . "'");

                   $vbulletin->url = '' . $vbulletin->options['vbgamez_userbar_path'] . '?do=edit&id='.$userbarinfo['userbarid'].'&'.$vbulletin->session->vars['sessionurl'];

	           eval(print_standard_redirect('vbgamez_userbar_edit'));
         }

         if($_REQUEST['configid'])
         {

                   $configinfo = vB_vBGamez_Userbar_dm::fetch_configinfo($_REQUEST['configid']);
                   $userbarinfo = vB_vBGamez_Userbar_dm::fetch_userbarinfo($configinfo['userbarid']);

                   if(!vB_vBGamez_Userbar_dm::verify_permissions($userbarinfo))
                   {
                           print_no_permission();
                   }

                   @unlink($configinfo['font']);

                   $db->query("UPDATE " . TABLE_PREFIX . "vbgamez_userbar_config SET font = '' WHERE configid = '" . intval($configinfo['configid']) . "'");

                   $vbulletin->url = '' . $vbulletin->options['vbgamez_userbar_path'] . '?do=editlocation&id='.$configinfo['configid'].'&'.$vbulletin->session->vars['sessionurl'];

	           eval(print_standard_redirect('vbgamez_userbar_location_edited'));
         }
}
// ############################# LIGHTBOX  #####################
if ($_REQUEST['do'] == 'lightbox')
{
	$vbulletin->input->clean_array_gpc('r', array(
                'id' => TYPE_UINT,
                'uniqueid' => TYPE_INT
		));

		require_once(DIR . '/includes/class_xml.php');
		$xml = new vB_AJAX_XML_Builder($vbulletin, 'text/xml');

		$imagelink = '' . $vbulletin->options['vbgamez_userbar_path'] . '?do=viewbackground&id='.$vbulletin->GPC['id'];

                $attachmentinfo['time_string'] = '';
                $attachmentinfo['filename'] = '';
                $uniqueid = $vbulletin->GPC['uniqueid'];

                if(VBG_IS_VB4)
                {
		        $templater = vB_Template::create('lightbox');
			$templater->register('attachmentinfo', $attachmentinfo);
			$templater->register('imagelink', $imagelink);
			$templater->register('uniqueid', $uniqueid);
		        $html = $templater->render(true);

                        $html = str_replace(vB_Template_Runtime::fetchStyleVar('imgdir_misc').'/lightbox_progress.gif', $vbulletin->options['bburl'].'/'.vB_Template_Runtime::fetchStyleVar('imgdir_misc').'/lightbox_progress.gif', $html);

                }else{
                        eval('$html = "' . fetch_template('lightbox', 0, 0) . '";');

                        $html = str_replace($stylevar['imgdir_misc'].'/lightbox_progress.gif', $vbulletin->options['bburl'].'/'.$stylevar['imgdir_misc'].'/lightbox_progress.gif', $html);

                }

		$xml->add_group('img');
		$xml->add_tag('html', process_replacement_vars($html));
		$xml->add_tag('link', $imagelink);
		$xml->add_tag('name', 'Background');
		$xml->add_tag('date', '00.00.00');
		$xml->add_tag('time', '00:00');
		$xml->close_group();

	$xml->print_xml();
}

// ############################# ADD LOCATION   #####################
if($_REQUEST['do'] == 'addlocation')
{
        $userbarinfo = vB_vBGamez_Userbar_dm::fetch_userbarinfo($_REQUEST['id']);

        if(empty($_REQUEST['id']) OR empty($userbarinfo) OR !vB_vBGamez_Userbar_dm::verify_permissions($userbarinfo))
        {
                 print_no_permission();
        }

        $pagetitle = $vbphrase['vbgamez_userbarmgr_add_location'];
  	$navbits['' . $vbulletin->options['vbgamez_userbar_path'] . '' . $vbulletin->session->vars['sessionurl'] . ''] = $vbphrase['vbgamez_userbarmgr'];
  	$navbits['' . $vbulletin->options['vbgamez_userbar_path'] . '' . $vbulletin->session->vars['sessionurl'] . '?do=edit&id=' . $userbarinfo['userbarid'] . ''] = htmlspecialchars($userbarinfo['name']);
  	$navbits[] = $vbphrase['vbgamez_userbarmgr_add_location'];

  	$step = 3;

        if(VBG_IS_VB4)
        {
              $templater = vB_Template::create('vbgamez_createuserbar');

              $navbits = construct_navbits($navbits);
              $navbar = render_navbar_template($navbits);
              $templater->register_page_templates();
              $templater->register('step', $step);
              $templater->register('navbar', $navbar);
              $templater->register('pagetitle', $pagetitle);
              $templater->register('userbar', $userbarinfo);
              print_output($templater->render());
        }else{
	      $navbits = construct_navbits($navbits);
	      eval('$navbar = "' . fetch_template('navbar') . '";');
	      eval('print_output("' . fetch_template('vbgamez_createuserbar') . '");');
        }
}


// ############################# DO ADD LOCATION   #####################
if($_REQUEST['do'] == 'doaddlocation')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'id' 	=> TYPE_INT,
		'text' 	=> TYPE_STR,
		'repeat_x' 	=> TYPE_INT,
		'repeat_y' 	=> TYPE_INT,
		'radius' 	=> TYPE_INT,
		'fontsize' 	=> TYPE_INT,
		'fontcolor' 	=> TYPE_STR,
		'imagesize' 	=> TYPE_INT));

	$vbulletin->input->clean_gpc('f', 'font', TYPE_FILE);

        $userbarinfo = vB_vBGamez_Userbar_dm::fetch_userbarinfo($vbulletin->GPC['id']);

        if(empty($vbulletin->GPC['id']) OR empty($userbarinfo) OR !vB_vBGamez_Userbar_dm::verify_permissions($userbarinfo))
        {
                 print_no_permission();
        }

        $vbulletin->GPC['text'] = trim($vbulletin->GPC['text']);

        if(empty($vbulletin->GPC['text']) OR empty($vbulletin->GPC['repeat_x']) OR empty($vbulletin->GPC['repeat_y']))
        {
                      eval(standard_error($vbphrase['vbgamez_userbarmgr_emptyfieldslocation']));
        }

	if (!empty($vbulletin->GPC['fontcolor']) AND !vB_vBGamez_Userbar_dm::fetchColorType($vbulletin->GPC['fontcolor']))
        {
           eval(standard_error($vbphrase['vbgamez_userbar_invalid_fonttype']));
        }
        // ############################# UPLOAD FONT  #####################

        if($vbulletin->GPC['font']['tmp_name'])
        {
               vB_Upload_Userbar_Font::upload('font');

               if(!$errors AND vB_Upload_Userbar_Font::$errors)
               {
                       eval(standard_error(vB_Upload_Userbar_Font::$errors));
               }

               $font = vB_Upload_Userbar_Font::fetch_filename();
        }

        $userbar_manager->do_add_userbar_location($userbarinfo['userbarid'], $vbulletin->GPC['text'], $vbulletin->GPC['radius'], $vbulletin->GPC['repeat_x'], $vbulletin->GPC['repeat_y'], $font, $vbulletin->GPC['fontsize'], $vbulletin->GPC['fontcolor'], $vbulletin->GPC['imagesize'], 1);

        $vbulletin->url = '' . $vbulletin->options['vbgamez_userbar_path'] . '?do=edit&id='.$userbarinfo['userbarid'].'&'.$vbulletin->session->vars['sessionurl'];

	eval(print_standard_redirect('vbgamez_userbar_location_added'));
        
}


// ############################# DELETE LOCATION   #####################
if($_REQUEST['do'] == 'deletelocation')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'id' 	=> TYPE_INT));

        $userbarid = $userbar_manager->get_userbarid_by_configid($vbulletin->GPC['id']);

        $userbarinfo = vB_vBGamez_Userbar_dm::fetch_userbarinfo($userbarid);

        if(empty($vbulletin->GPC['id']) OR empty($userbarinfo) OR !vB_vBGamez_Userbar_dm::verify_permissions($userbarinfo) OR !$userbar_manager->verify_config($vbulletin->GPC['id']))
        {
                 print_no_permission();
        }

        $userbar_manager->delete_userbar_location($vbulletin->GPC['id']);

        $vbulletin->url = '' . $vbulletin->options['vbgamez_userbar_path'] . '?do=edit&id='.$userbarinfo['userbarid'].'&'.$vbulletin->session->vars['sessionurl'];

	eval(print_standard_redirect('vbgamez_userbar_location_deleted'));
}

// ############################# DISABLE LOCATION   #####################
if($_REQUEST['do'] == 'disablelocation')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'id' 	=> TYPE_INT));

        $userbarid = $userbar_manager->get_userbarid_by_configid($vbulletin->GPC['id']);

        $userbarinfo = vB_vBGamez_Userbar_dm::fetch_userbarinfo($userbarid);

        if(empty($vbulletin->GPC['id']) OR empty($userbarinfo) OR !vB_vBGamez_Userbar_dm::verify_permissions($userbarinfo) OR !$userbar_manager->verify_config($vbulletin->GPC['id']))
        {
                 print_no_permission();
        }

        $userbar_manager->disable_location($vbulletin->GPC['id']);

        $vbulletin->url = '' . $vbulletin->options['vbgamez_userbar_path'] . '?do=edit&id='.$userbarinfo['userbarid'].'&'.$vbulletin->session->vars['sessionurl'];

	eval(print_standard_redirect('vbgamez_userbar_location_disabled'));
}

// ############################# ENABLE LOCATION   #####################
if($_REQUEST['do'] == 'enablelocation')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'id' 	=> TYPE_INT));

        $userbarid = $userbar_manager->get_userbarid_by_configid($vbulletin->GPC['id']);

        $userbarinfo = vB_vBGamez_Userbar_dm::fetch_userbarinfo($userbarid);

        if(empty($vbulletin->GPC['id']) OR empty($userbarinfo) OR !vB_vBGamez_Userbar_dm::verify_permissions($userbarinfo) OR !$userbar_manager->verify_config($vbulletin->GPC['id']))
        {
                 print_no_permission();
        }

        $userbar_manager->enable_location($vbulletin->GPC['id']);

        $vbulletin->url = '' . $vbulletin->options['vbgamez_userbar_path'] . '?do=edit&id='.$userbarinfo['userbarid'].'&'.$vbulletin->session->vars['sessionurl'];

	eval(print_standard_redirect('vbgamez_userbar_location_enabled'));
}

// ############################# EDIT LOCATION   #####################
if($_REQUEST['do'] == 'editlocation')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'id' 	=> TYPE_INT));

        $userbarid = $userbar_manager->get_userbarid_by_configid($vbulletin->GPC['id']);

        $userbarinfo = vB_vBGamez_Userbar_dm::fetch_userbarinfo($userbarid);

        if(empty($vbulletin->GPC['id']) OR empty($userbarinfo) OR !vB_vBGamez_Userbar_dm::verify_permissions($userbarinfo) OR !$userbar_manager->verify_config($vbulletin->GPC['id']))
        {
                 print_no_permission();
        }

        $select_configinfo = $db->query("SELECT * FROM " . TABLE_PREFIX . "vbgamez_userbar_config WHERE configid = '" . intval($vbulletin->GPC['id']) . "'");
        $configinfo = $db->fetch_array($select_configinfo);

        $configinfo['fontcolor'] = htmlspecialchars_uni($configinfo['fontcolor']);
        $configinfo['font'] = vB_vBGamez_Userbar_dm::vbg_fetch_font_name($configinfo['font']);

        $configinfo['text'] = htmlspecialchars_uni($configinfo['text']);

        $configinfo['enabled'] = iif($configinfo['enabled'], 'checked="checked"');

        $pagetitle = $vbphrase['vbgamez_userbarmgr_location_edit'];
  	$navbits['' . $vbulletin->options['vbgamez_userbar_path'] . '' . $vbulletin->session->vars['sessionurl'] . ''] = $vbphrase['vbgamez_userbarmgr'];
  	$navbits['' . $vbulletin->options['vbgamez_userbar_path'] . '' . $vbulletin->session->vars['sessionurl'] . '?do=edit&id=' . $userbarinfo['userbarid'] . ''] = htmlspecialchars($userbarinfo['name']);
  	$navbits[] = $vbphrase['vbgamez_userbarmgr_location_edit'];

  	$step = 4;

        if(VBG_IS_VB4)
        {
              $templater = vB_Template::create('vbgamez_createuserbar');

              $navbits = construct_navbits($navbits);
              $navbar = render_navbar_template($navbits);
              $templater->register_page_templates();
              $templater->register('step', $step);
              $templater->register('navbar', $navbar);
              $templater->register('pagetitle', $pagetitle);
              $templater->register('userbar', $userbarinfo);
              $templater->register('configinfo', $configinfo);
              print_output($templater->render());
        }else{
	      $navbits = construct_navbits($navbits);
	      eval('$navbar = "' . fetch_template('navbar') . '";');
	      eval('print_output("' . fetch_template('vbgamez_createuserbar') . '");');
        }
}

// ############################# DO EDIT LOCATION   #####################
if($_REQUEST['do'] == 'doeditlocation')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'id' 	=> TYPE_INT,
		'text' 	=> TYPE_STR,
		'repeat_x' 	=> TYPE_INT,
		'repeat_y' 	=> TYPE_INT,
		'radius' 	=> TYPE_INT,
		'fontsize' 	=> TYPE_INT,
		'fontcolor' 	=> TYPE_STR,
		'imagesize' 	=> TYPE_INT,
		'enabled' 	=> TYPE_INT));

	$vbulletin->input->clean_gpc('f', 'font', TYPE_FILE);

        $userbarid = $userbar_manager->get_userbarid_by_configid($vbulletin->GPC['id']);

        $userbarinfo = vB_vBGamez_Userbar_dm::fetch_userbarinfo($userbarid);

        if(empty($vbulletin->GPC['id']) OR empty($userbarinfo) OR !vB_vBGamez_Userbar_dm::verify_permissions($userbarinfo) OR !$userbar_manager->verify_config($vbulletin->GPC['id']))
        {
                 print_no_permission();
        }

        $configid = intval($vbulletin->GPC['id']);
        $text = trim($vbulletin->GPC['text']);
        $radius = intval($vbulletin->GPC['radius']);
        $repeat_x = intval($vbulletin->GPC['repeat_x']);
        $repeat_y = intval($vbulletin->GPC['repeat_y']);
        $fontsize = intval($vbulletin->GPC['fontsize']);
        $fontcolor = trim($vbulletin->GPC['fontcolor']);
        $enabled = trim($vbulletin->GPC['enabled']);
        $width = trim($vbulletin->GPC['imagesize']);

        $select_configinfo = $db->query("SELECT * FROM " . TABLE_PREFIX . "vbgamez_userbar_config WHERE configid = '" . intval($vbulletin->GPC['id']) . "'");
        $configinfo = $db->fetch_array($select_configinfo);


        if(empty($text) OR empty($repeat_x) OR empty($repeat_y))
        {
                      eval(standard_error($vbphrase['vbgamez_userbarmgr_emptyfieldslocation']));
        }

	if (!empty($vbulletin->GPC['fontcolor']) AND !vB_vBGamez_Userbar_dm::fetchColorType($vbulletin->GPC['fontcolor']))
        {
           eval(standard_error($vbphrase['vbgamez_userbar_invalid_fonttype']));
        }
        // ############################# UPLOAD FONT  #####################

        if($vbulletin->GPC['font']['tmp_name'])
        {
               vB_Upload_Userbar_Font::upload('font');

               if(!$errors AND vB_Upload_Userbar_Font::$errors)
               {
                       eval(standard_error(vB_Upload_Userbar_Font::$errors));
               }

               $font = vB_Upload_Userbar_Font::fetch_filename();

               @unlink($configinfo['font']);
        }else{
               $font = $configinfo['font'];
        }

        // ############################# !UPLOAD FONT  #####################


        $userbar_manager->do_edit_userbar_location($configid, $text, $radius, $repeat_x, $repeat_y, $font, $fontsize, $fontcolor, $enabled, $width);

        $vbulletin->url = '' . $vbulletin->options['vbgamez_userbar_path'] . '?do=edit&id='.$userbarinfo['userbarid'].'&'.$vbulletin->session->vars['sessionurl'];

	eval(print_standard_redirect('vbgamez_userbar_location_edited'));

}

// ############################# TEST USERBAR  #####################
if($_REQUEST['do'] == 'preview')
{
                 if(empty($_REQUEST['sid'])) { exit; }

	         if($_COOKIE[COOKIE_PREFIX.'vbg_fromserverid'])
	         {
		        $fromserverid = intval($_COOKIE[COOKIE_PREFIX.'vbg_fromserverid']);
		        $verifyserver = vB_vBGamez::vbgamez_verify_id($fromserverid);

		        if($verifyserver)
		        {
                		$userbar = new vBGamez_Userbar();

                		$server = vB_vBGamez::vBG_Datastore_Cache($verifyserver['ip'], $verifyserver['q_port'], $verifyserver['c_port'], $verifyserver['s_port'], $verifyserver['type'], "sep", $verifyserver);

                		$misc   = vB_vBGamez::vbgamez_server_misc($server);
                		$server = vB_vBGamez::vbgamez_server_html($server);

                 		$userbar->serverinfo = $server;
                 		$userbar->additionalinfo = $verifyserver;
                 		$userbar->construct_userbar($_REQUEST['sid']);
		        }else{
				$use_default = true;
		        }
	         }else{
			$use_default = true;
		 }

		 if($use_default)
		 {
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

                 		$userbar->serverinfo = $server;
                 		$userbar->additionalinfo = $server['a'];
                 		$userbar->construct_userbar($_REQUEST['sid']);
		}
}

// ############################# New USERBAR  #####################
if($_REQUEST['do'] == 'newpreview')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'id' 	=> TYPE_INT,
		'configid' => TYPE_INT,
		'text' 	=> TYPE_STR,
		'isadd' 	=> TYPE_INT,
		'repeat_x' 	=> TYPE_INT,
		'repeat_y' 	=> TYPE_INT,
		'radius' 	=> TYPE_INT,
		'fontsize' 	=> TYPE_INT,
		'fontcolor' 	=> TYPE_STR,
		'imagesize' 	=> TYPE_INT,
		'enabled' 	=> TYPE_INT));
	
        	 $userbarid = intval($vbulletin->GPC['id']);
			 $configid = intval($vbulletin->GPC['configid']);
        	 $text = trim($vbulletin->GPC['text']);
        	 $radius = intval($vbulletin->GPC['radius']);
        	 $repeat_x = intval($vbulletin->GPC['repeat_x']);
        	 $repeat_y = intval($vbulletin->GPC['repeat_y']);
        	 $fontsize = intval($vbulletin->GPC['fontsize']);
        	 $fontcolor = trim($vbulletin->GPC['fontcolor']);
        	 $enabled = trim($vbulletin->GPC['enabled']);
        	 $width = trim($vbulletin->GPC['imagesize']);

                 $userbarinfo = vB_vBGamez_Userbar_dm::fetch_userbarinfo($userbarid);
                 $userbarid = $userbarinfo['userbarid'];

                 if(empty($userbarid) OR empty($userbarinfo) OR !vB_vBGamez_Userbar_dm::verify_permissions($userbarinfo))
                 {
                                print_no_permission();
                 }

                 if(empty($text) OR empty($repeat_x) OR empty($repeat_y))
                 {
                               eval(standard_error($vbphrase['vbgamez_userbarmgr_emptyfieldslocation']));
                 }

                 // set charset
                 require_once('./packages/vbgamez/comments.php');

                 $text = vBGamez_Comments::set_comment_charset($text);

                 $fetch_similar_location = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "vbgamez_userbar_config WHERE userbarid = $userbarid AND configid = $configid");

                 $configid = $fetch_similar_location['configid'];

                        $db->query("INSERT INTO " . TABLE_PREFIX . "vbgamez_userbar_config
                               (userbarid, text, radius, repeat_x, repeat_y, font, fontsize, fontcolor, enabled, width, ispreview)
                               VALUES (".$userbarid.",
                                       ".$db->sql_prepare($text).",
                                       ".$db->sql_prepare($radius).",
                                       ".$db->sql_prepare($repeat_x).",
                                       ".$db->sql_prepare($repeat_y).",
                                       ".$db->sql_prepare($font).",
                                       ".$db->sql_prepare($fontsize).",
                                       ".$db->sql_prepare($fontcolor).",
                                       1,
                                       ".$db->sql_prepare($width).", 1)");

                 $rand = rand();

                 if(VBG_IS_VB4)
                 {
                               $tpl = vB_Template::create('vbgamez_createuserbar_preview');
                               $tpl->register('userbarid', $userbarid); 
                               $tpl->register('rand', $rand);
                               $tpl->register('configid', $configid);
                               print $tpl->render(); exit;
                 }else{
                               eval('$html .= "' . fetch_template('vbgamez_createuserbar_preview') . '";');  
                               print $html; exit;
                 }

}
// ############################# DISABLE USERBAR   #####################
if($_REQUEST['do'] == 'disableuserbar')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'id' 	=> TYPE_INT));

        $userbarinfo = vB_vBGamez_Userbar_dm::fetch_userbarinfo($vbulletin->GPC['id']);

        if(empty($vbulletin->GPC['id']) OR empty($userbarinfo) OR !vB_vBGamez_Userbar_dm::verify_permissions($userbarinfo))
        {
                 print_no_permission();
        }

        $userbar_manager->disable_userbar($vbulletin->GPC['id']);

        $vbulletin->url = '' . $vbulletin->options['vbgamez_userbar_path'] . '?'.$vbulletin->session->vars['sessionurl'];

	eval(print_standard_redirect('vbgamez_userbar_userbar_disabled'));
}

// ############################# ENABLE USERBAR   #####################
if($_REQUEST['do'] == 'enableuserbar')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'id' 	=> TYPE_INT));

        $userbarinfo = vB_vBGamez_Userbar_dm::fetch_userbarinfo($vbulletin->GPC['id']);

        if(empty($vbulletin->GPC['id']) OR empty($userbarinfo) OR !vB_vBGamez_Userbar_dm::verify_permissions($userbarinfo))
        {
                 print_no_permission();
        }

        $userbar_manager->enable_userbar($vbulletin->GPC['id']);

        $vbulletin->url = '' . $vbulletin->options['vbgamez_userbar_path'] . '?'.$vbulletin->session->vars['sessionurl'];

	eval(print_standard_redirect('vbgamez_userbar_userbar_enabled'));
}

// ############################# DELETE USERBAR   #####################
if($_REQUEST['do'] == 'deleteuserbar')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'id' 	=> TYPE_INT));

        $userbarinfo = vB_vBGamez_Userbar_dm::fetch_userbarinfo($vbulletin->GPC['id']);

        if(empty($vbulletin->GPC['id']) OR empty($userbarinfo) OR !vB_vBGamez_Userbar_dm::verify_permissions($userbarinfo))
        {
                 print_no_permission();
        }

        $fetch_locations = $db->query("SELECT * FROM " . TABLE_PREFIX . "vbgamez_userbar_config WHERE userbarid = '" . $userbarinfo['userbarid'] . "'");

        while($config = $db->fetch_array($fetch_locations))
        {
                    @unlink($config['font']);
        }
        $userbar_manager->delete_userbar($vbulletin->GPC['id']);

        @unlink($userbarinfo['font']);
        @unlink($userbarinfo['background']);

        $vbulletin->url = '' . $vbulletin->options['vbgamez_userbar_path'] . '?'.$vbulletin->session->vars['sessionurl'];

	eval(print_standard_redirect('vbgamez_userbar_userbar_deleted'));
}

// ############################# CREATE EXAMPLE   #####################
if($_REQUEST['do'] == 'createexample')
{
        if(!vB_vBGamez_Userbar_dm::canCreateUserbar())
        {
                        eval(standard_error(fetch_error('vbgamez_you_have_max_userbars', $vbulletin->options['vbgamez_max_count_of_userbars'])));
        }

	$userbarid = vB_vBGamez_Userbar_dm::createExampleUserbar($_REQUEST['id']);
	
	$_REQUEST['fromserverid'] = intval($_REQUEST['fromserverid']);
	$serverinfo = vB_vBGamez::vbgamez_verify_id($_REQUEST['fromserverid']);
	if($serverinfo)
	{
		vbsetcookie('vbg_fromserverid', $serverinfo['id']);
	}else{
		vbsetcookie('vbg_fromserverid', '');
	}
	
        $vbulletin->url = '' . $vbulletin->options['vbgamez_userbar_path'] . '?do=edit&id='.$userbarid.'&'.$vbulletin->session->vars['sessionurl'];

	eval(print_standard_redirect('vbgamez_userbar_added'));
}
?>