<?php
/*
 httpperm_edit.php
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
 Purpose of file: edit/create an http permission bloc/dns name 
 ----------------------------------------------------------------------
*/
require_once("../class/config.php");
include_once("head.php");

if ( !isset($is_include) ) {
  $fields = array (
    "id"      => array ("request", "integer", ""),
    "create"  => array ("get", "integer", "0"),
    "name"     => array ("get", "string", "0"),
  );
  getFields($fields);
}

if (!$id && !$create) {
  $error=_("Neither a creation nor a edition");
  echo "<h3>"._("Create a HTTP Permission")."</h3>";
  echo "<p class=\"alert alert-danger\">$error</p>";
  include_once("foot.php");
  exit();
}

if (!$id && $create) { //creation
  echo "<h3>"._("Create a HTTP Permission")."</h3>";
  $rr=false;
} else {
   echo "<h3>"._("Editing a HTTP Permission")."</h3>";
  $rr=$httpperm->get_details($id);
  if (!$rr) {
    $error=$err->errstr();
  }
}

?>
<?php
if (isset($error) && $error) {
	echo "<p class=\"alert alert-danger\">$error</p>";
}
?>
<form method="post" action="httpperm_doedit.php" name="main" id="main" autocomplete="off">
<?php csrf_get(); ?>
<!-- honeypot fields -->

  <input type="hidden" name="id" value="<?php ehe($id); ?>" />
  <input type="hidden" name="create" value="<?php ehe($create); ?>" />
  <table border="1" cellspacing="0" cellpadding="4" class="tedit">
    <tr>
      <th><label for="name"><?php __("IPv4, IPv6, Prefix or DNS Name"); ?></label></th>
      <td><input type="text" class="int" name="name" id="name" value="<?php ehe($rr[0]["name"]); ?>" size="20" maxlength="64" /></td>
    </tr>
  </table>
  <p>
    <input type="submit" class="inb ok" name="submit" value="<?php __("Save"); ?>" /> &nbsp; 
    <input type="button" class="inb cancel" name="cancel" value="<?php __("Cancel"); ?>" onclick="document.location='httpperm_list.php'"/>
  </p>
</form>

<script type="text/javascript">
 document.forms['main'].name.focus();
</script>
<?php

include_once("foot.php"); 
?>
