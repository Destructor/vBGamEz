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
 * @version $Revision: 54 $
 * @copyright GiveMeABreak
 */

class vbgamez_Widget_vbgamez extends vBCms_Widget
{

	protected $package = 'vbgamez';

	protected $class = 'vbgamez';

	protected $cache_ttl = 5;

	public function getConfigView()
	{

		$this->assertWidget();

		vB::$vbulletin->input->clean_array_gpc('r', array(
			'do'      => vB_Input::TYPE_STR,
			'vbg_servers_limit'      => vB_Input::TYPE_INT,
			'vbg_servers_sort'      => vB_Input::TYPE_INT,
			'vbg_servers_order'      => vB_Input::TYPE_INT,
			'vbg_servers_ids'      => vB_Input::TYPE_STR,
			'vbg_servers_info'      => vB_Input::TYPE_INT,
			'vbg_server_show_sticked'      => vB_Input::TYPE_INT,
		));

		$view = new vB_View_AJAXHTML('cms_widget_config');
		$view->title = new vB_Phrase('vbcms', 'configuring_widget_x', $this->widget->getTitle());
		$config = $this->widget->getConfig();

                require_once DIR . '/includes/functions_databuild.php';
                fetch_phrase_group('vbblocksettings');

		if ((vB::$vbulletin->GPC['do'] == 'config') AND $this->verifyPostId())
		{

			$config['vbg_servers_limit'] = vB::$vbulletin->GPC['vbg_servers_limit'];
			$config['vbg_servers_sort'] = vB::$vbulletin->GPC['vbg_servers_sort'];
			$config['vbg_servers_order'] = vB::$vbulletin->GPC['vbg_servers_order'];
			$config['vbg_servers_ids'] = vB::$vbulletin->GPC['vbg_servers_ids'];
			$config['vbg_servers_info'] = vB::$vbulletin->GPC['vbg_servers_info'];
			$config['vbg_server_show_sticked'] = vB::$vbulletin->GPC['vbg_server_show_sticked'];

			$verifyconfig = array('vbg_servers_limit'       => "5",
					'vbg_servers_sort' => '8',
					'vbg_servers_order' => '2',
					'vbg_servers_ids' => '',
					'vbg_servers_info' => '0',
					'vbg_server_show_sticked' => '0');

                        foreach($verifyconfig AS $key => $value)
                        {
                                 if(empty(vB::$vbulletin->GPC[$key]))
                                 {
                                       $config[$key] = $value;
                                 }
                        }

			$widgetdm = $this->widget->getDM();

			if ($this->content)
			{
				$widgetdm->setConfigNode($this->content->getNodeId());
			}

			$widgetdm->set('config', $config);

			$widgetdm->save();

			if (!$widgetdm->hasErrors())
			{
				if ($this->content)
				{
					$segments = array('node' => $this->content->getNodeURLSegment(),
										'action' => vB_Router::getUserAction('vBCms_Controller_Content', 'EditPage'));
					$view->setUrl(vB_View_AJAXHTML::URL_FINISHED, vBCms_Route_Content::getURL($segments));
				}

				$view->setStatus(vB_View_AJAXHTML::STATUS_FINISHED, new vB_Phrase('vbcms', 'configuration_saved'));
			}
			else
			{
				if (vB::$vbulletin->debug)
				{
					$view->addErrors($widgetdm->getErrors());
				}

				$view->setStatus(vB_View_AJAXHTML::STATUS_MESSAGE, new vB_Phrase('vbcms', 'configuration_failed'));
			}

			vB_Cache::instance()->clean(false);

			//vB_Cache::instance()->event('cms_vbgamez' . $this->widget->getId());
			//vB_Cache::instance()->cleanNow();
		}
		else
		{
			$configview = $this->createView('config');

			$configview->vbg_servers_limit = $config['vbg_servers_limit'];

			$configview->vbg_servers_ids = $config['vbg_servers_ids'];

			$configview->vbg_servers_info = iif($config['vbg_servers_info'], 'checked="checked"');

			$configview->vbg_servers_sort = vbgamez_Widget_vbgamez::construct_select_sort($config['vbg_servers_sort']);

			$configview->vbg_servers_order = vbgamez_Widget_vbgamez::construct_select_sort($config['vbg_servers_order'], 'order');

			$configview->vbg_server_show_sticked = iif($config['vbg_server_show_sticked'], 'checked="checked"');


			$this->addPostId($configview);

			$view->setContent($configview);

			$view->setStatus(vB_View_AJAXHTML::STATUS_VIEW, new vB_Phrase('vbcms', 'configuring_widget'));
		}

		return $view;

	}

