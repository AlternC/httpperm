#!/usr/bin/php
<?php

require_once("connect.php");

$f = fopen("php://stdin","rb");
$debug=false;
$i=0;

$stmt=array();
pdo_connect();

function pdo_connect() {
  global $db,$stmt,$debug,$dbuser,$dbpass,$dbname;
  $db = new PDO("mysql:dbname=".$dbname.";host=localhost;charset=UTF8",
		$dbuser,$dbpass,
		array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION )
		);
  // Prepared statements of everything : 
  $stmt["insert"] = $db->prepare("INSERT INTO http_log SET srcip=?, dstip=?, dstport=?, uid=?, gid=?;");
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


$qids=array();

while ($s=fgets($f,1024)) {

  // Aug  3 11:07:33 alice  IN= OUT=lo MAC= SRC=127.0.0.1 DST=127.0.0.1 LEN=60 TOS=00 PREC=0x00 TTL=64 ID=22098 DF PROTO=TCP SPT=44860 DPT=25 SEQ=3864228039 ACK=0 WINDOW=43690 SYN URGP=0 UID=33 GID=33 MARK=0

  $now=time();

  if (preg_match('# SRC=([^ ]*) DST=([^ ]*) .* DPT=(443|80) .* UID=([0-9]*) GID=([0-9]*) #',$s,$mat)) {
    if ($debug) echo "\nULOG : ".$mat[1]." ".$mat[2]." ".$mat[3]." ".$mat[4]." ".$mat[5]."\n";
    pdo_exec("insert",array( $mat[1], $mat[2], $mat[3], $mat[4], $mat[5] ));
    continue;
  }


  // nope
  if ($debug) {
    echo "."; $i++;
    if ($i%100==0) echo " $i\n".date("Y-m-d H:i:s ");
  }
}

