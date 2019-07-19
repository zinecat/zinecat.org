<!-- Portfolio Work -->
<?php $wl_theme_options = creative_general_options();
if($wl_theme_options['home_portfolio']=='on'){ ?>
<div class="bottom-pad margin-top100">
    <div class="container">
        <div class="row">
            <div class="portfolio-content">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12"><?php
                    if(($wl_theme_options['home_port_title']!="") || ($wl_theme_options['home_port_description']!="")){ ?>
                    <div class="portfolio-title text-center">
                        <h2 class="wow bounceIn creative_home_port_title"><?php echo esc_attr($wl_theme_options['home_port_title']); ?></h2>
                        <h4 class="wow fadeInRight creative_home_port_desc"><?php echo get_theme_mod('home_port_description' , $wl_theme_options['home_port_description']); ?> </h4>
                    </div>
					<?php } ?>
                </div>
                <div class="col-md-12 col-sm-12 col-xs-12 portfolio-three-column wow bounceIn">
                    <div class="grid">
						<!-- Item 1 -->
						<?php for($i=1; $i<=3; $i++){ ?>
						<figure class="effect-zoe portfolio-border web jquery item <?php echo 'creative_home_port_img_'.$i; ?>">
							<a href="<?php echo esc_url($wl_theme_options['port_image_'.$i]);  ?>" class="portfolio-item-link" data-rel="prettyPhoto" >
							<img src="<?php echo esc_url($wl_theme_options['port_image_'.$i]); ?>" alt="creative_image" height="278" width="378"></a>
							<figcaption>
								<h2><span class="<?php echo 'creative_home_port_title_'.$i; ?>"><?php echo esc_attr($wl_theme_options['port_title_'.$i]); ?><span><?php _e(' ', 'creative'); ?><?php echo esc_attr($wl_theme_options['port_tagline_'.$i]);; ?></span></h2>
								<span><a href="<?php echo esc_url($wl_theme_options['port_image_'.$i]);  ?>" class="portfolio-item-link" data-rel="prettyPhoto" ><i class="fa fa-eye"></i></a></span>
								<?php if($wl_theme_options['port_link_'.$i]) {
									$port_link = $wl_theme_options['port_link_'.$i];
									} else {
									$port_link = the_permalink();
									}
								?>
								<span><a href="<?php echo $port_link; ?>" <?php if($wl_theme_options['port_link_target_'.$i]=='on'){ echo "target='_blank'"; } ?> class="portfolio-item-link"><i class="fa fa-paperclip"></i></a></span>
								<p class="<?php echo 'creative_home_port_desc_'.$i; ?>"><?php echo get_theme_mod('port_description_'.$i , $wl_theme_options['port_description_'.$i]); ?></p>
							</figcaption>
						</figure>
						<?php } ?>
                    </div>
                    <!-- /grid -->
                </div>
                <div class="clearfix">
                </div>
            </div>
        </div>
    </div>
</div>
<?php } ?>
 <!-- /Portfolio work -->