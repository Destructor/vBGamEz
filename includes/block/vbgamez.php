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


class vB_BlockType_vbgamez extends vB_BlockType
{
	protected $productid = 'vbgamez';

	protected $title = 'vBGaMez';

	protected $description = 'Game Servers Monitoring';

	protected $settings = array(
		'vbg_servers_limit' => array(
			'defaultvalue' => '5',
			'displayorder' => 1,
			'datatype'     => 'integer'
		),

		'vbg_servers_sort' => array(
			'defaultvalue' => '8',
			'optioncode'   => 'radio:piped
			1|vbgamez_by_game
			2|vbgamez_by_server_name
			3|vbgamez_by_id
			4|vbgamez_by_map
			5|vbgamez_by_players
			6|vbgamez_by_comments
			7|vbgamez_by_views
			8|vbgamez_by_rating',
			'displayorder' => 2,
			'datatype'     => 'integer'
		),

		'vbg_servers_order' => array(
			'defaultvalue' => '2',
			'displayorder' => 2,
			'optioncode'     => 'radio:piped
			1|vbgamez_asc
			2|vbgamez_desc',
			'datatype'     => 'integer'
		),

		'vbg_servers_ids' => array(
			'defaultvalue' => '',
			'displayorder' => 3,
			'datatype'     => 'free'
		),

		'vbg_servers_info' => array(
			'defaultvalue' => '0',
			'displayorder' => 4,
			'optioncode'     => 'yesno',
			'datatype'     => 'integer'
		)	,

			'vbg_server_show_sticked' => array(
				'defaultvalue' => '1',
				'displayorder' => 4,
				'optioncode'     => 'yesno',
				'datatype'     => 'integer'
			)

	);
 
     public function getData()
     {
     }

     public function getHTML()
     {  
               require_once('./packages/vbgamez/bootstrap.php');
               require_once('./packages/vbgamez/comments.php');

               vB_vBGamez::bootstrap(true);

               require_once(DIR . '/vb/vb.php');
               require_once(DIR . '/vb/phrase.php');
               vB::init(false);
			   define('VBG_IS_BLOCK', true);
               $set_sqlsort = $this->config['vbg_servers_sort'];
               $set_sortorder = $this->config['vbg_servers_order'];
               $ids = vB::$db->escape_string($this->config['vbg_servers_ids']);
               $limit = vB::$db->escape_string($this->config['vbg_servers_limit']);
			   $sticked = vB::$db->escape_string($this->config['vbg_server_show_sticked']);
                 switch ($set_sortorder)
                 {
	               case '1':
		               $sortorder = 'asc';
		               break;
	               case '2':
		               $sortorder = 'desc';
		               break;
	               default:
		               $sortorder = 'asc';
                 }


                 switch ($set_sqlsort)
                 {
	               case '3':
		               $sqlsort = 'vbgamez.id';
		               break;
	               case '2':
		               $sqlsort = 'vbgamez.cache_name';
		               break;
	               case '1':
		               $sqlsort = 'vbgamez.cache_game';
		               break;
	               case '6':
	               	       $sqlsort = 'vbgamez.comments';
	               	       break;
	               case '4':
		                $sqlsort = 'vbgamez.cache_map';
		               break;
	               case '5':
		               $sqlsort = 'vbgamez.cache_players';
		               break;
	               case '7':
		               $sqlsort = 'vbgamez.views';
		               break;
	               case '8':
		               $sqlsort = 'vbgamez.rating';
		               break;
	               default:
		               $sqlsort = 'vbgamez.rating';
                 }

                 $server_list = vB_vBGamez::VBG_Sidebar("s", $sqlsort, $sortorder, $limit, $ids, $sticked);

                 $server = array();
                                         
                         foreach($server_list AS $server)
                         {
                                      $misc   = vB_vBGamez::vbgamez_server_misc($server);
                                      $server = vB_vBGamez::vbgamez_server_html($server);

                                      $connectlink = vbgamez_software_link($server['b']['type'], $server['b']['ip'], $server['b']['c_port'], $server['b']['q_port'], $server['b']['s_port']);
                                      $misc['show_adv_info'] = $this->config['vbg_servers_info'];
									$server['s']['name'] = fetch_trimmed_title($server['s']['name'], vB::$vbulletin->options['vbgamez_blocks_trim']);
                                $templater = vB_Template::create('vbgamez_blockbits');
                                $templater->register('server', $server);
                                $templater->register('connectlink', $connectlink);
                                $templater->register('misc', $misc);

                              $vbgamez_serverbits .= $templater->render(); 

                         }

               if(!empty($vbgamez_serverbits))
               {
                         $templater = vB_Template::create('vbgamez_block');
                         $templater->register('vbgamez_serverbits', $vbgamez_serverbits);
                         return $templater->render(); 
               }
	}



}
