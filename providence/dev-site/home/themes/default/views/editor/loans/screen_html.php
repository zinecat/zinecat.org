<?php
/* ----------------------------------------------------------------------
 * app/views/editor/loans/screen_html.php : 
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2011-2013 Whirl-i-Gig
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
 	$t_loan 			= $this->getVar('t_subject');
	$vn_loan_id 		= $this->getVar('subject_id');
	$vn_above_id 		= $this->getVar('above_id');

	$vb_can_edit	 	= $t_loan->isSaveable($this->request);
	$vb_can_delete		= $t_loan->isDeletable($this->request);

	$vs_rel_table		= $this->getVar('rel_table');
	$vn_rel_type_id		= $this->getVar('rel_type_id');
	$vn_rel_id			= $this->getVar('rel_id');
	
	if ($vb_can_edit) {
		$va_cancel_parameters = ($vn_loan_id ? array('loan_id' => $vn_loan_id) : array('type_id' => $t_loan->getTypeID()));
		print $vs_control_box = caFormControlBox(
			caFormSubmitButton($this->request, __CA_NAV_BUTTON_SAVE__, _t("Save"), 'LoanEditorForm').' '.
			($this->getVar('show_save_and_return') ? caFormSubmitButton($this->request, __CA_NAV_BUTTON_SAVE__, _t("Save and return"), 'LoanEditorForm', array('isSaveAndReturn' => true)) : '').' '.
			caNavButton($this->request, __CA_NAV_BUTTON_CANCEL__, _t("Cancel"), '', 'editor/loans', 'LoanEditor', 'Edit/'.$this->request->getActionExtra(), $va_cancel_parameters),
			'', 
			((intval($vn_loan_id) > 0) && $vb_can_delete) ? caNavButton($this->request, __CA_NAV_BUTTON_DELETE__, _t("Delete"), '', 'editor/loans', 'LoanEditor', 'Delete/'.$this->request->getActionExtra(), array('loan_id' => $vn_loan_id)) : ''
		);
	}
?>
	<div class="sectionBox">
<?php
			print caFormTag($this->request, 'Save/'.$this->request->getActionExtra().'/loan_id/'.$vn_loan_id, 'LoanEditorForm', null, 'POST', 'multipart/form-data');
			
			$va_form_elements = $t_loan->getBundleFormHTMLForScreen($this->request->getActionExtra(), array(
									'request' => $this->request, 
									'formName' => 'LoanEditorForm'), $va_bundle_list);
									
			print join("\n", $va_form_elements);
			
			if ($vb_can_edit) { print $vs_control_box; }
?>
			<input type='hidden' name='loan_id' value='<?php print $vn_loan_id; ?>'/>
			<input type='hidden' name='above_id' value='<?php print $vn_above_id; ?>'/>
			<input id='isSaveAndReturn' type='hidden' name='is_save_and_return' value='0'/>
			<input type='hidden' name='rel_table' value='<?php print $vs_rel_table; ?>'/>
			<input type='hidden' name='rel_type_id' value='<?php print $vn_rel_type_id; ?>'/>
			<input type='hidden' name='rel_id' value='<?php print $vn_rel_id; ?>'/>
<?php
			if($this->request->getParameter('rel', pInteger)) {
?>
				<input type='hidden' name='rel' value='1'/>
<?php
			}
?>
		</form>
	</div>

	<div class="editorBottomPadding"><!-- empty --></div>
	
	<?php print caSetupEditorScreenOverlays($this->request, $t_loan, $va_bundle_list); ?>