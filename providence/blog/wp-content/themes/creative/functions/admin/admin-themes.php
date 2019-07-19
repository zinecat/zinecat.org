<?php if (!function_exists('creative_info_page')) {
	function creative_info_page() {
	$page1=add_theme_page(__('Welcome to Creative', 'creative'), __('About Creative', 'creative'), 'edit_theme_options', 'creative', 'creative_display_theme_info_page');
	
	add_action('admin_print_styles-'.$page1, 'creative_admin_info');
	}	
}
add_action('admin_menu', 'creative_info_page');

function creative_admin_info(){
	// CSS
	wp_enqueue_style('bootstrap',  get_template_directory_uri() .'/functions/admin/bootstrap/css/bootstrap.min.css');
	wp_enqueue_style('admin',  get_template_directory_uri() .'/functions/admin/admin-themes.css');
	wp_enqueue_style('font-awesome',  get_template_directory_uri() .'/css/font-awesome-4.7.0/css/font-awesome.min.css');
	//JS
	wp_enqueue_script('jquery');
	wp_enqueue_script('bootstrap-js',get_template_directory_uri() .'/functions/admin/bootstrap/js/bootstrap.js');
} 
if (!function_exists('creative_display_theme_info_page')) {
	function creative_display_theme_info_page() {
		$theme_data = wp_get_theme(); ?>
	<div class="wrap elw-page-welcome about-wrap seting-page">

    <div class="col-md-12 settings">
         <div class=" col-md-9">
            <div class="col-md-12" style="padding:0">
				<?php $wl_th_info = wp_get_theme(); ?>
				<h2><span class="elw_shortcode_heading">Welcome to Creative - Version <?php echo esc_html( $wl_th_info->get('Version') ); ?> </span></h2>
				<p style="font-size:19px;color: #555d66;">Creative is a Full Responsive Multi-Purpose Theme suitable for Business , corporate office and others .Cool Home Blog Layout with Ajax and CUSTOMIZR ready . It has TWO COLUMN LAYOUT. Creative is an incredibly spacious multipurpose responsive theme coded & designed with a lot of care and love. You can use it for your business, portfolio, blogging or any type of site. It has 4 page layouts, 2 page templates and blog display types, 2 widgets areas, focusing on business template, awesome slider, primary color option to match your logo & website, boxed & wide layout, light & dark color skin, translation ready and many more. </p>
            </div>
			
		 </div>
       
        <div class=" col-md-3">
			<div class="update_pro">
				<h3> Upgrade Pro </h3>
				<a class="demo" href="https://weblizar.com/themes/creative-premium/">Get Pro In $39</a>
			</div>
		</div>
	</div>

            <!-- Themes & Plugin -->
            <div class="col-md-12  product-main-cont">
                <ul class="nav nav-tabs product-tbs">
				    <li class="active"><a data-toggle="tab" href="#start"> Getting Started </a></li>
                    <li><a data-toggle="tab" href="#themesd"> Creative Premium </a></li>
					<li><a data-toggle="tab" href="#freepro">Free Vs Pro</a></li>
                </ul>

                <div class="tab-content">
				
				
				<div id="start" class="tab-pane fade in active">
                        <div class="space  theme active">

                            <div class=" p_head theme">
                                <!--<h1 class="section-title">WordPress Themes</h1>-->
                            </div>							

                            <div class="row p_plugin blog_gallery">
                                <div class="col-xs-12 col-sm-7 col-md-7 p_plugin_pic">
                                    <h4>Step 1: Create a Homepage</h4>
									<ol>
									<li> Create a new page -> home and publish. </li>
									<li> Go to Appearance -> Customize > Homepage Settings -> select A static page option. </li>
									<li> In "Home", select the page that you created as a home page. </li>
									<li> Now edit this page and select template "Home". </li>
									<li> Save changes </li>
									</ol>
									<a class="add_page" target="_blank" href="<?php echo admin_url('/post-new.php?post_type=page') ?>">Add New Page</a>
                                </div>
                                <div class="col-xs-12 col-sm-5 col-md-5 p_plugin_desc">
                                    <div class="row p-box">
                                         <div class="img-thumbnail">
										<img src="<?php echo get_template_directory_uri(); ?>/screenshot.png" class="img-responsive" alt="img"/>
                                    </div>
                                    </div>
                                </div>
                            </div>
							
							
							<div class="row p_plugin blog_gallery">
                                <div class="col-xs-12 col-sm-4 col-md-12 p_plugin_pic">
                                    <h4>Step 2: Customizer Options Panel </h4>
									<ol>
									<li> Go to Appearance -> Customize > Creative Options </li>
									<li> Theme General Options - General Options use for Ultimate Responsive Image Slider enable/disable,  Snow Effect on/off, add logo, logo height, logo width, add favicon. </li>
									<li> Typography Section - Select font family for theme. </li>
									<li> Home Service Options - It is use to enable/disable service on homepage, add Service Title, Service Description, service link. </li>
									<li> Portfolio Options - It is use to enable/disable portfolio on homepage, add portfolio heading, Portfolio Description, Portfolio link. </li>
									<li> Home Blog Options - Use to enable/disable blog on homepage, add blog title, description, and enable/disable blog section on homepage. </li>
									<li> Footer Call-Out Options - Use to add Call-Out title, button text, link and enable/disable Call-Out section on homepage.</li>
									<li> Footer Options- Use to enabled/disable Copyright Section, add Customization text, developed by text and developed by link. </li>
									<li> Social Options- Use to enabled/disable social media on header and footer, Enable/disable Header Contact, add social links, email id phone no. </li>
									<li> Home Page Layout Options - use for Front Page Layout Option.</li>
									</ol>
									<a class="add_page" target="_blank" href="<?php echo esc_url(admin_url('customize.php')); ?>">Go to Customizer</a>
                                </div>
                            </div>
							
							
							<div class="row p_plugin blog_gallery visit_pro">
                                <p>Visit Our Latest Pro Themes & See Demos</p>
								<p style="font-size: 17px !important;">We have put in a lot of effort in all our themes, free and premium both. Each of our Premium themes has a corresponding free version so that you can try out the theme before you decide to purchase it.</p>
								<a href="https://weblizar.com/themes/">Visit Themes</a>
                            </div>	
                        </div>
                    </div>
				
				<!-- end 1st tab -->
				
				
                    <div id="themesd" class="tab-pane fade">
                        <div class="space theme">
                            <div class="row p_plugin blog_gallery">
                                <div class="col-xs-12 col-sm-4 col-md-5 p_plugin_pic">
                                    <div class="img-thumbnail">
										<img src="<?php echo get_template_directory_uri(); ?>/img/Creative.jpg" class="img-responsive" alt="img"/>
                                    </div>
									
                                </div>
                                <div class="col-xs-12 col-sm-5 col-md-5 p_plugin_desc">
                                    <div class="row p-box">
                                        <div>
                                            <p><strong>Description: </strong> Creative Premium is perfectly scalable, performance and responsive, multi-purpose WordPress theme and suitable for Business. Perfect to promote your work or your creative business. It is cross-browser compatible, fully responsive, and retina ready.Creative is one of our most customizable and flexible themes, you can create practically unlimited versions of your website, your imagination is the only limit.  </p>
                                        </div>
										<p><strong>Tags: </strong>two-columns, custom-menu, right-sidebar, custom-background, featured-image-header, sticky-post, theme-options, threaded-comments, featured-images, flexible-header</p>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-3 col-md-2 p_plugin_pic">
                                    <div class="price1">
                                        <span class="currency">USD</span>
                                        <span class="price-number">$<span>39</span></span>
                                    </div>
                                    <div class="btn-group-vertical">
                                        <a class="btn btn-primary btn-lg" href="https://weblizar.com/themes/creative-premium/">Detail</a>
                                    </div>
                                </div>
                            </div>
							
							
							<div class="row p_plugin blog_gallery">
                                <div class="col-xs-12 col-sm-4 col-md-4 p_plugin_pic">
                                    <div class="img-thumbnail pro_theme">
										<img src="<?php echo get_template_directory_uri(); ?>/img/Enigma.jpg" class="img-responsive" alt="img"/>
										<div class="btn-vertical">
										<h4 class="pro_thm">
                                        <a href="https://weblizar.com/themes/enigma-premium/">Enigma Premium</a></h4>
										</div>
                                    </div>
									
                                </div>
                                <div class="col-xs-12 col-sm-4 col-md-4 p_plugin_pic">
                                    <div class="img-thumbnail pro_theme">
										<img src="<?php echo get_template_directory_uri(); ?>/img/explora.jpg" class="img-responsive" alt="img"/>
										<div class="btn-vertical">
										<h4 class="pro_thm">
                                        <a href="https://weblizar.com/themes/explora-premium/">Explora Premium</a></h4>
										</div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-4 col-md-4 p_plugin_pic">
                                    <div class="img-thumbnail pro_theme">
										<img src="<?php echo get_template_directory_uri(); ?>/img/Scorline.jpg" class="img-responsive" alt="img"/>
										<div class="btn-vertical">
										<h4 class="pro_thm">
                                        <a href="https://weblizar.com/themes/scoreline-premium/">Scorline Premium</a></h4>
										</div>
                                    </div>
                                </div>
                            </div>
							
							
							<div class="row p_plugin blog_gallery visit_pro">
                                <p>Visit Our Latest Pro Themes & See Demos</p>
								<p style="font-size: 17px !important;">We have put in a lot of effort in all our themes, free and premium both. Each of our Premium themes has a corresponding free version so that you can try out the theme before you decide to purchase it.</p>
								<a href="https://weblizar.com/themes/">Visit Themes</a>
                            </div>	
                        </div>
                    </div>
					
					<div id="freepro" class="tab-pane fade">
							<div class=" p_head theme">
                                <!--<h1 class="section-title">Weblizar Offers</h1>-->
                            </div>
						<div class="row p_plugin blog_gallery">		
						<div class="columns">
						  <ul class="price">
							<li class="header" style="background:#59ab02">Creative</li>
							<li class="grey">Features</li>
							<li>Customize Front Page</li>
							<li>Theme Option Panel</li>
							<li>Unlimited Color Skins</li>
							<li>Multiple Background Patterns</li>
							<li>5 Portfolio Layout</li>
							<li>3 Page Layout</li>
							<li>2 Service Page Template</li>
							<li>2 About Us Page with short-code</li>
							<li>Customs Widgets</li>
						  </ul>
						</div>
						
						 <div class="columns">
						  <ul class="price">
							<li class="header">Free</li>
							<li class="grey">$ 00</li>
							<li><i class="fa fa-check"></i></li>
							<li><i class="fa fa-close"></i></li>
							<li><i class="fa fa-close"></i></li>
							<li><i class="fa fa-close"></i></li>
							<li><i class="fa fa-close"></i></li>
							<li><i class="fa fa-close"></i></li>
							<li><i class="fa fa-close"></i></li>
							<li><i class="fa fa-close"></i></li>
							<li><i class="fa fa-close"></i></li>
						  </ul>
						</div>

						<div class="columns">
						  <ul class="price">
							<li class="header" style="background-color:#59ab02">Creative Pro</li>
							<li class="grey">$ 39</li>
							<li><i class="fa fa-check"></i></li>
							<li><i class="fa fa-check"></i></li>
							<li><i class="fa fa-check"></i></li>
							<li><i class="fa fa-check"></i></li>
							<li><i class="fa fa-check"></i></li>
							<li><i class="fa fa-check"></i></li>
							<li><i class="fa fa-check"></i></li>
							<li><i class="fa fa-check"></i></li>
							<li><i class="fa fa-check"></i></li>
							<li class="grey"><a href="http://demo.weblizar.com/creative-premium/" class="pro_btn">Visit Demo</a></li>
						  </ul>
						</div>
						</div>
					</div>
                </div>
            </div>            
<?php
	}
}
?>