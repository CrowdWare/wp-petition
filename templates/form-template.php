<?php
/**
 * Template for the donation form.
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
    <h3><?php echo esc_html__('Spende Deine Zeit', 'wp-petition'); ?></h3>
    
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
        <input type="hidden" name="donation_type" value="hours">
        
        <div class="form-field">
            <label for="name"><?php echo esc_html__('Name', 'wp-petition'); ?> *</label>
            <input type="text" name="name" id="name" required>
        </div>
        
        <div class="form-field">
            <label for="email"><?php echo esc_html__('Email', 'wp-petition'); ?> *</label>
            <input type="email" name="email" id="email" required>
        </div>
        <p>Ich biete wie folgt an...</p>
        <div class="form-field checkbox-field">
            <input type="checkbox" name="facebook_post" id="facebook_post" value="1">
            <label for="facebook_post"><?php echo esc_html__('Facebook Posts', 'wp-petition'); ?></label>
        </div>
        
        <div class="form-field checkbox-field">
            <input type="checkbox" name="x_post" id="x_post" value="1">
            <label for="x_post"><?php echo esc_html__('X Post', 'wp-petition'); ?></label>
        </div>
        
        <div class="form-field">
            <label for="other_support"><?php echo esc_html__('Sonstiges', 'wp-petition'); ?></label>
            <textarea name="other_support" id="other_support" rows="4"></textarea>
        </div>
        
        <div class="form-field">
            <label for="hours"><?php echo esc_html__('Stunden', 'wp-petition'); ?> *</label>
            <input type="number" name="hours" id="hours" min="1" value="1" required>
        </div>
        
        <div class="form-field">
            <button type="submit" name="wp_petition_submit" class="submit-button"><?php echo esc_html__('Zeit spenden', 'wp-petition'); ?></button>
        </div>
    </form>
    
    <!--div class="wp-petition-social-sharing">
        <h4><?php echo esc_html__('Share This Campaign', 'wp-petition'); ?></h4>
        <div class="social-buttons">
            <a href="#" class="social-button facebook-button" data-url="<?php echo esc_url(get_permalink()); ?>" data-title="<?php echo esc_attr($campaign->title); ?>"><?php echo esc_html__('Share on Facebook', 'wp-petition'); ?></a>
            <a href="#" class="social-button x-button" data-url="<?php echo esc_url(get_permalink()); ?>" data-title="<?php echo esc_attr($campaign->title); ?>"><?php echo esc_html__('Share on X', 'wp-petition'); ?></a>
        </div>
    </div-->
</div>
