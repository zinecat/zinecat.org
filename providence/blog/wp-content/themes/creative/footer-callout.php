<!-- Slogan -->
<?php $wl_theme_options = creative_general_options(); ?>
<div class="creative_call_out" ?>
if($wl_theme_options['home_call_out_enabled']=='on'){ ?>
<div class="slogan bottom-pad-small">
	<div class="pattern-overlay">
		<div class="container">
			<div class="row">
				<div class="slogan-content">
					<div class="col-lg-10 col-md-10 wow fadeInLeft"><?php
						if($wl_theme_options['footer_call_out_title']!=""){ ?>
						<h2 class="slogan-title creative_footer_call_title"><?php echo esc_attr($wl_theme_options['footer_call_out_title']); ?></h2><?php
						} ?>
					</div>
					<div class="col-lg-2 col-md-2 wow fadeInRight">
					<?php if($wl_theme_options['footer_call_out_button_text']){ ?>
						<div class="get-started">
						<a href='<?php if($wl_theme_options['footer_call_out_button_link']!=""){echo esc_url($wl_theme_options['footer_call_out_button_link']); } ?>' <?php if($wl_theme_options['footer_call_out_button_target']=="on"){ echo "target='_blank'"; } ?> class="btn-special btn-grey pull-right creative_footer_call_text">
						<?php echo esc_attr($wl_theme_options['footer_call_out_button_text']); ?></a>
						</div>
						<?php } ?>
					</div>
					<div class="clearfix"></div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>
</div>