<?php
/** ---------------------------------------------------------------------
 * app/helpers/displayHelpers.php : miscellaneous functions
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2009-2016 Whirl-i-Gig
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
 * @package CollectiveAccess
 * @subpackage utils
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License version 3
 * 
 * ----------------------------------------------------------------------
 */

 /**
   *
   */
   	
require_once(__CA_LIB_DIR__.'/core/Datamodel.php');
require_once(__CA_LIB_DIR__.'/core/Configuration.php');
require_once(__CA_LIB_DIR__.'/core/Parsers/TimeExpressionParser.php');
require_once(__CA_LIB_DIR__.'/core/Parsers/ExpressionParser.php');
require_once(__CA_LIB_DIR__."/ca/ApplicationPluginManager.php");
require_once(__CA_LIB_DIR__.'/core/Parsers/ganon.php');
require_once(__CA_LIB_DIR__.'/core/Parsers/DisplayTemplateParser.php');

/**
 * Regex used to parse bundle display template tags (Eg. ^I_am_a_tag)
 * More about bundle display templates here: http://docs.collectiveaccess.org/wiki/Bundle_Display_Templates
 */

define("__CA_BUNDLE_DISPLAY_TEMPLATE_TAG_REGEX__", "/\^([0-9]+(?=[.,;])|[\/A-Za-z0-9]+\[[\@\[\]\=\'A-Za-z0-9\.\-\/\:]+|[A-Za-z0-9_\.:\/]+[%]{1}[^ \^\t\r\n\"\'<>\(\)\{\}\/]*|[A-Za-z0-9_\.\/]+[:]{1}[A-Za-z0-9_\.\/]+|[A-Za-z0-9_\.\/:]+[~]{1}[A-Za-z0-9]+[:]{1}[A-Za-z0-9_\.\/]+|[A-Za-z0-9_\.\/:]+)/");
	
	# ------------------------------------------------------------------------------------------------
	/**
	 * @param $ps_item_locale -
	 * @param $pa_preferred_locales -
	 * @return Array - returns an associative array defining which locales should be used when displaying values; suitable for use with caExtractValuesByLocale()
	 */
	$g_user_locale_rules = array();
	function caGetUserLocaleRules($ps_item_locale=null, $pa_preferred_locales=null) {
		global $g_ui_locale, $g_ui_locale_id, $g_user_locale_rules;
		
		if (isset($g_user_locale_rules[$ps_item_locale])) { return $g_user_locale_rules[$ps_item_locale]; }
		
		$o_config = Configuration::load();
		$va_default_locales = $o_config->getList('locale_defaults');
		
		$va_preferred_locales = array();
		if ($ps_item_locale) {
			// if item locale is passed as locale_id we need to convert it to a code
			if (is_numeric($ps_item_locale)) {
				$t_locales = new ca_locales();
				if ($t_locales->load($ps_item_locale)) {
					$ps_item_locale = $t_locales->getCode();
				} else {
					$ps_item_locale = null;
				}
			}
			if ($ps_item_locale) {
				$va_preferred_locales[$ps_item_locale] = true;
			}
		}
		
		if (is_array($pa_preferred_locales)) {
			foreach($pa_preferred_locales as $vs_preferred_locale) {
				$va_preferred_locales[$vs_preferred_locale] = true;
			}
		}
		
		$va_fallback_locales = array();
		if (is_array($va_default_locales)) {
			foreach($va_default_locales as $vs_fallback_locale) {
				if (!isset($va_preferred_locales[$vs_fallback_locale]) || !$va_preferred_locales[$vs_fallback_locale]) {
					$va_fallback_locales[$vs_fallback_locale] = true;
				}
			}
		}
		if ($g_ui_locale) {
			if (!isset($va_preferred_locales[$g_ui_locale]) || !$va_preferred_locales[$g_ui_locale]) {
				$va_preferred_locales[$g_ui_locale] = true;
			}
		}

		$va_rules = array(
			'preferred' => $va_preferred_locales,	/* all of these locales will display if available */
			'fallback' => $va_fallback_locales		/* the first of these that is available will display, but only if none of the preferred locales are available */
		);

		if($ps_item_locale){ $g_user_locale_rules[$ps_item_locale] = $va_rules; }
		
		return $va_rules;
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 *
	 * @param $pa_locale_rules - Associative array defining which locales to extract, and how to fall back to alternative locales should your preferred locales not exist in $pa_values
	 * @param $pa_values - Associative array keyed by unique item_id and then locale code (eg. en_US) or locale_id; the values can be anything - string, numbers, objects, arrays, etc.
	 * @param $pa_options [optional] - Associative array of options; available options are:
	 *									'returnList' = return an indexed array of found values rather than an associative array keys on unique item_id [default is false]
	 *									'debug' = print debugging information [default is false]
	 * @return Array - an array of found values keyed by unique item_id; or an indexed list of found values if option 'returnList' is passed in $pa_options
	 */
	function caExtractValuesByLocale($pa_locale_rules, $pa_values, $pa_options=null) {
		if (!is_array($pa_values)) { return array(); }
		$va_locales = ca_locales::getLocaleList();
		
		if (!is_array($pa_options)) { $pa_options = array(); }
		if (!isset($pa_options['returnList'])) { $pa_options['returnList'] = false; }
		
		if (!is_array($pa_values)) { return array(); }
		$va_values = array();
		foreach($pa_values as $vm_id => $va_value_list_by_locale) {
			if (sizeof($va_value_list_by_locale) == 1) {		// Don't bother looking if there's just a single value
				$va_values[$vm_id] = array_pop($va_value_list_by_locale);
				continue;
			}
			foreach($va_value_list_by_locale as $pm_locale => $vm_value) {
				// convert locale_id to locale string
				if (is_numeric($pm_locale)) {
					if (!$va_locales[$pm_locale]) { continue; }	// invalid locale_id?
					$vs_locale = $va_locales[$pm_locale]['language'].'_'.$va_locales[$pm_locale]['country'];
				} else {
					$vs_locale = $pm_locale;
				}
				
				// try to find values for preferred locale
				if (isset($pa_locale_rules['preferred'][$vs_locale]) && $pa_locale_rules['preferred'][$vs_locale]) {
					$va_values[$vm_id] = $vm_value;
					break;
				}
				
				// try fallback locales
				if (isset($pa_locale_rules['fallback'][$vs_locale]) && $pa_locale_rules['fallback'][$vs_locale]) {
					$va_values[$vm_id] = $vm_value;
				}
			}
			
			if (!isset($va_values[$vm_id])) {
				// desperation mode: pick an available locale
				$va_values[$vm_id] = array_pop($va_value_list_by_locale);
			}
		}
		return ($pa_options['returnList']) ? array_values($va_values) : $va_values;
	}
	# ------------------------------------------------------------------------------------------------
	function caExtractValuesByUserLocale($pa_values, $ps_item_locale=null, $pa_preferred_locales=null, $pa_options=null) {
		return caExtractValuesByLocale(caGetUserLocaleRules($ps_item_locale, $pa_preferred_locales), $pa_values, $pa_options);
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * Takes the output of BaseModel->getHierarchyAncestors() and tries to extract the appropriate values for the current user's locale.
	 * This is designed for the common case where you want to get a list of ancestors with their labels in the appropriate language,
	 * so you call getHierarchyAncestors() with the 'getHierarchyAncestors' option set to the label table. What you get back is a simple
	 * list where each item is a node with the table fields + the label fields; if a node has labels in several languages then you'll get back
	 * dupes - one for each language. 
	 *
	 * This function takes that list with dupes and returns an array key'ed upon the primary key containing a single entry for each node
	 * and the label set to the appropriate language - no dupes!
	 *
	 * @param array - the list of ancestor hierarchy nodes as returned by BaseModel->getHierarchyAncestors()
	 * @param string - the field name of the primary key of the hierarchy (eg. 'place_id' for ca_places)
	 * @return array - the list of ancestors with labels in the appropriate language; array is indexed by the primary key
	 */
	function caExtractValuesByUserLocaleFromHierarchyAncestorList($pa_list, $ps_primary_key_name, $ps_label_display_field, $ps_use_if_no_label_field, $ps_default_text='???') {
		if (!is_array($pa_list)) { return array(); }
		$va_values = array();
		foreach($pa_list as $vn_i => $va_item) {
			if (!isset($va_item[$ps_label_display_field]) || !$va_item[$ps_label_display_field]) {
				if (!isset($va_item[$ps_use_if_no_label_field]) || !($va_item[$ps_label_display_field] = $va_item[$ps_use_if_no_label_field])) {
					$va_item[$ps_label_display_field] = $ps_default_text;
				}
			}
			$va_values[$va_item['NODE'][$ps_primary_key_name]][$va_item['NODE']['locale_id']] = $va_item;
		}
		
		return caExtractValuesByUserLocale($va_values);
	}
	# ------------------------------------------------------------------------------------------------
	function caExtractValuesByUserLocaleFromHierarchyChildList($pa_list, $ps_primary_key_name, $ps_label_display_field, $ps_use_if_no_label_field, $ps_default_text='???') {
		if (!is_array($pa_list)) { return array(); }
		$va_values = array();
		foreach($pa_list as $vn_i => $va_item) {
			if (!$va_item[$ps_label_display_field]) {
				if (!($va_item[$ps_label_display_field] = $va_item[$ps_use_if_no_label_field])) {
					$va_item[$ps_label_display_field] = $ps_default_text;
				}
			}
			$va_values[$va_item[$ps_primary_key_name]][$va_item['locale_id']] = $va_item;
		}
		
		return caExtractValuesByUserLocale($va_values);
	}
	# ------------------------------------------------------------------------------------------------
	function caFormatFieldErrorsAsHTML($pa_errors, $ps_css_class) {
		
		$vs_output = "<ul class='{$ps_css_class}'>\n";
		foreach($pa_errors as $o_e) {
			$vs_output .= '<li class="'.$ps_css_class.'"><img src=""/> ';
			$vs_output .= $o_e->getErrorMessage()."</li>";
		}
		$vs_output .= "</ul>\n";
		
		
		return $vs_output;
	}
	# ------------------------------------------------------------------------------------------------
	function caFormControlBox($ps_left_content, $ps_middle_content, $ps_right_content, $ps_second_row_content='') {
		$vs_output = '<div class="control-box rounded">
		<div class="control-box-left-content">'.$ps_left_content;
			
		$vs_output .= '</div>
		<div class="control-box-right-content">'.$ps_right_content;

		$vs_output .= '</div><div class="control-box-middle-content">'.$ps_middle_content.'</div>';
		
		if ($ps_second_row_content) {
			$vs_output .= '<div class="clear"><!--empty--></div>'.$ps_second_row_content;
		}
		
	$vs_output .= '</div>
	<div class="clear"><!--empty--></div>'."\n";
	
		return $vs_output;
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 *
	 */
	function caDeleteWarningBox($po_request, $t_instance, $ps_item_name, $ps_module_path, $ps_controller, $ps_cancel_action, $pa_parameters) {
		if ($vs_warning = isset($pa_parameters['warning']) ? $pa_parameters['warning'] : null) {
			$vs_warning = '<br/>'.$vs_warning;
		}
		
		$vs_remapping_controls = caDeleteRemapper($po_request, $t_instance);
		$vs_output = caFormTag($po_request, 'Delete', 'caDeleteForm', null, 'post', 'multipart/form-data', '_top', array('disableUnsavedChangesWarning' => true));
		$vs_output .= "<div class='delete-control-box'>".caFormControlBox(
			"<div class='delete_warning_box'>"._t('Really delete "%1"?', $ps_item_name)."</div>".
			($vs_remapping_controls ? "<div class='delete_remapping_controls'>{$vs_remapping_controls}</div>" : ''),
			$vs_warning,
			caFormSubmitButton($po_request, __CA_NAV_BUTTON_DELETE__, _t("Delete"), 'caDeleteForm', array()).
			caNavButton($po_request, __CA_NAV_BUTTON_CANCEL__, _t("Cancel"), '', $ps_module_path, $ps_controller, $ps_cancel_action, $pa_parameters)
		)."</div>\n";
		
		
		foreach(array_merge($pa_parameters, array('confirm' => 1)) as $vs_f => $vs_v) {
			$vs_output .= caHTMLHiddenInput($vs_f, array('value' => $vs_v));
		}
		$vs_output .= caHTMLHiddenInput($t_instance->primaryKey(), array('value' => $t_instance->getPrimaryKey()));
		$vs_output .= "</form>\n";
		
		return $vs_output;
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 *
	 */
	function caDeleteRemapper($po_request, $t_instance) {
		$vs_instance_table = $t_instance->tableName();
		
		$vn_count = 0;
		$va_buf = array();
		switch($vs_instance_table) {
			case 'ca_relationship_types':
				// get # of relationships using this type
				$vn_rel_count = $t_instance->getRelationshipCountForType();
				$t_rel_instance = $t_instance->getAppDatamodel()->getInstanceByTableNum($t_instance->get('table_num'));
				if (!$t_rel_instance->load($t_instance->get('table_num'))) { return ''; }
				if ($vn_rel_count == 1) {
					$va_buf[] = _t("Type is used by %1 %2", $vn_rel_count, $t_rel_instance->getProperty('NAME_PLURAL'))."<br>\n";
				} else {
					$va_buf[] = _t("Type is used by %1 %2", $vn_rel_count, $t_rel_instance->getProperty('NAME_PLURAL'))."<br>\n";
				}
				$vn_count += $vn_rel_count;
				
				$vs_typename = _t('relationship type');
				break;
			default:
				// Check relationships
				$va_tables = array(
					'ca_objects', 'ca_object_lots', 'ca_entities', 'ca_places', 'ca_occurrences', 'ca_collections', 'ca_storage_locations', 'ca_list_items', 'ca_loans', 'ca_movements', 'ca_tours', 'ca_tour_stops', 'ca_object_representations'
				);
				
				if (!in_array($t_instance->tableName(), $va_tables)) { return null; }
				
				foreach($va_tables as $vs_table) {
					$va_items = $t_instance->getRelatedItems($vs_table);
					
					if (!($vn_c = sizeof($va_items))) { continue; }
					if ($vn_c == 1) {
						$va_buf[] = _t("Has %1 relationship to %2", $vn_c, caGetTableDisplayName($vs_table, true))."<br>\n";
					} else {
						$va_buf[] = _t("Has %1 relationships to %2", $vn_c, caGetTableDisplayName($vs_table, true))."<br>\n";
					}
					$vn_count += $vn_c;
				}
				
				// Check attributes
				if ($vn_datatype = $t_instance->authorityElementDatatype()) {
					if ($vn_c = $t_instance->getAuthorityElementReferences(array('countOnly' => true))) {
						if ($vn_c == 1) {
							$va_buf[] = _t("Is referenced %1 time", $vn_c)."<br>\n";
						} else {
							$va_buf[] = _t("Is referenced %1 times", $vn_c)."<br>\n";
						}
						$vn_count += $vn_c;
					}
				}
				
				$vs_typename = $t_instance->getTypeName();
		}
		
		$vs_output = '';
		if (sizeof($va_buf)) {
			// add autocompleter for remapping
			if ($vn_count == 1) {
				$vs_output .= "<h3 id='caDeleteReferenceCount'>"._t('This %1 is referenced %2 time', $vs_typename, $vn_count).". "._t('When deleting this %1:', $vs_typename)."</h3>\n";
			} else {
				$vs_output .= "<h3 id='caDeleteReferenceCount'>"._t('This %1 is referenced %2 times', $vs_typename, $vn_count).". "._t('When deleting this %1:', $vs_typename)."</h3>\n";
			}
			$vs_output .= caHTMLRadioButtonInput('referenceHandling', array('value' => 'delete', 'checked' => 1, 'id' => 'caReferenceHandlingDelete')).' '._t('remove all references')."<br/>\n";
			$vs_output .= caHTMLRadioButtonInput('referenceHandling', array('value' => 'remap', 'id' => 'caReferenceHandlingRemap')).' '._t('transfer references to').' '.caHTMLTextInput('remapTo', array('value' => '', 'size' => 40, 'id' => 'remapTo', 'class' => 'lookupBg', 'disabled' => 1));
			$vs_output .= "<a href='#' class='button' onclick='jQuery(\"#remapToID\").val(\"\"); jQuery(\"#remapTo\").val(\"\"); jQuery(\"#caReferenceHandlingClear\").css(\"display\", \"none\"); return false;' style='display: none;' id='caReferenceHandlingClear'>"._t('Clear').'</a>';
			$vs_output .= caHTMLHiddenInput('remapToID', array('value' => '', 'id' => 'remapToID'));
			$vs_output .= "<script type='text/javascript'>";
			
			$va_service_info = caJSONLookupServiceUrl($po_request, $t_instance->tableName(), array('noSymbols' => 1, 'noInline' => 1, 'exclude' => (int)$t_instance->getPrimaryKey(), 'table_num' => (int)$t_instance->get('table_num')));
			$vs_output .= "jQuery(document).ready(function() {";
			$vs_output .= "jQuery('#remapTo').autocomplete(
					{
						source: '".$va_service_info['search']."', html: true,
						minLength: 3, delay: 800,
						select: function(event, ui) {
							jQuery('#remapToID').val(ui.item.id);
							jQuery('#caReferenceHandlingClear').css('display', 'inline');
						}
					}
				);";
				
			$vs_output .= "jQuery('#caReferenceHandlingRemap').click(function() {
				jQuery('#remapTo').attr('disabled', false);
			});
			jQuery('#caReferenceHandlingDelete').click(function() {
				jQuery('#remapTo').attr('disabled', true);
			});
			";
			$vs_output .= "});";
			$vs_output .= "</script>\n";
			
			TooltipManager::add('#caDeleteReferenceCount', "<h2>"._t('References to this %1', $t_instance->getProperty('NAME_SINGULAR'))."</h2>\n".join("\n", $va_buf));
		}
		
		return $vs_output;
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * Returns HTML <img> tag displaying spinning "I'm doing something" icon
	 */
	function caBusyIndicatorIcon($po_request, $pa_attributes=null) {
		if (!is_array($pa_attributes)) { $pa_attributes = array(); }
		
		$vs_attr = _caHTMLMakeAttributeString($pa_attributes);
		$vs_button = "<img src='".$po_request->getThemeUrlPath()."/graphics/icons/indicator.gif' border='0' {$vs_attr}/> ";
	
		return $vs_button;
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * Formats extracted media metadata for display to user.
	 *
	 * @param $pa_metadata array - array key'ed by metadata system (eg. EXIF, DPX, IPTC) where values are arrays containing key/value metadata pairs
	 *
	 * @return string - formated metadata for display to user
	 */
	function caFormatMediaMetadata($pa_metadata) {
		$vs_buf = "<table>\n";
			
		$vn_metadata_rows = 0;
		if (is_array($pa_metadata) && sizeof($pa_metadata)) {
			foreach($pa_metadata as $vs_metadata_type => $va_metadata_data) {
				if (isset($va_metadata_data) && is_array($va_metadata_data)) {
					$vs_buf .= "<tr><th>".preg_replace('!^METADATA_!', '', $vs_metadata_type)."</th><th colspan='2'><!-- empty --></th></tr>\n";
					foreach($va_metadata_data as $vs_key => $vm_value) {
						$vs_buf .=  "<tr valign='top'><td><!-- empty --></td><td>{$vs_key}</td><td>"._caFormatMediaMetadataArray($vm_value, 0, $vs_key)."</td></tr>\n";
						$vn_metadata_rows++;
					}
				}
			}
		}
		
		if (!$vn_metadata_rows) {
			$vs_buf .=  "<tr valign='top'><td colspan='3'>"._t('No embedded metadata was extracted from the media')."</td></tr>\n";
		}
		$vs_buf .= "</table>\n";
		
		return $vs_buf;
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * Formats extracted media metadata for display to user.
	 *
	 * @param $pa_metadata array - array key'ed by metadata system (eg. EXIF, DPX, IPTC) where values are arrays containing key/value metadata pairs
	 *
	 * @return string - formated metadata for display to user
	 */
	function _caFormatMediaMetadataArray($pa_array, $pn_level=0, $ps_key=null) {
		if(!is_array($pa_array)) { return $pa_array; }
		
		$vs_buf = "<div style='width: 100%; overflow: auto;'><table style='margin-left: ".($pn_level * 10)."px;'>";
		foreach($pa_array as $vs_key => $vs_val) {
			$vs_val = preg_replace('![^A-Za-z0-9 \-_\+\!\@\#\$\%\^\&\*\(\)\[\]\{\}\?\<\>\,\.\"\'\=]+!', '', $vs_val);
			switch($vs_key) {
				case 'MakerNote':	// EXIF tags to skip output of
				case 'ImageResourceInformation':
				case 'ImageSourceData':
				case 'ICC_Profile':
					continue(2);
					break;
			}
			$vs_buf .= "<tr><td width='130'>{$vs_key}</td><td>"._caFormatMediaMetadataArray($vs_val, $pn_level + 1, $vs_key)."</td></tr>";
		}
		$vs_buf .= "</table></div>\n";
		return $vs_buf;
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * Generates next/previous/back-to-results navigation HTML for bundleable editors
	 *
	 * @param $po_request RequestHTTP The current request
	 * @param $po_instance BaseModel An instance containing the currently edited record
	 * @param $po_result_context ResultContext The current result content
	 * @param $pa_options array An optional array of options. Supported options are:
	 *		backText = a string to use as the "back" button text; default is "Results"
	 *
	 * @return string HTML implementing the navigation element
	 */
	function caEditorFindResultNavigation($po_request, $po_instance, $po_result_context, $pa_options=null) {
		$vn_item_id 			= $po_instance->getPrimaryKey();
		$vs_pk 					= $po_instance->primaryKey();
		$vs_table_name			= $po_instance->tableName();
		if (($vs_priv_table_name = $vs_table_name) == 'ca_list_items') {
			$vs_priv_table_name = 'ca_lists';
		}
		
		$va_found_ids 			= $po_result_context->getResultList();
		$vn_current_pos			= $po_result_context->getIndexInResultList($vn_item_id);
		$vn_prev_id 			= $po_result_context->getPreviousID($vn_item_id);
		$vn_next_id 			= $po_result_context->getNextID($vn_item_id);
		
		if (isset($pa_options['backText']) && $pa_options['backText']) {
			$vs_back_text = $pa_options['backText'];
		} else {
			$vs_back_text = "<span class='resultLink'>"._t('Results')."</span>";
		}
		
		$vs_buf = '';
		if (is_array($va_found_ids) && sizeof($va_found_ids)) {
			if ($vn_prev_id > 0) {
				if(
					$po_request->user->canAccess($po_request->getModulePath(),$po_request->getController(),"Edit",array($vs_pk => $vn_prev_id))
					&&
					!($po_request->getAppConfig()->get($vs_table_name.'_editor_defaults_to_summary_view'))
				){
					$vs_buf .= caNavLink($po_request, _t('&#60; prev'), 'prev', $po_request->getModulePath(), $po_request->getController(), 'Edit'.'/'.$po_request->getActionExtra(), array($vs_pk => $vn_prev_id)).'&nbsp;';
				} else {
					$vs_buf .= caNavLink($po_request, _t('&#60; prev'), 'prev', $po_request->getModulePath(), $po_request->getController(), 'Summary', array($vs_pk => $vn_prev_id)).'&nbsp;';
				}
			} else {
				$vs_buf .=  '<span class="prev disabled">'._t('&#60; prev').'</span>';
			}
				
			$vs_buf .= "<span class='resultCount'>".ResultContext::getResultsLinkForLastFind($po_request, $vs_table_name,  $vs_back_text, ''). " (".($vn_current_pos)."/".sizeof($va_found_ids).")</span>";
			
			if (!$vn_next_id && sizeof($va_found_ids)) { $vn_next_id = $va_found_ids[0]; }
			if ($vn_next_id > 0) {
				if(
					$po_request->user->canAccess($po_request->getModulePath(),$po_request->getController(),"Edit",array($vs_pk => $vn_next_id))
					&&
					!($po_request->getAppConfig()->get($vs_table_name.'_editor_defaults_to_summary_view'))
				){
					$vs_buf .= '&nbsp;'.caNavLink($po_request, _t('&#62; next'), 'next', $po_request->getModulePath(), $po_request->getController(), 'Edit'.'/'.$po_request->getActionExtra(), array($vs_pk => $vn_next_id));
				} else {
					$vs_buf .= '&nbsp;'.caNavLink($po_request, _t('&#62; next'), 'next', $po_request->getModulePath(), $po_request->getController(), 'Summary', array($vs_pk => $vn_next_id));
				}
			} else {
				$vs_buf .=  '<span class="next disabled">'._t('&#62; next').'</span>';
			}
		} elseif ($vn_item_id) {
			$vs_buf .= ResultContext::getResultsLinkForLastFind($po_request, $vs_table_name,  $vs_back_text, '');
		} 
		
		return $vs_buf;
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * 
	 *
	 * @param array $pa_bundle_list 
	 * @param array $pa_options Optional array of options. Supported options are:
	 *		NONE
	 *
	 * @return string 
	 */
	function caSetupEditorScreenOverlays($po_request, $pt_subject, $pa_bundle_list, $pa_options=null) {
		$vs_buf = '';
		if ($pt_subject && $pt_subject->isHierarchical()) {
			$vs_buf .= caEditorHierarchyOverview($po_request, $pt_subject->tableName(), $pt_subject->getPrimaryKey(), $pa_options);
		}
		$vs_buf .= caEditorFieldList($po_request, $pt_subject, $pa_bundle_list, $pa_options);	
		
		return $vs_buf;
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * 
	 *
	 * @param array $pa_bundle_list 
	 * @param array $pa_options Optional array of options. Supported options are:
	 *		NONE
	 *
	 * @return string 
	 */
	function caEditorFieldList($po_request, $pt_subject, $pa_bundle_list, $pa_options=null) {
		$vs_buf = "<script type=\"text/javascript\">
		jQuery(document).ready(function() {
			jQuery(document).bind('keydown.ctrl_f', function() {
				caHierarchyOverviewPanel.hidePanel({dontCloseMask:1});
				caEditorFieldList.onOpenCallback = function(){
					var selector = '#' + caEditorFieldList.panelID + ' a.editorFieldListLink:link';
					jQuery(selector).first().focus();
				};
				caEditorFieldList.showPanel();
			});
			jQuery('#editorFieldListContentArea').html(jQuery(\"#editorFieldListHTML\").html());
			jQuery('#editorFieldListContentArea a').click(function() {
				caEditorFieldList.hidePanel();
			});
			
			if (typeof caBundleVisibilityManager !== 'undefined') { caBundleVisibilityManager.setAll(); }
			if (typeof caBundleUpdateManager !== 'undefined') { caBundleUpdateManager = caUI.initBundleUpdateManager({url:'".caNavUrl($po_request, '*', '*', 'reload')."', screen:'".$po_request->getActionExtra()."', key:'".$pt_subject->primaryKey()."', id: ".(int)$pt_subject->getPrimaryKey()."}); }
			caBundleUpdateManager.registerBundles(".json_encode($pa_bundle_list).");
		});
</script>
<div id=\"editorFieldListHTML\">";
		if (is_array($pa_bundle_list)) { 
			foreach($pa_bundle_list as $vs_anchor => $va_info) {
				$vs_buf .= "<a href=\"#\" onclick=\"jQuery.scrollTo('a[name={$vs_anchor}]', {duration: 350, offset: -80 , onAfter : function(selector, data){jQuery(selector).parent('.bundleLabel').find('a:link').first().focus();}}); return false;\" class=\"editorFieldListLink\">".$va_info['name']."</a><br/>";
			}	
		}
		$vs_buf .= "</div>\n";
		
		return $vs_buf;
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * 
	 *
	 * @param array $pa_bundle_list 
	 * @param array $pa_options Optional array of options. Supported options are:
	 *		NONE
	 *
	 * @return string 
	 */
	function caEditorHierarchyOverview($po_request, $ps_table, $pn_id, $pa_options=null) {
		$o_dm = Datamodel::load();
		$t_subject = $o_dm->getInstanceByTableName($ps_table, true);
		$vs_buf = "<script type=\"text/javascript\">
		jQuery(document).ready(function() {
			jQuery(document).bind('keydown.ctrl_h', function() {
				caEditorFieldList.hidePanel({dontCloseMask:1});
				
				var url;
				if (jQuery('#caHierarchyOverviewContentArea').html().length == 0) {
					url = '".caNavUrl($po_request, $po_request->getModulePath(), $po_request->getController(), 'getHierarchyForDisplay', array($t_subject->primaryKey() => $pn_id))."';
				}
				caHierarchyOverviewPanel.showPanel(url, null, false);
			});
			jQuery('#caHierarchyOverviewContentArea').html('');
			jQuery('#caHierarchyOverviewContentArea a').click(function() {
				caHierarchyOverviewPanel.hidePanel();
			});
		});
</script>
\n";
		
		return $vs_buf;
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * Generates standard-format inspector panels for editors
	 *
	 * @param View $po_view Inspector view object
	 * @param array $pa_options Optional array of options. Supported options are:
	 *		backText = a string to use as the "back" button text; default is "Results"
	 *
	 * @return string HTML implementing the inspector
	 */
	function caEditorInspector($po_view, $pa_options=null) {
		require_once(__CA_MODELS_DIR__.'/ca_sets.php');
		require_once(__CA_MODELS_DIR__.'/ca_data_exporters.php');
		
		$t_item 				= $po_view->getVar('t_item'); 
		$vs_table_name = $t_item->tableName();
		if (($vs_priv_table_name = $vs_table_name) == 'ca_list_items') {
			$vs_priv_table_name = 'ca_lists';
		}
		
		$vn_item_id 			= $t_item->getPrimaryKey();
		$o_result_context		= $po_view->getVar('result_context');
		$t_ui 					= $po_view->getVar('t_ui');
		$t_type 				= method_exists($t_item, "getTypeInstance") ? $t_item->getTypeInstance() : null;
		$vs_type_name			= method_exists($t_item, "getTypeName") ? $t_item->getTypeName() : '';
		if (!$vs_type_name) { $vs_type_name = $t_item->getProperty('NAME_SINGULAR'); }
		
		$va_reps 				= $po_view->getVar('representations');
		
		
		$o_dm = Datamodel::load();
		
		if ($t_item->isHierarchical()) {
			$va_ancestors 		= $po_view->getVar('ancestors');
			$vn_parent_id		= $t_item->get($t_item->getProperty('HIERARCHY_PARENT_ID_FLD'));
		} else {
			$va_ancestors = array();
			$vn_parent_id = null;
		}

		// action extra to preserve currently open screen across next/previous links
		$vs_screen_extra 	= ($po_view->getVar('screen')) ? '/'.$po_view->getVar('screen') : '';
		if ($vs_type_name == "list item") {
			$vs_style = "style='height:auto;'";
		}
		if (($vn_item_id) | ($po_view->request->getAction() === 'Delete')) {
			$vs_buf = '<h3 class="nextPrevious" '.$vs_style.'>'.caEditorFindResultNavigation($po_view->request, $t_item, $o_result_context, $pa_options)."</h3>\n";
		}
		
		$vs_color = null;
		if ($t_type) { $vs_color = trim($t_type->get('color')); } 
		if (!$vs_color && $t_ui) { $vs_color = trim($t_ui->get('color')); }
		if (!$vs_color) { $vs_color = "FFFFFF"; }
		
		$vs_buf .= "<h4><div id='caColorbox' style='border: 6px solid #{$vs_color};'>\n";
		
		$vs_icon = null;
		if ($t_type) { $vs_icon = $t_type->getMediaTag('icon', 'icon'); }
		if (!$vs_icon && $t_ui) { $vs_icon = $t_ui->getMediaTag('icon', 'icon'); }
		
		if ($vs_icon){
			$vs_buf .= "<div id='inspectoricon' style='border-right: 6px solid #{$vs_color}; border-bottom: 6px solid #{$vs_color}; -moz-border-radius-bottomright: 8px; -webkit-border-bottom-right-radius: 8px;'>\n{$vs_icon}</div>\n";
		}
		
		if (($po_view->request->getAction() === 'Delete') && ($po_view->request->getParameter('confirm', pInteger))) { 

			$vs_buf .= "<strong>"._t("Deleted %1", $vs_type_name)."</strong>\n";
			$vs_buf .= "<br style='clear: both;'/></div></h4>\n";
		} else {	
			if ($vn_item_id) {
				if(!$po_view->request->config->get("{$vs_priv_table_name}_inspector_disable_headline")) {
					if($po_view->request->user->canDoAction("can_edit_".$vs_priv_table_name) && (sizeof($t_item->getTypeList()) > 1)){		
						$vs_buf .= "<strong>"._t("Editing %1", $vs_type_name).": </strong>\n";
					}else{
						$vs_buf .= "<strong>"._t("Viewing %1", $vs_type_name).": </strong>\n";
					}
				}
				
				if ($t_item->hasField('is_deaccessioned') && $t_item->get('is_deaccessioned') && ($t_item->get('deaccession_date', array('getDirectDate' => true)) <= caDateToHistoricTimestamp(_t('now')))) {
					// If currently deaccessioned then display deaccession message
					$vs_buf .= "<br/><div class='inspectorDeaccessioned'>"._t('Deaccessioned %1', $t_item->get('deaccession_date'))."</div>\n";
					if ($vs_deaccession_notes = $t_item->get('deaccession_notes')) { TooltipManager::add(".inspectorDeaccessioned", $vs_deaccession_notes); }
				} else {
					if ($po_view->request->user->canDoAction('can_see_current_location_in_inspector_ca_objects')) {
						if (($t_ui && method_exists($t_item, "getObjectHistory")) && (is_array($va_placements = $t_ui->getPlacementsForBundle('ca_objects_history')) && (sizeof($va_placements) > 0))) {
							//
							// Output current "location" of object in life cycle. Configuration is taken from a ca_objects_history bundle configured for the current editor
							//
							$va_placement = array_shift($va_placements);
							$va_bundle_settings = $va_placement['settings'];
							if (is_array($va_history = $t_item->getObjectHistory($va_bundle_settings, array('limit' => 1, 'currentOnly' => true))) && (sizeof($va_history) > 0)) {
								$va_current_location = array_shift(array_shift($va_history));

								if(!($vs_inspector_current_location_label = $po_view->request->config->get("ca_objects_inspector_current_location_label"))) {
									$vs_inspector_current_location_label = _t('Current');
								}
								if ($va_current_location['display']) { $vs_buf .= "<div class='inspectorCurrentLocation'><strong>".$vs_inspector_current_location_label.':</strong><br/>'.$va_current_location['display']."</div>"; }
							}
						} elseif (method_exists($t_item, "getLastLocationForDisplay")) {
							// If no ca_objects_history bundle is configured then display the last storage location
							if ($vs_current_location = $t_item->getLastLocationForDisplay("<ifdef code='ca_storage_locations.parent.preferred_labels'>^ca_storage_locations.parent.preferred_labels ➜ </ifdef>^ca_storage_locations.preferred_labels.name")) {
								$vs_buf .= "<br/><div class='inspectorCurrentLocation'>"._t('Location: %1', $vs_current_location)."</div>\n";
								$vs_full_location_hierarchy = $t_item->getLastLocationForDisplay("^ca_storage_locations.hierarchy.preferred_labels.name%delimiter=_➜_");
								if ($vs_full_location_hierarchy !== $vs_current_location) { TooltipManager::add(".inspectorCurrentLocation", $vs_full_location_hierarchy); }
							}
						}
					}
				}
				
				//
				// Display flags; expressions for these are defined in app.conf in the <table_name>_inspector_display_flags directive
				//
				if (is_array($va_display_flags = $po_view->request->config->getAssoc("{$vs_table_name}_inspector_display_flags"))) {
					$va_display_flag_buf = array();
					foreach($va_display_flags as $vs_exp => $vs_display_flag) {
						$va_exp_vars = array();
						foreach(ExpressionParser::getVariableList($vs_exp) as $vs_var_name) {
							$va_exp_vars[$vs_var_name] = $t_item->get($vs_var_name, array('returnIdno' => true));
						}
						
						if (ExpressionParser::evaluate($vs_exp, $va_exp_vars)) {
							$va_display_flag_buf[] = $t_item->getWithTemplate("{$vs_display_flag}");
						}
					}

					if(!($vs_display_flag_delim = $po_view->request->config->get("{$vs_table_name}_inspector_display_flags_delimiter"))) {
						$vs_display_flag_delim = '; ';
					}
					if (sizeof($va_display_flag_buf) > 0) { $vs_buf .= join($vs_display_flag_delim, $va_display_flag_buf); }
				}
				
				$vs_label = '';
				$vb_dont_use_labels_for_ca_objects = (bool)$t_item->getAppConfig()->get('ca_objects_dont_use_labels');
				if(!(($vs_table_name === 'ca_objects') && $vb_dont_use_labels_for_ca_objects)){
					if ($vs_get_spec = $po_view->request->config->get("{$vs_table_name}_inspector_display_title")) {
						$vs_label = caProcessTemplateForIDs($vs_get_spec, $vs_table_name, array($t_item->getPrimaryKey()));
					} else {
						$va_object_collection_collection_ancestors = $po_view->getVar('object_collection_collection_ancestors');
						if (
							($t_item->tableName() == 'ca_objects') && 
							$t_item->getAppConfig()->get('ca_objects_x_collections_hierarchy_enabled') && 
							is_array($va_object_collection_collection_ancestors) && sizeof($va_object_collection_collection_ancestors)
						) {
							$va_collection_links = array();
							foreach($va_object_collection_collection_ancestors as $va_collection_ancestor) {
								$va_collection_links[] = caEditorLink($po_view->request, $va_collection_ancestor['label'], '', 'ca_collections', $va_collection_ancestor['collection_id']);
							}
							$vs_label .= join(" / ", $va_collection_links).' &gt; ';
						}
					
						if (method_exists($t_item, 'getLabelForDisplay')) {
							$vn_parent_index = (sizeof($va_ancestors) - 1);
							if ($vn_parent_id && (($vs_table_name != 'ca_places') || ($vn_parent_index > 0))) {
								$va_parent = $va_ancestors[$vn_parent_index];
								$vs_disp_fld = $t_item->getLabelDisplayField();
							
								if ($va_parent['NODE'][$vs_disp_fld] && ($vs_editor_link = caEditorLink($po_view->request, $va_parent['NODE'][$vs_disp_fld], '', $vs_table_name, $va_parent['NODE'][$t_item->primaryKey()]))) {
									$vs_label .= $vs_editor_link.' &gt; '.$t_item->getLabelForDisplay();
								} else {
									$vs_label .= ($va_parent['NODE'][$vs_disp_fld] ? $va_parent['NODE'][$vs_disp_fld].' &gt; ' : '').$t_item->getLabelForDisplay();
								}
							} else {
								$vs_label .= $t_item->getLabelForDisplay();
								if (($vs_table_name === 'ca_editor_uis') && (in_array($po_view->request->getAction(), array('EditScreen', 'DeleteScreen', 'SaveScreen')))) {
									$t_screen = new ca_editor_ui_screens($po_view->request->getParameter('screen_id', pInteger));
									if (!($vs_screen_name = $t_screen->getLabelForDisplay())) {
										$vs_screen_name = _t('new screen');
									}
									$vs_label .= " &gt; ".$vs_screen_name;
								} 
							
							}
						} else {
							$vs_label .= $t_item->get('name');
						}
					}
				}
				
				$vb_show_idno = (bool)($vs_idno = $t_item->get($t_item->getProperty('ID_NUMBERING_ID_FIELD')));
				
				if (!$vs_label) { 
					switch($vs_table_name) {
						default:
							if (($vs_table_name === 'ca_objects') && $vb_dont_use_labels_for_ca_objects) {
								$vs_label = $vs_idno;
								$vb_show_idno = false;
							} else {
								$vs_label =  '['._t('BLANK').']'; 
							}
							break;
					}
				}
			
				
				$vs_buf .= "<div class='recordTitle {$vs_table_name}' style='width:190px; overflow:hidden;'>{$vs_label}".(($vb_show_idno) ? "<a title='$vs_idno'>".($vs_idno ? " ({$vs_idno})" : '') : "")."</a></div>";
				if (($vs_table_name === 'ca_object_lots') && $t_item->getPrimaryKey()) {
					$vs_buf .= "<div id='inspectorLotMediaDownload'><strong>".((($vn_num_objects = $t_item->numObjects()) == 1) ? _t('Lot contains %1 object', $vn_num_objects) : _t('Lot contains %1 objects', $vn_num_objects))."</strong>\n";
				}
				if ($po_view->request->config->get("include_custom_inspector")) {
					if(file_exists($po_view->request->getViewsDirectoryPath()."/bundles/inspector_info.php")) {
						$vo_inspector_view = new View($po_view->request, $po_view->request->getViewsDirectoryPath()."/bundles/");
						$vo_inspector_view->setVar('t_item', $t_item);
						$vs_buf .= $vo_inspector_view->render('inspector_info.php');
					}
				}
			} else {
				$vs_parent_name = '';
				if ($vn_parent_id = $po_view->request->getParameter('parent_id', pInteger)) {
					$t_parent = clone $t_item;
					$t_parent->load($vn_parent_id);
					$vs_parent_name = $t_parent->getLabelForDisplay();
				}
				$vs_buf .= "<div class='creatingNew'>"._t("Creating new %1", $vs_type_name)." ".($vs_parent_name ?  _t("%1 &gt; New %2", $vs_parent_name, $vs_type_name) : '')."</div>\n";
				$vs_buf .= "<br/>\n";
			}
			
		// -------------------------------------------------------------------------------------
	
		if($t_item->getPrimaryKey()) {
			if (sizeof($va_reps) > 0) {	
				$va_imgs = array();
				
				$vs_buf .= "<div id='inspectorMedia'>";
			
				$vn_r = $vn_primary_index = 0;
				foreach($va_reps as $va_rep) {
					if (!($va_rep['info']['preview170']['WIDTH'] && $va_rep['info']['preview170']['HEIGHT'])) { continue; }
				
					if ($vb_is_primary = (isset($va_rep['is_primary']) && (bool)$va_rep['is_primary'])) {
						$vn_primary_index = $vn_r;
					}
					
					$va_imgs[] = "{url:'".$va_rep['urls']['preview170']."', width: ".$va_rep['info']['preview170']['WIDTH'].", height: ".
					$va_rep['info']['preview170']['HEIGHT'].", link: '#', onclick:  'caMediaPanel.showPanel(\'".
					caNavUrl($po_view->request, '*', '*', 'GetMediaOverlay', array($t_item->primaryKey() => $vn_item_id, 'representation_id' => $va_rep['representation_id']))."\')'}";
					
					$vn_r++;
				}

					if (sizeof($va_reps) > 1) {
						$vs_buf .= "
					<div class='leftScroll'>
						<a href='#' onclick='inspectorInfoRepScroller.scrollToPreviousImage(); return false;'>".caNavIcon($po_view->request, __CA_NAV_BUTTON_SCROLL_LT__)."</a>
					</div>
		";
					}
									
				if (sizeof($va_imgs) > 0) {
					$vs_buf .= "
				<div id='inspectorInfoRepScrollingViewer' style='position: relative;'>
					<div id='inspectorInfoRepScrollingViewerContainer'>
						<div id='inspectorInfoRepScrollingViewerImageContainer'></div>
					</div>
				</div>
		";
					if (sizeof($va_reps) > 1) {
						$vs_buf .= "
					<div class='rightScroll'>
						<a href='#' onclick='inspectorInfoRepScroller.scrollToNextImage(); return false;'>".caNavIcon($po_view->request, __CA_NAV_BUTTON_SCROLL_RT__)."</a>
					</div>
		";
					}
					TooltipManager::add(".leftScroll", _t('Previous'));
					TooltipManager::add(".rightScroll", _t('Next'));

					$vs_buf .= "<script type='text/javascript'>";
					$vs_buf .= "
					var inspectorInfoRepScroller = caUI.initImageScroller([".join(",", $va_imgs)."], 'inspectorInfoRepScrollingViewerImageContainer', {
							containerWidth: 170, containerHeight: 170,
							imageCounterID: 'inspectorInfoRepScrollingViewerCounter',
							scrollingImageClass: 'inspectorInfoRepScrollerImage',
							scrollingImagePrefixID: 'inspectorInfoRep',
							initialIndex: {$vn_primary_index}
							
					});
				</script>";
				
				}
				$vs_buf .= "</div>\n";
				
				if ($vs_get_spec = $po_view->request->config->get("{$vs_table_name}_inspector_display_below_media")) {
					$vs_buf .= caProcessTemplateForIDs($vs_get_spec, $vs_table_name, array($t_item->getPrimaryKey()));
				}
			}

			//
			// Output configurable additional info from config, if set
			//

			if ($vs_additional_info = $po_view->request->config->get("{$vs_table_name}_inspector_additional_info")) {
				if(is_array($vs_additional_info)){
					$vs_buf .= "<br/>";
					foreach($vs_additional_info as $vs_info){
						$vs_buf .= caProcessTemplateForIDs($vs_info, $vs_table_name, array($t_item->getPrimaryKey()),array('requireLinkTags' => true))."<br/>\n";
					}
				} else {
					$vs_buf .= "<br/>".caProcessTemplateForIDs($vs_additional_info, $vs_table_name, array($t_item->getPrimaryKey()),array('requireLinkTags' => true))."<br/>\n";
				}
			}
			
			$vs_buf .= "<div id='toolIcons'>";	
			
			if ($vn_item_id) {
				# --- watch this link
				$vs_watch = "";
				if (in_array($vs_table_name, array('ca_objects', 'ca_object_lots', 'ca_entities', 'ca_places', 'ca_occurrences', 'ca_collections', 'ca_storage_locations'))) {
					require_once(__CA_MODELS_DIR__.'/ca_watch_list.php');
					$t_watch_list = new ca_watch_list();
					$vs_watch = "<div class='watchThis'><a href='#' title='"._t('Add/remove item to/from watch list.')."' onclick='caToggleItemWatch(); return false;' id='caWatchItemButton'>".caNavIcon($po_view->request, $t_watch_list->isItemWatched($vn_item_id, $t_item->tableNum(), $po_view->request->user->get("user_id")) ? __CA_NAV_BUTTON_UNWATCH__ : __CA_NAV_BUTTON_WATCH__)."</a></div>";
					
					$vs_buf .= "\n<script type='text/javascript'>
		function caToggleItemWatch() {
			var url = '".caNavUrl($po_view->request, $po_view->request->getModulePath(), $po_view->request->getController(), 'toggleWatch', array($t_item->primaryKey() => $vn_item_id))."';
			
			jQuery.getJSON(url, {}, function(data, status) {
				if (data['status'] == 'ok') {
					jQuery('#caWatchItemButton').html((data['state'] == 'watched') ? '".addslashes(caNavIcon($po_view->request, __CA_NAV_BUTTON_UNWATCH__))."' : '".addslashes(caNavIcon($po_view->request, __CA_NAV_BUTTON_WATCH__))."');
				} else {
					console.log('Error toggling watch status for item: ' + data['errors']);
				}
			});
		}
		</script>\n";
				}		

					$vs_buf .= "{$vs_watch}\n";
					TooltipManager::add("#caWatchItemButton", _t('Watch/Unwatch this record'));

					if ($po_view->request->user->canDoAction("can_change_type_{$vs_table_name}")) {
						
						$vs_buf .= "<div id='inspectorChangeType'><div id='inspectorChangeTypeButton'><a href='#' onclick='caTypeChangePanel.showPanel(); return false;'>".caNavIcon($po_view->request, __CA_NAV_BUTTON_CHANGE__." Change Type", array('title' => _t('Change type')))."</a></div></div>\n";
						
						$vo_change_type_view = new View($po_view->request, $po_view->request->getViewsDirectoryPath()."/bundles/");
						$vo_change_type_view->setVar('t_item', $t_item);
						
						FooterManager::add($vo_change_type_view->render("change_type_html.php"));
						TooltipManager::add("#inspectorChangeType", _t('Change Record Type'));
					}
					
					if ($t_item->getPrimaryKey() && $po_view->request->config->get($vs_table_name.'_show_add_child_control_in_inspector')) {
						$vb_show_add_child_control = true;
						if (is_array($va_restrict_add_child_control_to_types = $po_view->request->config->getList($vs_table_name.'_restrict_child_control_in_inspector_to_types')) && sizeof($va_restrict_add_child_control_to_types)) {
							$t_type_instance = $t_item->getTypeInstance();
							if (!in_array($t_type_instance->get('idno'), $va_restrict_add_child_control_to_types) && !in_array($t_type_instance->getPrimaryKey(), $va_restrict_add_child_control_to_types)) {
								$vb_show_add_child_control = false;
							}
						}
						if ($vb_show_add_child_control) {
							if ((bool)$po_view->request->config->get($vs_table_name.'_enforce_strict_type_hierarchy')) {
								// strict menu
								$vs_type_list = $t_item->getTypeListAsHTMLFormElement('type_id', array('style' => 'width: 90px; font-size: 9px;'), array('childrenOfCurrentTypeOnly' => true, 'directChildrenOnly' => ($po_view->request->config->get($vs_table_name.'_enforce_strict_type_hierarchy') == '~') ? false : true, 'returnHierarchyLevels' => true, 'access' => __CA_BUNDLE_ACCESS_EDIT__));
							} else {
								// all types
								$vs_type_list = $t_item->getTypeListAsHTMLFormElement('type_id', array('style' => 'width: 90px; font-size: 9px;'), array('access' => __CA_BUNDLE_ACCESS_EDIT__));
							}
							
							if ($vs_type_list) {
								$vs_buf .= "<div id='inspectorCreateChild'><div id='inspectorCreateChildButton'><a href='#' onclick='caCreateChildPanel.showPanel(); return false;'>".caNavIcon($po_view->request, __CA_NAV_BUTTON_CHILD__, array('title' => _t('Create Child Record')))."</a></div></div>\n";
						
								$vo_create_child_view = new View($po_view->request, $po_view->request->getViewsDirectoryPath()."/bundles/");
								$vo_create_child_view->setVar('t_item', $t_item);
								$vo_create_child_view->setVar('type_list', $vs_type_list);
						
								FooterManager::add($vo_create_child_view->render("create_child_html.php"));
								TooltipManager::add("#inspectorCreateChildButton", _t('Create a child record under this one'));
							}
						}
					}
			}
			
			if($po_view->request->user->canDoAction('can_duplicate_'.$vs_table_name) && $t_item->getPrimaryKey()) {
				$vs_buf .= '<div id="caDuplicateItemButton">';
			
				$vs_buf .= caFormTag($po_view->request, 'Edit', 'DuplicateItemForm', $po_view->request->getModulePath().'/'.$po_view->request->getController(), 'post', 'multipart/form-data', '_top', array('disableUnsavedChangesWarning' => true, 'noTimestamp' => true));
				$vs_buf .= caFormSubmitLink($po_view->request, caNavIcon($po_view->request, __CA_NAV_BUTTON_DUPLICATE__), '', 'DuplicateItemForm');
				
				$vs_buf .= caHTMLHiddenInput($t_item->primaryKey(), array('value' => $t_item->getPrimaryKey()));
				$vs_buf .= caHTMLHiddenInput('mode', array('value' => 'dupe'));
			
				$vs_buf .= "</form>";
				$vs_buf .= "</div>";
			
				TooltipManager::add("#caDuplicateItemButton", _t('Duplicate this %1', mb_strtolower($vs_type_name, 'UTF-8')));
			}

			//
			// Download media in lot ($vn_num_objects is only set for object lots)
			if ($vn_num_objects > 0) {
				$vs_buf .= "<div id='inspectorLotMediaDownloadButton'>".caNavLink($po_view->request, caNavIcon($po_view->request, __CA_NAV_BUTTON_DOWNLOAD__), "button", $po_view->request->getModulePath(), $po_view->request->getController(), 'getLotMedia', array('lot_id' => $t_item->getPrimaryKey(), 'download' => 1), array())."</div>\n";
				TooltipManager::add('#inspectorLotMediaDownloadButton', _t("Download all media associated with objects in this lot"));
			}

			//
			// Download media in set
			if(($vs_table_name == 'ca_sets') && (sizeof($t_item->getItemRowIDs())>0)) {
				$vs_buf .= "<div id='inspectorSetMediaDownloadButton'>".caNavLink($po_view->request, caNavIcon($po_view->request, __CA_NAV_BUTTON_DOWNLOAD__), "button", $po_view->request->getModulePath(), $po_view->request->getController(), 'getSetMedia', array('set_id' => $t_item->getPrimaryKey(), 'download' => 1), array())."</div>\n";

				TooltipManager::add('#inspectorSetMediaDownloadButton', _t("Download all media associated with records in this set"));
			}
		
			$vs_more_info = '';
			
			// list of sets in which item is a member
			$t_set = new ca_sets();
			if (is_array($va_sets = caExtractValuesByUserLocale($t_set->getSetsForItem($t_item->tableNum(), $t_item->getPrimaryKey(), array('user_id' => $po_view->request->getUserID(), 'access' => __CA_SET_READ_ACCESS__)))) && sizeof($va_sets)) {
				$va_links = array();
				foreach($va_sets as $vn_set_id => $va_set) {
					$va_links[] = "<a href='".caEditorUrl($po_view->request, 'ca_sets', $vn_set_id)."'>".$va_set['name']."</a>";
				}
				$vs_more_info .= "<div><strong>".((sizeof($va_links) == 1) ? _t("In set") : _t("In sets"))."</strong> ".join(", ", $va_links)."</div>\n";
			}
			
			
			// export options		
			if ($vn_item_id && $vs_select = $po_view->getVar('available_mappings_as_html_select')) {
				$vs_more_info .= "<div class='inspectorExportControls'>".caFormTag($po_view->request, 'exportItem', 'caExportForm', null, 'post', 'multipart/form-data', '_top', array('disableUnsavedChangesWarning' => true));
				$vs_more_info .= $vs_select;
				$vs_more_info .= caHTMLHiddenInput($t_item->primaryKey(), array('value' => $t_item->getPrimaryKey()));
				$vs_more_info .= caHTMLHiddenInput('download', array('value' => 1));
				$vs_more_info .= caFormSubmitLink($po_view->request, 'Export &rsaquo;', 'button', 'caExportForm');
				$vs_more_info .= "</form></div>";
			}
			
			
			$va_creation = $t_item->getCreationTimestamp();
			$va_last_change = $t_item->getLastChangeTimestamp();
			
			if ($va_creation['timestamp'] || $va_last_change['timestamp']) {
				$vs_more_info .= "<div class='inspectorChangeDateList'>";
				
				if($va_creation['timestamp']) {
					if (!trim($vs_name = $va_creation['fname'].' '.$va_creation['lname'])) { $vs_name = null; }
					$vs_interval = (($vn_t = (time() - $va_creation['timestamp'])) == 0) ? _t('Just now') : _t('%1 ago', caFormatInterval($vn_t , 2));
					
					$vs_more_info .= "<div class='inspectorChangeDateListLine'  id='caInspectorCreationDate'>".
						($vs_name ? _t('<strong>Created</strong><br/>%1 by %2', $vs_interval, $vs_name) : _t('<strong>Created</strong><br/>%1', $vs_interval)).
						"</div>";
					
					TooltipManager::add("#caInspectorCreationDate", "<h2>"._t('Created on')."</h2>"._t('Created on %1', caGetLocalizedDate($va_creation['timestamp'], array('dateFormat' => 'delimited'))));
				}
				
				if ($va_last_change['timestamp'] && ($va_creation['timestamp'] != $va_last_change['timestamp'])) {
					if (!trim($vs_name = $va_last_change['fname'].' '.$va_last_change['lname'])) { $vs_name = null; }
					$vs_interval = (($vn_t = (time() - $va_last_change['timestamp'])) == 0) ? _t('Just now') : _t('%1 ago', caFormatInterval($vn_t , 2));
					
					$vs_more_info .= "<div class='inspectorChangeDateListLine' id='caInspectorChangeDate'>".
						($vs_name ? _t('<strong>Last changed</strong><br/>%1 by %2', $vs_interval, $vs_name) : _t('<strong>Last changed</strong><br/>%1', $vs_interval)).
						"</div>";
					
					TooltipManager::add("#caInspectorChangeDate", "<h2>"._t('Last changed on')."</h2>"._t('Last changed on %1', caGetLocalizedDate($va_last_change['timestamp'], array('dateFormat' => 'delimited'))));
				}
				
				if (method_exists($t_item, 'getMetadataDictionaryRuleViolations') && is_array($va_violations = $t_item->getMetadataDictionaryRuleViolations()) && (($vn_num_violations = (sizeof($va_violations))) > 0)) {
					$va_violation_messages = array();
					foreach($va_violations as $vn_violation_id => $va_violation) {
						$vs_label = $t_item->getDisplayLabel($va_violation['bundle_name']);
						$va_violation_messages[] = "<li><em><u>{$vs_label}</u></em> ".$va_violation['violationMessage']."</li>";
					}
					
					$vs_more_info .= "<div id='caInspectorViolationsList'>".($vs_num_violations_display = "<img src='".$po_view->request->getThemeUrlPath()."/graphics/icons/warning_small.gif' border='0'/> ".(($vn_num_violations > 1) ? _t('%1 problems require attention', $vn_num_violations) : _t('%1 problem requires attention', $vn_num_violations)))."</div>\n"; 
					TooltipManager::add("#caInspectorViolationsList", "<h2>{$vs_num_violations_display}</h2><ol>".join("\n", $va_violation_messages))."</ol>\n";
				}
				
				$vs_more_info .= "</div>\n";
			}
			
			if ($vs_get_spec = $po_view->request->config->get("{$vs_table_name}_inspector_display_more_info")) {
				$vs_more_info .= caProcessTemplateForIDs($vs_get_spec, $vs_table_name, array($t_item->getPrimaryKey()));
			}
			if ($vs_more_info) {
				$vs_buf .= "<div class='button info'><a href='#' id='inspectorMoreInfo'>".caNavIcon($po_view->request, __CA_NAV_BUTTON_INFO2__)."</a></div>
			<div id='inspectorInfo' >";
				$vs_buf .= $vs_more_info."</div>\n";
				
				TooltipManager::add("#inspectorMoreInfo", _t('See more information about this record'));

			}
			
			$vs_buf .= "</div><!--End tooIcons-->";
		}
	
		// -------------------------------------------------------------------------------------
		//
		// Item-specific information
		//
			//
			// Output info for related items
			//
			if(!$t_item->getPrimaryKey()) { // only applies to new records
				$vs_rel_table = $po_view->request->getParameter('rel_table', pString);
				$vn_rel_type_id = $po_view->request->getParameter('rel_type_id', pString);
				$vn_rel_id = $po_view->request->getParameter('rel_id', pInteger);
				if($vs_rel_table && $po_view->request->datamodel->tableExists($vs_rel_table) && $vn_rel_type_id && $vn_rel_id) {
					$t_rel = $po_view->request->datamodel->getTableInstance($vs_rel_table);
					if($t_rel && $t_rel->load($vn_rel_id)){
						$vs_buf .= '<strong>'._t("Will be related to %1", $t_rel->getTypeName()).'</strong>: '.$t_rel->getLabelForDisplay();
					}
				}
			}

			//
			// Output lot info for ca_objects
			//
			$vb_is_currently_part_of_lot = true;
			if (!($vn_lot_id = $t_item->get('lot_id'))) {
				$vn_lot_id = $po_view->request->getParameter('lot_id', pInteger);
				$vb_is_currently_part_of_lot = false;
			}
			
			if (($vs_table_name === 'ca_objects') && ($vn_lot_id)) {
				require_once(__CA_MODELS_DIR__.'/ca_object_lots.php');
			
				$va_lot_lots = caGetTypeListForUser('ca_object_lots', array('access' => __CA_BUNDLE_ACCESS_READONLY__));	
				$t_lot = new ca_object_lots($vn_lot_id);
				if (($t_lot->get('deleted') == 0) && (in_array($t_lot->get('type_id'), $va_lot_lots))) {
					if(!($vs_lot_displayname = $t_lot->get('idno_stub'))) {
						if((!$vs_lot_displayname = $t_lot->getLabelForDisplay())){
							$vs_lot_displayname = "Lot {$vn_lot_id}";		
						}
					}
					if ($vs_lot_displayname) {
						if(!($vs_part_of_lot_msg = $po_view->request->config->get("ca_objects_inspector_part_of_lot_msg"))){
							$vs_part_of_lot_msg = _t('Part of lot');
						}
						if(!($vs_will_be_part_of_lot_msg = $po_view->request->config->get("ca_objects_inspector_will_be_part_of_lot_msg"))){
							$vs_will_be_part_of_lot_msg = _t('Will be part of lot');
						}
						$vs_buf .= "<strong>".($vb_is_currently_part_of_lot ? $vs_part_of_lot_msg : $vs_will_be_part_of_lot_msg)."</strong>: " . caNavLink($po_view->request, $vs_lot_displayname, '', 'editor/object_lots', 'ObjectLotEditor', 'Edit', array('lot_id' => $vn_lot_id));
					}
				}
			}
			
			$va_object_container_types = $po_view->request->config->getList('ca_objects_container_types');
			$va_object_component_types = $po_view->request->config->getList('ca_objects_component_types');
			$vb_can_add_component = (($vs_table_name === 'ca_objects') && $t_item->getPrimaryKey() && ($po_view->request->user->canDoAction('can_create_ca_objects')) && $t_item->canTakeComponents());
	
			if (method_exists($t_item, 'getComponentCount')) {
				if ($vn_component_count = $t_item->getComponentCount()) {
					if ($t_ui && ($vs_component_list_screen = $t_ui->getScreenWithBundle("ca_objects_components_list", $po_view->request)) && ($vs_component_list_screen !== $po_view->request->getActionExtra())) { 
						$vs_component_count_link = caNavLink($po_view->request, (($vn_component_count == 1) ? _t('%1 component', $vn_component_count) : _t('%1 components', $vn_component_count)), '', '*', '*', $po_view->request->getAction().'/'.$vs_component_list_screen, array($t_item->primaryKey() => $t_item->getPrimaryKey()));
					} else {
						$vs_component_count_link = (($vn_component_count == 1) ? _t('%1 component', $vn_component_count) : _t('%1 components', $vn_component_count));
					}
					$vs_buf .= "<br/><strong>"._t('Has').":</strong> {$vs_component_count_link}";
				}
			}
								
			if ($vb_can_add_component) {
				$vs_buf .= ' <a href="#" onclick=\'caObjectComponentPanel.showPanel("'.caNavUrl($po_view->request, '*', 'ObjectComponent', 'Form', array('parent_id' => $t_item->getPrimaryKey())).'"); return false;\')>'.caNavIcon($po_view->request, __CA_NAV_BUTTON_ADD__).'</a>';

				$vo_change_type_view = new View($po_view->request, $po_view->request->getViewsDirectoryPath()."/bundles/");
				$vo_change_type_view->setVar('t_item', $t_item);

				FooterManager::add($vo_change_type_view->render("create_component_html.php"));
			}
			
			//
			// Output lot info for ca_object_lots
			//
			if (($vs_table_name === 'ca_object_lots') && $t_item->getPrimaryKey()) {
				$va_component_types = $po_view->request->config->getList('ca_objects_component_types');
				if (is_array($va_component_types) && sizeof($va_component_types)) {
					$vs_buf .= "<strong>".((($vn_num_objects = $t_item->numObjects(null, array('return' => 'objects'))) == 1) ? _t('Lot contains %1 object', $vn_num_objects) : _t('Lot contains %1 objects', $vn_num_objects))."</strong>\n";
					$vs_buf .= "<strong>".((($vn_num_components = $t_item->numObjects(null, array('return' => 'components'))) == 1) ? _t('Lot contains %1 component', $vn_num_components) : _t('Lot contains %1 components', $vn_num_components))."</strong>\n";
				} else {
					$vs_buf .= "<strong>".((($vn_num_objects = $t_item->numObjects()) == 1) ? _t('Lot contains %1 object', $vn_num_objects) : _t('Lot contains %1 objects', $vn_num_objects))."</strong>\n";
				}

				if (((bool)$po_view->request->config->get('allow_automated_renumbering_of_objects_in_a_lot')) && ($va_nonconforming_objects = $t_item->getObjectsWithNonConformingIdnos())) {
				
					$vs_buf .= '<br/><br/><em>'. ((($vn_c = sizeof($va_nonconforming_objects)) == 1) ? _t('There is %1 object with non-conforming numbering', $vn_c) : _t('There are %1 objects with non-conforming numbering', $vn_c))."</em>\n";
					
					$vs_buf .= "<a href='#' onclick='jQuery(\"#inspectorNonConformingNumberList\").toggle(250); return false;'>".caNavIcon($po_view->request, __CA_NAV_BUTTON_ADD__);
					
					$vs_buf .= "<div id='inspectorNonConformingNumberList' class='inspectorNonConformingNumberList'><div class='inspectorNonConformingNumberListScroll'><ol>\n";
					foreach($va_nonconforming_objects as $vn_object_id => $va_object_info) {
						$vs_buf .= '<li>'.caEditorLink($po_view->request, $va_object_info['idno'], '', 'ca_objects', $vn_object_id)."</li>\n";
					}
					$vs_buf .= "</ol></div>";
					$vs_buf .= caNavLink($po_view->request, _t('Re-number objects').' &rsaquo;', 'button', $po_view->request->getModulePath(), $po_view->request->getController(), 'renumberObjects', array('lot_id' => $t_item->getPrimaryKey()));
					$vs_buf .= "</div>\n";
				}
			
				require_once(__CA_MODELS_DIR__.'/ca_objects.php');
				$t_object = new ca_objects();
				
				$vs_buf .= "<div class='inspectorLotObjectTypeControls'><form action='#' id='caAddObjectToLotForm'>";
				if ((bool)$po_view->request->config->get('ca_objects_enforce_strict_type_hierarchy')) {
					// strict menu
					$vs_buf .= _t('Add new %1 to lot', $t_object->getTypeListAsHTMLFormElement('type_id', array('id' => 'caAddObjectToLotForm_type_id'), array('childrenOfCurrentTypeOnly' => true, 'directChildrenOnly' => ($po_view->request->config->get('ca_objects_enforce_strict_type_hierarchy') == '~') ? false : true, 'returnHierarchyLevels' => true, 'access' => __CA_BUNDLE_ACCESS_EDIT__)));
				} else {
					// all types
					$vs_buf .= _t('Add new %1 to lot', $t_object->getTypeListAsHTMLFormElement('type_id', array('id' => 'caAddObjectToLotForm_type_id'), array('access' => __CA_BUNDLE_ACCESS_EDIT__)));
				}
				
				$vs_buf .= " <a href='#' onclick='caAddObjectToLotForm()'>".caNavIcon($po_view->request, __CA_NAV_BUTTON_ADD__).'</a>';
				$vs_buf .= "</form></div>\n";
				
				$vs_buf .= "<script type='text/javascript'>
	function caAddObjectToLotForm() { 
		window.location='".caEditorUrl($po_view->request, 'ca_objects', 0, false, array('lot_id' => $t_item->getPrimaryKey(), 'rel' => 1, 'type_id' => ''))."' + jQuery('#caAddObjectToLotForm_type_id').val();
	}
	jQuery(document).ready(function() {
		jQuery('#objectLotsNonConformingNumberList').hide();
	});
</script>\n";	
			}	
			
			//
			// Output related objects for ca_object_representations
			//
			if ($vs_table_name === 'ca_object_representations') {
				foreach(array('ca_objects', 'ca_object_lots', 'ca_entities', 'ca_places', 'ca_occurrences', 'ca_collections', 'ca_storage_locations', 'ca_loans', 'ca_movements') as $vs_rel_table) {
					if (sizeof($va_objects = $t_item->getRelatedItems($vs_rel_table))) {
						$vs_buf .= "<div><strong>"._t("Related %1", $o_dm->getTableProperty($vs_rel_table, 'NAME_PLURAL'))."</strong>: <br/>\n";
						
						$vs_screen = '';
						if ($t_ui = ca_editor_uis::loadDefaultUI($vs_rel_table, $po_view->request, null)) {
							$vs_screen = $t_ui->getScreenWithBundle('ca_object_representations', $po_view->request);
						}
						foreach($va_objects as $vn_rel_id => $va_rel_info) {
							if ($vs_label = array_shift($va_rel_info['labels'])) {
								$vs_buf .= caEditorLink($po_view->request, '&larr; '.$vs_label.' ('.$va_rel_info['idno'].')', '', $vs_rel_table, $va_rel_info[$o_dm->getTablePrimaryKeyName($vs_rel_table)], array(), array(), array('action' => 'Edit'.($vs_screen ? "/{$vs_screen}" : "")))."<br/>\n";
							}
						}
						$vs_buf .= "</div>\n";
					}
				}
			}
			
			//
			// Output related object reprsentation for ca_representation_annotation
			//
			if ($vs_table_name === 'ca_representation_annotations') {
				if ($vn_representation_id = $t_item->get('representation_id')) {
					$vs_buf .= "<div><strong>"._t("Applied to representation")."</strong>: <br/>\n";
					$t_rep = new ca_object_representations($vn_representation_id);
					$vs_buf .= caNavLink($po_view->request, '&larr; '.$t_rep->getLabelForDisplay(), '', 'editor/object_representations', 'ObjectRepresentationEditor', 'Edit/'.$po_view->getVar('representation_editor_screen'), array('representation_id' => $vn_representation_id)).'<br/>';
					
					$vs_buf .= "</div>\n";
				}
			}
			
			//
			// Output extra useful info for sets
			//
			if ($vs_table_name === 'ca_sets') {
				
				$vn_set_item_count = $t_item->getItemCount(array('user_id' => $po_view->request->getUserID()));
				
				if (($vn_set_item_count > 0) && ($po_view->request->user->canDoAction('can_batch_edit_'.$o_dm->getTableName($t_item->get('table_num'))))) {
					$vs_buf .= caNavButton($po_view->request, __CA_NAV_BUTTON_BATCH_EDIT__, _t('Batch edit'), 'editorBatchSetEditorLink', 'batch', 'Editor', 'Edit', array('set_id' => $t_item->getPrimaryKey()), array(), array('icon_position' => __CA_NAV_BUTTON_ICON_POS_LEFT__, 'no_background' => true, 'dont_show_content' => true));
				}
				
				$vs_buf .= "<div><strong>"._t("Number of items")."</strong>: {$vn_set_item_count}<br/>\n";
					
				if ($t_item->getPrimaryKey()) {
					
					$vn_set_table_num = $t_item->get('table_num');
					$vs_set_table_name = $o_dm->getTableName($vn_set_table_num);
					$vs_buf .= "<strong>"._t("Type of content")."</strong>: ".caGetTableDisplayName($vn_set_table_num)."<br/>\n";
					
					$vs_buf .= "</div>\n";

					if($po_view->request->user->canDoAction('can_duplicate_' . $vs_set_table_name)) {
						$vs_buf .= '<div style="border-top: 1px solid #aaaaaa; margin-top: 5px; font-size: 10px; text-align: right;" ></div>';
						$vs_buf .= caFormTag($po_view->request, 'DuplicateItems', 'caDupeSetItemsForm', 'manage/sets/SetEditor', 'post', 'multipart/form-data', '_top', array('disableUnsavedChangesWarning' => true));
						$vs_buf .= _t("Duplicate items in this set and add to") . " ";
						$vs_buf .= caHTMLSelect('setForDupes', array(
							_t('current set') => 'current',
							_t('new set') => 'new',
						));
						$vs_buf .= caHTMLHiddenInput('set_id', array('value' => $t_item->getPrimaryKey()));
						$vs_buf .= caFormSubmitLink($po_view->request, _t('Go') . " &rsaquo;", "button", "caDupeSetItemsForm");
						$vs_buf .= "</form>";
						$vs_buf .= '<div style="border-top: 1px solid #aaaaaa; margin-top: 5px; font-size: 10px; text-align: right;" ></div>';
					}
				} else {
					if ($vn_set_table_num = $po_view->request->getParameter('table_num', pInteger)) {
						$vs_buf .= "<div><strong>"._t("Type of content")."</strong>: ".caGetTableDisplayName($vn_set_table_num)."<br/>\n";
					
						$vs_buf .= "</div>\n";
					}
				}
				$t_user = new ca_users(($vn_user_id = $t_item->get('user_id')) ? $vn_user_id : $po_view->request->getUserID());
				if ($t_user->getPrimaryKey()) {
					$vs_buf .= "<div><strong>"._t('Owner')."</strong>: ".$t_user->get('fname').' '.$t_user->get('lname')."</div>\n";
				}

				if($po_view->request->user->canDoAction('can_export_'.$vs_set_table_name) && $t_item->getPrimaryKey() && (sizeof(ca_data_exporters::getExporters($vn_set_table_num))>0)) {
					$vs_buf .= '<div style="border-top: 1px solid #aaaaaa; margin-top: 5px; font-size: 10px; text-align: right;" id="caExportItemButton">';

					$vs_buf .= _t('Export this set of records')."&nbsp; ";
					$vs_buf .= "<a class='button' onclick='jQuery(\"#exporterFormList\").show();' style='text-align:right;' href='#'>".caNavIcon($po_view->request, __CA_NAV_BUTTON_ADD__)."</a>";

					$vs_buf .= caFormTag($po_view->request, 'ExportData', 'caExportForm', 'manage/MetadataExport', 'post', 'multipart/form-data', '_top', array('disableUnsavedChangesWarning' => true));
					$vs_buf .= "<div id='exporterFormList'>";
					$vs_buf .= ca_data_exporters::getExporterListAsHTMLFormElement('exporter_id', $vn_set_table_num, array('id' => 'caExporterList'),array('width' => '135px'));
					$vs_buf .= caHTMLHiddenInput('set_id', array('value' => $t_item->getPrimaryKey()));
					$vs_buf .= caFormSubmitLink($po_view->request, _t('Export')." &rsaquo;", "button", "caExportForm");
					$vs_buf .= "</div>\n";
					$vs_buf .= "</form>";

					$vs_buf .= "</div>";

					$vs_buf .= "<script type='text/javascript'>";
					$vs_buf .= "jQuery(document).ready(function() {";
					$vs_buf .= "jQuery(\"#exporterFormList\").hide();";
					$vs_buf .= "});";
					$vs_buf .= "</script>";
				}
			}
			
			//
			// Output extra useful info for set items
			//
			if ($vs_table_name === 'ca_set_items') {
				AssetLoadManager::register("panel");
				$t_set = new ca_sets();
				if ($t_set->load($vn_set_id = $t_item->get('set_id'))) {
					$vs_buf .= "<div><strong>"._t("Part of set")."</strong>: ".caEditorLink($po_view->request, $t_set->getLabelForDisplay(), '', 'ca_sets', $vn_set_id)."<br/>\n";
					
					$t_content_instance = $t_item->getAppDatamodel()->getInstanceByTableNum($vn_item_table_num = $t_item->get('table_num'));
					if ($t_content_instance->load($vn_row_id = $t_item->get('row_id'))) {
						$vs_label = $t_content_instance->getLabelForDisplay();
						if ($vs_id_fld = $t_content_instance->getProperty('ID_NUMBERING_ID_FIELD')) {
							$vs_label .= " (".$t_content_instance->get($vs_id_fld).")";
						}	
						$vs_buf .= "<strong>"._t("Is %1", caGetTableDisplayName($vn_item_table_num, false)."</strong>: ".caEditorLink($po_view->request, $vs_label, '', $vn_item_table_num, $vn_row_id))."<br/>\n";
					}
					
					$vs_buf .= "</div>\n";
				}
			}
			
			//
			// Output extra useful info for lists
			// 
			if (($vs_table_name === 'ca_lists') && $t_item->getPrimaryKey()) {
				$vs_buf .= "<strong>"._t("Number of items")."</strong>: ".$t_item->numItemsInList()."<br/>\n";
					$t_list_item = new ca_list_items();
					$t_list_item->load(array('list_id' => $t_item->getPrimaryKey(), 'parent_id' => null));
					$vs_type_list = $t_list_item->getTypeListAsHTMLFormElement('type_id', array('style' => 'width: 90px; font-size: 9px;'), array('access' => __CA_BUNDLE_ACCESS_EDIT__));
					
					if ($vs_type_list) {
						$vs_buf .= '<div style="border-top: 1px solid #aaaaaa; margin-top: 5px; font-size: 10px;">';
						$vs_buf .= caFormTag($po_view->request, 'Edit', 'NewChildForm', 'administrate/setup/list_item_editor/ListItemEditor', 'post', 'multipart/form-data', '_top', array('disableUnsavedChangesWarning' => true));
						$vs_buf .= _t('Add a %1 to this list', $vs_type_list).caHTMLHiddenInput($t_list_item->primaryKey(), array('value' => '0')).caHTMLHiddenInput('parent_id', array('value' => $t_list_item->getPrimaryKey()));
						$vs_buf .= caFormSubmitLink($po_view->request, caNavIcon($po_view->request, __CA_NAV_BUTTON_ADD__), '', 'NewChildForm');
						$vs_buf .= "</form></div>\n";
					}
			}
			
			//
			// Output containing list for list items
			// 
			if ($vs_table_name === 'ca_list_items') {
				if ($t_list = $po_view->getVar('t_list')) {
					$vn_list_id = $t_list->getPrimaryKey();
					$vs_buf .= "<strong>"._t("Part of")."</strong>: ".caEditorLink($po_view->request, $t_list->getLabelForDisplay(), '', 'ca_lists', $vn_list_id) ."<br/>\n";
					if ($t_item->get('is_default')) {
						$vs_buf .= "<strong>"._t("Is default for list")."</strong><br/>\n";
					}
				}
			}
	
			//
			// Output containing relationship type name for relationship types
			// 
			if ($vs_table_name === 'ca_relationship_types') {
				if (!($t_rel_instance = $t_item->getAppDatamodel()->getInstanceByTableNum($t_item->get('table_num'), true))) {
					if ($vn_parent_id = $po_view->request->getParameter('parent_id', pInteger)) {
						$t_rel_type = new ca_relationship_types($vn_parent_id);
						$t_rel_instance = $t_item->getAppDatamodel()->getInstanceByTableNum($t_rel_type->get('table_num'), true);
					}
				}
				
				if ($t_rel_instance) {
					$vs_buf .= "<div><strong>"._t("Is a")."</strong>: ".$t_rel_instance->getProperty('NAME_SINGULAR')."<br/></div>\n";
				}
			}
			
			//
			// Output extra useful info for metadata elements
			// 
			if (($vs_table_name === 'ca_metadata_elements') && $t_item->getPrimaryKey()) {
				$vs_buf .= "<div><strong>"._t("Element code")."</strong>: ".$t_item->get('element_code')."<br/></div>\n";
				
				if (sizeof($va_uis = $t_item->getUIs()) > 0) {
					$vs_buf .= "<div><strong>"._t("Referenced by user interfaces")."</strong>:<br/>\n";
					foreach($va_uis as $vn_ui_id => $va_ui_info) {
						$vs_buf .= caNavLink($po_view->request, $va_ui_info['name'], '', 'administrate/setup/interface_screen_editor', 'InterfaceScreenEditor', 'Edit', array('ui_id' => $vn_ui_id, 'screen_id' => $va_ui_info['screen_id']));
						$vs_buf .= " (".$o_dm->getTableProperty($va_ui_info['editor_type'], 'NAME_PLURAL').")<br/>\n";
					}
					$vs_buf .= "</div>\n";
				}
			}
			
			//
			// Output related objects for ca_editor_uis and ca_editor_ui_screens
			//
			if ($vs_table_name === 'ca_editor_uis') {
				$vs_buf .= "<div><strong>"._t("Number of screens")."</strong>: ".(int)$t_item->getScreenCount()."\n";
				
				if ($t_item->getPrimaryKey()) {
					$vs_buf .= "<div><strong>"._t("Edits")."</strong>: ".caGetTableDisplayName($t_item->get('editor_type'))."<br/>\n";
				} else {
					$vs_buf .= "<div><strong>"._t("Edits")."</strong>: ".caGetTableDisplayName($po_view->request->getParameter('editor_type', pInteger))."<br/>\n";
				}	
				$vs_buf .= "</div>\n";
			}
			
			//
			// Output related objects for ca_editor_uis and ca_editor_ui_screens
			//
			if ($vs_table_name === 'ca_editor_ui_screens') {
				$t_ui = new ca_editor_uis($vn_ui_id = $t_item->get('ui_id'));
				$vs_buf .= "<div><strong>"._t("Part of")."</strong>: ".caNavLink($po_view->request, $t_ui->getLabelForDisplay(), '',  'administrate/setup/interface_editor', 'InterfaceEditor', 'Edit', array('ui_id' => $vn_ui_id))."\n";
					
				$vs_buf .= "</div>\n";
			}
			
			//
			// Output extra useful info for bundle displays
			//
			if ($vs_table_name === 'ca_bundle_displays') {
				$vs_buf .= "<div><strong>"._t("Number of placements")."</strong>: ".$t_item->getPlacementCount(array('user_id' => $po_view->request->getUserID()))."<br/>\n";
					
				if ($t_item->getPrimaryKey()) {
					
					$vn_content_table_num = $t_item->get('table_num');
					$vs_buf .= "<strong>"._t("Type of content")."</strong>: ".caGetTableDisplayName($vn_content_table_num)."\n";
					
					$vs_buf .= "</div>\n";
				} else {
					if ($vn_content_table_num = $po_view->request->getParameter('table_num', pInteger)) {
						$vs_buf .= "<div><strong>"._t("Type of content")."</strong>: ".caGetTableDisplayName($vn_content_table_num)."\n";
					
						$vs_buf .= "</div>\n";
					}
				}
				
				$t_user = new ca_users(($vn_user_id = $t_item->get('user_id')) ? $vn_user_id : $po_view->request->getUserID());
				if ($t_user->getPrimaryKey()) {
					$vs_buf .= "<div><strong>"._t('Owner')."</strong>: ".$t_user->get('fname').' '.$t_user->get('lname')."</div>\n";
				}
			}
			
			//
			// Output extra useful info for search forms
			//
			if ($vs_table_name === 'ca_search_forms') {
				$vs_buf .= "<div><strong>"._t("Number of placements")."</strong>: ".$t_item->getPlacementCount(array('user_id' => $po_view->request->getUserID()))."<br/>\n";
					
				if ($t_item->getPrimaryKey()) {
					
					$vn_content_table_num = $t_item->get('table_num');
					$vs_buf .= "<strong>"._t("Searches for")."</strong>: ".caGetTableDisplayName($vn_content_table_num)."\n";
					$vs_buf .= "</div>\n";
				} else {
					if ($vn_content_table_num = $po_view->request->getParameter('table_num', pInteger)) {
						$vs_buf .= "<strong>"._t("Searches for")."</strong>: ".caGetTableDisplayName($vn_content_table_num)."\n";
						$vs_buf .= "</div>\n";
					}
				}
				$t_user = new ca_users(($vn_user_id = $t_item->get('user_id')) ? $vn_user_id : $po_view->request->getUserID());
				if ($t_user->getPrimaryKey()) {
					$vs_buf .= "<div><strong>"._t('Owner')."</strong>: ".$t_user->get('fname').' '.$t_user->get('lname')."</div>\n";
				}
			}
			
			//
			// Output extra useful info for tours
			// 
			if (($vs_table_name === 'ca_tours') && $t_item->getPrimaryKey()) {
				$vs_buf .= "<br/><strong>"._t("Number of stops")."</strong>: ".$t_item->getStopCount()."<br/>\n";
			}
			
			//
			// Output containing tour for tour stops
			// 
			if ($vs_table_name === 'ca_tour_stops') {
				$t_tour = new ca_tours($vn_tour_id = $t_item->get('tour_id'));
				$vs_buf .= "<strong>"._t("Part of")."</strong>: ".caEditorLink($po_view->request, $t_tour->getLabelForDisplay(), '', 'ca_tours', $vn_tour_id) ."<br/>\n";
			}
			
			//
			// Output extra useful info for bundle mappings
			//
			if ($vs_table_name === 'ca_bundle_mappings') {
				if ($t_item->getPrimaryKey()) {
					$vn_content_table_num = $t_item->get('table_num');
					$vs_buf .= "<br/><strong>"._t("Type of content")."</strong>: ".caGetTableDisplayName($vn_content_table_num)."<br/>\n";
					$vs_buf .= "<strong>"._t("Type")."</strong>: ".$t_item->getChoiceListValue('direction', $t_item->get('direction'))."<br/>\n";
					$vs_buf .= "<strong>"._t("Target format")."</strong>: ".$t_item->get('target')."<br/>\n";

					$va_stats = $t_item->getMappingStatistics();
					$vs_buf .= "<div><strong>"._t("Number of groups")."</strong>: ".$va_stats['groupCount']."<br/>\n";
					$vs_buf .= "<strong>"._t("Number of rules")."</strong>: ".$va_stats['ruleCount']."<br/>\n";
					
					
					$vs_buf .= "</div>\n";
				} else {
					if ($vn_content_table_num = $po_view->request->getParameter('table_num', pInteger)) {
						$vs_buf .= "<div><strong>"._t("Type of content")."</strong>: ".caGetTableDisplayName($vn_content_table_num)."<br/>\n";
						$vs_buf .= "<strong>"._t("Type")."</strong>: ".$t_item->getChoiceListValue('direction', $po_view->request->getParameter('direction', pString))."<br/>\n";
						$vs_buf .= "<strong>"._t("Target format")."</strong>: ".$po_view->request->getParameter('target', pString)."<br/>\n";
				
						$vs_buf .= "<div><strong>"._t("Number of groups")."</strong>: 0<br/>\n";
						$vs_buf .= "<strong>"._t("Number of rules")."</strong>: 0</div>\n";
					
						$vs_buf .= "</div>\n";
					}
				}
			}
		// -------------------------------------------------------------------------------------
		// Export
		

		if ($t_item->getPrimaryKey() && $po_view->request->config->get($vs_table_name.'_show_add_child_control_in_inspector')) {
			$vb_show_add_child_control = true;
			if (is_array($va_restrict_add_child_control_to_types = $po_view->request->config->getList($vs_table_name.'_restrict_child_control_in_inspector_to_types')) && sizeof($va_restrict_add_child_control_to_types)) {
				$t_type_instance = $t_item->getTypeInstance();
				if (!in_array($t_type_instance->get('idno'), $va_restrict_add_child_control_to_types) && !in_array($t_type_instance->getPrimaryKey(), $va_restrict_add_child_control_to_types)) {
					$vb_show_add_child_control = false;
				}
			}
		}
		
		

		if($po_view->request->user->canDoAction('can_export_'.$vs_table_name) && $t_item->getPrimaryKey() && (sizeof(ca_data_exporters::getExporters($t_item->tableNum()))>0)) {
			$vs_buf .= '<div style="border-top: 1px solid #aaaaaa; margin-top: 5px; font-size: 10px; text-align: right;" id="caExportItemButton">';
				
			$vs_buf .= _t('Export this %1', mb_strtolower($vs_type_name, 'UTF-8'))." ";
			$vs_buf .= "<a class='button' onclick='jQuery(\"#exporterFormList\").show();' style='text-align:right;' href='#'>".caNavIcon($po_view->request, __CA_NAV_BUTTON_ADD__)."</a>";

			$vs_buf .= caFormTag($po_view->request, 'ExportSingleData', 'caExportForm', 'manage/MetadataExport', 'post', 'multipart/form-data', '_top', array('disableUnsavedChangesWarning' => true));
			$vs_buf .= "<div id='exporterFormList'>";
			$vs_buf .= ca_data_exporters::getExporterListAsHTMLFormElement('exporter_id', $t_item->tableNum(), array('id' => 'caExporterList'),array('width' => '120px'));
			$vs_buf .= caHTMLHiddenInput('item_id', array('value' => $t_item->getPrimaryKey()));
			$vs_buf .= caFormSubmitLink($po_view->request, _t('Export')." &rsaquo;", "button", "caExportForm");
			$vs_buf .= "</div>\n";
			$vs_buf .= "</form>";
				
			$vs_buf .= "</div>";

			$vs_buf .= "<script type='text/javascript'>";
			$vs_buf .= "jQuery(document).ready(function() {";
			$vs_buf .= "jQuery(\"#exporterFormList\").hide();";
			$vs_buf .= "});";
			$vs_buf .= "</script>";
		}
		
		
		
		$vs_buf .= "</div></h4>\n";
		
		$vs_buf .= "<script type='text/javascript'>
			var inspectorCookieJar = jQuery.cookieJar('caCookieJar');";
			if($t_item->getPrimaryKey()) {
				if ($vs_more_info) {
					$vs_buf .= "			
			if (inspectorCookieJar.get('inspectorMoreInfoIsOpen') == undefined) {		// default is to have info open
				inspectorCookieJar.set('inspectorMoreInfoIsOpen', 1);
			}
			if (inspectorCookieJar.get('inspectorMoreInfoIsOpen') == 1) {
				jQuery('#inspectorInfo').toggle(0);
				jQuery('#inspectorMoreInfo').html('".addslashes(caNavIcon($po_view->request, __CA_NAV_BUTTON_COLLAPSE__))."');
			}
		
			jQuery('#inspectorMoreInfo').click(function() {
				jQuery('#inspectorInfo').slideToggle(350, function() { 
					inspectorCookieJar.set('inspectorMoreInfoIsOpen', (this.style.display == 'block') ? 1 : 0); 
					jQuery('#inspectorMoreInfo').html((this.style.display == 'block') ? '".addslashes(caNavIcon($po_view->request, __CA_NAV_BUTTON_COLLAPSE__))."' : '".addslashes(caNavIcon($po_view->request, __CA_NAV_BUTTON_INFO2__))."');
					caResizeSideNav();
				}); 
				return false;
			});
		";
				}
	
				if (sizeof($va_reps)) {
					$vs_buf .= "
		if (inspectorCookieJar.get('inspectorShowMediaIsOpen') == undefined) {		// default is to have media open
			inspectorCookieJar.set('inspectorShowMediaIsOpen', 1);
		}
		
		if (inspectorCookieJar.get('inspectorShowMediaIsOpen') == 1) {
			jQuery('#inspectorMedia').toggle();
		}
	
		jQuery('#caColorbox').on('click', function(e) {
			if (e.altKey) {
				jQuery('#inspectorMedia').slideToggle(200, function() { 
					inspectorCookieJar.set('inspectorShowMediaIsOpen', (this.style.display == 'block') ? 1 : 0); 
						caResizeSideNav();
				}); 
				return false;
			}
		});
					";
				}
			}

			$vs_buf .= "</script>\n";
		}

        $o_app_plugin_manager = new ApplicationPluginManager();
        $va_hookAppend = $o_app_plugin_manager->hookAppendToEditorInspector(array("t_item"=>$t_item));
        if (is_string($va_hookAppend["caEditorInspectorAppend"])) {
            $vs_buf .= $va_hookAppend["caEditorInspectorAppend"];
        }

        return $vs_buf;
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * Generates access control list (ACL) editor for item
	 *
	 * @param View $po_view Inspector view object
	 * @param BaseModel $pt_instance Model instance representing the item for which ACL is being managed
	 * @param array $pa_options None implemented yet
	 *
	 * @return string HTML implementing the inspector
	 */
	function caEditorACLEditor($po_view, $pt_instance, $pa_options=null) {
		$vs_view_path = (isset($pa_options['viewPath']) && $pa_options['viewPath']) ? $pa_options['viewPath'] : $po_view->request->getViewsDirectoryPath();
		$o_view = new View($po_view->request, "{$vs_view_path}/bundles/");
		
		$o_view->setVar('t_instance', $pt_instance);
		return $o_view->render('ca_acl_access.php');
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * Generates standard-format inspector panels for editors
	 *
	 * @param View $po_view Inspector view object
	 * @param array $pa_options Optional array of options. Supported options are:
	 *		backText = a string to use as the "back" button text; default is "Results"
	 *
	 * @return string HTML implementing the inspector
	 */
	function caBatchEditorInspector($po_view, $pa_options=null) {
		require_once(__CA_MODELS_DIR__.'/ca_sets.php');
		
		$t_set 					= $po_view->getVar('t_set');
		$t_item 				= $po_view->getVar('t_item');
		$vs_table_name = $t_item->tableName();
		if (($vs_priv_table_name = $vs_table_name) == 'ca_list_items') {
			$vs_priv_table_name = 'ca_lists';
		}
		
		$o_result_context		= $po_view->getVar('result_context');
		$t_ui 					= $po_view->getVar('t_ui');
		
		$o_dm = Datamodel::load();
	
		// action extra to preserve currently open screen across next/previous links
		//$vs_screen_extra 	= ($po_view->getVar('screen')) ? '/'.$po_view->getVar('screen') : '';
		
		$vs_buf = '<h3 class="nextPrevious">'.caNavLink($po_view->request, 'Back', '', 'manage', 'Set', 'ListSets')."</h3>\n";

		$vs_color = $vs_type_name = null;
		
		$t_type = method_exists($t_item, "getTypeInstance") ? $t_item->getTypeInstance() : null;
		if ($t_type) { 
			$vs_color = trim($t_type->get('color')); 
			$vs_type_name = $t_type->getTypeName();
		}
		if (!$vs_color && $t_ui) { $vs_color = trim($t_ui->get('color')); }
		if (!$vs_color) { $vs_color = "444444"; }
		
		$vs_buf .= "<h4><div id='caColorbox' style='border: 6px solid #{$vs_color}; padding-bottom:15px;'>\n";
		
		if($po_view->request->user->canDoAction("can_edit_".$vs_priv_table_name) && (sizeof($t_item->getTypeList()) > 1)){
			if ($po_view->request->user->canDoAction("can_change_type_{$vs_table_name}")) {
				
				$vs_buf .= "<div id='inspectorChangeType'><div id='inspectorChangeTypeButton'><a href='#' onclick='caTypeChangePanel.showPanel(); return false;'>".caNavIcon($po_view->request, __CA_NAV_BUTTON_CHANGE__, array('title' => _t('Change type')))."</a></div></div>\n";
				TooltipManager::add("#inspectorChangeType", _t('Change Record Type')); 

				$vo_change_type_view = new View($po_view->request, $po_view->request->getViewsDirectoryPath()."/bundles/");
				$vo_change_type_view->setVar('t_item', $t_item);
				$vo_change_type_view->setVar('t_set', $t_set);
				$vo_change_type_view->setVar('set_id', $t_set->getPrimaryKey());
				
				FooterManager::add($vo_change_type_view->render("batch_change_type_html.php"));
			}
			$vs_buf .= "<strong>"._t("Editing %1", $vs_type_name).": </strong>\n";
		}else{
			$vs_buf .= "<strong>"._t("Viewing %1", $vs_type_name).": </strong>\n";
		}
		
		$vn_item_count = $t_set->getItemCount(array('user_id' => $po_view->request->getUserID()));
		$vs_item_name = ($vn_item_count == 1) ? $t_item->getProperty("NAME_SINGULAR"): $t_item->getProperty("NAME_PLURAL");
		
		$vs_buf .= "<strong>"._t("Batch editing %1 %2 in set", $vn_item_count, $vs_item_name).": </strong>\n";
		
		
		if (!($vs_label = $t_set->getLabelForDisplay())) {
			if (!($vs_label = $t_set->get('set_code'))) {
				$vs_label = '['._t('BLANK').']'; 
			}
		}
		
		if($t_set->haveAccessToSet($po_view->request->getUserID(), __CA_SET_EDIT_ACCESS__)) {
			$vs_label = caEditorLink($po_view->request, $vs_label, '', 'ca_sets', $t_set->getPrimaryKey());
		}
	
		
		$vs_buf .= " {$vs_label}"."<a title='$vs_idno'>".($vs_idno ? " ({$vs_idno})" : '')."</a>\n";

		
		// -------------------------------------------------------------------------------------
	
		$vs_buf .= "<div>"._t('Set contains <em>%1</em>', join(", ", $t_set->getTypesForItems()))."</div>\n";

		// -------------------------------------------------------------------------------------
		// Nav link for batch delete
		// -------------------------------------------------------------------------------------

		if (($vn_item_count > 0) && ($po_view->request->user->canDoAction('can_batch_delete_'.$o_dm->getTableName($t_set->get('table_num'))))) {

			$vs_buf .= "<div class='button' style='text-align:right;'><a href='#' id='inspectorMoreInfo'>"._t("More options")."</a> &rsaquo;</div>
				<div id='inspectorInfo' style='background-color:#f9f9f9; border: 1px solid #eee;'>";
			$vs_buf .= caNavLink($po_view->request, 
				caNavIcon($po_view->request, __CA_NAV_BUTTON_DEL_BUNDLE__, array('style' => 'margin-top:7px; vertical-align: text-bottom;'))." "._t("Delete <strong><em>all</em></strong> records in set")
				, null, 'batch', 'Editor', 'Delete', array('set_id' => $t_set->getPrimaryKey())
			);

			$vs_buf .= "</div>\n";

			$vs_buf .= "<script type='text/javascript'>
				jQuery('#inspectorMoreInfo').click(function() {
					jQuery('#inspectorInfo').slideToggle(350, function() { 
						jQuery('#inspectorMoreInfo').html((this.style.display == 'block') ? '".addslashes(_t('Close options'))."' : '".addslashes(_t('More options'))."');
					}); 
					return false;
				});
			</script>";

		}

		// -------------------------------------------------------------------------------------
		
		$vs_buf .= "</div></h4>\n";
	
		return $vs_buf;
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * Generates standard-format inspector panels for editors
	 *
	 * @param View $po_view Inspector view object
	 * @param array $pa_options Optional array of options. Supported options are:
	 *		backText = a string to use as the "back" button text; default is "Results"
	 *
	 * @return string HTML implementing the inspector
	 */
	function caBatchMediaImportInspector($po_view, $pa_options=null) {
		$vs_color = "444444"; 
		$vs_buf .= "<h4><div id='caColorbox' style='border: 6px solid #{$vs_color}; padding-bottom:15px;'>\n";
		$vs_buf .= "<strong>"._t("Batch import media")."</strong>\n";
		
		$vs_batch_media_import_root_directory = $po_view->request->config->get('batch_media_import_root_directory');
		$vs_buf .= "<p>"._t('<strong>Server directory:</strong> %1', $vs_batch_media_import_root_directory)."</p>\n";

		// Show the counts here is nice but can bog the server down when the import directory is an NFS or SAMBA mount
		//$va_counts = caGetDirectoryContentsCount($vs_batch_media_import_root_directory, true, false, false); 
		//$vs_buf .= "<p>"._t('<strong>Directories on server:</strong> %1', $va_counts['directories'])."<br/>\n";
		//$vs_buf .= _t('<strong>Files on server:</strong> %1', $va_counts['files'])."<p>\n";

		$vs_buf .= "</div></h4>\n";
		
		return $vs_buf;
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * Generates standard-format inspector panels for exporters
	 *
	 * @param View $po_view Inspector view object
	 *
	 * @return string HTML implementing the inspector
	 */
	function caBatchMetadataExportInspector($po_view) {
		$vs_color = "444444";
		$vs_buf = "<h4><div id='caColorbox' style='border: 6px solid #{$vs_color}; padding-bottom:15px;'>\n";

		$vs_buf .= "<strong>"._t("Batch export metadata")."</strong>\n";

		$t_item = $po_view->getVar("t_item");
		$vs_buf .= "<p>"._t("Selected exporter").":<br />".$t_item->getLabelForDisplay()."</p>";

		if($vn_id = $po_view->request->getParameter('item_id', pInteger)) {
			$vs_buf .= "<p>".caEditorLink($po_view->request, _t("Back to record"), 'caResultsEditorEditLink', $t_item->getTargetTableName(), $vn_id)."</p>";
		}
		
		$vs_buf .= "</div></h4>\n";
		
		return $vs_buf;
	}
	# ------------------------------------------------------------------------------------------------
	/**
	  *
	  */
	function caTableIsActive($pm_table) {
		$o_dm = Datamodel::load();
		$t_instance = is_numeric($pm_table) ? $o_dm->getInstanceByTableNum($pm_table, true) : $o_dm->getInstanceByTableName($pm_table, true);
		if (!$t_instance) { return null; }
		
		$vs_table_name = $t_instance->tableName();
		
		$o_config = Configuration::load();
		if (is_subclass_of($t_instance, "BaseRelationshipModel")) {
			$vs_left_table_name = $t_instance->getLeftTableName();
			if ($vs_left_table_name == 'ca_tour_stops') { $vs_left_table_name = 'ca_tours'; }
			$vs_right_table_name = $t_instance->getRightTableName();
			if ($vs_right_table_name == 'ca_tour_stops') { $vs_right_table_name = 'ca_tours'; }
			
			if ((int)($o_config->get("{$vs_left_table_name}_disable"))) { return false; }
			if ((int)($o_config->get("{$vs_right_table_name}_disable"))) { return false; }
		} else {
			switch($vs_table_name) {
				case 'ca_object_representations':
					if (!(int)($o_config->get('ca_objects_disable'))) { return true; }	
					break;
			}
			if ((int)($o_config->get($vs_table_name.'_disable'))) { return false; }
		}
		
		switch($vs_table_name) {
			case 'ca_tour_stops':
				if ((int)($o_config->get('ca_tours_disable'))) { return false; }
				break;
		}
		
		return true;
	}
	# ------------------------------------------------------------------------------------------------
	/**
	  *
	  */
	function caFilterTableList($pa_tables, $pa_options=null) {
		require_once(__CA_MODELS_DIR__.'/ca_occurrences.php');
		$o_config = Configuration::load();
		$o_dm = Datamodel::load();
		
		// assume table display names (*not actual database table names*) are keys and table_nums are values
		$va_filtered_tables = array();
		foreach($pa_tables as $vs_display_name => $vn_table_num) {
			$vs_display_name = mb_strtolower($vs_display_name, 'UTF-8');
			
			if (!caTableIsActive($vn_table_num)) { continue; }
			$vs_table_name = $o_dm->getTableName($vn_table_num);
			
			switch($vs_table_name) {
				case 'ca_occurrences':
					$t_occ = new ca_occurrences();	
					$va_types = $t_occ->getTypeList();
					$va_type_labels = array();
					foreach($va_types as $vn_item_id => $va_type_info) {
						$va_type_labels[] = mb_strtolower($va_type_info['name_plural'], 'UTF-8');
					}
					if (sizeof($va_type_labels)) {
						if (mb_strlen($vs_label = join('/', $va_type_labels)) > 50) {
							$vs_label = mb_substr($vs_label, 0, 60).'...';
						}
						$va_filtered_tables[$vs_label] = $vn_table_num;
					} else {
						$va_filtered_tables[$vs_display_name] = $vn_table_num;
					}
					break;
				default:	
					$va_filtered_tables[$vs_display_name] = $vn_table_num;
					break;
			}
		}
		
		if (caGetOption("sort", $pa_options, true)) {
			ksort($va_filtered_tables);
		}
		
		return $va_filtered_tables;
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 *
	 */
	function caGetTableDisplayName($pm_table_name_or_num, $pb_use_plural=true) {
		require_once(__CA_MODELS_DIR__.'/ca_occurrences.php');
		$o_dm = Datamodel::load();
		
		$vs_table = $o_dm->getTableName($pm_table_name_or_num);
		
		switch($vs_table) {
			case 'ca_occurrences':
				$t_occ = new ca_occurrences();	
					$va_types = $t_occ->getTypeList();
					$va_type_labels = array();
					foreach($va_types as $vn_item_id => $va_type_info) {
						$va_type_labels[] = $va_type_info[($pb_use_plural ? 'name_plural' : 'name_singular')];
					}
					
					return join('/', $va_type_labels);
				break;
			default:
				if($t_instance = $o_dm->getInstanceByTableName($vs_table, true)) {
					return $t_instance->getProperty(($pb_use_plural ? 'NAME_PLURAL' : 'NAME_SINGULAR'));
				}
				break;
		}
		
		return null;
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * 
	 *
	 * @param
	 *
	 * @return 
	 */
	function caGetMediaDisplayInfo($ps_context, $ps_mimetype) {
		$o_config = Configuration::load();
		$o_media_display_config = Configuration::load(__CA_APP_DIR__.'/conf/media_display.conf');
		
		if (!is_array($va_context = $o_media_display_config->getAssoc($ps_context))) { return null; }
	
		if (!$ps_mimetype) { return $va_context; }
		foreach($va_context as $vs_media_class => $va_media_class_info) {
			if (!is_array($va_mimetypes = $va_media_class_info['mimetypes'])) { continue; }
			
			if (in_array($ps_mimetype, $va_mimetypes)) {
				return $va_media_class_info;
			}
		}
		return null;
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * Returns a list of "^" prefixed-tags (eg. ^forename) present in a template
	 *
	 * @param string $ps_template
	 * @param array $pa_options No options are currently supported
	 * 
	 * @return array An array of tags
	 */
	function caGetTemplateTags($ps_template, $pa_options=null) {
		$va_tags = array();
		
		$vs_prefix = caGetOption('prefix', $pa_options, null);
		
		if (preg_match_all(__CA_BUNDLE_DISPLAY_TEMPLATE_TAG_REGEX__, $ps_template, $va_matches)) {
			foreach($va_matches[1] as $vn_i => $vs_possible_tag) {
				//if ($vs_prefix) { $va_matches[1][$vn_i] = $vs_possible_tag = $vs_prefix.$vs_possible_tag; }
				if (strpos($vs_possible_tag, "~") !== false) { continue; }	// don't clip trailing characters when there's a tag directive specified
				$va_matches[1][$vn_i] = rtrim($vs_possible_tag, "/.%");	// remove trailing slashes, periods and percent signs as they're potentially valid tag characters that are never meant to be at the end
			}
			$va_tags = $va_matches[1];
		}
		
		return $va_tags;
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * Replace "^" prefix-edtags (eg. ^forename) in a template with values from an array
	 *
	 * @param string $ps_template String with embedded tags. Tags are just alphanumeric strings prefixed with a caret ("^")
	 * @param array $pa_values Array of values; keys must match tag names
	 * @param array $pa_options Supported options are:
	 *			prefix = string to add to beginning of tags extracted from template before doing lookup into value array
	 *			removePrefix = string to remove from tags extracted from template before doing lookup into value array
	 *			getFrom = a model instance to draw data from. If set, $pa_values is ignored.
	 *			quote = quote replacement values (Eg. ^ca_objects.idno becomes "2015.001" rather than 2015.001). Value containing quotes will be escaped with a backslash. [Default is false]
	 *
	 * @return string Output of processed template
	 */
	function caProcessTemplate($ps_template, $pa_values, $pa_options=null) {
		$ps_prefix = caGetOption('prefix', $pa_options, null);
		$ps_remove_prefix = caGetOption('removePrefix', $pa_options, null);
		$pb_quote = caGetOption('quote', $pa_options, false);
		
		$va_tags = caGetTemplateTags($ps_template);
		
		$t_instance = null;
		if (isset($pa_options['getFrom']) && (method_exists($pa_options['getFrom'], 'get'))) {
			$t_instance = $pa_options['getFrom'];
		}
		
		foreach($va_tags as $vs_tag) {
			$va_tmp = explode("~", $vs_tag);
			$vs_proc_tag = array_shift($va_tmp);
			if ($ps_remove_prefix) {
				$vs_proc_tag = str_replace($ps_remove_prefix, '', $vs_proc_tag);
			}
			if ($ps_prefix && !preg_match("!^".preg_quote($ps_prefix, "!")."!", $vs_proc_tag)) {
				$vs_proc_tag = $ps_prefix.$vs_proc_tag;
			}
			
			if ($t_instance && ($vs_gotten_val = $t_instance->get($vs_proc_tag, $pa_options))) {
				$vs_gotten_val = caProcessTemplateTagDirectives($vs_gotten_val, $va_tmp);
				
				$ps_template = preg_replace("/\^".preg_quote($vs_tag, '/')."(?![A-Za-z0-9]+)/", $vs_gotten_val, $ps_template);
			} else {
				if (is_array($vs_val = isset($pa_values[$vs_proc_tag]) ? $pa_values[$vs_proc_tag] : '')) {
					// If value is an array try to make a string of it
					$vs_val = join(" ", $vs_val);
				}
				
				$vs_val = caProcessTemplateTagDirectives($vs_val, $va_tmp);
				
				if ($pb_quote) { $vs_val = '"'.addslashes($vs_val).'"'; }
				$vs_tag_proc = preg_quote($vs_tag, '/');
				$ps_template = preg_replace("/\^(?={$vs_tag_proc}[^A-Za-z0-9]+|{$vs_tag_proc}$){$vs_tag_proc}/", str_replace("$", "\\$", $vs_val), $ps_template);	// escape "$" to prevent interpretation as backreferences
			}
		}
		return $ps_template;
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * Modified tag value based upon settings in supported tag directives
	 * The directive syntax is <directive>:<directive parameters>
	 * Supported directives include:
	 *		LP = left pad value; parameters are in the format <padding>/<length>. Ex. LP:0/10 will left pad a string with zeros to a length of 10.
	 *		RP = right pad value; parameters are in the format <padding>/<length>
	 *		PREFIX = add prefix to value if not empty; parameter is the prefix text
	 *		SUFFIX = add suffix to value if not empty;  parameter is the suffix text
	 *
	 * @param string $ps_value
	 * @param array $pa_directives
	 *
	 * @return string
	 */
	function caProcessTemplateTagDirectives($ps_value, $pa_directives) {
		if (!is_array($pa_directives) || !sizeof($pa_directives)) { return $ps_value; }
		foreach($pa_directives as $vs_directive) {
			$va_tmp = explode(":", $vs_directive);
			switch(strtoupper($va_tmp[0])) {
				case 'LP':		// left padding
					$va_params = explode("/", $va_tmp[1]);
					$vn_len = (int)$va_params[1];
					$vs_str = (string)$va_params[0];
					if (($vn_len > 0) && strlen($vs_str)) {
						$ps_value = str_pad($ps_value, $vn_len, $vs_str, STR_PAD_LEFT);
					}
					break;
				case 'RP':		// right padding
					$va_params = explode("/", $va_tmp[1]);
					$vn_len = (int)$va_params[1];
					$vs_str = (string)$va_params[0];
					if (($vn_len > 0) && strlen($vs_str)) {
						$ps_value = str_pad($ps_value, $vn_len, $vs_str, STR_PAD_RIGHT);
					}
					break;
				case 'PREFIX':
				case 'PX':
					if ((strlen($ps_value) > 0) && (strlen($va_tmp[1]))) {
						$ps_value = $va_tmp[1].$ps_value;
					}
					break;
				case 'SUFFIX':
				case 'SX':
					if ((strlen($ps_value) > 0) && (strlen($va_tmp[1]))) {
						$ps_value = $ps_value.$va_tmp[1];
					}
					break;
				case 'LOWER':
					$ps_value = strtolower($ps_value);
					break;
				case 'UPPER':
					$ps_value = strtoupper($ps_value);
					break;
			}
		}
		return $ps_value;
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * Replace "^" prefixed tags (eg. ^forename) in a template with values from an array
	 *
	 * @param string $ps_template String with embedded tags. Tags are just alphanumeric strings prefixed with a caret ("^")
	 * @param string $pm_tablename_or_num Table name or number of table from which values are being formatted
	 * @param string $pa_row_ids An array of primary key values in the specified table to be pulled into the template
	 * @param array $pa_options Supported options are:
	 *		returnAsArray = if true an array of processed template values is returned, otherwise the template values are returned as a string joined together with a delimiter. Default is false.
	 *		delimiter = value to string together template values with when returnAsArray is false. Default is ';' (semicolon)
	 *		placeholderPrefix = attribute container to implicitly place primary record fields into. Ex. if the table is "ca_entities" and the placeholder is "address" then tags like ^city will resolve to ca_entities.address.city
	 *		requireLinkTags = if set then links are only added when explicitly defined with <l> tags. Default is to make the entire text a link in the absence of <l> tags.
	 *		primaryIDs = row_ids for primary rows in related table, keyed by table name; when resolving ambiguous relationships the row_ids will be excluded from consideration. This option is rarely used and exists primarily to take care of a single
	 *						edge case: you are processing a template relative to a self-relationship such as ca_entities_x_entities that includes references to the subject table (ca_entities, in the case of ca_entities_x_entities). There are
	 *						two possible paths to take in this situations; primaryIDs lets you specify which ones you *don't* want to take by row_id. For interstitial editors, the ids will be set to a single id: that of the subject (Eg. ca_entities) row
	 *						from which the interstitial was launched.
	 *		sort = optional list of tag values to sort repeating values within a row template on. The tag must appear in the template. You can specify more than one tag by separating the tags with semicolons.
	 *		sortDirection = The direction of the sort of repeating values within a row template. May be either ASC (ascending) or DESC (descending). [Default is ASC]
	 *		linkTarget = Optional target to use when generating <l> tag-based links. By default links point to standard detail pages, but plugins may define linkTargets that point elsewhere.
	 * 		skipIfExpression = skip the elements in $pa_row_ids for which the given expression does not evaluate true
	 *		includeBlankValuesInArray = include blank template values in primary template and all <unit>s in returned array when returnAsArray is set. If you need the returned array of values to line up with the row_ids in $pa_row_ids this should be set. [Default is false]
	 *		includeBlankValuesInTopLevelForPrefetch = include blank template values in *primary template* (not <unit>s) in returned array when returnAsArray is set. Used by template prefetcher to ensure returned values align with id indices. [Default is false]
	 *
	 * @return mixed Output of processed templates
	 */
	function caProcessTemplateForIDs($ps_template, $pm_tablename_or_num, $pa_row_ids, $pa_options=null) {
		return DisplayTemplateParser::evaluate($ps_template, $pm_tablename_or_num, $pa_row_ids, $pa_options);
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * Returns display string for relationship bundles. Used by themes/default/bundles/ca_entities.php and the like.
	 *
	 * @param RequestHTTP $po_request
	 * @param string $ps_table
	 * @param array $pa_attributes
	 * @param array $pa_options
	 *
	 * @return string 
	 */
	function caGetRelationDisplayString($po_request, $ps_table, $pa_attributes=null, $pa_options=null) {
		$o_config = Configuration::load();
		$o_dm = Datamodel::load();
		
		if (!($vs_relationship_type_display_position = caGetOption('relationshipTypeDisplayPosition', $pa_options, null))) {
			$vs_relationship_type_display_position = strtolower($o_config->get($ps_table.'_lookup_relationship_type_position'));
		}
		
		$vs_attr_str = _caHTMLMakeAttributeString(is_array($pa_attributes) ? $pa_attributes : array());
		$vs_display = "{".((isset($pa_options['display']) && $pa_options['display']) ? $pa_options['display'] : "_display")."}";
		if (isset($pa_options['makeLink']) && $pa_options['makeLink']) {
			$vs_display = "<a href='".urldecode(caEditorUrl($po_request, $ps_table, '{'.$o_dm->getTablePrimaryKeyName($ps_table).'}', false, array('rel' => true)))."' {$vs_attr_str}>{$vs_display}</a>";
		}
		
		switch($vs_relationship_type_display_position) {
			case 'left':
				return "({{relationship_typename}}) {$vs_display}";
				break;
			case 'none':
				return "{$vs_display}";
				break;
			default:
			case 'right':
				return "{$vs_display} ({{relationship_typename}})";
				break;
		}
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * Returns date/time as a localized string for display, subject to the settings in the app/conf/datetime.conf configuration
	 *
	 * @param null|int $pn_timestamp Unix timestamp for date/time to localize; if omitted defaults to current date and time.
	 * @param null|array $pa_options All options supported by TimeExpressionParser::getText() are supported
	 * @return string Localized date/time expression
	 */
	function caGetLocalizedDate($pn_timestamp=null, $pa_options=null) {
		if (!$pn_timestamp) { $pn_timestamp = time(); }
		$o_tep = new TimeExpressionParser();
		
		$o_tep->setUnixTimestamps($pn_timestamp, $pn_timestamp);
		
		return $o_tep->getText($pa_options);
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * Returns date/time as a localized string for display, subject to the settings in the app/conf/datetime.conf configuration 
	 *
	 * @param int $pn_timestamp Historic timestamp for date/time to localize; if omitted defaults to current date and time.
	 * @param array $pa_options All options supported by TimeExpressionParser::getText() are supported
	 *
	 * @return string Localized date/time expression
	 */
	function caGetLocalizedHistoricDate($pn_timestamp=null, $pa_options=null) {
		if (!$pn_timestamp) { return ''; }
		$o_tep = new TimeExpressionParser();
		
		$o_tep->setHistoricTimestamps($pn_timestamp, $pn_timestamp);
		
		return $o_tep->getText($pa_options);
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * Returns date range as a localized string for display, subject to the settings in the app/conf/datetime.conf configuration 
	 *
	 * @param int $pn_start_timestamp Historic start timestamp for date range to localize
	 * @param int $pn_end_timestamp Historic end timestamp for date range to localize
	 * @param array $pa_options All options supported by TimeExpressionParser::getText() are supported
	 *
	 * @return string Localized date/time expression
	 */
	function caGetLocalizedHistoricDateRange($pn_start_timestamp, $pn_end_timestamp, $pa_options=null) {
		$o_tep = new TimeExpressionParser();
		
		$o_tep->setHistoricTimestamps($pn_start_timestamp, $pn_end_timestamp);
		
		return $o_tep->getText($pa_options);
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * Returns date range as a localized string for display, subject to the settings in the app/conf/datetime.conf configuration 
	 *
	 * @param int $pn_start_timestamp Start of date range, as Unix timestamp
	 * @param int $pn_end_timestamp End of date range, as Unix timestamp
	 * @param array $pa_options All options supported by TimeExpressionParser::getText() are supported
	 *
	 * @return string Localized date range expression
	 */
	function caGetLocalizedDateRange($pn_start_timestamp, $pn_end_timestamp, $pa_options=null) {
		$o_tep = new TimeExpressionParser();
		
		$o_tep->setUnixTimestamps($pn_start_timestamp, $pn_end_timestamp);
		
		return $o_tep->getText($pa_options);
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * Normalize arbitrarily precise date expression to century, decade, year, month or day
	 *
	 * @param string $ps_expression A valid date expression parseable by the TimeExpressionParser class
	 * @param string $ps_normalization Level to normalize to. Valid values are centuries, decades, years, months, days
	 * @param array $pa_options
	 *			delimiter = A string to join multiple values with when returning normalized date range as a string. Default is semicolon followed by space ("; ")
	 *			returnAsArray = If set an array of normalized values will be returned rather than a string. Default is false.
	 * @return mixes The normalized expression. If the expression normalizes to multiple values (eg. a range of years being normalized to months) then the values will be joined with a delimiter and returned as a string unless the "returnAsArray" option is set.
	 */
	function caNormalizeDateRange($ps_expression, $ps_normalization, $pa_options=null) {
		$o_tep = new TimeExpressionParser();
		if ($o_tep->parse($ps_expression)) {
			$va_dates = $o_tep->getHistoricTimestamps();
			$va_vals= $o_tep->normalizeDateRange($va_dates['start'], $va_dates['end'], $ps_normalization);
			
			if (isset($pa_options['returnAsArray']) && $pa_options['returnAsArray']) {
				return $va_vals;
			} else {
				$vs_delimiter = isset($pa_options['returnAsArray']) ? $pa_options['returnAsArray'] : "; ";
				return join($vs_delimiter, $va_vals);
			}
		}
		return null;
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * Returns 
	 *
	 * @param int $pn_start_timestamp Start of date range, as Unix timestamp
	 * @param array $pa_options All options supported by TimeExpressionParser::getText() are supported
	 *
	 * @return string Localized date range expression
	 */
	function caGetDateRangeForTimelineJS($pa_historic_timestamps, $pa_options=null) {
		$o_tep = new TimeExpressionParser();
		
		$va_start = $o_tep->getHistoricDateParts($pa_historic_timestamps[0]);
		$va_end = $o_tep->getHistoricDateParts($pa_historic_timestamps[1]);
		
		//if ($va_start['year'] < 0) { $va_start['year'] = 1900; }
		if ($va_end['year'] >= 2000000) { $va_end['year'] = date("Y"); }
		
		return array(
			'start' => $va_start['year'].','.$va_start['month'].','.$va_start['day'],
			'end' => $va_end['year'].','.$va_end['month'].','.$va_end['day'],
		);
	}
    # ------------------------------------------------------------------------------------------------
    /**
     * Returns date range for calendar display
     *
     * @param int $pn_start_timestamp Start of date range, as Unix timestamp
     * @param array $pa_options All options supported by TimeExpressionParser::getText() are supported
     *
     * @return string Localized date range expression
     */
    function caGetDateRangeForCalendar($pa_historic_timestamps, $pa_options=null) {
        $o_tep = new TimeExpressionParser();

        $va_start = $o_tep->getHistoricDateParts($pa_historic_timestamps[0]);
        $va_end = $o_tep->getHistoricDateParts($pa_historic_timestamps[1]);

        if ($va_start['year'] < 0) { $va_start['year'] = 1900; }
        if ($va_end['year'] >= 2000000) { $va_end['year'] = date("Y"); }

        return array(
            'start'=> $va_start,
            'end' => $va_end,
            'start_iso' => $o_tep->getISODateTime($va_start, 'FULL'),
            'end_iso' => $o_tep->getISODateTime($va_end, 'FULL')
        );
    }
	# ------------------------------------------------------------------------------------------------
	/**
	 * Returns text describing dimensions of object representation
	 *
	 * @param DbResult or ca_object_representations instance $po_rep An object containing representation data. Can be either a DbResult object (ie. a query result) or ca_object_representations instance (an instance representing a row in the ca_object_representation class)
	 * @param string $ps_version the name of the media version to return dimensions information for
	 * @param array $pa_options Array of options, including:
	 *		returnAsArray = if set an array with elements of the dimensions display text is returned
	 
	 * @return mixed Text ready for display describing dimensions of the representation's media. Can be array if 'returnAsArray' option is set.
	 */
	function caGetRepresentationDimensionsForDisplay($po_rep, $ps_version, $pa_options=null) {
		$va_tmp = $po_rep->getMediaInfo('media', $ps_version);
		$va_dimensions = array();
			if (isset($va_tmp['WIDTH']) && isset($va_tmp['HEIGHT'])) {
			if (($vn_w = $va_tmp['WIDTH']) && ($vn_h = $va_tmp['HEIGHT'])) {
				$va_dimensions[] = $va_tmp['WIDTH'].'p x '.$va_tmp['HEIGHT'].'p';
			}
		}
		if (isset($va_tmp['PROPERTIES']['bitdepth']) && ($vn_depth = $va_tmp['PROPERTIES']['bitdepth'])) {
			$va_dimensions[] = intval($vn_depth).' bpp';
		}
		if (isset($va_tmp['PROPERTIES']['colorspace']) && ($vs_colorspace = $va_tmp['PROPERTIES']['colorspace'])) {
			$va_dimensions[] = $vs_colorspace;
		}
		if (isset($va_tmp['PROPERTIES']['resolution']) && is_array($va_resolution = $va_tmp['PROPERTIES']['resolution'])) {
			if (isset($va_resolution['x']) && isset($va_resolution['y']) && $va_resolution['x'] && $va_resolution['y']) {
				// TODO: units for resolution? right now assume pixels per inch
				if ($va_resolution['x'] == $va_resolution['y']) {
					$va_dimensions[] = $va_resolution['x'].'ppi';
				} else {
					$va_dimensions[] = $va_resolution['x'].'x'.$va_resolution['y'].'ppi';
				}
			}
		}
		if (isset($va_tmp['PROPERTIES']['duration']) && ($vn_duration = $va_tmp['PROPERTIES']['duration'])) {
			$va_dimensions[] = caFormatInterval($vn_duration);
		}
		if (isset($va_tmp['PROPERTIES']['pages']) && ($vn_pages = $va_tmp['PROPERTIES']['pages'])) {
			$va_dimensions[] = $vn_pages.' '.(($vn_pages == 1) ? _t('page') : _t('pages'));
		}
		if (!isset($va_tmp['PROPERTIES']['filesize']) || !($vn_filesize = $va_tmp['PROPERTIES']['filesize'])) {
			$vn_filesize = @filesize($po_rep->getMediaPath('media', $ps_version));
		}
		if ($vn_filesize) {
			$va_dimensions[] = caFormatFileSize($vn_filesize);
		}
		
		if(isset($pa_options['returnAsArray']) && $pa_options['returnAsArray']) {
			return $va_dimensions;
		}
		return join('; ', $va_dimensions);
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 *
	 * @return string 
	 */
	function caFormatFileSize($pn_bytes) {
		if ($pn_bytes >= 1073741824) {
			$pn_bytes = number_format($pn_bytes/1073741824, 2).'gb';
		}
		elseif ($pn_bytes >= 1048576) {
			$pn_bytes = number_format($pn_bytes/1048576, 2).'mb';
		} elseif ($pn_bytes >= 1024) {
			$pn_bytes = number_format($pn_bytes/1024, 2).'kb';
		} elseif ($pn_bytes > 1) {
			$pn_bytes = $pn_bytes.'b';
		} elseif ($pn_bytes == 1) {
			$pn_bytes = $pn_bytes.'b';
		} else {
			$pn_bytes = '0b';
		}

		return $pn_bytes;
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 * Converts result set into display labels for relationship lookup
	 *
	 * @param SearchResult $qr_rel_items 
	 * @param BaseModel $pt_rel
	 * @param array $pa_options Array of options, including:
	 *		stripTags = default is false
	 * 		exclude = list of primary key values to omit from returned list
	 *		config = 
	 *		limit = maximum number of items to return; if omitted all items are returned
	 *		inlineCreateMessage = 
	 *		inlineCreateQuery =
	 *		inlineCreateMessageDoesNotExist =
	 *		template = 
	 *		primaryIDs = row_ids for primary rows in related table, keyed by table name; when resolving ambiguous relationships the row_ids will be excluded from consideration. This option is rarely used and exists primarily to take care of a single
	 *						edge case: you are processing a template relative to a self-relationship such as ca_entities_x_entities that includes references to the subject table (ca_entities, in the case of ca_entities_x_entities). There are
	 *						two possible paths to take in this situations; primaryIDs lets you specify which ones you *don't* want to take by row_id. For interstitial editors, the ids will be set to a single id: that of the subject (Eg. ca_entities) row
	 *						from which the interstitial was launched.
	 * @return mixed 
	 */
	function caProcessRelationshipLookupLabel($qr_rel_items, $pt_rel, $pa_options=null) {
		$va_initial_values = array();
		
		$vs_hier_fld 					= $pt_rel->getProperty('HIERARCHY_ID_FLD');
		$vs_idno_fld 					= $pt_rel->getProperty('ID_NUMBERING_ID_FIELD');
		$vs_idno_sort_fld 				= $pt_rel->getProperty('ID_NUMBERING_SORT_FIELD');
		$vs_rel_pk            			= caGetOption('primaryKey', $pa_options, $pt_rel->primaryKey());
 		$vs_rel_table         			= caGetOption('table', $pa_options, $pt_rel->tableName());
		
		$o_config = (!isset($pa_options['config']) || !is_object($pa_options['config'])) ? Configuration::load() : $pa_options['config'];
		
		$pn_limit = 								caGetOption('limit', $pa_options, null);
		$ps_inline_create_message = 				caGetOption('inlineCreateMessage', $pa_options, null);
		$ps_inline_create_does_not_exist_message = 	caGetOption('inlineCreateMessageDoesNotExist', $pa_options, null);
		$ps_inline_create_query = 					caGetOption('inlineCreateQuery', $pa_options, null);
		$ps_inline_create_query_lc = 				mb_strtolower($ps_inline_create_query);
		
		$ps_empty_result_message = 					caGetOption('emptyResultMessage', $pa_options, null);
		$ps_empty_result_query = 					caGetOption('emptyResultQuery', $pa_options, null);
		
		$vs_template =								caGetOption('template', $pa_options, null);
		
		$va_exclude = 								caGetOption('exclude', $pa_options, array(), array('castTo' => 'array'));
		$po_request = 								caGetOption('request', $pa_options, null);
		if(!$po_request) { global $g_request; $po_request = $g_request; }
		
	
		$va_display_format = $o_config->getList("{$vs_rel_table}_lookup_settings");
		$vs_display_delimiter = $o_config->get("{$vs_rel_table}_lookup_delimiter");
		if (!$vs_template) { $vs_template = join($vs_display_delimiter, $va_display_format); }
		
		$va_related_item_info = $va_parent_ids = $va_hierarchy_ids = array();
		$va_items = array();
		
		$o_dm = Datamodel::load();
		$t_rel = $o_dm->getInstanceByTableName($vs_rel_table, true);
		/** @var ca_sets $t_set */
		$t_set = $o_dm->getInstance('ca_sets', true);
		$vs_type_id_fld = method_exists($t_rel, 'getTypeFieldName') ? $t_rel->getTypeFieldName() : null;
		
		$vn_c = 0;
		$vb_include_inline_add_does_not_exist_message = $vb_include_empty_result_message = false;
		$vb_include_inline_add_message = true;
		
		if (is_object($qr_rel_items)) {
			if (!$qr_rel_items->numHits()) {
				if ($ps_inline_create_does_not_exist_message) {
					$vb_include_inline_add_does_not_exist_message = true;
					$vb_include_inline_add_message = false;
				} else {
					if ($ps_empty_result_message) { 
						$vb_include_empty_result_message = true;	
					}
				}
			} else {
				$vs_table = 	$qr_rel_items->tableName();
				$vs_pk = 		$qr_rel_items->primaryKey();
				
				$va_primary_ids = (method_exists($pt_rel, "isSelfRelationship") && ($vb_is_self_rel = $pt_rel->isSelfRelationship())) ? caGetOption("primaryIDs", $pa_options, null) : null;
				
				while($qr_rel_items->nextHit()) {
					$vn_id = $qr_rel_items->get("{$vs_rel_table}.{$vs_rel_pk}");
					if(($qr_rel_items->tableName() == 'ca_sets') && ($po_request instanceof RequestHTTP)) {
						if(!$t_set->haveAccessToSet($po_request->getUserID(), __CA_ACL_EDIT_ACCESS__, $vn_id)) {
							continue;
						}
					}
					if(in_array($vn_id, $va_exclude)) { continue; }
					
					$va_item = array(
						'id' => $vn_id,
						$vs_rel_pk => $vn_id
					);
					
					if ($vs_type_id_fld) {
						$va_item['type_id'] = $qr_rel_items->get("{$vs_rel_table}.{$vs_type_id_fld}");
					}
					
					$va_item['_display'] = caProcessTemplateForIDs($vs_template, $vs_table, array($qr_rel_items->get("{$vs_table}.{$vs_pk}")), array('returnAsArray' => false, 'returnAsLink' => false, 'delimiter' => caGetOption('delimiter', $pa_options, $vs_display_delimiter), 'resolveLinksUsing' => $vs_rel_table, 'primaryIDs' => $va_primary_ids));
					$va_item['label'] = mb_strtolower($qr_rel_items->get("{$vs_table}.preferred_labels"));
					
					$va_items[$vn_id] = $va_item;
					
					$vn_c++;
					if (($pn_limit) && ($pn_limit <= $vn_c)) {
						break;
					}
				}
			}
		}
			
		if (isset($pa_options['relatedItems']) && is_array($pa_options['relatedItems']) && sizeof($pa_options['relatedItems'])) {
			$va_tmp = array();
			foreach ($pa_options['relatedItems'] as $vn_relation_id => $va_relation) {
				$va_items[$va_relation[$vs_rel_pk]]['relation_id'] = $va_relation['relation_id'];
				$va_items[$va_relation[$vs_rel_pk]]['relationship_type_id'] = $va_items[$va_relation[$vs_rel_pk]]['type_id'] = ($va_relation['direction']) ?  $va_relation['direction'].'_'.$va_relation['relationship_type_id'] : $va_relation['relationship_type_id'];
				$va_items[$va_relation[$vs_rel_pk]]['rel_type_id'] = $va_relation['relationship_type_id'];
				$va_items[$va_relation[$vs_rel_pk]]['relationship_typename'] = $va_relation['relationship_typename'];
				$va_items[$va_relation[$vs_rel_pk]]['idno'] = $va_relation[$vs_idno_fld];
				$va_items[$va_relation[$vs_rel_pk]]['idno_sort'] = $va_relation[$vs_idno_sort_fld];
				$va_items[$va_relation[$vs_rel_pk]]['label'] = $va_relation['label'];
				$va_items[$va_relation[$vs_rel_pk]]['direction'] = $va_relation['direction'];
				$va_items[$va_relation[$vs_rel_pk]]['effective_date'] = $va_relation['effective_date'];
				
				if (isset($va_relation['surname'])) {		// pass forename and surname entity label fields to support proper sorting by name
					$va_items[$va_relation[$vs_rel_pk]]['surname'] = $va_relation['surname'];
					$va_items[$va_relation[$vs_rel_pk]]['forename'] = $va_relation['forename'];
				}
				
				if (!isset($va_items[$va_relation[$vs_rel_pk]][$vs_rel_pk]) || !$va_items[$va_relation[$vs_rel_pk]][$vs_rel_pk]) {
					$va_items[$va_relation[$vs_rel_pk]][$vs_rel_pk] = $va_items[$va_relation[$vs_rel_pk]]['id'] = $va_relation[$vs_rel_pk];
				}
				
                if ($vs_template) {
                    $va_items[$va_relation[$vs_rel_pk]]['_display'] = caProcessTemplateForIDs($vs_template, $pt_rel->tableName(), array($va_relation['relation_id'] ? $va_relation['relation_id'] : $va_relation[$vs_pk]), array('returnAsArray' => false, 'returnAsLink' => false, 'delimiter' => caGetOption('delimiter', $pa_options, $vs_display_delimiter), 'resolveLinksUsing' => $vs_rel_table, 'primaryIDs' => $va_primary_ids));
                } else {
                    $va_items[$va_relation[$vs_rel_pk]]['_display'] = $va_items[$va_relation[$vs_rel_pk]]['label'];
                }
				
				$va_tmp[$vn_relation_id] = $va_items[$va_relation[$vs_rel_pk]];
			}
			$va_items = $va_tmp;
			unset($va_tmp);
		}
		
		foreach ($va_items as $va_item) {
			$vn_id = $va_item[$vs_rel_pk];
			if(in_array($vn_id, $va_exclude)) { continue; }
			
			
			$vs_display = $va_item['_display'];
			if (isset($pa_options['stripTags']) && $pa_options['stripTags']) {
				if (preg_match('!(<[A-Za-z0-9]+[ ]+[A-Za-z0-9 ,;\&\-_]*>)!', $vs_display, $va_matches)) {	// convert text in <> to non-tags if the text has only letters, numbers and spaces in it
					array_shift($va_matches);
					foreach($va_matches as $vs_match) {
						$vs_display = str_replace($vs_match, htmlspecialchars($vs_match), $vs_display);
					}
				}
				$vs_display = trim(strip_tags($vs_display));
				
				$vs_label = $va_item['label'];
				if (preg_match('!(<[A-Za-z0-9]+[ ]+[A-Za-z0-9 ,;\&\-_]*>)!', $vs_label, $va_matches)) {	// convert text in <> to non-tags if the text has only letters, numbers and spaces in it
					array_shift($va_matches);
					foreach($va_matches as $vs_match) {
						$vs_label = str_replace($vs_match, htmlspecialchars($vs_match), $vs_label);
					}
				}
				$va_item['label'] = trim(strip_tags($vs_label));
				
			}
			
			$vs_display_lc = mb_strtolower($vs_display);
			if (($vs_display_lc == $ps_inline_create_query_lc) || (isset($va_item['label']) && ($va_item['label'] == $ps_inline_create_query_lc))) {
				$vb_include_inline_add_message = false;
			}

			$po_request = caGetOption('request',$pa_options);
			if($po_request && ca_editor_uis::loadDefaultUI($pt_rel->tableName(),$po_request,$va_item['rel_type_id'])) {
				$va_item['hasInterstitialUI'] = true;
			} else {
				$va_item['hasInterstitialUI'] = false;
			}
			
			$va_initial_values[$va_item['relation_id'] ? (int)$va_item['relation_id'] : $va_item[$vs_rel_pk]] = array_merge(
				$va_item,
				array(
					'label' => $vs_display
				)
			);
		}
		
		if($vb_include_inline_add_message && $ps_inline_create_message) {
			array_push($va_initial_values, 
					array(
						'label' => $ps_inline_create_message,
						'id' => 0,
						$vs_rel_pk => 0,
						'_query' => $ps_inline_create_query
					)
			);
		} elseif ($vb_include_inline_add_does_not_exist_message && $ps_inline_create_does_not_exist_message) {
			array_push($va_initial_values, 
					array(
						'label' => $ps_inline_create_does_not_exist_message,
						'id' => 0,
						$vs_rel_pk => 0,
						'_query' => $ps_inline_create_query
					)
			);
		} elseif ($vb_include_empty_result_message) {
			array_push($va_initial_values, 
				array(
					'label' => $ps_empty_result_message,
					'id' => -1,
					$vs_rel_pk => -1,
					'_query' => $ps_empty_result_query
				)
			);
		}
		
		return $va_initial_values;		
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 *
	 */
	function caGetMediaMimetypeToDisplayClassMap($ps_context) {
		$o_config = Configuration::load();
		$o_media_display_config = Configuration::load(__CA_CONF_DIR__.'/media_display.conf');
		
		if (!is_array($va_context = $o_media_display_config->getAssoc($ps_context))) { return null; }
		
		$va_map = array();
		foreach($va_context as $vs_media_class => $va_media_class_info) {
			if (!is_array($va_mimetypes = $va_media_class_info['mimetypes'])) { continue; }
			
			foreach($va_mimetypes as $vs_mimetype) {
				$va_map[$vs_mimetype] = $vs_media_class;
			}
		}
		return $va_map;
	}
	# ------------------------------------------------------------------------------------------------
	/**
	 *
	 */
	function caObjectsDisplayDownloadLink($po_request) {
		$o_config = Configuration::load();
		$vn_can_download = false;
		if($o_config->get('allow_ca_objects_representation_download')){
			switch($o_config->get('allow_ca_objects_representation_download')){
				case "anyone":
					$vn_can_download = true;
				break;
				# ------------------------------------------
				case "logged_in":
					if ($po_request->isLoggedIn()) {
						$vn_can_download = true;
					}
				break;
				# ------------------------------------------
				case "logged_in_privileged":
					if (($po_request->isLoggedIn()) && ($po_request->user->canDoAction('can_download_media'))) {
						$vn_can_download = true;
					}
				break;
				# ------------------------------------------
			}
		}
		return $vn_can_download;
	}
	# ------------------------------------------------------------------
	/**
	 * Creates links to the appropriate editor (in Providence) or detail page (in Pawtucket) from supplied text and ids.
	 * Used in SearchResult::get() and BundlableLabelableBaseModelWithAttributes::get() to automatically generate links when fetching
	 * information from related tables.
	 *
	 * @param array $pa_text An array of strings to create links for
	 * @param string $ps_table_name The name of the table/record to which the links refer
	 * @param array $pa_row_ids Array of row_ids to link to. Values must correspond by index with those in $pa_text
	 * @param string $ps_class Optional CSS class to apply to links
	 * @param string $ps_target
	 * @param array $pa_options Supported options are:
	 *		requireLinkTags = if set then links are only added when explicitly defined with <l> tags. Default is to make the entire text a link in the absence of <l> tags.
	 * 		addRelParameter =
	 *
	 * @return array A list of HTML links
	 */
	function caCreateLinksFromText($pa_text, $ps_table_name, $pa_row_ids, $ps_class=null, $ps_target=null, $pa_options=null) {
		if (!in_array(__CA_APP_TYPE__, array('PROVIDENCE', 'PAWTUCKET'))) { return $pa_text; }
		if (__CA_APP_TYPE__ == 'PAWTUCKET') {
			$o_config = Configuration::load();
		}

		$pb_add_rel = caGetOption('addRelParameter', $pa_options, false);
		
		$vb_can_handle_target = false;
		if ($ps_target) {
			$o_app_plugin_manager = new ApplicationPluginManager();
			$vb_can_handle_target = $o_app_plugin_manager->hookCanHandleGetAsLinkTarget(array('target' => $ps_target));
		}
		
		// Parse template
		$o_doc = str_get_dom($ps_template);	
		
		$va_links = array();
		
		global $g_request;
		if (!$g_request) { return $pa_text; }
		
		foreach($pa_text as $vn_i => $vs_text) {
			$vs_text = preg_replace("!([A-Za-z0-9]+)='([^']*)'!", "$1=\"$2\"", $vs_text);	
			$va_l_tags = array();
			$o_links = $o_doc('l');
			
			foreach($o_links as $o_link) {
				if (!$o_link) { continue; }
				$vs_html = $o_link->html();
				
				$vs_content = preg_replace("!^<[^\>]+>!", "", $vs_html);
				$vs_content = preg_replace("!<[^\>]+>$!", "", $vs_content);
		
				$va_l_tags[] = array('directive' => html_entity_decode($vs_html), 'content' => $vs_content);	//html_entity_decode
			}
		
			if (sizeof($va_l_tags)) {
				$vs_content = html_entity_decode($vs_text);
				$vs_content = preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, $vs_content); 
				foreach($va_l_tags as $va_l) {
					if ($vb_can_handle_target) {
						$va_params = array('request' => $g_request, 'content' => $va_l['content'], 'table' => $ps_table_name, 'id' => $pa_row_ids[$vn_i], 'classname' => $ps_class, 'target' => $ps_target, 'additionalParameters' => null, 'options' => null);
						$va_params = $o_app_plugin_manager->hookGetAsLink($va_params);
						$vs_link_text = $va_params['tag'];
					} else {
						switch(__CA_APP_TYPE__) {
							case 'PROVIDENCE':
								$vs_link_text= caEditorLink($g_request, $va_l['content'], $ps_class, $ps_table_name, $pa_row_ids[$vn_i], ($pb_add_rel ? array('rel' => true) : array()));
								break;
							case 'PAWTUCKET':
								$vs_link_text= caDetailLink($g_request, $va_l['content'], $ps_class, $ps_table_name, $pa_row_ids[$vn_i]);
								break;
						}					
					}
					
					if ($vs_link_text) {
						$vs_content = str_replace($va_l['directive'], $vs_link_text, $vs_content);
					} else {
						$vs_content = str_replace($va_l['directive'], $va_l['content'], $vs_content);
					}
				}
				$va_links[] = $vs_content;
			} else {
				if (isset($pa_options['requireLinkTags']) && $pa_options['requireLinkTags']) { 
					$va_links[] = $vs_text;
					continue;
				}
				if ($vb_can_handle_target) {
					$va_params = array('request' => $g_request, 'content' => $vs_text, 'table' => $ps_table_name, 'id' => $pa_row_ids[$vn_i], 'classname' => $ps_class, 'target' => $ps_target, 'additionalParameters' => null, 'options' => null);
					$va_params = $o_app_plugin_manager->hookGetAsLink($va_params);
					$va_links[]  = $va_params['tag'];
				} else {
					switch(__CA_APP_TYPE__) {
						case 'PROVIDENCE':
							$va_links[] = ($vs_link = caEditorLink($g_request, $vs_text, $ps_class, $ps_table_name, $pa_row_ids[$vn_i], ($pb_add_rel ? array('rel' => true) : array()))) ? $vs_link : $vs_text;
							break;
						case 'PAWTUCKET':
							$va_links[] = ($vs_link = caDetailLink($g_request, $vs_text, $ps_class, $ps_table_name, $pa_row_ids[$vn_i])) ? $vs_link : $vs_text;
							break;
						default:
							$va_links[] = $vs_text;
							break;
					}
				}
			}
		}
		return $va_links;
	}
	# ------------------------------------------------------------------
	/**
	 * 
	 *
	 * @param BaseModel $pt_subject 
	 * @param string $ps_related_table
	 * @param array $pa_bundle_settings 
	 * @param array $pa_options Supported options are:
	 *		
	 *
	 * @return string
	 */
	function caGetBundleDisplayTemplate($pt_subject, $ps_related_table, $pa_bundle_settings, $pa_options=null) {
		$vs_template = null;
		if(strlen(trim($pa_bundle_settings['display_template']))) {
			$vs_template = trim($pa_bundle_settings['display_template']);
		} 
		
		// If no display_template set try to get a default out of the app.conf file
		if (!$vs_template) {
			if (is_array($va_lookup_settings = $pt_subject->getAppConfig()->getList("{$ps_related_table}_lookup_settings"))) {
				if (!($vs_lookup_delimiter = $pt_subject->getAppConfig()->get("{$ps_related_table}_lookup_delimiter"))) { $vs_lookup_delimiter = ''; }
				$vs_template = join($vs_lookup_delimiter, $va_lookup_settings);
			}
		}
		
		// If no app.conf default then just show preferred_labels
		if (!$vs_template) {
			$vs_template = "^preferred_labels";
		}
		return $vs_template;
	}
	# ---------------------------------------
	/**
	 * Generates show/hide control HTML for bundles
	 *
	 * @param RequestHTTP $po_request
	 * @param string $ps_id_prefix
	 * @param array $pa_settings bundle placement option array
	 * @param bool $pb_has_value
	 * @param string $ps_preview_init string to initialize bundle preview content section with
	 * 
	 * @return string HTML implementing the control
	 */
	function caEditorBundleShowHideControl($po_request, $ps_id_prefix, $pa_settings=null, $pb_has_value=false, $ps_preview_init="&nbsp;") {
		$vs_expand_collapse_value = caGetOption('expand_collapse_value', $pa_settings, 'dont_force');
		$vs_expand_collapse_no_value = caGetOption('expand_collapse_no_value', $pa_settings, 'dont_force');
		$vs_expand_collapse = caGetOption('expand_collapse', $pa_settings, false);

		if(!$vs_expand_collapse) {
			$vs_expand_collapse = ($pb_has_value ? $vs_expand_collapse_value : $vs_expand_collapse_no_value);
		}

		switch(strtolower($vs_expand_collapse)) {
			case 'collapse':
				$vs_force = 'closed';
				break;
			case 'expand':
				$vs_force = 'open';
				break;
			case 'dont_force':
			default:
				$vs_force = '';
				break;
		}

		$ps_preview_id_prefix = preg_replace("/[0-9]+\_rel/", "", $ps_id_prefix);

		$vs_buf  = "<span class='bundleContentPreview' id='{$ps_preview_id_prefix}_BundleContentPreview'>{$ps_preview_init}</span>";
		$vs_buf .= "<span style='position: absolute; top: 2px; right: 7px;'>";
		$vs_buf .= "<a href='#' onclick='caBundleVisibilityManager.toggle(\"{$ps_id_prefix}\");  return false;'><img src=\"".$po_request->getThemeUrlPath()."/graphics/arrows/expand.jpg\" border=\"0\" id=\"{$ps_id_prefix}VisToggleButton\"/></a>";
		$vs_buf .= "</span>\n";	
		$vs_buf .= "<script type='text/javascript'>jQuery(document).ready(function() { caBundleVisibilityManager.registerBundle('{$ps_id_prefix}', '{$vs_force}'); }); </script>";
		
		return $vs_buf;
	}
	# ---------------------------------------
	/**
	 * Generates metadata dictionary control HTML for bundles
	 *
	 * @param RequestHTTP $po_request
	 * @param string $ps_id_prefix
	 * @param array $pa_settings
	 * 
	 * @return string HTML implementing the control
	 */
	function caEditorBundleMetadataDictionary($po_request, $ps_id_prefix, $pa_settings) {
		global $g_ui_locale;
		
		if (!($vs_definition = trim(caGetOption($g_ui_locale, $pa_settings['definition'], null)))) { return ''; }
		
		$vs_buf = '';
		$vs_buf .= "<span style='position: absolute; top: 2px; right: 26px;'>";
		$vs_buf .= "<a href='#' class='caMetadataDictionaryDefinitionToggle' onclick='caBundleVisibilityManager.toggleDictionaryEntry(\"{$ps_id_prefix}\");  return false;'><img src=\"".$po_request->getThemeUrlPath()."/graphics/icons/info.png\" border=\"0\" id=\"{$ps_id_prefix}MetadataDictionaryToggleButton\"/></a>";
		$vs_buf .= "</span>\n";	
		
		$vs_buf .= "<div id='{$ps_id_prefix}DictionaryEntry' class='caMetadataDictionaryDefinition'>{$vs_definition}</div>";
		$vs_buf .= "<script type='text/javascript'>jQuery(document).ready(function() { caBundleVisibilityManager.registerBundle('{$ps_id_prefix}'); }); </script>";	
		
		return $vs_buf;
	}
	# ---------------------------------------
	/**
	 * Generates sort control HTML for relation bundles (Eg. ca_entities, ca_occurrences)
	 *
	 * @param RequestHTTP $po_request
	 * @param string $ps_id_prefix
	 * @param array $pa_settings
	 * 
	 * @return string HTML implementing the control
	 */
	function caEditorBundleSortControls($po_request, $ps_id_prefix, $pa_settings) {
		$vs_buf = "	<div class=\"caItemListSortControlContainer\">
		<div class=\"caItemListSortControlTrigger\" id=\"{$ps_id_prefix}caItemListSortControlTrigger\">
			"._t('Sort by')." <img src=\"".$po_request->getThemeUrlPath()."/graphics/icons/bg.gif\" alt=\"Sort\"/>
		</div>
		<div class=\"caItemListSortControls\" id=\"{$ps_id_prefix}caItemListSortControls\">
			<ul>
				<li><a href=\"#\" onclick=\"caRelationBundle{$ps_id_prefix}.sort('name'); return false;\" class=\"caItemListSortControl\">"._t('name')."</a><br/></li>
				<li><a href=\"#\" onclick=\"caRelationBundle{$ps_id_prefix}.sort('idno'); return false;\" class=\"caItemListSortControl\">"._t('idno')."</a><br/></li>
				<li><a href=\"#\" onclick=\"caRelationBundle{$ps_id_prefix}.sort('type'); return false;\" class=\"caItemListSortControl\">"._t('type')."</a><br/></li>
				<li><a href=\"#\" onclick=\"caRelationBundle{$ps_id_prefix}.sort('entry'); return false;\" class=\"caItemListSortControl\">"._t('entry')."</a><br/></li>
			</ul>
		</div>
	</div>";
		
		return $vs_buf;
	}
	# ---------------------------------------
	/**
	 * 
	 */
	function caProcessBottomLineTemplate($po_request, $pa_placement, $pr_res, $pa_options=null) {
		global $g_ui_units_pref, $g_ui_locale;
		
		if (!isset($pa_placement['settings']['bottom_line']) || !$pa_placement['settings']['bottom_line']) { return null; }
		if (!$pr_res) { return null; }
		
		$vs_template = $pa_placement['settings']['bottom_line'];
		$vs_bundle_name = $pa_placement['bundle_name'];
		
		$pn_page_start = caGetOption('pageStart', $pa_options, 0);
		$pn_page_end = caGetOption('pageEnd', $pa_options, $pr_res->numHits());
		
		if (($vn_current_index = $pr_res->currentIndex()) < 0) { $vn_current_index = 0; }
		$pr_res->seek(0);
		
		$o_dm = Datamodel::load();
		
		$va_tmp = explode(".", $vs_bundle_name);
		if (!($t_instance = $o_dm->getInstanceByTableName($va_tmp[0], true))) {
			return null;
		}

		$vn_datatype = ca_metadata_elements::getElementDatatype($va_tmp[1]);
		if (is_null($vn_datatype)) { return null; }
		
		if (!($vs_user_currency = $po_request->user ? $po_request->user->getPreference('currency') : 'USD')) {
			$vs_user_currency = 'USD';
		}
	
		// Parse out tags and optional sub-elements from template
		//		we have to pull each sub-element separately
		//
		//		Ex. 	^SUM:valuation = sum of "valuation" sub-element
		//				^SUM = sum of primary value in non-container element
		if (!preg_match("!(\^[A-Z]+[\:]{0,1}[A-Za-z0-9\_\-]*)!", $vs_template, $va_tags)) {
			return $vs_template;
		}

		$va_tags_to_process = array();
		$va_subelements_to_process = array();
		
		if ($vn_datatype == 0) {	// container
			foreach($va_tags as $vs_raw_tag) {
				$va_tmp = explode(":", $vs_raw_tag);
				$vs_tag = $va_tmp[0];
				if (sizeof($va_tmp) == 2) {
					$vs_subelement = $va_tmp[1];
				} else {
					continue;
				}
			
				$va_tags_to_process[$vs_raw_tag] = true;
				$va_subelements_to_process["{$vs_bundle_name}.{$vs_subelement}"] = ca_metadata_elements::getElementDatatype($vs_subelement);
			}
		} else {
			$va_tmp = explode(".", $vs_bundle_name);
			if (sizeof($va_tmp) == 2) { $vs_bundle_name .= ".".array_pop($va_tmp); }
			$va_subelements_to_process = array($vs_bundle_name => $vn_datatype);
		}
	
		$vn_c = 0;
		$vn_page_len = 0;
		$vb_has_timecode = false;
		
		$vn_min = $vn_max = null;
		$vn_page_min = $vn_page_max = null;
		
		$va_tag_values = array();
		while($pr_res->nextHit()) {
			foreach($va_subelements_to_process as $vs_subelement => $vn_subelement_datatype) {
				if (!is_array($va_tag_values[$vs_subelement])) {
					$va_tag_values[$vs_subelement]['SUM'] = 0;
					$va_tag_values[$vs_subelement]['PAGESUM'] = 0;
					$va_tag_values[$vs_subelement]['MIN'] = null;
					$va_tag_values[$vs_subelement]['PAGEMIN'] = null;
					$va_tag_values[$vs_subelement]['MAX'] = null;
					$va_tag_values[$vs_subelement]['PAGEMAX'] = null;
					$va_tag_values[$vs_subelement]['AVG'] = 0;
					$va_tag_values[$vs_subelement]['PAGEAVG'] = 0;
				}
			
				switch($vn_subelement_datatype) {
					case 2:		// date range
				
						$vs_value = $pr_res->get($vs_subelement);
						break;
					case 6:		// currency
						$va_values = $pr_res->get($vs_subelement, array('returnAsDecimalWithCurrencySpecifier' => true, 'returnAsArray' => true));
						
						if(is_array($va_values)) {
							foreach($va_values as $vs_value) {
								$vn_value = (float)caConvertCurrencyValue($vs_value, $vs_user_currency, array('numericValue' => true));
						
								$va_tag_values[$vs_subelement]['SUM'] += $vn_value;
								if (is_null($va_tag_values[$vs_subelement]['MIN']) || ($vn_value < $va_tag_values[$vs_subelement]['MIN'])) { $va_tag_values[$vs_subelement]['MIN'] = $vn_value; }
								if (is_null($va_tag_values[$vs_subelement]['MAX']) || ($vn_value > $va_tag_values[$vs_subelement]['MAX'])) { $va_tag_values[$vs_subelement]['MAX'] = $vn_value; }
					
								if (($vn_c >= $pn_page_start) && ($vn_c <= $pn_page_end)) {
									$va_tag_values[$vs_subelement]['PAGESUM'] += $vn_value;
									if (is_null($va_tag_values[$vs_subelement]['PAGEMIN']) || ($vn_value < $va_tag_values[$vs_subelement]['PAGEMIN'])) { $va_tag_values[$vs_subelement]['PAGEMIN'] = $vn_value; }
									if (is_null($va_tag_values[$vs_subelement]['PAGEMAX']) || ($vn_value > $va_tag_values[$vs_subelement]['PAGEMAX'])) { $va_tag_values[$vs_subelement]['PAGEMAX'] = $vn_value; }
									$vn_page_len++;
								}
							}
						}
						break;
					case 8:		// length
					case 9:		// weight
						$va_values = $pr_res->get($vs_subelement, array('returnAsDecimalMetric' => true, 'returnAsArray' => true));
						
						if(is_array($va_values)) {
							foreach($va_values as $vs_value) {
								$vn_value = (float)$vs_value;
								$va_tag_values[$vs_subelement]['SUM'] += $vn_value;
								if (is_null($va_tag_values[$vs_subelement]['MIN']) || ($vn_value < $va_tag_values[$vs_subelement]['MIN'])) { $va_tag_values[$vs_subelement]['MIN'] = $vn_value; }
								if (is_null($va_tag_values[$vs_subelement]['MAX']) || ($vn_value > $va_tag_values[$vs_subelement]['MAX'])) { $va_tag_values[$vs_subelement]['MAX'] = $vn_value; }
					
								if (($vn_c >= $pn_page_start) && ($vn_c <= $pn_page_end)) {
									$va_tag_values[$vs_subelement]['PAGESUM'] += $vn_value;
									if (is_null($va_tag_values[$vs_subelement]['PAGEMIN']) || ($vn_value < $va_tag_values[$vs_subelement]['PAGEMIN'])) { $va_tag_values[$vs_subelement]['PAGEMIN'] = $vn_value; }
									if (is_null($va_tag_values[$vs_subelement]['PAGEMAX']) || ($vn_value > $va_tag_values[$vs_subelement]['PAGEMAX'])) { $va_tag_values[$vs_subelement]['PAGEMAX'] = $vn_value; }
									$vn_page_len++;
								}
							}
						}
						break;
					case 10:	// timecode
						$va_values = $pr_res->get($vs_subelement, array('returnAsDecimal' => true, 'returnAsArray' => true));
						
						if(is_array($va_values)) {
							foreach($va_values as $vn_value) {
								$va_tag_values[$vs_subelement]['SUM'] += $vn_value;
								if (is_null($vn_min) || ($vn_value < $vn_min)) { $vn_min = $vn_value; }
								if (is_null($vn_max) || ($vn_value > $vn_max)) { $vn_max = $vn_value; }
					
								if (($vn_c >= $pn_page_start) && ($vn_c <= $pn_page_end)) {
									$va_tag_values[$vs_subelement]['PAGESUM'] += $vn_value;
									if (is_null($vn_page_min) || ($vn_value < $vn_page_min)) { $vn_page_min = $vn_value; }
									if (is_null($vn_page_max) || ($vn_value > $vn_page_max)) { $vn_page_max = $vn_value; }
									$vn_page_len++;
								}
							}
						}
						$vb_has_timecode = true;
						break;
					case 11:	// integer
					case 12:	// numeric (decimal)
						$va_values = $pr_res->get($vs_subelement, array('returnAsArray' => true));
						
						if(is_array($va_values)) {
							foreach($va_values as $vs_value) {
								$vn_value = (float)$vs_value;
								$va_tag_values[$vs_subelement]['SUM'] += $vn_value;
								if (is_null($vn_min) || ($vn_value < $vn_min)) { $vn_min = $vn_value; }
								if (is_null($vn_max) || ($vn_value > $vn_max)) { $vn_max = $vn_value; }
					
								if (($vn_c >= $pn_page_start) && ($vn_c <= $pn_page_end)) {
									$va_tag_values[$vs_subelement]['PAGESUM'] += $vn_value;
									if (is_null($vn_page_min) || ($vn_value < $vn_page_min)) { $vn_page_min = $vn_value; }
									if (is_null($vn_page_max) || ($vn_value > $vn_page_max)) { $vn_page_max = $vn_value; }
									$vn_page_len++;
								}
							}
						}
						break;
					default:
						break(2);
				}
			}			
			$vn_c++;
		}
		
		if ($vb_has_timecode) {			
			$o_tcp = new TimecodeParser();
			$o_config = Configuration::load();
			if (!($vs_timecode_format = $o_config->get('timecode_output_format'))) { $vs_timecode_format = 'HOURS_MINUTES_SECONDS'; }
		}
		
		// Post processing
		foreach($va_subelements_to_process as $vs_subelement => $vn_subelement_datatype) {
			switch($vn_subelement_datatype) {
				case 6:		// currency
					$va_tag_values[$vs_subelement]['PAGEAVG'] = ($vn_page_len > 0) ? sprintf("%1.2f", $va_tag_values[$vs_subelement]['PAGESUM']/$vn_page_len) : 0;
					$va_tag_values[$vs_subelement]['AVG'] = ($vn_c > 0) ? sprintf("%1.2f", $va_tag_values[$vs_subelement]['SUM']/$vn_c) : "0.00";
					
					foreach($va_tag_values[$vs_subelement] as $vs_tag => $vn_val) {
						$va_tag_values[$vs_subelement][$vs_tag] = "{$vs_user_currency} ".$va_tag_values[$vs_subelement][$vs_tag];
					}
					
					break;
				case 8:		// length
					$va_tag_values[$vs_subelement]['PAGEAVG'] = ($vn_page_len > 0) ? sprintf("%1.2f", $va_tag_values[$vs_subelement]['PAGESUM']/$vn_page_len) : 0;
					$va_tag_values[$vs_subelement]['AVG'] = ($vn_c > 0) ? sprintf("%1.2f", $va_tag_values[$vs_subelement]['SUM']/$vn_c) : "0.00";
					
					foreach($va_tag_values[$vs_subelement] as $vs_tag => $vn_val) {
						$vo_measurement = new Zend_Measure_Length((float)$vn_val, 'METER', $g_ui_locale);
						$va_tag_values[$vs_subelement][$vs_tag] = $vo_measurement->convertTo(($g_ui_units_pref == 'metric') ? Zend_Measure_Length::METER :  Zend_Measure_Length::FEET, 4);
					}
					
					break;
				case 9:		// weight
					$va_tag_values[$vs_subelement]['PAGEAVG'] = ($vn_page_len > 0) ? sprintf("%1.2f", $va_tag_values[$vs_subelement]['PAGESUM']/$vn_page_len) : 0;
					$va_tag_values[$vs_subelement]['AVG'] = ($vn_c > 0) ? sprintf("%1.2f", $va_tag_values[$vs_subelement]['SUM']/$vn_c) : "0.00";
					
					foreach($va_tag_values[$vs_subelement] as $vs_tag => $vn_val) {
						$vo_measurement = new Zend_Measure_Length((float)$vn_val, 'KILOGRAM', $g_ui_locale);
						$va_tag_values[$vs_subelement][$vs_tag] = $vo_measurement->convertTo(($g_ui_units_pref == 'metric') ? Zend_Measure_Weight::KILOGRAM :  Zend_Measure_Weight::POUND, 4);
					}
					
					break;
				case 10:	// timecode
					$va_tag_values[$vs_subelement]['PAGEAVG'] = ($vn_page_len > 0) ? sprintf("%1.2f", $va_tag_values[$vs_subelement]['PAGESUM']/$vn_page_len) : 0;
					$va_tag_values[$vs_subelement]['AVG'] = ($vn_c > 0) ? sprintf("%1.2f", $va_tag_values[$vs_subelement]['SUM']/$vn_c) : 0;
					
					foreach($va_tag_values[$vs_subelement] as $vs_tag => $vn_val) {
						if (!$vb_has_timecode) { $va_tag_values[$vs_subelement][$vs_tag] = ''; continue; }
						$o_tcp->setParsedValueInSeconds($vn_val);
						$va_tag_values[$vs_subelement][$vs_tag] = $o_tcp->getText($vs_timecode_format); 
					}
					
					break;
				case 11:	// integer
					foreach($va_tag_values[$vs_subelement] as $vs_tag => $vn_val) {
						$va_tag_values[$vs_subelement][$vs_tag] = (int)$va_tag_values[$vs_subelement][$vs_tag];
					}
					
					break;
				case 12:	// numeric (decimal)
					foreach($va_tag_values[$vs_subelement] as $vs_tag => $vn_val) {
						$va_tag_values[$vs_subelement][$vs_tag] = (float)$va_tag_values[$vs_subelement][$vs_tag];
					}
					
					break;
			}
		}
		
		// Restore current position of search result
		$pr_res->seek($vn_current_index);
		
		foreach($va_tag_values as $vs_subelement => $va_tag_data) {
			foreach($va_tag_data as $vs_tag => $vs_tag_value) {
				if ($vs_subelement == $vs_bundle_name) {
					$vs_template = str_replace("^{$vs_tag}", $vs_tag_value, $vs_template);
				} else {
					$va_tmp = explode(".", $vs_subelement);
					$vs_template = str_replace("^{$vs_tag}:".array_pop($va_tmp), $vs_tag_value, $vs_template);
				}
			}
		}
		
		return $vs_template;
	}
	# ------------------------------------------------------
 	/**
 	 * Return rendered HTML for media viewer for both re
 	 *
 	 * @param RequestHTTP $po_request
 	 * @param array $pa_options
 	 * @param array $pa_additional_display_options
 	 * @return string HTML output
 	 */
 	function caGetMediaViewerHTMLBundle($po_request, $pa_options=null, $pa_additional_display_options=null) {
 		$va_access_values = (isset($pa_options['access']) && is_array($pa_options['access'])) ? $pa_options['access'] : array();	
 		$vs_display_type = (isset($pa_options['display']) && $pa_options['display']) ? $pa_options['display'] : 'media_overlay';	
 		$vs_container_dom_id = (isset($pa_options['containerID']) && $pa_options['containerID']) ? $pa_options['containerID'] : null;	
 		
 		$t_subject = (isset($pa_options['t_subject']) && $pa_options['t_subject']) ? $pa_options['t_subject'] : null;
 		
 		$t_rep = (isset($pa_options['t_representation']) && $pa_options['t_representation']) ? $pa_options['t_representation'] : null;
 		$vn_representation_id = $t_rep ? $t_rep->getPrimaryKey() : null;
 		$t_attr_val = (isset($pa_options['t_attribute_value']) && $pa_options['t_attribute_value']) ? $pa_options['t_attribute_value'] : null;
 		$vn_value_id = $t_attr_val ? $t_attr_val->getPrimaryKey() : null;
 		
 		$vn_item_id = (isset($pa_options['item_id']) && $pa_options['item_id']) ? $pa_options['item_id'] : null;
 		
 		$vb_media_editor = (isset($pa_options['mediaEditor']) && $pa_options['mediaEditor']) ? true : false;
 		$vb_no_controls = (isset($pa_options['noControls']) && $pa_options['noControls']) ? true : false;
 		
 		$vn_item_id = (isset($pa_options['item_id']) && $pa_options['item_id']) ? $pa_options['item_id'] : null;
 		
 		$vn_subject_id = $t_subject ? $t_subject->getPrimaryKey() : null;
 		
 		if(!$vn_value_id && !$vn_representation_id) {
 			$t_rep->load($t_subject->getPrimaryRepresentationID(array('checkAccess' => $va_access_values)));
 		}
 		
		$o_view = new View($po_request, $po_request->getViewsDirectoryPath().'/bundles/');
	
		require_once(__CA_MODELS_DIR__.'/ca_set_items.php');	
		$t_set_item = new ca_set_items();
		if ($vn_item_id) { $t_set_item->load($vn_item_id); }
		
		$o_view->setVar('containerID', $vs_container_dom_id);
		
 		$o_view->setVar('t_subject', $t_subject);
		$o_view->setVar('t_representation', $t_rep);
 		if ($vn_representation_id && ((!sizeof($va_access_values) || in_array($t_rep->get('access'), $va_access_values)))) { 		// check rep access
			$va_rep_display_info = caGetMediaDisplayInfo($vs_display_type, $t_rep->getMediaInfo('media', 'INPUT', 'MIMETYPE'));
			$va_rep_display_info['poster_frame_url'] = $t_rep->getMediaUrl('media', $va_rep_display_info['poster_frame_version']);
			
			$o_view->setVar('num_multifiles', $t_rep->numFiles());
				
 			if (isset($pa_options['use_book_viewer'])) {
 				$va_rep_display_info['use_book_viewer'] = (bool)$pa_options['use_book_viewer'];
 			}		
			$o_view->setVar('display_type', $vs_display_type);
			
			if (is_array($pa_additional_display_options)) { $va_rep_display_info = array_merge($va_rep_display_info, $pa_additional_display_options); }
			$o_view->setVar('display_options', $va_rep_display_info);
			$o_view->setVar('representation_id', $vn_representation_id);
			$o_view->setVar('versions', $va_versions = $t_rep->getMediaVersions('media'));
			
			$t_media = new Media();
			$o_view->setVar('version_type', $t_media->getMimetypeTypename($t_rep->getMediaInfo('media', 'original', 'MIMETYPE')));
		
			if ($vn_subject_id) { 
				$o_view->setVar('reps', $va_reps = $t_subject->getRepresentations(array('icon'), null, array("return_with_access" => $va_access_values)));
				
				$vn_next_rep = $vn_prev_rep = null;
				
				$va_rep_list = array_values($va_reps);
				foreach($va_rep_list as $vn_i => $va_rep) {
					if ($va_rep['representation_id'] == $vn_representation_id) {
						if (isset($va_rep_list[$vn_i - 1])) {
							$vn_prev_rep = $va_rep_list[$vn_i - 1]['representation_id'];
						}
						if (isset($va_rep_list[$vn_i + 1])) {
							$vn_next_rep = $va_rep_list[$vn_i + 1]['representation_id'];
						}
						$o_view->setVar('representation_index', $vn_i + 1);
					}
				}
				$o_view->setVar('previous_representation_id', $vn_prev_rep);
				$o_view->setVar('next_representation_id', $vn_next_rep);
			}	
			$ps_version 	= $po_request->getParameter('version', pString);
			if (!in_array($ps_version, $va_versions)) { 
				if (!($ps_version = $va_rep_display_info['display_version'])) { $ps_version = null; }
			}
			$o_view->setVar('version', $ps_version);
			$o_view->setVar('version_info', $t_rep->getMediaInfo('media', $ps_version));
			
 			$o_view->setVar('t_set_item', $t_set_item);
 			$o_view->setVar('use_media_editor', $vb_media_editor);
 			$o_view->setVar('noControls', $vb_no_controls);
		} else {
			//$t_attr = new ca_attributes($t_attr_val->get('attribute_id'));
			$t_attr_val->useBlobAsMediaField(true);
			
			$va_rep_display_info = caGetMediaDisplayInfo($vs_display_type, $t_attr_val->getMediaInfo('value_blob', 'INPUT', 'MIMETYPE'));
			$va_rep_display_info['poster_frame_url'] = $t_attr_val->getMediaUrl('value_blob', $va_rep_display_info['poster_frame_version']);
			
			$o_view->setVar('num_multifiles', $t_attr_val->numFiles());
				
 			if (isset($pa_options['use_book_viewer'])) {
 				$va_rep_display_info['use_book_viewer'] = (bool)$pa_options['use_book_viewer'];
 			}		
			$o_view->setVar('display_type', $vs_display_type);
			
			if (is_array($pa_additional_display_options)) { $va_rep_display_info = array_merge($va_rep_display_info, $pa_additional_display_options); }
			$o_view->setVar('display_options', $va_rep_display_info);
			$o_view->setVar('representation_id', $vn_representation_id);
			$o_view->setVar('t_attribute_value', $t_attr_val);
			$o_view->setVar('versions', $va_versions = $t_attr_val->getMediaVersions('value_blob'));
			
			$t_media = new Media();
			$o_view->setVar('version_type', $t_media->getMimetypeTypename($t_attr_val->getMediaInfo('value_blob', 'original', 'MIMETYPE')));
			
			$o_view->setVar('reps', array());
				
			$ps_version 	= $po_request->getParameter('version', pString);
			if (!in_array($ps_version, $va_versions)) { 
				if (!($ps_version = $va_rep_display_info['display_version'])) { $ps_version = null; }
			}
			$o_view->setVar('version', $ps_version);
			$o_view->setVar('version_info', $t_attr_val->getMediaInfo('value_blob', $ps_version));
			
 			$o_view->setVar('t_subject', $t_subject);
 			$o_view->setVar('t_set_item', $t_set_item);
 			$o_view->setVar('use_media_editor', $vb_media_editor);
 			$o_view->setVar('noControls', $vb_no_controls);
		}
		return $o_view->render('representation_viewer_html.php');
 	}
	# ------------------------------------------------------------------
	/**
	 * Get Javascript code that generates Tooltips for a list of elements
	 * @param array $pa_tooltips List of tooltips to set as selector=>text map
	 * @param string $ps_class CSS class to use for tooltips
	 * @return string
	 */
	function caGetTooltipJS($pa_tooltips, $ps_class = 'tooltipFormat') {
		$vs_buf = "<script type='text/javascript'>\njQuery(document).ready(function() {\n";

		foreach($pa_tooltips as $vs_element_selector => $vs_tooltip_text) {
			$vs_buf .= "jQuery('{$vs_element_selector}').attr('title', '".preg_replace('![\n\r]{1}!', ' ', addslashes($vs_tooltip_text))."').tooltip({ tooltipClass: '{$ps_class}', show: 150, hide: 150});\n";
		}

		$vs_buf .= "});\n</script>\n";

		return $vs_buf;
	}
	# ------------------------------------------------------------------
	/**
	 * Get bundle preview for a relationship bundle
	 * @param array $pa_initial_values
	 * @param string $ps_delimiter
	 * @return string
	 */
	function caGetBundlePreviewForRelationshipBundle($pa_initial_values, $ps_delimiter='; ') {
		if(!is_array($pa_initial_values) || sizeof($pa_initial_values) == 0) {
			return '""';
		}

		// it's very unlikely that the preview will fit more then 10 items
		if(sizeof($pa_initial_values) > 10) {
			$pa_initial_values = array_slice($pa_initial_values, 0, 10);
		}

		$va_previews = array();
		foreach($pa_initial_values as $va_item) {
			$va_previews[] = trim($va_item['_display']);
		}

		return caEscapeForBundlePreview(join($ps_delimiter, $va_previews));
	}
	# ------------------------------------------------------------------
