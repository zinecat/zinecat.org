<?php
function creative_customizer( $wp_customize ) {
	wp_enqueue_style('creative-customizr', get_template_directory_uri() .'/css/customizr.css');	wp_enqueue_style('FA-5.8.1', get_template_directory_uri() .'/css/font-awesome-5.8.1/css/all.min.css');
	//wp_enqueue_style('FA', get_template_directory_uri()  .'/css/font-awesome-4.7.0/css/font-awesome.min.css');
	$ImageUrl4 = get_template_directory_uri() ."/img/portfolio/01.jpg";
	$ImageUrl5 = get_template_directory_uri() ."/img/portfolio/02.jpg";
	$ImageUrl6 = get_template_directory_uri() ."/img/portfolio/03.jpg";  
	/* Genral section */
	$wp_customize->add_panel( 'creative_theme_option', array(
    'title' => __( 'Creative Options','creative' ),
    'priority' => 1, // Mixed with top-level-section hierarchy.
	) );
	$wp_customize->add_section(
        'general_sec',
        array(
            'title' => __('Theme General Options','creative'),
            'description' => __('Here you can customize Your theme\'s general Settings','creative'),
			'panel'=>'creative_theme_option',
			'capability'=>'edit_theme_options',
            'priority' => 35,
        )
    );
	$wp_customize->selective_refresh->add_partial( 'blogname', array(
	'selector' => '.site-titlee',
	'render_callback' => 'blogname',
	) );
	$wp_customize->selective_refresh->add_partial( 'custom_logo', array(
	'selector' => '.site-custom_logo',
	'render_callback' => 'custom_logo',
	) );
	$wl_theme_options = creative_general_options();	
	$wp_customize->add_setting(
		'slider_option',
		array(
			'default'=>$wl_theme_options['plugin_slider'],
			'sanitize_callback'=>'creative_sanitize_checkbox',
			'capability' => 'edit_theme_options'
		)
	);
	$wp_customize->selective_refresh->add_partial( 'blogname', array(
	'selector' => '.site-title',
	'render_callback' => 'blogname',
	) );
	$wp_customize->selective_refresh->add_partial( 'custom_logo', array(
	'selector' => '.site-custom_logo',
	'render_callback' => 'custom_logo',
	) );
	$wp_customize->add_control( new Creative_desc_Control( $wp_customize, 'creative_desc', array(
				'label'    => __( 'Choose Slider', 'creative' ),
				'section'  => 'general_sec',
				'settings' => 'slider_option',
				'priority' => 1,
			) ) );
	$wp_customize->add_setting(
		'creative_general_options[plugin_slider]',
		array(
			'type'    => 'option',
			'default'=>$wl_theme_options['plugin_slider'],
			'sanitize_callback'=>'creative_sanitize_checkbox',
			'capability' => 'edit_theme_options'
		)
	);
	$wp_customize->add_control( 'creative_show_slider', array(
		'type'=> 'checkbox',
		'label'=>'Ultimate Responsive Image Slider',
		'section'    => 'general_sec',
		'settings'   => 'creative_general_options[plugin_slider]'
	) );
	
	
	$wp_customize->add_setting(
		'creative_general_options[snoweffect]',
		array(
			'type'    => 'option',
			'default'=>$wl_theme_options['snoweffect'],
			'sanitize_callback'=>'creative_sanitize_checkbox',
			'capability' => 'edit_theme_options'
		)
	);
	$wp_customize->add_control( 'snoweffect', array(
		'type'=> 'checkbox',
		'label'=>'Snow Effect',
		'section'    => 'general_sec',
		'settings'   => 'creative_general_options[snoweffect]'
	) );
	
	
	$wp_customize->add_setting(
		'creative_general_options[upload_image_logo]',
		array(
			'type'    => 'option',
			'default'=>$wl_theme_options['upload_image_logo'],
			'sanitize_callback'=>'esc_url_raw',
			'capability'        => 'edit_theme_options',
		)
	);
	$wp_customize->add_setting(
		'creative_general_options[height]',
		array(
			'type'    => 'option',
			'default'=>$wl_theme_options['height'],
			'sanitize_callback'=>'creative_sanitize_integer',
			'capability'        => 'edit_theme_options'
		)
	);
	$wp_customize->add_setting(
		'creative_general_options[width]',
		array(
			'type'    => 'option',
			'default'=>$wl_theme_options['width'],
			'sanitize_callback'=>'creative_sanitize_integer',
			'capability'        => 'edit_theme_options',
		)
	);
	
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'creative_upload_image_logo', array(
		'label'        => __( 'Website Logo', 'creative' ),
		'section'    => 'general_sec',
		'settings'   => 'creative_general_options[upload_image_logo]',
	) ) );
	$wp_customize->add_control( 'creative_logo_height', array(
		'label'        => __( 'Logo Height', 'creative' ),
		'type'=>'number',
		'section'    => 'general_sec',
		'settings'   => 'creative_general_options[height]',
	) );
	$wp_customize->add_control( 'creative_logo_width', array(
		'label'        => __( 'Logo Width', 'creative' ),
		'type'=>'number',
		'section'    => 'general_sec',
		'settings'   => 'creative_general_options[width]',
	) );
	
	$wp_customize->add_setting(
		'creative_general_options[upload_image_favicon]',
		array(
			'type'    => 'option',
			'default'=>$wl_theme_options['upload_image_favicon'],
			'capability'        => 'edit_theme_options',
			'sanitize_callback'=>'esc_url_raw',
		)
	);
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'creative_upload_favicon_image', array(
		'label'        => __( 'Custom favicon', 'creative' ),
		'section'    => 'general_sec',
		'settings'   => 'creative_general_options[upload_image_favicon]',
	) ) );
	
	
	if (get_template_directory() !== get_stylesheet_directory()) {
	/* Product options */
	$wp_customize->add_section('product_section',array(
	'title'=>__("Product Options",'creative'),
	'panel'=>'creative_theme_option',
	'capability'=>'edit_theme_options',
    'priority' => 35,
	));	
	
	$wp_customize->add_setting(
	'creative_general_options[product_title]',
		array(
		'default'=>esc_attr($wl_theme_options['product_title']),
		'type'=>'option',
		'capability'=>'edit_theme_options',
		'sanitize_callback'=>'creative_sanitize_text',
		)
	);
	$wp_customize->add_control( 'product_title', array(
		'label'        => __( 'Product Title', 'creative' ),
		'type'=>'text',
		'section'    => 'product_section',
		'settings'   => 'creative_general_options[product_title]'
	) ); }
	
	
	/* Typography  & Google Font Section */
	$wp_customize->add_section(
        'font_sec',
        array(
            'title' =>  __( 'Typography Section','creative' ),
			'panel'=>'creative_theme_option',
            'description' =>  __( 'Here you can manage your theme Font Style','creative' ),
			'capability'=>'edit_theme_options',
            'priority' => 35,
        ));
		$wp_customize->add_setting(
	'creative_general_options[logo_font]',
		array(
		'default'=>esc_attr($wl_theme_options['logo_font']),
		'type'=>'option',
		'sanitize_callback'=>'creative_sanitize_text',
		'capability'=>'edit_theme_options'
		)
	);
	$wp_customize->add_control( new creative_Font_Control( $wp_customize, 'logo_font',array(
			'label'    => __('Header Font Style', 'creative'),
			'extra' => __('Logo & Tagline Font Family', 'creative'),
			'section'  => 'font_sec',
			'settings' => 'creative_general_options[logo_font]',			
	) ) );
		$wp_customize->add_setting(
	'creative_general_options[header_menu_font]',
		array(
		'default'=>esc_attr($wl_theme_options['header_menu_font']),
		'type'=>'option',
		'sanitize_callback'=>'creative_sanitize_text',
		'capability'=>'edit_theme_options'
		)
	);
	$wp_customize->add_control( new creative_Font_Control( $wp_customize, 'header_menu_font',array(
			'label'    => __('Header Menu Font', 'creative'),
			'extra' => __('Header Menu Font Family', 'creative'),
			'section'  => 'font_sec',
			'settings' => 'creative_general_options[header_menu_font]',			
	) ) );
		$wp_customize->add_setting(
	'creative_general_options[themes_title]',
		array(
		'default'=>esc_attr($wl_theme_options['themes_title']),
		'type'=>'option',
		'sanitize_callback'=>'creative_sanitize_text',
		'capability'=>'edit_theme_options'
		)
	);
	$wp_customize->add_control( new creative_Font_Control( $wp_customize, 'themes_title',array(
			'label'    => __('Theme Heading Title', 'creative'),
			'extra' => __('Change Theme Title Font Family', 'creative'),
			'section'  => 'font_sec',
			'settings' => 'creative_general_options[themes_title]',			
	) ) );
	$wp_customize->add_setting(
	'creative_general_options[theme_descrp_font]',
		array(
		'default'=>esc_attr($wl_theme_options['theme_descrp_font']),
		'type'=>'option',
		'sanitize_callback'=>'creative_sanitize_text',
		'capability'=>'edit_theme_options'
		)
	);
	$wp_customize->add_control( new creative_Font_Control( $wp_customize, 'theme_descrp_font',array(
			'label'    => __('Font Family For Theme','creative'), 
			'extra' => __('', 'creative'),
			'section'  => 'font_sec',
			'settings' => 'creative_general_options[theme_descrp_font]',			
	) ) );

	/* Portfolio Section */
	$wp_customize->add_section(
        'portfolio_section',
        array(
            'title' => __('Portfolio Options','creative'),
            'description' => __('Here you can add Portfolio title,description and even portfolios','creative'),
			'panel'=>'creative_theme_option',
			'capability'=>'edit_theme_options',
            'priority' => 36,
			'active_callback' => 'is_front_page',
        )
    );
	
	$wp_customize->add_setting(
		'creative_general_options[home_portfolio]',
		array(
			'type'    => 'option',
			'default'=>$wl_theme_options['home_portfolio'],
			'sanitize_callback'=>'creative_sanitize_checkbox',
			'capability' => 'edit_theme_options'
		)
	);
	$wp_customize->add_setting(
		'creative_general_options[home_port_title]',
		array(
			'type'    => 'option',
			'default'=>$wl_theme_options['home_port_title'],
			'capability' => 'edit_theme_options',
			'sanitize_callback'=>'creative_sanitize_text',
		)
	);
	$wp_customize->selective_refresh->add_partial( 'creative_general_options[home_port_title]', array(
	'selector' => '.creative_home_port_title',
	'render_callback' => 'creative_general_options[home_port_title]',
	) );
	$wp_customize->add_setting(
	'home_port_description',
		array(
		'default'=>esc_attr($wl_theme_options['home_port_description']),
		'sanitize_callback'=>'creative_sanitize_text',
		'capability'=>'edit_theme_options'
	));	
	$wp_customize->selective_refresh->add_partial( 'home_port_description', array(
	'selector' => '.creative_home_port_desc',
	'render_callback' => 'home_port_description',
	) );
	for($i=1;$i<=3;$i++){ 
		$wp_customize->add_setting(
			'creative_general_options[port_image_'.$i.']',
			array(
				'type'    => 'option',
				'default'=>$wl_theme_options['port_image_'.$i],
				'capability' => 'edit_theme_options',
				'sanitize_callback'=>'esc_url_raw',
			)
		);
	$wp_customize->selective_refresh->add_partial( 'creative_general_options[port_image_'.$i.']', array(
	'selector' => '.creative_home_port_img_'.$i,
	'render_callback' => 'creative_general_options[port_image_'.$i.']',
	) );
		$wp_customize->add_setting(
			'creative_general_options[port_title_'.$i.']',
			array(
				'type'    => 'option',
				'default'=>$wl_theme_options['port_title_'.$i],
				'capability' => 'edit_theme_options',
				'sanitize_callback'=>'creative_sanitize_text',
			)
		);
	$wp_customize->selective_refresh->add_partial( 'creative_general_options[port_title_'.$i.']', array(
	'selector' => '.creative_home_port_title_'.$i,
	'render_callback' => 'creative_general_options[port_title_'.$i.']',
	) );
		$wp_customize->add_setting(
			'creative_general_options[port_tagline_'.$i.']',
			array(
				'type'    => 'option',
				'default'=>$wl_theme_options['port_tagline_'.$i],
				'capability' => 'edit_theme_options',
				'sanitize_callback'=>'creative_sanitize_text',
			)
		);
		$wp_customize->add_setting(
			'creative_general_options[port_description_'.$i.']',
			array(
				'type'    => 'option',
				'default'=>$wl_theme_options['port_description_'.$i],
				'capability' => 'edit_theme_options',
				'sanitize_callback'=>'creative_sanitize_text',
			)
		);
	$wp_customize->add_setting(
	'port_description_'.$i,
		array(
		'default'=>esc_attr($wl_theme_options['port_description_'.$i]),
		'sanitize_callback'=>'creative_sanitize_text',
		'capability'=>'edit_theme_options'
	));	
	$wp_customize->selective_refresh->add_partial( 'port_description_'.$i, array(
	'selector' => '.creative_home_port_desc_'.$i,
	'render_callback' => 'port_description_'.$i,
	) );
		$wp_customize->add_setting(
		'creative_general_options[port_link_'.$i.']',
			array(
			'type'    => 'option',
			'default'=>$wl_theme_options['port_link_'.$i],
			'capability' => 'edit_theme_options',
			'sanitize_callback'=>'esc_url_raw'
			)
		);
		$wp_customize->add_setting(
			'creative_general_options[port_link_target_'.$i.']',
			array(
				'type'    => 'option',
				'default'=>$wl_theme_options['port_link_target_'.$i],
				'sanitize_callback'=>'creative_sanitize_checkbox',
				'capability'        => 'edit_theme_options',
			)
		);
	}
	$wp_customize->add_control( 'creative_show_portfolio', array(
		'label'        => __( 'Enable Portfolio on Home', 'creative' ),
		'type'	=>'checkbox',
		'section'    => 'portfolio_section',
		'settings'   => 'creative_general_options[home_portfolio]'
	) );
	$wp_customize->add_control( 'creative_portfolio_title', array(
		'label'        => __( 'Portfolio Heading', 'creative' ),
		'type'	=>'text',
		'section'    => 'portfolio_section',
		'settings'   => 'creative_general_options[home_port_title]'
	) );
	$wp_customize->add_control(new One_Page_Editor($wp_customize, 'home_port_description', array(
		'label'        => __( 'Portfolio Description', 'creative' ),
		'section'    => 'portfolio_section',
		'active_callback' => 'show_on_front',
		'include_admin_print_footer' => true,
		'settings'   => 'home_port_description'
	)));
	
	for($i=1;$i<=3;$i++){
	$j = array('', ' One', ' Two', ' Three');
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'creative_portfolio_img_'.$i, array(
		'label'        => __( 'Portfolio Image', 'creative' ).$j[$i],
		'section'    => 'portfolio_section',
		'settings'   => 'creative_general_options[port_image_'.$i.']'
	) ) );
	$wp_customize->add_control( 'creative_portfolio_title_'.$i, array(
		'label'        => __( 'Portfolio Title', 'creative').$j[$i],
		'type'=>	'text',
		'section'    => 'portfolio_section',
		'settings'   => 'creative_general_options[port_title_'.$i.']'
	) );
	$wp_customize->add_control( 'creative_portfolio_text_'.$i, array(
		'label'        => __( 'Portfolio Text', 'creative').$j[$i],
		'type'=>	'text',
		'section'    => 'portfolio_section',
		'settings'   => 'creative_general_options[port_tagline_'.$i.']'
	) );
	$wp_customize->add_control(new One_Page_Editor($wp_customize, 'port_description_'.$i, array(
		'label'        => __( 'Portfolio Description', 'creative' ).$j[$i],
		'section'    => 'portfolio_section',
		'active_callback' => 'show_on_front',
		'include_admin_print_footer' => true,
		'settings'   => 'port_description_'.$i
	)));
	$wp_customize->add_control( 'creative_port_link_'.$i, array(
		'label'        => __( 'Portfolio Link', 'creative').$j[$i],
		'type'=>	'url',
		'section'    => 'portfolio_section',
		'settings'   => 'creative_general_options[port_link_'.$i.']',
	) );
	$wp_customize->add_control( 'creative_portfolio_target_'.$i, array(
		'label'        => __( 'Portfolio link open in new tab', 'creative' ),
		'type'=>	'checkbox',
		'section'    => 'portfolio_section',
		'settings'   => 'creative_general_options[port_link_target_'.$i.']',
	) );
	}

	/* Blog Option */
	$wp_customize->add_section('blog_section',array(
	'title'=>__('Home Blog Options','creative'),
	'panel'=>'creative_theme_option',
	'capability'=>'edit_theme_options',
    'priority' => 37
	));
	$wp_customize->add_setting(
		'creative_general_options[home_blog]',
		array(
			'type'    => 'option',
			'default'=>$wl_theme_options['home_blog'],
			'sanitize_callback'=>'creative_sanitize_checkbox',
			'capability'        => 'edit_theme_options',
		)
	);
	$wp_customize->add_control( 'creative_blog_enabled', array(
		'label'        => __( 'Enable Home Blog', 'creative' ),
		'type'=>'checkbox',
		'section'    => 'blog_section',
		'settings'   => 'creative_general_options[home_blog]',
	) );
	$wp_customize->add_setting(
	'creative_general_options[home_blog_title]',
		array(
		'default'=>esc_attr($wl_theme_options['home_blog_title']),
		'type'=>'option',
		'sanitize_callback'=>'creative_sanitize_text',
		'capability'=>'edit_theme_options'
		)
	);
	$wp_customize->selective_refresh->add_partial( 'creative_general_options[home_blog_title]', array(
	'selector' => '.creative_blog_title',
	'render_callback' => 'creative_general_options[home_blog_title]',
	) );
	$wp_customize->add_control( 'creative_blog_title', array(
		'label'        => __( 'Home Blog Title', 'creative' ),
		'type'=>'text',
		'section'    => 'blog_section',
		'settings'   => 'creative_general_options[home_blog_title]'
	) );
	$wp_customize->add_setting(
	'home_blog_description',
		array(
		'default'=>esc_attr($wl_theme_options['home_blog_description']),
		'sanitize_callback'=>'creative_sanitize_text',
		'capability'=>'edit_theme_options'
	));	
		$wp_customize->selective_refresh->add_partial( 'home_blog_description', array(
	'selector' => '.creative_blog_desc',
	'render_callback' => 'home_blog_description',
	) );
	$wp_customize->add_control(new One_Page_Editor($wp_customize, 'home_blog_description', array(
		'label'        => __( 'Home Blog Description', 'creative' ),
		'section'    => 'blog_section',
		'active_callback' => 'show_on_front',
		'include_admin_print_footer' => true,
		'settings'   => 'home_blog_description'
	)));
	/* Service Section */
	$wp_customize->add_section('service_section',array(
	'title'=>__('Service Options','creative'),
	'panel'=>'creative_theme_option',
	'capability'=>'edit_theme_options',
    'priority' => 35,
	'active_callback' => 'is_front_page',
	));
	$wp_customize->add_setting(
		'creative_general_options[home_service]',
		array(
			'type'    => 'option',
			'default'=>$wl_theme_options['home_service'],
			'sanitize_callback'=>'creative_sanitize_checkbox',
			'capability'        => 'edit_theme_options',
		)
	);
	$wp_customize->add_setting(
	'creative_general_options[home_service_title]',
		array(
		'default'=>esc_attr($wl_theme_options['home_service_title']),
		'type'=>'option',
		'capability'=>'edit_theme_options',
		'sanitize_callback'=>'creative_sanitize_text',
		)
	);
	$wp_customize->selective_refresh->add_partial( 'creative_general_options[home_service_title]', array(
	'selector' => '.creative_ser_title',
	'render_callback' => 'creative_general_options[home_service_title]',
	) );
		$wp_customize->add_setting(
	'home_service_description',
		array(
		'default'=>esc_attr($wl_theme_options['home_service_description']),
		'sanitize_callback'=>'creative_sanitize_text',
		'capability'=>'edit_theme_options'
	));	
	$wp_customize->selective_refresh->add_partial( 'home_service_description', array(
	'selector' => '.creative_service_description',
	'render_callback' => 'home_service_description',
	) );
	$wp_customize->add_control( 'creative_service_enabled', array(
		'label'        => __( 'Enable Home Service', 'creative' ),
		'type'=>'checkbox',
		'section'    => 'service_section',
		'settings'   => 'creative_general_options[home_service]',
	) );
	$wp_customize->add_control( 'creative_service_title', array(
		'label'        => __( 'Service Title', 'creative' ),
		'type'	=>'text',
		'section'    => 'service_section',
		'settings'   => 'creative_general_options[home_service_title]'
	) );
	$wp_customize->add_control(new One_Page_Editor($wp_customize, 'home_service_description', array(
	'label'        => __( 'Service Description', 'creative' ),
	'section'    => 'service_section',
	'active_callback' => 'show_on_front',
	'include_admin_print_footer' => true,
	'settings'   => 'home_service_description'
	)));
	for($i=1;$i<=3;$i++){
	$wp_customize->add_setting(
	'creative_general_options[service_icon_'.$i.']',
		array(
		'default'=>esc_attr($wl_theme_options['service_icon_'.$i]),
		'type'=>'option',
		'capability'=>'edit_theme_options',
		'sanitize_callback'=>'creative_sanitize_text',
			)
	);
	$wp_customize->selective_refresh->add_partial( 'creative_general_options[service_icon_'.$i.']', array(
	'selector' => '.creative_home_serv_icon_'.$i,
	'render_callback' => 'creative_general_options[service_icon_'.$i.']',
	) );
	$wp_customize->add_setting(
	'creative_general_options[service_title_'.$i.']',
		array(
		'default'=>esc_attr($wl_theme_options['service_title_'.$i]),
		'type'=>'option',
		'capability'=>'edit_theme_options',
		'sanitize_callback'=>'creative_sanitize_text',
			)
	);
	$wp_customize->selective_refresh->add_partial( 'creative_general_options[service_title_'.$i.']', array(
	'selector' => '.creative_home_serv_title_'.$i,
	'render_callback' => 'creative_general_options[service_title_'.$i.']',
	) );
		$wp_customize->add_setting(
	'service_description_'.$i,
		array(
		'default'=>esc_attr($wl_theme_options['service_description_'.$i]),
		'sanitize_callback'=>'creative_sanitize_text',
		'capability'=>'edit_theme_options'
	));	
	$wp_customize->selective_refresh->add_partial( 'service_description_'.$i, array(
	'selector' => '.creative_home_serv_desc_'.$i,
	'render_callback' => 'service_description_'.$i,
	) );
	$wp_customize->add_setting(
	'creative_general_options[service_link_'.$i.']',
		array(
		'type'    => 'option',
		'default'=>$wl_theme_options['service_link_'.$i],
		'capability' => 'edit_theme_options',
		'sanitize_callback'=>'esc_url_raw'
		)
	);
	$wp_customize->add_setting(
		'creative_general_options[service_target_'.$i.']',
		array(
			'type'    => 'option',
			'default'=>$wl_theme_options['service_target_'.$i],
			'sanitize_callback'=>'creative_sanitize_checkbox',
			'capability'        => 'edit_theme_options',
		)
	);
	}
	for($i=1;$i<=3;$i++){
	$j = array('', ' One', ' Two', ' Three');
	$wp_customize->add_control( new creative_Customize_Misc_Control($wp_customize, 'creative_general_options1-line', array(
            'section'  => 'service_section',
            'type'     => 'line'
        )
    ));
	$wp_customize->add_control( new creative_Customizer_Icon_Picker_Control($wp_customize,'creative_service_icon'.$i, array(
		'label'        => __( 'Service Icon', 'creative' ).$j[$i],
			'type'=>'text',
		'section'  => 'service_section',
		'settings'   => 'creative_general_options[service_icon_'.$i.']'
	)
    ) );
	$wp_customize->add_control( 'creative_service_title'.$i, array(
		'label'        => __( 'Service Title', 'creative').$j[$i],
		'type'=>'text',
		'section'    => 'service_section',
		'settings'   => 'creative_general_options[service_title_'.$i.']'
	) );
	$wp_customize->add_control(new One_Page_Editor($wp_customize, 'service_description_'.$i, array(
	'label'        => __( 'Service text ', 'creative' ).$i,
	'section'    => 'service_section',
	'active_callback' => 'show_on_front',
	'include_admin_print_footer' => true,
	'settings'   => 'service_description_'.$i
	)));
	$wp_customize->add_control( 'creative_service_link_'.$i, array(
		'label'        => __( 'Service Link', 'creative').$j[$i],
		'type'=>	'url',
		'section'    => 'service_section',
		'settings'   => 'creative_general_options[service_link_'.$i.']',
	) );
	$wp_customize->add_control( 'creative_service_link_target_'.$i, array(
		'label'        => __( 'Open link in new tab', 'creative' ),
		'type'=>	'checkbox',
		'section'    => 'service_section',
		'settings'   => 'creative_general_options[service_target_'.$i.']',
	) );
	}
	/* Social options */
	$wp_customize->add_section('social_section',array(
	'title'=>__("Social Options",'creative'),
	'panel'=>'creative_theme_option',
	'capability'=>'edit_theme_options',
    'priority' => 41
	));
	$wp_customize->add_setting(
	'creative_general_options[header_social_media_enabled]',
		array(
		'default'=>esc_attr($wl_theme_options['header_social_media_enabled']),
		'type'=>'option',
		'sanitize_callback'=>'creative_sanitize_checkbox',
		'capability'=>'edit_theme_options'
		)
	);
	$wp_customize->selective_refresh->add_partial( 'creative_general_options[header_social_media_enabled]', array(
	'selector' => '.creative_social_header',
	'render_callback' => 'creative_general_options[header_social_media_enabled]',
	) );
	$wp_customize->add_control( 'header_social_media_enabled', array(
		'label'        => __( 'Enable Social Media Icons in Header', 'creative' ),
		'type'=>'checkbox',
		'section'    => 'social_section',
		'settings'   => 'creative_general_options[header_social_media_enabled]'
	) );
	$wp_customize->add_setting(
	'creative_general_options[footer_social_media_enabled]',
		array(
		'default'=>esc_attr($wl_theme_options['footer_social_media_enabled']),
		'type'=>'option',
		'sanitize_callback'=>'creative_sanitize_checkbox',
		'capability'=>'edit_theme_options'
		)
	);
	$wp_customize->selective_refresh->add_partial( 'creative_general_options[footer_social_media_enabled]', array(
	'selector' => '.creative_social_footer',
	'render_callback' => 'creative_general_options[footer_social_media_enabled]',
	) );
	$wp_customize->add_control( 'footer_social_media_enabled', array(
		'label'        => __( 'Enable Social Media Icons in Footer', 'creative' ),
		'type'=>'checkbox',
		'section'    => 'social_section',
		'settings'   => 'creative_general_options[footer_social_media_enabled]'
	) );
	$wp_customize->add_setting(
	'creative_general_options[header_contact_enabled]',
		array(
		'default'=>esc_attr($wl_theme_options['header_contact_enabled']),
		'type'=>'option',
		'sanitize_callback'=>'creative_sanitize_checkbox',
		'capability'=>'edit_theme_options'
		)
	);
	$wp_customize->add_control( 'header_contact_enabled', array(
		'label'        => __( 'Enable Header Contact', 'creative' ),
		'type'=>'checkbox',
		'section'    => 'social_section',
		'settings'   => 'creative_general_options[header_contact_enabled]'
	) );
	$wp_customize->add_setting(
	'creative_general_options[facebook_link]',
		array(
		'default'=>esc_attr($wl_theme_options['facebook_link']),
		'type'=>'option',
		'sanitize_callback'=>'esc_url_raw',
		'capability'=>'edit_theme_options'
		)
	);
	$wp_customize->add_control( 'facebook_link', array(
		'label'        => __( 'Facebook URL', 'creative' ),
		'type'=>'url',
		'section'    => 'social_section',
		'settings'   => 'creative_general_options[facebook_link]'
	) );
	$wp_customize->add_setting(
	'creative_general_options[twitter_link]',
		array(
		'default'=>esc_attr($wl_theme_options['twitter_link']),
		'type'=>'option',
		'sanitize_callback'=>'esc_url_raw',
		'capability'=>'edit_theme_options'
		)
	);
	$wp_customize->add_control( 'twitter_link', array(
		'label'        =>  __('Twitter URL', 'creative' ),
		'type'=>'url',
		'section'    => 'social_section',
		'settings'   => 'creative_general_options[twitter_link]'
	) );
	$wp_customize->add_setting(
	'creative_general_options[dribbble_link]',
		array(
		'default'=>esc_attr($wl_theme_options['dribbble_link']),
		'type'=>'option',
		'sanitize_callback'=>'esc_url_raw',
		'capability'=>'edit_theme_options'
		)
	);
	$wp_customize->add_control( 'dribbble_link', array(
		'label'        =>  __('Dribbble URL', 'creative' ),
		'type'=>'url',
		'section'    => 'social_section',
		'settings'   => 'creative_general_options[dribbble_link]'
	) );
	$wp_customize->add_setting(
	'creative_general_options[linkedin_link]',
		array(
		'default'=>esc_attr($wl_theme_options['linkedin_link']),
		'type'=>'option',
		'sanitize_callback'=>'esc_url_raw',
		'capability'=>'edit_theme_options'
		)
	);
		$wp_customize->add_control( 'linkedin_link', array(
		'label'        => __( 'LinkedIn URL', 'creative' ),
		'type'=>'url',
		'section'    => 'social_section',
		'settings'   => 'creative_general_options[linkedin_link]'
	) );
	$wp_customize->add_setting(
	'creative_general_options[rss_link]',
		array(
		'default'=>esc_attr($wl_theme_options['rss_link']),
		'type'=>'option',
		'sanitize_callback'=>'esc_url_raw',
		'capability'=>'edit_theme_options'
		)
	);
		$wp_customize->add_control( 'rss_link', array(
		'label'        => __( 'RSS URL', 'creative' ),
		'type'=>'url',
		'section'    => 'social_section',
		'settings'   => 'creative_general_options[rss_link]'
	) );
	$wp_customize->add_setting(
	'creative_general_options[youtube_link]',
		array(
		'default'=>esc_attr($wl_theme_options['youtube_link']),
		'type'=>'option',
		'sanitize_callback'=>'esc_url_raw',
		'capability'=>'edit_theme_options'
		)
	);
		$wp_customize->add_control( 'youtube_link', array(
		'label'        => __( 'Youtube URL', 'creative' ),
		'type'=>'url',
		'section'    => 'social_section',
		'settings'   => 'creative_general_options[youtube_link]'
	) );
	$wp_customize->add_setting(
	'creative_general_options[instagram_link]',
		array(
		'default'=>esc_attr($wl_theme_options['instagram_link']),
		'type'=>'option',
		'sanitize_callback'=>'esc_url_raw',
		'capability'=>'edit_theme_options'
		)
	);
		$wp_customize->add_control( 'instagram_link', array(
		'label'        => __( 'Instagram URL', 'creative' ),
		'type'=>'url',
		'section'    => 'social_section',
		'settings'   => 'creative_general_options[instagram_link]'
	) );
	$wp_customize->add_setting(
	'creative_general_options[googleplus_link]',
		array(
		'default'=>esc_attr($wl_theme_options['googleplus_link']),
		'type'=>'option',
		'sanitize_callback'=>'esc_url_raw',
		'capability'=>'edit_theme_options'
		)
	);
		$wp_customize->add_control( 'googleplus_link', array(
		'label'        => __( 'Goole+ URL', 'creative' ),
		'type'=>'url',
		'section'    => 'social_section',
		'settings'   => 'creative_general_options[googleplus_link]'
	) );
	$wp_customize->add_setting(
	'creative_general_options[email_id]',
		array(
		'default'=>esc_attr($wl_theme_options['email_id']),
		'type'=>'option',
		'capability'=>'edit_theme_options',
		'sanitize_callback'=>'is_email',
		)
	);
	$wp_customize->selective_refresh->add_partial( 'creative_general_options[email_id]', array(
	'selector' => '.creative_social_email',
	'render_callback' => 'creative_general_options[email_id]',
	) );
		$wp_customize->add_control( 'email_id', array(
		'label'        => __( 'Email-ID', 'creative' ),
		'type'=>'email',
		'section'    => 'social_section',
		'settings'   => 'creative_general_options[email_id]'
	) );
	$wp_customize->add_setting(
	'creative_general_options[phone_no]',
		array(
		'default'=>esc_attr($wl_theme_options['phone_no']),
		'type'=>'option',
		'capability'=>'edit_theme_options',
		'sanitize_callback'=>'creative_sanitize_text',
		)
	);
	$wp_customize->selective_refresh->add_partial( 'creative_general_options[phone_no]', array(
	'selector' => '.creative_social_phone',
	'render_callback' => 'creative_general_options[phone_no]',
	) );
		$wp_customize->add_control( 'phone_no', array(
		'label'        => __( 'Phone Number', 'creative' ),
		'type'=>'text',
		'section'    => 'social_section',
		'sanitize_callback'=>'creative_sanitize_text',
		'settings'   => 'creative_general_options[phone_no]'
	) );
	/* Footer Callout */
	$wp_customize->add_section('callout_section',array(
	'title'=>__("Footer Call-Out Options",'creative'),
	'panel'=>'creative_theme_option',
	'capability'=>'edit_theme_options',
    'priority' => 39
	));
	$wp_customize->add_setting(
	'creative_general_options[home_call_out_enabled]',
		array(
		'default'=>esc_attr($wl_theme_options['home_call_out_enabled']),
		'type'=>'option',
		'sanitize_callback'=>'creative_sanitize_checkbox',
		'capability'=>'edit_theme_options'
		)
	);
	$wp_customize->selective_refresh->add_partial( 'creative_general_options[home_call_out_enabled]', array(
	'selector' => '.creative_call_out',
	'render_callback' => 'creative_general_options[home_call_out_enabled]',
	) );
	$wp_customize->add_control( 'home_call_out_enabled', array(
		'label'        => __( 'Enable Footer Call-Out in Home', 'creative' ),
		'type'=>'checkbox',
		'section'    => 'callout_section',
		'settings'   => 'creative_general_options[home_call_out_enabled]'
	) );
	$wp_customize->add_setting(
	'creative_general_options[footer_call_out_title]',
		array(
		'default'=>esc_attr($wl_theme_options['footer_call_out_title']),
		'type'=>'option',
		'capability'=>'edit_theme_options',
		'sanitize_callback'=>'creative_sanitize_text',
		)
	);
	$wp_customize->selective_refresh->add_partial( 'creative_general_options[footer_call_out_title]', array(
	'selector' => '.creative_footer_call_title',
	'render_callback' => 'creative_general_options[footer_call_out_title]',
	) );
	$wp_customize->add_control( 'callout_title', array(
		'label'        => __( 'Call-Out Title', 'creative' ),
		'type'=>'text',
		'section'    => 'callout_section',
		'settings'   => 'creative_general_options[footer_call_out_title]'
	) );
	$wp_customize->add_setting(
	'creative_general_options[footer_call_out_button_text]',
		array(
		'default'=>esc_attr($wl_theme_options['footer_call_out_button_text']),
		'type'=>'option',
		'sanitize_callback'=>'creative_sanitize_text',
		'capability'=>'edit_theme_options',
		)
	);
	$wp_customize->selective_refresh->add_partial( 'creative_general_options[footer_call_out_button_text]', array(
	'selector' => '.creative_footer_call_text',
	'render_callback' => 'creative_general_options[footer_call_out_button_text]',
	) );
	$wp_customize->add_control( 'callout_btn_text', array(
		'label'        => __( 'Callout Button Text', 'creative' ),
		'type'=>'text',
		'section'    => 'callout_section',
		'settings'   => 'creative_general_options[footer_call_out_button_text]'
	) );
	$wp_customize->add_setting(
	'creative_general_options[footer_call_out_button_link]',
		array(
		'default'=>esc_attr($wl_theme_options['footer_call_out_button_link']),
		'type'=>'option',
		'capability'=>'edit_theme_options',
		'sanitize_callback'=>'esc_url_raw',
		)
	);
	$wp_customize->add_control( 'callout_btn_link', array(
		'label'        => __( 'Call-Out Button Link', 'creative' ),
		'type'=>'url',
		'section'    => 'callout_section',
		'settings'   => 'creative_general_options[footer_call_out_button_link]'
	) );
	$wp_customize->add_setting(
	'creative_general_options[footer_call_out_button_target]',
		array(
		'default'=>esc_attr($wl_theme_options['footer_call_out_button_target']),
		'type'=>'option',
		'sanitize_callback'=>'creative_sanitize_checkbox',
		'capability'=>'edit_theme_options'
		)
	);
	$wp_customize->add_control( 'footer_call_out_button_target', array(
		'label'        => __( 'Open Link into new tab', 'creative' ),
		'type'=>'checkbox',
		'section'    => 'callout_section',
		'settings'   => 'creative_general_options[footer_call_out_button_target]'
	) );
		// home layout //
	$wp_customize->add_section('Home_Page_Layout',array(
    'title'=>__("Home Page Layout Option",'creative'),
    'panel'=>'creative_theme_option',
    'capability'=>'edit_theme_options',
    'priority' => 41,
    ));
	$wp_customize->add_setting('home_reorder',
            array(
				'type'=>'theme_mod',
                'sanitize_callback' => 'sanitize_json_string',
				'capability'        => 'edit_theme_options',
            )
        );
        $wp_customize->add_control(new creative_Custom_sortable_Control($wp_customize,'home_reorder', array(
			'label'=>__( 'Front Page Layout Option', 'creative' ),
            'section' => 'Home_Page_Layout',
            'type'    => 'home-sortable',
            'choices' => array(
                'service'      => __('Home Service', 'creative'),
                'portfolio'     => __('Home Portfolio', 'creative'),
                'blog'        => __('Home Blog', 'creative'),
            ),
			'settings'=>'home_reorder',
        )));
	// home layout close //
	/* Footer Options */
	$wp_customize->add_section('footer_section',array(
	'title'=>__("Footer Options","creative"),
	'panel'=>'creative_theme_option',
	'capability'=>'edit_theme_options',
    'priority' => 40
	));
	$wp_customize->add_setting(
	'creative_general_options[home_footer_enabled]',
		array(
		'default'=>esc_attr($wl_theme_options['home_footer_enabled']),
		'type'=>'option',
		'sanitize_callback'=>'creative_sanitize_checkbox',
		'capability'=>'edit_theme_options'
		)
	);
	$wp_customize->add_control( 'home_footer_enabled', array(
		'label'        => __( 'Enabled Copyright Section', 'creative' ),
		'type'=>'checkbox',
		'section'    => 'footer_section',
		'settings'   => 'creative_general_options[home_footer_enabled]'
	) );
	$wp_customize->add_setting(
	'creative_general_options[footer_customizations]',
		array(
		'default'=>esc_attr($wl_theme_options['footer_customizations']),
		'type'=>'option',
		'sanitize_callback'=>'creative_sanitize_text',
		'capability'=>'edit_theme_options'
		)
	);
	$wp_customize->selective_refresh->add_partial( 'creative_general_options[footer_customizations]', array(
	'selector' => '.creative_footer_customizations',
	'render_callback' => 'creative_general_options[footer_customizations]',
	) );
	$wp_customize->add_control( 'creative_footer_customizations', array(
		'label'        => __( 'Footer Customization Text', 'creative' ),
		'type'=>'text',
		'section'    => 'footer_section',
		'settings'   => 'creative_general_options[footer_customizations]'
	) );
	
	$wp_customize->add_setting(
	'creative_general_options[developed_by_text]',
		array(
		'default'=>esc_attr($wl_theme_options['developed_by_text']),
		'type'=>'option',
		'sanitize_callback'=>'creative_sanitize_text',
		'capability'=>'edit_theme_options'
		)
	);
	$wp_customize->add_control( 'creative_developed_by_text', array(
		'label'        => __( 'Footer Developed By Text', 'creative' ),
		'type'=>'text',
		'section'    => 'footer_section',
		'settings'   => 'creative_general_options[developed_by_text]'
	) );
	$wp_customize->add_setting(
	'creative_general_options[developed_by_creative_text]',
		array(
		'default'=>esc_attr($wl_theme_options['developed_by_creative_text']),
		'type'=>'option',
		'sanitize_callback'=>'creative_sanitize_text',
		'capability'=>'edit_theme_options'
		)
	);
	$wp_customize->add_control( 'creative_developed_by_creative_text', array(
		'label'        => __( 'Footer Company Text', 'creative' ),
		'type'=>'text',
		'section'    => 'footer_section',
		'settings'   => 'creative_general_options[developed_by_creative_text]'
	) );
	$wp_customize->add_setting(
	'creative_general_options[developed_by_link]',
		array(
		'default'=>esc_attr($wl_theme_options['developed_by_link']),
		'type'=>'option',
		'capability'=>'edit_theme_options',
		'sanitize_callback'=>'esc_url_raw'
		)
	);
	$wp_customize->add_control( 'creative_developed_by_link', array(
		'label'        => __( 'Footer Customization Link', 'creative' ),
		'type'=>'url',
		'section'    => 'footer_section',
		'settings'   => 'creative_general_options[developed_by_link]'
	) );
	$wp_customize->add_section( 'creative_pro' , array(
				'title'      	=> __( 'Upgrade to Creative Premium ', 'creative' ),
				'priority'   	=> 999,
				'panel'=>'creative_theme_option',
			) );

			$wp_customize->add_setting( 'creative_more', array(
				'default'    		=> null,
				'sanitize_callback' => 'sanitize_text_field',
			) );

			$wp_customize->add_control( new creative_parallax_More_Control( $wp_customize, 'creative_more', array(
				'label'    => __( 'Creative Premium', 'creative' ),
				'section'  => 'creative_pro',
				'settings' => 'creative_more',
				'priority' => 1,
			) ) );
}
add_action( 'customize_register', 'creative_customizer' );

