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
* Class to work vBGamez Search
*
* @package	vBGamEz
* @autor	vBGamEz developers
* @version	$Revision: 85 $
*/

class vB_vBGamez_Search_Core_Results
{

	/*========================================================================
         *
	 * Использование некэшированных результатов
         * 
	 */

  	 public static function getResults($server_list)
   	 {
      	 	 $server = array();

      	 	 foreach ($server_list as $server)
      	 	 {

          	 	 $misc   = vB_vBGamez::vbgamez_server_misc($server);
          	 	 $server = vB_vBGamez::vbgamez_server_html($server);

          	 	 $connectlink = vbgamez_software_link($server['b']['type'], $server['b']['ip'], $server['b']['c_port'], $server['b']['q_port'], $server['b']['s_port']);

          	 	 if(VBG_IS_VB4)
          	 	 {
                	 	 $templater = vB_Template::create('vbgamez_search_result_bit');
                	 	 $templater->register('server', $server);
                	 	 $templater->register('connectlink', $connectlink);
                	 	 $templater->register('misc', $misc);
                	 	 $listbits .= $templater->render(); 
          	 	 }else{
                	 	 eval('$listbits .= "' . fetch_template('vbgamez_listbits') . '";');
          	 	 }
     	 	 }


    	 	 return $listbits;

   	 }
}