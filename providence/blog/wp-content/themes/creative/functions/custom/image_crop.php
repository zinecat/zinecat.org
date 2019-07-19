<?php
 if ( function_exists( 'add_image_size' ) )
 {
	/*** slider ***/
	add_image_size('home_slider_bg',1400,570,true);
	//Blog Thumb Image Sizes
	add_image_size('blog_full_thumb',1140,506,true);
	add_image_size('blog_left_thumb',750,333,true);
	add_image_size('home_post_thumb',328,247,true);
	add_image_size('widget_post_thumb',90,85,true);
	//Service Template Image Sizes
	add_image_size('service_one_img',1140,370,true);
	add_image_size('service_two_img',555,387,true);
	add_image_size('home_service_img',70,70,true);
	//Service Template Image Sizes
	add_image_size('port_2_thumb',568,418,true);
	add_image_size('port_3_thumb',378,278,true);
	add_image_size('port_4_thumb',283,208,true);
	add_image_size('port_full_thumb',394,290,true);
	add_image_size('port_single_thumb',1140,760,true);
	//Home Testimonial Thumb
	add_image_size('home_test_thumb',70,70,true);
	//Home Clients Thumb
	add_image_size('home_clientel_thumb',238,88,true);
	//About-Us Post Thumb
	add_image_size('about_team_thumb',263, 263,true);
}
// code for image resize for according to image layout
add_filter( 'intermediate_image_sizes', 'creative_image_presets');
function creative_image_presets($sizes){
   $type = get_post_type($_REQUEST['post_id']);
    foreach($sizes as $key => $value){
		if($type=='creative_portfolio' && $value != 'port_single_thumb' && $value != 'port_full_thumb' && $value != 'port_2_thumb' && $value != 'port_3_thumb' && $value != 'port_4_thumb')
		{ unset($sizes[$key]);  }
		elseif($type=='creative_slider' && $value != 'home_slider_bg')
		{ unset($sizes[$key]);  }
		elseif($type=='creative_service' && $value != 'home_service_img')
		{ unset($sizes[$key]);  }
		elseif($type=='post' && $value != 'blog_full_thumb' && $value != 'blog_left_thumb' && $value!='home_post_thumb' && $value!='widget_post_thumb')
		{ unset($sizes[$key]);  }
		elseif($type=='creative_testimonial' && $value != 'home_test_thumb')
		{ unset($sizes[$key]);  }
		elseif($type=='creative_client' && $value != 'home_clientel_thumb')
		{ unset($sizes[$key]);  }
		elseif($type=='page' && $value != 'blog_full_thumb' && $value != 'blog_left_thumb' && $value != 'about_post_thumb' && $value != 'about_post_thumb2' && $value != 'wl_page_thumb' && $value != 'wl_pageff_thumb' && $value != 'service_one_img' && $value != 'service_two_img')
		{ unset($sizes[$key]);  }
		elseif($type=='creative_team' && $value != 'about_team_thumb' && $value !='about_team_thumb_two')
		{ unset($sizes[$key]);  }
		}
    return $sizes;
}
?>