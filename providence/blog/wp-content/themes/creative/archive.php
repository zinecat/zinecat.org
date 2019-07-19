<?php 
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package creative
 */
get_header(); ?>
<!-- Title, Breadcrumb -->
<div class="breadcrumb-wrapper">
	<div class="pattern-overlay">
		<div class="container">
			<div class="row">
				<div class="col-lg-6 col-md-6 col-xs-12 col-sm-6">
					<h2 class="title"><?php if ( is_day() ):
						printf( __( 'Daily Archives: %s', 'creative' ), '<span>' . get_the_date() . '</span>' );
					elseif ( is_month() ) :
							printf( __( 'Monthly Archives: %s', 'creative' ), '<span>' . get_the_date( _x( 'F Y', 'monthly archives date format', 'creative' ) ) . '</span>' );
					elseif ( is_year() ) :
							printf( __( 'Yearly Archives: %s', 'creative' ), '<span>' . get_the_date( _x( 'Y', 'yearly archives date format', 'creative' ) ) . '</span>' );
					else :
							_e( 'Archives', 'creative' );
					endif; ?></h2>
				</div>
					<?php if (function_exists('creative_breadcrumbs')) creative_breadcrumbs(); ?>
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
			</div><?php
			get_sidebar(); ?>
		</div>
	</div>
</div>
<?php get_footer(); ?>