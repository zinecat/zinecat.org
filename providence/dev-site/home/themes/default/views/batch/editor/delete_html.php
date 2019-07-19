<?php
/* ----------------------------------------------------------------------
 * views/batch/editor/delete_html.php : 
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2013 Whirl-i-Gig
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
 	$t_set = $this->getVar('t_set');
?>
<div class="sectionBox">
<?php
	if (!$this->getVar('confirmed')) {
		// show delete confirmation notice
		print caFormTag($this->request, 'Delete', 'caDeleteForm', null, 'post', 'multipart/form-data', '_top', array('disableUnsavedChangesWarning' => true));
		print "<div class='delete-control-box'>".caFormControlBox(
			"<div class='delete_warning_box'>"._t('Really delete ALL records in "%1"?', $t_set->getLabelForDisplay())."</div>",
			"",
			caFormSubmitButton($this->request, __CA_NAV_BUTTON_DELETE__, _t("Delete"), 'caDeleteForm', array()).
			caNavButton($this->request, __CA_NAV_BUTTON_CANCEL__, _t("Cancel"), '', "batch", "Editor", "Edit", array('set_id' => $this->getVar('set_id')))
		)."</div>\n";
		
		print caHTMLHiddenInput('confirm', array('value' => 1));

		print caHTMLHiddenInput($t_set->primaryKey(), array('value' => $t_set->getPrimaryKey()));
		print "</form>\n";
	} else {

		AssetLoadManager::register("sortableUI");
?>
<h1><?php print _t('Batch processing status'); ?></h1>


<div class="batchProcessingTableProgressGroup">
	<div id="batchProcessingTableStatus" class="batchProcessingStatus"> </div>
	<div id="progressbar"></div>
</div>

<div id="batchProcessingCounts"></div>
<div id="batchProcessingElapsedTime"></div>

<br class="clear"/>

<div id="batchProcessingReport"></div>
<div class="editorBottomPadding"><!-- empty --></div>

<div id="batchProcessingMore">
	<?php print caNavLink($this->request, _t('Leave batch editor and delete this set'), '', 'manage/sets', 'SetEditor', 'Delete', array('set_id' => $this->getVar('set_id'))); ?>
</div>
	
<script type="text/javascript">
		jQuery('#progressbar').progressbar({
			value: 0
		});
</script>

<?php
		function caIncrementBatchEditorProgress($po_request, $pn_rows_complete, $pn_total_rows, $ps_message, $pn_elapsed_time, $pn_memory_used, $pn_num_processed, $pn_num_errors) {
			$pn_percentage = ($pn_rows_complete/$pn_total_rows) * 100;
			if (is_null($ps_message)) {
				$ps_message = _t('Processed %1/%2', $pn_rows_complete, $pn_total_rows);
			}
			$ps_message = addslashes($ps_message);
			print "<script type='text/javascript'>";
			print "jQuery('#progressbar').progressbar('value',{$pn_percentage}); jQuery('#batchProcessingTableStatus').html('{$ps_message}');";
			print "jQuery('#batchProcessingElapsedTime').html('".caFormatInterval($pn_elapsed_time)."/".sprintf("%4.2f mb", ($pn_memory_used/ 1048576))."');"; 
			print "jQuery('#batchProcessingCounts').html('".addslashes(_t("%1 processed; %2 errors", $pn_num_processed, $pn_num_errors))."');"; 
			
			print "</script>";
			caFlushOutput();
		}
		
		function caCreateBatchEditorResultsReport($po_request, $pa_general, $pa_notices, $pa_errors) {
			$vs_buf = '';
			if (is_array($pa_errors) && sizeof($pa_errors)) {
				$vs_buf .= '<div class="batchProcessingReportSectionHead">'._t('Errors occurred').':</div><ul>';
				foreach($pa_errors as $vn_id => $va_error) {
					$va_error_list = array();
					foreach($va_error['errors'] as $o_error) {
						$va_error_list[] = $o_error->getErrorDescription();
					}
					$vs_buf .= "<li><em>".caEditorLink($po_request, $va_error['label'], '', $pa_general['table'], $vn_id)."</em> (".$va_error['idno']."): ".join("; ", $va_error_list)."</li>";
				}
				$vs_buf .= "</ul>";
				
				$vs_buf .= '<div class="batchProcessingReportSectionWarning">'._t('Note: The above record(s) were not deleted due to the errors.')."</div>";
			}
			if (is_array($pa_notices) && sizeof($pa_notices)) {
				$vs_buf .= '<div class="batchProcessingReportSectionHead">'._t('Deleted successfully').':</div><ol>';
				foreach($pa_notices as $vn_id => $va_notice) {
					$vs_buf .= "<li><em>".preg_replace("![\r\n\t]+!", " ", $va_notice['label'])."</em> (".$va_notice['idno']."): ".$va_notice['status']."</li>";
				}
				$vs_buf .= "</ol>";
			}
			
			print "<script type='text/javascript'>";
			print "jQuery('#batchProcessingReport').html('".addslashes($vs_buf)."').fadeIn(300);"; 
			print "jQuery('#batchProcessingMore').fadeIn(300);"; 
			print "</script>";
			caFlushOutput();
		}
	}
?>
</div>