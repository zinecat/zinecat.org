<?php
/**
 * Sample implementation of the Custom Header feature.
 *
 * You can add an optional custom header image to header.php like so ...
 *
	<?php if ( get_header_image() ) : ?>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
		<img src="<?php header_image(); ?>" width="<?php echo esc_attr( get_custom_header()->width ); ?>" height="<?php echo esc_attr( get_custom_header()->height ); ?>" alt="">
	</a>
	<?php endif; // End header image check. ?>
 *
 * @link https://developer.wordpress.org/themes/functionality/custom-headers/
 *
 * @package eDS_Opener
 */

/**
 * Set up the WordPress core custom header feature.
 *
 * @uses creative_header_style()
 */
function creative_custom_header_setup() {
	add_theme_support( 'custom-header', apply_filters( 'creative_custom_header_args', array(
		'default-image'          => '',
		'default-text-color'     => 'ffffff',
		'width'                  => 1000,
		'height'                 => 250,
		'flex-height'            => true,
		'wp-head-callback'       => 'creative_header_style',
	) ) );
}
add_action( 'after_setup_theme', 'creative_custom_header_setup' );

if ( ! function_exists( 'creative_header_style' ) ) :
/**
 * Styles the header image and text displayed on the blog.
 *
 * @see wp_news_custom_header_setup().
 */
function creative_header_style() {
	$header_text_color = get_header_textcolor();

	/*
	 * If no custom options for text are set, let's bail.
	 * get_header_textcolor() options: Any hex value, 'blank' to hide text. Default: HEADER_TEXTCOLOR.
	 */
	if ( get_theme_support( 'custom-header', 'default-text-color' ) === $header_text_color ) {
		return;
	}		

	// If we get this far, we have custom styles. Let's do this.
	?>
	<style type="text/css">
	<?php
		// Has the text been hidden?
		if ( ! display_header_text() ) :  ?>
		.logo h1,.logo h1:hover {
		color: rgba(241, 241, 241, 0);
		position:absolute;
		clip: rect(1px 1px 1px 1px);
		}
		.logo p {
		color: rgba(241, 241, 241, 0);
		position:absolute;
		clip: rect(1px 1px 1px 1px);
		}
		.phone-login a, .phone-login i {
		color: #4d5258 ;
		}
		a.creative_social_phone{
			 color: #4d5258 ;
		}
	<?php
		// If the user has set a custom color for the text use that.
		else :	?>
		.logo h1, .logo p, .phone-login a, .phone-login i, a.creative_social_phone
			color: #<?php echo esc_attr( $header_text_color ); ?>;
		}
	<?php endif; ?>
	</style>
	<?php } endif; ?>