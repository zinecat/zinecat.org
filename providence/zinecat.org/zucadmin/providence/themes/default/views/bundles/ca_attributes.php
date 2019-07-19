<?php
/* ----------------------------------------------------------------------
 * bundles/ca_attributes.php : 
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2009-2014 Whirl-i-Gig
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
 
	$vs_id_prefix 				= 	$this->getVar('placement_code').$this->getVar('id_prefix');
	$vs_error_source_code 		= 	$this->getVar('error_source_code');
	$vs_render_mode 			=	$this->getVar('render_mode');

	/** @var BaseModelWithAttributes $t_instance */
	$t_instance 				=	$this->getVar('t_instance');
	/** @var ca_metadata_elements $t_element */
	$t_element					=	$this->getVar('t_element');
	$va_elements 				=	$this->getVar('elements');
	$va_element_ids 			= 	$this->getVar('element_ids');
	$va_element_info 			= 	$this->getVar('element_info');
	$va_root_element 			= 	current($va_element_info);
	
	
	$vs_element_set_label 		= 	$this->getVar('element_set_label');
	
	$va_attribute_list 			= 	$this->getVar('attribute_list');
	
	$va_element_value_defaults 	= 	$this->getVar('element_value_defaults');
	
	$va_failed_inserts 			= 	$this->getVar('failed_insert_attribute_list');
	$va_failed_updates 			= 	$this->getVar('failed_update_attribute_list');
	
	$va_settings 				= 	$this->getVar('settings');
	$vb_read_only				=	((isset($va_settings['readonly']) && $va_settings['readonly'])  || ($this->request->user->getBundleAccessLevel($this->getVar('t_instance')->tableName(), $this->getVar('element_code')) == __CA_BUNDLE_ACCESS_READONLY__));
	$vb_batch					=	$this->getVar('batch');
	$va_element_settings 		=	$t_element->getSettings();

	$vb_is_read_only_for_existing_vals = false;
	if(($t_element->get('datatype') == __CA_ATTRIBUTE_VALUE_CONTAINER__) && isset($va_element_settings['readonlyTemplate']) && (strlen($va_element_settings['readonlyTemplate']) > 0)) {
		$vb_is_read_only_for_existing_vals = true;

		$va_display_vals = array_shift($t_instance->getAttributeDisplayValues($va_root_element['element_id'], $t_instance->getPrimaryKey()));
		$va_readonly_previews = array();
		if(is_array($va_display_vals)) {
			$vn_i = 0;
			foreach($va_display_vals as $vn_attr_id => $va_display_val) {
				$vs_template = "<unit relativeTo='{$t_instance->tableName()}.{$t_element->get('element_code')}' start='{$vn_i}' length='1'>{$va_element_settings['readonlyTemplate']}</unit>";
				$va_readonly_previews[$vn_attr_id] =
					caProcessTemplateForIDs($vs_template, $t_instance->tableName(), array($t_instance->getPrimaryKey()));
				$vn_i++;
			}
		}
	}
	
	// generate list of inital form values; the bundle Javascript call will
	// use the template to generate the initial form
	$va_initial_values = array();
	$va_errors = array();
	$vs_bundle_preview = '';
	
	$va_template_tags = $va_element_ids;
	$vs_display_template = caGetOption('displayTemplate', $va_element_settings);

	$va_element_settings = $t_element->getSettings();
	if($t_instance->getAppConfig()->get('always_show_bundle_preview_for_attributes') || $vs_display_template) {
		$vs_bundle_preview = $t_instance->getAttributesForDisplay($va_root_element['element_id'], null, array('showHierarchy' => true));
	}

	if (sizeof($va_attribute_list)) {
		$va_item_ids = array();
		foreach ($va_attribute_list as $o_attr) {
			$va_initial_values[$o_attr->getAttributeID()] = array();
			foreach($o_attr->getValues() as $o_value) {
				$vn_attr_id = $o_attr->getAttributeID();
				$vn_element_id = $o_value->getElementID();
				
				if ($va_failed_updates[$vn_attr_id] && !in_array($o_value->getDatatype(), array(
					__CA_ATTRIBUTE_VALUE_LCSH__, 
					__CA_ATTRIBUTE_VALUE_PLACE__,
					__CA_ATTRIBUTE_VALUE_OCCURRENCE__,
					__CA_ATTRIBUTE_VALUE_TAXONOMY__,
					__CA_ATTRIBUTE_VALUE_INFORMATIONSERVICE__,
					__CA_ATTRIBUTE_VALUE_OBJECTREPRESENTATIONS__,
					__CA_ATTRIBUTE_VALUE_ENTITIES__
				))) {
					// copy value from failed update into form (so user can correct it)
					$vs_display_val = $va_failed_updates[$vn_attr_id][$vn_element_id];
				} else {
					$vs_display_val = $o_value->getDisplayValue(array('request' => $this->request, 'includeID' => true));
				}
				
				$va_initial_values[$vn_attr_id][$vn_element_id] = $vs_display_val;
				
				if (isset($va_element_info[$vn_element_id]) && isset($va_element_info[$vn_element_id]['settings']['render']) && ($va_element_info[$vn_element_id]['settings']['render'] == 'lookup')) {		// autocompleter-based mode for list attributes
					$va_template_tags[] = "{$vn_element_id}_label";
					$va_initial_values[$vn_attr_id]["{$vn_element_id}_label"] = '';
					$va_item_ids[] = (int)$vs_display_val;
				}
				
			}
			$va_initial_values[$o_attr->getAttributeID()]['locale_id'] = $o_attr->getLocaleID();
			
			// set errors for attribute
			if(is_array($va_action_errors = $this->request->getActionErrors($vs_error_source_code, $o_attr->getAttributeID()))) {
				foreach($va_action_errors as $o_error) {
					$va_errors[$o_attr->getAttributeID()][] = array('errorDescription' => $o_error->getErrorDescription(), 'errorCode' => $o_error->getErrorNumber());
				}
			}
		}		
				
		if(sizeof($va_item_ids)) {
			$t_list_item = new ca_list_items();
			$va_labels = $t_list_item->getPreferredDisplayLabelsForIDs($va_item_ids);
			foreach($va_initial_values as $vn_attr_id => $va_values) {
				foreach($va_values as $vn_element_id => $vs_value) {
					$va_initial_values[$vn_attr_id][$vn_element_id.'_label'] = $va_labels[$va_initial_values[$vn_attr_id][$vn_element_id]];
				}
			}
		}
	} else {
		// set labels for replacement in blank lookups	
		if (is_array($va_element_ids)) {
			foreach($va_element_ids as $vn_element_id) {
				$va_template_tags[] = "{$vn_element_id}_label";
			}
		}
	}
	
	// bundle settings
	global $g_ui_locale;
	if (!$vs_add_label = $va_settings['add_label'][$g_ui_locale]) {
		$vs_add_label = _t("Add %1", $vs_element_set_label);
	}
	
	if ($vb_batch) {
		print caBatchEditorAttributeModeControl($vs_id_prefix);
	} else {
		print caEditorBundleShowHideControl($this->request, $vs_id_prefix, $va_settings, caInitialValuesArrayHasValue($vs_id_prefix, $va_initial_values));
	}
	print caEditorBundleMetadataDictionary($this->request, $vs_id_prefix, $va_settings);
