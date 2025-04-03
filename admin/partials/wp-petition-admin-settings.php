<?php
/**
 * Admin settings page template.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Petition
 * @subpackage WP_Petition/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get current settings
$stripe_integration = get_option('wp_petition_stripe_integration', 0);
$default_goal_hours = get_option('wp_petition_default_goal_hours', 100);
$default_goal_amount = get_option('wp_petition_default_goal_amount', 1000.00);
$default_goal_minutos = get_option('wp_petition_default_goal_minutos', 3000);
$social_sharing = get_option('wp_petition_social_sharing', 1);
$export_format = get_option('wp_petition_export_format', 'pdf');
?>

<div class="wrap wp-petition-admin-wrap">
    <div class="wp-petition-admin-header">
        <h1><?php echo esc_html__('Settings', 'wp-petition'); ?></h1>
    </div>
    
    <div class="wp-petition-admin-notices"></div>
    
    <form id="wp-petition-settings-form" class="wp-petition-settings-form">
        <?php wp_nonce_field('wp_petition_settings_form', 'wp_petition_settings_nonce'); ?>
        
        <h2><?php echo esc_html__('General Settings', 'wp-petition'); ?></h2>
        
        <div class="form-field">
            <label for="default_goal_hours"><?php echo esc_html__('Default Hours Goal', 'wp-petition'); ?></label>
            <input type="number" name="default_goal_hours" id="default_goal_hours" min="0" value="<?php echo esc_attr($default_goal_hours); ?>">
            <p class="description"><?php echo esc_html__('Default hours goal for new campaigns.', 'wp-petition'); ?></p>
        </div>
        
        <div class="form-field">
            <label for="default_goal_amount"><?php echo esc_html__('Default Money Goal (€)', 'wp-petition'); ?></label>
            <input type="number" name="default_goal_amount" id="default_goal_amount" min="0" step="0.01" value="<?php echo esc_attr($default_goal_amount); ?>">
            <p class="description"><?php echo esc_html__('Default money goal for new campaigns.', 'wp-petition'); ?></p>
        </div>
        
        <div class="form-field">
            <label for="default_goal_minutos"><?php echo esc_html__('Default Minutos Goal', 'wp-petition'); ?></label>
            <input type="number" name="default_goal_minutos" id="default_goal_minutos" min="0" value="<?php echo esc_attr($default_goal_minutos); ?>">
            <p class="description"><?php echo esc_html__('Default Minutos goal for new campaigns.', 'wp-petition'); ?></p>
        </div>
        
        <div class="form-field">
            <label for="social_sharing"><?php echo esc_html__('Social Sharing', 'wp-petition'); ?></label>
            <select name="social_sharing" id="social_sharing">
                <option value="1" <?php selected($social_sharing, 1); ?>><?php echo esc_html__('Enabled', 'wp-petition'); ?></option>
                <option value="0" <?php selected($social_sharing, 0); ?>><?php echo esc_html__('Disabled', 'wp-petition'); ?></option>
            </select>
            <p class="description"><?php echo esc_html__('Enable or disable social sharing buttons on the donation form.', 'wp-petition'); ?></p>
        </div>
        
        <div class="form-field">
            <label for="export_format"><?php echo esc_html__('Export Format', 'wp-petition'); ?></label>
            <select name="export_format" id="export_format">
                <option value="pdf" <?php selected($export_format, 'pdf'); ?>><?php echo esc_html__('PDF', 'wp-petition'); ?></option>
                <option value="csv" <?php selected($export_format, 'csv'); ?>><?php echo esc_html__('CSV', 'wp-petition'); ?></option>
                <option value="excel" <?php selected($export_format, 'excel'); ?>><?php echo esc_html__('Excel', 'wp-petition'); ?></option>
            </select>
            <p class="description"><?php echo esc_html__('Default format for exporting donor lists.', 'wp-petition'); ?></p>
        </div>
        
        <h2><?php echo esc_html__('Integration Settings', 'wp-petition'); ?></h2>
        
        <div class="form-field">
            <label for="stripe_integration"><?php echo esc_html__('Stripe Integration', 'wp-petition'); ?></label>
            <select name="stripe_integration" id="stripe_integration">
                <option value="1" <?php selected($stripe_integration, 1); ?>><?php echo esc_html__('Enabled', 'wp-petition'); ?></option>
                <option value="0" <?php selected($stripe_integration, 0); ?>><?php echo esc_html__('Disabled', 'wp-petition'); ?></option>
            </select>
            <p class="description"><?php echo esc_html__('Enable or disable Stripe payment integration for monetary donations.', 'wp-petition'); ?></p>
        </div>
        
        <div class="form-field submit-button">
            <button type="submit" class="button button-primary"><?php echo esc_html__('Save Settings', 'wp-petition'); ?></button>
        </div>
    </form>
    
    <div class="wp-petition-about-info">
        <h3><?php echo esc_html__('About WP Petition', 'wp-petition'); ?></h3>
        <p><?php echo esc_html__('WP Petition is a WordPress plugin for time-based crowdfunding campaigns where users can donate their time instead of money.', 'wp-petition'); ?></p>
        <p><?php echo esc_html__('Version:', 'wp-petition'); ?> <?php echo esc_html(WP_PETITION_VERSION); ?></p>
        <p><?php echo esc_html__('Author:', 'wp-petition'); ?> <a href="https://example.com" target="_blank">Your Name</a></p>
    </div>
</div>