function creative_sanitize_text( $input ) {
    return wp_kses_post( force_balance_tags( $input ) );
}
function creative_sanitize_checkbox( $input ) {
    if ( $input == 'on' ) {
        return 'on';
    } else {
        return '';
    }
}
function creative_sanitize_integer( $input ) {
    return (int)($input);
}
/* Custom Control Class */
if ( class_exists( 'WP_Customize_Control' ) && ! class_exists( 'creative_Customize_Misc_Control' ) ) :
class creative_Customize_Misc_Control extends WP_Customize_Control {
    public $settings = 'blogname';
    public $description = '';
    public function render_content() {
        switch ( $this->type ) {
            default:
           
            case 'heading':
                echo '<span class="customize-control-title">' . esc_html( $this->label ) . '</span>';
                break;
 
            case 'line' :
                echo '<hr />';
                break;
			
        }
    }
}
endif;

		
if ( class_exists( 'WP_Customize_Control' ) && ! class_exists( 'creative_parallax_More_Control' ) ) :
class creative_parallax_More_Control extends WP_Customize_Control {

	/**
	* Render the content on the theme customizer page
	*/
	public function render_content() {
		?>
		
	
		
		<label style="overflow: hidden; zoom: 1;">
			<div class="col-md-2 col-sm-6 upsell-btn">					
					<a style="margin-bottom:20px;margin-left:20px;" href="https://weblizar.com/themes/creative-premium/" target="blank" class="btn btn-success btn"><?php _e('Upgrade to Creative Premium','creative'); ?> </a>
			</div>
			<div class="col-md-4 col-sm-6">
				<img class="enigma_img_responsive " src="<?php echo get_template_directory_uri().'/img/creative.jpg';?>">
			</div>			
			<div class="col-md-3 col-sm-6">
				<h3 style="margin-top:10px;margin-left: 20px;text-decoration:underline;color:#333;"><?php echo _e( 'Creative Premium - Features','creative'); ?></h3>
					<ul style="padding-top:20px">
						<li class="upsell-enigma"> <div class="dashicons dashicons-yes"></div> <?php _e('Responsive Design','creative'); ?> </li>						
						<li class="upsell-enigma"> <div class="dashicons dashicons-yes"></div> <?php _e('WPML Compatible','creative'); ?> </li>						
						<li class="upsell-enigma"> <div class="dashicons dashicons-yes"></div> <?php _e('Retina Ready','creative'); ?> </li>											
						<li class="upsell-enigma"> <div class="dashicons dashicons-yes"></div> <?php _e('Multi Purpose','creative'); ?> </li>						
						<li class="upsell-enigma"> <div class="dashicons dashicons-yes"></div> <?php _e('Unlimited Color Skins','creative'); ?> </li>						
						<li class="upsell-enigma"> <div class="dashicons dashicons-yes"></div> <?php _e('Well Documented','creative'); ?> </li>						
						<li class="upsell-enigma"> <div class="dashicons dashicons-yes"></div> <?php _e('Multiple Theme Templates','creative'); ?> </li>						
						<li class="upsell-enigma"> <div class="dashicons dashicons-yes"></div> <?php _e('Ultimate Portfolio Design','creative'); ?> </li>						
						<li class="upsell-enigma"> <div class="dashicons dashicons-yes"></div> <?php _e('Touch Slider','creative'); ?> </li>						
						<li class="upsell-enigma"> <div class="dashicons dashicons-yes"></div> <?php _e('Coming Soon Mode','creative'); ?> </li>						
						<li class="upsell-enigma"> <div class="dashicons dashicons-yes"></div> <?php _e('Custom Shortcodes','creative'); ?> </li>						
						<li class="upsell-enigma"> <div class="dashicons dashicons-yes"></div> <?php _e('Browser Compatibility','creative'); ?> </li>						
						<li class="upsell-enigma"> <div class="dashicons dashicons-yes"></div> <?php _e('Fast & Friendly Support','creative'); ?> </li>						
						<li class="upsell-enigma"> <div class="dashicons dashicons-yes"></div> <?php _e('Theme Option Panel','creative'); ?> </li>						
					</ul>
			</div>
			<div class="col-md-2 col-sm-6 upsell-btn">					
					<a style="margin-bottom:20px;margin-left:20px;" href="https://weblizar.com/themes/creative-premium/" target="blank" class="btn btn-success btn"><?php _e('Upgrade to Creative Premium','creative'); ?> </a>
			</div>
			<span class="customize-control-title"><?php _e( 'Enjoying Creative?', 'creative' ); ?></span>
			<p>
				<?php
					printf( __( 'If you Like our Products , Please do Rate us on %sWordPress.org%s?  We\'d really appreciate it!', 'creative' ), '<a target="" href="https://wordpress.org/support/view/theme-reviews/creative?filter=5">', '</a>' );
				?>
			</p>
		</label>
		<?php
	}
}
endif;

