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

// Bootstraps 
require_once("/usr/share/alternc/panel/class/config_nochk.php");

// Script lock through filesystem
$admin->stop_if_jobs_locked();

$httpperm->lock_cron();

$httpperm->process_missing();

$httpperm->process_expired();

$httpperm->process_ipset();

$httpperm->process_logs();

$httpperm->unlock_cron();

