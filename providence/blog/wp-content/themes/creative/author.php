<?php get_header(); ?>
 <!-- Title, Breadcrumb-->
<div class="breadcrumb-wrapper">
	<div class="pattern-overlay">
		<div class="container">
			<div class="row">
				<div class="col-lg-6 col-md-6 col-xs-12 col-sm-6">
					<h2 class="title"><?php printf( __( 'Author Archives: %s', 'creative' ), '<span class="vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( "ID" ) ) ) . '" title="' . esc_attr( get_the_author() ) . '" rel="me">' . get_the_author() . '</a></span>' ); ?></h2>
				</div><?php
					if (function_exists('creative_breadcrumbs')) creative_breadcrumbs(); ?>
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
				<?php creative_navigation(); ?>
			</div>
			<?php get_sidebar(); ?>
		</div>
	</div>
</div>
<?php get_footer(); ?>