if( class_exists( 'WP_Customize_Control' ) ):
	class Creative_desc_Control extends WP_Customize_Control { 
		public function render_content() {
		?>
			<label>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<p>You can add Unlimited Slides in slider. Please Install and activate <a href="https://wordpress.org/plugins/ultimate-responsive-image-slider/">Ultimate Responsive Image Slider</a> and choose Ultimate Responsive Image Slider here.</p>
			</label>
		<?php
		}
	}
endif;


/* class for font-family */
if ( class_exists( 'WP_Customize_Control' ) && ! class_exists( 'creative_Font_Control' ) ) :
class creative_Font_Control extends WP_Customize_Control 
{  
 public function render_content() 
 {?>
   <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
   <select <?php $this->link(); ?> >
    <option  value="Abril Fatface"<?php if($this->value()== 'Abril Fatface') echo 'selected="selected"';?>><?php _e('Abril Fatface','creative'); ?></option>
	<option  value="Advent Pro"<?php if($this->value()== 'Advent Pro')  echo 'selected="selected"';?>><?php _e('Advent Pro','creative'); ?></option>
	<option  value="Aldrich"<?php if($this->value()== 'Aldrich') echo 'selected="selected"';?>><?php _e('Aldrich','creative'); ?></option>
	<option  value="Alex Brush"<?php if($this->value()== 'Alex Brush') echo 'selected="selected"';?>><?php _e('Alex Brush','creative'); ?></option>
	<option  value="Allura"<?php if($this->value()== 'Allura') echo 'selected="selected"';?>><?php _e('Allura','creative'); ?></option>
	<option  value="Amatic SC"<?php if($this->value()== 'Amatic SC') echo 'selected="selected"';?>><?php _e('Amatic SC','creative'); ?></option>
	<option  value="arial"<?php if($this->value()== 'arial') echo 'selected="selected"';?>><?php _e('Arial','creative'); ?></option>
	<option  value="Astloch"<?php if($this->value()== 'Astloch') echo 'selected="selected"';?>><?php _e('Astloch','creative'); ?></option>
	<option  value="arno pro bold italic"<?php if($this->value()== 'arno pro bold italic') echo 'selected="selected"';?>><?php _e('Arno pro bold italic','creative'); ?></option>
	<option  value="Bad Script"<?php if($this->value()== 'Bad Script') echo 'selected="selected"';?>><?php _e('Bad Script','creative'); ?></option>
	<option  value="Bilbo"<?php if($this->value()== 'Bilbo') echo 'selected="selected"';?>><?php _e('Bilbo','creative'); ?></option>
	<option  value="Calligraffitti"<?php if($this->value()== 'Calligraffitti') echo 'selected="selected"';?>><?php _e('Calligraffitti','creative'); ?></option>
	<option  value="Candal"<?php if($this->value()== 'Candal') echo 'selected="selected"';?>><?php _e('Candal','creative'); ?></option>
	<option  value="Cedarville Cursive"<?php if($this->value()== 'Cedarville Cursive') echo 'selected="selected"';?>><?php _e('Cedarville Cursive','creative'); ?></option>
	<option  value="Clicker Script"<?php if($this->value()== 'Clicker Script') echo 'selected="selected"';?>><?php _e('Clicker Script','creative'); ?></option>
	<option  value="Dancing Script"<?php if($this->value()== 'Dancing Script') echo 'selected="selected"';?>><?php _e('Dancing Script','creative'); ?></option>
	<option  value="Dawning of a New Day"<?php if($this->value()== 'Dawning of a New Day') echo 'selected="selected"';?>><?php _e('Dawning of a New Day','creative'); ?></option>
	<option  value="Fredericka the Great"<?php if($this->value()== 'Fredericka the Great') echo 'selected="selected"';?>><?php _e('Fredericka the Great','creative'); ?></option>
	<option  value="Felipa"<?php if($this->value()== 'Felipa') echo 'selected="selected"';?>><?php _e('Felipa','creative'); ?></option>
	<option  value="Give You Glory"<?php if($this->value()== 'Give You Glory') echo 'selected="selected"';?>><?php _e('Give You Glory','creative'); ?></option>
	<option  value="Great vibes"<?php if($this->value()== 'Great vibes') echo 'selected="selected"';?>><?php _e('Great vibes','creative'); ?></option>
	<option  value="Homemade Apple"<?php if($this->value()== 'Homemade Apple') echo 'selected="selected"';?>><?php _e('Homemade Apple','creative'); ?></option>
	<option  value="Indie Flower"<?php if($this->value()== 'Indie Flower') echo 'selected="selected"';?>><?php _e('Indie Flower','creative'); ?></option>
	<option  value="Italianno"<?php if($this->value()== 'Italianno') echo 'selected="selected"';?>><?php _e('Italianno','creative'); ?></option>
	<option  value="Jim Nightshade"<?php if($this->value()== 'Jim Nightshade') echo 'selected="selected"';?>><?php _e('Jim Nightshade','creative'); ?></option>
	<option  value="Kaushan Script"<?php if($this->value()== 'Kaushan Script') echo 'selected="selected"';?>><?php _e('Kaushan Script','creative'); ?></option>
	<option  value="Kristi"<?php if($this->value()== 'Kristi') echo 'selected="selected"';?>><?php _e('Kristi','creative'); ?></option>
	<option  value="La Belle Aurore"<?php if($this->value()== 'La Belle Aurore') echo 'selected="selected"';?>><?php _e('La Belle Aurore','creative'); ?></option>
	<option  value="Meddon"<?php if($this->value()== 'Meddon') echo 'selected="selected"';?>><?php _e('Meddon','creative'); ?></option>
	<option  value="Montez"<?php if($this->value()== 'Montez') echo 'selected="selected"';?>><?php _e('Montez','creative'); ?></option>
	<option  value="Megrim"<?php if($this->value()== 'Megrim') echo 'selected="selected"';?>><?php _e('Megrim','creative'); ?></option>
	<option  value="Mr Bedfort"<?php if($this->value()== 'Mr Bedfort') echo 'selected="selected"';?>><?php _e('Mr Bedfort','creative'); ?></option>
	<option  value="Neucha"<?php if($this->value()== 'Neucha') echo 'selected="selected"';?>><?php _e('Neucha','creative'); ?></option>
	<option  value="Nothing You Could Do"<?php if($this->value()== 'Nothing You Could Do') echo 'selected="selected"';?>><?php _e('Nothing You Could Do','creative'); ?></option>
	<option  value="Open Sans"<?php if($this->value()== 'Open Sans') echo 'selected="selected"';?>><?php _e('Open Sans','creative'); ?></option>
	<option  value="Over the Rainbow"<?php if($this->value()== 'Over the Rainbow') echo 'selected="selected"';?>><?php _e('Over the Rainbow','creative'); ?></option>
	<option  value="Pinyon Script"<?php if($this->value()== 'Pinyon Script') echo 'selected="selected"';?>><?php _e('Pinyon Script','creative'); ?></option>
	<option  value="Princess Sofia"<?php if($this->value()== 'Princess Sofia') echo 'selected="selected"';?>><?php _e('Princess Sofia','creative'); ?></option>
	<option  value="Reenie Beanie"<?php if($this->value()== 'Reenie Beanie') echo 'selected="selected"';?>><?php _e('Reenie Beanie','creative'); ?></option>
	<option  value="Rochester"<?php if($this->value()== 'Rochester') echo 'selected="selected"';?>><?php _e('Rochester','creative'); ?></option>
	<option  value="Rock Salt"<?php if($this->value()== 'Rock Salt') echo 'selected="selected"';?>><?php _e('Rock Salt','creative'); ?></option>
	<option  value="Ruthie"<?php if($this->value()== 'Ruthie') echo 'selected="selected"';?>><?php _e('Ruthie','creative'); ?></option>
	<option  value="Sacramento"<?php if($this->value()== 'Sacramento') echo 'selected="selected"';?>><?php _e('Sacramento','creative'); ?></option>
	<option  value="Sans Serif"<?php if($this->value()== 'Sans Serif') echo 'selected="selected"';?>><?php _e('Sans Serif','creative'); ?></option>
	<option  value="Seaweed Script"<?php if($this->value()== 'Seaweed Script') echo 'selected="selected"';?>><?php _e('Seaweed Script','creative'); ?></option>
	<option  value="Shadows Into Light"<?php if($this->value()== 'Shadows Into Light') echo 'selected="selected"';?>><?php _e('Shadows Into Light','creative'); ?></option>
	<option  value="Smythe"<?php if($this->value()== 'Smythe') echo 'selected="selected"';?>><?php _e('Smythe','creative'); ?></option>
	<option  value="Stalemate"<?php if($this->value()== 'Stalemate') echo 'selected="selected"';?>><?php _e('Stalemate','creative'); ?></option>
	<option  value="Tahoma"<?php if($this->value()== 'Tahoma') echo 'selected="selected"';?>><?php _e('Tahoma','creative'); ?></option>
	<option  value="Tangerine"<?php if($this->value()== 'Tangerine') echo 'selected="selected"';?>><?php _e('Tangerine','creative'); ?></option>
	<option  value="Trade Winds"<?php if($this->value()== 'Trade Winds') echo 'selected="selected"';?>><?php _e('Trade Winds','creative'); ?></option>
	<option  value="UnifrakturMaguntia"<?php if($this->value()== 'UnifrakturMaguntia') echo 'selected="selected"';?>><?php _e('UnifrakturMaguntia','creative'); ?></option>
	<option  value="Verdana"<?php if($this->value()== 'Verdana') echo 'selected="selected"';?>><?php _e('Verdana','creative'); ?></option>
	<option  value="Waiting for the Sunrise"<?php if($this->value()== 'Waiting for the Sunrise') echo 'selected="selected"';?>><?php _e('Waiting for the Sunrise','creative'); ?></option>
	<option  value="Warnes"<?php if($this->value()== 'Warnes') echo 'selected="selected"';?>><?php _e('Warnes','creative'); ?></option>
	<option  value="Yesteryear"<?php if($this->value()== 'Yesteryear') echo 'selected="selected"';?>><?php _e('Yesteryear','creative'); ?></option>
	<option  value="Zeyada"<?php if($this->value()== 'Zeyada') echo 'selected="selected"';?>><?php _e('Zeyada','creative'); ?></option>
    </select>		
		
  <?php
 }
}
endif;

