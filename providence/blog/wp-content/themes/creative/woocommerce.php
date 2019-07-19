<?php get_header(); ?>
<div class="breadcrumb-wrapper">
	<div class="pattern-overlay">
		<div class="container">
			<div class="row">
				<div class="col-lg-6 col-md-6 col-xs-12 col-sm-6">
					<h2 class="title">
						<?php	global $wp;
								echo $current_url = (add_query_arg(array(),$wp->request)); ?>
					</h2>
				</div>
				<?php if(!is_home()){
				 if (function_exists('creative_breadcrumbs')) creative_breadcrumbs();
				 } ?>
			</div>
		</div>
	</div>
</div>
<div class="container">
	<div class="col-md-8 content_left top_margin">
        <?php woocommerce_content(); ?>   
<!-- end content left side -->
<div class="top_margin">
<?php get_sidebar(); ?>
</div>
</div>
<div class="margin_top5"></div>	
<?php get_footer(); ?>