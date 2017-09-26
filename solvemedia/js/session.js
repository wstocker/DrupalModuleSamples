(function($, w, d) {
	/**
	 * Load remote content after the main page loaded.
	 */
	Drupal.behaviors.nro_bouncex = {
		attach: function(context, settings) {
			solvemediaUnsetsession();
			
			//Callback to unset session.
			function solvemediaUnsetsession() {
				jQuery.ajax({
					type: "POST",
					url: "/solvemedia/unset",
					dataType: "html",
					cache: false,
				});
			}
		}
	}
};
})(jQuery);