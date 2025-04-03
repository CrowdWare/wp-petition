(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 */

	$(document).ready(function() {
		
		// Campaign form submission
		$('#wp-petition-campaign-form').on('submit', function(e) {
			e.preventDefault();
			
			var form = $(this);
			var submitButton = form.find('button[type="submit"]');
			var formData = form.serialize();
			var isUpdate = form.find('input[name="campaign_id"]').val() > 0;
			
			// Disable submit button
			submitButton.prop('disabled', true).text(isUpdate ? 'Updating...' : 'Creating...');
			
			// AJAX request
			$.ajax({
				url: wp_petition_admin.ajax_url,
				type: 'POST',
				data: formData + '&action=' + (isUpdate ? 'wp_petition_update_campaign' : 'wp_petition_create_campaign') + '&nonce=' + wp_petition_admin.nonce,
				success: function(response) {
					if (response.success) {
						// Show success message
						$('.wp-petition-admin-notices').html(
							'<div class="wp-petition-notice success">' + response.data.message + '</div>'
						);
						
						// Redirect to campaigns list after a delay if creating a new campaign
						if (!isUpdate) {
							setTimeout(function() {
								window.location.href = 'admin.php?page=wp-petition';
							}, 1500);
						} else {
							// Re-enable submit button
							submitButton.prop('disabled', false).text('Update Campaign');
						}
					} else {
						// Show error message
						$('.wp-petition-admin-notices').html(
							'<div class="wp-petition-notice error">' + response.data.message + '</div>'
						);
						
						// Re-enable submit button
						submitButton.prop('disabled', false).text(isUpdate ? 'Update Campaign' : 'Create Campaign');
					}
				},
				error: function() {
					// Show error message
					$('.wp-petition-admin-notices').html(
						'<div class="wp-petition-notice error">An error occurred. Please try again.</div>'
					);
					
					// Re-enable submit button
					submitButton.prop('disabled', false).text(isUpdate ? 'Update Campaign' : 'Create Campaign');
				}
			});
		});
		
		// Delete campaign confirmation
		$('.wp-petition-delete-campaign').on('click', function(e) {
			e.preventDefault();
			
			var campaignId = $(this).data('campaign-id');
			var campaignTitle = $(this).data('campaign-title');
			
			if (confirm('Are you sure you want to delete the campaign "' + campaignTitle + '"? This action cannot be undone.')) {
				// AJAX request to delete campaign
				$.ajax({
					url: wp_petition_admin.ajax_url,
					type: 'POST',
					data: {
						action: 'wp_petition_delete_campaign',
						campaign_id: campaignId,
						nonce: wp_petition_admin.nonce
					},
					success: function(response) {
						if (response.success) {
							// Show success message
							$('.wp-petition-admin-notices').html(
								'<div class="wp-petition-notice success">' + response.data.message + '</div>'
							);
							
							// Remove the campaign row from the table
							$('#campaign-row-' + campaignId).fadeOut(300, function() {
								$(this).remove();
							});
						} else {
							// Show error message
							$('.wp-petition-admin-notices').html(
								'<div class="wp-petition-notice error">' + response.data.message + '</div>'
							);
						}
					},
					error: function() {
						// Show error message
						$('.wp-petition-admin-notices').html(
							'<div class="wp-petition-notice error">An error occurred. Please try again.</div>'
						);
					}
				});
			}
		});
		
		// Export donors to PDF
		$('.wp-petition-export-donors').on('click', function(e) {
			e.preventDefault();
			
			var campaignId = $(this).data('campaign-id');
			var button = $(this);
			
			// Disable button
			button.prop('disabled', true).text('Exporting...');
			
			// AJAX request to export donors
			$.ajax({
				url: wp_petition_admin.ajax_url,
				type: 'POST',
				data: {
					action: 'wp_petition_export_donors',
					campaign_id: campaignId,
					nonce: wp_petition_admin.nonce
				},
				success: function(response) {
					if (response.success) {
						// Show success message
						$('.wp-petition-admin-notices').html(
							'<div class="wp-petition-notice success">' + response.data.message + '</div>'
						);
						
						// If PDF URL is provided, open it in a new tab
						if (response.data.pdf_url) {
							window.open(response.data.pdf_url, '_blank');
						}
					} else {
						// Show error message
						$('.wp-petition-admin-notices').html(
							'<div class="wp-petition-notice error">' + response.data.message + '</div>'
						);
					}
					
					// Re-enable button
					button.prop('disabled', false).text('Export Donors');
				},
				error: function() {
					// Show error message
					$('.wp-petition-admin-notices').html(
						'<div class="wp-petition-notice error">An error occurred. Please try again.</div>'
					);
					
					// Re-enable button
					button.prop('disabled', false).text('Export Donors');
				}
			});
		});
		
		// Settings form submission
		$('#wp-petition-settings-form').on('submit', function(e) {
			e.preventDefault();
			
			var form = $(this);
			var submitButton = form.find('button[type="submit"]');
			var formData = form.serialize();
			
			// Disable submit button
			submitButton.prop('disabled', true).text('Saving...');
			
			// AJAX request
			$.ajax({
				url: wp_petition_admin.ajax_url,
				type: 'POST',
				data: formData + '&action=wp_petition_save_settings&nonce=' + wp_petition_admin.nonce,
				success: function(response) {
					if (response.success) {
						// Show success message
						$('.wp-petition-admin-notices').html(
							'<div class="wp-petition-notice success">' + response.data.message + '</div>'
						);
					} else {
						// Show error message
						$('.wp-petition-admin-notices').html(
							'<div class="wp-petition-notice error">' + response.data.message + '</div>'
						);
					}
					
					// Re-enable submit button
					submitButton.prop('disabled', false).text('Save Settings');
				},
				error: function() {
					// Show error message
					$('.wp-petition-admin-notices').html(
						'<div class="wp-petition-notice error">An error occurred. Please try again.</div>'
					);
					
					// Re-enable submit button
					submitButton.prop('disabled', false).text('Save Settings');
				}
			});
		});
		
		// Mark Minutos as received
		
		$(document).on('click', '.wp-petition-mark-minutos-received', function(e) {
			e.preventDefault();
			
			var donationId = $(this).data('donation-id');
			var button = $(this);
			var row = button.closest('tr');
			
			// Confirm action
			if (confirm('Are you sure you want to mark these Minutos as received?')) {
				// Disable button
				button.prop('disabled', true).text('Processing...');
				
				// AJAX request to mark Minutos as received
				$.ajax({
					url: wp_petition_admin.ajax_url,
					type: 'POST',
					data: {
						action: 'wp_petition_mark_minutos_received',
						donation_id: donationId,
						nonce: wp_petition_admin.nonce
					},
					success: function(response) {
						if (response.success) {
							// Show success message
							$('.wp-petition-admin-notices').html(
								'<div class="wp-petition-notice success">' + response.data.message + '</div>'
							);
							
							// Update the row to show received status
							row.addClass('minutos-received').removeClass('minutos-pending');
							row.find('.minutos-status').html('Received').addClass('received').removeClass('pending');
							
							// Remove the button
							button.remove();
						} else {
							// Show error message
							$('.wp-petition-admin-notices').html(
								'<div class="wp-petition-notice error">' + response.data.message + '</div>'
							);
							
							// Re-enable button
							button.prop('disabled', false).text('Mark as Received');
						}
					},
					error: function() {
						// Show error message
						$('.wp-petition-admin-notices').html(
							'<div class="wp-petition-notice error">An error occurred. Please try again.</div>'
						);
						
						// Re-enable button
						button.prop('disabled', false).text('Mark as Received');
					}
				});
			}
		});
		
		// Date picker initialization (if jQuery UI datepicker is available)
		if ($.datepicker) {
			$('.wp-petition-datepicker').datepicker({
				dateFormat: 'yy-mm-dd',
				changeMonth: true,
				changeYear: true
			});
		}
		
		// Page selector (if select2 is available)
		if ($.fn.select2) {
			$('.wp-petition-page-select').select2({
				placeholder: 'Select a page',
				allowClear: true
			});
		}
	});

})( jQuery );
