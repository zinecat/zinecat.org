<?php
add_action('wp_enqueue_scripts', 'inventiveScripts' , 20);
function inventiveScripts() 
{
  wp_enqueue_style( 'default',get_template_directory_uri() .'/style.css'); 
  wp_enqueue_style('new', get_stylesheet_directory_uri() . '/light-green.css');
  

}
?>

