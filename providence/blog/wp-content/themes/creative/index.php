<?php
get_header(); 
get_template_part('header_call_out'); ?>
<!-- Main Content -->
<div class="content margin-top60 margin-bottom60">
	<div class="container">
		<div class="row">
			<!-- Blog Posts -->
			<div class="posts-block col-lg-8 col-md-8 col-sm-8 col-xs-12">
			<?php  if(have_posts()): while(have_posts()): the_post(); ?>
				<!-- Blog Post 1 -->
				<?php
				get_template_part('loop');
				 ?>
				<!-- /Blog Post 1 -->
				<!-- Star-->
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
				<!-- /Star -->
				<?php 
				endwhile;
				 creative_navigation();
				endif;?>
			</div>
			<!-- /Blog Posts -->
			<!-- Sidebar -->
			<?php get_sidebar(); ?>
			<!-- /Sidebar -->
		</div>
	</div>
</div>
<!-- /Main Content -->
<?php get_footer(); ?>