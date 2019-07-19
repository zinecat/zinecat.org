<?php
/* ----------------------------------------------------------------------
 * install/inc/Installer.php : class that wraps installer functionality
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2011-2012 Whirl-i-Gig
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

require_once(__CA_LIB_DIR__.'/core/Cache/CompositeCache.php');
require_once(__CA_LIB_DIR__.'/core/Configuration.php');
require_once(__CA_LIB_DIR__.'/core/Datamodel.php');
require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_LIB_DIR__.'/core/Media/MediaVolumes.php');
require_once(__CA_APP_DIR__.'/helpers/utilityHelpers.php');
require_once(__CA_LIB_DIR__.'/ca/BundlableLabelableBaseModelWithAttributes.php');
require_once(__CA_MODELS_DIR__.'/ca_users.php');
require_once(__CA_MODELS_DIR__.'/ca_user_groups.php');
require_once(__CA_LIB_DIR__.'/core/Plugins/SearchEngine/ElasticSearch.php');

class Installer {
	# --------------------------------------------------
	protected $opa_errors;
	protected $opb_debug;
	protected $ops_profile_debug = "";
	# --------------------------------------------------
	protected $ops_profile_dir;
	protected $ops_profile_name;

	protected $ops_admin_email;
	protected $opb_overwrite;
	# --------------------------------------------------
	/** @var  SimpleXMLElement */
	protected $opo_profile;
	/** @var  SimpleXMLElement */
	protected  $opo_base;
	/** @var  string */
	protected  $ops_base_name;
	# --------------------------------------------------
	/** @var array  */
	protected $opa_locales;
	# --------------------------------------------------
	/**
	 * @var Db
	 */
	protected $opo_db;
	# --------------------------------------------------
	/**
	 * Constructor
	 *
	 * @param string $ps_profile_dir path to a directory containing profiles and XML schema
	 * @param string $ps_profile_name of the profile, as in <$ps_profile_dir>/<$ps_profile_name>.xml
	 * @param string $ps_admin_email e-mail address for the initial administrator account
	 * @param boolean $pb_overwrite overwrite existing install? optional, defaults to false
	 * @param boolean $pb_debug enable or disable debugging mode
	 * @param boolean $pb_skip_load dont actually load profile (useful if you want to fill in some gaps by hand)
	 */
	public function  __construct($ps_profile_dir,$ps_profile_name,$ps_admin_email=null,$pb_overwrite=false,$pb_debug=false,$pb_skip_load=false) {
		$this->ops_profile_dir = $ps_profile_dir;
		$this->ops_profile_name = $ps_profile_name;
		$this->ops_admin_email = $ps_admin_email;
		$this->opb_overwrite = $pb_overwrite;
		$this->opb_debug = $pb_debug;

		$this->opa_locales = array();

		$this->opo_db = new Db();

		if(!$pb_skip_load) {
			if($this->loadProfile($ps_profile_dir, $ps_profile_name)) {
				$this->extractAndLoadBase();

				if(!$this->validateProfile()) {
					$this->addError("Profile validation failed. Your profile doesn't conform to the required XML schema.");
				}
			} else {
				$this->addError("Could not read profile '{$ps_profile_name}'. Please check the file permissions.");
			}
		}
	}
	# --------------------------------------------------
	/**
	 * Get an installer instance from just a XML string. This allows initializing
	 * a system without having to write the profile to a file first.
	 *
	 * @param string $ps_profile_xml
	 * @param string $ps_admin_email
	 * @param bool $pb_overwrite
	 * @return Installer|false
	 */
	public static function getFromString($ps_profile_xml, $ps_admin_email='', $pb_overwrite=false) {
		$o_installer = new Installer(__CA_BASE_DIR__.'/install/profiles/xml', 'export', $ps_admin_email, $pb_overwrite, false, true);
		if($o_installer->loadProfileFromString($ps_profile_xml, true)) {
			$o_installer->extractAndLoadBase();
			return $o_installer;
		} else {
			return false;
		}
	}
	# --------------------------------------------------
	/**
	 * @param string $ps_profile_dir path to a directory containing profiles and XML schema
	 * @param string $ps_profile_name of the profile, as in <$ps_profile_dir>/<$ps_profile_name>.xml
	 * @return array
	 */
	static public function getProfileInfo($ps_profile_dir, $ps_profile_name) {
		$o_installer = new Installer($ps_profile_dir,$ps_profile_name);
		$o_installer->loadProfile($ps_profile_dir, $ps_profile_name);

		return array(
			'useForConfiguration' => $o_installer->getAttribute($o_installer->opo_profile, 'useForConfiguration'),
			'display' => (string)$o_installer->opo_profile->{'profileName'},
			'description' => (string)$o_installer->opo_profile->{'profileDescription'},
			'locales' => (string)$o_installer->opo_profile->{'locales'},
		);
	}
	# --------------------------------------------------
	private function validateProfile() {
		// simplexml doesn't support validation -> use DOMDocument
		$vo_profile = new DOMDocument();
		$vo_profile->load($this->ops_profile_dir."/".$this->ops_profile_name.".xml");

		if($this->opo_base) {
			$vo_base = new DOMDocument();
			$vo_base->load($this->ops_profile_dir."/".$this->ops_base_name.".xml");

			if($this->opb_debug) {
				ob_start();
				$vb_return = $vo_profile->schemaValidate($this->ops_profile_dir."/profile.xsd") && $vo_base->schemaValidate($this->ops_profile_dir."/profile.xsd");
				$this->ops_profile_debug .= ob_get_clean();
			} else {
				$vb_return = @$vo_profile->schemaValidate($this->ops_profile_dir."/profile.xsd") && @$vo_base->schemaValidate($this->ops_profile_dir."/profile.xsd");
			}
		} else {
			if($this->opb_debug) {
				ob_start();
				$vb_return = $vo_profile->schemaValidate($this->ops_profile_dir."/profile.xsd");
				$this->ops_profile_debug .= ob_get_clean();
			} else {
				$vb_return = @$vo_profile->schemaValidate($this->ops_profile_dir."/profile.xsd");
			}
		}

		return $vb_return;
	}
	# --------------------------------------------------
	public function loadProfile($ps_profile_dir, $ps_profile_name) {
		$vs_file = $ps_profile_dir."/".$ps_profile_name.".xml";

		if(is_readable($vs_file)) {
			$this->opo_profile = simplexml_load_file($vs_file);
			return true;
		} else {
			return false;
		}
	}
	# --------------------------------------------------
	public function loadProfileFromString($ps_xml, $pb_skip_validation=false) {
		if(!$pb_skip_validation) {
			$o_dom = new DOMDocument();
			$o_dom->loadXML($ps_xml);
			if(!@$o_dom->schemaValidate(__CA_BASE_DIR__.'/install/profiles/xml/profile.xsd')) {
				throw new Exception("Profile validation failed. Your profile doesn't conform to the required XML schema.");
			}
		}
		$this->opo_profile = @simplexml_load_string($ps_xml);
		if(!$this->opo_profile) {
			throw new Exception('Something went wrong while initializing Installer. Did you send valid XML?');
		}

		return (bool) $this->opo_profile;
	}
	# --------------------------------------------------
	public function extractAndLoadBase() {
		$this->ops_base_name = self::getAttribute($this->opo_profile, "base");
		if($this->ops_base_name) {
			$this->opo_base = simplexml_load_file($this->ops_profile_dir."/".$this->ops_base_name.".xml");
		} else {
			$this->opo_base = null;
		}
	}
	# --------------------------------------------------
	# ERROR HANDLING / DEBUGGING
	# --------------------------------------------------
	protected function addError($ps_error) {
		$this->opa_errors[] = $ps_error;
	}
	# --------------------------------------------------
	/**
	 * Returns number of errors that occurred while processing
	 *
	 * @return int number of errors
	 */
	public function numErrors() {
		return sizeof($this->opa_errors);
	}
	# --------------------------------------------------
	/**
	 * Returns array of error messages
	 *
	 * @return array errors
	 */
	public function getErrors() {
		return $this->opa_errors;
	}
	# --------------------------------------------------
	/**
	 * Get profile debug info. Only has content if debug mode is enabled.
	 * WARNING: can lead to very verbose output, especially if the php
	 * extension xdebug is installed and enabled.
	 *
	 * @return string profile debug info
	 */
	public function getProfileDebugInfo() {
		return $this->ops_profile_debug;
	}
	# --------------------------------------------------
	# UTILITIES
	# --------------------------------------------------
	protected  static function getAttribute($po_simplexml, $ps_attr) {
		if(isset($po_simplexml[$ps_attr])) {
			return (string) $po_simplexml[$ps_attr];
		} else {
			return null;
		}
	}
	# --------------------------------------------------
	private static function getRandomPassword() {
		return substr(md5(uniqid(microtime())), 0, 6);
	}
	# --------------------------------------------------
	private static function createDirectoryPath($ps_path) {
		if (!file_exists($ps_path)) {
			if (!@mkdir($ps_path, 0777, true)) {
				return false;
			} else {
				return true;
			}
		} else {
			return true;
		}
	}
	# --------------------------------------------------
	/**
	 * @param LabelableBaseModelWithAttributes $t_instance
	 * @param SimpleXMLElement $po_labels
	 * @param array $pa_locales array
	 * @param bool $pb_force_preferred
	 * @return bool
	 */
	protected static function addLabelsFromXMLElement($t_instance,$po_labels, $pa_locales, $pb_force_preferred=false) {
		require_once(__CA_LIB_DIR__."/ca/LabelableBaseModelWithAttributes.php");

		if(!($t_instance instanceof LabelableBaseModelWithAttributes)) {
			return false;
		}
		/** @var LabelableBaseModelWithAttributes $t_instance */
		if (!$po_labels || !$po_labels->children()) {
			$t_instance->addLabel(array($t_instance->getLabelDisplayField() => "???"), array_shift($pa_locales), false, true);
			return true; 
		}

		$va_old_label_ids = array_flip($t_instance->getLabelIDs());

		foreach($po_labels->children() as $vo_label) {
			$va_label_values = array();
			$vs_locale = self::getAttribute($vo_label, "locale");
			$vn_locale_id = $pa_locales[$vs_locale];

			$vb_preferred = self::getAttribute($vo_label, "preferred");
			if($pb_force_preferred || (bool)$vb_preferred || is_null($vb_preferred)) {
				$vb_preferred = true;
			} else {
				$vb_preferred = false;
			}

			foreach($vo_label->children() as $vo_field) {
				$va_label_values[$vo_field->getName()] = (string) $vo_field;
			}
			$va_existing_labels = $vb_preferred ? $t_instance->getPreferredLabels(array($vn_locale_id)) : $t_instance->getNonPreferredLabels(array($vn_locale_id));
			if(
				is_array($va_existing_labels) &&
				(sizeof($va_existing_labels) > 0) &&
				($vn_label_id = $va_existing_labels[(int)$t_instance->getPrimaryKey()][(int)$vn_locale_id][0]['label_id'])
			) {
				$vn_label_id = $t_instance->editLabel($vn_label_id, $va_label_values, $vn_locale_id, null, $vb_preferred);
			} else {
				$vn_label_id = $t_instance->addLabel($va_label_values, $vn_locale_id, false, $vb_preferred);
			}

			unset($va_old_label_ids[$vn_label_id]);
		}

		// remove all old labels that are not present in the XML!
		foreach($va_old_label_ids as $vn_label_id => $_) {
			$t_instance->removeLabel($vn_label_id);
		}

		return true;
	}
	# --------------------------------------------------
	public function performPreInstallTasks() {
		$o_config = Configuration::load();
		CompositeCache::flush(); // avoid stale cache

		// create tmp dir
		if (!file_exists($o_config->get('taskqueue_tmp_directory'))) {
			if (!self::createDirectoryPath($o_config->get('taskqueue_tmp_directory'))) {
				$this->addError("Couldn't create tmp directory at ".$o_config->get('taskqueue_tmp_directory'));
				return false;
			}
		} else {
			// if already exists then remove all contents to avoid stale cache
			caRemoveDirectory($o_config->get('taskqueue_tmp_directory'), false);
		}

		// Create media directories
		$o_media_volumes = new MediaVolumes();
		$va_media_volumes = $o_media_volumes->getAllVolumeInformation();

		$vs_base_dir = $o_config->get('ca_base_dir');
		foreach($va_media_volumes as $vs_label => $va_volume_info) {
			if (preg_match('!^'.$vs_base_dir.'!', $va_volume_info['absolutePath'])) {
				if (!self::createDirectoryPath($va_volume_info['absolutePath'])) {
					$this->addError("Couldn't create directory for media volume {$vs_label}");
					return false;
				}
			}
		}

		if ($o_config->get('search_engine_plugin') == 'ElasticSearch') {
			$o_es = new WLPlugSearchEngineElasticSearch();
			$o_es->truncateIndex();
		}

		return true;
	}
	# --------------------------------------------------
	public function performPostInstallTasks() {
		$o_config = Configuration::load();
		if ($o_config->get('search_engine_plugin') == 'ElasticSearch') {
			$o_si = new SearchIndexer();
			$o_si->reindex(null, array('showProgress' => false, 'interactiveProgressDisplay' => false));
			CompositeCache::flush();
		}
	}
	# --------------------------------------------------
	/**
	 * Loads CollectiveAccess schema into an empty database
	 *
	 * @param callable $f_callback Function to be called for each SQL statement in the schema. Function is passed four parameters: the SQL code of the statement, the table name, the number of the table being loaded and the total number of tables.
	 * @return boolean Returns true on success, false if an error occurred
	 */
	public function loadSchema($f_callback=null) {

		$vo_config = Configuration::load();
		$vo_dm = Datamodel::load();
		if (defined('__CA_ALLOW_INSTALLER_TO_OVERWRITE_EXISTING_INSTALLS__') && __CA_ALLOW_INSTALLER_TO_OVERWRITE_EXISTING_INSTALLS__ && ($this->opb_overwrite)) {
			$this->opo_db->query('DROP DATABASE IF EXISTS `'.__CA_DB_DATABASE__.'`');
			$this->opo_db->query('CREATE DATABASE `'.__CA_DB_DATABASE__.'`');
			$this->opo_db->query('USE `'.__CA_DB_DATABASE__.'`');
		}

		$va_ca_tables = $vo_dm->getTableNames();

		$qr_tables = $this->opo_db->query("SHOW TABLES");

		while($qr_tables->nextRow()) {
			$vs_table = $qr_tables->getFieldAtIndex(0);
			if (in_array($vs_table, $va_ca_tables)) {
				$this->addError("Table ".$vs_table." already exists; have you already installed CollectiveAccess?");
				return false;
			}
		}

		// load schema

		if (!($vs_schema = file_get_contents(__CA_BASE_DIR__."/install/inc/schema_mysql.sql"))) {
			$this->addError("Could not open schema definition file");
			return false;
		}
		$va_schema_statements = explode(';', $vs_schema);

		$vn_num_tables = 0;
		foreach($va_schema_statements as $vs_statement) {
			if (!trim($vs_statement)) { continue; }
			if (preg_match('!create table!i', $vs_statement)) {
				$vn_num_tables++;
			}
		}

		$vn_i = 0;
		foreach($va_schema_statements as $vs_statement) {
			if (!trim($vs_statement)) { continue; }

			if (is_callable($f_callback) && preg_match('!create[ ]+table[ ]+([A-Za-z0-9_]+)!i', $vs_statement, $va_matches)) {
				$vn_i++;
				if (file_exists(__CA_MODELS_DIR__.'/'.$va_matches[1].'.php')) {
					include_once(__CA_MODELS_DIR__.'/'.$va_matches[1].'.php');
					$vs_table = BaseModel::$s_ca_models_definitions[$va_matches[1]]['NAME_PLURAL'];
				} else {
					$vs_table = $va_matches[1];
				}
				$f_callback($vs_statement, $vs_table, $vn_i, $vn_num_tables);
			}
			$this->opo_db->query($vs_statement);
			if ($this->opo_db->numErrors()) {
				$this->addError("Error while loading the database schema: ".join("; ",$this->opo_db->getErrors()));
				return false;
			}
		}
	}
	# --------------------------------------------------
	# PROFILE CONTENT PROCESSING
	# --------------------------------------------------
	public function processLocales() {
		require_once(__CA_MODELS_DIR__."/ca_locales.php");

		$t_locale = new ca_locales();
		$t_locale->setMode(ACCESS_WRITE);
		// Find any existing locales
		$va_locales = $t_locale->getLocaleList(array('index_by_code' => true));
		foreach($va_locales as $vs_code => $va_locale) {
			$this->opa_locales[$vs_code] = $va_locale['locale_id'];
		}
		if($this->ops_base_name) {
			$va_locales = array();
			foreach($this->opo_profile->locales->children() as $vo_locale) {
				$va_locales[] = $vo_locale;
			}
			foreach($this->opo_base->locales->children() as $vo_locale) {
				$va_locales[] = $vo_locale;
			}
		} else {
			$va_locales = $this->opo_profile->locales->children();
		}

		foreach($va_locales as $vo_locale) {
			$t_locale->clear();
			$vs_language = self::getAttribute($vo_locale, "lang");
			$vs_dialect = self::getAttribute($vo_locale, "dialect");
			$vs_country = self::getAttribute($vo_locale, "country");
			$vb_dont_use_for_cataloguing = self::getAttribute($vo_locale, "dontUseForCataloguing");

			if(isset($this->opa_locales[$vs_language."_".$vs_country]) && ($vn_locale_id = $this->opa_locales[$vs_language."_".$vs_country])) { // don't insert duplicate locales
				$t_locale->load($vn_locale_id); // load locale so that we can 'overwrite' any existing attributes/fields
			}

			$t_locale->set('name', (string)$vo_locale);
			$t_locale->set('country', $vs_country);
			$t_locale->set('language', $vs_language);
			if($vs_dialect) $t_locale->set('dialect', $vs_dialect);
			$t_locale->set('dont_use_for_cataloguing', (bool)$vb_dont_use_for_cataloguing);

			($t_locale->getPrimaryKey() > 0) ? $t_locale->update() : $t_locale->insert();

			if ($t_locale->numErrors()) {
				$this->addError("There was an error while inserting locale {$vs_language}_{$vs_country}: ".join(" ",$t_locale->getErrors()));
			}

			$this->opa_locales[$vs_language."_".$vs_country] = $t_locale->getPrimaryKey();
		}

		$va_locales = $t_locale->getAppConfig()->getList('locale_defaults');
		$vn_locale_id = $t_locale->localeCodeToID($va_locales[0]);

		if(!$vn_locale_id) {
			throw new Exception("The locale default is set to a non-existing locale. Try adding '". $va_locales[0] . "' to your profile.");
		}

		return true;
	}
	# --------------------------------------------------
	public function processLists($f_callback=null) {
		require_once(__CA_MODELS_DIR__."/ca_lists.php");
		require_once(__CA_MODELS_DIR__."/ca_list_items.php");

		if($this->ops_base_name) { // "merge" profile and its base
			$va_lists = array();
			foreach($this->opo_base->lists->children() as $vo_list) {
				$va_lists[self::getAttribute($vo_list, "code")] = $vo_list;
			}
			foreach($this->opo_profile->lists->children() as $vo_list) {
				$va_lists[self::getAttribute($vo_list, "code")] = $vo_list;
			}
		} else {
			$va_lists = $this->opo_profile->lists->children();
		}

		$vn_i = 0;
		$vn_num_lists = sizeof($va_lists);
		foreach($va_lists as $vo_list) {
			$vs_list_code = self::getAttribute($vo_list, "code");
			if(!($t_list = ca_lists::find(array('list_code' => $vs_list_code), array('returnAs' => 'firstModelInstance')))) {
				$t_list = new ca_lists();
			}
			$t_list->setMode(ACCESS_WRITE);

			if(self::getAttribute($vo_list, "deleted") && $t_list->getPrimaryKey()) {
				$t_list->delete(true);
				continue;
			}

			$vb_hierarchical = self::getAttribute($vo_list, "hierarchical");
			$vb_system = self::getAttribute($vo_list, "system");
			$vb_voc = self::getAttribute($vo_list, "vocabulary");
			$vn_def_sort = self::getAttribute($vo_list, "defaultSort");

			if (is_callable($f_callback)) {
				$vn_i++;

				$f_callback($vs_list_code, $vn_i, $vn_num_lists);
			}

			$t_list->set("list_code",$vs_list_code);
			$t_list->set("is_system_list",intval($vb_system));
			$t_list->set("is_hierarchical",$vb_hierarchical);
			$t_list->set("use_as_vocabulary",$vb_voc);
			if($vn_def_sort) $t_list->set("default_sort",(int)$vn_def_sort);
			if($t_list->getPrimaryKey()) {
				$t_list->update();
			} else {
				$t_list->insert();
			}

			if ($t_list->numErrors()) {
				$this->addError("There was an error while inserting list {$vs_list_code}: ".join(" ",$t_list->getErrors()));
			} else {
				self::addLabelsFromXMLElement($t_list, $vo_list->labels, $this->opa_locales);
				if ($t_list->numErrors()) {
					$this->addError("There was an error while inserting list label for {$vs_list_code}: ".join(" ",$t_list->getErrors()));
				}
				if($vo_list->items) {
					if(!$this->processListItems($t_list, $vo_list->items, null)) {
						return false;
					}
				}
			}
		}

		return true;
	}
	# --------------------------------------------------
	/**
	 * @param $t_list ca_lists
	 * @param $po_items SimpleXMLElement
	 * @param $pn_parent_id int
	 * @return bool
	 */
	protected  function processListItems($t_list, $po_items, $pn_parent_id) {
		foreach($po_items->children() as $vo_item) {
			$vs_item_value = self::getAttribute($vo_item, "value");
			$vs_item_idno = self::getAttribute($vo_item, "idno");
			$vs_type = self::getAttribute($vo_item, "type");
			$vs_status = self::getAttribute($vo_item, "status");
			$vs_access = self::getAttribute($vo_item, "access");
			$vs_rank = self::getAttribute($vo_item, "rank");
			$vn_enabled = self::getAttribute($vo_item, "enabled");
			$vn_default = self::getAttribute($vo_item, "default");

			if (!isset($vs_item_value) || !strlen(trim($vs_item_value))) {
				$vs_item_value = $vs_item_idno;
			}

			$vn_type_id = null;
			if ($vs_type) {
				$vn_type_id = $t_list->getItemIDFromList('list_item_types', $vs_type);
			}

			if (!isset($vs_status)) { $vs_status = 0; }
			if (!isset($vs_access)) { $vs_access = 0; }
			if (!isset($vs_rank)) { $vs_rank = 0; }

			if($vn_item_id = caGetListItemID($t_list->get('list_code'), $vs_item_idno, array('dontCache' => true))) {
				if(self::getAttribute($vo_item, "deleted")) {
					$t_item = new ca_list_items($vn_item_id);
					$t_item->setMode(ACCESS_WRITE);
					$t_item->delete();
					continue;
				}
				$t_item = $t_list->editItem($vn_item_id, $vs_item_value, $vn_enabled, $vn_default, $pn_parent_id, $vs_item_idno, '', (int)$vs_status, (int)$vs_access, (int)$vs_rank);
			} else {
				$t_item = $t_list->addItem($vs_item_value, $vn_enabled, $vn_default, $pn_parent_id, $vn_type_id, $vs_item_idno, '', (int)$vs_status, (int)$vs_access, (int)$vs_rank);
			}

			if (($t_list->numErrors() > 0) || !is_object($t_item)) {
				$this->addError("There was an error while inserting list item {$vs_item_idno}: ".join(" ",$t_list->getErrors()));
				return false;
			} else {
				$t_item->setMode(ACCESS_WRITE);
				if($vo_item->settings) {
					$this->_processSettings($t_item, $vo_item->settings);
					$t_item->update();
					if ($t_item->numErrors()) {
						$this->addError("There was an error while adding a setting for list item with idno {$vs_item_idno}: ".join(" ",$t_item->getErrors()));
					}
				}
				self::addLabelsFromXMLElement($t_item, $vo_item->labels, $this->opa_locales);
				if ($t_item->numErrors()) {
					$this->addError("There was an error while inserting list item label for {$vs_item_idno}: ".join(" ",$t_item->getErrors()));
				}
			}

			if (isset($vo_item->items)) {
				if(!$this->processListItems($t_list, $vo_item->items, $t_item->getPrimaryKey())) {
					return false;
				}
			}
		}

		return true;
	}
	# --------------------------------------------------
	public function processMetadataElements() {
		require_once(__CA_MODELS_DIR__."/ca_lists.php");
		require_once(__CA_MODELS_DIR__."/ca_list_items.php");
		require_once(__CA_MODELS_DIR__."/ca_relationship_types.php");

		$vo_dm = Datamodel::load();
		$t_rel_types = new ca_relationship_types();
		$t_list = new ca_lists();

		$va_elements = array();
		if($this->ops_base_name) { // "merge" profile and its base
			foreach($this->opo_base->elementSets->children() as $vo_element) {
				$va_elements[self::getAttribute($vo_element, "code")] = $vo_element;
			}
			foreach($this->opo_profile->elementSets->children() as $vo_element) {
				$va_elements[self::getAttribute($vo_element, "code")] = $vo_element;
			}
		} else {
			foreach($this->opo_profile->elementSets->children() as $vo_element) {
				$va_elements[self::getAttribute($vo_element, "code")] = $vo_element;
			}
		}

		foreach($va_elements as $vs_element_code => $vo_element) {
			if($vn_element_id = $this->processMetadataElement($vo_element, null)) {
				// nuke previous restrictions. there shouldn't be any if we're installing from scratch.
				// if we're updating, we expect the list of restrictions to include all restrictions!
				if(sizeof($vo_element->typeRestrictions->children())) {
					$this->opo_db->query('DELETE FROM ca_metadata_type_restrictions WHERE element_id=?', $vn_element_id);
				}

				// handle restrictions
				foreach($vo_element->typeRestrictions->children() as $vo_restriction) {
					$vs_restriction_code = self::getAttribute($vo_restriction, "code");

					if (!($vn_table_num = $vo_dm->getTableNum((string)$vo_restriction->table))) {
						$this->addError("Invalid table specified for restriction $vs_restriction_code in element $vs_element_code");
						return false;
					}
					$t_instance = $vo_dm->getTableInstance((string)$vo_restriction->table);
					$vn_type_id = null;
					$vs_type = trim((string)$vo_restriction->type);

					// is this restriction further restricted on a specific type? -> get real id from code
					if (strlen($vs_type)>0) {
						// interstitial with type restriction -> code is relationship type code
						if($t_instance instanceof BaseRelationshipModel) {
							$vn_type_id = $t_rel_types->getRelationshipTypeID($t_instance->tableName(),$vs_type);
						} else { // "normal" type restriction -> code is from actual type list
							$vs_type_list_name = $t_instance->getFieldListCode($t_instance->getTypeFieldName());
							$vn_type_id = $t_list->getItemIDFromList($vs_type_list_name,$vs_type);
						}
					}

					// add restriction
					$t_restriction = new ca_metadata_type_restrictions();
					$t_restriction->setMode(ACCESS_WRITE);
					$t_restriction->set('table_num', $vn_table_num);
					$t_restriction->set('include_subtypes', (bool)$vo_restriction->includeSubtypes ? 1 : 0);
					$t_restriction->set('type_id', $vn_type_id);
					$t_restriction->set('element_id', $vn_element_id);

					$this->_processSettings($t_restriction, $vo_restriction->settings);
					$t_restriction->insert();

					if ($t_restriction->numErrors()) {
						$this->addError("There was an error while inserting type restriction {$vs_restriction_code} for metadata element {$vs_element_code}: ".join("; ",$t_restriction->getErrors()));
					}
				}
			}
		}
		return true;
	}
	# --------------------------------------------------
	private function processMetadataElement($po_element, $pn_parent_id) {
		require_once(__CA_MODELS_DIR__."/ca_metadata_elements.php");
		require_once(__CA_MODELS_DIR__."/ca_lists.php");

		$vs_element_code = self::getAttribute($po_element, "code");

		// try to load element by code for potential update. codes are unique, globally
		if(!($t_md_element = ca_metadata_elements::getInstance($vs_element_code))) {
			$t_md_element = new ca_metadata_elements();
		}

		$t_md_element->setMode(ACCESS_WRITE);

		if(self::getAttribute($po_element, 'deleted') && $t_md_element->getPrimaryKey()) {
			$t_md_element->delete(true, array('hard' => true));
			return false; // we don't want the postprocessing to kick in. our work here is done.
		}

		if (($vn_datatype = ca_metadata_elements::getAttributeTypeCode(self::getAttribute($po_element, "datatype"))) === false) {
			return false; // should not happen due to XSD restrictions, but just in case
		}

		$t_lists = new ca_lists();

		$t_md_element->set('element_code', $vs_element_code);
		$t_md_element->set('parent_id', $pn_parent_id);
		$t_md_element->set('documentation_url',(string)$po_element->documentationUrl);
		$t_md_element->set('datatype', $vn_datatype);

		$vs_list = self::getAttribute($po_element, "list");

		if (isset($vs_list) && $vs_list && $t_lists->load(array('list_code' => $vs_list))) {
			$vn_list_id = $t_lists->getPrimaryKey();
		} else {
			$vn_list_id = null;
		}
		$t_md_element->set('list_id', $vn_list_id);
		$this->_processSettings($t_md_element, $po_element->settings);

		if($t_md_element->getPrimaryKey()) {
			$t_md_element->update();
		}else{
			$t_md_element->insert();
		}

		if ($t_md_element->numErrors()) {
			$this->addError("There was an error while inserting metadata element {$vs_element_code}: ".join(" ",$t_md_element->getErrors()));
			return false;
		}

		$vn_element_id = $t_md_element->getPrimaryKey();

		// add element labels
		self::addLabelsFromXMLElement($t_md_element, $po_element->labels, $this->opa_locales);

		if ($po_element->elements) {
			foreach($po_element->elements->children() as $vo_child) {
				$this->processMetadataElement($vo_child, $vn_element_id);
			}
		}

		return $vn_element_id;
	}
	# --------------------------------------------------
	public function processMetadataDictionary() {
		require_once(__CA_MODELS_DIR__.'/ca_metadata_dictionary_entries.php');

		if(!$this->opo_profile->metadataDictionary) { return true; } // no dict specified. it's optional, so don't barf

		// dictionary entries don't have a code or any other attribute that could be used for
		// identification so we won't support setting them in a base profile, for now ...

		foreach($this->opo_profile->metadataDictionary->children() as $vo_entry) {
			$vs_field = self::getAttribute($vo_entry, "bundle");

			if(strlen($vs_field)<1) {
				$this->addError("No bundle specified in a metadata dictionary entry. Skipping row.");
				continue;
			}

			// insert dictionary entry
			$t_entry = new ca_metadata_dictionary_entries();
			$t_entry->setMode(ACCESS_WRITE);
			$t_entry->set('bundle_name', $vs_field);
			$this->_processSettings($t_entry, $vo_entry->settings);

			$t_entry->insert();

			if($t_entry->numErrors() > 0 || !($t_entry->getPrimaryKey()>0)) {
				$this->addError("There were errors while adding dictionary entry: " . join(';', $t_entry->getErrors()));
				return false;
			}

			if($vo_entry->rules) {
				foreach($vo_entry->rules->children() as $vo_rule) {
					$vs_code = self::getAttribute($vo_rule, "code");
					$vs_level = self::getAttribute($vo_rule, "level");

					$t_rule = new ca_metadata_dictionary_rules();
					$t_rule->setMode(ACCESS_WRITE);
					$t_rule->set('entry_id', $t_entry->getPrimaryKey());
					$t_rule->set('rule_code', $vs_code);
					$t_rule->set('rule_level', $vs_level);
					$t_rule->set('expression', (string) $vo_rule->expression);
					$this->_processSettings($t_rule, $vo_rule->settings);

					$t_rule->insert();
					if ($t_rule->numErrors()) {
						$this->addError("There were errors while adding dictionary rule: " . join(';', $t_rule->getErrors()));
						continue;
					}
				}
			}
		}

		return true;
	}
	# --------------------------------------------------
	public function processUserInterfaces() {
		require_once(__CA_MODELS_DIR__."/ca_editor_uis.php");
		require_once(__CA_MODELS_DIR__."/ca_editor_ui_screens.php");
		require_once(__CA_MODELS_DIR__."/ca_lists.php");
		require_once(__CA_MODELS_DIR__."/ca_list_items.php");
		require_once(__CA_MODELS_DIR__."/ca_relationship_types.php");

		$vo_dm = Datamodel::load();

		$t_list = new ca_lists();
		$t_rel_types = new ca_relationship_types();
		$va_uis = array();
		if($this->ops_base_name) { // "merge" profile and its base
			foreach($this->opo_base->userInterfaces->children() as $vo_ui) {
				$va_uis[self::getAttribute($vo_ui, "code")] = $vo_ui;
			}
			foreach($this->opo_profile->userInterfaces->children() as $vo_ui) {
				$va_uis[self::getAttribute($vo_ui, "code")] = $vo_ui;
			}
		} else {
			foreach($this->opo_profile->userInterfaces->children() as $vo_ui) {
				$va_uis[self::getAttribute($vo_ui, "code")] = $vo_ui;
			}
		}

		foreach($va_uis as $vs_ui_code => $vo_ui) {
			$vs_type = self::getAttribute($vo_ui, "type");
			if (!($vn_type = $vo_dm->getTableNum($vs_type))) {
				$this->addError("Invalid type {$vs_type} for UI code {$vs_ui_code}");
				return false;
			}

			// model instance of UI type
			$t_instance = $vo_dm->getInstanceByTableNum($vn_type);

			// create ui row
			if(!($t_ui = ca_editor_uis::find(array('editor_code' => $vs_ui_code, 'editor_type' =>  $vn_type), array('returnAs' => 'firstModelInstance')))) {
				$t_ui = new ca_editor_uis();
			}
			$t_ui->setMode(ACCESS_WRITE);

			if(self::getAttribute($vo_ui, 'deleted') && $t_ui->getPrimaryKey()) {
				$t_ui->delete(true, array('hard' => true));
				continue;
			}

			$t_ui->set('user_id', null);
			$t_ui->set('is_system_ui', 1);
			$t_ui->set('editor_code', $vs_ui_code);
			$t_ui->set('editor_type', $vn_type);

			if($t_ui->getPrimaryKey()) {
				$t_ui->update();
			}else{
				$t_ui->insert();
			}

			if ($t_ui->numErrors()) {
				$this->addError("Errors inserting UI {$vs_ui_code}: ".join("; ",$t_ui->getErrors()));
				return false;
			}

			$vn_ui_id = $t_ui->getPrimaryKey();

			self::addLabelsFromXMLElement($t_ui, $vo_ui->labels, $this->opa_locales);

			// create ui type restrictions
			if($vo_ui->typeRestrictions) {
				// nuke previous restrictions. there shouldn't be any if we're installing from scratch.
				// if we're updating, we expect the list of restrictions to include all restrictions!
				if(sizeof($vo_ui->typeRestrictions->children())) {
					$this->opo_db->query('DELETE FROM ca_editor_ui_type_restrictions WHERE ui_id=?', $vn_ui_id);
				}

				foreach($vo_ui->typeRestrictions->children() as $vo_restriction) {
					$vs_restriction_type = self::getAttribute($vo_restriction, "type");

					if (strlen($vs_restriction_type)>0) {
						// interstitial with type restriction -> code is relationship type code
						if($t_instance instanceof BaseRelationshipModel) {
							$vn_type_id = $t_rel_types->getRelationshipTypeID($t_instance->tableName(),$vs_restriction_type);
						} else { // "normal" type restriction -> code is from actual type list
							$vs_type_list_name = $t_instance->getFieldListCode($t_instance->getTypeFieldName());
							$vn_type_id = $t_list->getItemIDFromList($vs_type_list_name,$vs_restriction_type);
						}

						if($vn_type_id) {
							$t_ui->addTypeRestriction($vn_type_id);
						}
					}
				}
			}

			// create ui screens
			foreach($vo_ui->screens->children() as $vo_screen) {
				$vs_screen_idno = self::getAttribute($vo_screen, "idno");
				$vn_is_default = self::getAttribute($vo_screen, "default");

				$t_ui_screens = ca_editor_ui_screens::find(array(
					'idno' => $vs_screen_idno,
					'ui_id' => $vn_ui_id
				), array('returnAs' => 'firstModelInstance'));

				$t_ui_screens = $t_ui_screens ? $t_ui_screens : new ca_editor_ui_screens();
				$t_ui_screens->setMode(ACCESS_WRITE);

				if(self::getAttribute($vo_screen, 'deleted') && $t_ui_screens->getPrimaryKey()) {
					$t_ui_screens->delete(true, array('hard' => true));
					continue;
				}

				$t_ui_screens->set('idno',$vs_screen_idno);
				$t_ui_screens->set('ui_id', $vn_ui_id);
				$t_ui_screens->set('is_default', $vn_is_default);

				if($t_ui_screens->getPrimaryKey()) {
					$t_ui_screens->update();
				}else{
					$t_ui_screens->set('parent_id', null);
					$t_ui_screens->insert();
				}

				if ($t_ui_screens->numErrors()) {
					$this->addError("Errors inserting UI screen {$vs_screen_idno} for UI {$vs_ui_code}: ".join("; ",$t_ui_screens->getErrors()));
					return false;
				}

				self::addLabelsFromXMLElement($t_ui_screens, $vo_screen->labels, $this->opa_locales);

				$va_available_bundles = $t_ui_screens->getAvailableBundles(null,array('dontCache' => true));

				// nuke previous placements. there shouldn't be any if we're installing from scratch.
				// if we're updating, we expect the list of placements to include all of them!
				if(sizeof($vo_screen->bundlePlacements->children())) {
					$this->opo_db->query('DELETE FROM ca_editor_ui_bundle_placements WHERE screen_id=?', $t_ui_screens->getPrimaryKey());
				}

				// create ui bundle placements
				foreach($vo_screen->bundlePlacements->children() as $vo_placement) {
					$vs_placement_code = self::getAttribute($vo_placement, "code");
					$vs_bundle = trim((string)$vo_placement->bundle);

					$va_settings = $this->_processSettings(null, $vo_placement->settings);

					$t_ui_screens->addPlacement($vs_bundle, $vs_placement_code, $va_settings, null, array('additional_settings' => $va_available_bundles[$vs_bundle]['settings']));
				}

				// create ui screen type restrictions
				if($vo_screen->typeRestrictions) {
					// nuke previous restrictions. there shouldn't be any if we're installing from scratch.
					// if we're updating, we expect the list of restrictions to include all of them!
					if(sizeof($vo_screen->typeRestrictions->children())) {
						$this->opo_db->query('DELETE FROM ca_editor_ui_screen_type_restrictions WHERE screen_id=?', $t_ui_screens->getPrimaryKey());
					}

					foreach($vo_screen->typeRestrictions->children() as $vo_restriction) {
						$vs_restriction_type = self::getAttribute($vo_restriction, "type");

						if (strlen($vs_restriction_type)>0) {
							// interstitial with type restriction -> code is relationship type code
							if($t_instance instanceof BaseRelationshipModel) {
								$vn_type_id = $t_rel_types->getRelationshipTypeID($t_instance->tableName(),$vs_restriction_type);
							} else { // "normal" type restriction -> code is from actual type list
								$vs_type_list_name = $t_instance->getFieldListCode($t_instance->getTypeFieldName());
								$vn_type_id = $t_list->getItemIDFromList($vs_type_list_name,$vs_restriction_type);
							}

							if($vn_type_id) {
								$t_ui_screens->addTypeRestriction($vn_type_id);
							}
						}
					}
				}
			}

			// set user and group access
			if($vo_ui->userAccess) {
				$t_user = new ca_users();
				$va_ui_users = array();
				foreach($vo_ui->userAccess->children() as $vo_permission) {
					$vs_user = trim((string)self::getAttribute($vo_permission, "user"));
					$vn_access = $this->_convertUserGroupAccessStringToInt(self::getAttribute($vo_permission, 'access'));

					if($vn_access && $t_user->load(array('user_name' => $vs_user))) {
						$va_ui_users[$t_user->getUserID()] = $vn_access;
					} else {
						$this->addError("User name or access value invalid for UI {$vs_ui_code} (permission item with user name '{$vs_user}')");
					}
				}

				if(sizeof($va_ui_users)>0) {
					$t_ui->addUsers($va_ui_users);
				}
			}

			if($vo_ui->groupAccess) {
				$t_group = new ca_user_groups();
				$va_ui_groups = array();
				foreach($vo_ui->groupAccess->children() as $vo_permission) {
					$vs_group = trim((string)self::getAttribute($vo_permission, "group"));
					$vn_access = $this->_convertUserGroupAccessStringToInt(self::getAttribute($vo_permission, 'access'));

					if($vn_access && $t_group->load(array('code' => $vs_group))) {
						$va_ui_groups[$t_group->getPrimaryKey()] = $vn_access;
					} else {
						$this->addError("Group code or access value invalid for UI {$vs_ui_code} (permission item with group code '{$vs_group}')");
					}
				}

				if(sizeof($va_ui_groups)>0) {
					$t_ui->addUserGroups($va_ui_groups);
				}
			}
		}
		return true;
	}
	# --------------------------------------------------
	public function processRelationshipTypes() {
		require_once(__CA_MODELS_DIR__."/ca_relationship_types.php");

		$va_rel_tables = array();
		if($this->ops_base_name) { // "merge" profile and its base
			foreach($this->opo_base->relationshipTypes->children() as $vo_rel_table) {
				$va_rel_tables[self::getAttribute($vo_rel_table, "name")] = $vo_rel_table;
			}
			foreach($this->opo_profile->relationshipTypes->children() as $vo_rel_table) {
				$va_rel_tables[self::getAttribute($vo_rel_table, "name")] = $vo_rel_table;
			}
		} else {
			foreach($this->opo_profile->relationshipTypes->children() as $vo_rel_table) {
				$va_rel_tables[self::getAttribute($vo_rel_table, "name")] = $vo_rel_table;
			}
		}

		$qr_lists = $this->opo_db->query("SELECT * FROM ca_lists");

		$va_list_names = array();
		$va_list_item_ids = array();
		while($qr_lists->nextRow()) {
			$va_list_names[$qr_lists->get('list_id')] = $qr_lists->get('list_code');
		}

		// get list items
		$qr_list_item_result = $this->opo_db->query("SELECT * FROM ca_list_items cli INNER JOIN ca_list_item_labels AS clil ON clil.item_id = cli.item_id");
		while($qr_list_item_result->nextRow()) {
			$vs_type_code = $va_list_names[$qr_list_item_result->get('list_id')];
			$va_list_item_ids[$vs_type_code][$qr_list_item_result->get('item_value')] = $qr_list_item_result->get('item_id');
		}

		$vo_dm = Datamodel::load();


		foreach($va_rel_tables as $vs_table => $vo_rel_table) {
			$vn_table_num = $vo_dm->getTableNum($vs_table);

			$t_rel_table = $vo_dm->getTableInstance($vs_table);

			if (!method_exists($t_rel_table, 'getLeftTableName')) {
				continue;
			}
			$vs_left_table = $t_rel_table->getLeftTableName();
			$vs_right_table = $t_rel_table->getRightTableName();


			$vs_root_type_code = 'root_for_'.$vn_table_num;

			/** @var ca_relationship_types $t_rel_type */
			$t_rel_type = ca_relationship_types::find(
				array('type_code' => $vs_root_type_code, 'table_num' => $vn_table_num, 'parent_id' => null),
				array('returnAs' => 'firstModelInstance')
			);

			$t_rel_type = $t_rel_type ? $t_rel_type : new ca_relationship_types();
			$t_rel_type->setMode(ACCESS_WRITE);
			// create relationship type root if necessary
			$t_rel_type->set('parent_id', null);
			$t_rel_type->set('type_code', $vs_root_type_code);
			$t_rel_type->set('sub_type_left_id', null);
			$t_rel_type->set('sub_type_right_id', null);
			$t_rel_type->set('table_num', $vn_table_num);
			$t_rel_type->set('rank', 10);
			$t_rel_type->set('is_default', 0);
			if(!$t_rel_type->getPrimaryKey()) { // do nothing if find() found that very root
				$t_rel_type->insert();
			}

			if ($t_rel_type->numErrors()) {
				$this->addError("Errors inserting relationship root for {$vs_table}: ".join("; ",$t_rel_type->getErrors()));
				return false;
			}

			$vn_parent_id = $t_rel_type->getPrimaryKey();

			$this->processRelationshipTypesForTable($vo_rel_table->types, $vn_table_num, $vs_left_table, $vs_right_table, $vn_parent_id, $va_list_item_ids);
		}
		return true;
	}
	# --------------------------------------------------
	private function processRelationshipTypesForTable($po_relationship_types, $pn_table_num, $ps_left_table, $ps_right_table, $pn_parent_id, $pa_list_item_ids) {
		$o_dm = Datamodel::load();

		// nuke caches to be safe
		ca_relationship_types::$s_relationship_type_id_cache = array();
		ca_relationship_types::$s_relationship_type_table_cache = array();
		ca_relationship_types::$s_relationship_type_id_to_code_cache = array();

		$t_rel_type = new ca_relationship_types();
		$t_rel_type->setMode(ACCESS_WRITE);

		$vn_rank_default = (int)$t_rel_type->getFieldInfo('rank', 'DEFAULT');
		foreach($po_relationship_types->children() as $vo_type) {
			$vs_type_code = self::getAttribute($vo_type, "code");
			$vn_default = self::getAttribute($vo_type, "default");
			$vn_rank = (int)self::getAttribute($vo_type, "rank");

			$t_rel_type = ca_relationship_types::find(
				array('type_code' => $vs_type_code, 'table_num' => $pn_table_num, 'parent_id' => $pn_parent_id),
				array('returnAs' => 'firstModelInstance')
			);

			$t_rel_type = $t_rel_type ? $t_rel_type : new ca_relationship_types();
			$t_rel_type->setMode(ACCESS_WRITE);

			if(self::getAttribute($vo_type, "deleted") && $t_rel_type->getPrimaryKey()) {
				$t_rel_type->delete(true);
				continue;
			}

			$t_rel_type->set('table_num', $pn_table_num);
			$t_rel_type->set('type_code', $vs_type_code);
			$t_rel_type->set('parent_id', $pn_parent_id);
			$t_rel_type->set('is_default', $vn_default ? 1 : 0);

			if ($vn_rank > 0) {
				$t_rel_type->set("rank", $vn_rank);
			} else {
				$t_rel_type->set("rank", $vn_rank_default);
			}

			if($t_rel_type->getPrimaryKey()) {
				$t_rel_type->update();
			} else {
				$t_rel_type->insert();
			}

			if (trim($vs_left_subtype_code = (string) $vo_type->subTypeLeft)) {
				$t_obj = $o_dm->getTableInstance($ps_left_table);
				$vs_list_code = $t_obj->getFieldListCode($t_obj->getTypeFieldName());

				if (isset($pa_list_item_ids[$vs_list_code][$vs_left_subtype_code])) {
					$t_rel_type->set('sub_type_left_id', $pa_list_item_ids[$vs_list_code][$vs_left_subtype_code]);
					$t_rel_type->update();
				}
			}
			if (trim($vs_right_subtype_code = (string) $vo_type->subTypeRight)) {
				$t_obj = $o_dm->getTableInstance($ps_right_table);
				$vs_list_code = $t_obj->getFieldListCode($t_obj->getTypeFieldName());
				if (isset($pa_list_item_ids[$vs_list_code][$vs_right_subtype_code])) {
					$t_rel_type->set('sub_type_right_id', $pa_list_item_ids[$vs_list_code][$vs_right_subtype_code]);
					$t_rel_type->update();
				}
			}

			if ($t_rel_type->numErrors()) {
				$this->addError("Errors inserting relationship {$vs_type_code}: ".join("; ",$t_rel_type->getErrors()));
				return false;
			}


			self::addLabelsFromXMLElement($t_rel_type, $vo_type->labels, $this->opa_locales);

			if ($vo_type->types) {
				$this->processRelationshipTypesForTable($vo_type->types, $pn_table_num, $ps_left_table, $ps_right_table, $t_rel_type->getPrimaryKey(), $pa_list_item_ids);
			}
		}
	}
	# --------------------------------------------------
	public function processRoles() {
		require_once(__CA_MODELS_DIR__."/ca_user_roles.php");
		$va_roles = array();
		if($this->ops_base_name) { // "merge" profile and its base

			if($this->opo_base->roles) {
				foreach($this->opo_base->roles->children() as $vo_role) {
					$va_roles[self::getAttribute($vo_role, "code")] = $vo_role;
				}
			}
			if($this->opo_profile->roles) {
				foreach($this->opo_profile->roles->children() as $vo_role) {
					$va_roles[self::getAttribute($vo_role, "code")] = $vo_role;
				}
			}
		} else {
			if($this->opo_profile->roles) {
				foreach($this->opo_profile->roles->children() as $vo_role) {
					$va_roles[self::getAttribute($vo_role, "code")] = $vo_role;
				}
			}
		}

		foreach($va_roles as $vs_role_code => $vo_role) {
			if(!($t_role = ca_user_roles::find(array('code' => (string)$vs_role_code), array('returnAs' => 'firstModelInstance')))) {
				$t_role = new ca_user_roles();
			}

			$t_role->setMode(ACCESS_WRITE);

			if(self::getAttribute($vo_role, "deleted") && $t_role->getPrimaryKey()) {
				$t_role->delete(true);
				continue;
			}

			$t_role->set('name', trim((string) $vo_role->name));
			$t_role->set('description', trim((string) $vo_role->description));
			$t_role->set('code', $vs_role_code);

			// add actions
			$va_actions = array();
			if($vo_role->actions) {
				foreach($vo_role->actions->children() as $vo_action) {
					$va_actions[] = trim((string) $vo_action);
				}
			}
			$t_role->setRoleActions($va_actions);
			if($t_role->getPrimaryKey()) {
				$t_role->update();
			} else {
				$t_role->insert();
			}

			if ($t_role->numErrors()) {
				$this->addError("Errors inserting access role {$vs_role_code}: ".join("; ",$t_role->getErrors()));
				return false;
			}

			// add bundle level ACL items
			if($vo_role->bundleLevelAccessControl) {
				// nuke old items
				if(sizeof($vo_role->bundleLevelAccessControl->children()) > 0) {
					$t_role->removeAllBundleAccessSettings();
				}

				foreach($vo_role->bundleLevelAccessControl->children() as $vo_permission) {
					$vs_permission_table = self::getAttribute($vo_permission, 'table');
					$vs_permission_bundle = self::getAttribute($vo_permission, 'bundle');
					$vn_permission_access = $this->_convertACLStringToConstant(self::getAttribute($vo_permission, 'access'));

					if(!$t_role->setAccessSettingForBundle($vs_permission_table, $vs_permission_bundle, $vn_permission_access)) {
						$this->addError("Could not add bundle level access control for table '{$vs_permission_table}' and bundle '{$vs_permission_bundle}'. Check the table and bundle names.");
						//return false;
					}
				}
			}

			// add type level ACL items
			if($vo_role->typeLevelAccessControl) {
				// nuke old items
				if(sizeof($vo_role->typeLevelAccessControl->children()) > 0) {
					$t_role->removeAllTypeAccessSettings();
				}

				foreach($vo_role->typeLevelAccessControl->children() as $vo_permission) {
					$vs_permission_table = self::getAttribute($vo_permission, 'table');
					$vs_permission_type = self::getAttribute($vo_permission, 'type');
					$vn_permission_access = $this->_convertACLStringToConstant(self::getAttribute($vo_permission, 'access'));

					if(!$t_role->setAccessSettingForType($vs_permission_table, $vs_permission_type, $vn_permission_access)) {
						$this->addError("Could not add type level access control for table '{$vs_permission_table}' and type '{$vs_permission_type}'. Check the table name and the type code.");
						//return false;
					}
				}
			}

			// add source level ACL items
			if($vo_role->sourceLevelAccessControl) {
				// nuke old items
				if(sizeof($vo_role->sourceLevelAccessControl->children()) > 0) {
					$t_role->removeAllSourceAccessSettings();
				}

				foreach($vo_role->sourceLevelAccessControl->children() as $vo_permission) {
					$vs_permission_table = self::getAttribute($vo_permission, 'table');
					$vs_permission_source = self::getAttribute($vo_permission, 'source');
					$vs_permission_default = self::getAttribute($vo_permission, 'default');
					$vn_permission_access = $this->_convertACLStringToConstant(self::getAttribute($vo_permission, 'access'));

					if(!$t_role->setAccessSettingForSource($vs_permission_table, $vs_permission_source, $vn_permission_access, (bool)$vs_permission_default)) {
						$this->addError("Could not add source level access control for table '{$vs_permission_table}' and source '{$vs_permission_source}'. Check the table name and the source code.");
						//return false;
					}
				}
			}
		}
		return true;
	}
	# --------------------------------------------------
	public function processDisplays() {
		require_once(__CA_MODELS_DIR__."/ca_bundle_displays.php");
		require_once(__CA_MODELS_DIR__."/ca_bundle_display_placements.php");
		require_once(__CA_MODELS_DIR__."/ca_bundle_display_type_restrictions.php");

		$o_config = Configuration::load();

		$vo_dm = Datamodel::load();

		$va_displays = array();
		if($this->ops_base_name) { // "merge" profile and its base
			if($this->opo_base->displays) {
				foreach($this->opo_base->displays->children() as $vo_display) {
					$va_displays[self::getAttribute($vo_display, "code")] = $vo_display;
				}
			}

			if($this->opo_profile->displays) {
				foreach($this->opo_profile->displays->children() as $vo_display) {
					$va_displays[self::getAttribute($vo_display, "code")] = $vo_display;
				}
			}
		} else {
			if($this->opo_profile->displays) {
				foreach($this->opo_profile->displays->children() as $vo_display) {
					$va_displays[self::getAttribute($vo_display, "code")] = $vo_display;
				}
			}
		}

		if(sizeof($va_displays) == 0) { return true; }

		foreach($va_displays as $vo_display) {
			$vs_display_code = self::getAttribute($vo_display, "code");
			$vb_system = self::getAttribute($vo_display, "system");
			$vs_table = self::getAttribute($vo_display, "type");
			$vn_table_num = $vo_dm->getTableNum($vs_table);

			if ($o_config->get($vs_table.'_disable')) { continue; }

			if(!($t_display = ca_bundle_displays::find(array('display_code' => $vs_display_code), array('returnAs' => 'firstModelInstance')))) {
				$t_display = new ca_bundle_displays();
			}

			$t_display->setMode(ACCESS_WRITE);

			if(self::getAttribute($vo_display, "deleted") && $t_display->getPrimaryKey()) {
				$t_display->delete(true);
				continue;
			}

			$t_display->set("display_code", $vs_display_code);
			$t_display->set("is_system", $vb_system);
			$t_display->set("table_num",$vo_dm->getTableNum($vs_table));
			$t_display->set("user_id", 1);		// let administrative user own these

			$this->_processSettings($t_display, $vo_display->settings);

			if($t_display->getPrimaryKey()) {
				$t_display->update();
			} else {
				$t_display->insert();
			}

			if ($t_display->numErrors()) {
				$this->addError("There was an error while inserting display {$vs_display_code}: ".join(" ",$t_display->getErrors()));
			} else {
				self::addLabelsFromXMLElement($t_display, $vo_display->labels, $this->opa_locales);
				if ($t_display->numErrors()) {
					$this->addError("There was an error while inserting display label for {$vs_display_code}: ".join(" ",$t_display->getErrors()));
				}
				if(!$this->processDisplayPlacements($t_display, $vo_display->bundlePlacements, null)) {
					return false;
				}
			}

			if ($vo_display->typeRestrictions) {
				// nuke previous restrictions. there shouldn't be any if we're installing from scratch.
				// if we're updating, we expect the list of restrictions to include all restrictions!
				if(sizeof($vo_display->typeRestrictions->children())) {
					$this->opo_db->query('DELETE FROM ca_bundle_display_type_restrictions WHERE display_id=?', $t_display->getPrimaryKey());
				}

				foreach($vo_display->typeRestrictions->children() as $vo_restriction) {
					$t_list = new ca_lists();
					$t_list_item = new ca_list_items();
					$vs_restriction_code = trim((string)self::getAttribute($vo_restriction, "code"));
					$vs_type = trim((string)self::getAttribute($vo_restriction, "type"));

					$t_instance = $vo_dm->getInstanceByTableNum($vn_table_num);
					$vs_type_list_name = $t_instance->getFieldListCode($t_instance->getTypeFieldName());
					if ($vs_type) {
						$t_list->load(array('list_code' => $vs_type_list_name));
						$t_list_item->load(array('list_id' => $t_list->getPrimaryKey(), 'idno' => $vs_type));
					}
					$vn_type_id = ($vs_type) ? $t_list_item->getPrimaryKey() : null;
					$t_restriction = new ca_bundle_display_type_restrictions();
					$t_restriction->setMode(ACCESS_WRITE);
					$t_restriction->set('table_num', $vn_table_num);
					$t_restriction->set('include_subtypes', (bool)$vo_restriction->includeSubtypes ? 1 : 0);
					$t_restriction->set('type_id', $vn_type_id);
					$t_restriction->set('display_id', $t_display->getPrimaryKey());

					$this->_processSettings($t_restriction, $vo_restriction->settings);
					$t_restriction->insert();

					if ($t_restriction->numErrors()) {
						$this->addError("There was an error while inserting type restriction {$vs_restriction_code} in display {$vs_display_code}: ".join("; ",$t_restriction->getErrors()));
					}
				}
			}

			if($vo_display->userAccess) {
				$t_user = new ca_users();
				$va_display_users = array();
				foreach($vo_display->userAccess->children() as $vo_permission) {
					$vs_user = trim((string)self::getAttribute($vo_permission, "user"));
					$vn_access = $this->_convertUserGroupAccessStringToInt(self::getAttribute($vo_permission, 'access'));

					if($vn_access && $t_user->load(array('user_name' => $vs_user))) {
						$va_display_users[$t_user->getUserID()] = $vn_access;
					} else {
						$this->addError("User name or access value invalid for display {$vs_display_code} (permission item with user name '{$vs_user}')");
					}
				}

				if(sizeof($va_display_users)>0) {
					$t_display->addUsers($va_display_users);
				}
			}

			if($vo_display->groupAccess) {
				$t_group = new ca_user_groups();
				$va_display_groups = array();
				foreach($vo_display->groupAccess->children() as $vo_permission) {
					$vs_group = trim((string)self::getAttribute($vo_permission, "group"));
					$vn_access = $this->_convertUserGroupAccessStringToInt(self::getAttribute($vo_permission, 'access'));

					if($vn_access && $t_group->load(array('code' => $vs_group))) {
						$va_display_groups[$t_group->getPrimaryKey()] = $vn_access;
					} else {
						$this->addError("Group code or access value invalid for display {$vs_display_code} (permission item with group code '{$vs_group}')");
					}
				}

				if(sizeof($va_display_groups)>0) {
					$t_display->addUserGroups($va_display_groups);
				}
			}

		}

		return true;
	}
	# --------------------------------------------------
	private function processDisplayPlacements($t_display, $po_placements) {
		$va_available_bundles = $t_display->getAvailableBundles(null, array('no_cache' => true));

		// nuke previous placements. there shouldn't be any if we're installing from scratch.
		// if we're updating, we expect the list of restrictions to include all restrictions!
		if(sizeof($po_placements->children())) {
			$this->opo_db->query('DELETE FROM ca_bundle_display_placements WHERE display_id=?', $t_display->getPrimaryKey());
		}

		$vn_i = 1;
		foreach($po_placements->children() as $vo_placement) {
			$vs_code = self::getAttribute($vo_placement, "code");
			$vs_bundle = (string)$vo_placement->bundle;

			$va_settings = $this->_processSettings(null, $vo_placement->settings);
			$t_display->addPlacement($vs_bundle, $va_settings, $vn_i, array('additional_settings' => $va_available_bundles[$vs_bundle]['settings']));
			if ($t_display->numErrors()) {
				$this->addError("There was an error while inserting display placement {$vs_code}: ".join(" ",$t_display->getErrors()));
				return false;
			}
			$vn_i++;
		}

		return true;
	}
	# --------------------------------------------------
	public function processSearchForms() {
		require_once(__CA_MODELS_DIR__."/ca_search_forms.php");
		require_once(__CA_MODELS_DIR__."/ca_search_form_placements.php");

		$o_config = Configuration::load();
		$vo_dm = Datamodel::load();

		$va_forms = array();
		if($this->ops_base_name) { // "merge" profile and its base
			if($this->opo_base->searchForms) {
				foreach($this->opo_base->searchForms->children() as $vo_form) {
					$va_forms[self::getAttribute($vo_form, "code")] = $vo_form;
				}
			}

			if($this->opo_profile->searchForms) {
				foreach($this->opo_profile->searchForms->children() as $vo_form) {
					$va_forms[self::getAttribute($vo_form, "code")] = $vo_form;
				}
			}
		} else {
			if($this->opo_profile->searchForms) {
				foreach($this->opo_profile->searchForms->children() as $vo_form) {
					$va_forms[self::getAttribute($vo_form, "code")] = $vo_form;
				}
			}
		}

		if(sizeof($va_forms) == 0) { return true; }

		foreach($va_forms as $vo_form) {
			$vs_form_code = self::getAttribute($vo_form, "code");
			$vb_system = self::getAttribute($vo_form, "system");
			$vs_table = self::getAttribute($vo_form, "type");
			if (!($t_instance = $vo_dm->getInstanceByTableName($vs_table, true))) { continue; }
			if (method_exists($t_instance, 'getTypeList') && !sizeof($t_instance->getTypeList())) { continue; } // no types configured
			if ($o_config->get($vs_table.'_disable')) { continue; }
			$vn_table_num = (int)$vo_dm->getTableNum($vs_table);

			if(!($t_form = ca_search_forms::find(array('form_code' => (string)$vs_form_code, 'table_num' => $vn_table_num), array('returnAs' => 'firstModelInstance')))) {
				$t_form = new ca_search_forms();
			}
			$t_form->setMode(ACCESS_WRITE);

			if(self::getAttribute($vo_form, "deleted") && $t_form->getPrimaryKey()) {
				$t_form->delete(true);
				continue;
			}

			$t_form->set("form_code", (string)$vs_form_code);
			$t_form->set("is_system", (int)$vb_system);
			$t_form->set("table_num", $vn_table_num);

			$this->_processSettings($t_form, $vo_form->settings);

			if($t_form->getPrimaryKey()) {
				$t_form->update();
			} else {
				$t_form->set("user_id", 1);		// let administrative user own these
				$t_form->insert();
			}

			if ($t_form->numErrors()) {
				$this->addError("There was an error while inserting search form {$vs_form_code}: ".join(" ",$t_form->getErrors()));
			} else {
				self::addLabelsFromXMLElement($t_form, $vo_form->labels, $this->opa_locales);
				if ($t_form->numErrors()) {
					$this->addError("There was an error while inserting search form label for {$vs_form_code}: ".join(" ",$t_form->getErrors()));
				}
				if(!$this->processSearchFormPlacements($t_form, $vo_form->bundlePlacements, null)) {
					return false;
				}
			}

			// set user and group access
			if($vo_form->userAccess) {
				$t_user = new ca_users();
				$va_form_users = array();
				foreach($vo_form->userAccess->children() as $vo_permission) {
					$vs_user = trim((string)self::getAttribute($vo_permission, "user"));
					$vn_access = $this->_convertUserGroupAccessStringToInt(self::getAttribute($vo_permission, 'access'));

					if($vn_access && $t_user->load(array('user_name' => $vs_user))) {
						$va_form_users[$t_user->getUserID()] = $vn_access;
					} else {
						$this->addError("User name or access value invalid for search form {$vs_form_code} (permission item with user name '{$vs_user}')");
					}
				}

				if(sizeof($va_form_users)>0) {
					$t_form->addUsers($va_form_users);
				}
			}

			if($vo_form->groupAccess) {
				$t_group = new ca_user_groups();
				$va_form_groups = array();
				foreach($vo_form->groupAccess->children() as $vo_permission) {
					$vs_group = trim((string)self::getAttribute($vo_permission, "group"));
					$vn_access = $this->_convertUserGroupAccessStringToInt(self::getAttribute($vo_permission, 'access'));

					if($vn_access && $t_group->load(array('code' => $vs_group))) {
						$va_form_groups[$t_group->getPrimaryKey()] = $vn_access;
					} else {
						$this->addError("Group code or access value invalid for search form {$vs_form_code} (permission item with group code '{$vs_group}')");
					}
				}

				if(sizeof($va_form_groups)>0) {
					$t_form->addUserGroups($va_form_groups);
				}
			}
		}

		return true;
	}
	# --------------------------------------------------
	private function processSearchFormPlacements($t_form, $po_placements) {
		$va_available_bundles = $t_form->getAvailableBundles();

		// nuke previous restrictions. there shouldn't be any if we're installing from scratch.
		// if we're updating, we expect the list of restrictions to include all restrictions!
		if(sizeof($po_placements->children())) {
			$this->opo_db->query('DELETE FROM ca_search_form_placements WHERE form_id=?', $t_form->getPrimaryKey());
		}

		$vn_i = 0;
		foreach($po_placements->children() as $vo_placement) {
			$vs_code = self::getAttribute($vo_placement, "code");
			$vs_bundle = (string)$vo_placement->bundle;

			$va_settings = $this->_processSettings(null, $vo_placement->settings);

			$t_form->addPlacement($vs_bundle, $va_settings, $vn_i, array('additional_settings' => $va_available_bundles[$vs_bundle]['settings']));
			if ($t_form->numErrors()) {
				$this->addError("There was an error while inserting search form placement {$vs_code}: ".join(" ",$t_form->getErrors()));
				return false;
			}
			$vn_i++;
		}

		return true;
	}
	# --------------------------------------------------
	public function processGroups() {

		// Create root group
		$t_user_group = ca_user_groups::find(array('code' => 'Root', 'parent_id' => null), array('returnAs' => 'firstModelInstance'));
		$t_user_group = $t_user_group ? $t_user_group : new ca_user_groups();

		$t_user_group->setMode(ACCESS_WRITE);
		$t_user_group->set('name', 'Root');
		if($t_user_group->getPrimaryKey()) {
			$t_user_group->update();
		} else {
			$t_user_group->set('code', 'Root');
			$t_user_group->set('parent_id', null);
			$t_user_group->insert();
		}

		if ($t_user_group->numErrors()) {
			$this->addError("Errors creating root user group 'Root': ".join("; ",$t_user_group->getErrors()));
			return false;
		}
		if($this->ops_base_name) { // "merge" profile and its base
			$va_groups = array();
			if($this->opo_base->groups) {
				foreach($this->opo_base->groups->children() as $vo_group) {
					$va_groups[self::getAttribute($vo_group, "code")] = $vo_group;
				}
			}
			if($this->opo_profile->groups) {
				foreach($this->opo_profile->groups->children() as $vo_group) {
					$va_groups[self::getAttribute($vo_group, "code")] = $vo_group;
				}
			}
		} else {
			if($this->opo_profile->groups) {
				foreach($this->opo_profile->groups->children() as $vo_group) {
					$va_groups[self::getAttribute($vo_group, "code")] = $vo_group;
				}
			}
		}

		if (is_array($va_groups)) {
			foreach($va_groups as $vs_group_code => $vo_group) {
				if(!($t_group = ca_user_groups::find(array('code' => (string)$vs_group_code), array('returnAs' => 'firstModelInstance')))) {
					$t_group = new ca_user_groups();
				}

				$t_group->setMode(ACCESS_WRITE);

				if(self::getAttribute($vo_group, "deleted") && $t_group->getPrimaryKey()) {
					$t_group->delete(true);
					continue;
				}

				$t_group->setMode(ACCESS_WRITE);
				$t_group->set('name', trim((string) $vo_group->name));
				$t_group->set('description', trim((string) $vo_group->description));
				if($t_group->getPrimaryKey()) {
					$t_group->update();
				} else {
					$t_group->set('code', $vs_group_code);
					$t_group->set('parent_id', null);
					$t_group->insert();
				}

				$va_roles = array();

				if($vo_group->roles) {
					foreach($vo_group->roles->children() as $vo_role) {
						$va_roles[] = trim((string) $vo_role);
					}
				}

				$t_group->addRoles($va_roles);

				if ($t_group->numErrors()) {
					$this->addError("Errors inserting user group {$vs_group_code}: ".join("; ",$t_group->getErrors()));
					return false;
				}
			}
		}

		return true;
	}
	# --------------------------------------------------
	public function processLogins($pb_create_admin_account=true) {
		$va_logins = array();
		if($this->ops_base_name) { // "merge" profile and its base
			if($this->opo_base->logins) {
				foreach($this->opo_base->logins->children() as $vo_login) {
					$vs_logins[self::getAttribute($vo_login, "user_name")] = $vo_login;
				}
			}
			if($this->opo_profile->logins) {
				foreach($this->opo_profile->logins->children() as $vo_login) {
					$va_logins[self::getAttribute($vo_login, "user_name")] = $vo_login;
				}
			}
		} else {
			if($this->opo_profile->logins) {
				foreach($this->opo_profile->logins->children() as $vo_login) {
					$va_logins[self::getAttribute($vo_login, "user_name")] = $vo_login;
				}
			}
		}

		// If no logins are defined in the profile create an admin login with random password
		if (!sizeof($va_logins) && $pb_create_admin_account) {
			$vs_password = $this->createAdminAccount();
			return array('administrator' => $vs_password);
		}

		$va_login_info = array();

		foreach($va_logins as $vs_user_name => $vo_login) {
			if (!($vs_password = trim((string) self::getAttribute($vo_login, "password")))) {
				$vs_password = $this->getRandomPassword();
			}

			$t_user = new ca_users();
			$t_user->setMode(ACCESS_WRITE);
			$t_user->set('user_name', $vs_user_name = trim((string) self::getAttribute($vo_login, "user_name")));
			$t_user->set('password', $vs_password);
			$t_user->set('fname',  trim((string) self::getAttribute($vo_login, "fname")));
			$t_user->set('lname',  trim((string) self::getAttribute($vo_login, "lname")));
			$t_user->set('email',  trim((string) self::getAttribute($vo_login, "email")));
			$t_user->set('active', 1);
			$t_user->set('userclass', 0);
			$t_user->insert();

			$va_roles = array();
			if($vo_login->role) {
				foreach($vo_login->role as $vo_role) {
					$va_roles[] = trim((string) self::getAttribute($vo_role, "code"));
				}
			}
			if (sizeof($va_roles)) { $t_user->addRoles($va_roles); }


			$va_groups = array();
			if($vo_login->group) {
				foreach($vo_login->group as $vo_group) {
					$va_groups[] = trim((string) self::getAttribute($vo_group, "code"));
				}
			}
			if (sizeof($va_groups)) { $t_user->addToGroups($va_groups); }

			if ($t_user->numErrors()) {
				$this->addError("Errors adding login {$vs_user_name}: ".join("; ",$t_user->getErrors()));
				return false;
			}

			$va_login_info[$vs_user_name] = $vs_password;
		}

		return $va_login_info;
	}
	# --------------------------------------------------
	public function processMiscHierarchicalSetup() {
		require_once(__CA_MODELS_DIR__."/ca_storage_locations.php");

		#
		# Create roots for storage locations hierarchies
		#
		$t_storage_location = new ca_storage_locations();
		$t_storage_location->setMode(ACCESS_WRITE);
		$t_storage_location->set('status', 0);
		$t_storage_location->set('parent_id', null);
		$t_storage_location->insert();

		if ($t_storage_location->numErrors()) {
			$this->addError("Errors inserting the storage location root: ".join("; ",$t_storage_location->getErrors()));
			return;
		}
	}
	# --------------------------------------------------
	public function createAdminAccount() {
		require_once(__CA_MODELS_DIR__."/ca_users.php");

		$ps_password = $this->getRandomPassword();
		$t_user = new ca_users();
		$t_user->setMode(ACCESS_WRITE);
		$t_user->set("user_name", 'administrator');
		$t_user->set("password", $ps_password);
		$t_user->set("email", $this->ops_admin_email);
		$t_user->set("fname", 'CollectiveAccess');
		$t_user->set("lname", 'Administrator');
		$t_user->set("userclass", 0);
		$t_user->set("active", 1);
		$t_user->insert();

		if ($t_user->numErrors()) {
			$this->addError("Errors while adding the default administrator account: ".join("; ",$t_user->getErrors()));
			return false;
		}

		return $ps_password;
	}
	# --------------------------------------------------
	private function _processSettings($pt_instance, $po_settings_node) {
		$va_settings = array();
		if($po_settings_node) {
			foreach($po_settings_node->children() as $vo_setting) {
				// some settings like 'label' or 'add_label' have 'locale' as sub-setting
				$vs_locale = self::getAttribute($vo_setting, "locale");
				if($vs_locale && isset($this->opa_locales[$vs_locale])) {
					$vn_locale_id = $this->opa_locales[$vs_locale];
				} else {
					$vn_locale_id = null;
				}

				$vs_setting_name = self::getAttribute($vo_setting, "name");
				$vs_value = (string) $vo_setting;

				if((strlen($vs_setting_name)>0) && (strlen($vs_value)>0)) { // settings need at least name and value
					if ($vs_locale) { // settings with locale (those can't repeat)
						$va_settings[$vs_setting_name][$vs_locale] = $vs_value;
					} else {
						// some settings allow multiple values under the same key, for instance restrict_to_types.
						// in those cases $va_settings[$vs_setting_name] becomes an array of values
						if (isset($va_settings[$vs_setting_name])) {
							if (!is_array($va_settings[$vs_setting_name])) {
								$va_settings[$vs_setting_name] = array($va_settings[$vs_setting_name]);
							}
							$va_settings[$vs_setting_name][] = $vs_value;
						} else {
							$va_settings[$vs_setting_name] = $vs_value;
						}
					}
				}
			}

			if (is_object($pt_instance)) {
				foreach($va_settings as $vs_setting_name => $vm_setting_value) {
					$pt_instance->setSetting($vs_setting_name, $vm_setting_value);
				}
			}
		}

		return $va_settings;
	}
	# --------------------------------------------------
	private function _convertACLStringToConstant($ps_name) {
		switch($ps_name) {
			case 'edit':
				return __CA_BUNDLE_ACCESS_EDIT__;
			case 'read':
				return __CA_BUNDLE_ACCESS_READONLY__;
			case 'none':
			default:
				return __CA_BUNDLE_ACCESS_NONE__;
		}
	}
	# --------------------------------------------------
	private function _convertUserGroupAccessStringToInt($ps_name) {
		switch($ps_name) {
			case 'read':
				return 1;
			case 'edit':
				return 2;
			default:
				return null;
		}
	}
	# --------------------------------------------------
}
