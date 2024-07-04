<?php
/*
 httpperm_list.php
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

if (!$r=$httpperm->get_list()) {
	$error=$err->errstr();
}

?>
<h3><?php __("HTTP Permission list"); ?></h3>
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

<?php if ($quota->cancreate("httpperm")) { ?>

<p><a class="inb" href="httpperm_log.php"><?php __("View the log of HTTP requests from your account"); ?></a></p>
<p><?php __("Enter the IP, Prefix or DNS Name to add as an allowed outgoing HTTP / HTTPS."); ?></p>
<form method="post" action="httpperm_doedit.php" name="main" id="main">
<?php csrf_get(); ?>
<!-- honeypot fields -->

  <input type="hidden" name="create" value="1" />
  <table border="1" cellspacing="0" cellpadding="4" class="tedit">
    <tr>
      <th><label for="name"><?php __("IPv4, IPv6, IP/Prefix or DNS Name"); ?></label></th>
      <td><input type="text" class="int" name="name" id="name" value="<?php ehe($rr[0]["name"]); ?>" size="60" maxlength="255" /></td>
    </tr>
  </table>
  <p>
    <input type="submit" class="inb ok" name="submit" value="<?php __("Save"); ?>" /> &nbsp; 
  </p>
</form>

<script type="text/javascript">
 document.forms['main'].name.focus();
</script>


<?php  	} ?>

 <?php if (count($r)) { ?>
<form method="post" action="httpperm_del.php">
   <?php csrf_get(); ?>
<table class="tlist" id="httpperm_list_table">
<thead>
  <tr><th> </th><th><?php __("IP, Network or DNS name"); ?></th><th><?php __("Date"); ?></th><th><?php __("Match List"); ?></th></tr>
</thead>
<?php
reset($r);
while (list($key,$val)=each($r)) { ?>
	<tr class="lst">
		<td align="center">
                  <input type="hidden" name="names[<?php ehe($val['id']); ?>]" value="<?php ehe($val["name"]); ?>" />
<input type="checkbox" class="inc" id="del_<?php ehe($val["id"]); ?>" name="del_<?php ehe($val["id"]); ?>" value="<?php ehe($val["id"]); ?>" /></td>
					<?php /* TODO: add a grey style if not YET added (+ a title ?) */ ?>
    <td><label for="del_<?php ehe($val["id"]); ?>"><?php ehe($val["name"]); ?></label></td>
    <td><?php ehe($val["cdate"]); ?></td>
    <td><?php
					if ($val["matchlist"]) {
					  echo str_replace(" ","<br />",($val["matchlist"])); 
					} else {
					  echo "<i>"._("pending...")."</i>";
					}
?></td>

	</tr>
<?php
	}
?>
</table>
<p><input type="submit" name="submit" class="inb delete" value="<?php __("Delete checked IP, Network or DNS names"); ?>" /></p>
</form>
  <?php } ?>



<?php
$mem->show_help("httpperm_list");
?>
<script type="text/javascript">

$(document).ready(function() 
    { 
        $("#httpperm_list_table").tablesorter(); 
    } 
); 
</script>

<?php include_once("foot.php"); ?>
