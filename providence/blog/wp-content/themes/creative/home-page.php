<?php //Template Name:HOME
get_header();
	$wl_theme_options = creative_general_options();
	if ($wl_theme_options['front_page_enabled']=='on' && is_front_page())
	{
	// Front page content
	get_template_part('home','slider');
	//****** get home callout ********
	if($sections = json_decode(get_theme_mod('home_reorder'),true)) {
		foreach ($sections as $section) {
			$data = "home_".$section;
			if($wl_theme_options[$data]=="on") {
			get_template_part('home', $section);
			}
		}
	} else {
		if($wl_theme_options['home_portfolio'] == "on") {
		get_template_part('home','portfolio'); 
		}
		
		if($wl_theme_options['home_service'] == "on") {
		get_template_part('home','service'); 
		}
		if($wl_theme_options['home_blog'] == "on") {
		get_template_part('home','blog');
		}
	}
	get_footer();
	}else if(is_page()){
		get_template_part('page');
	} else {
		get_template_part('index');
	}
?>