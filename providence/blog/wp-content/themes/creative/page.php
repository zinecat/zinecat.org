<?php
get_header();
get_template_part('header_call_out');
?>
<!-- Main Content -->
<div class="content margin-top60 margin-bottom60">
	<?php the_post(); ?>
	<div class="container">
		<div class="row">
			<!-- Left Section -->
			<div class="posts-block col-lg-8 col-md-8 col-sm-8 col-xs-12">
				<article class="post hentry"><?php
					if(has_post_thumbnail()){
						$post_thumbnail_id = get_post_thumbnail_id();
						$post_image_url = wp_get_attachment_url($post_thumbnail_id); ?>
						<div class="post-image">
							<a href="<?php echo esc_url( $post_image_url ); ?>" data-rel="prettyPhoto">
								<span class="img-hover"></span>
								<span class="fullscreen"><i class="fa fa-plus"></i></span><?php
								 $class=array('class'=>'img_responsive');
									the_post_thumbnail('blog_left_thumb', $class); ?>
							</a>
						</div><?php
					} ?>
					<div class="post-content">
						<?php the_content(); ?>
					</div><?php
					wp_link_pages(); ?>
				</article><?php
					comments_template( '', true );  ?>
				<!-- /Left Section -->
			</div>
				<!-- Sidebar -->
			<?php get_sidebar(); ?>
			<!-- /Sidebar -->
		</div>
	</div>
</div>
<!-- /Main Content -->
<?php get_footer(); ?>