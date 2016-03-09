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
 * VBGamEz генерация фрейма
 *
 * @package vBGamEz
 * @author GiveMeABreak aka Developer, LGSL author
 * @version $Revision: 200 $
 * @copyright GiveMeABreak
 */

class vBGamez_FrameView
{
         var $framecache;
         var $framestylevarcache;

	 public function instance()
	 {
		return new vBGamez_FrameView();
	 }

         function vBGamez_FrameView()
         {
                global $vbulletin;

                $this->framecache =& $vbulletin->vbgamez_framecache;
                $this->framestylevarcache =& $vbulletin->vbgamez_framestylevarcache;
         } 

		function getFrameSelect($selected_id)
		{
			global $vbulletin;
			$serverid = $vbulletin->GPC['id'];
			$framelist = '';
			foreach($this->framecache AS $frame)
            {
					$framelist .= '<option value="' . $frame['frameid'] . '">' . htmlspecialchars_uni($frame['name']) . '</option>';
					$framecount++;
			}
			if($framecount == 1)
			{
				return '';
			}
			if(VBG_IS_VB4)
			{
				$tpl = vB_Template::create('vbgamez_frame_ajax_select');
				$tpl->register('framelist', $framelist);
				$tpl->register('serverid', $serverid);
				return $tpl->render();
			}else{
				return 'need convert frame_ajax_select for vb3';
			}
		}
		function loadFrame($id, $serverid)
		{
			$frame = $this->framecache[$id];
			if(empty($frame))
			{
				return false;
			}
			
			$frame['name'] = htmlspecialchars_uni($frame['name']);
            
			 $width = intval($frame['width'] + $this->getFrameWidth($frame['frameid']));

             $height = intval($frame['height'] + $this->getFrameHeight($frame['frameid']));

			 if($statisheight = $this->setFixedWidthAndHeight('addheight', $frame['frameid']))
		 	 {
				 if($statisheight['action'] == 'add')
				 {
				 	$height += $statisheight['value'];
				 }else{
					$height = $statisheight['value'];
				}
			}
			 if($statiswidth = $this->setFixedWidthAndHeight('addwidth', $frame['frameid']))
		 	 {
				 if($statiswidth['action'] == 'add')
				 {
				 	$width += $statiswidth['value'];
				 }else{
					$width = $statiswidth['value'];
				}
			 }
			
			$frame['is_configure'] = $this->is_configure($frame['frameid'], $frame['is_configure']);
			
			$url = vB_vBGamez::fetch_codes_url('iframe', $serverid, $frame['frameid']);
		    $url .= $this->createDefaultRequestQuery($frame['frameid']);
		
			$frametpl = vB_Template::create('vbgamez_framebit');
			$frametpl->register('frame', $frame);
			$frametpl->register('height', $height);
			$frametpl->register('width', $width);
			$frametpl->register('url', $url);
			$frameContent = $frametpl->render();
			
			return vB_vBGamez::jsonEncode(array('content' => vB_vBGamez::toUTF8($frameContent), 'title' => vB_vBGamez::toUTF8($frame['name']), 'configure' => $frame['is_configure']));
		}
	 /*========================================================================
         *
	 * Конструирование списка фреймов для отображения в деталях сервера
	 *
	 */

