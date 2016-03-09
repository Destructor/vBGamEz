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
 * VBGamEz мониторинг игр wow, la2 :: функции
 *
 * @package vBGamEz
 * @author GiveMeABreak aka Developer, LGSL author
 * @version $Revision: 22 $
 * @copyright GiveMeABreak
 */

class vBGamez_dbGames_Bootstrap
{
           // Поддерживаемые типы игр
           private static $dbgames = array('wow' => array(
                                                          'name' => 'World of Warcraft (Mangos)',
                                                          'url' => '{vbg_script}?do=realmlist&ip={IP}&port={S_PORT}',
                                                          'showdbname' => false,
                                                          'showservername' => false,
                                                          'showserverip' => false,
                                                           ),
											'wow_t' => array('name' => 'World of Warcraft (Trinity)',
												           'url' => '{vbg_script}?do=realmlist&ip={IP}&port={S_PORT}',
												           'showdbname' => false,
												           'showservername' => false,
												           'showserverip' => false,
												           ),
                                           'la2' => array(
                                                          'name' => 'LineAge 2 (l2b)',
                                                          'url' => '{vbg_script}?do=realmlist&ip={IP}&port={S_PORT}', // who needed to LA2?
                                                          'showdbname' => true,
                                                          'showservername' => true,
                                                          'showserverip' => true,
                                                           )
                                          ); 
		   
		   private static $_salt = 'gDqVE#Ep/-v51J7d`K!BaX/xsKiaDs';
           /**
           * Нужно ли показывать поля
           *
           */

           public static function fieldIsRequired($gametype, $fieldname)
           {     
                   if(empty(vBGamez_dbGames_Bootstrap::$dbgames))
                   {
                                return false;
                   }

                   return vBGamez_dbGames_Bootstrap::$dbgames[$gametype][$fieldname];
           }

           /**
           * Нужно ли показывать поля
           *
           */

           public static function get_js_fields()
           {     
                   if(empty(vBGamez_dbGames_Bootstrap::$dbgames))
                   {
                                return '';
                   }

                   foreach(vBGamez_dbGames_Bootstrap::$dbgames AS $gametype => $game)
                   {
                                $jsbits .= ' var ' . $gametype . '_showdbname = ' . intval(vBGamez_dbGames_Bootstrap::$dbgames[$gametype]['showdbname']) . ';';
                                $jsbits .= ' var ' . $gametype . '_showservername = ' . intval(vBGamez_dbGames_Bootstrap::$dbgames[$gametype]['showservername']) . ';';
                                $jsbits .= ' var ' . $gametype . '_showserverip = ' . intval(vBGamez_dbGames_Bootstrap::$dbgames[$gametype]['showserverip']) . ';';

                   }
                   return $jsbits;

           }

           /**
           * Загрузка класса
           *
           */

           public static function fetchClassLibary($gametype, $dbinfo)
           {     
                 if(!vBGamez_dbGames_Bootstrap::vbgamez_is_db_game($gametype))
                 {
                              return false;
                 }
                 
                 $class = 'vBGamez_dbGames_' . $gametype . '';

                 if(class_exists($class))
                 {
                        return new $class($dbinfo);
                 }else{
                        trigger_error('vBGamEz Fatal Error: Called unknown class "'.$class.'"'); exit;
                 }
           }

           /**
           * Является ли игра с БД?
           *
           */

           public static function vbgamez_is_db_game($value)
           {
                   if(empty(vBGamez_dbGames_Bootstrap::$dbgames))
                   {
                                return false;
                   }

                   foreach(vBGamez_dbGames_Bootstrap::$dbgames AS $gametype => $game)
                   {
                             if($value == $gametype)
                             {
                                        return true;
                             }
                   }
           }

           /**
           * проверка настроек
           *
           */

