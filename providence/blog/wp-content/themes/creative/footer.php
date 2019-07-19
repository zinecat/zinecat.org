<?php /**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package creative
 */ ?>

		</section>
			<footer id="footer">
				<div class="pattern-overlay">
					<!-- Footer Top -->
					<div class="footer-top">
						<div class="container">
							<div class="row">
								<?php if ( is_active_sidebar( 'footer-widget-area' ) )
								{   dynamic_sidebar( 'footer-widget-area' );
								}else
								{   $args = array(
									'before_widget' => ' <section class="col-lg-3 col-md-3 col-xs-12 col-sm-3 footer-one wow fadeIn">',
									'after_widget'  => '</section>',
									'before_title'  => ' <h3 class="light">',
									'after_title'   => '</h3>' );
									the_widget('WP_Widget_Archives', null, $args);
									the_widget('WP_Widget_Recent_Posts', null, $args);
									the_widget('WP_Widget_Meta', null, $args);
									the_widget('WP_Widget_Calendar', null, $args);
								} ?>
								</div>
						</div>
					</div>
					<!-- /Footer Top -->
					<!-- Footer Bottom -->
					<?php $wl_theme_options = creative_general_options(); ?>
					<div class="footer-bottom">
						<div class="container">
							<div class="row">
								<?php if($wl_theme_options['home_footer_enabled']=='on'){ ?>
									<div class="col-lg-6 col-md-6 col-xs-12">
										<p class="credits creative_footer_customizations"><?php echo esc_attr($wl_theme_options['footer_customizations'])." ".esc_attr($wl_theme_options['developed_by_text'])." "; ?><a href='<?php if($wl_theme_options["developed_by_link"]!="" ){ echo esc_url($wl_theme_options["developed_by_link"]);} ?>'><?php echo esc_attr($wl_theme_options['developed_by_creative_text']); ?></a></p>
									</div>
								<?php } ?>
								<?php $wl_theme_options = creative_general_options();
									if($wl_theme_options['footer_social_media_enabled']=='on'){ ?>
								<div class="col-lg-6 col-md-6 col-xs-12">
									<ul class="social pull-right creative_social_footer">
									<?php if($wl_theme_options['facebook_link']!='') { ?>
										<li class="facebook"><a href="<?php echo esc_url($wl_theme_options['facebook_link']); ?>"><i class="fa fa-facebook"></i></a></li>
										<?php } if($wl_theme_options['twitter_link']!='') {?>
										<li class="twitter"><a href="<?php echo esc_url($wl_theme_options['twitter_link']); ?>"><i class="fa fa-twitter"></i></a></li>
										<?php } if($wl_theme_options['dribbble_link']!='') {?>
										<li class="dribbble"><a href="<?php echo esc_url($wl_theme_options['dribbble_link']); ?>"><i class="fa fa-dribbble"></i></a></li>
										<?php } if($wl_theme_options['linkedin_link']!='') {?>
										<li class="linkedin"><a href="<?php echo esc_url($wl_theme_options['linkedin_link']); ?>"><i class="fa fa-linkedin"></i></a></li>
										<?php } if($wl_theme_options['rss_link']!='') {?>
										<li class="rss"><a href="<?php echo esc_url($wl_theme_options['rss_link']); ?>"><i class="fa fa-rss"></i></a></li>
										<?php } if($wl_theme_options['youtube_link']!='') { ?>
										<li class="youtube"><a href="<?php echo esc_url($wl_theme_options['youtube_link']); ?>"><i class="fa fa-youtube"></i></a></li>
									<?php } if($wl_theme_options['instagram_link']!='') { ?>
									<li class="instagram"><a href="<?php echo esc_url($wl_theme_options['instagram_link']); ?>"><i class="fa fa-instagram"></i></a></li>
									<?php } if($wl_theme_options['googleplus_link']!='') { ?>
									<li class="instagram"><a href="<?php echo esc_url($wl_theme_options['googleplus_link']); ?>"><i class="fa fa-google-plus"></i></a></li>
									<?php } ?>
									</ul>
								</div>
								<?php } ?>
							</div>
						</div>
					</div>
					<!-- /Footer Bottom -->
					<!-- /Footer Bottom -->
				</div>
			</footer>
			<?php
			$wl_theme_options = creative_general_options();
			//  custom css code
			if($wl_theme_options['custom_css']!='') { echo "<style>".$wl_theme_options['custom_css']."</style>"; }
			?>
			<!-- Scroll To Top -->
			<a href="#" class="scrollup"><i class="fa fa-angle-up"></i></a>
		</div>
		<!-- /Wrap -->
		<script type="text/javascript">
			jQuery(function() {
				var Page = (function() {
					var $nav = jQuery( '#nav-dots > span' ),
						slitslider = jQuery( '#slider' ).slitslider( {
							onBeforeChange : function( slide, pos ) {
								$nav.removeClass( 'nav-dot-current' );
								$nav.eq( pos ).addClass( 'nav-dot-current' );
							}
						} ),
						init = function() {
							initEvents();
						},
						initEvents = function() {
							$nav.each( function( i ) {
								jQuery( this ).on( 'click', function( event ) {
									var $dot = jQuery( this );
									if( !slitslider.isActive() ) {
										$nav.removeClass( 'nav-dot-current' );
										$dot.addClass( 'nav-dot-current' );
									}
									slitslider.jump( i + 1 );
									return false;
								} );
							} );
						};
						return { init : init };
				})();
				
				Page.init();
				/**
				* Notes:
				*
				* example how to add items:
				*/
			}); 
		</script>
		<?php get_template_part('font', 'family'); ?>
		<?php wp_footer(); ?>
	</body>
</html>