	public function getPageView()
	{
		$config = $this->widget->getConfig();
		// Create view

		$config['template_name'] = 'vbgamez_vbcms';

		if (!isset($config['cache_ttl']) )
		{
			$config['cache_ttl'] = 5;
		}

		// Create view
		$view = new vBCms_View_Widget($config['template_name']);
		$view->class = $this->widget->getClass();
		$view->title = $view->widget_title = $this->widget->getTitle();
		$view->description = $this->widget->getDescription();

		$hash = $this->getHash($this->widget->getId());
		$view->output = vB_Cache::instance()->read($hash, true, true);

		if ($view->output)
		{
			return $view;
		}

		$this->assertWidget();

		try
		{
                              require_once('./packages/vbgamez/bootstrap.php');
                              require_once('./packages/vbgamez/comments.php');

                              vB_vBGamez::bootstrap(true);
							  define('VBG_IS_BLOCK', true);
                              $set_sqlsort = $config['vbg_servers_sort'];
                              $set_sortorder = $config['vbg_servers_order'];
                              $ids = vB::$db->escape_string($config['vbg_servers_ids']);
                              $limit = vB::$db->escape_string($config['vbg_servers_limit']);
							  $sticky = vB::$db->escape_string($config['vbg_server_show_sticked']);
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

                                $server_list = vB_vBGamez::VBG_Sidebar("s", $sqlsort, $sortorder, $limit, $ids, $sticky);

                                $server = array();
                                         
                                foreach($server_list AS $server)
                                {
                                        $misc   = vB_vBGamez::vbgamez_server_misc($server);
                                        $server = vB_vBGamez::vbgamez_server_html($server);

                                        $connectlink = vbgamez_software_link($server['b']['type'], $server['b']['ip'], $server['b']['c_port'], $server['b']['q_port'], $server['b']['s_port']);
                                        $misc['show_adv_info'] = $config['vbg_servers_info'];
										$server['s']['name'] = fetch_trimmed_title($server['s']['name'], vB::$vbulletin->options['vbgamez_blocks_trim']);
                                        $templater = vB_Template::create('vbgamez_vbcmsbits');
                                        $templater->register('server', $server);
                                        $templater->register('connectlink', $connectlink);
                                        $templater->register('misc', $misc);

                              	        $vbgamez_serverbits .= $templater->render(); 

                         	}

				$view->output = $vbgamez_serverbits;

			vB_Cache::instance()->write($hash,
			   $view->output, $config['cache_ttl'],
			   array($this->package . '_event_' . $this->class . '_' . $this->widget->getId()));
		}

		catch(Exception $e)
		{
			$view->output = '';

		}

		return $view;
	}

	protected function getHash($widgetid)
	{
		$context = new vB_Context('widget' , array('widgetid' =>$widgetid));
		return strval($context);

	}


	public function construct_select_sort($selected_value, $type = 'sort')
	{
                global $vbphrase;

                  if($type == 'sort')
                  {
                         $sort_types = array('1' => 'vbgamez_by_game',
			                     '2' => 'vbgamez_by_server_name',
			                     '3' => 'vbgamez_by_id',
			                     '4' => 'vbgamez_by_map',
			                     '5' => 'vbgamez_by_players',
			                     '6' => 'vbgamez_by_comments',
			                     '7' => 'vbgamez_by_views',
			                     '8' => 'vbgamez_by_rating');

                         $select = '<select name="vbg_servers_sort">';
                  }else{
                         $sort_types = array('1' => 'vbgamez_asc',
			                     '2' => 'vbgamez_desc');

                         $select = '<select name="vbg_servers_order">';
                  }

                  foreach($sort_types as $key => $title)
                  {
                         $select .= '<option value="' . $key . '" ' . iif($selected_value == $key, 'selected="selected"') . '>' . $vbphrase[$title] . '</option>';
                  }

                  $select .= '</select>';

            return $select;

	}


}