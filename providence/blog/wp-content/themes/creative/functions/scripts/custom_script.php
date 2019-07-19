<?php function creative_scripts()
      {
			// Google fonts - witch you want to use - (rest you can just remove)
			wp_enqueue_style('OpenSans', '//fonts.googleapis.com/css?family=Open+Sans:400,300,600,700,800');
			wp_enqueue_style('Raleway', 'fonts.googleapis.com/css?family=Raleway:100,200,300,700,800,900');
			// CSS
			wp_enqueue_style('bootstrap', get_template_directory_uri() .'/css/bootstrap.css');
			wp_enqueue_style('bootstrap-theme', get_template_directory_uri() . '/css/bootstrap-theme.css');
			wp_enqueue_style('font-awesome-latest', get_template_directory_uri() . '/css/font-awesome-latest/css/fontawesome-all.css');
			wp_enqueue_style('font-awesome', get_template_directory_uri() . '/css/fonts/font-awesome/css/font-awesome.css');
			wp_enqueue_style('animations', get_template_directory_uri() . '/css/animations.css');
			wp_enqueue_style('menu', get_template_directory_uri() . '/css/menu.css');
			wp_enqueue_style('slider-style', get_template_directory_uri() . '/css/slider/style.css');
			wp_enqueue_style('custom', get_template_directory_uri() . '/css/slider/custom.css');
			wp_enqueue_style('team-member', get_template_directory_uri() . '/css/team-member.css');
			wp_enqueue_style('prettyPhoto', get_template_directory_uri() . '/css/prettyPhoto.css');
			wp_enqueue_style('mainStyle', get_stylesheet_uri() );
			wp_enqueue_style('green', get_template_directory_uri() . '/css/colors/green.css');
			wp_enqueue_style('theme-responsive', get_template_directory_uri() . '/css/theme-responsive.css');
			wp_enqueue_style('switcher', get_template_directory_uri() . '/css/switcher.css');
			wp_enqueue_style('spectrum', get_template_directory_uri() . '/css/spectrum.css');
			// JS
			wp_enqueue_script('classie',get_template_directory_uri() .'/js/classie.js');
			wp_enqueue_script('jquery.min', get_template_directory_uri() .'/js/jquery.min.js');
			wp_enqueue_script('modernizr-2.6.2.min', get_template_directory_uri() .'/js/modernizr-2.6.2.min.js');
			wp_enqueue_script('jquery-migrate-1.0.0', get_template_directory_uri() .'/js/jquery-migrate-1.0.0.js');
			wp_enqueue_script('jquery-ui', get_template_directory_uri() .'/js/jquery-ui.js');
			wp_enqueue_script('bootstrap', get_template_directory_uri() .'/js/bootstrap.js');
			wp_enqueue_script('ba-cond.min', get_template_directory_uri() . '/js/slider/jquery.ba-cond.min.js');
			wp_enqueue_script('slitslider', get_template_directory_uri() . '/js/slider/jquery.slitslider.js');
			wp_enqueue_script('jquery.parallax', get_template_directory_uri() .'/js/jquery.parallax.js');
			wp_enqueue_script('jquery.wait', get_template_directory_uri() .'/js/jquery.wait.js');
			wp_enqueue_script('fappear', get_template_directory_uri() .'/js/fappear.js');
			wp_enqueue_script('jquery.bxslider.min', get_template_directory_uri() .'/js/jquery.bxslider.min.js');
			wp_enqueue_script('jquery.prettyPhoto', get_template_directory_uri() .'/js/jquery.prettyPhoto.js');
			wp_enqueue_script('tweetMachine', get_template_directory_uri() .'/js/tweetMachine.js');
			wp_enqueue_script('tytabs', get_template_directory_uri() .'/js/tytabs.js');
			wp_enqueue_script('jquery.gmap.min', get_template_directory_uri() .'/js/jquery.gmap.min.js');
			wp_enqueue_script('jquery.sticky', get_template_directory_uri() .'/js/jquery.sticky.js');
			wp_enqueue_script('jquery.countTo', get_template_directory_uri() .'/js/jquery.countTo.js');
			wp_enqueue_script('jflickrfeed', get_template_directory_uri() .'/js/jflickrfeed.js');
			wp_enqueue_script('knob', get_template_directory_uri() .'/js/jquery.knob.js');
			wp_enqueue_script('imagesloaded.pkgd.min', get_template_directory_uri() .'/js/imagesloaded.pkgd.min.js');
			wp_enqueue_script('waypoints.min', get_template_directory_uri() .'/js/waypoints.min.js');
			wp_enqueue_script('wow', get_template_directory_uri() .'/js/wow.js');
			wp_enqueue_script('jquery.fitvids', get_template_directory_uri() .'/js/jquery.fitvids.js');
			wp_enqueue_script('spectrum', get_template_directory_uri() .'/js/spectrum.js');
			wp_enqueue_script('switcher', get_template_directory_uri() .'/js/switcher.js');
			wp_enqueue_script('masonary', get_template_directory_uri() .'/js/masonry.pkgd.min.js');
			wp_enqueue_script('custom', get_template_directory_uri() .'/js/custom.js');
			require('custom.php');
			if ( is_singular() ) wp_enqueue_script( "comment-reply" );
        }
        add_action('wp_enqueue_scripts', 'creative_scripts');
?>