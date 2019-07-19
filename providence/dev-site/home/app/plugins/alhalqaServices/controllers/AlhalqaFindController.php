<?php
/* ----------------------------------------------------------------------
 * app/plugins/aboutDrawingServices/controllers/AlhalqaFindController.php
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2015 Whirl-i-Gig
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
 * ----------------------------------------------------------------------
 */
 	require_once(__CA_APP_DIR__.'/plugins/alhalqaServices/services/AlhalqaSearchService.php');
	require_once(__CA_APP_DIR__.'/service/controllers/FindController.php');

	class AlhalqaFindController extends FindController {

		# -------------------------------------------------------
		public function __call($ps_table, $pa_args) {
			$vo_service = new AlhalqaSearchService($this->request,$ps_table);
			$va_content = $vo_service->dispatch();

			if(intval($this->request->getParameter("pretty",pInteger))>0){
				$this->view->setVar("pretty_print",true);
			}

			if($vo_service->hasErrors()){
				$this->view->setVar("errors",$vo_service->getErrors());
				$this->render("json_error.php");
			} else {
				$this->view->setVar("content",$va_content);
				$this->render("json.php");
			}
		}
		# -------------------------------------------------------


	}
