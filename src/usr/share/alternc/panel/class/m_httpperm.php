<?php

/*
  ----------------------------------------------------------------------
  AlternC - Web Hosting System
  Copyright (C) 2000-2017 by the AlternC Development Team.
  https://alternc.org/
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
  Purpose of file: Manage HTTP / HTTPS outgoing permissions
  ----------------------------------------------------------------------
 */

/**
 * HTTP / HTTPS outgoing permissions
 */
class m_httpperm {

  const CRON_PID = "/var/run/alternc/httpperm.pid";
  
  
  /* ----------------------------------------------------------------- */
  
  /**
   * Constructeur
   */
  function m_httpperm() {
  }

    /* ----------------------------------------------------------------- */

    function hook_menu() {
        global $quota;
        $q = $quota->getquota("ftp");

        if ($quota->cancreate("httpperm")) {

        $obj = array(
            'title' => _("HTTP Permissions"),
            'ico' => 'images/ftp.png',
            'link' => 'toggle',
            'pos' => 60,
            'links' => array(),
        );

            $obj['links'][] = array(
                'ico' => 'images/new.png',
                'txt' => _("Manages HTTP Permissions"),
                'url' => "httpperm_list.php",
                'class' => '',
            );
        }


        return $obj;
    }

    /* ----------------------------------------------------------------- */

    /** Retourne la liste des comptes FTP du compte h�berg�
     * Retourne la liste des comptes FTP sous forme de tableau index� de
     * tableaus associatifs comme suit :
     * $a["id"]= ID du compte ftp
     * $a["login"]= Nom de login du compte
     * $a["dir"]= Dossier relatif � la racine du compte de l'utilisateur
     * @return array Retourne le tableau des comptes 
     */
    function get_list() {
      global $db, $err, $cuid, $admin;
        $err->log("http", "get_list");
        $r = array();
	$param=array();
	if ($admin->enabled) {
	  $sql="";
	} else {
	  $sql=" WHERE p.uid= ? ";
	  $param[]=$cuid;
	}
        $db->query("SELECT p.*, GROUP_CONCAT(b.bloc SEPARATOR ' ') AS matchlist FROM http_permission p LEFT JOIN http_permission_bloc b ON b.permid=p.id  $sql GROUP BY p.id ORDER BY name;", $param);
        if ($db->num_rows()) {
	  while ($db->next_record()) {
	    $r[] = $db->Record;
	  }
	  return $r;
        } else {
	  return array();
        }
    }


    /* ----------------------------------------------------------------- */

    function edit($id, $name) {
        global $db, $err, $cuid, $admin;
        $err->log("httpperm", "edit", $id);

	$param=array($id);
	if ($admin->enabled) {
	  $sql="";
	} else {
	  $sql=" AND uid= ? ";
	  $param[]=$cuid;
	}
        $db->query("SELECT count(*) AS cnt FROM http_permission WHERE id= ? $sql;", $param);
        $db->next_record();
        if (!$db->f("cnt")) {
            $err->raise("httpperm", _("This HTTP Permission does not exist"));
            return false;
        }

	$param[]=$name;
        $db->query("SELECT COUNT(*) AS cnt FROM http_permission WHERE id!= ? $sql AND name= ?;", $param);
        $db->next_record();
        if ($db->f("cnt")) {
            $err->raise("httpperm", _("This http_permission already exists"));
            return false;
        }
	// delete cached permission blocs: 
	$db->query("DELETE FROM http_permission_bloc WHERE id=?", arary($id));
	// update the permission:
	$db->query("UPDATE http_permission SET name= ? WHERE id= ?;", array($name,$id));
        return true;
    }

    /* ----------------------------------------------------------------- */
    function delete($id) {
        global $db, $err, $cuid;
        $err->log("httpperm", "delete", $id);
	$param=array($id);
	if ($admin->enabled) {
	  $sql="";
	} else {
	  $sql=" AND uid= ? ";
	  $param[]=$cuid;
	}
        $db->query("SELECT count(*) AS cnt FROM http_permission WHERE id= ? $sql;", $param);
        $db->next_record();
        if (!$db->f("cnt")) {
            $err->raise("httpperm", _("This HTTP Permission does not exist"));
            return false;
        }
        $db->query("DELETE FROM http_permission_bloc WHERE permid= ? ;", array($id));
        $db->query("DELETE FROM http_permission WHERE id= ? ;", array($id));
        return true;
    }

