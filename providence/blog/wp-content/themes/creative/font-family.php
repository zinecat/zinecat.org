<?php $wl_theme_options = creative_general_options(); ?>

<style>
.logo h1 a{
	font-family : <?php echo $wl_theme_options['logo_font']; ?>;
}
.menu ul li a, .menu .dropdown-menu li a {
	font-family : <?php echo $wl_theme_options['header_menu_font']; ?>;
}
.bounceIn, h2.title{
	font-family : <?php echo $wl_theme_options['themes_title']; ?>;
}
.fadeInRight, .slogan-title, .phone-login a,
.breadcrumbs ul li,
.grid figure figcaption h2,figure.effect-zoe p, .main-content h3,
.main-content p, .main-content a, #latest-posts h4 a, .content h2 a,
.post-content p, .btn-color, .post-meta, .blog-entry-meta, .comment-by, .content p, .content h3, input.form-control,
textarea.form-control, .pager .previous a, .pager .next a,
p.credits, h3.light, section.footer-one ul li a, .textwidget,
h3.title, .widget h3, .tabs ul.posts li, .widget ul, .testimonial-author-info, .testimonial p, .widget p{
	font-family : <?php echo $wl_theme_options['theme_descrp_font']; ?>;	
}
</style>