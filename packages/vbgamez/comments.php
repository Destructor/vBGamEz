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
 * VBGamEz комментарии
 *
 * @package vBGamEz
 * @author GiveMeABreak aka Developer
 * @version $Revision: 113 $
 * @copyright GiveMeABreak
 */

class vBGamez_Comments
{
	/*Генерация========================================================================*/

	/**
	 * Возвращение информации о сервере
	 *
	 */

          public static function fetch_comment($comment, $template = 'vbgamez_commentbits')
          {
            global $bbcode_parser, $server, $vbulletin, $vbphrase, $stylevar, $show;

                $comment['musername'] = fetch_musername($comment);
                $comment['pagetext'] = fetch_censored_text($comment['pagetext']);

                      if(VBG_IS_VB4)
                      {
	                    $comment['pagetext'] = parse_video_bbcode($comment['pagetext']);
                      }
		            $comment['pagetext'] = $bbcode_parser->do_parse($comment['pagetext']);
		            $comment['date'] = vbdate($vbulletin->options['dateformat'].' '.$vbulletin->options['timeformat'],$comment['dateline'],true);
		            $comment['deletedbyuserid'] = 'member.php?u='.$comment['deletedbyuserid'];

		            $comment = array_merge($comment , convert_bits_to_array($comment['options'] , $vbulletin->bf_misc_useroptions));
		            $comment = array_merge($comment , convert_bits_to_array($comment['adminoptions'] , $vbulletin->bf_misc_adminoptions));

		            cache_permissions($comment, false);

			if ($comment['avatarid'])
			{
				$avatarurl = $comment['avatarpath'];
			}
			else
			{
				if ($comment['hascustomavatar'] AND $vbulletin->options['avatarenabled'] AND ($comment['permissions']['genericpermissions'] & $vbulletin->bf_ugp_genericpermissions['canuseavatar'] OR $comment['adminavatar']))
				{
					if ($vbulletin->options['usefileavatar'])
					{
						$avatarurl = $vbulletin->options['avatarurl'] . "/avatar$comment[userid]_$comment[avatarrevision].gif";
					}
					else
					{
						$avatarurl = 'image.php?' . $vbulletin->session->vars['sessionurl'] . "u=$comment[userid]&amp;dateline=$comment[avatardateline]";
					}
				}
				else
				{
					$avatarurl = '';
				}
			}

                $comment['avatarurl'] = $avatarurl;
                $comment['pagetext'] = fetch_word_wrapped_string($comment['pagetext'], 45);

                if(VBG_IS_VB4)
                {
		           $comment['memberaction_dropdown'] = construct_memberaction_dropdown($comment);
                }

                $ajax_restore = '<div id="comment_edit_restore_' . $comment['id'] . '" style="display:none"></div>';

                if(VBG_IS_VB4)
                { 
                         $templater = vB_Template::create($template);

                         $templater->register('comment', $comment);
						 $templater->register('serveruserid', ($server['i']['userid'] ? $server['i']['userid'] : $comment['owner']));
                         return $templater->render().$ajax_restore;
                }else{
                         eval('$return = "' . fetch_template($template) . '";');
                         return $return.$ajax_restore;
                }
          }


	/*Установка правельной кодировки========================================================================*/

	/**
	 * Возвращяет текст в правельной кодировке
	 *
	 */

		public static function set_comment_charset($comment)
         {
           if(vB_vBGamez::fetch_stylevar('charset') != 'UTF-8')
           {
                  return iconv('UTF-8', vB_vBGamez::fetch_stylevar('charset'), $comment);
           }else{
                  return $comment;
           }
         }
}
?>
