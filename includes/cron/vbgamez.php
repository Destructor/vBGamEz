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


// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

if (!is_object($vbulletin->db))
{
	exit;
}

require_once('./packages/vbgamez/bootstrap.php');

$result_servers = $vbulletin->db->query_write("SELECT *  FROM " . TABLE_PREFIX . "vbgamez WHERE disabled = 0 ORDER BY cache_time ASC");

while($server = $vbulletin->db->fetch_array($result_servers))
{
            vB_vBGamez::vBG_Datastore_Cache($server['ip'], $server['q_port'], $server['c_port'], $server['s_port'], $server['type'], "sep", $server);
}

log_cron_action('', $nextitem, 1);

?>