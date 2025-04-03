<?php
/**
 * Template for the Minutos donation form.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Petition
 * @subpackage WP_Petition/templates
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get the campaign
if (!isset($campaign) || !$campaign) {
    return;
}
?>

<div class="wp-petition-form-container">
    <h3><?php echo esc_html__('Spende Minutos', 'wp-petition'); ?></h3>
    
    <div class="wp-petition-notice-container">
        <?php
        // Display success message if set
        if (isset($_GET['wp_petition_success'])) {
            echo '<div class="wp-petition-notice success">' . esc_html__('Thank you for your donation!', 'wp-petition') . '</div>';
        }
        
        // Display error message if set
        if (isset($_GET['wp_petition_error'])) {
            echo '<div class="wp-petition-notice error">' . esc_html(urldecode($_GET['wp_petition_error'])) . '</div>';
        }
        ?>
    </div>
    
    <form class="wp-petition-form" method="post" action="">
        <?php wp_nonce_field('wp_petition_donation_form', 'wp_petition_nonce'); ?>
        <input type="hidden" name="campaign_id" value="<?php echo esc_attr($campaign->campaign_id); ?>">
        <input type="hidden" name="donation_type" value="minutos">
        
        <div class="form-field">
            <label for="name"><?php echo esc_html__('Name', 'wp-petition'); ?> *</label>
            <input type="text" name="name" id="name" required>
        </div>
        
        <div class="form-field">
            <label for="email"><?php echo esc_html__('Email', 'wp-petition'); ?> *</label>
            <input type="email" name="email" id="email" required>
        </div>
        
        <div class="form-field">
            <label for="minutos"><?php echo esc_html__('Minutos', 'wp-petition'); ?> *</label>
            <input type="number" name="minutos" id="minutos" min="15" value="15" required>
            <p class="description">Bitte sende Deine <a target="_blank" href="https://minuto.org/de">Minutos</a> per Post nach der Übermittelung dieses Formulares.</p>
        </div>
        
        <div class="form-field">
            <button type="submit" name="wp_petition_submit" class="submit-button"><?php echo esc_html__('Minutos spenden', 'wp-petition'); ?></button>
        </div>
    </form>
</div>
