<?php
  /**
    * File:        snmp_const.php
    * Description: konstanty
    * @version     1.0
    * @author      Pavel Müller
    * @see
  */

  /**
  * Konstanty adres
  **/
    const DATA_TABLE_URL = "http://www.chmi.cz/files/portal/docs/uoco/web_generator/aqindex_slide3/mp_TOVKA_CZ.html";
    const DATA_TABLE_XML = "http://portal.chmi.cz/files/portal/docs/uoco/web_generator/AIMdata_hourly.xml";
    const PATH_TO_LOG    = "/var/www/html/pavel/log/snmp_log.log";

  /**
  * Konstanty kvality ovzduší
  **/
    const STATION_CODE   = "TOVKA"; #opava katerinky


  /**
  * Konstanty kvality ovzduší
  **/
    const AIR_QUALITY_VERY_GOOD   = 1; #velmi dobra
    const AIR_QUALITY_GOOD        = 2; #dobra
    const AIR_QUALITY_SATISFYING  = 3; #uspokojiva
    const AIR_QUALITY_SUITABLE    = 4; #vyhovujici
    const AIR_QUALITY_BAD         = 5; #spatna
    const AIR_QUALITY_VERY_BAD    = 6; #velmi spatna

  /**
  * Konstanty snmp protokolu
  **/
    const HOST_IP_ADRESS     = "";             #ip adresa pristroje
    const READ_COMMUNITY     = "";                    #pristup pro cteni
    const WRITE_COMMUNITY    = "";                    #pristup pro zapis
    const OUTPUT_OBJECT_ID   = "";  #id objektu
    const OBJECT_TYPE_STRING = "s";                           #string
    const TURN_OUTPUT_ON     = "1";                           #zapnout
    const TURN_OUTPUT_OFF    = "0";                           #vypnout

?>