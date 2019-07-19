/*about theme page menu active */
jQuery(document).ready(function() {
	var active_menu;
	jQuery('.theme-menu').click(function(){
		active_menu=jQuery(this).attr('id');
		jQuery('.theme-menu').removeClass('active');
		jQuery(this).addClass('active');
		jQuery('.p_front').removeClass('active');
		jQuery('.p_front.'+active_menu).addClass('active');
	});
});

