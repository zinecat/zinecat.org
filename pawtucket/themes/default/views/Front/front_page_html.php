<?php
/** ---------------------------------------------------------------------
 * themes/default/Front/front_page_html : Front page of site
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2013 Whirl-i-Gig
 *
 * For more information visit http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license as published by Whirl-i-Gig
 *
 * CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * This source code is free and modifiable under the terms of
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details, or visit the CollectiveAccess web site at
 * http://www.CollectiveAccess.org
 *
 * @package CollectiveAccess
 * @subpackage Core
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License version 3
 *
 * ----------------------------------------------------------------------
 */
//		print $this->render("Front/featured_set_slideshow_html.php");
?>
<div class="container">
	<div class="row">
		<div class="col-sm-8">
			<H1>Welcome to the Zine Union Catalog! <br>Use the search bar below to begin.</H1>
			<form class="navbar-form navbar-left" role="search" action="/index.php/MultiSearch/Index">
					<div class="formOutline">
						<div class="form-group">
							<input type="text" class="form-control" placeholder="Search" name="search">
						</div>
						<button type="submit" class="btn-search"><span class="glyphicon glyphicon-search"></span></button>
					</div>
				</form>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>    <p style="margin-left:85px;"><i>Partner Libraries:</i></p>
		        <a href='https://denverzinelibrary.org/' target="_blank"> <img src='/themes/default/assets/pawtucket/graphics/Logo_Denver_Zine_Library.png' style='width:160px; height:150px; margin-left: 75px;' data-toggle='tooltip' data-placement='top' title='Denver Zine Library'/></a>
		        <a href='https://zines.barnard.edu/' target="_blank"> <img src='/themes/default/assets/pawtucket/graphics/Logo_Barnard_Zine_Library.png' style='width:225px; height:50px; margin-top: 85px; margin-left: 20px;' data-toggle='tooltip' data-placement='top' title='Barnard Zine Library'/></a>
                <a href='http://archive.qzap.org/index.php' target="_blank"> <img src='/themes/default/assets/pawtucket/graphics/Logo_Queer_Zine_Archive_Project.png' style='width:210px; height:55px; margin-top: 90px; margin-left: 20px; margin-right: -200px;' data-toggle='tooltip' data-placement='top' title='Queer Zine Archive Project'/></a>
                <br>
                <a href='https://www.librarything.com/profile/clpzines' target="_blank"> <img src='/themes/default/assets/pawtucket/graphics/Carnegie_logo.png' style='width:88px; height:55px; margin-top: 10px; margin-left: 75px; margin-right: 0px;' data-toggle='tooltip' data-placement='top' title='Carnegie Library Pittsburg'/></a>
                <a href='http://www.abcnorio.org/' target="_blank"> <img src='/themes/default/assets/pawtucket/graphics/abcnorio-logo.gif' style='width:88px; height:55px; margin-top: 10px; margin-left: 163px; margin-right: 0px;' data-toggle='tooltip' data-placement='top' title='ABC No Rio'/></a>
		</div><!--end col-sm-8-->
		<div class="col-sm-4">
		</div> <!--end col-sm-4-->
	</div><!-- end row -->
</div> <!--end container-->