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

class vBGamEz_FieldsController
{
           // vB_Registry
           var $registry;

           // Errors
           var $errors;

           var $action;

           var $cached_data;

           function vBGamEz_FieldsController($registry)
           {
                       $this->registry =& $registry;
                       $this->action = $_REQUEST['do'];

                       if($this->action == 'doaddserver')
                       {
                                      $this->action = 'addserver';
                       }
                       if($this->action == 'doeditserver')
                       {
                                      $this->action = 'editserver';
                       }

                       $this->cached_data =& $this->registry->vbgamez_fieldcache;
           }

           function getDisplayView($fieldvalues = null, $getOnlyRequired = false)
           {
                    if(!$this->cached_data)
                    {
                               return false;
                    }

                    foreach($this->cached_data AS $fieldid => $field)
                    {
                                 $value = '';
                                 if(!$this->can_edit_value($field))
                                 {
                                                continue;
                                 }

                                 if(!$this->needPrintForm($getOnlyRequired, $field))
                                 {
                                                continue;
                                 }
                                 
                                 if($this->is_search AND !$field['enablesearch'])
                                 {
                                                continue; 
                                 }

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
                                                       $optionvalues = "\n<option value=\"\">---</option>\n";
                                               }
											   $num = 0;
                                               foreach($option_value AS $key => $val)
                                               {
                                                      $num++;
                                                      $optionvalues .= "<option value=\"" . $num . "\" " . iif(in_array($num, explode(',', $fieldvalues[$fieldid])), 'selected="selected"') . ">" . htmlspecialchars($val) . "</option>\n";
                                               }
                                 }

                                 if($field['type'] == 'radio')
                                 { 
                                               $option_value = explode("\r\n", $field['options']);

                                               if($field['set_first_default'] AND empty($fieldvalues[$fieldid]))
                                               {
                                                       $firstchecked = 'checked="checked"';
                                               }
										       $num = 0;
                                               foreach($option_value AS $key => $val)
                                               { 
                                                      $num++;
                                                      $optionvalues .= "<li><input type=\"radio\" id=\"$fieldid\" name=\"$fieldid\" value=\"$num\" " . iif($num == 1 AND $field['set_first_default'], 'checked="checked"') . " " . iif( $num == $fieldvalues[$fieldid], 'checked="checked"') . "> $val </li>";
                                               }
                                 }