if ( class_exists( 'WP_Customize_Control' ) && ! class_exists( 'creative_Customizer_Icon_Picker_Control' ) ) :
	class creative_Customizer_Icon_Picker_Control extends WP_Customize_Control {
		public function enqueue() {
			wp_enqueue_script( 'fontawesome-iconpicker', get_template_directory_uri() . '/iconpicker-control/assets/js/fontawesome-iconpicker.min.js', array( 'jquery' ), '1.0.0', true );
			wp_enqueue_script( 'iconpicker-control', get_template_directory_uri() . '/iconpicker-control/assets/js/iconpicker-control.js', array( 'jquery' ), '1.0.0', true );
			wp_enqueue_style( 'iconpicker-css', get_template_directory_uri() . '/iconpicker-control/assets/css/fontawesome-iconpicker.css', array(), rand() );	
			wp_enqueue_style('font-awesome-latest', get_template_directory_uri(). '/css/font-awesome-latest/css/fontawesome-all.css');

		}
		
		public function render_content() {
			?>
			<label>
				<style>
				input.icp.icp-auto.iconpicker-element.iconpicker-input {
				width: 90% !important;
				}
				</style>
				<span class="customize-control-title">
					<?php echo esc_html( $this->label ); ?>
				</span>
				<div class="input-group icp-container">
					<input data-placement="bottomRight" class="icp icp-auto" <?php $this->link(); ?> value="<?php echo esc_attr( $this->value() ); ?>" type="text">
					<span class="input-group-addon"></span>
				</div>
			</label>
			<?php
		}
	}
