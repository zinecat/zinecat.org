<div class="breadcrumb-wrapper">
	<div class="pattern-overlay">
		<div class="container">
			<div class="row">
				<div class="col-lg-6 col-md-6 col-xs-12 col-sm-6">
					<h2 class="title"><?php if(is_home()){ _e('Blog Posts', 'creative'); } else { the_title(); } ?></h2>
				</div>
				<?php if(!is_home()){
				 if (function_exists('creative_breadcrumbs')) creative_breadcrumbs();
				 } ?>
			</div>
		</div>
	</div>
</div>