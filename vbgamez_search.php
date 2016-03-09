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
define('THIS_SCRIPT', 'vbgamez_search');
define('VBG_PACKAGE', true);
define('CSRF_PROTECTION', true);

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array('posting', 'user', 'vbgamez', 'search');

// get special data templates from the datastore
$specialtemplates = array('bbcodecache', 'smiliecache', 'vbgamez_fieldcache');

// pre-cache templates used by specific actions
$globaltemplates = array('vbgamez_search','vbgamez_search_result','vbgamez_search_result_bit', 'vbgamez_fieldbits');

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/packages/vbgamez/bootstrap.php');
require_once(DIR . '/packages/vbgamez/search/searchcore.php');
require_once(DIR . '/packages/vbgamez/search/indexcontroller/indexcontroller.php');
require_once(DIR . '/packages/vbgamez/search/result/searchresults.php');
require_once(DIR . '/packages/vbgamez/search/field.php');
require_once(DIR . '/packages/vbgamez/field.php');

$vBG_SearchFieldController = new vBGamEz_FieldsSearchController($vbulletin);
vB_vBGamez::bootstrap();

if(!$vbulletin->options['vbgamez_search_enable'])
{
         standard_error (fetch_error ('vbgamez_search_disabled'));
}

if(empty($_REQUEST['do']))
{
         $_REQUEST['do'] = 'search';
}
       
// ########################## VBGAMEZ SEARCH ###########################

if ($_REQUEST['do'] == 'search')
{
  $game_types = vB_vBGamez::Fetch_Game_Types();

  $navbits['' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . ''] = $vbphrase['vbgamez'];
  $navbits['' . $vbulletin->options['vbgamez_search_path'] . '?' . $vbulletin->session->vars['sessionurl'] . 'do=search'] = $vbphrase['vbgamez_search'];

  $navbits = construct_navbits($navbits);

  $search = array();

  $search['sort'] = array($vbulletin->options['vbgamez_search_default_sort'] => 'selected="selected"');
   
  $custom_additional_fields = $vBG_SearchFieldController->getDisplayView();

  if(VBG_IS_VB4)
  {
         $navbar = render_navbar_template($navbits);
         $templater = vB_Template::create('vbgamez_search');
         $templater->register_page_templates();
         $templater->register('navbar', $navbar);
         $templater->register('custom_additional_fields', $custom_additional_fields);
         $templater->register('search', $search);
         $templater->register('game_types', $game_types);
         print_output($templater->render());
  }else{
	 eval('$navbar = "' . fetch_template('navbar') . '";');
	 eval('print_output("' . fetch_template('vbgamez_search') . '");');
  }
}

// ########################## DO SEARCH ###########################