endif;

if ( class_exists( 'WP_Customize_Control' ) && ! class_exists( 'creative_Custom_sortable_Control' ) ) :
class creative_Custom_sortable_Control extends WP_Customize_Control
{
    public $type = 'home-sortable';
    /*Enqueue resources for the control*/
    public function enqueue()
    {

        wp_enqueue_style('customizer-repeater-admin-stylesheet', get_template_directory_uri() . '/assets/customizer_js_css/css/creative-admin-style.css', time());

        wp_enqueue_script('customizer-repeater-script', get_template_directory_uri() . '/assets/customizer_js_css/js/creative-customizer_repeater.js', array('jquery', 'jquery-ui-draggable'), time(), true);

    }
    public function render_content()
    {
        if (empty($this->choices)) {
            return;
        }
        $values = json_decode($this->value());
        $name         = $this->id;
        ?>

		<span class="customize-control-title">
			<?php echo esc_attr($this->label); ?>
		</span>

		<?php if (!empty($this->description)): ?>
			<span class="description customize-control-description"><?php echo esc_html($this->description); ?></span>
		<?php endif;?>

		<div class="customizer-repeater-general-control-repeater customizer-repeater-general-control-droppable">
			<?php 
			if(!empty($values)){ 
				foreach ($values as $value) {?>
					<div class="customizer-repeater-general-control-repeater-container customizer-repeater-draggable ui-sortable-handle">
					<div class="customizer-repeater-customize-control-title">
						<?php echo $this->choices[$value]; ?>
					</div>
					<input type="hidden" class="section-id" value="<?php echo $value; ?>">
					</div>	
				<?php }?>
				
			<?php }else{
			foreach ($this->choices as $value => $label): ?>
					<div class="customizer-repeater-general-control-repeater-container customizer-repeater-draggable ui-sortable-handle">
					<div class="customizer-repeater-customize-control-title">
						<?php echo $label; ?>
					</div>
					<input type="hidden" class="section-id" value="<?php echo $value; ?>">
					</div>

				<?php endforeach;
			}
        		if (!empty($value)) {?>
					<input type="hidden"
					       id="customizer-repeater-<?php echo $this->id; ?>-colector" <?php esc_url($this->link());?>
					       class="customizer-repeater-colector"
					       value="<?php echo esc_textarea(json_encode($value)); ?>"/>
					<?php
				} else {?>
					<input type="hidden"
					       id="customizer-repeater-<?php echo $this->id; ?>-colector" <?php esc_url($this->link());?>
					       class="customizer-repeater-colector"/>
					<?php
				}?>
		</div>
		<?php
}
}
endif;