        function construct_framebits($serverid)
        {
              global $vbphrase, $vbulletin, $show;

              if(empty($this->framecache))
              {
                             if(vB_vBGamez::is_vb4())
                             {
                                            return '<br /><br />'.$vbphrase['vbgamez_noframes'].'<br /><br />';
                             }else{
                                            return '<td class="alt1" align="center"><br /><br />'.$vbphrase['vbgamez_noframes'].'<br /><br /></td>'; 
                             }
              }

              foreach($this->framecache AS $frame)
              {
					 $frameCount++;
					 if($frameCount > 1 AND vB_vBGamez::vb_call()->options['vbgamez_show_full_frames'] == 0) { break; }
                     if(!empty($frame['code'])) { $show['customize'] = true; }

                     $frame['name'] = htmlspecialchars_uni($frame['name']);

                     $width = intval($frame['width'] + $this->getFrameWidth($frame['frameid']));

                     $height = intval($frame['height'] + $this->getFrameHeight($frame['frameid']));

					 if($statisheight = $this->setFixedWidthAndHeight('addheight', $frame['frameid']))
				 	 {
						 if($statisheight['action'] == 'add')
						 {
						 	$height += $statisheight['value'];
						 }else{
							$height = $statisheight['value'];
						}
					}
					 if($statiswidth = $this->setFixedWidthAndHeight('addwidth', $frame['frameid']))
				 	 {
						 if($statiswidth['action'] == 'add')
						 {
						 	$width += $statiswidth['value'];
						 }else{
							$width = $statiswidth['value'];
						}
					 }
					
                     $frame['is_configure'] = $this->is_configure($frame['frameid'], $frame['is_configure']);

                     $url = vB_vBGamez::fetch_codes_url('iframe', $vbulletin->GPC['id'], $frame['frameid']);
		     $url .= $this->createDefaultRequestQuery($frame['frameid']);

				 	 $frametpl = vB_Template::create('vbgamez_framebit');
					 $frametpl->register('frame', $frame);
					 $frametpl->register('height', $height);
					 $frametpl->register('width', $width);
					 $frametpl->register('url', $url);
					 $frameContent = $frametpl->render();
                     if(VBG_IS_VB4)
                     {
                              $templater = vB_Template::create('vbgamez_framebits');
                              $templater->register('frame', $frame);
                              $templater->register('serverid', $serverid);
							  $templater->register('frame_ajax_select', $this->getFrameSelect($frame['frameid']));
							  $templater->register('framecontent', $frameContent);
                              $framebits .= $templater->render();
                     }else{
                              eval('$framebits .= "' . fetch_template('vbgamez_framebits') . '";');
                     }
              } 

          return $framebits;

        }

	function getFrameWidth($frameid)
	{
	       $Width = 0;
               foreach($this->getStylevars($frameid) AS $stylevarid => $stylevardata)
               {
			$stylevarWidth = $this->getWidthHeightFromFrame('addwidth', $frameid, $stylevarid);
                        if($stylevarWidth)
                        {
                                     $Width += $stylevarWidth;
                        }
               }

               return $Width;
	}

	function getFrameHeight($frameid)
	{
	       $Height = 0;
               foreach($this->getStylevars($frameid) AS $stylevarid => $stylevardata)
               {
			$stylevarHeight = $this->getWidthHeightFromFrame('addheight', $frameid, $stylevarid);
                        if($stylevarHeight)
                        {
                                     $Height += $stylevarHeight;
                        }
               }

               return $Height;
	}

	function getWidthHeightFromFrame($type = 'addwidth', $frameid, $needstylevarid)
	{
	       if(is_array($this->framestylevarcache))
		{
			foreach($this->framestylevarcache AS $stylevarid => $data)
			{
				$_REQUEST[$data['variable']] = str_replace('#', '', $_REQUEST[$data['variable']]);
				if($data['frameid'] == $frameid AND $data['variable'] == $needstylevarid AND ($data[$type] != 0 AND $data[$type] != 'SET_DEFAULT_VALUE' AND $data[$type] != 'ADD_TO_CURRENT_VALUE') AND ($_REQUEST[$data['variable']] == 1 OR $_REQUEST[$data['variable']] == 'checked' OR $data['default'])) 
				{
					if($_REQUEST['do'] == 'previewblock')
					{
						if(!empty($_REQUEST[$data['variable']]))
						{
							return intval($data[$type]);
						}
					}else{
							return intval($data[$type]);
					}
				}
			}
		}
	}
	
	function setFixedWidthAndHeight($get = 'addwidth', $frameid)
	{
		   if(is_array($this->framestylevarcache))
		{
			foreach($this->framestylevarcache AS $stylevarid => $data)
			{
				$_REQUEST[$data['variable']] = str_replace('#', '', $_REQUEST[$data['variable']]);
				
				if($data['frameid'] == $frameid AND ($data[$get] == 'ADD_TO_CURRENT_VALUE' OR $data[$get] == 'SET_DEFAULT_VALUE'))
				{
					if(!empty($data[$get]))
					{
						if($data[$get] == 'ADD_TO_CURRENT_VALUE')
						{
						 	return array('action' => 'add', 'value' => ($_REQUEST[$data['variable']] ? intval($_REQUEST[$data['variable']]) : $data['default']));
						}else{
						 	return array('action' => 'add', 'value' => ($_REQUEST[$data['variable']] ? intval($_REQUEST[$data['variable']]) : $data['default']));
						}
					}
				}
			}
		}
	}

