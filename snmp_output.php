<?php
  /**
  * File:        snmp_output.php
  * Description: script kontrolujici aktualni smogovou situaci a
  *              nastavujici output vzdaleneho zarizeni
  * @version     1.0
  * @author      Pavel Müller
  * @see
  */
  #konstanty
  include('snmp_const.php');

  #nastav casovou zonu
  date_default_timezone_set('Europe/Prague');
  #loguj
  writeLog("[" . date("d.m.Y H:i:s") . "]");

  #definuj objekty
  $oXml = new stdClass();
  $oData = new stdClass();
  $oStation = new stdClass();
  $oMeasurement = new stdClass();
  $oAveragedTime = new stdClass();
  $oValue = new stdClass();
  $aElements = array();
  $aValues = array();

  try {
      #ziskej xml soubor z url adresy, random url adresa kvuli kesovani
      $oXml = simplexml_load_file(DATA_TABLE_XML . '?rand=' . time());
      #uloz data
      $oData = $oXml->Data;

      #projdi vsechny stanice
      foreach ($oData->station as $sKey => $oStation) {
        #pokud existuje dana stanice
        if((string)$oStation->code == STATION_CODE) {
          #projdi jeji data a uloz do pole
          foreach($oStation->measurement as $sKey => $oMeasurement) {
            foreach ($oMeasurement->averaged_time as $sKey => $oAveragedTime) {
              $aElements[] = $oAveragedTime;
            }
          }
        }
      }

      #projdi elementy a ukladej hodnoty pod klici
      foreach ($aElements as $iKey => $oValue) {
        $aValues[$iKey] = (string)$oValue->value;
      }

      #preved cas a ziskej hodnoty z tabulky
      $sDateTimeUtc = strtotime($oData->datetime_to . ' UTC');
      $sLastUpdateDate = date('d.m.Y H:i:s', $sDateTimeUtc);   #datum a cas posledni aktualizace (probiha kazdou hodinu kolem 40. minuty)
      $fNO2 = $aValues[0];                                     #Oxid dusičitý - NO2 1H µg/m³
      $fO3 = $aValues[1];                                      #Ozón - O3 1H µg/m³
      $fPM10 = $aValues[3];                                    #Pevné částice - PM10 1H µg/m³

      #pokud hodnoty existuji
      if (!empty($fNO2) && !empty($fO3) && !empty($fPM10) ) {
        #defaultne nastav velmi dobrou kvalitu ovzdusi
        $iAirQuality = AIR_QUALITY_VERY_GOOD;
        
        #rozlis kvalitu ovzdusi podle maximalnich hodnot prvku
        ##################### dobra #######################
        if (($fNO2 > 200) || ($fO3 > 180) || ($fPM10 > 30)) {
          $iAirQuality = AIR_QUALITY_BAD;
        }

        #loguj hodnoty
        writeLog(" [Last update: " . $sLastUpdateDate . "]");
        writeLog(" [Values: NO2:" . $fNO2 . ", O3:" . $fO3 . ", PM10:" . $fPM10 . "]");
        writeLog(" [Qaulity: " . $iAirQuality ."]") ;
      } else {
        #vyhod exception
        throw new Exception;
      }

      ##########################################################################
      ########################## zjisti stav outputu ###########################
      ##########################################################################
      $sOutputStatus = snmpget(HOST_IP_ADRESS, READ_COMMUNITY, OUTPUT_OBJECT_ID);

      #pokud byl zjisten stav outputu
      if ($sOutputStatus) {
        #preved na cislo
        $aMatch = array();
        preg_match('/.*(\d+).*/', $sOutputStatus, $aMatch);
        $iOutpuStatus = intval($aMatch[1]);
        #loguj
        writeLog(" [Output status: " .$iOutpuStatus . "]");

        #defaultni stav outputu stejny
        $sTurnOutput = "Current";
        #pokud je kvalita vzduchu spatna a output neni zapnut
        if (($iAirQuality >= AIR_QUALITY_BAD) && ($iOutpuStatus == 0)) {
          ##########################################################################
          ############################# spust output ###############################
          ##########################################################################
          $bSetOutput = snmpset(HOST_IP_ADRESS, WRITE_COMMUNITY, OUTPUT_OBJECT_ID, OBJECT_TYPE_STRING, TURN_OUTPUT_ON);
          #pokud vraci false, nastala chyba
          $bSetOutput ? $sTurnOutput = "Turn on" : $sTurnOutput = "undefined";
          #pokud neni kvalita vzduchu spatna, ale output bezi
        } else if (($iAirQuality < AIR_QUALITY_BAD) && ($iOutpuStatus == 1)) {
          ##########################################################################
          ############################# vypni output ###############################
          ##########################################################################
          $bSetOutput = snmpset(HOST_IP_ADRESS, WRITE_COMMUNITY, OUTPUT_OBJECT_ID, OBJECT_TYPE_STRING, TURN_OUTPUT_OFF);
          #pokud vraci false, nastala chyba
          $bSetOutput ? $sTurnOutput = "Turn off" : $sTurnOutput = "undefined";
        }

        #loguj a pokud nastala chyba vrat error
        writeLog(" [Set output: " . $sTurnOutput . "]");
        ($sTurnOutput == "undefined") ? writeLog(" PROCCESS ERROR\n") : writeLog(" PROCCESS OK\n");

      } else {
        #loguj
        writeLog(" [Output status: undefined] PROCCESS ERROR\n");
      }

  } catch (Exception $oExp) {
    #loguj exception
    writeLog(" [Values: undefined] PROCESS ERROR\n");
  }

  /**
   * Fuknce pro zapisovani do logu
   * @param   string    $sLog
   */
  function writeLog($sLog) {
    #otevri log soubor
    $oLog = fopen(PATH_TO_LOG, "a+");
    #zapis do logu
    fwrite($oLog, $sLog);
    #uzavri soubor
    fclose($oLog);
  }
?>