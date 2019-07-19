<?php $page_layout = get_post_meta( get_the_ID(),'page_layout', true ); ?>
<?php if($page_layout == "fullwidth") { $page_width_type=12; } else { $page_width_type = 8; } ?>
<article id="post-<?php the_ID(); ?>" <?php post_class('post hentry'); ?>>
<?php
    if(has_post_thumbnail()):
       $post_thumbnail_id = get_post_thumbnail_id();
	   if($page_width_type==12){
		$post_thumbnail_url = wp_get_attachment_image_src( $post_thumbnail_id,'blog_full_thumb',true );
	   } else if($page_width_type==8){
		$post_thumbnail_url = wp_get_attachment_image_src( $post_thumbnail_id,'blog_left_thumb',true );
	   }?>
	   <div class="post-image">
            <a href="<?php echo esc_url( $post_thumbnail_url[0] ); ?>" data-rel="prettyPhoto">
            <span class="img-hover"></span>
            <span class="fullscreen"><i class="fa fa-plus"></i></span>
            <img alt="<?php the_title(); ?>" src="<?php echo esc_url( $post_thumbnail_url[0] ); ?>" width="100%">
            </a>
        </div> <?php
    endif; ?>
    <header class="post-header">
        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        <div class="blog-entry-meta">
            <div class="blog-entry-meta-date">
                <i class="fa fa-clock-o"></i>
                <span class="blog-entry-meta-date-month"><?php echo get_post_time( get_option('date_format'), true ); ?></span>
            </div>
            <div class="blog-entry-meta-author">
                <i class="fa fa-user"></i>
                <a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"><?php the_author(); ?></a>
            </div>
			<?php if(get_the_tag_list() != '') { ?>
            <div class="blog-entry-meta-tags">
                <i class="fa fa-tags"></i>
                <?php the_tags('',', ','');
				get_the_category_list( ',' ); ?>
            </div>
			<?php } ?>
            <div class="blog-entry-meta-comments">
                <i class="fa fa-comments"></i>
               <?php comments_popup_link('No Comments &#187;', '1 Comment', '% Comments'); ?> <?php edit_post_link('Edit', ' &#124; ', ''); ?>
            </div>
        </div>
    </header>
	<?php if(is_page_template( 'blog_excerpt.php' )){ ?>
	<div class="post-content"><?php the_excerpt(); ?></div>
    <?php } else { ?>
	<div class="post-content"><?php the_content(__('Read More...','creative')); ?></div>
	<?php } ?>
</article>