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
* vBGamez:: Indexcontroller
*
* @package	vBGamEz
* @autor	vBGamEz developers
* @version	$Revision: 104 $
*/

class vB_vBGamez_Search_Indexcontroller extends vB_vBGamez_Search_Core
{
         public static function getSearchDb($query, $sqlsort, $sortorder)
         {
                return vB_vBGamez::vBG_Datastore_Cache_all("s", '', '', "AND $query AND valid = 0 ORDER BY $sqlsort $sortorder LIMIT 50");
         }
}