if ($_REQUEST['do'] == 'dosearch')
{
  $vbulletin->input->clean_array_gpc('p', array(
		'exactmap' 	=> TYPE_INT,
		'nocache' 	=> TYPE_INT,
		'playersoptions' 	=> TYPE_INT,
		'playersmax' 	=> TYPE_INT,
		'slotsoptions' 	=> TYPE_INT,
		'players' 	=> TYPE_INT,
		'ratingoptions' 	=> TYPE_INT,
		'rating' 	=> TYPE_INT,
		'viewoptions' 	=> TYPE_INT,
		'steam' 	=> TYPE_INT,
		'pirated' 	=> TYPE_INT,
		'nonsteam' 	=> TYPE_INT,
		'views' 	=> TYPE_INT,
		'sort' 	=> TYPE_STR,
		'order' 	=> TYPE_STR));

  if(!empty($_REQUEST['map']))
  {
        $vbulletin->GPC['map'] = $vbulletin->input->clean_gpc('r', 'map', TYPE_STR);
  }else{
        $vbulletin->GPC['map'] = $vbulletin->input->clean_gpc('p', 'map', TYPE_STR);
  }

  if(!empty($_REQUEST['game']))
  {
        $vbulletin->GPC['game'] = $vbulletin->input->clean_gpc('r', 'game', TYPE_STR);
  }else{
        $vbulletin->GPC['game'] = $vbulletin->input->clean_gpc('p', 'game', TYPE_STR);
  }

  if(!empty($_REQUEST['additional_game']))
  {
        $vbulletin->GPC['additional_game'] = $vbulletin->input->clean_gpc('r', 'additional_game', TYPE_STR);
  }else{
        $vbulletin->GPC['additional_game'] = $vbulletin->input->clean_gpc('p', 'additional_game', TYPE_STR);
  }

  if(!empty($_REQUEST['exclude']))
  {
        $vbulletin->GPC['exclude'] = $vbulletin->input->clean_gpc('r', 'exclude', TYPE_STR);
  }else{
        $vbulletin->GPC['exclude'] = $vbulletin->input->clean_gpc('p', 'exclude', TYPE_STR);
  }

  if(!empty($_REQUEST['query']))
  {
        $vbulletin->GPC['query'] = $vbulletin->input->clean_gpc('r', 'query', TYPE_STR);
  }else{
        $vbulletin->GPC['query'] = $vbulletin->input->clean_gpc('p', 'query', TYPE_STR);
  }

  $vbulletin->GPC['query'] = htmlspecialchars(trim($vbulletin->GPC['query']));
  $vbulletin->GPC['game'] = htmlspecialchars(trim($vbulletin->GPC['game']));
  $vbulletin->GPC['additional_game'] = htmlspecialchars(trim($vbulletin->GPC['additional_game']));
  $vbulletin->GPC['map'] = htmlspecialchars(trim($vbulletin->GPC['map']));
  $vbulletin->GPC['exactmap'] = intval($vbulletin->GPC['exactmap']);
  $vbulletin->GPC['nocache'] = intval($vbulletin->GPC['nocache']);
  $vbulletin->GPC['playersoptions'] = intval($vbulletin->GPC['playersoptions']);
  $vbulletin->GPC['playersmax'] = intval($vbulletin->GPC['playersmax']);
  $vbulletin->GPC['exclude'] = intval($vbulletin->GPC['exclude']);

  $vbulletin->GPC['slotsoptions'] = intval($vbulletin->GPC['slotsoptions']);
  $vbulletin->GPC['players'] = intval($vbulletin->GPC['players']);
  $vbulletin->GPC['ratingoptions'] = intval($vbulletin->GPC['ratingoptions']);
  $vbulletin->GPC['rating'] = intval($vbulletin->GPC['rating']);
  $vbulletin->GPC['viewoptions'] = intval($vbulletin->GPC['viewoptions']);
  $vbulletin->GPC['views'] = intval($vbulletin->GPC['views']);
  $vbulletin->GPC['sort'] = trim($vbulletin->GPC['sort']);
  $vbulletin->GPC['order'] = trim($vbulletin->GPC['order']);

  $vbulletin->GPC['steam'] = intval($vbulletin->GPC['steam']);
  $vbulletin->GPC['pirated'] = intval($vbulletin->GPC['pirated']);
  $vbulletin->GPC['nonsteam'] = intval($vbulletin->GPC['nonsteam']);

  if(vB_vBGamez::vbg_ajax_show_steam($vbulletin->GPC['game'], $vbulletin->GPC))
  {
           $show['steam_style_display'] = '';
           $show['steam_content'] = vB_vBGamez::vbg_ajax_show_steam($vbulletin->GPC['game'], $vbulletin->GPC);
  }else{
              $show['steam_style_display'] = 'style="display:none;"';
  }

  if(!empty($vbulletin->GPC['game']))
  {
   foreach(vB_vBGamez::fetch_additional_game_type($vbulletin->GPC['game']) AS $type => $game)
   {
           $types .= '<option value="' . $type . '" ' . iif($vbulletin->GPC['additional_game'] == $type, 'selected="selected"') . '>' . $game . '</option>';
   }

   if(!empty($types))
   {
           $show['additional_game_content'] = '<select class="textbox" name="additional_game" tabindex="1">' . $types . '</select>';
   }
  }

  if ($vbulletin->GPC['order'] == 'desc')
  {
	$sortorder = 'asc';
        $oppositeorder = 'desc';
  }
  else
  { 
	$sortorder = 'desc';
        $oppositeorder = 'asc';
  }

  if(empty($vbulletin->GPC['sort']))
  {
          $vbulletin->GPC['sort'] = $vbulletin->options['vbgamez_search_default_sort'];
  }

  switch ($vbulletin->GPC['sort'])
  {
	case 'id':
		$sqlsort = 'vbgamez.id';
                $sort = 'id';
		break;
	case 'name':
		$sqlsort = 'vbgamez.cache_name';
                $sort = 'name';
		break;
	case 'game':
		$sqlsort = 'vbgamez.cache_game';
                $sort = 'game';
		break;
	case 'comments':
		$sqlsort = 'vbgamez.comments';
                $sort = 'comments';
		break;
	case 'map':
		$sqlsort = 'vbgamez.cache_map';
                $sort = 'map';
		break;
	case 'players':
		$sqlsort = 'vbgamez.cache_players';
                $sort = 'players';
		break;
	case 'views':
		$sqlsort = 'vbgamez.views';
                $sort = 'views';
		break;
	case 'rating':
		$sqlsort = 'vbgamez.rating';
                $sort = 'rating';
		break;
	default:
		$sqlsort = 'rating';
  }

  $searchtime = vB_vBGamez_Search_Core::fetch_microtime();
 
  $lookup['type'] = $vbulletin->GPC['game'];

  $game_types = vB_vBGamez::Fetch_Game_Types(true);

  if ($vbulletin->GPC['query'] OR $vbulletin->GPC['map'] OR $vbulletin->GPC['game'] OR $vbulletin->GPC['players'] OR $vbulletin->GPC['playersmax'] OR $vbulletin->GPC['rating'] OR $vbulletin->GPC['views'])
  {
           $query = vB_vBGamez_Search_Core::getSearchQuery();
  }


   if (vbstrlen($query) > 0)
   {
	$query = substr($query,4);
   }
   else
   {
	$query = "id=".$db->sql_prepare(-1);
   }

   $search_result = vB_vBGamez_Search_Core_Results::getResults(vB_vBGamez_Search_Indexcontroller::getSearchDb($query, $sqlsort, $sortorder));
   $show['onlinestatus'] = false;

   if ($search_result)
    {
        $searchbits = $search_result;
        $templatename = 'vbgamez_search_result';
    }else{
        if(vbstrlen($query) < 30)
         {
		$error_text = fetch_error('searchspecifyterms');
         }else{
		$error_text = fetch_error('searchnoresults', '');
         }

         $templatename = 'vbgamez_search';
         $show['errors'] = true;
    } 

  $search = array('query' => $vbulletin->GPC['query'],
                  'map' => $vbulletin->GPC['map'],
                  'exactmap' => iif($vbulletin->GPC['exactmap'], 'checked="checked"'),
                  'nocache' => iif($vbulletin->GPC['nocache'], 'checked="checked"'),
                  'playersoptions' => array($vbulletin->GPC['playersoptions'] => 'selected="selected"'),
                  'playersmax' => $vbulletin->GPC['playersmax'],
                  'slotsoptions' => array($vbulletin->GPC['slotsoptions'] => 'selected="selected"'),
                  'players' => $vbulletin->GPC['players'],
                  'ratingoptions' => array($vbulletin->GPC['ratingoptions'] => 'selected="selected"'),
                  'rating' => $vbulletin->GPC['rating'],
                  'viewoptions' => array($vbulletin->GPC['viewoptions'] => 'selected="selected"'),
                  'views' => $vbulletin->GPC['views'],
                  'sort' => array($sort => 'selected="selected"'),
                  'order' => array($oppositeorder => 'selected="selected"'));

  if(empty($sort))
  {
         $search['sort'] = array($vbulletin->options['vbgamez_search_default_sort'] => 'selected="selected"'); 
  }

  $navbits['' . $vbulletin->options['vbgamez_search_path'] . '?' . $vbulletin->session->vars['sessionurl'] . ''] = $vbphrase['vbgamez'];
  $navbits['' . $vbulletin->options['vbgamez_path'] . '?' . $vbulletin->session->vars['sessionurl'] . 'do=search'] = $vbphrase['vbgamez_search'];

  if(!$show['errors'])
  {
          $navbits['' . $vbulletin->options['vbgamez_search_path'] . '?' . $vbulletin->session->vars['sessionurl'] . 'do=dosearch'] = $vbphrase['vbgamez_search_results'];
  }

  if($vbulletin->options['vbgamez_ratingsystem_enable'] AND $vbulletin->options['vbgamez_comments_enable'])
  {
      $vbg_colspan = 8;
  }elseif($vbulletin->options['vbgamez_ratingsystem_enable'] AND !$vbulletin->options['vbgamez_comments_enable'])
  {
      $vbg_colspan = 7;
  }elseif(!$vbulletin->options['vbgamez_ratingsystem_enable'] AND $vbulletin->options['vbgamez_comments_enable'])
  {
      $vbg_colspan = 7;
  }elseif(!$vbulletin->options['vbgamez_ratingsystem_enable'] AND $vbulletin->options['vbgamez_comments_enable'])
  {
      $vbg_colspan = 6;
  }

  $custom_additional_fields = $vBG_SearchFieldController->getDisplayView($_POST);

  if(VBG_IS_VB4)
  {
           $navbits = construct_navbits($navbits);
           $navbar = render_navbar_template($navbits);

           $templater = vB_Template::create($templatename);
           $templater->register_page_templates();
           $templater->register('navbar', $navbar);
           $templater->register('game_types', $game_types);
           $templater->register('search', $search);
           $templater->register('error_text', $error_text);
           $templater->register('custom_additional_fields', $custom_additional_fields);
           $templater->register('searchbits', $searchbits);
           $templater->register('searchtime', $searchtime);
           print_output($templater->render());
  }else{
	   $navbits = construct_navbits($navbits);
	   eval('$navbar = "' . fetch_template('navbar') . '";');
	   eval('print_output("' . fetch_template($templatename) . '");');
  }
}
