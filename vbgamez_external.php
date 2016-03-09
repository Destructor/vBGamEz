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
define('THIS_SCRIPT', 'vbgamez_external');
define('VBG_PACKAGE', true);
define('CSRF_PROTECTION', true);

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array('vbgamez');

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by specific actions
$globaltemplates = array();

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/packages/vbgamez/bootstrap.php');
require_once(DIR . '/packages/vbgamez/comments.php');

vB_vBGamez::bootstrap();

if(!$vbulletin->options['vbgamez_enable_rss'])
{
        print_no_permission();
}
// ######################### START MAIN SCRIPT ############################

$id = intval($_REQUEST['id']);

$lookup = vB_vBGamez::vbgamez_verify_id($id);

if(!$lookup)
{ 
       print_no_permission();
}

if(vB_vBGamez::fetch_stylevar('charset') != 'UTF-8')
{
      $servername = @iconv('UTF-8', vB_vBGamez::fetch_stylevar('charset'), $lookup['cache_name']);
}else{
      $servername = $lookup['cache_name'];
}

$show['commentsenable'] = vB_vBGamez::vbg_check_comments_enable($lookup['commentsenable']);

if(!$vbulletin->options['vbgamez_comments_enable'] OR !$show['commentsenable'])
{
      print_no_permission();
}

$description = construct_phrase($vbphrase['vbgamez_rss_comments'], $servername);
$rssicon = $vbulletin->options['bburl'].'/images/vbgamez/rss.png';
$rsstitle =& $description;

$fetchPosts = $db->query("SELECT * FROM " . TABLE_PREFIX . "vbgamez_comments WHERE serverid = '$lookup[id]' Order by dateline DESC LIMIT 100");

while($post = $db->fetch_array($fetchPosts))
{
         $postcache[$post['id']] = $post;
}

$headers[] = 'Content-Type: text/xml' . (vB_vBGamez::fetch_stylevar('charset') != '' ? '; charset=' .  vB_vBGamez::fetch_stylevar('charset') : '');

$output = '<?xml version="1.0" encoding="' . vB_vBGamez::fetch_stylevar('charset') . '"?>' . "\r\n\r\n";

require_once(DIR . '/includes/class_xml.php');
$xml = new vB_XML_Builder($vbulletin);
$rsstag = array(
		'version'       => '2.0',
		'xmlns:dc'      => 'http://purl.org/dc/elements/1.1/',
		'xmlns:content' => 'http://purl.org/rss/1.0/modules/content/'
);

$xml->add_group('rss', $rsstag);
	$xml->add_group('channel');
	$xml->add_tag('title', $servername.' - '.$vbphrase['vbgamez_comments']);
	$xml->add_tag('link', $vbulletin->options['vbgamez_path'].'?do=view&id='.$lookup['id'], array(), false, true);
	$xml->add_tag('description', $description);
	$xml->add_tag('language', vB_vBGamez::fetch_stylevar('languagecode'));
	$xml->add_tag('lastBuildDate', gmdate('D, d M Y H:i:s') . ' GMT');
	$xml->add_tag('generator', 'vBGamEz');
	$xml->add_group('image');
				$xml->add_tag('url', $rssicon);
				$xml->add_tag('title', $rsstitle);
				$xml->add_tag('link', $vbulletin->options['vbgamez_path'].'?do=view&id='.$lookup['id'], array(), false, true);
	$xml->close_group('image');

require_once(DIR . '/includes/class_bbcode_alt.php');

if(!empty($postcache))
{
  foreach($postcache AS $post)
  {
        if($post['onmoderate'] AND !vB_vBGamez::can_view_comment($lookup, $post))
        {
                   	continue;
        }

        if($post['deleted'] AND !vB_vBGamez::vbg_check_delete_comments_permissions($post['serverid'], $lookup['userid']))
        {
                   		continue;
        }

	$page = vB_vBGamez::vbgamez_get_comment_page($id, $post['id'], 'external');

	$xml->add_group('item');
				$xml->add_tag('title', $vbphrase['vbgamez_comment_by']." ".$post['username']);
				$xml->add_tag('link', $vbulletin->options['vbgamez_path'].'?do=view&id='.$lookup['id'].'&page=' . $page . '#comment_'.$post['id'], array(), false, true);
				$xml->add_tag('pubDate', gmdate('D, d M Y H:i:s', $post['dateline']) . ' GMT');
				$xml->add_tag('description', substr(strip_tags(strip_bbcode($post['pagetext'])), 0, 200));
	$xml->close_group('item');
  }
}

$xml->close_group('channel');
$xml->close_group('rss');
$output .= $xml->output();
unset($xml);

$db->close();

foreach ($headers AS $header)
{
	header($header);
}

echo $output;

?>