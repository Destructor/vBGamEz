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
 * VBGamEz - менеджер юзербаров
 *
 * @package vBGamEz
 * @author GiveMeABreak aka Developer
 * @version $Revision: 140 $
 * @copyright GiveMeABreak
 */
 
 class vBGamEz_Userbar_Manager
 {
       var $registry;
       var $vbphrase;

       function vBGamEz_Userbar_Manager($vbulletin)
       {
                $this->registry = $vbulletin;
       }

       function bootstrap($vbphrase)
       {
                $get_userbars = $this->registry->db->query("SELECT * FROM " . TABLE_PREFIX . "vbgamez_userbar WHERE userid = 0 ORDER BY `order`");

                while($userbar = $this->registry->db->fetch_array($get_userbars))
                {
                         $this->userbarcache[$userbar['userbarid']] = $userbar;
                }
                
                $this->vbphrase = $vbphrase;
       }

       function create_userbar()
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

                print_cp_header($this->vbphrase['vbgamez_userbarmgr']);

                print_form_header('vbgamez_admin', 'doadduserbar');

                print_table_header($this->vbphrase['vbgamez_userbarmgr_add']);

                print_input_row($this->vbphrase['vbgamez_userbarmgr_name'], 'name');

                print_textarea_row($this->vbphrase['vbgamez_userbarmgr_description'], 'description');
                
                print_input_row($this->vbphrase['vbgamez_userbarmgr_order'], 'order');

                print_submit_row($this->vbphrase['save']);

	        print_form_header('vbgamez_admin', 'upload', 1, 1, 'uploadform" onsubmit="return js_confirm_upload(this, this.userbarfile);');

	        print_table_header($this->vbphrase['vbgamez_import_userbar_xml_file']);

	        print_upload_row($this->vbphrase['upload_xml_file'], 'userbarfile', 999999999);

	        print_input_row($this->vbphrase['import_xml_file'], 'serverfile', './vbgamez-userbar.xml');

	        print_submit_row($this->vbphrase['import']);

       }
                                
       function do_add_userbar($name, $description, $order, $userid = 0)
       {
               $this->registry->db->query("INSERT INTO " . TABLE_PREFIX . "vbgamez_userbar
                              (`name`, `description`, `order`, `enabled`, `userid`) VALUES
                              (".$this->registry->db->sql_prepare($name).", ".$this->registry->db->sql_prepare($description).", ".$this->registry->db->sql_prepare($order).", '1', ".$this->registry->db->sql_prepare($userid).")");
                    
       }
       

       function edit_userbar($userbarid)
       {
                $userbarinfo = $this->userbarcache[$userbarid];
                
                print_cp_header($this->vbphrase['vbgamez_userbarmgr']);

                print_form_header('vbgamez_admin', 'doedituserbar');

                print_table_header($this->vbphrase['vbgamez_userbarmgr_edit'].' (ID: ' . $userbarinfo['userbarid'] . ')');

                print_input_row($this->vbphrase['vbgamez_userbarmgr_name'], 'name', $userbarinfo['name']);

                print_textarea_row($this->vbphrase['vbgamez_userbarmgr_description'], 'description', $userbarinfo['description']);
                
                print_input_row($this->vbphrase['vbgamez_userbarmgr_order'], 'order', $userbarinfo['order']);
                
                print_checkbox_row($this->vbphrase['vbgamez_userbarmgr_enabled'], 'enabled', $userbarinfo['enabled']);
                               
                construct_hidden_code('id', $userbarid);
                
                print_submit_row($this->vbphrase['save']);
       }
         
       function do_edit_userbar($id, $name, $description, $order, $enabled)
       {
               $this->registry->db->query("UPDATE " . TABLE_PREFIX . "vbgamez_userbar SET 
                              `name` = ".$this->registry->db->sql_prepare($name).", `description` = ".$this->registry->db->sql_prepare($description).", `order` = ".$this->registry->db->sql_prepare($order).", `enabled` = ".$this->registry->db->sql_prepare($enabled)."
                               WHERE userbarid = '" . $id . "'");
                                 
       }    
       
       function delete_userbar($id)
       {

               $this->registry->db->query("DELETE FROM " . TABLE_PREFIX . "vbgamez_userbar
                               WHERE userbarid = '" . $id . "'");
                               
               $this->registry->db->query("DELETE FROM " . TABLE_PREFIX . "vbgamez_userbar_config
                               WHERE userbarid = '" . $id . "'");
       }      
       
       function save_order($info)
       {
	                foreach($info AS $id => $order)
	                {
                            $order = intval($order);
                            $id = intval($id);
                            $this->registry->db->query("UPDATE " . TABLE_PREFIX . "vbgamez_userbar SET `order` = $order WHERE userbarid = $id");
	                }               
       }  
       
       function enable_userbar($id)
       {
               $this->registry->db->query("UPDATE " . TABLE_PREFIX . "vbgamez_userbar SET enabled = 1
                               WHERE userbarid = '" . $id . "'");
                                 
       }     
       
       function disable_userbar($id)
       {
               $this->registry->db->query("UPDATE " . TABLE_PREFIX . "vbgamez_userbar SET enabled = 0
                               WHERE userbarid = '" . $id . "'");
                                 
       }

       function replace_games($type, $value)
       {
			// new 
            return vB_vBGamez::replaceGameTypesForAddons($type, $value);
       }


       function fetch_all_game_types($name, $value)
       {
             
             $select_obj = '<select name="'.$name.'" multiple="multiple">';
             $select_obj .= '<option value="global" '.iif(in_array('global', $value), 'selected="selected"').'>' . $this->vbphrase['vbgamez_alltypes'].'</option>';

             foreach(vbgamez_type_list() AS $type => $name)
             {
                   $options .= '<option value="'.$type.'" '.iif(in_array($type, $value), 'selected="selected"').'>'.$name.'</option>';
 
                   $adv_games = vB_vBGamez::fetch_additional_game_type($type);

                   if(!empty($adv_games))
                   {
                       foreach($adv_games AS $atype => $aname)
                       {
                             $options .= '<option value="'.$this->replace_games($type, $atype).'" '.iif(in_array($this->replace_games($type, $atype), $value), 'selected="selected"').'>-- '.$aname.'</option>';
                       }
                   }
             }

             $select_obj .= $options."</select>";

            return $select_obj;
       }
       
       function configure_userbar($userbarid)
       {
                $userbarinfo = $this->userbarcache[$userbarid];

                print_cp_header($this->vbphrase['vbgamez_userbarmgr']);

                print_form_header('vbgamez_admin', 'doconfigureuserbar');

                print_table_header($this->vbphrase['vbgamez_userbarmgr_configure_userbar'].' (ID: ' . $userbarinfo['userbarid'] . ')');

	        print_cells_row(array($this->vbphrase['vbgamez_userbarmgr_game_types'], $this->fetch_all_game_types('fieldname[]', explode(',', $userbarinfo['fieldname']))), false, '', -2);

                print_input_row($this->vbphrase['vbgamez_userbarmgr_background'], 'background', $userbarinfo['background']);

                print_input_row($this->vbphrase['vbgamez_userbarmgr_default_textcolor'], 'textcolor', $userbarinfo['textcolor']);
                
                print_input_row($this->vbphrase['vbgamez_userbarmgr_default_font'], 'font', $userbarinfo['font']);

                print_input_row($this->vbphrase['vbgamez_userbarmgr_default_fontsize'], 'fontsize', $userbarinfo['fontsize']);

                construct_hidden_code('id', $userbarid);
                
                print_submit_row($this->vbphrase['save']);

                print_form_header('vbgamez_admin', 'save');

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
	              			case 'edit': page = "vbgamez_admin.php?do=edituserbarcfg&id="; break;
	              			case 'delete': page = "vbgamez_admin.php?do=deleteuserbarcfg&id="; break;
	              			case 'disable': page = "vbgamez_admin.php?do=disableuserbarcfg&id="; break;
	              			case 'enable': page = "vbgamez_admin.php?do=enableuserbarcfg&id="; break;
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
	
                print_table_header($this->vbphrase['vbgamez_userbarmgr_locations'], 8); 

                print_cells_row(array($this->vbphrase['vbgamez_userbarmgr_text'], $this->vbphrase['vbgamez_userbarmgr_radius'], $this->vbphrase['vbgamez_userbarmgr_repeat_x'], $this->vbphrase['vbgamez_userbarmgr_repeat_y'], $this->vbphrase['vbgamez_userbarmgr_font'], $this->vbphrase['vbgamez_userbarmgr_fontsize'], $this->vbphrase['vbgamez_userbarmgr_fontcolor'],  $this->vbphrase['controls']), 1);

                $i = 0;

                $select_configinfo = $this->registry->db->query("SELECT * FROM " . TABLE_PREFIX . "vbgamez_userbar_config WHERE userbarid = '" . $userbarid . "'");

                while ($config = $this->registry->db->fetch_array($select_configinfo))
                {
		                $text = htmlspecialchars_uni($config['text']);

		                if (!$config['enabled'])
		                {
			                $text = "<strike>$text</strike>";
		                }
		                
		                $options['edit'] = $this->vbphrase['edit'];
		                
		                if ($config['enabled'])
		                {
			                $options['disable'] = $this->vbphrase['disable'];
		                }
		                else
		                {
			                $options['enable'] = $this->vbphrase['enable'];
		                }

		                $options['delete'] = $this->vbphrase['delete'];

                    $i++;

		                print_cells_row(array(
			                $text,
                      iif($config['radius'], $config['radius'], '--'),
                      $config['repeat_x'],
                      $config['repeat_y'],
                      iif($config['font'], $config['font'], '--'),
                      iif($config['fontsize'],$config['fontsize'], '--'),
                      iif($config['fontcolor'], $config['fontcolor'], '--'),
                      
			                "<div align=\"" . vB_vBGamez::fetch_stylevar('right') . "\">
			                	<select name=\"s$config[configid]\" id=\"prodsel$i\" onchange=\"js_page_jump($i, '$config[configid]')\" class=\"bginput\">
				                	" . construct_select_options($options) . "
				                </select>&nbsp;<input type=\"button\" class=\"button\" value=\"" . $this->vbphrase['go'] . "\" onclick=\"js_page_jump($i, '$config[configid]');\" />
		                	</div>"
		                ), false, '', -2);
	                }
	                
		            print_cells_row(array('<input type="button" class="button" value="' . $this->vbphrase['vbgamez_userbarmgr_add_location'] . '" onclick="window.location=\'vbgamez_admin.php?' . $this->registry->session->vars['sessionurl'] . 'do=adduserbarlocation&id=' . $userbarid . '\';" />', '', '',  '', '',  '', '', ''));

	              print_table_footer();

	print_form_header('', '', 1);

	print_table_header($this->vbphrase['vbgamez_userbarmgr_note_title']);

        $imagesrc = '<center><img src="vbgamez_admin.php?do=testuserbar&sid='.$_REQUEST['id'].'"></center>';

	print_description_row($imagesrc);

	print_table_footer();
 
       }
       
       function do_configure_userbar($id, $background, $textcolor, $font, $fontsize, $fieldname = '')
       {
            if(empty($fieldname))
            {
               if(empty($_POST['fieldname']))
               {
                          $_POST['fieldname'][] = 'global';
               }

               $fieldname = implode(',', $_POST['fieldname']);

               if(in_array('global', $_POST['fieldname']))
               {
                            $fieldname = 'global';
               }
            }

            $this->registry->db->query("UPDATE " . TABLE_PREFIX . "vbgamez_userbar SET 
                              `background` = ".$this->registry->db->sql_prepare($background).", `textcolor` = ".$this->registry->db->sql_prepare($textcolor).", `font` = ".$this->registry->db->sql_prepare($font).", `fontsize` = ".$this->registry->db->sql_prepare($fontsize).", `fieldname` = ".$this->registry->db->sql_prepare($fieldname)."
                               WHERE userbarid = '" . $id . "'");
                                 
       }      
       
       function add_userbar_location($userbarid)
       {
                $userbarinfo = $this->userbarcache[$userbarid];
                
                print_cp_header($this->vbphrase['vbgamez_userbarmgr']);

                print_form_header('vbgamez_admin', 'doadduserbarlocation');

                print_table_header($this->vbphrase['vbgamez_userbarmgr_add_location'].' (ID: ' . $userbarid . ')');

                print_input_row($this->vbphrase['vbgamez_userbarmgr_text_full'], 'text');

                print_input_row($this->vbphrase['vbgamez_userbarmgr_radius_full'], 'radius');

                print_input_row($this->vbphrase['vbgamez_userbarmgr_repeat_x_full'], 'repeat_x');

                print_input_row($this->vbphrase['vbgamez_userbarmgr_repeat_y_full'], 'repeat_y');

                print_input_row($this->vbphrase['vbgamez_userbarmgr_font_full'], 'font');

                print_input_row($this->vbphrase['vbgamez_userbarmgr_fontsize_full'], 'fontsize');

                print_input_row($this->vbphrase['vbgamez_userbarmgr_fontcolor_full'], 'fontcolor');

                print_input_row($this->vbphrase['vbgamez_userbarmgr_width'], 'width');

                construct_hidden_code('id', $userbarid);
                
                print_submit_row($this->vbphrase['save']);
       }      
       
       function do_add_userbar_location($userbarid, $text, $radius, $repeat_x, $repeat_y, $font, $fontsize, $fontcolor, $width = '', $enabled = 1, $isprivew = 0)
       {
                $this->registry->db->query("INSERT INTO " . TABLE_PREFIX . "vbgamez_userbar_config
                               (userbarid, text, radius, repeat_x, repeat_y, font, fontsize, fontcolor, enabled, width, ispreview)
                               VALUES (".$userbarid.", ".$this->registry->db->sql_prepare($text).", ".$this->registry->db->sql_prepare($radius).", ".$this->registry->db->sql_prepare($repeat_x).",
                               ".$this->registry->db->sql_prepare($repeat_y).", ".$this->registry->db->sql_prepare($font).", ".$this->registry->db->sql_prepare($fontsize).", ".$this->registry->db->sql_prepare($fontcolor).", ".$enabled.", ".$this->registry->db->sql_prepare($width).", ".$this->registry->db->sql_prepare($isprivew) . ")");
                               
       }    
       
       function delete_userbar_location($configid)
       {
                $this->registry->db->query("DELETE FROM " . TABLE_PREFIX . "vbgamez_userbar_config WHERE configid = '" . $configid . "'");     
       }    
       
       
       function edit_userbar_location($configid)
       {
                $select_configinfo = $this->registry->db->query("SELECT * FROM " . TABLE_PREFIX . "vbgamez_userbar_config WHERE configid = '" . $configid . "'");
                $configinfo = $this->registry->db->fetch_array($select_configinfo);
                
                print_cp_header($this->vbphrase['vbgamez_userbarmgr']);

                print_form_header('vbgamez_admin', 'doedituserbarlocation');

                print_table_header('' . $this->vbphrase['vbgamez_userbarmgr_location_edit'] . ' (ID: ' . $configid . ')');

                print_input_row($this->vbphrase['vbgamez_userbarmgr_text_full'], 'text', $configinfo['text']);

                print_input_row($this->vbphrase['vbgamez_userbarmgr_radius_full'], 'radius', $configinfo['radius']);

                print_input_row($this->vbphrase['vbgamez_userbarmgr_repeat_x_full'], 'repeat_x', $configinfo['repeat_x']);

                print_input_row($this->vbphrase['vbgamez_userbarmgr_repeat_y_full'], 'repeat_y', $configinfo['repeat_y']);

                print_input_row($this->vbphrase['vbgamez_userbarmgr_font_full'], 'font', $configinfo['font']);

                print_input_row($this->vbphrase['vbgamez_userbarmgr_fontsize_full'], 'fontsize', $configinfo['fontsize']);

                print_input_row($this->vbphrase['vbgamez_userbarmgr_fontcolor_full'], 'fontcolor', $configinfo['fontcolor']);

                print_input_row($this->vbphrase['vbgamez_userbarmgr_width'], 'width', $configinfo['width']);

                print_checkbox_row($this->vbphrase['vbgamez_userbarmgr_enabled'], 'enabled', $configinfo['enabled']);

                construct_hidden_code('configid', $configid);
                
                print_submit_row($this->vbphrase['save']);
       }  
       
       function do_edit_userbar_location($configid, $text, $radius, $repeat_x, $repeat_y, $font, $fontsize, $fontcolor, $enabled, $width = '')
       {
                $this->registry->db->query("UPDATE " . TABLE_PREFIX . "vbgamez_userbar_config SET
                               text = ".$this->registry->db->sql_prepare($text).", radius = ".$this->registry->db->sql_prepare($radius).", repeat_x = ".$this->registry->db->sql_prepare($repeat_x).", repeat_y = ".$this->registry->db->sql_prepare($repeat_y).", font = ".$this->registry->db->sql_prepare($font).", fontsize = ".$this->registry->db->sql_prepare($fontsize).", fontcolor = ".$this->registry->db->sql_prepare($fontcolor).", enabled = ".$this->registry->db->sql_prepare($enabled).", width = ".$this->registry->db->sql_prepare($width)."
                               WHERE configid = '" . $configid . "'");
                               
       }  
       
       function get_userbarid_by_configid($configid)
       {
             $select_userbarinfo = $this->registry->db->query("SELECT userbarid FROM " . TABLE_PREFIX . "vbgamez_userbar_config WHERE configid = '" . $configid . "'");
             $userbarinfo = $this->registry->db->fetch_array($select_userbarinfo);
             return $userbarinfo['userbarid'];                  
       }  
       
       
       function verify_config($configid)
       {
             $select_userbarinfo = $this->registry->db->query("SELECT configid FROM " . TABLE_PREFIX . "vbgamez_userbar_config WHERE configid = '" . intval($configid) . "'");
             $userbarinfo = $this->registry->db->fetch_array($select_userbarinfo);
             return $userbarinfo['configid'];                  
       }  
       
       function verify_userbar($userbarid)
       {
             $select_userbarinfo = $this->registry->db->query("SELECT userbarid FROM " . TABLE_PREFIX . "vbgamez_userbar WHERE userbarid = '" . intval($userbarid) . "'");
             $userbarinfo = $this->registry->db->fetch_array($select_userbarinfo);
             return $userbarinfo['userbarid'];                  
       } 
       
       function disable_location($configid)
       {
              $this->registry->db->query("UPDATE " . TABLE_PREFIX . "vbgamez_userbar_config SET enabled = 0 WHERE configid = '" . $configid . "'");
       }
       
       function enable_location($configid)
       {
              $this->registry->db->query("UPDATE " . TABLE_PREFIX . "vbgamez_userbar_config SET enabled = 1 WHERE configid = '" . $configid . "'");
       }
       
       function userbars_list()
       {
       	     print_cp_header($this->vbphrase['vbgamez_userbarmgr']);

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
				     	     case 'configure': page = "vbgamez_admin.php?do=configureuserbar&id="; break;
				     	     case 'edit': page = "vbgamez_admin.php?do=edituserbar&id="; break;
				     	     case 'delete': page = "vbgamez_admin.php?do=deleteuserbar&id="; break;
				     	     case 'disable': page = "vbgamez_admin.php?do=disableuserbar&id="; break;
				     	     case 'enable': page = "vbgamez_admin.php?do=enableuserbar&id="; break;
				     	     case 'download': page = "vbgamez_admin.php?do=downloaduserbar&id="; break;
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
	     	     print_form_header('vbgamez_admin', 'userbarorder');

	     	     print_table_header($this->vbphrase['vbgamez_userbarmgr'], 4); 

	     	     print_cells_row(array($this->vbphrase['vbgamez_userbarmgr_name'], $this->vbphrase['vbgamez_userbarmgr_description'], $this->vbphrase['vbgamez_userbarmgr_order'], $this->vbphrase['controls']), 1);

	     	     $i = 0;

                     if(!empty($this->userbarcache))
                     {

	     	     foreach($this->userbarcache AS $userbar)
	     	     {
		     	     $name = htmlspecialchars_uni($userbar['name']);
		     	     $description = htmlspecialchars_uni($userbar['description']);

		     	     if (!$userbar['enabled'])
		     	     {
			     	     $name = "<strike>$name</strike>";
		     	     }

		     	     $options['configure'] = $this->vbphrase['vbgamez_userbarmgr_configure'];
		
		     	     $options['edit'] = $this->vbphrase['edit'];

		     	     if ($userbar['enabled'])
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
                        '<center><input name="displayorder[' . $userbar['userbarid'] . ']" value="'.$userbar['order'].'" class="bginput" size="4" style="text-align: right;" type="text"></center>',
			     	     "<div align=\"" . vB_vBGamez::fetch_stylevar('right') . "\">
				     	     <select name=\"s$userbar[userbarid]\" id=\"prodsel$i\" onchange=\"js_page_jump($i, '$userbar[userbarid]')\" class=\"bginput\">
				     	     	" . construct_select_options($options) . "
				     	     </select>&nbsp;<input type=\"button\" class=\"button\" value=\"" . $this->vbphrase['go'] . "\" onclick=\"js_page_jump($i, '$userbar[userbarid]');\" />
			     	     </div>"
		     	     ), false, '', -2);
	     	     }
                    }
		     	     print_cells_row(array(

		     	     '<input type="button" class="button" value="' . $this->vbphrase['vbgamez_userbarmgr_add'] . '" onclick="window.location=\'vbgamez_admin.php?' . $this->registry->session->vars['sessionurl'] . 'do=adduserbar\';" />', '', '',  
		     	     ($i ? '<div align="' . vB_vBGamez::fetch_stylevar('right') . '"><input type="submit" class="button" accesskey="s" value="' . $this->vbphrase['save_display_order'] . '" />' : '&nbsp;'))
	     	     );

	     	    print_table_footer();
         }
         
         function download_userbar($userbarid)
         {
	          if (function_exists('set_time_limit') AND !SAFEMODE)
	          {
		          @set_time_limit(1200);
	          }

	          $userbar = $this->registry->db->query_first("SELECT * FROM " . TABLE_PREFIX . "vbgamez_userbar WHERE userbarid = ".intval($userbarid)."");

	          $title = str_replace('"', '\"', $userbar['title']);

	          $getconfigs = $this->registry->db->query_read("
		          SELECT * FROM " . TABLE_PREFIX . "vbgamez_userbar_config WHERE userbarid = ".intval($userbarid)."
	          ");

	          while ($getconfig = $this->registry->db->fetch_array($getconfigs))
	          {
		          $configs[$getconfig['configid']] = $getconfig;
	          }

                  unset($getconfigs);

	          $this->registry->db->free_result($getconfigs);

	          if (empty($configs))
	          {
		          print_stop_message('download_contains_no_customizations');
	          }

	          require_once(DIR . '/includes/class_xml.php');
	          $xml = new vB_XML_Builder($this->registry);

	          $xml->add_group('userbar', array('name' => $userbar['name'], 'description' => $userbar['description'], 'background' => $userbar['background'], 'textcolor' => $userbar['textcolor'], 'font' => $userbar['font'], 'fontsize' => $userbar['fontsize'], 'fieldname' => $userbar['fieldname']));

	          foreach ($configs AS $config)
	          {

			          $attributes = array(
				          'fontsize' => $config['fontsize'],
				          'radius' => $config['radius'],
				          'repeat_x' => $config['repeat_x'],
				          'repeat_y' => $config['repeat_y'],
				          'fontcolor' => $config['fontcolor'],
				          'font' => $config['font'],
				          'enabled' => $config['enabled'],
				          'width' => $config['width'],
			          );


			          $xml->add_tag('config', $config['text'], $attributes, true);

	          }

	          $xml->close_group();

	          $doc = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n\r\n";

	          $doc .= $xml->output();
	          $xml = null;

	          require_once(DIR . '/includes/functions_file.php');
	          file_download($doc, 'vbgamez-userbar-' . $userbar['userbarid'].'.xml', 'text/xml');
         }


         function upload_userbar($xml)
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
			          print_stop_message('please_ensure_x_file_is_located_at_y', 'vbulletin-userbar.xml', $GLOBALS['path']);
	          }

	          if(!$arr =& $xmlobj->parse())
	          {
		          print_dots_stop();
		          print_stop_message('xml_error_x_at_line_y', $xmlobj->error_string(), $xmlobj->error_line());
	          }


                  if(empty($arr['name']))
	          {
		          print_dots_stop();
		          print_stop_message('xml_error_x_at_line_y', 'Invalid Userbar XML file', 1);
	          }
      
                  $this->do_add_userbar($arr['name'], $arr['description'], 0);
                  $userbarid = $this->registry->db->insert_id();

                  $this->do_configure_userbar($userbarid, $arr['background'], $arr['textcolor'], $arr['font'], $arr['fontsize'], $arr['fieldname']);

                  foreach($arr['config'] AS $config)
                  {
                         $this->do_add_userbar_location($userbarid, $config['value'], $config['radius'], $config['repeat_x'], $config['repeat_y'], $config['font'], $config['fontsize'], $config['fontcolor'], $config['width'], $config['enabled']);
                  }
         }
}