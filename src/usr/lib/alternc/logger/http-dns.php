#!/usr/bin/php
<?php

/* 
   prend les requêtes de la dernière minute dans HTTP_LOG et demande à cache0/cache1 les entrées DNS inverse correspondantes : 
 */
require_once("connect.php");

$stmt=array();
pdo_connect();

$dnss=array(
	    "91.194.60.250",
	    "91.194.60.251",
	    );


function pdo_connect() {
  global $db,$stmt,$debug,$dbuser,$dbpass,$dbname;
  $db = new PDO("mysql:dbname=".$dbname.";host=localhost;charset=UTF8",
		$dbuser,$dbpass,
		array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION )
		);
  // Prepared statements of everything : 
  $stmt["get"] = $db->prepare("SELECT DISTINCT(dstip) AS ip FROM http_log WHERE sdate >DATE_SUB(NOW(), INTERVAL 1 MINUTE) AND dns='';");
  $stmt["update"] = $db->prepare("UPDATE http_log SET dns=? WHERE dstip=? AND dns='';");
}

function pdo_exec($statement,$params) {
  global $stmt,$debug;
  try {
    $stmt[$statement]->execute($params);
  } catch (PDOException $pe){
    if ($debug) echo "\n".$pe->getMessage()."\n";
    // RECONNECT : 
    pdo_connect();
    // RETRY 
    $stmt[$statement]->execute($params);
  }    
}

pdo_exec("get",array());
$ip=array();
while ($one = $stmt["get"]->fetch()) {
  if (!isset($ip[$one["ip"]])) {
    $ip[$one["ip"]]=$one["ip"];
  }
}
//print_r($ip);

$results=array();

foreach($dnss as $server) {
  $f=fsockopen($server,4224);
  fputs($f,implode(" ",$ip)."\n");
  while ($s=fgets($f,1024)) {
    list($oneip,$onename)=explode(" ",trim($s),2);
    if (!isset($results[$oneip]) || !in_array($onename,$results[$oneip])) {
      $results[$oneip][] = $onename;
    }
  }
  fclose($f);
}
//print_r($results);

foreach($results as $k=>$v) {
  pdo_exec("update",array(implode(" ",$v),$k));
}