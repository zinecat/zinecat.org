<?php
/* ----------------------------------------------------------------------
 * bundles/ca_user_groups.php : 
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2011-2014 Whirl-i-Gig
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
 
	$vs_id_prefix 		= $this->getVar('placement_code').$this->getVar('id_prefix');
	$t_instance 		= $this->getVar('t_instance');
	$t_item 			= $this->getVar('t_group');				// user group
	$t_rel 				= $this->getVar('t_rel');			// *_x_user_groups instance (eg. ca_sets_x_user_groups)
	$t_subject 			= $this->getVar('t_subject');		
	$va_settings 		= $this->getVar('settings');
	$vs_add_label 		= $this->getVar('add_label');
	
	$vb_read_only		=	((isset($va_settings['readonly']) && $va_settings['readonly'])  || ($this->request->user->getBundleAccessLevel($t_instance->tableName(), 'ca_users') == __CA_BUNDLE_ACCESS_READONLY__));
	
	
	$va_initial_values = $this->getVar('initialValues');
	if (!is_array($va_initial_values)) { $va_initial_values = array(); }
	
	print caEditorBundleShowHideControl($this->request, $vs_id_prefix);
	print caEditorBundleMetadataDictionary($this->request, $vs_id_prefix, $va_settings);
?>
<div id="<?php print $vs_id_prefix; ?>">
<?php
	//
	// The bundle template - used to generate each bundle in the form
	//
?>
	<textarea class='caItemTemplate' style='display: none;'>
		<div id="<?php print $vs_id_prefix; ?>Item_{n}" class="labelInfo">
			<table class="caListItem">
				<tr>
					<td class="formLabel">
						<?php print _t('Group'); ?>
						<input type="text" size="30" name="<?php print $vs_id_prefix; ?>_autocomplete{n}" value="{{label}}" id="<?php print $vs_id_prefix; ?>_autocomplete{n}" class="lookupBg"/>
						<?php print $t_rel->htmlFormElement('access', '^ELEMENT', array('name' => $vs_id_prefix.'_access_{n}', 'id' => $vs_id_prefix.'_access_{n}', 'no_tooltips' => true, 'value' => '{{access}}')); ?>
						<?php if ($t_rel->hasField('effective_date')) { print _t(' for period ').$t_rel->htmlFormElement('effective_date', '^ELEMENT', array('name' => $vs_id_prefix.'_effective_date_{n}', 'value' => '{{effective_date}}',  'no_tooltips' => true, 'classname'=> 'dateBg')); } ?>
						<input type="hidden" name="<?php print $vs_id_prefix; ?>_id{n}" id="<?php print $vs_id_prefix; ?>_id{n}" value="{id}"/>
					</td>
					<td>
<?php
	if (!$vb_read_only) {
?>	
						<a href="#" class="caDeleteItemButton"><?php print caNavIcon($this->request, __CA_NAV_BUTTON_DEL_BUNDLE__); ?></a>
<?php
	}
?>
					</td>
				</tr>
			</table>
		</div>
	</textarea>
	
	<div class="bundleContainer">
		<div class="caItemList">
		
		</div>
<?php
	if (!$vb_read_only) {
?>	
		<div class='button labelInfo caAddItemButton'><a href='#'><?php print caNavIcon($this->request, __CA_NAV_BUTTON_ADD__); ?> <?php print $vs_add_label ? $vs_add_label : _t("Add  group access"); ?></a></div>
<?php
	}
?>
	</div>
</div>
			
<script type="text/javascript">
	jQuery(document).ready(function() {
		caUI.initRelationBundle('#<?php print $vs_id_prefix; ?>', {
			fieldNamePrefix: '<?php print $vs_id_prefix; ?>_',
			templateValues: ['label', 'effective_date', 'access', 'id'],
			initialValues: <?php print json_encode($va_initial_values); ?>,
			initialValueOrder: <?php print json_encode(array_keys($va_initial_values)); ?>,
			itemID: '<?php print $vs_id_prefix; ?>Item_',
			templateClassName: 'caItemTemplate',
			itemListClassName: 'caItemList',
			addButtonClassName: 'caAddItemButton',
			deleteButtonClassName: 'caDeleteItemButton',
			showEmptyFormsOnLoad: 0,
			readonly: <?php print $vb_read_only ? "true" : "false"; ?>,
			autocompleteUrl: '<?php print caNavUrl($this->request, 'lookup', 'UserGroup', 'Get', array()); ?>'
		});
	});
</script>