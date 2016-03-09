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
define('THIS_SCRIPT', 'vbgamez_post');
define('VBG_PACKAGE', true);
define('CSRF_PROTECTION', true);
define('GET_EDIT_TEMPLATES', true);

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array('posting', 'user', 'vbgamez', 'messaging');

// get special data templates from the datastore
$specialtemplates = array('bbcodecache', 'smiliecache');

// pre-cache templates used by specific actions
$globaltemplates = array('vbgamez_addcomment','vbgamez_editcomment', 'vbgamez_ajax_quickedit', 'reportitem', 'humanverify_image');

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/packages/vbgamez/bootstrap.php');
require_once(DIR . '/packages/vbgamez/comments.php');

vB_vBGamez::bootstrap();

// ######################### EDIT COMMENT ##################
if($_REQUEST['do'] == 'editcomment')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'id' 	=> TYPE_INT,
                'ajax' 	=> TYPE_INT));

	$id = intval($vbulletin->GPC['id']);

	$select_comment = $vbulletin->db->query_read("SELECT vbgamez.id AS server_id, vbgamez.commentsenable AS commentsenable, vbgamez_comments.* FROM " . TABLE_PREFIX . "vbgamez_comments AS vbgamez_comments
                                                      LEFT JOIN " . TABLE_PREFIX . "vbgamez AS vbgamez ON(vbgamez.id = vbgamez_comments.serverid)
                                                       WHERE vbgamez_comments.id = " . $db->sql_prepare(intval($vbulletin->GPC['id'])) . "");

	$fetch_comment = $vbulletin->db->fetch_array($select_comment);

        $serverid = $fetch_comment['server_id'];

        if(!vB_vBGamez::vbg_check_edit_comments_permissions($fetch_comment['userid']))
        {
                 print_no_permission();
        }

        if(empty($serverid) OR empty($id))
        {
                  print_no_permission();
        }

        if(!vB_vBGamez::vbg_check_comments_enable($fetch_comment['commentsenable']))
        { 
                  print_no_permission();
        }
        
       $fetch_comment['pagetext'] = fetch_censored_text($fetch_comment['pagetext']);

        if($vbulletin->GPC['ajax'])
        {
                if(VBG_IS_VB4)
                {
	                  $templater = vB_Template::create('vbgamez_ajax_quickedit');
	                  $templater->register('comment', $fetch_comment);
	                  print_output($templater->render());
                }else{
                          $comment = $fetch_comment;
                          eval('print_output("' . fetch_template('vbgamez_ajax_quickedit') . '");');
                }

         }else{

                $navbits['' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . ''] = $vbphrase['vbgamez'];
                $navbits['' . $vbulletin->options['vbgamez_post_path'] . '?' . $vbulletin->session->vars['sessionurl'] . 'do=editcomment'] = $vbphrase['vbgamez_editcomment'];

	        $editorid = @construct_edit_toolbar(
			$fetch_comment['pagetext'],
			false,
			'signature',
			$vbulletin->options['allowsmilies'],
			true,
			false);

	        $select_comment = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez_comments WHERE id = " . $db->sql_prepare(intval($vbulletin->GPC['id'])) . "");
	        $fetch_comment = $vbulletin->db->fetch_array($select_comment);

                if(VBG_IS_VB4)
                {  
                         $navbits = construct_navbits($navbits);
                         $navbar = render_navbar_template($navbits);

	                 $templater = vB_Template::create('vbgamez_editcomment');
	                 $templater->register('comment', $fetch_comment);
	                 $templater->register('navbar', $navbar);
	                 $templater->register('editorid', $editorid);
	                 $templater->register('messagearea', $messagearea);
                         $templater->register_page_templates();

	                 print_output($templater->render());
                }else{
                          $comment = $fetch_comment;
	                  $navbits = construct_navbits($navbits);
	                  eval('$navbar = "' . fetch_template('navbar') . '";');
                          eval('print_output("' . fetch_template('vbgamez_editcomment') . '");');
                }
         }
}
// ######################### DO EDIT COMMENT ##################
if($_POST['do'] == 'doeditcomment')
{
        if(!vB_vBGamez::vbg_check_edit_comments_permissions($vbulletin->userinfo['userid']))
        {
              print_no_permission();
        }

	$vbulletin->input->clean_array_gpc('r', array(
		'wysiwyg' 	=> TYPE_STR,
		'id' 	=> TYPE_INT,
		'ajax' 	=> TYPE_INT,
                'message' 	=> TYPE_STR,
                'rbutton' 	=> TYPE_STR,
                'sbutton' 	=> TYPE_STR,
		'parseurl'         => TYPE_BOOL));

	$id = intval($vbulletin->GPC['id']);
	$vbulletin->GPC['message'] = trim($vbulletin->GPC['message']);

	if ($vbulletin->GPC['wysiwyg'])
	{
		  if(VBG_IS_VB4)
		  {
			require_once(DIR . '/includes/class_wysiwygparser.php');
			$html_parser = new vB_WysiwygHtmlParser($vbulletin);
			$vbulletin->GPC['message'] = $html_parser->parse_wysiwyg_html_to_bbcode($vbulletin->GPC['message'], $foruminfo['allowhtml']);

		 }else{
	      require_once(DIR . '/includes/functions_wysiwyg.php');
	      $vbulletin->GPC['message'] = convert_wysiwyg_html_to_bbcode($vbulletin->GPC['message'], $vbulletin->options['allowhtml']);
	 	 }
	}

	if ($vbulletin->options['allowbbcode'] AND $vbulletin->GPC['parseurl'])
	{
	      require_once(DIR . '/includes/functions_newpost.php');
	      $vbulletin->GPC['message'] = convert_url_to_bbcode($vbulletin->GPC['message']);
	}


	$select_comment = $vbulletin->db->query_read("SELECT vbgamez.id AS server_id, vbgamez.commentsenable AS commentsenable, vbgamez_comments.* FROM " . TABLE_PREFIX . "vbgamez_comments AS vbgamez_comments
                                                      LEFT JOIN " . TABLE_PREFIX . "vbgamez AS vbgamez ON(vbgamez.id = vbgamez_comments.serverid)
                                                       WHERE vbgamez_comments.id = " . $db->sql_prepare(intval($vbulletin->GPC['id'])) . "");

	$fetch_comment = $vbulletin->db->fetch_array($select_comment);

        $serverid = $fetch_comment['server_id'];

        if(!vB_vBGamez::vbg_check_edit_comments_permissions($fetch_comment['userid']))
        {
               print_no_permission();
        }

        if(empty($serverid) OR empty($id))
        {
               exec_header_redirect('' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . "");
        }

        if(!vB_vBGamez::vbg_check_comments_enable($fetch_comment['commentsenable']))
        { 
               print_no_permission();
        }

	if (empty($vbulletin->GPC['message']))
        {  
            if(!$vbulletin->GPC['ajax'])
            {
            		standard_error(fetch_error('vbgamez_comment_missing'));
            }else{
            		vB_vBGamez::print_or_standard_error(fetch_error('vbgamez_comment_missing')); 
            }
        }

	if(vbstrlen(strip_bbcode($vbulletin->GPC['message'])) < $vbulletin->options['vbgamez_comments_minchars'])
        {  
            if(!$vbulletin->GPC['ajax'])
            {
            		standard_error(fetch_error('tooshort', $vbulletin->options['vbgamez_comments_minchars']));
            }else{
            		vB_vBGamez::print_or_standard_error(fetch_error('tooshort', $vbulletin->options['vbgamez_comments_minchars'])); 
            }
        }

        if($vbulletin->GPC['rbutton'])
        {
                  $message = $vbulletin->GPC['message'];

	          require_once(DIR . '/includes/functions_newpost.php');

	          $vbulletin->GPC['message'] = preg_replace('/&#(0*32|x0*20);/', ' ', $vbulletin->GPC['message']);
	          $vbulletin->GPC['message'] = trim($vbulletin->GPC['message']);

	          $vbulletin->GPC['message'] = preg_replace('#\[color=(&quot;|"|\'|)([a-f0-9]{6})\\1]#i', '[color=\1#\2\1]', $vbulletin->GPC['message']);

	          $vbulletin->GPC['message'] = preg_replace('#\[/(left|center|right)]((\r\n|\r|\n)*)\[\\1]#si', '\\2', $vbulletin->GPC['message']);

	          if (stristr($vbulletin->GPC['message'], '[/list=') != false)
	          {
		          $vbulletin->GPC['message'] = preg_replace('#\[/list=[a-z0-9]+\]#siU', '[/list]', $vbulletin->GPC['message']);
	          }

	          $vbulletin->GPC['message'] = fetch_censored_text($vbulletin->GPC['message']);

	          if ($vbulletin->GPC['parseurl'])
	          {
		          $vbulletin->GPC['message'] = convert_url_to_bbcode($vbulletin->GPC['message']);
	          }

                  if(VBG_IS_VB4)
                  {
	                  require_once(DIR . '/includes/functions_video.php');
	                  $vbulletin->GPC['message'] = parse_video_bbcode($vbulletin->GPC['message']);
                  }

                  require_once('./includes/class_bbcode.php');
                  $bbcode_parser = new vB_BbCodeParser($vbulletin, fetch_tag_list());
	          $vbulletin->GPC['message'] = $bbcode_parser->do_parse($vbulletin->GPC['message']);

                  $show['preview'] = true;

                  $navbits['' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . ''] = $vbphrase['vbgamez'];
                  $navbits['' . $vbulletin->options['vbgamez_post_path'] . '?' . $vbulletin->session->vars['sessionurl'] . 'do=editcomment'] = $vbphrase['vbgamez_editcomment'];

	          $editorid = @construct_edit_toolbar($message, false, 'signature', $vbulletin->options['allowsmilies'], true, false);

                  if(!$vbulletin->GPC['ajax'])
                  {
                          $comment['id'] = $vbulletin->GPC['id'];
                          $comment['pagetext'] = $vbulletin->GPC['message'];

                   if(VBG_IS_VB4)
                   {
                            $navbits = construct_navbits($navbits);
                            $navbar = render_navbar_template($navbits);

	                    $templater = vB_Template::create('vbgamez_editcomment');
	                    $templater->register('comment', $comment);
	                    $templater->register('navbar', $navbar);
	                    $templater->register('editorid', $editorid);
	                    $templater->register('messagearea', $messagearea);
                            $templater->register_page_templates();

	                    print_output($templater->render());
                    }else{
                          
	                    $navbits = construct_navbits($navbits);
	                    eval('$navbar = "' . fetch_template('navbar') . '";');
                            eval('print_output("' . fetch_template('vbgamez_editcomment') . '");');
                    }
                  }else{
                           print_output(fetch_word_wrapped_string($comment['pagetext'], 45));
                 }
       }
 
      if($vbulletin->GPC['sbutton'])
      {

          if(!$vbulletin->GPC['ajax'])
          { 
                       $vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "vbgamez_comments SET pagetext = " . $db->sql_prepare($vbulletin->GPC['message']) . ", ipaddress = " . $db->sql_prepare(IPADDRESS) . " WHERE id = '" . $vbulletin->GPC['id'] . "' AND userid = '" . $fetch_comment['userid'] . "'");

                       $vbulletin->url = '' . $vbulletin->options['vbgamez_path'] . '?do=view&amp;id='.$serverid.'&amp;page='.vB_vBGamez::vbgamez_get_comment_page($serverid, $vbulletin->GPC['id'], 'edit');

                       eval(print_standard_redirect('vbgamez_commentedited'));
          }else{
                      $vbulletin->GPC['message'] = vBGamez_Comments::set_comment_charset($vbulletin->GPC['message']);

                      $vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "vbgamez_comments SET pagetext = " . $db->sql_prepare($vbulletin->GPC['message']) . ", ipaddress = " . $db->sql_prepare(IPADDRESS) . " WHERE id = '" . $vbulletin->GPC['id'] . "' AND userid = '" . $fetch_comment['userid'] . "'");
                      require_once('./includes/class_bbcode.php');
                      $bbcode_parser = new vB_BbCodeParser($vbulletin, fetch_tag_list());
	              $vbulletin->GPC['message'] = $bbcode_parser->do_parse($vbulletin->GPC['message']);

            	      vB_vBGamez::print_or_standard_error('<span id="comment_editmessage_' . $vbulletin->GPC['id'] . '">' . $vbulletin->GPC['message'] . '</span>'); 

         }
     }

}