    /* ----------------------------------------------------------------- */
    function add($name) {
        global $db, $err, $cuid, $admin;
        $err->log("httpperm", "add", $id);

	$param=array($name);
	if ($admin->enabled) {
	  $sql="";
	} else {
	  $sql=" AND uid= ? ";
	  $param[]=$cuid;
	}
        $db->query("SELECT COUNT(*) AS cnt FROM http_permission WHERE name= ? $sql;", $param);
        $db->next_record();
        if ($db->f("cnt")) {
            $err->raise("httpperm", _("This http_permission already exists"));
            return false;
        }
	$db->query("INSERT INTO http_permission SET name= ? , uid=?;", array($name,$cuid));
        return true;
    }


    /* ----------------------------------------------------------------- */
    /**
     *
     */
    function get_log() {
      global $db, $err, $cuid, $admin;
      $err->log("httpperm", "get_log", $id);
      
      $param=array();
      if ($admin->enabled) {
	$sql="";
      } else {
	$sql=" AND uid= ? ";
	$param[]=$cuid;
      }
      $db->query("SELECT sdate,dstip,dstport,dns,uid,permid FROM http_log WHERE sdate>DATE_SUB(NOW(), INTERVAL 7 DAY) $sql ORDER BY sdate DESC LIMIT 200;",$param);
      $res=array();
      while ($db->next_record()) {
	$res[]=$db->Record;
      }
      return $res;
    }

    /* ----------------------------------------------------------------- */

    /** Fonction appellee par membres quand un membre est efface
     * @access private
     */
    function alternc_del_member() {
        global $db, $err, $cuid;
        $err->log("httpperm", "alternc_del_member");
        $db->query("DELETE http_permission_bloc FROM http_permission, http_permission_bloc WHERE uid= ? AND http_permission.id=http_permission_bloc.permid;", array($cuid));
        $db->query("DELETE FROM http_permission WHERE uid= ?", array($cuid));
        return true;
    }

    /* ----------------------------------------------------------------- */

    /**
     * Returns the used quota for the $name service for the current user.
     * @param $name string name of the quota 
     * @return integer the number of service used or false if an error occured
     * @access private
     */
    function hook_quota_get() {
        global $db, $err, $cuid;
        $err->log("httpperm", "getquota");
        $q = Array("name" => "httpperm", "description" => _("HTTP Permissions"), "used" => 0);
        return $q;
    }


    const KIND_UNKNOWN = 0;
    const KIND_BAD = 100;

    const KIND_IPV4 = 1;
    const KIND_IPV4BLOC = 2;
    const KIND_IPV6 = 3;
    const KIND_IPV6BLOC = 4;
    const KIND_NAME = 5;

    /* ----------------------------------------------------------------- */

    /**
     * return the KIND and canonicalize a NAME
     * "kind" can be any KIND_* constant
     */
    function canonicalize($name) {
      $name=trim(strtolower($name));
      $name=str_replace("[","",str_replace("]","",$name)); // in case of [2001:67c:288::] notation
      $name=str_replace(" ","",str_replace(chr(9),"",$name)); // just to be nice and accept spaces in weird places

      if (preg_match('#^[0-9]{1,}\.[0-9]{1,}\.[0-9]{1,}\.[0-9]{1,}$#',$name)) {
	$tmp=@inet_pton($name);
	if ($tmp===false) {
	  return array($this::KIND_BAD,false);
	}
	return array($this::KIND_IPV4,inet_ntop($tmp));
      }

      // ipv6-in-ipv4 notation  ::ffff:192.168.0.1
      if (preg_match('#^::ffff:([0-9]{1,}\.[0-9]{1,}\.[0-9]{1,}\.[0-9]{1,})$#',$name,$mat)) {
	$tmp=@inet_pton($mat[1]);
	if ($tmp===false) {
	  return array($this::KIND_BAD,false);
	}
	return array($this::KIND_IPV4,inet_ntop($tmp));
      }

      if (preg_match('#^([0-9]{1,}\.[0-9]{1,}\.[0-9]{1,}\.[0-9]{1,})/([0-9]{1,2})$#',$name,$mat)) {
	$tmp=@inet_pton($mat[1]);
	$prefix=intval($mat[2]); 
	if ($tmp===false || $prefix<8 || $prefix>32) {
	  return array($this::KIND_BAD,false);
	}
	if ($prefix==32) {
	  return array($this::KIND_IPV4,inet_ntop($tmp));
	}
	return array($this::KIND_IPV4BLOC,inet_ntop($tmp)."/".$prefix);
      }

      if (preg_match('#^[0-9a-f]{0,4}:[0-9a-f]{0,4}:[0-9a-f:\.]*$#',$name)) {
	$tmp=@inet_pton($name);
	if ($tmp===false) {
	  return array($this::KIND_BAD,false);
	}
	return array($this::KIND_IPV6,inet_ntop($tmp));
      }

      if (preg_match('#^([0-9a-f]{0,4}:[0-9a-f]{0,4}:[0-9a-f:\.]*)/([0-9]{1,3})$#',$name,$mat)) {
	$tmp=@inet_pton($mat[1]);
	$prefix=intval($mat[2]); 
	if ($tmp===false || $prefix<8 || $prefix>128) {
	  return array($this::KIND_BAD,false);
	}
	if ($prefix==128) {
	  return array($this::KIND_IPV6,inet_ntop($tmp));
	}
	return array($this::KIND_IPV6BLOC,inet_ntop($tmp)."/".$prefix);
      }
      
      if (preg_match('#\.[0-9]+\.#',$name) || substr($name,0,1)==".") {
	// only figures between dots : nope ;)  or starting by .
	return array($this::KIND_BAD,false);
      }
      $name=rtrim($name,"."); // remove final dots... 
      if (preg_match('#^[a-z0-9-\.]{1,255}$#',$name)) {
	return array($this::KIND_NAME,$name);
      }
      return array($this::KIND_BAD,false);
    }


