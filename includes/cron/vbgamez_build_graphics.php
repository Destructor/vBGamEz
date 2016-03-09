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

$db =& $vbulletin->db;

require_once('./packages/vbgamez/bootstrap.php');
require_once('./packages/vbgamez/statistics.php');

$datecut = TIMENOW - (60 * 60 * 24 * 365);
$db->query("DELETE FROM " . TABLE_PREFIX . "vbgamez_statistics WHERE dateline < $datecut");


$result_servers = $vbulletin->db->query_write("SELECT * FROM " . TABLE_PREFIX . "vbgamez WHERE disabled = 0 ORDER BY cache_time ASC");

while($server = $vbulletin->db->fetch_array($result_servers))
{
            $data = vB_vBGamez::vBG_Datastore_Cache($server['ip'], $server['q_port'], $server['c_port'], $server['s_port'], $server['type'], "sep", $server);

            $vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "vbgamez_statistics
                                         (serverid, players, dateline, mapname) VALUES (" . $server['id'] . ", " . $server['cache_players'] . ", " . TIMENOW . ", " . $db->sql_prepare($server['cache_map']) . ")");

             vBGamez_Stats_Builder::instance($vbulletin, $data)->buildNow();
}


log_cron_action('', $nextitem, 1);

?>