// ######################### ADD COMMENT ##################
if($_REQUEST['do'] == 'addcomment')
{
        if(!$vbulletin->userinfo['userid'] AND !$vbulletin->options['vbgamez_comments_from_guests'])
        {
                    print_no_permission();
        }

	$vbulletin->input->clean_array_gpc('r', array(
		'id' 	=> TYPE_INT,
                'ajax' 	=> TYPE_INT));

	$id = intval($vbulletin->GPC['id']);

        $lookup = vB_vBGamez::vbgamez_verify_id($id);

        if (!$lookup)
        {
             exec_header_redirect('' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . "");
        }

	$select_comment = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez WHERE id = " . $db->sql_prepare(intval($id)) . "");
	$fetch_comment = $vbulletin->db->fetch_array($select_comment);

        $serverid = $fetch_comment['id'];

        if(empty($serverid))
        {
                exec_header_redirect('' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . "");
        }

        if(!vB_vBGamez::vbg_check_comments_enable($fetch_comment['commentsenable']))
        { 
                print_no_permission();
        }

  	if($vbulletin->options['vbgamez_comments_from_guests'] AND !$vbulletin->userinfo['userid'])
  	{
		require_once(DIR . '/includes/class_humanverify.php');
		$verify =& vB_HumanVerify::fetch_library($vbulletin);
		$human_verify = $verify->output_token();
  	}

        if(!$vbulletin->GPC['ajax'])
        {

                $navbits['' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . ''] = $vbphrase['vbgamez'];
                $navbits['' . $vbulletin->options['vbgamez_post_path'] . '?' . $vbulletin->session->vars['sessionurl'] . 'do=addcomment'] = $vbphrase['vbgamez_addcomment'];

	        $editorid = @construct_edit_toolbar('', false, 'signature', $vbulletin->options['allowsmilies'], true, false);

                if(VBG_IS_VB4)
                {
                      $navbits = construct_navbits($navbits);
                      $navbar = render_navbar_template($navbits);

	              $templater = vB_Template::create('vbgamez_addcomment');

	              $templater->register('navbar', $navbar);
	              $templater->register('editorid', $editorid);
	              $templater->register('messagearea', $messagearea);
	              $templater->register('humanverify', $human_verify);
	              $templater->register('id', $id);
                      $templater->register_page_templates();

	              print_output($templater->render());
                }else{
	              $navbits = construct_navbits($navbits);
	              eval('$navbar = "' . fetch_template('navbar') . '";');
                      eval('print_output("' . fetch_template('vbgamez_addcomment') . '");');
                }
     }
}