?>
<div id="<?php print $vs_id_prefix; ?>" <?php print $vb_batch ? "class='editorBatchBundleContent'" : ''; ?>>
<?php
if (caGetOption('canMakePDF', $va_element_info[$t_element->getPrimaryKey()]['settings'], false)) {
	$va_template_list = caGetAvailablePrintTemplates('bundles', array('table' => $t_instance->tableName(), 'elementCode' => $t_element->get('element_code'), 'forHTMLSelect' => true));
	if (sizeof($va_template_list) > 0) {
?>
	<div class='editorBundlePrintControl'>
<?php
		print (sizeof($va_template_list) > 1) ? caHTMLSelect('template', $va_template_list, array('class' => 'dontTriggerUnsavedChangeWarning', 'id' => "{$vs_id_prefix}PrintTemplate")) : caHTMLHiddenInput('template', array('value' => array_pop($va_template_list), 'id' => "{$vs_id_prefix}PrintTemplate"));
		print "<a href='#' onclick='{$vs_id_prefix}Print(); return false;'>".caNavIcon($this->request, __CA_NAV_BUTTON_PDF_SMALL__)."</a>";
?>
	</div>
<?php
	}
}
	//
	// The bundle template - used to generate each bundle in the form
	//
?>
	<textarea class='caItemTemplate' style='display: none;'>
		<div id="<?php print $vs_id_prefix; ?>Item_{n}" class="labelInfo">	
			<span class="formLabelError">{error}</span>
