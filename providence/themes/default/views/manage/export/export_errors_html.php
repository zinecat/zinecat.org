<?php
/* ----------------------------------------------------------------------
 * manage/export/export_errors_html.php:
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2014 Whirl-i-Gig
 *
 * For more information visit http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license as published by Whirl-i-Gig
 *
 * CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * This source code is free and modifiable under the terms of
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details, or visit the CollectiveAccess web site at
 * http://www.CollectiveAccess.org
 *
 * ----------------------------------------------------------------------
 */

$va_errors = $this->getVar('errors');

if($va_errors && is_array($va_errors)){
	print "<div class='notification-error-box'><h2 style='margin-left:25px;'>"._t("Export mapping has errors")."</h2>";
	print "<ul>";

	foreach($va_errors as $vs_error){
		print "<li class='notification-error-box'>$vs_error</li>";
	}

	print "</ul></div>";
}