           public static function vbgamez_verify_dbsettings($address, $user, $pass)
           {
                     global $vbulletin;
                     $dbname = @mysql_connect($address, $user, $pass);
                     if(!$dbname OR mysql_error()) { return false; }

                     return $dbname;
           }

           /**
           * получение информации о бд 
           * ИМЕЙТЕ СОВЕСТЬ!
           *
           */

           public static function vbgamez_fetch_db_info($data)
           {
                           return unserialize(base64_decode($data.self::$_salt));
           }

           /**
           * шифрование информации о БД
           * ИМЕЙТЕ СОВЕСТЬ!
           *
           */

           public static function vbgamez_encode_db_info($data)
           {
                           return base64_encode(serialize($data).self::$_salt);
           }


           /**
           * конструирование аптайма
           *
           */

           public static function construct_uptime($serveruptime)
           {
                                $uptime = array();

                                $uptime['day'] = floor(($serveruptime / 86400) * 1.0);

                                $calc1 = $uptime['day'] * 86400;
                                $calc2 = $serveruptime - $calc1;

                                $uptime['hour'] = floor(($calc2 / 3600) * 1.0);

                                if ($uptime['hour'] < 10)
                                {
                                        $uptime['hour'] = "0".$uptime['hour'];
                                }          

                                $calc3 = $uptime['hour'] * 3600;

                                $calc4 = $calc2 - $calc3;

                                $uptime['min'] = floor(($calc4 / 60) * 1.0);

                                if ($uptime['min'] < 10)
                                {
                                           $uptime['min'] = "0".$uptime['min'];
                                }

                                $calc5 = $uptime['min'] * 60;

                                $uptime['sec'] = floor(($calc4 - $calc5) * 1.0);

                                if ($uptime['sec'] < 10)
                                {
                                        $uptime['sec'] = "0".$uptime['sec'];
                                }

                      return $uptime;
           }

           /**
           * Получение DB-игр
           *
           */

           public static function get_db_gameTypes()
           {     
                       return vBGamez_dbGames_Bootstrap::$dbgames;
           }

           /**
           * Получение DB-игр JS
           *
           */

           public static function get_js_db_gameTypes()
           {     
                      if(empty(vBGamez_dbGames_Bootstrap::$dbgames))
                      {
                                return '""';
                      }

                      foreach(vBGamez_dbGames_Bootstrap::$dbgames AS $gametype => $game)
                      {
                                $games .= ', "' . $gametype . '"';
                      }

                      $games = substr($games, 2);

                      return 'new Array('.$games.');';
           }
}


/**
 * VBGamEz мониторинг WOW
 *
 * @package vBGamEz
 * @author GiveMeABreak aka Developer, LGSL author
 * @version $Revision: 151 $
 * @copyright GiveMeABreak
 */

class vBGamez_dbGames_wow
{
          /**
           * конструктор :P
           *
           */

           function vBGamez_dbGames_wow($dbinfo = '')
           { 
                  if(!empty($dbinfo))
                  {
                        $this->game['name'] = 'wow';

                        $this->game['db'] = vBGamez_dbGames_Bootstrap::vbgamez_fetch_db_info($dbinfo);
                        $this->game['db']['characters'] = 'characters';
                        $this->game['db']['mangos'] = 'mangos';
                        $this->game['db']['realmd'] = 'realmd';
                  }
            }

          /**
           * подключение к базе данных вова
           *
           */

           function connect_to_db()
           {
                  $dbname = @mysql_connect($this->game['db']['address'], $this->game['db']['user'], $this->game['db']['password']);

                  if(!$dbname OR mysql_error())
                  {
                                return false;
                  }

                  @mysql_select_db($this->game['db']['characters'], $dbname);

                  return $dbname;
           }

          /**
           * проверка таблиц
           *
           */

