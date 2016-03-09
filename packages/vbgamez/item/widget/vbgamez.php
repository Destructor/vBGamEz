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

/**
 * VBGamEz - модуль vBCMS
 * @package vBGamEz
 * @author GiveMeABreak aka Developer
 * @version $Revision: 41 $
 * @copyright GiveMeABreak
 */

class vbgamez_Item_Widget_vbgamez extends vBCms_Item_Widget
{
	/*Properties====================================================================*/

	/**
	 * A package identifier.
	 *
	 * @var string
	 */
	protected $package = 'vbgamez';

	/**
	 * A class identifier.
	 *
	 * @var string
	 */
	protected $class = 'vbgamez';

	/** The default configuration **/
	protected $config = array(
		'vbg_servers_limit'       => "5",
		'vbg_servers_sort' => '8',
		'vbg_servers_order' => '2',
		'vbg_servers_ids' => '',
		'vbg_servers_info' => '0',
		'vbg_server_show_sticked' => 1
	);

}