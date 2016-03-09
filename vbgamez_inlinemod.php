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
define('THIS_SCRIPT', 'vbgamez_inlinemod');
define('VBG_PACKAGE', true);
define('CSRF_PROTECTION', true);

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array('posting', 'user', 'vbgamez', 'inlinemod', 'threadmanage', 'forumdisplay');

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by specific actions
$globaltemplates = array('vbgamez_deletecomments', 'USERCP_SHELL', 'usercp_nav_folderbit', 'vbgamez_moderation_comments', 'vbgamez_sortarrow');

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/packages/vbgamez/bootstrap.php');
require_once(DIR . '/packages/vbgamez/comments.php');

vB_vBGamez::bootstrap();
// ############################# DELETE COMMENTS #####################

if ($_REQUEST['do'] == 'deletecomments' OR $_REQUEST['do'] == 'dodeletecomments')
{
        if(empty($_REQUEST['commentsarray'])) { standard_error(fetch_error('you_did_not_select_any_valid_messages')); }

        if(is_array($_REQUEST['commentsarray']))
        {
              $improde = implode(' ', $_REQUEST['commentsarray']);

              $explode = explode(" ", $improde);

              foreach($explode AS $id)
              {
					  $id = intval($id);
                      $sql_ids .= ",$id";
                      $formdata .= '<input type="hidden" name="commentsarray[]" value="'.$id.'">';
              }
			  if(count($explode) == 1)
			  {
				$is_only = true;
			  }
              $check_perms = true;

        }elseif(is_numeric($_REQUEST['commentsarray']))
        {
			 $is_only = true;
             $sql_ids = ','.$_REQUEST['commentsarray'];
             $formdata = '<input type="hidden" name="commentsarray" value="'.$_REQUEST['commentsarray'].'">';
        }else{
             print_no_permission();
        }

        $fetch_comment = $vbulletin->db->query_first("SELECT serverid, userid, deleted FROM " . TABLE_PREFIX . "vbgamez_comments WHERE id IN(0$sql_ids)");
		$isdeleted = intval($fetch_comment['deleted']);
		
        $serverid = intval($fetch_comment['serverid']);

        $lookup = vB_vBGamez::vbgamez_verify_id($serverid);
 
        if (!$lookup)
        {
          exec_header_redirect('' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . "");
        }

        if (!$vbulletin->userinfo['userid'] OR empty($sql_ids) OR empty($serverid))
        {
          exec_header_redirect('' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . ""); 
        }

        if($check_perms AND !vB_vBGamez::vbg_check_delete_comments_permissions($lookup['id'], $lookup['userid']))
        {
              print_no_permission();
        }

        if(!vB_vBGamez::vbg_check_comments_enable($lookup['commentsenable']))
        { 
           print_no_permission();
        }

        if(!vB_vBGamez::vbg_check_delete_comments_permissions($lookup['id'], $lookup['userid'], $fetch_comment['userid']))
        { 
           print_no_permission();
        }
}

if ($_REQUEST['do'] == 'deletecomments')
{
		if($is_only AND $isdeleted)
		{
			$show['delete_checked_2'] = 'checked="checked"';
			$show['delete_checked_1'] = 'disabled="disabled"';
		}else{
        	$show['delete_checked_1'] = 'checked="checked"';
		}
		
        if(vB_vBGamez::vbg_check_delete_comments_permissions($lookup['id'], $lookup['userid']))
        { 
           $show['canremove'] = true;
        }

        $navbits['' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . ''] = $vbphrase['vbgamez'];
        $navbits[] = $vbphrase['delete_messages'];

        if(VBG_IS_VB4)
        {
                  $templater = vB_Template::create('vbgamez_deletecomments');

                  $navbits = construct_navbits($navbits);
                  $navbar = render_navbar_template($navbits);
                  $templater->register_page_templates();
                  $templater->register('navbar', $navbar);
                  $templater->register('formdata', $formdata);
                  $templater->register('type', $_REQUEST['type']);
                  print_output($templater->render());
        }else{
	          $navbits = construct_navbits($navbits);
	          eval('$navbar = "' . fetch_template('navbar') . '";');
	          eval('print_output("' . fetch_template('vbgamez_deletecomments') . '");');
        }
}

