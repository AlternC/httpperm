<?php
/*
 httpperm_doedit.php
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
 Purpose of file: Editing an HTTP Permission 
 ----------------------------------------------------------------------
*/
require_once("../class/config.php");
$fields = array (
  "id"        => array ("post", "integer", ""),
  "create"        => array ("request", "integer", ""),
  "name"        => array ("request", "string", ""),
);
getFields($fields);

if (! $id && !$create) { //not a creation and not an edit
  $error=_("Error: neither a creation nor an edition");
  include("httpperm_list.php");
  exit();
}

if (! $id ) { //create
  $r=$httpperm->add($name);
} else { // edit

  $r=$httpperm->edit($id,$name);
}

if (!$r) {
  $error=$err->errstr();
  $rr[0]["name"]=$name;
  include_once("httpperm_edit.php");
  exit();
} else {
if ($create) {
  $info=_("The HTTP Permission has been successfully created");
} else {
  $info=_("The HTTP Permission account has been successfully saved");
}
if ($_GET["backto"]=="log") {
  include("httpperm_log.php");
  exit();
}
include("httpperm_list.php");
exit();

}

?>