function sanitize_json_string($json){
    $sanitized_value = array();
    foreach (json_decode($json,true) as $value) {
        $sanitized_value[] = esc_attr($value);
    }
    return json_encode($sanitized_value);
}

if ( class_exists( 'WP_Customize_Control' ) && ! class_exists( 'One_Page_Editor' ) ) :
/* Class to create a custom tags control */
class One_Page_Editor extends WP_Customize_Control {	
	private $include_admin_print_footer = false;
	private $teeny = false;
	public $type = 'text-editor';
	public function __construct( $manager, $id, $args = array() ) {
		parent::__construct( $manager, $id, $args );
		if ( ! empty( $args['include_admin_print_footer'] ) ) {
			$this->include_admin_print_footer = $args['include_admin_print_footer'];
		}
		if ( ! empty( $args['teeny'] ) ) {
			$this->teeny = $args['teeny'];
		}
	}
	/* Enqueue scripts */
	public function enqueue() {
		wp_enqueue_script( 'one_lite_text_editor', get_template_directory_uri() . '/inc/customizer-page-editor/js/one-lite-text-editor.js', array( 'jquery' ), false, true );
	}
	/* Render the content on the theme customizer page */
	public function render_content() {
		?>

		<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<input type="hidden" <?php $this->link(); ?> value="<?php echo esc_textarea( $this->value() ); ?>">
		<?php
		$settings = array(
			'textarea_name' => $this->id,
			'teeny' => $this->teeny,
		);
		$control_content = $this->value();
		wp_editor( $control_content, $this->id, $settings );

		if ( $this->include_admin_print_footer === true ) {
			do_action( 'admin_print_footer_scripts' );
		}
	}
}
endif;

function show_on_front() {
	if(is_front_page())
	{
		return is_front_page() && 'posts' !== get_option( 'show_on_front' );
	}
	elseif(is_home()) 
	{
		return is_home();
	}
}
?>