<!-- Sidebar Start -->
<div class="sidebar col-lg-4 col-md-4 col-sm-4 col-xs-12">
<?php if ( is_active_sidebar( 'sidebar-primary' ) )
	{ dynamic_sidebar( 'sidebar-primary' );	}
	else  {  ?>
	<!-- Search Widget Start -->
	<div class="widget search-form">
		<div class="input-group">
			<input type="text" value="Search..." onfocus="if(this.value=='Search...')this.value='';" onblur="if(this.value=='')this.value='Search...';" class="search-input form-control">
			<span class="input-group-btn">
			<button type="submit" class="subscribe-btn btn"><i class="fa fa-search"></i></button>
			</span>
		</div>
		<!-- /input-group -->
	</div>
	<!-- Search Widget End -->
	<!-- Tab Start -->
	<div class="widget tabs">
		<div id="horizontal-tabs">
			<ul class="tabs">
				<li id="tab1" class="current"><?php _e('Popular', 'creative'); ?></li>
				<li id="tab2"><?php _e('Recent', 'creative'); ?></li>
				<li id="tab3"><?php _e('Comments', 'creative'); ?></li>
			</ul>
			<div class="contents">
				<div class="tabscontent" id="content1" style="display: block;">
					<ul class="posts">
						<li>
							<a href="#"><img class="img-thumbnail recent-post-img" alt="" src="<?php echo esc_url( get_template_directory_uri() ); ?>/img/68x68.png"></a>
							<p><a href="#"><?php _e('Lorem Ipsum is simply dummy text.', 'creative'); ?></a></p>
							<span class="color"><?php _e('27 July 2014', 'creative'); ?></span>
						</li>
						<li>
							<a href="#"><img class="img-thumbnail recent-post-img" alt="" src="<?php echo esc_url( get_template_directory_uri() ); ?>/img/68x68.png"></a>
							<p><a href="#"><?php _e('Lorem Ipsum is simply dummy text.', 'creative'); ?></a></p>
							<span class="color"><?php _e('30 July 2014', 'creative'); ?></span>
						</li>
					</ul>
				</div>
				<div class="tabscontent" id="content2">
					<ul class="posts">
						<li>
							<a href="#"><img class="img-thumbnail recent-post-img" alt="" src="<?php echo esc_url( get_template_directory_uri() ); ?>/img/68x68.png"></a>
							<p><a href="#"><?php _e('Lorem Ipsum is simply dummy text.', 'creative'); ?></a></p>
							<span class="color"><?php _e('27 July 2014', 'creative'); ?></span>
						</li>
						<li>
							<a href="#"><img class="img-thumbnail recent-post-img" alt="" src="<?php echo esc_url( get_template_directory_uri() ); ?>/img/68x68.png"></a>
							<p><a href="#"><?php _e('Lorem Ipsum is simply dummy text.', 'creative'); ?></a></p>
							<span class="color"><?php _e('30 July 2014', 'creative'); ?></span>
						</li>
					</ul>
				</div>
				<div class="tabscontent" id="content3">
					<ul class="posts">
						<li>
							<a href="#"><img class="img-thumbnail recent-post-img" alt="" src="<?php echo esc_url( get_template_directory_uri() ); ?>/img/68x68.png"></a>
							<p><a href="#"><?php _e('Lorem Ipsum is simply dummy text.', 'creative'); ?></a></p>
							<?php _e('by', 'creative'); ?> <span class="color"><?php _e('wptuts+', 'creative'); ?></span>
						</li>
						<li>
							<a href="#"><img class="img-thumbnail recent-post-img" alt="" src="<?php echo esc_url( get_template_directory_uri() ); ?>/img/68x68.png"></a>
							<p><a href="#"><?php _e('Lorem Ipsum is simply dummy text.', 'creative'); ?></a></p>
							<?php _e('by', 'creative'); ?> <span class="color"><?php _e('wptuts+', 'creative'); ?></span>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<!-- /Tab End -->
	<!-- Testimonials Widget  -->
	<div class="row">
		<div class="testimonials widget">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<div class="testimonials-title">
					<h3 class="title"><?php _e('Testimonials', 'creative'); ?></h3>
				</div>
			</div>
			<div class="clearfix"></div>
			<div id="testimonials-carousel" class="testimonials-carousel slide">
				<div class="carousel-inner">
					<div class="item active">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<div class="testimonial item">
								<p>
									<?php _e('Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type.', 'creative'); ?>
								</p>
								<div class="testimonials-arrow">
								</div>
								<div class="author">
									<div class="testimonial-image "><img alt="" src="<?php echo esc_url( get_template_directory_uri() ); ?>/img/testimonial/member-1.jpg"></div>
									<div class="testimonial-author-info">
										<a href="#"><span class="color"><?php _e('Monica Sing', 'creative'); ?></span></a> <?php _e('FIFO Themes', 'creative'); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="item">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<div class="testimonial item">
								<p>
									<?php _e('Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type.', 'creative'); ?>
								</p>
								<div class="testimonials-arrow">
								</div>
								<div class="author">
									<div class="testimonial-image "><img alt="" src="<?php echo esc_url( get_template_directory_uri() ); ?>/img/testimonial/member-2.jpg"></div>
									<div class="testimonial-author-info">
										<a href="#"><span class="color"><?php _e('Monzurul Haque', 'creative'); ?></span></a><?php _e('FIFO Themes', 'creative'); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="item">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<div class="testimonial item">
								<p>
									<?php _e('Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type.', 'creative'); ?>
								</p>
								<div class="testimonials-arrow">
								</div>
								<div class="author">
									<div class="testimonial-image "><img alt="" src="<?php echo esc_url( get_template_directory_uri() ); ?>/img/testimonial/member-1.jpg"></div>
									<div class="testimonial-author-info">
										<a href="#"><span class="color"><?php _e('Carol Johansen', 'creative'); ?></span></a> <?php _e('Weblizar Themes', 'creative'); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /Testimonials Widget  -->
	<!-- Text Widget -->
	<div class="widget text">
		<h3 class="title"><?php _e('Text Widget', 'creative'); ?></h3>
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<p><?php _e('Fugiat dapibus, tellus ac cursus commodo, mauesris condime ntum nibh, ut fermentum mas justo sitters amet risus. Cras mattis cosi sectetut amet fermens etrsaters tum aecenas faucib sadips amets.', 'creative'); ?></p>
			</div>
		</div>
	</div>
	<!-- /Text Widget -->
	<!-- Category Widget Start -->
	<div class="widget category">
		<h3 class="title"><?php _e('Categories', 'creative'); ?></h3>
		<ul class="category-list slide">
			<li><a href="#"><?php _e('Web Design', 'creative'); ?></a></li>
			<li><a href="#"><?php _e('Graphic Design', 'creative'); ?></a></li>
			<li><a href="#"><?php _e('Illustration', 'creative'); ?></a></li>
			<li><a href="#"><?php _e('Logo Design', 'creative'); ?></a></li>
			<li><a href="#"><?php _e('Wordpress Themes', 'creative'); ?></a></li>
		</ul>
	</div>
	<!-- /Category Widget End -->
	<!-- Tag Cloud Widget Start -->
	<div class="widget tags">
		<h3><?php _e('Tag Cloud', 'creative'); ?></h3>
		<ul class="tag-cloud">
			<li><a class="btn btn-xs btn-primary" href="#"><?php _e('Design', 'creative'); ?></a></li>
			<li><a class="btn btn-xs btn-primary" href="#"><?php _e('Amazing', 'creative'); ?></a></li>
			<li><a class="btn btn-xs btn-primary" href="#"><?php _e('Colors', 'creative'); ?></a></li>
			<li><a class="btn btn-xs btn-primary" href="#"><?php _e('Responsive', 'creative'); ?></a></li>
			<li><a class="btn btn-xs btn-primary" href="#"><?php _e('Multipurpose', 'creative'); ?></a></li>
			<li><a class="btn btn-xs btn-primary" href="#"><?php _e('Clean', 'creative'); ?></a></li>
			<li><a class="btn btn-xs btn-primary" href="#"><?php _e('Wordpress', 'creative'); ?></a></li>
			<li><a class="btn btn-xs btn-primary" href="#"><?php _e('Bootstrap', 'creative'); ?></a></li>
			<li><a class="btn btn-xs btn-primary" href="#"><?php _e('Themes', 'creative'); ?></a></li>
			<li><a class="btn btn-xs btn-primary" href="#"><?php _e('Retina Ready', 'creative'); ?></a></li>
		</ul>
	</div>
	<!--/Tag Cloud Widget End -->
	<!-- Ads Widget Start -->
	<div class="widget ads">
		<h3 class="title"><?php _e('Advertisement', 'creative'); ?></h3>
		<div class="ads-img row">
			<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
				<img class="img-thumbnail" alt="" src="<?php echo esc_url( get_template_directory_uri() ); ?>/img/ad.png">
			</div>
			<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
				<img class="img-thumbnail" alt="" src="<?php echo esc_url( get_template_directory_uri() ); ?>/img/ad.png">
			</div>
			<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
				<img class="img-thumbnail" alt="" src="<?php echo esc_url( get_template_directory_uri() ); ?>/img/ad.png">
			</div>
		</div>
	</div>
	<!-- /Ads Widget End -->
<?php } ?>
</div>
<!-- /Sidebar End -->