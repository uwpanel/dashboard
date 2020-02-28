/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * JQuery plugin that includes basic callbacks for Helpers
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

(function ($) {
	/**
	 * KumbiaPHP object
	 *
	 */
	$.KumbiaPHP = {
		/**
		 * Path to public directory on server
		 *
		 * @var String
		 */
		publicPath: null,

		/**
		 * Loaded plugins
		 *
		 * @var Array
		 */
		plugin: [],

		/**
		 * Show confirmation message
		 *
		 * @param Object event
		 */
		cConfirm: function (event) {
			var este = $(this);
			if (!confirm(este.data('msg'))) {
				event.preventDefault();
			}
		},

		/**
		 * Apply an effect to an item
		 *
		 * @param String fx
		 */
		cFx: function (fx) {
			return function (event) {
				event.preventDefault();
				var este = $(this),
					rel = $('#' + este.data('to'));
				rel[fx]();
			}
		},

		/**
		 * Load with AJAX
		 *
		 * @param Object event
		 */
		cRemote: function (event) {
			var este = $(this),
				rel = $('#' + este.data('to'));
			event.preventDefault();
			rel.load(this.href);
		},

		/**
		 * Load with AJAX and Confirmation
		 *
		 * @param Object event
		 */
		cRemoteConfirm: function (event) {
			var este = $(this),
				rel = $('#' + este.data('to'));
			event.preventDefault();
			if (confirm(este.data('msg'))) {
				rel.load(this.href);
			}
		},

		/**
		 * Send forms asynchronously, via POST
		 * And load them in a container
		 */
		cFRemote: function (event) {
			event.preventDefault();
			este = $(this);
			var button = $('[type=submit]', este);
			button.attr('disabled', 'disabled');
			var url = este.attr('action');
			var div = este.attr('data-to');
			$.post(url, este.serialize(), function (data, status) {
				var capa = $('#' + div);
				capa.html(data);
				capa.hide();
				capa.show('slow');
				button.attr('disabled', null);
			});
		},

		/**
		 * Load with AJAX when changing select
		 *
		 * @param Object event
		 */
		cUpdaterSelect: function (event) {
			var $t = $(this),
				$u = $('#' + $t.data('update'))
			url = $t.data('url');
			$u.empty();
			$.get(url, {
				'id': $t.val()
			}, function (d) {
				for (i in d) {
					var a = $('<option />').text(d[i]).val(i);
					$u.append(a);
				}
			}, 'json');
		},

		/**
		 * Link to the default classes
		 *
		 */
		bind: function () {
			// Link and button with confirmation
			$("body").on('click', "a.js-confirm, input.js-confirm", this.cConfirm);

			// Ajax link
			$("body").on('click', "a.js-remote", this.cRemote);

			// Ajax link with confirmation
			$("body").on('click', "a.js-remote-confirm", this.cRemoteConfirm);

			// Show effect
			$("body").on('click', "a.js-show", this.cFx('show'));

			// Hide effect
			$("body").on('click', "a.js-hide", this.cFx('hide'));

			// Toggle effect
			$("body").on('click', "a.js-toggle", this.cFx('toggle'));

			// FadeIn effect
			$("body").on('click', "a.js-fade-in", this.cFx('fadeIn'));

			// FadeOut effect
			$("body").on('click', "a.js-fade-out", this.cFx('fadeOut'));

			// Ajax form
			$("body").on('submit', "form.js-remote", this.cFRemote);

			// Drop-down list that updates with ajax
			$("body").on('change', "select.js-remote", this.cUpdaterSelect);

			// Link DatePicker
			$.KumbiaPHP.bindDatePicker();

		},

		/**
		 * Implement plugins autoload, these should follow
		 * a convention so that it can work properly
		 */
		autoload: function () {
			var elem = $("[class*='jp-']");
			$.each(elem, function (i, val) {
				var este = $(this); //points to the element with class jp- *
				var classes = este.attr('class').split(' ');
				for (i in classes) {
					if (classes[i].substr(0, 3) == 'jp-') {
						if ($.inArray(classes[i].substr(3), $.KumbiaPHP.plugin) != -1)
							continue;
						$.KumbiaPHP.plugin.push(classes[i].substr(3))
					}
				}
			});
			var head = $('head');
			for (i in $.KumbiaPHP.plugin) {
				$.ajaxSetup({
					cache: true
				});
				head.append('<link href="' + $.KumbiaPHP.publicPath + 'css/' + $.KumbiaPHP.plugin[i] + '.css" type="text/css" rel="stylesheet"/>');
				$.getScript($.KumbiaPHP.publicPath + 'javascript/jquery/jquery.' + $.KumbiaPHP.plugin[i] + '.js', function (data, text) {});
			}
		},

		/**
		 * Load and Link Unobstrusive DatePicker if necessary
		 *
		 */
		bindDatePicker: function () {

			// Select the input fields
			var inputs = $('input.js-datepicker');
			/**
			 * Function responsible for linking the DatePicker to the Inputs
			 *
			 */
			var bindInputs = function () {
				inputs.each(function () {
					var opts = {
						monthSelector: true,
						yearSelector: true
					};
					var input = $(this);
					// Check if there is a minimum
					if (input.attr('min') != undefined) {
						opts.dateMin = input.attr('min').split('-');
					}
					// Check if there is maximum
					if (input.attr('max') != undefined) {
						opts.dateMax = input.attr('max').split('-');
					}

					// Create the calendar
					input.pickadate(opts);
				});
			}

			// If Unobstrusive DatePicker is already loaded, integrate it at once
			if (typeof ($.pickadate) != "undefined") {
				return bindInputs();
			}

			// Load the style sheet
			$('head').append('<link href="' + this.publicPath + 'css/pickadate.css" type="text/css" rel="stylesheet"/>');

			// Load Unobstrusive DatePicker, in order to use cache
			jQuery.ajax({
				dataType: "script",
				cache: true,
				url: this.publicPath + 'javascript/jquery/pickadate.js'
			}).done(function () {
				bindInputs();
			});
		},

		/**
		 * Initialize the plugin
		 *
		 */
		initialize: function () {
			// Get the publicPath, subtracting the remaining characters
			// of the route, with respect to the location route of the KumbiaPHP plugin
			// "javascript/jquery/jquery.kumbiaphp.js"
			var src = $('script:last').attr('src');
			this.publicPath = src.substr(0, src.length - 37);

			// Link to the default classes
			$(function () {
				$.KumbiaPHP.bind();
				$.KumbiaPHP.autoload();

			});
		}
	}

	// Initialize the plugin
	$.KumbiaPHP.initialize();
})(jQuery);