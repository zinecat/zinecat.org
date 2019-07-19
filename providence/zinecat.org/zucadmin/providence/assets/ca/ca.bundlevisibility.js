/* ----------------------------------------------------------------------
 * js/ca/ca.bundlevisibilty.js
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2013-2016 Whirl-i-Gig
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

var caUI = caUI || {};

(function ($) {
	caUI.initBundleVisibilityManager = function(options) {
		// --------------------------------------------------------------------------------
		// setup options
		var that = jQuery.extend({
			bundles: [],
			cookieJar: jQuery.cookieJar('caBundleVisibility'),
			bundleStates: {},
			bundleDictionaryStates: {}
		}, options);

		// --------------------------------------------------------------------------------
		// Define methods
		// --------------------------------------------------------------------------------
		/**
		 * Register a bundle
		 * @param id
		 * @param force
		 */
		that.registerBundle = function(id, force) {
			that.bundles.push(id);
			var bundleState;

			if(force) { // if override is set, use it
				bundleState = force;
			} else { // otherwise use cookiejar and default to open
				bundleState = (that.cookieJar.get(id) == 'closed') ? 'closed' : 'open';
			}

			that.bundleStates[id] = bundleState;
			that.bundleDictionaryStates[id] = (that.cookieJar.get(id + 'DictionaryEntry') == 'open') ? 'open' : 'closed';	// default to closed

			// actually open/close elements
			if (that.bundleStates[id] == 'closed') {
				that.close(id, true);
			} else {
				that.open(id, true);
			}
			if (that.bundleDictionaryStates[id] == 'closed') {
				that.closeDictionaryEntry(id, true);
			} else {
				that.openDictionaryEntry(id, true);
			}
		};

		// Set initial visibility of all registered bundles
		that.setAll = function() {
			jQuery.each(that.bundles, function(k, id) {
				var container = jQuery("#" + id);

				if(that.bundleStates[id] == 'closed') {
					container.hide();
				} else {
					container.show();
				}
			});
		};

		// Toggle bundle
		that.toggle = function(id) {
			if(that.bundleStates[id] == 'closed') {
				that.open(id);
			} else {
				that.close(id);
			}
			return false;
		};

		// Open bundle
		that.open = function(id, dontAnimate) {
			if (id === undefined) {
				jQuery.each(that.bundles, function(k, id) {
					that.open(id);
				});
			} else {
				var preview_id = id.replace(/[0-9]+\_rel/g, '');
				jQuery("#" + id).slideDown(dontAnimate ? 0 : 250);
				jQuery("#" + preview_id + '_BundleContentPreview').hide();

				if (jQuery("#" + id + 'DictionaryEntry').length && (that.bundleDictionaryStates[id] == 'open')) {
					jQuery("#" + id + 'DictionaryEntry').slideDown(dontAnimate ? 0 : 250);
				}

				that.bundleStates[id] = 'open';
				that.cookieJar.set(id, 'open');

				if (dontAnimate) {
					jQuery("#" + id + "VisToggleButton").rotate({ angle: 180 });
				} else {
					jQuery("#" + id + "VisToggleButton").rotate({ duration:500, angle: 0, animateTo: 180 });
				}
			}
			return false;
		};

		// Close bundle
		that.close = function(id, dontAnimate) {
			if (id === undefined) {
				jQuery.each(that.bundles, function(k, id) {
					that.close(id);
				});
			} else {
				var preview_id = id.replace(/[0-9]+\_rel/g, '');
				jQuery("#" + id).slideUp(dontAnimate ? 0 : 250);
				jQuery("#" + preview_id + '_BundleContentPreview').show();

				if (jQuery("#" + id + 'DictionaryEntry').length && (that.bundleDictionaryStates[id] == 'open')) {
					jQuery("#" + id + 'DictionaryEntry').slideUp(dontAnimate ? 0 : 250);
				}

				that.bundleStates[id] = 'closed';
				that.cookieJar.set(id, 'closed');

				if (dontAnimate) {
					jQuery("#" + id + "VisToggleButton").rotate({ angle: 0 });
				} else {
					jQuery("#" + id + "VisToggleButton").rotate({ duration:500, angle: 180, animateTo: 0 });
				}
			}
			return false;
		};

		// Toggle dictionary entry
		that.toggleDictionaryEntry = function(id) {
			if(that.bundleDictionaryStates[id] == 'closed') {
				that.openDictionaryEntry(id);
			} else {
				that.closeDictionaryEntry(id);
			}
			return false;
		};

		// Open dictionary entry
		that.openDictionaryEntry = function(id, dontAnimate) {
			if (id === undefined) {
				jQuery.each(that.bundles, function(k, id) {
					that.openDictionaryEntry(id);
				});
			} else {
				if (!jQuery("#" + id + 'DictionaryEntry').length) { return false; }
				jQuery("#" + id + 'DictionaryEntry').slideDown(dontAnimate ? 0 : 250);
				that.bundleDictionaryStates[id] = 'open';
				that.cookieJar.set(id + 'DictionaryEntry', 'open');

				if (that.bundleStates[id] == 'closed') {
					that.open(id);
				}

				if (dontAnimate) {
					jQuery("#" + id + "MetadataDictionaryToggleButton").css("opacity", 1.0);
				} else {
					jQuery("#" + id + "MetadataDictionaryToggleButton").animate({ duration:500, opacity: 1.0, animateTo: 0.4 });
				}
			}

			return false;
		};

		// Close dictionary entry
		that.closeDictionaryEntry = function(id, dontAnimate) {
			if (id === undefined) {
				jQuery.each(that.bundles, function(k, id) {
					that.closeDictionaryEntry(id);
				});
			} else {
				if (!jQuery("#" + id + 'DictionaryEntry').length) { return false; }
				jQuery("#" + id + 'DictionaryEntry').slideUp(dontAnimate ? 0 : 250);
				that.bundleDictionaryStates[id] = 'closed';
				that.cookieJar.set(id + 'DictionaryEntry', 'closed');

				if (dontAnimate) {
					jQuery("#" + id + "MetadataDictionaryToggleButton").css("opacity", 0.4);
				} else {
					jQuery("#" + id + "MetadataDictionaryToggleButton").animate({ duration:500, opacity: 0.4, animateTo: 1.0 });
				}
			}
			return false;
		};

		// --------------------------------------------------------------------------------

		return that;
	};

	caBundleVisibilityManager = caUI.initBundleVisibilityManager();
})(jQuery);