if ($_REQUEST['do'] == 'dodeletecomments')
{
	$vbulletin->input->clean_array_gpc('r', array(
                'deletetype' => TYPE_INT,
		'deletereason' 	=> TYPE_STR));

        if(vB_vBGamez::vbg_check_delete_comments_permissions($lookup['id'], $lookup['userid']))
        { 

            if($vbulletin->GPC['deletetype'] == 2)
            {
	             $vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "vbgamez_comments WHERE id IN (0$sql_ids)");
            }else if($vbulletin->GPC['deletetype'] == 1)
            {
	             $vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "vbgamez_comments SET onmoderate = 0, deletedby = ".$db->sql_prepare($vbulletin->userinfo['username']).", deletedbyuserid = ".$db->sql_prepare($vbulletin->userinfo['userid']).", deletedreason = ".$db->sql_prepare($vbulletin->GPC['deletereason']).", deleted = 1 WHERE id IN (0$sql_ids)");
            } 

        }else{
	             $vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "vbgamez_comments SET onmoderate = 0, deletedby = ".$db->sql_prepare($vbulletin->userinfo['username']).", deletedbyuserid = ".$db->sql_prepare($vbulletin->userinfo['userid']).", deletedreason = ".$db->sql_prepare($vbulletin->GPC['deletereason']).", deleted = 1 WHERE id IN (0$sql_ids)");
        }

        vB_vBGamez::update_count_of_comments($lookup['id']);

        if(!empty($_REQUEST['type']))
        {
                   if($_REQUEST['type'] == 'deleted')
                   {
                                $vbulletin->url = '' . $vbulletin->options['vbgamez_inlinemod_path'] . '?do=viewcomments&amp;type=deleted';
                   }else{
                                $vbulletin->url = '' . $vbulletin->options['vbgamez_inlinemod_path'] . '?do=viewcomments&amp;type=moderated';
                   }
        }else{
                   $vbulletin->url = '' . $vbulletin->options['vbgamez_path'] . '?do=view&amp;id='.$serverid.'&amp;page='.vB_vBGamez::vbgamez_get_comment_page($serverid, null);

        }

        vB_vBGamez::vBG_Datastore_Clear_Cache($lookup['id'], 'comments');

	eval(print_standard_redirect('vbgamez_deletecomment'));

}

// ############################# UNDELETE COMMENTS #####################
if ($_REQUEST['do'] == 'undeletecomments')
{
        if(empty($_REQUEST['commentsarray'])) { standard_error(fetch_error('you_did_not_select_any_valid_messages')); }

        if(is_array($_REQUEST['commentsarray']))
        {
              $improde = implode(' ', $_REQUEST['commentsarray']);

              $explode = explode(" ", $improde);

              foreach($explode AS $id)
              {
					  $id = intval($id);
                      $sql_ids .= ",$id";
              }

        }elseif(is_numeric($_REQUEST['commentsarray']))
        {
             $sql_ids = ','.intval($_REQUEST['commentsarray']);
        }

        $fetch_comment = $vbulletin->db->query_first("SELECT serverid, userid FROM " . TABLE_PREFIX . "vbgamez_comments WHERE id IN(0$sql_ids)");

        $serverid = intval($fetch_comment['serverid']);

        $lookup = vB_vBGamez::vbgamez_verify_id($serverid);

        if (!$lookup)
        {
          exec_header_redirect('' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . "");
        }

        if (!$vbulletin->userinfo['userid'] OR empty($sql_ids) OR empty($serverid))
        {
          exec_header_redirect('' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . ""); 
        }

        if(!vB_vBGamez::vbg_check_comments_enable($lookup['commentsenable']))
        { 
           print_no_permission();
        }

        if(!vB_vBGamez::vbg_check_delete_comments_permissions($lookup['id'], $lookup['userid'], $fetch_comment['userid']))
        { 
           print_no_permission();
        }

	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "vbgamez_comments SET deleted = 0, onmoderate = 0 WHERE id IN (0$sql_ids)");

        vB_vBGamez::update_count_of_comments($lookup['id']);

        if(!empty($_REQUEST['type']))
        {
                   if($_REQUEST['type'] == 'deleted')
                   {
                                $vbulletin->url = '' . $vbulletin->options['vbgamez_inlinemod_path'] . '?do=viewcomments&amp;type=deleted';
                   }else{
                                $vbulletin->url = '' . $vbulletin->options['vbgamez_inlinemod_path'] . '?do=viewcomments&amp;type=moderated';
                   }
        }else{
                   $vbulletin->url = '' . $vbulletin->options['vbgamez_path'] . '?do=view&amp;id='.$serverid.'&amp;page='.vB_vBGamez::vbgamez_get_comment_page($serverid, null);

        }

        vB_vBGamez::vBG_Datastore_Clear_Cache($lookup['id'], 'comments');
	eval(print_standard_redirect('inline_undeleteposts'));

}