// ######################### DO ADD COMMENT ##################
if($_POST['do'] == 'doaddcomment')
{
        if(!$vbulletin->userinfo['userid'] AND !$vbulletin->options['vbgamez_comments_from_guests'])
        {
                    print_no_permission();
        }
	$vbulletin->input->clean_array_gpc('r', array(
		'wysiwyg' 	=> TYPE_STR,
		'id' 	=> TYPE_INT,
		'ajax' 	=> TYPE_INT,
                'message' 	=> TYPE_STR,
                'rbutton' 	=> TYPE_STR,
                'sbutton' 	=> TYPE_STR,
		'parseurl'         => TYPE_BOOL,
		'humanverify' => TYPE_ARRAY,
		'from_preview' => TYPE_STR, 'page' => TYPE_INT));

	$id = intval($vbulletin->GPC['id']);
	$vbulletin->GPC['message'] = trim($vbulletin->GPC['message']);

	if ($vbulletin->GPC['wysiwyg'])
	{
		  if(VBG_IS_VB4)
		  {
			require_once(DIR . '/includes/class_wysiwygparser.php');
			$html_parser = new vB_WysiwygHtmlParser($vbulletin);
			$vbulletin->GPC['message'] = $html_parser->parse_wysiwyg_html_to_bbcode($vbulletin->GPC['message'], $foruminfo['allowhtml']);

		 }else{
	      require_once(DIR . '/includes/functions_wysiwyg.php');
	      $vbulletin->GPC['message'] = convert_wysiwyg_html_to_bbcode($vbulletin->GPC['message'], $vbulletin->options['allowhtml']);
	 	 }
	}

	if ($vbulletin->options['allowbbcode'] AND $vbulletin->GPC['parseurl'])
	{
			require_once(DIR . '/includes/functions_newpost.php');
			$vbulletin->GPC['message'] = convert_url_to_bbcode($vbulletin->GPC['message']);
	}


	$select_comment = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez WHERE id = " . $db->sql_prepare($id) . "");
	$fetch_comment = $vbulletin->db->fetch_array($select_comment);

        $serverid = $fetch_comment['id'];

        if(empty($serverid))
        {
        		exec_header_redirect('' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . "");
        }

        if(!vB_vBGamez::vbg_check_comments_enable($fetch_comment['commentsenable']))
        { 
        		print_no_permission();
        }

	if (empty($vbulletin->GPC['message']))
        {  
            if(!$vbulletin->GPC['ajax'])
            {
            		standard_error(fetch_error('vbgamez_comment_missing'));
            }else{
            		vB_vBGamez::print_or_standard_error(fetch_error('vbgamez_comment_missing')); 
            }
        }

	if(vbstrlen(strip_bbcode($vbulletin->GPC['message'])) < $vbulletin->options['vbgamez_comments_minchars'])
        {  
            if(!$vbulletin->GPC['ajax'])
            {
            		standard_error(fetch_error('tooshort', $vbulletin->options['vbgamez_comments_minchars']));
            }else{
            		vB_vBGamez::print_or_standard_error(fetch_error('tooshort', $vbulletin->options['vbgamez_comments_minchars'])); 
            }
        }

	if(!$vbulletin->userinfo['userid'] AND empty($vbulletin->GPC['from_preview']))
	{
		require_once(DIR . '/includes/class_humanverify.php');
		$verify =& vB_HumanVerify::fetch_library($vbulletin);
		if (!$verify->verify_token($vbulletin->GPC['humanverify']))
		{
				vB_vBGamez::print_or_standard_error(fetch_error($verify->fetch_error())); 
		}
	}

    if($vbulletin->GPC['rbutton'])
    {
    	$message = $vbulletin->GPC['message'];

	require_once(DIR . '/includes/functions_newpost.php');

	$vbulletin->GPC['message'] = preg_replace('/&#(0*32|x0*20);/', ' ', $vbulletin->GPC['message']);
	$vbulletin->GPC['message'] = trim($vbulletin->GPC['message']);

	$vbulletin->GPC['message'] = preg_replace('#\[color=(&quot;|"|\'|)([a-f0-9]{6})\\1]#i', '[color=\1#\2\1]', $vbulletin->GPC['message']);

	$vbulletin->GPC['message'] = preg_replace('#\[/(left|center|right)]((\r\n|\r|\n)*)\[\\1]#si', '\\2', $vbulletin->GPC['message']);

	if (stristr($vbulletin->GPC['message'], '[/list=') != false)
	{
		$vbulletin->GPC['message'] = preg_replace('#\[/list=[a-z0-9]+\]#siU', '[/list]', $vbulletin->GPC['message']);
	}

	$vbulletin->GPC['message'] = fetch_censored_text($vbulletin->GPC['message']);

	if ($vbulletin->GPC['parseurl'])
	{
		$vbulletin->GPC['message'] = convert_url_to_bbcode($vbulletin->GPC['message']);
	}


        if(VBG_IS_VB4)
        {
		require_once(DIR . '/includes/functions_video.php');
		$vbulletin->GPC['message'] = parse_video_bbcode($vbulletin->GPC['message']);
        }

        require_once('./includes/class_bbcode.php');
        $bbcode_parser = new vB_BbCodeParser($vbulletin, fetch_tag_list());
	$vbulletin->GPC['message'] = $bbcode_parser->do_parse($vbulletin->GPC['message']);

        $show['preview'] = true;

        $navbits['' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . ''] = $vbphrase['vbgamez'];
        $navbits['' . $vbulletin->options['vbgamez_post_path'] . '?' . $vbulletin->session->vars['sessionurl'] . 'do=addcomment'] = $vbphrase['vbgamez_addcomment'];

	$editorid = @construct_edit_toolbar($message,false,'signature',$vbulletin->options['allowsmilies'],true,false);

        if(!$vbulletin->GPC['ajax'])
        {

                  if(VBG_IS_VB4)
                  {
        	          $navbits = construct_navbits($navbits);
        	          $navbar = render_navbar_template($navbits);
        	          $comment['id'] = $vbulletin->GPC['id'];
        	          $comment['pagetext'] = $vbulletin->GPC['message'];

		          $templater = vB_Template::create('vbgamez_addcomment');
	                  $templater->register('pagetext', $vbulletin->GPC['message']);
	                  $templater->register('id', $vbulletin->GPC['id']);

	                  $templater->register('comment', $comment);
	                  $templater->register('navbar', $navbar);
	                  $templater->register('editorid', $editorid);
	                  $templater->register('messagearea', $messagearea);
                          $templater->register_page_templates();

	                  print_output($templater->render());
                  }else{     
	                  $navbits = construct_navbits($navbits);
	                  eval('$navbar = "' . fetch_template('navbar') . '";');
                          eval('print_output("' . fetch_template('vbgamez_addcomment') . '");');
                  }

        }else{
                  print_output(fetch_word_wrapped_string($comment['pagetext'], 45));
        }
    }
 

  if($vbulletin->GPC['sbutton'])
  {

        if(!vB_vBGamez::moderate_comment_before_add($fetch_comment))
        {
                 $onmoderate = 1;
        }else{
                 $onmoderate = 0;
        }

        if(!$onmoderate)
        {
             $vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "vbgamez SET comments = comments +1 WHERE id = " . $id . "");
             vB_vBGamez::vBG_Datastore_Clear_Cache($id, 'comments');
        }
        
        vB_vBGamez::send_pm('Comment', $fetch_comment);

        if(!$vbulletin->GPC['ajax'])
        { 

	      $vbulletin->db->query_write(
		"INSERT INTO " . TABLE_PREFIX . "vbgamez_comments " . 
		"(serverid, userid, username, pagetext, dateline, onmoderate, ipaddress) " . 
		"VALUES ($id, " . $db->sql_prepare($vbulletin->userinfo['userid']) . ", " . $db->sql_prepare($vbulletin->userinfo['username']) . ",
                " . $db->sql_prepare($vbulletin->GPC['message']) . ", " . time() . ", '".$onmoderate."', " . $db->sql_prepare(IPADDRESS) . ")");

             $vbulletin->url = '' . $vbulletin->options['vbgamez_path'] . '?do=view&amp;id='.$id.'&amp;page='.vB_vBGamez::vbgamez_get_comment_page($id, $vbulletin->db->insert_id()).'#comments';

	     eval(print_standard_redirect('vbgamez_commentthanks'));

        }else{

           $vbulletin->GPC['message'] = vBGamez_Comments::set_comment_charset($vbulletin->GPC['message']);

	   $vbulletin->db->query_write(
		"INSERT INTO " . TABLE_PREFIX . "vbgamez_comments " . 
		"(serverid, userid, username, pagetext, dateline, onmoderate, ipaddress) " . 
		"VALUES ($id, " . $db->sql_prepare($vbulletin->userinfo['userid']) . ", " . $db->sql_prepare($vbulletin->userinfo['username']) . ",
                " . $db->sql_prepare($vbulletin->GPC['message']) . ", " . time() . ", '".$onmoderate."', " . $db->sql_prepare(IPADDRESS) . ")");

           $commentid = $vbulletin->db->insert_id();

           $avatar = fetch_avatar_url($vbulletin->userinfo['userid'], false);

           $show['edit'] = vB_vBGamez::vbg_check_edit_comments_permissions($vbulletin->userinfo['userid']);

           $show['delete'] = vB_vBGamez::vbg_check_delete_comments_permissions($serverid, $fetch_comment['userid'], $vbulletin->userinfo['userid']);

           require_once('./includes/class_bbcode.php');

           if(VBG_IS_VB4)
           {
                    require_once(DIR . '/includes/functions_video.php');
           }

           $bbcode_parser = new vB_BbCodeParser($vbulletin, fetch_tag_list());

           $vbulletin->GPC['message'] = fetch_censored_text($vbulletin->GPC['message']);

           if(VBG_IS_VB4)
           {
                    $vbulletin->GPC['message'] = parse_video_bbcode($vbulletin->GPC['message']);
           }

	   $vbulletin->GPC['message'] = $bbcode_parser->do_parse($vbulletin->GPC['message']);

           $comment = array(
            'id' => $commentid,
            'serverid' => $id,
            'userid' => $vbulletin->userinfo['userid'],
            'username' => $vbulletin->userinfo['username'],
            'musername' => fetch_musername($vbulletin->userinfo),
            'pagetext' => fetch_word_wrapped_string($vbulletin->GPC['message'], 45),
            'date' => vbdate($vbulletin->options['dateformat'].' '.$vbulletin->options['timeformat'],TIMENOW,true),
            'avatarurl' => $avatar[0],
            'onmoderate' => $onmoderate,
			'owner' => $fetch_comment['userid']);

           if(VBG_IS_VB4)
           {
                    $comment['memberaction_dropdown'] = construct_memberaction_dropdown($vbulletin->userinfo);
           }

           $show['delete_array'] = vB_vBGamez::vbg_check_delete_comments_permissions($serverid, $fetch_comment['userid']);
           $ajax_restore = '<div id="comment_edit_restore_' . $commentid . '" style="display:none"></div>';

		   $new_value_page = vB_vBGamez::vbgamez_get_comment_page($id, $fetch_comment);
		   if($vbulletin->GPC['page'] != $new_value_page OR !$vbulletin->userinfo['userid'])
		   {
			$rand_f = rand(1000, 9999);
             print 'go_to_page:'.$vbulletin->options['vbgamez_path'] . '?do=view&id='.$id.'&page='.$new_value_page.'&r=' . $rand_f . '#comments';
			exit;
		}
		
           if(VBG_IS_VB4)
           { 
                    $templater = vB_Template::create('vbgamez_commentbits');

                    $templater->register('comment', $comment);
					$templater->register('serveruserid', $fetch_comment['userid']);
                    $return = $templater->render();
           }else{
                    eval('$return = "' . fetch_template('vbgamez_commentbits') . '";');
           }

           print $return.$ajax_restore; exit;

        }
    }

}
// ######################### REPORT ##################
if($_REQUEST['do'] == 'report' OR $_REQUEST['do'] == 'doreport')
{
        if(!$vbulletin->userinfo['userid'])
        {
                print_no_permission();
        }
        $postid = intval($_POST['id']);
        $getid = intval($_REQUEST['id']);
        
        $id = iif($postid, $postid, $getid);
        
	      $select_comment = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez_comments WHERE id = " . $db->sql_prepare($id) . "");
	      $fetch_comment = $vbulletin->db->fetch_array($select_comment);
        if(!$fetch_comment)
        {
               eval(standard_error(fetch_error('invalidid', $vbphrase['message'])));
        }
        
        $lookup = vB_vBGamez::vbgamez_verify_id($fetch_comment['serverid']);
        if(!$lookup)
        {
               eval(standard_error(fetch_error('invalidid', $vbphrase['vbgamez_server'])));
        }

        if($_REQUEST['do'] == 'report')
        {
                 $forminfo = array();
                 $forminfo['reportphrase'] = $vbphrase['report_bad_post'];
                 $forminfo['reporttype'] = $vbphrase['vbgamez_server'];
                 $forminfo['itemlink'] = $vbulletin->options['vbgamez_path'].'?'.$vbulletin->session->vars['sessionurl'] .'do=view&amp;id='.$fetch_comment['serverid'];
                 $forminfo['file'] = str_replace('.php', '', $vbulletin->options['vbgamez_post_path']);
                 $forminfo['action'] = 'doreport';
                 $forminfo['itemname'] = vB_vBGamez::vbgamez_string_html($lookup['cache_name']);
                 $forminfo['description'] = $vbphrase['only_used_to_report'];
                 $forminfo['hiddenfields'] = '<input type="hidden" name="id" value="' . $id . '">';
        
                 $navbits['' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . ''] = $vbphrase['vbgamez'];
                 $navbits['' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . 'do=view&amp;id='.$lookup['id']] = vB_vBGamez::vbgamez_string_html($lookup['cache_name']);
                 $navbits['' . $vbulletin->options['vbgamez_post_path'] . '?' . $vbulletin->session->vars['sessionurl'] . 'do=report'] = $vbphrase['report_bad_post'];
        
                 $navbits = construct_navbits($navbits);
                 
                 $url = $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . 'do=view&id='.$lookup['id'];
                 
                 if(VBG_IS_VB4)
                 { 
                    $navbar = render_navbar_template($navbits);

                    $tpl = vB_Template::create('reportitem');
                    $tpl->register_page_templates();
                    $tpl->register('navbar', $navbar);
                    $tpl->register('forminfo', $forminfo);
                    $tpl->register('url', $url);
                    print_output($tpl->render());
                 }else{
	      	         eval('print_output("' . fetch_template('reportitem') . '");'); 
                 }
        }else{

		         $vbulletin->input->clean_array_gpc('p', array(
			         'reason' => TYPE_STR,
		         ));

             $vbulletin->GPC['reason'] = trim($vbulletin->GPC['reason']);
             
		         if ($vbulletin->GPC['reason'] == '')
		         {
			                  eval(standard_error(fetch_error('noreason')));
		         }
		         
		         $fetch_comment['pagetext'] = fetch_censored_text($fetch_comment['pagetext']);

		         $serverurl = $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . 'do=view&id='.$lookup['id'];
		         
		         $reasontext = construct_phrase($vbphrase['vbgamez_report_comment'], $vbulletin->userinfo['username'], $vbulletin->userinfo['email'], $fetch_comment['pagetext'], $serverurl, $vbulletin->GPC['reason']);

		         if($vbulletin->options['vbgamez_comment_adminid'])
		         {
		                 $fetch_users = $db->query_read("SELECT userid, email FROM " . TABLE_PREFIX . "user
		                                                  WHERE usergroupid IN (" . $vbulletin->options['vbgamez_comment_adminid'] . ")");

		                 while($user = $db->fetch_array($fetch_users))
		                 {
		                            vbmail($user['email'], construct_phrase($vbphrase['vbgamez_report_comment_title'], $vbulletin->options['bbtitle']), $reasontext);

		                 }
		         }
		         
		         $vbulletin->url = $serverurl;
		         eval(print_standard_redirect('redirect_reportthanks'));
		   }
}

// ######################### VIEW IP ##################
if ($_REQUEST['do'] == 'viewip')
{
	// check moderator permissions for getting ip
	if (!can_moderate(0, 'canviewips'))
	{
		print_no_permission();
	}
	
  $id = intval($_REQUEST['id']);
        
	$select_comment = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez_comments WHERE id = " . $db->sql_prepare($id) . "");
	$fetch_comment = $vbulletin->db->fetch_array($select_comment);
  if(!$fetch_comment)
  {
           eval(standard_error(fetch_error('invalidid', $vbphrase['message'])));
  }
        
  $lookup = vB_vBGamez::vbgamez_verify_id($fetch_comment['serverid']);
  if(!$lookup)
  {
           eval(standard_error(fetch_error('invalidid', $vbphrase['vbgamez_server'])));
  }
  $posterinfo = fetch_userinfo($fetch_comment['userid']);
  if($posterinfo['ipaddress'])
  {
	         $host = @gethostbyaddr($posterinfo['ipaddress']);
	         $ip = $posterinfo['ipaddress'];
	}else{
		       $host = @gethostbyaddr($fetch_comment['ipaddress']);
	         $ip = $fetch_comment['ipaddress'];
	}

	eval(standard_error(fetch_error('thread_displayip', $ip, $host), '', 0));
}
?>