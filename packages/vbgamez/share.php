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

class vB_vBGamez_Share
{
      private static $publishers = array('facebook' => 1, 'mailru' => 2, 'vk' => 4, 'twitter' => 8, 'odnoklassniki' => 16, 'friendfeed' => 32, 'livejournal' => 64, 'yaru' => 128);

      public static function canPublishTo($type)
      {
		if(vB_vBGamez::vb_call()->options['vbgamez_share'] & self::$publishers[$type])
		{
					return true;
		}
		return false;
      }

      public static function registerPublishersToShowArray($show)
      {
                foreach(self::$publishers AS $type => $bitfield)
		{
			if(self::canPublishTo($type))
			{
				$show['vbg_share_' . $type . ''] = true;
			}
		}
		return $show;
      }

      public static function preparePublishTitle($title)
      {
		$search = array('#', "'", '"');
		$replace = array(' ', '', '');
		return str_replace($search, $replace, $title);
      }
}