           function verify_db_tables()
           {
                  if(!($dbname = $this->connect_to_db()))
                  {
                            return false;
                  }

                  $data = @mysql_fetch_array(mysql_query("SELECT name, address, port FROM ".$this->game['db']['realmd'].".realmlist", $dbname), MYSQL_ASSOC);

                  if(empty($data['name']) OR empty($data['address']) OR empty($data['port']))
                  {
                           return false;
                  }else{
                           return true;
                  }
           }

          /**
           * получение информации (игроки, макс. игроки и тд)
           *
           */

           function fetch_info()
           {
                  if(!($dbname = $this->connect_to_db()))
                  {
                            return false;
                  }

                  $server['s'] = array();

                  $server['e'] = array();

                  $server['p'] = array();

                  $getplayers = mysql_query("SELECT * FROM characters WHERE online = 1", $dbname);

                  while($player = @mysql_fetch_array($getplayers, MYSQL_ASSOC))
                  {
                       $players[$player['account']] = $player;
                       $playersonline++;
                  }

                  $server['s'] = @mysql_fetch_array(mysql_query("SELECT name, address, port FROM ".$this->game['db']['realmd'].".realmlist", $dbname), MYSQL_ASSOC);

                  $server['p'] = $players;
                  $server['s']['players'] = $playersonline;
                  $server['s']['playersmax'] = @mysql_result(mysql_query("SELECT MAX(`maxplayers`) FROM ".$this->game['db']['realmd'].".uptime", $dbname),0);

                  unset($players);

                  return $server;
            }


          /**
           * получение забаненных аккаунтов
           *
           */

