<?php
/* ----------------------------------------------------------------------
 * views/find/quick_search_results.php : 
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
 
 $o_search_config = caGetSearchConfig();
 
 $ps_search = $this->getVar('search');
 
 $o_dm = Datamodel::load();
 
 $vs_sort_form = caFormTag($this->request, 'Index', 'QuickSearchSortForm');
 $vs_sort_form .= _t('Sort by ').caHTMLSelect('sort', array(_t('name') => 'name', _t('idno') => 'idno'), array('onchange' => 'jQuery("#QuickSearchSortForm").submit();'), array('value' => $this->getVar('sort')));
 $vs_sort_form .= "</form>";
 
 print $vs_control_box = caFormControlBox(
		'<div class="quickSearchHeader">'._t("Top %1 results for <em>%2</em>", $this->getVar('maxNumberResults'), $this->getVar('search')).'</div>', 
		'',
		$vs_sort_form
	);
	
	$vn_num_occurrence_types = sizeof($va_occurrence_types = $this->getVar('occurrence_types'));
	
	$vn_num_result_lists_to_display = 0;
	
	$va_searches = $this->getVar('searches');
?>

<div class="quickSearchContentArea">

<?php
	$vs_visibility = (sizeof($va_searches) == 1) ? 'block' : 'none';
	foreach($va_searches as $vs_table => $va_info) {
		if ($vs_table == 'ca_occurrences') {
			$t_instance = $o_dm->getInstanceByTableName($vs_table, true);
						
			if($vn_num_occurrence_types > 0) {
				$va_occurrences_by_type = array();
				
				$o_res = $this->getVar('ca_occurrences_results');
				while($o_res->nextHit()) {
					$vs_type = $t_instance->getTypeCode((int)$o_res->get($vs_table.'.type_id'));
					if (!($vs_template = $o_search_config->get($vs_table.'_'.$vs_type.'_quicksearch_result_display_template'))) {
						$vs_template = $o_search_config->get($vs_table.'_quicksearch_result_display_template');
					}
					
					$va_occurrences_by_type[$o_res->get('ca_occurrences.type_id')][] = array(
						'name' => $vs_name = $o_res->get('ca_occurrences.preferred_labels'),
						'occurrence_id' => $o_res->get('ca_occurrences.occurrence_id'),
						'idno' => $o_res->get('ca_occurrences.idno'),
						'template' => ($vs_template) ? $o_res->getWithTemplate($vs_template) : $vs_name
					);
				}
			
				$vn_i = 0;
				foreach($va_occurrence_types as $vn_type_id => $va_type_info) {
					
					if ((!isset($va_occurrences_by_type[$vn_type_id])) || (!$va_occurrences_by_type[$vn_type_id])) {
						print "<div class='quickSearchNoResults rounded'>".unicode_ucfirst($va_type_info['name_plural'])." (0)"."</div>";
					}else{
						if (is_array($va_occurrences_by_type) && is_array($va_occurrences_by_type[$vn_type_id])) {
							$va_occurrences = $va_occurrences_by_type[$vn_type_id];
						} else {
							$va_occurrences = array();
						}
?>
						<div class="quickSearchResultHeader rounded">
							<div class="quickSearchFullResultsLink"><?php print caNavLink($this->request, caNavIcon($this->request, __CA_NAV_BUTTON_FILTER__)." "._t("Full Results &rsaquo;"), null, $va_info['searchModule'], $va_info['searchController'], $va_info['searchAction'], array("search" => urlencode($ps_search), "type_id" => $vn_type_id)); ?></div>
							<a href='#' style="text-decoration:none; color:#333;" id='show<?php print $vs_table.$vn_type_id; ?>' onclick='return caQuickSearchShowHideResults("show", "<?php print $vs_table.$vn_type_id; ?>");'><?php print unicode_ucfirst($va_type_info['name_plural'])." (".sizeof($va_occurrences_by_type[$vn_type_id]).")"; ?> <?php print caNavIcon($this->request, __CA_NAV_BUTTON_EXPAND__); ?></a>
							<a href='#' id='hide<?php print $vs_table.$vn_type_id; ?>' style='display:none; text-decoration:none; color:#333;' onclick='return caQuickSearchShowHideResults("hide", "<?php print $vs_table.$vn_type_id; ?>").hide();'><?php print unicode_ucfirst($va_type_info['name_plural'])." (".sizeof($va_occurrences_by_type[$vn_type_id]).")"; ?> <?php print caNavIcon($this->request, __CA_NAV_BUTTON_COLLAPSE__); ?></a>
						</div>
						<div class="quickSearchHalfWidthResults" id="<?php print $vs_table.$vn_type_id; ?>_results" style="display:none;">
							<ul class='quickSearchList'>
<?php
								foreach($va_occurrences as $vn_i => $va_occurrence) {
									
									if ($vs_template) {
										print '<li class="quickSearchList">'.$va_occurrence['template']."</li>\n";
									} else {
										$vs_idno_display = '';
										if ($va_occurrence['idno']) {
											$vs_idno_display = ' ['.$va_occurrence['idno'].']';
										}

										print '<li class="quickSearchList">' .
											caEditorLink($this->request, $va_occurrence['name'], null, $vs_table, $va_occurrence['occurrence_id']) .
											($vs_idno_display ? $vs_idno_display : "") . "</li>\n";
									}
								}
?>
							</ul>
							<div class="quickSearchResultHide"><a href='#' id='hide<?php print $vs_table.$vn_type_id; ?>' onclick='jQuery("#<?php print $vs_table.$vn_type_id; ?>_results").slideUp(250); jQuery("#show<?php print $vs_table.$vn_type_id; ?>").slideDown(1); jQuery("#hide<?php print $vs_table.$vn_type_id; ?>").hide(); return false;'> <?php print caNavIcon($this->request, __CA_NAV_BUTTON_COLLAPSE__); ?></a></div>
						</div>
				
<?php
						$vn_i++;
					}
				}
			}	
		} else {
			$o_res = $this->getVar($vs_table.'_results');

			if ($o_res->numHits() >= 1) { 
?>
				<div class="quickSearchResultHeader rounded" >
					<div class="quickSearchFullResultsLink"><?php print caNavLink($this->request, caNavIcon($this->request, __CA_NAV_BUTTON_FILTER__)." "._t("Full Results &rsaquo;"), null, $va_info['searchModule'], $va_info['searchController'], $va_info['searchAction'], array("search" => urlencode($ps_search))); ?></div>
					<a href='#' style="text-decoration:none; color:#333;" id='show<?php print $vs_table; ?>' onclick='return caQuickSearchShowHideResults("show", "<?php print $vs_table; ?>");'><?php print $va_info['displayname']." (".$o_res->numHits().")"; ?> <?php print caNavIcon($this->request, __CA_NAV_BUTTON_EXPAND__); ?></a>
					<a href='#' id='hide<?php print $vs_table; ?>' style='display:none; text-decoration:none; color:#333;' onclick='return caQuickSearchShowHideResults("hide", "<?php print $vs_table; ?>");'><?php print $va_info['displayname']." (".$o_res->numHits().")"; ?> <?php print caNavIcon($this->request, __CA_NAV_BUTTON_COLLAPSE__); ?></a>
				</div>
				<div class="quickSearchHalfWidthResults" id='<?php print $vs_table; ?>_results' style="display:none;">
					<ul class='quickSearchList'>
<?php
						$t_instance = $o_dm->getInstanceByTableName($vs_table, true);
						$va_type_list = $t_instance->getTypeList();
						
						$vb_show_labels = !(($vs_table === 'ca_objects') && ($t_instance->getAppConfig()->get('ca_objects_dont_use_labels')));
						
						while($o_res->nextHit()) {
							$vs_type = $t_instance->getTypeCode((int)$o_res->get($vs_table.'.type_id'));
							if (!($vs_template = $o_search_config->get($vs_table.'_'.$vs_type.'_quicksearch_result_display_template'))) {
								$vs_template = $o_search_config->get($vs_table.'_quicksearch_result_display_template');
							}
							
							if ($vs_template) {
								print '<li class="quickSearchList">'.$o_res->getWithTemplate($vs_template)."</li>\n";
							} else {
								$vs_idno_display = trim($o_res->get($va_info['displayidno']));
							
								if ($vb_show_labels) {
									$vs_label = $o_res->get($vs_table.'.preferred_labels');
								} else {
									$vs_label = $vs_idno_display;
									$vs_idno_display = '';
								}
								$vs_type_display = '';
								if (($vn_type_id = trim($o_res->get($vs_table.'.type_id'))) && $va_type_list[$vn_type_id]) {
									$vs_type_display = ' ['.$va_type_list[$vn_type_id]['name_singular'].']';
								}
								
								print '<li class="quickSearchList">' .
									caEditorLink($this->request, $vs_label, null, $vs_table, $o_res->get($va_info['primary_key'])) .
									" ".($vs_idno_display ? "({$vs_idno_display})" : "") .
									" {$vs_type_display}</li>\n";
							}
						}
	?>
					</ul>
					<div class="quickSearchResultHide"><a href='#' id='hide<?php print $vs_table; ?>' onclick='jQuery("#<?php print $vs_table; ?>_results").slideUp(250); jQuery("#show<?php print $vs_table; ?>").slideDown(1); jQuery("#hide<?php print $vs_table; ?>").hide(); return false;'> <?php print caNavIcon($this->request, __CA_NAV_BUTTON_COLLAPSE__); ?></a></div>
				</div>
<?php	
			} else {
				print "<div class='quickSearchNoResults rounded'>".$va_info['displayname']." (".$o_res->numHits().")"."</div>";
			}
		}
	}
?>
</div>

<script type="text/javascript">
	function caQuickSearchShowHideResults(m, t) {
		if (m == 'show') {
			jQuery("#" + t + "_results").slideDown(250); 
			jQuery("#show" + t).hide(); 
			jQuery("#hide" + t).show(); 
		} else {
			jQuery("#" + t + "_results").slideUp(250); 
			jQuery("#show" + t).show(); 
			jQuery("#hide" + t).hide(); 
		}	
		return false;
	}
<?php
	if (sizeof($va_searches) > 0) {
?>
		jQuery(document).ready(function() {
			caQuickSearchShowHideResults('show', '<?php print array_shift(array_keys($va_searches)); ?>');
		});
<?php
	}
?>
</script>

<div class="editorBottomPadding"><!-- empty --></div>