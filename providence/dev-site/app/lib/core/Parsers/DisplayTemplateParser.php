<?php
/** ---------------------------------------------------------------------
 * app/lib/core/Parsers/DisplayTemplateParser.php : 
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2015-2016 Whirl-i-Gig
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
 * @subpackage Parsers
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License version 3
 * 
 * ----------------------------------------------------------------------
 */
 
require_once(__CA_LIB_DIR__.'/core/Parsers/ganon.php');

 
class DisplayTemplateparser {
	# -------------------------------------------------------------------
	/**
	 *
	 */
	static $template_cache = null; 
	
	/**
	 *
	 */
	static $value_cache = null;
	
	/**
	 *
	 */
	static $value_count_cache = null;
	
	# -------------------------------------------------------------------
	/**
     *  Statically evaluate an expression, returning the value
     */
	static public function evaluate($ps_template, $pm_tablename_or_num, $pa_row_ids, $pa_options=null) {
		return DisplayTemplateParser::process($ps_template, $pm_tablename_or_num, $pa_row_ids, $pa_options);
	}
	# -------------------------------------------------------------------
	/**
	 *
	 */
	static public function prefetchAllRelatedIDs($po_nodes, $ps_tablename, $pa_row_ids, $pa_options=null) {
		foreach($po_nodes as $vn_index => $o_node) {
			switch($vs_tag = $o_node->tag) {
				case 'unit':
					if ($vs_relative_to = $o_node->relativeTo) { 
						$va_get_options = ['returnAsArray' => true, 'checkAccess' => caGetOption('checkAccess', $pa_options, null)];
				
						$va_get_options['restrictToTypes'] = DisplayTemplateParser::_getCodesFromAttribute($o_node, ['attribute' => 'restrictToTypes']); 
						$va_get_options['excludeTypes'] = DisplayTemplateParser::_getCodesFromAttribute($o_node, ['attribute' => 'excludeTypes']); 
						$va_get_options['restrictToRelationshipTypes'] = DisplayTemplateParser::_getCodesFromAttribute($o_node, ['attribute' => 'restrictToRelationshipTypes']);
						$va_get_options['excludeRelationshipTypes'] = DisplayTemplateParser::_getCodesFromAttribute($o_node, ['attribute' => 'excludeRelationshipTypes']);

						$va_search_result_opts = array();
						if($o_node->includeNonPrimaryRepresentations) {
							$va_search_result_opts['filterNonPrimaryRepresentations'] = false;
						}
					
						if ($o_node->sort) {
							$va_get_options['sort'] = preg_split('![ ,;]+!', $o_node->sort);
							$va_get_options['sortDirection'] = $o_node->sortDirection;
						}
						
						try {
							$va_row_ids = DisplayTemplateParser::_getRelativeIDsForRowIDs($ps_tablename, $vs_relative_to, $pa_row_ids, 'related', $va_get_options);
				
							if (!sizeof($va_row_ids)) { return; }
							$qr_res = caMakeSearchResult($ps_tablename, $va_row_ids, $va_search_result_opts);
							if (!$qr_res) { return; }
						
						
						
							$va_cache_opts = $qr_res->get($vs_relative_to.".".$qr_res->primaryKey(), array_merge($va_get_options, ['returnCacheOptions' => true]));
						
							$qr_res->prefetchRelated($vs_relative_to, 0, $qr_res->getOption('prefetch'), $va_cache_opts);
						
							if ($o_node->children) {
								DisplayTemplateParser::prefetchAllRelatedIDs($o_node->children, $vs_relative_to, $va_row_ids, $pa_options);
							}
						} catch (Exception $e) {
							// prefetch failed
						}
					}
					break;
			}
		}
	}
	# -------------------------------------------------------------------
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
	 *		requireLinkTags = if set then links are only added when explicitly defined with <l> tags. [Default is true]
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
	 *		forceValues = Optional array of values indexed by placeholder without caret (eg. ca_objects.idno) and row_id. When present these values will be used in place of the placeholders, rather than whatever value normal processing would result in. [Default is null]
	 *		aggregateUnique = Remove duplicate values. If set then array of evaluated templates may not correspond one-to-one with the original list of row_ids set in $pa_row_ids. [Default is false]
	 *
	 * @return mixed Output of processed templates
	 *
	 * TODO: sort and sortDirection are not currently supported! They are ignored for the time being
	 */
	static public function process($ps_template, $pm_tablename_or_num, array $pa_row_ids, array $pa_options=null) {
		// Set up options
			foreach(array(
				'request', 
				'template',	// we pass through options to get() and don't want templates 
				'restrict_to_relationship_types', 'restrictToRelationshipTypes', 'excludeRelationshipTypes',
				'useLocaleCodes') as $vs_k) {
				unset($pa_options[$vs_k]);
			}
			if (!isset($pa_options['convertCodesToDisplayText'])) { $pa_options['convertCodesToDisplayText'] = true; }
			$pb_return_as_array = (bool)caGetOption('returnAsArray', $pa_options, false);
			unset($pa_options['returnAsArray']);
		
			if (($pa_sort = caGetOption('sort', $pa_options, null)) && !is_array($pa_sort)) {
				$pa_sort = explode(";", $pa_sort);
			}
			$ps_sort_direction = caGetOption('sortDirection', $pa_options, null, array('forceUppercase' => true));
			if(!in_array($ps_sort_direction, array('ASC', 'DESC'))) { $ps_sort_direction = 'ASC'; }
		
			$ps_delimiter = caGetOption('delimiter', $pa_options, '; ');
			
			$pb_include_blanks = caGetOption('includeBlankValuesInArray', $pa_options, false);
			$pb_include_blanks_for_prefetch = caGetOption('includeBlankValuesInTopLevelForPrefetch', $pa_options, false);
		
		// Bail if no rows or template are set
		if (!is_array($pa_row_ids) || !sizeof($pa_row_ids) || !$ps_template) {
			return $pb_return_as_array ? array() : "";
		}
		
		// Parse template
		if(!is_array($va_template = DisplayTemplateParser::parse($ps_template, $pa_options))) { return null; }
		
		$o_dm = Datamodel::load();
		$ps_tablename = is_numeric($pm_tablename_or_num) ? $o_dm->getTableName($pm_tablename_or_num) : $pm_tablename_or_num;
		$t_instance = $o_dm->getInstanceByTableName($ps_tablename, true);
		$vs_pk = $t_instance->primaryKey();
		
		
		// Prefetch related items for <units>
		if (!$pa_options['isUnit'] && !caGetOption('dontPrefetchRelated', $pa_options, false)) {
			DisplayTemplateParser::prefetchAllRelatedIDs($va_template['tree']->children, $ps_tablename, $pa_row_ids, $pa_options);
		}
		
		
		$qr_res = caMakeSearchResult($ps_tablename, $pa_row_ids);
		if(!$qr_res) { return $pb_return_as_array ? array() : ""; }
		
		$pa_check_access = ($t_instance->hasField('access')) ? caGetOption('checkAccess', $pa_options, null) : null;
		if (!is_array($pa_check_access) || !sizeof($pa_check_access)) { $pa_check_access = null; }
		
		$ps_skip_if_expression = caGetOption('skipIfExpression', $pa_options, false);
		$va_skip_if_expression_tags = caGetTemplateTags($ps_skip_if_expression);
		
		$va_proc_templates = [];
		while($qr_res->nextHit()) {
			// check access
			if ($pa_check_access && !in_array($qr_res->get("{$ps_tablename}.access"), $pa_check_access)) { continue; }
			
			// check if we skip this row because of skipIfExpression
			if(strlen($ps_skip_if_expression) > 0) {
				$va_expression_vars = [];
				foreach($va_skip_if_expression_tags as $vs_expression_tag) {
					if(!isset($va_expression_vars[$vs_expression_tag])) {
						$va_expression_vars[$vs_expression_tag] = $qr_res->get($vs_expression_tag, ['assumeDisplayField' => true, 'returnIdno' => true, 'delimiter' => $ps_delimiter]);
					}
				}

				if(ExpressionParser::evaluate($ps_skip_if_expression, $va_expression_vars)) { continue; }
			}
			
			if ($pa_options['relativeToContainer']) {
				$va_vals = DisplayTemplateParser::_getValues($qr_res, $va_template['tags'], $pa_options);
				if(isset($pa_options['sort'])&& is_array($pa_options['sort'])) {
					$va_vals = caSortArrayByKeyInValue($va_vals, array('__sort__'), $pa_options['sortDirection'], array('dontRemoveKeyPrefixes' => true));
				}
				foreach($va_vals as $vn_index => $va_val_list) {
					$va_proc_templates[] = is_array($va_val_list) ? DisplayTemplateParser::_processChildren($qr_res, $va_template['tree']->children, $va_val_list, array_merge($pa_options, ['index' => $vn_index, 'returnAsArray' => $pa_options['aggregateUnique']])) : '';
				}
			} else {
				$va_proc_templates[] = DisplayTemplateParser::_processChildren($qr_res, $va_template['tree']->children, DisplayTemplateParser::_getValues($qr_res, $va_template['tags'], $pa_options), array_merge($pa_options, ['returnAsArray' => $pa_options['aggregateUnique']]));
			}
		}
		
		if ($pa_options['aggregateUnique']) {
			$va_acc = [];
			foreach($va_proc_templates as $va_val_list) {
				if(is_array($va_val_list)) { 
					$va_acc = array_merge($va_acc, $va_val_list); 
				} else {
					$va_acc[] = $va_val_list;
				}
			}
			$va_proc_templates = array_unique($va_acc);
		}
		
		if (!$pb_include_blanks && !$pb_include_blanks_for_prefetch) { $va_proc_templates = array_filter($va_proc_templates, 'strlen'); }
		
		// Transform links
		$va_proc_templates = caCreateLinksFromText(
			$va_proc_templates, $ps_tablename, $pa_row_ids,
			null, caGetOption('linkTarget', $pa_options, null),
			array_merge(['addRelParameter' => true, 'requireLinkTags' => true], $pa_options)
		);
		
		if (!$pb_include_blanks && !$pb_include_blanks_for_prefetch) { $va_proc_templates = array_filter($va_proc_templates, 'strlen'); }
		
		if (!$pb_return_as_array) {
			return join($ps_delimiter, $va_proc_templates);
		}
		return $va_proc_templates;
	}
	# -------------------------------------------------------------------
	/**
	 *
	 */
	static public function parse($ps_template, $pa_options=null) {
		$vs_cache_key = md5($ps_template);
		if(isset(DisplayTemplateParser::$template_cache[$vs_cache_key])) { return DisplayTemplateParser::$template_cache[$vs_cache_key]; }
		
		$ps_template_original = $ps_template;
		
		// Parse template
		$o_doc = str_get_dom($ps_template);	
		$ps_template = str_replace("<~root~>", "", str_replace("</~root~>", "", $o_doc->html()));	// replace template with parsed version; this allows us to do text find/replace later

		$va_tags = DisplayTemplateParser::_getTags($o_doc->children, array_merge($pa_options, ['maxLevels' => 1]));

		if (!is_array(DisplayTemplateParser::$template_cache)) { DisplayTemplateParser::$template_cache = []; }
		return DisplayTemplateParser::$template_cache[$vs_cache_key] = [
			'original_template' => $ps_template_original, 	// template as passed by caller
			'template' => $ps_template, 					// full template with compatibility transformations performed and units replaced with placeholders
			'tags' => $va_tags, 							// all placeholder tags used in template, both replaceable (eg. ^ca_objects.idno) and directive codes (eg. <ifdef code="ca_objects.idno">...</ifdef>
			'tree' => $o_doc								// ganon instance containing parsed template HTML
		];	
	}
	# -------------------------------------------------------------------
	/**
	 *
	 */
	private static function _getTags($po_nodes, $pa_options=null) {
		$o_dm = caGetOption('datamodel', $pa_options, Datamodel::load());
		$ps_relative_to = caGetOption('relativeTo', $pa_options, null);
	
		$pa_tags = caGetOption('tags', $pa_options, array());
		foreach($po_nodes as $vn_index => $o_node) {
			switch($vs_tag = $o_node->tag) {
				case 'unit':
					// noop - units are processed recursively so no need to look for tags now
					break;	
				case 'if':
					$va_codes = caGetTemplateTags((string)$o_node->rule, $pa_options, null);
					foreach($va_codes as $vs_code) { 
						$va_code = explode('.', $vs_code);
						if ($ps_relative_to && !$o_dm->tableExists($va_code[0])) { $vs_code = "{$ps_relative_to}.{$vs_code}"; }
						$pa_tags[$vs_code] = true; 
					}
					// fall through to default case
				default:
					$va_codes = caGetTemplateTags((string)$o_node->html(), $pa_options);
					foreach($va_codes as $vs_code) { 
						$va_code = explode('.', $vs_code);
						if ($ps_relative_to && !$o_dm->tableExists($va_code[0])) { $vs_code = "{$ps_relative_to}.{$vs_code}"; }
						$pa_tags[$vs_code] = true; 
					}
					break;
			}
			
		}
		
		return $pa_tags;
	}
	# -------------------------------------------------------------------
	/**
	 *
	 */
	static private function _processChildren(SearchResult $pr_res, $po_nodes, array $pa_vals, array $pa_options=null) {
		if(!is_array($pa_options)) { $pa_options = []; }
		if (!$po_nodes) { return ''; }
		$vs_acc = '';
		$ps_tablename = $pr_res->tableName();
				
		$o_dm = Datamodel::load();
		$t_instance = $o_dm->getInstanceByTableName($ps_tablename, true);
		$ps_delimiter = caGetOption('delimiter', $pa_options, '; ');
		$pb_is_case = caGetOption('isCase', $pa_options, false, ['castTo' => 'boolean']);
		$pb_quote = caGetOption('quote', $pa_options, false, ['castTo' => 'boolean']);
		$pa_primary_ids = caGetOption('primaryIDs', $pa_options, null);
		$pb_include_blanks = caGetOption('includeBlankValuesInArray', $pa_options, false);
		
		unset($pa_options['quote']);
		
		$vn_last_unit_omit_count = null;
		
		foreach($po_nodes as $vn_index => $o_node) {
			switch($vs_tag = strtolower($o_node->tag)) {
				case 'case':
					if (!$pb_is_case) {
						$vs_acc .= DisplayTemplateParser::_processChildren($pr_res, $o_node->children, $pa_vals, array_merge($pa_options, ['isCase' => true]));	
					}
					break;
				case 'if':
					if (strlen($vs_rule = $o_node->rule) && ExpressionParser::evaluate($vs_rule, $pa_vals)) {
						$vs_acc .= DisplayTemplateParser::_processChildren($pr_res, $o_node->children, DisplayTemplateParser::_getValues($pr_res, DisplayTemplateParser::_getTags($o_node->children, $pa_options), $pa_options), $pa_options);	
						 
						if ($pb_is_case) { break(2); }
					}
					break;
				case 'ifdef':
				case 'ifnotdef':
					$vb_defined = DisplayTemplateParser::_evaluateCodeAttribute($pr_res, $o_node, ['index' => caGetOption('index', $pa_options, null), 'mode' => ($vs_tag == 'ifdef') ? 'present' : 'not_present']);
					
					if ((($vs_tag == 'ifdef') && $vb_defined) || (($vs_tag == 'ifnotdef') && $vb_defined)) {
						// Make sure returned values are not empty
						$vs_acc .= DisplayTemplateParser::_processChildren($pr_res, $o_node->children, DisplayTemplateParser::_getValues($pr_res, DisplayTemplateParser::_getTags($o_node->children, $pa_options), $pa_options), $pa_options);
						if ($pb_is_case) { break(2); }
					}
					break;
				case 'ifcount':
					$vn_min = (int)$o_node->min;
					$vn_max = (int)$o_node->max;
					
					if(!is_array($va_codes = DisplayTemplateParser::_getCodesFromAttribute($o_node)) || !sizeof($va_codes)) { break; }
					
					$pa_check_access = ($t_instance->hasField('access')) ? caGetOption('checkAccess', $pa_options, null) : null;
					if (!is_array($pa_check_access) || !sizeof($pa_check_access)) { $pa_check_access = null; }
					
					$vb_bool = DisplayTemplateParser::_getCodesBooleanModeAttribute($o_node);
					$va_restrict_to_types = DisplayTemplateParser::_getCodesFromAttribute($o_node, ['attribute' => 'restrictToTypes']); 
					$va_exclude_types = DisplayTemplateParser::_getCodesFromAttribute($o_node, ['attribute' => 'excludeTypes']); 
					$va_restrict_to_relationship_types = DisplayTemplateParser::_getCodesFromAttribute($o_node, ['attribute' => 'restrictToRelationshipTypes']); 
					$va_exclude_to_relationship_types = DisplayTemplateParser::_getCodesFromAttribute($o_node, ['attribute' => 'excludeRelationshipTypes']); 
		
					$vm_count = ($vb_bool == 'AND') ? 0 : [];
					foreach($va_codes as $vs_code) {
						$va_vals = $pr_res->get($vs_code, ['checkAccess' => $pa_check_access, 'returnAsArray' => true, 'restrictToTypes' => $va_restrict_to_types, 'excludeTypes' => $va_exclude_types, 'restrictToRelationshipTypes' => $va_restrict_to_relationship_types, 'excludeRelationshipTypes' => $va_exclude_to_relationship_types]);
						if (is_array($va_vals)) { 
							if ($vb_bool == 'AND') {
								$vm_count += sizeof($va_vals); 
							} else {
								$vm_count[$vs_code] = sizeof($va_vals);
							}
						}
					}
					
					if ($vb_bool == 'AND') {
						if (($vn_min <= $vm_count) && (($vn_max >= $vm_count) || !$vn_max)) {
							$vs_acc .= DisplayTemplateParser::_processChildren($pr_res, $o_node->children, DisplayTemplateParser::_getValues($pr_res, DisplayTemplateParser::_getTags($o_node->children, $pa_options), $pa_options), $pa_options);
							if ($pb_is_case) { break(2); }
						}
					} else {
						$vb_all_have_count = true;
						foreach($vm_count as $vs_code => $vn_count) {
							if(!(($vn_min <= $vn_count) && (($vn_max >= $vn_count) || !$vn_max))) {
								$vb_all_have_count = false;
								break(2);
							}	
						}
						if ($vb_all_have_count) {
							$vs_acc .= DisplayTemplateParser::_processChildren($pr_res, $o_node->children, $pa_vals, $pa_options);
							if ($pb_is_case) { break(2); }
						}
					}
					break;
				case 'more':
					// Does a placeholder with value follow this tag?
					// NOTE: 	We don't take into account <ifdef> and friends when finding a set placeholder; it may be set but not visible due to a conditional
					// 			This case is not covered at the moment on the assumption that if you're using <more> you're not using conditionals. This may or may not be a good assumption.
					for($vn_i = $vn_index + 1; $vn_i < sizeof($po_nodes); $vn_i++) {
						if ($po_nodes[$vn_i] && ($po_nodes[$vn_i]->tag == '~text~') && is_array($va_following_tags = caGetTemplateTags($po_nodes[$vn_i]->text))) {
							
							foreach($va_following_tags as $vs_following_tag) {
								if(isset($pa_vals[$vs_following_tag]) && strlen($pa_vals[$vs_following_tag])) {
									$vs_acc .= DisplayTemplateParser::_processChildren($pr_res, $o_node->children, $pa_vals, $pa_options);
									if ($pb_is_case) { break(2); }
								}
							}
						}
					}
					break;
				case 'between':
					// Does a placeholder with value precede this tag?
					// NOTE: 	We don't take into account <ifdef> and friends when finding a set placeholder; it may be set but not visible due to a conditional
					// 			This case is not covered at the moment on the assumption that if you're using <between> you're not using conditionals. This may or may not be a good assumption.
					
					$vb_has_preceding_value = false;
					for($vn_i = 0; $vn_i < $vn_index; $vn_i++) {
						if ($po_nodes[$vn_i] && ($po_nodes[$vn_i]->tag == '~text~') && is_array($va_preceding_tags = caGetTemplateTags($po_nodes[$vn_i]->text))) {
							
							foreach($va_preceding_tags as $vs_preceding_tag) {
								if(isset($pa_vals[$vs_preceding_tag]) && strlen($pa_vals[$vs_preceding_tag])) {
									$vb_has_preceding_value = true;
								}
							}
						}
					}
					
					if ($vb_has_preceding_value) {
						// Does it have a value immediately following it?
						for($vn_i = $vn_index + 1; $vn_i < sizeof($po_nodes); $vn_i++) {
							if ($po_nodes[$vn_i] && ($po_nodes[$vn_i]->tag == '~text~') && is_array($va_following_tags = caGetTemplateTags($po_nodes[$vn_i]->text))) {
							
								foreach($va_following_tags as $vs_following_tag) {
									if(isset($pa_vals[$vs_following_tag]) && strlen($pa_vals[$vs_following_tag])) {
										$vs_acc .= DisplayTemplateParser::_processChildren($pr_res, $o_node->children, $pa_vals, $pa_options);
										if ($pb_is_case) { break(2); }
									}
									break;
								}
							}
						}
					}
					break;
				case 'expression':
					if ($vs_exp = trim($o_node->getInnerText())) {
						$vs_acc .= ExpressionParser::evaluate(DisplayTemplateParser::_processChildren($pr_res, $o_node->children, DisplayTemplateParser::_getValues($pr_res, DisplayTemplateParser::_getTags($o_node->children, $pa_options), $pa_options), array_merge($pa_options, ['quote' => true])), $pa_vals);
						
						if ($pb_is_case) { break(2); }
					}
					break;
				case 'unit':
					$va_relative_to_tmp = $o_node->relativeTo ? explode(".", $o_node->relativeTo) : [$ps_tablename];
				
					if ($va_relative_to_tmp[0] && !($t_rel_instance = $o_dm->getInstanceByTableName($va_relative_to_tmp[0], true))) { continue; }
					
					$vn_last_unit_omit_count = 0;
					
					// <unit> attributes
					$vs_unit_delimiter = $o_node->delimiter ? (string)$o_node->delimiter : $ps_delimiter;
					$vb_unique = $o_node->unique ? (bool)$o_node->unique : false;
					$vb_aggregate_unique = $o_node->aggregateUnique ? (bool)$o_node->aggregateUnique : false;
					$vs_unit_skip_if_expression = (string)$o_node->skipIfExpression;
					
					$vn_start = (int)$o_node->start;
					$vn_length = (int)$o_node->length;
					
					$pa_check_access = ($t_instance->hasField('access')) ? caGetOption('checkAccess', $pa_options, null) : null;
					if (!is_array($pa_check_access) || !sizeof($pa_check_access)) { $pa_check_access = null; }

					// additional get options for pulling related records
					$va_get_options = ['returnAsArray' => true, 'checkAccess' => $pa_check_access];
				
					$va_get_options['restrictToTypes'] = DisplayTemplateParser::_getCodesFromAttribute($o_node, ['attribute' => 'restrictToTypes']); 
					$va_get_options['excludeTypes'] = DisplayTemplateParser::_getCodesFromAttribute($o_node, ['attribute' => 'excludeTypes']); 
					$va_get_options['restrictToRelationshipTypes'] = DisplayTemplateParser::_getCodesFromAttribute($o_node, ['attribute' => 'restrictToRelationshipTypes']); 
					$va_get_options['excludeRelationshipTypes'] = DisplayTemplateParser::_getCodesFromAttribute($o_node, ['attribute' => 'excludeRelationshipTypes']); 
					
					
					if ($o_node->sort) {
						$va_get_options['sort'] = preg_split('![ ,;]+!', $o_node->sort);
						$va_get_options['sortDirection'] = $o_node->sortDirection;
					}
	
					$va_relation_ids = $va_relationship_type_ids = null;
					if (
						((sizeof($va_relative_to_tmp) == 1) && ($va_relative_to_tmp[0] == $ps_tablename))
						||
						((sizeof($va_relative_to_tmp) >= 1) && ($va_relative_to_tmp[0] == $ps_tablename) && ($va_relative_to_tmp[1] != 'related'))
					) {
					
						$vs_relative_to_container = null;
						switch(strtolower($va_relative_to_tmp[1])) {
							case 'hierarchy':
								$va_relative_ids = $pr_res->get($t_rel_instance->tableName().".hierarchy.".$t_rel_instance->primaryKey(), $va_get_options);
								$va_relative_ids = array_values($va_relative_ids);
								break;
							case 'parent':
								$va_relative_ids = $pr_res->get($t_rel_instance->tableName().".parent.".$t_rel_instance->primaryKey(), $va_get_options);
								$va_relative_ids = array_values($va_relative_ids);
								break;
							case 'children':
								$va_relative_ids = $pr_res->get($t_rel_instance->tableName().".children.".$t_rel_instance->primaryKey(), $va_get_options);
								$va_relative_ids = array_values($va_relative_ids);
								break;
							case 'siblings':
								$va_relative_ids = $pr_res->get($t_rel_instance->tableName().".siblings.".$t_rel_instance->primaryKey(), $va_get_options);
								$va_relative_ids = array_values($va_relative_ids);
								break;
							default:
								// If relativeTo is not set to a valid attribute try to guess from template
								if ($t_rel_instance->isValidMetadataElement(join(".", array_slice($va_relative_to_tmp, 1, 1)), true)) {
									$vs_relative_to_container = join(".", array_slice($va_relative_to_tmp, 0, 2));
								} else {
									$va_tags = caGetTemplateTags($o_node->getInnerText());
									foreach($va_tags as $vs_tag) {
										$va_tag = explode('.', $vs_tag);
										
										while(sizeof($va_tag) > 1) {
											$vs_end = array_pop($va_tag);
											if ($t_rel_instance->isValidMetadataElement($vs_end, true)) {
												$va_tag[] = $vs_end;
												$vs_relative_to_container = join(".", $va_tag);
												break(2);
											}
										}
									}
								}
								$va_relative_ids = array($pr_res->getPrimaryKey());
								break;
						}
						
						// process template for all records selected by unit tag
						$va_tmpl_val = DisplayTemplateParser::evaluate(
							$o_node->getInnerText(), $ps_tablename, $va_relative_ids,
							array_merge(
								$pa_options,
								[
									'sort' => $va_get_options['sort'],
									'sortDirection' => $va_get_options['sortDirection'],
									'returnAsArray' => true,
									'delimiter' => $vs_unit_delimiter,
									'skipIfExpression' => $vs_unit_skip_if_expression,
									'placeholderPrefix' => (string)$o_node->relativeTo,
									'restrictToTypes' => $va_get_options['restrictToTypes'],
									'excludeTypes' => $va_get_options['excludeTypes'],
									'isUnit' => true,
									'unitStart' => $vn_start,
									'unitLength' => $vn_length,
									'relativeToContainer' => $vs_relative_to_container,
									'includeBlankValuesInTopLevelForPrefetch' => false,
									'unique' => $vb_unique,
									'aggregateUnique' => $vb_aggregate_unique
								]
							)
						);
						
						if ($vb_unique) { $va_tmpl_val = array_unique($va_tmpl_val); }
						if (($vn_start > 0) || !is_null($vn_length)) { 
							$vn_last_unit_omit_count = sizeof($va_tmpl_val) - ($vn_length - $vn_start);
						}
						if (caGetOption('returnAsArray', $pa_options, false)) { return $va_tmpl_val; }
						$vs_acc .= join($vs_unit_delimiter, $va_tmpl_val);
						if ($pb_is_case) { break(2); }
					} else { 
						switch(strtolower($va_relative_to_tmp[1])) {
							case 'hierarchy':
								$va_relative_ids = $pr_res->get($t_rel_instance->tableName().".hierarchy.".$t_rel_instance->primaryKey(), $va_get_options);
								$va_relative_ids = array_values($va_relative_ids);
								break;
							case 'parent':
								$va_relative_ids = $pr_res->get($t_rel_instance->tableName().".parent.".$t_rel_instance->primaryKey(), $va_get_options);
								$va_relative_ids = array_values($va_relative_ids);
								break;
							case 'children':
								$va_relative_ids = $pr_res->get($t_rel_instance->tableName().".children.".$t_rel_instance->primaryKey(), $va_get_options);
								$va_relative_ids = array_values($va_relative_ids);
								break;
							case 'siblings':
								$va_relative_ids = $pr_res->get($t_rel_instance->tableName().".siblings.".$t_rel_instance->primaryKey(), $va_get_options);
								$va_relative_ids = array_values($va_relative_ids);
								break;
							case 'related':
								$va_relative_ids = $pr_res->get($t_rel_instance->tableName().".related.".$t_rel_instance->primaryKey(), $va_get_options);
								$va_relative_ids = array_values($va_relative_ids);
								
								$va_relation_ids = array_keys($t_instance->getRelatedItems($t_rel_instance->tableName(), array_merge($va_get_options, array('returnAs' => 'data'))));
								
								$va_relationship_type_ids = array();
								if (is_array($va_relation_ids) && sizeof($va_relation_ids)) {
									$qr_rels = caMakeSearchResult($t_rel_instance->getRelationshipTableName($ps_tablename), $va_relation_ids);
									$va_relationship_type_ids = $qr_rels->getAllFieldValues($t_rel_instance->getRelationshipTableName($ps_tablename).'.type_id');
								}
								break;
							default:
								if (method_exists($t_instance, 'isSelfRelationship') && $t_instance->isSelfRelationship() && is_array($pa_primary_ids) && isset($pa_primary_ids[$t_rel_instance->tableName()])) {
									$va_relative_ids = array_values($t_instance->getRelatedIDsForSelfRelationship($pa_primary_ids[$t_rel_instance->tableName()], array($pr_res->getPrimaryKey())));
									
									$va_relation_ids = array_keys($t_instance->getRelatedItems($t_rel_instance->tableName(), array_merge($va_get_options, array('returnAs' => 'data'))));
									$va_relationship_type_ids = array();
									if (is_array($va_relation_ids) && sizeof($va_relation_ids)) {
										$qr_rels = caMakeSearchResult($t_rel_instance->getSelfRelationTableName(), $va_relation_ids);
										$va_relationship_type_ids = $qr_rels->getAllFieldValues($t_rel_instance->getSelfRelationTableName().'.type_id');
									}
								} else {
									$va_relative_ids = $pr_res->get($t_rel_instance->primaryKey(true), $va_get_options);
									$va_relative_ids = is_array($va_relative_ids) ? array_values($va_relative_ids) : array();
									
									$va_relation_ids = array_keys($t_instance->getRelatedItems($t_rel_instance->tableName(), array_merge($va_get_options, array('returnAs' => 'data'))));
									$va_relationship_type_ids = array();
									if (is_array($va_relation_ids) && sizeof($va_relation_ids)) {
										$qr_rels = caMakeSearchResult($t_rel_instance->getRelationshipTableName($ps_tablename), $va_relation_ids);
										$va_relationship_type_ids = $qr_rels->getAllFieldValues($t_rel_instance->getRelationshipTableName($ps_tablename).'.type_id');
									}
								}
							
								break;
						}
						
						$va_tmpl_val = DisplayTemplateParser::evaluate(
							$o_node->getInnerText(), $va_relative_to_tmp[0], $va_relative_ids,
							array_merge(
								$pa_options,
								[
									'sort' => $va_unit['sort'],
									'sortDirection' => $va_unit['sortDirection'],
									'delimiter' => $vs_unit_delimiter,
									'returnAsArray' => true,
									'skipIfExpression' => $vs_unit_skip_if_expression,
									'placeholderPrefix' => (string)$o_node->relativeTo,
									'restrictToTypes' => $va_get_options['restrictToTypes'],
									'excludeTypes' => $va_get_options['excludeTypes'],
									'isUnit' => true,
									'unitStart' => $vn_start,
									'unitLength' => $vn_length,
									'includeBlankValuesInTopLevelForPrefetch' => false,
									'unique' => $vb_unique,
									'aggregateUnique' => $vb_aggregate_unique,
									'relationIDs' => $va_relation_ids,
									'relationshipTypeIDs' => $va_relationship_type_ids
								]
							)
						);	
						if ($vb_unique) { $va_tmpl_val = array_unique($va_tmpl_val); }
						if (($vn_start > 0) || !is_null($vn_length)) { 
							$vn_num_vals = sizeof($va_tmpl_val);
							$va_tmpl_val = array_slice($va_tmpl_val, $vn_start, ($vn_length > 0) ? $vn_length : null); 
							$vn_last_unit_omit_count = $vn_num_vals -  ($vn_length - $vn_start);
						}
						
						if (caGetOption('returnAsArray', $pa_options, false)) { return $va_tmpl_val; }
						$vs_acc .= join($vs_unit_delimiter, $va_tmpl_val);
						if ($pb_is_case) { break(2); }
					}
				
					break;
				case 'whenunitomits':
					if ($vn_last_unit_omit_count > 0) {
						$vs_proc_template = caProcessTemplate($o_node->getInnerText(), array_merge($pa_vals, ['omitcount' => (int)$vn_last_unit_omit_count]), ['quote' => $pb_quote]);
						$vs_acc .= $vs_proc_template;
					}
					break;
				default:
					if ($o_node->children && (sizeof($o_node->children) > 0)) {
						$vs_proc_template = DisplayTemplateParser::_processChildren($pr_res, $o_node->children, $pa_vals, $pa_options);
					} else {
						$vs_proc_template = caProcessTemplate($o_node->html(), $pa_vals, ['quote' => $pb_quote]);
					}
					
					if ($vs_tag === 'l') {
						$va_proc_templates = caCreateLinksFromText(
							["{$vs_proc_template}"], $ps_tablename, [$pr_res->getPrimaryKey()],
							null, caGetOption('linkTarget', $pa_options, null),
							array_merge(['addRelParameter' => true, 'requireLinkTags' => false], $pa_options)
						);
						$vs_proc_template = array_shift($va_proc_templates);	
					} elseif(strlen($vs_tag) && ($vs_tag[0] !=='~')) { 
						if ($o_node->children && (sizeof($o_node->children) > 0)) {
							$vs_attr = '';
							if ($o_node->attributes) {
								foreach($o_node->attributes as $attribute => $value) {
									$vs_attr .=  " {$attribute}=\"".htmlspecialchars(caProcessTemplate($value, $pa_vals, ['quote' => $pb_quote]))."\""; 
								}
							}
							$vs_proc_template = "<{$vs_tag}{$vs_attr}>{$vs_proc_template}</{$vs_tag}>"; 
						} elseif ($o_node->attributes && (sizeof($o_node->attributes) > 0)) {
							$vs_attr = '';
							foreach($o_node->attributes as $attribute => $value) {
								$vs_attr .=  " {$attribute}=\"".htmlspecialchars(caProcessTemplate($value, $pa_vals, ['quote' => $pb_quote]))."\""; 
							}
							
							switch(strtolower($vs_tag)) {
								case 'br':
								case 'hr':
								case 'meta':
								case 'link':
								case 'base':
								case 'img':
								case 'embed':
								case 'param':
								case 'area':
								case 'col':
								case 'input':
									$vs_proc_template = "<{$vs_tag}{$vs_attr} />"; 
									break;
								default:
									$vs_proc_template = "<{$vs_tag}{$vs_attr}></{$vs_tag}>"; 
									break;
							}
							
						} else {
							$vs_proc_template = $o_node->html();
						}
					}
					
					$vs_acc .= $vs_proc_template;
					break;
			}
		}
		

		return $vs_acc;
	}
	# -------------------------------------------------------------------
	/**
	 *
	 */
	static private function _getValues(SearchResult $pr_res, array $pa_tags, array $pa_options=null) {
		unset($pa_options['returnAsArray']);
		unset($pa_options['returnWithStructure']);
		
		$vn_start = caGetOption('unitStart', $pa_options, 0, ['castTo' => 'int']);
		$vn_length = caGetOption('unitLength', $pa_options, 0, ['castTo' => 'int']);
		
		$va_relationship_type_ids = caGetOption('relationshipTypeIDs', $pa_options, array(), ['castTo' => 'array']);
		
		$o_dm = Datamodel::load();
		
		
		$pb_include_blanks = caGetOption('includeBlankValuesInArray', $pa_options, false);
		$ps_prefix = caGetOption(['placeholderPrefix', 'relativeTo', 'prefix'], $pa_options, null);
		$pn_index = caGetOption('index', $pa_options, null);
		
		$vs_cache_key = md5($pr_res->tableName()."/".$pr_res->getPrimaryKey()."/".print_r($pa_tags, true)."/".print_r($pa_options, true));
		
		$va_get_specs = [];
		foreach(array_keys($pa_tags) as $vs_tag) {
			// Apply placeholder prefix to any tag except the "specials"
			if (!in_array(strtolower($vs_tag), ['relationship_typename', 'relationship_type_id', 'relationship_typecode', 'relationship_type_code', 'date', 'primary', 'count', 'index', 'omitcount'])) {
				$va_tag = explode(".", $vs_tag);
				$vs_get_spec = $vs_tag;
				if ($ps_prefix && (!$o_dm->tableExists($va_tag[0])) &&  (!preg_match("!^".preg_quote($ps_prefix, "!")."\.!", $vs_tag)) && (sizeof($va_tag) > 0)) {
					$vs_get_spec = "{$ps_prefix}.".array_shift($va_tag);
					if(sizeof($va_tag) > 0) {
						$vs_get_spec .= ".".join(".", $va_tag);
					}
				}
			} else {
				$vs_get_spec = $vs_tag;
			}

			// Get trailing options (eg. ca_entities.preferred_labels.displayname%delimiter=;_)
			if (is_array($va_parsed_tag_opts = DisplayTemplateParser::_parseTagOpts($vs_get_spec))) {
				$vs_get_spec = $va_parsed_tag_opts['tag'];
			}
			
			$va_get_specs[$vs_tag] = [
				'spec' => $vs_get_spec,
				'parsed' => $va_parsed_tag_opts
			];
		}
		
		
		$vn_count = 1;
		$va_tag_vals = null;
		if ($vs_relative_to_container = caGetOption('relativeToContainer', $pa_options, null)) {
			if (DisplayTemplateParser::$value_cache[$vs_cache_key]) {
				$va_tag_vals = DisplayTemplateParser::$value_cache[$vs_cache_key];
				$vn_count = DisplayTemplateParser::$value_count_cache[$vs_cache_key];
			} else {
				$va_tag_vals = [];
				foreach(array_keys($pa_tags) as $vs_tag) {					
					$vs_get_spec = $va_get_specs[$vs_tag]['spec'];
					$va_parsed_tag_opts = $va_get_specs[$vs_tag]['parsed'];
					
					$va_vals = $pr_res->get($vs_get_spec, array_merge($pa_options, $va_parsed_tag_opts['options'], ['returnAsArray' => true, 'returnBlankValues' => true]));
					
					if (is_array($va_vals)) {
						if ((($vn_start > 0) || ($vn_length > 0)) && ($vn_start < sizeof($va_vals)) && (!$vn_length || ($vn_start + $vn_length <= sizeof($va_vals)))) {
							$va_vals = array_slice($va_vals, $vn_start, ($vn_length > 0) ? $vn_length : null);
						}
						
						foreach($va_vals as $vn_index => $vs_val) {
							$va_tag_vals[$vn_index][$vs_tag] = $vs_val;
						}
					}
				}
				
				if (isset($pa_options['sort']) && is_array($pa_options['sort']) && sizeof($pa_options['sort'])) {
					$va_sortables = array();
					foreach($pa_options['sort'] as $vs_sort_spec) {
						$va_sortables[] = $pr_res->get($vs_sort_spec, ['sortable' => true, 'returnAsArray' => true, 'returnBlankValues' => true]);
					}
					if ((($vn_start > 0) || ($vn_length > 0)) && ($vn_start < sizeof($va_sortables)) && (!$vn_length || ($vn_start + $vn_length <= sizeof($va_sortables)))) {
						$va_sortables = array_slice($va_sortables, $vn_start, ($vn_length > 0) ? $vn_length : null);
					}
					
					if(is_array($va_sortables)) {
						foreach($va_sortables as $i => $va_sort_values) {
							if (!is_array($va_sort_values)) { continue; }
							foreach($va_sort_values as $vn_index => $vs_sort_value) {
								$va_tag_vals[$vn_index]['__sort__'] .= $vs_sort_value;
							}
						}
					}
				}
			
				DisplayTemplateParser::$value_cache[$vs_cache_key] = $va_tag_vals;
				DisplayTemplateParser::$value_count_cache[$vs_cache_key] = $vn_count = sizeof($va_tag_vals);
			
			}
			
			if(strlen($pn_index)) {
				$va_tag_vals = $va_tag_vals[$pn_index];	
				$vs_relative_to_container = null;
			}
		}
		
		$va_vals = [];
		
		for($vn_c = 0; $vn_c < $vn_count; $vn_c++) {
			foreach(array_keys($pa_tags) as $vs_tag) {
				$vs_get_spec = $va_get_specs[$vs_tag]['spec'];
				$va_parsed_tag_opts = $va_get_specs[$vs_tag]['parsed'];
				
				switch(strtolower($vs_get_spec)) {
					case 'relationship_typename':
						$va_val_list = array();
						if (is_array($va_relationship_type_ids) && ($vn_type_id = $va_relationship_type_ids[$pr_res->currentIndex()])) {
							$qr_rels = caMakeSearchResult('ca_relationship_types', array($vn_type_id));
							if ($qr_rels->nextHit()) {
								$va_val_list = $qr_rels->get('ca_relationship_types.preferred_labels.'.((caGetOption('orientation', $pa_options, 'LTOR') == 'LTOR') ? 'typename' : 'typename_reverse'), $va_opts = array_merge($pa_options, $va_parsed_tag_opts['options'], ['returnAsArray' => true, 'returnWithStructure' => false]));
							}
						} else {
							$va_val_list = $pr_res->get('ca_relationship_types.preferred_labels.'.((caGetOption('orientation', $pa_options, 'LTOR') == 'LTOR') ? 'typename' : 'typename_reverse'), $va_opts = array_merge($pa_options, $va_parsed_tag_opts['options'], ['returnAsArray' => true, 'returnWithStructure' => false]));
						}
						break;
					case 'relationship_type_id':
						if (is_array($va_relationship_type_ids) && ($vn_type_id = $va_relationship_type_ids[$pr_res->currentIndex()])) {
							$va_val_list = [$va_relationship_type_ids[$pr_res->currentIndex()]];
						} else {
							$va_val_list = $pr_res->get('ca_relationship_types.type_id', $va_opts = array_merge($pa_options, $va_parsed_tag_opts['options'], ['returnAsArray' => true, 'returnWithStructure' => false]));
						}
						break;
					case 'relationship_typecode':
					case 'relationship_type_code':
						$va_val_list = array();
						if (is_array($va_relationship_type_ids) && ($vn_type_id = $va_relationship_type_ids[$pr_res->currentIndex()])) {
							$qr_rels = caMakeSearchResult('ca_relationship_types', array($vn_type_id));
							if ($qr_rels->nextHit()) {
								$va_val_list = $qr_rels->get('ca_relationship_types.type_code', $va_opts = array_merge($pa_options, $va_parsed_tag_opts['options'], ['returnAsArray' => true, 'returnWithStructure' => false]));
							}
						} else {
							$va_val_list = $pr_res->get('ca_relationship_types.type_code', $va_opts = array_merge($pa_options, $va_parsed_tag_opts['options'], ['returnAsArray' => true, 'returnWithStructure' => false]));
						}
						break;
					case 'date':		// allows embedding of current date
						$va_val_list = [date(caGetOption('format', $va_parsed_tag_opts['options'], 'd M Y'))];
						break;
					case 'primary':
						$va_val_list = [$pr_res->tableName()];
						break;
					case 'count':
						$va_val_list = [$pr_res->numHits()];
						break;
					case 'omitcount':
						$va_val_list = [(int)$pr_res->numHits() - ($vn_length - $vn_start)];
						break;
					case 'index':
						$va_val_list = [$pr_res->currentIndex() + 1];
						break;
					default:
						if(isset($pa_options['forceValues'][$vs_get_spec][$pr_res->getPrimaryKey()])) { 
							$va_val_list = [$pa_options['forceValues'][$vs_get_spec][$pr_res->getPrimaryKey()]];
						} elseif ($vs_relative_to_container) {
							$va_val_list = [$va_tag_vals[$vn_c][$vs_tag]];
						} elseif(strlen($pn_index)) {
							$va_val_list = [$va_tag_vals[$vs_tag]];
						} else {
							$va_val_list = $pr_res->get($vs_get_spec, $va_opts = array_merge($pa_options, $va_parsed_tag_opts['options'], ['returnAsArray' => true, 'returnWithStructure' => false]));
							if (!is_array($va_val_list)) { $va_val_list = array(); }
							if ((($vn_start > 0) || ($vn_length > 0)) && ($vn_start < sizeof($va_val_list)) && (!$vn_length || ($vn_start + $vn_length <= sizeof($va_val_list)))) {
								$va_val_list = array_slice($va_val_list, $vn_start, ($vn_length > 0) ? $vn_length : null);
							}
						}
						break;
				}
				$ps_delimiter = caGetOption('delimiter', $va_opts, ';');
				
				if ($vs_relative_to_container) {
					$va_vals[$vn_c][$vs_tag] = join($ps_delimiter, $va_val_list);
					if (isset($va_tag_vals[$vn_c]['__sort__'])) {
						$va_vals[$vn_c]['__sort__'] = $va_tag_vals[$vn_c]['__sort__'];
					}
				} else {
					if(!$pb_include_blanks) { $va_val_list = array_filter($va_val_list, 'strlen'); }
					$va_vals[$vs_tag] = join($ps_delimiter, $va_val_list);
					if (isset($va_tag_vals[$vn_c]['__sort__'])) {
						$va_vals['__sort__'] = $va_tag_vals[$vn_c]['__sort__'];
					}
				}
			}
		}
		
		return $va_vals;
	}
	# -------------------------------------------------------------------
	/**
	 *
	 */
	static private function _getValuesForCodeAttribute(SearchResult $pr_res, $po_node, array $pa_options=null) {
		if(!($va_codes = DisplayTemplateParser::_getCodesFromAttribute($po_node))) { return []; }
		
		$pb_include_blanks = caGetOption('includeBlankValuesInArray', $pa_options, false);
		$ps_delimiter = caGetOption('delimiter', $pa_options, ';');
		
		$va_vals = [];
		foreach($va_codes as $vs_code) {
			if(!is_array($va_val_list = $pr_res->get($vs_code, ['returnAsArray' => true]))) { continue; }
			
			if (!$pb_include_blanks) {
				$va_val_list = array_filter($va_val_list);
			}
			$va_vals[$vs_code] = sizeof($va_val_list) ? join($ps_delimiter, $va_val_list): '';
		}
		return $va_vals;
	}
	# -------------------------------------------------------------------
	/**
	 *
	 */
	static private function _evaluateCodeAttribute(SearchResult $pr_res, $po_node, array $pa_options=null) {
		if(!($va_codes = DisplayTemplateParser::_getCodesFromAttribute($po_node, ['includeBooleans' => true]))) { return []; }
		
		$pb_include_blanks = caGetOption('includeBlankValuesInArray', $pa_options, false);
		$ps_delimiter = caGetOption('delimiter', $pa_options, ';');
		$pb_mode = caGetOption('mode', $pa_options, 'present');	// value 'present' or 'not_present'
		$pn_index = caGetOption('index', $pa_options, null);
		
		$vb_has_value = null;
		foreach($va_codes as $vs_code => $vs_bool) {
			$va_val_list = $pr_res->get($vs_code, ['returnAsArray' => true, 'returnBlankValues' => true, 'convertCodesToDisplayText' => true, 'returnAsDecimal' => true, 'getDirectDate' => true]);
			if(!is_array($va_val_list)) {  // no value
				$vb_value_present = false;
			} else {
				if(!is_null($pn_index)) {
					if (!isset($va_val_list[$pn_index]) || ((is_numeric($va_val_list[$pn_index]) && (float)$va_val_list[$pn_index] == 0) || !strlen(trim($va_val_list[$pn_index])))) {
						$vb_value_present = false;			// no value
					} else {
						$va_val_list = array($va_val_list[$pn_index]);
						if (!$pb_include_blanks) { $va_val_list = array_filter($va_val_list); }
						$vb_value_present = (bool)(sizeof($va_val_list));
					}
				} else {
					if (!$pb_include_blanks) { 
						foreach($va_val_list as $vn_i => $vm_val) {
							if ((is_numeric($vm_val) && (float)$vm_val == 0) || !strlen(trim($vm_val))) {
								unset($va_val_list[$vn_i]);
							}
						}
					}
					$vb_value_present = (bool)(sizeof($va_val_list));
				}
			}
			if ($pb_mode !== 'present') { $vb_value_present = !$vb_value_present; }
			
			if (is_null($vb_has_value)) { $vb_has_value = $vb_value_present; }
			
			$vb_has_value = ($vs_bool == 'OR') ? ($vb_has_value || $vb_value_present) : ($vb_has_value && $vb_value_present);
		}
		return $vb_has_value;
	}
	# -------------------------------------------------------------------
	/**
	 *
	 */
	static private function _getCodesFromAttribute($po_node, array $pa_options=null) {
		$vs_attribute = caGetOption('attribute', $pa_options, 'code'); 
		$pb_include_booleans = caGetOption('includeBooleans', $pa_options, false); 
		
		$vs_code_list = $po_node->{$vs_attribute};
		if (!$po_node || !$po_node->{$vs_attribute}) { return null; }
		$va_codes = preg_split("![ ,;\|]+!", $po_node->{$vs_attribute});
		if ($pb_include_booleans) { preg_match_all("![ ,;\|]+!", $po_node->{$vs_attribute}, $va_matches); $va_matches = array_shift($va_matches); }
		if (!$va_codes || !sizeof($va_codes)) { return null; }
		
		if ($pb_include_booleans) {
			$va_codes = array_flip($va_codes);
			foreach($va_codes as $vs_code => $vn_i) {
				if ($vn_i == 0) { $va_codes[$vs_code] = null; continue; }
				$va_codes[$vs_code] = ($va_matches[$vn_i-1] == '|') ? 'OR' : 'AND';
			}
		}
		
		return $va_codes;
	}
	# -------------------------------------------------------------------
	/**
	 *
	 */
	static private function _getCodesBooleanModeAttribute($po_node, array $pa_options=null) {
		$vs_attribute = caGetOption('attribute', $pa_options, 'code'); 
		$vs_code_list = $po_node->{$vs_attribute};
		if (!$po_node || !$po_node->{$vs_attribute}) { return null; }
		
		if (strpos($po_node->{$vs_attribute}, "|") !== false) { 
			return 'OR';
		}
		return 'AND';
	}
	# -------------------------------------------------------------------
	/**
	 *
	 */
	static private function _parseTagOpts($ps_get_spec) {
		$va_skip_opts = ['checkAccess'];
		$va_tag_opts = $va_tag_opts = [];
		
		$va_tmp = explode('.', $ps_get_spec);
		$vs_last_element = $va_tmp[sizeof($va_tmp)-1];
		$va_tag_opt_tmp = preg_split("![\%\&]{1}!", $vs_last_element);
		if (sizeof($va_tag_opt_tmp) > 1) {
			$vs_tag_bit = array_shift($va_tag_opt_tmp); // get rid of getspec
			foreach($va_tag_opt_tmp as $vs_tag_opt_raw) {
				if (preg_match("!^\[([^\]]+)\]$!", $vs_tag_opt_raw, $va_matches)) {
					if(sizeof($va_filter = explode("=", $va_matches[1])) == 2) {
						$va_tag_filters[$va_filter[0]] = $va_filter[1];
					}
					continue;
				}
				$va_tag_tmp = explode("=", $vs_tag_opt_raw);
				$va_tag_tmp[0] = trim($va_tag_tmp[0]);
				if(sizeof($va_tag_tmp) == 1) { $va_tag_tmp[1] = true; }	// value-less options are considered "true"
				
				if (in_array($va_tag_tmp[0], $va_skip_opts)) { continue; }
				
				$va_tag_tmp[1] = trim($va_tag_tmp[1]);
				if (in_array($va_tag_tmp[0], array('delimiter', 'hierarchicalDelimiter'))) {
					$va_tag_tmp[1] = str_replace("_", " ", $va_tag_tmp[1]);
				}
				if (sizeof($va_tag_line_tmp = explode("|", $va_tag_tmp[1])) > 1) {
					$va_tag_opts[trim($va_tag_tmp[0])] = $va_tag_line_tmp;
				} else {
					$va_tag_opts[trim($va_tag_tmp[0])] = $va_tag_tmp[1];
				}
			}
			
			$va_tmp[sizeof($va_tmp)-1] = $vs_tag_bit;	// remove option from tag-part array
			$vs_tag_proc = join(".", $va_tmp);
			
			$ps_get_spec = $vs_tag_proc;
		}
		
		return ['tag' => $ps_get_spec, 'options' => $va_tag_opts, 'filters' => $va_tag_filters];
	}
	# -------------------------------------------------------------------
	/**
	 *
	 */
	static public function _getRelativeIDsForRowIDs($ps_tablename, $ps_relative_to, $pa_row_ids, $ps_mode, $pa_options=null) {
		$o_dm = Datamodel::load();
		$t_instance = $o_dm->getInstanceByTableName($ps_tablename, true);
		if (!$t_instance) { return null; }
		$t_rel_instance = $o_dm->getInstanceByTableName($ps_relative_to, true);
		if (!$t_rel_instance) { return null; }
		
		$vs_pk = $t_instance->primaryKey();
		$vs_rel_pk = $t_rel_instance->primaryKey();
		
		$o_db = new Db();
		
		switch($ps_mode) {
			case 'related':
				$va_params = array($pa_row_ids);
				if ($ps_tablename !== $ps_relative_to) {
					// related
					$vs_relationship_type_sql = null;
					if (!is_array($va_path = array_keys($o_dm->getPath($ps_tablename, $ps_relative_to))) || !sizeof($va_path)) {
						throw new Exception(_t("Cannot be path between %1 and %2", $ps_tablename, $ps_relative_to));
					}
					
					$va_joins = array();
					switch(sizeof($va_path)) {
						case 2:
							$vs_left_table = $va_path[1];
							$vs_right_table = $va_path[0];
							
							$va_relationships = $o_dm->getRelationships($vs_left_table, $vs_right_table);
							$va_conditions = array();								
							foreach($va_relationships[$vs_left_table][$vs_right_table] as $va_rel) {
								$va_conditions[] = "{$vs_left_table}.{$va_rel[0]} = {$vs_right_table}.{$va_rel[1]}";
							}
							$va_joins[] = "INNER JOIN {$vs_right_table} ON ".join(" OR ", $va_conditions);
							break;
						default:
							$va_path = array_reverse($va_path);
							$vs_left_table = array_shift($va_path);
							foreach($va_path as $vs_right_table) {
								$va_relationships = $o_dm->getRelationships($vs_left_table, $vs_right_table);
								
								$va_conditions = array();								
								foreach($va_relationships[$vs_left_table][$vs_right_table] as $va_rel) {
									$va_conditions[] = "{$vs_left_table}.{$va_rel[0]} = {$vs_right_table}.{$va_rel[1]}";
								}
								
								$va_joins[] = "INNER JOIN {$vs_right_table} ON ".join(" OR ", $va_conditions);
								$vs_left_table = $vs_right_table;
							}
						
							
							break;
					}
					
					$qr_res = $o_db->query("
						SELECT {$ps_relative_to}.{$vs_rel_pk} 
						FROM {$ps_relative_to} 
						".join("\n", $va_joins)."
						WHERE {$ps_tablename}.{$vs_pk} IN (?) {$vs_relationship_type_sql}
					", $va_params);
					$va_vals = $qr_res->getAllFieldValues($vs_rel_pk);
					
					if(!is_array($va_vals)) { $va_vals = array(); }
					return array_values(array_unique($va_vals));
					
				} elseif($vs_link = $t_instance->getSelfRelationTableName()) {
					// self relation
					
					$vs_relationship_type_sql = '';
					if ($va_relationship_types = caGetOption('restrictToRelationshipTypes', $pa_options, null)) {
						$t_rel_type = new ca_relationship_types();
						$va_relationship_type_ids = $t_rel_type->relationshipTypeListToIDs($vs_link, $va_relationship_types);
						if (is_array($va_relationship_type_ids) && sizeof($va_relationship_type_ids)) {
							$va_params[] = $va_relationship_type_ids;
							$vs_relationship_type_sql = " AND ({$vs_link}.type_id IN (?))";
						}		
					}
					if ($va_relationship_types = caGetOption('excludeRelationshipTypes', $pa_options, null)) {
						$t_rel_type = new ca_relationship_types();
						$va_relationship_type_ids = $t_rel_type->relationshipTypeListToIDs($vs_link, $va_relationship_types);
						if (is_array($va_relationship_type_ids) && sizeof($va_relationship_type_ids)) {
							$va_params[] = $va_relationship_type_ids;
							$vs_relationship_type_sql .= " AND ({$vs_link}.type_id NOT IN (?))";
						}		
					}
					
					$t_rel = $o_dm->getInstanceByTableName($vs_link, true);
					$vs_left_field = $t_rel->getLeftTableFieldName();
					$vs_right_field = $t_rel->getRightTableFieldName();
					$qr_res = $o_db->query($x="
						SELECT {$vs_link}.{$vs_left_field} 
						FROM {$vs_link} 
						WHERE {$vs_link}.{$vs_right_field} IN (?) {$vs_relationship_type_sql}
					", $va_params);
					$va_vals = $qr_res->getAllFieldValues($vs_left_field);
					
					$qr_res = $o_db->query("
						SELECT {$vs_link}.{$vs_right_field} 
						FROM {$vs_link} 
						WHERE {$vs_link}.{$vs_left_field} IN (?) {$vs_relationship_type_sql}
					", $va_params);
					$va_vals = array_merge($va_vals, $qr_res->getAllFieldValues($vs_right_field));
					
					if(!is_array($va_vals)) { $va_vals = array(); }
					return array_values(array_unique($va_vals));
				}
				break;
			default:
				throw new Exception("Unsupported mode in _getRelativeIDsForRowIDs: {$ps_mode}");
				break;
		}
		return array();
	}
	# -------------------------------------------------------------------
}