            function fetch_info_banned()
            {
                  if(!($dbname = $this->connect_to_db()))
                  {
                            return false;
                  }

                  $fetch_banned_ip = mysql_query("SELECT ip, banreason, bandate, unbandate FROM ".$this->game['db']['realmd'].".ip_banned");   
                  while ($ban = mysql_fetch_array($fetch_banned_ip, MYSQL_ASSOC))   
                  {   
                            $this->bannedips[$ban['ip']] = $ban;
                  }
 
                  $fetch_banned_acc = mysql_query("SELECT * FROM ".$this->game['db']['realmd'].".account_banned AS account_banned
                                                   LEFT JOIN ".$this->game['db']['realmd'].".account AS account ON(account.id = account_banned.id)
                                                   WHERE active = 1 ORDER BY bandate DESC LIMIT 100");  
 
                  while ($ban = mysql_fetch_array($fetch_banned_acc, MYSQL_ASSOC))   
                  {   
                            $this->banned[$ban['id']] = $ban;
                  }

                  
            }

          /**
           * получение доп.инфы
           *
           */

            function fetch_info_additional_info()
            {
                  if(!($dbname = $this->connect_to_db()))
                  {
                            return false;
                  }

                  $server['e']['accounts'] = @mysql_fetch_array(mysql_query("SELECT COUNT(*) AS count FROM ".$this->game['db']['realmd'].".account", $dbname), MYSQL_ASSOC);

                  $server['e']['char'] = @mysql_fetch_array(mysql_query("SELECT COUNT(*) AS count FROM ".$this->game['db']['characters'].".characters", $dbname), MYSQL_ASSOC);

                  $server['e']['guild'] = @mysql_fetch_array(mysql_query("SELECT COUNT(*) AS count FROM ".$this->game['db']['characters'].".guild", $dbname), MYSQL_ASSOC);

                  $server['e']['allies'] = @mysql_fetch_array(mysql_query("SELECT COUNT(*) AS count FROM ".$this->game['db']['characters'].".characters WHERE race IN (1,3,4,7,11)", $dbname), MYSQL_ASSOC);

                  $server['e']['horde'] = @mysql_fetch_array(mysql_query("SELECT COUNT(*) AS count FROM ".$this->game['db']['characters'].".characters WHERE race IN (2,5,6,8,10)", $dbname), MYSQL_ASSOC);

                  foreach($server['e'] AS $field => $value)
                  {
                      $server['e'][$field] = $server['e'][$field]['count'];
                  }

                  $uptime_query = @mysql_fetch_array(mysql_query("SELECT * FROM ".$this->game['db']['realmd'].".uptime ORDER BY starttime DESC", $dbname), MYSQL_ASSOC);

                  $server['e']['uptime'] = TIMENOW - $uptime_query['starttime'];

                 return $server['e'];
            }

}

/**
 * VBGamEz :: порт запроса Wow
 *
 */

function vbgamez_query_wow(&$server, &$vbgamez_need, &$vbgamez_fp)
{
    global $vbphrase;

    $dbgame = new vBGamez_dbGames_wow($server['dbinfo']);

    if(!($wowinfo = $dbgame->fetch_info()))
    {
        $server['s'] = array();

        $server['e'] = array();

        $server['p'] = array();

    }else{

        $server['s'] = array(
        "banned"       => true,
        "game"       => "wow",
        "name"       => $wowinfo['s']['name'],
        "map"        => iif($wowinfo['s']['map'], $wowinfo['s']['map'], "---"),
        "players"    => $wowinfo['s']['players'],
        "playersmax" => $wowinfo['s']['playersmax'],
        "password"   => 0);

        $server['e'] = true;

        if(!empty($wowinfo['p']))
        {
             foreach ($wowinfo['p'] as $key => $name)
             {
                 if(in_array($name['race'], array(1, 3, 4, 7, 11)))
                 {
                            $server['p'][$key]['fr'] = '<img src="' . vB_vBGamez::fetch_image('images/vbgamez/icons/wow/alliance.gif') . '" alt="" />';
                 }

                 if(in_array($name['race'], array(2, 5, 6, 8, 10)))
                 {
                            $server['p'][$key]['fr'] = '<img src="' . vB_vBGamez::fetch_image('images/vbgamez/icons/wow/horde.gif') . '" alt="" />';
                 }

                 $server['p'][$key]['name'] = $name['name'];
                 $server['p'][$key]['race'] = '<img src="' . vB_vBGamez::fetch_image('images/vbgamez/icons/wow/race/' . $name['race'] . '-1.gif') . '" alt="" />';
                 $server['p'][$key]['class'] = '<img src="' . vB_vBGamez::fetch_image('images/vbgamez/icons/wow/class/' . $name['class'] . '.gif') . '" alt="" />';
                 $server['p'][$key]['level'] = $name['level'];
            }
        }
    }

    return true;
}






/**
 * VBGamEz мониторинг LA2
 *
 * @package vBGamEz
 * @author GiveMeABreak aka Developer, LGSL author
 * @version $Revision: 45 $
 * @copyright GiveMeABreak
 */

class vBGamez_dbGames_la2
{
          /**
           * конструктор :P
           *
           */

           function vBGamez_dbGames_la2($dbinfo = '')
           { 
                  if(!empty($dbinfo))
                  {
                        $this->game['db'] = vBGamez_dbGames_Bootstrap::vbgamez_fetch_db_info($dbinfo);
                  }
            }

          /**
           * подключение к базе данных вова
           *
           */

           function connect_to_db()
           {
                  $dbname = @mysql_connect($this->game['db']['address'], $this->game['db']['user'], $this->game['db']['password']);

                  @mysql_select_db($this->game['db']['db_name'], $dbname);

                  if(!$dbname OR mysql_error())
                  {
                                return false;
                  }


                  return $dbname;
           }

          /**
           * проверка таблиц
           *
           */

           function verify_db_tables()
           {

                  if(!($dbname = $this->connect_to_db()))
                  {
                           return false;
                  }

                  $data = @mysql_fetch_array(mysql_query("SELECT obj_id FROM characters LIMIT 1", $dbname), MYSQL_ASSOC);

                  if(empty($data['objid']))
                  {
                            return true;
                  }else{
                           return true;
                  }
           }

          /**
           * получение информации (игроки, макс. игроки и тд)
           *
           */

