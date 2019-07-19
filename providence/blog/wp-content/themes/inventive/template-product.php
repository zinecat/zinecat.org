<?php  //Template Name: Product page
get_header();

$wl_theme_options = creative_general_options(); 
$slider_type = "uris";
if($wl_theme_options['plugin_slider']=="on"){

	/**
	 * Detect plugin. For use on Front End only.
	 */
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	// check for plugin using plugin name
	if ( is_plugin_active( 'ultimate-responsive-image-slider/ultimate-responsive-image-slider.php' ) ) {
	?>
	<style>
	.slider-pro {
		margin-bottom: 0px;
	}
	</style>
	<?php 
	  //plugin is activated 
	  $args = array('post_type' => 'ris_gallery');
		global $URISP_Sliders;
		$URISP_Sliders = new WP_Query( $args );	
		$i = 1;		
		if( $URISP_Sliders->have_posts() ) { 
		while ( $URISP_Sliders->have_posts() ) : $URISP_Sliders->the_post();
			  $id_slider = get_the_ID(); 
			 echo do_shortcode('[URIS id='.$id_slider.']');
			if($i=="1"){
			 break;
			}
			$i++;
		endwhile; 
		} else { ?>
	<div id="slider" class="sl-slider-wrapper tp-banner-container slider_error">
		<div class="sl-slider fullwidthbanner rslider tp-banner" >
		<h1 class="no_plugin">No Slider is Added</h1>
		<h3 class="no_plugin_desc">Please add Slider from Admin Panel!</h3>
		</div>
	</div>
	<?php }
	  
	  
	}else{ ?>
	<div id="slider" class="sl-slider-wrapper tp-banner-container slider_error">
		<div class="sl-slider fullwidthbanner rslider tp-banner" >
		<h1 class="no_plugin">Plugin Is not Active!</h1>
		<h3 class="no_plugin_desc">Please Download and active the Ultimate Responsive Image Slider or switch back to normal Slider!</h3>
		<a href="https://wordpress.org/plugins/ultimate-responsive-image-slider/" class="btn no_plugin_link">Download Plugin<a>
		</div>
	</div>
	<?php }
}
	else{ ?>
<div id="slider" class="sl-slider-wrapper tp-banner-container">
	<div class="sl-slider fullwidthbanner rslider tp-banner" >
		<?php
		$dot=0;		
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		$args = array( 'post_type' => 'post','paged'=>$paged, 'posts_per_page'=>-1, 'post_status'=>'publish');
		$post_data = new WP_Query( $args );		
		while($post_data->have_posts()){		
		$post_data->the_post();
		if(has_post_thumbnail() && $dot<3): 
		global $more;
		$dot++;
		if ($dot % 2 == 0){	$orientation = 'horizontal';	}
		else{	$orientation = 'vertical';	}	$more = 0; ?>
		<div class="sl-slide" data-orientation="<?php echo $orientation; ?>" data-slice1-rotation="-25" data-slice2-rotation="-25" data-slice1-scale="2" data-slice2-scale="2">
			<div class="sl-slide-inner">			
				<?php 
				$defalt_arg =array('class' => "img-responsive bg-img"); 
				the_post_thumbnail('home_slider_thumb', $defalt_arg);
				?>
				<h2><?php the_title();?></h2>
				<blockquote><?php the_excerpt(); ?></blockquote>				
			</div>
		</div>
		<?php 
		endif;
		} 
if($dot<1){ ?>
	<div class="sl-slide" data-orientation="horizontal" data-slice1-rotation="-25" data-slice2-rotation="-25" data-slice1-scale="2" data-slice2-scale="2">
			<div class="sl-slide-inner">			
				<img class="img-responsive bg-img" src="<?php echo get_template_directory_uri(); ?>/img/slider/1.jpg">
				<h2><?php _e('Creative Slider' ,'creative'); ?></h2>
				<blockquote><?php _e('Until he extends the circle of his compassion to all living things, man will not himself find peace','creative'); ?></blockquote>				
			</div>
	</div>
	<div class="sl-slide" data-orientation="vertical" data-slice1-rotation="-25" data-slice2-rotation="-25" data-slice1-scale="2" data-slice2-scale="2">
			<div class="sl-slide-inner">			
				<img class="img-responsive bg-img" src="<?php echo get_template_directory_uri(); ?>/img/slider/2.jpg">
				<h2><?php _e('Regula aurea', 'creative'); ?></h2>
				<blockquote><?php _e('Until he extends the circle of his compassion to all living things, man will not himself find peace','creative'); ?></blockquote>				
			</div>
	</div>
	<div class="sl-slide" data-orientation="horizontal" data-slice1-rotation="-25" data-slice2-rotation="-25" data-slice1-scale="2" data-slice2-scale="2">
			<div class="sl-slide-inner">			
				<img class="img-responsive bg-img" src="<?php echo get_template_directory_uri(); ?>/img/slider/3.jpg">
				<h2><?php _e('Responsive Slider','creative'); ?></h2>
				<blockquote><?php _e('Until he extends the circle of his compassion to all living things, man will not himself find peace','creative'); ?></blockquote>				
			</div>
	</div>
 <?php $dot=3; }	
	?>
	</div><!-- /sl-slider -->
	<nav id="nav-dots" class="nav-dots" ><?php
		for($i=1; $i<=$dot; $i++) { ?>
			<span <?php echo $i==1 ? 'class="nav-dot-current"' : ""; ?>></span>
		<?php } ?>
	</nav>
</div><!-- /slider-wrapper -->
	<?php } ?>
	
		<div class="container product"> 
		<?php if($wl_theme_options['product_title']!='') {  ?>
		<h1> <?php echo esc_attr($wl_theme_options['product_title']); ?> </h1><?php } ?> 
			<?php include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			  if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$args = array( 'post_type' => 'product','orderby' => 'rand' );
			  $loop = new WP_Query( $args ); ?>
			  <?php if( $loop->have_posts() ){ ?>

		   <?php  while ( $loop->have_posts() ) : $loop->the_post(); global $product; ?>
				<div class="col-md-3 about-men-top">
					<div class="img-thumbnail gallery">
					   <?php if (has_post_thumbnail( $loop->post->ID )) echo get_the_post_thumbnail($loop->post->ID, 'shop_catalog'); else echo '<img
					   src="'.woocommerce_placeholder_img_src().'" alt="Placeholder" class="img-responsive" />'; ?>
					</div>
					<div class="col-md-12 col-sm-12 about-men-name">
					   <h2><a href="<?php the_permalink(); ?>" ><?php the_title(); ?></a></h2>
					   <span class="price"><?php echo $product->get_price_html(); ?></span><br>
					   <div class="col-md-12 pro_btn">
					 <?php woocommerce_template_loop_add_to_cart( $loop->post, $product ); ?>
					</div>    
					</div>    
				</div> 
				<?php endwhile; ?>
				<?php } else {
				   echo "<p class='title' style='color:#fff;'>No Featured Products Added Yet..!!</p>";
			   } } ?>
	   </div> 

<?php get_footer(); ?>