// ############################# APPROVE COMMENTS #####################

if ($_REQUEST['do'] == 'approve')
{
        if(empty($_REQUEST['commentsarray'])) { standard_error(fetch_error('you_did_not_select_any_valid_messages')); }

        if(is_array($_REQUEST['commentsarray']))
        {
              $improde = implode(' ', $_REQUEST['commentsarray']);

              $explode = explode(" ", $improde);

              foreach($explode AS $id)
              {
					  $id = intval($id);
                      $sql_ids .= ",$id";
              }

        }elseif(is_numeric($_REQUEST['commentsarray']))
        {
             $sql_ids = ','.intval($_REQUEST['commentsarray']);
        }

        $fetch_comment = $vbulletin->db->query_first("SELECT serverid, userid FROM " . TABLE_PREFIX . "vbgamez_comments WHERE id IN(0$sql_ids)");

        $serverid = intval($fetch_comment['serverid']);

        $lookup = vB_vBGamez::vbgamez_verify_id($serverid);

        if (!$lookup)
        {
          exec_header_redirect('' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . "");
        }

        if (!$vbulletin->userinfo['userid'] OR empty($sql_ids) OR empty($serverid))
        {
          exec_header_redirect('' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . ""); 
        }

        if(!vB_vBGamez::vbg_check_comments_enable($lookup['commentsenable']))
        { 
           print_no_permission();
        }

        if(!vB_vBGamez::vbg_check_delete_comments_permissions($lookup['id'], $lookup['userid'], $fetch_comment['userid']))
        { 
           print_no_permission();
        }

	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "vbgamez_comments SET onmoderate = 0, deleted = 0 WHERE id IN (0$sql_ids)");

        vB_vBGamez::update_count_of_comments($lookup['id']);

        if(!empty($_REQUEST['type']))
        {
                   if($_REQUEST['type'] == 'deleted')
                   {
                                $vbulletin->url = '' . $vbulletin->options['vbgamez_inlinemod_path'] . '?do=viewcomments&amp;type=deleted';
                   }else{
                                $vbulletin->url = '' . $vbulletin->options['vbgamez_inlinemod_path'] . '?do=viewcomments&amp;type=moderated';
                   }
        }else{
                   $vbulletin->url = '' . $vbulletin->options['vbgamez_path'] . '?do=view&amp;id='.$serverid.'&amp;page='.vB_vBGamez::vbgamez_get_comment_page($serverid, null);

        }

        vB_vBGamez::vBG_Datastore_Clear_Cache($lookup['id'], 'comments');
	eval(print_standard_redirect('inline_approvedposts'));

}