           function fetch_info()
           {

                  if(!($dbname = $this->connect_to_db()))
                  {
                            return false;
                  }

                  $server['s'] = array();

                  $server['e'] = array();

                  $server['p'] = array();

                  $getplayers = mysql_query("SELECT * FROM characters WHERE online = 1", $dbname);

                  while($player = @mysql_fetch_array($getplayers, MYSQL_ASSOC))
                  {
                       $players[$player['account']] = $player;
                       $playersonline++;
                  }
 
                  $server['s']['name'] = $this->game['db']['server_name'];
                  $ip_port = explode(':', $this->game['db']['server_ip']);
                  $server['s']['address'] = $ip_port[0];
                  $server['s']['port'] = $ip_port[1];

                  $server['p'] = $players;

                  $server['s']['players'] = $playersonline;

                  $server['e']['playersmaxs'] = @mysql_fetch_array(mysql_query("SELECT COUNT(*) AS count FROM characters", $dbname), MYSQL_ASSOC);

                  $server['s']['playersmax'] = $server['e']['playersmaxs']['count'];

                  unset($players);

                  return $server;
            }


          /**
           * получение забаненных аккаунтов
           *
           */

            function fetch_info_banned()
            {

                  if(!($dbname = $this->connect_to_db()))
                  {
                            return false;
                  }
 
                  $fetch_banned_acc = mysql_query("SELECT * FROM characters WHERE isBanned = 1");  
 
                  while ($ban = mysql_fetch_array($fetch_banned_acc, MYSQL_ASSOC))   
                  {   
                            $this->banned[$ban['id']] = $ban;
                  }

                  
            }

          /**
           * получение доп.инфы
           *
           */

            function fetch_info_additional_info()
            {

                  if(!($dbname = $this->connect_to_db()))
                  {
                            return false;
                  }

                  $server['e']['accounts'] = @mysql_fetch_array(mysql_query("SELECT COUNT(*) AS count FROM accounts", $dbname), MYSQL_ASSOC);

                  $server['e']['char'] = @mysql_fetch_array(mysql_query("SELECT COUNT(*) AS count FROM characters", $dbname), MYSQL_ASSOC);

                  $server['e']['allies'] = @mysql_fetch_array(mysql_query("SELECT COUNT(*) AS count FROM characters WHERE race IN (1,3,4,7,11)", $dbname), MYSQL_ASSOC);

                  $server['e']['horde'] = @mysql_fetch_array(mysql_query("SELECT COUNT(*) AS count FROM characters WHERE race IN (2,5,6,8,10)", $dbname), MYSQL_ASSOC);

                  foreach($server['e'] AS $field => $value)
                  {
                      $server['e'][$field] = $server['e'][$field]['count'];
                  }

                  return $server['e'];
            }

}

/**
 * VBGamEz :: порт запроса la2
 *
 */

function vbgamez_query_la2(&$server, &$vbgamez_need, &$vbgamez_fp)
{
    global $vbphrase;

    $dbgame = new vBGamez_dbGames_la2($server['dbinfo']);

    if(!($la2info = $dbgame->fetch_info()))
    {
        $server['s'] = array();

        $server['e'] = array();

        $server['p'] = array();

    }else{

        $server['s'] = array(
        "banned"       => true,
        "game"       => "la2",
        "name"       => $la2info['s']['name'],
        "map"        => iif($la2info['s']['map'], $la2info['s']['map'], "---"),
        "players"    => $la2info['s']['players'],
        "playersmax" => $la2info['s']['playersmax'],
        "password"   => 0);

        $server['e'] = true;

        if(!empty($la2info['p']))
        {
             foreach ($la2info['p'] as $key => $name)
             {
                 $server['p'][$key]['name'] = $name['char_name'];
                 $server['p'][$key]['level'] = $name['level'];
            }
        }
    }

    return true;
}




/**
 * VBGamEz мониторинг WOW
 *
 * @package vBGamEz
 * @author GiveMeABreak aka Developer, LGSL author
 * @version $Revision: 151 $
 * @copyright GiveMeABreak
 */

class vBGamez_dbGames_wow_t
{
          /**
           * конструктор :P
           *
           */

