<?php 

/**
 * The template for displaying comments
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package creative
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( ! empty($comments_by_type['comment']) ) : ?>
<div class="star">
	<div class="row">
		<div class="col-md-12">
			<div class="star-divider">
				<div class="star-divider-icon">
					<i class=" fa fa-star"></i>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="comment-wrapper">
    <h3 class="title"><?php
    printf( _n( 'One Comment on &ldquo;%2$s&rdquo;', '%1$s Comments on &ldquo;%2$s&rdquo;',   get_comments_number(), 'creative' ),
    number_format_i18n( get_comments_number() ), get_the_title() ); ?></h3><?php
    if ( post_password_required(get_the_ID()) ) { ?>
        <p class="nocomments"><?php _e('Please enter the password in above text field to view or post a comments','creative'); ?></p>
		</div>
		<?php return;
    } ?>
    <div class="clearfix"></div>
    <div class="comments-sec">
        <ol class="commentlist">
            <?php
            wp_list_comments('style=ol&callback=creative_comments'); ?>
        </ol>
		<?php paginate_comments_links(); ?>
    </div>
    <div class="clearfix">
    </div>
</div>
<?php endif; ?>
<!-- /Comments Section -->
<?php
	if ( comments_open() ) { ?>
	<div class="star">
	<div class="row">
		<div class="col-md-12">
			<div class="star-divider">
				<div class="star-divider-icon">
					<i class=" fa fa-star"></i>
				</div>
			</div>
		</div>
	</div>
</div><?php
		$fields=array(
			'author'=>'<div class="row">
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
								<input type="text" name="author" id="author" required="" placeholder="Name" value="" class="form-control">
                            </div>',
			'email'=>'<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
							<input type="text" name="email" id="email" required="" placeholder="Email" value="" class="form-control">
                        </div>',
			'Website'=>'<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
							<input type="text" name="url" id="url" placeholder="Website" value="" class="form-control">
                        </div>
					</div>',
				);
		function wc_defaullt_fields($fields){
			return $fields;
		}
		add_filter('comment_form_default_fields', 'wc_defaullt_fields');
		$comments_args = array(
				'fields'=> apply_filters( 'comment_form_default_fields', $fields ),
				'label_submit'=>__('Submit Message','creative'),
				'comment_notes_after' => '',
				'comment_field' => '<div class="row">
							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
								<textarea required="" placeholder="Comment" name="comment" id="comment" cols="40" rows="3" class="form-control"></textarea>
							</div>
						',
				'class_submit'=>'btn btn-color pull-right',
		);
		comment_form($comments_args);
	}?>
	<?php
if(get_post_meta(get_the_ID(),'page_layout',true)=="fullwidth"){ ?>
<style>
</style><?php
}elseif(get_post_meta(get_the_ID(),'page_layout',true)=="fullwidth_right"){ ?>
<style>
.depth-1 {
  margin: 23px 0 0 116px;
}
ol.commentlist {
  margin-left: -125px;
}
</style><?php
} ?>