// ############################# UN APPROVE COMMENTS #####################
if ($_REQUEST['do'] == 'unapprove')
{
        if(empty($_REQUEST['commentsarray'])) { standard_error(fetch_error('you_did_not_select_any_valid_messages')); }

        $improde = implode(' ', $_REQUEST['commentsarray']);

        $explode = explode(" ", $improde);

        foreach($explode AS $id)
        {
	            $id = intval($id);
                $sql_ids .= ",$id";
        }

        $fetch_comment = $vbulletin->db->query_first("SELECT serverid, userid FROM " . TABLE_PREFIX . "vbgamez_comments WHERE id IN(0$sql_ids)");

        $serverid = intval($fetch_comment['serverid']);

        $lookup = vB_vBGamez::vbgamez_verify_id($serverid);

        if (!$lookup)
        {
          exec_header_redirect('' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . "");
        }

        if (!$vbulletin->userinfo['userid'] OR empty($id) OR empty($serverid))
        {
          exec_header_redirect('' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . ""); 
        }

        if(!vB_vBGamez::vbg_check_comments_enable($lookup['commentsenable']))
        { 
           print_no_permission();
        }

        if(!vB_vBGamez::vbg_check_delete_comments_permissions($lookup['id'], $lookup['userid'], $fetch_comment['userid']))
        { 
           print_no_permission();
        }

	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "vbgamez_comments SET onmoderate = 1, deleted = 0 WHERE id IN (0$sql_ids)");

        vB_vBGamez::update_count_of_comments($lookup['id']);

        $vbulletin->url = '' . $vbulletin->options['vbgamez_path'] . '?do=view&amp;id='.$serverid.'&amp;page='.vB_vBGamez::vbgamez_get_comment_page($serverid, null);

        vB_vBGamez::vBG_Datastore_Clear_Cache($lookup['id'], 'comments');
	eval(print_standard_redirect('inline_unapprovedposts'));

}
// ############################# INLINE MOD #####################
if($_REQUEST['do'] == 'inlinemod')
{
      // simple error if action empty

      standard_error(fetch_error('invalid_action_specified'));
}