<?php
	if (($vs_render_mode !== 'checklist') && !$vb_read_only) {		// static (non-repeating) checkbox list for list attributes
?>
			<div style="float: right;">
				<a href="#" class="caDeleteItemButton"><?php print caNavIcon($this->request, __CA_NAV_BUTTON_DEL_BUNDLE__); ?></a>
			</div>				
<?php
	}
		
	if (!$vb_batch && ($vs_presets = $t_element->getPresetsAsHTMLFormElement(array('width' => '100px')))) {
		print "<div style='float: right; margin-right: 10px;'>{$vs_presets}</div>\n";
	}
	
			foreach($va_elements as $vn_container_id => $va_element_list) {
				if ($vn_container_id === '_locale_id') { continue; }
?>
				<table class="attributeListItem">
					<tr>
<?php
						foreach($va_element_list as $vs_element) {
							// any <textarea> tags in the template needs to be renamed to 'textentry' for the template to work
							print '<td class="attributeListItem">'.str_replace("textarea", "textentry", $vs_element).'</td>';
						}
?>
					</tr>
				</table>
<?php
			}

if (caGetOption('canMakePDFForValue', $va_element_info[$t_element->getPrimaryKey()]['settings'], false)) {
	$va_template_list = caGetAvailablePrintTemplates('bundles', array('table' => $t_instance->tableName(), 'elementCode' => $t_element->get('element_code'), 'forHTMLSelect' => true));
	if (sizeof($va_template_list) > 0) {
?>
	<div class='editorBundleValuePrintControl' id='<?php print $vs_id_prefix; ?>_print_control_{n}'>
<?php
		print (sizeof($va_template_list) > 1) ? caHTMLSelect('template', $va_template_list, array('class' => 'dontTriggerUnsavedChangeWarning', 'id' => "{$vs_id_prefix}PrintTemplate{n}")) : caHTMLHiddenInput('template', array('value' => array_pop($va_template_list), 'id' => "{$vs_id_prefix}PrintTemplate{n}"));
		print "<a href='#' onclick='{$vs_id_prefix}Print({n}); return false;'>".caNavIcon($this->request, __CA_NAV_BUTTON_PDF_SMALL__)."</a>";
?>
	</div>
<?php
	}
}	

			if (isset($va_elements['_locale_id'])) {
				print ($va_elements['_locale_id']['hidden']) ? $va_elements['_locale_id']['element'] : '<div class="formLabel">'._t('Locale').' '.$va_elements['_locale_id']['element'].'</div>';
			}
?>
		</div>
	</textarea>
	
	<div class="bundleContainer">
		<div class="caItemList">
<?php
			if($vb_is_read_only_for_existing_vals) {
				// hidden list of previews for read-only containers. these get inserted
				// instead of the bundle form if the container is configured to do so

				// it also includes javascript to make the bundle form re-appear
				// if the user clicks an "edit" button next to the preview text
?>

				<div style="visibility: hidden; height: 0px;">
<?php
					foreach($va_readonly_previews as $vn_attr_id => $vs_readonly_preview) {
?>
						<div class="caReadonlyContainer" id="caReadonlyContainer<?php print $vs_id_prefix.'_'.$vn_attr_id; ?>">
							<a class="caReadonlyContainerEditLink" id="caContainerEditLink<?php print $vs_id_prefix.'_'.$vn_attr_id; ?>" href="#"><?php print _t('Edit'); ?></a>
							<div class="caReadonlyContainerDisplay"><?php print $vs_readonly_preview; ?></div>
						</div>
						<script type="text/javascript">
							jQuery(document).ready(function() {
								jQuery("#caContainerEditLink<?php print $vs_id_prefix . '_' . $vn_attr_id; ?>").click(function () {
									jQuery('#<?php print $vs_id_prefix; ?>Item_<?php print $vn_attr_id; ?>').show();
									jQuery('#caReadonlyContainer<?php print $vs_id_prefix.'_'.$vn_attr_id; ?>').hide();
									jQuery('input[name="<?php print $vs_id_prefix.'_dont_save_'.$vn_attr_id; ?>"]').val('0');
								});
							});
						</script>
<?php
						// this signals saveBundlesForScreen() that this particular value shouldn't be saved;
						// otherwise we nuke existing values that are in read-only mode
						print caHTMLHiddenInput($vs_id_prefix.'_dont_save_'.$vn_attr_id, array('value' => 1));
					}
?>
				</div>
<?php
				}
?>
		</div>
<?php
	if (($vs_render_mode !== 'checklist') && !$vb_read_only) {
?>
		<div class='button labelInfo caAddItemButton'><a href='#'><?php print caNavIcon($this->request, __CA_NAV_BUTTON_ADD__); ?> <?php print $vs_add_label; ?></a></div>
<?php
	}
?>
	</div>
