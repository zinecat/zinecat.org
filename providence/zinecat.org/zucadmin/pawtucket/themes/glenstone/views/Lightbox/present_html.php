<?php
	$t_set = $this->getVar('set');
	$va_items = caExtractValuesByUserLocale($t_set->getItems(array('thumbnailVersions' => array('small', 'mediumlarge'))));
?>
<!doctype html>
<html lang="en">

	<head>
		<meta charset="utf-8">

		<title><?php print $this->request->config->get('html_page_title'); ?></title>
		
		<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,400italic,600italic' rel='stylesheet' type='text/css'>

		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />

		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

		<?php print MetaTagManager::getHTML(); ?>
		<?php print AssetLoadManager::getLoadHTML($this->request); ?>

		<!-- If the query includes 'print-pdf', use the PDF print sheet -->
		<script>
			document.write( '<link rel="stylesheet" href="css/print/' + ( window.location.search.match( /print-pdf/gi ) ? 'pdf' : 'paper' ) + '.css" type="text/css" media="print">' );
		</script>

		<!--[if lt IE 9]>
		<script src="lib/js/html5shiv.js"></script>
		<![endif]-->
	</head>
	<body>
		<div class="reveal">
			<?php print caNavLink($this->request, _t("Back to Lightbox"), "", "", "Lightbox", "setDetail", array("set_id" => $t_set->get("set_id")), array("style" => "font-size:14px; padding:20px;")); ?>
			<!-- Any section element inside of this container is displayed as a slide -->
			<div class="slides">
<?php
	foreach($va_items as $vn_i => $va_item) {
	$t_object = new ca_objects($va_item['row_id']);
?>
		<section>
			<h3><?php print $t_object->get('ca_entities.preferred_labels', array('restrictToRelationshipTypes' => array('artist'))); ?></h3>
			<h3><?php print "<i>".$va_item['name']."</i>"; ?></h3>
			<p><?php print $va_item['representation_tag_mediumlarge']; ?></p>
			<h3><?php print $va_item['idno']; ?></h3>
		</section>
<?php
	}
?>
			</div>

		<script>

			// Full list of configuration options available here:
			// https://github.com/hakimel/reveal.js#configuration
			Reveal.initialize({
				controls: true,
				progress: true,
				history: true,
				center: true,

				theme: Reveal.getQueryHash().theme, // available themes are in /css/theme
				transition: Reveal.getQueryHash().transition || 'default', // default/cube/page/concave/zoom/linear/fade/none

				// Optional libraries used to extend on reveal.js
				dependencies: [
					//{ src: 'lib/js/classList.js', condition: function() { return !document.body.classList; } },
					//{ src: 'plugin/markdown/marked.js', condition: function() { return !!document.querySelector( '[data-markdown]' ); } },
					//{ src: 'plugin/markdown/markdown.js', condition: function() { return !!document.querySelector( '[data-markdown]' ); } },
					//{ src: 'plugin/highlight/highlight.js', async: true, callback: function() { hljs.initHighlightingOnLoad(); } },
					//{ src: 'plugin/zoom-js/zoom.js', async: true, condition: function() { return !!document.body.classList; } },
					//{ src: 'plugin/notes/notes.js', async: true, condition: function() { return !!document.body.classList; } }
				]
			});

		</script>

	</body>
</html>