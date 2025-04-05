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
$default_goal_votes = get_option('wp_petition_default_goal_votes', 100);
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
            <label for="default_goal_votes"><?php echo esc_html__('Default Votes Goal', 'wp-petition'); ?></label>
            <input type="number" name="default_goal_votes" id="default_goal_votes" min="0" value="<?php echo esc_attr($default_goal_votes); ?>">
            <p class="description"><?php echo esc_html__('Default votes goal for new campaigns.', 'wp-petition'); ?></p>
        </div>
        
        <div class="form-field">
            <label for="social_sharing"><?php echo esc_html__('Social Sharing', 'wp-petition'); ?></label>
            <select name="social_sharing" id="social_sharing">
                <option value="1" <?php selected($social_sharing, 1); ?>><?php echo esc_html__('Enabled', 'wp-petition'); ?></option>
                <option value="0" <?php selected($social_sharing, 0); ?>><?php echo esc_html__('Disabled', 'wp-petition'); ?></option>
            </select>
            <p class="description"><?php echo esc_html__('Enable or disable social sharing buttons on the petition form.', 'wp-petition'); ?></p>
        </div>
        
        <div class="form-field">
            <label for="export_format"><?php echo esc_html__('Export Format', 'wp-petition'); ?></label>
            <select name="export_format" id="export_format">
                <option value="pdf" <?php selected($export_format, 'pdf'); ?>><?php echo esc_html__('PDF', 'wp-petition'); ?></option>
                <option value="csv" <?php selected($export_format, 'csv'); ?>><?php echo esc_html__('CSV', 'wp-petition'); ?></option>
                <option value="excel" <?php selected($export_format, 'excel'); ?>><?php echo esc_html__('Excel', 'wp-petition'); ?></option>
            </select>
            <p class="description"><?php echo esc_html__('Default format for exporting voter lists.', 'wp-petition'); ?></p>
        </div>
        
        
        <div class="form-field submit-button">
            <button type="submit" class="button button-primary"><?php echo esc_html__('Save Settings', 'wp-petition'); ?></button>
        </div>
    </form>
    
    <div class="wp-petition-about-info">
        <h3><?php echo esc_html__('About WP Petition', 'wp-petition'); ?></h3>
        <p><?php echo esc_html__('WP Petition is a WordPress plugin for petition campaigns where users can sign and support your cause.', 'wp-petition'); ?></p>
        <p><?php echo esc_html__('Version:', 'wp-petition'); ?> <?php echo esc_html(WP_PETITION_VERSION); ?></p>
        <p><?php echo esc_html__('Author:', 'wp-petition'); ?> <a href="https://example.com" target="_blank">Your Name</a></p>
    </div>
</div>