    /* ------------------------------------------------------------ */    
    /** 
     * resolve the A and AAAA for $name in the DNS
     * add them to http_permission_bloc list for permission id $id
     */
    function resolve($name,$id, $ttl=86400, $depth=0) {
      global $db;

      // oups, CNAMES recursively calling ?
      if ($depth==10) return false;

      $r = dns_get_record($name,DNS_A);
      if (is_array($r) && count($r)) {
	foreach($r as $one) {
	  if ($one["type"]=="A") {
	    $db->query("INSERT IGNORE INTO http_permission_bloc SET permid=?, bloc=?, kind=?;",array($id, $one["ip"], $this::KIND_IPV4));
	    if ($one["ttl"]<$ttl) $ttl=$one["ttl"];
	  }
	}
      }
            
      $r = dns_get_record($name,DNS_AAAA);
      if (is_array($r) && count($r)) {
	foreach($r as $one) {
	  if ($one["type"]=="AAAA") {
	    $db->query("INSERT IGNORE INTO http_permission_bloc SET permid=?, bloc=?, kind=?;",array($id, $one["ipv6"], $this::KIND_IPV6));
	    if ($one["ttl"]<$ttl) $ttl=$one["ttl"];
	  }
	}
      }
      
      $r = dns_get_record($name,DNS_CNAME);
      if (is_array($r) && count($r)) {
	foreach($r as $one) {
	  if ($one["type"]=="CNAME") {
	    if ($one["ttl"]<$ttl) $ttl=$one["ttl"];
	    $returnedttl = $this->resolve($one["target"],$id,$ttl,$depth+1);
	    if ($returnedttl !== false && $returnedttl<$ttl) $ttl=$returnedttl;
	  }
	}
      }
      
      if ($depth===0) {
	$db->query("UPDATE http_permission SET udate=NOW(), ttl=? WHERE id=?",array($ttl,$id));
      }
      return $ttl;
    }


    /** 
     * lock the cron to prevent multiple parallel execution 
     */
    function lock_cron() {
      $MY_PID=getmypid();
      if (file_exists($this::CRON_PID)) {
	if (is_dir("/proc/".readfile($this::CRON_PID))) {
	  echo "Locked by still running previous file, exiting...\n";
	  exit();
	}
      }
      // lock
      file_put_contents($this::CRON_PID,$MY_PID);
    }

    /*
     * unlock the crontab for next execution
     */
    function unlock_cron() {
      unlink($this::CRON_PID);
    }


    /* ------------------------------------------------------------ */
    /** 
     * Process the list of unresolved names : 
     * launch me as a crontab <3
     */
    function process_missing() {
      global $db;
      $db->query("SELECT * FROM http_permission WHERE kind=0;");
      $names=array();
      while ($db->next_record()) {
	$names[$db->f("id")] = $db->f("name");
      }
      
      foreach($names as $id => $name) {
	$result = $this->canonicalize($name);
	if ($result[0] == $this::KIND_BAD) {
	  $db->query(
		     "UPDATE http_permission SET kind=?, udate=NOW() WHERE id=?",
		     array($this::KIND_BAD, $id)
		     );
	}
	$db->query(
		   "UPDATE http_permission SET kind=?, name=?, udate=NOW() WHERE id=?",
		   array($result[0],$result[1],$id)
		   );
	// now resolve them or add them as needed : 
	if ($result[0] != $this::KIND_NAME) {
	  $db->query(
		     "INSERT INTO http_permission_bloc SET kind=?, bloc=?, permid=?;", 
		     array($result[0],$result[1],$id)
		     );
	} else {
	  $this->resolve($name,$id);
	}
      }
      
    } // process_missing