           function vBGamez_dbGames_wow_t($dbinfo = '')
           { 
                  if(!empty($dbinfo))
                  {
                        $this->game['name'] = 'wow_t';

                        $this->game['db'] = vBGamez_dbGames_Bootstrap::vbgamez_fetch_db_info($dbinfo);
                        $this->game['db']['characters'] = 'trinity_characters';
                        $this->game['db']['realmd'] = 'trinity_realmd';
                        $this->game['db']['world'] = 'trinity_world';
                  }
            }

          /**
           * подключение к базе данных вова
           *
           */

           function connect_to_db()
           {
                  $dbname = @mysql_connect($this->game['db']['address'], $this->game['db']['user'], $this->game['db']['password']);

                  if(!$dbname OR mysql_error())
                  {
                                return false;
                  }

                  @mysql_select_db($this->game['db']['characters'], $dbname);

                  return $dbname;
           }

          /**
           * проверка таблиц
           *
           */

           function verify_db_tables()
           {
                  if(!($dbname = $this->connect_to_db()))
                  {
                            return false;
                  }

                  $data = @mysql_fetch_array(mysql_query("SELECT name, address, port FROM ".$this->game['db']['realmd'].".realmlist", $dbname), MYSQL_ASSOC);

                  if(empty($data['name']) OR empty($data['address']) OR empty($data['port']))
                  {
                           return false;
                  }else{
                           return true;
                  }
           }

          /**
           * получение информации (игроки, макс. игроки и тд)
           *
           */

           function fetch_info()
           {
                  if(!($dbname = $this->connect_to_db()))
                  {
                            return false;
                  }

                  $server['s'] = array();

                  $server['e'] = array();

                  $server['p'] = array();

				  mysql_query("SET names utf8");
                  $getplayers = mysql_query("SELECT * FROM characters WHERE online = 1", $dbname);

                  while($player = @mysql_fetch_array($getplayers, MYSQL_ASSOC))
                  {
                       $players[$player['account']] = $player;
                       $playersonline++;
                  }

                  $server['s'] = @mysql_fetch_array(mysql_query("SELECT name, address, port FROM ".$this->game['db']['realmd'].".realmlist", $dbname), MYSQL_ASSOC);

                  $server['p'] = $players;
                  $server['s']['players'] = $playersonline;
                  $server['s']['playersmax'] = @mysql_result(mysql_query("SELECT MAX(`maxplayers`) FROM ".$this->game['db']['realmd'].".uptime", $dbname),0);

                  unset($players);

                  return $server;
            }


          /**
           * получение забаненных аккаунтов
           *
           */

