<?php

/* ----------------------------------------------------------------------
 * themes/default/views/bundles/ca_objects_default_html.php : 
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 * ->collection display: {{{<unit relativeTo="ca_collections" delimiter="<br/>"><l>^ca_collections.preferred_labels.name</l></unit><ifcount min="1" code="ca_collections"> ➔ </ifcount>}}} 
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2013-2015 Whirl-i-Gig
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
 
	$t_object = 			$this->getVar("item");
	$va_comments = 			$this->getVar("comments");
	$vn_comments_enabled = 	$this->getVar("commentsEnabled");
	$vn_share_enabled = 	$this->getVar("shareEnabled");

?>
<div class="row">
	<div class='col-xs-12 navTop'><!--- only shown at small screen size -->
		{{{previousLink}}}{{{resultsLink}}}{{{nextLink}}}
	</div><!-- end detailTop -->
	<div class='navLeftRight col-xs-1 col-sm-1 col-md-1 col-lg-1'>
		<div class="detailNavBgLeft">
			{{{previousLink}}}{{{resultsLink}}}
		</div><!-- end detailNavBgLeft -->
	</div><!-- end col -->
	<div class='col-xs-12 col-sm-10 col-md-10 col-lg-10'>
		<div class="container"><div class="row">
			<div class='col-sm-6 col-md-6 col-lg-5 col-lg-offset-1'>
				{{{representationViewer}}}
				
				
				<div id="detailAnnotations"></div>
				
				 <?php /*print caObjectRepresentationThumbnails($this->request, $this->getVar("representation_id"), $t_object, array("returnAs" => "bsCols", "linkTo" => "carousel", "bsColClasses" => "smallpadding col-sm-3 col-md-3 col-xs-4")); ?>
				
<?php */
				# Comment and Share Tools
				if ($vn_comments_enabled | $vn_share_enabled) {
						
					print '<div id="detailTools">';
					if ($vn_comments_enabled) {
?>				
						<div class="detailTool"><a href='#' onclick='jQuery("#detailComments").slideToggle(); return false;'><span class="glyphicon glyphicon-comment"></span>Comments (<?php print sizeof($va_comments); ?>)</a></div><!-- end detailTool -->
						<div id='detailComments'><?php print $this->getVar("itemComments");?></div><!-- end itemComments -->
<?php				
					}
					if ($vn_share_enabled) {
						print '<div class="detailTool"><span class="glyphicon glyphicon-share-alt"></span>'.$this->getVar("shareLink").'</div><!-- end detailTool -->';
					}
					print '</div><!-- end detailTools -->';
				}		
//				print_r($t_object);		
?>
                {{{<ifdef code="ca_objects.description"><span class="x">^ca_objects.description
</span></ifdef>}}}
			</div><!-- end col -->
			
			<div class='col-sm-6 col-md-6 col-lg-5'>
				<H4>{{{<ifcount min="1" code="ca_entities"> ^ca_objects.preferred_labels.name ➔ </ifcount><unit relativeTo="ca_entities" delimiter="<br/>" restrictToRelationshipTypes="creator"><l>^ca_entities.preferred_labels</l></unit>}}} </H4>
				<H6>{{{<unit>^ca_objects.type_id</unit>}}}</H6>	
	
				<HR>
	{{{<ifdef code="ca_objects.source"><H6>Source:</H6>^ca_objects.source<br/></ifdef>}}}

	{{{<ifdef code="ca_objects.rights"><H6>Freedoms and Restrictions:</H6>^ca_objects.rights<br/></ifdef>}}}
	
	{{{<ifdef code="ca_objects.language"><H6>Language:</H6>^ca_objects.language<br/></ifdef>}}}
	
	{{{<ifdef code="ca_objects.coverage"><H6>Coverage:</H6>^ca_objects.coverage<br/></ifdef>}}}
	
	{{{<ifdef code="ca_objects.format_text"><H6>Format:</H6>^ca_objects.format_text<br/></ifdef>}}}
	{{{<ifdef code="ca_objects.format_text"><H6>Format:</H6>^ca_objects.format_text<br/></ifdef>}}}
				
				
				{{{<ifdef code="ca_objects.date.dates_value"><H6>Created:</H6><i>Location: </i>^ca_objects.coverage<br><i>Date: </i>^ca_objects.date.dates_value</ifdef>}}}
				{{{<ifdef code="ca_objects.dateSet.setDisplayValue"><H6>Date:</H6>^ca_objects.dateSet.setDisplayValue<br/></ifdev>}}}
				
				<hr></hr>
					<div class="row">
						<div class="col-sm-6">		
							{{{<ifcount code="ca_entities" restrictToRelationshipTypes="curator" min="1" max="1"><H6>Collection Curator:</H6></ifcount>}}}
							{{{<ifcount code="ca_entities" restrictToRelationshipTypes="curator" min="2"><H6>Collection Curators:</H6></ifcount>}}}

							{{{<unit relativeTo="ca_entities" delimiter="<br \>" restrictToRelationshipTypes="curator"><unit relativeTo="ca_entities"><l>^ca_entities.preferred_labels</l></unit> (^relationship_typename)</unit>}}}
								{{{<ifcount code="ca_entities" min="1" max="1"><H6>Author:</H6></ifcount>}}}
							{{{<ifcount code="ca_entities" min="2"><H6>Authors:</H6></ifcount>}}}

							{{{<unit relativeTo="ca_entities" delimiter="<br \>" restrictToRelationshipTypes="creator"><unit relativeTo="ca_entities"><l>^ca_entities.preferred_labels</l></unit> (^relationship_typename)</unit>}}}
							
