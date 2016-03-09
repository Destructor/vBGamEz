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
 * VBGamEz определение страны/города по IP
 *
 * @package vBGamEz
 * @author GiveMeABreak aka Developer
 * @version $Revision: 4 $
 * @copyright GiveMeABreak
 */

class vB_vBGamez_Geo_db
{
	const DbPath = './packages/vbgamez/3rd_party_classes/geodb/GeoLiteCity.dat';
	 /*========================================================================
         *
	 * Проверка параметров
	 *
	 */

         public static function check_settings()
         {
                   global $vbulletin, $vbphrase;

                   if(!$vbulletin->options['vbgamez_geo_database']) 
                   {
                               return false;
                   }

                   if(!vB_vBGamez_Geo_db::fetchDatabase()) 
                   {
                               return false;
                   }

              return true;
         }

	 /*========================================================================
         *
	 * Получение информации
	 *
	 */

         public static function fetchInfo($ipaddress)
         {
                  if(!vB_vBGamez_Geo_db::fetchDatabase())
                  {
                                   return false;
                  }

                  require_once('./packages/vbgamez/3rd_party_classes/geodb/geoipcity.inc');

                  $gi = GeoIP_open(vB_vBGamez_Geo_db::fetchDatabase(), GEOIP_STANDARD);

                  $ip_info = GeoIP_record_by_addr($gi, $ipaddress);

                  GeoIP_close($gi); 

                  return $ip_info;
         }

	 /*========================================================================
         *
	 * Получение библиотеки
	 *
	 */

         public static function fetchDatabase()
         { 
                  if(@file_exists(self::DbPath))
                  {
                       return self::DbPath;
                  }
         }

	 /*========================================================================
         *
	 * Загрузка базы
         * Автор: ManHunter
         * Статья: http://www.manhunter.ru/webmaster/184_opredelenie_geograficheskogo_polozheniya_po_ip_adresu.html
	 *
	 */

         public static function downloadDatabase($clearold = false)
         {
               global $vbphrase;

                  if($clearold)
                  {
                          @unlink(self::DbPath);
                  }

                  $lnk="http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz";

                      if ($f1=@fopen($lnk,"r")) {

                      if ($f2=@fopen(self::DbPath,"w+")) {
                      while (!feof($f1)) {
                            $data=fread($f1,10000);
                            fwrite($f2,$data);
                          }
                          fclose($f1);
                          fclose($f2);
 
                          if ($f1=@gzopen(self::DbPath,"rb")) {
                            if ($f2=@fopen(self::DbPath,"w+")) {
                              do {
                                $data=gzread($f1,100000);
                                fwrite($f2,$data);
                              } while ($data!="");
                              gzclose($f1);
                              fclose($f2);
                            }
                            else {
                              echo $vbphrase['vbgamez_base_downloading_error']; exit;
                            }
                          }
                          else {
                            echo $vbphrase['vbgamez_base_downloading_error']; exit;
                          }
                          unlink(self::DbPath);
                        }
                        else {
                          echo $vbphrase['vbgamez_base_downloading_error']; exit;
                          fclose($f1);
                        }
                      }
                      else {
                        echo $vbphrase['vbgamez_base_downloading_error']; exit;
                      }
                               }
          }

?>