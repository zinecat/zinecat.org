<?php $wl_theme_options = creative_general_options();
if($wl_theme_options['home_service']=='on'){ ?>
<div class="row">
	<div class="col-lg-12 col-md-12 text-center service">
	<?php if($wl_theme_options['home_service_title']){ ?>
		<h2 class="wow bounceIn creative_ser_title"><?php echo esc_attr($wl_theme_options['home_service_title']); ?></h2>
	<?php } ?>
	<?php if($wl_theme_options['home_service_description']){ ?>
		<h4 class="wow fadeInRight creative_service_description"><?php echo get_theme_mod('home_service_description' , $wl_theme_options['home_service_description']); ?></h4>
	<?php } ?>
	</div>
</div>
<!-- /Services -->
<!-- Main Content -->
<div class="main-content">
	<div class="container">
		<div class="row">
		<?php for($i=1; $i<=3; $i++ ) {
		?>
		<?php if($wl_theme_options['service_icon_'.$i]!='' || $wl_theme_options['service_title_'.$i]!=''){  ?>
			<div class="col-lg-4 col-md-4 col-sm-4 wow swing">
				<div class="content-box big ch-item bottom-pad-small">
				<?php if($wl_theme_options['service_icon_'.$i]!=''){ ?>
					<div class="ch-info-wrap">
						<div class="ch-info">
							<div class="ch-info-front ch-img-1 <?php echo 'creative_home_serv_icon_'.$i; ?>"><i class="<?php echo esc_attr($wl_theme_options['service_icon_'.$i]); ?>"></i></div>
								<div class="ch-info-back">
									<i class="<?php echo esc_attr($wl_theme_options['service_icon_'.$i]); ?>"></i>
								</div>
						</div>
					</div>
				<?php } ?>
					<div class="content-box-info">
						<?php if($wl_theme_options['service_title_'.$i]!=''){ ?>
						<h3 class="<?php echo 'creative_home_serv_title_'.$i; ?>"><?php echo esc_attr($wl_theme_options['service_title_'.$i]); ?></h3>
						<?php } ?>
						<p class="<?php echo 'creative_home_serv_desc_'.$i; ?>">
							<?php echo get_theme_mod('service_description_'.$i , $wl_theme_options['service_description_'.$i]); ?>
						</p>
						<a href="<?php echo esc_url($wl_theme_options['service_link_'.$i]); ?>" <?php if($wl_theme_options['service_target_'.$i]=="on"){ echo "target='_blank'"; } ?>><?php _e('Read More','creative'); ?> <i class="fa fa-angle-right"></i><i class="fa fa-angle-right"></i></a>
					</div>
					<div class="border-bottom margin-top30">
					</div>
					<div class="border-bottom">
					</div>
				</div>
			</div>
		<?php } } ?>
		</div>
	</div>
</div>
<?php } ?>