<?php
function creative_get_options() {
// Options API
return wp_parse_args( 
	get_option( 'creative_general_options', array() ), 
	creative_general_options());    
}
add_action( 'wp_ajax_reset_options', 'reset_options_data' );
function reset_options_data() {

	if(!wp_verify_nonce($_POST['creative_nonce'], 'creative_nonce')) exit;
	$wl_theme_options = creative_general_options();
	// Handle request then generate response using WP_Ajax_Response
	if($_POST['option']=="creative_general_options1"){
		$wl_theme_options['front_page_enabled']='on';
		$wl_theme_options['upload_image_logo']=null;
		$wl_theme_options['height']=60;
		$wl_theme_options['width']=150;
		$wl_theme_options['upload_image_favicon']=null;
		$wl_theme_options['custom_css']=null;
		update_option('creative_general_options', $wl_theme_options);
	}
	if($_POST['option']=="creative_slideshow_options"){
		$ImageUrl1 = get_template_directory_uri() ."/img/slider/1.jpg";
		$ImageUrl2 = get_template_directory_uri() ."/img/slider/2.jpg";
		$ImageUrl3 = get_template_directory_uri() ."/img/slider/3.jpg";
		$wl_theme_options['home_slider_enabled']='on';
		$wl_theme_options['slider_image_1']=$ImageUrl1;
		$wl_theme_options['slider_title_1']= __('Aldus PageMaker', 'creative');
		$wl_theme_options['slider_description_1']= __('Lorem Ipsum is simply dummy text of the printing', 'creative');
		$wl_theme_options['slider_button_text_1']= __('Read More', 'creative');
		$wl_theme_options['slider_button_link_1']='#';
		$wl_theme_options['slider_button_target_1']='off';
		
		$wl_theme_options['slider_image_2']=$ImageUrl2;
		$wl_theme_options['slider_title_2']= __('variations of passages', 'creative');
		$wl_theme_options['slider_description_2']= __('Contrary to popular belief, Lorem Ipsum is not simply random text', 'creative');
		$wl_theme_options['slider_button_text_2']= __('Read More', 'creative');
		$wl_theme_options['slider_button_link_2']='#';
		$wl_theme_options['slider_button_target_2']='off';
		
		$wl_theme_options['slider_image_3']=$ImageUrl3;
		$wl_theme_options['slider_title_3']= __('Contrary to popular', 'creative');
		$wl_theme_options['slider_description_3']= __('Aldus PageMaker including versions of Lorem Ipsum, rutrum turpi', 'creative');
		$wl_theme_options['slider_button_text_3']= __('Read More', 'creative');
		$wl_theme_options['slider_button_link_3']='#';
		$wl_theme_options['slider_button_target_3']='off';
		update_option('creative_general_options', $wl_theme_options);
	}
	if($_POST['option']=="creative_service_options"){
		$wl_theme_options['home_service_enabled']='on';
		$wl_theme_options['home_service_title']= __('Services We Provide', 'creative');
		$wl_theme_options['home_service_description']= __('Lorem Ipsum is simply dummy text of the printing', 'creative');
		
		$wl_theme_options['service_icon_1']='fa fa-cloud-download';
		$wl_theme_options['service_title_1']= __('Idea', 'creative');
		$wl_theme_options['service_description_1']= __('There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in.', 'creative');
		$wl_theme_options['service_link_1']='#';
		$wl_theme_options['service_target_1']='off';
		
		$wl_theme_options['service_icon_2']='fa fa-bullhorn';
		$wl_theme_options['service_title_2']= __('Records', 'creative');
		$wl_theme_options['service_description_2']= __('There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in.', 'creative');
		$wl_theme_options['service_link_2']='#';
		$wl_theme_options['service_target_2']='off';
		
		$wl_theme_options['service_icon_3']='fa fa-user';
		$wl_theme_options['service_title_3']= __('WordPress', 'creative');
		$wl_theme_options['service_description_3']= __('There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in.', 'creative');
		$wl_theme_options['service_link_3']='#';
		$wl_theme_options['service_target_3']='off';
		update_option('creative_general_options', $wl_theme_options);
	}
	if($_POST['option']=="creative_portfolio_options"){
		$ImageUrl4 = get_template_directory_uri() ."/img/portfolio/01.jpg";
		$ImageUrl5 = get_template_directory_uri() ."/img/portfolio/02.jpg";
		$ImageUrl6 = get_template_directory_uri() ."/img/portfolio/03.jpg";
		$wl_theme_options['home_port_enabled']='on';
		$wl_theme_options['home_port_title']= __('creative Portfolio Showcasse', 'creative');
		$wl_theme_options['home_port_description']= __('creative provides you to show your portfolio contents in beautiful layout. Make a cool and colorful showcase for your site...', 'creative');
		
		$wl_theme_options['port_image_1']=$ImageUrl4;
		$wl_theme_options['port_title_1']= __('Create', 'creative');
		$wl_theme_options['port_tagline_1']= __('Smart', 'creative');
		$wl_theme_options['port_description_1']= __('Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'creative');
		$wl_theme_options['port_link_1']='#';
		$wl_theme_options['port_link_target_1']='off';
		
		$wl_theme_options['port_image_2']=$ImageUrl5;
		$wl_theme_options['port_title_2']= __('Content', 'creative');
		$wl_theme_options['port_tagline_2']= __('More', 'creative');
		$wl_theme_options['port_description_2']= __('Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'creative');
		$wl_theme_options['port_link_2']='#';
		$wl_theme_options['port_link_target_2']='off';
		
		$wl_theme_options['port_image_3']=$ImageUrl6;
		$wl_theme_options['port_title_3']= __('Dictionary', 'creative');
		$wl_theme_options['port_tagline_3']= __('Wins', 'creative');
		$wl_theme_options['port_description_3']= __('Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'creative');
		$wl_theme_options['port_link_3']='#';
		$wl_theme_options['port_link_target_3']='off';
		update_option('creative_general_options', $wl_theme_options);
	}
	if($_POST['option']=="creative_blog_options"){
		$wl_theme_options['home_blog_enabled']='on';
		$wl_theme_options['home_blog_title']= __('Latest Posts', 'creative');
		$wl_theme_options['home_blog_description']= __('We regularly post updates on our blog. Feel free to join with our blog!', 'creative');
		update_option('creative_general_options', $wl_theme_options);
	}
	if($_POST['option']=="creative_footercallout_options"){
		$wl_theme_options['home_call_out_enabled']='on';
		$wl_theme_options['footer_call_out_title']= __('Found a reason to work with me? Lets start!', 'creative');
		$wl_theme_options['footer_call_out_button_text']= __('Buy Now', 'creative');
		$wl_theme_options['footer_call_out_button_link']='#';
		$wl_theme_options['footer_call_out_button_target']='off';
		update_option('creative_general_options', $wl_theme_options);
	}
	if($_POST['option']=="creative_social_options"){
		$wl_theme_options['header_social_media_enabled']='on';
		$wl_theme_options['footer_social_media_enabled']='on';
		$wl_theme_options['header_contact_enabled']='on';
		$wl_theme_options['facebook_link']='#';
		$wl_theme_options['twitter_link']='#';
		$wl_theme_options['dribbble_link']='#';
		$wl_theme_options['linkedin_link']='#';
		$wl_theme_options['rss_link']='#';
		$wl_theme_options['youtube_link']='#';
		$wl_theme_options['instagram_link']='#';
		$wl_theme_options['googleplus_link']='#';
		$wl_theme_options['email_id']= __('lizarweb@gmail.com', 'creative');
		$wl_theme_options['phone_no']= __('8801111111', 'creative');
		update_option('creative_general_options', $wl_theme_options);
	}
	if($_POST['option']=="creative_footer_options"){
		$wl_theme_options['home_footer_enabled']='on';
		$wl_theme_options['footer_customizations']= __('&#169; 2014 Weblizar Theme', 'creative');
		$wl_theme_options['developed_by_text']= __('Theme Developed By', 'creative');
		$wl_theme_options['developed_by_creative_text']= __('Weblizar Themes', 'creative');
		$wl_theme_options['developed_by_link']= __('http://www.weblizar.com/', 'creative');
		update_option('creative_general_options', $wl_theme_options);
	}
	if(isset($_POST['option']) && $_POST['option']=="restoreall"){
		delete_option('creative_general_options');
	}
} ?>