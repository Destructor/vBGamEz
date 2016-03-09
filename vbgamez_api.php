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

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'vbgamez_api');
define('VBG_PACKAGE', true);
define('CSRF_PROTECTION', true);

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array();

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by specific actions
$globaltemplates = array();

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/packages/vbgamez/bootstrap.php');

vB_vBGamez::bootstrap();

if(!$vbulletin->options['vbgamez_enable_api'])
{
               exit('API disabled by admin.');
}

// ######################### START MAIN SCRIPT ############################
  header('Conten-type:text/html; charset=' . vB_vBGamez::fetch_stylevar('charset') . ';');

  $vbulletin->input->clean_array_gpc('r', array('id' => TYPE_INT, 'type' => TYPE_STR));

  $vbulletin->GPC['id'] = intval($vbulletin->GPC['id']);

  $lookup = vB_vBGamez::vbgamez_verify_id($vbulletin->GPC['id'], false, true);

  if (!$lookup)
  {
              exit('Called unknown server.');
  }

  if (!$vbulletin->GPC['type'])
  {
              exit('Called unknown type.');
  }

  $server = vB_vBGamez::vBG_Datastore_Cache($lookup['ip'], $lookup['q_port'], $lookup['c_port'], $lookup['s_port'], $lookup['type'], "sep", $lookup);

  $misc   = vB_vBGamez::vbgamez_server_misc($server);

  $server = vB_vBGamez::vbgamez_server_html($server);

  $connectlink = vbgamez_software_link($lookup['type'], $lookup['ip'], $lookup['c_port'], $lookup['q_port'], $lookup['s_port']);

  $serverarraydata = array('ip' => $server['b']['ip'], 
                           'port' => $server['b']['c_port'],
                           'players' => $server['s']['players'],
                           'playersmax' => $server['s']['playersmax'],
                           'name' => vB_vBGamez::vbgamez_string_html($server['s']['name']),
                           'link' => $connectlink,
                           'type' => $server['b']['type'],
                           'game' => $server['s']['game'],
                           'fulltype' => $misc['text_game'],
                           'map' => $server['s']['map'],
                           'views' => $lookup['views'],
                           'rating' => $lookup['rating'],
                           'mapimage' => $vbulletin->options['bburl'].'/'.$misc['image_map'],
                           'gameicon' => $vbulletin->options['bburl'].'/'.$misc['icon_game'],
                           'status' => $lookup['status']);
  $playersarraydata = array();

  foreach ($server['p'] as $player_key => $player)
  {
		      $playersarraydata['playerslist'][$player_key] = array();
                      foreach (vB_vBGamez::fetch_player_fields($server) as $field_key => $field)
                      {
			         $playersarraydata['playerslist'][$player_key][$field] = $player[$field];
                      }
  }

  if($vbulletin->GPC['type'] == 'json')
  {
              if(function_exists('json_encode'))
              {
                    exit(json_encode(array_merge($serverarraydata, $playersarraydata)));
              }else{
                    require_once(DIR .'/packages/vbgamez/3rd_party_classes/json/JSON.php');
                    $json = new Services_JSON();
                    exit($json->encode(array_merge($serverarraydata, $playersarraydata)));                
              }
  }elseif($vbulletin->GPC['type'] == 'xml')
  {
              @header('Content-type: text/xml');

	      require_once(DIR . '/includes/class_xml.php');
	      $xml = new vB_XML_Builder($vbulletin);

	      $xml->add_group('server');

	      foreach ($serverarraydata AS $name => $value)
	      {
			 $xml->add_tag($name, $value);
	      }

	      $xml->add_group('playerslist');

              foreach ($server['p'] as $player_key => $player)
              {
		      $xml->add_group('player');
                      foreach (vB_vBGamez::fetch_player_fields($server) as $field_key => $field)
                      {
			         $xml->add_tag($field, $player[$field]);
                      }
                      $xml->close_group();
              }

	      $xml->close_group();

	      $xml->close_group();

	      $doc = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n\r\n";

	      $doc .= $xml->output();

              exit($doc);

  }elseif($vbulletin->GPC['type'] == 'serialize')
  {
              $dates = explode('_', $lookup['cache_time']);

              @header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $dates[0]) . ' GMT'); 

              exit(serialize(array_merge($serverarraydata, $playersarraydata)));

  }else{
              exit('Called unknown type');
  }

?>