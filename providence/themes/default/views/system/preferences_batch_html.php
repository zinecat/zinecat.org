<?php
/* ----------------------------------------------------------------------
 * app/views/system/preferences_batch_html.php : 
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2012 Whirl-i-Gig
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
 
 	
	$t_user = $this->getVar('t_user');
	$vs_group = $this->getVar('group');
 
 ?>
<div class="sectionBox">
<?php
	print $vs_control_box = caFormControlBox(
		caFormSubmitButton($this->request, __CA_NAV_BUTTON_SAVE__, _t("Save"), 'PreferencesForm').' '.
		caNavButton($this->request, __CA_NAV_BUTTON_CANCEL__, _t("Reset"), '', 'system', 'Preferences', $this->request->getAction(), array()), 
		'', 
		''
	);

	$va_group_info = $t_user->getPreferenceGroupInfo($vs_group);
	print "<h1>"._t("Preferences").": "._t($va_group_info['name'])."</h1>\n";
	
	print caFormTag($this->request, 'Save', 'PreferencesForm');
	
	$va_prefs = $t_user->getValidPreferences($vs_group);
	
	$o_dm = Datamodel::load();
	print "<div class='preferenceSectionDivider'><!-- empty --></div>\n"; 
	
	foreach(array(
		'ca_objects', 'ca_object_lots', 'ca_entities', 'ca_places', 'ca_occurrences', 'ca_collections', 'ca_storage_locations',
		'ca_loans', 'ca_movements', 'ca_tours', 'ca_tour_stops'
	) as $vs_table) {
		if (!caTableIsActive($vs_table)) { continue; }
		$t_instance = $o_dm->getInstanceByTableName($vs_table, true);
		print "<h2>"._t('Batch user interface for %1', $t_instance->getProperty('NAME_PLURAL'))."</h2>";
		
		print "<table width='100%'><tr valign='top'><td width='250'>";
		foreach($va_prefs as $vs_pref) {
			if (!preg_match("!^batch_{$vs_table}_!", $vs_pref)) { continue; }
			print $t_user->preferenceHtmlFormElement($vs_pref, '^ELEMENT', array('genericUIList' => true));
		}
		print "</td>";
		
		print "</tr></table>\n";
		print "<div class='preferenceSectionDivider'><!-- empty --></div>\n"; 
	}
?>
		<input type="hidden" name="action" value="<?php print $this->request->getAction(); ?>"/>
	</form>
<?php
	print $vs_control_box;
?>

	<div class="editorBottomPadding"><!-- empty --></div>
</div>