</div>
			
<script type="text/javascript">
<?php
	if (!$vb_batch) {
		print $t_element->getPresetsJavascript($vs_id_prefix);
	}
	if ($vs_render_mode === 'checklist') {
?>
		caUI.initChecklistBundle('#<?php print $vs_id_prefix; ?>', {
			fieldNamePrefix: '<?php print $vs_id_prefix; ?>_',
			templateValues: [<?php print join(',', caQuoteList($va_template_tags)); ?>],
			initialValues: <?php print json_encode($va_initial_values); ?>,
			initialValueOrder: <?php print json_encode(array_keys($va_initial_values)); ?>,
			errors: <?php print json_encode($va_errors); ?>,
			itemID: '<?php print $vs_id_prefix; ?>Item_',
			templateClassName: 'caItemTemplate',
			itemListClassName: 'caItemList',
			minRepeats: <?php print ($vn_n = $this->getVar('min_num_repeats')) ? $vn_n : 0 ; ?>,
			maxRepeats: <?php print ($vn_n = $this->getVar('max_num_repeats')) ? $vn_n : 65535; ?>,
			defaultValues: <?php print json_encode($va_element_value_defaults); ?>,
			bundlePreview: <?php print caEscapeForBundlePreview($vs_bundle_preview); ?>,
			readonly: <?php print $vb_read_only ? "1" : "0"; ?>,
			defaultLocaleID: <?php print ca_locales::getDefaultCataloguingLocaleID(); ?>
		});
<?php	
	} else {
?>
		var caHideBundlesForReadOnlyContainers = function(attribute_id, values, element, isNew) {
			if(isNew) { return false; }
<?php
		if($vb_is_read_only_for_existing_vals) {
			// we hide the bundle form element and insert the preview instead
?>
			var bundleFormElement = jQuery("#" + element.container.replace('#', '') + "Item_" + attribute_id);
			bundleFormElement.hide();
			bundleFormElement.after(jQuery('#caReadonlyContainer<?php print $vs_id_prefix?>_' + attribute_id));
<?php
		}
?>
		};
		caUI.initBundle('#<?php print $vs_id_prefix; ?>', {
			fieldNamePrefix: '<?php print $vs_id_prefix; ?>_',
			templateValues: [<?php print join(',', caQuoteList($va_template_tags)); ?>],
			initialValues: <?php print json_encode($va_initial_values); ?>,
			initialValueOrder: <?php print json_encode(array_keys($va_initial_values)); ?>,
			forceNewValues: <?php print json_encode($va_failed_inserts); ?>,
			errors: <?php print json_encode($va_errors); ?>,
			itemID: '<?php print $vs_id_prefix; ?>Item_',
			templateClassName: 'caItemTemplate',
			itemListClassName: 'caItemList',
			addButtonClassName: 'caAddItemButton',
			deleteButtonClassName: 'caDeleteItemButton',
			minRepeats: <?php print ($vn_n = $this->getVar('min_num_repeats')) ? $vn_n : 0 ; ?>,
			maxRepeats: <?php print ($vn_n = $this->getVar('max_num_repeats')) ? $vn_n : 65535; ?>,
			showEmptyFormsOnLoad: <?php print intval($this->getVar('min_num_to_display')); ?>,
			hideOnNewIDList: ['<?php print $vs_id_prefix; ?>_download_control_', '<?php print $vs_id_prefix; ?>_print_control_',],
			showOnNewIDList: ['<?php print $vs_id_prefix; ?>_upload_control_'],
			defaultValues: <?php print json_encode($va_element_value_defaults); ?>,
			bundlePreview: <?php print caEscapeForBundlePreview($vs_bundle_preview); ?>,
			readonly: <?php print $vb_read_only ? "1" : "0"; ?>,
			defaultLocaleID: <?php print ca_locales::getDefaultCataloguingLocaleID(); ?>,
			onInitializeItem: caHideBundlesForReadOnlyContainers /* todo: look for better callback (or make one up?) */
		});
<?php
	}
?>
	
	function <?php print $vs_id_prefix; ?>Print(attribute_id) {
		if (!attribute_id) { attribute_id = ''; }
		var template = jQuery('#<?php print $vs_id_prefix; ?>PrintTemplate' + attribute_id).val();
		window.location = '<?php print caNavUrl($this->request, '*', '*', 'PrintBundle', array('element_code' => $t_element->get('element_code'), $t_instance->primaryKey() => $t_instance->getPrimaryKey())); ?>/template/' + template + '/attribute_id/' + attribute_id;
	}
</script>
