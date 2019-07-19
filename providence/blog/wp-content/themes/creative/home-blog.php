<!-- Latest Posts -->
<?php $wl_theme_options=creative_general_options();
if($wl_theme_options['home_blog']=='on'){ ?>
<div id="latest-posts" class=" margin-top100">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 text-center">
			<?php if($wl_theme_options['home_blog_title']){ ?>
                <h2 class="wow bounceIn creative_blog_title"><?php echo esc_attr($wl_theme_options['home_blog_title']); ?></h2>
            <?php } if($wl_theme_options['home_blog_description']){ ?>
				<h4 class="wow fadeInRight creative_blog_desc"><?php echo get_theme_mod('home_blog_description' , $wl_theme_options['home_blog_description']); ?></h4>
            <?php } ?>
			</div>
        </div>
        <div class="row">
            <div class="masonry1 padding-top40"><?php
				$count_posts = wp_count_posts();
				$published_posts = $count_posts->publish;
                $args = array('post_type' => 'post' ,'post__not_in'  => get_option( 'sticky_posts' ), 'posts_per_page' => -1);
                $post_type_data = new WP_Query($args);
				$i=1;
                if($post_type_data->have_posts()){
                    while($post_type_data->have_posts()): $post_type_data->the_post(); ?>
                        <!-- post item -->
                        <div class="item col-lg-3 col-md-3 col-sm-6 post-item wow fadeInUp" id="row-<?php echo $i; ?>">
                            <div class="post-img">
                               <?php if(has_post_thumbnail()):
									$post_thumbnail_id = get_post_thumbnail_id();
									$post_thumbnail_url = wp_get_attachment_image_src( $post_thumbnail_id,'home_post_thumb',true ); ?>
									<img alt="creative_image" class="img-responsive img-blog" src="<?php echo $post_thumbnail_url[0]; ?>"><?php
									endif; ?>
                            </div>
                            <div class="post-content blog-post-content">
                                <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                                <?php the_excerpt(); ?>
                            </div>
                            <div class="meta post-meta">
									<div class="post-date post-meta-content">
										<i title="<?php echo get_post_time( get_option('date_format'), true ); ?>" class="fa fa-clock-o"></i>
									</div>
									<?php $comment = wp_count_comments(get_the_ID()); ?>
									<div class="post-comment post-meta-content">
										<i class="fa fa-comment-o"></i> <?php comments_popup_link('0', '1', '%'); ?>
									</div>
									<div class="post-link post-meta-content">
										<a title="Read More" class="post-meta-link" href="<?php the_permalink(); ?>"><?php _e('...','creative'); ?></a>
									</div>
								</div>
                        </div><?php
                   if($i%4==0){ echo "<div class='clearfix'></div>"; } $i++; endwhile;
					} ?>
                <!-- /post item -->
            </div>
        </div>
    </div>
</div>
<ul class="post-footer post-btn1"><li><a class="append-button btn btn-color"><?php _e('Show More', 'creative'); ?></a></li></ul>
<?php } ?>
<!-- /Latest Posts -->
<?php require('maso_part2.php'); ?>