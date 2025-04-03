(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 */

	$(document).ready(function() {

		// Form submission handler
		$('.wp-petition-form').on('submit', function(e) {
			var form = $(this);
			var donation_type = form.find('input[name="donation_type"]').val();

			// --- Check if this is the vote form ---
			// If it's the vote form, allow the default PHP submission to handle it.
			if (donation_type === 'vote') {
				// Do not prevent default and do not proceed with AJAX.
				// The PHP code in petition_vote_form will handle this.
				return true;
			}

			// --- If it's NOT the vote form, proceed with AJAX submission ---
			e.preventDefault(); // Prevent default only for non-vote forms

			var submitButton = form.find('input[type="submit"], button[type="submit"]'); // Find both input and button submit types
			var formData = form.serialize();
			var noticeContainer = form.closest('.wp-petition-form-container').find('.wp-petition-notice-container'); // Find notice container relative to parent container

			// Validate form
			var isValid = true;
			// donation_type is already defined above
			var name = form.find('input[name="name"]').val();
			var email = form.find('input[name="email"]').val();
			var hours = parseInt(form.find('input[name="hours"]').val(), 10);
			var minutos = parseInt(form.find('input[name="minutos"]').val(), 10)
			
			if (!name) {
				isValid = false;
				form.find('input[name="name"]').addClass('error');
			} else {
				form.find('input[name="name"]').removeClass('error');
			}
			
			if (!email || !isValidEmail(email)) {
				isValid = false;
				form.find('input[name="email"]').addClass('error');
			} else {
				form.find('input[name="email"]').removeClass('error');
			}
			
			if (donation_type == "hours") {
				if (isNaN(hours) || hours < 1) {
					isValid = false;
					form.find('input[name="hours"]').addClass('error');
				} else {
					form.find('input[name="hours"]').removeClass('error');
				}
			} else if (donation_type == "minutos") {
				if (isNaN(minutos) || minutos < 1) {
					isValid = false;
					form.find('input[name="minutos"]').addClass('error');
				} else {
					form.find('input[name="minutos"]').removeClass('error');
				}
			}
			
			if (!isValid) {
				noticeContainer.html(
					'<div class="wp-petition-notice error">Please fill in all required fields correctly.</div>'
				);
				return;
			}
			
			// Disable submit button
			submitButton.prop('disabled', true).text('Submitting...');
			
			// AJAX request
			$.ajax({
				url: wp_petition_public.ajax_url,
				type: 'POST',
				data: formData + '&action=wp_petition_submit_donation&nonce=' + wp_petition_public.nonce,
				success: function(response) {
					if (response.success) {
						// Show success message
						noticeContainer.html(
							'<div class="wp-petition-notice success">' + response.data.message + '</div>'
						);
						
						// Reset form
						form[0].reset();
						
						// Update progress bars if they exist on the page
						updateProgressBars();
						
						// Update donors list if it exists on the page
						updateDonorsList();
					} else {
						// Show error message
						noticeContainer.html(
							'<div class="wp-petition-notice error">' + response.data.message + '</div>'
						);
					}
					
					// Re-enable submit button
					submitButton.prop('disabled', false).text('Donate time');
				},
				error: function() {
					// Show error message
					noticeContainer.html(
						'<div class="wp-petition-notice error">An error occurred. Please try again.</div>'
					);
					
					// Re-enable submit button
					// Reset button text based on donation type (or use a generic text)
					var originalButtonText = donation_type === 'hours' ? 'Donate time' : (donation_type === 'minutos' ? 'Donate Minutos' : 'Submit'); // Adjust as needed
					submitButton.prop('disabled', false).val(originalButtonText); // Use .val() for input type=submit
				},
				error: function() {
					// Show error message
					noticeContainer.html(
						'<div class="wp-petition-notice error">An error occurred. Please try again.</div>'
					);

					// Re-enable submit button
					var originalButtonText = donation_type === 'hours' ? 'Donate time' : (donation_type === 'minutos' ? 'Donate Minutos' : 'Submit'); // Adjust as needed
					submitButton.prop('disabled', false).val(originalButtonText); // Use .val() for input type=submit
				}
			});
		});
		
		// Social sharing buttons
		$('.wp-petition-social-sharing .facebook-button').on('click', function(e) {
			e.preventDefault();
			
			var url = $(this).data('url');
			var title = $(this).data('title');
			
			window.open(
				'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url) + '&t=' + encodeURIComponent(title),
				'facebook-share-dialog',
				'width=626,height=436'
			);
		});
		
		$('.wp-petition-social-sharing .x-button').on('click', function(e) {
			e.preventDefault();
			
			var url = $(this).data('url');
			var title = $(this).data('title');
			
			window.open(
				'https://twitter.com/intent/tweet?text=' + encodeURIComponent(title) + '&url=' + encodeURIComponent(url),
				'twitter-share-dialog',
				'width=626,height=436'
			);
		});
		
		// Function to validate email
		function isValidEmail(email) {
			var pattern = /^([a-z0-9_\.-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})$/i;
			return pattern.test(email);
		}
		
		// Function to update progress bars
		function updateProgressBars() {
			$('.wp-petition-progress-container').each(function() {
				var container = $(this);
				var campaignId = container.data('campaign-id');
				var type = container.data('type');
				
				if (campaignId) {
					$.ajax({
						url: wp_petition_public.ajax_url,
						type: 'POST',
						data: {
							action: 'wp_petition_get_progress',
							campaign_id: campaignId,
							type: type,
							nonce: wp_petition_public.nonce
						},
						success: function(response) {
							if (response.success) {
								container.replaceWith(response.data.html);
							}
						}
					});
				}
			});
		}
		
		// Function to update donors list
		function updateDonorsList() {
			$('.wp-petition-donors-container').each(function() {
				var container = $(this);
				var campaignId = container.data('campaign-id');
				
				if (campaignId) {
					$.ajax({
						url: wp_petition_public.ajax_url,
						type: 'POST',
						data: {
							action: 'wp_petition_get_donors',
							campaign_id: campaignId,
							nonce: wp_petition_public.nonce
						},
						success: function(response) {
							if (response.success) {
								container.replaceWith(response.data.html);
							}
						}
					});
				}
			});
		}
	});

})( jQuery );