	 /*========================================================================
         *
	 * Парсинг stylevar'а
	 *
	 */

        function parseVar($var, $default = '')
        {
                 return iif($_REQUEST[$var], '#'.htmlspecialchars($_REQUEST[$var]), $default);
        }

	 /*========================================================================
         *
	 * Получение stylevars
	 *
	 */

        function getStylevars($frameid)
        {
               $stylevars = array();

               foreach($this->framestylevarcache AS $stylevarid => $stylevardata)
               {
                        if($stylevardata['frameid'] != $frameid) 
                        {
                                       continue; 
                        }

						if($stylevardata['type'] == 'input')
						{
							$stylevars[$stylevardata['variable']] = intval(($_REQUEST[$stylevardata['variable']] ? $_REQUEST[$stylevardata['variable']] : $stylevardata['default']));
						}else{
                        	$stylevars[$stylevardata['variable']] = $this->parseVar($stylevardata['variable'], $stylevardata['default']);
						}
               }

               return $stylevars;      
        }

	 /*========================================================================
         *
	 * Настраиваемый ли это фрейм..
	 *
	 */
        function is_configure($frameid, $isconfigured)
        {
               if(!$isconfigured) { return false; }

               foreach($this->framestylevarcache AS $stylevarid => $stylevardata)
               {
                        if($stylevardata['frameid'] != $frameid) 
                        {
                                       continue; 
                        }

                        return true;
               }

               return false;
        }

	 /*========================================================================
         *
	 * Создание REQUEST-запроса
	 *
	 */

	function getStylevarType($frameid, $needstylevarid)
	{
	       if(is_array($this->framestylevarcache))
		{
			foreach($this->framestylevarcache AS $stylevarid => $data)
			{
				if($data['frameid'] == $frameid AND $data['variable'] == $needstylevarid)
				{
					return $data['type'];
				}
			}
		}
	}

        function createRequestQuery($frameid)
        {
               
               foreach($this->getStylevars($frameid) AS $stylevarid => $stylevardata)
               {
                        if($this->getStylevarType($frameid, $stylevarid) == 'checkbox')
                        {
				     $stylevardata = str_replace('#', '', $stylevardata);
                                     $requestData .= '&'.$stylevarid.'='.iif($stylevardata == 1, 1, 0);
                        }else{
                                     $requestData .= '&'.$stylevarid.'='.str_replace('#', '', $stylevardata);
                        }
               }

               return $requestData;
        }

	function createDefaultRequestQuery($frameid)
	{
               foreach($this->getStylevars($frameid) AS $stylevarid => $stylevardata)
               {
                        if($this->getStylevarType($frameid, $stylevarid) == 'checkbox' AND $stylevardata == 'checked')
                        {
                                     $requestData .= '&'.$stylevarid.'=1';
                        }
               }

               return $requestData;
	 }
	 /*========================================================================
         *
	 * Создание JS значений
	 *
	 */

        function createJsVars($frameid)
        {
               foreach($this->getStylevars($frameid) AS $stylevarid => $stylevardata)
               {
                        if($stylevarid == 'players') { continue; }

                        if($this->getStylevarType($frameid, $stylevarid) == 'checkbox')
                        {
                                     $jsvars .= 'var '.$stylevarid.' = (fetch_object(\'vbgamez_' . $stylevarid . '\').checked ? \'1\' : \'0\');';
                        }else{
                                     $jsvars .= 'var '.$stylevarid.' = fetch_object(\'' . iif(!VBG_IS_VB4, 'usercss_') . 'vbgamez_' . $stylevarid . '\').value; ';
                        }
               }

               return $jsvars;
        }

        function createJsRequestVars($frameid)
        {
               foreach($this->getStylevars($frameid) AS $stylevarid => $stylevardata)
               {
                        if($stylevarid == 'players') { continue; }

                        $jsvars .= ' + \'&'.$stylevarid. '=\' + ' . $stylevarid . '';
               }

               return $jsvars;
        }

