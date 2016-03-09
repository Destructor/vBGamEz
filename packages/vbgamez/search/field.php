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
class vBGamEz_FieldsSearchController
{
           var $registry; var $mainObj;

           function vBGamEz_FieldsSearchController($registry)
           {
                    $this->registry =& $registry;
                    $this->mainObj = new vBGamEz_FieldsController($registry);
                    $this->mainObj->is_search = true;
           }

           function getDisplayView($fieldvalues = null)
           {
                    return $this->mainObj->getDisplayView($fieldvalues, false);
           }

           function searchFieldExists()
           {
                    $cached_data = $this->registry->vbgamez_fieldcache;

                    if(!$cached_data)
                    {
                               return false;
                    }

                    foreach($cached_data AS $fieldid => $field)
                    {
                                     $fieldid = 'field'.$fieldid;
                                     if(!empty($_POST[$fieldid]))
                                     {
                                                 return true;
                                     }
                    }
           }


           function get_Search_Field_Query()
           {
                    $cached_data = $this->registry->vbgamez_fieldcache;

                    if(!$cached_data)
                    {
                               return false;
                    }

                    foreach($cached_data AS $fieldid => $field)
                    {
                                   $fieldid = 'field'.$fieldid;

                                   if(empty($_POST[$fieldid]))
                                   {
                                                 continue;
                                   }

                                   if($field['type'] == 'checkbox' OR $field['type'] == 'select_multiple')
                                   {
                                              $search_fields .= " AND $fieldid IN(" . $this->registry->db->escape_string(implode(',', $_POST[$fieldid])) . ") ";
                                   }else{
                                              $search_fields .= " AND $fieldid LIKE ".$this->registry->db->sql_prepare('%'.$_POST[$fieldid].'%');
                                   }
                    }

                    return $search_fields;
           }
}