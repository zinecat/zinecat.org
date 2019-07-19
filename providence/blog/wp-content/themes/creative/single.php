<?php
get_header();
get_template_part('header_call_out');
?>
<!-- Main Content -->
<div class="content margin-top60 margin-bottom60">
	<div class="container">
		<div class="row">
			<div class="posts-block col-lg-12 col-md-12 col-xs-12"><?php
				if(have_posts()):
					while(have_posts()): the_post();
						get_template_part('loop');
						creative_page_nav_link();
						comments_template( '', true );
					endwhile;
				endif;?>
			</div>
		</div>
	</div>
</div>
<?php get_footer(); ?>