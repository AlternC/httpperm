#!/usr/bin/php -q
<?php
/*
   httpperm_cron.php
   ----------------------------------------------------------------------
   AlternC - Web Hosting System
   Copyright (C) 2002 by the AlternC Development Team.
   http://alternc.org/
   ----------------------------------------------------------------------
   LICENSE

   This program is free software; you can redistribute it and/or
   modify it under the terms of the GNU General Public License (GPL)
   as published by the Free Software Foundation; either version 2
   of the License, or (at your option) any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   To read the license please visit http://www.gnu.org/copyleft/gpl.html
   ----------------------------------------------------------------------
   Original Author of file: Benjamin Sonntag
   Purpose of file: update the IPSETs to allow HTTP Outgoing streams 
   ----------------------------------------------------------------------
 */

// Put this var to 1 if you want to enable debug prints
$debug=0;

// Collects errors along execution. If length > 1, an email is sent.
$errorsList=array();

// Bootstraps 
require_once("/usr/share/alternc/panel/class/config_nochk.php");

$tests = array(
	       "192.168.0.1" => array($httpperm::KIND_IPV4,"192.168.0.1"),
	       "  192.168.0.1  " => array($httpperm::KIND_IPV4,"192.168.0.1"),
	       "00192.00168.00.001" => array($httpperm::KIND_BAD,false),
	       "292.168.0.1" => array($httpperm::KIND_BAD,false),
	       ".168.0.1" => array($httpperm::KIND_BAD,false),

	       "192.168.0.1/24" => array($httpperm::KIND_IPV4BLOC,"192.168.0.1/24"),
	       "192.168.0.1/33" => array($httpperm::KIND_BAD,false),
	       " 192.0168.0.01/8 " => array($httpperm::KIND_BAD,false),
	       "192.168.0.1/32" => array($httpperm::KIND_IPV4,"192.168.0.1"),

	       "::1" => array($httpperm::KIND_IPV6,"::1"),
	       "2001:067C:0288:0000::2" => array($httpperm::KIND_IPV6,"2001:67c:288::2"),
	       "  2001:067C:0288:0000:dead:beef:caca:2 " => array($httpperm::KIND_IPV6,"2001:67c:288:0:dead:beef:caca:2"),
	       "::ffff:192.168.0.1" => array($httpperm::KIND_IPV4,"192.168.0.1"),
	       "1:2:3:4:5:6:7:8:9" => array($httpperm::KIND_BAD,false),
	       "2001:67c:fffff::2" => array($httpperm::KIND_BAD,false),
	       "2001:67c::ffff::2" => array($httpperm::KIND_BAD,false),

	       "2001:067c:0288::2/64" => array($httpperm::KIND_IPV6BLOC,"2001:67c:288::2/64"),
	       "2001:067c:0288::2/128" => array($httpperm::KIND_IPV6,"2001:67c:288::2"),
	       "2001:067c:0288::2/0" => array($httpperm::KIND_BAD,false),
	       
	       "octopuce.fr" => array($httpperm::KIND_NAME,"octopuce.fr"),
	       "ocTopUce.fr" => array($httpperm::KIND_NAME,"octopuce.fr"),
	       " octopuce.fr " => array($httpperm::KIND_NAME,"octopuce.fr"),
	       "résolument.com" => array($httpperm::KIND_BAD,false),
	       "xn--bcher-kva.ch" => array($httpperm::KIND_NAME,"xn--bcher-kva.ch"),
	       "coin.192.nope.fr" => array($httpperm::KIND_BAD,false),
	       "toto.titi.12plus" => array($httpperm::KIND_NAME,"toto.titi.12plus"),
	       "toto.titi." => array($httpperm::KIND_NAME,"toto.titi"),
	       ".toto.titi" => array($httpperm::KIND_BAD,false),
	       );

foreach($tests as $test => $expected) {
  $result = $httpperm->canonicalize($test);

  if ($result[0]!==$expected[0] || $result[1]!==$expected[1]) {
    echo "ERROR on test [".$test."], expected ".$expected[0]."/".$expected[1]."  got  ".$result[0]."/".$result[1]."\n";
  } else {
    echo "OK on test [".$test."], got ".$result[0]."/".$result[1]."\n";
  }
}
