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
 * VBGamEz - לוםוהזונ פנוילמג
 *
 * @package vBGamEz
 * @author GiveMeABreak aka Developer
 * @version $Revision: 140 $
 * @copyright GiveMeABreak
 */
 
 class vBGamEz_Frame_Manager
 {
       var $registry;

       var $vbphrase;

       function vBGamEz_Frame_Manager($vbulletin)
       {
                $this->registry = $vbulletin;
       }

       function bootstrap($vbphrase)
       {
                $get_frames = $this->registry->db->query("SELECT * FROM " . TABLE_PREFIX . "vbgamez_frame ORDER BY `order`");

                while($frame = $this->registry->db->fetch_array($get_frames))
                {
                         $this->framecache[$frame['frameid']] = $frame;
                }
                
                $this->vbphrase = $vbphrase;
       }

       function build_frame_datastore()
       {
	       global $vbulletin;

	       $frame_cache = array();

	       $frame_result = $this->registry->db->query_read("SELECT *
		                                                 FROM " . TABLE_PREFIX . "vbgamez_frame
		                                                 WHERE enabled = 1
		                                                 ORDER BY `order` ASC");

	       while ($frame = $this->registry->db->fetch_array($frame_result))
	       {
		       $frame_cache["$frame[frameid]"] = $frame;
	       }

	       $this->registry->db->free_result($frame_result);

	       build_datastore('vbgamez_framecache', serialize($frame_cache), 1);

	       $stylevar_cache = array();

	       $frame_stylevar_result = $this->registry->db->query_read("SELECT *
		                                                 FROM " . TABLE_PREFIX . "vbgamez_frame_stylevar
		                                                 WHERE enabled = 1
		                                                 ORDER BY `order` ASC");

	       while ($stylevar = $this->registry->db->fetch_array($frame_stylevar_result))
	       {
                       if(!$stylevar['enabled'])
                       {
                                   continue;
                       }

		       $stylevar_cache["$stylevar[stylevarid]"] = $stylevar;
	       }

	       $this->registry->db->free_result($frame_stylevar_result);

	       build_datastore('vbgamez_framestylevarcache', serialize($stylevar_cache), 1);
       }

       function create_frame()
       {
	        ?>
	        <script type="text/javascript">
	        <!--
	        function js_confirm_upload(tform, filefield)
	        {
		        if (filefield.value == "")
		        {
			        return confirm("<?php echo construct_phrase($this->vbphrase['you_did_not_specify_a_file_to_upload'], '" + tform.serverfile.value + "'); ?>");
		        }
		        return true;
	        }

	        // -->
	        </script>
	        <?php

                print_cp_header($this->vbphrase['vbgamez_framemanager']);

                print_form_header('vbgamez_admin', 'doaddframe');

                print_table_header($this->vbphrase['vbgamez_framemgr_add']);

                print_input_row($this->vbphrase['title'], 'name');

                print_textarea_row($this->vbphrase['description'], 'description');

                print_textarea_row($this->vbphrase['vbgamez_framemgr_code'], 'code');

                print_textarea_row($this->vbphrase['vbgamez_framemgr_codeplayers'], 'codeplayers');

                print_textarea_row($this->vbphrase['vbgamez_framemgr_codenoplayers'], 'codenoplayers');
                
                print_input_row($this->vbphrase['width'].$this->vbphrase['vbgamez_framemanager_without_modules'], 'width');

                print_input_row($this->vbphrase['height'].$this->vbphrase['vbgamez_framemanager_without_modules'], 'height');

                print_input_row($this->vbphrase['vbgamez_admincp_poryadok'], 'order');

                print_submit_row($this->vbphrase['save']);

	        print_form_header('vbgamez_admin', 'uploadframe', 1, 1, 'uploadform" onsubmit="return js_confirm_upload(this, this.framefile);');

	        print_table_header($this->vbphrase['vbgamez_import_frame_xml_file']);

	        print_upload_row($this->vbphrase['upload_xml_file'], 'framefile', 999999999);

	        print_input_row($this->vbphrase['import_xml_file'], 'serverfile', './vbgamez-frame.xml');

	        print_submit_row($this->vbphrase['import']);

       }
           

       function create_stylevar($frameid, $stylevarinfo = '')
       {

                print_cp_header($this->vbphrase['vbgamez_framemanager']);

                print_form_header('vbgamez_admin', iif(!$stylevarinfo, 'doaddstylevar', 'doeditstylevar'));

                print_table_header($this->vbphrase['vbgamez_framemgr_add_stylevar']);

                print_input_row($this->vbphrase['vbgamez_variable'], 'variable', $stylevarinfo['variable']);

                print_input_row($this->vbphrase['title'], 'title', $stylevarinfo['title']);

                print_textarea_row($this->vbphrase['description'], 'description', $stylevarinfo['description']);

                print_select_row($this->vbphrase['type'], 'type', array('' => '---', 'checkbox' => 'CheckBox', 'colorpicker' => 'Color', 'input' => 'Text'), $stylevarinfo['type']);

                print_input_row($this->vbphrase['vbgamez_framemgr_add_height'], 'addheight', $stylevarinfo['addheight']);

                print_input_row($this->vbphrase['vbgamez_framemgr_add_width'], 'addwidth', $stylevarinfo['addwidth']);

                print_input_row($this->vbphrase['default'].$this->vbphrase['vbgamez_framemgr_default_note'], 'default', $stylevarinfo['default']);

                print_input_row($this->vbphrase['vbgamez_admincp_poryadok'], 'order', iif(!$stylevarinfo['order'], 0, $stylevarinfo['order']));

                if($stylevarinfo)
                {
                           print_yes_no_row($this->vbphrase['enabled'], 'enabled', $stylevarinfo['enabled']);
                               
                }

                construct_hidden_code('id', $frameid);

                print_submit_row($this->vbphrase['save']);

       }
                     
       function do_add_frame($name, $description, $order, $code, $codeplayers, $codenoplayers, $width, $height, $is_configure)
       {
               $this->registry->db->query("INSERT INTO " . TABLE_PREFIX . "vbgamez_frame
                              (`name`, `description`, `order`, `enabled`, `code`, `codeplayers`, `codenoplayers`, `width`, `height`, `is_configure`) VALUES
                              (".$this->registry->db->sql_prepare($name).", ".$this->registry->db->sql_prepare($description).", ".$this->registry->db->sql_prepare($order).", '1', ".$this->registry->db->sql_prepare($code).", ".$this->registry->db->sql_prepare($codeplayers).", ".$this->registry->db->sql_prepare($codenoplayers)."
                               , ".$this->registry->db->sql_prepare($width).", ".$this->registry->db->sql_prepare($height).", ".$this->registry->db->sql_prepare($is_configure).")");

               return $this->registry->db->insert_id();
       }
       
       function do_add_stylevar($frameid, $title,  $description, $order, $default, $variable, $type, $addwidth, $addheight)
       {
               $this->registry->db->query("INSERT INTO " . TABLE_PREFIX . "vbgamez_frame_stylevar
                              (`frameid`, `title`,  `description`, `order`, `default`, `variable`, `enabled`, `type`, `addwidth`, `addheight`) VALUES
                              (".$this->registry->db->sql_prepare($frameid).", ".$this->registry->db->sql_prepare($title).", ".$this->registry->db->sql_prepare($description).", ".$this->registry->db->sql_prepare($order).", ".$this->registry->db->sql_prepare($default).", ".$this->registry->db->sql_prepare($variable).", 1, ".$this->registry->db->sql_prepare($type).", ".$this->registry->db->sql_prepare($addwidth).", ".$this->registry->db->sql_prepare($addheight).")");
                    
       }

       function edit_frame($frameid)
       {
                $frameinfo = $this->framecache[$frameid];
                
                print_cp_header($this->vbphrase['vbgamez_framemanager']);

                print_form_header('vbgamez_admin', 'doeditframe');

                print_table_header($this->vbphrase['vbgamez_framemgr_edit'].' (ID: ' . $frameinfo['frameid'] . ')');

                print_input_row($this->vbphrase['title'], 'name', $frameinfo['name']);

                print_textarea_row($this->vbphrase['description'], 'description', $frameinfo['description']);

                print_textarea_row($this->vbphrase['vbgamez_framemgr_code'], 'code', $frameinfo['code']);
                
                print_textarea_row($this->vbphrase['vbgamez_framemgr_codeplayers'], 'codeplayers', $frameinfo['codeplayers']);

                print_textarea_row($this->vbphrase['vbgamez_framemgr_codenoplayers'], 'codenoplayers', $frameinfo['codenoplayers']);
                
                print_input_row($this->vbphrase['width'].$this->vbphrase['vbgamez_framemanager_without_modules'], 'width', $frameinfo['width']);

                print_input_row($this->vbphrase['height'].$this->vbphrase['vbgamez_framemanager_without_modules'], 'height', $frameinfo['height']);

                print_input_row($this->vbphrase['vbgamez_admincp_poryadok'], 'order', $frameinfo['order']);
                
                print_checkbox_row($this->vbphrase['vbgamez_framemgr_enabled'], 'enabled', $frameinfo['enabled']);
                               
                print_yes_no_row($this->vbphrase['vbgamez_framemgr_is_configure'], 'is_configure', $frameinfo['is_configure']);


                construct_hidden_code('id', $frameid);
                
                print_submit_row($this->vbphrase['save']);  

                print_table_footer();

                $this->stylevars_list($frameid);             
       }
         
       function do_edit_frame($id, $name, $description, $order, $enabled, $code, $codeplayers, $codenoplayers, $width, $height, $is_configure)
       {
               $this->registry->db->query("UPDATE " . TABLE_PREFIX . "vbgamez_frame SET 
                              `name` = ".$this->registry->db->sql_prepare($name).", `description` = ".$this->registry->db->sql_prepare($description).", `order` = ".$this->registry->db->sql_prepare($order).", `enabled` = ".$this->registry->db->sql_prepare($enabled).", `code` = ".$this->registry->db->sql_prepare($code).", `codeplayers` = ".$this->registry->db->sql_prepare($codeplayers).", `codenoplayers` = ".$this->registry->db->sql_prepare($codenoplayers)."
                               , `width` = ".$this->registry->db->sql_prepare($width).", `height` = ".$this->registry->db->sql_prepare($height).", `is_configure` = ".$this->registry->db->sql_prepare($is_configure)."
                               WHERE frameid = '" . $id . "'");
                                 
       }    
       
       function do_edit_stylevar($id, $title, $description, $order, $default, $variable, $enabled, $type, $addwidth, $addheight)
       {
               $this->registry->db->query("UPDATE " . TABLE_PREFIX . "vbgamez_frame_stylevar SET 
                              `title` = ".$this->registry->db->sql_prepare($title).", `description` = ".$this->registry->db->sql_prepare($description).", `order` = ".$this->registry->db->sql_prepare($order).", `default` = ".$this->registry->db->sql_prepare($default).", `variable` = ".$this->registry->db->sql_prepare($variable).", `enabled` = ".$this->registry->db->sql_prepare($enabled)." , `type` = ".$this->registry->db->sql_prepare($type)." , `addwidth` = ".$this->registry->db->sql_prepare($addwidth)." , `addheight` = ".$this->registry->db->sql_prepare($addheight)." 
                               WHERE stylevarid = '" . $id . "'");
                                 
       }   

       function delete_frame($id)
       {

               $this->registry->db->query("DELETE FROM " . TABLE_PREFIX . "vbgamez_frame
                               WHERE frameid = '" . $id . "'");

               $this->registry->db->query("DELETE FROM " . TABLE_PREFIX . "vbgamez_frame_stylevar
                               WHERE frameid = '" . $id . "'");


               $this->registry->db->query("DELETE FROM " . TABLE_PREFIX . "template
                               WHERE title = 'vbgamez_frame_" . $this->registry->db->escape_string($id) . "'");

               $this->registry->db->query("DELETE FROM " . TABLE_PREFIX . "template
                               WHERE title = 'vbgamez_frame_" . $this->registry->db->escape_string($id) . "_players'");

               $this->registry->db->query("DELETE FROM " . TABLE_PREFIX . "template
                               WHERE title = 'vbgamez_frame_" . $this->registry->db->escape_string($id) . "_noplayers'");


                       print_cp_header($this->vbphrase['vbgamez_framemanager']);

                       build_all_styles(0, 0, 'vbgamez_admin.php?do=framemanager');
                       print_table_footer();

       }      
              
       function delete_stylevar($id)
       {
               
               $this->registry->db->query("DELETE FROM " . TABLE_PREFIX . "vbgamez_frame_stylevar
                               WHERE stylevarid = '" . $id . "'");
       }      
       
       function save_order($info)
       {
	                foreach($info AS $id => $order)
	                {
                            $order = intval($order);
                            $id = intval($id);
                            $this->registry->db->query("UPDATE " . TABLE_PREFIX . "vbgamez_frame SET `order` = $order WHERE frameid = $id");
	                } 

                        $this->build_frame_datastore();              
       }  
       
       function enable_frame($id)
       {
               $this->registry->db->query("UPDATE " . TABLE_PREFIX . "vbgamez_frame SET enabled = 1
                               WHERE frameid = '" . $id . "'");
                                 
       }     
       

       function enable_stylevar($id)
       {
               $this->registry->db->query("UPDATE " . TABLE_PREFIX . "vbgamez_frame_stylevar SET enabled = 1
                               WHERE stylevarid = '" . $id . "'");
                                 
       }     

       function disable_frame($id)
       {
               $this->registry->db->query("UPDATE " . TABLE_PREFIX . "vbgamez_frame SET enabled = 0
                               WHERE frameid = '" . $id . "'");
                                 
       }

       function disable_stylevar($id)
       {
               $this->registry->db->query("UPDATE " . TABLE_PREFIX . "vbgamez_frame_stylevar SET enabled = 0
                               WHERE stylevarid = '" . $id . "'");
                                 
       }

       function verify_frame($frameid)
       {
             $select_frameinfo = $this->registry->db->query_first("SELECT * FROM " . TABLE_PREFIX . "vbgamez_frame WHERE frameid = '" . intval($frameid) . "'");

             return $select_frameinfo;                  
       } 

       function verify_stylevar($stylevarid)
       {
             $select_frameinfo = $this->registry->db->query_first("SELECT * FROM " . TABLE_PREFIX . "vbgamez_frame_stylevar WHERE stylevarid = '" . intval($stylevarid) . "'");

             return $select_frameinfo;                  
       } 

       function save_frame_config($update, $frameid, $configid, $border, $background, $tablebackground, $text, $texttitle, $scorecolor, $showmap, $showplayers, $size, $players)
       {
               if($update)
               {
                      $this->registry->db->query("INSERT INTO " . TABLE_PREFIX . "vbgamez_frame_config SET
                                                  `frameid' = $frameid,
                                                  `border` = ".$this->registry->db->sql_prepare($border).",
                                                  `background` = ".$this->registry->db->sql_prepare($background).",
                                                  `tablebackground` = ".$this->registry->db->sql_prepare($tablebackground).",
                                                  `text` = ".$this->registry->db->sql_prepare($text).",
                                                  `texttitle` = ".$this->registry->db->sql_prepare($texttitle).",
                                                  `scorecolor` = ".$this->registry->db->sql_prepare($scorecolor).",
                                                  `showmap` = ".$this->registry->db->sql_prepare($showmap).",
                                                  `showplayers` = ".$this->registry->db->sql_prepare($showplayers).",
                                                  `size` = ".$this->registry->db->sql_prepare($size).",
                                                  `players` = ".$this->registry->db->sql_prepare($players)."
                                                   WHERE configid = $configid");
               }else{
                      $this->registry->db->query("INSERT INTO " . TABLE_PREFIX . "vbgamez_frame_config
                              (`frameid` ,`border` ,`background` ,`tablebackground` ,`text` ,`texttitle` ,`scorecolor` ,`showmap` ,`showplayers` ,`size` , `players` ) VALUES
                              (".$this->registry->db->sql_prepare($frameid).",
                               ".$this->registry->db->sql_prepare($border).",
                               ".$this->registry->db->sql_prepare($background).", 
                               ".$this->registry->db->sql_prepare($tablebackground).", 
                               ".$this->registry->db->sql_prepare($text).", 
                               ".$this->registry->db->sql_prepare($texttitle).", 
                               ".$this->registry->db->sql_prepare($scorecolor).", 
                               ".$this->registry->db->sql_prepare($showmap).", 
                               ".$this->registry->db->sql_prepare($showplayers).", 
                               ".$this->registry->db->sql_prepare($size).", 
                               ".$this->registry->db->sql_prepare($players).")");
               }
       }

       function frame_list()
       {
       	     print_cp_header($this->vbphrase['vbgamez_framemanager']);

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
				     	     case 'edit': page = "vbgamez_admin.php?do=editframe&id="; break;
				     	     case 'delete': page = "vbgamez_admin.php?do=deleteframe&id="; break;
				     	     case 'disable': page = "vbgamez_admin.php?do=disableframe&id="; break;
				     	     case 'enable': page = "vbgamez_admin.php?do=enableframe&id="; break;
				     	     case 'download': page = "vbgamez_admin.php?do=downloadframe&id="; break;
				     	     default: return;
			     	     }
			     	     document.cpform.reset();
			     	     jumptopage = page + sid + "&s=<?php echo $this->registry->session->vars['sessionhash']; ?>";
			     	     window.location = jumptopage;
		     	     }
		     	     else
		     	     {
			     	     alert('<?php echo addslashes_js('Invalid Action'); ?>');
		     	     }
	     	     }
	     	     </script>
	     	     <?php
	     	     print_form_header('vbgamez_admin', 'frameorder');

	     	     print_table_header($this->vbphrase['vbgamez_framemanager'], 7); 

	     	     print_cells_row(array($this->vbphrase['title'], $this->vbphrase['description'], $this->vbphrase['vbgamez_admincp_poryadok'], $this->vbphrase['width'], $this->vbphrase['height'], $this->vbphrase['vbgamez_framemgr_is_configure2'], $this->vbphrase['controls']), 1);

	     	     $i = 0;

                     if(!empty($this->framecache))
                     {

	     	     foreach($this->framecache AS $frame)
	     	     {
		     	     $name = htmlspecialchars_uni($frame['name']);
		     	     $description = htmlspecialchars_uni($frame['description']);

		     	     if (!$frame['enabled'])
		     	     {
			     	     $name = "<strike>$name</strike>";
		     	     }

		     	     $options['edit'] = $this->vbphrase['edit'];

		     	     if ($frame['enabled'])
		     	     {
			     	     $options['disable'] = $this->vbphrase['disable'];
		     	     }
		     	     else
		     	     {
			     	     $options['enable'] = $this->vbphrase['enable'];
		     	     }
		     	     $options['download'] = $this->vbphrase['export'];
		     	     $options['delete'] = $this->vbphrase['delete'];
         	     $i++;

		     	     print_cells_row(array(
			     	     $name,
           	     $description,
                        '<center><input name="displayorder[' . $frame['frameid'] . ']" value="'.$frame['order'].'" class="bginput" size="4" style="text-align: right;" type="text"></center>',
                     $frame['width'], $frame['height'], iif($frame['is_configure'], $this->vbphrase['yes'], $this->vbphrase['no']),
			     	     "<div align=\"" . vB_vBGamez::fetch_stylevar('right') . "\">
				     	     <select name=\"s$frame[frameid]\" id=\"prodsel$i\" onchange=\"js_page_jump($i, '$frame[frameid]')\" class=\"bginput\">
				     	     	" . construct_select_options($options) . "
				     	     </select>&nbsp;<input type=\"button\" class=\"button\" value=\"" . $this->vbphrase['go'] . "\" onclick=\"js_page_jump($i, '$frame[frameid]');\" />
			     	     </div>"
		     	     ), false, '', -2);
	     	     }
                    }
		     	     print_cells_row(array(

		     	     '<input type="button" class="button" value="' . $this->vbphrase['vbgamez_framemgr_add'] . '" onclick="window.location=\'vbgamez_admin.php?' . $this->registry->session->vars['sessionurl'] . 'do=addframe\';" />', '', '',  '', '', '',  
		     	     ($i ? '<div align="' . vB_vBGamez::fetch_stylevar('right') . '"><input type="submit" class="button" accesskey="s" value="' . $this->vbphrase['save_display_order'] . '" />' : '&nbsp;'))
	     	     );


	     	    print_table_footer();
         }
         
         function build_frame_template($name, $template, $template_un)
         {
	         global $db, $vbulletin;
		 $db->show_errors();
		 $getTemplate = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "template WHERE title = 'vbgamez_frame_" . $db->escape_string($name) . "' AND styleid = '-1'");
		 if($getTemplate['title'])
		 {
	         	$db->query_write("
		         REPLACE INTO " . TABLE_PREFIX . "template SET
			         styleid = -1,
			         title = 'vbgamez_frame_" . $db->escape_string($name) . "',
			         template = '" . $db->escape_string($template) . "',
			         template_un = '" . $db->escape_string($template_un) . "',
			         templatetype = 'template',
			         dateline = " . TIMENOW . ",
			         username = '" . $db->escape_string($vbulletin->userinfo['username']) . "',
			         version = '" . $db->escape_string($vbulletin->options['templateversion']) . "',
			         product = 'vbgamez'
	         	");
		}else{

	         	$db->query_write("
		         INSERT INTO " . TABLE_PREFIX . "template SET
			         styleid = -1,
			         title = 'vbgamez_frame_" . $db->escape_string($name) . "',
			         template = '" . $db->escape_string($template) . "',
			         template_un = '" . $db->escape_string($template_un) . "',
			         templatetype = 'template',
			         dateline = " . TIMENOW . ",
			         username = '" . $db->escape_string($vbulletin->userinfo['username']) . "',
			         version = '" . $db->escape_string($vbulletin->options['templateversion']) . "',
			         product = 'vbgamez'
	         	");
		}
		$db->hide_errors();

         }         

       function stylevars_list(&$frameid)
       {

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
				     	     case 'edit': page = "vbgamez_admin.php?do=editstylevar&id="; break;
				     	     case 'delete': page = "vbgamez_admin.php?do=deletestylevar&id="; break;
				     	     case 'disable': page = "vbgamez_admin.php?do=disablestylevar&id="; break;
				     	     case 'enable': page = "vbgamez_admin.php?do=enablestylevar&id="; break;
				     	     default: return;
			     	     }
			     	     //document.cpform.reset();
			     	     jumptopage = page + sid + "&s=<?php echo $this->registry->session->vars['sessionhash']; ?>";
			     	     window.location = jumptopage;
		     	     }
		     	     else
		     	     {
			     	     alert('<?php echo addslashes_js('Invalid Action'); ?>');
		     	     }
	     	     }
	     	     </script>
	     	     <?php

	     	     print_table_start();
	     	     print_description_row($this->vbphrase['vbgamez_frame_stylevar_description']);
	     	     print_table_footer(2, '', '', false);


	     	     print_form_header('vbgamez_admin', 'stylevarorder');

	     	     print_table_header($this->vbphrase['vbgamez_frame_stylevars'], 6); 

	     	     print_cells_row(array($this->vbphrase['title'], $this->vbphrase['description'], $this->vbphrase['vbgamez_admincp_poryadok'], $this->vbphrase['default'], $this->vbphrase['vbgamez_variable'], $this->vbphrase['controls']), 1);

	     	     $i = 0;

                     global $db;

                     $fetch_stylevars = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez_frame_stylevar WHERE frameid = " . $frameid . " ORDER BY `order`");
 
	     	     while($frame = $db->fetch_array($fetch_stylevars))
	     	     {
                             $options = '';

		     	     $name = htmlspecialchars_uni($frame['title']);
		     	     $description = htmlspecialchars_uni($frame['description']);

		     	     if (!$frame['enabled'])
		     	     {
			     	     $name = "<strike>$name</strike>";
		     	     }

		     	     $options['edit'] = $this->vbphrase['edit'];

		     	     if ($frame['enabled'])
		     	     {
			     	     $options['disable'] = $this->vbphrase['disable'];
		     	     }
		     	     else
		     	     {
			     	     $options['enable'] = $this->vbphrase['enable'];
		     	     }
                             if(VBG_IS_VB4)
                             {
                                         $tpl_code = '{vb:raw vbg_style.' . $frame['variable'] . '}';
                             }else{
                                         $tpl_code = '$vbg_style[' . $frame['variable'] . ']';
                             }

		     	     $options['delete'] = $this->vbphrase['delete'];
         	     $i++;

		     	     print_cells_row(array(
			     	     $name,
           	     $description,
                        '<center><input name="displayorder[' . $frame['stylevarid'] . ']" value="'.$frame['order'].'" class="bginput" size="4" style="text-align: right;" type="text"></center>',
                     $frame['default'], $tpl_code, 
			     	     "<div align=\"" . vB_vBGamez::fetch_stylevar('right') . "\">
				     	     <select name=\"s$frame[stylevarid]\" id=\"prodsel$i\" onchange=\"js_page_jump($i, '$frame[stylevarid]')\" class=\"bginput\">
				     	     	" . construct_select_options($options) . "
				     	     </select>&nbsp;<input type=\"button\" class=\"button\" value=\"" . $this->vbphrase['go'] . "\" onclick=\"js_page_jump($i, '$frame[stylevarid]');\" />
			     	     </div>"
		     	     ), false, '', -2);
	     	     }
                             construct_hidden_code('frameid', $frameid);

		     	     print_cells_row(array(

		     	     '<input type="button" class="button" value="' . $this->vbphrase['vbgamez_frame_add_stylevar'] . '" onclick="window.location=\'vbgamez_admin.php?' . $this->registry->session->vars['sessionurl'] . 'do=addstylevar&frameid=' . $frameid . '\';" />', '', '',  '', '', 
		     	     ($i ? '<div align="' . vB_vBGamez::fetch_stylevar('right') . '"><input type="submit" class="button" accesskey="s" value="' . $this->vbphrase['save_display_order'] . '" />' : '&nbsp;'))
	     	     );

	     	    print_table_footer();
         }

         function download_frame($frameid)
         {
	          if (function_exists('set_time_limit') AND !SAFEMODE)
	          {
		          @set_time_limit(1200);
	          }

	          $frame = $this->registry->db->query_first("SELECT * FROM " . TABLE_PREFIX . "vbgamez_frame WHERE frameid = ".intval($frameid)."");

	          $title = str_replace('"', '\"', $frame['title']);

	          $getstylevars = $this->registry->db->query_read("SELECT * FROM " . TABLE_PREFIX . "vbgamez_frame_stylevar WHERE frameid = ".intval($frameid)."");

	          while ($stylevar = $this->registry->db->fetch_array($getstylevars))
	          {
		          $stylevars[$stylevar['stylevarid']] = $stylevar;
	          }

                  unset($stylevar);

	          $this->registry->db->free_result($stylevar);

	          if (empty($stylevars))
	          {
		         // print_stop_message('download_contains_no_customizations');
	          }

	          require_once(DIR . '/includes/class_xml.php');
	          $xml = new vB_XML_Builder($this->registry);

	          $xml->add_group('frame', array('name' => $frame['name'], 'description' => $frame['description'], 'width' => $frame['width'], 'height' => $frame['height'], 'is_configure' => $frame['is_configure']));
		  $xml->add_tag('framecode', $frame['code']);
		  $xml->add_tag('framecodeplayers', $frame['codeplayers']);
		  $xml->add_tag('framecodenoplayers', $frame['codenoplayers']);

                  if($stylevars)
                  {
	                   foreach ($stylevars AS $stylevar)
	                   {

			          $attributes = array(
				          'title' => $stylevar['title'],
				          'description' => $stylevar['description'],
				          'order' => $stylevar['order'],
				          'default' => $stylevar['default'],
				          'variable' => $stylevar['variable'],
				          'enabled' => $stylevar['enabled'],
				          'type' => $stylevar['type'],
				          'addwidth' => $stylevar['addwidth'],
				          'addheight' => $stylevar['addheight'],
			          );


			          $xml->add_tag('stylevar', $stylevar['stylevarid'], $attributes, true);

	                   }
                  }

	          $xml->close_group();

	          $doc = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n\r\n";

	          $doc .= $xml->output();
	          $xml = null;

	          require_once(DIR . '/includes/functions_file.php');
	          file_download($doc, 'vbgamez-frame-' . $frame['frameid'].'.xml', 'text/xml');
         }

         function upload_frame($xml, $hide_messages = false)
         {
	          require_once(DIR . '/includes/class_xml.php');
	          require_once(DIR . '/includes/functions_misc.php');

	          $xmlobj = new vB_XML_Parser($xml);
	          if ($xmlobj->error_no == 1)
	          {
			          print_dots_stop();
			          print_stop_message('no_xml_and_no_path');
	          }
	          else if ($xmlobj->error_no == 2)
	          {
			          print_dots_stop();
			          print_stop_message('please_ensure_x_file_is_located_at_y', 'vbulletin-frame.xml', $GLOBALS['path']);
	          }

	          if(!$arr =& $xmlobj->parse())
	          {
		          print_dots_stop();
		          print_stop_message('xml_error_x_at_line_y', $xmlobj->error_string(), $xmlobj->error_line());
	          }

                  if(empty($arr['name']))
	          {
		          print_dots_stop();
		          print_stop_message('xml_error_x_at_line_y', 'Invalid frame XML file', 1);
	          }
      


                  $frameid = $this->do_add_frame($arr['name'], $arr['description'], 0, $arr['framecode'], $arr['framecodeplayers'], $arr['framecodenoplayers'], $arr['width'], $arr['height'], $arr['is_configure']);

		  if(!empty($arr['stylevar']))
	          {
                  	foreach($arr['stylevar'] AS $stylevar)
                  	{
                         	$this->do_add_stylevar($frameid, $stylevar['title'], $stylevar['description'], $stylevar['order'], $stylevar['default'], $stylevar['variable'], $stylevar['type'], $stylevar['addwidth'], $stylevar['addheight']);
                  	}
		  }

                  $this->build_frame_template($frameid, compile_template($arr['framecode']), $arr['framecode']);

                  $this->build_frame_template($frameid.'_players', compile_template($arr['framecodeplayers']), $arr['framecodeplayers']);

                  $this->build_frame_template($frameid.'_noplayers', compile_template($arr['framecodenoplayers']), $arr['framecodenoplayers']);

                  build_all_styles(0, 0, 'vbgamez_admin.php?do=framedone&gotoedit=1&id='.$frameid);
         }
}