	 /*========================================================================
         *
	 * Создание выбора цветов
	 *
	 */

        function createPickerData($frameid)
        {
               global $vbphrase, $show;

               foreach($this->framestylevarcache AS $stylevarid => $stylevardata)
               {
                        if($stylevardata['frameid'] != $frameid) 
                        {
                                       continue; 
                        }

                        $title = htmlspecialchars($stylevardata['title']);
                        $variable = htmlspecialchars($stylevardata['variable']);
                        $default = htmlspecialchars($stylevardata['default']);
                        $description = htmlspecialchars($stylevardata['description']);

                        $show['is_checkbox'] = ($stylevardata['type'] == 'checkbox');
                        $show['is_input'] = ($stylevardata['type'] == 'input');

                        if(VBG_IS_VB4)
                        {
                                   $tpl = vB_Template::create('vbgamez_frame_options');
                                   $tpl->register('title', $title);
                                   $tpl->register('variable', $variable);
                                   $tpl->register('default', $default);
                                   $tpl->register('description', $description);
                                   $bits .= $tpl->render();
                        }else{
                               eval('$bits .= "' . fetch_template('vbgamez_frame_options') . '";');
                        }
               }

               return $bits;
        }

	 /*========================================================================
         *
	 * Конструирование юзербара
	 *
	 */

       function construct_frame($frameid, $server, $misc, $connectlink)
       {
           global $vbulletin, $vbphrase, $db;
 
           $frameinfo =& $this->framecache[$frameid];

           if(!$frameinfo)
           {
                      return false;
           }

           $show['is_configure'] = $this->is_configure($frameid, $frameinfo['is_configure']);

           if(!empty($server['s']['players']) AND !empty($server['s']['playersmax']))
           {
                    $playersline = 100 / $server['s']['playersmax'] * $server['s']['players'];
           }else{
                    $playersline = 0;
           }

	   $defaultGraphicId = vBGamez_Stats_Display::create($vbulletin, $server)->getDefaultGraphicId();
           if(!empty($server['p']))
           {
             if(!empty($frameinfo['codeplayers']))
             {
                    foreach ($server['p'] as $player_key => $player)
                    {
                           $playerid++;
			   if(empty($player['name']) OR str_replace(' ', '', $player['name']) == '') { $player['name'] = 'Unknown'; }
	
                           if(VBG_IS_VB4)
                           {
                              $templater = vB_Template::create('vbgamez_frame_' . $frameid . '_players');
                              $templater->register('player', $player);
                              $templater->register('playerid', $playerid);
                              $templater->register('vbg_style', $this->getStylevars($frameid));
                              $players .= $templater->render();
                           }else{
                               eval('$players .= "' . fetch_template('vbgamez_frame_' . $frameid . '_players') . '";');
                           }
                   }
             }    
          }else{
             if(!empty($frameinfo['codenoplayers']))
             {
                    if(VBG_IS_VB4)
                    {
                       $templater = vB_Template::create('vbgamez_frame_' . $frameid . '_noplayers');
                       $templater->register('vbg_style', $this->getStylevars($frameid));
                       $players .= $templater->render();
                    }else{
                        eval('$players = "' . fetch_template('vbgamez_frame_' . $frameid . '_noplayers') . '";');
                    }
             }
           }

           if(VBG_IS_VB4)
           {
                      $tpl = vB_Template::create('vbgamez_frame_' . $frameid);
                      $tpl->register_page_templates();
                      $tpl->register('server', $server);
                      $tpl->register('misc', $misc);
                      $tpl->register('id', $server['o']['id']);
                      $tpl->register('connectlink', $connectlink);
                      $tpl->register('players', $players);
                      $tpl->register('vbg_style', $this->getStylevars($frameid));
                      $tpl->register('playersline', $playersline);
                      $tpl->register('defaultGraphicId', $defaultGraphicId);
                      return $tpl->render();
           }else{
                      eval('$return = "' . fetch_template('vbgamez_frame_' . $frameid) . '";');
                      return $return;
           }
       }
}