            function fetch_info_banned()
            {
                  if(!($dbname = $this->connect_to_db()))
                  {
                            return false;
                  }

                  $fetch_banned_ip = mysql_query("SELECT ip, banreason, bandate, unbandate FROM ".$this->game['db']['realmd'].".ip_banned");   
                  while ($ban = mysql_fetch_array($fetch_banned_ip, MYSQL_ASSOC))   
                  {   
                            $this->bannedips[$ban['ip']] = $ban;
                  }
 
                  $fetch_banned_acc = mysql_query("SELECT * FROM ".$this->game['db']['realmd'].".account_banned AS account_banned
                                                   LEFT JOIN ".$this->game['db']['realmd'].".account AS account ON(account.id = account_banned.id)
                                                   WHERE active = 1 ORDER BY bandate DESC LIMIT 100");  
 
                  while ($ban = mysql_fetch_array($fetch_banned_acc, MYSQL_ASSOC))   
                  {   
                            $this->banned[$ban['id']] = $ban;
                  }

                  
            }

          /**
           * получение доп.инфы
           *
           */

            function fetch_info_additional_info()
            {
                  if(!($dbname = $this->connect_to_db()))
                  {
                            return false;
                  }

                  $server['e']['accounts'] = @mysql_fetch_array(mysql_query("SELECT COUNT(*) AS count FROM ".$this->game['db']['realmd'].".account", $dbname), MYSQL_ASSOC);

                  $server['e']['char'] = @mysql_fetch_array(mysql_query("SELECT COUNT(*) AS count FROM ".$this->game['db']['characters'].".characters", $dbname), MYSQL_ASSOC);

                  $server['e']['guild'] = @mysql_fetch_array(mysql_query("SELECT COUNT(*) AS count FROM ".$this->game['db']['characters'].".guild", $dbname), MYSQL_ASSOC);

                  $server['e']['allies'] = @mysql_fetch_array(mysql_query("SELECT COUNT(*) AS count FROM ".$this->game['db']['characters'].".characters WHERE race IN (1,3,4,7,11)", $dbname), MYSQL_ASSOC);

                  $server['e']['horde'] = @mysql_fetch_array(mysql_query("SELECT COUNT(*) AS count FROM ".$this->game['db']['characters'].".characters WHERE race IN (2,5,6,8,10)", $dbname), MYSQL_ASSOC);

                  foreach($server['e'] AS $field => $value)
                  {
                      $server['e'][$field] = $server['e'][$field]['count'];
                  }

                  $uptime_query = @mysql_fetch_array(mysql_query("SELECT * FROM ".$this->game['db']['realmd'].".uptime ORDER BY starttime DESC", $dbname), MYSQL_ASSOC);

                  $server['e']['uptime'] = TIMENOW - $uptime_query['starttime'];

                 return $server['e'];
            }

}

/**
 * VBGamEz :: порт запроса Wow
 *
 */

function vbgamez_query_wow_t(&$server, &$vbgamez_need, &$vbgamez_fp)
{
    global $vbphrase;
    $dbgame = new vBGamez_dbGames_wow_t($server['dbinfo']);

    if(!($wowinfo = $dbgame->fetch_info()))
    {
        $server['s'] = array();

        $server['e'] = array();

        $server['p'] = array();

    }else{

        $server['s'] = array(
        "banned"       => true,
        "game"       => "wow_t",
        "name"       => $wowinfo['s']['name'],
        "map"        => iif($wowinfo['s']['map'], $wowinfo['s']['map'], "---"),
        "players"    => $wowinfo['s']['players'],
        "playersmax" => $wowinfo['s']['playersmax'],
        "password"   => 0);

        $server['e'] = true;

        if(!empty($wowinfo['p']))
        {
             foreach ($wowinfo['p'] as $key => $name)
             {
                 if(in_array($name['race'], array(1, 3, 4, 7, 11)))
                 {
                            $server['p'][$key]['fr'] = '<img src="' . vB_vBGamez::fetch_image('images/vbgamez/icons/wow/alliance.gif') . '" alt="" />';
                 }

                 if(in_array($name['race'], array(2, 5, 6, 8, 10)))
                 {
                            $server['p'][$key]['fr'] = '<img src="' . vB_vBGamez::fetch_image('images/vbgamez/icons/wow/horde.gif') . '" alt="" />';
                 }

                 $server['p'][$key]['name'] = $name['name'];
                 $server['p'][$key]['race'] = '<img src="' . vB_vBGamez::fetch_image('images/vbgamez/icons/wow/race/' . $name['race'] . '-1.gif') . '" alt="" />';
                 $server['p'][$key]['class'] = '<img src="' . vB_vBGamez::fetch_image('images/vbgamez/icons/wow/class/' . $name['class'] . '.gif') . '" alt="" />';
                 $server['p'][$key]['level'] = $name['level'];
            }
        }
    }

    return true;
}

?>