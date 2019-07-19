<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<!--<![endif]-->
	<head>
		<meta charset="<?php bloginfo('charset'); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Google Fonts -->
		<!-- Favicons -->
		<?php $wl_theme_options = creative_general_options(); ?>
		<?php if($wl_theme_options['upload_image_favicon']!=''){ ?>
		<link rel="shortcut icon" href="<?php  echo esc_url($wl_theme_options['upload_image_favicon']); ?>" />
		<?php } ?>
		<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
		<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<script src="js/respond.min.js"></script>
		<![endif]-->
		<!--[if IE]>
		<link rel="stylesheet" href="css/ie.css">
		<![endif]-->
		<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>
		<div class="page-mask">
			<div class="page-loader">
				<div class="spinner"></div>
				<?php _e('Loading...','creative'); ?>
			</div>
		</div>
		<!-- Wrap -->
		<div class="wrap">
			<!-- Header -->
			<header id="header">
			<img style="position: absolute;" class="hd-img" src="<?php header_image(); ?>" height="<?php echo get_custom_header()->height; ?>" width="<?php echo get_custom_header()->width; ?>" alt="" /> 
			<!-- Header Top Bar -->
				<div class="top-bar">
					<div class="slidedown collapse">
						<div class="container">
							<?php $wl_theme_options = creative_general_options();
							if($wl_theme_options['header_social_media_enabled']=='on') { ?>
							<div class="pull-left">
								<ul class="social pull-left creative_social_header">
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
							<?php }
							if($wl_theme_options['header_contact_enabled']=='on') { ?>
							<div class="phone-login pull-right">
								<?php if($wl_theme_options['phone_no'] !='') { ?>
								<a class="creative_social_phone"><i class="fa fa-phone"></i> <?php _e('Call Us : ','creative');  echo esc_attr($wl_theme_options['phone_no']); ?></a>
								<?php } if($wl_theme_options['email_id'] !='') { ?>
								<a class="creative_social_email"><i class="fa fa-envelope"></i><?php _e('Email : ','creative');  echo sanitize_email($wl_theme_options['email_id']); ?></a>
								<?php } ?>
							</div>
							<?php } ?>
						</div>
					</div>
				</div>
				<!-- /Header Top Bar -->
				<!-- Main Header -->
				<div class="main-header">
					<div class="container">
						<!-- TopNav -->
						<div class="topnav navbar-header">
							<a class="navbar-toggle down-button" data-toggle="collapse" data-target=".slidedown">
							<i class="fa fa-angle-down icon-current"></i>
							</a>
						</div>
						<!-- /TopNav-->
						<!-- Logo -->
						<div class="logo pull-left">
							<h1><?php $wl_theme_options = creative_general_options();
								$header_text = display_header_text();
								if($header_text)
								{ ?>  
								<?php if($wl_theme_options['upload_image_logo']!= ''){ ?>
								<a href="<?php echo esc_url(home_url( '/' )); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>">
								<span class="site-custom_logo"></span>
								<img class="logo-color" src="<?php echo esc_url($wl_theme_options['upload_image_logo']); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" style="height:<?php if($wl_theme_options['height']!='') { echo (int)($wl_theme_options['height']); }  else { "50"; } ?>px; width:<?php if($wl_theme_options['width']!='') { echo (int) ($wl_theme_options['width']); }  else { "180"; } ?>px;" />
								<?php } else { ?>
								 <h1 class="site-titlee"><?php echo get_bloginfo('name');  ?></h1>
								<?php } ?>
								</a>
								<p class="site-desc"><?php echo get_bloginfo('description');  ?></p>
								<?php } ?>
							</h1>
						</div>
						<!-- /Logo -->
						<!-- Mobile Menu -->
						<div class="mobile navbar-header">
							<a class="navbar-toggle" data-toggle="collapse" href=".navbar-collapse">
							<i class="fa fa-bars fa-2x"></i>
							</a>
						</div>
						<!-- /Mobile Menu -->
						<?php
						?>
						<!-- Menu Start -->
						<nav class="collapse navbar-collapse menu">
							<?php  wp_nav_menu( array(
							'theme_location' => 'primary',
							'menu_class' => 'nav navbar-nav sf-menu',
							'fallback_cb' => 'creative_fallback_page_menu',
							'walker' => new creative_nav_walker(),
							)
							); ?>
						</nav>
						<!-- /Menu -->
					</div>
				</div>
				<!-- /Main Header -->
			</header>
			<!-- /Header -->
			<!-- Main Section -->
			<section id="main" class="demo-2">
			
			<script>
			jQuery(document).ready(function () {	
		jQuery(window).scroll(function () {
			if (jQuery(this).scrollTop() > 150) {
				jQuery('.main-header').addClass('sticky-header');
			} else {
				jQuery('.main-header').removeClass('sticky-header');
			}
		});  
	});	
			</script>