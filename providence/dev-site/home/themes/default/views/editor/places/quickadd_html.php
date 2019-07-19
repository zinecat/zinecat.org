<?php
/* ----------------------------------------------------------------------
 * app/views/editor/places/quickadd_html.php : 
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2012-2015 Whirl-i-Gig
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
 	global $g_ui_locale_id;
 
 	$t_subject 			= $this->getVar('t_subject');
	$vn_subject_id 		= $this->getVar('subject_id');
	
	$va_restrict_to_types = $this->getVar('restrict_to_types');
	
	$vs_field_name_prefix = $this->getVar('field_name_prefix');
	$vs_n 				= $this->getVar('n');
	$vs_q				= caUcFirstUTF8Safe($this->getVar('q'), true);

	$vb_can_edit	 	= $t_subject->isSaveable($this->request);
	
	$vs_form_name = "PlaceQuickAddForm";
?>		
<script type="text/javascript">
	var caQuickAddFormHandler = caUI.initQuickAddFormHandler({
		formID: '<?php print $vs_form_name.$vs_field_name_prefix.$vs_n; ?>',
		formErrorsPanelID: '<?php print $vs_form_name; ?>Errors<?php print $vs_field_name_prefix.$vs_n; ?>',
		formTypeSelectID: '<?php print $vs_form_name; ?>TypeID<?php print $vs_field_name_prefix.$vs_n; ?>', 
		
		formUrl: '<?php print caNavUrl($this->request, 'editor/places', 'PlaceQuickAdd', 'Form'); ?>',
		fileUploadUrl: '<?php print caNavUrl($this->request, "editor/places", "PlaceEditor", "UploadFiles"); ?>',
		saveUrl: '<?php print caNavUrl($this->request, "editor/places", "PlaceQuickAdd", "Save"); ?>',
		
		headerText: '<?php print addslashes(_t('Quick add %1', $t_subject->getTypeName())); ?>',
		saveText: '<?php print addslashes(_t('Created %1 ', $t_subject->getTypeName())); ?> <em>%1</em>',
		busyIndicator: '<?php print addslashes(caBusyIndicatorIcon($this->request)); ?>'
	});
</script>
<form action="#" class="quickAddSectionForm" name="<?php print $vs_form_name; ?>" method="POST" enctype="multipart/form-data" id="<?php print $vs_form_name.$vs_field_name_prefix.$vs_n; ?>">
	<div class='quickAddDialogHeader'><?php
		print "<div class='quickAddTypeList'>"._t('Quick Add %1', $t_subject->getTypeListAsHTMLFormElement('change_type_id', array('id' => "{$vs_form_name}TypeID{$vs_field_name_prefix}{$vs_n}", 'onchange' => "caQuickAddFormHandler.switchForm();"), array('value' => $t_subject->get('type_id'), 'restrictToTypes' => $va_restrict_to_types)))."</div>";
		if ($vb_can_edit) {
			print "<div class='quickAddControls'>".caJSButton($this->request, __CA_NAV_BUTTON_ADD_LARGE__, _t("Add %1", $t_subject->getTypeName()), "{$vs_form_name}{$vs_field_name_prefix}{$vs_n}", array("onclick" => "caQuickAddFormHandler.save(event);"))
			.' '.caJSButton($this->request, __CA_NAV_BUTTON_CANCEL__, _t("Cancel"), "{$vs_form_name}{$vs_field_name_prefix}{$vs_n}", array("onclick" => "jQuery(\"#{$vs_form_name}".$vs_field_name_prefix.$vs_n."\").parent().data(\"panel\").hidePanel();"))."</div>\n";
		}
		print "<div class='quickAddProgress'></div><br style='clear: both;'/>";
?>
	</div>
	
	<div class="quickAddFormTopPadding"><!-- empty --></div>
	<div class="quickAddErrorContainer" id="<?php print $vs_form_name; ?>Errors<?php print $vs_field_name_prefix.$vs_n; ?>"> </div>
	<div class="quickAddSectionBox" id="{$vs_form_name}Container<?php print $vs_field_name_prefix.$vs_n; ?>">
<?php
			// Output hierarchy browser
			$va_lookup_urls = caJSONLookupServiceUrl($this->request, 'ca_places');
?>
	<div class='bundleLabel'><span class="formLabelText"><?php print _t('Location in hierarchy'); ?></span><br/>
		<div class="bundleContainer">
			<div class="caItemList">
				<div class="hierarchyBrowserContainer">
					<div id="caQuickAdd<?php print $vs_form_name; ?>HierarchyBrowser" class="hierarchyBrowserSmall">
						<!-- Content for hierarchy browser is dynamically inserted here by ca.hierbrowser -->
					</div><!-- end hierbrowser -->
					<div>
						<?php print _t('Search'); ?>: <input type="text" id="caQuickAdd<?php print $vs_form_name; ?>HierarchyBrowserSearch" name="search" value="<?php print htmlspecialchars($this->getVar('search'), ENT_QUOTES, 'UTF-8'); ?>" size="100"/>
					</div>
				</div>
							
				<script type="text/javascript">
					// Set up "add" hierarchy browser
					var o<?php print $vs_form_name.$vs_field_name_prefix; ?>HierarchyBrowser = null;				
					if (!o<?php print $vs_form_name.$vs_field_name_prefix; ?>HierarchyBrowser) {
						o<?php print $vs_form_name.$vs_field_name_prefix; ?>HierarchyBrowser = caUI.initHierBrowser('caQuickAdd<?php print $vs_form_name.$vs_field_name_prefix; ?>HierarchyBrowser', {
							levelDataUrl: '<?php print $va_lookup_urls['levelList']; ?>',
							initDataUrl: '<?php print $va_lookup_urls['ancestorList']; ?>',
							editButtonIcon: '<img src="<?php print $this->request->getThemeUrlPath(); ?>/graphics/buttons/arrow_grey_right.gif" border="0" title="Edit place">',
						
							readOnly: false,
							selectOnLoad: true,
							
							initItemID: '<?php print (int)$this->getVar("default_parent_id"); ?>',
							indicatorUrl: '<?php print $this->request->getThemeUrlPath(); ?>/graphics/icons/indicator.gif',
							displayCurrentSelectionOnLoad: true,
							
							currentSelectionIDID: '<?php print $vs_form_name; ?>_parent_id',
							currentSelectionDisplayID: 'browseCurrentSelection',
							onSelection: function(item_id, parent_id, name, display, type_id) {
								jQuery('#<?php print $vs_form_name; ?>_parent_id').val(item_id);
							}
						});
					}
					jQuery('#caQuickAdd<?php print $vs_form_name.$vs_field_name_prefix; ?>HierarchyBrowserSearch').autocomplete(
						{
							minLength: 3, delay: 800,
							source: '<?php print caNavUrl($this->request, 'lookup', 'Place', 'Get', array('noInline' => 1)); ?>',
							select: function(event, ui) {
								if (parseInt(ui.item.id) > 0) {
									o<?php print $vs_form_name.$vs_field_name_prefix; ?>HierarchyBrowser.setUpHierarchy(ui.item.id);	// jump browser to selected item
								}
								jQuery('#caQuickAdd<?php print $vs_form_name.$vs_field_name_prefix; ?>HierarchyBrowserSearch').val('');
							}
						}
					);
				</script>
				<input type="hidden" name="parent_id" value="<?php print (int)$this->getVar("default_parent_id"); ?>" id="<?php print $vs_form_name; ?>_parent_id"/>
			</div>
		</div>
	</div>
<?php
			$va_force_new_label = array();
			foreach($t_subject->getLabelUIFields() as $vn_i => $vs_fld) {
				$va_force_new_label[$vs_fld] = '';
			}
			$va_force_new_label['locale_id'] = $g_ui_locale_id;							// use default locale
			$va_force_new_label[$t_subject->getLabelDisplayField()] = $vs_q;				// query text is used for display field
			
			$va_form_elements = $t_subject->getBundleFormHTMLForScreen($this->getVar('screen'), array(
					'request' => $this->request, 
					'formName' => $vs_form_name.$vs_field_name_prefix.$vs_n,
					'restrictToTypes' => array($t_subject->get('type_id')),
					'forceLabelForNew' => $va_force_new_label,							// force query text to be default in label fields
					'omit' => array('parent_id')
			));
			
			print join("\n", $va_form_elements);
?>
		<input type='hidden' name='_formName' value='<?php print $vs_form_name.$vs_field_name_prefix.$vs_n; ?>'/>
		<input type='hidden' name='q' value='<?php print htmlspecialchars($vs_q, ENT_QUOTES, 'UTF-8'); ?>'/>
		<input type='hidden' name='screen' value='<?php print htmlspecialchars($this->getVar('screen')); ?>'/>
		<input type='hidden' name='types' value='<?php print htmlspecialchars(is_array($va_restrict_to_types) ? join(',', $va_restrict_to_types) : ''); ?>'/>
	</div>
</form>