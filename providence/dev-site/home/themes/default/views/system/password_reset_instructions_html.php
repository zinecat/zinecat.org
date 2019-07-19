<?php
/* ----------------------------------------------------------------------
 * app/views/system/password_reset_instructions_html.php :
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
AppController::getInstance()->removeAllPlugins();
?>
<html>
<head>
	<title><?php print $this->request->config->get("app_display_name"); ?></title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />

	<link href="<?php print $this->request->getThemeUrlPath(); ?>/css/login.css" rel="stylesheet" type="text/css" />
	<?php
	print AssetLoadManager::getLoadHTML($this->request);
	?>

	<script type="text/javascript">
		// initialize CA Utils
		jQuery(document).ready(function() { caUI.utils.disableUnsavedChangesWarning(true); });
	</script>
</head>
<body>
<div align="center">
	<div id="loginBox">
		<div align="center">
			<img src="<?php print $this->request->getThemeUrlPath()."/graphics/logos/".$this->request->config->get('login_logo');?>" border="0">
		</div>
		<div id="systemTitle">

			<p class="smallContent">
				<?php print _t("Thank you for your request. We will send you an email with further instructions. If you don't receive the message after submitting the form, please wait a couple of minutes and also make sure to check your spam and junk folders. If you don't receive an email within 15 minutes after submitting the form, you may have misspelled your user name. Either resubmit the previous form or contact your CollectiveAccess administrator."); ?>
			</p>

		</div><!-- end  systemTitle -->
		<div id="loginForm">
			<?php print caNavLink($this->request, _t("Back to login"), 'loginLink', 'system/auth', 'login', ''); ?>
		</div><!-- end loginForm -->
	</div><!-- end loginBox -->
</div><!-- end center -->
</body>
</html>