                                 if($field['type'] == 'checkbox')
                                 { 
                                               $newfieldid = $fieldid.'[]';
                                               $option_value = explode("\r\n", $field['options']);
										       $num = 0;
                                               foreach($option_value AS $key => $val)
                                               { 
                                                      $num++;
                                                      $optionvalues .= "<li><input type=\"checkbox\" id=\"$newfieldid\" name=\"$newfieldid\" value=\"$num\" " . iif(in_array($num, explode(',', $fieldvalues[$fieldid])), 'checked="checked"') . "> $val </li>";
                                               }
                                 }
                                 if(VBG_IS_VB4) 
                                 {
                                             $tpl = vB_Template::create('vbgamez_fieldbits');
                                             $tpl->register('fieldid', $fieldid);
                                             $tpl->register('field', $field);
                                             $tpl->register('title', $title);
                                             $tpl->register('fieldsize', $fieldsize);
                                             $tpl->register('maxchars', $maxchars);
                                             $tpl->register('description', $description);
                                             $tpl->register('optionvalues', $optionvalues);
                                             $tpl->register('value', $value);
                                             $fields .= $tpl->render();
                                 }else{
                                             eval('$fields .= "' . fetch_template('vbgamez_fieldbits') . '";');
                                 }
                     }

                     return $fields;
             }


             function verifyPostFields()
             {
                    if(!$this->cached_data)
                    {
                               return true;
                    }

                    global $vbphrase;
                    foreach($this->cached_data AS $fieldid => $field)
                    {
                                     if(!$this->can_edit_value($field))
                                     {
                                                    continue;
                                     }

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

             function can_edit_value($field)
             {
                         if($field['canedit'] == 1 OR ($this->action == 'addserver' AND $field['canedit'] == 2))
                         {
                                          return true;
                         }
             }

             function is_required_field($field)
             {
                             if($this->action == 'addserver')
                             {
                                         if($field['required'] == 1 OR $field['required'] == 3)
                                         { 
                                                     return true;
                                         }
                             }

                             if($this->action == 'editserver')
                             {
                                         if($field['required'] == 2 OR $field['required'] == 3)
                                         { 
                                                     return true;
                                         }
                             }

                             if($this->action == 'search' OR $this->action == 'dosearch')
                             {
                                         if($field['required'] == 2 OR $field['required'] == 3)
                                         { 
                                                     return false;
                                         }
                             }

                             return false;
             }

             function save_info(&$serverid)
             {
                    if(!$this->cached_data OR !$serverid)
                    {
                               return '';
                    }

                    foreach($this->cached_data AS $fieldid => $field)
                    {
                                   if(!$this->can_edit_value($field))
                                   {
                                                continue;
                                   }

                                   $fieldid = 'field'.$fieldid;
                                   if($field['type'] == 'checkbox' OR $field['type'] == 'select_multiple')
                                   {
                                              $set_fields .= ", $fieldid = " . $this->registry->db->sql_prepare(@implode(',', $_POST[$fieldid])) . "";
                                   }else{
                                              $set_fields .= ", $fieldid = " . $this->registry->db->sql_prepare($_POST[$fieldid]) . "";
                                   }
                    }

                    $this->registry->db->query_write("UPDATE " . TABLE_PREFIX . "vbgamez SET disabled = 0 $set_fields WHERE id = " . intval($serverid) . "");
             }

             function needPrintForm($getOnlyRequired = false, $field = null)
             {
                                 if($getOnlyRequired)
                                 {
                                           if($this->action == 'addserver')
                                           {
                                                     if($field['required'] == 2 OR $field['required'] == 0)
                                                     { 
                                                                   return false;
                                                     }
                                           }

                                           if($this->action == 'editserver')
                                           {
                                                     if($field['required'] == 1 OR $field['required'] == 0)
                                                     { 
                                                                   return false;
                                                     }
                                           }
                                 }else{
                                           if($this->action == 'addserver')
                                           {
                                                     if($field['required'] == 1 OR $field['required'] == 3)
                                                     { 
                                                                   return false;
                                                     }
                                           }

                                           if($this->action == 'editserver')
                                           {
                                                     if($field['required'] == 2 OR $field['required'] == 3)
                                                     { 
                                                                   return false;
                                                     }
                                           }
                                 } 

                                 return true;
            }


             function getDisplayViewInfo($fieldsvalue)
             {
                    if(!$this->cached_data)
                    {
                               return '';
                    }

                    foreach($this->cached_data AS $fieldid => $field)
                    {
                                      $fieldid = 'field'.$fieldid;
                                      $field['title'] = htmlspecialchars_uni($field['title']);
                                      $fieldsvalue[$fieldid] = htmlspecialchars_uni($fieldsvalue[$fieldid]);

                                      if(!empty($fieldsvalue[$fieldid]))
                                      {
                                                       if($field['type'] == 'checkbox' OR $field['type'] == 'select_multiple' OR $field['type'] == 'radio' OR $field['type'] == 'select')
                                                       {  

                                                              $final_value = $this->getValueFromArray($fieldid, $fieldsvalue[$fieldid]);
                                                       }else{
                                                              $final_value = $fieldsvalue[$fieldid];
                                                       }
                                                       if(VBG_IS_VB4)
                                                       {
                                                                   $tpl = vB_Template::create('vbgamez_infobits');
                                                                   $tpl->register('key', $field['title']);
                                                                   $tpl->register('val', $final_value);
                                                                   $tpl_data .= $tpl->render();
                                                       }else{
                                                                   eval('$tpl_data .= "' . fetch_template('vbgamez_infobits') . '";');
                                                       }
                                      }
                    }

                    return $tpl_data;
             }


             function getDisplayViewDetalisInfo($fieldsvalue)
             {
                    if(!$this->cached_data)
                    {
                               return '';
                    }

                    global $bbcode_parser;

                    foreach($this->cached_data AS $fieldid => $field)
                    {
                                      if($field['private'])
                                      {
                                                 continue;
                                      }

                                      $fieldid = 'field'.$fieldid;
                                      $field['title'] = htmlspecialchars_uni($field['title']);

                                      if(!empty($fieldsvalue[$fieldid]))
                                      {
                                      		if($field['verifymethod'] == 'VerifyMethod_icq')
                                      		{
                                                   		$fieldsvalue[$fieldid] = vB_vBGamez::handle_bbcode_icq($fieldsvalue[$fieldid]);
                                      		}elseif($field['verifymethod'] == 'VerifyMethod_Link')
                                      		{
                                                   		$fieldsvalue[$fieldid] = $bbcode_parser->handle_bbcode_url($fieldsvalue[$fieldid], $fieldsvalue[$fieldid]);
                                      		}elseif($field['verifymethod'] == 'is_valid_email')
                                      		{
                                                   		$fieldsvalue[$fieldid] = $bbcode_parser->handle_bbcode_email($fieldsvalue[$fieldid]);
                                      		}else{
                                                   		$fieldsvalue[$fieldid] = htmlspecialchars_uni($fieldsvalue[$fieldid]);
                                      		}
                                      }

                                      if(!empty($fieldsvalue[$fieldid]))
                                      {
                                                if($field['type'] == 'checkbox' OR $field['type'] == 'select_multiple' OR $field['type'] == 'radio' OR $field['type'] == 'select')
                                                {  
                                                       $final_value = $this->getValueFromArray($fieldid, $fieldsvalue[$fieldid]);
                                                }else{
                                                       $final_value = $fieldsvalue[$fieldid];
                                                }

                                                if(VBG_IS_VB4)
                                                {
                                                        $tpl = vB_Template::create('vbgamez_detalis_fieldbits');
                                                        $tpl->register('title', $field['title']);
                                                        $tpl->register('value', nl2br($final_value));
                                                        $tpl->register('field', $field);
                                                        $tpl_data .= $tpl->render();
                                                }else{
                                                        eval('$tpl_data .= "' . fetch_template('vbgamez_detalis_fieldbits') . '";');
                                                }
                                      }
                    }

                    return $tpl_data;
             }


             function getValueFromArray($oldfiedid, $valuesarray)
             {
                    if(!$this->cached_data)
                    {
                               return '';
                    }

                    foreach($this->cached_data AS $fieldid => $field)
                    {
                                     $current_fieldid = 'field'.$fieldid;
                                     if($oldfiedid != $current_fieldid)
                                     {
                                                      continue;
                                     }

                                     $explode_options = explode("\r\n", $field['options']);

                                     foreach($explode_options AS $ftext)
                                     {
                                           $textid++;

                                                  $explode_values = explode(',', $valuesarray);
                                                  foreach($explode_values AS $valueid)
                                                  {
                                                                if($textid == $valueid)
                                                                {
                                                                               $field_text .= ', '.$ftext;
                                                                }
                                                  }
                                     }
                    }

                    if($field_text)
                    {
                                   return str_replace('start,', '', 'start'.$field_text);
                    }
					return '---';
             }
}

function VerifyMethod_icq($number)
{
                 $number = intval($number);

                 if(vbstrlen($number) > 9)
                 {
                             return false;
                 }else{
                             return true; 
                 }
}

function VerifyMethod_Link($link)
{
                 return !empty($link);
}