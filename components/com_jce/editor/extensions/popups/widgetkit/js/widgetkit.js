/**
 * @package   	Yootheme WidgetKit Lightbox
 * @copyright 	@@copyright@@
 * @license   	@@licence@@
 * @author		Ryan Demmer
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
(function() {
	WFPopups.addPopup('widgetkit', {
		
		params : {
			lightbox_padding 		: '',
			lightbox_overlayShow 	: '',
			lightbox_transitionIn	: '',
			lightbox_transitionOut	: '',
			lightbox_titlePosition	: ''
		},

		setup : function() {
			$('#widgetkit_lightbox_width').change(function() {
				var w = $(this).val(), $height = $('#widgetkit_lightbox_height');

				if(w && $height.val()) {
					// if constrain is on
					if($('#widgetkit_lightbox_constrain').is(':checked')) {
						var tw = $(this).data('tmp'), h = $height.val();

						if(tw) {
							var temp = ((h / tw) * w).toFixed(0);
							$height.val(temp).data('tmp', temp);
						}
					}
				}
				// store new tmp value
				$(this).data('tmp', w);
			});


			$('#widgetkit_lightbox_height').change(function() {
				var h = $(this).val(), $width = $('#widgetkit_lightbox_width');

				if(h && $width.val()) {
					// if constrain is on
					if($('#widgetkit_lightbox_constrain').is(':checked')) {
						var th = $(this).data('tmp'), w = $width.val();
						if(th) {
							var temp = ((w / th) * h).toFixed(0);
							$width.val(temp).data('tmp', temp);
						}
					}
				}
				// store new tmp value
				$(this).data('tmp', h);
			});
			
			// set defaults
			$.each(this.params, function(k, v) {
				$('#widgetkit_' + k).val(v);
			});
		},

		/**
		 * Check if node is a JCE MediaBox popup
		 * @param {Object} n Element
		 */
		check : function(n) {
			return n.getAttribute('data-lightbox');
		},

		/**
		 * Clean a link of popup attributes
		 * @param {Object} n
		 */
		remove : function(n) {
			var ed = tinyMCEPopup.editor;

			ed.dom.setAttrib(n, 'data-lightbox', '');
		},

		/**
		 * Convert parameter string to JSON object
		 */
		convertData : function(s) {
			var a = [];

			$.each(s.split(';'), function(i, n) {
				if(n) {
					n = n.replace(/([\w]+):(.*)/, '"$1":"$2"');
					a.push(n);
				}
			});

			return $.parseJSON('{' + a.join(',') + '}');
		},

		/**
		 * Get popup parameters
		 * @param {Object} n Popup node
		 */
		getAttributes : function(n) {
			var ed = tinyMCEPopup.editor, args = {};

			var data = ed.dom.getAttrib(n, 'data-lightbox');

			if(data && data != 'on') {
				data = this.convertData(data);
			}

			$.each(data, function(k, v) {
				$('#widgetkit_lightbox_' + k).val(v);
			});
			
			$('#widgetkit_lightbox_title').val(ed.dom.getAttrib(n, 'title'));

			$.extend(args, {
				src 	: ed.dom.getAttrib(n, 'href')
			});

			return args;
		},

		/**
		 * Set Popup Attributes
		 * @param {Object} n Link Element
		 */
		setAttributes : function(n, args) {
			var ed = tinyMCEPopup.editor;

			this.remove(n);

			var data = [];

			tinymce.each(['group', 'width', 'height', 'transitionIn', 'transitionOut', 'titlePosition', 'overlayShow', 'padding'], function(k) {
				var v = $('#widgetkit_lightbox_' + k).val();

				if(v == '' || v == null) {
					if(args[k]) {
						v = args[k];
					} else {
						return;
					}
				}

				data.push(k + ':' + v);
			});

			if(args.data) {
				$.each(args.data, function(k, v) {
					data.push(k + ':' + v);
				});

			}
			
			data = data.length ? data.join(';') : 'on';

			// set json data
			ed.dom.setAttrib(n, 'data-lightbox', data);
			
			// set title
			ed.dom.setAttrib(n, 'title', $('#widgetkit_lightbox_title').val());

			// Set target
			ed.dom.setAttrib(n, 'target', '_blank');
		},

		/**
		 * Function to call when popup extension selected
		 */
		onSelect : function() {
		},

		/**
		 * Call function when a file is selected / clicked
		 * @param {Object} args Function arguments
		 */
		onSelectFile : function(args) {
		}

	});
})();