    /* ------------------------------------------------------------ */
    /**
     * UPDATE list of NAMES whose TTL is expired (half of ttl passed since last update)
     * launch me as a crontab <3
     */
    function process_expired() {
      global $db;

      $db->query("SELECT * FROM http_permission WHERE kind=5 AND udate< DATE_SUB(NOW(), INTERVAL (ttl/2) SECOND);");
      $updates=array();
      while ($db->next_record()) {
	$updates[$db->f("id")] = $db->f("name");
      }
      foreach($updates as $id => $name) {
	$this->resolve($name,$id);
      }
    } // process_expired


    /* ------------------------------------------------------------ */
    /**
     * Update the ipset by ADDING or REMOVING ipset entries according to the DB content
     * launch me as a crontab <3
     */
    function process_ipset() {
      global $db;

      putenv("PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin");

      $all=array(
		 $this::KIND_IPV4 => "http-perm-ip4",
		 $this::KIND_IPV6 => "http-perm-ip6",
		 $this::KIND_IPV4BLOC => "http-perm-net4",
		 $this::KIND_IPV6BLOC => "http-perm-net6",
		 );

      // update the ipsets (add all)
      $everyone=array();
      foreach($all as $kind => $ipset) {
	$db->query("SELECT bloc FROM http_permission_bloc WHERE kind=?",array($kind));
	while ($db->next_record()) {
	  unset($out);
	  exec("ipset add $ipset ".escapeshellarg($db->f("bloc"))." 2>/dev/null",$out,$ret);
	  if ($ret==0) echo "Added $ipset:".$db->f("bloc")."\n";
	  $everyone[$kind][$db->f("bloc")]=1;
	}
      }

      // remove IPs that should not be in ipset anymore : 
      foreach($all as $kind => $ipset) {
	$out=array();
	exec("LC_ALL=C ipset list $ipset",$out);
	$members=false;
	foreach($out as $line) {
	  $line=trim($line);
	  if ($members) {
	    // search for $line in permission_bloc as a KIND_IPV4 style. if not present, removes it 
	    if (!isset($everyone[$kind][$line])) {
	      exec("ipset del $ipset ".escapeshellarg($line));
	      echo "Removed $ipset:".$line."\n";
	    }
	  }
	  if ($line=="Members:") {
	    $members=true;
	  }
	}	
      } 


    } // process_ipset


    /**
     * update the http_log "permid" entries to tell whether an IP was authorized in the permission_list
     */
    function process_logs() {
      global $db;

      // load the IP / blocs
      $allowedip=array();
      $db->query("SELECT permid, bloc FROM http_permission_bloc WHERE kind=".$this::KIND_IPV4." OR kind=".$this::KIND_IPV6.";");
      while ($db->next_record()) {
	$allowedip[$db->f("bloc")]=$db->f("permid");
      }
      foreach($allowedip as $ip => $id) {
	$db->query("UPDATE http_log SET permid=? WHERE dstip=? AND sdate > DATE_SUB(NOW(), INTERVAL 7 DAY) AND permid=0;",array($id, $ip) );
      }
      unset($allowedip);

      // check the blocs : 
      $allowedbloc=array();
      $db->query("SELECT bloc,permid FROM http_permission_bloc WHERE kind=".$this::KIND_IPV4BLOC." OR kind=".$this::KIND_IPV6BLOC.";");
      while ($db->next_record()) {
	$allowedbloc[$db->f("bloc")]=$db->f("permid");
      }
      
      require_once("/usr/share/alternc/panel/class/IpUtils.php");
      

      $db->query("SELECT DISTINCT(dstip) AS dstip FROM http_log WHERE sdate > DATE_SUB(NOW(), INTERVAL 7 DAY) AND permid=0;");
      $results=array();
      while ($db->next_record()) {
	foreach($allowedbloc as $bloc => $id) {
	  if (IpUtils::checkip($db->f("dstip"), $bloc)) {
	    $results[$db->f("dstip")] = $db->f("permid");
	    break; // next log entry NOW
	  }
	}
      }
      foreach($results as $ip => $id) {
	$db->query("UPDATE http_log SET permid=? WHERE dstip=? AND sdate > DATE_SUB(NOW(), INTERVAL 7 DAY) AND permid=0;",array($id, $ip) );
      }
      
    } // process_logs

}

/* Class m_httpperm */
