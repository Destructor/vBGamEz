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
 * VBGamEz - менеджер полей добавления серверов
 *
 * @package vBGamEz
 * @author GiveMeABreak aka Developer
 * @version $Revision: 41 $
 * @copyright GiveMeABreak
 */

// load $this->verifyTypes functions
require_once('./packages/vbgamez/field.php');

class vBGamEz_FieldManager
{
       var $registry;
       var $types;
       var $verifyTypes;
       var $action;

       function vBGamEz_FieldManager($vbulletin)
       {
                global $vbphrase;

                $this->registry = $vbulletin;

                $this->types = array(
	                       'input'           => $vbphrase['single_line_text_box'],
	                       'textarea'        => $vbphrase['multiple_line_text_box'],
	                       'radio'           => $vbphrase['single_selection_radio_buttons'],
	                       'select'          => $vbphrase['single_selection_menu'],
	                       'select_multiple' => $vbphrase['multiple_selection_menu'],
	                       'checkbox'        => $vbphrase['multiple_selection_checkbox']
                       );

                // method: return function('..');
                // example: is_valid_email($text);

                $this->verifyTypes = array('' => '---',
                                           'is_numeric' => $vbphrase['vbgamez_onlyintvalue'],
                                           'is_valid_email' => $vbphrase['vbgamez_fieldmanager_email'],
                                           'VerifyMethod_icq' => $vbphrase['vbgamez_icq'],
                                           'VerifyMethod_Link' => $vbphrase['url']);

                $this->action = $_REQUEST['do'];
       }

       function fetch_field_name($type)
       {
                return $this->types[$type];
       }