<hr>


  {{{<ifcount code="ca_objects.related" min="1">
 <hr></hr> <H6>Related Objects</H6><unit relativeTo="ca_objects.related" delimiter="<br \>">* <l>^ca_objects.preferred_labels</l></unit>
  </ifcount>}}}




						
							{{{<ifcount code="ca_collections" min="1" max="1"><H6>Related Collections:</H6></ifcount>}}}
							{{{<ifcount code="ca_collections" min="2"><H6>Related Collection</H6></ifcount>}}}
							{{{<unit relativeTo="ca_collections" delimiter="<br/>"><l>^ca_collections.preferred_labels</l></unit>}}}
							
							{{{<unit relativeTo="ca_objects.external_link" delimiter="<br/>"><H6>Links:</H6>^ca_objects.external_link%returnAsLink=1</1></ifdef>}}}
							
							{{{<ifcount code="ca_list_items" min="1" max="1"><H6>Related Term</H6></ifcount>}}}
							{{{<ifcount code="ca_list_items" min="2"><H6>Related Terms</H6></ifcount>}}}
<!--							{{{<unit relativeTo="ca_list_items" delimiter="<br/>"> <l>^ca_list_items.preferred_labels.name_plural</1></unit>}}} -->

							  



<?php
# --- Subjects / Replacing LCSH
$va_subjects = $t_object->get("ca_list_items.preferred_labels.name_plural", array('returnAsArray' => true, 'returnAllLocales' => false));
//print_r($va_subjects);
if(sizeof($va_subjects) > 0){
//print "<div class= 'unit'><h6>"._t("Keyword").((sizeof($va_subjects) > 1) ? "s" : "")." </h2>";
print "<div>";
foreach($va_subjects as $va_subjects) {
// Regex to render the output searchable within CA
$string_for_output = caNavLink($this->request, $va_subjects, '', '', 'Search', 'Index', array('search' => 'ca_objects.ca_list_items.preferred_labels.name_plural:'.trim($va_subjects).''));

$string_for_output3 = caNavLink($this->request, $va_subjects, '', '', 'MultiSearch', 'Index', array('search' => 'ca_list_items.preferred_labels:'.$va_subjects));

$string_part1 = preg_replace('/(^<a .*?>)(.*)/ms',"$1",$string_for_output);
$string_part1 = str_replace('--','+',$string_part1);
$string_part1 = str_replace('+++','+',$string_part1);
$string_part2 =  preg_replace('/(^<a .*?>)(.*)/',"$2",$string_for_output);

//print "--".$string_part1.$string_part2;
//print $string_for_output;

print "-- ".$string_for_output3;
print "<br \> ";

 }
print "</div>";
}
?>
							{{{<ifcount code="ca_objects.LcshTerms" min="1"><H6>LC Terms</H6></ifcount>}}}
							{{{<unit delimiter="<br/>"><l>^ca_objects.LcshTerms</l></unit>}}} 
												{{{<ifcount code="ca_objects.LcshTopical" min="1" max="1"><h3>Subject Heading:</h3>^ca_objects.LcshTopical</ifcount>}}}
					{{{<ifcount code="ca_objects.LcshTopical" min="2" max="30" delimiter=";">><h3>Subject Headings:</h3>^ca_objects.LcshTopical</ifcount>}}}

							<hr></hr>
							{{{<ifdef code="ca_objects.idno"><H6>ZineCat Identifer:</H6>^ca_objects.idno<br/></ifdef>}}}




							
						</div><!-- end col -->				
						<div class="col-sm-6 colBorderLeft">
							{{{map}}}
						</div>
					</div><!-- end row -->
			</div><!-- end col -->
		</div><!-- end row --></div><!-- end container -->
	</div><!-- end col -->
	 <!--<div class='navLeftRight col-xs-1 col-sm-1 col-md-1 col-lg-1'>
		<div class="detailNavBgRight">
			{{{nextLink}}}
		</div><!-- end detailNavBgLeft -->
	<!--</div><!-- end col -->
</div><!-- end row -->

<script type='text/javascript'>
	jQuery(document).ready(function() {
		$('.trimText').readmore({
		  speed: 75,
		  maxHeight: 120
		});
	});
</script>