// ############################# MODERATE COMMENTS ##############
if($_REQUEST['do'] == 'viewcomments')
{

  if(!vB_vBGamez::check_moderate_permissions())
  {
        print_no_permission();
  }
  
  if($_REQUEST['type'] != 'moderated' AND $_REQUEST['type'] != 'deleted')
  {
        exit;
  }
  
  if($_REQUEST['type'] == 'moderated')
  {
          $where_cond = 'WHERE onmoderate = 1';
          $type = 'moderated';
          $total_comments = vB_vBGamez::fetch_count_comments_on_moderation();
          $type_phrase = $vbphrase['vbgamez_moderate_comments'];
          $type = 'moderated';
  }else{
          $where_cond = 'WHERE deleted = 1';
          $type = 'deleted';
          $total_comments = vB_vBGamez::fetch_count_comments_deleted(); 
          $show['deleted'] = true;
          $type_phrase = $vbphrase['vbgamez_deleted_comments'];
          $type = 'deleted';
  }
  
        $perpage = $vbulletin->input->clean_gpc('r', 'perpage', TYPE_UINT);
        $page = $vbulletin->input->clean_gpc('r', 'page', TYPE_UINT);
        $sortfield = $vbulletin->input->clean_gpc('r', 'sortfield', TYPE_NOHTML);
        $sortorder = $vbulletin->input->clean_gpc('r', 'sortorder', TYPE_NOHTML);
        $vbulletin->GPC['daysprune'] = $vbulletin->input->clean_gpc('r', 'daysprune', TYPE_INT);

	$daysprune  =& $vbulletin->GPC['daysprune'];

        $vbulletin->GPC['perpage'] = vB_vBGamez::sanitize_perpage($vbulletin->GPC['perpage'], 100, '25');

        if (!$vbulletin->GPC['page'])
        {
	      $vbulletin->GPC['page']  = 1;
        }


        $pos = ($vbulletin->GPC['page'] - 1) * $vbulletin->GPC['perpage'];

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
                $sortfield = 'date';
        }

        switch ($sortfield)
        {
	      case 'username':
		      $sqlsort = 'vbgamez_comments.username';
		      break;
	      case 'servername':
		      $sqlsort = 'vbgamez.cache_name';
		      break;
	      case 'date':
		      $sqlsort = 'vbgamez_comments.dateline';
		      break;

	      default:
		      $sqlsort = 'vbgamez_comments.dateline';
                      $sortfield = 'vbgamez_comments.dateline';
        }

	if (!$daysprune)
	{
		$daysprune = ($vbulletin->userinfo['daysprune']) ? $vbulletin->userinfo['daysprune'] : 30;
	}

	$datecut = ($daysprune != -1) ? "AND vbgamez_comments.dateline >= " . (TIMENOW - ($daysprune * 86400)) : '';

	$daysprunesel = iif($daysprune == -1, 'all', $daysprune);
	$daysprunesel = array($daysprunesel => 'selected="selected"');

        $sorturl = '' . $vbulletin->options['vbgamez_inlinemod_path'] . '?' . $vbulletin->session->vars['sessionurl'] . 'do=viewcomments&amp;type=' . $type . '&amp;daysprune='.$daysprune;
  
        $sorturl = preg_replace('#&amp;$#s', '', $sorturl);

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
        if(VBG_IS_VB4)
        {
                  $order = array($oppositeorder => 'checked="checked"');
        }else{
                  $order = array($oppositeorder => 'selected="selected"');
        }

        require_once('./includes/class_bbcode.php');

        if(VBG_IS_VB4)
        {
                           require_once(DIR . '/includes/functions_video.php');
        }

        $bbcode_parser = new vB_BbCodeParser($vbulletin, fetch_tag_list());

        $result_comments = $vbulletin->db->query_read("SELECT vbgamez.cache_name AS servername, vbgamez.id AS serverid, vbgamez_comments.*, user.userid, user.usergroupid, user.avatarrevision
                                 " . ($vbulletin->options['avatarenabled'] ? ",avatar.avatarpath, NOT ISNULL(customavatar.userid) AS hascustomavatar, customavatar.dateline AS avatardateline,customavatar.width AS avwidth,customavatar.height AS avheight, customavatar.width_thumb AS avwidth_thumb, customavatar.height_thumb AS avheight_thumb, filedata_thumb, NOT ISNULL(customavatar.userid) AS hascustom" : "") . "
		                 FROM " . TABLE_PREFIX . "vbgamez_comments AS vbgamez_comments
		                 LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = vbgamez_comments.userid)
		                 LEFT JOIN " . TABLE_PREFIX . "vbgamez AS vbgamez ON(vbgamez.id = vbgamez_comments.serverid)
                                 " . ($vbulletin->options['avatarenabled'] ? "LEFT JOIN " . TABLE_PREFIX . "avatar AS avatar ON(avatar.avatarid = user.avatarid) LEFT JOIN " . TABLE_PREFIX . "customavatar AS customavatar ON(customavatar.userid = vbgamez_comments.userid)" : "") . "
		                 $where_cond
                                 $datecut
		                 ORDER by $sqlsort DESC LIMIT $pos, ".$vbulletin->GPC['perpage']."");

	while ($comment = $vbulletin->db->fetch_Array($result_comments))
	{   
               $comment['servername'] = vB_vBGamez::vbgamez_string_html($comment['servername']);

               $comment['smalltext'] = substr(strip_bbcode(strip_tags(fetch_word_wrapped_string($comment['pagetext'], 45))), 0, 50)."...";

               $commentbits .= vBGamez_Comments::fetch_comment($comment, 'vbgamez_moderation_commentsbits');

               if(!VBG_IS_VB4)
               {
	                  eval('$vbgpopups .= "' . fetch_template('vbgamez_moderation_view_popup') . '";');
               }
        }

	construct_usercp_nav();

        $pagenav = construct_page_nav($vbulletin->GPC['page'], $vbulletin->GPC['perpage'], $total_comments, '' . $vbulletin->options['vbgamez_inlinemod_path'] . '?' . $vbulletin->session->vars['sessionurl'] . 'do=viewcomments&amp;type=' . $type . '&amp;pp=' . $vbulletin->GPC['perpage']);

        $navbits['' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . ''] = $vbphrase['vbgamez'];
        $navbits[] = $type_phrase;

        if(VBG_IS_VB4)
        {
                 $templater = vB_Template::create('vbgamez_moderation_comments');

                 $navbits = construct_navbits($navbits);
                 $navbar = render_navbar_template($navbits);

                 $templater->register('listbits', $commentbits);
                 $templater->register('sortorder', $sortorder);
                 $templater->register('pagenumber', $vbulletin->GPC['page']);
                 $templater->register('perpage', $vbulletin->GPC['perpage']);
                 $templater->register('sortfield', $sortfield);
                 $templater->register('sorturl', $sorturl);
                 $templater->register('sortarrow', $sortarrow);
                 $templater->register('sort', $sort);
                 $templater->register('order', $order);
                 $templater->register('pagenav', $pagenav);
                 $templater->register('total_comments', $total_comments);
                 $templater->register('daysprunesel', $daysprunesel);
                 $templater->register('type_phrase', $type_phrase);
                 $templater->register('type', $type);

                 $HTML = $templater->render();

	         $navbar = render_navbar_template($navbits);

	         $templater = vB_Template::create('USERCP_SHELL');
		         $templater->register_page_templates();
		         $templater->register('cpnav', $cpnav);
		         $templater->register('HTML', $HTML);
		         $templater->register('clientscripts', $clientscripts);
		         $templater->register('navbar', $navbar);
		         $templater->register('navclass', $navclass);
		         $templater->register('onload', $onload);
		         $templater->register('pagetitle', $pagetitle);
		         $templater->register('template_hook', $template_hook);
	         print_output($templater->render());
        }else{
           $pagenumber = $vbulletin->GPC['page'];
           $perpage = $vbulletin->GPC['perpage'];
           $listbits = $commentbits;

           $navbits = construct_navbits($navbits);

	   eval('$HTML = "' . fetch_template('vbgamez_moderation_comments') . '";');

	   eval('$navbar = "' . fetch_template('navbar') . '";');
	   eval('$vbg_css = "' . fetch_template('vbgamez.css') . '";');
	   eval('print_output("' . fetch_template('USERCP_SHELL') . '");'); 
        }
}

// ############################# VIEW FULL COMMENT ##############
if($_REQUEST['do'] == 'viewfullcomment')
{
  if(!vB_vBGamez::check_moderate_permissions())
  {
        print_no_permission();
  }

  $_POST['commentid'] = intval($_POST['commentid']);

  if(empty($_POST['commentid']))
  {
        print_no_permission();
  }

  require_once('./includes/class_bbcode.php');

  if(VBG_IS_VB4)
  {
                 require_once(DIR . '/includes/functions_video.php');
  }

  $bbcode_parser = new vB_BbCodeParser($vbulletin, fetch_tag_list());

  $result_comment = $vbulletin->db->query_first("SELECT vbgamez_comments.pagetext FROM " . TABLE_PREFIX . "vbgamez_comments AS vbgamez_comments WHERE id = '" . intval($_POST['commentid']) . "'");

  $result_comment['pagetext'] = fetch_censored_text($result_comment['pagetext']);

  if(VBG_IS_VB4)
  {
              $result_comment['pagetext'] = parse_video_bbcode($result_comment['pagetext']);
  }

  $result_comment['pagetext'] = $bbcode_parser->do_parse($result_comment['pagetext']);

  vB_vBGamez::print_or_standard_error($result_comment['pagetext']);

}
