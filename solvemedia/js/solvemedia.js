(function($, w, d) {
	/**
	 * Load remote content after the main page loaded.
	 */
	Drupal.behaviors.nro_solve_media_forms = {
		attach: function(context, settings) {
			$(document).on('click', '.form-submit', function(e) {
				var valid = '';
				var toWebforms = $(".form-email").val();
				var toDonationform = $("#edit-email-address").val();
				var toUserRegisterform = $("#edit-mail").val();
				var popUpforms = $("#mail").val();
				if (toWebforms !== undefined) {
					//Webforms
					var to = toWebforms;
				}
				if (toDonationform !== undefined) {
					//Donation Forms
					var to = toDonationform;
				}
				if (toUserRegisterform !== undefined) {
					//User Registration Form
					var to = toUserRegisterform;
				}
				if (popUpforms !== undefined) {
					//User Registration Form
					var to = popUpforms;
				}
				if (!to.match(/^([a-z0-9._-]+@[a-z0-9._-]+\.[a-z]{2,4}$)/i)) {
					valid += 'Not a valid email.';
				}
				if (valid.length < 1) {
					var promise = solvemediaCallbackmodule(to);
				}
			});
			//Make the callback to get the Solve Media script

			function solvemediaCallbackmodule(email) {
				var url = "/solvemedia/callback";
				$post(url, { // Data Sending With Request To Server
					to: email
				}, function(result) {
					if (result.length > 1) {
						//If succesful pass the params to next function
						solvemediaAPImodule(result);
					}
				});
			}
			//Make the callback to Solve Media
			/*data.circulate.com/dapi/data?sid=$PUBID;type=js;hema=$HASH*/

			function solvemediaAPImodule(result) {
				var json = JSON.parse(result);
				var hema = encodeURIComponent(json['hema']);
				var sid = encodeURIComponent(json['sid']);
				$ajax({
					type: "GET",
					url: ('https:' == document.location.protocol ? 'https://data-secure' : 'http://data') + ".circulate.com/dapi/data?sid=" + sid + '&type=js&hema=' + hema,
					dataType: "script",
					cache: false,
					success: function(callback) {
						console.log(hema);
					}
				});
			}
		}
	}
};
})(jQuery);