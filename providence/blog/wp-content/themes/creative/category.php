<?php get_header(); ?>
<!-- Title, Breadcrumb -->
<div class="breadcrumb-wrapper">
	<div class="pattern-overlay">
		<div class="container">
			<div class="row">
				<div class="col-lg-6 col-md-6 col-xs-12 col-sm-6">
					<h2 class="title"><?php printf( __( 'Category Archives: %s', 'creative' ),  ucfirst(single_cat_title( '', false )) ); ?></h2>
				</div><?php
					creative_breadcrumbs(); ?>
			</div>
		</div>
	</div>
</div>
<!-- /Title, Breadcrumb -->
<!-- Main Content -->
<div class="content margin-top60 margin-bottom60">
	<div class="container">
		<div class="row">
			<div class="posts-block col-lg-8 col-md-8 col-sm-8 col-xs-12">
				<!--  Single Post --><?php
				if(have_posts()):
					while(have_posts()): the_post();
						get_template_part('loop');
					endwhile;
				endif; ?>
				<!--  /Single Post -->
				<!-- Star -->
				<div class="star">
					<div class="row">
						<div class="col-md-12">
							<div class="star-divider">
								<div class="star-divider-icon">
									<i class=" fa fa-star"></i>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- Star -->
				<?php creative_navigation(); ?>
			</div><?php
				get_sidebar(); ?>
		</div>
	</div>
</div>
<?php get_footer(); ?>