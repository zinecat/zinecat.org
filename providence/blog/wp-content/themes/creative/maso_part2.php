<script>
docReady( function() {
var $container = $('.masonry1');
var totalPosts= <?php echo $published_posts; ?>;
var j;
var i;
var totPost = totalPosts;
j = i = totalPosts-4; //  Show only 10 posts
for(totalPosts; i>=1; i--,totalPosts--){
	jQuery('#row-'+totalPosts).hide();
}
if(totPost<=4){
	jQuery('.post-btn1').hide();
} else if(totPost>=5){
	jQuery('.post-btn1').show();
}
function getItemElement(id) {
  var elem = document.createElement('div');
  elem.className = 'cls-'+id+' item col-lg-3 col-md-3 col-sm-6 post-item wow fadeInUp';
  return elem;
}
jQuery(".append-button").click(function(){
	var showPosts = 4;
	while(!showPosts==0 && totalPosts < totPost ){
	var plusOne = totalPosts+1;
	var elems = getItemElement(totalPosts+1);
	 var Html = jQuery('#row-'+plusOne).html();
	 jQuery('#row-'+plusOne).remove();
	 jQuery(elems).append(Html);
	 $container.append( elems ).masonry( 'appended', elems );
	 showPosts--;
	 totalPosts++;
	}
	jQuery('.cls-'+totalPosts).after('<div class="clearfix"></div>');
	if(totPost==totalPosts)
	{
	jQuery('.post-btn1').hide();
	}
});
});
</script>