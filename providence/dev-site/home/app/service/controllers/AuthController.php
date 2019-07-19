<?php
/* ----------------------------------------------------------------------
 * app/service/controllers/AuthController.php :
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
	require_once(__CA_LIB_DIR__.'/ca/Service/BaseServiceController.php');
	require_once(__CA_LIB_DIR__.'/ca/Service/ModelService.php');

	class AuthController extends BaseServiceController {
		# -------------------------------------------------------
		public function __construct(&$po_request, &$po_response, $pa_view_paths) {
 			parent::__construct($po_request, $po_response, $pa_view_paths);
 		}
		# -------------------------------------------------------
		public function login() {
			$o_session = $this->getRequest()->getSession();
			if(!$o_session->getSessionID()) {
				$this->view->setVar("errors", array("Invalid session"));
				$this->render("json_error.php");
				return;
			}

			$this->view->setVar("content",array('authToken' => $o_session->getServiceAuthToken()));
			$this->render('json.php');
		}
		# -------------------------------------------------------
		public function logout() {
			$o_session = $this->getRequest()->getSession();
			if(!$o_session->getSessionID()) {
				$this->view->setVar("errors", array("Invalid session"));
				$this->render("json_error.php");
				return;
			}

			$o_session->deleteSession();

			$this->view->setVar("content",array('authToken' => $o_session->getServiceAuthToken()));
			$this->render('json.php');
		}
		# -------------------------------------------------------
	}
