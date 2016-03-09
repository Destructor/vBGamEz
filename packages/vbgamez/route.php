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
 * VBGamEz URLS
 *
 * @package vBGamEz
 * @author GiveMeABreak aka Developer, LGSL author
 * @version $Revision: 100 $
 * @copyright GiveMeABreak
 */


class vB_vBGamez_Route
{
         private static $vBGamez_Urls = array('vbgamez_path' => 'vbgamez.php',
                                              'vbgamez_external_path' => 'vbgamez_external.php',
                                              'vbgamez_inlinemod_path' => 'vbgamez_inlinemod.php',
                                              'vbgamez_post_path' => 'vbgamez_post.php',
                                              'vbgamez_search_path' => 'vbgamez_search.php',
                                              'vbgamez_usercp_path' => 'vbgamez_usercp.php',
                                              'vbgamez_api_path' => 'vbgamez_api.php',
                                              'vbgamez_userbar_path' => 'vbgamez_userbar.php',
											  'vbgamez_vbimage_path' => 'image.php',
											  'vbgamez_vbajax_path' => 'ajax.php');

         public static function getUrls()
         {
                       global $vbulletin;

					   if(!$vbulletin->options['vbgamez_domain_indexfile'])
					   {
							 $vbulletin->options['vbgamez_domain_indexfile'] = 'index.php';
					   }
                       if($vbulletin->options['vbgamez_custom_urls_enable'])
                       {
                               $Urls = array();

                               foreach(self::$vBGamez_Urls AS $name => $value)
                               {
                                       $Urls[$name] = $vbulletin->options[$name.'_real'];
									   if(!$Urls[$name])
									   {
											$Urls[$name] = self::$vBGamez_Urls[$name];
								       }
                               }	
                               return $Urls;

                       }else{
                               return self::$vBGamez_Urls;
                       }
         }

         public static function setUrls()
         {
                       global $vbulletin;

                       foreach(self::getUrls() AS $pathtitle => $scriptname)
                       {
                 		if(defined('VBG_DOMAIN'))
                 		{
                                    $vbulletin->options[$pathtitle] = VBG_DOMAIN."/".VBG_SCRIPTNAME."/".$scriptname;
                 		}else{
                                    $vbulletin->options[$pathtitle] = iif($vbulletin->options['vbgamez_tab_urls'], $vbulletin->options['vbgamez_tab_urls'].'/'.$vbulletin->options['vbgamez_domain_indexfile'], $vbulletin->options['bburl'])."/".$scriptname;
                          	}
                       }
          }

	 /*========================================================================
         *
	 * Установка пути скриптов JS
	 *
	 */ 

         public static function fetch_js_paths()
         {
                 global $vbulletin;

                 foreach(self::getUrls() AS $path => $scriptname)
                 {
                 	if(defined('VBG_DOMAIN'))
                 	{
                            $js_data .= "var $path = '" . VBG_DOMAIN."/".VBG_SCRIPTNAME."/".$scriptname . "'; \n";
                 	}else{
                            $js_data .= "var $path = '" . $vbulletin->options['bburl'] . "/" . $scriptname . "'; \n";
                 	}
                 }
                 return $js_data;
         }
}

