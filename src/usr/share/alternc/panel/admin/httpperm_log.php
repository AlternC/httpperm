<?php
/*
 httpperm_log.php
 ----------------------------------------------------------------------
 AlternC - Web Hosting System
 Copyright (C) 2002-2017 by the AlternC Development Team.
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
 Purpose of file: List HTTP / HTTPS outgoing permission names for a user
 ----------------------------------------------------------------------
*/
require_once("../class/config.php");
include_once("head.php");

if (!$r=$httpperm->get_log()) {
	$error=$err->errstr();
}
if ($admin->enabled) {
  $uidlist=$admin->get_list(1);
}
?>
<h3><?php __("HTTP Permission log"); ?></h3>
<hr id="topbar"/>
<br />
 
<?php
if (isset($error) && $error) {
?>
<p class="alert alert-danger"><?php echo $error ?></p>
<?php 
include_once("foot.php");
exit();
} ?>

<?php
if (isset($info) && $info) {
?>
<p class="alert alert-info"><?php echo $info ?></p>
    <?php } ?>

<p><a class="inb" href="httpperm_list.php"><?php __("Manage your HTTP Permissions"); ?></a></p>

 <?php if (count($r)) { ?>
<table class="tlist">
<thead>
  <tr><th> </th><th><?php __("Date"); ?></th>
 <?php if ($admin->enabled) {  ?><th><?php __("Account"); ?></th><?php } ?>
  <th><?php __("IP"); ?></th><th><?php __("Port"); ?></th><th><?php __("Name"); ?></th></tr>
</thead>
<?php
reset($r);
while (list($key,$val)=each($r)) { ?>
	<tr class="lst">
		<td align="center">
    <td><?php ehe($val["sdate"]); ?></td>
<?php if ($admin->enabled) {  ?><td><?php
					if (isset($uidlist[$val["uid"]])) {
					  echo "<a title=\""._("Log into this account")."\" href=\"/adm_login.php?id=".$val["uid"]."\">".$uidlist[$val["uid"]]["login"]."</a>";
					} else {
					  echo "UID: ".$val["uid"];
					}
 ?></td><?php } ?>
					<td><?php
					   if ($val["permid"]) {
					     echo "<img src=\"/images/check_ok.png\" title=\""._("IP Allowed")."\"/>";	
					   } else {
					     echo "<img src=\"/images/check_no.png\" title=\""._("IP NOT Allowed")."\"/>";
					   }
  echo " &nbsp; ";
  echo "<a title=\""._("Add this IP as an allowed one")."\" href=\"httpperm_doedit.php?create=1&name=".htmlentities($val["dstip"]).$isadmin."&backto=log\">".$val["dstip"]."</a>";
 ?></td>
<td><?php
									    if ($val["dstport"]=="80") echo "HTTP";
									    if ($val["dstport"]=="443") echo "HTTPS";
?></td>
<td><?php 

   $names=explode(" ",$val["dns"]);
									    foreach($names as $name) {
									      $name=trim($name); $name=trim($name,".");
									      if ($admin->enabled) $isadmin="&uid=".$val["uid"]; else $isadmin="";
									      // TODO : add this link ONLY if the name is NOT already ALLOWED
									      echo "<a title=\""._("Add this name as an allowed one")."\" href=\"httpperm_doedit.php?create=1&name=".htmlentities($name).$isadmin."&backto=log\">".$name."</a> &nbsp; ";
									    }

 ?></td>

	</tr>
<?php
							    }
?>
</table>
  <?php } ?>


<?php include_once("foot.php"); ?>
