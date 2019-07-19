<?php
/** ---------------------------------------------------------------------
 * app/helpers/searchHelpers.php : miscellaneous functions
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2011-2015 Whirl-i-Gig
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
	require_once(__CA_MODELS_DIR__.'/ca_lists.php');


	# ---------------------------------------
	/**
	 * Get search instance for given table name
	 * @param string $pm_table_name_or_num Table name or number
	 * @return BaseSearch
	 */
	function caGetSearchInstance($pm_table_name_or_num, $pa_options=null) {
		$o_dm = Datamodel::load();
		
		$vs_table = (is_numeric($pm_table_name_or_num)) ? $o_dm->getTableName((int)$pm_table_name_or_num) : $pm_table_name_or_num;
		
		if (!($t_instance = $o_dm->getInstanceByTableName($vs_table, true))) { return null; }
		if ($t_instance->isRelationship()) { 
			require_once(__CA_LIB_DIR__.'/ca/Search/InterstitialSearch.php');
			return new InterstitialSearch($vs_table);
		}
		
		switch($vs_table) {
			case 'ca_objects':
				require_once(__CA_LIB_DIR__.'/ca/Search/ObjectSearch.php');
				return new ObjectSearch();
				break;
			case 'ca_entities':
				require_once(__CA_LIB_DIR__.'/ca/Search/EntitySearch.php');
				return new EntitySearch();
				break;
			case 'ca_places':
				require_once(__CA_LIB_DIR__.'/ca/Search/PlaceSearch.php');
				return new PlaceSearch();
				break;
			case 'ca_occurrences':
				require_once(__CA_LIB_DIR__.'/ca/Search/OccurrenceSearch.php');
				return new OccurrenceSearch();
				break;
			case 'ca_collections':
				require_once(__CA_LIB_DIR__.'/ca/Search/CollectionSearch.php');
				return new CollectionSearch();
				break;
			case 'ca_loans':
				require_once(__CA_LIB_DIR__.'/ca/Search/LoanSearch.php');
				return new LoanSearch();
				break;
			case 'ca_movements':
				require_once(__CA_LIB_DIR__.'/ca/Search/MovementSearch.php');
				return new MovementSearch();
				break;
			case 'ca_lists':
				require_once(__CA_LIB_DIR__.'/ca/Search/ListSearch.php');
				return new ListSearch();
				break;
			case 'ca_list_items':
				require_once(__CA_LIB_DIR__.'/ca/Search/ListItemSearch.php');
				return new ListItemSearch();
				break;
			case 'ca_object_lots':
				require_once(__CA_LIB_DIR__.'/ca/Search/ObjectLotSearch.php');
				return new ObjectLotSearch();
				break;
			case 'ca_object_representations':
				require_once(__CA_LIB_DIR__.'/ca/Search/ObjectRepresentationSearch.php');
				return new ObjectRepresentationSearch();
				break;
			case 'ca_representation_annotations':
				require_once(__CA_LIB_DIR__.'/ca/Search/RepresentationAnnotationSearch.php');
				return new RepresentationAnnotationSearch();
				break;
			case 'ca_user_representation_annotations':
				require_once(__CA_LIB_DIR__.'/ca/Search/UserRepresentationAnnotationSearch.php');
				return new UserRepresentationAnnotationSearch();
				break;
			case 'ca_item_comments':
				require_once(__CA_LIB_DIR__.'/ca/Search/ItemCommentSearch.php');
				return new ItemCommentSearch();
				break;
			case 'ca_item_tags':
				require_once(__CA_LIB_DIR__.'/ca/Search/ItemTagSearch.php');
				return new ItemTagSearch();
				break;
			case 'ca_relationship_types':
				require_once(__CA_LIB_DIR__.'/ca/Search/RelationshipTypeSearch.php');
				return new RelationshipTypeSearch();
				break;
			case 'ca_sets':
				require_once(__CA_LIB_DIR__.'/ca/Search/SetSearch.php');
				return new SetSearch();
				break;
			case 'ca_set_items':
				require_once(__CA_LIB_DIR__.'/ca/Search/SetItemSearch.php');
				return new SetItemSearch();
				break;
			case 'ca_tours':
				require_once(__CA_LIB_DIR__.'/ca/Search/TourSearch.php');
				return new TourSearch();
				break;
			case 'ca_tour_stops':
				require_once(__CA_LIB_DIR__.'/ca/Search/TourStopSearch.php');
				return new TourStopSearch();
				break;
			case 'ca_storage_locations':
				require_once(__CA_LIB_DIR__.'/ca/Search/StorageLocationSearch.php');
				return new StorageLocationSearch();
				break;
			case 'ca_users':
				require_once(__CA_LIB_DIR__.'/ca/Search/UserSearch.php');
				return new UserSearch();
				break;
			case 'ca_user_groups':
				require_once(__CA_LIB_DIR__.'/ca/Search/UserGroupSearch.php');
				return new UserGroupSearch();
				break;
			default:
				return null;
				break;
		}
	}
	 # ------------------------------------------------------------------------------------------------
	/**
	 *
	 */
	function caSearchLink($po_request, $ps_content, $ps_classname, $ps_table, $ps_search, $pa_other_params=null, $pa_attributes=null, $pa_options=null) {
		if (!($vs_url = caSearchUrl($po_request, $ps_table, $ps_search, false, $pa_other_params, $pa_options))) {
			return "<strong>Error: no url for search</strong>";
		}
		
		$vs_tag = "<a href='".$vs_url."'";
		
		if ($ps_classname) { $vs_tag .= " class='$ps_classname'"; }
		if (is_array($pa_attributes)) {
			$vs_tag .= _caHTMLMakeAttributeString($pa_attributes);
		}
		
		$vs_tag .= '>'.$ps_content.'</a>';
		
		return $vs_tag;
	}
	 
	# ---------------------------------------
	/**
	 * 
	 *
	 * @return string 
	 */
	function caSearchUrl($po_request, $ps_table, $ps_search=null, $pb_return_url_as_pieces=false, $pa_additional_parameters=null, $pa_options=null) {
		$o_dm = Datamodel::load();
		
		if (is_numeric($ps_table)) {
			if (!($t_table = $o_dm->getInstanceByTableNum($ps_table, true))) { return null; }
		} else {
			if (!($t_table = $o_dm->getInstanceByTableName($ps_table, true))) { return null; }
		}
		
		$vb_return_advanced = isset($pa_options['returnAdvanced']) && $pa_options['returnAdvanced'];
		
		switch($ps_table) {
			case 'ca_objects':
			case 57:
				$vs_module = 'find';
				$vs_controller = ($vb_return_advanced) ? 'SearchObjectsAdvanced' : 'SearchObjects';
				$vs_action = 'Index';
				break;
			case 'ca_object_lots':
			case 51:
				$vs_module = 'find';
				$vs_controller = ($vb_return_advanced) ? 'SearchObjectLotsAdvanced' : 'SearchObjectLots';
				$vs_action = 'Index';
				break;
			case 'ca_entities':
			case 20:
				$vs_module = 'find';
				$vs_controller = ($vb_return_advanced) ? 'SearchEntitiesAdvanced' : 'SearchEntities';
				$vs_action = 'Index';
				break;
			case 'ca_places':
			case 72:
				$vs_module = 'find';
				$vs_controller = ($vb_return_advanced) ? 'SearchPlacesAdvanced' : 'SearchPlaces';
				$vs_action = 'Index';
				break;
			case 'ca_occurrences':
			case 67:
				$vs_module = 'find';
				$vs_controller = ($vb_return_advanced) ? 'SearchOccurrencesAdvanced' : 'SearchOccurrences';
				$vs_action = 'Index';
				break;
			case 'ca_collections':
			case 13:
				$vs_module = 'find';
				$vs_controller = ($vb_return_advanced) ? 'SearchCollectionsAdvanced' : 'SearchCollections';
				$vs_action = 'Index';
				break;
			case 'ca_storage_locations':
			case 89:
				$vs_module = 'find';
				$vs_controller = ($vb_return_advanced) ? 'SearchStorageLocationsAdvanced' : 'SearchStorageLocations';
				$vs_action = 'Index';
				break;
			case 'ca_list_items':
			case 33:
				$vs_module = 'administrate/setup';
				$vs_controller = ($vb_return_advanced) ? '' : 'Lists';
				$vs_action = 'Index';
				break;
			case 'ca_object_representations':
			case 56:
				$vs_module = 'find';
				$vs_controller = ($vb_return_advanced) ? 'SearchObjectRepresentationsAdvanced' : 'SearchObjectRepresentations';
				$vs_action = 'Index';
				break;
			case 'ca_representation_annotations':
			case 82:
				$vs_module = 'find';
				$vs_controller = ($vb_return_advanced) ? 'SearchRepresentationAnnotationsAdvanced' : 'SearchRepresentationAnnotations';
				$vs_action = 'Index';
				break;
			case 'ca_user_representation_annotations':
			case 219:
				$vs_module = 'find';
				$vs_controller = ($vb_return_advanced) ? 'SearchUserRepresentationAnnotationsAdvanced' : 'SearchUserRepresentationAnnotations';
				$vs_action = 'Index';
				break;
			case 'ca_relationship_types':
			case 79:
				$vs_module = 'administrate/setup';
				$vs_controller = ($vb_return_advanced) ? '' : 'RelationshipTypes';
				$vs_action = 'Index';
				break;
			case 'ca_loans':
			case 133:
				$vs_module = 'find';
				$vs_controller = ($vb_return_advanced) ? 'SearchLoansAdvanced' : 'SearchLoans';
				$vs_action = 'Index';
				break;
			case 'ca_movements':
			case 137:
				$vs_module = 'find';
				$vs_controller = ($vb_return_advanced) ? 'SearchMovementsAdvanced' : 'SearchMovements';
				$vs_action = 'Index';
				break;
			case 'ca_tours':
			case 153:
				$vs_module = 'find';
				$vs_controller = ($vb_return_advanced) ? 'SearchToursAdvanced' : 'SearchTours';
				$vs_action = 'Index';
				break;
			case 'ca_tour_stops':
			case 155:
				$vs_module = 'find';
				$vs_controller = ($vb_return_advanced) ? 'SearchTourStopsAdvanced' : 'SearchTourStops';
				$vs_action = 'Index';
				break;
			default:
				return null;
				break;
		}
		if ($pb_return_url_as_pieces) {
			return array(
				'module' => $vs_module,
				'controller' => $vs_controller,
				'action' => $vs_action
			);
		} else {
			if (!is_array($pa_additional_parameters)) { $pa_additional_parameters = array(); }
			$pa_additional_parameters = array_merge(array('search' => $ps_search), $pa_additional_parameters);
			return caNavUrl($po_request, $vs_module, $vs_controller, $vs_action, $pa_additional_parameters);
		}
	}
	# ---------------------------------------
	/**
	 * 
	 *
	 * @return array 
	 */
	function caSearchGetAccessPoints($ps_search_expression) {
		if(preg_match("!\b([A-Za-z0-9\-\_]+):!", $ps_search_expression, $va_matches)) {
			array_shift($va_matches);
			return $va_matches;
		}
		return array();
	}
	# ---------------------------------------
	/**
	 * 
	 *
	 * @return array 
	 */
	function caSearchGetTablesForAccessPoints($pa_access_points) {
		$o_config = Configuration::load();
		$o_search_config = Configuration::load(__CA_CONF_DIR__.'/search.conf');
		$o_search_indexing_config = Configuration::load(__CA_CONF_DIR__.'/search_indexing.conf');	
			
		$va_tables = $o_search_indexing_config->getAssocKeys();
		
		$va_aps = array();
		foreach($va_tables as $vs_table) {
			$va_config = $o_search_indexing_config->getAssoc($vs_table);
			if(is_array($va_config) && is_array($va_config['_access_points'])) {
				if (array_intersect($pa_access_points, array_keys($va_config['_access_points']))) {
					$va_aps[$vs_table] = true;	
				}
			}
		}
		
		return array_keys($va_aps);
	}
	# ---------------------------------------
	/**
	 * Performs search using expression for each provided search "block." A block defines a
	 * search on a specific item (Eg. ca_objects, ca_entities), with or without type restriction, with
	 * results rendered using a provided view. The results for all blocks are returned in an array.
	 * 
	 * Used by MultiSearch to generate results. Blame Sophie for the function name.
	 *
	 * @param RequestHTTP $po_request
	 * @param string $ps_search_expression
	 * @param array $pa_blocks
	 * @param array $pa_options
	 *			itemsPerPage =
	 *			itemsPerColumn =
	 *			contexts =
	 *			... any other options passed through as-is to SearchEngine::search()
	 *
	 * @return array 
	 */
	function caPuppySearch($po_request, $ps_search_expression, $pa_blocks, $pa_options=null) {
		if (!is_array($pa_options)) { $pa_options = array(); }
		$va_access_values = caGetUserAccessValues($po_request);
 		if(is_array($va_access_values) && sizeof($va_access_values)){
 			$pa_options["checkAccess"] = $va_access_values;
 		}	
		$vn_items_per_page_default = caGetOption('itemsPerPage', $pa_options, 10);
		$vn_items_per_column_default = caGetOption('itemsPerColumn', $pa_options, 1);
		$vb_match_on_stem = caGetOption('matchOnStem', $pa_options, false);
		
		$va_contexts = caGetOption('contexts', $pa_options, array(), array('castTo' => 'array'));
		unset($pa_options['contexts']);
		
		//
		// Block are lazy-loaded using Ajax requests with additional items as they are scrolled.
		// "Ajax mode" is used by caPuppySearch to render a single block when it is scrolled
		// The block to be rendered is specified in the "block" request parameter. The offset
		// from the beginning of the result to start rendering from is specified in the "s" request parameter.
		//
		$vb_ajax_mode = false;
		if ($po_request->isAjax() && ($ps_block = $po_request->getParameter('block', pString)) && isset($pa_blocks[$ps_block])) {
			$pa_blocks = array($ps_block => $pa_blocks[$ps_block]);
			$vb_ajax_mode = true;
		}
		
		$va_ret = array();
		$vn_i = 0;
		$vn_total_cnt = 0;
		
		$va_table_counts = array();
		foreach($pa_blocks as $vs_block => $va_block_info) {
			if (!($o_search = caGetSearchInstance($va_block_info['table']))) { continue; }
			
			if (!is_array($va_block_info['options'])) { $va_block_info['options'] = array(); }
			$va_options = array_merge($pa_options, $va_block_info['options']);
			
			$va_sorts = caGetOption('sortBy', $va_block_info, null);
			$ps_sort = null;
			$vb_sort_changed = false;
 			if (!($ps_sort = $po_request->getParameter("{$vs_block}Sort", pString))) {
 				if (isset($va_contexts[$vs_block])) {
 					if(!($ps_sort = $va_contexts[$vs_block]->getCurrentSort()) && ($va_sorts) && sizeof($va_sorts)) { 
						$ps_sort = array_shift(array_keys($va_sorts));
						$va_contexts[$vs_block]->setCurrentSort($ps_sort); 
						$vb_sort_changed = true;
					//} else {
					//	if (isset($va_sorts[$ps_sort])) { 
					//		$ps_sort = $va_sorts[$ps_sort];
					//	}
					}
 				}
 			}else{
 				$vb_sort_changed = true;
 			}
 			if($vb_sort_changed && ($va_sorts) && sizeof($va_sorts)){
				# --- set the default sortDirection if available
				$va_sort_directions = caGetOption('sortDirection', $va_block_info, null);
				//$ps_sort_key = array_search($ps_sort, $va_sorts);
				if(is_array($va_sort_directions) && ($ps_sort_direction = $va_sort_directions[$ps_sort])){
					$va_contexts[$vs_block]->setCurrentSortDirection($ps_sort_direction);
				}			
 			}
 			if (!($ps_sort_direction = $po_request->getParameter("{$vs_block}SortDirection", pString))) {
 				if (!($ps_sort_direction = $va_contexts[$vs_block]->getCurrentSortDirection())) {
 					$ps_sort_direction = 'asc';
 				}
 			}
 			$va_contexts[$vs_block]->setCurrentSortDirection($ps_sort_direction); 
 			
 			$va_options['sort'] = $va_sorts[$ps_sort];
 			$va_options['sort_direction'] = $ps_sort_direction;
 			
 			$va_types = caGetOption('restrictToTypes', $va_block_info, array(), array('castTo' => 'array'));
		
			if (is_array($va_types) && sizeof($va_types)) { $o_search->setTypeRestrictions($va_types, $va_block_info); }
			$va_options['restrictSearchToFields'] = caGetOption('restrictSearchToFields', $va_block_info, null);
			$va_options['excludeFieldsFromSearch'] = caGetOption('excludeFieldsFromSearch', $va_block_info, null);
			
			if (caGetOption('dontShowChildren', $va_block_info, false)) {
				$o_search->addResultFilter('ca_objects.parent_id', 'is', 'null');	
			}
			$qr_res = $o_search->search(trim($ps_search_expression).(($vb_match_on_stem && !preg_match('![\*\"\']$!', $ps_search_expression)) ? '*' : ''), $va_options);
			
			$va_contexts[$vs_block]->setSearchExpression($ps_search_expression);
			$va_contexts[$vs_block]->setResultList($qr_res->getPrimaryKeyValues());
			
			// In Ajax mode we scroll to an offset
			$vn_start = 0;
			if ($vb_ajax_mode) {
				if (($vn_start = $po_request->getParameter('s', pInteger)) < $qr_res->numHits()) {
					$qr_res->seek($vn_start);
					if (isset($va_contexts[$vs_block])) {
						$va_contexts[$vs_block]->setParameter('start', $vn_start);
					}
				} else {
					// If the offset is past the end of the result return an empty string to halt the continuous scrolling
					return '';
				}
			} else {				
				//
				// Reset start if it's a new search
				//
				if ($va_contexts[$vs_block]->getSearchExpression(true) != $ps_search_expression) {
					$va_contexts[$vs_block]->setParameter('start', 0);
				}
			}
			$va_contexts[$vs_block]->saveContext();
			
			
			$vn_items_per_page = caGetOption('itemsPerPage', $va_block_info, $vn_items_per_page_default);
			$vn_items_per_column = caGetOption('itemsPerColumn', $va_block_info, $vn_items_per_column_default);
			
			$vn_count = $qr_res->numHits();
			$va_sort_by = caGetOption('sortBy', $va_block_info, null);
			
			$vs_sort_list = '';
			if(is_array($va_sort_by)) {
				$va_sort_list = array();
				foreach ($va_sort_by as $vs_sort_label => $vs_sort) {
					$va_sort_list[] = "<li".(($vs_sort_label == $ps_sort) ? " class='selectedSort'" : '')."><a href='#' rel='{$vs_sort_label}'>{$vs_sort_label}</a></li>";
				}
				
				$vs_sort_list = "<ul id='{$vs_block}_sort'>".join("\n", $va_sort_list)."</ul>";
			}
			
			
			$o_view = new View($po_request, $po_request->getViewsDirectoryPath());
			$o_view->setVar('result', $qr_res);
			$o_view->setVar('count', $vn_count);
			$o_view->setVar('block', $vs_block);
			$o_view->setVar('blockInfo', $va_block_info);
			$o_view->setVar('blockIndex', $vn_i);
			$o_view->setVar('start', $vn_start);
			$o_view->setVar('itemsPerPage', $vn_items_per_page);
			$o_view->setVar('itemsPerColumn', $vn_items_per_column);
			$o_view->setVar('hasMore', (bool)($vn_count > $vn_start + $vn_items_per_page));
			$o_view->setVar('sortBy', is_array($va_sort_by) ? $va_sort_by : null);
			$o_view->setVar('sortBySelect', $vs_sort_by_select = (is_array($va_sort_by) ? caHTMLSelect("{$vs_block}_sort", $va_sort_by, array('id' => "{$vs_block}_sort", "class" => "form-control input-sm"), array("value" => $ps_sort)) : ''));
			$o_view->setVar('sortByControl', ($va_block_info["sortControlType"] && ($va_block_info["sortControlType"] == "list")) ? $vs_sort_list : $vs_sort_by_select); // synonym for sortBySelect
			$o_view->setVar('sortByList', $vs_sort_list);
			$o_view->setVar('sort', $ps_sort);
			$o_view->setVar('accessValues', $va_access_values);
			
			$o_view->setVar('sortDirectionControl', '<a href="#" id="'.$vs_block.'_sort_direction"><span class="glyphicon glyphicon-sort-by-alphabet'.(($ps_sort_direction == 'desc') ? '-alt' : '').'"></span></a>');
			$o_view->setVar('sortDirection', $ps_sort_direction);
			
			
			$o_view->setVar('search', $ps_search_expression);
			$o_view->setVar('cacheKey', md5($ps_search_expression));
			
			if (!$vb_ajax_mode) {
				if (isset($va_contexts[$vs_block])) {
					$o_view->setVar('initializeWithStart', (int)$va_contexts[$vs_block]->getParameter('start'));
				} else {
					$o_view->setVar('initializeWithStart', 0);
				}
			}
			
			$vs_html = $o_view->render($va_block_info['view']);
			
			$va_ret[$vs_block] = array(
				'count' => $vn_count,
				'html' => $vs_html,
				'displayName' => $va_block_info['displayName'],
				'ids' => $qr_res->getPrimaryKeyValues(),
				'sort' => $ps_sort,
				'sortDirection' => $ps_sort_direction
			);
			$va_table_counts[$va_block_info['table']] += $vn_count;
			$vn_total_cnt += $vn_count;
			$vn_i++;
			
			if ($vb_ajax_mode) {
				// In Ajax mode return rendered HTML for the single block
				return $va_ret;
			}
		}
		$va_ret['_info_'] = array(
			'totalCount' => $vn_total_cnt
		);
		
		// Set generic contexts for each table in multisearch (no specific block); 
		// used to house search history and overall counts when there is more than one block for a given table
		foreach($va_table_counts as $vs_table => $vn_count) {
			$va_contexts["_multisearch_{$vs_table}"]->setSearchExpression($ps_search_expression);
			$va_contexts["_multisearch_{$vs_table}"]->setSearchHistory($vn_count);
			$va_contexts["_multisearch_{$vs_table}"]->saveContext();
		}
		return $va_ret;
	}
	# ---------------------------------------
	/**
	 * 
	 *
	 * @return array 
	 */
	function caSplitSearchResultByType($pr_res, $pa_options=null) {
		$o_dm = Datamodel::load();
		if (!($t_instance = $o_dm->getInstanceByTableName($pr_res->tableName(), true))) { return null; }
		
		if (!($vs_type_fld = $t_instance->getTypeFieldName())) { return null; }
		$vs_table = $t_instance->tableName();
		$va_types = $t_instance->getTypeList();
		
		$pr_res->seek(0);
		$va_type_ids = array();
		while($pr_res->nextHit()) {
			$va_type_ids[$pr_res->get($vs_type_fld)]++;
		}
		
		$va_results = array();
		foreach($va_type_ids as $vn_type_id => $vn_count) {
			$qr_res = $pr_res->getClone();
			$qr_res->filterResult("{$vs_table}.{$vs_type_fld}", array($vn_type_id));
			$qr_res->seek(0);
			$va_results[$vn_type_id] = array(
				'type' => $va_types[$vn_type_id],
				'result' =>$qr_res
			);
		}
		return $va_results;
	}
	# ---------------------------------------
	/**
	 * 
	 *
	 * @return Configuration 
	 */
	function caGetSearchConfig() {
		return Configuration::load(__CA_THEME_DIR__.'/conf/search.conf');
	}
	# ---------------------------------------
	/**
	 * 
	 *
	 * @return Configuration 
	 */
	function caGetSearchIndexingConfig() {
		return Configuration::load(__CA_CONF_DIR__.'/search_indexing.conf');
	}
	# ---------------------------------------
	/**
	 * 
	 *
	 * @return array 
	 */
	function caGetInfoForSearchType($ps_search_type) {
		$o_search_config = caGetSearchConfig();
		
		$va_search_types = $o_search_config->getAssoc('searchTypes');
		$ps_search_type = strtolower($ps_search_type);
		
		if (isset($va_search_types[$ps_search_type])) {
			return $va_search_types[$ps_search_type];
		}
		return null;
	}
	# ---------------------------------------
	/**
	 * 
	 *
	 * @return array 
	 */
	function caGetInfoForAdvancedSearchType($ps_search_type) {
		$o_search_config = caGetSearchConfig();
		
		$va_search_types = $o_search_config->getAssoc('advancedSearchTypes');
		$ps_search_type = strtolower($ps_search_type);
		
		if (isset($va_search_types[$ps_search_type])) {
			return $va_search_types[$ps_search_type];
		}
		return null;
	}
	# ---------------------------------------
	/**
	 *
	 */
	function caGetQueryStringForHTMLFormInput($po_result_context, $pa_options=null) {
		$pa_form_values = caGetOption('formValues', $pa_options, $_REQUEST);
		$va_form_contents = explode('|', caGetOption('_formElements', $pa_form_values, ''));
		
		$va_for_display = array();
	 	$va_default_values = $va_values = $va_booleans = array();
	 	
	 	foreach($va_form_contents as $vn_i => $vs_element) {
			$vs_dotless_element = str_replace('.', '_', $vs_element);
			
			switch($vs_element) {
				case '_fieldlist':
					foreach($pa_form_values[$vs_dotless_element.'_field'] as $vn_j => $vs_fieldlist_field) {
						if(!strlen(trim($vs_fieldlist_field))) { continue; }
						$va_values[$vs_fieldlist_field][] = trim($pa_form_values[$vs_dotless_element.'_value'][$vn_j]);
						$va_default_values['_fieldlist_field'][] = $vs_fieldlist_field;
						$va_default_values['_fieldlist_value'][] = trim($pa_form_values[$vs_dotless_element.'_value'][$vn_j]);
						$va_booleans["_fieldlist:boolean"][] = $va_booleans["{$vs_fieldlist_field}:boolean"][] = isset($pa_form_values["_fieldlist:boolean"][$vn_j]) ? $pa_form_values["_fieldlist:boolean"][$vn_j] : null;
						
					}
					break;
				default:
					if (!is_array($pa_form_values[$vs_dotless_element]) && $pa_form_values[$vs_dotless_element]) {
						$pa_form_values[$vs_dotless_element] = array($pa_form_values[$vs_dotless_element]);
					}
					if (is_array($pa_form_values[$vs_dotless_element])) {
						// are there relationship types?
						if (is_array($pa_form_values[$vs_dotless_element.':relationshipTypes'])) {
							$vs_element .= "/".join(";", $pa_form_values[$vs_dotless_element.':relationshipTypes']);
						}
						foreach($pa_form_values[$vs_dotless_element] as $vn_j => $vs_element_value) {
							if(!strlen(trim($vs_element_value))) { continue; }
							$va_default_values[$vs_element][] = trim($vs_element_value);
							$va_values[$vs_element][] = trim($vs_element_value);
							$va_booleans["{$vs_element}:boolean"][] = isset($pa_form_values["{$vs_dotless_element}:boolean"][$vn_j]) ? $pa_form_values["{$vs_dotless_element}:boolean"][$vn_j] : null;
						}
					}
					break;
			}
		}
		
		$po_result_context->setParameter("pawtucketAdvancedSearchFormContent_{$pa_form_values['_advancedFormName']}", $va_default_values);
		$po_result_context->setParameter("pawtucketAdvancedSearchFormBooleans_{$pa_form_values['_advancedFormName']}", $va_booleans);
		$po_result_context->saveContext();
	
	 	$va_query_elements = $va_query_booleans = array();
	 	
	 	$vb_match_on_stem = caGetOption('matchOnStem', $pa_options, false);
	 	
	 	if (is_array($va_values) && sizeof($va_values)) {
			foreach($va_values as $vs_element => $va_value_list) {
				foreach($va_value_list as $vn_i => $vs_value) {
					if (!strlen(trim($vs_value))) { continue; }
					if ((strpos($vs_value, ' ') !== false) && ($vs_value{0} != '[')) {
						$vs_query_element = '"'.str_replace('"', '', $vs_value).'"';
					} else {
						$vs_query_element = $vs_value;
					}
					
					$vs_query_element .= ($vb_match_on_stem && !preg_match('!\*$!', $vs_query_element) && preg_match('!^[\w]+$!', $vs_query_element) && !preg_match('!^[0-9]+$!', $vs_query_element)) ? '*' : '';
					
					$va_query_booleans[$vs_element][] = (isset($va_booleans["{$vs_element}:boolean"][$vn_i]) && $va_booleans["{$vs_element}:boolean"][$vn_i]) ? $va_booleans["{$vs_element}:boolean"][$vn_i] : 'AND';
					switch($vs_element){
						case '_fulltext':		// don't qualify special "fulltext" element
							$va_query_elements[$vs_element][] = $vs_query_element;
							break;
						case '_fieldlist_value':
							// noop
							break;
						case '_fieldlist_field':
							if(!strlen(trim($pa_form_values['_fieldlist_value'][$vn_i]))) { continue; }
							$va_query_elements[$vs_element][] = "(".$va_values['_fieldlist_field'][$vn_i].":".$pa_form_values['_fieldlist_value'][$vn_i].")";
							break;
						default:
							$va_query_elements[$vs_element][] = "({$vs_element}:{$vs_query_element})";
							break;
					}
				}
			}
		}
	
		$vs_query_string = '';
		foreach($va_query_elements as $vs_element => $va_query_elements_by_element) {
			$vs_query_string .= ($vs_query_string ? (($vs_b = $va_query_booleans[$vs_element][0]) ? " {$vs_b} " : ' AND ') : '').'(';
			foreach($va_query_elements_by_element as $vn_i => $vs_val) {
				$vs_query_string .= $vs_val;
				if ($vn_i < (sizeof($va_query_elements_by_element) - 1)) {
					$vs_query_string .= ' '.$va_query_booleans[$vs_element][$vn_i].' ';
				}
			}
			$vs_query_string = trim($vs_query_string). ')';
		}
		
		return $vs_query_string;
	}
	# ---------------------------------------
	/**
	 *
	 */
	function caGetDisplayStringForHTMLFormInput($po_result_context, $pa_options=null) {
		$pa_form_values = caGetOption('formValues', $pa_options, $_REQUEST);
		$va_form_contents = explode('|', caGetOption('_formElements', $pa_form_values, ''));

		$o_dm = Datamodel::load();
		
	 	$va_display_string = array();
	 	
	 	foreach($va_form_contents as $vn_i => $vs_element) {
			$vs_dotless_element = str_replace('.', '_', $vs_element);
			
			if (
				(!is_array($pa_form_values[$vs_dotless_element]) && !strlen($pa_form_values[$vs_dotless_element])) 
				|| 
				(is_array($pa_form_values[$vs_dotless_element]) && !sizeof(array_filter($pa_form_values[$vs_dotless_element])))
			) { continue; }
	
			if(!is_array($pa_form_values[$vs_dotless_element])) { $pa_form_values[$vs_dotless_element] = array($pa_form_values[$vs_dotless_element]); }
			if(!($vs_label = trim($pa_form_values[$vs_dotless_element.'_label']))) { $vs_label = "???"; }
		
			$va_fld = explode(".", $vs_element);
			$t_table = $o_dm->getInstanceByTableName($va_fld[0], true);
		
		// TODO: need universal way to convert item_ids in attributes and intrinsics to display text
			if ($t_table && ($t_table->hasField($va_fld[1]))) {
				switch($va_fld[1]) {
					case 'type_id':
						$va_values = array($t_table->getTypeName($pa_form_values[$vs_dotless_element][0]));
						break;
					default:
						$va_values = $pa_form_values[$vs_dotless_element];
						break;
				}
			} else {
				$va_tmp = explode('.', $vs_element);
				$vs_possible_element_with_rel = array_pop($va_tmp);
				$va_tmp2 = explode("/", $vs_possible_element_with_rel);
				$vs_possible_element = array_shift($va_tmp2);
				
				// TODO: display relationship types when defined?
				//$vs_relationship_type_ids = array_shift($va_tmp2);
				
				switch(ca_metadata_elements::getElementDatatype($vs_possible_element)) {
					case 3:
						$va_values = array();
						foreach($pa_form_values[$vs_dotless_element] as $vn_i => $vm_value) {
							$va_values[$vn_i] = caGetListItemByIDForDisplay($vm_value);
						}
						break;
					default:
						$va_values = $pa_form_values[$vs_dotless_element];
						break;
				}
			}
			
			$va_display_string[] = "{$vs_label}: ".join("; ", $va_values);
		}
		
		$po_result_context->setParameter("pawtucketAdvancedSearchFormDisplayString_{$pa_form_values['_advancedFormName']}", $va_display_string);
		$po_result_context->saveContext();
	
		return join("; ", $va_display_string);
	}
	# ---------------------------------------
	/**
	 * Returns all available search form placements - those data bundles that can be searches for the given content type, in other words.
	 * The returned value is a list of arrays; each array contains a 'bundle' specifier than can be passed got Model::get() or SearchResult::get() and a display name
	 *
	 * @param mixed $pm_table_name_or_num The table name or number specifying the content type to fetch bundles for. If omitted the content table of the currently loaded search form will be used.
	 * @param array $pa_options
	 *
	 * @return array And array of bundles keyed on display label. Each value is an array with these keys:
	 *		bundle = The bundle name (eg. ca_objects.idno)
	 *		display = Display label for each available bundle
	 *		description = Description of bundle
	 * 
	 * Will return null if table name or number is invalid.
	 */
	function caGetBundlesAvailableForSearch($pm_table_name_or_num, $pa_options=null) {
		$pb_for_select = caGetOption('forSelect', $pa_options, false);
		$pa_filter = caGetOption('filter', $pa_options, null);
		
		$o_dm = Datamodel::load();
		$o_config = Configuration::load();
		$o_indexing_config = caGetSearchIndexingConfig();
		
		$pm_table_name_or_num = $o_dm->getTableNum($pm_table_name_or_num);
		if (!$pm_table_name_or_num) { return null; }
		
		$t_instance = $o_dm->getInstanceByTableNum($pm_table_name_or_num, true);
		$va_search_settings = $o_indexing_config->getAssoc($o_dm->getTableName($pm_table_name_or_num));
		
		$vs_primary_table = $t_instance->tableName();
		$vs_table_display_name = $t_instance->getProperty('NAME_PLURAL');
		
		
		$va_available_bundles = array();
		
		// Full-text 
		$vs_bundle = "_fulltext";
		
		if (!((is_array($pa_filter) && sizeof($pa_filter) && !in_array($vs_bundle, $pa_filter)))) { 
			$vs_label = _t('Full text');
			$va_available_bundles[$vs_label][$vs_bundle] = array(
				'bundle' => $vs_bundle,
				'label' => $vs_label,
				'description' => $vs_description = _t('Searches on all content that has been indexed')
			);	
		}
		
		// rewrite label filters to use label tables actually used in indexing config
		if (is_array($pa_filter)) {
			foreach($pa_filter as $vn_i => $vs_filter) {
				$va_tmp = explode('.', $vs_filter);
				if (in_array($va_tmp[1], array('preferred_labels', 'nonpreferred_labels'))) {
					if ($t_filter_instance = $o_dm->getInstanceByTableName($va_tmp[0], true)) {
						$pa_filter[] = $t_filter_instance->getLabelTableName().($va_tmp[2] ? '.'.$va_tmp[2] : '');
					}
				}
			}
		}
		
		
		// get fields 
		  
		foreach($va_search_settings as $vs_table => $va_fields) {
			if (!is_array($va_fields['fields'])) { continue; }
				
			if ($vs_table == $vs_primary_table) {
				$va_element_codes = (method_exists($t_instance, 'getApplicableElementCodes') ? $t_instance->getApplicableElementCodes(null, false, false) : array());

				$va_field_list = array();
				foreach($va_fields['fields'] as $vs_field => $va_field_indexing_info) {
					if ($vs_field === '_metadata') {
						foreach($va_element_codes as $vs_code) {
							$va_field_list[$vs_code] = array();	
						}
					} else {
						$va_field_list[$vs_field] = $va_field_indexing_info;
					}
				}
				
				foreach($va_field_list as $vs_field => $va_field_indexing_info) {
					if (in_array('DONT_INCLUDE_IN_SEARCH_FORM', $va_field_indexing_info)) { continue; }
					if (is_array($pa_filter) && sizeof($pa_filter) && !in_array($vs_table.'.'.$vs_field, $pa_filter)) { continue; }
										
					if (!($va_field_info = $t_instance->getFieldInfo($vs_field))) {
						// is it an attribute?
						if (in_array($vs_field, $va_element_codes)) {
							$t_element = $t_instance->_getElementInstance($vs_field);
							if(!$t_element) { continue; }
							if (in_array($t_element->get('datatype'), array(15, 16))) { continue; } 		// skip file and media attributes - never searchable
							if (!$t_element->getSetting('canBeUsedInSearchForm')) { continue; }
				
							if (caGetBundleAccessLevel($vs_primary_table, $vs_field) == __CA_BUNDLE_ACCESS_NONE__) { continue;}
							
							$vs_bundle = $vs_table.'.'.$vs_field;
							
							$vs_label = $t_instance->getDisplayLabel($vs_bundle);
							$va_available_bundles[$vs_label][$vs_bundle] = array(
								'bundle' => $vs_bundle,
								'label' => $vs_label,
								'description' => $vs_description = $t_instance->getDisplayDescription($vs_bundle)
							);	
						}
					} else {
						if (isset($va_field_info['DONT_USE_AS_BUNDLE']) && $va_field_info['DONT_USE_AS_BUNDLE']) { continue; }
						if (in_array($va_field_info['FIELD_TYPE'], array(FT_MEDIA, FT_FILE))) { continue; }
						
						$vs_bundle = $vs_table.'.'.$vs_field;
						$vs_label = $t_instance->getDisplayLabel($vs_bundle);
						$va_available_bundles[$vs_label][$vs_bundle] = array(
							'bundle' => $vs_bundle,
							'label' => $vs_label,
							'description' => $vs_description = $t_instance->getDisplayDescription($vs_bundle)
						);
					}
				}
			} else {
				// related table
					if ($o_config->get($vs_table.'_disable')) { continue; }
					$t_table = $o_dm->getInstanceByTableName($vs_table, true);
					if ((method_exists($t_table, "getSubjectTableName") && $vs_subject_table = $t_table->getSubjectTableName())) {
						if ($o_config->get($vs_subject_table.'_disable')) { continue; }
					}
					
					if (caGetBundleAccessLevel($vs_primary_table, $vs_subject_table) == __CA_BUNDLE_ACCESS_NONE__) { continue;}
					foreach($va_fields['fields'] as $vs_field => $va_field_indexing_info) {
						if (in_array('DONT_INCLUDE_IN_SEARCH_FORM', $va_field_indexing_info)) { continue; }
						if (is_array($pa_filter) && sizeof($pa_filter) && !in_array($vs_table.'.'.$vs_field, $pa_filter)) { continue; }
							
						if (($va_field_info = $t_table->getFieldInfo($vs_field))) {
							if (isset($va_field_info['DONT_USE_AS_BUNDLE']) && $va_field_info['DONT_USE_AS_BUNDLE']) { continue; }
							
							
							$vs_bundle = $vs_table.'.'.$vs_field;
							
							$vs_related_table = caUcFirstUTF8Safe($t_table->getProperty('NAME_SINGULAR'));
							if (method_exists($t_table, 'getSubjectTableInstance')) {
								$t_subject = $t_table->getSubjectTableInstance();
								$vs_related_table = caUcFirstUTF8Safe($t_subject->getProperty('NAME_SINGULAR'));
							}
							
							$vs_label = $t_instance->getDisplayLabel($vs_bundle);
							
							$va_available_bundles[$vs_label][$vs_bundle] = array(
								'bundle' => $vs_bundle,
								'label' => $vs_label,
								'description' => $vs_description = $t_instance->getDisplayDescription($vs_bundle)
							);
						}
					}
				
			}
		}
		
		
		//
		// access points
		//
		$va_access_points = (isset($va_search_settings['_access_points']) && is_array($va_search_settings['_access_points'])) ? $va_search_settings['_access_points'] : array();
		
		foreach($va_access_points as $vs_access_point => $va_access_point_info) {
			if (isset($va_access_point_info['options']) && is_array($va_access_point_info['options'])) {
				if (in_array('DONT_INCLUDE_IN_SEARCH_FORM', $va_access_point_info['options'])) { continue; }
			}
			
			if (is_array($pa_filter) && sizeof($pa_filter) && !in_array($vs_access_point, $pa_filter)) { continue; }
			$vs_label = ((isset($va_access_point_info['name']) && $va_access_point_info['name'])  ? $va_access_point_info['name'] : $vs_access_point);
			$va_available_bundles[$vs_label][$vs_access_point] = array(
				'bundle' => $vs_access_point,
				'label' => $vs_label,
				'description' =>  $vs_description = ((isset($va_access_point_info['description']) && $va_access_point_info['description'])  ? $va_access_point_info['description'] : '')
			);
		}
		
		//
		// created and modified
		//
		foreach(array('created', 'modified') as $vs_bundle) {
			if (is_array($pa_filter) && sizeof($pa_filter) && !in_array($vs_bundle, $pa_filter)) { continue; }
			$vs_label = $t_instance->getDisplayLabel($vs_bundle);
			$va_available_bundles[$vs_label][$vs_bundle] = array(
				'bundle' => $vs_bundle,
				'label' => $vs_label,
				'description' => $vs_description = $t_instance->getDisplayDescription($vs_bundle)
			);
		}
		
		ksort($va_available_bundles);
	
		$va_sorted_bundles = array();
		foreach($va_available_bundles as $vs_k => $va_val) {
			foreach($va_val as $vs_real_key => $va_info) {
				if ($pb_for_select) {
					$va_sorted_bundles[$va_info['label']] = $vs_real_key;
				} else {
					$va_sorted_bundles[$vs_real_key] = $va_info;
				}
			}
		}
		
		// rewrite bundles to used preferred_labels notation
		if ($pb_for_select) {
			foreach($va_sorted_bundles as $vs_label => $vs_key) {
				$va_tmp = explode('.', $vs_key);
				if (preg_match('!_labels$!', $va_tmp[0])) {
					if (($t_label_instance = $o_dm->getInstanceByTableName($va_tmp[0], true)) && (is_a($t_label_instance, 'BaseLabel'))) {
						$va_sorted_bundles[$vs_label] = $t_label_instance->getSubjectTableName().'.preferred_labels'.($va_tmp[1] ? '.'.$va_tmp[1] : '');
					}
				}
			}
		}
		
		return $va_sorted_bundles;
	}
	# ---------------------------------------
	/**
	 * @param Zend_Search_Lucene_Index_Term $po_term
	 * @return Zend_Search_Lucene_Index_Term
	 */
	function caRewriteElasticSearchTermFieldSpec($po_term) {
		return new Zend_Search_Lucene_Index_Term(
			$po_term->text, (strlen($po_term->field) > 0) ? str_replace('.', '\/', str_replace('/', '|', $po_term->field)) : $po_term->field
		);
	}
	# ---------------------------------------
	/**
	 * ElasticSearch won't accept dates where day or month is zero, so we have to
	 * rewrite certain dates, especially when dealing with "open-ended" date ranges,
	 * e.g. "before 1998", "after 2012"
	 *
	 * @param string $ps_date
	 * @param bool $pb_is_start
	 * @return string
	 */
	function caRewriteDateForElasticSearch($ps_date, $pb_is_start=true) {
		// substitute start and end of universe values with ElasticSearch's builtin boundaries
		$ps_date = str_replace(TEP_START_OF_UNIVERSE,"-292275054",$ps_date);
		$ps_date = str_replace(TEP_END_OF_UNIVERSE,"9999",$ps_date);

		if(preg_match("/(\d+)\-(\d+)\-(\d+)T(\d+)\:(\d+)\:(\d+)Z/", $ps_date, $va_date_parts)) {
			// fix large (positive) years
			if(intval($va_date_parts[1]) > 9999) { $va_date_parts[1] = "9999"; }
			// fix month-less dates
			if(intval($va_date_parts[2]) < 1) { $va_date_parts[2]  = ($pb_is_start ?  "01" : "12"); }
			// fix messed up months
			if(intval($va_date_parts[2]) > 12) { $va_date_parts[2] = "12"; }
			// fix day-less dates
			if(intval($va_date_parts[3]) < 1) { $va_date_parts[3]  = ($pb_is_start ?  "01" : "31"); }
			// fix messed up days
			$vn_days_in_month = cal_days_in_month(CAL_GREGORIAN, intval($va_date_parts[2]), intval($va_date_parts[1]));
			if(intval($va_date_parts[3]) > $vn_days_in_month) { $va_date_parts[3] = (string) $vn_days_in_month; }

			// fix hours
			if(intval($va_date_parts[4]) > 23) { $va_date_parts[4] = "23"; }
			if(intval($va_date_parts[4]) < 0) { $va_date_parts[4]  = ($pb_is_start ?  "00" : "23"); }
			// minutes and seconds
			if(intval($va_date_parts[5]) > 59) { $va_date_parts[5] = "59"; }
			if(intval($va_date_parts[5]) < 0) { $va_date_parts[5]  = ($pb_is_start ?  "00" : "59"); }
			if(intval($va_date_parts[6]) > 59) { $va_date_parts[6] = "59"; }
			if(intval($va_date_parts[6]) < 0) { $va_date_parts[6]  = ($pb_is_start ?  "00" : "59"); }

			return "{$va_date_parts[1]}-{$va_date_parts[2]}-{$va_date_parts[3]}T{$va_date_parts[4]}:{$va_date_parts[5]}:{$va_date_parts[6]}Z";
		} else {
			return '';
		}
	}
	# ---------------------------------------
	/**
	 * @param Db $po_db
	 * @param int $pn_table_num
	 * @param int $pn_row_id
	 * @return array
	 */
	function caGetChangeLogForElasticSearch($po_db, $pn_table_num, $pn_row_id) {
		$qr_res = $po_db->query("
				SELECT ccl.log_id, ccl.log_datetime, ccl.changetype, u.user_name
				FROM ca_change_log ccl
				LEFT JOIN ca_users AS u ON ccl.user_id = u.user_id
				WHERE
					(ccl.logged_table_num = ?) AND (ccl.logged_row_id = ?)
					AND
					(ccl.changetype <> 'D')
			", $pn_table_num, $pn_row_id);

		$va_return = array();
		while($qr_res->nextRow()) {
			$vs_change_date = caGetISODates(date("c", $qr_res->get('log_datetime')))['start'];
			if ($qr_res->get('changetype') == 'I') {
				$va_return["created"][] = $vs_change_date;

				if($vs_user = $qr_res->get('user_name')) {
					$va_return["created/{$qr_res->get('user_name')}"][] = $vs_change_date;
				}
			} else {
				$va_return["modified"][] = $vs_change_date;

				if($vs_user = $qr_res->get('user_name')) {
					$va_return["modified/{$qr_res->get('user_name')}"][] = $vs_change_date;
				}
			}
		}

		return $va_return;
	}