       function build_field_datastore()
       {
	       global $vbulletin;

	       $field_cache = array();

	       $field_result = $this->registry->db->query_read("SELECT *
		                                                 FROM " . TABLE_PREFIX . "vbgamez_textfields
		                                                 WHERE enabled = 1
		                                                 ORDER BY sortorder ASC");

	       while ($field = $this->registry->db->fetch_array($field_result))
	       {
		       $field_cache["$field[fieldid]"] = $field;
	       }

	       $this->registry->db->free_result($field_result);

	       build_datastore('vbgamez_fieldcache', serialize($field_cache), 1);
       }

       function verifyPostFields()
       {
                    $cached_data = $this->registry->vbgamez_fieldcache;

                    if(!$cached_data)
                    {
                               return true;
                    }

                    global $vbphrase;
                    foreach($cached_data AS $fieldid => $field)
                    {
                                     $fieldid = 'field'.$fieldid;

                                     if(empty($_POST[$fieldid]) AND $this->is_required_field($field))
                                     {
                                                       $this->errors = construct_phrase($vbphrase['vbgamez_field_empty_value'], $field['title']);
                                     }

                                     if(!empty($_POST[$fieldid]) AND !empty($field['max_selects']))
                                     {
                                                       if($field['type'] == 'checkbox' OR $field['type'] == 'select_multiple')
                                                       {
                                                                      $explodeValues = $_POST[$fieldid];

                                                                      if(!empty($explodeValues))
                                                                      {
                                                                                 foreach($explodeValues AS $value)
                                                                                 {
                                                                                             $selectedOptions++;
                                                                                 }
                                                                      }

                                                                      if($selectedOptions > $field['max_selects'])
                                                                      {
                                                                                 $this->errors = construct_phrase($vbphrase['vbgamez_field_maximum_selected'], $field['title'], $field['max_selects']);
                                                                      }
                                                        }
                                     }

                                     $needVerify = false;

                                     if($field['verifymethod'])
                                     {
                                             if(!empty($_POST[$fieldid]))
                                             {
                                                     $needVerify = true;
                                             }

                                             $function = $field['verifymethod'];

                                             if(!$function($_POST[$fieldid]) AND $needVerify)
                                             {
                                                       $this->errors = construct_phrase($vbphrase['vbgamez_field_invalid_value'], $field['title']); 
                                             }
                                     }
                    }

                    return $fields;
       }

       function getDisplayView($fieldvalues = null)
       {
                    $cached_data = $this->registry->vbgamez_fieldcache;

                    if(!$cached_data)
                    {
                               return false;
                    }

                    foreach($cached_data AS $fieldid => $field)
                    {
                                 $value = '';

                                 $title = htmlspecialchars_uni($field['title']);

                                 $description = htmlspecialchars_uni($field['description']);

                                 $fieldid = 'field'.$fieldid;

                                 $fieldsize = intval($field['fieldsize']);

                                 $maxchars = intval($field['maxchars']);

                                 if(is_string($fieldvalues[$fieldid]))
                                 {
                                                 $value = htmlspecialchars($fieldvalues[$fieldid]);
                                 }elseif(is_array($fieldvalues[$fieldid])){
                                                 $value = implode(',', $fieldvalues[$fieldid]);
                                                 $fieldvalues[$fieldid] = implode(',', $fieldvalues[$fieldid]);
                                 }

                                 $field['required'] = $this->is_required_field($field);

                                 if($field['required']) { $title .= ' <font color="red">*</font>'; }

                                 if(empty($value))
                                 {
                                               $value = htmlspecialchars_uni($field['defaultvalue']);
                                 }

                                 $optionvalues = '';
                                 if($field['type'] == 'select' OR $field['type'] == 'select_multiple')
                                 { 
                                               $option_value = explode("\r\n", $field['options']);

                                               if(!$field['set_first_default'] AND empty($fieldvalues[$fieldid]))
                                               {
                                                       $optionvalues[0] = "---";
                                               }

                                               foreach($option_value AS $key => $val)
                                               {
                                                      $num++;
                                                      $optionvalues[$num] = $val;
                                               }
                                 }

                                 if($field['type'] == 'radio')
                                 { 
                                               $option_value = explode("\r\n", $field['options']);

                                               if($field['set_first_default'] AND empty($fieldvalues[$fieldid]))
                                               {
                                                       $value = 1;
                                               }

                                               foreach($option_value AS $key => $val)
                                               { 
                                                      $num++;
                                                      $optionvalues[$num] = $val;
                                               }
                                 }

                                 if($field['type'] == 'checkbox')
                                 { 
                                               $newfieldid = $fieldid.'[]';
                                               $option_value = explode("\r\n", $field['options']);

                                               foreach($option_value AS $key => $val)
                                               { 
                                                      $num++;
                                                      $optionvalues .= "<input type=\"checkbox\" id=\"$newfieldid\" name=\"$newfieldid\" value=\"$num\" " . iif(in_array($num, explode(',', $fieldvalues[$fieldid])), 'checked="checked"') . "> $val <br />";
                                               }
                                 }

                                 if($field['type'] == 'input')
                                 {
                                                 print_input_row($title, $fieldid, $value, false, $fieldsize, $maxchars);
                                 }

                                 if($field['type'] == 'textarea')
                                 {
                                                 print_textarea_row($title, $fieldid, $value, $field['rows'], $field['fieldsize']);
                                 }

                                 if($field['type'] == 'radio')
                                 {
                                                  print_radio_row($title, $fieldid, $optionvalues, $value);
                                 }

                                 if($field['type'] == 'select')
                                 {
                                                  print_select_row($title, $fieldid, $optionvalues, $value);
                                 }

                                 if($field['type'] == 'select_multiple')
                                 {
                                                  print_select_row($title, $fieldid, $optionvalues, $value, false, 0, true); 
                                 }

                                 if($field['type'] == 'checkbox')
                                 {
                                                  print_label_row($title, $optionvalues); 
                                 }

                     }

                     return $fields;
             }


             function is_required_field($field)
             {
                             if($this->action == 'add' OR $this->action == 'doadd')
                             {
                                         if($field['required'] == 1 OR $field['required'] == 3)
                                         { 
                                                     return true;
                                         }
                             }

                             if($this->action == 'modify' OR $this->action == 'domodify')
                             {
                                         if($field['required'] == 2 OR $field['required'] == 3)
                                         { 
                                                     return true;
                                         }
                             }

                             return false;
             }

             function save_info(&$serverid)
             {

                    $cached_data = $this->registry->vbgamez_fieldcache;

                    if(!$cached_data OR !$serverid)
                    {
                               return '';
                    }

                    foreach($cached_data AS $fieldid => $field)
                    {
				   
                                   $fieldid = 'field'.$fieldid;
                                   if($field['type'] == 'checkbox' OR $field['type'] == 'select_multiple')
                                   {
                                              $set_fields .= ", $fieldid = " . $this->registry->db->sql_prepare(@implode(',', $_POST[$fieldid])) . "";
                                   }else{
                                              $set_fields .= ", $fieldid = " . $this->registry->db->sql_prepare($_POST[$fieldid]) . "";
                                   }
                    }

                    $this->registry->db->query_write("UPDATE " . TABLE_PREFIX . "vbgamez SET id = id $set_fields WHERE id = " . intval($serverid) . "");
             }
}

function start_ajax_obj($displayed = 'style="display:none;"')
{
       $GLOBALS['vbg_obj_setted']++;

       print '<tbody id="vbg_objid' . $GLOBALS['vbg_obj_setted'] . '" ' . $displayed . '>';      
}

function end_ajax_obj()
{
       print '</tbody>';      
}

function vbg_print_select_row($title, $name, $array, $selected = '', $htmlise = false, $size = 0, $multiple = false)
{
	global $vbulletin;

	$uniqueid = fetch_uniqueid_counter();

	$select = "<div id=\"ctrl_$name\"><select name=\"$name\" id=\"sel_{$name}_$uniqueid\" tabindex=\"1\" class=\"bginput\"" . iif($size, " size=\"$size\"") . iif($multiple, ' multiple="multiple"') . iif($vbulletin->debug, " title=\"name=&quot;$name&quot;\"") . " onchange=\"vBG_showFieldContnent(this.value)\">\n";
	$select .= construct_select_options($array, $selected, $htmlise);
	$select .= "</select></div>\n";

	print_label_row($title, $select, '', 'top', $name);
}
function vbg_print_form_header($phpscript = '', $do = '', $uploadform = false, $addtable = true, $name = 'cpform', $width = '90%', $target = '', $echobr = true, $method = 'post', $cellspacing = 0, $border_collapse = false, $formid = '')
{
	global $vbulletin, $tableadded;

	if (($quote_pos = strpos($name, '"')) !== false)
	{
		$clean_name = substr($name, 0, $quote_pos);
	}
	else
	{
		$clean_name = $name;
	}

	echo "\n<!-- form started:" . $vbulletin->db->querycount . " queries executed -->\n";
	echo "<form action=\"$phpscript.php?do=$do\"" . ($uploadform ? " enctype=\"multipart/form-data\"" : "") . " method=\"$method\"" . ($target ? " target=\"$target\"" : "") . " name=\"$clean_name\" id=\"" . ($formid ? $formid : $clean_name) . "\" onsubmit=\"vbg_prepare_clear()\">\n";

	if (!empty($vbulletin->session->vars['sessionhash']))
	{
		//construct_hidden_code('s', $vbulletin->session->vars['sessionhash']);
		echo "<input type=\"hidden\" name=\"s\" value=\"" . htmlspecialchars_uni($vbulletin->session->vars['sessionhash']) . "\" />\n";
	}
	//construct_hidden_code('do', $do);
	echo "<input type=\"hidden\" name=\"do\" id=\"do\" value=\"" . htmlspecialchars_uni($do) . "\" />\n";
	if (strtolower(substr($method, 0, 4)) == 'post') // do this because we now do things like 'post" onsubmit="bla()' and we need to just know if the string BEGINS with POST
	{
		echo "<input type=\"hidden\" name=\"adminhash\" value=\"" . ADMINHASH . "\" />\n";
		echo "<input type=\"hidden\" name=\"securitytoken\" value=\"" . $vbulletin->userinfo['securitytoken'] . "\" />\n";
	}

	if ($addtable)
	{
		print_table_start($echobr, $width, $cellspacing, $clean_name . '_table', $border_collapse);
	}
	else
	{
		$tableadded = 0;
	}
}